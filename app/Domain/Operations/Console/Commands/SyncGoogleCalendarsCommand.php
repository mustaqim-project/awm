<?php

declare(strict_types=1);

namespace App\Domain\Operations\Console\Commands;

use App\Domain\Operations\Models\GoogleCalendarAccount;
use App\Domain\Operations\Services\GoogleCalendarSyncService;
use Illuminate\Console\Command;

class SyncGoogleCalendarsCommand extends Command
{
    protected $signature = 'operations:sync-google-calendars';

    protected $description = 'Sinkronisasi dua arah kalender kegiatan dengan Google Calendar untuk seluruh tenant aktif';

    public function handle(GoogleCalendarSyncService $syncService): int
    {
        $this->info('Memulai sinkronisasi Google Calendar...');

        $accounts = GoogleCalendarAccount::where('is_active', true)
            ->where('auto_sync', true)
            ->get();

        $totalSynced = 0;

        foreach ($accounts as $account) {
            $this->line("Sinkronisasi akun: {$account->email} (Tenant: {$account->business_id})");
            $synced = $syncService->pullEvents($account);
            $totalSynced += $synced;
        }

        $this->info("Sinkronisasi selesai. Total {$totalSynced} event diperbarui.");

        return Command::SUCCESS;
    }
}
