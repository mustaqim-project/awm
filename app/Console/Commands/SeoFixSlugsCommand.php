<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Redirect;
use App\Services\Seo\SitemapService;
use Illuminate\Console\Command;

final class SeoFixSlugsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:fix-slugs
                            {--apply : Terapkan perubahan ke database dan buat 301 redirect}
                            {--force : Paksa jalankan tanpa konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbaiki slug artikel terpotong (>= 60 karakter) dengan generator kata kunci ringkas dan simpan 301 redirect';

    /**
     * Protected slugs handled separately in Prompt 6.
     *
     * @var array<string>
     */
    private const PROTECTED_SLUGS = [
        'nib-gratis-tapi-bingung-mulai-dari-mana-ini-7-langkah-praktis-untuk-um',
        'umkm-wajib-nib-di-2026-aturan-baru-yang-perlu-kamu-tahu-sebelum-mulai',
        '5-kesalahan-saat-daftar-nib-yang-bikin-pengajuan-ditolak-oss',
        'usaha-rumahan-tanpa-nib-ini-risiko-tersembunyi-yang-bikin-rugi-puluhan',
        'cara-cek-nib-sudah-terbit-atau-belum-tanpa-harus-ke-kantor-oss',
        'modal-nib-sudah-ada-lalu-apa-5-dokumen-wajib-umkm-setelahnya',
        'bisnis-pulsa-top-up-juga-butuh-nib-ini-jawaban-untuk-umkm-digital',
        'punya-nib-bagaimana-cara-pasang-di-toko-online-shop-panduan-branding-u',
        'stok-opname-warung-sederhana-cara-hitung-selisih-tanpa-drama-dalam-30',
        'stok-opname-30-menit-selesai-tahapan-yang-bisa-kamu-tiru-untuk-warung',
        'catatan-keuangan-warung-buku-tulis-vs-aplikasi-mana-yang-lebih-akurat',
        'kelola-keuangan-warung-dari-nol-7-kebiasaan-owner-yang-bikin-rekening',
        'rapikan-keuangan-warung-dalam-14-hari-step-by-step-untuk-owner-sibuk',
        'laporan-keuangan-sederhana-umkm-sak-emkm-vs-pembukuan-sederhana',
    ];

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): int
    {
        $this->info('Memeriksa slug artikel blog yang terpotong atau >= 60 karakter...');

        $apply = (bool) $this->option('apply');

        $candidates = Post::all()->filter(function (Post $post) {
            return strlen($post->slug) >= 60 && !in_array($post->slug, self::PROTECTED_SLUGS, true);
        });

        if ($candidates->isEmpty()) {
            $this->info('Tidak ditemukan slug bermasalah di luar cluster yang diproteksi.');
            return self::SUCCESS;
        }

        $tableRows = [];
        $actions = [];
        $usedSlugs = Post::pluck('slug')->toArray();

        foreach ($candidates as $post) {
            $oldSlug = $post->slug;
            $newSlug = Post::generateCleanSlug($post->title);

            // Avoid collisions
            if ($newSlug !== $oldSlug && in_array($newSlug, $usedSlugs, true)) {
                $candidateBase = $newSlug;
                $counter = 2;
                while (in_array($candidateBase . '-' . $counter, $usedSlugs, true)) {
                    $counter++;
                }
                $newSlug = $candidateBase . '-' . $counter;
            }

            $usedSlugs[] = $newSlug;

            $actions[] = [
                'post' => $post,
                'old_slug' => $oldSlug,
                'new_slug' => $newSlug,
            ];

            $tableRows[] = [
                'old_slug' => $oldSlug,
                'old_len' => strlen($oldSlug),
                'new_slug' => $newSlug,
                'new_len' => strlen($newSlug),
            ];
        }

        $this->info("Ditemukan " . count($actions) . " artikel dengan slug >= 60 karakter:");
        $this->table(
            ['Slug Lama', 'Len', 'Usulan Slug Baru (Keyword)', 'Len'],
            $tableRows
        );

        if (!$apply) {
            $this->warn("\nMode DRY-RUN aktif. Database belum diubah.");
            $this->line("Jalankan dengan opsi '--apply' untuk menerapkan slug baru, menyimpan 301 redirect, dan update sitemap.");
            return self::SUCCESS;
        }

        $this->info("\nMenerapkan slug baru dan mendaftarkan 301 redirect...");

        $updatedCount = 0;
        foreach ($actions as $action) {
            /** @var Post $p */
            $p = $action['post'];
            $oldSlug = $action['old_slug'];
            $newSlug = $action['new_slug'];

            if ($oldSlug === $newSlug) {
                continue;
            }

            // 1. Update Post
            $p->slug = $newSlug;
            $p->save();

            // 2. Save 301 Redirect in redirects table
            Redirect::updateOrCreate(
                ['source_url' => '/blog/' . $oldSlug],
                [
                    'target_url' => '/blog/' . $newSlug,
                    'status_code' => 301,
                ]
            );

            // 3. Update any internal link references in all post contents
            Post::where('content', 'like', "%/blog/{$oldSlug}%")
                ->each(function (Post $target) use ($oldSlug, $newSlug) {
                    $target->content = str_replace("/blog/{$oldSlug}", "/blog/{$newSlug}", (string) $target->content);
                    $target->save();
                });

            $updatedCount++;
        }

        // 4. Regenerate sitemap_blog.xml
        $sitemapService->saveBlogToPublic();
        $this->info("✓ Berhasil memperbarui {$updatedCount} slug artikel, menyimpan 301 redirect, dan mengupdate public/sitemap_blog.xml!");

        return self::SUCCESS;
    }
}

