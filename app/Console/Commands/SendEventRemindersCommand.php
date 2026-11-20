<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Operations\Services\EventReminderService;
use Illuminate\Console\Command;

class SendEventRemindersCommand extends Command
{
    protected $signature = 'operations:send-event-reminders';

    protected $description = 'Kirim notifikasi pengingat kegiatan yang sudah jatuh tempo (WhatsApp / Email / In-App)';

    public function handle(EventReminderService $service): int
    {
        $this->info('Memeriksa antrean pengingat kegiatan...');
        $count = $service->dispatchDueReminders();
        $this->info("Berhasil mengirim {$count} pengingat kegiatan.");

        return Command::SUCCESS;
    }
}
