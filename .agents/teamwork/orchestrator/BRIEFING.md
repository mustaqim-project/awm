# BRIEFING — 2026-11-07T11:42:00Z

## Mission
Audit, simplify UI layouts, enforce RBAC authorization, and generate business owner documentation for the Community, CRM, and Customers modules in COOCA for production readiness.

## 🔒 My Identity
- Archetype: teamwork_orchestrator
- Roles: orchestrator, user_liaison, human_reporter, successor
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator
- Original parent: Sentinel / Parent Agent
- Original parent conversation ID: b1574c02-f26c-447c-9fee-969393ec89c1

## 🔒 My Workflow
- **Pattern**: Project
- **Scope document**: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
1. **Decompose**: Milestones to be established in PROJECT.md after Survey:
   - Phase 0: Survey & Technical Discovery (3 Explorers in parallel) [IN_PROGRESS]
   - Milestone 1: RBAC Enforcement & Controller-Level Guards (Seeders, Controllers, Routes, View Guards) [PLANNED]
   - Milestone 2: UI Simplification & Layout Optimization for Community, CRM, Customers Views [PLANNED]
   - Milestone 3: Business Owner Documentation & User Guides in `resources/views/app/docs/` [PLANNED]
   - Milestone 4: Module Audit, Production Readiness, Verification & Forensic Audit Gate [PLANNED]
2. **Dispatch & Execute**:
   - Direct iteration loop: Explorer -> Worker -> Reviewers (2) -> Challengers (2) -> Forensic Auditor (1) -> Gate
3. **On failure** (in this order):
   - Retry: nudge stuck agent or re-send task
   - Replace: spawn fresh agent with partial progress
   - Skip: proceed without (only if non-critical)
   - Redistribute: split stuck agent's remaining work
   - Redesign: re-partition decomposition
   - Escalate: report to parent (sub-orchestrators only, last resort)
4. **Succession**: Spawn count threshold 16 / 16. On threshold, write handoff.md and spawn successor.
- **Work items**:
  1. Survey & Technical Discovery [in-progress]
  2. Milestone 1: RBAC Enforcement & Controller-Level Guards [pending]
  3. Milestone 2: UI Simplification & Layout Cleanliness [pending]
  4. Milestone 3: Business Owner Documentation [pending]
  5. Milestone 4: Module Audit, Verification & Forensic Audit Gate [pending]
- **Current phase**: Phase 0: Survey & Technical Discovery
- **Current focus**: Monitoring 3 parallel survey explorers.

## 🔒 Key Constraints
- NEVER write, modify, or create source code files directly.
- NEVER run build/test commands yourself — require workers to do so.
- NEVER investigate or explore the problem at the code level — dispatch Explorers for technical investigation.
- You MAY use file-editing tools ONLY for metadata/state files (.md) in your .agents/teamwork/ folder.
- Preserve all existing functionality, multi-tenant boundaries, and clean architecture.
- Never reuse a subagent after it has delivered its handoff — always spawn fresh.
- Hard audit veto: clean forensic audit required.

## Current Parent
- Conversation ID: b1574c02-f26c-447c-9fee-969393ec89c1
- Updated: 2026-11-07T11:32:00Z

## Key Decisions Made
- Dispatched 3 parallel survey explorers for UI, RBAC, and Audit/Docs.

## Team Roster
| Agent | Type | Work Item | Status | Conv ID |
|-------|------|-----------|--------|---------|
| explorer_ui_survey | teamwork_explorer | Survey UI & Blade views in community, crm, customers | in-progress | 1e2d4b2e-fb93-41cf-ac24-aa6e61a5c5ad |
| explorer_rbac_survey | teamwork_explorer | Survey RBAC, controllers, seeders, and view guards | in-progress | 90aea942-ae93-4b03-98dc-1bf670f8b3c5 |
| explorer_audit_docs_survey | teamwork_explorer | Survey module health, test coverage, and docs architecture | in-progress | 6635ad4a-90de-4315-ba57-43be675b1561 |

## Succession Status
- Succession required: no
- Spawn count: 3 / 16
- Pending subagents: 1e2d4b2e-fb93-41cf-ac24-aa6e61a5c5ad, 90aea942-ae93-4b03-98dc-1bf670f8b3c5, 6635ad4a-90de-4315-ba57-43be675b1561
- Predecessor: none
- Successor: none

## Active Timers
- Heartbeat cron: active (schedule */10 * * * *)
- Safety timer: active (schedule 600s)

## Artifact Index
- c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md — Authoritative user request
- c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\DISPATCH.md — Incoming parent dispatch
- c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\progress.md — Progress tracking
- c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md — Global project plan & feature inventory
