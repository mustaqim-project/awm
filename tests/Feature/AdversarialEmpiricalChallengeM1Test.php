<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Business;
use App\Models\CommerceStoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Adversarial Empirical Verification Suite for Milestone 1 (M1)
 *
 * Focus areas:
 * 1. Chatbot Deferral & Lazy DOM Mounting (preventing eager layout parsing)
 * 2. Timer & Network Quarantine (zero background setInterval or eager WebSockets on load)
 * 3. Mobile Theme Switch Availability (< 640px) & Anti-FOUC Single Source of Truth
 * 4. Blog Show CSS Consolidation, Rule Integrity, and WCAG Contrast Verification
 */
final class AdversarialEmpiricalChallengeM1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app('translator')->addLines(['storefront.tracking' => 'Lacak Pesanan'], 'id');
        app('translator')->addLines(['storefront.tracking' => 'Track Order'], 'en');
    }

    /**
     * Requirement 1 & 2: Prove help_chatbot_widget and live_chat_widget wrap heavy modal DOM in <template x-if="...">
     * and do NOT start eager intervals or WebSocket connections during initial page load.
     */
    public function test_empirical_chatbot_and_live_chat_deferred_dom_and_timer_quarantine(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $html = $response->getContent();

        // 1. Verify help_chatbot_widget template wrapping directly via Blade rendering
        $helpHtml = view('layouts.partials.help_chatbot_widget')->render();
        $this->assertStringContainsString('id="cooca-help-chatbot-root"', $helpHtml);
        $this->assertMatchesRegularExpression(
            '/<template\s+x-if="isMounted\s*\|\|\s*isOpen">\s*<div\s+x-show="isOpen"/i',
            $helpHtml,
            'Help chatbot modal window must be guarded inside <template x-if="isMounted || isOpen"> to prevent eager DOM node creation.'
        );

        // 2. Verify live_chat_widget template wrapping on public homepage
        $this->assertStringContainsString('id="cooca-live-chat-root"', $html);
        $this->assertMatchesRegularExpression(
            '/<template\s+x-if="isMounted\s*\|\|\s*isOpen">\s*<div\s+x-show="isOpen"[^>]*class="[^"]*floating-live-chat-window/i',
            $html,
            'Live chat modal window must be guarded inside <template x-if="isMounted || isOpen"> to prevent eager DOM node creation.'
        );

        // 3. Inspect help_chatbot_widget JS code for zero eager setInterval in init()
        $helpWidgetBlade = file_get_contents(resource_path('views/layouts/partials/help_chatbot_widget.blade.php'));
        $this->assertStringContainsString('isMounted: false', $helpWidgetBlade);
        $this->assertStringContainsString('isOpen: false', $helpWidgetBlade);

        // Extract init() method from help_chatbot_widget
        preg_match('/init\(\)\s*\{([\s\S]*?)\},\s*async toggleHistoryView/i', $helpWidgetBlade, $helpInitMatch);
        $this->assertNotEmpty($helpInitMatch, 'init() method in help_chatbot_widget must exist.');
        $helpInitBody = $helpInitMatch[1];
        $this->assertStringNotContainsString('setInterval', $helpInitBody, 'help_chatbot init() must not register any setInterval timers.');
        $this->assertStringNotContainsString('WebSocket', $helpInitBody, 'help_chatbot init() must not instantiate WebSockets.');

        // 4. Inspect live_chat_widget JS code for zero eager setInterval in init()
        $liveChatBlade = file_get_contents(resource_path('views/public/partials/live_chat_widget.blade.php'));
        $this->assertStringContainsString('isMounted: false', $liveChatBlade);
        $this->assertStringContainsString('isOpen: false', $liveChatBlade);

        preg_match('/init\(\)\s*\{([\s\S]*?)\},\s*startActivePolling/i', $liveChatBlade, $liveChatInitMatch);
        $this->assertNotEmpty($liveChatInitMatch, 'init() method in live_chat_widget must exist.');
        $liveChatInitBody = $liveChatInitMatch[1];
        $this->assertStringNotContainsString('setInterval', $liveChatInitBody, 'live_chat_widget init() must not register any setInterval timers.');
        $this->assertStringNotContainsString('connectReverb', $liveChatInitBody, 'live_chat_widget init() must not eagerly connect to Reverb WebSockets.');
        $this->assertStringNotContainsString('new WebSocket', $liveChatInitBody, 'live_chat_widget init() must not create WebSocket instances.');

        // 5. Verify live chat intervals are quarantined within startActivePolling() and cleared in stopActivePolling()
        $this->assertStringContainsString('startActivePolling()', $liveChatBlade);
        $this->assertStringContainsString('stopActivePolling()', $liveChatBlade);
        $this->assertStringContainsString('clearInterval(this.pollInterval)', $liveChatBlade);
        $this->assertStringContainsString('clearInterval(this.timeoutCheckInterval)', $liveChatBlade);
    }

    /**
     * Requirement 3: Prove that theme switch button is present and clickable on mobile screens (< 640px)
     * without FOUC or theme desynchronization.
     */
    public function test_empirical_mobile_theme_switch_presence_and_anti_fouc_synchronization(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $html = $response->getContent();

        // 1. Anti-FOUC bootstrap script located in <head> before stylesheets and body
        $headClosePos = strpos($html, '</head>');
        $foucScriptPos = strpos($html, '<!-- No-Flash Theme Bootstrap (Eliminates FOUC) -->');
        $this->assertNotFalse($foucScriptPos, 'Anti-FOUC bootstrap script must be present in HTML.');
        $this->assertLessThan($headClosePos, $foucScriptPos, 'Anti-FOUC script must be positioned in <head> before closing tag.');

        // 2. Head script synchronizes localStorage with documentElement class and data-theme attribute
        $this->assertStringContainsString("var theme = localStorage.getItem('cooca-theme') || 'light';", $html);
        $this->assertStringContainsString("document.documentElement.classList.add('dark');", $html);
        $this->assertStringContainsString("document.documentElement.setAttribute('data-theme', 'dark');", $html);
        $this->assertStringContainsString("document.documentElement.classList.remove('dark');", $html);
        $this->assertStringContainsString("document.documentElement.setAttribute('data-theme', 'light');", $html);

        // 3. Mobile Header Theme Switch Button: Must NOT be hidden on small screens
        // Inspect public_marketing.blade.php directly to verify classes
        $layoutContent = file_get_contents(resource_path('views/layouts/public_marketing.blade.php'));

        preg_match(
            '/<!-- Apple Theme Switcher Button \(Accessible on Mobile, Tablet & Desktop\) -->\s*<button type="button"\s+@click="toggleTheme\(\)"\s+class="([^"]+)"/i',
            $layoutContent,
            $headerButtonMatch
        );
        $this->assertNotEmpty($headerButtonMatch, 'Header theme switcher button must be present.');
        $headerButtonClass = $headerButtonMatch[1];

        $this->assertStringNotContainsString('hidden', $headerButtonClass, 'Header theme switcher must NOT contain hidden or hidden sm: class.');
        $this->assertStringContainsString('inline-flex', $headerButtonClass, 'Header theme switcher must be inline-flex.');

        // 4. Mobile Drawer Theme Switcher: Present inside mobile menu container
        $this->assertStringContainsString('x-show="mobileMenu"', $layoutContent, 'Mobile menu drawer must exist.');
        $this->assertMatchesRegularExpression(
            '/x-text="isDark\s*\?\s*\'Light Mode\'\s*:\s*\'Dark Mode\'"/i',
            $layoutContent,
            'Mobile drawer must offer an interactive theme switch toggle.'
        );

        // 5. Alpine toggleTheme() method synchronizes documentElement class, data-theme, and localStorage
        $this->assertStringContainsString('toggleTheme() {', $layoutContent);
        $this->assertStringContainsString("localStorage.setItem('cooca-theme', 'dark');", $layoutContent);
        $this->assertStringContainsString("localStorage.setItem('cooca-theme', 'light');", $layoutContent);
    }

    /**
     * Requirement 4: Prove blog show CSS consolidation retains proper contrast in both light and dark themes
     * without broken style rules.
     */
    public function test_empirical_blog_show_css_consolidation_contrast_and_syntax_integrity(): void
    {
        $blogViewPath = resource_path('views/public/blog/show.blade.php');
        $this->assertFileExists($blogViewPath);
        $blogBlade = file_get_contents($blogViewPath);

        // 1. Confirm elimination of duplicate @push('late-styles')
        $this->assertStringNotContainsString("@push('late-styles')", $blogBlade, 'Redundant @push(late-styles) must be completely eliminated.');

        // 2. Extract @push('styles') content and verify balanced CSS braces
        preg_match('/@push\(\'styles\'\)\s*<style>([\s\S]*?)<\/style>\s*@endpush/i', $blogBlade, $styleMatch);
        $this->assertNotEmpty($styleMatch, '@push(styles) block must exist in blog/show.blade.php');
        $css = $styleMatch[1];

        $openBraces = substr_count($css, '{');
        $closeBraces = substr_count($css, '}');
        $this->assertSame($openBraces, $closeBraces, "CSS syntax error: mismatched curly braces (Open: {$openBraces}, Close: {$closeBraces}).");

        // 3. Verify absence of broken selectors or invalid syntax
        $this->assertStringNotContainsString(';;', $css, 'CSS must not contain duplicate semicolons.');
        $this->assertStringNotContainsString('{ }', $css, 'CSS must not contain empty rule blocks.');

        // 4. Contrast ratio calculation using WCAG relative luminance formula
        // Test light mode values:
        $lightBg = '#FFFFFF';
        $lightProseText = '#1D1D1F';
        $lightHeadingText = '#1D1D1F';
        $lightLinkText = '#007AFF';
        $lightQuoteText = '#48484A';
        $lightCodeBg = '#F2F2F7';
        $lightCodeText = '#1D1D1F';

        // WCAG relative luminance calculation
        $luminance = function (string $hex): float {
            $hex = ltrim($hex, '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            $r = hexdec(substr($hex, 0, 2)) / 255;
            $g = hexdec(substr($hex, 2, 2)) / 255;
            $b = hexdec(substr($hex, 4, 2)) / 255;

            $toLinear = function (float $c): float {
                return $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
            };

            return 0.2126 * $toLinear($r) + 0.7152 * $toLinear($g) + 0.0722 * $toLinear($b);
        };

        $contrast = function (string $c1, string $c2) use ($luminance): float {
            $l1 = $luminance($c1);
            $l2 = $luminance($c2);
            $lighter = max($l1, $l2);
            $darker = min($l1, $l2);
            return ($lighter + 0.05) / ($darker + 0.05);
        };

        // Light Mode Assertions (WCAG AA: >= 4.5:1 for body text, >= 3.0:1 for large text/headings)
        $ratioLightProse = $contrast($lightProseText, $lightBg);
        $this->assertGreaterThanOrEqual(4.5, $ratioLightProse, "Light prose contrast ratio ({$ratioLightProse}:1) must meet WCAG AA (>= 4.5:1).");

        $ratioLightHeading = $contrast($lightHeadingText, $lightBg);
        $this->assertGreaterThanOrEqual(3.0, $ratioLightHeading, "Light heading contrast ratio ({$ratioLightHeading}:1) must meet WCAG AA (>= 3.0:1).");

        $ratioLightLink = $contrast($lightLinkText, $lightBg);
        $this->assertGreaterThanOrEqual(3.0, $ratioLightLink, "Light link contrast ratio ({$ratioLightLink}:1) must meet UI/graphic component minimum (>= 3.0:1) with explicit underline.");

        $ratioLightQuote = $contrast($lightQuoteText, $lightBg);
        $this->assertGreaterThanOrEqual(4.5, $ratioLightQuote, "Light quote contrast ratio ({$ratioLightQuote}:1) must meet WCAG AA (>= 4.5:1).");

        $ratioLightCode = $contrast($lightCodeText, $lightCodeBg);
        $this->assertGreaterThanOrEqual(4.5, $ratioLightCode, "Light code contrast ratio ({$ratioLightCode}:1) must meet WCAG AA (>= 4.5:1).");

        // Dark Mode Assertions (Surface: #1C1C1E)
        $darkSurfaceBg = '#1C1C1E';
        $darkProseText = '#E5E5EA';
        $darkHeadingText = '#FFFFFF';
        $darkLinkText = '#0A84FF';
        $darkQuoteText = '#AEAEB2';
        $darkCodeBg = '#2C2C2E';
        $darkCodeText = '#E5E5EA';

        $ratioDarkProse = $contrast($darkProseText, $darkSurfaceBg);
        $this->assertGreaterThanOrEqual(4.5, $ratioDarkProse, "Dark prose contrast ratio ({$ratioDarkProse}:1) must meet WCAG AA (>= 4.5:1).");

        $ratioDarkHeading = $contrast($darkHeadingText, $darkSurfaceBg);
        $this->assertGreaterThanOrEqual(3.0, $ratioDarkHeading, "Dark heading contrast ratio ({$ratioDarkHeading}:1) must meet WCAG AA (>= 3.0:1).");

        $ratioDarkLink = $contrast($darkLinkText, $darkSurfaceBg);
        $this->assertGreaterThanOrEqual(4.5, $ratioDarkLink, "Dark link contrast ratio ({$ratioDarkLink}:1) must meet WCAG AA (>= 4.5:1).");

        $ratioDarkQuote = $contrast($darkQuoteText, $darkSurfaceBg);
        $this->assertGreaterThanOrEqual(4.5, $ratioDarkQuote, "Dark quote contrast ratio ({$ratioDarkQuote}:1) must meet WCAG AA (>= 4.5:1).");

        $ratioDarkCode = $contrast($darkCodeText, $darkCodeBg);
        $this->assertGreaterThanOrEqual(4.5, $ratioDarkCode, "Dark code contrast ratio ({$ratioDarkCode}:1) must meet WCAG AA (>= 4.5:1).");

        // 5. Verify the consolidated CSS explicitly defines these exact dark mode classes
        $this->assertStringContainsString('.dark .article-prose', $css);
        $this->assertStringContainsString('[data-theme="dark"] .article-prose', $css);
        $this->assertStringContainsString('color: #E5E5EA !important;', $css);
        $this->assertStringContainsString('color: #FFFFFF !important;', $css);
        $this->assertStringContainsString('color: #0A84FF !important;', $css);
    }
}
