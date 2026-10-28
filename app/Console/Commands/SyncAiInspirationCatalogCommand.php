<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Ai\Inspiration\Services\InspirationHarvesterService;
use Illuminate\Console\Command;

final class SyncAiInspirationCatalogCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai-studio:sync-inspiration 
                            {--source=all : Kode sumber inspirasi (all, copasprompt, civitai)}
                            {--limit=24 : Batas maksimum item per sumber}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan katalog inspirasi visual & prompt poster viral dari CopasPrompt.id dan Civitai ke AI Studio';

    /**
     * Execute the console command.
     */
    public function handle(InspirationHarvesterService $harvester): int
    {
        $source = (string) $this->option('source');
        $limit = (int) $this->option('limit');

        $this->info("=== Memulai Sinkronisasi Inspirasi AI Studio ===");
        $this->line("Sumber Target : <comment>{$source}</comment>");
        $this->line("Batas Item    : <comment>{$limit}</comment>");

        try {
            $result = $harvester->harvest($source, $limit);

            $this->newLine();
            $this->info("--- Hasil Sinkronisasi per Sumber ---");

            foreach ($result['sources'] as $srcCode => $info) {
                $status = $info['status'] ?? 'unknown';
                $synced = $info['synced'] ?? 0;

                if ($status === 'success') {
                    $this->info("✓ [{$srcCode}] Berhasil sinkronisasi {$synced} item.");
                } elseif ($status === 'skipped') {
                    $this->warn("⚠ [{$srcCode}] Dilewati: " . ($info['reason'] ?? 'Non-aktif'));
                } else {
                    $this->error("✗ [{$srcCode}] Gagal: " . ($info['error'] ?? 'Terjadi kesalahan'));
                }
            }

            $this->newLine();
            $this->info("Total Item Tersinkronisasi: {$result['total_synced']}");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Fatal Error: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
