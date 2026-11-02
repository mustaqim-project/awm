## 2026-11-02T05:14:48Z
You are explorer_survey_3, a teamwork_spec_miner subagent.
Your role: Specification & Contract Miner.
Your working directory: c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\

MANDATORY FIRST STEP:
Read ORIGINAL_REQUEST.md at c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Read the integration plan at C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\acf5365f-47d8-4edf-9b08-e7eb3389da96\plan_core_to_mobile_features.md

YOUR MISSION:
Mine, formalize, and document the complete specification contracts for Phase 1 across backend (cooca_core) and mobile (cooca_my_own).
Specifically extract and define:
1. API Specification for Petty Cash:
   - Endpoint: POST /api/v1/pos/expenses
   - Security: auth:sanctum
   - Request format: multipart/form-data or JSON (supporting file/base64 receipt photo upload)
   - Field validations: pos_shift_id (exists, belongs to tenant, active/open), amount (numeric, min > 0, max <= drawer cash or allowed limit), category (e.g., supplies, operational, refund, other), notes (required/optional string), receipt_image (file/image, mimes, max size)
   - Business rule: Must record CashLedgerEntry (credit/outflow), must update or be associated with the active POS shift, must decrease expected cash in shift reconciliation
   - Response format: 201 Created with JSON structure { success: true, message: "...", data: { id, pos_shift_id, amount, category, notes, receipt_url, created_at, ... } }
   - Error cases: 401 Unauthenticated, 403 Forbidden (no permission or shift belongs to another), 422 Unprocessable (validation errors or shift already closed)
2. API Specification for Owner Approval Center:
   - Endpoint: GET /api/v1/owner/approvals/pending
     - Query parameters: optional filters (type, date)
     - Response format: 200 OK with list of pending approvals: { id, type: 'void'|'discount'|'attendance', title, description, requester: { id, name, role }, meta: { order_id, original_amount, discount_amount, reason, ... }, created_at }
   - Endpoint: POST /api/v1/owner/approvals/{id}/action
     - Request body: { action: 'approve' | 'reject', reason: string (optional if approve, required if reject) }
     - Business rule: Update approval status to approved/rejected, set approved_by / rejected_by to current owner user, execute corresponding entity side-effect (e.g. void order status update, discount unlock, attendance adjust), prevent duplicate action (idempotency)
     - Response format: 200 OK with updated approval object and status
3. Mobile Component Contracts:
   - PosExpenseModal (lib/screens/pos/pos_expense_modal.dart):
     - Trigger button in POS Shift menu
     - Form fields: category dropdown, amount field with currency formatting (IDR), notes field, photo receipt attachment button (camera / gallery), image preview & remove button
     - Submit handling: network call to POST /api/v1/pos/expenses or queue in offline sync manager if offline
     - Feedback: success notification, auto-refresh shift state
     - Integration with pos_shift_modal.dart: expected cash formula: opening_cash + cash_sales - petty_cash_expenses
   - OwnerApprovalScreen (lib/screens/approvals/owner_approval_screen.dart):
     - Screen architecture: Cupertino/HIG styling, pull to refresh, empty state
     - Card interaction: Swipe right to Approve (green accent, checkmark, haptic medium), swipe left to Reject (red accent, cancel icon, haptic heavy, prompt for reason)
     - Badge integration on dashboard_screen.dart: badge showing count of pending approvals
4. Test Matrix Specifications:
   - Full list of test scenarios for backend Pest/PHPUnit tests
   - Full list of test scenarios for mobile Flutter tests

DELIVERABLE:
Write your complete specification report to:
c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\report.md
Write your handoff report to:
c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\handoff.md
Update progress.md in your working directory.
When finished, notify your caller with send_message including summary and file path.
