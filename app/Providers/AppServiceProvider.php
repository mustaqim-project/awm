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
