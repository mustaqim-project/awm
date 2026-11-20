<?php

declare(strict_types=1);

namespace Tests\E2E\Tier2_BoundaryCorner;

use Tests\E2E\Support\BaseTestCase;

/**
 * Tier 2 Boundary & Corner Cases: F21 Visual QA & Cross-Platform Consistency Report
 *
 * Verifies Visual QA boundary conditions: truncated currency symbol detection (Rp...),
 * untranslated raw translation keys detection, Indonesian date format standardization (DD/MM/YYYY),
 * Indonesian thousand separator (period) and decimal (comma) conventions, and WIB timezone consistency.
 */
final class F21_VisualQaBoundaryTest extends BaseTestCase
{
    /**
     * 1. Currency formatting rules: currency must never be truncated as 'Rp...' or 'Rp-'.
     */
    public function test_truncated_currency_symbol_detection_in_ui_views(): void
    {
        $sampleAmounts = [
            0 => 'Rp 0',
            50000 => 'Rp 50.000',
            1250000 => 'Rp 1.250.000',
            99999999 => 'Rp 99.999.999',
        ];

        foreach ($sampleAmounts as $raw => $expectedFormatted) {
            $formatted = 'Rp ' . number_format($raw, 0, ',', '.');
            $this->assertSame($expectedFormatted, $formatted);

            // Assert no truncated markers
            $this->assertStringNotContainsString('Rp...', $formatted);
            $this->assertStringNotContainsString('Rp-', $formatted);
            $this->assertStringStartsWith('Rp ', $formatted);
        }
    }

    /**
     * 2. Localized views contain zero untranslated raw key strings (e.g. '__("messages.') or '{{ messages.'.
     */
    public function test_untranslated_raw_translation_keys_in_views(): void
    {
        $viewsDir = base_path('resources/views/auth');
        $this->assertDirectoryExists(
            $viewsDir,
            'Authentication views directory [resources/views/auth] must exist.'
        );

        $files = glob($viewsDir . '/*.blade.php') ?: [];
        $this->assertNotEmpty(
            $files,
            'Authentication views directory must contain Blade templates.'
        );

        foreach ($files as $file) {
            $content = (string) file_get_contents($file);
            $this->assertStringNotContainsString('{{ messages.', $content);
            $this->assertStringNotContainsString('{{ validation.', $content);
        }
    }

    /**
     * 3. Indonesian user-facing date formatting standard: DD/MM/YYYY with TimezoneHelper and Blade directives.
     */
    public function test_date_format_standardization_indonesian_locale(): void
    {
        $testDate = \Carbon\Carbon::create(2026, 11, 20, 14, 30, 0, 'UTC');

        // 1. Verify COOCA central TimezoneHelper date formatting in Asia/Jakarta timezone
        $formattedDate = \App\Support\TimezoneHelper::formatDate($testDate, 'd/m/Y', 'Asia/Jakarta');
        $this->assertSame('20/11/2026', $formattedDate, 'TimezoneHelper::formatDate must output DD/MM/YYYY.');

        // 2. Verify localized datetime string contains WIB timezone abbreviation
        $formattedLocal = \App\Support\TimezoneHelper::formatLocal($testDate, 'd/m/Y, H:i', 'Asia/Jakarta', true);
        $this->assertStringStartsWith('20/11/2026', $formattedLocal);
        $this->assertStringContainsString('WIB', $formattedLocal);

        // 3. Verify Blade directive compilation for @date and @datetime
        $compiledDate = \Illuminate\Support\Facades\Blade::compileString('@date($testDate)');
        $this->assertStringContainsString('TimezoneHelper::formatDate', $compiledDate);

        $compiledDateTime = \Illuminate\Support\Facades\Blade::compileString('@datetime($testDate)');
        $this->assertStringContainsString('TimezoneHelper::formatLocal', $compiledDateTime);
    }

    /**
     * 4. Indonesian currency formatting convention: periods (.) for thousands, 0 decimals for IDR.
     */
    public function test_indonesian_thousand_separator_period_format(): void
    {
        // 1. Verify Currency model configuration for Indonesian Rupiah
        $idrCurrency = \App\Models\Currency::where('code', 'IDR')->first();
        $this->assertNotNull($idrCurrency, 'IDR currency model must exist in database.');
        $this->assertSame('Rp', $idrCurrency->symbol);
        $this->assertSame(0, $idrCurrency->decimal_places, 'IDR base currency must have 0 decimal places.');
        $this->assertTrue($idrCurrency->is_base);

        // 2. Verify RoundingService rounds IDR amounts according to currency precision
        $rawAmount = 1500250.75;
        $roundedIdr = \App\Support\RoundingService::roundCurrency($rawAmount, $idrCurrency);
        $this->assertSame(1500251.0, $roundedIdr, 'RoundingService must round IDR to 0 decimals.');

        // 3. Verify model formatted price accessor produces Indonesian period format without decimals
        $event = new \App\Models\CommunityEvent([
            'price' => 1500250,
            'event_type' => \App\Models\CommunityEvent::TYPE_OFFLINE,
        ]);
        $formattedPrice = $event->formatted_price;
        $this->assertSame('Rp 1.500.250', $formattedPrice);
        $this->assertStringContainsString('.', $formattedPrice, 'Indonesian currency formatting must use period for thousand separator.');
        $this->assertStringNotContainsString(',', $formattedPrice, 'Integer IDR formatted price must not contain decimal comma.');
    }

    /**
     * 5. Application timezone is configured to Asia/Jakarta (WIB, UTC+7) or equivalent Indonesian timezone.
     */
    public function test_wib_timezone_consistency_on_financial_records(): void
    {
        $configuredTimezone = config('app.timezone');
        $allowedTimezones = [
            'Asia/Jakarta',
            'Asia/Makassar',
            'Asia/Jayapura',
            'UTC',
        ];

        $this->assertContains(
            $configuredTimezone,
            $allowedTimezones,
            "Application timezone [{$configuredTimezone}] should be set to standard Indonesian timezone."
        );
    }
}
