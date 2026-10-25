<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\PaymentAccount;
use App\Models\PaymentSettlement;
use App\Models\PlatformChartOfAccount;
use App\Models\PlatformJournalEntry;
use App\Models\PlatformJournalEntryLine;
use App\Models\PlatformPayable;
use App\Models\PlatformPayablePayment;
use App\Models\PlatformReceivable;
use App\Models\PlatformReceivablePayment;
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
                'store', 'update', 'destroy', 'storeJournal', 'storeAccount', 'updateAccount', 'toggleAccount',
                'storeReceivable', 'recordReceivablePayment', 'storePayable', 'recordPayablePayment',
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

        // Accounts Receivable (Piutang Usaha)
        $receivableQuery = PlatformReceivable::with(['payments.account', 'business', 'admin']);
        if ($search && $activeTab === 'ar') {
            $receivableQuery->where(function ($q) use ($search): void {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }
        $receivables = $receivableQuery->orderBy('due_date', 'asc')->paginate(20, ['*'], 'ar_page')->withQueryString();
        $arAging = $this->accountingService->getReceivableAging();

        // Accounts Payable (Hutang Usaha & Tagihan Vendor)
        $payableQuery = PlatformPayable::with(['payments.account', 'expenseAccount', 'admin']);
        if ($search && $activeTab === 'ap') {
            $payableQuery->where(function ($q) use ($search): void {
                $q->where('bill_number', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }
        $payables = $payableQuery->orderBy('due_date', 'asc')->paginate(20, ['*'], 'ap_page')->withQueryString();
        $apAging = $this->accountingService->getPayableAging();

        // Tenant businesses for AR creation
        $businesses = Business::select('id', 'name', 'email', 'phone')->orderBy('name')->get();

        // Cash & Bank Accounts for payment recording
        $cashBankAccounts = PlatformChartOfAccount::where('type', PlatformChartOfAccount::TYPE_ASSET)
            ->where('category_group', 'kas_bank')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        // Expense Accounts for AP recording
        $expenseAccounts = PlatformChartOfAccount::where('type', PlatformChartOfAccount::TYPE_EXPENSE)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

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
            'categoryLabels',
            'receivables',
            'arAging',
            'payables',
            'apAging',
            'businesses',
            'cashBankAccounts',
            'expenseAccounts'
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

    /**
     * Create a new Accounts Receivable invoice (Tagihan Piutang Tenant).
     */
    public function storeReceivable(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_name'   => ['required', 'string', 'max:150'],
            'client_email'  => ['nullable', 'email', 'max:150'],
            'client_phone'  => ['nullable', 'string', 'max:50'],
            'business_id'   => ['nullable', 'exists:businesses,id'],
            'title'         => ['required', 'string', 'max:255'],
            'category'      => ['required', 'string', 'in:subscription,custom_setup,enterprise_annual,consulting,other'],
            'subtotal'      => ['required', 'numeric', 'min:0.01'],
            'tax_amount'    => ['nullable', 'numeric', 'min:0'],
            'invoice_date'  => ['required', 'date'],
            'due_date'      => ['required', 'date', 'after_or_equal:invoice_date'],
            'notes'         => ['nullable', 'string', 'max:1000'],
            'attachment'    => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('finance/receivables', 'public');
        }

        $datePrefix = Carbon::parse($validated['invoice_date'])->format('Ym');
        $count = PlatformReceivable::where('invoice_number', 'like', "INV-AR-{$datePrefix}-%")->count() + 1;
        $invNumber = sprintf('INV-AR-%s-%04d', $datePrefix, $count);

        $subtotal = (float) $validated['subtotal'];
        $tax = (float) ($validated['tax_amount'] ?? 0.0);
        $total = $subtotal + $tax;

        $receivable = PlatformReceivable::create([
            'invoice_number'  => $invNumber,
            'client_name'     => $validated['client_name'],
            'client_email'    => $validated['client_email'] ?? null,
            'client_phone'    => $validated['client_phone'] ?? null,
            'business_id'     => $validated['business_id'] ?? null,
            'invoice_date'    => $validated['invoice_date'],
            'due_date'        => $validated['due_date'],
            'subtotal'        => $subtotal,
            'tax_amount'      => $tax,
            'total_amount'    => $total,
            'paid_amount'     => 0.00,
            'balance_due'     => $total,
            'status'          => PlatformReceivable::STATUS_UNPAID,
            'category'        => $validated['category'],
            'title'           => $validated['title'],
            'notes'           => $validated['notes'] ?? null,
            'attachment_path' => $attachmentPath,
            'admin_id'        => Auth::guard('admin')->id(),
        ]);

        // Auto post double-entry journal for Accounts Receivable
        $this->accountingService->syncReceivableInvoiceJournal($receivable);

        return redirect()->route('admin.finance.index', ['tab' => 'ar'])
            ->with('status', "Invoice piutang {$receivable->invoice_number} berhasil diterbitkan dan dijurnal otomatis ke Piutang Usaha!");
    }

    /**
     * Record a payment against an Accounts Receivable invoice.
     */
    public function recordReceivablePayment(Request $request, PlatformReceivable $receivable): RedirectResponse
    {
        $validated = $request->validate([
            'amount'           => ['required', 'numeric', 'min:1', 'max:' . $receivable->balance_due],
            'payment_date'     => ['required', 'date'],
            'payment_method'   => ['required', 'string', 'max:50'],
            'account_id'       => ['required', 'exists:platform_chart_of_accounts,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'attachment'       => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('finance/receivable_payments', 'public');
        }

        $datePrefix = Carbon::parse($validated['payment_date'])->format('Ym');
        $count = PlatformReceivablePayment::where('payment_number', 'like', "PAY-AR-{$datePrefix}-%")->count() + 1;
        $payNumber = sprintf('PAY-AR-%s-%04d', $datePrefix, $count);

        $payment = PlatformReceivablePayment::create([
            'receivable_id'    => $receivable->id,
            'payment_number'   => $payNumber,
            'payment_date'     => $validated['payment_date'],
            'amount'           => (float) $validated['amount'],
            'payment_method'   => $validated['payment_method'],
            'account_id'       => $validated['account_id'],
            'attachment_path'  => $attachmentPath,
            'reference_number' => $validated['reference_number'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'admin_id'         => Auth::guard('admin')->id(),
        ]);

        // Auto post double-entry journal (Debit: Kas/Bank, Credit: Piutang)
        $this->accountingService->syncReceivablePaymentJournal($payment);

        return redirect()->route('admin.finance.index', ['tab' => 'ar'])
            ->with('status', "Pembayaran piutang {$payment->payment_number} sebesar Rp " . number_format((float) $payment->amount, 0, ',', '.') . " berhasil dicatat dan dijurnal otomatis!");
    }

    /**
     * Create a new Accounts Payable bill (Tagihan Hutang Vendor Platform).
     */
    public function storePayable(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_name'        => ['required', 'string', 'max:150'],
            'vendor_category'    => ['required', 'string', 'in:infrastructure,ai_api,whatsapp_gateway,office_rent,legal_tax,salary,marketing,other'],
            'reference_number'   => ['nullable', 'string', 'max:100'],
            'title'              => ['required', 'string', 'max:255'],
            'expense_account_id' => ['nullable', 'exists:platform_chart_of_accounts,id'],
            'subtotal'           => ['required', 'numeric', 'min:0.01'],
            'tax_amount'         => ['nullable', 'numeric', 'min:0'],
            'bill_date'          => ['required', 'date'],
            'due_date'           => ['required', 'date', 'after_or_equal:bill_date'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'attachment'         => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('finance/payables', 'public');
        }

        $datePrefix = Carbon::parse($validated['bill_date'])->format('Ym');
        $count = PlatformPayable::where('bill_number', 'like', "BILL-AP-{$datePrefix}-%")->count() + 1;
        $billNumber = sprintf('BILL-AP-%s-%04d', $datePrefix, $count);

        $subtotal = (float) $validated['subtotal'];
        $tax = (float) ($validated['tax_amount'] ?? 0.0);
        $total = $subtotal + $tax;

        $payable = PlatformPayable::create([
            'bill_number'        => $billNumber,
            'vendor_name'        => $validated['vendor_name'],
            'vendor_category'    => $validated['vendor_category'],
            'reference_number'   => $validated['reference_number'] ?? null,
            'bill_date'          => $validated['bill_date'],
            'due_date'           => $validated['due_date'],
            'subtotal'           => $subtotal,
            'tax_amount'         => $tax,
            'total_amount'       => $total,
            'paid_amount'        => 0.00,
            'balance_due'        => $total,
            'status'             => PlatformPayable::STATUS_UNPAID,
            'title'              => $validated['title'],
            'expense_account_id' => $validated['expense_account_id'] ?? null,
            'notes'              => $validated['notes'] ?? null,
            'attachment_path'    => $attachmentPath,
            'admin_id'           => Auth::guard('admin')->id(),
        ]);

        // Auto post double-entry journal for Accounts Payable (Debit: Beban, Credit: Hutang Usaha)
        $this->accountingService->syncPayableBillJournal($payable);

        return redirect()->route('admin.finance.index', ['tab' => 'ap'])
            ->with('status', "Tagihan vendor {$payable->bill_number} berhasil dicatat dan dijurnal otomatis ke Hutang Usaha!");
    }

    /**
     * Record a payment against an Accounts Payable bill.
     */
    public function recordPayablePayment(Request $request, PlatformPayable $payable): RedirectResponse
    {
        $validated = $request->validate([
            'amount'           => ['required', 'numeric', 'min:1', 'max:' . $payable->balance_due],
            'payment_date'     => ['required', 'date'],
            'payment_method'   => ['required', 'string', 'max:50'],
            'account_id'       => ['required', 'exists:platform_chart_of_accounts,id'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
            'attachment'       => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,pdf', 'max:5120'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('finance/payable_payments', 'public');
        }

        $datePrefix = Carbon::parse($validated['payment_date'])->format('Ym');
        $count = PlatformPayablePayment::where('payment_number', 'like', "PAY-AP-{$datePrefix}-%")->count() + 1;
        $payNumber = sprintf('PAY-AP-%s-%04d', $datePrefix, $count);

        $payment = PlatformPayablePayment::create([
            'payable_id'       => $payable->id,
            'payment_number'   => $payNumber,
            'payment_date'     => $validated['payment_date'],
            'amount'           => (float) $validated['amount'],
            'payment_method'   => $validated['payment_method'],
            'account_id'       => $validated['account_id'],
            'attachment_path'  => $attachmentPath,
            'reference_number' => $validated['reference_number'] ?? null,
            'notes'            => $validated['notes'] ?? null,
            'admin_id'         => Auth::guard('admin')->id(),
        ]);

        // Auto post double-entry journal (Debit: Hutang Usaha, Credit: Kas/Bank)
        $this->accountingService->syncPayablePaymentJournal($payment);

        return redirect()->route('admin.finance.index', ['tab' => 'ap'])
            ->with('status', "Pelunasan hutang vendor {$payment->payment_number} sebesar Rp " . number_format((float) $payment->amount, 0, ',', '.') . " berhasil dicatat dan dijurnal otomatis!");
    }
}
