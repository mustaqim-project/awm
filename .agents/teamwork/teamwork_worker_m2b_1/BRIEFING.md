# BRIEFING  2026-11-05T01:53:00Z

## Mission
Refactor 11 Themes F&B & Retail (33 Blade files) for Milestone 2B to conform strictly with Universal Blade Contracts §3.1, §3.2, §3.3, eliminate hardcoded Unsplash URLs and industry terms, maintain visual taste/typography/layouts, and verify with tests.

## 🔒 My Identity
- Archetype: worker
- Roles: implementer, qa
- Working directory: c:\laragon\www\cooca_core\.agents\teamwork\teamwork_worker_m2b_1
- Original parent: 3e6ffd97-6534-466f-a0f2-f2278f17c1fe
- Milestone: Milestone 2B (Refactoring Batch 2 Themes)

## 🔒 Key Constraints
- Exclusive write ownership: 33 Blade files in 11 themes (`hero.blade.php`, `features_banner.blade.php`, `category_chips.blade.php` for each) + `tests/Feature/StorefrontBatch2EmpiricalChallengeTest.php`.
- Do NOT edit outside assigned scope.
- Zero cheat mandate: genuine implementations, real dynamic data bindings, no hardcoded test shortcuts.
- Universal Blade Contracts:
  - §3.1 for `hero.blade.php` (Brand Bento Graphic Showcase fallback when heroImage null, dynamic universal values badges, dynamic CTAs).
  - §3.2 for `features_banner.blade.php` (loop over `$landingPage->getUniversalValues()`, dynamic icon/title/desc, preserve card visual taste).
  - §3.3 for `category_chips.blade.php` (dynamic heading/sub, reset filter 'Semua Produk', loop categories with active state, dynamic count/icon).
- Complete elimination of `images.unsplash.com` and hardcoded sector phrases.
- Syntax verification `php -l` on all 33 Blade files, regex audit, and test suite execution passing.

## Current Parent
- Conversation ID: 3e6ffd97-6534-466f-a0f2-f2278f17c1fe
- Updated: not yet

## Task Summary
- **What to build**: Refactor 33 Blade files across 11 F&B and Retail themes, create and run Batch 2 empirical challenge test suite.
- **Success criteria**: 0 syntax errors, 0 unsplash URLs, 0 hardcoded sector terms, 100% contracts compliance, all tests pass.
- **Interface contracts**: STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md §3.1, §3.2, §3.3

## Key Decisions Made
- [TBD]

## Artifact Index
- `.agents/teamwork/teamwork_worker_m2b_1/progress.md`
- `.agents/teamwork/teamwork_worker_m2b_1/handoff.md`

## Change Tracker
- **Files modified**: None yet
- **Build status**: Pending
- **Pending issues**: None

## Quality Status
- **Build/test result**: Pending
- **Lint status**: 0 violations
- **Tests added/modified**: `tests/Feature/StorefrontBatch2EmpiricalChallengeTest.php` (planned)

## Loaded Skills
- None
