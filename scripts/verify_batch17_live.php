<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Post;
use App\Http\Controllers\Web\PublicBlogController;

$slugs = [
    762 => 'kewajiban-nib-seller-marketplace-online-permendag',
    763 => 'diskon-50-persen-biaya-layanan-marketplace-umkm',
    764 => 'aturan-label-nutri-level-bpom-makanan-minuman',
];

$emDash = chr(0xE2) . chr(0x80) . chr(0x94);
$enDash = chr(0xE2) . chr(0x80) . chr(0x93);

echo "========================================================\n";
echo "BATCH 17 LIVE DATABASE & CONTROLLER VERIFICATION\n";
echo "========================================================\n";

$sitemap = file_get_contents(__DIR__ . '/../public/sitemap_blog.xml');
$blogLinksData = json_decode(file_get_contents(__DIR__ . '/../database/data/blog_links.json'), true);
$linkUrls = array_column($blogLinksData['links'] ?? $blogLinksData, 'url');
$blogLinksSet = array_flip($linkUrls);

$passedCount = 0;
$controller = new PublicBlogController();

foreach ($slugs as $id => $slug) {
    echo "Testing #{$id} [{$slug}] ... \n";

    // 1. Database check
    $post = Post::published()->where('slug', $slug)->first();
    if (!$post) {
        echo "FAILED: Post not found or not published in DB!\n";
        exit(1);
    }
    echo "  [PASS] DB record found: ID {$post->id}, Status: Published\n";

    // 2. Dash check
    if (str_contains($post->title, $emDash) || str_contains($post->title, $enDash)) {
        echo "FAILED: Em/En dash found in title!\n";
        exit(1);
    }
    if (str_contains((string)$post->content, $emDash) || str_contains((string)$post->content, $enDash)) {
        echo "FAILED: Em/En dash found in content!\n";
        exit(1);
    }
    if (str_contains((string)$post->meta_description, $emDash) || str_contains((string)$post->meta_description, $enDash)) {
        echo "FAILED: Em/En dash found in meta_description!\n";
        exit(1);
    }
    echo "  [PASS] 0 Em/En dash in title, content, meta_description\n";

    // 3. Controller Rendering Check
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
    echo "  [PASS] Controller rendered successfully with H1 and FAQPage Schema\n";

    // 4. Cover image check
    $imgLocal = __DIR__ . '/../public/images/blog/' . $slug . '.jpg';
    if (!file_exists($imgLocal)) {
        echo "FAILED: Image file not found at {$imgLocal}!\n";
        exit(1);
    }
    $imgSizeKb = round(filesize($imgLocal) / 1024, 1);
    echo "  [PASS] Cover image verified: {$slug}.jpg ({$imgSizeKb} KB)\n";

    // 5. Sitemap & blog_links check
    $url = "https://cooca.id/blog/{$slug}";
    if (!str_contains($sitemap, "<loc>{$url}</loc>")) {
        echo "FAILED: Missing in sitemap_blog.xml!\n";
        exit(1);
    }
    if (!isset($blogLinksSet[$url])) {
        echo "FAILED: Missing in database/data/blog_links.json!\n";
        exit(1);
    }
    echo "  [PASS] Present in sitemap_blog.xml and database/data/blog_links.json\n";

    $passedCount++;
}

echo "========================================================\n";
echo "SUCCESS: All {$passedCount} Batch 17 articles verified in local database and rendering engine!\n";
echo "========================================================\n";
exit(0);
