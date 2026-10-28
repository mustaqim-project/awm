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
                            {--limit=100 : Batas maksimum item per sumber}
                            {--no-download : Lewati download aset gambar lokal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan katalog inspirasi visual & download aset poster viral dari CopasPrompt.id dan Civitai ke AI Studio';

    /**
     * Execute the console command.
     */
    public function handle(InspirationHarvesterService $harvester): int
    {
        $source = (string) $this->option('source');
        $limit = (int) $this->option('limit');
        $downloadAssets = ! (bool) $this->option('no-download');

        $this->info("=== Memulai Sinkronisasi & Download Aset AI Studio ===");
        $this->line("Sumber Target : <comment>{$source}</comment>");
        $this->line("Batas Item    : <comment>{$limit}</comment>");
        $this->line("Download Aset : <comment>" . ($downloadAssets ? 'YA (Disimpan di storage/ai-studio/inspirations)' : 'TIDAK (Hanya Metadata)') . "</comment>");
        $this->newLine();

        try {
            $result = $harvester->harvest(
                sourceCode: $source,
                limit: $limit,
                downloadAssets: $downloadAssets,
                onProgress: function (string $src, int $current, int $total, $item, bool $wasDownloaded): void {
                    $dlStatus = $wasDownloaded ? '<info>[DOWNLOADED ASET]</info>' : '<comment>[TERSIMPAN]</comment>';
                    $title = \Illuminate\Support\Str::limit($item->title, 40);
                    $assetUrl = $item->thumbnail_url;
                    $this->line("  {$dlStatus} ({$src} {$current}/{$total}) {$title} &rarr; <comment>{$assetUrl}</comment>");
                }
            );

            $this->newLine();
            $this->info("=== Rangkuman Sinkronisasi per Sumber ===");

            foreach ($result['sources'] as $srcCode => $info) {
                $status = $info['status'] ?? 'unknown';
                $synced = $info['synced'] ?? 0;
                $downloaded = $info['downloaded'] ?? 0;

                if ($status === 'success') {
                    $this->info("✓ [{$srcCode}] Tersinkronisasi: {$synced} item | Aset Baru Didownload: {$downloaded} file.");
                } elseif ($status === 'skipped') {
                    $this->warn("⚠ [{$srcCode}] Dilewati: " . ($info['reason'] ?? 'Non-aktif'));
                } else {
                    $this->error("✗ [{$srcCode}] Gagal: " . ($info['error'] ?? 'Terjadi kesalahan'));
                }
            }

            $this->newLine();
            $this->info("Total Item Tersinkronisasi : {$result['total_synced']}");
            $this->info("Total Aset Lokal Didownload: {$result['total_downloaded']}");
            $this->info("Lokasi Direktori Aset      : " . storage_path('app/public/ai-studio/inspirations/'));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Fatal Error: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
