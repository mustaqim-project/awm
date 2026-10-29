<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AbcActivityController;
use App\Http\Controllers\Api\V1\AccountRecoveryApiController;
use App\Http\Controllers\Api\V1\AiAssistantController;
use App\Http\Controllers\Api\V1\AllocationRuleController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BepController;
use App\Http\Controllers\Api\V1\BomController;
use App\Http\Controllers\Api\V1\Crm\CrmLoyaltyController;
use App\Http\Controllers\Api\V1\Crm\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\Finance\FinanceController;
use App\Http\Controllers\Api\V1\Finance\CashLedgerController;
use App\Http\Controllers\Api\V1\Finance\PaymentSettlementController;
use App\Http\Controllers\Api\V1\Inventory\InventoryController;
use App\Http\Controllers\Api\V1\Inventory\StockOpnameController;
use App\Http\Controllers\Api\V1\Inventory\StockTransferController;
use App\Http\Controllers\Api\V1\Pos\PosAgentApiController;
use App\Http\Controllers\Api\V1\Pos\PosModifierApiController;
use App\Http\Controllers\Api\V1\Pos\PosOrderController;
use App\Http\Controllers\Api\V1\Pos\PosReportController;
use App\Http\Controllers\Api\V1\Pos\PosShiftController;
use App\Http\Controllers\Api\V1\Pos\PosTerminalController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\Purchasing\GoodsReceiptController;
use App\Http\Controllers\Api\V1\Purchasing\PurchaseOrderController;
use App\Http\Controllers\Api\V1\Purchasing\PurchaseReportController;
use App\Http\Controllers\Api\V1\Purchasing\SupplierInvoiceController;
use App\Http\Controllers\Api\V1\Sales\InvoiceController;
use App\Http\Controllers\Api\V1\Sales\QuotationController;
use App\Http\Controllers\Api\V1\Sales\SalesOrderController;
use App\Http\Controllers\Api\V1\BusinessController;
use App\Http\Controllers\Api\V1\BusinessTypeTemplateController;
use App\Http\Controllers\Api\V1\CalculationController;
use App\Http\Controllers\Api\V1\CostDriverController;
use App\Http\Controllers\Api\V1\CostingRunController;
use App\Http\Controllers\Api\V1\CostModelController;
use App\Http\Controllers\Api\V1\CostModelLaborController;
use App\Http\Controllers\Api\V1\CostModelMachineController;
use App\Http\Controllers\Api\V1\CostPoolController;
use App\Http\Controllers\Api\V1\CurrencyController;
use App\Http\Controllers\Api\V1\ExchangeRateController;
use App\Http\Controllers\Api\V1\FeeController;
use App\Http\Controllers\Api\V1\FormulaController;
use App\Http\Controllers\Api\V1\LaborRateController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\MachineController;
use App\Http\Controllers\Api\V1\MaterialCategoryController;
use App\Http\Controllers\Api\V1\MaterialController;
use App\Http\Controllers\Api\V1\MaterialUnitConversionController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\OverheadController;
use App\Http\Controllers\Api\V1\PricingController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductCostVersionController;
use App\Http\Controllers\Api\V1\ProfitabilityController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SimulationController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UnitController;
use App\Http\Controllers\Api\V1\UnitConversionController;
use App\Http\Controllers\Api\V1\VarianceController;
use App\Support\Context;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

// WhatsApp Gateway Incoming Webhook (from Render.com Node.js Baileys)
// NOTE: Only registered under /v1 prefix with high-capacity webhook throttle to eliminate 429 drops.
Route::prefix('v1')->middleware('throttle:600,1')->group(function (): void {
    Route::post('/wa/webhook', [\App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'handle']);
    Route::post('/wa/admin-webhook', [\App\Http\Controllers\Api\V1\WhatsAppWebhookController::class, 'handle']);

    // Official Meta WhatsApp & Page Webhook (Verification & Omnichannel Event Handlers)
    Route::match(['GET', 'POST'], '/wa/meta/webhook', function (\Illuminate\Http\Request $request) {
        $ctrl = app(\App\Http\Controllers\Api\V1\SocialMedia\MetaOmnichannelWebhookController::class);
        return $request->isMethod('GET') ? $ctrl->verify($request) : $ctrl->handle($request);
    });

    // Unified Meta Omnichannel Webhook (Messenger, Instagram DM/Comments, WhatsApp Cloud API, FB Feed/Comments)
    Route::match(['GET', 'POST'], '/meta/omnichannel/webhook', function (\Illuminate\Http\Request $request) {
        $ctrl = app(\App\Http\Controllers\Api\V1\SocialMedia\MetaOmnichannelWebhookController::class);
        return $request->isMethod('GET') ? $ctrl->verify($request) : $ctrl->handle($request);
    });

    // Official Meta Social Media Webhook
    Route::match(['GET', 'POST'], '/social-media/meta/webhook', function (\Illuminate\Http\Request $request) {
        $ctrl = app(\App\Http\Controllers\Api\V1\SocialMedia\MetaSocialMediaWebhookController::class);
        return $request->isMethod('GET') ? $ctrl->verify($request) : $ctrl->handle($request);
    });

    // Official TikTok Open API Webhook (Event notification & Verification Ping)
    Route::match(['GET', 'POST', 'HEAD'], '/social-media/tiktok/webhook', function (\Illuminate\Http\Request $request) {
        $challenge = $request->input('challenge') ?? $request->input('hub_challenge') ?? 'ok';
        return response()->json([
            'status'    => 'success',
            'challenge' => $challenge,
        ], 200);
    });

    // Test Suite Diagnostic and Execution Endpoint (Strictly disabled in production & gated)
    Route::match(['GET', 'POST'], '/social-media/test-suite/status', function (\Illuminate\Http\Request $request) {
        abort_if(app()->isProduction(), 404);

        $token = $request->query('token') ?? $request->input('token');
        $expectedToken = config('services.test_suite.token', env('TEST_SUITE_TOKEN', 'cooca_test_suite_2026'));
        if (! auth('admin')->check() && (empty($token) || ! hash_equals((string) $expectedToken, (string) $token))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($request->boolean('view_log')) {
            $lines = file_exists(storage_path('logs/laravel.log')) ? array_slice(file(storage_path('logs/laravel.log')), -50) : [];
            return response()->json([
                'status' => 'ok',
                'log'    => implode('', $lines),
            ], 200);
        }

        $artisanOutput = null;
        if ($request->boolean('trigger')) {
            $format = $request->input('format', 'all');
            $platform = $request->input('platform', 'all');
            $accountId = $request->input('account_id');
            $params = [
                '--format'   => $format,
                '--platform' => $platform,
            ];
            if (! empty($accountId)) {
                $params['--account-id'] = $accountId;
            }
            \Illuminate\Support\Facades\Artisan::call('social-media:test-upload-suite', $params);
            $artisanOutput = \Illuminate\Support\Facades\Artisan::output();
        }

        $tiktokDiagnostic = null;
        $action = $request->input('action');
        if ($action === 'tiktok_diagnostic') {
            try {
                $acc = \App\Models\SocialMediaAccount::where('platform', 'tiktok')
                    ->when($request->input('account_id'), fn ($q, $id) => $q->where('id', $id))
                    ->where('status', 'active')
                    ->latest()
                    ->first();
                if ($acc) {
                    $provider = app(\App\Domain\SocialMedia\Providers\TikTokProvider::class);
                    $client = $provider->getClient();
                    $token = $provider->checkAndRefreshToken($acc);
                    $creatorInfo = $client->getCreatorInfo($token);
                    $userInfo = $client->getUserInfo($token);
                    $checkPublishId = $request->input('publish_id', 'p_inbox_url~v2.7700303806813390868');
                    $publishStatus = ! empty($checkPublishId) ? $client->getPublishStatus($token, $checkPublishId) : null;
                    $videoList = $client->getVideoList($token, 5);

                    $allAccounts = \App\Models\SocialMediaAccount::where('platform', 'tiktok')->get()->map(fn ($a) => [
                        'id'               => $a->id,
                        'account_name'     => $a->account_name,
                        'username'         => $a->username,
                        'status'           => $a->status,
                        'is_platform'      => (bool) $a->is_platform,
                        'token_expires_at' => $a->token_expires_at?->toIso8601String(),
                        'has_access_token' => ! empty($a->access_token),
                        'has_refresh_token'=> ! empty($a->refresh_token),
                    ]);

                    $tiktokDiagnostic = [
                        'active_account_id'=> $acc->id,
                        'account_name'     => $acc->account_name,
                        'username'         => $acc->username,
                        'token_expires_at' => $acc->token_expires_at?->toIso8601String(),
                        'creator_info'     => $creatorInfo,
                        'user_info'        => $userInfo,
                        'checked_publish_id' => $checkPublishId,
                        'publish_status'   => $publishStatus,
                        'video_list'       => $videoList,
                        'all_accounts'     => $allAccounts,
                        'client_key'       => substr($client->getClientKey(), 0, 8) . '...',
                    ];
                } else {
                    $tiktokDiagnostic = ['error' => 'No active TikTok account found'];
                }
            } catch (\Throwable $e) {
                $tiktokDiagnostic = ['error' => $e->getMessage(), 'trace' => substr($e->getTraceAsString(), 0, 500)];
            }
        } elseif ($action === 'tiktok_direct_publish') {
            try {
                $acc = \App\Models\SocialMediaAccount::where('platform', 'tiktok')
                    ->when($request->input('account_id'), fn ($q, $id) => $q->where('id', $id))
                    ->first();
                if (! $acc) {
                    return response()->json(['error' => 'No TikTok account found'], 404);
                }
                $provider = app(\App\Domain\SocialMedia\Providers\TikTokProvider::class);
                $client = $provider->getClient();
                $token = $provider->checkAndRefreshToken($acc);
                $type = $request->input('type', 'video');
                $privacy = $request->input('privacy', 'SELF_ONLY');
                $postMode = $request->input('post_mode', 'DIRECT_POST');

                $baseUrl = rtrim(config('app.url') ?: 'https://cooca.id', '/');
                if (! str_starts_with($baseUrl, 'https://')) {
                    $baseUrl = preg_replace('#^http://#', 'https://', $baseUrl);
                }

                if ($type === 'video') {
                    $videoUrl = "{$baseUrl}/konten-video.mp4";
                    $caption = "Testing TikTok Video Direct Post [Cooca AI - " . date('d/m H:i') . "]";
                    $init = $client->publishVideo($token, $caption, $videoUrl, [
                        'privacy_level' => $privacy,
                        'post_mode'     => $postMode,
                    ]);
                    $wait = $client->waitForPublishFinished($token, $init['publish_id'] ?? '');
                    return response()->json([
                        'action'      => 'tiktok_direct_publish',
                        'type'        => 'video',
                        'post_mode'   => $postMode,
                        'privacy'     => $privacy,
                        'init_result' => $init,
                        'wait_result' => $wait,
                    ]);
                } else {
                    $photoUrls = ["{$baseUrl}/konten-day-1/F1.jpg"];
                    $caption = "Testing TikTok Photo Direct Post [Cooca AI - " . date('d/m H:i') . "]";
                    $init = $client->publishPhoto($token, $caption, $photoUrls, [
                        'privacy_level' => $privacy,
                        'post_mode'     => $postMode,
                    ]);
                    $wait = $client->waitForPublishFinished($token, $init['publish_id'] ?? '');
                    return response()->json([
                        'action'      => 'tiktok_direct_publish',
                        'type'        => 'photo',
                        'post_mode'   => $postMode,
                        'privacy'     => $privacy,
                        'init_result' => $init,
                        'wait_result' => $wait,
                    ]);
                }
            } catch (\Throwable $e) {
                return response()->json([
                    'action' => 'tiktok_direct_publish',
                    'error'  => $e->getMessage(),
                ], 200);
            }
        } elseif ($action === 'tiktok_deep_test') {
            try {
                $acc = \App\Models\SocialMediaAccount::where('platform', 'tiktok')
                    ->when($request->input('account_id'), fn ($q, $id) => $q->where('id', $id))
                    ->where('status', 'active')
                    ->latest()
                    ->first();
                if (! $acc) {
                    return response()->json(['error' => 'No active TikTok account found'], 404);
                }

                $provider = app(\App\Domain\SocialMedia\Providers\TikTokProvider::class);
                $client = $provider->getClient();
                $token = $provider->checkAndRefreshToken($acc);

                $baseUrl = rtrim(config('app.url') ?: 'https://cooca.id', '/');
                if (! str_starts_with($baseUrl, 'https://')) {
                    $baseUrl = preg_replace('#^http://#', 'https://', $baseUrl);
                }

                $testType = $request->input('test_type', 'all'); // 'video_direct', 'video_inbox', 'photo_direct', 'photo_inbox', or 'all'
                $results = [];

                // 1. Photo Direct (PUBLIC)
                if ($testType === 'all' || $testType === 'photo_direct_public') {
                    $res = \Illuminate\Support\Facades\Http::withToken($token)->post($client->endpoint('post/publish/content/init/'), [
                        'post_info' => [
                            'title' => 'Photo Direct Public Test',
                            'description' => 'Photo Direct Public Test #Cooca',
                            'privacy_level' => 'PUBLIC_TO_EVERYONE',
                        ],
                        'source_info' => [
                            'source' => 'PULL_FROM_URL',
                            'photo_cover_index' => 0,
                            'photo_images' => ["{$baseUrl}/konten-day-1/1.jpg"],
                        ],
                        'post_mode' => 'DIRECT_POST',
                        'media_type' => 'PHOTO',
                    ]);
                    $results['photo_direct_public'] = [
                        'status' => $res->status(),
                        'body' => $res->json() ?: $res->body(),
                    ];
                }

                // 2. Photo Direct (SELF_ONLY)
                if ($testType === 'all' || $testType === 'photo_direct_private') {
                    $res = \Illuminate\Support\Facades\Http::withToken($token)->post($client->endpoint('post/publish/content/init/'), [
                        'post_info' => [
                            'title' => 'Photo Direct Private Test',
                            'description' => 'Photo Direct Private Test #Cooca',
                            'privacy_level' => 'SELF_ONLY',
                        ],
                        'source_info' => [
                            'source' => 'PULL_FROM_URL',
                            'photo_cover_index' => 0,
                            'photo_images' => ["{$baseUrl}/konten-day-1/1.jpg"],
                        ],
                        'post_mode' => 'DIRECT_POST',
                        'media_type' => 'PHOTO',
                    ]);
                    $results['photo_direct_private'] = [
                        'status' => $res->status(),
                        'body' => $res->json() ?: $res->body(),
                    ];
                }

                // 3. Photo Inbox (MEDIA_UPLOAD)
                if ($testType === 'all' || $testType === 'photo_inbox') {
                    $res = \Illuminate\Support\Facades\Http::withToken($token)->post($client->endpoint('post/publish/content/init/'), [
                        'post_info' => [
                            'title' => 'Photo Inbox Mode Test',
                            'description' => 'Photo Inbox Mode Test #Cooca',
                        ],
                        'source_info' => [
                            'source' => 'PULL_FROM_URL',
                            'photo_cover_index' => 0,
                            'photo_images' => ["{$baseUrl}/konten-day-1/1.jpg"],
                        ],
                        'post_mode' => 'MEDIA_UPLOAD',
                        'media_type' => 'PHOTO',
                    ]);
                    $results['photo_inbox'] = [
                        'status' => $res->status(),
                        'body' => $res->json() ?: $res->body(),
                    ];
                }

                // 4. Video Direct (PUBLIC)
                if ($testType === 'all' || $testType === 'video_direct_public') {
                    $res = \Illuminate\Support\Facades\Http::withToken($token)->post($client->endpoint('post/publish/video/init/'), [
                        'post_info' => [
                            'title' => 'Video Direct Public Test #Cooca',
                            'privacy_level' => 'PUBLIC_TO_EVERYONE',
                        ],
                        'source_info' => [
                            'source' => 'PULL_FROM_URL',
                            'video_url' => "{$baseUrl}/konten-video.mp4",
                        ],
                    ]);
                    $results['video_direct_public'] = [
                        'status' => $res->status(),
                        'body' => $res->json() ?: $res->body(),
                    ];
                }

                // 5. Video Direct (SELF_ONLY)
                if ($testType === 'all' || $testType === 'video_direct_private') {
                    $res = \Illuminate\Support\Facades\Http::withToken($token)->post($client->endpoint('post/publish/video/init/'), [
                        'post_info' => [
                            'title' => 'Video Direct Private Test #Cooca',
                            'privacy_level' => 'SELF_ONLY',
                        ],
                        'source_info' => [
                            'source' => 'PULL_FROM_URL',
                            'video_url' => "{$baseUrl}/konten-video.mp4",
                        ],
                    ]);
                    $results['video_direct_private'] = [
                        'status' => $res->status(),
                        'body' => $res->json() ?: $res->body(),
                    ];
                }

                // 6. Video Inbox (MEDIA_UPLOAD)
                if ($testType === 'all' || $testType === 'video_inbox') {
                    $res = \Illuminate\Support\Facades\Http::withToken($token)->post($client->endpoint('post/publish/inbox/video/init/'), [
                        'source_info' => [
                            'source' => 'PULL_FROM_URL',
                            'video_url' => "{$baseUrl}/konten-video.mp4",
                        ],
                    ]);
                    $results['video_inbox'] = [
                        'status' => $res->status(),
                        'body' => $res->json() ?: $res->body(),
                    ];
                }

                return response()->json([
                    'action' => 'tiktok_deep_test',
                    'account' => [
                        'id' => $acc->id,
                        'name' => $acc->account_name,
                        'username' => $acc->username,
                    ],
                    'results' => $results,
                ]);
            } catch (\Throwable $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } elseif ($action === 'inspect_tiktok_raw') {
            $row = \Illuminate\Support\Facades\DB::table('social_media_accounts')->where('platform', 'tiktok')->where('status', 'active')->first();
            $rawAt = (string) ($row->access_token ?? '');
            $rawRt = (string) ($row->refresh_token ?? '');
            $decryptedAt = null;
            $decryptError = null;
            try {
                $decryptedAt = \Illuminate\Support\Facades\Crypt::decryptString($rawAt);
            } catch (\Throwable $e) {
                $decryptError = $e->getMessage();
            }

            $key = (string) \App\Models\SystemSetting::get('tiktok_client_key', '');
            $secret = (string) \App\Models\SystemSetting::get('tiktok_client_secret', '');
            $apiUrl = (string) \App\Models\SystemSetting::get('tiktok_api_url', '');
            $authUrl = (string) \App\Models\SystemSetting::get('tiktok_auth_url', '');

            if (empty($apiUrl)) {
                \App\Models\SystemSetting::set('tiktok_api_url', 'https://open.tiktokapis.com/v2/', 'social_media');
                $apiUrl = 'https://open.tiktokapis.com/v2/';
            }
            if (empty($authUrl)) {
                \App\Models\SystemSetting::set('tiktok_auth_url', 'https://www.tiktok.com/v2/auth/authorize/', 'social_media');
                $authUrl = 'https://www.tiktok.com/v2/auth/authorize/';
            }

            return response()->json([
                'id'                  => $row->id ?? null,
                'account_name'        => $row->account_name ?? null,
                'status'              => $row->status ?? null,
                'is_platform'         => (bool) ($row->is_platform ?? false),
                'token_expires_at'    => $row->token_expires_at ?? null,
                'raw_at_len'          => strlen($rawAt),
                'decrypt_error'       => $decryptError,
                'decrypted_at_prefix' => $decryptedAt ? substr($decryptedAt, 0, 10) : null,
                'system_settings'     => [
                    'tiktok_client_key'          => $key,
                    'has_tiktok_client_secret'   => ! empty($secret),
                    'tiktok_client_secret_len'   => strlen($secret),
                    'tiktok_client_secret_start' => substr($secret, 0, 4) . '...',
                    'tiktok_api_url'             => $apiUrl,
                    'tiktok_auth_url'            => $authUrl,
                ],
            ]);
        } elseif ($action === 'inspect_platform_posts') {
            $posts = \App\Models\SocialMediaPost::where('is_platform', true)
                ->with(['targets'])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($p) => [
                    'id'               => $p->id,
                    'status'           => $p->status,
                    'content'          => substr($p->content, 0, 80) . '...',
                    'platform_post_id' => $p->platform_post_id,
                    'created_at'       => $p->created_at?->toIso8601String(),
                    'targets'          => $p->targets->map(fn ($t) => [
                        'channel'          => $t->channel,
                        'status'           => $t->status,
                        'platform_post_id' => $t->platform_post_id,
                        'account_id'       => $t->social_media_account_id,
                    ]),
                ]);
            return response()->json(['platform_posts' => $posts]);
        } elseif ($action === 'admin_publish_tiktok') {
            try {
                // Deactivate known old expired TikTok account so it never gets picked
                \Illuminate\Support\Facades\DB::table('social_media_accounts')
                    ->where('id', '022bbb65-b8ee-4688-a3cc-3893e03b9270')
                    ->update(['status' => 'inactive', 'is_platform' => false]);

                // 1. Ensure active TikTok account is marked as platform account
                $acc = \App\Models\SocialMediaAccount::where('platform', 'tiktok')
                    ->when($request->input('account_id'), fn ($q, $id) => $q->where('id', $id))
                    ->where('status', 'active')
                    ->latest()
                    ->first();
                if (! $acc) {
                    return response()->json(['error' => 'No active TikTok account found'], 404);
                }

                \Illuminate\Support\Facades\DB::table('social_media_accounts')
                    ->where('id', $acc->id)
                    ->update(['is_platform' => true, 'status' => 'active']);

                // 2. Resolve Admin user
                $admin = \App\Models\Admin::first();
                if (! $admin) {
                    $admin = \App\Models\Admin::create([
                        'name'      => 'Superadmin Cooca',
                        'email'     => 'admin@cooca.id',
                        'password'  => bcrypt('AdminCooca2026!'),
                        'role'      => 'superadmin',
                        'is_active' => true,
                    ]);
                }

                // 3. Prepare parameters
                $format = $request->input('format', 'photo');
                $baseUrl = rtrim(config('app.url') ?: 'https://cooca.id', '/');
                if (! str_starts_with($baseUrl, 'https://')) {
                    $baseUrl = preg_replace('#^http://#', 'https://', $baseUrl);
                }

                $caption = $request->input('caption') ?: ("Postingan Resmi Admin COOCA di TikTok [Cooca AI - " . date('d/m/Y H:i:s') . "] #CoocaID #UMKMIndonesia #TipsBisnis");

                if ($format === 'carousel') {
                    $mediaType = 'carousel';
                    $mediaUrls = [
                        "{$baseUrl}/konten-day-1/1.jpg",
                        "{$baseUrl}/konten-day-1/2.jpg",
                        "{$baseUrl}/konten-day-1/3.jpg",
                        "{$baseUrl}/konten-day-1/4.jpg",
                        "{$baseUrl}/konten-day-1/5.jpg",
                        "{$baseUrl}/konten-day-1/6.jpg",
                    ];
                } elseif ($format === 'video' || $format === 'reels') {
                    $mediaType = 'video';
                    $mediaUrls = [
                        "{$baseUrl}/konten-video.mp4",
                    ];
                } else {
                    $mediaType = 'photo';
                    $mediaUrls = [
                        "{$baseUrl}/konten-day-1/F1.jpg",
                    ];
                }

                $postData = [
                    'platforms'  => ['tiktok'],
                    'platform'   => 'tiktok',
                    'content'    => $caption,
                    'media_type' => $mediaType,
                    'media_urls' => $mediaUrls,
                ];

                /** @var \App\Domain\SocialMedia\AdminSocialMediaService $adminService */
                $adminService = app(\App\Domain\SocialMedia\AdminSocialMediaService::class);
                $post = $adminService->createAndPublishPlatformPost($admin, $postData);
                $target = $post->targets()->where('channel', 'tiktok')->first();

                return response()->json([
                    'action'            => 'admin_publish_tiktok',
                    'success'           => $target?->status === 'published' || $post->status === 'published',
                    'format'            => $format,
                    'post_id'           => $post->id,
                    'post_status'       => $post->status,
                    'target_id'         => $target?->id,
                    'target_status'     => $target?->status,
                    'platform_post_id'  => $target?->platform_post_id ?: $post->platform_post_id,
                    'error_message'     => $target?->error_message ?: $post->error_message,
                    'account_used'      => [
                        'id'           => $acc->id,
                        'account_name' => $acc->account_name,
                        'username'     => $acc->username,
                        'is_platform'  => (bool) $acc->fresh()->is_platform,
                    ],
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'action'  => 'admin_publish_tiktok',
                    'success' => false,
                    'error'   => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ], 200);
            }
        } elseif ($action === 'linkedin_diagnostic') {
            try {
                $acc = \App\Models\SocialMediaAccount::where('platform', 'linkedin')->first();
                if (! $acc) {
                    return response()->json(['error' => 'No LinkedIn account found'], 200);
                }
                $client = app(\App\Domain\SocialMedia\Clients\LinkedInClient::class);
                $profile = $client->getProfile($acc->access_token);
                $authorUrn = str_starts_with((string) $acc->account_id, 'urn:li:')
                    ? $acc->account_id
                    : "urn:li:person:{$acc->account_id}";

                // Test registerUpload for image
                $regImgResponse = \Illuminate\Support\Facades\Http::withToken($acc->access_token)
                    ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
                    ->timeout(20)
                    ->post('https://api.linkedin.com/v2/assets?action=registerUpload', [
                        'registerUploadRequest' => [
                            'recipes' => ['urn:li:digitalmediaRecipe:feedshare-image'],
                            'owner'   => $authorUrn,
                            'serviceRelationships' => [
                                [
                                    'relationshipType' => 'OWNER',
                                    'identifier'       => 'urn:li:userGeneratedContent',
                                ],
                            ],
                            'supportedUploadMechanism' => ['SYNCHRONOUS_UPLOAD'],
                        ],
                    ]);

                // Test registerUpload for video
                $regVidResponse = \Illuminate\Support\Facades\Http::withToken($acc->access_token)
                    ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
                    ->timeout(20)
                    ->post('https://api.linkedin.com/v2/assets?action=registerUpload', [
                        'registerUploadRequest' => [
                            'recipes' => ['urn:li:digitalmediaRecipe:feedshare-video'],
                            'owner'   => $authorUrn,
                            'serviceRelationships' => [
                                [
                                    'relationshipType' => 'OWNER',
                                    'identifier'       => 'urn:li:userGeneratedContent',
                                ],
                            ],
                            'supportedUploadMechanism' => ['SYNCHRONOUS_UPLOAD'],
                        ],
                    ]);

                $imgVal = $regImgResponse->json('value') ?? [];
                $vidVal = $regVidResponse->json('value') ?? [];

                return response()->json([
                    'status'        => 'ok',
                    'account_id'    => $acc->id,
                    'account_name'  => $acc->account_name,
                    'author_urn'    => $authorUrn,
                    'img_status'    => $regImgResponse->status(),
                    'img_keys'      => array_keys($imgVal),
                    'img_asset'     => $imgVal['asset'] ?? null,
                    'img_artifact'  => $imgVal['mediaArtifact'] ?? null,
                    'img_upload_url'=> ! empty(data_get($imgVal, 'uploadMechanism.com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest.uploadUrl')),
                    'vid_status'    => $regVidResponse->status(),
                    'vid_keys'      => array_keys($vidVal),
                    'vid_asset'     => $vidVal['asset'] ?? null,
                    'vid_artifact'  => $vidVal['mediaArtifact'] ?? null,
                    'vid_upload_url'=> ! empty(data_get($vidVal, 'uploadMechanism.com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest.uploadUrl')),
                    'vid_error'     => $regVidResponse->failed() ? ($regVidResponse->json() ?: $regVidResponse->body()) : null,
                ], 200);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'error',
                    'error'  => $e->getMessage(),
                ], 200);
            }
        } elseif ($action === 'linkedin_direct_publish') {
            try {
                $acc = \App\Models\SocialMediaAccount::where('platform', 'linkedin')->first();
                if (! $acc) {
                    return response()->json(['error' => 'No LinkedIn account found'], 200);
                }
                $client = app(\App\Domain\SocialMedia\Clients\LinkedInClient::class);
                $type = $request->input('type', 'photo');

                $baseUrl = rtrim(config('app.url') ?: 'https://cooca.id', '/');
                if (! str_starts_with($baseUrl, 'https://')) {
                    $baseUrl = preg_replace('#^http://#', 'https://', $baseUrl);
                }

                $timeTag = date('d/m H:i');
                if ($type === 'video') {
                    $caption = "Testing LinkedIn Video Upload (COOCA Video) [Ref: {$timeTag}]\n\nSimak kemudahan kelola bisnis dan ringkasan otomatis bersama COOCA AI.\n\n#CoocaAI #BisnisUMKM #KelolaBisnis";
                    $mediaItems = [
                        [
                            'media_url'  => "{$baseUrl}/konten-video.mp4",
                            'media_type' => 'video',
                        ],
                    ];
                } elseif ($type === 'carousel') {
                    $caption = "Testing LinkedIn Carousel Upload (Day 1 - 6 Slides) [Ref: {$timeTag}]\n\nPanduan praktis mengelola operasional bisnis Anda langkah demi langkah bersama COOCA.\n\nGeser ke samping untuk melihat detailnya 👉\n\n#CoocaID #TipsBisnis #UMKMIndonesia";
                    $mediaItems = [
                        ['media_url' => "{$baseUrl}/konten-day-1/1.jpg", 'media_type' => 'image'],
                        ['media_url' => "{$baseUrl}/konten-day-1/2.jpg", 'media_type' => 'image'],
                        ['media_url' => "{$baseUrl}/konten-day-1/3.jpg", 'media_type' => 'image'],
                        ['media_url' => "{$baseUrl}/konten-day-1/4.jpg", 'media_type' => 'image'],
                        ['media_url' => "{$baseUrl}/konten-day-1/5.jpg", 'media_type' => 'image'],
                        ['media_url' => "{$baseUrl}/konten-day-1/6.jpg", 'media_type' => 'image'],
                    ];
                } else {
                    $caption = "Testing LinkedIn Photo Upload (COOCA AI Banner) [Ref: {$timeTag}]\n\nMau Tahu Kondisi Bisnis? Tanya COOCA AI.\n\nDari penjualan, stok, hingga arus kas. Dapatkan ringkasan bisnis lewat chat.\n\n#CoocaAI #BisnisUMKM #KelolaBisnis";
                    $mediaItems = [
                        [
                            'media_url'  => "{$baseUrl}/konten-day-1/F1.jpg",
                            'media_type' => 'image',
                        ],
                    ];
                }

                $result = $client->publishPost(
                    (string) $acc->account_id,
                    $acc->access_token,
                    $caption,
                    $mediaItems
                );

                return response()->json([
                    'status'        => 'ok',
                    'action'        => 'linkedin_direct_publish',
                    'type'          => $type,
                    'account_name'  => $acc->account_name,
                    'media_count'   => count($mediaItems),
                    'publish_result'=> $result,
                ], 200);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'error',
                    'error'  => $e->getMessage(),
                ], 200);
            }
        }

        $summaryFile = storage_path('framework/test_upload_suite_result.json');
        $logFile = storage_path('logs/social_media_test_suite.log');
        $retryLog = storage_path('logs/tiktok_retry.log');
        $tiktokV4Log = storage_path('logs/tiktok_suite_v4.log');
        $linkedinLog = storage_path('logs/linkedin_suite.log');
        $gitPullLog = storage_path('logs/git_pull.log');

        $summary = file_exists($summaryFile) ? json_decode((string) file_get_contents($summaryFile), true) : null;
        $recentLog = file_exists($logFile) ? mb_substr((string) file_get_contents($logFile), -4000) : null;
        $recentRetryLog = file_exists($retryLog) ? mb_substr((string) file_get_contents($retryLog), -4000) : null;
        $recentTikTokV4Log = file_exists($tiktokV4Log) ? mb_substr((string) file_get_contents($tiktokV4Log), -4000) : null;
        $recentLinkedInLog = file_exists($linkedinLog) ? mb_substr((string) file_get_contents($linkedinLog), -4000) : null;
        $recentGitPullLog = file_exists($gitPullLog) ? mb_substr((string) file_get_contents($gitPullLog), -2000) : null;
        $recentLaravelLog = file_exists(storage_path('logs/laravel.log')) ? mb_substr((string) file_get_contents(storage_path('logs/laravel.log')), -4000) : null;

        return response()->json([
            'status'               => 'ok',
            'artisan_output'       => $artisanOutput,
            'tiktok_diagnostic'    => $tiktokDiagnostic,
            'summary'              => $summary,
            'recent_linkedin_log'  => $recentLinkedInLog,
            'recent_tiktok_v4_log' => $recentTikTokV4Log,
            'recent_git_pull_log'  => $recentGitPullLog,
            'recent_log'           => $recentLog,
            'recent_retry_log'     => $recentRetryLog,
            'recent_laravel_log'   => $recentLaravelLog,
            'latest_posts'         => \App\Models\SocialMediaPost::with('targets')->latest()->take(6)->get(),
        ]);
    });

    // TriPay Payment Gateway Incoming Webhook (Model B) - supports GET/HEAD ping and POST callback
    Route::match(['GET', 'POST', 'HEAD'], '/payments/tripay/callback', [\App\Http\Controllers\Api\V1\Payment\TripayCallbackController::class, 'handle']);
    Route::match(['GET', 'POST', 'HEAD'], '/tripay/callback', [\App\Http\Controllers\Api\V1\Payment\TripayCallbackController::class, 'handle']);
});

// Webhook Aliases without v1 prefix
Route::middleware('throttle:600,1')->group(function (): void {
    Route::match(['GET', 'POST'], '/wa/meta/webhook', function (\Illuminate\Http\Request $request) {
        $ctrl = app(\App\Http\Controllers\Api\V1\SocialMedia\MetaOmnichannelWebhookController::class);
        return $request->isMethod('GET') ? $ctrl->verify($request) : $ctrl->handle($request);
    });
    Route::match(['GET', 'POST'], '/meta/omnichannel/webhook', function (\Illuminate\Http\Request $request) {
        $ctrl = app(\App\Http\Controllers\Api\V1\SocialMedia\MetaOmnichannelWebhookController::class);
        return $request->isMethod('GET') ? $ctrl->verify($request) : $ctrl->handle($request);
    });
    Route::match(['GET', 'POST'], '/social-media/meta/webhook', function (\Illuminate\Http\Request $request) {
        $ctrl = app(\App\Http\Controllers\Api\V1\SocialMedia\MetaSocialMediaWebhookController::class);
        return $request->isMethod('GET') ? $ctrl->verify($request) : $ctrl->handle($request);
    });
    Route::match(['GET', 'POST', 'HEAD'], '/payments/tripay/callback', [\App\Http\Controllers\Api\V1\Payment\TripayCallbackController::class, 'handle']);
    Route::match(['GET', 'POST', 'HEAD'], '/tripay/callback', [\App\Http\Controllers\Api\V1\Payment\TripayCallbackController::class, 'handle']);
});


Route::prefix('v1')->group(function (): void {

    // Health Check & API Docs
    Route::get('/health', function (): JsonResponse {
        return response()->json([
            'status' => 'ok',
            'service' => 'Cooca Engine',
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ]);
    });

    Route::get('/docs', [ReportController::class, 'docs']);

    // Model Context Protocol (MCP) Multi-Tenant Universal AI Gateway
    // Supports: Streamable HTTP (POST JSON-RPC), Server-Sent Events (GET SSE), OAuth 2.0 / OIDC, and OpenAPI 3.1
    Route::prefix('mcp')->group(function (): void {
        // RFC 8414 & OIDC Discovery inside /api/v1/mcp
        Route::match(['GET', 'OPTIONS', 'HEAD'], '/.well-known/oauth-authorization-server', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'wellKnownOAuth']);
        Route::match(['GET', 'OPTIONS', 'HEAD'], '/.well-known/openid-configuration', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'wellKnownOpenId']);

        // OAuth 2.0 & OIDC Endpoints for ChatGPT Apps SDK / Connectors
        Route::match(['GET', 'OPTIONS', 'HEAD'], '/oauth/authorize', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'authorize']);
        Route::post('/oauth/authorize', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'authorize']);
        Route::match(['POST', 'OPTIONS'], '/oauth/token', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'token']);
        Route::match(['POST', 'OPTIONS'], '/oauth/register', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'register']);
        Route::match(['GET', 'OPTIONS', 'HEAD'], '/oauth/userinfo', [\App\Http\Controllers\Api\V1\Mcp\McpOAuthController::class, 'userinfo']);

        // Core MCP Protocol Endpoints (Protected by mcp.auth)
        Route::middleware(['mcp.auth', 'throttle:120,1'])->group(function (): void {
            Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'handleEndpoint']);
            Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '/', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'handleEndpoint']);
            Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '/sse', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'handleEndpoint']);
            Route::match(['GET', 'POST', 'OPTIONS', 'HEAD'], '/message', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'message']);
            Route::match(['GET', 'OPTIONS', 'HEAD'], '/openapi.json', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'openapi']);
            Route::match(['POST', 'OPTIONS'], '/tools/{tool}/execute', [\App\Http\Controllers\Api\V1\Mcp\McpSseController::class, 'executeDirect']);
        });
    });

    // Currencies (Global List)
    Route::get('/currencies', [CurrencyController::class, 'index']);

    // Units Public/Available Listing & Live Converter
    Route::get('/units', [UnitController::class, 'index']);
    Route::post('/units/convert', [UnitController::class, 'convert']);

    // Business Type Templates (Public list)
    Route::get('/business-type-templates', [BusinessTypeTemplateController::class, 'index']);
    Route::get('/business-type-templates/{businessTypeTemplate}', [BusinessTypeTemplateController::class, 'show']);

    // Formula Engine Utilities (Public preview / dry-run)
    Route::post('/formulas/evaluate', [FormulaController::class, 'evaluate']);
    Route::post('/formulas/validate', [FormulaController::class, 'validateFormula']);

    // Currency Conversion Live
    Route::post('/exchange-rates/convert', [ExchangeRateController::class, 'convert']);

    // ──────────────────────────────────────────────────────────────────
    // CUSTOMER MARKETPLACE API – Public Discovery & Catalog
    // ──────────────────────────────────────────────────────────────────
    Route::prefix('marketplace')->middleware('throttle:120,1')->group(function (): void {
        Route::get('/home', [\App\Http\Controllers\Api\V1\Customer\MarketplaceCatalogApiController::class, 'homeFeed']);
        Route::get('/categories', [\App\Http\Controllers\Api\V1\Customer\MarketplaceCatalogApiController::class, 'categories']);
        Route::get('/products', [\App\Http\Controllers\Api\V1\Customer\MarketplaceCatalogApiController::class, 'searchProducts']);
        Route::get('/products/{slug}', [\App\Http\Controllers\Api\V1\Customer\MarketplaceCatalogApiController::class, 'productDetail']);
        Route::get('/stores/{slug}', [\App\Http\Controllers\Api\V1\Customer\MarketplaceCatalogApiController::class, 'storeProfile']);
    });

    // ──────────────────────────────────────────────────────────────────
    // CUSTOMER MARKETPLACE API – Customer Authentication
    // ──────────────────────────────────────────────────────────────────
    Route::prefix('customer/auth')->middleware('throttle:60,1')->group(function (): void {
        Route::post('/register', [\App\Http\Controllers\Api\V1\Customer\CustomerAuthApiController::class, 'register']);
        Route::post('/login', [\App\Http\Controllers\Api\V1\Customer\CustomerAuthApiController::class, 'login']);
        Route::post('/google', [\App\Http\Controllers\Api\V1\Customer\CustomerAuthApiController::class, 'googleLogin']);
        Route::post('/send-otp', [\App\Http\Controllers\Api\V1\Customer\CustomerAuthApiController::class, 'sendOtp']);
        Route::post('/verify-otp', [\App\Http\Controllers\Api\V1\Customer\CustomerAuthApiController::class, 'verifyOtp']);
    });

    // ──────────────────────────────────────────────────────────────────
    // CUSTOMER MARKETPLACE API – Authenticated Customer Portal
    // ──────────────────────────────────────────────────────────────────
    Route::prefix('customer')->middleware(['auth:sanctum', 'throttle:180,1'])->group(function (): void {
        Route::get('/me', [\App\Http\Controllers\Api\V1\Customer\CustomerAuthApiController::class, 'me']);
        Route::post('/logout', [\App\Http\Controllers\Api\V1\Customer\CustomerAuthApiController::class, 'logout']);
        // Aliases for Mobile Customer Portal
        Route::get('/auth/profile', [\App\Http\Controllers\Api\V1\Customer\CustomerAuthApiController::class, 'me']);
        Route::post('/auth/logout', [\App\Http\Controllers\Api\V1\Customer\CustomerAuthApiController::class, 'logout']);

        // Address Book
        Route::get('/addresses', [\App\Http\Controllers\Api\V1\Customer\CustomerAddressApiController::class, 'index']);
        Route::post('/addresses', [\App\Http\Controllers\Api\V1\Customer\CustomerAddressApiController::class, 'store']);
        Route::put('/addresses/{id}', [\App\Http\Controllers\Api\V1\Customer\CustomerAddressApiController::class, 'update']);
        Route::delete('/addresses/{id}', [\App\Http\Controllers\Api\V1\Customer\CustomerAddressApiController::class, 'destroy']);
        Route::post('/addresses/{id}/set-default', [\App\Http\Controllers\Api\V1\Customer\CustomerAddressApiController::class, 'setDefault']);

        // Multi-Merchant Cart
        Route::get('/cart', [\App\Http\Controllers\Api\V1\Customer\CustomerCartApiController::class, 'index']);
        Route::post('/cart/{slug}', [\App\Http\Controllers\Api\V1\Customer\CustomerCartApiController::class, 'addItem']);
        Route::put('/cart/{slug}/{itemId}', [\App\Http\Controllers\Api\V1\Customer\CustomerCartApiController::class, 'updateItem']);
        Route::delete('/cart/{slug}/{itemId}', [\App\Http\Controllers\Api\V1\Customer\CustomerCartApiController::class, 'removeItem']);

        // Checkout & Orders
        Route::post('/checkout/rates', [\App\Http\Controllers\Api\V1\Customer\CustomerCheckoutApiController::class, 'calculateRates']);
        Route::post('/checkout', [\App\Http\Controllers\Api\V1\Customer\CustomerCheckoutApiController::class, 'submitCheckout']);
        Route::get('/orders', [\App\Http\Controllers\Api\V1\Customer\CustomerCheckoutApiController::class, 'orders']);
        Route::get('/orders/{id}', [\App\Http\Controllers\Api\V1\Customer\CustomerCheckoutApiController::class, 'orderDetail']);
        Route::post('/orders/{id}/upload-proof', [\App\Http\Controllers\Api\V1\Customer\CustomerCheckoutApiController::class, 'uploadProof']);
        Route::post('/orders/{id}/cancel', [\App\Http\Controllers\Api\V1\Customer\CustomerCheckoutApiController::class, 'cancelOrder']);
        Route::post('/orders/{id}/complete', [\App\Http\Controllers\Api\V1\Customer\CustomerCheckoutApiController::class, 'completeOrder']);
        Route::post('/orders/{id}/review', [\App\Http\Controllers\Api\V1\Customer\CustomerCheckoutApiController::class, 'storeReview']);

        // Wishlists
        Route::get('/wishlist', [\App\Http\Controllers\Api\V1\Customer\CustomerWishlistApiController::class, 'index']);
        Route::post('/wishlist/toggle', [\App\Http\Controllers\Api\V1\Customer\CustomerWishlistApiController::class, 'toggle']);

        // Mobile Device Tokens (FCM Push Notifications for Customer)
        Route::post('/devices/fcm-token', [\App\Http\Controllers\Api\V1\Device\MobileDeviceTokenApiController::class, 'registerCustomerToken']);
        Route::delete('/devices/fcm-token', [\App\Http\Controllers\Api\V1\Device\MobileDeviceTokenApiController::class, 'unregisterCustomerToken']);
    });

    // Authentication (Public with 60 req/min rate limit, login & forgot-password strictly throttled to 10/min)
    Route::middleware('throttle:60,1')->prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('/google', [AuthController::class, 'googleLogin']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:10,1');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:10,1');
    });

    // Account Recovery (Public)
    Route::prefix('account-recovery')->middleware('throttle:10,1')->group(function (): void {
        Route::post('/', [AccountRecoveryApiController::class, 'store']);
        Route::get('/status/{ticket}', [AccountRecoveryApiController::class, 'status']);
    });

    // Authenticated User Routes (with 300 req/min rate limit)
    Route::middleware(['auth:sanctum', 'throttle:300,1'])->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        // Mobile Device Tokens (FCM Push Notifications for B2B Staff)
        Route::post('/devices/fcm-token', [\App\Http\Controllers\Api\V1\Device\MobileDeviceTokenApiController::class, 'registerB2bToken']);
        Route::delete('/devices/fcm-token', [\App\Http\Controllers\Api\V1\Device\MobileDeviceTokenApiController::class, 'unregisterB2bToken']);

        // User & Business Management
        Route::get('/me/businesses', [BusinessController::class, 'index']);
        Route::post('/me/active-business', [BusinessController::class, 'setActive']);
        Route::get('/businesses', [BusinessController::class, 'index']);
        Route::post('/businesses/switch', [BusinessController::class, 'setActive']);
        Route::post('/businesses', [BusinessController::class, 'store']);
        Route::get('/businesses/{business:slug}', [BusinessController::class, 'show']);
        Route::post('/businesses/{business:slug}/apply-template', [BusinessTypeTemplateController::class, 'apply']);
        Route::post('/profile/complete', [ProfileController::class, 'completeProfile']);

        // Business Members Management
        Route::get('/businesses/{business:slug}/members', [MemberController::class, 'index'])->middleware('require.permission:users.view');
        Route::post('/businesses/{business:slug}/members', [MemberController::class, 'store'])
            ->middleware('business.active', 'require.role:owner,admin');
        Route::patch('/businesses/{business:slug}/members/{member}', [MemberController::class, 'update'])
            ->middleware('business.active', 'require.role:owner,admin');
        Route::delete('/businesses/{business:slug}/members/{member}', [MemberController::class, 'destroy'])
            ->middleware('business.active', 'require.role:owner,admin');

        // Tenant-Aware Routes (Requires Active Business Context)
        Route::middleware('business.active')->group(function (): void {
            Route::get('/context/current', function (): JsonResponse {
                $business = Context::requireBusiness();

                return response()->json([
                    'business' => [
                        'id' => $business->id,
                        'name' => $business->name,
                        'slug' => $business->slug,
                    ],
                    'role' => Context::role(),
                    'is_owner' => Context::isOwner(),
                    'permissions' => Context::permissions(),
                    'can_view_margin' => Context::hasPermission('costing.view_margin'),
                ]);
            });

            // Convenience Members endpoints for active business
            Route::get('/members', [MemberController::class, 'index'])->middleware('require.permission:users.view');
            Route::post('/members', [MemberController::class, 'store'])
                ->middleware('require.role:owner,admin');
            Route::patch('/members/{member}', [MemberController::class, 'update'])
                ->middleware('require.role:owner,admin');
            Route::delete('/members/{member}', [MemberController::class, 'destroy'])
                ->middleware('require.role:owner,admin');

            // Tenant Custom Units & Conversions
            Route::post('/units', [UnitController::class, 'store'])->middleware('require.permission:master_data.units.manage');
            Route::get('/units/{unit}', [UnitController::class, 'show'])->middleware('require.permission:master_data.units.view');
            Route::put('/units/{unit}', [UnitController::class, 'update'])->middleware('require.permission:master_data.units.manage');
            Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->middleware('require.permission:master_data.units.manage');

            Route::get('/unit-conversions', [UnitConversionController::class, 'index'])->middleware('require.permission:master_data.units.view');
            Route::post('/unit-conversions', [UnitConversionController::class, 'store'])->middleware('require.permission:master_data.units.manage');
            Route::delete('/unit-conversions/{unitConversion}', [UnitConversionController::class, 'destroy'])->middleware('require.permission:master_data.units.manage');

            Route::get('/material-unit-conversions', [MaterialUnitConversionController::class, 'index'])->middleware('require.permission:master_data.units.view');
            Route::post('/material-unit-conversions', [MaterialUnitConversionController::class, 'store'])->middleware('require.permission:master_data.units.manage');
            Route::delete('/material-unit-conversions/{materialUnitConversion}', [MaterialUnitConversionController::class, 'destroy'])->middleware('require.permission:master_data.units.manage');

            // Multi-Location & Multi-Currency per Business
            Route::get('/locations', [LocationController::class, 'index'])->middleware('require.permission:warehouse.view');
            Route::post('/locations', [LocationController::class, 'store'])->middleware('require.permission:warehouse.manage');
            Route::get('/locations/{location:slug}', [LocationController::class, 'show'])->middleware('require.permission:warehouse.view');
            Route::put('/locations/{location:slug}', [LocationController::class, 'update'])->middleware('require.permission:warehouse.manage');
            Route::delete('/locations/{location:slug}', [LocationController::class, 'destroy'])->middleware('require.permission:warehouse.manage');

            Route::get('/exchange-rates', [ExchangeRateController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/exchange-rates', [ExchangeRateController::class, 'store'])->middleware('require.permission:costing.manage');

            // Suppliers CRUD
            Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('require.permission:master_data.suppliers.view');
            Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('require.permission:master_data.suppliers.manage');
            Route::get('/suppliers/{supplier:slug}', [SupplierController::class, 'show'])->middleware('require.permission:master_data.suppliers.view');
            Route::put('/suppliers/{supplier:slug}', [SupplierController::class, 'update'])->middleware('require.permission:master_data.suppliers.manage');
            Route::delete('/suppliers/{supplier:slug}', [SupplierController::class, 'destroy'])->middleware('require.permission:master_data.suppliers.manage');

            // Material Categories CRUD
            Route::get('/material-categories', [MaterialCategoryController::class, 'index'])->middleware('require.permission:master_data.material_categories.view');
            Route::post('/material-categories', [MaterialCategoryController::class, 'store'])->middleware('require.permission:master_data.material_categories.manage');
            Route::get('/material-categories/{materialCategory:slug}', [MaterialCategoryController::class, 'show'])->middleware('require.permission:master_data.material_categories.view');
            Route::put('/material-categories/{materialCategory:slug}', [MaterialCategoryController::class, 'update'])->middleware('require.permission:master_data.material_categories.manage');
            Route::delete('/material-categories/{materialCategory:slug}', [MaterialCategoryController::class, 'destroy'])->middleware('require.permission:master_data.material_categories.manage');

            // Materials & Prices CRUD
            Route::get('/materials', [MaterialController::class, 'index'])->middleware('require.permission:materials.view');
            Route::post('/materials', [MaterialController::class, 'store'])->middleware('require.permission:materials.create');
            Route::get('/materials/{material:slug}', [MaterialController::class, 'show'])->middleware('require.permission:materials.view');
            Route::put('/materials/{material:slug}', [MaterialController::class, 'update'])->middleware('require.permission:materials.edit');
            Route::delete('/materials/{material:slug}', [MaterialController::class, 'destroy'])->middleware('require.permission:materials.delete');
            Route::post('/materials/{material:slug}/prices', [MaterialController::class, 'storePrice'])->middleware('require.permission:materials.edit');
            Route::get('/materials/{material:slug}/effective-cost', [MaterialController::class, 'effectiveCost'])->middleware('require.permission:materials.view');

            // Product Categories CRUD
            Route::get('/product-categories', [ProductCategoryController::class, 'index'])->middleware('require.permission:master_data.product_categories.view');
            Route::post('/product-categories', [ProductCategoryController::class, 'store'])->middleware('require.permission:master_data.product_categories.manage');
            Route::get('/product-categories/{productCategory:slug}', [ProductCategoryController::class, 'show'])->middleware('require.permission:master_data.product_categories.view');
            Route::put('/product-categories/{productCategory:slug}', [ProductCategoryController::class, 'update'])->middleware('require.permission:master_data.product_categories.manage');
            Route::delete('/product-categories/{productCategory:slug}', [ProductCategoryController::class, 'destroy'])->middleware('require.permission:master_data.product_categories.manage');

            // Products CRUD
            Route::get('/products', [ProductController::class, 'index'])->middleware('require.permission:products.view');
            Route::post('/products', [ProductController::class, 'store'])->middleware('require.permission:products.create');
            Route::get('/products/{product:slug}', [ProductController::class, 'show'])->middleware('require.permission:products.view');
            Route::put('/products/{product:slug}', [ProductController::class, 'update'])->middleware('require.permission:products.edit');
            Route::delete('/products/{product:slug}', [ProductController::class, 'destroy'])->middleware('require.permission:products.delete');

            // Services CRUD (Service-type Products)
            Route::get('/services', [ProductController::class, 'index'])->middleware('require.permission:products.view');
            Route::post('/services', [ProductController::class, 'store'])->middleware('require.permission:products.create');
            Route::get('/services/{product:slug}', [ProductController::class, 'show'])->middleware('require.permission:products.view');
            Route::put('/services/{product:slug}', [ProductController::class, 'update'])->middleware('require.permission:products.edit');
            Route::delete('/services/{product:slug}', [ProductController::class, 'destroy'])->middleware('require.permission:products.delete');

            // Cost Categories & Cost Models
            Route::get('/cost-categories', [CostModelController::class, 'categories'])->middleware('require.permission:costing.view_margin');
            Route::post('/products/{product:slug}/cost-models', [CostModelController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/cost-models/{costModel:slug}', [CostModelController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/cost-models/{costModel:slug}', [CostModelController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-models/{costModel:slug}', [CostModelController::class, 'destroy'])->middleware('require.permission:costing.manage');
            Route::put('/cost-models/{costModel:slug}/components', [CostModelController::class, 'syncComponents'])->middleware('require.permission:costing.manage');
            Route::get('/cost-models/{costModel:slug}/preview', [CostModelMachineController::class, 'preview'])->middleware('require.permission:costing.view_margin');

            // BOM / Recipe Engine
            Route::post('/cost-models/{costModel:slug}/bom', [BomController::class, 'createOrGetHeader'])->middleware('require.permission:costing.manage');
            Route::post('/bom-headers/{bomHeader}/items', [BomController::class, 'addItem'])->middleware('require.permission:costing.manage');
            Route::put('/bom-items/{bomItem}', [BomController::class, 'updateItem'])->middleware('require.permission:costing.manage');
            Route::delete('/bom-items/{bomItem}', [BomController::class, 'deleteItem'])->middleware('require.permission:costing.manage');
            Route::get('/bom-headers/{bomHeader}/explode', [BomController::class, 'explode'])->middleware('require.permission:costing.view_margin');

            // Labor Rates & Labor Assignments
            Route::get('/labor-rates', [LaborRateController::class, 'index'])->middleware('require.permission:labor_machines.view');
            Route::post('/labor-rates', [LaborRateController::class, 'store'])->middleware('require.permission:labor_machines.manage');
            Route::get('/labor-rates/{laborRate:slug}', [LaborRateController::class, 'show'])->middleware('require.permission:labor_machines.view');
            Route::put('/labor-rates/{laborRate:slug}', [LaborRateController::class, 'update'])->middleware('require.permission:labor_machines.manage');
            Route::delete('/labor-rates/{laborRate:slug}', [LaborRateController::class, 'destroy'])->middleware('require.permission:labor_machines.manage');
            Route::get('/labor-rates/{laborRate:slug}/hourly-equivalent', [LaborRateController::class, 'hourlyEquivalent'])->middleware('require.permission:labor_machines.view');
            Route::post('/cost-models/{costModel:slug}/labor', [CostModelLaborController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::put('/cost-model-labors/{costModelLabor}', [CostModelLaborController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-model-labors/{costModelLabor}', [CostModelLaborController::class, 'destroy'])->middleware('require.permission:costing.manage');

            // Machines & Machine Assignments
            Route::get('/machines', [MachineController::class, 'index'])->middleware('require.permission:labor_machines.view');
            Route::post('/machines', [MachineController::class, 'store'])->middleware('require.permission:labor_machines.manage');
            Route::get('/machines/{machine:slug}', [MachineController::class, 'show'])->middleware('require.permission:labor_machines.view');
            Route::put('/machines/{machine:slug}', [MachineController::class, 'update'])->middleware('require.permission:labor_machines.manage');
            Route::delete('/machines/{machine:slug}', [MachineController::class, 'destroy'])->middleware('require.permission:labor_machines.manage');
            Route::get('/machines/{machine:slug}/cost-per-hour', [MachineController::class, 'costPerHour'])->middleware('require.permission:labor_machines.view');
            Route::post('/cost-models/{costModel:slug}/machines', [CostModelMachineController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::put('/cost-model-machines/{costModelMachine}', [CostModelMachineController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-model-machines/{costModelMachine}', [CostModelMachineController::class, 'destroy'])->middleware('require.permission:costing.manage');

            // Overheads & Cost Pools
            Route::get('/overheads', [OverheadController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/overheads', [OverheadController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/overheads/{overhead:slug}', [OverheadController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/overheads/{overhead:slug}', [OverheadController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/overheads/{overhead:slug}', [OverheadController::class, 'destroy'])->middleware('require.permission:costing.manage');

            Route::get('/cost-pools', [CostPoolController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/cost-pools', [CostPoolController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/cost-pools/{costPool:slug}', [CostPoolController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/cost-pools/{costPool:slug}', [CostPoolController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-pools/{costPool:slug}', [CostPoolController::class, 'destroy'])->middleware('require.permission:costing.manage');

            // Cost Drivers & Allocation Rules
            Route::get('/cost-drivers', [CostDriverController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/cost-drivers', [CostDriverController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/cost-drivers/{costDriver:slug}', [CostDriverController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/cost-drivers/{costDriver:slug}', [CostDriverController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-drivers/{costDriver:slug}', [CostDriverController::class, 'destroy'])->middleware('require.permission:costing.manage');

            Route::get('/allocation-rules', [AllocationRuleController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/allocation-rules', [AllocationRuleController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/allocation-rules/{allocationRule}', [AllocationRuleController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/allocation-rules/{allocationRule}', [AllocationRuleController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/allocation-rules/{allocationRule}', [AllocationRuleController::class, 'destroy'])->middleware('require.permission:costing.manage');
            Route::post('/allocation-rules/{allocationRule}/preview', [AllocationRuleController::class, 'preview'])->middleware('require.permission:costing.view_margin');

            // Activity-Based Costing (ABC)
            Route::get('/abc/activities', [AbcActivityController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/abc/activities', [AbcActivityController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/abc/activities/{activity:slug}', [AbcActivityController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/abc/activities/{activity:slug}', [AbcActivityController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/abc/activities/{activity:slug}', [AbcActivityController::class, 'destroy'])->middleware('require.permission:costing.manage');
            Route::post('/cost-models/{costModel:slug}/abc-activities', [AbcActivityController::class, 'assignToCostModel'])->middleware('require.permission:costing.manage');
            Route::put('/cost-model-activities/{costModelActivity}', [AbcActivityController::class, 'updateCostModelActivity'])->middleware('require.permission:costing.manage');
            Route::delete('/cost-model-activities/{costModelActivity}', [AbcActivityController::class, 'removeCostModelActivity'])->middleware('require.permission:costing.manage');
            Route::get('/cost-models/{costModel:slug}/abc-cost', [AbcActivityController::class, 'calculateForCostModel'])->middleware('require.permission:costing.view_margin');

            // Calculation Engine Execution
            Route::post('/costing/calculate/{costModel:slug}', [CalculationController::class, 'calculate'])->middleware('require.permission:costing.manage');

            // Costing Runs & Official History
            Route::get('/cost-models/{costModel:slug}/costing-runs', [CostingRunController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/cost-models/{costModel:slug}/costing-runs', [CostingRunController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/costing-runs/{costingRun}', [CostingRunController::class, 'show'])->middleware('require.permission:costing.view_margin');

            // Product Cost Versioning & Approval Workflow
            Route::get('/products/{product:slug}/cost-versions', [ProductCostVersionController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/products/{product:slug}/cost-versions', [ProductCostVersionController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/cost-versions/{productCostVersion}', [ProductCostVersionController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::post('/cost-versions/{productCostVersion}/submit-review', [ProductCostVersionController::class, 'submitForReview'])->middleware('require.permission:costing.manage');
            Route::post('/cost-versions/{productCostVersion}/approve', [ProductCostVersionController::class, 'approve'])->middleware('require.permission:costing.manage');
            Route::post('/cost-versions/{productCostVersion}/publish', [ProductCostVersionController::class, 'publish'])->middleware('require.permission:costing.manage');
            Route::post('/cost-versions/{productCostVersion}/archive', [ProductCostVersionController::class, 'archive'])->middleware('require.permission:costing.manage');

            // Pricing Engine & Fee Deductions
            Route::post('/pricing/calculate', [PricingController::class, 'calculate'])->middleware('require.permission:costing.view_margin');
            Route::get('/pricing-rules', [PricingController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/pricing-rules', [PricingController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/pricing-rules/{pricingRule}', [PricingController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/pricing-rules/{pricingRule}', [PricingController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/pricing-rules/{pricingRule}', [PricingController::class, 'destroy'])->middleware('require.permission:costing.manage');

            Route::get('/fees', [FeeController::class, 'index'])->middleware('require.permission:costing.view_margin');
            Route::post('/fees', [FeeController::class, 'store'])->middleware('require.permission:costing.manage');
            Route::get('/fees/{fee:slug}', [FeeController::class, 'show'])->middleware('require.permission:costing.view_margin');
            Route::put('/fees/{fee:slug}', [FeeController::class, 'update'])->middleware('require.permission:costing.manage');
            Route::delete('/fees/{fee:slug}', [FeeController::class, 'destroy'])->middleware('require.permission:costing.manage');

            // Profitability & BEP
            Route::post('/profitability/analyze', [ProfitabilityController::class, 'analyze'])->middleware('require.permission:costing.view_margin');
            Route::post('/bep/calculate', [BepController::class, 'calculate'])->middleware('require.permission:costing.view_margin');

            // Actual Costs & Variance Analysis
            Route::post('/products/{product:slug}/actual-cost', [VarianceController::class, 'recordActual'])->middleware('require.permission:costing.manage');
            Route::post('/variance/analyze', [VarianceController::class, 'analyze'])->middleware('require.permission:costing.view_margin');

            // What-If Simulation Engine
            Route::post('/cost-models/{costModel:slug}/simulate', [SimulationController::class, 'simulate'])->middleware('require.permission:costing.manage');
            Route::get('/cost-models/{costModel:slug}/scenarios', [SimulationController::class, 'index'])->middleware('require.permission:costing.view_margin');

            // Reports Suite (§45 Blueprint)
            Route::get('/reports/hpp-per-product', [ReportController::class, 'hppPerProduct'])->middleware('require.permission:costing.view_margin');
            Route::get('/reports/cost-breakdown', [ReportController::class, 'costBreakdown'])->middleware('require.permission:costing.view_margin');

            // Sales Reports berbasis SNAPSHOT transaksi
            Route::get('/reports/sales-summary', [PosReportController::class, 'salesSummary'])->middleware('require.permission:pos.reports');
            Route::get('/reports/sales-average-prices', [PosReportController::class, 'averagePrices'])->middleware('require.permission:pos.reports');

            // Purchase Reports berbasis SNAPSHOT PO
            Route::get('/reports/purchase-summary', [PurchaseReportController::class, 'summary'])->middleware('require.permission:purchasing.view');
            Route::get('/reports/purchase-history', [PurchaseReportController::class, 'history'])->middleware('require.permission:purchasing.view');
            Route::get('/reports/purchase-period', [PurchaseReportController::class, 'period'])->middleware('require.permission:purchasing.view');
            Route::get('/reports/purchase-supplier', [PurchaseReportController::class, 'supplier'])->middleware('require.permission:purchasing.view');
            Route::get('/reports/purchase-product', [PurchaseReportController::class, 'product'])->middleware('require.permission:purchasing.view');

            // Audit Logs (Restricted to Owner and Admin)
            Route::get('/audit-logs', [AuditLogController::class, 'index'])
                ->middleware('require.role:owner,admin');

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Dashboard
            // ──────────────────────────────────────────────────────────────────
            Route::get('/dashboard', [DashboardController::class, 'summary'])->middleware('require.permission:dashboard.view');
            Route::get('/dashboard/analytics', [\App\Http\Controllers\Api\V1\Dashboard\MobileOwnerPulseApiController::class, 'analytics'])->middleware('require.permission:dashboard.view');
            Route::get('/reports/cockpit', [\App\Http\Controllers\Api\V1\Dashboard\MobileOwnerPulseApiController::class, 'analytics'])->middleware('require.permission:dashboard.view');

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Profile & Business Profile
            // ──────────────────────────────────────────────────────────────────
            Route::get('/profile', [ProfileController::class, 'show']);
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
            Route::get('/auth/verification-status', [AuthController::class, 'verificationStatus']);
            Route::post('/auth/verification-notification', [AuthController::class, 'resendVerification']);
            Route::post('/profile/logout', [ProfileController::class, 'logout']);
            Route::post('/profile/logout-all', [ProfileController::class, 'logoutAllDevices']);
            Route::get('/profile/businesses/{business}', [ProfileController::class, 'businessProfile']);
            Route::put('/profile/businesses/{business}', [ProfileController::class, 'updateBusinessProfile']);

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – POS (Point-of-Sale)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('pos')->group(function (): void {
                // Terminal / Register
                Route::get('/terminal/bootstrap', [PosTerminalController::class, 'terminalData'])->middleware('require.permission:pos.terminal');
                Route::get('/terminal/search-products', [PosTerminalController::class, 'searchProducts'])->middleware('require.permission:pos.terminal');
                Route::post('/terminal/checkout', [PosTerminalController::class, 'checkout'])->middleware('require.permission:pos.terminal');
                Route::post('/terminal/send-to-kitchen', [PosTerminalController::class, 'sendToKitchen'])->middleware('require.permission:pos.terminal');
                Route::post('/terminal/toggle-auto-kds', [PosTerminalController::class, 'toggleAutoKds'])->middleware('require.permission:pos.terminal');
                Route::post('/terminal/hold', [PosTerminalController::class, 'holdOrder'])->middleware('require.permission:pos.terminal');
                Route::get('/terminal/held-orders', [PosTerminalController::class, 'getHeldOrders'])->middleware('require.permission:pos.terminal');
                Route::post('/orders/{posOrder}/hold', [PosTerminalController::class, 'holdOrder'])->middleware('require.permission:pos.terminal');
                Route::post('/orders/{posOrder}/resume', [PosTerminalController::class, 'resumeOrder'])->middleware('require.permission:pos.terminal');
                Route::get('/orders/{posOrder}/receipt', [PosTerminalController::class, 'receipt'])->middleware('require.permission:pos.terminal');
                Route::post('/reservations/{reservation}/seat', [PosTerminalController::class, 'seatReservation'])->middleware('require.permission:pos.terminal');
                Route::get('/orders/{order}/status', [PosTerminalController::class, 'checkOrderStatus'])->middleware('require.permission:pos.terminal');
                Route::get('/orders/{order}/qr-image', [PosTerminalController::class, 'getQrisImage'])->middleware('require.permission:pos.terminal');
                Route::post('/orders/{order}/cancel-qris', [PosTerminalController::class, 'cancelQrisOrder'])->middleware('require.permission:pos.terminal');
                Route::post('/orders/{order}/simulate-qris', [PosTerminalController::class, 'simulateSandboxPayment'])->middleware('require.permission:pos.terminal');

                // Offline Batch Synchronization (Idempotent sync by client_uuid)
                Route::post('/sync/batch', [\App\Http\Controllers\Api\V1\Pos\PosSyncApiController::class, 'batchSync'])->middleware('require.permission:pos.terminal');

                // Kitchen & Bar Display (KDS)
                Route::get('/kitchen/orders', [\App\Http\Controllers\Api\V1\Pos\PosKitchenApiController::class, 'index'])->middleware('require.permission:pos.kitchen');
                Route::post('/kitchen/orders/{order}/status', [\App\Http\Controllers\Api\V1\Pos\PosKitchenApiController::class, 'updateStatus'])->middleware('require.permission:pos.kitchen');
                Route::get('/kitchen/prep-sheet', [\App\Http\Controllers\Api\V1\Pos\PosKitchenApiController::class, 'prepSheet'])->middleware('require.permission:pos.kitchen');

                // Table Management & Self-Order QR Cards
                Route::get('/tables', [\App\Http\Controllers\Api\V1\Pos\PosTableApiController::class, 'index'])->middleware('require.permission:pos.tables');
                Route::post('/tables', [\App\Http\Controllers\Api\V1\Pos\PosTableApiController::class, 'store'])->middleware('require.permission:pos.tables');
                Route::put('/tables/{table}', [\App\Http\Controllers\Api\V1\Pos\PosTableApiController::class, 'update'])->middleware('require.permission:pos.tables');
                Route::delete('/tables/{table}', [\App\Http\Controllers\Api\V1\Pos\PosTableApiController::class, 'destroy'])->middleware('require.permission:pos.tables');
                Route::post('/tables/{table}/regenerate-qr', [\App\Http\Controllers\Api\V1\Pos\PosTableApiController::class, 'regenerateQr'])->middleware('require.permission:pos.tables');
                Route::get('/tables/{table}/qr-card', [\App\Http\Controllers\Api\V1\Pos\PosTableApiController::class, 'qrCardData'])->middleware('require.permission:pos.tables');
                Route::post('/tables/{table}/close-session', [\App\Http\Controllers\Api\V1\Pos\PosTableApiController::class, 'closeSession'])->middleware('require.permission:pos.tables');

                // Supervisor Authorization (mirror of web /pos/verify-pin)
                Route::post('/verify-pin', [PosTerminalController::class, 'verifySupervisorPin'])->middleware('throttle:5,1');

                // Modifiers & Add-ons
                Route::get('/modifiers', [PosModifierApiController::class, 'index'])->middleware('require.permission:pos.terminal');
                Route::post('/modifiers/groups', [PosModifierApiController::class, 'storeGroup'])->middleware('require.permission:pos.terminal');
                Route::put('/modifiers/groups/{group}', [PosModifierApiController::class, 'updateGroup'])->middleware('require.permission:pos.terminal');
                Route::delete('/modifiers/groups/{group}', [PosModifierApiController::class, 'destroyGroup'])->middleware('require.permission:pos.terminal');
                Route::post('/modifiers/options', [PosModifierApiController::class, 'storeOption'])->middleware('require.permission:pos.terminal');
                Route::put('/modifiers/options/{option}', [PosModifierApiController::class, 'updateOption'])->middleware('require.permission:pos.terminal');
                Route::delete('/modifiers/options/{option}', [PosModifierApiController::class, 'destroyOption'])->middleware('require.permission:pos.terminal');

                // Shift Management
                Route::get('/shifts', [PosShiftController::class, 'index'])->middleware('require.permission:pos.terminal,pos.orders');
                Route::post('/shifts/open', [PosShiftController::class, 'open'])->middleware('require.permission:pos.terminal,pos.orders');
                Route::post('/shifts/{posShift}/close', [PosShiftController::class, 'close'])->middleware('require.permission:pos.terminal,pos.orders');
                Route::get('/shifts/{posShift}/summary', [PosShiftController::class, 'summary'])->middleware('require.permission:pos.terminal,pos.orders');
                Route::post('/shifts/{posShift}/cash-movement', [PosShiftController::class, 'recordCashMovement'])->middleware('require.permission:pos.terminal,pos.orders');
                Route::get('/shifts/active', [PosShiftController::class, 'current'])->middleware('require.permission:pos.terminal,pos.orders');
                Route::get('/shifts/current', [PosShiftController::class, 'current'])->middleware('require.permission:pos.terminal,pos.orders');
                Route::post('/shifts/{posShift}/print', [PosShiftController::class, 'printSummary'])->middleware('require.permission:pos.terminal,pos.orders');

                // Orders
                Route::get('/orders', [PosOrderController::class, 'index'])->middleware('require.permission:pos.orders');
                Route::get('/orders/{posOrder}', [PosOrderController::class, 'show'])->middleware('require.permission:pos.orders');
                Route::post('/orders/{posOrder}/void', [PosOrderController::class, 'void'])->middleware('require.permission:pos.supervisor_pin');
                Route::post('/orders/{posOrder}/refund', [PosOrderController::class, 'refund'])->middleware('require.permission:pos.supervisor_pin');

                // Reports
                Route::get('/reports/sales-summary', [PosReportController::class, 'salesSummary'])->middleware('require.permission:pos.reports');
                Route::get('/reports/average-prices', [PosReportController::class, 'averagePrices'])->middleware('require.permission:pos.reports');
                Route::get('/reports/payment-breakdown', [PosReportController::class, 'paymentBreakdown'])->middleware('require.permission:pos.reports');
                Route::get('/reports/top-products', [PosReportController::class, 'topProducts'])->middleware('require.permission:pos.reports');
                Route::get('/reports/hourly-heatmap', [PosReportController::class, 'hourlyHeatmap'])->middleware('require.permission:pos.reports');

                // Local POS Agent Bridge
                Route::prefix('agent')->middleware('require.permission:pos.terminal')->group(function (): void {
                    Route::get('/jobs', [PosAgentApiController::class, 'getPendingJobs']);
                    Route::post('/jobs/{job}/status', [PosAgentApiController::class, 'updateJobStatus']);
                    Route::post('/printers/{printer}/status', [PosAgentApiController::class, 'syncDeviceStatus']);
                    Route::post('/sync-device', [PosAgentApiController::class, 'syncDevice']);
                    Route::get('/printers', [PosAgentApiController::class, 'getPrinters']);
                });
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Inventory Management
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('inventory')->group(function (): void {
                Route::get('/stocks', [InventoryController::class, 'stocks'])->middleware('require.permission:inventory.view');
                Route::get('/stocks/{product}/movements', [InventoryController::class, 'movements'])->middleware('require.permission:inventory.view');
                Route::get('/movements', [InventoryController::class, 'movements'])->middleware('require.permission:inventory.view');
                Route::post('/adjustments', [InventoryController::class, 'adjust'])->middleware('require.permission:inventory.manage');

                // Stock Opname (Physical Count)
                Route::get('/opnames', [StockOpnameController::class, 'index'])->middleware('require.permission:inventory.view');
                Route::post('/opnames', [StockOpnameController::class, 'store'])->middleware('require.permission:inventory.manage');
                Route::get('/opnames/{stockOpname}', [StockOpnameController::class, 'show'])->middleware('require.permission:inventory.view');
                Route::post('/opnames/{stockOpname}/reconcile', [StockOpnameController::class, 'reconcile'])->middleware('require.permission:inventory.manage');

                // Stock Transfers (Inter-Location)
                Route::get('/transfers', [StockTransferController::class, 'index'])->middleware('require.permission:inventory.view');
                Route::post('/transfers', [StockTransferController::class, 'store'])->middleware('require.permission:inventory.manage');
                Route::get('/transfers/{stockTransfer}', [StockTransferController::class, 'show'])->middleware('require.permission:inventory.view');
                Route::post('/transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive'])->middleware('require.permission:inventory.manage');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Purchasing
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('purchasing')->group(function (): void {
                // Purchase Orders
                Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->middleware('require.permission:purchasing.view');
                Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->middleware('require.permission:purchasing.manage');
                Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->middleware('require.permission:purchasing.view');
                Route::put('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->middleware('require.permission:purchasing.manage');
                Route::post('/purchase-orders/{purchaseOrder}/confirm', [PurchaseOrderController::class, 'confirm'])->middleware('require.permission:purchasing.manage');
                Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->middleware('require.permission:purchasing.manage');

                // Goods Receipts
                Route::get('/goods-receipts', [GoodsReceiptController::class, 'index'])->middleware('require.permission:receiving.manage');
                Route::post('/goods-receipts', [GoodsReceiptController::class, 'store'])->middleware('require.permission:receiving.manage');
                Route::get('/goods-receipts/{goodsReceipt}', [GoodsReceiptController::class, 'show'])->middleware('require.permission:receiving.manage');
                Route::post('/stock-in', [GoodsReceiptController::class, 'instantStockIn'])->middleware('require.permission:receiving.manage');
                Route::get('/supplier-invoices', [SupplierInvoiceController::class, 'index'])->middleware('require.permission:purchasing.bills');
                Route::get('/supplier-invoices/{supplierInvoice}', [SupplierInvoiceController::class, 'show'])->middleware('require.permission:purchasing.bills');
                Route::post('/supplier-invoices/{supplierInvoice}/payments', [SupplierInvoiceController::class, 'recordPayment'])->middleware('require.permission:purchasing.bills');
                Route::get('/returns', [\App\Http\Controllers\Api\V1\Purchasing\PurchaseReturnController::class, 'index'])->middleware('require.permission:purchase.returns');
                Route::post('/returns', [\App\Http\Controllers\Api\V1\Purchasing\PurchaseReturnController::class, 'store'])->middleware('require.permission:purchase.returns');
                Route::post('/returns/{return}/approve', [\App\Http\Controllers\Api\V1\Purchasing\PurchaseReturnController::class, 'approve'])->middleware('require.permission:purchase.returns');
                Route::post('/returns/{return}/complete', [\App\Http\Controllers\Api\V1\Purchasing\PurchaseReturnController::class, 'complete'])->middleware('require.permission:purchase.returns');

                // Purchase Reports (snapshot harga beli PO)
                Route::prefix('reports')->group(function (): void {
                    Route::get('/summary', [PurchaseReportController::class, 'summary'])->middleware('require.permission:purchasing.view');
                    Route::get('/history', [PurchaseReportController::class, 'history'])->middleware('require.permission:purchasing.view');
                    Route::get('/period', [PurchaseReportController::class, 'period'])->middleware('require.permission:purchasing.view');
                    Route::get('/supplier', [PurchaseReportController::class, 'supplier'])->middleware('require.permission:purchasing.view');
                    Route::get('/product', [PurchaseReportController::class, 'product'])->middleware('require.permission:purchasing.view');
                });
            });

            // Cash & bank ledger for the active business.
            Route::prefix('finance/cash-ledger')->group(function (): void {
                Route::get('/accounts', [CashLedgerController::class, 'accounts'])->middleware('require.permission:finance.cash_bank');
                Route::get('/transactions', [CashLedgerController::class, 'transactions'])->middleware('require.permission:finance.cash_bank');
                Route::post('/inflows', [CashLedgerController::class, 'inflow'])->middleware('require.permission:finance.cash_bank');
                Route::post('/outflows', [CashLedgerController::class, 'outflow'])->middleware('require.permission:finance.cash_bank');
                Route::post('/transfers', [CashLedgerController::class, 'transfer'])->middleware('require.permission:finance.cash_bank');
            });
            Route::prefix('finance/settlements')->group(function (): void {
                Route::get('/', [PaymentSettlementController::class, 'index'])->middleware('require.permission:finance.cash_bank');
                Route::post('/reconcile', [PaymentSettlementController::class, 'reconcile'])->middleware('require.permission:finance.cash_bank');
                Route::get('/{settlement}', [PaymentSettlementController::class, 'show'])->middleware('require.permission:finance.cash_bank');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Sales Pipeline
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('sales')->group(function (): void {
                // Quotations
                Route::get('/quotations', [QuotationController::class, 'index'])->middleware('require.permission:sales.view');
                Route::post('/quotations', [QuotationController::class, 'store'])->middleware('require.permission:sales.pipeline');
                Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->middleware('require.permission:sales.view');
                Route::post('/quotations/{quotation}/convert', [QuotationController::class, 'convert'])->middleware('require.permission:sales.pipeline');

                // Sales Orders
                Route::get('/orders', [SalesOrderController::class, 'index'])->middleware('require.permission:sales.view');
                Route::post('/orders', [SalesOrderController::class, 'store'])->middleware('require.permission:sales.pipeline');
                Route::get('/orders/{salesOrder}', [SalesOrderController::class, 'show'])->middleware('require.permission:sales.view');
                Route::put('/orders/{salesOrder}', [SalesOrderController::class, 'update'])->middleware('require.permission:sales.pipeline');
                Route::delete('/orders/{salesOrder}', [SalesOrderController::class, 'destroy'])->middleware('require.permission:sales.pipeline');
                Route::post('/orders/{salesOrder}/generate-invoice', [SalesOrderController::class, 'generateInvoice'])->middleware('require.permission:sales.pipeline');

                // Invoices
                Route::get('/invoices', [InvoiceController::class, 'index'])->middleware('require.permission:invoices.view');
                Route::post('/invoices', [InvoiceController::class, 'store'])->middleware('require.permission:invoices.create');
                Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->middleware('require.permission:invoices.view');
                Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->middleware('require.permission:invoices.edit');
                Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->middleware('require.permission:invoices.record_payment');
                Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->middleware('require.permission:invoices.delete');
                Route::get('/returns', [\App\Http\Controllers\Api\V1\Sales\SalesReturnController::class, 'index'])->middleware('require.permission:sales.returns');
                Route::post('/returns', [\App\Http\Controllers\Api\V1\Sales\SalesReturnController::class, 'store'])->middleware('require.permission:sales.returns');
                Route::post('/returns/{return}/approve', [\App\Http\Controllers\Api\V1\Sales\SalesReturnController::class, 'approve'])->middleware('require.permission:sales.returns');
                Route::post('/returns/{return}/complete', [\App\Http\Controllers\Api\V1\Sales\SalesReturnController::class, 'complete'])->middleware('require.permission:sales.returns');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – CRM (Customer Relationship Management)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('crm')->group(function (): void {
                Route::get('/customers', [CustomerController::class, 'index'])->middleware('require.permission:customers.view');
                Route::post('/customers', [CustomerController::class, 'store'])->middleware('require.permission:customers.create');
                Route::get('/customers/{customer}', [CustomerController::class, 'show'])->middleware('require.permission:customers.view');
                Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('require.permission:customers.edit');
                Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('require.permission:customers.delete');

                // Loyalty
                Route::get('/loyalty/program', [CrmLoyaltyController::class, 'program'])->middleware('require.permission:crm.view');
                Route::get('/loyalty/customers/{customer}', [CrmLoyaltyController::class, 'customerAccount'])->middleware('require.permission:crm.view');
                Route::post('/loyalty/customers/{customer}/adjust', [CrmLoyaltyController::class, 'adjustPoints'])->middleware('require.permission:crm.manage');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Finance
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('finance')->group(function (): void {
                Route::get('/dashboard', [FinanceController::class, 'dashboard'])->middleware('require.permission:accounting.view');
                Route::get('/expenses', [FinanceController::class, 'expenses'])->middleware('require.permission:expenses.view');
                Route::post('/expenses', [FinanceController::class, 'storeExpense'])->middleware('require.permission:expenses.manage');
                Route::get('/chart-of-accounts', [FinanceController::class, 'chartOfAccounts'])->middleware('require.permission:accounting.view');
                Route::get('/journal-entries', [FinanceController::class, 'journalEntries'])->middleware('require.permission:accounting.view');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – AI Assistant (Cooca Intelligence)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('ai')->group(function (): void {
                Route::post('/chat', [AiAssistantController::class, 'chat'])->middleware('require.permission:ai.access');
                Route::post('/execute-action', [AiAssistantController::class, 'executeAction'])->middleware('require.permission:ai.access');
                Route::get('/token-usage', [AiAssistantController::class, 'tokenUsage'])->middleware('require.permission:ai.access');
                Route::get('/forecasting', [AiAssistantController::class, 'salesForecasting'])->middleware('require.permission:ai.access');
                Route::get('/stock-prediction', [AiAssistantController::class, 'stockPrediction'])->middleware('require.permission:ai.access');
                Route::get('/fraud-detection', [AiAssistantController::class, 'fraudDetection'])->middleware('require.permission:ai.access');
                Route::get('/profitability-matrix', [AiAssistantController::class, 'profitabilityMatrix'])->middleware('require.permission:ai.access');
                Route::get('/smart-pricing', [AiAssistantController::class, 'smartPricing'])->middleware('require.permission:ai.access');
                Route::get('/smart-promo', [AiAssistantController::class, 'smartPromo'])->middleware('require.permission:ai.access');
                Route::get('/customer-rfm', [AiAssistantController::class, 'customerRfm'])->middleware('require.permission:ai.access');
                Route::get('/cashier-performance', [AiAssistantController::class, 'cashierPerformance'])->middleware('require.permission:ai.access');
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP & BACKOFFICE API – HRM Attendance & Biometrics
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('attendance')->group(function (): void {
                Route::post('/check-in', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'checkIn']);
                Route::post('/clock-in', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'checkIn']);
                Route::post('/check-out', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'checkOut']);
                Route::post('/clock-out', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'checkOut']);
                Route::get('/today', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'today']);
                Route::get('/portal', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'portal']);
                Route::get('/payslips', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'payslips']);
                Route::get('/payslips/{item}', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'showPayslip']);
                Route::get('/history', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'history']);
                Route::get('/summary', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'summary']);

                // Face Biometrics
                Route::post('/face-template/register', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'registerFace']);
                Route::post('/face-template/verify', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'verifyFace']);
                Route::post('/face-template/reset', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'resetFace']);

                // Attendance Corrections (Ajukan Perbaikan Absen)
                Route::get('/corrections', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'listCorrections']);
                Route::post('/corrections', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'storeCorrection']);
                Route::put('/corrections/{correction}', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'updateCorrection']);
                Route::post('/corrections/{correction}/approve', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'approveCorrection']);
                Route::post('/corrections/{correction}/reject', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'rejectCorrection']);
                Route::post('/corrections/{correction}/request-revision', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'requestRevision']);

                // Attendance Exception Policies (WFH, WFA, Field Work, Business Trip)
                Route::get('/exceptions', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'listExceptions']);
                Route::post('/exceptions', [\App\Http\Controllers\Api\V1\Hrm\AttendanceApiController::class, 'storeException']);
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP & BACKOFFICE API – HRM Hub (Staff, Shifts, Payroll, Loans)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('hrm')->group(function (): void {
                Route::get('/hub', [\App\Http\Controllers\Api\V1\Hrm\HrmApiController::class, 'hub']);
                Route::post('/employees', [\App\Http\Controllers\Api\V1\Hrm\HrmApiController::class, 'storeEmployee']);
                Route::put('/employees/{membership}', [\App\Http\Controllers\Api\V1\Hrm\HrmApiController::class, 'updateEmployee']);
                Route::post('/shifts', [\App\Http\Controllers\Api\V1\Hrm\HrmApiController::class, 'storeShift']);
                Route::post('/loans', [\App\Http\Controllers\Api\V1\Hrm\HrmApiController::class, 'storeLoan']);
                Route::post('/corrections/{correction}/approve', [\App\Http\Controllers\Api\V1\Hrm\HrmApiController::class, 'approveCorrection']);
                Route::post('/corrections/{correction}/reject', [\App\Http\Controllers\Api\V1\Hrm\HrmApiController::class, 'rejectCorrection']);
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – KDS (Kitchen Display System)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('pos/kitchen')->middleware('require.permission:pos.terminal')->group(function (): void {
                Route::get('/orders', [\App\Http\Controllers\Api\V1\Pos\PosKitchenApiController::class, 'index']);
                Route::get('/prep-sheet', [\App\Http\Controllers\Api\V1\Pos\PosKitchenApiController::class, 'prepSheet']);
                Route::post('/orders/{order}/status', [\App\Http\Controllers\Api\V1\Pos\PosKitchenApiController::class, 'updateStatus']);
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Merchant Commerce Orders (Native COOCA Fulfillment)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('commerce/orders')->middleware('require.permission:sales.view')->group(function (): void {
                Route::get('/', [\App\Http\Controllers\Api\V1\Commerce\MerchantOrderApiController::class, 'index']);
                Route::get('/{order}', [\App\Http\Controllers\Api\V1\Commerce\MerchantOrderApiController::class, 'show']);
                Route::post('/{order}/verify-payment', [\App\Http\Controllers\Api\V1\Commerce\MerchantOrderApiController::class, 'verifyPayment'])->middleware('require.permission:sales.pipeline');
                Route::post('/{order}/request-pickup', [\App\Http\Controllers\Api\V1\Commerce\MerchantOrderApiController::class, 'requestPickup'])->middleware('require.permission:sales.pipeline');
                Route::post('/{order}/waybill', [\App\Http\Controllers\Api\V1\Commerce\MerchantOrderApiController::class, 'updateWaybill'])->middleware('require.permission:sales.pipeline');
                Route::get('/{order}/shipping-label-data', [\App\Http\Controllers\Api\V1\Commerce\MerchantOrderApiController::class, 'shippingLabelData']);
            });

            // ──────────────────────────────────────────────────────────────────
            // MOBILE APP API – Owner Pulse Dashboard (Apple HIG Bento)
            // ──────────────────────────────────────────────────────────────────
            Route::prefix('mobile/dashboard')->group(function (): void {
                Route::get('/pulse', [\App\Http\Controllers\Api\V1\Dashboard\MobileOwnerPulseApiController::class, 'pulse'])->middleware('require.permission:dashboard.view');
                Route::get('/analytics', [\App\Http\Controllers\Api\V1\Dashboard\MobileOwnerPulseApiController::class, 'analytics'])->middleware('require.permission:dashboard.view');
            });
        });
    });
});

