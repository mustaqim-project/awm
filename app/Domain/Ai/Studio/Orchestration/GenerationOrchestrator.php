<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Orchestration;

use App\Domain\Ai\Studio\Billing\CostQuoteService;
use App\Domain\Ai\Studio\Billing\CreditLedgerService;
use App\Domain\Ai\Studio\Compilers\PromptCompilerService;
use App\Domain\Ai\Studio\Jobs\ProcessImageGenerationJob;
use App\Models\AiStudio\BusinessInspirationGeneration;
use App\Models\AiStudio\BusinessInspirationProject;
use App\Models\AiStudio\BusinessInspirationPromptVersion;
use App\Models\Business;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class GenerationOrchestrator
{
    public function __construct(
        protected CreditLedgerService $creditLedgerService = new CreditLedgerService,
        protected CostQuoteService $costQuoteService = new CostQuoteService,
        protected PromptCompilerService $promptCompilerService = new PromptCompilerService
    ) {}

    /**
     * Orkestrasi siklus hidup pembuatan gambar AI Studio:
     * Validasi kepemilikan -> Kompilasi Prompt -> Hitung Biaya -> Hold Kredit Dua Tahap -> Dispatch Job.
     *
     * @param array<string, mixed> $options
     * @throws Throwable
     */
    public function dispatchGeneration(
        Business $business,
        BusinessInspirationProject $project,
        array $options = []
    ): BusinessInspirationGeneration {
        // 1. Validasi Isolasi Multi-Tenant
        if ($project->business_id !== $business->id) {
            throw new InvalidArgumentException('Proyek tidak terdaftar pada bisnis yang aktif.');
        }

        $userId = (string) ($options['user_id'] ?? $project->user_id ?? '');
        $userId = $userId !== '' ? $userId : null;

        // 2. Dapatkan atau Kompilasi Versi Prompt
        $promptVersion = $this->resolvePromptVersion($business, $project, $options);

        // 3. Hitung Kebutuhan Kredit Finansial
        $provider = (string) ($options['provider'] ?? 'openai');
        $modelName = (string) ($options['model_name'] ?? 'dall-e-3');
        $aspectRatio = (string) ($options['aspect_ratio'] ?? $promptVersion->aspect_ratio ?? '1:1');
        $batchCount = (int) ($options['batch_count'] ?? 1);
        $enableUpscale = (bool) ($options['enable_upscale'] ?? false);

        $quote = $this->costQuoteService->quote(
            provider: $provider,
            modelName: $modelName,
            aspectRatio: $aspectRatio,
            batchCount: $batchCount,
            enableUpscale: $enableUpscale
        );

        $creditCost = $quote['credit_cost'];

        // 4. Kunci Idempotensi & Hold Kredit Finansial (Tahap 1)
        $idempotencyKey = (string) ($options['idempotency_key'] ?? ('gen_hold_' . Str::uuid()));

        $this->creditLedgerService->reserveHold(
            business: $business,
            credits: $creditCost,
            idempotencyKey: $idempotencyKey,
            userId: $userId,
            description: "Hold {$creditCost} kredit untuk proyek '{$project->name}'",
            metadata: [
                'project_id' => $project->id,
                'prompt_version_id' => $promptVersion->id,
                'provider' => $provider,
                'model_name' => $modelName,
            ]
        );

        // 5. Buat Entri Transaksi Generasi di Database
        try {
            /** @var BusinessInspirationGeneration $generation */
            $generation = BusinessInspirationGeneration::create([
                'business_id' => $business->id,
                'project_id' => $project->id,
                'prompt_version_id' => $promptVersion->id,
                'user_id' => $userId,
                'provider' => $provider,
                'model_name' => $modelName,
                'status' => 'queued',
                'credit_cost' => $creditCost,
                'idempotency_key' => $idempotencyKey,
                'batch_count' => $batchCount,
                'generation_meta' => [
                    'quote' => $quote,
                    'options' => $options,
                ],
            ]);

            $project->update([
                'status' => 'generating',
                'last_activity_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Rollback hold kredit jika penyimpanan generation gagal
            $this->creditLedgerService->releaseHold(
                business: $business,
                holdIdempotencyKey: $idempotencyKey,
                reason: 'Gagal membuat rekaman generasi: ' . $e->getMessage()
            );
            throw $e;
        }

        // 6. Jalankan Antrean Asinkron (atau Synchronous jika opsi sync aktif)
        $isSync = (bool) ($options['sync'] ?? false);

        if ($isSync) {
            ProcessImageGenerationJob::dispatchSync($generation->id);
            $generation->refresh();
        } else {
            ProcessImageGenerationJob::dispatch($generation->id);
        }

        return $generation;
    }

    /**
     * Resolusi versi prompt: gunakan versi yang sudah ada atau kompilasi versi baru.
     *
     * @param array<string, mixed> $options
     */
    protected function resolvePromptVersion(
        Business $business,
        BusinessInspirationProject $project,
        array $options
    ): BusinessInspirationPromptVersion {
        if (! empty($options['prompt_version_id'])) {
            /** @var BusinessInspirationPromptVersion $existing */
            $existing = BusinessInspirationPromptVersion::query()
                ->where('business_id', $business->id)
                ->where('project_id', $project->id)
                ->findOrFail($options['prompt_version_id']);

            return $existing;
        }

        // Ambil produk dan referensi inspirasi terkait proyek
        $product = $project->product()->first();
        $reference = $project->inspirationItem()->first();

        $compiled = $this->promptCompilerService->compile(
            business: $business,
            product: $product,
            reference: $reference,
            brief: $options
        );

        $nextVersion = (int) BusinessInspirationPromptVersion::query()
            ->where('project_id', $project->id)
            ->max('version_number') + 1;

        return BusinessInspirationPromptVersion::create([
            'business_id' => $business->id,
            'project_id' => $project->id,
            'version_number' => $nextVersion,
            'positive_prompt' => $compiled['positive_prompt'],
            'negative_prompt' => $compiled['negative_prompt'],
            'style_preset' => $compiled['style_preset'],
            'aspect_ratio' => $compiled['aspect_ratio'],
            'prompt_parameters' => $compiled['prompt_parameters'],
            'is_approved' => $compiled['guardrail_status'] === 'passed',
            'guardrail_status' => $compiled['guardrail_status'],
            'guardrail_notes' => $compiled['guardrail_notes'],
            'created_by' => $options['user_id'] ?? $project->user_id,
        ]);
    }
}
