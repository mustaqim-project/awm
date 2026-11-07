## 2026-11-07T09:20:36Z
You are Explorer 3: Production Readiness & Docs Explorer.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\explorer_audit_docs_survey\

MANDATORY INSTRUCTIONS:
1. You MUST read the authoritative request at: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
2. You MUST read the domain skill instructions at:
   - C:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-module-audit\SKILL.md
   - C:\Users\Ulfah Ramadhani\.gemini\config\skills\business-owner-doc-generator\SKILL.md
   - C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md

SCOPE & INVESTIGATION:
1. Production Readiness Audit (`analytics`, `approvals`, `billing`):
   - Error handling & validation: Are inputs validated properly in controller store/update methods? Are exceptions caught and user feedback provided cleanly?
   - Flash alerts: Do controllers pass success/error flash messages (`session('success')`, `session('error')`), and do views display them?
   - Multi-tenant query consistency: Are all queries properly scoped to current business/tenant?
   - Test suites: Check existing test coverage in `tests/Feature/` and `tests/Unit/` for analytics, approvals, billing. What tests exist, and what tests need to be written or updated?
2. Business Owner Documentation Architecture:
   - Inspect `resources/views/app/docs/` and corresponding routes/controllers (e.g., `DocsController.php`).
   - How are documentation pages structured, rendered, and linked? Inspect existing docs (e.g., AI, POS, Inventory, etc.) to see standard layout, component usage, and style.
   - For `analytics`, `approvals`, and `billing`: Determine whether documentation files exist or where they should be created (e.g. `resources/views/app/docs/analytics.blade.php`, `resources/views/app/docs/approvals.blade.php`, `resources/views/app/docs/billing.blade.php` or similar).
   - Ensure the docs adhere to `/business-owner-doc-generator`: Business Owner POV, simple language without technical jargon, 3-5 practical steps, 20-industry application matrix, and actionable route links (e.g., `route('...')`).
3. Provide concrete remediation plan for module audit improvements and doc generation.

OUTPUT:
Write your complete findings and report to:
`c:\laragon\www\cooca_core\.agents\teamwork\explorer_audit_docs_survey\handoff.md`
Then send a completion message with summary and file path back to orchestrator.
