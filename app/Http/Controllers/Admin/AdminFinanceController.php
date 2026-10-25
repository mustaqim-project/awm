<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentAccount;
use App\Models\PaymentSettlement;
use App\Models\PlatformTransaction;
use App\Models\SubscriptionPayment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminFinanceController extends Controller implements HasMiddleware
{
    /**
     * Map controller actions to granular platform admin permissions.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.finance.view', only: ['index']),
            new Middleware('require.admin.permission:admin.finance.manage', only: ['store', 'update', 'destroy']),
            new Middleware('require.admin.permission:admin.finance.sync', only: ['sync']),
            new Middleware('require.admin.permission:admin.finance.export', only: ['export']),
        ];
    }

    /**
     * Display the Finance & Accounting Command Center.
     */
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type      = $request->input('type');
        $category  = $request->input('category');
        $source    = $request->input('source');
        $search    = $request->input('search');
        $activeTab = $request->input('tab', 'journal');

        // Base Query with filters
        $query = PlatformTransaction::query()
            ->with(['admin', 'subscriptionPayment', 'paymentSettlement'])
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($type && in_array($type, [PlatformTransaction::TYPE_IN, PlatformTransaction::TYPE_OUT], true)) {
            $query->where('type', $type);
        }

        if ($category) {
            $query->where('category', $category);
        }

        if ($source) {
            $query->where('source_type', $source);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }

        // 1. KPI Totals for selected date range
        $totalInflow = (float) PlatformTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('type', PlatformTransaction::TYPE_IN)
            ->sum('amount');

        $totalOutflow = (float) PlatformTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('type', PlatformTransaction::TYPE_OUT)
            ->sum('amount');

        $netProfit = $totalInflow - $totalOutflow;
        $profitMargin = $totalInflow > 0 ? round(($netProfit / $totalInflow) * 100, 1) : 0;
        $totalTransactionsCount = PlatformTransaction::whereBetween('transaction_date', [$startDate, $endDate])->count();

        // 2. Breakdown per Category for Profit & Loss
        $inflowCategories = PlatformTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('type', PlatformTransaction::TYPE_IN)
            ->selectRaw('category, sum(amount) as total, count(*) as count')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->all();

        $outflowCategories = PlatformTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('type', PlatformTransaction::TYPE_OUT)
            ->selectRaw('category, sum(amount) as total, count(*) as count')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->all();

        // 3. Integration & Reconciliation Status
        $unsyncedSubscriptionsCount = SubscriptionPayment::where('status', SubscriptionPayment::STATUS_APPROVED)
            ->whereNotIn('id', function ($q): void {
                $q->select('source_id')
                    ->from('platform_transactions')
                    ->where('source_type', 'subscription_payment')
                    ->whereNotNull('source_id');
            })->count();

        $unsyncedSettlementsCount = PaymentSettlement::where('status', PaymentSettlement::STATUS_COMPLETED)
            ->whereNotIn('id', function ($q): void {
                $q->select('source_id')
                    ->from('platform_transactions')
                    ->where('source_type', 'payment_settlement')
                    ->whereNotNull('source_id');
            })->count();

        // Platform accounts
        $platformAccounts = PaymentAccount::where('is_active', true)->get();

        // Paginated list
        $transactions = (clone $query)
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $categoryLabels = PlatformTransaction::categoryLabels();

        return view('admin.finance.index', compact(
            'transactions',
            'startDate',
            'endDate',
            'type',
            'category',
            'source',
            'search',
            'activeTab',
            'totalInflow',
            'totalOutflow',
            'netProfit',
            'profitMargin',
            'totalTransactionsCount',
            'inflowCategories',
            'outflowCategories',
            'unsyncedSubscriptionsCount',
            'unsyncedSettlementsCount',
            'platformAccounts',
            'categoryLabels'
        ));
    }

    /**
     * Record a new manual transaction with optional image attachment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type'             => ['required', 'in:in,out'],
            'category'         => ['required', 'string', 'max:50'],
            'title'            => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payment_method'   => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'attachment'       => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('finance/attachments', 'public');
        }

        // Generate Transaction Code (e.g. TRX-202610-0042)
        $datePrefix = Carbon::parse($validated['transaction_date'])->format('Ym');
        $count = PlatformTransaction::where('transaction_number', 'like', "TRX-{$datePrefix}-%")->count() + 1;
        $trxNumber = sprintf('TRX-%s-%04d', $datePrefix, $count);

        PlatformTransaction::create([
            'transaction_number' => $trxNumber,
            'type'               => $validated['type'],
            'category'           => $validated['category'],
            'title'              => $validated['title'],
            'amount'             => (float) $validated['amount'],
            'transaction_date'   => $validated['transaction_date'],
            'payment_method'     => $validated['payment_method'] ?? 'Kas / Transfer',
            'notes'              => $validated['notes'] ?? null,
            'attachment_path'    => $attachmentPath,
            'source_type'        => 'manual',
            'admin_id'           => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('admin.finance.index')
            ->with('success', "Transaksi '{$trxNumber}' berhasil dicatat ke dalam buku kas.");
    }

    /**
     * Update an existing transaction record & optional image attachment.
     */
    public function update(Request $request, PlatformTransaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'type'             => ['required', 'in:in,out'],
            'category'         => ['required', 'string', 'max:50'],
            'title'            => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payment_method'   => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'attachment'       => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:5120'],
        ]);

        $data = [
            'type'             => $validated['type'],
            'category'         => $validated['category'],
            'title'            => $validated['title'],
            'amount'           => (float) $validated['amount'],
            'transaction_date' => $validated['transaction_date'],
            'payment_method'   => $validated['payment_method'] ?? $transaction->payment_method,
            'notes'            => $validated['notes'] ?? $transaction->notes,
        ];

        if ($request->hasFile('attachment')) {
            // Delete old file if exists and not shared from subscription/settlement original path
            if ($transaction->attachment_path && ! str_starts_with($transaction->attachment_path, 'payment_proofs/')) {
                Storage::disk('public')->delete($transaction->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('finance/attachments', 'public');
        }

        $transaction->update($data);

        return redirect()->route('admin.finance.index')
            ->with('success', "Transaksi '{$transaction->transaction_number}' berhasil diperbarui.");
    }

    /**
     * Remove a manual transaction record.
     */
    public function destroy(PlatformTransaction $transaction): RedirectResponse
    {
        $num = $transaction->transaction_number;

        // If local custom attachment, cleanup file
        if ($transaction->attachment_path && str_starts_with($transaction->attachment_path, 'finance/attachments/')) {
            Storage::disk('public')->delete($transaction->attachment_path);
        }

        $transaction->delete();

        return redirect()->route('admin.finance.index')
            ->with('success', "Transaksi '{$num}' berhasil dihapus dari buku kas.");
    }

    /**
     * 1-Click Sync from Subscriptions (Inflow) and Settlements (Outflow).
     */
    public function sync(): RedirectResponse
    {
        $syncedSubscriptions = 0;
        $syncedSettlements = 0;

        // 1. Sync Approved Subscriptions -> Kas Masuk (Inflow)
        $approvedSubscriptions = SubscriptionPayment::where('status', SubscriptionPayment::STATUS_APPROVED)
            ->whereNotIn('id', function ($q): void {
                $q->select('source_id')
                    ->from('platform_transactions')
                    ->where('source_type', 'subscription_payment')
                    ->whereNotNull('source_id');
            })->get();

        foreach ($approvedSubscriptions as $sub) {
            $tDate = $sub->approved_at ? Carbon::parse($sub->approved_at)->toDateString() : Carbon::parse($sub->created_at)->toDateString();
            $datePrefix = Carbon::parse($tDate)->format('Ym');
            $count = PlatformTransaction::where('transaction_number', 'like', "TRX-{$datePrefix}-%")->count() + 1;
            $trxNumber = sprintf('TRX-%s-%04d', $datePrefix, $count);

            PlatformTransaction::create([
                'transaction_number' => $trxNumber,
                'type'               => PlatformTransaction::TYPE_IN,
                'category'           => PlatformTransaction::CAT_SUBSCRIPTION_REVENUE,
                'title'              => "Langganan {$sub->package_name} (#{$sub->order_number})",
                'amount'             => (float) ($sub->total_payable ?: $sub->amount),
                'transaction_date'   => $tDate,
                'payment_method'     => strtoupper($sub->payment_method ?: 'Transfer Bank'),
                'notes'              => "Otomatis tersinkronisasi dari Billing Subscriptions. Tenant: {$sub->business?->name}",
                'attachment_path'    => $sub->payment_proof_path, // Auto reuse uploaded transfer proof!
                'source_type'        => 'subscription_payment',
                'source_id'          => $sub->id,
                'admin_id'           => Auth::guard('admin')->id(),
            ]);

            $syncedSubscriptions++;
        }

        // 2. Sync Completed Settlements -> Kas Keluar (Outflow)
        $completedSettlements = PaymentSettlement::where('status', PaymentSettlement::STATUS_COMPLETED)
            ->whereNotIn('id', function ($q): void {
                $q->select('source_id')
                    ->from('platform_transactions')
                    ->where('source_type', 'payment_settlement')
                    ->whereNotNull('source_id');
            })->get();

        foreach ($completedSettlements as $set) {
            $tDate = $set->transferred_at ? Carbon::parse($set->transferred_at)->toDateString() : Carbon::parse($set->settlement_date)->toDateString();
            $datePrefix = Carbon::parse($tDate)->format('Ym');
            $count = PlatformTransaction::where('transaction_number', 'like', "TRX-{$datePrefix}-%")->count() + 1;
            $trxNumber = sprintf('TRX-%s-%04d', $datePrefix, $count);

            PlatformTransaction::create([
                'transaction_number' => $trxNumber,
                'type'               => PlatformTransaction::TYPE_OUT,
                'category'           => PlatformTransaction::CAT_MERCHANT_SETTLEMENT,
                'title'              => "Pencairan Saldo Merchant (#{$set->settlement_number})",
                'amount'             => (float) $set->net_amount,
                'transaction_date'   => $tDate,
                'payment_method'     => $set->destination_bank ?: 'Transfer Bank',
                'notes'              => "Otomatis tersinkronisasi dari Settlement Merchant. Merchant: {$set->business?->name}",
                'attachment_path'    => $set->proof_image_path, // Auto reuse admin transfer receipt!
                'source_type'        => 'payment_settlement',
                'source_id'          => $set->id,
                'admin_id'           => Auth::guard('admin')->id(),
            ]);

            $syncedSettlements++;
        }

        $msg = "Sinkronisasi selesai: {$syncedSubscriptions} transaksi langganan billing (Kas Masuk) dan {$syncedSettlements} pencairan settlement (Kas Keluar) berhasil dibukukan.";

        return redirect()->route('admin.finance.index')->with('success', $msg);
    }

    /**
     * Export Finance & Cashflow Ledger to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type      = $request->input('type');
        $category  = $request->input('category');

        $query = PlatformTransaction::query()
            ->with(['admin', 'subscriptionPayment', 'paymentSettlement'])
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($type) {
            $query->where('type', $type);
        }

        if ($category) {
            $query->where('category', $category);
        }

        $records = $query->orderBy('transaction_date')->orderBy('transaction_number')->get();
        $filename = sprintf('laporan_keuangan_platform_%s_sampai_%s.csv', $startDate, $endDate);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($records): void {
            $output = fopen('php://output', 'w');
            // Add UTF-8 BOM
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, [
                'No. Transaksi',
                'Tanggal',
                'Tipe',
                'Kategori Akun',
                'Judul / Deskripsi',
                'Nominal (IDR)',
                'Metode / Rekening',
                'Sumber Sistem',
                'Catatan',
                'Admin Pencatat',
                'Lampiran Bukti',
            ]);

            $catLabels = PlatformTransaction::categoryLabels();

            foreach ($records as $r) {
                fputcsv($output, [
                    $r->transaction_number,
                    $r->transaction_date->format('Y-m-d'),
                    $r->type === 'in' ? 'KAS MASUK (INFLOW)' : 'KAS KELUAR (OUTFLOW)',
                    $catLabels[$r->category] ?? $r->category,
                    $r->title,
                    number_format($r->amount, 2, '.', ''),
                    $r->payment_method,
                    $r->source_type === 'subscription_payment' ? 'Billing Subscriptions' : ($r->source_type === 'payment_settlement' ? 'Merchant Settlement' : 'Manual Entry'),
                    $r->notes ?? '-',
                    $r->admin?->name ?? 'System',
                    $r->attachment_url ?? 'Tidak Ada',
                ]);
            }

            fclose($output);
        }, 200, $headers);
    }
}
