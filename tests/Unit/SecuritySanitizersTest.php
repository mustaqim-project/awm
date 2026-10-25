<?php

namespace Tests\Unit;

use App\Support\Security\CsvSanitizer;
use App\Support\Security\HtmlSanitizer;
use Tests\TestCase;

class SecuritySanitizersTest extends TestCase
{
    /**
     * Test CsvSanitizer prepends single quote to formula injection triggers.
     */
    public function test_csv_sanitizer_neutralizes_formula_prefixes(): void
    {
        $this->assertSame("'=1+2", CsvSanitizer::sanitize('=1+2'));
        $this->assertSame("'+SUM(A1:B2)", CsvSanitizer::sanitize('+SUM(A1:B2)'));
        $this->assertSame("'-calc", CsvSanitizer::sanitize('-calc'));
        $this->assertSame("'@calc", CsvSanitizer::sanitize('@calc'));
        $this->assertSame("'|powershell", CsvSanitizer::sanitize('|powershell'));
        $this->assertSame("'\tcmd", CsvSanitizer::sanitize("\tcmd"));
        $this->assertSame("'\rcmd", CsvSanitizer::sanitize("\rcmd"));
        $this->assertSame("'\ncmd", CsvSanitizer::sanitize("\ncmd"));
        $this->assertSame("'%calc", CsvSanitizer::sanitize('%calc'));
    }

    /**
     * Test CsvSanitizer does not alter benign content.
     */
    public function test_csv_sanitizer_preserves_safe_values(): void
    {
        $this->assertSame('Normal User', CsvSanitizer::sanitize('Normal User'));
        $this->assertSame('user@example.com', CsvSanitizer::sanitize('user@example.com'));
        $this->assertSame('12345', CsvSanitizer::sanitize(12345));
        $this->assertSame('', CsvSanitizer::sanitize(null));
        $this->assertSame('', CsvSanitizer::sanitize(''));
    }

    /**
     * Test CsvSanitizer sanitizeRow array transformation.
     */
    public function test_csv_sanitizer_row_sanitization(): void
    {
        $row = [
            'name' => '=HYPERLINK("http://attacker.com")',
            'email' => 'legit@domain.com',
            'amount' => 500000,
        ];

        $sanitized = CsvSanitizer::sanitizeRow($row);

        $this->assertSame("'=HYPERLINK(\"http://attacker.com\")", $sanitized['name']);
        $this->assertSame('legit@domain.com', $sanitized['email']);
        $this->assertSame('500000', $sanitized['amount']);
    }

    /**
     * Test HtmlSanitizer strips harmful XSS payloads.
     */
    public function test_html_sanitizer_strips_scripts_and_event_handlers(): void
    {
        $xss = '<p>Hello</p><script>alert("XSS")</script><img src="x" onerror="alert(1)">';
        $cleaned = HtmlSanitizer::clean($xss);

        $this->assertStringNotContainsString('<script>', $cleaned);
        $this->assertStringNotContainsString('alert("XSS")', $cleaned);
        $this->assertStringNotContainsString('onerror', $cleaned);
        $this->assertStringContainsString('Hello', $cleaned);
    }

    /**
     * Test HtmlSanitizer strips iframes and javascript: pseudo-protocols.
     */
    public function test_html_sanitizer_strips_iframes_and_javascript_links(): void
    {
        $xss = '<iframe src="https://malicious.com"></iframe><a href="javascript:alert(1)">Click me</a>';
        $cleaned = HtmlSanitizer::clean($xss);

        $this->assertStringNotContainsString('<iframe', $cleaned);
        $this->assertStringNotContainsString('javascript:alert(1)', $cleaned);
        $this->assertStringContainsString('Click me', $cleaned);
    }

    /**
     * Test HtmlSanitizer preserves legitimate rich text markup.
     */
    public function test_html_sanitizer_preserves_legitimate_html(): void
    {
        $safeHtml = '<h2>Title</h2><p>This is a <strong>safe</strong> paragraph with <a href="https://cooca.id">a link</a>.</p>';
        $cleaned = HtmlSanitizer::clean($safeHtml);

        $this->assertStringContainsString('<h2>Title</h2>', $cleaned);
        $this->assertStringContainsString('<strong>safe</strong>', $cleaned);
        $this->assertStringContainsString('href="https://cooca.id"', $cleaned);
    }
}
