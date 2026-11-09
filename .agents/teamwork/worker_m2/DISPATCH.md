## 2026-11-08T23:52:58Z

You are worker_m2.
Your working directory is: C:\laragon\www\cooca_core\.agents\teamwork\worker_m2\
The workspace root is: C:\laragon\www\cooca_core

MANDATORY FIRST STEP: Read C:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md before starting work.
Also read C:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md.
Also read Explorer 1 findings:
- C:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\handoff.md
- Skill reference: C:\Users\Ulfah Ramadhani\.gemini\config\skills\ui-simplify-layout\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

SCOPE & EXCLUSIVE WRITE OWNERSHIP:
You own exclusively for Milestone 2:
- `resources/views/app/invoices/index.blade.php`
- `resources/views/app/invoices/create.blade.php`
- `resources/views/app/invoices/edit.blade.php`
- `resources/views/app/invoices/show.blade.php`
- `resources/views/app/invoices/print.blade.php`
- `resources/views/app/labor-machines/index.blade.php`

TASKS:
1. Fix Critical JS Syntax in `resources/views/app/invoices/print.blade.php`:
   - Fix lines 472-477: unclosed `else` block and unclosed `function downloadPDF()`. Close the block properly so `window.addEventListener('load')` and `downloadPDF()` compile and auto-trigger on `?print=1` or `?download=1`. Test with Node.js parser.
2. Primary Action Consolidation & View Auth Gating in `create.blade.php` & `edit.blade.php`:
   - Eliminate competing duplicate solid `#007AFF` Save buttons. Retain single authoritative solid primary action (or sticky bottom bar) and secondary Cancel button per `ui-simplify-layout`.
   - Wrap top Save button with `@can('invoices.create')` in `create.blade.php` and `@can('invoices.edit')` in `edit.blade.php`.
   - Fix mobile touch targets (ensure >= 44px for buttons, inputs, item remove buttons). Standardize button heights (`min-h-[44px]`).
3. Mobile Touch Targets & Dropdown Clipping in `resources/views/app/invoices/index.blade.php`:
   - Replace crowded 4-button mobile layout (32px buttons) with a clean chevron link to detail and an overflow menu (`⋯`) or well-spaced targets (`min-h-[44px]`).
   - Fix table action dropdown clipping by using `<template x-teleport="body">` or proper positioning so it isn't clipped by `overflow-hidden`.
   - Wrap PDF / Print link with `@can('invoices.export')` or `@can('invoices.view')`.
4. Header Action Clutter & Auth Gating in `resources/views/app/invoices/show.blade.php`:
   - Clean up responsive toolbar wrapping on mobile to avoid overlapping header elements.
   - Wrap Quick "Cetak A4", WhatsApp link, and PDF link with `@can`. Replace raw `Context::hasPermission` with Blade `@can`.
5. Layout Ergonomics, Modals & Touch Targets in `resources/views/app/labor-machines/index.blade.php`:
   - Consolidate competing "Add" buttons: provide a clean, context-aware primary action in the header that adapts to active section.
   - Mobile card list: upgrade Edit and Delete button touch targets to `min-h-[44px] min-w-[44px]` and ensure >=8px spacing to eliminate accidental deletes.
   - Expand form modals from narrow `sm:max-w-md` to comfortable 2-column Bento canvas (`max-w-4xl`).
   - Wrap hardcoded strings with `__()`.
6. Verification:
   - Run `php -l` on all 6 view files (must pass 0 errors).
   - Run `php artisan view:clear` and `php artisan view:cache` to confirm clean Blade compilation.
   - Test JS parsing on `print.blade.php`.

DELIVERABLE:
Write a comprehensive handoff report to:
`C:\laragon\www\cooca_core\.agents\teamwork\worker_m2\handoff.md`
Document every change, file paths, and verification command outputs.
Update `C:\laragon\www\cooca_core\.agents\teamwork\worker_m2\progress.md`.
Send a completion message when finished.
