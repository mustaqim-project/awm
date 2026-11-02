## 2026-11-02T05:30:43Z
You are worker_backend_m1, a teamwork_worker subagent.
Your role: Backend Implementation Worker for cooca_core (Milestone 1).
Your working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_backend_m1\

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

MANDATORY FIRST STEP:
Read ORIGINAL_REQUEST.md at c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Read PROJECT.md at c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md
Read the surveyor findings at:
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\report.md
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\report.md

EXCLUSIVE FILE OWNERSHIP:
You own exclusively:
- app/Http/Controllers/Api/V1/Pos/PosExpenseApiController.php (create)
- app/Http/Controllers/Api/V1/Owner/OwnerApprovalApiController.php (create)
- routes/api.php (add new route definitions)
- tests/Feature/Api/PosExpenseApiTest.php (create)
- tests/Feature/Api/OwnerApprovalApiTest.php (create)

TASK SPECIFICATIONS:

1. Endpoint POST /api/v1/pos/expenses:
   - Location: app/Http/Controllers/Api/V1/Pos/PosExpenseApiController.php
   - Route in routes/api.php under prefix 'pos' guarded by 'auth:sanctum', 'business.active', and 'require.permission:pos.terminal,pos.orders'.
   - Validate request:
     - pos_shift_id: required, integer
     - amount: required, numeric, min: 1
     - category: required, string, max: 100
     - notes: required, string, max: 500
     - receipt_image: optional, file, mimes:jpeg,png,jpg,webp, max:5120
     - receipt_image_base64: optional, string
   - Active Shift Check: Look up PosShift for the active business ($business->id). If not found or status != 'open', return 422 JSON error: `Shift kasir telah ditutup atau tidak aktif.`
   - Receipt Storage: Save receipt file or decode base64 to TenantStorage::publicDir($business, TenantStorage::FOLDER_EXPENSES) (or storage/app/public/bisnis/{slug}/expenses) and generate public receipt_url.
   - Cash Movement: Call PosShiftService::recordCashMovement($shift, PosCashMovement::TYPE_CASH_OUT, (float)$request->amount, (string)$request->notes, $request->user()->id).
   - Cash Ledger Mutation: Call CashLedgerService::recordOutflow($business, (float)$request->amount, 'pos_shift_expense', (string)$shift->id, (string)$request->notes, 'cash', (string)$request->user()->id). If CashAccount is needed, look up default cash account via CashAccount::firstOrCreateForBusinessDefault(...) or CashLedgerService.
   - Return 201 Created with JSON structure per PROJECT.md interface contract.

2. Endpoint GET /api/v1/owner/approvals/pending and POST /api/v1/owner/approvals/{id}/action:
   - Location: app/Http/Controllers/Api/V1/Owner/OwnerApprovalApiController.php
   - Route in routes/api.php under prefix 'owner' guarded by 'auth:sanctum', 'business.active', and 'require.role:owner,admin'.
   - GET /api/v1/owner/approvals/pending:
     - Aggregates pending records from:
       1) PosApprovalRequest::where('business_id', $business->id)->where('status', 'pending')->get()
       2) AttendanceCorrection::where('business_id', $business->id)->where('status', 'pending')->get()
       3) ApprovalRequest::where('business_id', $business->id)->where('status', 'pending')->get()
     - Formats into unified array with fields: id, type ('void', 'discount', 'attendance', 'document'), source, reference_id, title, description, amount, requester, meta, created_at.
     - Returns 200 OK with count and data.
   - POST /api/v1/owner/approvals/{id}/action:
     - URL param {id} may be compound like 'pos_approval_12' or 'attendance_correction_4' or parsed cleanly.
     - Payload: action ('approve' or 'reject'), reason (optional on approve, required on reject).
     - Idempotency guard: if the request is already approved or rejected, return 422 `Permintaan persetujuan telah diproses sebelumnya.`
     - State mutation:
       - If PosApprovalRequest: update status, approved_by, approved_at, rejection_reason. If action is approve and action_type is 'void' or 'refund', update corresponding PosOrder status to 'voided' or 'cancelled'.
       - If AttendanceCorrection: update status, reviewed_by, review_notes.
       - If ApprovalRequest: call ApprovalWorkflowService or update status.
     - Return 200 OK with updated approval status.

3. Automated Test Suite:
   - Create tests/Feature/Api/PosExpenseApiTest.php (PHPUnit test class extending Tests\TestCase with RefreshDatabase).
   - Create tests/Feature/Api/OwnerApprovalApiTest.php (PHPUnit test class extending Tests\TestCase with RefreshDatabase).
   - Cover all acceptance criteria:
     - Unauthorized/unauthenticated rejection (401/403)
     - Shift validation (cannot record expense on closed shift -> 422)
     - Valid expense creation with receipt file & base64
     - Verification that PosCashMovement was created and CashTransaction outflow recorded
     - Listing pending approvals
     - Approving and rejecting approval requests
     - Idempotency (cannot process already resolved approval)
   - RUN the tests using php vendor/phpunit/phpunit/phpunit tests/Feature/Api/PosExpenseApiTest.php and tests/Feature/Api/OwnerApprovalApiTest.php.
   - Ensure 100% of test cases pass!
