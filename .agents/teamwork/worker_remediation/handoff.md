# Handoff Report: RBAC View Guard Remediation and RbacTest Fix

**Agent**: `worker_remediation`  
**Roles**: implementer, qa, specialist  
**Date**: 2026-11-08  
**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\worker_remediation\`  

---

## 1. Observation

### 1.1 Pre-Remediation Code Inspection & Verification
1. **`resources/views/app/hrm/payroll/show.blade.php`**:
   - Lines 68 and 121 previously used:
     `@if(\App\Support\Context::hasPermission('users.manage'))`
   - Observation: Users holding the granular permission `hrm.payroll.manage` (e.g. `finance` role) were blocked in the UI from viewing the Approve, Delete Draft, and Pay Payroll action buttons, despite backend controllers permitting them.

2. **`resources/views/app/hrm/partials/tab-payrolls.blade.php`**:
   - Lines 74 and 171 previously contained:
     `@if($p->status === 'draft')`
     wrapping the delete form without checking user permissions.
   - Observation: Read-only users with `hrm.payroll.view` saw a red trash button in the UI that yielded a 403 upon submission.

3. **`tests/Feature/RbacTest.php`**:
   - In `test_owner_can_add_member_and_update_role` (line 43), the test suite previously set up only an `$owner` and then attempted to add a staff member `$calonMember` expecting HTTP 403 `RESOURCE_LIMIT_EXCEEDED`.
   - Running `php artisan test tests/Feature/RbacTest.php` prior to remediation produced:
     ```json
     {"tool":"phpunit","result":"failed","tests":3,"passed":2,"assertions":15,"duration_ms":16295,"failed":1,"failures":[{"test":"Tests\\Feature\\RbacTest::test_owner_can_add_member_and_update_role","file":"C:\\laragon\\www\\cooca_core\\tests\\Feature\\RbacTest.php","line":43,"message":"Expected response status code [403] but received 201.\nFailed asserting that 201 is identical to 403."}]}
     ```
   - Direct inspection of `app/Domain/Billing/EntitlementService.php` revealed:
     - `countOwnerUsers($owner)` filters `where('role', '!=', 'owner')`. With only the owner, count was 0.
     - `TIER_USER_PER_OWNER_LIMITS[TIER_FREE]` is 1. Since `0 < 1`, adding the first staff member returns HTTP 201 Created.

---

## 2. Logic Chain

1. **View Permission Guard Alignment in `payroll/show.blade.php`**:
   - Updating lines 68 and 121 to:
     `@if(\App\Support\Context::hasPermission('hrm.payroll.manage') || \App\Support\Context::hasPermission('users.manage'))`
     allows both finance personnel with `hrm.payroll.manage` and administrative personnel with `users.manage` to view and trigger Approve, Delete Draft, and Pay actions.
2. **View Permission Guard Alignment in `tab-payrolls.blade.php`**:
   - Wrapping the desktop table delete button (line 74) and mobile card delete button (line 172) with:
     `@if(\App\Support\Context::hasPermission('hrm.payroll.manage') || \App\Support\Context::hasPermission('users.manage'))`
     ensures unauthorized users (e.g. read-only payroll viewers or cashiers) do not see the deletion trigger.
3. **Genuine Entitlement Limit Testing in `RbacTest.php`**:
   - Because Free plan entitlement permits 1 non-owner member (`TIER_USER_PER_OWNER_LIMITS[TIER_FREE] = 1`), attaching 1 initial staff member `$existingMember` brings the owner's active member count to 1 (`role != 'owner'`).
   - Consequently, when the owner attempts to add a 2nd member `$calonMember`, `EntitlementService::canAddMember($business)` evaluates `1 < 1` as false, causing `MemberController::store` to reject the request with HTTP 403 Forbidden and `error_code: RESOURCE_LIMIT_EXCEEDED`.
   - After `upgradeToCore($business)`, the tier limit expands, allowing `$calonMember` to be added (HTTP 201 Created) and subsequently promoted to `admin` (HTTP 200 OK).

---

## 3. Caveats

- Remediations were strictly constrained to the three assigned files (`resources/views/app/hrm/payroll/show.blade.php`, `resources/views/app/hrm/partials/tab-payrolls.blade.php`, and `tests/Feature/RbacTest.php`).
- No core domain logic in `EntitlementService` or controllers required modification because the business logic correctly implements the Free plan non-owner member limit (1 non-owner member allowed).

---

## 4. Conclusion

All three remediation tasks have been completed and verified with genuine test and build executions:
1. `payroll/show.blade.php` guards aligned with `hrm.payroll.manage` and `users.manage`.
2. `tab-payrolls.blade.php` draft deletion buttons guarded with `hrm.payroll.manage` and `users.manage`.
3. `tests/Feature/RbacTest.php` correctly tests Free plan limit exhaustion and Core upgrade transition.
4. View cache compiles with 0 errors, and all test suites pass 100%.

### Verification Command Outputs Verbatim:

#### 1. Blade View Compilation & Cache:
Command: `php artisan view:clear; php artisan view:cache`
```
INFO Compiled views cleared successfully.

INFO Blade templates cached successfully.
```
Exit code: 0.

#### 2. RbacTest Feature Suite:
Command: `php artisan test tests/Feature/RbacTest.php`
```json
{"tool":"phpunit","result":"passed","tests":3,"passed":3,"assertions":23,"duration_ms":16522}
```
Exit code: 0 (3 passed, 23 assertions, 0 failures).

#### 3. Comprehensive Security Integration Suite:
Command: `php artisan test tests/Feature/HrmImportInventoryComprehensiveSecurityTest.php`
```json
{"tool":"phpunit","result":"passed","tests":8,"passed":8,"assertions":57,"duration_ms":44126}
```
Exit code: 0 (8 passed, 57 assertions, 0 failures).

---

## 5. Verification Method

To independently verify this remediation:

```bash
# 1. Verify Blade views cache without error
php artisan view:clear
php artisan view:cache

# 2. Run RBAC feature test suite
php artisan test tests/Feature/RbacTest.php

# 3. Run Comprehensive RBAC & Security integration suite
php artisan test tests/Feature/HrmImportInventoryComprehensiveSecurityTest.php

# 4. Review git diff for surgical changes
git diff resources/views/app/hrm/payroll/show.blade.php resources/views/app/hrm/partials/tab-payrolls.blade.php tests/Feature/RbacTest.php
```
