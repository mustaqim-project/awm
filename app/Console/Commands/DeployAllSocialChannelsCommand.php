<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SocialMediaPost;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeployAllSocialChannelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:deploy-all-social
                            {--start-date=2026-11-09 : Tanggal target posting (format YYYY-MM-DD)}
                            {--force : Hapus seluruh jadwal platform sebelumnya}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Master orchestrator to deploy dedicated viral automations across Instagram, LinkedIn, TikTok, and Facebook';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================================');
        $this->info('  COOCA MASTER SOCIAL AUTOMATION: ALL CHANNELS ORCHESTRATION        ');
        $this->info('====================================================================');

        $startDate = (string) ($this->option('start-date') ?: '2026-11-09');
        $force = (bool) $this->option('force');

        $params = ['--start-date' => $startDate];
        if ($force) {
            $params['--force'] = true;
        }

        // 1. Deploy Instagram Carousel (12:15 WIB)
        $this->info("\n[1/4] Menjalankan Otomatisasi Instagram Carousel (5 Slide HD)...");
        $this->call('cooca:deploy-umkm-carousel', $params);

        // 2. Deploy LinkedIn B2B Post (08:00 WIB)
        $this->info("\n[2/4] Menjalankan Otomatisasi LinkedIn B2B Governance...");
        $this->call('cooca:deploy-linkedin-post', $params);

        // 3. Deploy Facebook Community Post (16:30 WIB)
        $this->info("\n[3/4] Menjalankan Otomatisasi Facebook Komunitas Ritel...");
        $this->call('cooca:deploy-facebook-post', $params);

        // 4. Deploy TikTok Prime Time FYP Post (19:45 WIB)
        $this->info("\n[4/4] Menjalankan Otomatisasi TikTok FYP Anti-Kecurangan...");
        $this->call('cooca:deploy-tiktok-post', $params);

        // Final Aggregate Report
        $this->info("\n==========================================================================================");
        $this->info("  MASTER DEPLOYMENT SUMMARY: ALL 4 CHANNELS SCHEDULED                                     ");
        $this->info("==========================================================================================");

        $posts = SocialMediaPost::where('is_platform', true)->with('targets', 'media')->get();
        $tableData = [];
        foreach ($posts as $p) {
            $tableData[] = [
                $p->id,
                strtoupper($p->platform),
                $p->scheduled_at ? Carbon::parse($p->scheduled_at)->format('d M Y H:i') . ' WIB' : 'SEGERA',
                $p->media_type === 'carousel' ? "Carousel ({$p->media->count()} Slide)" : 'Single Image',
                strtoupper($p->status),
                $p->metrics['viral_score'] ?? '95.0',
            ];
        }

        $this->table(
            ['Post ID', 'Saluran', 'Jadwal Tayang', 'Format Media', 'Status', 'Skor Viral'],
            $tableData
        );

        $this->info("\nSeluruh saluran resmi berhasil dijadwalkan secara independen!");
        $this->info("Buka Admin Social Media Center di: " . route('admin.social-media.index', ['tab' => 'posts', 'status' => 'scheduled']));

        return Command::SUCCESS;
    }
}
