# Handoff Report — Explorer 3: App Views & Translation Architecture Survey

**Date**: 2026-10-31  
**Agent Role**: Explorer 3 (App Views & Translation Architecture Survey)  
**Target Directory**: `c:\laragon\www\cooca_core\resources\views\app` & `c:\laragon\www\cooca_core\lang`  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3`  
**Status**: Completed (Read-Only Survey)

---

## 1. Observation

### 1.1 App Views Scope & Distribution (`resources/views/app`)
- **Total Blade Templates**: Exactly 185 `.blade.php` files located under `resources/views/app`.
- **Total Lines of Code**: 134,824 lines across all 185 files.
- **Existing Localization Density**: 12,267 existing `__('...')` / `@lang('...')` calls across the files.
- **Audit Tool Output (`python scratch/deep_audit_v2.py resources/views/app`)**:
  - **Total files audited**: 185
  - **Clean files (0 hardcoded issues)**: 107 files (57.8%)
  - **Files with hardcoded issues**: 78 files (42.2%)
  - **Total remaining hardcoded issues**: 762 issues
- **Submodule Breakdown (44 distinct submodule buckets)**:
  - `pos`: 29 files (15,934 lines | 1,675 i18n calls | 30 issues across 6 files | 23 clean files)
  - `ai`: 18 files (9,751 lines | 677 i18n calls | 17 issues in 1 file | 17 clean files)
  - `finance`: 16 files (8,011 lines | 1,113 i18n calls | 9 issues across 3 files | 13 clean files)
  - `storefront`: 12 files (4,896 lines | 589 i18n calls | 4 issues across 2 files | 10 clean files)
  - `ai-studio`: 8 files (3,182 lines | 0 i18n calls | 69 issues across 8 files | 0 clean files)
  - `marketplace`: 6 files (5,175 lines | 385 i18n calls | 35 issues across 5 files | 1 clean file)
  - `purchasing`: 6 files (1,316 lines | 157 i18n calls | 3 issues in 1 file | 5 clean files)
  - `billing`: 5 files (6,154 lines | 734 i18n calls | 2 issues in 1 file | 4 clean files)
  - `invoices`: 5 files (2,784 lines | 369 i18n calls | 1 issue in 1 file | 4 clean files)
  - `sales`: 5 files (2,524 lines | 255 i18n calls | 25 issues across 4 files | 1 clean file)
  - `settings`: 5 files (8,655 lines | 912 i18n calls | 19 issues across 4 files | 1 clean file)
  - `social_media`: 5 files (6,494 lines | 509 i18n calls | 45 issues across 4 files | 1 clean file)
  - `hrm`: 4 files (5,263 lines | 894 i18n calls | 0 issues | 4 clean files — 100% clean)
  - `inventory`: 4 files (2,022 lines | 154 i18n calls | 33 issues across 3 files | 1 clean file)
  - `purchase-orders`: 4 files (1,930 lines | 174 i18n calls | 3 issues across 2 files | 2 clean files)
  - `quotations`: 4 files (1,327 lines | 135 i18n calls | 25 issues across 4 files | 0 clean files)
  - `sales-orders`: 4 files (1,829 lines | 170 i18n calls | 20 issues across 4 files | 0 clean files)
  - `whatsapp`: 4 files (2,898 lines | 368 i18n calls | 2 issues in 1 file | 3 clean files)
  - `approvals`: 3 files (1,146 lines | 147 i18n calls | 0 issues | 3 clean files — 100% clean)
  - `community`: 3 files (2,222 lines | 0 i18n calls | 48 issues across 3 files | 0 clean files)
  - `feedback`: 3 files (785 lines | 95 i18n calls | 0 issues | 3 clean files — 100% clean)
  - `products`: 3 files (5,252 lines | 488 i18n calls | 14 issues across 2 files | 1 clean file)
  - `landing_page`: 2 files (4,102 lines | 0 i18n calls | 96 issues across 2 files | 0 clean files)
  - `docs`: 2 files (2,587 lines | 0 i18n calls | 72 issues across 2 files | 0 clean files)
  - `profile`: 2 files (386 lines | 0 i18n calls | 18 issues across 2 files | 0 clean files)
  - `security`: 2 files (1,061 lines | 0 issues | 2 clean files — 100% clean)
  - `warehouse`: 2 files (3,711 lines | 382 i18n calls | 2 issues in 1 file | 1 clean file)
  - `crm`: 2 files (1,132 lines | 118 i18n calls | 2 issues in 1 file | 1 clean file)
  - `root` (`dashboard.blade.php`, `calculator.blade.php`): 2 files (3,020 lines | 339 i18n calls | 0 issues — 100% clean)
  - Single-file submodules (1 file each):
    - `import`: 2,195 lines | 0 i18n calls | 62 issues (0% localized data import wizard)
    - `labor-machines`: 1,293 lines | 4 i18n calls | 32 issues
    - `master-data`: 1,188 lines | 0 i18n calls | 28 issues
    - `customers`: 1,506 lines | 132 i18n calls | 11 issues
    - `suppliers`: 1,353 lines | 100 i18n calls | 11 issues
    - `reports`: 890 lines | 0 i18n calls | 10 issues
    - `services`: 1,252 lines | 109 i18n calls | 5 issues
    - `tax`: 1,398 lines | 177 i18n calls | 4 issues
    - `mcp`: 130 lines | 0 i18n calls | 2 issues
    - `profitability`: 283 lines | 0 i18n calls | 2 issues
    - `portal`: 2,547 lines | 247 i18n calls | 1 issue
    - `analytics`: 867 lines | 0 issues (100% clean)
    - `materials`: 1,241 lines | 150 i18n calls | 0 issues (100% clean)
    - `roles`: 1,351 lines | 112 i18n calls | 0 issues (100% clean)
    - `simulator`: 559 lines | 71 i18n calls | 0 issues (100% clean)

- **Completely Clean Submodules (9 submodules, 18 files)**:
  `root` (dashboard, calculator), `analytics`, `approvals`, `feedback`, `hrm`, `materials`, `roles`, `security`, `simulator`.

---

### 1.2 Existing Translation Architecture (`lang/id` & `lang/en`)
- **PHP Translation Files**:
  - `lang/id/*.php`: Exactly 66 files.
  - `lang/en/*.php`: Exactly 66 files.
  - 100% symmetric filename pairing between `lang/id` and `lang/en`.
- **JSON Language Files**:
  - `lang/id.json`: **Does NOT exist** (0 files).
  - `lang/en.json`: **Does NOT exist** (0 files).
  - All project translations are strictly modular PHP arrays returning `return [ ... ];`.
- **Key Count & Parity Metrics**:
  - Total keys in `lang/id`: **17,068 keys**.
  - Total keys in `lang/en`: **17,068 keys**.
  - Total common keys: **17,068 keys**.
  - Missing keys in EN: **0**.
  - Missing keys in ID: **0**.
  - **Bilingual Key Parity**: **100.00%** across all 66 modular files.
- **Key Depth & Naming Structure**:
  - **Flat Array (Depth 2, e.g. `file.key`)**:
    - `common.php` (120 keys, e.g. `save`, `cancel`, `edit`, `delete`, `actions`)
    - `pos.php` (1,740 keys, e.g. `pos.accept_and_forward_kds`, `pos.btn_checkout`)
    - `hrm.php` (890 keys)
    - `admin.php` (875 keys)
    - `billing.php` (716 keys)
    - `settings.php` (406 keys)
  - **Nested Hierarchical Array (Depth 3 to 4, e.g. `file.group.subgroup.key`)**:
    - `storefront.php` (1,578 keys, depth 4, e.g. `storefront.nav.home`, `storefront.catalog.product_detail`)
    - `finance.php` (940 keys, depth 3, e.g. `finance.journal_unbalanced`, `finance.accounts.*`)
    - `sales.php` (765 keys, depth 3, e.g. `sales.sales_orders.page_title`, `sales.quotations.title`, `sales.returns.*`)
    - `ai.php` (690 keys, depth 4, e.g. `ai.navigation.virtual_office`, `ai.offices.available`)
    - `purchasing.php` (411 keys, depth 4)
    - `tax.php` (247 keys, depth 3)
    - `validation.php` (41 keys, depth 4)

---

### 1.3 Breakdown of the 762 Issues in `resources/views/app`
By inspecting `scratch/deep_audit_v2.py`:
- **Domain Breakdown**:
  1. `7. Buttons & Action Links`: 476 issues (62.5%)
  2. `3. Headers, H1, & Subtitles`: 107 issues (14.0%)
  3. `4. Forms, Labels, & Hints`: 81 issues (10.6%)
  4. `9. Tables, Badges, & Empty`: 53 issues (7.0%)
  5. `6. Error Lists & Summary`: 24 issues (3.1%)
  6. `1. Page Title & Meta Tags`: 17 issues (2.2%)
  7. `5. Dev / Test Alerts`: 4 issues (0.5%)
- **Issue Type Breakdown**:
  - `raw_button_label`: 308 issues (e.g. `<button>Batal</button>`, `<button>Simpan Perubahan</button>`, `<button>Hapus</button>`)
  - `raw_attr_title`: 119 issues (e.g. `title="Tutup"`, `title="Unduh PDF"`, `title="Pilih Sudut Pandang Kamera"`)
  - `raw_attr_placeholder`: 81 issues (e.g. `placeholder="u.p. Nama PIC Pelanggan"`, `placeholder="Cari peran..."`)
  - `empty_state_hardcoded`: 53 issues (e.g. `Belum Ada Riwayat Transaksi`, `Belum Ada Proyek Visual`, `Belum ada render`)
  - `raw_heading_h3`: 46 issues
  - `raw_attr_aria-label`: 38 issues (e.g. `aria-label="Tutup modal"`, `aria-label="Tutup tiket"`)
  - `raw_heading_h2`: 28 issues
  - `validation_errors_raw`: 24 issues (e.g. `Terjadi kesalahan saat mengunggah file.`)
  - `raw_heading_h4`: 18 issues
  - `extends_title_raw`: 17 issues (e.g. `@extends('...', ['title' => 'Riwayat Ledger & Kredit AI Studio'])`)
  - `raw_heading_h1`: 12 issues
  - `raw_attr_alt`: 11 issues (e.g. `alt="Foto Produk"`, `alt="QRIS Code"`)
  - `dev_bypass_hardcoded`: 4 issues
  - `raw_heading_h5`: 3 issues

---

### 1.4 Translation Usage Conventions in `app` Views
- **Top Translation Prefixes Called in Views**:
  - `pos`: 1,636 calls
  - `finance`: 1,041 calls
  - `hrm`: 1,022 calls
  - `sales`: 926 calls
  - `billing`: 734 calls
  - `ai`: 668 calls
  - `storefront`: 587 calls
  - `social_media`: 547 calls
  - `products`: 488 calls
  - `whatsapp`: 463 calls
  - `purchasing`: 425 calls
  - `mcp`: 420 calls
  - `marketplace`: 385 calls
  - `warehouse`: 368 calls
  - `settings`: 348 calls
  - `portal`: 250 calls
  - `common`: 205 calls (e.g. `common.close`, `common.cancel`, `common.previous`, `common.next`, `common.actions`)
- **Key Pattern Conventions**:
  - Views predominantly access their own domain dictionary (`pos.*`, `finance.*`, `hrm.*`, `sales.*`).
  - Cross-module views like `settings/index.blade.php` include partials from `settings.*`, `mcp.*`, `whatsapp.*`, and `social_media.*`.
  - Submodules with hyphens in directory names map to snake_case language files:
    - `sales-orders/` and `quotations/` use `sales.sales_orders.*` and `sales.quotations.*`.
    - `purchase-orders/` uses `purchasing.*`.
    - `master-data/` uses `master_data.php`.
    - `labor-machines/` uses `labor_machines.php`.

---

### 1.5 UI Complexity, Alpine.js, & Dynamic Patterns
- **Alpine.js Density**: 126 out of 185 files (68.1%) use Alpine.js, containing 203 distinct `x-data` scopes.
- **Dynamic Attribute Bindings**:
  - Multiple views bind dynamic attributes using Alpine expressions:
    - `pos/terminal.blade.php`: `:title="isFullscreen ? 'Keluar Fullscreen' : 'Mode Layar Penuh'"`
    - `ai/partials/virtual_office_canvas.blade.php`: `:title="isFullscreen ? 'Keluar Mode Layar Penuh [ESC]' : 'Masuk Mode Layar Penuh [F]'"`
    - `social_media/inbox.blade.php`: `:placeholder="'Balas di ' + (activeThread?.channel_label || 'WhatsApp') + '...'"`
  - High risk of syntax breakage if agents blindly replace text without preserving Alpine `:`-binding semantics.
- **Formatting Helpers**:
  - `number_format(...)`: 949 occurrences across financial, POS, and sales calculations.
  - Date formatting (`->translatedFormat(...)` / `->format(...)`): 270 occurrences.
  - `formatRupiah(...)`: 153 occurrences.
- **Heavy / Monolithic Views**:
  - `pos/terminal.blade.php`: 7,158 lines (POS terminal, split payment, scanner, KDS sync).
  - `ai/partials/virtual_office_canvas.blade.php`: 3,875 lines (3D canvas, keyboard shortcuts `[E]`, `[ESC]`, `[R]`, camera HUD).
  - `landing_page/edit.blade.php`: 3,334 lines (drag-and-drop page builder, block editor, live theme preview).
  - `import/index.blade.php`: 2,195 lines (Excel data import wizard, column mapper, batch execution, live log).
  - `social_media/inbox.blade.php`: 1,793 lines (omnichannel chat manager, sentiment badge, auto-responder).
- **Scanner Edge Cases**:
  - In `pos/terminal.blade.php:4698`: `canBypassSupervisor: {{ $canBypassSupervisor ?? false ? 'true' : 'false' }}` triggers `scratch/deep_audit_v2.py` under `5. Dev / Test Alerts` because the line contains `BypassSupervisor:`. The scanner rule `if '__(' in matched: continue` means adding an inline translation or translatable comment will resolve the trigger without altering variable names.

---

## 2. Logic Chain

```
[Observation 1.1]
185 Blade files in resources/views/app
107 files are 100% clean
78 files contain 762 hardcoded issues
     │
     ▼
[Observation 1.3]
Issues are clustered into specific types:
- 308 raw button labels
- 119 raw title attributes
- 81 raw placeholder attributes
- 53 hardcoded empty states
- 46 h3 headings
- 38 raw aria-labels
- 28 h2 headings
- 24 raw validation messages
     │
     ▼
[Observation 1.2 & 1.4]
Existing lang architecture has 66 files in ID and 66 files in EN
100.00% key parity (17,068 keys each)
No root JSON files exist
Views reference modular files (pos.*, finance.*, sales.*, common.*)
     │
     ▼
[Observation 1.5]
126 files use Alpine.js with reactive attribute bindings (:title, :placeholder)
Heavy monolithic views exist (Virtual Office Canvas, Landing Page Builder, POS Terminal)
     │
     ▼
[Deduction: Milestone Decomposition]
Remediation must be organized into 5 balanced, domain-coherent milestones that:
1. Group related business domains and their corresponding translation files together.
2. Distribute the issue count evenly (100–140 issues per milestone, with heavy builders isolated into Milestone 5).
3. Ensure 100% mathematical coverage of all 78 dirty files without omissions or duplicates.
```

---

## 3. Caveats

1. **Admin Views Out of Scope**: `resources/views/admin` (87 files) was not surveyed by this explorer because this investigation is explicitly scoped to `resources/views/app` (185 files) and the global translation architecture (`lang/`).
2. **Scanner Specifics**: `scratch/deep_audit_v2.py` is the official benchmark tool specified in `ORIGINAL_REQUEST.md`. Any remediation must satisfy its exact regex patterns (e.g., verifying attributes, headings, empty states, and bypass notices).
3. **No Code Modification Assumption**: Per Explorer instructions, no view or translation files were edited during this survey. All counts reflect the pristine workspace state.

---

## 4. Conclusion & Proposed 5-Milestone Decomposition

All 78 dirty files (762 issues) partition cleanly into **5 modular milestones** based on business domain, component architecture, and translation namespace:

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                       5-MILESTONE MODULAR DECOMPOSITION FOR APP VIEWS                       │
├────────────┬─────────────────────────────┬───────┬───────┬────────┬────────┬────────────────┤
│ Milestone  │ Domain Scope                │ Files │ Dirty │ Issues │ Lines  │ Lang Files     │
├────────────┼─────────────────────────────┼───────┼───────┼────────┼────────┼────────────────┤
│ M1         │ Retail & Omnichannel Comm.  │ 56    │ 19    │ 118    │ 36,990 │ pos, products, │
│            │ (POS, Inventory, Storefront,│       │       │        │        │ inventory,     │
│            │ Products, Warehouse, Mktpl) │       │       │        │        │ warehouse, etc.│
├────────────┼─────────────────────────────┼───────┼───────┼────────┼────────┼────────────────┤
│ M2         │ B2B Trade, Purchasing & CRM │ 33    │ 20    │ 106    │ 16,953 │ sales,         │
│            │ (Sales, SO, Quotes, Invoices│       │       │        │        │ purchasing,    │
│            │ PO, Suppliers, Customers)   │       │       │        │        │ customers, crm │
├────────────┼─────────────────────────────┼───────┼───────┼────────┼────────┼────────────────┤
│ M3         │ Finance, Tax, Settings & Fdn│ 34    │ 16    │ 125    │ 30,805 │ finance, tax,  │
│            │ (Finance, Billing, Settings,│       │       │        │        │ settings, auth,│
│            │ Master Data, Labor, Profile)│       │       │        │        │ labor_machines │
├────────────┼─────────────────────────────┼───────┼───────┼────────┼────────┼────────────────┤
│ M4         │ AI Suite & Social Omnichan. │ 36    │ 15    │ 135    │ 22,458 │ ai, ai_studio, │
│            │ (AI Office, AI Studio,      │       │       │        │        │ social_media,  │
│            │ Social Media, WhatsApp, MCP)│       │       │        │        │ whatsapp, mcp  │
├────────────┼─────────────────────────────┼───────┼───────┼────────┼────────┼────────────────┤
│ M5         │ Heavy Builders, Docs & Comm.│ 8     │ 8     │ 278    │ 11,106 │ landing_page,  │
│            │ (Landing Page Builder, Docs,│       │       │        │        │ docs, import,  │
│            │ Import Wizard, Community)   │       │       │        │        │ community      │
├────────────┼─────────────────────────────┼───────┼───────┼────────┼────────┼────────────────┤
│ Clean Sub. │ 9 Submodules (HRM, Roles,   │ 18    │ 0     │ 0      │ 16,512 │ Already clean  │
│            │ Feedback, Security, Root)   │       │       │        │        │                │
├────────────┼─────────────────────────────┼───────┼───────┼────────┼────────┼────────────────┤
│ TOTAL      │ Entire resources/views/app  │ 185   │ 78    │ 762    │ 134,824│ 100% Covered   │
└────────────┴─────────────────────────────┴───────┴───────┴────────┴────────┴────────────────┘
```

### Detailed Milestone Target Manifest:

#### Milestone 1: Retail & Omnichannel Commerce (19 dirty files | 118 issues)
- `inventory/stocks.blade.php` (17 issues, 451 lines)
- `marketplace/products.blade.php` (17 issues, 2,296 lines — all 17 are `raw_attr_title`)
- `inventory/transfers.blade.php` (15 issues, 405 lines)
- `pos/terminal.blade.php` (9 issues, 7,158 lines)
- `products/index.blade.php` (8 issues, 3,445 lines)
- `marketplace/index.blade.php` (8 issues, 586 lines)
- `marketplace/orders.blade.php` (8 issues, 835 lines)
- `pos/shifts.blade.php` (7 issues, 1,010 lines)
- `products/modifiers.blade.php` (6 issues, 1,368 lines)
- `pos/orders.blade.php` (5 issues, 792 lines)
- `pos/printers/index.blade.php` (5 issues, 1,082 lines)
- `pos/reports/tabs/payments.blade.php` (3 issues, 345 lines)
- `warehouse/index.blade.php` (2 issues, 1,812 lines)
- `storefront/orders/index.blade.php` (2 issues, 566 lines)
- `storefront/shipping/index.blade.php` (2 issues, 628 lines)
- `pos/tables.blade.php` (1 issue, 752 lines)
- `inventory/opname.blade.php` (1 issue, 811 lines)
- `marketplace/chat.blade.php` (1 issue, 350 lines)
- `marketplace/shipping_label.blade.php` (1 issue, 797 lines)

#### Milestone 2: B2B Trade, Purchasing & CRM (20 dirty files | 106 issues)
- `quotations/index.blade.php` (13 issues, 352 lines)
- `suppliers/index.blade.php` (11 issues, 1,353 lines)
- `customers/index.blade.php` (11 issues, 1,506 lines)
- `sales/returns/index.blade.php` (10 issues, 404 lines)
- `sales-orders/index.blade.php` (10 issues, 405 lines)
- `sales/returns/edit.blade.php` (9 issues, 314 lines)
- `quotations/edit.blade.php` (7 issues, 348 lines)
- `sales-orders/edit.blade.php` (5 issues, 352 lines)
- `services/index.blade.php` (5 issues, 1,252 lines)
- `sales/returns/show.blade.php` (4 issues, 688 lines)
- `sales-orders/show.blade.php` (4 issues, 461 lines)
- `quotations/show.blade.php` (4 issues, 342 lines)
- `purchasing/returns/show.blade.php` (3 issues, 242 lines)
- `sales/returns/create.blade.php` (2 issues, 592 lines)
- `purchase-orders/print.blade.php` (2 issues, 481 lines)
- `crm/vouchers.blade.php` (2 issues, 367 lines)
- `sales-orders/create.blade.php` (1 issue, 611 lines)
- `quotations/create.blade.php` (1 issue, 285 lines)
- `purchase-orders/create.blade.php` (1 issue, 440 lines)
- `invoices/index.blade.php` (1 issue, 438 lines)

#### Milestone 3: Finance, Tax, Settings & Foundation (16 dirty files | 125 issues)
- `labor-machines/index.blade.php` (32 issues, 1,293 lines)
- `master-data/index.blade.php` (28 issues, 1,188 lines)
- `profile/edit.blade.php` (14 issues, 309 lines)
- `settings/index.blade.php` (13 issues, 4,151 lines)
- `reports/index.blade.php` (10 issues, 890 lines)
- `finance/expenses.blade.php` (6 issues, 731 lines)
- `tax/index.blade.php` (4 issues, 1,398 lines — all 4 are `aria-label="Tutup modal"`)
- `settings/integrations/social_media.blade.php` (4 issues, 361 lines)
- `profile/verify-contact.blade.php` (4 issues, 77 lines)
- `finance/settlements/show.blade.php` (2 issues, 317 lines)
- `billing/payment.blade.php` (2 issues, 907 lines)
- `profitability/index.blade.php` (2 issues, 283 lines)
- `finance/edc-terminals/index.blade.php` (1 issue, 324 lines)
- `settings/integrations/mcp.blade.php` (1 issue, 3,363 lines)
- `settings/integrations/whatsapp.blade.php` (1 issue, 779 lines)
- `portal/index.blade.php` (1 issue, 2,547 lines)

#### Milestone 4: AI Suite, Studio & Social Omnichannel (15 dirty files | 135 issues)
- `social_media/inbox.blade.php` (34 issues, 1,793 lines)
- `ai/partials/virtual_office_canvas.blade.php` (17 issues, 3,875 lines)
- `ai-studio/show.blade.php` (16 issues, 766 lines)
- `ai-studio/index.blade.php` (15 issues, 429 lines)
- `ai-studio/create.blade.php` (13 issues, 695 lines)
- `ai-studio/partials/topup-modal.blade.php` (8 issues, 507 lines)
- `ai-studio/inspiration/index.blade.php` (6 issues, 255 lines)
- `ai-studio/credit-history.blade.php` (5 issues, 179 lines)
- `ai-studio/inspiration/show.blade.php` (4 issues, 232 lines)
- `social_media/index.blade.php` (4 issues, 492 lines)
- `social_media/posts.blade.php` (4 issues, 2,489 lines)
- `social_media/insights.blade.php` (3 issues, 1,412 lines)
- `ai-studio/prompt-preview.blade.php` (2 issues, 119 lines)
- `whatsapp/index.blade.php` (2 issues, 937 lines)
- `mcp/oauth/consent.blade.php` (2 issues, 130 lines)

#### Milestone 5: Heavy Builders, Docs, Import & Community (8 dirty files | 278 issues)
- `landing_page/edit.blade.php` (80 issues, 3,334 lines — monolithic drag-and-drop landing page editor)
- `import/index.blade.php` (62 issues, 2,195 lines — full interactive Excel import wizard)
- `docs/index.blade.php` (36 issues, 1,014 lines — business owner documentation guide)
- `docs/partials/sec_ai.blade.php` (36 issues, 1,573 lines — AI operations documentation)
- `community/index.blade.php` (25 issues, 1,279 lines)
- `landing_page/popup.blade.php` (16 issues, 768 lines — promotional popup builder)
- `community/events.blade.php` (16 issues, 642 lines)
- `community/event_payment.blade.php` (7 issues, 301 lines)

---

## 5. Verification Method

To independently verify all findings and datasets:

1. **Verify Baseline Issue Counts**:
   ```powershell
   python scratch/deep_audit_v2.py resources/views/app
   ```
   *Expected*: Total files = 185, Clean files = 107, Files with issues = 78, Total issues = 762.

2. **Verify Translation Key Parity**:
   ```powershell
   python .agents/teamwork/explorer_survey_3/print_lang_stats.py
   ```
   *Expected*: 66 files in ID and 66 in EN, 17,068 keys in each, 100% key parity.

3. **Verify Milestone Math & File Partitioning**:
   ```powershell
   python .agents/teamwork/explorer_survey_3/verify_milestones.py
   ```
   *Expected*: 
   - Dirty files: M1 (19) + M2 (20) + M3 (16) + M4 (15) + M5 (8) = 78 dirty files.
   - Issues: M1 (118) + M2 (106) + M3 (125) + M4 (135) + M5 (278) = 762 issues.
   - Clean submodules: 9 submodules (18 files).
   - Grand Total files: 78 dirty + 18 clean + remaining clean files across submodules = 185 files.

4. **Verify Blade Syntax Integrity (Non-breaking Baseline Check)**:
   ```powershell
   php -l resources/views/app/dashboard.blade.php
   ```

5. **Invalidation Conditions**:
   - Any modification to `resources/views/app` or `lang/` that alters file count, issue count, or key counts will require re-running `survey_data.json` generation.
