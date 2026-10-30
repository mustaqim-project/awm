<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

view()->share('errors', new \Illuminate\Support\ViewErrorBag());

$content = file_get_contents('resources/views/public/blog/index.blade.php');
preg_match_all('/@(if|endif|forelse|empty|endforelse|foreach|endforeach)\b/', $content, $m);
print_r(array_count_values($m[1]));

$code = file_get_contents('storage/framework/views/7265cb79bec511d0308e08791b81447c.php');
preg_match_all('/\b(if\s*\(|endif;)/', $code, $m);
print_r(array_count_values($m[1]));
exit;
echo "--- Testing Blog Index ---\n";
$posts = \App\Models\Post::paginate(10);
$featuredPost = $posts->first();
$categories = collect(['Tutorial', 'Keuangan']);
$currentCluster = null;
$currentCategory = null;
$search = '';

app()->setLocale('id');
$htmlId = view('public.blog.index', compact('posts', 'featuredPost', 'categories', 'currentCluster', 'currentCategory', 'search'))->render();
echo "Blog Index (id): " . strlen($htmlId) . " bytes\n";

app()->setLocale('en');
$htmlEn = view('public.blog.index', compact('posts', 'featuredPost', 'categories', 'currentCluster', 'currentCategory', 'search'))->render();
echo "Blog Index (en): " . strlen($htmlEn) . " bytes\n";

// 2. Test Blog Show
echo "--- Testing Blog Show ---\n";
if ($featuredPost) {
    $post = $featuredPost;
    $relatedPosts = $posts->filter(fn($p) => $p->id !== $post->id)->take(3);

    app()->setLocale('id');
    $htmlShowId = view('public.blog.show', compact('post', 'relatedPosts'))->render();
    echo "Blog Show (id): " . strlen($htmlShowId) . " bytes\n";

    app()->setLocale('en');
    $htmlShowEn = view('public.blog.show', compact('post', 'relatedPosts'))->render();
    echo "Blog Show (en): " . strlen($htmlShowEn) . " bytes\n";
} else {
    echo "No blog post found in DB to test show.\n";
}

echo "ALL BLOG TESTS PASSED!\n";
