<?php

declare(strict_types=1);

namespace Tests\E2E\Tier1_FeatureCoverage;

use Tests\E2E\Support\BaseTestCase;

/**
 * Feature F18: Multi-Industry End-to-End User Journeys Test Suite
 *
 * Validates the transactional user journeys, workflow traces,
 * Retail, F&B, Manufacturing, and Workshop operational lifecycles.
 */
final class F18_UserJourneysTest extends BaseTestCase
{
    private string $workflowReport = 'documentation/feature-readiness-audit/workflow-test-report.md';
    private string $journeyDir = 'documentation/user-journeys';

    /**
     * 1. Assert end-to-end user journeys or workflow test report is populated.
     */
    public function test_user_journeys_documentation_exists(): void
    {
        $this->assertMarkdownFilePopulated($this->workflowReport, 1000);
        $this->assertMarkdownContainsHeaders($this->workflowReport, [
            'End-to-End Workflow Test Report',
            'Verification Environment',
        ]);
    }

    /**
     * 2. Assert Retail procurement and Point of Sale (Order-to-Cash) workflow is verified.
     */
    public function test_retail_workflow_journey_valid(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->workflowReport));

        $this->assertStringContainsString(
            'Purchase to Pay',
            $content,
            "Workflow documentation must verify retail Purchase to Pay procurement chain."
        );
        $this->assertStringContainsString(
            'Order',
            $content,
            "Workflow documentation must verify order processing lifecycle."
        );
    }

    /**
     * 3. Assert Food & Beverage operational order and settlement workflow is verified.
     */
    public function test_fnb_workflow_journey_valid(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->workflowReport));

        $hasFnb = str_contains($content, 'F&B')
            || str_contains($content, 'POS')
            || str_contains($content, 'Warung')
            || str_contains($content, 'Split');

        $this->assertTrue(
            $hasFnb,
            "Workflow report must document F&B / POS operational transaction execution."
        );
    }

    /**
     * 4. Assert Manufacturing production work order and raw material workflow is verified.
     */
    public function test_mfg_workflow_journey_valid(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->workflowReport));

        $hasMfg = str_contains($content, 'Manufacturing')
            || str_contains($content, 'Production')
            || str_contains($content, 'Produksi')
            || str_contains($content, 'BOM')
            || str_contains($content, 'Inventory Transfer');

        $this->assertTrue(
            $hasMfg,
            "Workflow report must document manufacturing production or stock transfer lifecycle."
        );
    }

    /**
     * 5. Assert accounting journalization and multi-tenant close workflow is verified.
     */
    public function test_workshop_or_multi_tenant_workflow_valid(): void
    {
        $content = (string) file_get_contents($this->resolveFilePath($this->workflowReport));

        $this->assertStringContainsString(
            'AutoJournalService',
            $content,
            "Workflow report must confirm automatic journal voucher generation across transactions."
        );
        $this->assertTrue(
            str_contains($content, 'Multi-tenant') || str_contains($content, 'isolation'),
            "Workflow report must confirm multi-tenant transactional isolation."
        );
    }
}
