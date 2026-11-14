# BRIEFING — 2026-11-14T01:16:00Z

## Mission
Coordinate and monitor project orchestrator for COOCA public frontend deep audit and optimization, incorporating post-restart quality requirements.

## 🔒 My Identity
- Archetype: sentinel
- Working directory: C:\laragon\www\cooca_core
- State folder: C:\laragon\www\cooca_core\.agents\teamwork\sentinel\

## 🔒 Key Constraints
- No technical decisions — relay only
- Victory Audit is MANDATORY before reporting completion
- R1: Audit every listed layout, partial, public page, and nested directory file in scope without exception
- R2: Maximum 10 findings per file with significant issues across all 7 dimensions
- R3: Target LCP <= 2.5s, TBT <= 200ms, CLS <= 0.1, INP Good; fix render-blocking assets, theme contrast, localization
- Code integrity: php -l check 0 errors, npm run build clean, test suites pass
- Additional Quality Requirements (2026-11-14):
  1. No milestone complete solely on syntax/build/unit tests.
  2. Measure actual frontend performance (Lighthouse/equivalent) with before-and-after LCP, TBT, CLS, Speed Index, overall score.
  3. Test all public pages in both Light & Dark Theme, and in ID and EN.
  4. Perform responsive testing at 320px, 360px, 390px, 414px, 768px, 1024px, 1440px, and 1920px.
  5. Verify shared component changes prevent regressions on all public pages.
  6. Inspect and verify actual behavior before marking fixed.
  7. Maintain complete audit inventory of every scoped file with audit/issue/implementation status.
  8. Final delivery documentation: audited/modified files, before-after benchmarks, remaining issues, test results, theme verification, i18n verification, SEO validation.

## Active Orchestrator
- Active orchestrator: 4e686b8a-2e28-4b82-9b39-8b7a237105e3
- Orchestrator state folder: C:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\
- Active Sub-Orchestrator: 1cadca40-8749-4a27-9009-f425d4420b3b (sub_orch_m2)

## User Context
- **Last user request**: Additional Quality Requirements & Resume after Server Restart
- **Pending clarifications**: none
- **Delivered results**: Milestone 1 Verified & Completed (Shared Layouts, Vite Asset Pipeline, Chatbot Deferral, Cookie Banner 8-Viewport Elevation)

## Project Status
- **Phase**: in progress (Milestone 2 active implementation across 21 public marketing views by worker_m2 under sub_orch_m2)

## Victory Audit Status
- **Auditor**: to be spawned on victory claim
- **Triggered**: no
- **Verdict**: pending
- **Retry count**: 0

## Artifact Index
- C:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md — Authoritative user request + Additional Quality Requirements
- C:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md — Global project plan & architecture
- C:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\AUDIT_INVENTORY.md — Master audit inventory of all scoped files
- C:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\GATE_STATUS.md — Gate Verdicts (Gate 1: PASS)
