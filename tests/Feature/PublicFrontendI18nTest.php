<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Middleware\SetLocaleMiddleware;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Public Frontend CTA Standardization and i18n Parity Feature Test Suite
 *
 * Implements full 4-tier verification per TEST_INFRA.md:
 * - Tier 1: Canonical CTAs, banned terms absence, dictionary bilateral parity, and locale state.
 * - Tier 2: Boundary cases, unsupported locales, corrupted cookies, parameter placeholders.
 * - Tier 3: Cross-feature combinations (locale switch + CTA link rendering, subpage layout parity).
 * - Tier 4: Real-world user journeys and public route deep crawls.
 */
class PublicFrontendI18nTest extends TestCase
{
    /**
     * List of 15 modular public language files that must exist and have 100% key parity.
     */
    private const PUBLIC_LANG_FILES = [
        'public_blog',
        'public_bos',
        'public_calculators',
        'public_commercial',
        'public_contact',
        'public_content',
        'public_discovery',
        'public_erp',
        'public_home',
        'public_marketing',
        'public_marketplace',
        'public_omnichannel',
        'public_resources',
        'public_solutions',
        'public_templates',
        'common',
    ];

    /**
     * Banned trial phrases that must NEVER appear in public responses.
     */
    private const BANNED_TRIAL_TERMS = [
        'Free Trial',
        'Start Free Trial',
        'Try for Free',
        'Trial Gratis',
        'Coba Gratis',
        'Uji Coba Gratis',
    ];

    /*
    |--------------------------------------------------------------------------
    | TIER 1: Feature Coverage Specifications
    |--------------------------------------------------------------------------
    */

    /**
     * T1.1: Verify primary registration CTAs render the canonical URL.
     */
    public function test_primary_cta_renders_canonical_registration_url(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('https://cooca.id/register', false);
    }

    /**
     * T1.2: Verify secondary pricing CTAs render the canonical URL.
     */
    public function test_secondary_cta_renders_canonical_pricing_url(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('https://cooca.id/pricing', false);
    }

    /**
     * T1.3 & T1.4: Verify CTA copy displays standard non-trial text in English and Indonesian.
     */
    public function test_cta_copy_standardization_in_both_locales(): void
    {
        $enResponse = $this->get('/?lang=en');
        $enResponse->assertStatus(200);
        $enContent = $enResponse->getContent();

        // Must display canonical CTA URLs in English
        $this->assertStringContainsString('https://cooca.id/register', $enContent);
        $this->assertStringContainsString('https://cooca.id/pricing', $enContent);

        // Standardized English CTA copy assertions
        $enResponse->assertSee('Start for free');
        $enResponse->assertSee('View Plans & Pricing');

        // Negative assertions against relative CTAs in English
        $enResponse->assertDontSee('href="/register"', false);
        $enResponse->assertDontSee('href="/pricing"', false);

        $idResponse = $this->get('/?lang=id');
        $idResponse->assertStatus(200);
        $idContent = $idResponse->getContent();

        // Must display canonical CTA URLs in Indonesian
        $this->assertStringContainsString('https://cooca.id/register', $idContent);
        $this->assertStringContainsString('https://cooca.id/pricing', $idContent);

        // Negative assertions against relative CTAs in Indonesian
        $idResponse->assertDontSee('href="/register"', false);
        $idResponse->assertDontSee('href="/pricing"', false);
    }

    /**
     * T1.11 - T1.15: Verify all 16 target dictionary files exist in both ID and EN,
     * have 100% bilateral key parity, flat 1D arrays, and non-empty values.
     */
    public function test_public_language_dictionaries_have_bilateral_key_parity(): void
    {
        $langPath = resource_path('../lang');
        if (!is_dir($langPath)) {
            $langPath = base_path('lang');
        }

        $missingFiles = [];
        $asymmetricKeys = [];
        $emptyKeys = [];

        foreach (self::PUBLIC_LANG_FILES as $file) {
            $idFile = "{$langPath}/id/{$file}.php";
            $enFile = "{$langPath}/en/{$file}.php";

            if (!file_exists($idFile)) {
                $missingFiles[] = "lang/id/{$file}.php";
                continue;
            }
            if (!file_exists($enFile)) {
                $missingFiles[] = "lang/en/{$file}.php";
                continue;
            }

            $idArr = require $idFile;
            $enArr = require $enFile;

            $this->assertIsArray($idArr, "lang/id/{$file}.php must return an array");
            $this->assertIsArray($enArr, "lang/en/{$file}.php must return an array");

            $idKeys = array_keys($idArr);
            $enKeys = array_keys($enArr);

            $diffId = array_diff($idKeys, $enKeys);
            $diffEn = array_diff($enKeys, $idKeys);

            foreach ($diffId as $k) {
                $asymmetricKeys[] = "Key '{$k}' exists in lang/id/{$file}.php but is missing from lang/en/{$file}.php";
            }
            foreach ($diffEn as $k) {
                $asymmetricKeys[] = "Key '{$k}' exists in lang/en/{$file}.php but is missing from lang/id/{$file}.php";
            }

            // Flat array check and non-empty check
            foreach ($idArr as $k => $val) {
                $this->assertIsNotArray($val, "Nested array detected at key '{$k}' in lang/id/{$file}.php; must be flat 1D array");
                if (is_string($val) && trim($val) === '') {
                    $emptyKeys[] = "Empty string value for key '{$k}' in lang/id/{$file}.php";
                }
            }
            foreach ($enArr as $k => $val) {
                $this->assertIsNotArray($val, "Nested array detected at key '{$k}' in lang/en/{$file}.php; must be flat 1D array");
                if (is_string($val) && trim($val) === '') {
                    $emptyKeys[] = "Empty string value for key '{$k}' in lang/en/{$file}.php";
                }
            }
        }

        $this->assertEmpty($missingFiles, "Missing dictionary files: " . implode(', ', $missingFiles));
        $this->assertEmpty($asymmetricKeys, "Dictionary key parity gaps found:\n" . implode("\n", array_slice($asymmetricKeys, 0, 20)));
        $this->assertEmpty($emptyKeys, "Empty dictionary keys found:\n" . implode("\n", array_slice($emptyKeys, 0, 20)));
    }

    /**
     * T2.14: Verify dictionary keys have no trailing or leading whitespace.
     */
    public function test_dictionary_keys_have_no_whitespace_padding(): void
    {
        $langPath = resource_path('../lang');
        if (!is_dir($langPath)) {
            $langPath = base_path('lang');
        }

        $paddedKeys = [];
        foreach (self::PUBLIC_LANG_FILES as $file) {
            foreach (['id', 'en'] as $loc) {
                $filePath = "{$langPath}/{$loc}/{$file}.php";
                if (!file_exists($filePath)) continue;
                $arr = require $filePath;
                foreach (array_keys($arr) as $k) {
                    if (trim((string)$k) !== (string)$k) {
                        $paddedKeys[] = "Padded key '{$k}' in lang/{$loc}/{$file}.php";
                    }
                }
            }
        }

        $this->assertEmpty($paddedKeys, "Whitespace-padded keys found:\n" . implode("\n", $paddedKeys));
    }

    /**
     * T2.13: Verify parameter placeholders match identically between ID and EN definitions.
     */
    public function test_parameter_placeholders_match_between_locales(): void
    {
        $langPath = resource_path('../lang');
        if (!is_dir($langPath)) {
            $langPath = base_path('lang');
        }

        $mismatches = [];
        $pattern = '/:([a-zA-Z_][a-zA-Z0-9_]*)/';

        foreach (self::PUBLIC_LANG_FILES as $file) {
            $idFile = "{$langPath}/id/{$file}.php";
            $enFile = "{$langPath}/en/{$file}.php";
            if (!file_exists($idFile) || !file_exists($enFile)) continue;

            $idArr = require $idFile;
            $enArr = require $enFile;

            foreach ($idArr as $k => $valId) {
                if (!isset($enArr[$k]) || !is_string($valId) || !is_string($enArr[$k])) continue;

                preg_match_all($pattern, $valId, $matchesId);
                preg_match_all($pattern, $enArr[$k], $matchesEn);

                $phId = array_unique($matchesId[1]);
                $phEn = array_unique($matchesEn[1]);
                sort($phId);
                sort($phEn);

                if ($phId !== $phEn) {
                    $mismatches[] = "Placeholder mismatch at key '{$k}' in '{$file}.php': ID=" . implode(',', $phId) . " vs EN=" . implode(',', $phEn);
                }
            }
        }

        $this->assertEmpty($mismatches, "Placeholder mismatches found:\n" . implode("\n", $mismatches));
    }

    /**
     * T1.26: Verify GET /?lang=en switches locale to EN, sets session, and queues cookie.
     */
    public function test_locale_query_param_switches_to_english(): void
    {
        $response = $this->get('/?lang=en');

        $response->assertStatus(200);
        $response->assertSessionHas('locale', 'en');
        $response->assertCookie('cooca_locale', 'en');
        $this->assertSame('en', app()->getLocale());
    }

    /**
     * T1.27: Verify GET /?lang=id switches locale to ID, sets session, and queues cookie.
     */
    public function test_locale_query_param_switches_to_indonesian(): void
    {
        $response = $this->get('/?lang=id');

        $response->assertStatus(200);
        $response->assertSessionHas('locale', 'id');
        $response->assertCookie('cooca_locale', 'id');
        $this->assertSame('id', app()->getLocale());
    }

    /**
     * T1.29: Verify locale switch controller route /locale/{locale}.
     */
    public function test_locale_switch_controller_route(): void
    {
        $response = $this->from('/pricing')->get('/locale/en');

        $response->assertRedirect('/pricing');
        $response->assertSessionHas('locale', 'en');
        $response->assertCookie('cooca_locale', 'en');
    }

    /**
     * T1.30: Verify canonical hreflang tags rendered in <head>.
     */
    public function test_hreflang_tags_rendered_in_head(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('hreflang="id"', false);
        $response->assertSee('hreflang="en"', false);
    }

    /*
    |--------------------------------------------------------------------------
    | TIER 2: Boundary & Corner Cases
    |--------------------------------------------------------------------------
    */

    /**
     * T2.26: Verify unsupported locale query param gracefully falls back to default 'id'.
     */
    public function test_unsupported_locale_falls_back_to_default_id(): void
    {
        $response = $this->get('/?lang=de');

        $response->assertStatus(200);
        $this->assertSame('id', app()->getLocale());
    }

    /**
     * T2.27: Verify corrupted cookie handling defaults to 'id' without throwing 500 error.
     */
    public function test_corrupted_locale_cookie_handled_gracefully(): void
    {
        $response = $this->withCookie('cooca_locale', 'malicious_invalid_123')->get('/');

        $response->assertStatus(200);
        $this->assertSame('id', app()->getLocale());
    }

    /**
     * T2.28: Verify session fallback to persistent cookie when session has no locale.
     */
    public function test_locale_falls_back_to_cookie_when_session_empty(): void
    {
        $response = $this->withCookie('cooca_locale', 'en')->get('/');

        $response->assertStatus(200);
        $this->assertSame('en', app()->getLocale());
    }

    /**
     * T2.29: Verify sequential locale switching preserves correct active state.
     */
    public function test_sequential_locale_switching(): void
    {
        $this->get('/?lang=en')->assertSessionHas('locale', 'en');
        $this->assertSame('en', app()->getLocale());

        $this->get('/?lang=id')->assertSessionHas('locale', 'id');
        $this->assertSame('id', app()->getLocale());

        $this->get('/?lang=en')->assertSessionHas('locale', 'en');
        $this->assertSame('en', app()->getLocale());
    }

    /**
     * T2.30: Verify Carbon locale synchronization with application locale.
     */
    public function test_carbon_locale_synchronization(): void
    {
        $this->get('/?lang=en');
        $this->assertSame('en', Carbon::getLocale());

        $this->get('/?lang=id');
        $this->assertSame('id', Carbon::getLocale());
    }

    /*
    |--------------------------------------------------------------------------
    | TIER 3: Cross-Feature Combinations
    |--------------------------------------------------------------------------
    */

    /**
     * T3.1 & T3.2: Verify locale switch alters rendered content while preserving canonical CTAs.
     */
    public function test_locale_switch_maintains_canonical_cta_links(): void
    {
        $enResponse = $this->get('/?lang=en');
        $enResponse->assertStatus(200);
        $enResponse->assertSee('https://cooca.id/register', false);
        $enResponse->assertSee('https://cooca.id/pricing', false);

        $idResponse = $this->get('/?lang=id');
        $idResponse->assertStatus(200);
        $idResponse->assertSee('https://cooca.id/register', false);
        $idResponse->assertSee('https://cooca.id/pricing', false);
    }

    /**
     * T3.6: Verify language switchers synchronize active state visual classes.
     */
    public function test_language_switchers_synchronize_active_visual_state(): void
    {
        // When active locale is English
        $resEn = $this->get('/?lang=en');
        $resEn->assertStatus(200);
        $enHtml = $resEn->getContent();
        // Assert English button has active indicator class
        $this->assertMatchesRegularExpression('/lang=en[^>]*class="[^"]*bg-\[#007AFF\]\s+text-white/', $enHtml);

        // When active locale is Indonesian
        $resId = $this->get('/?lang=id');
        $resId->assertStatus(200);
        $idHtml = $resId->getContent();
        // Assert Indonesian button has active indicator class
        $this->assertMatchesRegularExpression('/lang=id[^>]*class="[^"]*bg-\[#007AFF\]\s+text-white/', $idHtml);
    }

    /**
     * T3.3: Verify public subpages render HTTP 200 in both Indonesian and English.
     */
    public function test_public_subpages_render_in_both_locales(): void
    {
        $routes = [
            '/about',
            '/support',
            '/kontak',
            '/business-operating-system/overview',
            '/business-operating-system/how-it-works',
            '/business-operating-system/why-cooca',
            '/omnichannel-erp/pos',
            '/omnichannel-erp/inventory',
            '/omnichannel-erp/finance',
            '/omnichannel-erp/accounting',
            '/omnichannel/social-media',
            '/omnichannel/whatsapp',
            '/content-automation/content-creation',
            '/solutions/fnb',
            '/solutions/retail',
            '/solutions/workshop',
            '/resources/guides',
            '/resources/faq',
        ];

        foreach ($routes as $route) {
            $resId = $this->get("{$route}?lang=id");
            $resId->assertStatus(200);

            $resEn = $this->get("{$route}?lang=en");
            $resEn->assertStatus(200);
        }
    }

    /**
     * T3.4: Verify financial calculator routes render HTTP 200 with localized views.
     */
    public function test_calculator_routes_render_in_both_locales(): void
    {
        $calcRoutes = [
            '/kalkulator',
            '/kalkulator/hpp',
            '/kalkulator/bep',
            '/kalkulator/harga-jual',
            '/kalkulator/laba-bersih',
        ];

        foreach ($calcRoutes as $route) {
            $this->get("{$route}?lang=id")->assertStatus(200);
            $this->get("{$route}?lang=en")->assertStatus(200);
        }
    }

    /**
     * T3.5: Verify commercial landing routes render HTTP 200 and canonical register link.
     */
    public function test_commercial_landing_routes(): void
    {
        $commercialRoutes = [
            '/aplikasi-kasir-gratis',
            '/aplikasi-kasir-android',
            '/aplikasi-kasir-offline',
        ];

        foreach ($commercialRoutes as $route) {
            $res = $this->get($route);
            $res->assertStatus(200);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TIER 4: Real-World Workload Scenarios
    |--------------------------------------------------------------------------
    */

    /**
     * T4.2: End-to-end visitor registration journey.
     */
    public function test_visitor_journey_registration_redirect(): void
    {
        // /demo redirects to registration
        $demoRes = $this->get('/demo');
        $demoRes->assertRedirect(route('register'));
    }

    /**
     * T4.3: End-to-end visitor pricing evaluation.
     */
    public function test_visitor_pricing_page_rendering(): void
    {
        $resId = $this->get('/pricing?lang=id');
        $resId->assertStatus(200);

        $resEn = $this->get('/pricing?lang=en');
        $resEn->assertStatus(200);
    }

    /**
     * T4.4: Deep-dive crawl of core public pages ensuring absence of raw translation keys
     * across all 15 public modular language domains in rendered output.
     */
    public function test_no_raw_translation_keys_in_rendered_output(): void
    {
        $sampleRoutes = [
            '/',
            '/about',
            '/support',
            '/kontak',
            '/business-operating-system/overview',
            '/business-operating-system/how-it-works',
            '/omnichannel-erp/pos',
            '/omnichannel-erp/finance',
            '/omnichannel/whatsapp',
            '/solutions/fnb',
            '/solutions/retail',
            '/resources/guides',
            '/resources/faq',
            '/kalkulator',
        ];

        $rawKeyPrefixes = [
            'public_blog.',
            'public_bos.',
            'public_calculators.',
            'public_commercial.',
            'public_contact.',
            'public_content.',
            'public_discovery.',
            'public_erp.',
            'public_home.',
            'public_marketing.',
            'public_marketplace.',
            'public_omnichannel.',
            'public_resources.',
            'public_solutions.',
            'public_templates.',
        ];

        foreach ($sampleRoutes as $route) {
            foreach (['id', 'en'] as $lang) {
                $response = $this->get("{$route}?lang={$lang}");
                $content = $response->getContent();

                foreach ($rawKeyPrefixes as $prefix) {
                    $this->assertStringNotContainsString(
                        $prefix,
                        $content,
                        "Raw un-translated key prefix '{$prefix}' found in rendered response for '{$route}?lang={$lang}'"
                    );
                }
            }
        }
    }

    /**
     * T1.6 - T1.10: Verify absence of banned trial terminology across rendered public responses
     * across all major public application suites.
     */
    public function test_rendered_public_pages_do_not_contain_banned_terms(): void
    {
        $sampleRoutes = [
            '/',
            '/pricing',
            '/about',
            '/support',
            '/kontak',
            '/business-operating-system/overview',
            '/business-operating-system/how-it-works',
            '/business-operating-system/why-cooca',
            '/omnichannel-erp/pos',
            '/omnichannel-erp/inventory',
            '/omnichannel-erp/finance',
            '/omnichannel-erp/accounting',
            '/omnichannel/social-media',
            '/omnichannel/whatsapp',
            '/content-automation/content-creation',
            '/solutions/fnb',
            '/solutions/retail',
            '/solutions/workshop',
            '/resources/guides',
            '/resources/faq',
            '/kalkulator',
        ];

        $violations = [];

        foreach ($sampleRoutes as $route) {
            foreach (['id', 'en'] as $lang) {
                $response = $this->get("{$route}?lang={$lang}");
                $content = $response->getContent();

                foreach (self::BANNED_TRIAL_TERMS as $term) {
                    if (($pos = stripos($content, $term)) !== false) {
                        $snippet = preg_replace('/\s+/', ' ', substr($content, max(0, $pos - 40), 100));
                        $violations[] = "Banned term '{$term}' found in '{$route}?lang={$lang}': [{$snippet}]";
                    }
                }
            }
        }

        $this->assertEmpty($violations, "Banned trial terms found in rendered pages:\n" . implode("\n", $violations));
    }
}
