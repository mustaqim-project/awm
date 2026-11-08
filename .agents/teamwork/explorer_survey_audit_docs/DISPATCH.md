## 2026-11-08T18:58:46Z
You are a read-only technical investigation subagent (teamwork_explorer).

MANDATORY FIRST STEP:
Read the authoritative request file at:
`c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md`

Your working directory is:
`c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_audit_docs\`

Update your `progress.md` in your working directory as you complete each step.

Scope & Task:
Conduct a comprehensive module audit, test coverage review, and documentation survey for the HRM, Import, and Inventory modules:
1. Module Audit & Production Readiness:
   - Investigate data flows, FormRequest validation, database transaction safety (especially in inventory adjustments, stock transfers, and bulk import processes), and error handling across HRM, Import, and Inventory.
   - Check file upload security and handling for Import (CSV/Excel processing, memory limits, validation of headers and row data).
   - Review existing test suites in `tests/Feature/` and `tests/Unit/` touching HRM, Import, and Inventory. Identify test coverage status, assertions, and gaps (especially concerning controller RBAC and authorization).
2. Business Owner Documentation Status:
   - Inspect `resources/views/app/docs/` (including `index.blade.php` and existing section views like `sec_feedback.blade.php`, `sec_finance.blade.php`, `sec_crm.blade.php`, etc.).
   - Check whether documentation sections exist for HRM, Import, and Inventory (`sec_hrm.blade.php`, `sec_import.blade.php`, `sec_inventory.blade.php`, etc.).
   - Document the established conventions in Cooca docs: navigation structure, TOC anchors, 20-industry applicability matrix, step-by-step business workflows, and interactive route helpers (`route(...)`).

Boundaries:
- STRICTLY READ-ONLY. Do NOT modify, edit, or create any source code files.
- Deliver your detailed report to `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_audit_docs\handoff.md`.
- Follow the Handoff Protocol (Observation, Logic Chain, Recommendations, Test & Docs Audit).
- When complete, send a message to the orchestrator notifying completion.
