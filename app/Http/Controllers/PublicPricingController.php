<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Billing\EarlyAdopterPricingService;
use App\Domain\Billing\EntitlementService;
use App\Models\BillingPackage;
use App\Models\BusinessSubscription;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PublicPricingController extends Controller
{
    public function __construct(
        private readonly EarlyAdopterPricingService $pricingService = new EarlyAdopterPricingService(),
        private readonly EntitlementService $entitlementService = new EntitlementService()
    ) {}

    /**
     * Show the public subscription pricing page with dynamic, single-source commercial rates.
     */
    public function index(Request $request): View
    {
        $tiers = [
            'standard' => BusinessSubscription::TIER_STANDARD,
            'premium'  => BusinessSubscription::TIER_PREMIUM,
            'prestige' => BusinessSubscription::TIER_PRESTIGE,
        ];

        $pricingData = [];
        $activeBatch = null;

        foreach ($tiers as $key => $tier) {
            $monthlyPricing = $this->pricingService->calculatePrice($tier, 'monthly');
            $annualPricing = $this->pricingService->calculatePrice($tier, 'annual');

            if ($monthlyPricing['is_early_adopter'] && $monthlyPricing['batch'] && ! $activeBatch) {
                $activeBatch = $monthlyPricing['batch'];
            }

            $monthlyPrice = (float) $monthlyPricing['price'];
            $annualPrice = (float) $annualPricing['price'];
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
                'is_early_adopter' => $monthlyPricing['is_early_adopter'] || $annualPricing['is_early_adopter'],
                'batch_name' => $monthlyPricing['batch_name'] ?? $annualPricing['batch_name'] ?? null,
                'remaining_quota' => $monthlyPricing['remaining_quota'] ?? $annualPricing['remaining_quota'] ?? null,
            ];
        }

        $annualDiscountBadge = SystemSetting::get('subscription_annual_discount_badge', 'Hemat 2 Bulan');
        $tierLimits = $this->entitlementService->getAllTierLimits();
        $tierFeatures = $this->entitlementService->getAllTierFeatures();

        // Dynamically fetch top-up storage & AI token add-on packages configured in admin/billing-packages
        try {
            $storagePackage = BillingPackage::where('type', BillingPackage::TYPE_STORAGE)->where('is_active', true)->first();
            $aiTokenPackage = BillingPackage::where('type', BillingPackage::TYPE_AI_TOKEN)->where('is_active', true)->first();
        } catch (\Throwable) {
            $storagePackage = null;
            $aiTokenPackage = null;
        }

        $storageTopupPrice = $storagePackage ? (float) $storagePackage->price : (float) SystemSetting::get('storage_topup_price', '50000');
        $storageTopupGb = $storagePackage && $storagePackage->storage_bytes ? (float) round($storagePackage->storage_bytes / 1073741824, 2) : (float) SystemSetting::get('storage_topup_gb', '1');

        $aiTokenTopupPrice = $aiTokenPackage ? (float) $aiTokenPackage->price : (float) SystemSetting::get('ai_token_topup_price', '50000');
        $aiTokenTopupAmount = $aiTokenPackage && $aiTokenPackage->token_quantity ? (int) $aiTokenPackage->token_quantity : (int) SystemSetting::get('ai_token_topup_amount', '1000000');

        $subscriptionAiTokensMonthly = (int) SystemSetting::get('subscription_ai_tokens_monthly', '10000000');

        return view('public.pricing', compact(
            'pricingData',
            'annualDiscountBadge',
            'activeBatch',
            'tierLimits',
            'tierFeatures',
            'storageTopupPrice',
            'storageTopupGb',
            'aiTokenTopupPrice',
            'aiTokenTopupAmount',
            'subscriptionAiTokensMonthly'
        ));
    }
}
