# Project: Cooca Multi-Language Localization Remediation

## Architecture
- **Target Scope**:
  - `resources/views/app` (185 Blade files, 134,824 lines of code across 44 submodules)
  - `resources/views/admin` (87 Blade files, 40,409 lines of code across 30 submodules)
  - Modular language files in `lang/id/*.php` and `lang/en/*.php` (66 symmetric domain files)
- **Technology Stack**: Laravel 11, Blade, Alpine.js, Tailwind CSS, Python audit engine.
- **Verification Gate**:
  - `python scratch/deep_audit_v2.py resources/views/app` reports 0 defects.
  - `python scratch/deep_audit_v2.py resources/views/admin` reports 0 defects.
  - `php scratch/fast_syntax_check.php` reports 0 syntax errors.
  - `lang/id/*.php` and `lang/en/*.php` maintain 100.00% key parity with no missing translations.
  - `php artisan view:clear` and `php artisan view:cache` execute without error.

## Feature Inventory
| # | Feature / Work Item | Description | Milestone | Source |
|---|---------------------|-------------|-----------|--------|
| 1 | Admin Core, Auth, Tenant Ops, B2B & Config Localization | Remediate 48 files across admin/dashboard, auth, roles, users, profile, pricing-programs, account-recoveries, subscriptions, billing-packages, businesses, promos, b2b, settings, ai_tokens, ai-studio, error-logs. | M1 | survey |
| 2 | Admin Finance, CMS, Marketing & Communication Mega-Files | Remediate 38 files across admin/finance (18 files), posts (3), events (4), templates (3), payment_accounts (3), settlements (2), legal-pages (2), feedback (2), leads (1), word_filters (1), and mega-files inbox (1), whatsapp (2), social_media (1). | M2 | survey |
| 3 | App Commerce, Trade, Purchasing & CRM Localization | Remediate 39 dirty files in app/pos, inventory, storefront, marketplace, products, warehouse, sales, sales-orders, quotations, purchasing, purchase-orders, invoices, customers, suppliers, services, crm. | M3 | survey |
| 4 | App Finance, Settings, AI Suite & Social Omnichannel Localization | Remediate 31 dirty files in app/finance, billing, tax, settings, profile, master-data, labor-machines, reports, profitability, portal, ai, ai-studio, social_media, whatsapp, mcp. | M4 | survey |
| 5 | App Heavy Builders, Docs, Import & Community Localization | Remediate 8 heavy files in app/landing_page (builder & popup), docs (guide & sec_ai), import (wizard), community (index, events, payment). | M5 | survey |
| 6 | E2E Testing Suite & Quality Gate Infrastructure | Build automated test runner verifying deep audit 0-issue check, Blade syntax validation, language key resolution & bidirectional parity, and publish TEST_READY.md. | E2E Track | survey |
| 7 | Final 100% E2E Verification & Adversarial Hardening | Execute full test suite, verify zero issues on app and admin views, 100% translation parity, run view:cache, and audit for integrity. | M6 | survey |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| M1 | Admin Views Batch 1: Core, Tenant Ops, B2B, Settings & AI | 48 files in `resources/views/admin` (dashboard, auth, roles, users, profile, pricing, subscriptions, billing, b2b, settings, tokens) + register keys in `lang/{id,en}/admin.php` | none | DONE (44 views remediated, 0 issues, 1015 keys) |
| M2 | Admin Views Batch 2: Finance, Marketing, CMS & Channels | 38 files in `resources/views/admin` (finance, posts, events, templates, accounts, settlements, legal, feedback, inbox, whatsapp, social) + register keys in `lang/{id,en}/admin.php` | M1 | IN_PROGRESS (2e8610a1-a6d4-462c-b2e0-c41004b00da0) |
| M3 | App Views Batch 1: Commerce, Trade, Purchasing & CRM | 39 dirty files in `resources/views/app` (pos, inventory, storefront, marketplace, products, warehouse, sales, so, quotes, purchasing, customers, suppliers, crm) + register keys in domain lang files | none | IN_PROGRESS (266c7b7e-e030-4d18-a3bc-3f4a61dd1a0e) |
| M4 | App Views Batch 2: Finance, Settings, AI Suite & Social | 31 dirty files in `resources/views/app` (finance, billing, tax, settings, profile, master-data, labor-machines, ai, ai-studio, social_media, whatsapp) + register keys in domain lang files | none | PLANNED |
| M5 | App Views Batch 3: Heavy Builders, Docs, Import & Community | 8 heavy files in `resources/views/app` (landing_page, docs, import, community) + register keys in domain lang files | none | PLANNED |
| M6 | Final Acceptance, Parity Audit & Adversarial Hardening | Pass 100% deep_audit_v2 on app & admin, 0 syntax errors, 100% key parity, view:cache, forensic integrity audit | M1, M2, M3, M4, M5, E2E | PLANNED |

## Interface Contracts
### View Templates ↔ Language Files (`lang/id/*.php` and `lang/en/*.php`)
- **Language Helper**:
  - In Blade HTML: `{{ __('domain.key') }}` or `@lang('domain.key')`.
  - In existing Blade `{{ ... }}` or PHP: `__('domain.key')` (NEVER nest `{{ ... }}`).
  - In Alpine.js / inline JS: `@json(__('domain.key'))` to prevent unescaped quote syntax errors.
  - In HTML attributes: `placeholder="{{ __('domain.key') }}"` or `:placeholder="@json(__('domain.key'))"`.
- **Key Registration**:
  - Keys must be registered in both `lang/id/<domain>.php` and `lang/en/<domain>.php` with 100% symmetry.
  - Admin keys reside in `lang/{id,en}/admin.php`.
  - App keys reside in respective domain files (`pos.php`, `sales.php`, `finance.php`, `ai.php`, `common.php`, etc.).

## Code Layout
- `resources/views/admin/` — Admin Blade templates (87 files)
- `resources/views/app/` — App Blade templates (185 files)
- `lang/id/*.php` — Indonesian modular translation files (66 files)
- `lang/en/*.php` — English modular translation files (66 files)
- `scratch/deep_audit_v2.py` — Official audit validation script
- `scratch/fast_syntax_check.php` — Rapid tokenization syntax validator
- `scratch/verify_localization_suite.py` — E2E test runner harness
