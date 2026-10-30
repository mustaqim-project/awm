## 2026-10-30T22:59:44Z

This is a single self-contained fix; keep it small and focused. Transform the sub-navigation layout in COOCA from top horizontal tabs into a secondary sidebar (dual sidebar layout), where the main sidebar is on the far left and the secondary sidebar is directly adjacent to it displaying the sub-navigation menu of the active module.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. Dual Sidebar Layout & Sub-Navigation Migration
Replace the top horizontal module tabs across all module views with a secondary sidebar directly adjacent to the primary sidebar. The secondary sidebar must render the sub-navigation items defined for the active module in `app/Support/Navigation/NavigationRegistry.php` and match the dual sidebar visual hierarchy shown in the user's reference image (`C:/Users/Ulfah Ramadhani/.gemini/antigravity/brain/5a387b3f-858d-46ab-a773-7091ccd180c9/.user_uploaded/media_1793400436379_e60310d7.png`).

### R2. Responsive Mobile & Tablet Drawer Integration
On mobile and tablet viewports, integrate the secondary sub-navigation into the slide-over/drawer mobile menu (e.g. as an accordion or nested sub-list under the corresponding module) so that all sub-menu links remain accessible without horizontal overflow.

### R3. Preserved RBAC Permissions & Multi-Industry Gating
Preserve all role-based access control (RBAC) rules and multi-industry module gating (Zero Feature Leakage) on all sub-menu links rendered in the secondary sidebar.

### R4. Automated Verification & Regression Safety
Implement automated programmatic test verification (such as PHPUnit tests verifying view compilation, navigation rendering, absence of legacy top tabs, and permission gating) confirming that all 15 modules compile and render properly.

## Acceptance Criteria

### UI & Layout Structure
- [ ] No module view displays the legacy horizontal top tabs (`<x-module-tabs />`)
- [ ] On desktop screens, the layout displays a dual sidebar: primary sidebar on the far left and secondary sidebar directly adjacent to it
- [ ] Sub-navigation items in the secondary sidebar reflect the active module's menu definitions from `NavigationRegistry`
- [ ] The active sub-page has a distinct active state styling (active pill / highlight) matching the reference design
- [ ] On mobile/tablet screens, sub-navigation is accessible within the mobile drawer navigation without breaking layout

### Functionality & Security Guardrails
- [ ] Clicking any sub-menu item navigates directly to the correct route across all 15 modules
- [ ] Gated features and restricted permissions are hidden or inaccessible according to tenant industry settings and user roles
- [ ] All automated PHPUnit tests for navigation rendering and layout assertions pass without errors

---
## Context & Directives
- **Your Working Directory**: `.agents/teamwork/swe_1/`
- **Original Request Reference**: `.agents/teamwork/ORIGINAL_REQUEST.md`
- **Role & Identity**: SWE Light Orchestrator (`teamwork_swe`). Implement the single self-contained change, run review cycles, write progress to `.agents/teamwork/swe_1/progress.md` and BRIEFING.md, and report victory when tests pass and requirements are fulfilled.
