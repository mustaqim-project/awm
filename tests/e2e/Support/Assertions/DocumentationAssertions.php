<?php

declare(strict_types=1);

namespace Tests\E2E\Support\Assertions;

use PHPUnit\Framework\Assert;

trait DocumentationAssertions
{
    /**
     * Assert that a markdown file exists and has at least $minBytes of content.
     */
    public function assertMarkdownFilePopulated(string $path, int $minBytes = 500): void
    {
        $resolvedPath = $this->resolveFilePath($path);

        Assert::assertFileExists(
            $resolvedPath,
            "Markdown file does not exist at: [{$path}]"
        );

        $actualBytes = filesize($resolvedPath);
        Assert::assertGreaterThanOrEqual(
            $minBytes,
            $actualBytes,
            "Markdown file [{$path}] is underpopulated: size is {$actualBytes} bytes, expected at least {$minBytes} bytes."
        );
    }

    /**
     * Assert that a markdown file has at least $minLines non-empty lines.
     */
    public function assertMarkdownMinLines(string $path, int $minLines): void
    {
        $resolvedPath = $this->resolveFilePath($path);
        $this->assertMarkdownFilePopulated($resolvedPath, 20);

        $lines = file($resolvedPath, FILE_IGNORE_NEW_LINES);
        $actualLines = is_array($lines) ? count(array_filter($lines, fn ($l) => trim($l) !== '')) : 0;

        Assert::assertGreaterThanOrEqual(
            $minLines,
            $actualLines,
            "Markdown file [{$path}] has {$actualLines} non-empty lines, expected at least {$minLines}."
        );
    }

    /**
     * Assert that a markdown file contains all required headers.
     *
     * @param array<int, string> $requiredHeaders
     */
    public function assertMarkdownContainsHeaders(string $path, array $requiredHeaders): void
    {
        $resolvedPath = $this->resolveFilePath($path);
        $this->assertMarkdownFilePopulated($resolvedPath, 50);

        $content = (string) file_get_contents($resolvedPath);

        foreach ($requiredHeaders as $header) {
            $cleanHeader = trim($header, "# \t\n\r\0\x0B");
            $pattern = '/^#{1,6}\s+.*?' . preg_quote($cleanHeader, '/') . '/im';

            $matched = preg_match($pattern, $content) === 1
                || str_contains(strtolower($content), strtolower($cleanHeader));

            Assert::assertTrue(
                $matched,
                "Markdown file [{$path}] is missing required header: [{$header}]"
            );
        }
    }

    /**
     * Assert that a markdown file contains a non-empty table (optionally within a specific section).
     */
    public function assertMarkdownTableNonEmpty(string $path, string $sectionHeader = ''): void
    {
        $resolvedPath = $this->resolveFilePath($path);
        $this->assertMarkdownFilePopulated($resolvedPath, 50);

        $content = (string) file_get_contents($resolvedPath);

        if ($sectionHeader !== '') {
            $cleanHeader = trim($sectionHeader, "# \t\n\r\0\x0B");
            // Match section from header to next header of equal or higher level
            $pattern = '/^(#{1,6})\s+.*?' . preg_quote($cleanHeader, '/') . '.*?\n([\s\S]*?)(?=^\1\s+|\z)/im';
            if (preg_match($pattern, $content, $sectionMatches)) {
                $content = $sectionMatches[2];
            } else {
                Assert::fail("Section [{$sectionHeader}] was not found in [{$path}].");
            }
        }

        // Markdown table regex: header row, delimiter row (|---|), and at least one data row
        $tableRegex = '/\|[^\n]+\|\s*\r?\n\s*\|(?:\s*:?-{2,}:?\s*\|)+\s*\r?\n\s*(?:\|[^\n]+\|\s*(?:\r?\n|\z))+/';

        Assert::assertMatchesRegularExpression(
            $tableRegex,
            $content,
            "Markdown file [{$path}]" . ($sectionHeader ? " under section [{$sectionHeader}]" : '') . " does not contain any populated markdown table."
        );
    }

    /**
     * Assert that a markdown table contains specific column headers.
     *
     * @param array<int, string> $expectedColumns
     */
    public function assertMarkdownTableHasHeaders(string $path, array $expectedColumns, string $sectionHeader = ''): void
    {
        $resolvedPath = $this->resolveFilePath($path);
        $this->assertMarkdownTableNonEmpty($resolvedPath, $sectionHeader);

        $content = (string) file_get_contents($resolvedPath);

        if ($sectionHeader !== '') {
            $cleanHeader = trim($sectionHeader, "# \t\n\r\0\x0B");
            $pattern = '/^(#{1,6})\s+.*?' . preg_quote($cleanHeader, '/') . '.*?\n([\s\S]*?)(?=^\1\s+|\z)/im';
            if (preg_match($pattern, $content, $sectionMatches)) {
                $content = $sectionMatches[2];
            }
        }

        // Find table header row
        $tableRegex = '/(\|(?:\s*[^|\r\n]+\s*\|)+)\s*\r?\n\s*\|(?:\s*:?-{2,}:?\s*\|)+/';
        if (preg_match($tableRegex, $content, $matches)) {
            $headerRow = strtolower($matches[1]);
            foreach ($expectedColumns as $col) {
                Assert::assertStringContainsString(
                    strtolower(trim($col)),
                    $headerRow,
                    "Table in [{$path}] is missing expected column [{$col}]."
                );
            }
        } else {
            Assert::fail("Could not locate markdown table header in [{$path}].");
        }
    }

    /**
     * Assert that a markdown file contains at least one syntactically valid Mermaid diagram.
     */
    public function assertMermaidDiagramValid(string $path): void
    {
        $resolvedPath = $this->resolveFilePath($path);
        $this->assertMarkdownFilePopulated($resolvedPath, 50);

        $content = (string) file_get_contents($resolvedPath);

        // Find all mermaid code fences
        preg_match_all('/```mermaid\s*([\s\S]*?)```/i', $content, $matches);

        Assert::assertNotEmpty(
            $matches[1],
            "Markdown file [{$path}] contains zero ```mermaid diagram blocks."
        );

        $validDiagramTypes = [
            'graph',
            'flowchart',
            'sequenceDiagram',
            'classDiagram',
            'stateDiagram',
            'stateDiagram-v2',
            'erDiagram',
            'gantt',
            'pie',
            'gitGraph',
            'journey',
            'timeline',
            'quadrantChart',
            'mindmap',
            'architecture-beta',
            'block-beta',
            'sankey-beta',
            'kanban',
            'packet-beta',
        ];

        foreach ($matches[1] as $index => $diagramCode) {
            $trimmed = trim($diagramCode);
            Assert::assertNotEmpty(
                $trimmed,
                "Mermaid diagram #{$index} in [{$path}] is empty."
            );

            // Assert starts with valid keyword
            $firstLine = trim(strtok($trimmed, "\r\n") ?: '');
            $hasValidType = false;
            foreach ($validDiagramTypes as $type) {
                if (str_starts_with($firstLine, $type)) {
                    $hasValidType = true;
                    break;
                }
            }

            Assert::assertTrue(
                $hasValidType,
                "Mermaid diagram #{$index} in [{$path}] starts with unrecognized diagram type: '{$firstLine}'."
            );

            // Check no truncation markers
            Assert::assertStringNotContainsString(
                '[TRUNCATED]',
                $trimmed,
                "Mermaid diagram #{$index} in [{$path}] appears truncated with [TRUNCATED] marker."
            );

            // Check basic bracket balance (square, curly, parentheses)
            $this->assertBalancedTokens($trimmed, "Mermaid diagram #{$index} in [{$path}]");
        }
    }

    /**
     * Resolve a path that may be relative to the application base path.
     */
    protected function resolveFilePath(string $path): string
    {
        if (file_exists($path)) {
            return $path;
        }

        if (function_exists('base_path') && file_exists(base_path($path))) {
            return base_path($path);
        }

        return $path;
    }

    /**
     * Helper to verify balanced delimiters in a code snippet.
     */
    protected function assertBalancedTokens(string $code, string $context): void
    {
        $stack = [];
        $pairs = [')' => '(', ']' => '[', '}' => '{'];
        $len = strlen($code);
        $inQuotes = false;
        $quoteChar = '';

        for ($i = 0; $i < $len; $i++) {
            $char = $code[$i];

            if (($char === '"' || $char === "'") && ($i === 0 || $code[$i - 1] !== '\\')) {
                if ($inQuotes && $char === $quoteChar) {
                    $inQuotes = false;
                } elseif (! $inQuotes) {
                    $inQuotes = true;
                    $quoteChar = $char;
                }
                continue;
            }

            if ($inQuotes) {
                continue;
            }

            if (in_array($char, ['(', '[', '{'], true)) {
                $stack[] = $char;
            } elseif (isset($pairs[$char])) {
                $expected = $pairs[$char];
                $actual = array_pop($stack);
                Assert::assertEquals(
                    $expected,
                    $actual,
                    "{$context} has unbalanced delimiter '{$char}'."
                );
            }
        }

        Assert::assertEmpty(
            $stack,
            "{$context} has unclosed delimiters: " . implode('', $stack)
        );
    }
}
