<?php

declare(strict_types=1);

namespace App\Domain\Currency;

use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExchangeRateSyncService
{
    public const CACHE_KEY = 'ai_studio:exchange_rate:usd_idr';
    public const CACHE_TTL_SECONDS = 3600; // 1 Jam
    public const DEFAULT_FALLBACK_RATE = 16000.0;

    /**
     * Dapatkan kurs USD ke IDR terkini (dengan hirarki Cache -> External API -> Database -> Fallback).
     */
    public function getUsdToIdrRate(): float
    {
        return (float) Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function (): float {
            return $this->resolveCurrentRate();
        });
    }

    /**
     * Sinkronkan dan perbarui kurs terkini secara paksa dari API eksternal terverifikasi.
     */
    public function syncLatestRate(): float
    {
        $rate = $this->fetchRateFromExternalApi();

        if ($rate !== null && $rate > 0) {
            Cache::put(self::CACHE_KEY, $rate, self::CACHE_TTL_SECONDS);
            $this->persistRateSnapshot($rate);
            return $rate;
        }

        return $this->getUsdToIdrRate();
    }

    /**
     * Set kurs manual (misal: penyesuaian khusus dari Super Admin).
     */
    public function setManualRate(float $rate): void
    {
        Cache::put(self::CACHE_KEY, $rate, self::CACHE_TTL_SECONDS);
        $this->persistRateSnapshot($rate);
    }

    /**
     * Resolusi kurs: Eksternal API -> DB Snapshot -> Setting Platform -> Fallback Konstanta.
     */
    protected function resolveCurrentRate(): float
    {
        // 1. Coba fetch dari API eksternal
        $apiRate = $this->fetchRateFromExternalApi();
        if ($apiRate !== null && $apiRate > 0) {
            $this->persistRateSnapshot($apiRate);
            return $apiRate;
        }

        // 2. Coba ambil snapshot terbaru dari tabel database exchange_rates
        $dbRate = $this->fetchRateFromDatabase();
        if ($dbRate !== null && $dbRate > 0) {
            return $dbRate;
        }

        // 3. Coba ambil dari System Setting
        try {
            $settingRate = Setting::get('ai_exchange_rate_usd_idr');
            if (!empty($settingRate) && is_numeric($settingRate)) {
                return (float) $settingRate;
            }
        } catch (Throwable) {
            // Ignore if setting table not accessible
        }

        // 4. Fallback aman
        return self::DEFAULT_FALLBACK_RATE;
    }

    /**
     * Panggil API eksternal publik terverifikasi (open.er-api.com atau frankfurter.app)
     */
    protected function fetchRateFromExternalApi(): ?float
    {
        if (app()->environment('testing')) {
            return self::DEFAULT_FALLBACK_RATE;
        }

        // Endpoint 1: Open Exchange Rate API (Gratis & Stabil)
        try {
            $response = Http::timeout(5)->get('https://open.er-api.com/v6/latest/USD');
            if ($response->successful()) {
                $idr = $response->json('rates.IDR');
                if (is_numeric($idr) && (float) $idr > 1000) {
                    return (float) $idr;
                }
            }
        } catch (Throwable $e) {
            Log::warning('[ExchangeRateSyncService] Primary API open.er-api failed: ' . $e->getMessage());
        }

        // Endpoint 2: Frankfurter API (Cadangan)
        try {
            $response = Http::timeout(5)->get('https://api.frankfurter.app/latest', [
                'from' => 'USD',
                'to' => 'IDR',
            ]);
            if ($response->successful()) {
                $idr = $response->json('rates.IDR');
                if (is_numeric($idr) && (float) $idr > 1000) {
                    return (float) $idr;
                }
            }
        } catch (Throwable $e) {
            Log::warning('[ExchangeRateSyncService] Secondary API frankfurter failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Ambil rate dari database exchange_rates jika tabel dan mata uang tersedia.
     */
    protected function fetchRateFromDatabase(): ?float
    {
        try {
            $usd = Currency::where('code', 'USD')->first();
            $idr = Currency::where('code', 'IDR')->first();

            if ($usd && $idr) {
                /** @var ExchangeRate|null $snapshot */
                $snapshot = ExchangeRate::where('from_currency_id', $usd->id)
                    ->where('to_currency_id', $idr->id)
                    ->latest('created_at')
                    ->first();

                if ($snapshot && $snapshot->rate > 0) {
                    return (float) $snapshot->rate;
                }
            }
        } catch (Throwable $e) {
            Log::warning('[ExchangeRateSyncService] Database lookup failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Simpan snapshot kurs ke tabel database exchange_rates.
     */
    protected function persistRateSnapshot(float $rate): void
    {
        try {
            $usd = Currency::where('code', 'USD')->first();
            $idr = Currency::where('code', 'IDR')->first();

            if ($usd && $idr) {
                ExchangeRate::create([
                    'business_id' => null, // Global Platform Rate
                    'from_currency_id' => $usd->id,
                    'to_currency_id' => $idr->id,
                    'rate' => $rate,
                    'snapshot_date' => now()->toDateString(),
                ]);
            }

            // Simpan juga ke setting untuk referensi admin
            Setting::set('ai_exchange_rate_usd_idr', (string) $rate);
            Setting::set('ai_exchange_rate_last_synced_at', now()->toIso8601String());
        } catch (Throwable $e) {
            Log::warning('[ExchangeRateSyncService] Failed persisting rate snapshot: ' . $e->getMessage());
        }
    }
}

