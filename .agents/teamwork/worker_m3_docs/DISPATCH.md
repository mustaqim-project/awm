## 2026-11-07T14:06:08Z
You are the Business Owner Documentation Specialist Worker for COOCA.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_m3_docs
Read ORIGINAL_REQUEST.md at: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Read PROJECT.md at: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
Read the survey report at: c:\laragon\www\cooca_core\.agents\teamwork\explorer_audit_docs_survey\survey_report.md
Read the skill file at: C:\Users\Ulfah Ramadhani\.gemini\config\skills\business-owner-doc-generator\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE WRITE OWNERSHIP (You own ONLY these files; do not touch other files):
- resources/views/app/docs/partials/sec_customers.blade.php (new)
- resources/views/app/docs/partials/sec_crm.blade.php (new)
- resources/views/app/docs/partials/sec_community.blade.php (new)
- resources/views/app/docs/index.blade.php

TASK OBJECTIVES:
1. Create `resources/views/app/docs/partials/sec_customers.blade.php`:
   - Perspective: Business Owner (UMKM) in conversational, professional Indonesian without excessive technical jargon.
   - Header Bento with Lucide icon, module badge, and direct button link to `route('customers.index')`.
   - Executive Overview: customer database, CRM sync, Piutang & Kasbon management (Accounts Receivable), customer loyalty tiering, B2B wholesale pricing, and CSV/Excel import/export.
   - 4-Step Practical Implementation Workflow.
   - 20-Industry Application Matrix across 6 standard clusters (F&B, Retail, Daily Services, Manufacturing, Project Services, Distribution/Agro).
   - Security, Fraud Prevention & Access Control (permission separation for creating/editing vs export/delete).
   - Practical FAQ.
2. Create `resources/views/app/docs/partials/sec_crm.blade.php`:
   - Perspective: Business Owner (UMKM).
   - Header Bento with direct links to `route('crm.members')` and `route('crm.vouchers')`.
   - Executive Overview: customer loyalty point calculation, membership tiers, voucher code generation, automatic WhatsApp receipt dispatch with promo codes.
   - 4-Step Practical Workflow.
   - 20-Industry Application Matrix across the 6 clusters.
   - Anti-Fraud & Security (preventing employee points inflation, voucher abuse).
   - Practical FAQ.
3. Create `resources/views/app/docs/partials/sec_community.blade.php`:
   - Perspective: Business Owner (UMKM).
   - Header Bento with direct links to `route('community.index')` and `route('community.events')`.
   - Executive Overview: business networking forum, peer sharing, verified badge, QRIS event registration, digital ticket QR check-in scanner.
   - 4-Step Practical Workflow.
   - 20-Industry Application Matrix across the 6 clusters.
   - Content Moderation & Anti-Spam Security.
   - Practical FAQ.
4. Update `resources/views/app/docs/index.blade.php`:
   - Register `sec-customers`, `sec-crm`, and `sec-community` in the Left Sidebar TOC with Lucide icons, badges, and smooth-scroll anchors.
   - Include `@include('app.docs.partials.sec_customers')`, `@include('app.docs.partials.sec_crm')`, `@include('app.docs.partials.sec_community')` in the document body.
5. Verify syntax with `php -l` on all modified/created Blade files and run test suites:
   `php artisan test --filter=Documentation`
   `php artisan test --filter=ModuleAuditAndDocsTest`

Write your handoff report to:
c:\laragon\www\cooca_core\.agents\teamwork\worker_m3_docs\handoff.md

Report back via send_message when complete.
