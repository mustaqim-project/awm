# BRIEFING — 2026-11-02T05:15:00Z

## Mission
Implement Phase 1 feature integration across backend `cooca_core` and mobile `cooca_my_own`: Petty Cash / Quick Expense Kasir and Owner Approval Center for production use with full automated tests.

## 🔒 My Identity
- Archetype: teamwork_orchestrator
- Roles: orchestrator, user_liaison, human_reporter, successor
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1
- Original parent: sentinel
- Original parent conversation ID: c9711e09-672a-406e-a3b8-bd4fe3fa2091

## 🔒 My Workflow
- **Pattern**: Project Orchestrator (Multi-Milestone Greenfield/Integration)
- **Scope document**: c:\laragon\www\cooca_core\PROJECT.md
1. **Decompose**:
   - Step 0: Survey full scope via 3 parallel Explorers (Backend `cooca_core`, Mobile `cooca_my_own`, Spec Miner contract mapping).
   - Step 1: Synthesize findings and write `PROJECT.md` with Feature Inventory, Milestones, and Interface Contracts.
   - Step 2: Dispatch implementation milestones to sub-orchestrators/workers, along with dual-track E2E/integration testing.
2. **Dispatch & Execute**:
   - Direct iteration loop: Explorer → Worker → Reviewer ×2 → Challenger ×2 → Auditor.
3. **On failure**:
   - Retry: nudge stuck agent or re-send task
   - Replace: spawn fresh agent with partial progress
   - Skip: proceed without (only if non-critical)
   - Redistribute: split stuck agent's remaining work
   - Redesign: re-partition decomposition
   - Escalate: report to parent as last resort
4. **Succession**: At 16 spawns, write handoff.md, spawn successor.
- **Work items**:
  1. Survey phase (3 parallel explorers) [in-progress]
  2. PROJECT.md Feature Inventory & Architecture [pending]
  3. Milestone 1: Backend API (Petty Cash & Owner Approval) [pending]
  4. Milestone 2: Mobile UI & Integration (Petty Cash & Owner Approval) [pending]
  5. Milestone 3: Automated Testing & Verification (PHPUnit/Pest + Flutter test/analyze) [pending]
- **Current phase**: 0 (Survey)
- **Current focus**: Parallel exploration of `cooca_core` backend and `cooca_my_own` Flutter app

## 🔒 Key Constraints
- NEVER write, modify, or create source code files directly.
- NEVER run build/test commands yourself — require workers to do so.
- NEVER investigate or explore the problem at the code level — dispatch Explorers for technical investigation.
- You MAY use file-editing tools ONLY for metadata/state files (.md) in your .agents/teamwork/ folder.
- Mandate strict adherence to `ORIGINAL_REQUEST.md` and `plan_core_to_mobile_features.md`.
- Zero tolerance for mock/cheating implementations; Forensic Auditor must report CLEAN.
- `flutter analyze` must produce 0 issues/errors; `flutter test` must pass 100%.
- Backend PHPUnit/Pest tests must pass 100%.

## Current Parent
- Conversation ID: c9711e09-672a-406e-a3b8-bd4fe3fa2091
- Updated: 2026-11-02T05:15:00Z

## Key Decisions Made
- Initialized Phase 1 integration project.
- Dispatched 3 Survey agents in parallel:
  - `explorer_survey_1` (cooca_core backend architecture)
  - `explorer_survey_2` (cooca_my_own mobile architecture)
  - `explorer_survey_3` (specification and contract miner)

## Team Roster
| Agent | Type | Work Item | Status | Conv ID |
|-------|------|-----------|--------|---------|
| explorer_survey_1 | teamwork_explorer | Backend cooca_core architecture survey | in-progress | cb4f110d-52dd-460c-9836-e93523e48577 |
| explorer_survey_2 | teamwork_explorer | Mobile cooca_my_own architecture survey | completed | b4336e98-ca62-4b5b-903e-123571000ced |
| explorer_survey_3 | teamwork_spec_miner | API contracts & specs mapping | in-progress | 55d64a3a-d9de-4b18-9a27-d9a4c54b4d78 |

## Succession Status
- Succession required: no
- Spawn count: 3 / 16
- Pending subagents: cb4f110d-52dd-460c-9836-e93523e48577, b4336e98-ca62-4b5b-903e-123571000ced, 55d64a3a-d9de-4b18-9a27-d9a4c54b4d78
- Predecessor: none
- Successor: not yet spawned

## Active Timers
- Heartbeat cron: 1516ea67-27cb-4c71-a78a-e0325a01044c/task-38
- Safety timer: none
- On succession: kill all timers before spawning successor
- On context truncation: handle timers as step 4 of the State Recovery section of your prompt says

## Artifact Index
- `c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md` — Authoritative original request
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\DISPATCH.md` — Incoming dispatch log
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\BRIEFING.md` — Working memory
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\progress.md` — Workflow progress and liveness heartbeat
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\GATE_STATUS.md` — Structured gate evaluation
- `c:\laragon\www\cooca_core\PROJECT.md` — Project scope and milestones status
