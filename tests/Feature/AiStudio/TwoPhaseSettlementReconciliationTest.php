<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Studio\Billing\CreditLedgerService;
use App\Models\AiStudio\AiCreditTransaction;
use App\Models\AiStudio\BusinessInspirationGeneration;
use App\Models\AiStudio\BusinessInspirationProject;
use App\Models\AiStudio\BusinessInspirationPromptVersion;
use App\Models\Business;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class TwoPhaseSettlementReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private CreditLedgerService $ledgerService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);

        $this->user = User::create([
            'name' => 'FinOps Merchant',
            'email' => 'finops@cooca.id',
            'password' => bcrypt('password'),
        ]);

        $this->business = Business::create([
            'name' => 'FinOps Store',
            'ai_credits_balance' => 10000,
        ]);

        $this->ledgerService = new CreditLedgerService;
    }

    public function test_settle_hold_with_lower_actual_usage_refunds_excess_atomically(): void
    {
        $holdKey = 'hold_reconcile_test_' . Str::uuid();

        // 1. Initial balance: 10,000. Hold 1,000 credits -> balance becomes 9,000
        $holdTx = $this->ledgerService->reserveHold(
            business: $this->business,
            credits: 1000,
            idempotencyKey: $holdKey,
            userId: $this->user->id,
            description: 'Hold 1000 credits for image generation'
        );

        $this->assertSame(9000, $this->ledgerService->getBalance($this->business));
        $this->assertSame(-1000, $holdTx->amount);
        $this->assertSame(10000, $holdTx->balance_before);
        $this->assertSame(9000, $holdTx->balance_after);

        // 2. Mock generation with prompt version
        $project = BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'name' => 'Project Reconcile',
            'status' => 'generating',
        ]);

        $promptVersion = BusinessInspirationPromptVersion::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'version_number' => 1,
            'positive_prompt' => 'A glowing facial serum on a podium',
            'aspect_ratio' => '1:1',
        ]);

        $generation = BusinessInspirationGeneration::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'prompt_version_id' => $promptVersion->id,
            'user_id' => $this->user->id,
            'provider' => 'openai',
            'model_name' => 'gpt-image-2.5-flare',
            'status' => 'processing',
            'credit_cost' => 1000,
            'idempotency_key' => $holdKey,
            'batch_count' => 1,
        ]);

        // 3. Settle with actual usage = 847 credits (excess = 1000 - 847 = 153 credits)
        $settledTx = $this->ledgerService->settleHold(
            business: $this->business,
            holdIdempotencyKey: $holdKey,
            generationId: $generation->id,
            actualCreditsUsed: 847,
            exchangeRate: 16000.0,
            apiCostUsd: 0.03125,
            tokensUsage: [
                'prompt_tokens' => 250,
                'completion_tokens' => 1000,
                'total_tokens' => 1250,
            ]
        );

        // Saldo bisnis harus kembali 153 kredit: 9000 + 153 = 9153
        $this->assertSame(9153, $this->ledgerService->getBalance($this->business));

        // Transaksi usage diubah nominalnya menjadi -847
        $this->assertSame(-847, $settledTx->amount);
        $this->assertSame('usage', $settledTx->type);
        $this->assertEquals(16000.0, $settledTx->exchange_rate);
        $this->assertEquals(0.03125, $settledTx->api_cost_usd);

        // Ada transaksi refund tercatat di ledger
        $refundTx = AiCreditTransaction::where('business_id', $this->business->id)
            ->where('type', 'refund')
            ->where('idempotency_key', 'refund_unused_hold_' . $holdKey)
            ->first();

        $this->assertNotNull($refundTx);
        $this->assertSame(153, $refundTx->amount);
        $this->assertSame(9000, $refundTx->balance_before);
        $this->assertSame(9153, $refundTx->balance_after);
    }

    public function test_settle_hold_when_actual_usage_matches_or_exceeds_held_amount(): void
    {
        $holdKey = 'hold_exact_test_' . Str::uuid();

        $this->ledgerService->reserveHold(
            business: $this->business,
            credits: 500,
            idempotencyKey: $holdKey,
            userId: $this->user->id
        );

        $this->assertSame(9500, $this->ledgerService->getBalance($this->business));

        $project = BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'name' => 'Exact Project',
            'status' => 'generating',
        ]);

        $promptVersion = BusinessInspirationPromptVersion::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'version_number' => 1,
            'positive_prompt' => 'Exact prompt test',
            'aspect_ratio' => '1:1',
        ]);

        $generation = BusinessInspirationGeneration::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'prompt_version_id' => $promptVersion->id,
            'provider' => 'openai',
            'model_name' => 'gpt-image-2.5-flare',
            'status' => 'processing',
            'credit_cost' => 500,
            'idempotency_key' => $holdKey,
        ]);

        $settledTx = $this->ledgerService->settleHold(
            business: $this->business,
            holdIdempotencyKey: $holdKey,
            generationId: $generation->id,
            actualCreditsUsed: 500
        );

        $this->assertSame(9500, $this->ledgerService->getBalance($this->business));
        $this->assertSame(-500, $settledTx->amount);

        // Tidak ada refund yang dibuat karena aktual == hold
        $this->assertDatabaseMissing('ai_credit_transactions', [
            'business_id' => $this->business->id,
            'idempotency_key' => 'refund_unused_hold_' . $holdKey,
        ]);
    }
}
