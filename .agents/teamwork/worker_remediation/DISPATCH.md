## 2026-11-08T20:34:24Z
You are a software remediation subagent (teamwork_worker).

MANDATORY FIRST STEP:
Read the authoritative request file at:
`c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md`

Read the review feedback report:
`c:\laragon\www\cooca_core\.agents\teamwork\reviewer_1_rbac\handoff.md`

Your working directory is:
`c:\laragon\www\cooca_core\.agents\teamwork\worker_remediation\`

Update your `progress.md` in your working directory as you complete each step.

EXCLUSIVE FILE OWNERSHIP (You may modify ONLY these files):
- `resources/views/app/hrm/payroll/show.blade.php`
- `resources/views/app/hrm/partials/tab-payrolls.blade.php`
- `tests/Feature/RbacTest.php`

Tasks:
1. **View Permission Guard Alignment in `resources/views/app/hrm/payroll/show.blade.php`**:
   - Lines 68 and 121: Change `@if(\App\Support\Context::hasPermission('users.manage'))` to:
     `@if(\App\Support\Context::hasPermission('hrm.payroll.manage') || \App\Support\Context::hasPermission('users.manage'))`
   - This ensures finance roles possessing `hrm.payroll.manage` can approve and disburse payrolls in the UI.
2. **View Permission Guard Alignment in `resources/views/app/hrm/partials/tab-payrolls.blade.php`**:
   - Lines 74 and 171: Wrap the draft payroll delete button/form with:
     `@if(\App\Support\Context::hasPermission('hrm.payroll.manage') || \App\Support\Context::hasPermission('users.manage'))`
     `...`
     `@endif`
3. **Fix `tests/Feature/RbacTest.php`**:
   - In `test_owner_can_add_member_and_update_role`:
     The Free plan entitlement limit (`TIER_USER_PER_OWNER_LIMITS[TIER_FREE]`) is 1 non-owner member. Currently, only the owner exists, so `countOwnerUsers($owner)` is 0, which allows adding 1 staff member.
     To test the intended limit check: create and attach 1 initial staff member (`$existingMember = User::create([...]); $business->users()->attach($existingMember->id, ['id' => (string) Str::uuid(), 'role' => 'staff']);`). Then attempt to add a 2nd member (`calonMember`), which will properly trigger HTTP 403 Forbidden with `RESOURCE_LIMIT_EXCEEDED`!
     Then upgrade to Core (`upgradeToCore($business)`), and adding the member succeeds.
   - Run `php artisan test tests/Feature/RbacTest.php` and verify all 3 tests pass 100%.
4. **Verification**:
   - Run `php artisan view:clear; php artisan view:cache`
   - Run `php artisan test tests/Feature/RbacTest.php`
   - Run `php artisan test tests/Feature/HrmImportInventoryComprehensiveSecurityTest.php`
   - Deliver `handoff.md` with complete command outputs.
