<?php

declare(strict_types=1);

namespace Tests\Feature\AiStudio;

use App\Domain\Currency\ExchangeRateSyncService;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class ExchangeRateSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExchangeRateSyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new ExchangeRateSyncService;
    }

    public function test_get_rate_returns_cached_rate_if_present(): void
    {
        Cache::put(ExchangeRateSyncService::CACHE_KEY, 16250.0, 3600);

        $rate = $this->service->getUsdToIdrRate();

        $this->assertEquals(16250.0, $rate);
    }

    public function test_set_manual_rate_updates_cache_and_persists_snapshot(): void
    {
        $usd = Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'is_base' => false,
            'decimal_places' => 2,
        ]);

        $idr = Currency::create([
            'code' => 'IDR',
            'name' => 'Indonesian Rupiah',
            'symbol' => 'Rp',
            'is_base' => true,
            'decimal_places' => 0,
        ]);

        $this->service->setManualRate(16500.0);

        $this->assertEquals(16500.0, Cache::get(ExchangeRateSyncService::CACHE_KEY));
        $this->assertEquals(16500.0, $this->service->getUsdToIdrRate());

        $this->assertDatabaseHas('exchange_rates', [
            'from_currency_id' => $usd->id,
            'to_currency_id' => $idr->id,
            'rate' => 16500.0,
        ]);
    }

    public function test_fallback_to_default_rate_when_no_cache_or_external_api(): void
    {
        Cache::forget(ExchangeRateSyncService::CACHE_KEY);

        $rate = $this->service->getUsdToIdrRate();

        $this->assertEquals(ExchangeRateSyncService::DEFAULT_FALLBACK_RATE, $rate);
    }
}

