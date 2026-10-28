<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Ai\Studio\Billing\CostQuoteService;
use App\Domain\Ai\Studio\Billing\CreditLedgerService;
use App\Domain\Ai\Studio\Exceptions\InsufficientCreditsException;
use App\Domain\Ai\Studio\Exceptions\InvalidHoldStateException;
use App\Models\AiStudio\AiCreditTransaction;
use App\Models\Business;
use App\Models\User;
use App\Support\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class TwoPhaseCreditLedgerTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $user;
    private CreditLedgerService $ledgerService;
    private CostQuoteService $quoteService;

    protected function setUp(): void
    {
        parent::setUp();
        Context::flush();

        $this->user = User::create([
            'name' => 'Studio Merchant',
            'email' => 'merchant@example.com',
            'password' => 'password123',
        ]);

        $this->business = Business::create([
            'name' => 'Toko Busana Cantik',
            'ai_credits_balance' => 0,
        ]);

        $this->business->users()->attach($this->user->id, [
            'id' => (string) Str::uuid(),
            'role' => 'owner',
            'is_active' => true,
        ]);

        $this->user->update(['active_business_id' => $this->business->id]);
        Context::setBusiness($this->business);

        $this->ledgerService = new CreditLedgerService;
        $this->quoteService = new CostQuoteService($this->ledgerService);
    }

    public function test_can_topup_business_credits_successfully_and_idempotently(): void
    {
        $idempotencyKey = 'topup-initial-10';

        $firstTx = $this->ledgerService->topup(
            business: $this->business,
            credits: 10,
            idempotencyKey: $idempotencyKey,
            userId: $this->user->id,
            description: 'Topup perdana paket 10 kredit'
        );

        $this->assertSame(10, $this->ledgerService->getBalance($this->business));
        $this->assertSame('topup', $firstTx->type);
        $this->assertSame(10, $firstTx->amount);
        $this->assertSame(10, $firstTx->balance_after);

        // Idempotency: eksekusi ulang dengan key yang sama tidak menambah saldo ganda
        $secondTx = $this->ledgerService->topup(
            business: $this->business,
            credits: 10,
            idempotencyKey: $idempotencyKey,
            userId: $this->user->id,
            description: 'Topup retry'
        );

        $this->assertSame($firstTx->id, $secondTx->id);
        $this->assertSame(10, $this->ledgerService->getBalance($this->business));
        $this->assertSame(1, AiCreditTransaction::count());
    }

    public function test_reserve_hold_locks_and_decrements_balance(): void
    {
        $this->ledgerService->topup($this->business, 15, 'topup-15');
        $this->assertSame(15, $this->ledgerService->getBalance($this->business));

        $holdTx = $this->ledgerService->reserveHold(
            business: $this->business,
            credits: 3,
            idempotencyKey: 'hold-job-abc-1',
            userId: $this->user->id,
            description: 'Hold untuk render 3 kredit'
        );

        $this->assertSame(12, $this->ledgerService->getBalance($this->business));
        $this->assertSame('hold', $holdTx->type);
        $this->assertSame(-3, $holdTx->amount);
        $this->assertSame(12, $holdTx->balance_after);
        $this->assertSame('pending', $holdTx->metadata['hold_status'] ?? null);
    }

    public function test_reserve_hold_throws_exception_when_balance_is_insufficient(): void
    {
        $this->ledgerService->topup($this->business, 2, 'topup-2');

        $this->expectException(InsufficientCreditsException::class);

        $this->ledgerService->reserveHold(
            business: $this->business,
            credits: 5,
            idempotencyKey: 'hold-fail-insufficient'
        );

        $this->assertSame(2, $this->ledgerService->getBalance($this->business));
    }

    public function test_reserve_hold_is_strictly_idempotent(): void
    {
        $this->ledgerService->topup($this->business, 10, 'topup-seed-10');

        $holdKey = 'hold-single-idemp-key';
        $firstHold = $this->ledgerService->reserveHold($this->business, 4, $holdKey);
        $secondHold = $this->ledgerService->reserveHold($this->business, 4, $holdKey);

        $this->assertSame($firstHold->id, $secondHold->id);
        // Saldo hanya berkurang 4, bukan 8
        $this->assertSame(6, $this->ledgerService->getBalance($this->business));
    }

    public function test_settle_hold_marks_transaction_as_used_and_links_generation(): void
    {
        $this->ledgerService->topup($this->business, 10, 'topup-seed');
        $holdKey = 'hold-generation-999';
        $this->ledgerService->reserveHold($this->business, 2, $holdKey);

        $generation = $this->createGenerationRecord();
        $settledTx = $this->ledgerService->settleHold($this->business, $holdKey, $generation->id);

        $this->assertSame('usage', $settledTx->type);
        $this->assertSame($generation->id, $settledTx->generation_id);
        $this->assertSame('settled', $settledTx->metadata['hold_status'] ?? null);
        $this->assertSame(8, $this->ledgerService->getBalance($this->business));

        // Idempotent retry settlement
        $retrySettle = $this->ledgerService->settleHold($this->business, $holdKey, $generation->id);
        $this->assertSame($settledTx->id, $retrySettle->id);
    }

    private function createGenerationRecord(): \App\Models\AiStudio\BusinessInspirationGeneration
    {
        $project = \App\Models\AiStudio\BusinessInspirationProject::create([
            'business_id' => $this->business->id,
            'user_id' => $this->user->id,
            'name' => 'Project Test Settle',
            'status' => 'generating',
        ]);

        $promptVersion = \App\Models\AiStudio\BusinessInspirationPromptVersion::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'version_number' => 1,
            'positive_prompt' => 'A photo of a luxury perfume bottle on a stone pedestal',
        ]);

        return \App\Models\AiStudio\BusinessInspirationGeneration::create([
            'business_id' => $this->business->id,
            'project_id' => $project->id,
            'prompt_version_id' => $promptVersion->id,
            'user_id' => $this->user->id,
            'provider' => 'ideogram',
            'model_name' => 'ideogram-v2',
            'status' => 'processing',
            'credit_cost' => 2,
        ]);
    }

    public function test_release_hold_restores_credits_atomically_to_business(): void
    {
        $this->ledgerService->topup($this->business, 10, 'topup-initial');
        $holdKey = 'hold-to-be-cancelled';

        $holdTx = $this->ledgerService->reserveHold($this->business, 4, $holdKey);
        $this->assertSame(6, $this->ledgerService->getBalance($this->business));

        $releaseTx = $this->ledgerService->releaseHold($this->business, $holdKey, 'provider_timeout');

        // Saldo kembali 10
        $this->assertSame(10, $this->ledgerService->getBalance($this->business));
        $this->assertSame('refund', $releaseTx->type);
        $this->assertSame(4, $releaseTx->amount);
        $this->assertSame(10, $releaseTx->balance_after);

        // Status hold berubah menjadi released
        $holdTx->refresh();
        $this->assertSame('released', $holdTx->metadata['hold_status'] ?? null);
    }

    public function test_cannot_release_already_settled_hold(): void
    {
        $this->ledgerService->topup($this->business, 10, 'topup-seed');
        $holdKey = 'hold-already-settled';

        $this->ledgerService->reserveHold($this->business, 3, $holdKey);
        $this->ledgerService->settleHold($this->business, $holdKey);

        $this->expectException(InvalidHoldStateException::class);
        $this->ledgerService->releaseHold($this->business, $holdKey);
    }

    public function test_cannot_settle_already_released_hold(): void
    {
        $this->ledgerService->topup($this->business, 10, 'topup-seed');
        $holdKey = 'hold-already-released';

        $this->ledgerService->reserveHold($this->business, 2, $holdKey);
        $this->ledgerService->releaseHold($this->business, $holdKey);

        $this->expectException(InvalidHoldStateException::class);
        $this->ledgerService->settleHold($this->business, $holdKey);
    }

    public function test_cost_quote_service_calculates_correct_cost_and_dimensions(): void
    {
        // 1. Standard Ideogram 1:1, single image
        $standardQuote = $this->quoteService->quote(
            provider: 'ideogram',
            modelName: 'ideogram-standard',
            aspectRatio: '1:1',
            batchCount: 1,
            enableUpscale: false
        );

        $this->assertSame(1, $standardQuote['credit_cost']);
        $this->assertSame(1, $standardQuote['base_cost_per_image']);
        $this->assertSame(0, $standardQuote['upscale_cost_per_image']);
        $this->assertSame(['width' => 1024, 'height' => 1024], $standardQuote['dimensions']);

        // 2. Pro/HD Ideogram V2 with Upscale 9:16, 2 batch
        $premiumQuote = $this->quoteService->quote(
            provider: 'ideogram',
            modelName: 'ideogram-v2',
            aspectRatio: '9:16',
            batchCount: 2,
            enableUpscale: true
        );

        // base=2, upscale=1 -> cost per image = 3 * 2 batch = 6 credits
        $this->assertSame(6, $premiumQuote['credit_cost']);
        $this->assertSame(2, $premiumQuote['base_cost_per_image']);
        $this->assertSame(1, $premiumQuote['upscale_cost_per_image']);
        $this->assertSame(2, $premiumQuote['batch_count']);
        $this->assertSame(['width' => 1620, 'height' => 2880], $premiumQuote['dimensions']);
    }

    public function test_cost_quote_service_can_afford_check(): void
    {
        $this->ledgerService->topup($this->business, 5, 'topup-5');

        $this->assertTrue($this->quoteService->canAfford($this->business, 5));
        $this->assertTrue($this->quoteService->canAfford($this->business, 3));
        $this->assertFalse($this->quoteService->canAfford($this->business, 6));
    }
}
