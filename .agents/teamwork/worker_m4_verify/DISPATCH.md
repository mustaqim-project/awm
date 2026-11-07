## 2026-11-07T14:58:57Z
You are the Test Suite Verification & Production Readiness Worker for COOCA.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_m4_verify
Read ORIGINAL_REQUEST.md at: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Read PROJECT.md at: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE WRITE OWNERSHIP:
- tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php

TASK OBJECTIVES:
1. Inspect `tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`:
   - Specifically investigate any test assertion failures in `test_customer_ui_auto_hides_b2b_fields_for_fnb_or_non_b2b_business` or line 498.
   - If `assertDontSeeText('NPWP Perusahaan')` fails because `<head>` injects `window.COOCA_I18N = @json(...)` containing all dictionary strings, adjust the assertion to check that the actual form inputs / DOM elements are hidden (`assertDontSee('name="tax_id"')`, `assertDontSee('name="b2b_tier"')`, etc.) or inspect what the test intended.
   - Ensure `CrmAndCustomerComprehensiveSecurityTest.php` passes cleanly without skipping valid checks.
2. Run automated test suites for all affected modules:
   - `php artisan test --filter=Community`
   - `php artisan test --filter=Crm`
   - `php artisan test --filter=Customer`
   - `php artisan test --filter=Documentation`
   - `php artisan test tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php`
3. Run `php -l` on all modified/created files in this project:
   - database/seeders/RbacSeeder.php
   - app/Http/Controllers/Web/CommunityWebController.php
   - routes/owner.php
   - resources/views/app/community/events.blade.php
   - resources/views/app/community/index.blade.php
   - resources/views/app/community/event_payment.blade.php
   - resources/views/app/crm/members.blade.php
   - resources/views/app/crm/vouchers.blade.php
   - resources/views/app/customers/index.blade.php
   - resources/views/app/docs/partials/sec_customers.blade.php
   - resources/views/app/docs/partials/sec_crm.blade.php
   - resources/views/app/docs/partials/sec_community.blade.php
   - resources/views/app/docs/index.blade.php
   - tests/Feature/CrmAndCustomerComprehensiveSecurityTest.php
4. Compile your verified results into:
   c:\laragon\www\cooca_core\.agents\teamwork\worker_m4_verify\handoff.md

Report back via send_message when complete.
