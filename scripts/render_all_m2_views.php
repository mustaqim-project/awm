<?php

declare(strict_types=1);

putenv('APP_ENV=testing');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE=:memory:');
putenv('CACHE_STORE=array');
putenv('SESSION_DRIVER=array');
$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = ':memory:';
$_ENV['CACHE_STORE'] = 'array';
$_ENV['SESSION_DRIVER'] = 'array';

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run migrations on in-memory sqlite
try {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
} catch (\Throwable $e) {
    echo "Migration note: " . $e->getMessage() . "\n";
}

// Ensure translation lines
app('translator')->addLines(['storefront.tracking' => 'Lacak Pesanan'], 'id');
app('translator')->addLines(['storefront.tracking' => 'Track Order'], 'en');

// Seed sample business for business_landing & discovery
try {
    $business = \App\Models\Business::create([
        'name' => 'Warung Berkah Nusantara',
        'slug' => 'warung-berkah-nusantara',
        'is_active' => true,
        'is_demo' => false,
        'is_test' => false,
        'phone' => '081234567890',
        'email' => 'berkah@example.com',
        'address' => 'Jl. Merdeka No. 45, Jakarta Pusat',
        'description' => 'Warung kuliner dan sembako khas nusantara terpercaya.',
    ]);

    \App\Models\CommerceStoreSetting::create([
        'business_id' => $business->id,
        'is_storefront_enabled' => true,
        'is_discoverable' => true,
        'allow_pickup' => true,
        'allow_delivery' => true,
        'allow_scheduled_order' => false,
        'allow_request_order' => false,
        'min_order_amount' => 5000,
    ]);

    \App\Models\BusinessLandingPage::create([
        'business_id' => $business->id,
        'headline' => 'Warung Berkah Nusantara',
        'subheadline' => 'Menyajikan kuliner khas nusantara higienis dan nikmat.',
        'is_published' => true,
        'show_pos_products' => true,
        'theme_color' => '#007AFF',
        'cta_primary_text' => 'Pesan Sekarang',
        'whatsapp_number' => '081234567890',
    ]);
} catch (\Throwable $e) {
    echo "Seed note: " . $e->getMessage() . "\n";
}

$routes = [
    'landing' => '/',
    'about' => '/about',
    'demo' => '/demo',
    'pricing' => '/pricing',
    'privacy' => '/privacy',
    'security' => '/security',
    'sitemap' => '/sitemap',
    'support' => '/support',
    'terms' => '/terms',
    'business_landing' => '/b/warung-berkah-nusantara',
    'kasir_android' => '/aplikasi-kasir-android',
    'kasir_gratis' => '/aplikasi-kasir-gratis',
    'kasir_offline' => '/aplikasi-kasir-offline',
    'discovery' => '/jelajah',
    'fnb' => '/solutions/fnb',
    'laundry' => '/solutions/laundry',
    'manufacturing' => '/solutions/manufacturing',
    'retail' => '/solutions/retail',
    'services' => '/solutions/services',
    'solutions_show' => '/solusi/kasir-warung',
    'workshop' => '/solutions/workshop',
];

$outputDir = __DIR__ . '/rendered_m2_views';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

echo "Rendering all 21 scoped views...\n";
$rendered = 0;

foreach ($routes as $name => $uri) {
    try {
        $request = \Illuminate\Http\Request::create($uri, 'GET');
        $response = $app->handle($request);
        $status = $response->getStatusCode();
        if ($status !== 200) {
            echo "WARN: [{$name}] URI [{$uri}] returned status {$status}\n";
        }
        $html = $response->getContent();
        $filePath = $outputDir . '/' . $name . '.html';
        file_put_contents($filePath, $html);
        echo "[$status] Rendered {$name} (" . strlen($html) . " bytes) -> {$filePath}\n";
        $rendered++;
    } catch (\Throwable $e) {
        echo "ERROR rendering {$name} at {$uri}: " . $e->getMessage() . "\n";
    }
}

echo "Done rendering {$rendered} views to {$outputDir}\n";
