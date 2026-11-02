# BRIEFING — 2026-11-02T05:28:45Z

## Mission
Mine, formalize, and document complete specification contracts for Phase 1 (Petty Cash / Quick Expense and Owner Approval Center) across backend (cooca_core) and mobile (cooca_my_own).

## 🔒 My Identity
- Archetype: explorer_survey
- Roles: Specification & Contract Miner
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\
- Original parent: 1516ea67-27cb-4c71-a78a-e0325a01044c
- Milestone: Phase 1 Specification Mining & Contract Definition

## 🔒 Key Constraints
- Do NOT implement code directly (Specification Miner is read-only).
- Thoroughly investigate authoritative source code and database schemas in both cooca_core and cooca_my_own.
- Produce comprehensive API specifications, data models, error codes, validation rules, mobile component contracts, and test matrices.
- Write output to report.md, handoff.md, and progress.md.
- Send completion message to parent 1516ea67-27cb-4c71-a78a-e0325a01044c via send_message.

## Current Parent
- Conversation ID: 1516ea67-27cb-4c71-a78a-e0325a01044c
- Updated: 2026-11-02T05:28:45Z

## Task Summary
- **What to build**: Complete specification contract covering Petty Cash API, Owner Approval API, Flutter UI/Logic Contracts, and Full Test Matrix.
- **Success criteria**: Exhaustive, production-ready specification contracts enabling seamless implementation for backend and mobile developers.
- **Interface contracts**: c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\report.md
- **Code layout**: Backend in `cooca_core`, Mobile in `cooca_my_own`

## Key Decisions Made
- Specification mining successfully completed across both `cooca_core` and `cooca_my_own`.
- Formalized `POST /api/v1/pos/expenses` linking `PosExpense` -> `PosCashMovement` (`TYPE_CASH_OUT`) -> `CashLedgerService::recordOutflow()`.
- Formalized unified `GET /api/v1/owner/approvals/pending` aggregating `PosApprovalRequest`, `AttendanceCorrection`, and `ApprovalRequest`.
- Formalized `POST /api/v1/owner/approvals/{id}/action` with transaction locking, idempotency guard (422 if resolved), and side-effects.
- Designed mobile contracts for `PosExpenseModal` (Apple HIG, camera capture, offline sync queue) and `OwnerApprovalScreen` (Cupertino, swipe-to-approve/reject with haptics, dashboard badge).
- Established 14 Backend test scenarios and 11 Mobile Flutter test scenarios.

## Artifact Index
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\DISPATCH.md` — Dispatch instructions
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\BRIEFING.md` — Working memory
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\progress.md` — Liveness & progress tracking
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\report.md` — Complete specification report
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\handoff.md` — 5-component handoff report

## Loaded Skills
- cooca-agent-directive: System orchestration for COOCA SaaS ERP multi-tenant Indonesian MSME
