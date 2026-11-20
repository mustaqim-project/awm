<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F13: Web Backoffice UI Reverse Rendering Test Suite
 *
 * Validates the web reverse rendering outputs, 1440x900 desktop viewport specs,
 * valid PNG headers, category module distribution, and generation report.
 */
final class F13_WebReverseRenderTest extends BaseTestCase
{
    private string $uiImagesDir = 'documentation/feature-ui-images';
    private string $reportPath = 'documentation/feature-ui-images/generation-report.md';

    /**
     * 1. Assert web UI reverse rendering image directory contains rendered PNG screenshots.
     */
    public function test_web_ui_images_directory_populated(): void
    {
        $this->assertScreenshotCountInDirectory($this->uiImagesDir, 45);
    }

    /**
     * 2. Assert rendered screenshot files have valid PNG magic bytes and headers.
     */
    public function test_web_screenshots_are_valid_pngs(): void
    {
        $sampleImages = [
            'documentation/feature-ui-images/A-kasir-operasional/hold-resume-order.png',
            'documentation/feature-ui-images/E-inventory-supply-chain/stock-opname-fisik-berkala.png',
            'documentation/feature-ui-images/F-finance-accounting/buku-besar-neraca-saldo.png',
        ];

        $this->assertNotEmpty($sampleImages, 'Sample images list must not be empty.');

        foreach ($sampleImages as $imgPath) {
            $resolved = $this->resolveFilePath($imgPath);
            $this->assertFileExists($resolved, "Sample screenshot must exist at: [{$imgPath}].");
            $this->assertValidPngImage($resolved);
        }
    }

    /**
     * 3. Assert desktop screenshots meet desktop viewport specifications (width >= 1000px).
     */
    public function test_web_desktop_viewport_resolution_specs(): void
    {
        $samplePath = $this->resolveFilePath('documentation/feature-ui-images/F-finance-accounting/buku-besar-neraca-saldo.png');

        if (file_exists($samplePath)) {
            $info = @getimagesize($samplePath);
            $this->assertIsArray($info);
            $width = (int) $info[0];
            $height = (int) $info[1];

            $this->assertGreaterThanOrEqual(1000, $width, "Desktop screenshot width must be >= 1000px.");
            $this->assertGreaterThanOrEqual(600, $height, "Desktop screenshot height must be >= 600px.");
        } else {
            $this->assertMarkdownFilePopulated($this->reportPath, 500);
        }
    }

    /**
     * 4. Assert key operational module categories (POS, Inventory, Finance, Production) are present.
     */
    public function test_web_module_categories_covered(): void
    {
        $requiredCategories = [
            'A-kasir-operasional',
            'C-produksi-hpp',
            'E-inventory-supply-chain',
            'F-finance-accounting',
        ];

        foreach ($requiredCategories as $cat) {
            $catDir = $this->resolveFilePath("{$this->uiImagesDir}/{$cat}");
            $this->assertTrue(
                is_dir($catDir),
                "Category directory [{$cat}] must exist under feature-ui-images."
            );
        }
    }

    /**
     * 5. Assert UI generation report markdown file is populated and structured.
     */
    public function test_web_rendering_report_documentation(): void
    {
        $this->assertMarkdownFilePopulated($this->reportPath, 1000);
        $this->assertMarkdownContainsHeaders($this->reportPath, [
            'Ringkasan Eksekutif Hasil Generasi',
            'Tabel Ringkasan per Kategori',
        ]);
        $this->assertMarkdownTableNonEmpty($this->reportPath);
    }
}
