<?php

declare(strict_types=1);

namespace App\Domain\Ai\Studio\Billing;

use App\Domain\Ai\Studio\Exceptions\CreditHoldNotFoundException;
use App\Domain\Ai\Studio\Exceptions\InsufficientCreditsException;
use App\Domain\Ai\Studio\Exceptions\InvalidHoldStateException;
use App\Models\AiStudio\AiCreditTransaction;
use App\Models\Business;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreditLedgerService
{
    /**
     * Dapatkan saldo kredit AI Studio saat ini untuk bisnis.
     */
    public function getBalance(string|Business $business): int
    {
        $businessId = $business instanceof Business ? $business->id : $business;

        /** @var Business $biz */
        $biz = Business::query()->findOrFail($businessId);

        return (int) ($biz->ai_credits_balance ?? 0);
    }

    /**
     * Top-up saldo kredit AI Studio secara atomik.
     */
    public function topup(
        string|Business $business,
        int $credits,
        string $idempotencyKey,
        ?string $userId = null,
        string $description = 'Topup saldo kredit AI Studio',
        array $metadata = []
    ): AiCreditTransaction {
        if ($credits <= 0) {
            throw new InvalidArgumentException('Jumlah kredit top-up harus lebih besar dari 0.');
        }

        $businessId = $business instanceof Business ? $business->id : $business;

        return DB::transaction(function () use ($businessId, $credits, $idempotencyKey, $userId, $description, $metadata): AiCreditTransaction {
            // Cek idempotency agar tidak terjadi duplikasi topup
            $existing = AiCreditTransaction::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            /** @var Business $biz */
            $biz = Business::query()->where('id', $businessId)->lockForUpdate()->firstOrFail();

            $biz->ai_credits_balance = (int) ($biz->ai_credits_balance ?? 0) + $credits;
            $biz->save();

            return AiCreditTransaction::create([
                'business_id' => $biz->id,
                'user_id' => $userId,
                'generation_id' => null,
                'type' => 'topup',
                'amount' => $credits,
                'balance_after' => $biz->ai_credits_balance,
                'idempotency_key' => $idempotencyKey,
                'description' => $description,
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Fase 1: Reserve hold kredit (Pessimistic Lock & Idempotent).
     * Kredit langsung dipotong sementara dari saldo bisnis untuk mengunci batas aman,
     * dan dicatat sebagai transaksi hold 'pending'.
     */
    public function reserveHold(
        string|Business $business,
        int $credits,
        string $idempotencyKey,
        ?string $userId = null,
        ?string $description = null,
        array $metadata = []
    ): AiCreditTransaction {
        if ($credits <= 0) {
            throw new InvalidArgumentException('Jumlah kredit yang di-hold harus lebih besar dari 0.');
        }

        $businessId = $business instanceof Business ? $business->id : $business;

        return DB::transaction(function () use ($businessId, $credits, $idempotencyKey, $userId, $description, $metadata): AiCreditTransaction {
            // Idempotent retry: jika sudah pernah di-hold dengan key ini, kembalikan transaksi eksis
            $existing = AiCreditTransaction::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            /** @var Business $biz */
            $biz = Business::query()->where('id', $businessId)->lockForUpdate()->firstOrFail();
            $currentBalance = (int) ($biz->ai_credits_balance ?? 0);

            if ($currentBalance < $credits) {
                throw new InsufficientCreditsException($credits, $currentBalance);
            }

            $biz->ai_credits_balance = $currentBalance - $credits;
            $biz->save();

            $holdMeta = array_merge($metadata, [
                'hold_status' => 'pending',
                'held_credits' => $credits,
                'reserved_at' => now()->toIso8601String(),
            ]);

            return AiCreditTransaction::create([
                'business_id' => $biz->id,
                'user_id' => $userId,
                'generation_id' => null,
                'type' => 'hold',
                'amount' => -$credits,
                'balance_after' => $biz->ai_credits_balance,
                'idempotency_key' => $idempotencyKey,
                'description' => $description ?? "Reservasi hold {$credits} kredit AI Studio",
                'metadata' => $holdMeta,
            ]);
        });
    }

    /**
     * Fase 2: Settle hold kredit.
     * Mengubah status hold menjadi 'settled' ('usage') dan menautkan generation_id resmi.
     */
    public function settleHold(
        string|Business $business,
        string $holdIdempotencyKey,
        ?string $generationId = null,
        array $metadata = []
    ): AiCreditTransaction {
        $businessId = $business instanceof Business ? $business->id : $business;

        return DB::transaction(function () use ($businessId, $holdIdempotencyKey, $generationId, $metadata): AiCreditTransaction {
            /** @var AiCreditTransaction|null $hold */
            $hold = AiCreditTransaction::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $holdIdempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($hold === null) {
                throw new CreditHoldNotFoundException("Hold kredit dengan kunci '{$holdIdempotencyKey}' tidak ditemukan.");
            }

            $meta = $hold->metadata ?? [];
            $status = $meta['hold_status'] ?? 'pending';

            if ($status === 'settled') {
                return $hold; // Idempotent return
            }

            if ($status === 'released') {
                throw new InvalidHoldStateException("Tidak dapat men-settle hold yang sudah di-release/dikembalikan.");
            }

            $meta['hold_status'] = 'settled';
            $meta['settled_at'] = now()->toIso8601String();
            if (! empty($metadata)) {
                $meta = array_merge($meta, $metadata);
            }

            $hold->type = 'usage';
            if ($generationId !== null) {
                $hold->generation_id = $generationId;
            }
            $hold->metadata = $meta;
            $hold->save();

            return $hold;
        });
    }

    /**
     * Fase Batal: Release hold kredit.
     * Jika request AI gagal, timeout, atau ditolak guardrail, kredit dikembalikan secara atomik ke saldo bisnis.
     */
    public function releaseHold(
        string|Business $business,
        string $holdIdempotencyKey,
        string $reason = 'failed_or_timeout',
        array $metadata = []
    ): AiCreditTransaction {
        $businessId = $business instanceof Business ? $business->id : $business;

        return DB::transaction(function () use ($businessId, $holdIdempotencyKey, $reason, $metadata): AiCreditTransaction {
            /** @var AiCreditTransaction|null $hold */
            $hold = AiCreditTransaction::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $holdIdempotencyKey)
                ->lockForUpdate()
                ->first();

            if ($hold === null) {
                throw new CreditHoldNotFoundException("Hold kredit dengan kunci '{$holdIdempotencyKey}' tidak ditemukan.");
            }

            $meta = $hold->metadata ?? [];
            $status = $meta['hold_status'] ?? 'pending';

            if ($status === 'released') {
                /** @var AiCreditTransaction|null $existingRelease */
                $existingRelease = AiCreditTransaction::query()
                    ->where('business_id', $businessId)
                    ->where('idempotency_key', "release_{$holdIdempotencyKey}")
                    ->first();

                return $existingRelease ?? $hold;
            }

            if ($status === 'settled') {
                throw new InvalidHoldStateException("Tidak dapat me-release hold yang sudah di-settle. Gunakan prosedur refund.");
            }

            /** @var Business $biz */
            $biz = Business::query()->where('id', $businessId)->lockForUpdate()->firstOrFail();
            $restoreAmount = abs((int) $hold->amount);

            $biz->ai_credits_balance = (int) ($biz->ai_credits_balance ?? 0) + $restoreAmount;
            $biz->save();

            $meta['hold_status'] = 'released';
            $meta['released_at'] = now()->toIso8601String();
            $meta['release_reason'] = $reason;
            $hold->metadata = $meta;
            $hold->save();

            return AiCreditTransaction::create([
                'business_id' => $biz->id,
                'user_id' => $hold->user_id,
                'generation_id' => $hold->generation_id,
                'type' => 'refund',
                'amount' => $restoreAmount,
                'balance_after' => $biz->ai_credits_balance,
                'idempotency_key' => "release_{$holdIdempotencyKey}",
                'description' => "Pengembalian hold kredit AI Studio: {$reason}",
                'metadata' => array_merge($metadata, [
                    'original_hold_key' => $holdIdempotencyKey,
                    'reason' => $reason,
                ]),
            ]);
        });
    }

    /**
     * Refund langsung untuk kompensasi generasi gambar yang cacat/gagal pasca-eksekusi.
     */
    public function refund(
        string|Business $business,
        int $credits,
        string $idempotencyKey,
        ?string $generationId = null,
        string $reason = 'Kompensasi generasi gagal',
        array $metadata = []
    ): AiCreditTransaction {
        if ($credits <= 0) {
            throw new InvalidArgumentException('Jumlah kredit refund harus lebih besar dari 0.');
        }

        $businessId = $business instanceof Business ? $business->id : $business;

        return DB::transaction(function () use ($businessId, $credits, $idempotencyKey, $generationId, $reason, $metadata): AiCreditTransaction {
            $existing = AiCreditTransaction::query()
                ->where('business_id', $businessId)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            /** @var Business $biz */
            $biz = Business::query()->where('id', $businessId)->lockForUpdate()->firstOrFail();

            $biz->ai_credits_balance = (int) ($biz->ai_credits_balance ?? 0) + $credits;
            $biz->save();

            return AiCreditTransaction::create([
                'business_id' => $biz->id,
                'user_id' => null,
                'generation_id' => $generationId,
                'type' => 'refund',
                'amount' => $credits,
                'balance_after' => $biz->ai_credits_balance,
                'idempotency_key' => $idempotencyKey,
                'description' => $reason,
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Riwayat transaksi kredit AI Studio.
     *
     * @return Collection<int, AiCreditTransaction>
     */
    public function getHistory(string|Business $business, int $limit = 50): Collection
    {
        $businessId = $business instanceof Business ? $business->id : $business;

        return AiCreditTransaction::query()
            ->where('business_id', $businessId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
