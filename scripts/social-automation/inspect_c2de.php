<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SocialMediaPost;

$p = SocialMediaPost::find('c2de89a0-1ee2-4886-a0b1-ff36eebff876');
echo json_encode($p ? $p->toArray() : null, JSON_PRETTY_PRINT);
