# TEST_INFRA.md — E2E Test Suite Specification & Infrastructure Architecture

**Project**: COOCA Dual-Platform Audit, UI Reverse Rendering, Multi-Industry Datasets, and QA  
**Target Environment**: `c:\laragon\www\cooca_core`  
**Test Suite Directory**: `tests/e2e/`  
**Total Test Count**: 242 Tests (Tier 1: 105 | Tier 2: 105 | Tier 3: 21 | Tier 4: 11)  
**Execution Command**: `php tests/e2e/runner.php` or `php artisan test --testsuite=E2E`  
**Pass Criteria**: 100% passing tests, exit code 0, non-destructive execution  

---

## 1. Test Philosophy & Core Architecture

The COOCA E2E Test Suite provides automated, opaque-box, requirement-driven verification across all 21 features defined in `PROJECT.md` and `ORIGINAL_REQUEST.md`.

### Core Design Principles:
1. **Opaque-Box Verification**: Tests validate observable deliverables, interface contracts, markdown audit outputs, database tenant partitions, SAK EMKM accounting balance equations, API HTTP endpoints, and screenshot visual assets without coupling to private implementation internals.
2. **Non-Destructive Invariant**: The test suite runs directly against the development MySQL schema (`cooca_id`). Destructive commands (`migrate:fresh`, `db:wipe`, `TRUNCATE`) are strictly banned. Any mutation test wraps execution in `DB::beginTransaction()` / `DB::rollBack()` or uses isolated transient tenant UUIDs cleaned up during `tearDown()`.
3. **Four-Tier Verification Model**:
   - **Tier 1 (Feature Coverage — 105 Tests)**: 5 happy-path existence and baseline structure tests for each of the 21 features.
   - **Tier 2 (Boundary & Corner Cases — 105 Tests)**: 5 boundary, negative, and edge-case tests for each of the 21 features.
   - **Tier 3 (Cross-Feature Combinations — 21 Tests)**: 21 pairwise integration tests validating cross-module consistency, route-to-API parity, and transaction-to-journal balance.
   - **Tier 4 (Real-World Scenarios — 11 Tests)**: 11 complete, multi-step business journeys simulating authentic operational lifecycles across Retail, F&B, Manufacturing, and Automotive Workshop industries.
4. **Deterministic Exit Codes**: Standalone runner and PHPUnit suite return exit code `0` on complete pass and `1` on any failure.

---

## 2. Directory Layout & Test File Structure

```
tests/e2e/
├── Support/
│   ├── BaseTestCase.php               # Base harness extending Tests\TestCase with tenant context helpers
│   ├── Assertions/
│   │   ├── DatabaseAssertions.php     # Tenant scoping, foreign key, and record count assertions
│   │   ├── DocumentationAssertions.php# Markdown file existence, size, header, and table parsing
│   │   ├── AccountingAssertions.php   # SAK EMKM debit === credit balance and trial balance verifications
│   │   └── ScreenshotAssertions.php   # PNG header validation, dimensions (1440x900 / 1080x2400)
│   └── Helpers/
│       ├── TenantHelper.php           # Active tenant context switcher and UUID generator
│       ├── TransactionHelper.php      # Transaction isolation and rollback wrapper
│       └── ApiClientHelper.php        # Sanctum token generator and HTTP API client wrapper
├── Tier1_FeatureCoverage/
│   ├── F01_WebRouteAuditTest.php      # 5 tests: Web routes, controllers, middleware
│   ├── F02_MobileScreenAuditTest.php  # 5 tests: Mobile screens, state, hardware bridges
│   ├── F03_FeatureMappingTest.php     # 5 tests: Feature inventory and parity status
│   ├── F04_DatabaseArchTest.php       # 5 tests: Multi-tenant schemas, BelongsToBusiness trait
│   ├── F05_ApiIntegrationTest.php     # 5 tests: 48+ REST endpoints, Sanctum headers
│   ├── F06_RbacPermissionTest.php     # 5 tests: 100+ permissions, 7 workspace roles
│   ├── F07_RetailDatasetTest.php      # 5 tests: COOCA Mart (3 branches, 35+ SKUs, POS/PO)
│   ├── F08_FnbDatasetTest.php         # 5 tests: COOCA Bistro (3 outlets, 32+ BOM menu items)
│   ├── F09_MfgDatasetTest.php         # 5 tests: COOCA Mfg (16+ finished goods, BOM, SPKP)
│   ├── F10_WorkshopDatasetTest.php    # 5 tests: COOCA AutoCare (22+ services, 32+ parts, vehicles)
│   ├── F11_DemoAccountsTest.php       # 5 tests: Demo accounts, password hashing, role binds
│   ├── F12_AutoJournalTest.php        # 5 tests: SAK EMKM COA, POS/GRN auto-journal balancing
│   ├── F13_WebReverseRenderTest.php   # 5 tests: 1440x900 desktop screenshots (45+ modules)
│   ├── F14_MobileReverseRenderTest.php# 5 tests: 1080x2400 mobile screenshots (23 screens)
│   ├── F15_WebDesignSystemTest.php    # 5 tests: Bento grid, typography, color tokens
│   ├── F16_MobileDesignSystemTest.php # 5 tests: 48pt touch targets, bottom sheets, mobile scale
│   ├── F17_SharedBrandIdentityTest.php# 5 tests: Master brand colors, logo clearspace, icons
│   ├── F18_UserJourneysTest.php       # 5 tests: Retail, F&B, Mfg, Workshop Mermaid workflows
│   ├── F19_DataIntegrityTest.php      # 5 tests: Zero tenant leakage, orphan scan, non-destructive
│   ├── F20_AccountingReconcileTest.php# 5 tests: Global trial balance, line-level debit==credit
│   └── F21_VisualQaReportTest.php     # 5 tests: Indonesian Rupiah, WIB timezone, UI consistency
├── Tier2_BoundaryCorner/
│   ├── F01_WebRouteBoundaryTest.php   # 5 tests: 404 fictitious routes, SQL injection, guest auth
│   ├── F02_MobileBoundaryTest.php     # 5 tests: Offline queue UUID idempotency, 401 interceptor
│   ├── F03_MappingBoundaryTest.php    # 5 tests: Empty table rows, invalid parity status rejection
│   ├── F04_DatabaseBoundaryTest.php   # 5 tests: Null business_id, cross-tenant FK leakage rejection
│   ├── F05_ApiBoundaryTest.php        # 5 tests: Missing X-Business-Id, 422 validation, throttling
│   ├── F06_RbacBoundaryTest.php       # 5 tests: Cashier privilege escalation blocked, role leakage
│   ├── F07_RetailBoundaryTest.php     # 5 tests: Negative stock prevention, wholesale price tiers
│   ├── F08_FnbBoundaryTest.php        # 5 tests: BOM auto-deduction on sale, table state machine
│   ├── F09_MfgBoundaryTest.php        # 5 tests: Raw material shortage alert, recursive BOM blocked
│   ├── F10_WorkshopBoundaryTest.php   # 5 tests: Duplicate license plate, odometer regression alert
│   ├── F11_DemoAccountsBoundaryTest.php# 5 tests: Admin guard login blocked, deactivated account
│   ├── F12_AutoJournalBoundaryTest.php# 5 tests: Unbalanced journal rollback, tax rounding splits
│   ├── F13_WebRenderBoundaryTest.php  # 5 tests: Whoops/500 error detection, horizontal scrollbar
│   ├── F14_MobileRenderBoundaryTest.php# 5 tests: Flutter red screen detection, RenderFlex overflow
│   ├── F15_WebDesignBoundaryTest.php  # 5 tests: WCAG AA contrast, tabular-nums font feature check
│   ├── F16_MobileDesignBoundaryTest.php# 5 tests: Bottom thumb zone reach, monospace receipt tokens
│   ├── F17_BrandBoundaryTest.php      # 5 tests: Prohibited logo modifications, minimum print size
│   ├── F18_UserJourneysBoundaryTest.php# 5 tests: Mermaid syntax parser, exception branch flows
│   ├── F19_DataIntegrityBoundaryTest.php# 5 tests: Deliberate cross-tenant FK simulated detection
│   ├── F20_AccountingBoundaryTest.php # 5 tests: Negative amount rejection, zero debit/credit check
│   └── F21_VisualQaBoundaryTest.php   # 5 tests: Truncated currency detection, English text leaks
├── Tier3_CrossFeature/
│   ├── CrossPlatformParityTest.php    # 7 tests: Web-to-Mobile route/screen/API mappings
│   ├── TransactionAccountingSyncTest.php# 7 tests: Retail/F&B/Mfg/Workshop transactions to auto-journals
│   └── SecurityTenantIsolationTest.php# 7 tests: Multi-tenant RBAC, cross-tenant API isolation
├── Tier4_RealWorldScenarios/
│   ├── RetailEndToEndWorkflowTest.php # 3 scenarios: Procure-to-Pay, Stock Transfer, Wholesale POS
│   ├── FnbEndToEndWorkflowTest.php    # 3 scenarios: Dine-in to KDS, Recipe BOM auto-procurement
│   ├── MfgEndToEndWorkflowTest.php    # 2 scenarios: Work Order to Finished Stock, Shortage Requisition
│   ├── WorkshopEndToEndWorkflowTest.php# 2 scenarios: Vehicle Check-in to SPK, Bay Scheduling
│   └── MultiTenantAuditWorkflowTest.php# 1 scenario: Concurrent multi-tenant POS & Financial Close
└── runner.php                         # Standalone executable CLI runner with TAP/Console reporting
```

---

## 3. Test Runner CLI Interface & Invocation

### Option A: Standalone Lightweight CLI Runner
```bash
# Run all 242 tests
php tests/e2e/runner.php

# Run specific tier
php tests/e2e/runner.php --tier=1
php tests/e2e/runner.php --tier=2
php tests/e2e/runner.php --tier=3
php tests/e2e/runner.php --tier=4

# Run specific feature group
php tests/e2e/runner.php --feature=F07
```

### Option B: Laravel Artisan Test Runner
```bash
# Run full E2E testsuite
php artisan test --testsuite=E2E
```

---

## 4. Coverage Thresholds & Feature Inventory (242 Tests)

| Tier | File Count | Tests Per File / Topic | Total Tests | Target Scope |
|------|:----------:|:----------------------:|:-----------:|--------------|
| **Tier 1: Feature Coverage** | 21 files | 5 tests per feature | **105 tests** | Features 1–21 baseline functionality & schema validation |
| **Tier 2: Boundary & Corner Cases** | 21 files | 5 tests per feature | **105 tests** | Edge cases, negative inputs, privilege escalations, error envelopes |
| **Tier 3: Cross-Feature Combinations** | 3 files | 7 tests per topic | **21 tests** | Pairwise module interactions, platform parity, journal synchronization |
| **Tier 4: Real-World Scenarios** | 5 files | 1–3 scenarios per domain | **11 tests** | Complex business workflows (Retail, F&B, Mfg, Workshop, Multi-Tenant Close) |
| **Total** | **50 files** | — | **242 tests** | Complete end-to-end requirement verification |

---

## 5. Non-Destructive Database Guidelines

1. **Read-Only Against Demo Datasets**: Tests checking for COOCA Mart, COOCA Bistro, COOCA Mfg, or COOCA AutoCare verify existing data created by seeders without mutating state.
2. **Transactional Mutations**: Tests performing write mutations must execute within a database transaction and roll back in `finally`:
   ```php
   DB::beginTransaction();
   try {
       // Perform mutation test
       $this->assertTrue(...);
   } finally {
       DB::rollBack();
   }
   ```
3. **Transient Tenant Isolation**: Tests that require committed database records must create a transient business with UUID prefix `test-transient-[uuid]` and delete only that tenant's records during cleanup.
