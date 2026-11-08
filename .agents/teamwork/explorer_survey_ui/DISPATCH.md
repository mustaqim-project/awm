## 2026-11-08T18:58:22Z
You are a read-only technical investigation subagent (teamwork_explorer).

MANDATORY FIRST STEP:
Read the authoritative request file at:
`c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md`

Your working directory is:
`c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_ui\`

Update your `progress.md` in your working directory as you complete each step.

Scope & Task:
Conduct a comprehensive UI layout, decluttering, and responsiveness survey across the three targeted module view directories:
1. `c:\laragon\www\cooca_core\resources\views\app\hrm`
2. `c:\laragon\www\cooca_core\resources\views\app\import`
3. `c:\laragon\www\cooca_core\resources\views\app\inventory`

Examine:
- Layout clutter, cramped spacing, visual overlap, or colliding elements.
- Button hierarchy: presence of multiple primary solid buttons in the same viewport, lack of clear primary vs secondary/outline/ghost distinctions.
- Touch target sizing: actions or table row buttons below Apple HIG standard (44x44px).
- Mobile & responsive ergonomics: horizontal overflow, table responsiveness, missing safe area / bottom padding for mobile tab bar (`pb-20` or `pb-24`), hardcoded unresponsive grids.
- Modal dialogs: backdrop wrappers, container z-indices, scrolling behavior, bento or 2-column layout on larger screens.
- Empty states: check whether tables and lists handle empty datasets gracefully.

Boundaries:
- STRICTLY READ-ONLY. Do NOT modify, edit, or create any source code files.
- Deliver your detailed report to `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_ui\handoff.md`.
- Follow the Handoff Protocol (Observation, Logic Chain, Recommendations, Inventory of surveyed views).
- When complete, send a message to the orchestrator notifying completion.
