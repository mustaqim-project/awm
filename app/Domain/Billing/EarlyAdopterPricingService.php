<?php

declare(strict_types=1);

namespace App\Domain\Billing;

use App\Models\AuditLog;
use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPriceHistory;
use App\Models\SubscriptionPricingBatch;
use App\Models\SubscriptionPricingProgram;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class EarlyAdopterPricingService
{
    public const MAX_ANNUAL_INCREASE_PERCENTAGE = 10.00;

    /**
     * Get the official base price for a given tier and billing cycle.
     */
    public function getBasePrice(string $tier, string $cycle = 'monthly'): float
    {
        $tier = strtolower($tier);
        $isAnnual = $cycle === 'annual';

        return match ($tier) {
            BusinessSubscription::TIER_PRESTIGE => (float) (
                $isAnnual
                    ? SystemSetting::get('subscription_price_prestige_annual', (string) EntitlementService::PRICE_PRESTIGE_ANNUAL)
                    : SystemSetting::get('subscription_price_prestige_monthly', (string) EntitlementService::PRICE_PRESTIGE_MONTHLY)
            ),
            BusinessSubscription::TIER_PREMIUM => (float) (
                $isAnnual
                    ? SystemSetting::get('subscription_price_premium_annual', (string) EntitlementService::PRICE_PREMIUM_ANNUAL)
                    : SystemSetting::get('subscription_price_premium_monthly', (string) EntitlementService::PRICE_PREMIUM_MONTHLY)
            ),
            default => (float) (
                $isAnnual
                    ? SystemSetting::get('subscription_price_standard_annual', (string) SystemSetting::get('subscription_price_annual', (string) EntitlementService::PRICE_STANDARD_ANNUAL))
                    : SystemSetting::get('subscription_price_standard_monthly', (string) SystemSetting::get('subscription_price_monthly', (string) EntitlementService::PRICE_STANDARD_MONTHLY))
            ),
        };
    }

    /**
     * Get active Early Adopter batch for a specific tier & cycle, executing auto-transitions if needed.
     */
    public function getActiveBatchForTier(string $tier, string $cycle = 'monthly'): ?SubscriptionPricingBatch
    {
        $tier = strtolower($tier);

        // Find candidate active batches
        $batches = SubscriptionPricingBatch::where('status', SubscriptionPricingBatch::STATUS_ACTIVE)
            ->whereHas('program', fn ($q) => $q->where('is_active', true))
            ->orderBy('batch_number')
            ->get();

        foreach ($batches as $batch) {
            // Check if batch is configured with a price for this tier & cycle
            if (! $batch->hasSpecialPriceFor($tier, $cycle)) {
                continue;
            }

            // Check if batch should transition due to quota or end date
            if ($batch->shouldTransition()) {
                $next = $this->transitionBatch($batch);
                if ($next && $next->hasSpecialPriceFor($tier, $cycle) && ! $next->shouldTransition()) {
                    return $next;
                }
                continue;
            }

            if ($batch->hasStarted() && ! $batch->isTimeExpired() && ! $batch->isQuotaExceeded()) {
                return $batch;
            }
        }

        return null;
    }

    /**
     * Atomically reserve a quota slot for an early adopter batch within a DB transaction.
     */
    public function reserveQuotaAtomically(SubscriptionPricingBatch $batch): bool
    {
        return DB::transaction(function () use ($batch): bool {
            /** @var SubscriptionPricingBatch|null $locked */
            $locked = SubscriptionPricingBatch::where('id', $batch->id)
                ->lockForUpdate()
                ->first();

            if (! $locked || ! $locked->isActive()) {
                return false;
            }

            if ($locked->isQuotaExceeded() || $locked->isTimeExpired()) {
                $this->transitionBatch($locked);
                return false;
            }

            $locked->increment('quota_used');

            // If this increment triggered completion of the batch quota, transition to next batch
            if ($locked->shouldTransition()) {
                $this->transitionBatch($locked);
            }

            return true;
        });
    }

    /**
     * Transition a batch to completed/expired and activate next batch if configured.
     */
    public function transitionBatch(SubscriptionPricingBatch $batch): ?SubscriptionPricingBatch
    {
        return DB::transaction(function () use ($batch): ?SubscriptionPricingBatch {
            $freshBatch = SubscriptionPricingBatch::where('id', $batch->id)
                ->lockForUpdate()
                ->first();

            if (! $freshBatch) {
                return null;
            }

            $newStatus = $freshBatch->isTimeExpired()
                ? SubscriptionPricingBatch::STATUS_EXPIRED
                : SubscriptionPricingBatch::STATUS_COMPLETED;

            $freshBatch->update(['status' => $newStatus]);

            Log::info("Early Adopter Batch #{$freshBatch->batch_number} ({$freshBatch->name}) transitioned to {$newStatus}. Used: {$freshBatch->quota_used}/{$freshBatch->quota_limit}.");

            // Activate next batch if linked
            if ($freshBatch->next_batch_id) {
                /** @var SubscriptionPricingBatch|null $nextBatch */
                $nextBatch = SubscriptionPricingBatch::where('id', $freshBatch->next_batch_id)
                    ->lockForUpdate()
                    ->first();

                if ($nextBatch && in_array($nextBatch->status, [SubscriptionPricingBatch::STATUS_DRAFT, SubscriptionPricingBatch::STATUS_ACTIVE], true)) {
                    $nextBatch->update([
                        'status' => SubscriptionPricingBatch::STATUS_ACTIVE,
                        'starts_at' => $nextBatch->starts_at ?? Carbon::now(),
                    ]);

                    Log::info("Early Adopter Next Batch #{$nextBatch->batch_number} ({$nextBatch->name}) has been activated automatically.");

                    return $nextBatch;
                }
            }

            Log::info("No next Early Adopter batch configured. Fallback behavior engaged: {$freshBatch->fallback_behavior}.");

            return null;
        });
    }

    /**
     * Calculate effective subscription price for a business, factoring in:
     * 1. Grandfathered price lock (for renewals)
     * 2. Active Early Adopter batch (for new subscribers or qualifying renewals)
     * 3. Base standard pricing (fallback)
     *
     * @return array<string, mixed>
     */
    public function calculatePrice(string $tier, string $cycle = 'monthly', ?Business $business = null): array
    {
        $tier = strtolower($tier);
        $basePrice = $this->getBasePrice($tier, $cycle);

        // 1. Check Grandfathered Price Lock on existing subscription
        if ($business !== null) {
            $subscription = $business->subscription;

            if ($subscription && $subscription->hasPriceLock() && $subscription->isPriceLockActive()) {
                $lockedTier = $subscription->locked_tier ?? $subscription->getTier();

                // If renewing the same tier: tenant retains their grandfathered price lock!
                if ($lockedTier === $tier) {
                    $lockedPrice = $subscription->getLockedPriceForCycle($cycle);

                    // If renewal cycle price is locked, use it
                    if ($lockedPrice !== null && $lockedPrice > 0) {
                        return [
                            'price' => (float) $lockedPrice,
                            'normal_price' => $basePrice,
                            'original_base_price' => $basePrice,
                            'savings' => max(0.0, $basePrice - (float) $lockedPrice),
                            'source' => 'locked_price',
                            'reason' => 'grandfathered_price_lock',
                            'is_early_adopter' => true,
                            'is_price_locked' => true,
                            'is_grandfathered' => true,
                            'program_id' => $subscription->pricing_program_id,
                            'batch_id' => $subscription->pricing_batch_id,
                            'batch_name' => $subscription->batch?->name ?? 'Grandfathered Batch',
                            'batch' => $subscription->batch,
                            'program' => $subscription->program,
                            'annual_increase_percentage' => (float) $subscription->annual_increase_percentage,
                            'annual_increase_basis' => $subscription->annual_increase_basis,
                            'remaining_quota' => null,
                        ];
                    }
                }
            }
        }

        // 2. Check for active Early Adopter batch
        $activeBatch = $this->getActiveBatchForTier($tier, $cycle);

        if ($activeBatch !== null) {
            $batchPrice = $activeBatch->getPriceForTier($tier, $cycle);

            if ($batchPrice !== null) {
                $remainingQuota = $activeBatch->quota_limit !== null
                    ? max(0, $activeBatch->quota_limit - $activeBatch->quota_used)
                    : null;

                return [
                    'price' => (float) $batchPrice,
                    'normal_price' => $basePrice,
                    'original_base_price' => $basePrice,
                    'savings' => max(0.0, $basePrice - (float) $batchPrice),
                    'source' => 'early_adopter_batch',
                    'reason' => 'active_early_adopter_batch',
                    'is_early_adopter' => true,
                    'is_price_locked' => false,
                    'is_grandfathered' => false,
                    'program_id' => $activeBatch->program_id,
                    'batch_id' => $activeBatch->id,
                    'batch_name' => $activeBatch->name,
                    'batch_number' => $activeBatch->batch_number,
                    'batch' => $activeBatch,
                    'program' => $activeBatch->program,
                    'annual_increase_percentage' => (float) $activeBatch->annual_increase_percentage,
                    'annual_increase_basis' => $activeBatch->annual_increase_basis,
                    'remaining_quota' => $remainingQuota,
                ];
            }
        }

        // 3. Fallback to normal Base Pricing
        return [
            'price' => $basePrice,
            'normal_price' => $basePrice,
            'original_base_price' => $basePrice,
            'savings' => 0.0,
            'source' => 'base_pricing',
            'reason' => 'base_pricing',
            'is_early_adopter' => false,
            'is_price_locked' => false,
            'is_grandfathered' => false,
            'program_id' => null,
            'batch_id' => null,
            'batch_name' => null,
            'batch_number' => null,
            'batch' => null,
            'program' => null,
            'annual_increase_percentage' => 0.0,
            'annual_increase_basis' => null,
            'remaining_quota' => null,
        ];
    }

    /**
     * Apply price lock and escalation metadata to BusinessSubscription upon payment approval.
     */
    public function applyPriceLockToSubscription(BusinessSubscription $subscription, SubscriptionPayment $payment): void
    {
        if (! $payment->is_early_adopter || empty($payment->pricing_batch_id)) {
            return;
        }

        /** @var SubscriptionPricingBatch|null $batch */
        $batch = SubscriptionPricingBatch::find($payment->pricing_batch_id);
        if (! $batch) {
            return;
        }

        $tier = $subscription->getTier();
        $lockedMonthly = $batch->getPriceForTier($tier, 'monthly');
        $lockedAnnual = $batch->getPriceForTier($tier, 'annual');
        $annualPct = min(self::MAX_ANNUAL_INCREASE_PERCENTAGE, max(0.0, (float) $batch->annual_increase_percentage));

        // Determine next price increase anniversary date
        $nextIncreaseAt = match ($batch->annual_increase_basis) {
            SubscriptionPricingBatch::BASIS_FIXED_DATE => (
                $batch->annual_increase_effective_month && $batch->annual_increase_effective_day
                    ? Carbon::create(
                        (int) Carbon::now()->addYear()->year,
                        $batch->annual_increase_effective_month,
                        $batch->annual_increase_effective_day
                    )->toDateString()
                    : Carbon::now()->addYear()->toDateString()
            ),
            default => Carbon::now()->addYear()->toDateString(),
        };

        $subscription->update([
            'pricing_program_id' => $batch->program_id,
            'pricing_batch_id' => $batch->id,
            'locked_price_monthly' => $lockedMonthly,
            'locked_price_annual' => $lockedAnnual,
            'locked_tier' => $tier,
            'locked_cycle' => $payment->cycle,
            'special_rate_starts_at' => Carbon::now(),
            'special_rate_ends_at' => $batch->ends_at,
            'annual_increase_percentage' => $annualPct,
            'next_price_increase_at' => $nextIncreaseAt,
            'is_price_lock_eligible' => true,
            'price_lock_forfeited_at' => null,
            'price_lock_forfeited_reason' => null,
        ]);

        // Record Initial Lock Event in Price History
        SubscriptionPriceHistory::create([
            'business_id' => $subscription->business_id,
            'business_subscription_id' => $subscription->id,
            'pricing_batch_id' => $batch->id,
            'event_type' => SubscriptionPriceHistory::EVENT_INITIAL_LOCK,
            'previous_price' => $payment->original_base_price ?? $payment->amount,
            'new_price' => $payment->amount,
            'previous_plan_code' => $subscription->plan_code,
            'new_plan_code' => $subscription->plan_code,
            'increase_percentage' => 0.00,
            'effective_date' => Carbon::today(),
            'performed_by_type' => 'tenant_checkout',
            'performed_by_id' => $payment->user_id,
            'notes' => "Harga khusus Early Adopter berhasil dikunci via Batch #{$batch->batch_number} ({$batch->name}).",
            'metadata' => [
                'order_number' => $payment->order_number,
                'tier' => $tier,
                'cycle' => $payment->cycle,
                'batch_name' => $batch->name,
                'annual_increase_percentage' => $annualPct,
            ],
        ]);
    }

    /**
     * Process annual price escalations (0% to max 10%) for eligible subscriptions.
     * Prevents duplicate increases in the same annual period (idempotent).
     *
     * @return array{
     *     processed_count: int,
     *     updated_subscriptions: array<int, array{business_id: string, old_price: float, new_price: float, percentage: float}>
     * }
     */
    public function processAnnualPriceEscalations(bool $dryRun = false): array
    {
        $today = Carbon::today();
        $thisYearStart = $today->copy()->startOfYear();

        // Query eligible subscriptions where next increase date has been reached
        $subscriptions = BusinessSubscription::where('is_price_lock_eligible', true)
            ->whereNotNull('pricing_batch_id')
            ->where('annual_increase_percentage', '>', 0)
            ->whereNotNull('next_price_increase_at')
            ->where('next_price_increase_at', '<=', $today->toDateString())
            ->where(function ($q) use ($thisYearStart) {
                // Idempotency: skip if already increased during this calendar anniversary cycle
                $q->whereNull('last_price_increase_at')
                    ->orWhere('last_price_increase_at', '<', $thisYearStart->toDateString());
            })
            ->with(['batch', 'business'])
            ->get();

        $processed = [];

        foreach ($subscriptions as $sub) {
            $pct = min(self::MAX_ANNUAL_INCREASE_PERCENTAGE, (float) $sub->annual_increase_percentage);
            if ($pct <= 0) {
                continue;
            }

            $oldPrice = (float) $sub->price;
            $rawNewPrice = $oldPrice * (1 + ($pct / 100));

            $batch = $sub->batch;
            $newPrice = $batch ? $batch->calculateRoundedPrice($rawNewPrice) : round($rawNewPrice, 2);

            $processed[] = [
                'business_id' => $sub->business_id,
                'subscription_id' => $sub->id,
                'business_name' => $sub->business?->name ?? 'Unknown',
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'percentage' => $pct,
            ];

            if (! $dryRun) {
                DB::transaction(function () use ($sub, $oldPrice, $newPrice, $pct, $today): void {
                    $nextIncrease = $today->copy()->addYear();

                    $sub->update([
                        'price' => $newPrice,
                        'last_price_increase_at' => $today->toDateString(),
                        'next_price_increase_at' => $nextIncrease->toDateString(),
                    ]);

                    SubscriptionPriceHistory::create([
                        'business_id' => $sub->business_id,
                        'business_subscription_id' => $sub->id,
                        'pricing_batch_id' => $sub->pricing_batch_id,
                        'event_type' => SubscriptionPriceHistory::EVENT_ANNUAL_ESCALATION,
                        'previous_price' => $oldPrice,
                        'new_price' => $newPrice,
                        'previous_plan_code' => $sub->plan_code,
                        'new_plan_code' => $sub->plan_code,
                        'increase_percentage' => $pct,
                        'effective_date' => $today,
                        'performed_by_type' => 'system_scheduler',
                        'performed_by_id' => null,
                        'notes' => "Kenaikan harga tahunan otomatis {$pct}% diterapkan sesuai kebijakan Early Adopter.",
                        'metadata' => [
                            'old_price' => $oldPrice,
                            'new_price' => $newPrice,
                            'percentage' => $pct,
                            'next_increase_at' => $nextIncrease->toDateString(),
                        ],
                    ]);

                    Log::info("Eskalasi harga tahunan berhasil: Bisnis {$sub->business_id} naik dari Rp {$oldPrice} menjadi Rp {$newPrice} (+{$pct}%).");
                });
            }
        }

        return [
            'processed_count' => count($processed),
            'updated_subscriptions' => $processed,
        ];
    }

    /**
     * Handle subscription lapse / expiration. If expired beyond grace period, forfeit price lock.
     */
    public function handleSubscriptionLapse(BusinessSubscription $subscription): void
    {
        if (! $subscription->hasPriceLock()) {
            return;
        }

        $batch = $subscription->batch;
        $policy = $batch?->resubscribe_policy ?? SubscriptionPricingBatch::POLICY_RESUBSCRIBE_GRACE;

        if ($policy === SubscriptionPricingBatch::POLICY_RESUBSCRIBE_RETAIN) {
            return; // Customer always retains price
        }

        $graceDays = $batch?->resubscribe_grace_days ?? 7;
        $cutoffDate = $subscription->ends_at?->copy()->addDays($graceDays);

        if ($policy === SubscriptionPricingBatch::POLICY_RESUBSCRIBE_FORFEIT || ($cutoffDate && Carbon::now()->greaterThan($cutoffDate))) {
            $subscription->update([
                'is_price_lock_eligible' => false,
                'price_lock_forfeited_at' => Carbon::now(),
                'price_lock_forfeited_reason' => "Langganan kedaluwarsa melewati masa tenggang toleransi ({$graceDays} hari).",
            ]);

            SubscriptionPriceHistory::create([
                'business_id' => $subscription->business_id,
                'business_subscription_id' => $subscription->id,
                'pricing_batch_id' => $subscription->pricing_batch_id,
                'event_type' => SubscriptionPriceHistory::EVENT_FORFEITED,
                'previous_price' => (float) $subscription->price,
                'new_price' => $this->getBasePrice($subscription->getTier(), 'monthly'),
                'previous_plan_code' => $subscription->plan_code,
                'new_plan_code' => $subscription->plan_code,
                'increase_percentage' => 0.00,
                'effective_date' => Carbon::today(),
                'performed_by_type' => 'system_scheduler',
                'notes' => "Hak harga khusus Early Adopter hangus karena masa aktif kedaluwarsa melebihi batas toleransi {$graceDays} hari.",
            ]);
        }
    }

    /**
     * Validate that an annual increase percentage does not exceed the maximum allowed limit (10.00%).
     *
     * @throws InvalidArgumentException
     */
    public static function validateAnnualIncreasePercentage(float $percentage): void
    {
        if ($percentage < 0.0 || $percentage > self::MAX_ANNUAL_INCREASE_PERCENTAGE) {
            throw new InvalidArgumentException(
                "Persentase kenaikan tahunan tidak boleh melebihi " . self::MAX_ANNUAL_INCREASE_PERCENTAGE . "% (diberikan: {$percentage}%)."
            );
        }
    }
}
