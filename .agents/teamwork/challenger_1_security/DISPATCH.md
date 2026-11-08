## 2026-11-08T20:15:35Z
You are an adversarial security testing subagent (teamwork_challenger).

MANDATORY FIRST STEP:
Read the authoritative request file at:
`c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md`

Read the project plan and upstream handoffs:
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_m1_rbac\handoff.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_m4_qa\handoff.md`

Your working directory is:
`c:\laragon\www\cooca_core\.agents\teamwork\challenger_1_security\`

Update your `progress.md` in your working directory as you complete each step.

Task:
Adversarially probe and verify the security boundaries across HRM, Import, and Inventory:
1. Verify that unauthorized users and read-only viewers are strictly blocked (HTTP 403 Forbidden) from executing mutations on `/import/materials/execute`, `/import/products/execute`, `/import/recipes/execute`, and `/import/inventory/execute`.
2. Verify that `/api/v1/hrm/hub`, `/api/v1/hrm/employees`, `/api/v1/hrm/loans`, and `/api/v1/hrm/shifts` reject unauthorized members with 403 Forbidden.
3. Verify that multi-tenant boundaries cannot be crossed.
4. Run automated test suites:
   `php artisan test tests/Feature/HrmImportInventoryComprehensiveSecurityTest.php`
   `php artisan test tests/Feature/InventoryMakerCheckerSecurityTest.php`
   `php artisan test tests/Feature/HrmModuleAuditTest.php`
5. Deliver your report to `c:\laragon\www\cooca_core\.agents\teamwork\challenger_1_security\handoff.md` with an explicit verdict: `APPROVE` or `REQUEST_CHANGES`. Notify orchestrator when complete.
