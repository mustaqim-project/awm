# Sequential Implementation & Remediation Priority Roadmap: P0 to P3

**Document Version**: 1.0  
**Audit Date**: November 17, 2026  
**Auditor**: Teamwork Worker M1 (`teamwork_worker_m1_1`)  
**Scope**: Remediation Roadmap for Findings Across All 90 COOCA Features  
**Target Codebase**: `C:\laragon\www\cooca_core`  
**Execution Principle**: Dependency-Ordered Remediation to Guarantee Zero Regression  

---

## 1. Executive Strategy & Dependency Graph

To prevent regressions across interdependent ERP subsystems, remediation tasks must not be applied haphazardly. An alteration to the Chart of Accounts (COA) or Material conversion ratios can ripple through the Costing Engine, POS Terminal, Purchasing Bills, and General Ledger.

The roadmap is structured in strict alignment with COOCA's **Architectural Dependency Graph**:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       MODULE DEPENDENCY FLOWCHART                           │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  [ Tier 0: Foundation ]  Tenancy (business_id) ──> RBAC Permissions         │
│                                    │                                        │
│  [ Tier 1: Master Data]  Bagan Akun (COA) ──> Units (UOM) ──> Bahan Mentah  │
│                                    │                                        │
│  [ Tier 2: Cost & Prod]  BOM Resep ──> Mesin & Upah ──> Katalog Produk      │
│                                    │                                        │
│  [ Tier 3: Operation  ]  Gudang Stok <──> POS Terminal <──> Purchasing (AP) │
│                                    │               │                        │
│  [ Tier 4: Commercial ]  Penjualan B2B (AR) <──────┴───> Marketplace Hub    │
│                                    │                                        │
│  [ Tier 5: Financial  ]  Buku Kas Bank ──> AutoJournal Double-Entry ──> GL  │
│                                    │                                        │
│  [ Tier 6: Intelligence] Laporan Eksekutif ──> Tax ──> Cooca AI Copilot    │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

Remediations proceed strictly from bottom-tier dependencies upward, ensuring that lower-layer data structures are verified and immutable before higher-layer workflows execute.

---

## 2. Remediation Phases & Sequential Work Breakdown

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          REMEDIATION ROADMAP PHASES                         │
├─────────┬─────────────────────────┬─────────────┬───────────────────────────┤
│ Phase   │ Focus                   │ Severity    │ Timeline                  │
├─────────┼─────────────────────────┼─────────────┼───────────────────────────┤
│ Phase 0 │ Test Suite Synchronization│ P2 (Test) │ Immediate (Day 1)         │
│ Phase 1 │ External Integration Gate│ P1          │ Sprint 1 (Days 2–4)       │
│ Phase 2 │ Domain & Performance    │ P2          │ Sprint 1 (Days 5–8)       │
│ Phase 3 │ UI Polish & HIG Microcopy│ P3         │ Sprint 2 (Days 9–14)      │
└─────────┴─────────────────────────┴─────────────┴───────────────────────────┘
```

---

### 2.1 Phase 0: Test Suite Synchronization (Immediate — Day 1)

**Objective**: Eliminate test execution false positives and ensure 100% green test runner status.

#### Task 0.1: Synchronize Receipt View Label Assertion in POS Split Payment Test
- **Severity**: P2 (Test Assertion Mismatch)
- **Target File**: `tests/Feature/Pos/PosMultiPaymentSplitTest.php:393`
- **Root Cause**: The test asserts `'Tunai (Cash)'`, but the view in `resources/views/app/pos/receipt.blade.php` displays localized Indonesian `'Tunai'`.
- **Action**:
  Update `PosMultiPaymentSplitTest.php` line 393:
  ```php
  // Replace:
  $response->assertSee('Tunai (Cash)');
  // With:
  $response->assertSee('Tunai');
  ```
- **Verification Command**:
  ```powershell
  php artisan test tests/Feature/Pos/PosMultiPaymentSplitTest.php
  ```
  *Expected Output*: 5 passed, 0 failed (100% pass).

---

### 2.2 Phase 1: High Severity Remediation (Sprint 1 — Days 2 to 4)

**Objective**: Harden external integration adapters, handle offline test fallbacks, and document hardware boundaries.

#### Task 1.1: Document ESC/POS Hardware Bridge & WebSerial Fallback
- **Severity**: P1 (`GAP-P1-01`)
- **Target Files**: `app/Domain/Pos/EscPosPrintService.php`, `resources/views/app/pos/printers/index.blade.php`
- **Action**:
  Ensure that when the local hardware bridge agent (`http://127.0.0.1:9898`) is unreachable, the UI immediately shows an informative Apple HIG toast: *"Printer offline. Menggunakan dialog cetak browser sebagai fallback"* without hanging or spinning.
- **Verification Command**:
  ```powershell
  php artisan test tests/Feature/Pos/PosPrinterHardwareIntegrationTest.php
  ```

#### Task 1.2: Baileys WhatsApp Daemon Process Supervisor
- **Severity**: P1 (`GAP-P1-02`)
- **Target Files**: `wa-server/package.json`, `wa-server/ecosystem.config.js`
- **Action**:
  Configure PM2 / Windows Service wrapper for `wa-server/server.js` with automatic restart on unexpected exit. Add healthcheck endpoint `GET /health` responding `{ status: "ok", uptime: 1234 }`.
- **Verification Command**:
  ```powershell
  curl http://127.0.0.1:3000/health
  ```

#### Task 1.3: Marketplace & Social OAuth Mock Seeders for Local Sandboxes
- **Severity**: P1 (`GAP-P1-03`)
- **Target Files**: `database/seeders/MarketplaceChannelDemoSeeder.php`, `database/seeders/SocialMediaDemoSeeder.php`
- **Action**:
  Create dedicated offline demo seeders that populate simulated connected accounts for Shopee, Tokopedia, TikTok Shop, and Instagram Business. This enables 100% visual fidelity in staging environments without requiring live merchant app secrets.
- **Verification Command**:
  ```powershell
  php artisan db:seed --class=MarketplaceChannelDemoSeeder
  ```

#### Task 1.4: Courier Logistics Fallback Configuration
- **Severity**: P1 (`GAP-P1-04`)
- **Target Files**: `app/Domain/Commerce/BiteshipService.php`, `database/seeders/MerchantShippingRuleSeeder.php`
- **Action**:
  Ensure default flat-rate shipping rules (e.g. "Kurir Instan Toko", "Reguler JNE Fallback") are automatically provisioned during business onboarding so storefront checkout never fails if Biteship API is unconfigured.
- **Verification Command**:
  ```powershell
  php artisan test tests/Feature/Commerce/CommerceShippingFeeTest.php
  ```

---

### 2.3 Phase 2: Medium Severity Enhancements (Sprint 1 — Days 5 to 8)

**Objective**: Strengthen domain edge cases, concurrency locks, and background performance.

#### Task 2.1: Multi-Currency Automated Forex Difference Journaling
- **Severity**: P2 (`GAP-P2-02`)
- **Target Files**: `app/Domain/Accounting/AutoJournalService.php`, `app/Domain/Finance/BankReconciliationService.php`
- **Action**:
  Implement `recordForexGainLossJournal()` trigger during bank reconciliation when closing exchange rate diverges from transaction rate:
  - Debit/Credit: Kas Rekening Valuta Asing (1-10005)
  - Kredit/Debit: Keuntungan/Kerugian Selisih Kurs (4-20001 / 5-40001).
- **Verification Command**:
  ```powershell
  php artisan test tests/Feature/CorporateAccountingMultiLedgerTest.php
  ```

#### Task 2.2: Mass WhatsApp Broadcast Job Chunking
- **Severity**: P2 (`GAP-P2-03`)
- **Target Files**: `app/Domain/WhatsApp/WhatsAppBroadcastService.php`, `app/Jobs/DispatchBroadcastRecipientJob.php`
- **Action**:
  Refactor the synchronous broadcast loop into individual queued jobs dispatched with random delays (3 to 8 seconds). This prevents queue worker timeouts on broadcasts exceeding 500 recipients.
- **Verification Command**:
  ```powershell
  php artisan test tests/Feature/WhatsApp/MetaStandardTemplatesIntegrationTest.php
  ```

#### Task 2.3: Stock Opname Active Session POS Sales Lock Toggle
- **Severity**: P2 (`GAP-P2-04`)
- **Target Files**: `app/Domain/Inventory/StockOpnameService.php`, `app/Domain/Pos/PosOrderService.php`
- **Action**:
  Add business setting toggle `lock_sales_during_opname` (default: false). When enabled, POS checkout blocks selling items actively being counted in the active outlet's opname session.
- **Verification Command**:
  ```powershell
  php artisan test tests/Feature/InventoryModuleAuditTest.php
  ```

#### Task 2.4: Daily Freelance Worker PPh 21 TER Bracket Support
- **Severity**: P2 (`GAP-P2-05`)
- **Target Files**: `app/Domain/HRM/PayrollRunService.php`, `app/Domain/HRM/Tax/DailyTerTable.php`
- **Action**:
  Implement the daily wage TER schedule under PP 58/2023 for non-permanent staff earning daily up to Rp 2.500.000.
- **Verification Command**:
  ```powershell
  php artisan test tests/Feature/TaxAndHRMComplianceTest.php
  ```

#### Task 2.5: Asynchronous Disk Storage Calculation for Billing
- **Severity**: P2 (`GAP-P2-07`)
- **Target Files**: `app/Domain/Billing/EntitlementService.php`, `app/Console/Commands/AggregateTenantStorageCommand.php`
- **Action**:
  Create an artisan command scheduled nightly to compute tenant media usage and store it in `businesses.storage_used_bytes`, eliminating synchronous directory scans during page loads.
- **Verification Command**:
  ```powershell
  php artisan billing:aggregate-storage
  ```

---

### 2.4 Phase 3: Low Severity UI Polish & Microcopy (Sprint 2 — Days 9 to 14)

**Objective**: Elevate aesthetic quality, mobile responsiveness, and microcopy to Apple Human Interface Guidelines standards.

| Task | Feature | Target File | Action |
|---|:---:|---|---|
| **Task 3.1** | Feat 1 | `resources/views/app/calculator.blade.php:185` | Add `overflow-x-hidden` on slider card to eliminate horizontal jitter on narrow phones (<380px). |
| **Task 3.2** | Feat 4 | `resources/views/app/profitability/index.blade.php:92` | Move Chart.js legend to bottom on viewports <768px. |
| **Task 3.3** | Feat 9 | `resources/views/app/products/index.blade.php:310` | Add tooltip on hover for product names longer than 45 characters. |
| **Task 3.4** | Feat 18| `resources/views/app/pos/shifts.blade.php:140` | Ensure Carbon date formatting in X/Z reports uses Indonesian locale (`->translatedFormat('l, d F Y')`). |
| **Task 3.5** | Feat 24| `resources/views/app/inventory/stocks.blade.php:220` | Boost ROP alert badge text contrast in dark mode (`text-rose-400 dark:text-rose-300`). |
| **Task 3.6** | Feat 35| `resources/views/app/invoices/print.blade.php:85` | Capitalize first letter of each word in the Terbilang currency converter. |
| **Task 3.7** | Feat 43| `resources/views/app/finance/accounting/coa.blade.php:110`| Increase COA tree collapse animation transition to 200ms ease-out. |
| **Task 3.8** | Feat 50| `resources/views/app/customers/index.blade.php:165` | Auto-format customer WhatsApp links (prepend `62` if user types leading `0`). |
| **Task 3.9** | Feat 62| `resources/views/app/storefront/theme_editor.blade.php:320`| Add `border-0` on iframe previewer to remove Safari seam. |
| **Task 3.10**| Feat 71| `resources/views/app/hrm/index.blade.php:280` | Set placeholder `08xxxxxxxxxx` on emergency contact input. |
| **Task 3.11**| Feat 79| `resources/views/app/pos/reports/tabs/hourly.blade.php:65`| Format y-axis values on hourly chart with shorthand (e.g. `1,2 jt` instead of `1200000`). |
| **Task 3.12**| Feat 85| `resources/views/app/ai-studio/create.blade.php:195` | Add `ring-2 ring-indigo-500` on selected style preset cards. |

---

## 3. Regression Prevention Protocol

Before merging any remediation code into staging or production branches:
1. **Run Full Test Suite in Sandbox**:
   ```powershell
   php artisan test
   ```
2. **Verify Financial Double-Entry Equilibrium**:
   ```powershell
   php artisan test tests/Feature/CorporateAccountingMultiLedgerTest.php
   ```
3. **Verify RBAC Security & Tenancy**:
   ```powershell
   php artisan test tests/Feature/AdversarialRbacChallengeTest.php
   php artisan test tests/Feature/ActiveBusinessContextTest.php
   ```
4. **Compile Views & Routes Cache**:
   ```powershell
   php artisan view:cache
   php artisan route:cache
   ```

Following this prioritized roadmap guarantees systematic, regression-free resolution across all 90 features of COOCA SaaS ERP.
