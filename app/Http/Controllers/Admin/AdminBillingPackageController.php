<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingPackage;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AdminBillingPackageController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('require.admin.permission:admin.billing_packages.view', only: ['index']),
            new Middleware('require.admin.permission:admin.billing_packages.create', only: ['store']),
            new Middleware('require.admin.permission:admin.billing_packages.edit', only: ['update']),
            new Middleware('require.admin.permission:admin.billing_packages.toggle', only: ['toggle']),
        ];
    }
    public function index(string $type = BillingPackage::TYPE_SUBSCRIPTION): View
    {
        abort_unless(in_array($type, BillingPackage::TYPES, true), 404);
        $packages = BillingPackage::where('type', $type)->orderBy('sort_order')->orderBy('name')->get();
        $counts = BillingPackage::select('type', DB::raw('COUNT(*) as total'))->groupBy('type')->pluck('total', 'type');

        // Fetch from BillingPackage catalog first to ensure exact single-source consistency with admin settings
        $pkgStandardMonthly = BillingPackage::where('type', BillingPackage::TYPE_SUBSCRIPTION)->where('code', 'standard-monthly')->value('price');
        $pkgStandardAnnual = BillingPackage::where('type', BillingPackage::TYPE_SUBSCRIPTION)->where('code', 'standard-annual')->value('price');
        $pkgPremiumMonthly = BillingPackage::where('type', BillingPackage::TYPE_SUBSCRIPTION)->where('code', 'premium-monthly')->value('price');
        $pkgPremiumAnnual = BillingPackage::where('type', BillingPackage::TYPE_SUBSCRIPTION)->where('code', 'premium-annual')->value('price');
        $pkgPrestigeMonthly = BillingPackage::where('type', BillingPackage::TYPE_SUBSCRIPTION)->where('code', 'prestige-monthly')->value('price');
        $pkgPrestigeAnnual = BillingPackage::where('type', BillingPackage::TYPE_SUBSCRIPTION)->where('code', 'prestige-annual')->value('price');

        $subscriptionPriceMonthly = SystemSetting::get('subscription_price_monthly', (string) ($pkgStandardMonthly ?? '39000'));
        $subscriptionPriceAnnual = SystemSetting::get('subscription_price_annual', (string) ($pkgStandardAnnual ?? '390000'));
        $subscriptionAiTokensMonthly = SystemSetting::get('subscription_ai_tokens_monthly', '10000000');
        $subscriptionAnnualDiscountBadge = SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan');

        $priceStandardMonthly = $pkgStandardMonthly !== null ? (string) (int) $pkgStandardMonthly : SystemSetting::get('subscription_price_standard_monthly', $subscriptionPriceMonthly);
        $priceStandardAnnual = $pkgStandardAnnual !== null ? (string) (int) $pkgStandardAnnual : SystemSetting::get('subscription_price_standard_annual', $subscriptionPriceAnnual);
        $pricePremiumMonthly = $pkgPremiumMonthly !== null ? (string) (int) $pkgPremiumMonthly : SystemSetting::get('subscription_price_premium_monthly', '89000');
        $pricePremiumAnnual = $pkgPremiumAnnual !== null ? (string) (int) $pkgPremiumAnnual : SystemSetting::get('subscription_price_premium_annual', '890000');
        $pricePrestigeMonthly = $pkgPrestigeMonthly !== null ? (string) (int) $pkgPrestigeMonthly : SystemSetting::get('subscription_price_prestige_monthly', '199000');
        $pricePrestigeAnnual = $pkgPrestigeAnnual !== null ? (string) (int) $pkgPrestigeAnnual : SystemSetting::get('subscription_price_prestige_annual', '1990000');

        // Default top-up pricing for token & storage catalogs
        $pkgAiTokenPrice = BillingPackage::where('type', BillingPackage::TYPE_AI_TOKEN)->where('code', 'token-1m')->value('price');
        $pkgAiTokenAmount = BillingPackage::where('type', BillingPackage::TYPE_AI_TOKEN)->where('code', 'token-1m')->value('token_quantity');
        $aiTokenTopupPrice = $pkgAiTokenPrice !== null ? (string) (int) $pkgAiTokenPrice : SystemSetting::get('ai_token_topup_price', '50000');
        $aiTokenTopupAmount = $pkgAiTokenAmount !== null ? (string) (int) $pkgAiTokenAmount : SystemSetting::get('ai_token_topup_amount', '1000000');

        $pkgStoragePrice = BillingPackage::where('type', BillingPackage::TYPE_STORAGE)->where('code', 'storage-1gb')->value('price');
        $pkgStorageBytes = BillingPackage::where('type', BillingPackage::TYPE_STORAGE)->where('code', 'storage-1gb')->value('storage_bytes');
        $pkgStorageGb = $pkgStorageBytes ? (int) round($pkgStorageBytes / 1073741824) : null;
        $storageTopupPrice = $pkgStoragePrice !== null ? (string) (int) $pkgStoragePrice : SystemSetting::get('storage_topup_price', '50000');
        $storageTopupGb = $pkgStorageGb !== null ? (string) $pkgStorageGb : SystemSetting::get('storage_topup_gb', '1');
        $ownerStorageLimitGb = SystemSetting::get('owner_storage_limit_gb', '3');

        $entitlementService = app(\App\Domain\Billing\EntitlementService::class);
        $tierLimits = $entitlementService->getAllTierLimits();
        $tierFeatures = $entitlementService->getAllTierFeatures();
        $featureDefinitions = \App\Domain\Billing\EntitlementService::FEATURE_DEFINITIONS;

        return view('admin.billing-packages.index', compact(
            'type',
            'packages',
            'counts',
            'subscriptionPriceMonthly',
            'subscriptionPriceAnnual',
            'subscriptionAiTokensMonthly',
            'subscriptionAnnualDiscountBadge',
            'priceStandardMonthly',
            'priceStandardAnnual',
            'pricePremiumMonthly',
            'pricePremiumAnnual',
            'pricePrestigeMonthly',
            'pricePrestigeAnnual',
            'aiTokenTopupPrice',
            'aiTokenTopupAmount',
            'ownerStorageLimitGb',
            'storageTopupPrice',
            'storageTopupGb',
            'tierLimits',
            'tierFeatures',
            'featureDefinitions'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePackage($request);
        $type = $validated['type'];
        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 2;
        while (BillingPackage::where('type', $type)->where('code', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $package = BillingPackage::create($this->packageData($validated, $slug));
        $this->syncPackageToSettings($package);

        return redirect()->route('admin.billing-packages.index', $type)->with('success', 'Paket billing berhasil dibuat.');
    }

    public function update(Request $request, BillingPackage $billingPackage): RedirectResponse
    {
        $validated = $this->validatePackage($request, false);
        abort_unless($billingPackage->type === $validated['type'], 422);
        $billingPackage->update($this->packageData($validated, $billingPackage->code));
        $this->syncPackageToSettings($billingPackage);

        return redirect()->route('admin.billing-packages.index', $billingPackage->type)->with('success', 'Paket billing berhasil diperbarui.');
    }

    public function toggle(BillingPackage $billingPackage): RedirectResponse
    {
        $billingPackage->update(['is_active' => ! $billingPackage->is_active]);
        $this->syncPackageToSettings($billingPackage);

        return back()->with('success', $billingPackage->is_active ? 'Paket diaktifkan.' : 'Paket dinonaktifkan.');
    }

    /**
     * Synchronize modified BillingPackage attributes back to SystemSettings and clear pricing caches.
     */
    private function syncPackageToSettings(BillingPackage $billingPackage): void
    {
        $codeMap = [
            'standard-monthly' => ['subscription_price_standard_monthly', 'subscription_price_monthly'],
            'standard-annual'  => ['subscription_price_standard_annual', 'subscription_price_annual'],
            'premium-monthly'  => ['subscription_price_premium_monthly'],
            'premium-annual'   => ['subscription_price_premium_annual'],
            'prestige-monthly' => ['subscription_price_prestige_monthly'],
            'prestige-annual'  => ['subscription_price_prestige_annual'],
        ];

        if (isset($codeMap[$billingPackage->code])) {
            foreach ($codeMap[$billingPackage->code] as $settingKey) {
                SystemSetting::set($settingKey, (string) $billingPackage->price, 'billing');
            }
        }

        if ($billingPackage->type === BillingPackage::TYPE_AI_TOKEN && ($billingPackage->code === 'token-1m' || Str::contains($billingPackage->code, 'token'))) {
            SystemSetting::set('ai_token_topup_price', (string) $billingPackage->price, 'billing');
            if ($billingPackage->token_quantity) {
                SystemSetting::set('ai_token_topup_amount', (string) $billingPackage->token_quantity, 'billing');
            }
        }

        if ($billingPackage->type === BillingPackage::TYPE_STORAGE && ($billingPackage->code === 'storage-1gb' || Str::contains($billingPackage->code, 'storage'))) {
            SystemSetting::set('storage_topup_price', (string) $billingPackage->price, 'billing');
            if ($billingPackage->storage_bytes) {
                $gb = (int) round($billingPackage->storage_bytes / 1073741824);
                if ($gb > 0) {
                    SystemSetting::set('storage_topup_gb', (string) $gb, 'billing');
                }
            }
        }

        \Illuminate\Support\Facades\Cache::forget('system_settings');
        \Illuminate\Support\Facades\Cache::forget('billing_all_tier_prices');
    }

    /** @return array<string, mixed> */
    private function validatePackage(Request $request, bool $creating = true): array
    {
        return $request->validate([
            'type' => ['required', 'in:' . implode(',', BillingPackage::TYPES)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'required_if:type,subscription'],
            'token_quantity' => ['nullable', 'integer', 'min:1', 'required_if:type,ai_token'],
            'token_expiry_days' => ['nullable', 'integer', 'min:1', 'required_if:type,ai_token'],
            'storage_gb' => ['nullable', 'numeric', 'min:0.01', 'required_if:type,storage'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    /** @param array<string, mixed> $validated @return array<string, mixed> */
    private function packageData(array $validated, string $code): array
    {
        return [
            'type' => $validated['type'],
            'code' => $code,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'duration_days' => $validated['type'] === BillingPackage::TYPE_SUBSCRIPTION ? $validated['duration_days'] : null,
            'token_quantity' => $validated['type'] === BillingPackage::TYPE_SUBSCRIPTION || $validated['type'] === BillingPackage::TYPE_AI_TOKEN ? ($validated['token_quantity'] ?? null) : null,
            'storage_bytes' => $validated['type'] === BillingPackage::TYPE_STORAGE ? (int) round((float) $validated['storage_gb'] * 1073741824) : null,
            'token_expiry_days' => $validated['type'] === BillingPackage::TYPE_AI_TOKEN ? $validated['token_expiry_days'] : null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ];
    }
}
