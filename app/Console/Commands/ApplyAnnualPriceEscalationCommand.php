<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Billing\EarlyAdopterPricingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class ApplyAnnualPriceEscalationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:apply-annual-price-escalation {--dry-run : Simulasi eskalasi harga tanpa mengubah database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Jalankan eskalasi harga tahunan otomatis (0% s/d max 10%) untuk pelanggan program Early Adopter yang memenuhi syarat ulang tahun langganan.';

    /**
     * Execute the console command.
     */
    public function handle(EarlyAdopterPricingService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Memulai evaluasi eskalasi harga tahunan Early Adopter...' . ($dryRun ? ' (MODE SIMULASI / DRY-RUN)' : ''));

        $result = $service->processAnnualPriceEscalations($dryRun);

        $count = $result['processed_count'];
        $subscriptions = $result['updated_subscriptions'];

        if ($count === 0) {
            $this->line('Tidak ada langganan yang jatuh tempo eskalasi harga tahunan hari ini.');
            return Command::SUCCESS;
        }

        $this->table(
            ['ID Bisnis', 'Nama Usaha', 'Harga Lama (Rp)', 'Harga Baru (Rp)', 'Kenaikan (%)'],
            array_map(fn ($item) => [
                $item['business_id'],
                $item['business_name'],
                number_format($item['old_price'], 0, ',', '.'),
                number_format($item['new_price'], 0, ',', '.'),
                $item['percentage'] . '%',
            ], $subscriptions)
        );

        $statusMsg = $dryRun ? "Simulasi selesai. {$count} langganan memenuhi kriteria eskalasi harga." : "Eskalasi berhasil! {$count} langganan telah diperbarui harganya.";
        $this->info($statusMsg);

        Log::info("Command cooca:apply-annual-price-escalation selesai: {$count} langganan diproses" . ($dryRun ? ' (dry-run)' : ''));

        return Command::SUCCESS;
    }
}
