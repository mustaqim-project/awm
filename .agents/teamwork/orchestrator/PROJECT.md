# Project: Community, CRM, and Customers Modules Refactor, RBAC & Documentation

## Architecture
- **Framework**: Laravel 11.x multi-tenant SaaS ERP (Cooca Core).
- **Presentation Layer**: Blade templates with Tailwind CSS, Alpine.js, Lucide icons, and Apple HIG responsive design principles.
- **Authorization Engine**: Spatie-based multi-tenant RBAC with `AppServiceProvider` `Gate::before` delegation to `\App\Support\Context::hasPermission(...)`, supporting native Blade `@can(...)` directives and controller `HasMiddleware` with `Middleware('require.permission:...')`.
- **Documentation Engine**: `DocumentationWebController` and `resources/views/app/docs/` with Bento Apple HIG partials, interactive verified `route(...)` links, and 20-industry practical implementation matrices.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| F1 | RBAC Seeder Registration | Register `community.view`, `community.post`, `community.events.register`, `community.manage` in `RbacSeeder.php` with proper role mappings | M1 | Survey (Explorer 2) |
| F2 | Controller Authorization Guards (Community) | Implement `HasMiddleware` in `CommunityWebController.php` protecting all 16 methods with appropriate permission middleware | M1 | Survey (Explorer 2) |
| F3 | Route Protection Alignment (Community) | Verify and align community route definitions in `routes/owner.php` with permission guards | M1 | Survey (Explorer 2) |
| F4 | Blade Action Guarding (Community) | Guard post creation, delete, comment, event register, and check-in buttons in `community/index`, `events`, `event_payment` with `@can(...)` | M1 | Survey (Explorers 1, 2) |
| F5 | Blade Action Guarding (Customers) | Guard Modal 2 (Edit Pelanggan template) and Detail Modal Edit button in `customers/index.blade.php` with `@can('customers.edit')` | M1 | Survey (Explorers 1, 2) |
| F6 | UI Simplification & Double Header Elimination | Eliminate duplicate topbar header title vs inner banner `<h1>` in `community/events.blade.php`; standardize breadcrumbs | M2 | Survey (Explorer 1) |
| F7 | Action Button Hierarchy & Anti-Clutter | Eliminate triple redundant "Buat Voucher" buttons in `crm/vouchers.blade.php`; distinguish primary vs secondary actions | M2 | Survey (Explorer 1) |
| F8 | Mobile KPI Horizontal Snap Sliders | Convert vertically stacking Bento KPI cards in `crm/members.blade.php` and `crm/vouchers.blade.php` to responsive horizontal snap sliders on mobile (375px) | M2 | Survey (Explorer 1) |
| F9 | Community Mobile Feed Ergonomics | Optimize mobile DOM ordering so feed content is prioritized above the left sidebar widget on small screens in `community/index.blade.php` | M2 | Survey (Explorer 1) |
| F10 | Action Button Ergonomics & Touch Targets | Ensure minimum 44px tap targets for buttons, pagination, carousel controls, and table actions across community, crm, and customers | M2 | Survey (Explorer 1) |
| F11 | Translation Domain Isolation | Replace 6 `purchasing.*` translation key references in `customers/index.blade.php` with canonical `customers.*` keys and register in `lang/id` & `lang/en` | M2 | Survey (Explorer 1, Challenger 2) |
| F12 | Customers Business Owner Guide | Create `resources/views/app/docs/partials/sec_customers.blade.php` with POV Business Owner, 4-step workflow, 20-industry matrix, debt management, and verified route links | M3 | Survey (Explorer 3) |
| F13 | CRM & Loyalty Business Owner Guide | Create `resources/views/app/docs/partials/sec_crm.blade.php` covering loyalty points, tiers, voucher generation, WhatsApp receipts, 20-industry matrix, and route links | M3 | Survey (Explorer 3) |
| F14 | Community Business Owner Guide | Create `resources/views/app/docs/partials/sec_community.blade.php` covering networking, business forums, QRIS events, digital ticket check-in, 20-industry matrix, and route links | M3 | Survey (Explorer 3) |
| F15 | Docs Navigation TOC Integration | Register `sec-customers`, `sec-crm`, and `sec-community` in `resources/views/app/docs/index.blade.php` Left Sidebar TOC with smooth scrolling links | M3 | Survey (Explorer 3) |
| F16 | Test Suite Fix & Coverage Verification | Address B2B i18n test assertion in `CrmAndCustomerComprehensiveSecurityTest.php`; run automated test suites for Community, CRM, Customers, and Docs | M4 | Survey (Explorer 3) |
| F17 | Syntax & Compilation Verification | Validate all modified and newly created Blade and PHP files with `php -l` and Blade compilation checks | M4 | Survey (Explorer 1, 3) |
| F18 | Multi-Perspective Quality Gate | Conduct independent review (2 Reviewers), adversarial challenge (2 Challengers), and forensic integrity audit (2 Auditors) | M4 | Project Pattern |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| 1 | RBAC Enforcement & Controller/View Guards | F1, F2, F3, F4, F5 | none | DONE |
| 2 | UI Simplification & Layout Optimization | F6, F7, F8, F9, F10, F11 | M1 | DONE |
| 3 | Business Owner Documentation | F12, F13, F14, F15 | none | DONE |
| 4 | Test Suites, Production Readiness & Forensic Audit Gate | F16, F17, F18 | M1, M2, M3 | DONE |

## Interface Contracts

### Multi-Tenant Authorization Contract
- Native Laravel Blade directives `@can('permission.name')` and `@cannot('permission.name')` delegate via `Gate::before` in `AppServiceProvider.php` to `\App\Support\Context::hasPermission('permission.name')`.
- Permissions registered in `database/seeders/RbacSeeder.php`:
  - `community.view`: View community feed, discussions, event list.
  - `community.post`: Create posts, reply, like, repost.
  - `community.events.register`: Register / buy tickets for events.
  - `community.manage`: Manage events, delete any post (moderation), scan event check-in QR.
  - `customers.view`: View customer list, cards, details.
  - `customers.create`: Add new customer.
  - `customers.edit`: Edit customer data.
  - `customers.delete`: Delete customer.
  - `customers.export`: Export customer data.
  - `crm.view`: View loyalty members and vouchers.
  - `crm.manage`: Configure loyalty rules, points, and create vouchers.
- All controller actions enforce authorization via `HasMiddleware` and `Middleware('require.permission:...')`.
- Read-only users (`*.view`) can view listings and records, but action buttons (create, edit, delete, register, export, voucher generation) MUST be hidden in Blade templates using `@can(...)`.

### Business Owner Documentation Contract
- Documentation partials located in `resources/views/app/docs/partials/sec_{module}.blade.php`.
- Each partial MUST contain:
  1. Header Bento with semantic icon and verified direct route link (`route('...')`).
  2. Executive Summary written in conversational Indonesian from Business Owner (UMKM) POV.
  3. 4-step practical workflow.
  4. 20-Industry Application Matrix across 6 standard clusters (F&B, Manufaktur, Ritel, Jasa Harian, Jasa Proyek, Distribusi/Agro).
  5. Security, authorization, and fraud prevention section.
  6. Practical FAQ.
- All links must use valid route names:
  - Customers: `route('customers.index')`
  - CRM: `route('crm.members')`, `route('crm.vouchers')`
  - Community: `route('community.index')`, `route('community.events')`

## Code Layout
- Controllers:
  - `app/Http/Controllers/Web/CommunityWebController.php`
  - `app/Http/Controllers/Web/Crm/CrmWebController.php`
  - `app/Http/Controllers/Web/Customer/CustomerWebController.php`
  - `app/Http/Controllers/Web/DocumentationWebController.php`
- Seeders:
  - `database/seeders/RbacSeeder.php`
- Routes:
  - `routes/owner.php`
  - `routes/web.php`
- Language Dictionaries:
  - `lang/id/customers.php`
  - `lang/en/customers.php`
- Blade Views:
  - `resources/views/app/community/index.blade.php`
  - `resources/views/app/community/events.blade.php`
  - `resources/views/app/community/event_payment.blade.php`
  - `resources/views/app/crm/members.blade.php`
  - `resources/views/app/crm/vouchers.blade.php`
  - `resources/views/app/customers/index.blade.php`
- Documentation Views:
  - `resources/views/app/docs/index.blade.php`
  - `resources/views/app/docs/partials/sec_customers.blade.php` (new)
  - `resources/views/app/docs/partials/sec_crm.blade.php` (new)
  - `resources/views/app/docs/partials/sec_community.blade.php` (new)
- Tests:
  - `tests/Feature/CommunityWebFeatureTest.php`
  - `tests/Feature/CommunityModuleAuditTest.php`
  - `tests/Feature/CommunityEventFeatureTest.php`
  - `tests/Feature/CommunityEventPaymentTest.php`
  - `tests/Feature/CrmWebFeatureTest.php`
  - `tests/Feature/CrmModuleAuditTest.php`
  - `tests/Feature/CustomerWebFeatureTest.php`
  - `tests/Feature/CustomerModuleAuditTest.php`
  - `tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`
  - `tests/Feature/AdversarialCompilationAndLayoutTest.php` (new)
  - `tests/Feature/AdversarialCommunityCrmCustomerTest.php` (new)
  - `tests/Feature/DocumentationFeatureTest.php`
