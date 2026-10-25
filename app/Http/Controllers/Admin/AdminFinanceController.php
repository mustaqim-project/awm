<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentAccount;
use App\Models\PaymentSettlement;
use App\Models\PlatformChartOfAccount;
use App\Models\PlatformJournalEntry;
use App\Models\PlatformJournalEntryLine;
use App\Models\PlatformTransaction;
use App\Models\SubscriptionPayment;
use App\Services\PlatformAccountingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminFinanceController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly PlatformAccountingService $accountingService
    ) {}

    /**
     * Map controller actions to granular platform admin permissions.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.finance.view', only: ['index']),
            new Middleware('require.admin.permission:admin.finance.manage', only: [
                'store', 'update', 'destroy', 'storeJournal', 'storeAccount', 'updateAccount', 'toggleAccount'
            ]),
            new Middleware('require.admin.permission:admin.finance.sync', only: ['sync']),
            new Middleware('require.admin.permission:admin.finance.export', only: ['export']),
        ];
    }

    /**
     * Display the Finance & Corporate Accounting Command Center.
     */
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type      = $request->input('type');
        $category  = $request->input('category');
        $source    = $request->input('source');
        $search    = $request->input('search');
        $activeTab = $request->input('tab', 'cashflow');

        // 1. Base Query with filters for Cashflow Ledger
        $cashflowQuery = PlatformTransaction::query()
            ->with(['admin', 'subscriptionPayment', 'paymentSettlement', 'journalEntry'])
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($type && in_array($type, [PlatformTransaction::TYPE_IN, PlatformTransaction::TYPE_OUT], true)) {
            $cashflowQuery->where('type', $type);
        }

        if ($category) {
            $cashflowQuery->where('category', $category);
        }

        if ($source) {
            $cashflowQuery->where('source_type', $source);
        }

        if ($search) {
            $cashflowQuery->where(function ($q) use ($search): void {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }

        // 2. KPI Totals for selected date range
        $totalInflow = (float) PlatformTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('type', PlatformTransaction::TYPE_IN)
            ->sum('amount');

        $totalOutflow = (float) PlatformTransaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('type', PlatformTransaction::TYPE_OUT)
            ->sum('amount');

        $netProfit = $totalInflow - $totalOutflow;
        $profitMargin = $totalInflow > 0 ? round(($netProfit / $totalInflow) * 100, 1) : 0;
        $totalTransactionsCount = PlatformTransaction::whereBetween('transaction_date', [$startDate, $endDate])->count();

        // 3. Breakdown per Category for Profit & Loss
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

        // 4. Pending Unsynced Items
        $syncedSubIds = PlatformTransaction::where('source_type', 'subscription_payment')->whereNotNull('source_id')->pluck('source_id')->all();
        $unsyncedSubscriptionsCount = SubscriptionPayment::where('status', 'approved')
            ->whereNotIn('id', $syncedSubIds)
            ->count();

        $syncedSetIds = PlatformTransaction::where('source_type', 'payment_settlement')->whereNotNull('source_id')->pluck('source_id')->all();
        $unsyncedSettlementsCount = PaymentSettlement::where('status', PaymentSettlement::STATUS_COMPLETED)
            ->whereNotIn('id', $syncedSetIds)
            ->count();

        // 5. Active Chart of Accounts for selection
        $allAccounts = PlatformChartOfAccount::where('is_active', true)->orderBy('code')->get();

        // 6. Data for all tabs (instant switching)
        $transactions = $cashflowQuery->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        $journalQuery = PlatformJournalEntry::with(['lines.account', 'admin'])
            ->whereBetween('entry_date', [$startDate, $endDate]);

        if ($search) {
            $journalQuery->where(function ($q) use ($search): void {
                $q->where('entry_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $journalEntries = $journalQuery->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(25, ['*'], 'journal_page')
            ->withQueryString();

        $selectedAccountId = $request->input('account_id', $allAccounts->firstWhere('code', '1120')?->id ?? $allAccounts->first()?->id);
        $ledgerData = $selectedAccountId
            ? $this->accountingService->getGeneralLedger((string) $selectedAccountId, $startDate, $endDate)
            : null;

        $trialBalanceData = $this->accountingService->getTrialBalance($startDate, $endDate);
        $balanceSheetData = $this->accountingService->getBalanceSheet($endDate);
        $pnlData = $this->accountingService->getProfitAndLoss($startDate, $endDate);

        $accountsGrouped = PlatformChartOfAccount::withCount('journalLines')
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        // Active Platform Payment Accounts (Bank CMS)
        $platformAccounts = PaymentAccount::where('is_active', true)->get();
        $categoryLabels = PlatformTransaction::categoryLabels();

        return view('admin.finance.index', compact(
            'activeTab',
            'startDate',
            'endDate',
            'type',
            'category',
            'source',
            'search',
            'transactions',
            'journalEntries',
            'ledgerData',
            'trialBalanceData',
            'balanceSheetData',
            'pnlData',
            'accountsGrouped',
            'allAccounts',
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
     * Record a new manual transaction with optional image attachment & auto double-entry journal.
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

        $transaction = PlatformTransaction::create([
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

        // Sync with double-entry general journal
        $this->accountingService->syncTransactionJournal($transaction);

        return redirect()->route('admin.finance.index', ['tab' => 'cashflow'])
            ->with('success', "Transaksi '{$trxNumber}' dan Jurnal Umum berhasil dicatat ke dalam sistem.");
    }

    /**
     * Update an existing transaction record, image attachment & journal entry.
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
            if ($transaction->attachment_path && ! str_starts_with($transaction->attachment_path, 'payment_proofs/')) {
                Storage::disk('public')->delete($transaction->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('finance/attachments', 'public');
        }

        $transaction->update($data);

        // Update corresponding journal entry
        $this->accountingService->syncTransactionJournal($transaction);

        return redirect()->route('admin.finance.index', ['tab' => 'cashflow'])
            ->with('success', "Transaksi '{$transaction->transaction_number}' berhasil diperbarui.");
    }

    /**
     * Record a manual double-entry journal entry.
     */
    public function storeJournal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entry_date'   => ['required', 'date'],
            'description'  => ['required', 'string', 'max:255'],
            'lines'        => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'uuid', 'exists:platform_chart_of_accounts,id'],
            'lines.*.type'       => ['required', 'in:debit,credit'],
            'lines.*.amount'     => ['required', 'numeric', 'min:0.01'],
            'lines.*.notes'      => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $journal = $this->accountingService->recordManualJournal($validated, Auth::guard('admin')->id());

            return redirect()->route('admin.finance.index', ['tab' => 'journals'])
                ->with('success', "Jurnal Umum '{$journal->entry_number}' berhasil dicatat dengan status seimbang.");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Store new Chart of Account.
     */
    public function storeAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'           => ['required', 'string', 'max:30', 'unique:platform_chart_of_accounts,code'],
            'name'           => ['required', 'string', 'max:150'],
            'type'           => ['required', 'in:asset,liability,equity,revenue,expense'],
            'normal_balance' => ['required', 'in:debit,credit'],
            'category_group' => ['nullable', 'string', 'max:50'],
            'description'    => ['nullable', 'string', 'max:500'],
        ]);

        PlatformChartOfAccount::create([
            'code'           => $validated['code'],
            'name'           => $validated['name'],
            'type'           => $validated['type'],
            'normal_balance' => $validated['normal_balance'],
            'category_group' => $validated['category_group'] ?? null,
            'description'    => $validated['description'] ?? null,
            'is_system'      => false,
            'is_active'      => true,
        ]);

        return redirect()->route('admin.finance.index', ['tab' => 'coa'])
            ->with('success', "Akun '{$validated['code']} - {$validated['name']}' berhasil ditambahkan ke Bagan Akun.");
    }

    /**
     * Update an existing Chart of Account.
     */
    public function updateAccount(Request $request, PlatformChartOfAccount $account): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'normal_balance' => ['required', 'in:debit,credit'],
            'category_group' => ['nullable', 'string', 'max:50'],
            'description'    => ['nullable', 'string', 'max:500'],
        ]);

        $account->update($validated);

        return redirect()->route('admin.finance.index', ['tab' => 'coa'])
            ->with('success', "Akun '{$account->code}' berhasil diperbarui.");
    }

    /**
     * Toggle active status of a Chart of Account.
     */
    public function toggleAccount(PlatformChartOfAccount $account): RedirectResponse
    {
        if ($account->is_system) {
            return redirect()->back()->with('error', "Akun sistem bawaan '{$account->code}' tidak dapat dinonaktifkan.");
        }

        $account->update(['is_active' => ! $account->is_active]);
        $statusText = $account->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.finance.index', ['tab' => 'coa'])
            ->with('success', "Akun '{$account->code}' berhasil {$statusText}.");
    }

    /**
     * Soft delete a transaction and its associated journal entry.
     */
    public function destroy(PlatformTransaction $transaction): RedirectResponse
    {
        $num = $transaction->transaction_number;

        if ($transaction->journal_entry_id) {
            $journal = PlatformJournalEntry::find($transaction->journal_entry_id);
            if ($journal) {
                $journal->lines()->delete();
                $journal->delete();
            }
        }

        $transaction->delete();

        return redirect()->route('admin.finance.index')
            ->with('success', "Transaksi '{$num}' dan jurnal terkait berhasil dihapus.");
    }

    /**
     * 1-Click Synchronization with Billing Subscriptions & Merchant Settlements.
     */
    public function sync(): RedirectResponse
    {
        $adminId = Auth::guard('admin')->id();
        $syncedSubscriptions = 0;
        $syncedSettlements = 0;

        DB::transaction(function () use ($adminId, &$syncedSubscriptions, &$syncedSettlements): void {
            // 1. Sync Approved Subscriptions -> Kas Masuk (Inflow)
            $syncedSubIds = PlatformTransaction::where('source_type', 'subscription_payment')->whereNotNull('source_id')->pluck('source_id')->all();
            $approvedSubscriptions = SubscriptionPayment::where('status', 'approved')
                ->whereNotIn('id', $syncedSubIds)
                ->with(['business', 'user', 'billingPackage'])
                ->get();

            foreach ($approvedSubscriptions as $sub) {
                $datePrefix = Carbon::parse($sub->approved_at ?? $sub->created_at)->format('Ym');
                $count = PlatformTransaction::where('transaction_number', 'like', "TRX-{$datePrefix}-%")->count() + 1;
                $trxNumber = sprintf('TRX-%s-%04d', $datePrefix, $count);

                $bizName = $sub->business?->name ?? 'Tenant';
                $planName = $sub->plan_name ?? $sub->billingPackage?->name ?? 'Langganan';

                $trx = PlatformTransaction::create([
                    'transaction_number'      => $trxNumber,
                    'type'                    => PlatformTransaction::TYPE_IN,
                    'category'                => PlatformTransaction::CAT_SUBSCRIPTION_REVENUE,
                    'title'                   => "Langganan {$planName} ({$bizName})",
                    'amount'                  => (float) ($sub->total_payable ?? $sub->amount),
                    'transaction_date'        => Carbon::parse($sub->approved_at ?? $sub->created_at)->toDateString(),
                    'payment_method'          => strtoupper(str_replace('_', ' ', $sub->payment_method ?? 'Transfer')),
                    'notes'                   => "Order #{$sub->order_number} disetujui via billing platform.",
                    'attachment_path'         => $sub->payment_proof_path,
                    'source_type'             => 'subscription_payment',
                    'source_id'               => $sub->id,
                    'admin_id'                => $adminId,
                ]);

                // Create double-entry journal automatically
                $this->accountingService->syncTransactionJournal($trx);

                $syncedSubscriptions++;
            }

            // 2. Sync Completed Settlements -> Kas Keluar (Outflow)
            $syncedSetIds = PlatformTransaction::where('source_type', 'payment_settlement')->whereNotNull('source_id')->pluck('source_id')->all();
            $completedSettlements = PaymentSettlement::where('status', PaymentSettlement::STATUS_COMPLETED)
                ->whereNotIn('id', $syncedSetIds)
                ->with(['business'])
                ->get();

            foreach ($completedSettlements as $set) {
                $datePrefix = Carbon::parse($set->transferred_at ?? $set->settlement_date ?? $set->created_at)->format('Ym');
                $count = PlatformTransaction::where('transaction_number', 'like', "TRX-{$datePrefix}-%")->count() + 1;
                $trxNumber = sprintf('TRX-%s-%04d', $datePrefix, $count);

                $bizName = $set->business?->name ?? 'Merchant';

                $trx = PlatformTransaction::create([
                    'transaction_number'      => $trxNumber,
                    'type'                    => PlatformTransaction::TYPE_OUT,
                    'category'                => PlatformTransaction::CAT_MERCHANT_SETTLEMENT,
                    'title'                   => "Pencairan Saldo Merchant ({$bizName})",
                    'amount'                  => (float) $set->net_amount,
                    'transaction_date'        => Carbon::parse($set->transferred_at ?? $set->settlement_date ?? $set->created_at)->toDateString(),
                    'payment_method'          => $set->payment_channel ?? 'Bank Transfer',
                    'notes'                   => "Settlement #{$set->settlement_number} rekening: {$set->destination_bank}",
                    'attachment_path'         => $set->proof_image_path,
                    'source_type'             => 'payment_settlement',
                    'source_id'               => $set->id,
                    'admin_id'                => $adminId,
                ]);

                // Create double-entry journal automatically
                $this->accountingService->syncTransactionJournal($trx);

                $syncedSettlements++;
            }
        });

        $totalSynced = $syncedSubscriptions + $syncedSettlements;
        $message = $totalSynced > 0
            ? "Sinkronisasi selesai! {$syncedSubscriptions} penerimaan billing dan {$syncedSettlements} pencairan settlement berhasil dibukukan ke Buku Kas dan Jurnal Umum."
            : "Buku Kas & Jurnal sudah tersinkron dengan seluruh data billing & settlement saat ini.";

        return redirect()->route('admin.finance.index')
            ->with('success', $message);
    }

    /**
     * Export Finance & Accounting Ledger to CSV format.
     */
    public function export(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $type      = $request->input('type');
        $category  = $request->input('category');
        $tab       = $request->input('tab', 'cashflow');

        $filename = "laporan_keuangan_platform_{$startDate}_sampai_{$endDate}.csv";

        return response()->streamDownload(function () use ($startDate, $endDate, $type, $category, $tab): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            if ($tab === 'trial_balance') {
                fputcsv($handle, ['NERACA SALDO (TRIAL BALANCE) PLATFORM COOCA']);
                fputcsv($handle, ["Periode: {$startDate} s/d {$endDate}"]);
                fputcsv($handle, []);
                fputcsv($handle, ['Kode Akun', 'Nama Akun Perkiraan', 'Kategori', 'Posisi Normal', 'Mutasi Debit', 'Mutasi Kredit', 'Saldo Akhir Debit', 'Saldo Akhir Kredit']);

                $tb = $this->accountingService->getTrialBalance($startDate, $endDate);
                foreach ($tb['accounts'] as $row) {
                    fputcsv($handle, [
                        $row['code'],
                        $row['name'],
                        strtoupper($row['type']),
                        strtoupper($row['normal_balance']),
                        number_format($row['mutation_debit'], 2, '.', ''),
                        number_format($row['mutation_credit'], 2, '.', ''),
                        number_format($row['ending_debit'], 2, '.', ''),
                        number_format($row['ending_credit'], 2, '.', ''),
                    ]);
                }
                fputcsv($handle, []);
                fputcsv($handle, ['TOTAL', '', '', '', '', '', number_format($tb['totalDebit'], 2, '.', ''), number_format($tb['totalCredit'], 2, '.', '')]);
            } else {
                // Cashflow & Ledger Export
                fputcsv($handle, [
                    'Nomor Transaksi',
                    'Tanggal',
                    'Tipe Mutasi',
                    'Kategori',
                    'Keterangan / Judul',
                    'Jumlah (IDR)',
                    'Metode Pembayaran',
                    'Sumber Data',
                    'Dicatat Oleh',
                    'Lampiran Bukti',
                ]);

                $query = PlatformTransaction::with('admin')
                    ->whereBetween('transaction_date', [$startDate, $endDate]);

                if ($type && in_array($type, [PlatformTransaction::TYPE_IN, PlatformTransaction::TYPE_OUT], true)) {
                    $query->where('type', $type);
                }

                if ($category) {
                    $query->where('category', $category);
                }

                $query->orderBy('transaction_date', 'asc')->chunk(200, function ($transactions) use ($handle): void {
                    foreach ($transactions as $trx) {
                        fputcsv($handle, [
                            $trx->transaction_number,
                            Carbon::parse($trx->transaction_date)->format('Y-m-d'),
                            $trx->type === PlatformTransaction::TYPE_IN ? 'KAS MASUK' : 'KAS KELUAR',
                            PlatformTransaction::categoryLabels()[$trx->category] ?? $trx->category,
                            $trx->title,
                            number_format((float) $trx->amount, 2, '.', ''),
                            $trx->payment_method ?? '-',
                            strtoupper($trx->source_type ?? 'MANUAL'),
                            $trx->admin?->name ?? 'Sistem',
                            $trx->attachment_url ?? '-',
                        ]);
                    }
                });
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
