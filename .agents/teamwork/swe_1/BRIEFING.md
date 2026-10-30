# BRIEFING — 2026-10-30T23:01:00Z

## Mission
Transform COOCA sub-navigation layout from horizontal top tabs into a secondary sidebar (dual sidebar layout), responsive mobile drawer integration, preserving RBAC and multi-industry gating, backed by PHPUnit automated tests.

## 🔒 My Identity
- Archetype: teamwork_swe
- Roles: orchestrator, user_liaison, human_reporter, successor
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\swe_1
- Original parent: parent
- Original parent conversation ID: b6506ba4-9183-4178-8893-7d375a3cf7e2

## 🔒 My Workflow
- **Pattern**: SWE Light
- **Scope document**: .agents/teamwork/swe_1/DISPATCH.md
1. **Decompose**: SWE Light does not decompose. Pass whole task verbatim.
2. **Dispatch & Execute**:
   - teamwork_implementer -> produces working diff and test results
   - teamwork_reviewer (Round 1) -> adversarial stress-test, break, fix, verify
   - teamwork_reviewer (Round 2) -> adversarial stress-test, break, fix, verify
   - teamwork_reviewer (Round 3) -> adversarial stress-test, break, fix, verify
   - teamwork_victory_auditor -> blocking independent audit
3. **On failure**:
   - Retry / Replace / Redistribute / Degrade
4. **Succession**: At spawn count >= 16 and all subagents complete, spawn successor.
- **Work items**:
  1. Primary implementation (teamwork_implementer) [in-progress]
  2. Review Round 1 (teamwork_reviewer) [pending]
  3. Review Round 2 (teamwork_reviewer) [pending]
  4. Review Round 3 (teamwork_reviewer) [pending]
  5. Independent Victory Audit (teamwork_victory_auditor) [pending]
- **Current phase**: 2 (Dispatch & Execute)
- **Current focus**: Monitoring teamwork_implementer (c4b91461-de67-48ed-a02c-b351436429dd)

## 🔒 Key Constraints
- Never write, modify, or create source code files yourself. Delegate all implementation and repair to workers.
- Never explore or debug the codebase to solve the task yourself.
- Verify independently: read diffs and re-run relevant tests.
- Maintain an open-issues ledger across all rounds.
- Propagate original task verbatim to workers.
- Run at least 3 review rounds.
- Never reuse a subagent after it has delivered its handoff — always spawn fresh.

## Current Parent
- Conversation ID: b6506ba4-9183-4178-8893-7d375a3cf7e2
- Updated: 2026-10-30T23:00:00Z

## Key Decisions Made
- Dispatched teamwork_implementer (c4b91461-de67-48ed-a02c-b351436429dd) for primary implementation.

## Team Roster
| Agent | Type | Work Item | Status | Conv ID |
|-------|------|-----------|--------|---------|
| c4b91461-de67-48ed-a02c-b351436429dd | teamwork_implementer | Primary implementation | in-progress | c4b91461-de67-48ed-a02c-b351436429dd |

## Succession Status
- Succession required: no
- Spawn count: 1 / 16
- Pending subagents: c4b91461-de67-48ed-a02c-b351436429dd
- Predecessor: none
- Successor: not yet spawned

## Active Timers
- Heartbeat cron: b4fa2f5a-2468-4f3f-9fc5-bea2710bb6f6/task-12
- Safety timer: none

## Artifact Index
- .agents/teamwork/ORIGINAL_REQUEST.md — Verbatim user request
- .agents/teamwork/swe_1/DISPATCH.md — Verbatim dispatch directive
- .agents/teamwork/swe_1/progress.md — Liveness & iteration progress
