# Dispatch: Worker  UI/UX Audit Report Remediation (Iteration 2 Replacement)

## Objective
Remediate and update `c:\laragon\www\cooca_core\docs\UI_UX_AUDIT_REPORT.md` to resolve all empirical discrepancies and integrity findings identified during Gate 1 review.

## Review & Challenger Feedback to Address
1. **Section 3.2 Double Header Manifest (CRITICAL)**:
   - In lines 247–290, replace the 11 non-existent generic paths:
     - `materials/categories.blade.php`
     - `products/categories.blade.php`
     - `purchasing/bills.blade.php`
     - `purchasing/index.blade.php`
     - `purchasing/receivings.blade.php`
     - `purchasing/returns.blade.php`
     - `roles/create.blade.php`
     - `roles/edit.blade.php`
     - `roles/show.blade.php`
     - `sales/index.blade.php`
     - `sales/returns.blade.php`
   - With the 11 verified actual paths on disk that exhibit Double Header redundancy:
     - `master-data/index.blade.php`
     - `materials/index.blade.php`
     - `products/bom.blade.php`
     - `products/modifiers.blade.php`
     - `quotations/index.blade.php`
     - `social_media/calendar.blade.php`
     - `social_media/inbox.blade.php`
     - `social_media/insights.blade.php`
     - `social_media/posts.blade.php`
     - `warehouse/show.blade.php`
     - `whatsapp/logs.blade.php`
   - Keep the verified total at exactly 44 views.

2. **Section 8.1 Command 3 & Section 1.2 Scorecard (CRITICAL)**:
   - Update Command 3 expected output: running Command 3 across `resources/views/app` yields **76 flawed backdrop instances across 21 views** (20 in `pos/terminal`, 8 in `products/index`, 7 in `materials/index`, 5 in `finance/cash-bank`, 5 in `settings/index`, 4 in `products/modifiers`, 4 in `finance/accounting/coa`, 3 in `master-data/index`, 3 in `warehouse/index`, 3 in `warehouse/show`, 2 in `calculator`, 2 in `ai-studio/show`, 2 in `products/bom`, 1 in `ai-studio/index`, 1 in `ai-studio/prompt-preview`, 1 in `finance/expenses`, 1 in `finance/payables`, 1 in `finance/receivables`, 1 in `finance/accounting/reconciliation`, 1 in `whatsapp/index`, 1 in `whatsapp/logs`).
   - Clarify in the Scorecard (Section 1.2 & Section 5.2.3) that 47 instances reside in the 15 core transactional modules, while 29 instances reside in back-office and management modules, totaling 76 instances globally across 21 views.

3. **Section 4 Real Filesystem Inventory & HRM Mega-Monolith (CRITICAL)**:
   - **Section 4.42 (HRM)**: Replace fictional `employees.blade.php` and `attendance.blade.php` with the real files: `hrm/index.blade.php` (5,705 lines), `hrm/payroll/create.blade.php` (682 lines), `hrm/payroll/payslip.blade.php` (481 lines), and `hrm/payroll/show.blade.php` (485 lines).
   - In Section 1.1 point 7, explicitly highlight `hrm/index.blade.php` (5,705 lines, 10 data tables, 15 modals) as the second-largest monolithic view in the system after `pos/terminal.blade.php` (7,158 lines).
   - **Section 4.43 (Approvals)**: Correct view list to `inbox.blade.php` (381 lines), `history.blade.php` (192 lines), and `rules.blade.php` (522 lines).
   - **Section 4.25 (Roles)**: Correct view list to `roles/index.blade.php` (1,351 lines).
   - **Section 4.5 (Storefront tabs)**: Correct tab list to `tab-features.blade.php`, `tab-fulfillment.blade.php`, `tab-general.blade.php`, `tab-payment-settlement.blade.php`, `tab-schedule.blade.php`.
   - **Section 4.35 (WhatsApp)**: Correct view list to include `broadcast_detail.blade.php`.

## Scope & Boundaries
- You exclusively own: `c:\laragon\www\cooca_core\docs\UI_UX_AUDIT_REPORT.md`
- Working directory: `c:\laragon\www\cooca_core\.agents\teamwork\worker_remediation_2`
- MANDATORY BOUNDARY: STRICTLY READ-ONLY towards `resources/views/app`. DO NOT modify any view or code files.
- Verify that `git status resources/views/app` remains clean after updating the report.

## Mandatory Integrity Warning
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.
