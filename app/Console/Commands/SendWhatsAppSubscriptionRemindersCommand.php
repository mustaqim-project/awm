<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\WhatsApp\AdminWhatsAppService;
use Illuminate\Console\Command;

class SendWhatsAppSubscriptionRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:send-wa-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi pengingat WhatsApp ke Bisnis Owner untuk masa patungan/langganan yang akan habis (H-7, H-3, H-1, Hari H)';

    /**
     * Execute the console command.
     */
    public function handle(AdminWhatsAppService $adminWaService): int
    {
        $this->info('Memeriksa langganan jatuh tempo (H-7, H-3, H-1, Hari H)...');

        $status = $adminWaService->getStatus();
        $isReady = ($status['status'] ?? '') === 'connected'
            || ($status['is_connected'] ?? false)
            || \App\Domain\WhatsApp\CloudApi\WhatsAppClient::forPlatform() !== null;

        if (! $isReady) {
            $this->warn('WhatsApp Admin Platform belum terhubung ke Meta Cloud API. Pengiriman pengingat langganan ditunda.');
            return self::SUCCESS;
        }

        try {
            $result = $adminWaService->sendAllDueReminders();
            $this->info("Pengiriman selesai: {$result['sent']} terkirim, {$result['failed']} gagal, {$result['skipped']} dilewati (sudah dikirim hari ini).");
        } catch (\Throwable $e) {
            $this->error('Gagal memproses pengingat langganan: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::warning('[SendWhatsAppSubscriptionReminders] Error: ' . $e->getMessage());
            return self::SUCCESS;
        }

        return self::SUCCESS;
    }
}
