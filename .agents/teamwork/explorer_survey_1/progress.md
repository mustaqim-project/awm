# Progress — explorer_survey_1

- Last visited: 2026-11-02T05:25:00Z
- Status: In Progress
- Completed:
  - Read ORIGINAL_REQUEST.md & plan_core_to_mobile_features.md
  - Investigated Cash Ledger & Petty Cash:
    - CashAccount & CashTransaction models, CashLedgerService (`recordInflow`, `recordOutflow`, `transfer`)
    - CashLedgerWebController & CashLedgerController (API)
    - PosShift & PosCashMovement models, PosShiftService (`openShift`, `closeShift`, `recordCashMovement`, `getShiftSummary`)
    - PosShiftController (API)
    - Tenant isolation: SetActiveBusinessContext, EnsureActiveBusiness, BelongsToBusiness trait, Context::requireBusiness()
    - Storage conventions: TenantStorage (DISK_PUBLIC, DISK_PRIVATE), Expense receipt image conventions
  - Investigated Owner Approval Center:
    - ApprovalRequest, PosApprovalRequest, AttendanceCorrection models & tables
    - Document approval rules & ApprovalWorkflowService
    - Authorization middleware: require.role, require.permission
- Current step:
  - Verifying test suite execution and preparing comprehensive survey report
