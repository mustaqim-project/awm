<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$catalog = ['openai' => ['name' => 'OpenAI', 'description' => 'Test', 'models' => []]];
$configured = collect();
$rendered = view('app.ai.providers', compact('catalog', 'configured'))->render();
// Find the line with :class in rendered HTML
preg_match('/<i data-lucide="radio"[^>]+>/', $rendered, $matches);
echo "Rendered HTML snippet:\n";
print_r($matches);
