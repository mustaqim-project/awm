<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PlatformChartOfAccount;
use App\Models\PlatformJournalEntry;
use App\Models\PlatformJournalEntryLine;
use App\Models\PlatformPayable;
use App\Models\PlatformPayablePayment;
use App\Models\PlatformReceivable;
use App\Models\PlatformReceivablePayment;
use App\Models\PlatformTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PlatformAccountingService
{
    /**
     * Map transaction category to appropriate chart of account code.
     */
    public function resolveCategoryAccountCode(string $type, string $category): string
    {
        if ($type === PlatformTransaction::TYPE_IN) {
            return match ($category) {
                PlatformTransaction::CAT_SUBSCRIPTION_REVENUE, 'langganan_billing' => '4110',
                PlatformTransaction::CAT_AI_TOKEN_REVENUE => '4120',
                PlatformTransaction::CAT_SETUP_FEE => '4130',
                default => '4190',
            };
        }

        return match ($category) {
            PlatformTransaction::CAT_MERCHANT_SETTLEMENT, 'settlement_merchant' => '2110',
            PlatformTransaction::CAT_INFRASTRUCTURE => '5110',
            PlatformTransaction::CAT_AI_API_COST => '5120',
            PlatformTransaction::CAT_WHATSAPP_GATEWAY => '5130',
            PlatformTransaction::CAT_SALARY => '5210',
            PlatformTransaction::CAT_MARKETING => '5220',
            PlatformTransaction::CAT_OPERATIONAL, 'operasional' => '5230',
            default => '5290',
        };
    }

    /**
     * Automatically create double-entry journal for a platform transaction.
     */
    public function syncTransactionJournal(PlatformTransaction $transaction): PlatformJournalEntry
    {
        return DB::transaction(function () use ($transaction): PlatformJournalEntry {
            // Remove existing journal if updating
            if ($transaction->journal_entry_id) {
                $existing = PlatformJournalEntry::find($transaction->journal_entry_id);
                if ($existing) {
                    $existing->lines()->delete();
                    $existing->delete();
                }
            }

            $cashAccount = PlatformChartOfAccount::where('code', '1120')->first()
                ?? PlatformChartOfAccount::where('code', '1110')->firstOrFail();

            $counterAccountCode = $this->resolveCategoryAccountCode($transaction->type, $transaction->category);
            $counterAccount = PlatformChartOfAccount::where('code', $counterAccountCode)->first()
                ?? PlatformChartOfAccount::where('type', $transaction->type === PlatformTransaction::TYPE_IN ? 'revenue' : 'expense')->firstOrFail();

            $amount = (float) $transaction->amount;
            $journal = PlatformJournalEntry::create([
                'entry_date' => $transaction->transaction_date,
                'reference_type' => PlatformJournalEntry::REF_TRANSACTION,
                'reference_id' => (string) $transaction->id,
                'description' => "Jurnal Otomatis: {$transaction->title} ({$transaction->transaction_number})",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'is_balanced' => true,
                'admin_id' => $transaction->admin_id,
            ]);

            if ($transaction->isInflow()) {
                // Check if this transaction is linked to a SubscriptionPayment with PPN tax
                $subPayment = $transaction->subscriptionPayment;
                $taxAmount = (float) ($subPayment?->tax_amount ?? 0);
                $taxAccount = $taxAmount > 0 ? PlatformChartOfAccount::where('code', '2140')->first() : null;

                if ($taxAmount > 0 && $taxAccount !== null) {
                    $dppAmount = max(0.0, round($amount - $taxAmount, 2));

                    // Debit: Kas & Bank (Asset +)
                    PlatformJournalEntryLine::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $cashAccount->id,
                        'type' => PlatformJournalEntryLine::TYPE_DEBIT,
                        'amount' => $amount,
                        'notes' => 'Penerimaan Kas/Bank dari transaksi ' . $transaction->transaction_number,
                    ]);

                    // Credit: Pendapatan SaaS (Revenue +) DPP Pokok
                    PlatformJournalEntryLine::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $counterAccount->id,
                        'type' => PlatformJournalEntryLine::TYPE_CREDIT,
                        'amount' => $dppAmount,
                        'notes' => $counterAccount->name . ' (DPP Pokok)',
                    ]);

                    // Credit: Hutang PPN Keluaran Platform (Liability +)
                    PlatformJournalEntryLine::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $taxAccount->id,
                        'type' => PlatformJournalEntryLine::TYPE_CREDIT,
                        'amount' => $taxAmount,
                        'notes' => 'Hutang PPN Keluaran Platform (' . ($subPayment->tax_rate ?? 11) . '%)',
                    ]);
                } else {
                    // Normal single credit line
                    PlatformJournalEntryLine::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $cashAccount->id,
                        'type' => PlatformJournalEntryLine::TYPE_DEBIT,
                        'amount' => $amount,
                        'notes' => 'Penerimaan Kas/Bank dari transaksi ' . $transaction->transaction_number,
                    ]);

                    PlatformJournalEntryLine::create([
                        'journal_entry_id' => $journal->id,
                        'account_id' => $counterAccount->id,
                        'type' => PlatformJournalEntryLine::TYPE_CREDIT,
                        'amount' => $amount,
                        'notes' => $counterAccount->name,
                    ]);
                }
            } else {
                // Outflow:
                // If settling merchant payable: Debit: Hutang Titipan (Liability -), Credit: Kas/Bank (Asset -)
                // If expense: Debit: Beban Operasional (Expense +), Credit: Kas/Bank (Asset -)
                PlatformJournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $counterAccount->id,
                    'type' => PlatformJournalEntryLine::TYPE_DEBIT,
                    'amount' => $amount,
                    'notes' => $counterAccount->name,
                ]);

                PlatformJournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $cashAccount->id,
                    'type' => PlatformJournalEntryLine::TYPE_CREDIT,
                    'amount' => $amount,
                    'notes' => 'Pengeluaran Kas/Bank untuk transaksi ' . $transaction->transaction_number,
                ]);
            }

            $journal->verifyBalance();

            $transaction->updateQuietly(['journal_entry_id' => $journal->id]);

            return $journal;
        });
    }

    /**
     * Create manual double-entry journal entry.
     *
     * @param array{
     *     entry_date: string,
     *     description: string,
     *     lines: array<int, array{account_id: string, type: string, amount: float|int|string, notes?: string}>
     * } $data
     */
    public function recordManualJournal(array $data, ?string $adminId = null): PlatformJournalEntry
    {
        return DB::transaction(function () use ($data, $adminId): PlatformJournalEntry {
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($data['lines'] as $line) {
                $amount = (float) $line['amount'];
                if ($line['type'] === PlatformJournalEntryLine::TYPE_DEBIT) {
                    $totalDebit += $amount;
                } else {
                    $totalCredit += $amount;
                }
            }

            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new InvalidArgumentException(sprintf(
                    'Jurnal tidak seimbang! Total Debit (Rp %s) harus sama dengan Total Kredit (Rp %s).',
                    number_format($totalDebit, 0, ',', '.'),
                    number_format($totalCredit, 0, ',', '.')
                ));
            }

            $journal = PlatformJournalEntry::create([
                'entry_date' => $data['entry_date'],
                'reference_type' => PlatformJournalEntry::REF_MANUAL,
                'description' => $data['description'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_balanced' => true,
                'admin_id' => $adminId,
            ]);

            foreach ($data['lines'] as $line) {
                PlatformJournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    'account_id' => $line['account_id'],
                    'type' => $line['type'],
                    'amount' => (float) $line['amount'],
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            $journal->verifyBalance();

            return $journal;
        });
    }

    /**
     * Compute Trial Balance (Neraca Saldo) for a given date range.
     *
     * @return array{
     *     accounts: list<array<string, mixed>>,
     *     totalDebit: float,
     *     totalCredit: float,
     *     isBalanced: bool
     * }
     */
    public function getTrialBalance(?string $startDate = null, ?string $endDate = null): array
    {
        $accounts = PlatformChartOfAccount::where('is_active', true)->orderBy('code')->get();
        $items = [];
        $sumDebit = 0.0;
        $sumCredit = 0.0;

        foreach ($accounts as $acc) {
            $query = PlatformJournalEntryLine::where('account_id', $acc->id)
                ->whereHas('entry', function ($q) use ($startDate, $endDate): void {
                    if ($startDate && $endDate) {
                        $q->whereBetween('entry_date', [$startDate, $endDate]);
                    }
                });

            $debit = (float) (clone $query)->where('type', 'debit')->sum('amount');
            $credit = (float) (clone $query)->where('type', 'credit')->sum('amount');

            $endingDebit = 0.0;
            $endingCredit = 0.0;

            if ($acc->normal_balance === PlatformChartOfAccount::BALANCE_DEBIT) {
                $net = $debit - $credit;
                if ($net >= 0) {
                    $endingDebit = $net;
                } else {
                    $endingCredit = abs($net);
                }
            } else {
                $net = $credit - $debit;
                if ($net >= 0) {
                    $endingCredit = $net;
                } else {
                    $endingDebit = abs($net);
                }
            }

            $sumDebit += $endingDebit;
            $sumCredit += $endingCredit;

            $items[] = [
                'id' => $acc->id,
                'code' => $acc->code,
                'name' => $acc->name,
                'type' => $acc->type,
                'normal_balance' => $acc->normal_balance,
                'mutation_debit' => $debit,
                'mutation_credit' => $credit,
                'ending_debit' => $endingDebit,
                'ending_credit' => $endingCredit,
            ];
        }

        return [
            'accounts' => $items,
            'totalDebit' => $sumDebit,
            'totalCredit' => $sumCredit,
            'isBalanced' => abs($sumDebit - $sumCredit) < 0.01,
        ];
    }

    /**
     * Compute General Ledger (Buku Besar) per Account.
     */
    public function getGeneralLedger(string $accountId, ?string $startDate = null, ?string $endDate = null): array
    {
        $account = PlatformChartOfAccount::findOrFail($accountId);

        $linesQuery = PlatformJournalEntryLine::with('entry')
            ->where('account_id', $accountId)
            ->whereHas('entry', function ($q) use ($startDate, $endDate): void {
                if ($startDate && $endDate) {
                    $q->whereBetween('entry_date', [$startDate, $endDate]);
                }
            })
            ->join('platform_journal_entries', 'platform_journal_entry_lines.journal_entry_id', '=', 'platform_journal_entries.id')
            ->orderBy('platform_journal_entries.entry_date', 'asc')
            ->orderBy('platform_journal_entries.created_at', 'asc')
            ->select('platform_journal_entry_lines.*');

        $lines = $linesQuery->get();

        $runningBalance = 0.0;
        $ledgerRows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            $debit = $line->isDebit() ? (float) $line->amount : 0.0;
            $credit = $line->isCredit() ? (float) $line->amount : 0.0;
            $totalDebit += $debit;
            $totalCredit += $credit;

            if ($account->normal_balance === PlatformChartOfAccount::BALANCE_DEBIT) {
                $runningBalance += ($debit - $credit);
            } else {
                $runningBalance += ($credit - $debit);
            }

            $ledgerRows[] = [
                'entry_number' => $line->entry?->entry_number ?? '-',
                'entry_date' => $line->entry?->entry_date ? Carbon::parse($line->entry->entry_date)->format('d M Y') : '-',
                'description' => $line->entry?->description ?? '-',
                'notes' => $line->notes,
                'debit' => $debit,
                'credit' => $credit,
                'running_balance' => $runningBalance,
            ];
        }

        return [
            'account' => $account,
            'rows' => $ledgerRows,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'endingBalance' => $runningBalance,
        ];
    }

    /**
     * Compute Balance Sheet (Neraca Posisi Keuangan) as of given date.
     */
    public function getBalanceSheet(?string $asOfDate = null): array
    {
        $asOf = $asOfDate ?? now()->toDateString();

        $accounts = PlatformChartOfAccount::where('is_active', true)->orderBy('code')->get();

        $assets = [];
        $liabilities = [];
        $equity = [];

        $totalAssets = 0.0;
        $totalLiabilities = 0.0;
        $totalEquityWithoutIncome = 0.0;

        foreach ($accounts as $acc) {
            $balance = $acc->calculateBalance(null, $asOf);

            if ($acc->type === PlatformChartOfAccount::TYPE_ASSET) {
                $assets[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $balance];
                $totalAssets += $balance;
            } elseif ($acc->type === PlatformChartOfAccount::TYPE_LIABILITY) {
                $liabilities[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $balance];
                $totalLiabilities += $balance;
            } elseif ($acc->type === PlatformChartOfAccount::TYPE_EQUITY) {
                $equity[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $balance];
                $totalEquityWithoutIncome += $balance;
            }
        }

        // Calculate current period net income for equity balance
        $pnl = $this->getProfitAndLoss(null, $asOf);
        $currentPeriodNetIncome = $pnl['netProfit'];

        $totalEquity = $totalEquityWithoutIncome + $currentPeriodNetIncome;
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return [
            'asOfDate' => $asOf,
            'assets' => $assets,
            'totalAssets' => $totalAssets,
            'liabilities' => $liabilities,
            'totalLiabilities' => $totalLiabilities,
            'equity' => $equity,
            'currentPeriodNetIncome' => $currentPeriodNetIncome,
            'totalEquity' => $totalEquity,
            'totalLiabilitiesAndEquity' => $totalLiabilitiesAndEquity,
            'isBalanced' => abs($totalAssets - $totalLiabilitiesAndEquity) < 0.01,
        ];
    }

    /**
     * Compute Profit and Loss (Laporan Laba Rugi) for a given date range.
     */
    public function getProfitAndLoss(?string $startDate = null, ?string $endDate = null): array
    {
        $accounts = PlatformChartOfAccount::where('is_active', true)->orderBy('code')->get();

        $revenues = [];
        $expenses = [];
        $totalRevenue = 0.0;
        $totalExpense = 0.0;

        foreach ($accounts as $acc) {
            $balance = $acc->calculateBalance($startDate, $endDate);

            if ($acc->type === PlatformChartOfAccount::TYPE_REVENUE) {
                $revenues[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $balance];
                $totalRevenue += $balance;
            } elseif ($acc->type === PlatformChartOfAccount::TYPE_EXPENSE) {
                $expenses[] = ['code' => $acc->code, 'name' => $acc->name, 'amount' => $balance];
                $totalExpense += $balance;
            }
        }

        $netProfit = $totalRevenue - $totalExpense;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'revenues' => $revenues,
            'totalRevenue' => $totalRevenue,
            'expenses' => $expenses,
            'totalExpense' => $totalExpense,
            'netProfit' => $netProfit,
            'profitMargin' => $profitMargin,
        ];
    }

    /**
     * Generate double-entry journal for a newly issued Platform Receivable (Invoice Piutang).
     * Debit: 1150 (Piutang Usaha & Tagihan Tenant)
     * Credit: 4110 / 4130 / 4190 (Pendapatan Usaha)
     */
    public function syncReceivableInvoiceJournal(PlatformReceivable $receivable): PlatformJournalEntry
    {
        return DB::transaction(function () use ($receivable): PlatformJournalEntry {
            if ($receivable->journal_entry_id) {
                $existing = PlatformJournalEntry::find($receivable->journal_entry_id);
                if ($existing) {
                    $existing->lines()->delete();
                    $existing->delete();
                }
            }

            $arAccount = PlatformChartOfAccount::where('code', '1150')->firstOrFail();

            $revenueCode = match ($receivable->category) {
                PlatformReceivable::CAT_SUBSCRIPTION, PlatformReceivable::CAT_ENTERPRISE_ANNUAL => '4110',
                PlatformReceivable::CAT_CUSTOM_SETUP, PlatformReceivable::CAT_CONSULTING => '4130',
                default => '4190',
            };
            $revenueAccount = PlatformChartOfAccount::where('code', $revenueCode)->first()
                ?? PlatformChartOfAccount::where('type', PlatformChartOfAccount::TYPE_REVENUE)->firstOrFail();

            $amount = (float) $receivable->total_amount;

            $journal = PlatformJournalEntry::create([
                'entry_date' => $receivable->invoice_date,
                'reference_type' => PlatformJournalEntry::REF_RECEIVABLE_INVOICE,
                'reference_id' => (string) $receivable->id,
                'description' => "Pengakuan Piutang: {$receivable->invoice_number} - {$receivable->client_name} ({$receivable->title})",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'is_balanced' => true,
                'admin_id' => $receivable->admin_id,
            ]);

            // Debit: Piutang Usaha
            PlatformJournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $arAccount->id,
                'type' => PlatformJournalEntryLine::TYPE_DEBIT,
                'amount' => $amount,
                'notes' => 'Tagihan invoice piutang kepada ' . $receivable->client_name,
            ]);

            // Credit: Pendapatan Usaha
            PlatformJournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $revenueAccount->id,
                'type' => PlatformJournalEntryLine::TYPE_CREDIT,
                'amount' => $amount,
                'notes' => $revenueAccount->name,
            ]);

            $journal->verifyBalance();
            $receivable->updateQuietly(['journal_entry_id' => $journal->id]);

            return $journal;
        });
    }

    /**
     * Generate double-entry journal for a received Platform Receivable Payment.
     * Debit: 1120 / 1110 (Kas & Bank)
     * Credit: 1150 (Piutang Usaha & Tagihan Tenant)
     */
    public function syncReceivablePaymentJournal(PlatformReceivablePayment $payment): PlatformJournalEntry
    {
        return DB::transaction(function () use ($payment): PlatformJournalEntry {
            if ($payment->journal_entry_id) {
                $existing = PlatformJournalEntry::find($payment->journal_entry_id);
                if ($existing) {
                    $existing->lines()->delete();
                    $existing->delete();
                }
            }

            $cashAccount = $payment->account
                ?? PlatformChartOfAccount::where('code', '1120')->first()
                ?? PlatformChartOfAccount::where('type', PlatformChartOfAccount::TYPE_ASSET)->firstOrFail();

            $arAccount = PlatformChartOfAccount::where('code', '1150')->firstOrFail();

            $amount = (float) $payment->amount;
            $receivable = $payment->receivable;

            $journal = PlatformJournalEntry::create([
                'entry_date' => $payment->payment_date,
                'reference_type' => PlatformJournalEntry::REF_RECEIVABLE_PAYMENT,
                'reference_id' => (string) $payment->id,
                'description' => "Penerimaan Piutang: {$payment->payment_number} ({$receivable?->invoice_number} - {$receivable?->client_name})",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'is_balanced' => true,
                'admin_id' => $payment->admin_id,
            ]);

            // Debit: Kas & Bank
            PlatformJournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $cashAccount->id,
                'type' => PlatformJournalEntryLine::TYPE_DEBIT,
                'amount' => $amount,
                'notes' => 'Penerimaan pembayaran piutang via ' . $cashAccount->name,
            ]);

            // Credit: Piutang Usaha
            PlatformJournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $arAccount->id,
                'type' => PlatformJournalEntryLine::TYPE_CREDIT,
                'amount' => $amount,
                'notes' => 'Pengurangan piutang invoice ' . ($receivable?->invoice_number ?? '-'),
            ]);

            $journal->verifyBalance();
            $payment->updateQuietly(['journal_entry_id' => $journal->id]);

            return $journal;
        });
    }

    /**
     * Generate double-entry journal for a recorded Platform Payable (Tagihan Vendor).
     * Debit: 5110 / 5120 / 5130 / 52xx (Beban Operasional Terkait)
     * Credit: 2130 (Hutang Biaya Operasional & Vendor)
     */
    public function syncPayableBillJournal(PlatformPayable $payable): PlatformJournalEntry
    {
        return DB::transaction(function () use ($payable): PlatformJournalEntry {
            if ($payable->journal_entry_id) {
                $existing = PlatformJournalEntry::find($payable->journal_entry_id);
                if ($existing) {
                    $existing->lines()->delete();
                    $existing->delete();
                }
            }

            $apAccount = PlatformChartOfAccount::where('code', '2130')->firstOrFail();

            $expenseAccount = $payable->expenseAccount;
            if (! $expenseAccount) {
                $expenseCode = match ($payable->vendor_category) {
                    PlatformPayable::CAT_INFRASTRUCTURE => '5110',
                    PlatformPayable::CAT_AI_API => '5120',
                    PlatformPayable::CAT_WHATSAPP_GATEWAY => '5130',
                    PlatformPayable::CAT_SALARY => '5210',
                    PlatformPayable::CAT_MARKETING => '5220',
                    PlatformPayable::CAT_OFFICE_RENT, PlatformPayable::CAT_LEGAL_TAX => '5230',
                    default => '5290',
                };
                $expenseAccount = PlatformChartOfAccount::where('code', $expenseCode)->first()
                    ?? PlatformChartOfAccount::where('type', PlatformChartOfAccount::TYPE_EXPENSE)->firstOrFail();
            }

            $amount = (float) $payable->total_amount;

            $journal = PlatformJournalEntry::create([
                'entry_date' => $payable->bill_date,
                'reference_type' => PlatformJournalEntry::REF_PAYABLE_BILL,
                'reference_id' => (string) $payable->id,
                'description' => "Pengakuan Hutang: {$payable->bill_number} - {$payable->vendor_name} ({$payable->title})",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'is_balanced' => true,
                'admin_id' => $payable->admin_id,
            ]);

            // Debit: Beban Operasional Terkait
            PlatformJournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $expenseAccount->id,
                'type' => PlatformJournalEntryLine::TYPE_DEBIT,
                'amount' => $amount,
                'notes' => $expenseAccount->name . ' - ' . $payable->vendor_name,
            ]);

            // Credit: Hutang Usaha / Hutang Vendor
            PlatformJournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $apAccount->id,
                'type' => PlatformJournalEntryLine::TYPE_CREDIT,
                'amount' => $amount,
                'notes' => 'Kewajiban hutang tagihan vendor ' . $payable->vendor_name,
            ]);

            $journal->verifyBalance();
            $payable->updateQuietly(['journal_entry_id' => $journal->id]);

            return $journal;
        });
    }

    /**
     * Generate double-entry journal for a recorded Platform Payable Payment (Pelunasan Vendor).
     * Debit: 2130 (Hutang Biaya Operasional & Vendor)
     * Credit: 1120 / 1110 (Kas & Bank)
     */
    public function syncPayablePaymentJournal(PlatformPayablePayment $payment): PlatformJournalEntry
    {
        return DB::transaction(function () use ($payment): PlatformJournalEntry {
            if ($payment->journal_entry_id) {
                $existing = PlatformJournalEntry::find($payment->journal_entry_id);
                if ($existing) {
                    $existing->lines()->delete();
                    $existing->delete();
                }
            }

            $apAccount = PlatformChartOfAccount::where('code', '2130')->firstOrFail();

            $cashAccount = $payment->account
                ?? PlatformChartOfAccount::where('code', '1120')->first()
                ?? PlatformChartOfAccount::where('type', PlatformChartOfAccount::TYPE_ASSET)->firstOrFail();

            $amount = (float) $payment->amount;
            $payable = $payment->payable;

            $journal = PlatformJournalEntry::create([
                'entry_date' => $payment->payment_date,
                'reference_type' => PlatformJournalEntry::REF_PAYABLE_PAYMENT,
                'reference_id' => (string) $payment->id,
                'description' => "Pelunasan Hutang Vendor: {$payment->payment_number} ({$payable?->bill_number} - {$payable?->vendor_name})",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'is_balanced' => true,
                'admin_id' => $payment->admin_id,
            ]);

            // Debit: Hutang Usaha / Vendor
            PlatformJournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $apAccount->id,
                'type' => PlatformJournalEntryLine::TYPE_DEBIT,
                'amount' => $amount,
                'notes' => 'Pelunasan kewajiban hutang kepada ' . ($payable?->vendor_name ?? '-'),
            ]);

            // Credit: Kas & Bank
            PlatformJournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $cashAccount->id,
                'type' => PlatformJournalEntryLine::TYPE_CREDIT,
                'amount' => $amount,
                'notes' => 'Pengeluaran kas/bank via ' . $cashAccount->name,
            ]);

            $journal->verifyBalance();
            $payment->updateQuietly(['journal_entry_id' => $journal->id]);

            return $journal;
        });
    }

    /**
     * Compute Accounts Receivable Aging Summary.
     */
    public function getReceivableAging(): array
    {
        $receivables = PlatformReceivable::all();

        $totalInvoiced = 0.0;
        $totalPaid = 0.0;
        $totalBalance = 0.0;

        $buckets = [
            'current' => 0.0,
            '1_30'    => 0.0,
            '31_60'   => 0.0,
            '61_90'   => 0.0,
            'over_90' => 0.0,
        ];

        $counts = [
            'total'     => $receivables->count(),
            'paid'      => 0,
            'partial'   => 0,
            'unpaid'    => 0,
            'overdue'   => 0,
        ];

        $today = Carbon::now()->startOfDay();

        foreach ($receivables as $ar) {
            $totalInvoiced += (float) $ar->total_amount;
            $totalPaid += (float) $ar->paid_amount;
            $balance = (float) $ar->balance_due;
            $totalBalance += $balance;

            if ($ar->status === PlatformReceivable::STATUS_PAID || $balance <= 0.01) {
                $counts['paid']++;
                continue;
            }

            if ($ar->paid_amount > 0) {
                $counts['partial']++;
            } else {
                $counts['unpaid']++;
            }

            $dueDate = Carbon::parse($ar->due_date)->startOfDay();

            if ($dueDate->gte($today)) {
                $buckets['current'] += $balance;
            } else {
                $counts['overdue']++;
                $days = $dueDate->diffInDays($today);

                if ($days <= 30) {
                    $buckets['1_30'] += $balance;
                } elseif ($days <= 60) {
                    $buckets['31_60'] += $balance;
                } elseif ($days <= 90) {
                    $buckets['61_90'] += $balance;
                } else {
                    $buckets['over_90'] += $balance;
                }
            }
        }

        $totalOverdue = $buckets['1_30'] + $buckets['31_60'] + $buckets['61_90'] + $buckets['over_90'];
        $collectionRate = $totalInvoiced > 0 ? round(($totalPaid / $totalInvoiced) * 100, 1) : 0;

        return [
            'totalInvoiced'  => $totalInvoiced,
            'totalPaid'      => $totalPaid,
            'totalBalance'   => $totalBalance,
            'totalOverdue'   => $totalOverdue,
            'collectionRate' => $collectionRate,
            'buckets'        => $buckets,
            'counts'         => $counts,
        ];
    }

    /**
     * Compute Accounts Payable Aging Summary.
     */
    public function getPayableAging(): array
    {
        $payables = PlatformPayable::all();

        $totalBilled = 0.0;
        $totalPaid = 0.0;
        $totalBalance = 0.0;

        $buckets = [
            'current' => 0.0,
            '1_30'    => 0.0,
            '31_60'   => 0.0,
            '61_90'   => 0.0,
            'over_90' => 0.0,
        ];

        $counts = [
            'total'     => $payables->count(),
            'paid'      => 0,
            'partial'   => 0,
            'unpaid'    => 0,
            'overdue'   => 0,
        ];

        $today = Carbon::now()->startOfDay();

        foreach ($payables as $ap) {
            $totalBilled += (float) $ap->total_amount;
            $totalPaid += (float) $ap->paid_amount;
            $balance = (float) $ap->balance_due;
            $totalBalance += $balance;

            if ($ap->status === PlatformPayable::STATUS_PAID || $balance <= 0.01) {
                $counts['paid']++;
                continue;
            }

            if ($ap->paid_amount > 0) {
                $counts['partial']++;
            } else {
                $counts['unpaid']++;
            }

            $dueDate = Carbon::parse($ap->due_date)->startOfDay();

            if ($dueDate->gte($today)) {
                $buckets['current'] += $balance;
            } else {
                $counts['overdue']++;
                $days = $dueDate->diffInDays($today);

                if ($days <= 30) {
                    $buckets['1_30'] += $balance;
                } elseif ($days <= 60) {
                    $buckets['31_60'] += $balance;
                } elseif ($days <= 90) {
                    $buckets['61_90'] += $balance;
                } else {
                    $buckets['over_90'] += $balance;
                }
            }
        }

        $totalOverdue = $buckets['1_30'] + $buckets['31_60'] + $buckets['61_90'] + $buckets['over_90'];
        $paymentRate = $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100, 1) : 0;

        return [
            'totalBilled'  => $totalBilled,
            'totalPaid'    => $totalPaid,
            'totalBalance' => $totalBalance,
            'totalOverdue' => $totalOverdue,
            'paymentRate'  => $paymentRate,
            'buckets'      => $buckets,
            'counts'       => $counts,
        ];
    }

    /**
     * Compute Platform Tax & VAT (PPN) Summary for Corporate Admin reporting.
     *
     * @return array<string, mixed>
     */
    public function getPlatformTaxSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->toDateString();

        $taxEnabled = (bool) \App\Models\SystemSetting::get('platform_tax_enabled', '0');
        $taxRate = max(0.0, (float) (\App\Models\SystemSetting::get('platform_tax_rate') ?: '11.0'));
        $taxInclusive = (bool) \App\Models\SystemSetting::get('platform_tax_inclusive', '0');
        $taxName = (string) (\App\Models\SystemSetting::get('platform_tax_name') ?: 'PPN');
        $applySub = (bool) \App\Models\SystemSetting::get('platform_tax_apply_subscription', '1');
        $applyTopup = (bool) \App\Models\SystemSetting::get('platform_tax_apply_topup', '1');
        $companyNpwp = (string) (\App\Models\SystemSetting::get('platform_tax_company_npwp') ?: '01.234.567.8-012.000');
        $companyName = (string) (\App\Models\SystemSetting::get('platform_tax_company_name') ?: 'PT Cooca Digital Solusindo');

        // Query approved subscription payments in date range
        $subQuery = \App\Models\SubscriptionPayment::with(['business', 'user'])
            ->where('status', \App\Models\SubscriptionPayment::STATUS_APPROVED)
            ->whereBetween('approved_at', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);

        $approvedPayments = $subQuery->orderBy('approved_at', 'desc')->get();

        $totalGrossRevenue = 0.0;
        $totalDpp = 0.0;
        $totalTaxCollected = 0.0;
        $taxablePayments = [];

        foreach ($approvedPayments as $payment) {
            $gross = (float) ($payment->total_payable ?? $payment->amount);
            $tax = (float) ($payment->tax_amount ?? 0);
            $dpp = $payment->subtotal ? (float) $payment->subtotal : max(0.0, $gross - $tax);

            $totalGrossRevenue += $gross;
            $totalDpp += $dpp;
            $totalTaxCollected += $tax;

            if ($tax > 0) {
                $taxablePayments[] = [
                    'id' => $payment->id,
                    'order_number' => $payment->order_number,
                    'approved_at' => $payment->approved_at ? Carbon::parse($payment->approved_at)->format('d M Y H:i') : '-',
                    'business_name' => $payment->business?->name ?? 'Tenant',
                    'plan_name' => $payment->package_name ?: ($payment->plan_code ?? 'Langganan SaaS'),
                    'dpp' => $dpp,
                    'tax_rate' => (float) ($payment->tax_rate ?? $taxRate),
                    'tax_amount' => $tax,
                    'total_payable' => $gross,
                    'is_inclusive' => (bool) $payment->is_tax_inclusive,
                ];
            }
        }

        // Fetch current VAT Payable (Akun 2140) balance from COA
        $vatAccount = PlatformChartOfAccount::where('code', '2140')->first();
        $vatPayableBalance = 0.0;
        if ($vatAccount) {
            $ledger = $this->getGeneralLedger((string) $vatAccount->id);
            $vatPayableBalance = $ledger['closingBalance'] ?? 0.0;
        }

        // Monthly SPT recap
        $monthlyRecap = [];
        foreach ($taxablePayments as $p) {
            $monthKey = Carbon::parse($p['approved_at'])->format('Y-m');
            if (! isset($monthlyRecap[$monthKey])) {
                $monthlyRecap[$monthKey] = [
                    'period' => Carbon::parse($p['approved_at'])->translatedFormat('F Y'),
                    'count' => 0,
                    'dpp' => 0.0,
                    'tax' => 0.0,
                    'gross' => 0.0,
                ];
            }
            $monthlyRecap[$monthKey]['count']++;
            $monthlyRecap[$monthKey]['dpp'] += $p['dpp'];
            $monthlyRecap[$monthKey]['tax'] += $p['tax_amount'];
            $monthlyRecap[$monthKey]['gross'] += $p['total_payable'];
        }

        return [
            'tax_enabled' => $taxEnabled,
            'tax_rate' => $taxRate,
            'tax_inclusive' => $taxInclusive,
            'tax_name' => $taxName,
            'apply_subscription' => $applySub,
            'apply_topup' => $applyTopup,
            'company_npwp' => $companyNpwp,
            'company_name' => $companyName,
            'total_gross_revenue' => $totalGrossRevenue,
            'total_dpp' => $totalDpp,
            'total_tax_collected' => $totalTaxCollected,
            'vat_payable_balance' => $vatPayableBalance,
            'taxable_payments' => $taxablePayments,
            'taxable_count' => count($taxablePayments),
            'monthly_recap' => array_values($monthlyRecap),
        ];
    }

    /**
     * Record settlement / payment of VAT (PPN) to DJP/Kas Negara.
     *
     * @param array{
     *     amount: float|int|string,
     *     payment_date: string,
     *     reference_number: string,
     *     notes?: string|null,
     *     attachment_path?: string|null
     * } $data
     */
    public function recordTaxSettlement(array $data, ?string $adminId = null): PlatformTransaction
    {
        return DB::transaction(function () use ($data, $adminId): PlatformTransaction {
            $amount = (float) $data['amount'];
            if ($amount <= 0.0) {
                throw new InvalidArgumentException('Nominal setoran pajak harus lebih besar dari 0.');
            }

            $cashAccount = PlatformChartOfAccount::where('code', '1120')->first()
                ?? PlatformChartOfAccount::where('code', '1110')->firstOrFail();

            $taxAccount = PlatformChartOfAccount::where('code', '2140')->first()
                ?? PlatformChartOfAccount::firstOrCreate(
                    ['code' => '2140'],
                    [
                        'name' => 'Hutang Pajak Penjualan Platform (PPN Keluaran SaaS & Top-Up)',
                        'type' => PlatformChartOfAccount::TYPE_LIABILITY,
                        'normal_balance' => PlatformChartOfAccount::BALANCE_CREDIT,
                        'category_group' => 'hutang_lancar',
                        'is_system' => true,
                        'is_active' => true,
                    ]
                );

            $datePrefix = Carbon::parse($data['payment_date'])->format('Ym');
            $count = PlatformTransaction::where('transaction_number', 'like', "TRX-{$datePrefix}-%")->count() + 1;
            $trxNumber = sprintf('TRX-%s-%04d', $datePrefix, $count);

            $ntpn = $data['reference_number'] ?? 'NTPN';

            $trx = PlatformTransaction::create([
                'transaction_number' => $trxNumber,
                'type' => PlatformTransaction::TYPE_OUT,
                'category' => PlatformTransaction::CAT_OPERATIONAL,
                'title' => "Setoran PPN Kas Negara (NTPN: {$ntpn})",
                'amount' => $amount,
                'transaction_date' => $data['payment_date'],
                'payment_method' => 'e-Billing DJP',
                'notes' => $data['notes'] ?? "Setor PPN Masa melalui e-Billing / NTPN #{$ntpn}",
                'attachment_path' => $data['attachment_path'] ?? null,
                'source_type' => 'manual',
                'admin_id' => $adminId,
            ]);

            // Create balanced journal:
            // Debit: Hutang PPN Keluaran 2140 (mengurangi hutang)
            // Credit: Kas & Bank 1120 (mengurangi kas)
            $journal = PlatformJournalEntry::create([
                'entry_date' => $data['payment_date'],
                'reference_type' => PlatformJournalEntry::REF_TRANSACTION,
                'reference_id' => (string) $trx->id,
                'description' => "Jurnal Setor Pajak PPN: NTPN {$ntpn} ({$trxNumber})",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'is_balanced' => true,
                'admin_id' => $adminId,
            ]);

            PlatformJournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $taxAccount->id,
                'type' => PlatformJournalEntryLine::TYPE_DEBIT,
                'amount' => $amount,
                'notes' => 'Pelunasan Hutang PPN ke Kas Negara (NTPN ' . $ntpn . ')',
            ]);

            PlatformJournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $cashAccount->id,
                'type' => PlatformJournalEntryLine::TYPE_CREDIT,
                'amount' => $amount,
                'notes' => 'Pengeluaran Kas/Bank untuk Pembayaran Pajak',
            ]);

            $journal->verifyBalance();
            $trx->updateQuietly(['journal_entry_id' => $journal->id]);

            return $trx;
        });
    }
}
