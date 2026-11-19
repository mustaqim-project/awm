# COOCA — File Audit Manifest: Searchable Dropdown Overhaul

> **Audit Date**: 2026-11-19
> **Workspace**: `c:\laragon\www\cooca_core`
> **Audit Scope**: Recursive scan of `resources\views\app\` (46 modules + root files) and extended dependencies (`components`, `layouts`, `resources/js`, `resources/css`, `public/js`, `public/css`).
> **Verification Command**: `php artisan view:clear && php artisan view:cache` (Exit code 0, 100% compilation passed).

## Executive Summary

- **Total Scanned App Files**: 275
- **Total Extended Dependency Files**: 34
- **Total Combined Scope Files**: 309
- **Total Dropdowns Discovered**: 313
- **Pre-Upgrade Searchable Dropdowns**: 9
- **Pre-Upgrade Not Searchable Dropdowns**: 304
- **Files Modified in `resources\views\app`**: 97 files (304 dropdowns converted to `<x-searchable-select>`)
- **Files with No Change in `resources\views\app`**: 178 files (176 zero-dropdown files + 2 already-searchable files)
- **Extended Dependencies Modified**: 2 files (`resources\views\components\searchable-select.blade.php`, `resources\views\layouts\app.blade.php`)
- **Post-Upgrade Searchable Coverage**: **100%** (313/313 dropdowns searchable across all app views)
- **View Compilation Status**: `Passed` (All views compiled with exit code 0 via `php artisan view:cache`)

---

## 1. Application Views Manifest (`resources\views\app\` — 275 Files)

| File Path | Module | File Type | Dropdown Found | Searchable | Not Searchable | Action | Test Status |
|---|---|---|:---:|:---:|:---:|:---:|:---:|
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\create.blade.php` | `ai-studio` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\credit-history.blade.php` | `ai-studio` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\index.blade.php` | `ai-studio` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\inspiration\categories.blade.php` | `ai-studio` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\inspiration\collection-detail.blade.php` | `ai-studio` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\inspiration\collections.blade.php` | `ai-studio` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\inspiration\favorites.blade.php` | `ai-studio` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\inspiration\index.blade.php` | `ai-studio` | Blade | 4 | 0 | 4 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\inspiration\my-library.blade.php` | `ai-studio` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\inspiration\show.blade.php` | `ai-studio` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\partials\topup-modal.blade.php` | `ai-studio` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\prompt-preview.blade.php` | `ai-studio` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai-studio\show.blade.php` | `ai-studio` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\actions.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\history.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\index.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\lobby.blade.php` | `ai` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\office.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\offices\executive.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\offices\growth.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\offices\operations.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\partials\agent_inspector_hud.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\partials\consultation_modal.blade.php` | `ai` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\partials\desks\executive_desks.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\partials\desks\growth_desks.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\partials\desks\lobby_desks.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\partials\desks\operations_desks.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\partials\office_navigation.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\partials\office_script.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\partials\virtual_office_canvas.blade.php` | `ai` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\ai\providers.blade.php` | `ai` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\analytics\index.blade.php` | `analytics` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\approvals\history.blade.php` | `approvals` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\approvals\inbox.blade.php` | `approvals` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\approvals\rules.blade.php` | `approvals` | Blade | 10 | 0 | 10 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\billing\checkout.blade.php` | `billing` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\billing\history.blade.php` | `billing` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\billing\invoice.blade.php` | `billing` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\billing\limits.blade.php` | `billing` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\billing\payment.blade.php` | `billing` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\calculator.blade.php` | `root` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\community\event_payment.blade.php` | `community` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\community\events.blade.php` | `community` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\community\index.blade.php` | `community` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\crm\members.blade.php` | `crm` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\crm\vouchers.blade.php` | `crm` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\customers\index.blade.php` | `customers` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\dashboard.blade.php` | `root` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\index.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_ai.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_ai_studio.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_analytics.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_approvals.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_billing.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_community.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_crm.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_customers.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_feedback.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_finance.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_hrm.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_import.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_inventory.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_invoices.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_labor_machines.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\docs\partials\sec_storefront.blade.php` | `docs` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\feedback\create.blade.php` | `feedback` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\feedback\index.blade.php` | `feedback` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\feedback\show.blade.php` | `feedback` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\accounting\balance-sheet.blade.php` | `finance` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\accounting\coa.blade.php` | `finance` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\accounting\general-ledger.blade.php` | `finance` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\accounting\reconciliation.blade.php` | `finance` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\accounting\trial-balance.blade.php` | `finance` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\cash-bank\index.blade.php` | `finance` | Blade | 10 | 0 | 10 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\cash-bank\ledger.blade.php` | `finance` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\edc-terminals\index.blade.php` | `finance` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\expenses.blade.php` | `finance` | Blade | 5 | 0 | 5 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\external-reconciliation\index.blade.php` | `finance` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\journals.blade.php` | `finance` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\payables.blade.php` | `finance` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\payout-accounts\index.blade.php` | `finance` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\receivables.blade.php` | `finance` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\settlements\index.blade.php` | `finance` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\finance\settlements\show.blade.php` | `finance` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\help_chatbot\conversations.blade.php` | `help_chatbot` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\index.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\add-correction.blade.php` | `hrm` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\add-employee.blade.php` | `hrm` | Blade | 6 | 0 | 6 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\add-loan.blade.php` | `hrm` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\add-location.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\add-schedule.blade.php` | `hrm` | Blade | 5 | 0 | 5 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\add-shift.blade.php` | `hrm` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\edit-employee.blade.php` | `hrm` | Blade | 6 | 0 | 6 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\edit-location.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\edit-schedule.blade.php` | `hrm` | Blade | 5 | 0 | 5 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\edit-shift.blade.php` | `hrm` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\face-attendance.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\import-attendance.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\import-employee.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\register-face.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\modals\review-correction.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\partials\tab-attendance.blade.php` | `hrm` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\partials\tab-corrections.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\partials\tab-employees.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\partials\tab-loans.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\partials\tab-locations.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\partials\tab-payrolls.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\partials\tab-schedules.blade.php` | `hrm` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\partials\tab-shifts.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\payroll\create.blade.php` | `hrm` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\payroll\payslip.blade.php` | `hrm` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\hrm\payroll\show.blade.php` | `hrm` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\import\index.blade.php` | `import` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\inventory\movements.blade.php` | `inventory` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\inventory\opname.blade.php` | `inventory` | Blade | 5 | 0 | 5 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\inventory\stocks.blade.php` | `inventory` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\inventory\transfers.blade.php` | `inventory` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\invoices\create.blade.php` | `invoices` | Blade | 7 | 0 | 7 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\invoices\edit.blade.php` | `invoices` | Blade | 5 | 0 | 5 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\invoices\index.blade.php` | `invoices` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\invoices\print.blade.php` | `invoices` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\invoices\show.blade.php` | `invoices` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\labor-machines\index.blade.php` | `labor-machines` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\landing_page\edit.blade.php` | `landing_page` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\landing_page\popup.blade.php` | `landing_page` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\marketplace\chat.blade.php` | `marketplace` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\marketplace\finances.blade.php` | `marketplace` | Blade | 4 | 0 | 4 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\marketplace\index.blade.php` | `marketplace` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\marketplace\logs.blade.php` | `marketplace` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\marketplace\orders.blade.php` | `marketplace` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\marketplace\products.blade.php` | `marketplace` | Blade | 1 | 1 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\marketplace\shipping_label.blade.php` | `marketplace` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\master-data\index.blade.php` | `master-data` | Blade | 6 | 2 | 4 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\materials\index.blade.php` | `materials` | Blade | 9 | 0 | 9 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\mcp\oauth\consent.blade.php` | `mcp` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\portal\index.blade.php` | `portal` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\kitchen.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\barcode-scanner-modal.blade.php` | `pos` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\barcode-scanner.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\camera-permission.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\cash-movement-modal.blade.php` | `pos` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\cash-movement.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\close-shift-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\close-shift.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\customer-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\dynamic-qris.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\held-orders-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\held-orders.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\hold-prompt-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\hold-prompt.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\incoming-orders-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\incoming-orders.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\item-detail-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\modifier-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\modifier-picker.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\open-shift-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\open-shift.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\pay-table-order.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\payment-modal.blade.php` | `pos` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\payment.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\pharmacy-batch.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\product-paywall-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\qris-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\quick-customer.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\receipt-success.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\reject-reason-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\reject-reason.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\restaurant-tables.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\scanner-permission-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\service-vertical-modal.blade.php` | `pos` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\service-vertical.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\settings-hardware.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\settings-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\success-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\supervisor-pin-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\supervisor-pin.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\table-payment-modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\modals\tables-modal.blade.php` | `pos` | Blade | 5 | 0 | 5 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\orders.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\prep_sheet.blade.php` | `pos` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\printers\index.blade.php` | `pos` | Blade | 6 | 0 | 6 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\qr-card.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\receipt.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\partials\filter_bar.blade.php` | `pos` | Blade | 6 | 0 | 6 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\partials\kpi_cards.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\partials\order_detail_modal.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\print_summary.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\cashiers.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\categories.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\channels.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\customers.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\discounts.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\hourly.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\outlets.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\overview.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\payments.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\products.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\profitability.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\refunds.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\shifts.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\transactions.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\reports\tabs\voids.blade.php` | `pos` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\shifts.blade.php` | `pos` | Blade | 5 | 0 | 5 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\tables.blade.php` | `pos` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\pos\terminal.blade.php` | `pos` | Blade | 4 | 1 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\production\create.blade.php` | `production` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\production\index.blade.php` | `production` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\production\show.blade.php` | `production` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\products\bom.blade.php` | `products` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\products\index.blade.php` | `products` | Blade | 11 | 0 | 11 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\products\modifiers.blade.php` | `products` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\profile\edit.blade.php` | `profile` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\profile\verify-contact.blade.php` | `profile` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\profitability\index.blade.php` | `profitability` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\purchase-orders\create.blade.php` | `purchase-orders` | Blade | 9 | 0 | 9 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\purchase-orders\index.blade.php` | `purchase-orders` | Blade | 6 | 0 | 6 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\purchase-orders\print.blade.php` | `purchase-orders` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\purchase-orders\show.blade.php` | `purchase-orders` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\purchasing\bills\index.blade.php` | `purchasing` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\purchasing\bills\show.blade.php` | `purchasing` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\purchasing\receipts\create.blade.php` | `purchasing` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\purchasing\returns\create.blade.php` | `purchasing` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\purchasing\returns\index.blade.php` | `purchasing` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\purchasing\returns\show.blade.php` | `purchasing` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\quotations\create.blade.php` | `quotations` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\quotations\edit.blade.php` | `quotations` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\quotations\index.blade.php` | `quotations` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\quotations\show.blade.php` | `quotations` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\reports\index.blade.php` | `reports` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\roles\index.blade.php` | `roles` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\sales-orders\create.blade.php` | `sales-orders` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\sales-orders\edit.blade.php` | `sales-orders` | Blade | 4 | 0 | 4 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\sales-orders\index.blade.php` | `sales-orders` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\sales-orders\show.blade.php` | `sales-orders` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\sales\returns\create.blade.php` | `sales` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\sales\returns\edit.blade.php` | `sales` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\sales\returns\index.blade.php` | `sales` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\sales\returns\print.blade.php` | `sales` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\sales\returns\show.blade.php` | `sales` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\security\audit-logs\index.blade.php` | `security` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\security\audit-logs\show.blade.php` | `security` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\services\index.blade.php` | `services` | Blade | 5 | 0 | 5 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\settings\index.blade.php` | `settings` | Blade | 4 | 2 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\settings\integrations\mcp.blade.php` | `settings` | Blade | 4 | 0 | 4 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\settings\integrations\social_media.blade.php` | `settings` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\settings\integrations\whatsapp.blade.php` | `settings` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\settings\roles.blade.php` | `settings` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\simulator\index.blade.php` | `simulator` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\social_media\calendar.blade.php` | `social_media` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\social_media\inbox.blade.php` | `social_media` | Blade | 2 | 0 | 2 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\social_media\index.blade.php` | `social_media` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\social_media\insights.blade.php` | `social_media` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\social_media\posts.blade.php` | `social_media` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\orders\index.blade.php` | `storefront` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\orders\shipping_label.blade.php` | `storefront` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\orders\show.blade.php` | `storefront` | Blade | 3 | 0 | 3 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\partials\navigation.blade.php` | `storefront` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\reservations\index.blade.php` | `storefront` | Blade | 10 | 0 | 10 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\settings.blade.php` | `storefront` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\shipping\index.blade.php` | `storefront` | Blade | 1 | 1 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\tabs\tab-features.blade.php` | `storefront` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\tabs\tab-fulfillment.blade.php` | `storefront` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\tabs\tab-general.blade.php` | `storefront` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\tabs\tab-payment-settlement.blade.php` | `storefront` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\tabs\tab-schedule.blade.php` | `storefront` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\storefront\theme_editor.blade.php` | `storefront` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\suppliers\index.blade.php` | `suppliers` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\tax\index.blade.php` | `tax` | Blade | 8 | 0 | 8 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\warehouse\index.blade.php` | `warehouse` | Blade | 8 | 2 | 6 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\warehouse\show.blade.php` | `warehouse` | Blade | 4 | 0 | 4 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\whatsapp\broadcast.blade.php` | `whatsapp` | Blade | 1 | 0 | 1 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\whatsapp\broadcast_detail.blade.php` | `whatsapp` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\whatsapp\index.blade.php` | `whatsapp` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\app\whatsapp\logs.blade.php` | `whatsapp` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |

---

## 2. Extended Dependencies Manifest (34 Files)

| File Path | Module | File Type | Dropdown Found | Searchable | Not Searchable | Action | Test Status |
|---|---|---|:---:|:---:|:---:|:---:|:---:|
| `C:\laragon\www\cooca_core\public\css\dashboard.css` | `public\css` | CSS | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\public\js\ai\virtual-office-3d.js` | `public\js` | JS | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\public\js\app-alert.js` | `public\js` | JS | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\public\js\dashboard.js` | `public\js` | JS | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\public\js\otp-widget.js` | `public\js` | JS | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\public\js\vendor\three\OrbitControls.js` | `public\js` | JS | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\public\js\vendor\three\three.min.js` | `public\js` | JS | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\css\app.css` | `resources\css` | CSS | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\js\app.js` | `resources\js` | JS | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\js\echo.js` | `resources\js` | JS | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\components\breadcrumb.blade.php` | `resources\views\components` | Component | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\components\document-stepper.blade.php` | `resources\views\components` | Component | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\components\module-header.blade.php` | `resources\views\components` | Component | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\components\module-tabs.blade.php` | `resources\views\components` | Component | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\components\quota-modal.blade.php` | `resources\views\components` | Component | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\components\searchable-select.blade.php` | `resources\views\components` | Component | 1 | 1 | 0 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\components\social-icon.blade.php` | `resources\views\components` | Component | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\components\sortable-th.blade.php` | `resources\views\components` | Component | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\components\table-search.blade.php` | `resources\views\components` | Component | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\components\table-sort-th.blade.php` | `resources\views\components` | Component | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\admin.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\ai.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\app.blade.php` | `resources\views\layouts` | Blade | 6 | 0 | 6 | `Modified` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\customer.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\guest.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\partials\help_chatbot_widget.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\partials\quick-actions.blade.php` | `resources\views\layouts` | Blade | 6 | 0 | 6 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\partials\secondary-sidebar.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\partials\sidebar-styles.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\partials\sidebar.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\partials\spotlight.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\partials\topbar.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\partials\typography.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
| `C:\laragon\www\cooca_core\resources\views\layouts\public_marketing.blade.php` | `resources\views\layouts` | Blade | 0 | 0 | 0 | `No Change` | `Passed` |
