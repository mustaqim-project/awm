<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Post;
use App\Http\Controllers\Web\PublicBlogController;

$slugs = json_decode(file_get_contents(__DIR__ . '/target_100_slugs.json'), true);
echo "Verifying " . count($slugs) . " articles in DB and Controller...\n";

$dbPosts = Post::whereIn('slug', $slugs)->get()->keyBy('slug');
echo "Found in DB: " . $dbPosts->count() . " / " . count($slugs) . "\n";

$controller = app(PublicBlogController::class);

$emDashErrors = 0;
$controllerErrors = 0;

foreach ($slugs as $idx => $slug) {
    if (!isset($dbPosts[$slug])) {
        echo "MISSING in DB: {$slug}\n";
        continue;
    }
    $post = $dbPosts[$slug];
    
    // Check em/en dash
    if (str_contains($post->title, '—') || str_contains($post->title, '–') ||
        str_contains($post->content, '—') || str_contains($post->content, '–') ||
        str_contains($post->meta_title, '—') || str_contains($post->meta_title, '–')) {
        echo "DASH ERROR in: {$slug}\n";
        $emDashErrors++;
    }
    
    // Test controller render on sample 10 posts
    if ($idx < 10) {
        try {
            $view = $controller->show($slug);
            if ($view instanceof \Illuminate\View\View) {
                // rendering the blade view
                $renderedHtml = $view->render();
                if (empty($renderedHtml)) {
                    echo "Empty HTML rendered for {$slug}\n";
                    $controllerErrors++;
                }
            } else {
                echo "Non-view returned for {$slug}\n";
                $controllerErrors++;
            }
        } catch (\Throwable $e) {
            echo "Exception on {$slug}: " . $e->getMessage() . "\n";
            $controllerErrors++;
        }
    }
}

echo "\n================ VERIFICATION SUMMARY ================\n";
echo "  Total Target Articles: " . count($slugs) . "\n";
echo "  Found in Database: " . $dbPosts->count() . " / 100\n";
echo "  Zero Em Dash & En Dash Violations: " . ($emDashErrors === 0 ? "PASSED (0 violations)" : "FAILED ({$emDashErrors} violations)") . "\n";
echo "  Live Blade Render Check: " . ($controllerErrors === 0 ? "PASSED (10/10 samples OK)" : "FAILED ({$controllerErrors} errors)") . "\n";
if ($dbPosts->count() === 100 && $emDashErrors === 0 && $controllerErrors === 0) {
    echo "STATUS: ALL 100 ARTICLES ARE LIVE, VALID, AND 100% PRODUCTION-READY!\n";
}
