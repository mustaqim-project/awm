<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Communication\Services\InvoicePaymentReminderDispatcher;
use Illuminate\Console\Command;

class SendAutomatedInvoiceRemindersCommand extends Command
{
    protected $signature = 'communication:send-invoice-reminders';

    protected $description = 'Kirim otomatis pengingat tagihan faktur penjualan yang belum lunas sesuai jadwal tiap tenant';

    public function handle(InvoicePaymentReminderDispatcher $dispatcher): int
    {
        $this->info('Memulai pengiriman pengingat tagihan faktur otomatis...');

        $stats = $dispatcher->processScheduledReminders();

        $this->info("Pengingat selesai diproses. Total dicek: {$stats['total_checked']}, Terkirim: {$stats['sent']}, Dilewati: {$stats['skipped']}, Gagal: {$stats['failed']}");

        return Command::SUCCESS;
    }
}
