<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Post;
use App\Http\Controllers\Web\PublicBlogController;

$progressFile = __DIR__ . '/../database/data/articles/.progress-2026-11-17.json';
if (!file_exists($progressFile)) {
    echo "ERROR: Progress file not found: {$progressFile}\n";
    exit(1);
}

$progress = json_decode(file_get_contents($progressFile), true);
$articles = $progress['summary'] ?? [];

echo "========================================================\n";
echo "40 EVERGREEN ARTICLES LIVE DATABASE & INTEGRITY VERIFICATION\n";
echo "========================================================\n";
echo "Total articles in progress: " . count($articles) . "\n\n";

$emDash = chr(0xE2) . chr(0x80) . chr(0x94);
$enDash = chr(0xE2) . chr(0x80) . chr(0x93);

$sitemap = file_get_contents(__DIR__ . '/../public/sitemap_blog.xml');

$blogLinksPath = file_exists(__DIR__ . '/../database/data/blog_links.json')
    ? __DIR__ . '/../database/data/blog_links.json'
    : __DIR__ . '/../blog_links.json';
$blogLinksData = json_decode(file_get_contents($blogLinksPath), true);
$linkUrls = array_column($blogLinksData['links'] ?? $blogLinksData, 'url');
$blogLinksSet = array_flip($linkUrls);

$passedCount = 0;
$controller = new PublicBlogController();

foreach ($articles as $art) {
    $id = $art['id'];
    $slug = $art['slug'];
    $pillar = $art['pillar'];
    $title = $art['title'];

    echo sprintf("[%02d/40] Testing '%s' ... ", $id, $slug);

    // 1. Database check
    $post = Post::published()->where('slug', $slug)->first();
    if (!$post) {
        echo "FAILED: Post not found or not published in DB!\n";
        exit(1);
    }

    // 2. No Em/En dash in database
    if (str_contains($post->title, $emDash) || str_contains($post->title, $enDash)) {
        echo "FAILED: Title contains em/en dash!\n";
        exit(1);
    }
    if (str_contains((string) $post->content, $emDash) || str_contains((string) $post->content, $enDash)) {
        echo "FAILED: Content contains em/en dash!\n";
        exit(1);
    }
    if (str_contains((string) $post->meta_description, $emDash) || str_contains((string) $post->meta_description, $enDash)) {
        echo "FAILED: Meta description contains em/en dash!\n";
        exit(1);
    }

    // 3. Cover image on disk
    $imgPath = __DIR__ . '/../public/images/blog/' . $slug . '.jpg';
    if (!file_exists($imgPath)) {
        echo "FAILED: Cover image missing at {$imgPath}!\n";
        exit(1);
    }
    $imgSize = filesize($imgPath);
    if ($imgSize === 0) {
        echo "FAILED: Cover image is empty file!\n";
        exit(1);
    }

    // 4. Sitemap check
    $expectedUrl = 'https://cooca.id/blog/' . $slug;
    if (!str_contains($sitemap, "<loc>{$expectedUrl}</loc>")) {
        echo "FAILED: Missing in sitemap_blog.xml ({$expectedUrl})!\n";
        exit(1);
    }

    // 5. Controller Rendering check
    $view = $controller->show($slug);
    $html = $view->render();

    if (!str_contains($html, '<h1')) {
        echo "FAILED: Missing H1 in rendered HTML!\n";
        exit(1);
    }
    if (!str_contains($html, 'https://schema.org') || !str_contains($html, 'FAQPage')) {
        echo "FAILED: Missing Schema.org FAQPage in rendered HTML!\n";
        exit(1);
    }
    if (str_contains($html, $emDash) || str_contains($html, $enDash)) {
        echo "FAILED: Rendered HTML contains em/en dash!\n";
        exit(1);
    }

    $actualWordCount = str_word_count(strip_tags((string) $post->content));
    echo "PASSED (Words: {$actualWordCount}, Image: " . round($imgSize / 1024) . "KB)\n";
    $passedCount++;
}

echo "\n========================================================\n";
echo "SUCCESS: ALL {$passedCount} / 40 ARTICLES VERIFIED PERFECTLY (100% PASS)\n";
echo "STATUS: 100% READY AND VERIFIED LIVE\n";
echo "========================================================\n";
