## 2026-11-08T19:44:22Z
You are a software QA and module audit implementation subagent (teamwork_worker).

MANDATORY FIRST STEP:
Read the authoritative request file at:
`c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md`

Read the project plan and technical reports at:
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_audit_docs\handoff.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_m1_rbac\handoff.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_m2_ui\handoff.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_m3_docs\handoff.md`

Your working directory is:
`c:\laragon\www\cooca_core\.agents\teamwork\worker_m4_qa\`

Update your `progress.md` in your working directory as you complete each step.

EXCLUSIVE FILE OWNERSHIP (You may modify ONLY these files):
- `app/Http/Controllers/Web/Inventory/InventoryWebController.php`
- `app/Http/Controllers/Web/Hrm/HrmWebController.php`
- `tests/Feature/ProductAndRecipeImportTest.php`
- `tests/Feature/HrmAttendanceBiometricAndExceptionTest.php`
- `tests/Feature/HrmImportInventoryComprehensiveSecurityTest.php` (new file)

Tasks (Features F12, F13, F14, F19):
1. **Multi-Step Mutation Transaction Wrapping (F12)**:
   - In `app/Http/Controllers/Web/Inventory/InventoryWebController.php`:
     - Wrap `quickAdjust` (around lines 251–296 where StockAdjustment::create, StockAdjustmentItem::create, recordMovement, and recordStockAdjustmentJournal execute) inside `\Illuminate\Support\Facades\DB::transaction(function () use (...) { ... });`.
     - Wrap `storeTransfer` (around lines 702–726 where StockTransfer::create and the StockTransferItem loop execute) inside `DB::transaction(...)`.
   - In `app/Http/Controllers/Web/Hrm/HrmWebController.php`:
     - Wrap `storeEmployee` (around lines 345–365 where User::create and BusinessMembership::create execute) inside `DB::transaction(...)`.
2. **Synchronize Existing Test Suites (F13, F14)**:
   - In `tests/Feature/ProductAndRecipeImportTest.php`:
     - Update lines 113, 145, 167, 189, 218, 324 to match `DataExchangeService` sheet names: `INPUT_BAHAN_BAKU`, `INPUT_PRODUK`, `INPUT_RESEP_BOM`, `INPUT_SALDO_STOK`, and `DATA_REFERENSI`.
   - In `tests/Feature/HrmAttendanceBiometricAndExceptionTest.php`:
     - For tests enforcing face biometrics, ensure test fixtures register a dummy face embedding or handle the biometric requirement so clock-in assertions run cleanly without unhandled precondition exceptions.
3. **Author Comprehensive Security & Integration Test Suite (F19)**:
   - Create `tests/Feature/HrmImportInventoryComprehensiveSecurityTest.php`:
     - Test that unauthorized/viewer users receive 403 Forbidden when calling POST `/import/materials/execute`, `/import/products/execute`, `/import/recipes/execute`, and `/import/inventory/execute`.
     - Test that users with `materials.manage`, `products.manage`, `inventory.manage`, or `import.manage` can successfully execute imports.
     - Test that unauthorized users receive 403 Forbidden when calling `/api/v1/hrm/hub`, `/api/v1/hrm/employees`, `/api/v1/hrm/loans`.
     - Test that users with `hrm.view` or `users.view` can access `/hrm` index.
     - Test that users with `hrm.payroll.view` or `users.view` can access payroll details.
     - Test that `sec_hrm.blade.php`, `sec_import.blade.php`, and `sec_inventory.blade.php` render properly in docs.
4. **Execution & Verification**:
   - Run `php artisan test tests/Feature/HrmImportInventoryComprehensiveSecurityTest.php`
   - Run `php artisan test tests/Feature/ProductAndRecipeImportTest.php`
   - Run `php artisan test tests/Feature/HrmModuleAuditTest.php`
   - Run `php artisan test tests/Feature/InventoryModuleAuditTest.php`
   - Ensure all tests pass 100% with zero regressions.

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Deliver your handoff report to `c:\laragon\www\cooca_core\.agents\teamwork\worker_m4_qa\handoff.md` and message the orchestrator when complete.

## 2026-11-08T20:08:05Z
**Context**: Milestone M4 QA & Test Verification
**Content**: Status check on full test suite run and `handoff.md` generation.
**Action**: Please report status, any test results or obstacles, update `progress.md`, and deliver `handoff.md`.

