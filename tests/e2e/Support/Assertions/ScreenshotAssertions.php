<?php

declare(strict_types=1);

namespace Tests\E2E\Support\Assertions;

use PHPUnit\Framework\Assert;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait ScreenshotAssertions
{
    /**
     * Assert that a file exists and is a valid PNG image format with valid magic bytes.
     */
    public function assertValidPngImage(string $path): void
    {
        $resolvedPath = $this->resolveScreenshotPath($path);

        Assert::assertFileExists(
            $resolvedPath,
            "Screenshot file does not exist at: [{$path}]"
        );

        Assert::assertGreaterThan(
            100,
            filesize($resolvedPath),
            "Screenshot file [{$path}] is suspiciously small (< 100 bytes)."
        );

        $fp = fopen($resolvedPath, 'rb');
        Assert::assertIsResource($fp, "Failed to open screenshot file for reading: [{$path}]");

        $magic = fread($fp, 8);
        fclose($fp);

        $expectedPngHeader = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"; // \x89PNG\r\n\x1a\n
        Assert::assertSame(
            $expectedPngHeader,
            $magic,
            "File [{$path}] does not have valid PNG magic bytes header (expected \\x89PNG\\r\\n\\x1a\\n)."
        );

        $imageInfo = @getimagesize($resolvedPath);
        Assert::assertIsArray(
            $imageInfo,
            "getimagesize() failed to parse image [{$path}]."
        );

        Assert::assertSame(
            IMAGETYPE_PNG,
            $imageInfo[2],
            "Image [{$path}] is not identified as IMAGETYPE_PNG."
        );
    }

    /**
     * Assert that a screenshot has exact pixel dimensions (width x height).
     */
    public function assertImageDimensions(string $path, int $expectedWidth, int $expectedHeight): void
    {
        $resolvedPath = $this->resolveScreenshotPath($path);
        $this->assertValidPngImage($resolvedPath);

        $imageInfo = @getimagesize($resolvedPath);
        Assert::assertIsArray($imageInfo, "Failed reading dimensions for [{$path}].");

        $actualWidth = (int) $imageInfo[0];
        $actualHeight = (int) $imageInfo[1];

        Assert::assertSame(
            $expectedWidth,
            $actualWidth,
            "Screenshot [{$path}] width ({$actualWidth}px) does not match expected ({$expectedWidth}px)."
        );

        Assert::assertSame(
            $expectedHeight,
            $actualHeight,
            "Screenshot [{$path}] height ({$actualHeight}px) does not match expected ({$expectedHeight}px)."
        );
    }

    /**
     * Assert that a desktop screenshot meets the standard desktop resolution (1440x900).
     */
    public function assertDesktopResolution(string $path): void
    {
        $this->assertImageDimensions($path, 1440, 900);
    }

    /**
     * Assert that a mobile screenshot meets standard mobile resolution (1080x2400 or allowed alternates).
     *
     * @param array<int, array{0: int, 1: int}> $allowedResolutions
     */
    public function assertMobileResolution(string $path, array $allowedResolutions = [[1080, 2400], [1080, 2340], [1170, 2532]]): void
    {
        $resolvedPath = $this->resolveScreenshotPath($path);
        $this->assertValidPngImage($resolvedPath);

        $imageInfo = @getimagesize($resolvedPath);
        Assert::assertIsArray($imageInfo, "Failed reading dimensions for [{$path}].");

        $actualWidth = (int) $imageInfo[0];
        $actualHeight = (int) $imageInfo[1];

        $matched = false;
        foreach ($allowedResolutions as $res) {
            if ($actualWidth === $res[0] && $actualHeight === $res[1]) {
                $matched = true;
                break;
            }
        }

        Assert::assertTrue(
            $matched,
            "Mobile screenshot [{$path}] has dimensions {$actualWidth}x{$actualHeight}px, which does not match expected resolutions (e.g. 1080x2400, 1080x2340, 1170x2532)."
        );
    }

    /**
     * Assert that a directory contains at least $minCount screenshot PNG files.
     */
    public function assertScreenshotCountInDirectory(string $dir, int $minCount): void
    {
        $resolvedDir = $this->resolveScreenshotPath($dir);

        Assert::assertTrue(
            is_dir($resolvedDir),
            "Screenshot directory does not exist: [{$dir}]"
        );

        $pngFiles = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($resolvedDir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'png') {
                $pngFiles[] = $file->getPathname();
            }
        }

        $count = count($pngFiles);
        Assert::assertGreaterThanOrEqual(
            $minCount,
            $count,
            "Directory [{$dir}] contains {$count} PNG screenshots, expected at least {$minCount}."
        );
    }

    /**
     * Resolve path relative to application base path if needed.
     */
    protected function resolveScreenshotPath(string $path): string
    {
        if (file_exists($path) || is_dir($path)) {
            return $path;
        }

        if (function_exists('base_path')) {
            $candidate = base_path($path);
            if (file_exists($candidate) || is_dir($candidate)) {
                return $candidate;
            }
        }

        return $path;
    }
}
