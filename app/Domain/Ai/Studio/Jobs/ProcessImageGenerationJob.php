<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Jobs;

use App\Domain\Ai\Studio\Adapters\OpenAiImageAdapter;
use App\Domain\Ai\Studio\Billing\CreditLedgerService;
use App\Domain\Ai\Studio\Contracts\ImageGenerationProviderInterface;
use App\Domain\Ai\Studio\Storage\AssetStorageManager;
use App\Domain\Currency\ExchangeRateSyncService;
use App\Models\AiStudio\AiModelRegistry;
use App\Models\AiStudio\BusinessInspirationGeneration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessImageGenerationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        public string $generationId
    ) {}

    /**
     * Eksekusi proses generasi gambar secara asinkron di antrean.
     */
    public function handle(
        CreditLedgerService $creditLedgerService,
        AssetStorageManager $storageManager
    ): void {
        /** @var BusinessInspirationGeneration|null $generation */
        $generation = BusinessInspirationGeneration::query()
            ->with(['promptVersion', 'project.assets'])
            ->find($this->generationId);

        if ($generation === null) {
            Log::warning("[ProcessImageGenerationJob] Generation record not found: {$this->generationId}");
            return;
        }

        // Idempotency: jika sudah berstatus selesai atau gagal diproses, lewati
        if (in_array($generation->status, ['completed', 'failed', 'refunded'], true)) {
            Log::info("[ProcessImageGenerationJob] Generation {$this->generationId} already in final status '{$generation->status}', skipping.");
            return;
        }

        $generation->update(['status' => 'processing']);

        $promptVersion = $generation->promptVersion;
        if ($promptVersion === null) {
            $this->failAndReleaseCredits(
                generation: $generation,
                creditLedgerService: $creditLedgerService,
                errorMessage: 'Versi prompt tidak ditemukan untuk generasi ini.'
            );
            return;
        }

        // 1. Resolusi provider adapter
        $providerAdapter = $this->resolveProviderAdapter($generation->provider);

        // 2. Susun payload untuk provider
        $params = (array) ($promptVersion->prompt_parameters ?? []);
        $refImagePath = $this->resolveReferenceImagePath($generation);

        $payload = [
            'model_name' => $generation->model_name,
            'positive_prompt' => $promptVersion->positive_prompt,
            'negative_prompt' => $promptVersion->negative_prompt,
            'aspect_ratio' => $promptVersion->aspect_ratio,
            'width' => (int) ($params['width'] ?? 1024),
            'height' => (int) ($params['height'] ?? 1024),
            'batch_count' => (int) ($generation->batch_count ?? 1),
            'style_preset' => $promptVersion->style_preset,
            'quality' => $params['quality'] ?? null,
            'output_format' => $params['output_format'] ?? 'png',
            'reference_image_path' => $refImagePath,
        ];

        // 3. Panggil API Provider AI
        try {
            $result = $providerAdapter->generate($payload);
        } catch (Throwable $e) {
            Log::error("[ProcessImageGenerationJob] Provider exception on {$this->generationId}: " . $e->getMessage());
            $this->failAndReleaseCredits(
                generation: $generation,
                creditLedgerService: $creditLedgerService,
                errorMessage: 'Provider AI mengalami galat: ' . $e->getMessage()
            );
            return;
        }

        // 4. Periksa hasil respon provider
        if (($result['status'] ?? 'failed') !== 'succeeded' || empty($result['images'])) {
            $errMsg = $result['error_message'] ?? 'Generasi gambar ditolak atau gagal di sisi provider.';
            $this->failAndReleaseCredits(
                generation: $generation,
                creditLedgerService: $creditLedgerService,
                errorMessage: $errMsg
            );
            return;
        }

        // 5. Simpan aset output ke storage tenant
        try {
            foreach ($result['images'] as $imgData) {
                $imgUrl = (string) ($imgData['url'] ?? '');
                if ($imgUrl !== '') {
                    $storageManager->storeGenerationOutput(
                        generation: $generation,
                        imageUrlOrContent: $imgUrl,
                        aspectRatio: $promptVersion->aspect_ratio,
                        metadata: $imgData
                    );
                }
            }
        } catch (Throwable $e) {
            Log::error("[ProcessImageGenerationJob] Asset storage failure for {$this->generationId}: " . $e->getMessage());
            $this->failAndReleaseCredits(
                generation: $generation,
                creditLedgerService: $creditLedgerService,
                errorMessage: 'Gagal mengunduh atau menyimpan gambar hasil render: ' . $e->getMessage()
            );
            return;
        }

        // Simpan token usage ke metadata generasi
        $genMeta = (array) ($generation->generation_meta ?? []);
        if (! empty($result['usage'])) {
            $genMeta['usage'] = $result['usage'];
        }

        // 6. Settle hold kredit finansial dengan rekonsiliasi pemakaian aktual token (Hold -> Settle + Refund Selisih)
        if (! empty($generation->idempotency_key)) {
            try {
                $actualCredits = null;
                $apiCostUsd = null;
                $exchangeRate = app(ExchangeRateSyncService::class)->getUsdToIdrRate();
                $usage = $result['usage'] ?? null;

                if (! empty($usage)) {
                    $textTokens = (int) ($usage['prompt_tokens_details']['text_tokens'] ?? $usage['prompt_tokens'] ?? 0);
                    $cachedTextTokens = (int) ($usage['prompt_tokens_details']['cached_tokens'] ?? 0);
                    $imgInTokens = (int) ($usage['prompt_tokens_details']['image_tokens'] ?? 0);
                    $imgOutTokens = (int) ($usage['completion_tokens_details']['image_tokens'] ?? $usage['completion_tokens'] ?? 0);

                    // Ambil tarif model dari Model Registry jika terdaftar
                    $registry = AiModelRegistry::with('activePricing')
                        ->where('exact_model_id', $generation->model_name)
                        ->orWhere('model_name', $generation->model_name)
                        ->first();

                    if ($registry && $registry->activePricing && $registry->activePricing->billing_mode === 'token') {
                        $p = $registry->activePricing;
                        $textCost = ($textTokens * $p->text_input_rate) / 1_000_000;
                        $cachedTextCost = ($cachedTextTokens * $p->cached_text_rate) / 1_000_000;
                        $imgInCost = ($imgInTokens * $p->image_input_rate) / 1_000_000;
                        $imgOutCost = ($imgOutTokens * $p->image_output_rate) / 1_000_000;
                        $apiCostUsd = $textCost + $cachedTextCost + $imgInCost + $imgOutCost;

                        $costIdr = $apiCostUsd * $exchangeRate;
                        // Margin 35%
                        $calculatedCredits = (int) ceil($costIdr / (1 - 0.35));
                        $actualCredits = min((int) $generation->credit_cost, max(1, $calculatedCredits));

                        $genMeta['finops_settlement'] = [
                            'api_cost_usd' => $apiCostUsd,
                            'exchange_rate' => $exchangeRate,
                            'actual_credits' => $actualCredits,
                            'held_credits' => $generation->credit_cost,
                        ];
                    }
                }

                $creditLedgerService->settleHold(
                    business: $generation->business_id,
                    holdIdempotencyKey: $generation->idempotency_key,
                    generationId: $generation->id,
                    metadata: [
                        'provider' => $generation->provider,
                        'model_name' => $generation->model_name,
                    ],
                    actualCreditsUsed: $actualCredits,
                    exchangeRate: $exchangeRate,
                    apiCostUsd: $apiCostUsd,
                    tokensUsage: $usage
                );
            } catch (Throwable $e) {
                Log::error("[ProcessImageGenerationJob] Settle hold failed for {$this->generationId}: " . $e->getMessage());
            }
        }

        // 7. Perbarui status generasi menjadi completed
        $generation->update([
            'status' => 'completed',
            'completed_at' => now(),
            'provider_job_id' => $result['provider_job_id'] ?? null,
            'latency_ms' => $result['latency_ms'] ?? null,
            'generation_meta' => $genMeta,
        ]);

        // 8. Update status proyek
        $generation->project?->update([
            'status' => 'ready',
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Resolusi jalur file fisik dari aset referensi (jika ada).
     */
    protected function resolveReferenceImagePath(BusinessInspirationGeneration $generation): ?string
    {
        $project = $generation->project;
        if (! $project) {
            return null;
        }

        $assets = $project->assets ?? collect();
        $refAsset = $assets->firstWhere('asset_type', 'reference_style')
            ?? $assets->firstWhere('asset_type', 'original_product');

        if ($refAsset && ! empty($refAsset->file_path)) {
            $fullPath = storage_path('app/public/' . ltrim($refAsset->file_path, '/'));
            if (file_exists($fullPath)) {
                return $fullPath;
            }
        }

        return null;
    }

    /**
     * Pembatalan generasi: tandai gagal dan kembalikan kredit yang di-hold secara atomik.
     */
    protected function failAndReleaseCredits(
        BusinessInspirationGeneration $generation,
        CreditLedgerService $creditLedgerService,
        string $errorMessage
    ): void {
        $generation->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);

        if (! empty($generation->idempotency_key)) {
            try {
                $creditLedgerService->releaseHold(
                    business: $generation->business_id,
                    holdIdempotencyKey: $generation->idempotency_key,
                    reason: $errorMessage
                );
            } catch (Throwable $e) {
                Log::error("[ProcessImageGenerationJob] Auto-release hold failed for {$generation->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Penanganan kegagalan tak tertangani saat worker job crash atau timeout.
     */
    public function failed(Throwable $e): void
    {
        Log::error("[ProcessImageGenerationJob] Job failed fatally on {$this->generationId}: " . $e->getMessage());

        /** @var BusinessInspirationGeneration|null $generation */
        $generation = BusinessInspirationGeneration::find($this->generationId);

        if ($generation !== null && $generation->status !== 'completed') {
            $creditLedgerService = app(CreditLedgerService::class);
            $this->failAndReleaseCredits(
                generation: $generation,
                creditLedgerService: $creditLedgerService,
                errorMessage: 'Job worker timeout atau gagal dieksekusi: ' . $e->getMessage()
            );
        }
    }

    /**
     * Resolusi provider adapter berdasarkan kode nama provider.
     */
    protected function resolveProviderAdapter(string $provider): ImageGenerationProviderInterface
    {
        return match (strtolower($provider)) {
            default => app(OpenAiImageAdapter::class),
        };
    }
}
