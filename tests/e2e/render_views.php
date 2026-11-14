<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$type = $argv[1] ?? 'landing';

if ($type === 'landing') {
    app('translator')->addLines(['storefront.tracking' => 'Lacak Pesanan'], 'id');
    app('translator')->addLines(['storefront.tracking' => 'Track Order'], 'en');
    echo view('landing')->render();
} elseif ($type === 'help_chatbot') {
    echo view('layouts.partials.help_chatbot_widget')->render();
} elseif ($type === 'blog_show') {
    // Create a mock post or render blog show with sample content
    $dummyPost = new \App\Models\Post([
        'title' => 'Panduan Lengkap POS & ERP UMKM Indonesia',
        'slug' => 'panduan-lengkap-pos-erp-umkm',
        'content' => '<h2>1. Pendahuluan</h2><p>Mengelola bisnis UMKM membutuhkan efisiensi tinggi.</p><h3>Fitur Utama</h3><p>Integrasi kasir dan pembukuan otomatis.</p><p><a href="#">Pelajari lebih lanjut</a></p><blockquote>Kunci sukses bisnis adalah pencatatan rapi.</blockquote><p>Gunakan perintah <code>npm run build</code> untuk aset.</p><table><thead><tr><th>Fitur</th><th>Status</th></tr></thead><tbody><tr><td>Kasir</td><td>Aktif</td></tr></tbody></table>',
        'excerpt' => 'Panduan lengkap optimasi operasional UMKM Indonesia.',
        'reading_time' => 5,
        'views' => 120,
    ]);
    $dummyPost->published_at = now();
    $dummyPost->created_at = now();
    $dummyPost->updated_at = now();

    echo view('public.blog.show', [
        'post' => $dummyPost,
        'relatedPosts' => collect(),
        'headings' => [
            ['id' => '1-pendahuluan', 'title' => '1. Pendahuluan', 'level' => 2],
            ['id' => 'fitur-utama', 'title' => 'Fitur Utama', 'level' => 3],
        ],
    ])->render();
}
