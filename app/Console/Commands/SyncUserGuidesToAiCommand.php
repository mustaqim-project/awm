<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\SocialMedia\AdminInboxAiService;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

final class SyncUserGuidesToAiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:ai-sync-guides';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kompilasi dan sinkronkan seluruh berkas panduan pengguna (docs/user-guides) ke AI Knowledge Base';

    public function handle(AdminInboxAiService $aiService): int
    {
        $this->info('Memulai kompilasi berkas panduan dari docs/user-guides...');

        $baseDir = base_path('docs/user-guides');
        if (! is_dir($baseDir)) {
            $this->error("Direktori {$baseDir} tidak ditemukan.");
            return self::FAILURE;
        }

        $modules = glob($baseDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
        $totalFiles = 0;
        foreach ($modules as $modDir) {
            $files = glob($modDir . DIRECTORY_SEPARATOR . '*.md') ?: [];
            $totalFiles += count($files);
            $this->line('  - Modul ' . strtoupper(basename($modDir)) . ': ' . count($files) . ' berkas panduan.');
        }

        $compiled = $aiService->compileAllUserGuides();

        SystemSetting::set('admin_inbox_ai_system_info', $compiled, AdminInboxAiService::SETTING_GROUP);

        $this->newLine();
        $this->info("✓ Sukses! Sebanyak {$totalFiles} berkas panduan dari " . count($modules) . ' modul berhasil dikompilasi dan disimpan ke AI Knowledge Base (' . strlen($compiled) . ' karakter).');

        return self::SUCCESS;
    }
}
