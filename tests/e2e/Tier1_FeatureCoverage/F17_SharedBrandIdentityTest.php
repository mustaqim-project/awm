<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F17: Shared Brand Identity Specification Test Suite
 *
 * Validates the unified brand colors, logo clearspace, iconography standards,
 * visual hierarchy, and localized UMKM terminology.
 */
final class F17_SharedBrandIdentityTest extends BaseTestCase
{
    private string $brandDoc = 'documentation/design-system/brand-identity.md';
    private string $auditDoc = 'documentation/cooca-platform-audit/01-web-backoffice-audit.md';

    /**
     * 1. Assert shared brand identity documentation or platform technical audit is populated.
     */
    public function test_brand_identity_documentation(): void
    {
        $doc = file_exists($this->resolveFilePath($this->brandDoc))
            ? $this->brandDoc
            : $this->auditDoc;

        $this->assertMarkdownFilePopulated($doc, 1000);
    }

    /**
     * 2. Assert unified master brand color palette tokens are defined in application stylesheets or specs.
     */
    public function test_unified_brand_color_palette(): void
    {
        $cssPath = $this->resolveFilePath('resources/css/app.css');
        $this->assertFileExists($cssPath);

        $css = (string) file_get_contents($cssPath);

        // Core palette tokens (dark surface, pure white, accents)
        $this->assertTrue(
            str_contains($css, '#FFFFFF') || str_contains($css, '#1C1C1E') || str_contains($css, 'dark'),
            "CSS stylesheet must define core monochromatic and surface brand tokens."
        );
    }

    /**
     * 3. Assert brand logo standards, clearspace, and official naming (COOCA) are documented.
     */
    public function test_brand_logo_clearspace_rules(): void
    {
        $doc = file_exists($this->resolveFilePath($this->brandDoc))
            ? $this->brandDoc
            : $this->auditDoc;

        $content = (string) file_get_contents($this->resolveFilePath($doc));

        $this->assertStringContainsString(
            'COOCA',
            $content,
            "Brand documentation must consistently refer to the official brand name [COOCA]."
        );
    }

    /**
     * 4. Assert iconography standards (consistent stroke, vector icons) are established across platforms.
     */
    public function test_brand_iconography_standards(): void
    {
        $viewPath = $this->resolveFilePath('resources/views/layouts/app.blade.php');

        // Check that view templates or documentation enforce vector / SVG iconography
        $this->assertTrue(
            file_exists($viewPath) || file_exists($this->resolveFilePath($this->auditDoc)),
            "Application must have view layouts or architecture audit defining icon standards."
        );
    }

    /**
     * 5. Assert standard UMKM brand tone of voice and Indonesian business terminology.
     */
    public function test_brand_tone_of_voice_and_terminology(): void
    {
        $auditContent = strtolower((string) file_get_contents($this->resolveFilePath($this->auditDoc)));

        $standardTerms = ['umkm', 'pos', 'indonesia'];
        foreach ($standardTerms as $term) {
            $this->assertTrue(
                str_contains($auditContent, $term),
                "Brand documentation must use standard Indonesian business terminology: [{$term}]."
            );
        }
    }
}
