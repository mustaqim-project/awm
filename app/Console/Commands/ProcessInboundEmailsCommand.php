<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Communication\Models\BusinessSmtpAccount;
use App\Domain\Communication\Services\InboundEmailReaderService;
use Illuminate\Console\Command;

class ProcessInboundEmailsCommand extends Command
{
    protected $signature = 'communication:process-inbound';

    protected $description = 'Mendeteksi dan memproses email masuk dari kotak masuk IMAP seluruh tenant';

    public function handle(InboundEmailReaderService $readerService): int
    {
        $this->info('Memulai pemindaian email masuk (inbound IMAP)...');

        $accounts = BusinessSmtpAccount::withoutGlobalScopes()
            ->where('is_active', true)
            ->where('enable_inbound_reading', true)
            ->get();

        $totalChecked = 0;
        $totalMatched = 0;

        foreach ($accounts as $account) {
            $this->line("Memeriksa kotak masuk: {$account->from_address} (Tenant: {$account->business_id})");
            $result = $readerService->checkAccount($account);
            $totalChecked += $result['checked_count'];
            $totalMatched += $result['matched_count'];
        }

        $this->info("Pemindaian selesai. {$totalChecked} email diperiksa, {$totalMatched} cocok dengan dokumen (Quotation/Invoice/PO).");

        return Command::SUCCESS;
    }
}
