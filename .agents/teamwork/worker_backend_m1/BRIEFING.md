# BRIEFING — 2026-11-02T05:31:00Z

## Mission
Implement backend APIs for POS Shift Expense recording and Owner Approvals (pending aggregation and action) along with comprehensive PHPUnit tests.

## 🔒 My Identity
- Archetype: teamwork_worker
- Roles: implementer, qa, specialist
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\worker_backend_m1\
- Original parent: 1516ea67-27cb-4c71-a78a-e0325a01044c
- Milestone: Milestone 1

## 🔒 Key Constraints
- Exclusive file ownership:
  - app/Http/Controllers/Api/V1/Pos/PosExpenseApiController.php (create)
  - app/Http/Controllers/Api/V1/Owner/OwnerApprovalApiController.php (create)
  - routes/api.php (add new route definitions)
  - tests/Feature/Api/PosExpenseApiTest.php (create)
  - tests/Feature/Api/OwnerApprovalApiTest.php (create)
- Genuine implementation only; no dummy/facade implementations or hardcoded test values.
- Must follow project patterns, interface contracts, and pass PHPUnit tests.

## Current Parent
- Conversation ID: 1516ea67-27cb-4c71-a78a-e0325a01044c
- Updated: 2026-11-02T05:31:00Z

## Task Summary
- **What to build**:
  1. POST /api/v1/pos/expenses (PosExpenseApiController)
  2. GET /api/v1/owner/approvals/pending and POST /api/v1/owner/approvals/{id}/action (OwnerApprovalApiController)
  3. Route definitions in routes/api.php
  4. Feature tests: tests/Feature/Api/PosExpenseApiTest.php and tests/Feature/Api/OwnerApprovalApiTest.php
- **Success criteria**:
  - Valid and invalid flows handled properly (active shift check, idempotency check, permission checks).
  - CashMovement and CashLedger recorded correctly.
  - Receipt storage (file upload and base64) handled.
  - 100% PHPUnit tests passing.
- **Interface contracts**: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md
- **Code layout**: Existing Laravel 11 structure in cooca_core.

## Key Decisions Made
- [TBD]

## Artifact Index
- DISPATCH.md — Assignment and requirements
- progress.md — Liveness heartbeat and progress tracker
- handoff.md — Final handoff report

## Change Tracker
- **Files modified**: None yet
- **Build status**: Untested
- **Pending issues**: None

## Quality Status
- **Build/test result**: Not yet run
- **Lint status**: Clean
- **Tests added/modified**: Pending

## Loaded Skills
- None
