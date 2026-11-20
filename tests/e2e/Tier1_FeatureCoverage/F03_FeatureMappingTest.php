<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F03: Dual-Platform Feature Mapping Matrix Test Suite
 *
 * Validates the cross-platform parity matrix, feature inventory catalog,
 * parity classifications, and functional domain coverage.
 */
final class F03_FeatureMappingTest extends BaseTestCase
{
    private string $mappingDoc = 'documentation/cooca-platform-audit/04-platform-feature-mapping.md';
    private string $inventoryDoc = 'documentation/cooca-platform-audit/03-feature-inventory.md';

    /**
     * 1. Assert cross-platform parity mapping document exists and is populated.
     */
    public function test_platform_feature_mapping_document_exists(): void
    {
        $this->assertMarkdownFilePopulated($this->mappingDoc, 1000);
        $this->assertMarkdownContainsHeaders($this->mappingDoc, [
            'Cross-Platform Parity Mapping',
            'Executive Parity Topology',
        ]);
    }

    /**
     * 2. Assert comprehensive feature inventory document exists and is populated.
     */
    public function test_feature_inventory_document_exists(): void
    {
        $this->assertMarkdownFilePopulated($this->inventoryDoc, 1000);
        $this->assertMarkdownContainsHeaders($this->inventoryDoc, [
            'Platform Feature Inventory',
            'Inventory Methodology',
        ]);
    }

    /**
     * 3. Assert parity classifications (Dual Platform, Web Only, Mobile Only) are established.
     */
    public function test_parity_status_classification(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->inventoryDoc));

        $this->assertStringContainsString(
            'Dual Platform',
            $content,
            "Feature inventory must classify shared cross-platform capabilities as [Dual Platform]."
        );
        $this->assertStringContainsString(
            'Web Only',
            $content,
            "Feature inventory must identify web-exclusive features as [Web Only]."
        );
        $this->assertStringContainsString(
            'Mobile Only',
            $content,
            "Feature inventory must identify mobile-exclusive edge features as [Mobile Only]."
        );
    }

    /**
     * 4. Assert core business modules are comprehensively covered across both documents.
     */
    public function test_core_module_cross_platform_coverage(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->inventoryDoc));

        $requiredModules = [
            'Point of Sale',
            'Inventory',
            'Purchasing',
            'Finance',
            'Manufacturing',
        ];

        foreach ($requiredModules as $module) {
            $this->assertStringContainsString(
                $module,
                $content,
                "Feature inventory must provide dedicated coverage for module [{$module}]."
            );
        }
    }

    /**
     * 5. Assert markdown tables in feature inventory contain canonical schema headers.
     */
    public function test_feature_matrix_headers_and_structure(): void
    {
        $this->assertMarkdownTableNonEmpty($this->inventoryDoc);
        $this->assertMarkdownTableHasHeaders(
            $this->inventoryDoc,
            ['Feature ID', 'Feature Name', 'Description', 'Platform Class.']
        );
    }
}
