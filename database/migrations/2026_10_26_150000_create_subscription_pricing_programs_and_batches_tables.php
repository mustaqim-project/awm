<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Early Adopter Subscription Pricing, Batches, and Grandfathered Price Locks.
     */
    public function up(): void
    {
        // 1. Subscription Pricing Programs (Container for Early Adopter Campaigns)
        Schema::create('subscription_pricing_programs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 64)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active', 'spp_active_idx');
        });

        // 2. Subscription Pricing Batches (Configurable Stages with Quota, Time, Pricing, and Escalation Rules)
        Schema::create('subscription_pricing_batches', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('program_id')->constrained('subscription_pricing_programs')->cascadeOnDelete();
            $table->unsignedInteger('batch_number')->default(1);
            $table->string('name', 150);
            $table->string('status', 32)->default('draft'); // draft, active, completed, expired, cancelled
            $table->string('trigger_mode', 32)->default('hybrid'); // quota_based, time_based, hybrid
            $table->unsignedInteger('quota_limit')->nullable();
            $table->unsignedInteger('quota_used')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->uuid('next_batch_id')->nullable();
            $table->string('fallback_behavior', 32)->default('base_pricing'); // base_pricing, next_batch, stay_active

            // Special Pricing Matrix per Tier
            $table->decimal('standard_monthly_price', 15, 2)->nullable();
            $table->decimal('standard_annual_price', 15, 2)->nullable();
            $table->decimal('premium_monthly_price', 15, 2)->nullable();
            $table->decimal('premium_annual_price', 15, 2)->nullable();
            $table->decimal('prestige_monthly_price', 15, 2)->nullable();
            $table->decimal('prestige_annual_price', 15, 2)->nullable();

            // Annual Price Escalation Policy (Max 10.00% enforced by business rules & constraints)
            $table->decimal('annual_increase_percentage', 5, 2)->default(0.00);
            $table->string('annual_increase_basis', 32)->default('subscription_anniversary'); // subscription_anniversary, calendar_anniversary, fixed_date
            $table->unsignedTinyInteger('annual_increase_effective_month')->nullable();
            $table->unsignedTinyInteger('annual_increase_effective_day')->nullable();
            $table->string('rounding_mode', 32)->default('none'); // none, nearest_1000, nearest_100, ceiling_1000

            // Resubscribe / Cancellation Policy
            $table->string('resubscribe_policy', 32)->default('grace_period'); // grace_period, immediate_forfeit, always_retain
            $table->unsignedInteger('resubscribe_grace_days')->default(7);
            $table->string('upgrade_downgrade_policy', 32)->default('inherit_active_batch'); // inherit_active_batch, retain_grandfathered_discount, forfeit_to_normal

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['program_id', 'status'], 'spb_prog_status_idx');
            $table->index(['status', 'batch_number'], 'spb_status_batch_idx');
        });

        // 3. Subscription Price Histories (Audit Trail for All Price Movements)
        Schema::create('subscription_price_histories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignUuid('business_subscription_id')->constrained('business_subscriptions')->cascadeOnDelete();
            $table->uuid('pricing_batch_id')->nullable();
            $table->string('event_type', 64); // initial_lock, annual_escalation, manual_admin_override, upgrade_downgrade, forfeited, cycle_switch, resubscribe_restored
            $table->decimal('previous_price', 15, 2)->default(0);
            $table->decimal('new_price', 15, 2)->default(0);
            $table->string('previous_plan_code', 64)->nullable();
            $table->string('new_plan_code', 64)->nullable();
            $table->decimal('increase_percentage', 5, 2)->default(0.00);
            $table->date('effective_date');
            $table->string('performed_by_type', 64)->default('system_scheduler'); // system_scheduler, admin, tenant_checkout
            $table->uuid('performed_by_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'created_at'], 'sph_biz_created_idx');
            $table->index(['business_subscription_id', 'event_type'], 'sph_sub_event_idx');
        });

        // 4. Augment business_subscriptions with Price Lock metadata
        Schema::table('business_subscriptions', function (Blueprint $table): void {
            $table->uuid('pricing_program_id')->nullable()->after('last_token_reset_at');
            $table->uuid('pricing_batch_id')->nullable()->after('pricing_program_id');
            $table->decimal('locked_price_monthly', 15, 2)->nullable()->after('pricing_batch_id');
            $table->decimal('locked_price_annual', 15, 2)->nullable()->after('locked_price_monthly');
            $table->string('locked_tier', 32)->nullable()->after('locked_price_annual');
            $table->string('locked_cycle', 32)->nullable()->after('locked_tier');
            $table->dateTime('special_rate_starts_at')->nullable()->after('locked_cycle');
            $table->dateTime('special_rate_ends_at')->nullable()->after('special_rate_starts_at');
            $table->decimal('annual_increase_percentage', 5, 2)->default(0.00)->after('special_rate_ends_at');
            $table->date('next_price_increase_at')->nullable()->after('annual_increase_percentage');
            $table->date('last_price_increase_at')->nullable()->after('next_price_increase_at');
            $table->boolean('is_price_lock_eligible')->default(true)->after('last_price_increase_at');
            $table->dateTime('price_lock_forfeited_at')->nullable()->after('is_price_lock_eligible');
            $table->string('price_lock_forfeited_reason', 255)->nullable()->after('price_lock_forfeited_at');

            $table->index(['pricing_batch_id', 'is_price_lock_eligible'], 'bs_batch_eligible_idx');
            $table->index(['is_price_lock_eligible', 'next_price_increase_at'], 'bs_escalation_idx');
        });

        // 5. Augment subscription_payments with Early Adopter tracking
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->uuid('pricing_program_id')->nullable()->after('promo_id');
            $table->uuid('pricing_batch_id')->nullable()->after('pricing_program_id');
            $table->boolean('is_early_adopter')->default(false)->after('pricing_batch_id');
            $table->decimal('original_base_price', 15, 2)->nullable()->after('is_early_adopter');

            $table->index('pricing_batch_id', 'sp_batch_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table): void {
            $table->dropIndex('sp_batch_idx');
            $table->dropColumn(['pricing_program_id', 'pricing_batch_id', 'is_early_adopter', 'original_base_price']);
        });

        Schema::table('business_subscriptions', function (Blueprint $table): void {
            $table->dropIndex('bs_batch_eligible_idx');
            $table->dropIndex('bs_escalation_idx');
            $table->dropColumn([
                'pricing_program_id',
                'pricing_batch_id',
                'locked_price_monthly',
                'locked_price_annual',
                'locked_tier',
                'locked_cycle',
                'special_rate_starts_at',
                'special_rate_ends_at',
                'annual_increase_percentage',
                'next_price_increase_at',
                'last_price_increase_at',
                'is_price_lock_eligible',
                'price_lock_forfeited_at',
                'price_lock_forfeited_reason',
            ]);
        });

        Schema::dropIfExists('subscription_price_histories');
        Schema::dropIfExists('subscription_pricing_batches');
        Schema::dropIfExists('subscription_pricing_programs');
    }
};
