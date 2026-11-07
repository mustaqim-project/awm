# Original User Request

## Initial Request — 2026-11-07T11:29:54Z

Audit, simplify UI layouts, enforce RBAC authorization, and generate business owner documentation for the Community, CRM, and Customers modules in COOCA for production readiness.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. UI Simplification and Layout Optimization
Simplify views in `resources/views/app/community`, `resources/views/app/crm`, and `resources/views/app/customers` using UI simplification principles: eliminate overlapping elements, structure button hierarchy (primary vs secondary/tertiary actions), and ensure responsive, uncluttered layouts across mobile and desktop viewports.

### R2. Role-Based Access Control (RBAC) Enforcement
Audit and enforce RBAC authorization for the Community, CRM, and Customers modules at both controller and view levels: protect controller methods and routes with appropriate permission middleware/guards, register any missing permissions/seeders, and wrap view action buttons and links with authorization checks so unauthorized actions remain hidden.

### R3. Module Audit & Production Readiness
Audit the Community, CRM, and Customers modules for production readiness: verify error handling, input validation, empty states, flash feedback alerts, and data query consistency to ensure the modules operate reliably in production without regressions.

### R4. Business Owner User Documentation
Generate or update business owner user guidelines and documentation for Community, CRM, and Customers in `resources/views/app/docs/`: author the guides from the perspective of business owners (UMKM) using accessible language without technical jargon, and provide direct route links to the relevant module features.

## Verification Resources
- Project automated test suite (`php artisan test`)
- PHP syntax checks (`php -l`)

## Acceptance Criteria

### UI Layout & Responsiveness
- [ ] Views across `resources/views/app/community`, `resources/views/app/crm`, and `resources/views/app/customers` display with no overlapping elements or broken layouts on mobile (375px) and desktop (1280px) viewports.
- [ ] Action buttons have a clear hierarchy (primary action distinguished from secondary/tertiary actions) with no cramped or overlapping action rows.
- [ ] Meaningful empty states are displayed when no records exist.

### RBAC & Security
- [ ] All routes and controller actions for Community, CRM, and Customers are secured with controller-level permission guards or middleware.
- [ ] Action buttons, modification links, and sensitive controls in Blade views are protected with permission checks (`@can` or equivalent) and hidden from unauthorized users.
- [ ] Required permissions for Community, CRM, and Customers are registered in the system seeders / database schema.

### Production Readiness & Verification
- [ ] All modified and newly created PHP and Blade files pass syntax validation (`php -l`) without errors.
- [ ] Relevant automated tests (`php artisan test`) pass without failures.
- [ ] Flash notifications and validation error summaries are displayed consistently across module actions.

### Business Owner Documentation
- [ ] User guidelines for Community, CRM, and Customers are available in `resources/views/app/docs/`.
- [ ] Documentation includes practical feature walkthroughs and direct route links tailored for business owners.
