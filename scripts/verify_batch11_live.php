<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Post;
use Illuminate\Http\Request;

echo "=================================================================\n";
echo "VERIFIKASI LIVE BATCH 11 (ID 572-591): DATABASE & CONTROLLER RENDERING\n";
echo "=================================================================\n\n";

$researchData = json_decode(file_get_contents(__DIR__ . '/../database/data/articles/trending-research-2026-11-14.json'), true);
$research = $researchData['topics'] ?? $researchData;
$sitemapXml = file_get_contents(__DIR__ . '/../public/sitemap_blog.xml');
$blogLinks = json_decode(file_get_contents(__DIR__ . '/../blog_links.json'), true);
$blogLinkSlugs = array_column($blogLinks['links'], 'slug');

$totalTested = 0;
$totalPassed = 0;
$errors = [];

foreach ($research as $item) {
    $totalTested++;
    $id = $item['id'];
    $slug = $item['slug'];
    $title = $item['topic'];

    echo "Testing #{$id}: {$slug}\n";

    // 1. Database Check
    $post = Post::where('slug', $slug)->first();
    if (!$post) {
        $errors[] = "#{$id} Post not found in DB with slug {$slug}";
        echo "  [FAIL] Post missing in DB\n";
        continue;
    }

    if (!$post->is_published) {
        $errors[] = "#{$id} Post is_published is false";
        echo "  [FAIL] is_published is false\n";
    }

    if ($post->published_at && $post->published_at->isFuture()) {
        $errors[] = "#{$id} Post published_at is in the future ({$post->published_at})";
        echo "  [FAIL] published_at is in the future\n";
    }

    // 2. Dash Check in DB
    $emDash = chr(0xE2) . chr(0x80) . chr(0x94);
    $enDash = chr(0xE2) . chr(0x80) . chr(0x93);

    if (str_contains($post->title, $emDash) || str_contains($post->title, $enDash)) {
        $errors[] = "#{$id} Title contains em/en dash in DB";
    }
    if (str_contains((string)$post->content, $emDash) || str_contains((string)$post->content, $enDash)) {
        $errors[] = "#{$id} Content contains em/en dash in DB";
    }
    if (str_contains((string)$post->meta_description, $emDash) || str_contains((string)$post->meta_description, $enDash)) {
        $errors[] = "#{$id} Meta description contains em/en dash in DB";
    }

    // 3. Controller Rendering Check
    $request = Request::create('/blog/' . $slug, 'GET');
    $response = $app->handle($request);
    $statusCode = $response->getStatusCode();

    if ($statusCode !== 200) {
        $errors[] = "#{$id} Controller returned HTTP {$statusCode}";
        echo "  [FAIL] HTTP {$statusCode}\n";
        continue;
    }

    $html = (string) $response->getContent();

    // Check H1
    if (!str_contains($html, '<h1') || !str_contains($html, $post->title)) {
        $errors[] = "#{$id} HTML missing H1 with title";
        echo "  [FAIL] H1 missing or mismatch\n";
    }

    // Check Schema
    if (!str_contains($html, 'https://schema.org') || !str_contains($html, 'FAQPage')) {
        $errors[] = "#{$id} HTML missing Schema.org / FAQPage";
        echo "  [FAIL] Schema.org missing\n";
    }

    // Check Dash in Rendered HTML
    if (str_contains($html, $emDash) || str_contains($html, $enDash)) {
        $errors[] = "#{$id} Rendered HTML contains em/en dash";
        echo "  [FAIL] Em/En dash detected in rendered HTML\n";
    }

    // 4. Sitemap and blog_links Check
    $expectedUrl = "https://cooca.id/blog/{$slug}";
    if (!str_contains($sitemapXml, "<loc>{$expectedUrl}</loc>")) {
        $errors[] = "#{$id} Missing in sitemap_blog.xml: {$expectedUrl}";
        echo "  [FAIL] Missing in sitemap_blog.xml\n";
    }

    if (!in_array($slug, $blogLinkSlugs, true)) {
        $errors[] = "#{$id} Missing in blog_links.json: {$slug}";
        echo "  [FAIL] Missing in blog_links.json\n";
    }

    if (empty($errors) || count($errors) === 0) {
        echo "  [PASS] 100% Valid (HTTP 200, H1, Schema.org, Sitemap, 0 Dashes)\n";
        $totalPassed++;
    } else {
        $lastError = end($errors);
        echo "  [ERROR] {$lastError}\n";
    }
}

echo "\n-----------------------------------------------------------------\n";
echo "SUMMARY HASIL VERIFIKASI BATCH 11:\n";
echo "Total Artikel Diaudit: {$totalTested}\n";
echo "Total Lolos 100%: {$totalPassed}\n";
echo "Total Error: " . count($errors) . "\n";
echo "-----------------------------------------------------------------\n";

if (count($errors) > 0) {
    echo "\nDAFTAR ERROR:\n";
    foreach ($errors as $e) {
        echo "- {$e}\n";
    }
    exit(1);
}

echo "\nSEMUA 20 ARTIKEL BATCH 11 BERHASIL DIVERIFIKASI 100%!\n";
exit(0);
