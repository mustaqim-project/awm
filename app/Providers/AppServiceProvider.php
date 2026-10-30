<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        \App\Domain\Mail\DynamicMailConfig::bootstrap();
        \Carbon\Carbon::setLocale('id');

        // Standardized Global Blade Directives for DateTime & Timezone
        \Illuminate\Support\Facades\Blade::directive('datetime', function ($expression) {
            return "<?php echo \\App\\Support\\TimezoneHelper::formatLocal({$expression}); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('date', function ($expression) {
            return "<?php echo \\App\\Support\\TimezoneHelper::formatDate({$expression}); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('time', function ($expression) {
            return "<?php echo \\App\\Support\\TimezoneHelper::formatTime({$expression}); ?>";
        });

        // Progressive Disclosure & Feature Parameterization Blade Directive
        \Illuminate\Support\Facades\Blade::if('feature', function (string $featureKey, ?bool $default = null): bool {
            $business = \App\Support\Context::business();

            return $business ? $business->isFeatureEnabled($featureKey, $default) : (bool) $default;
        });

        // Global View Composer for Public & Marketing Views (Single Source of Truth)
        \Illuminate\Support\Facades\View::composer(['public.*', 'layouts.public_marketing', 'landing'], function ($view) {
            $entitlement = app(\App\Domain\Billing\EntitlementService::class);
            $pricingService = app(\App\Domain\Billing\EarlyAdopterPricingService::class);

            $tiers = [
                'standard' => \App\Models\BusinessSubscription::TIER_STANDARD,
                'premium'  => \App\Models\BusinessSubscription::TIER_PREMIUM,
                'prestige' => \App\Models\BusinessSubscription::TIER_PRESTIGE,
            ];

            $pricingData = [];
            foreach ($tiers as $key => $tier) {
                $monthly = $pricingService->calculatePrice($tier, 'monthly');
                $annual = $pricingService->calculatePrice($tier, 'annual');
                $monthlyPrice = (float) $monthly['price'];
                $annualPrice = (float) $annual['price'];
                $annualMonthlyEquivalent = (int) round($annualPrice / 12);
                $annualSavings = max(0.0, ($monthlyPrice * 12) - $annualPrice);

                $pricingData[$key] = [
                    'tier' => $key,
                    'monthly_price' => $monthlyPrice,
                    'annual_price' => $annualPrice,
                    'monthly_price_formatted' => number_format($monthlyPrice, 0, ',', '.'),
                    'annual_price_formatted' => number_format($annualPrice, 0, ',', '.'),
                    'annual_monthly_equivalent_formatted' => number_format($annualMonthlyEquivalent, 0, ',', '.'),
                    'annual_savings_formatted' => number_format($annualSavings, 0, ',', '.'),
                    'is_early_adopter' => $monthly['is_early_adopter'] || $annual['is_early_adopter'],
                    'batch_name' => $monthly['batch_name'] ?? $annual['batch_name'] ?? null,
                    'remaining_quota' => $monthly['remaining_quota'] ?? $annual['remaining_quota'] ?? null,
                ];
            }

            $supportSla = [
                'hours' => \App\Models\SystemSetting::get('support_working_hours', 'Senin – Sabtu 08.00 – 20.00 WIB'),
                'response_time' => \App\Models\SystemSetting::get('support_response_time', '< 15 Menit'),
                'channel' => \App\Models\SystemSetting::get('support_channel_text', 'Chat WhatsApp Resmi & Pusat Bantuan'),
            ];

            $view->with([
                'tierLimits' => $entitlement->getAllTierLimits(),
                'pricingData' => $pricingData,
                'annualDiscountBadge' => \App\Models\SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan'),
                'supportSla' => $supportSla,
            ]);
        });

        // Platform Admin RBAC Gate Integration
        \Illuminate\Support\Facades\Gate::before(function ($user, string $ability) {
            if ($user instanceof \App\Models\Admin) {
                if ($user->isSuperAdmin()) {
                    return true;
                }

                return $user->hasPermission($ability) ? true : null;
            }

            return null;
        });

        // Self-heal: Ensure colliding public/admin/social-media directory does not intercept Laravel routes
        $collidingDir = public_path('admin/social-media');
        if (is_dir($collidingDir)) {
            try {
                \Illuminate\Support\Facades\File::deleteDirectory($collidingDir);
            } catch (\Throwable) {
                // Ignore filesystem edge cases
            }
        }

        // Enforce clean canonical root URL (prevent /public contamination in route and asset helpers)
        $this->app->booted(function () {
            try {
                $appUrl = (string) config('app.url');
                if ($appUrl !== '') {
                    // In local development, avoid forcing production cooca.id domain
                    if (app()->environment('local') && str_contains($appUrl, 'cooca.id')) {
                        return;
                    }
                    \Illuminate\Support\Facades\URL::forceRootUrl(rtrim($appUrl, '/'));
                    if (str_starts_with($appUrl, 'https://')) {
                        \Illuminate\Support\Facades\URL::forceScheme('https');
                    }
                }
            } catch (\Throwable) {
                // Ignore edge cases during early console bootstrap
            }
        });
    }
}
