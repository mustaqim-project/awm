## 2026-11-07T07:49:36Z
You are Worker M2 (UI & View Auth Worker).
Your Working Directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_m2
Your parent is: e2aae656-229b-42bb-a7be-8273bdd8a984

MANDATORY INSTRUCTIONS:
1. You MUST read the Original Request file first:
   c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
2. Read the Project Plan & Architecture:
   c:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md
3. Read Explorer 1's detailed handoff report (UI & layout specifics):
   c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\handoff.md
4. Read Explorer 2's detailed handoff report (View authorization wrapping matrix):
   c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_2\handoff.md
5. Read Explorer 3's handoff report (Polling optimization):
   c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_3\handoff.md
6. Consult relevant skills:
   C:\Users\Ulfah Ramadhani\.gemini\config\skills\ui-simplify-layout\SKILL.md
   C:\Users\Ulfah Ramadhani\.gemini\config\skills\laravel-permission\SKILL.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

EXCLUSIVE FILE OWNERSHIP (You own and may edit ONLY Blade view files under these paths):
- resources/views/app/ai/
- resources/views/app/ai-studio/

TASKS TO IMPLEMENT:
1. Header CTA Cleanliness & Single Primary Action:
   - `resources/views/app/ai/lobby.blade.php`: Header must have only 1 primary action (`Rapat Tim` `bg-[#007AFF]`); remove the 4 competing bright solid buttons from the header slot (lines 18-49) since they are already present in the segmented tabs bar below.
   - `resources/views/app/ai-studio/index.blade.php`: Retain `Buat Proyek Baru` (`bg-[#007AFF]`) as the single primary header action. Demote `Top Up Kredit` to a subtle secondary pill (`bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 hover:bg-amber-500/20`).
   - `resources/views/app/ai-studio/show.blade.php`: Consolidate header controls on mobile (responsive flex wrap, no overflow). In the canvas action bar (lines 159-190), make `Simpan ke Katalog` the primary solid action (`bg-[#007AFF]`), turn `Posting ke Media Sosial` into secondary tonal/outline, move `Hapus` to quiet destructive with gap >= 16px. Remove duplicated `+` prefix from `+ Render Variasi Baru`.
   - `resources/views/app/ai-studio/inspiration/collections.blade.php`: Remove duplicate `+` from `<span>+ Buat Koleksi Pertama Anda</span>`.
2. Mobile Fixed & Floating Overlap Remediation:
   - `resources/views/app/ai/providers.blade.php`: Adjust toast positioning from `bottom-6` to `bottom-[calc(76px+env(safe-area-inset-bottom))] sm:bottom-6 right-4 sm:right-6 z-[220]` so it never collides with `layouts.app`'s fixed bottom tab bar.
   - `resources/views/app/ai/partials/agent_inspector_hud.blade.php`: Adjust positioning to `inset-x-3 bottom-[calc(72px+env(safe-area-inset-bottom))] sm:inset-x-auto sm:right-6 sm:bottom-6 sm:max-w-md w-auto z-[60]` with `max-h-[70vh]` so it doesn't clip on 375px mobile screens.
   - `resources/views/app/ai/partials/virtual_office_canvas.blade.php`: Ensure Camera preset popover has responsive bounds (`max-w-[calc(100vw-32px)] w-72`).
3. Modal Stacking, Teleportation & z-Index Standardization:
   - Wrap with `<template x-teleport="body">` and set modal container & backdrop to `z-[200]`:
     - `resources/views/app/ai-studio/partials/topup-modal.blade.php` (`z-50` -> `z-[200]`)
     - `resources/views/app/ai-studio/inspiration/index.blade.php` (`collectionModalOpen`)
     - `resources/views/app/ai-studio/inspiration/collections.blade.php` (`createModalOpen`)
     - `resources/views/app/ai-studio/inspiration/my-library.blade.php` (`uploadModalOpen`)
4. Touch Targets & Swipeable Ergonomics:
   - `resources/views/app/ai-studio/inspiration/index.blade.php`: Expand card favorite and bookmark buttons from `w-7 h-7` to minimum 40x40px / 44x44px touch area (`min-w-[40px] min-h-[40px] flex items-center justify-center`).
   - `resources/views/app/ai/index.blade.php`: Convert the 7 quick prompt chips into a swipeable horizontal snap container (`flex gap-2 overflow-x-auto snap-x no-scrollbar pb-1`).
5. Consultation Modal Fixes (`resources/views/app/ai/partials/consultation_modal.blade.php`):
   - Fix malformed comment closing tag on line 101 (`-->`).
   - Add Light/Dark mode classes: `bg-white dark:bg-[#1C1C1E] border-slate-200 dark:border-white/10 text-slate-900 dark:text-white`.
   - Distinct message bubbles: AI Message (`bg-black/5 dark:bg-white/10 text-slate-900 dark:text-white`), User Message (`bg-[#007AFF] text-white`).
6. Polling Optimization (`resources/views/app/ai-studio/show.blade.php`):
   - Refactor Alpine polling loop from `fetch('{{ url("ai-studio/projects") }}/{{ $project->id }}')` to target the lightweight status endpoint with a 60-attempt timeout limit.
7. View-Level Authorization Directives:
   - Wrap action buttons and forms in `resources/views/app/ai/` and `resources/views/app/ai-studio/` with `@if (\App\Support\Context::hasPermission(...) || \App\Support\Context::isAdminOrOwner())` (or `isOwner()`) per Explorer 2 handoff Section 4.3:
     - `ai/actions.blade.php`: Approve/Revise/Reject forms (`ai.approve`)
     - `ai/offices/executive.blade.php`, `growth.blade.php`, `operations.blade.php`: Proposal quick actions (`ai.approve`)
     - `ai/providers.blade.php`: Save Provider form, Test button, Detect Models button (`ai.manage`)
     - `ai/index.blade.php`: Execute Action modal button (`ai.approve`)
     - `ai-studio/index.blade.php`: Buat Proyek Baru (`ai_studio.generate` || `ai_studio.manage`), Top Up Kredit (`ai_studio.generate` || `billing.manage`)
     - `ai-studio/create.blade.php`: Submit form & Prompt Compiler (`ai_studio.generate` || `ai_studio.manage`)
     - `ai-studio/show.blade.php`: Generate button (`ai_studio.generate`), Export to Catalog / Social Media / Delete (`ai_studio.manage`)
     - `ai-studio/inspiration/`: Collection create/delete (`ai_studio.manage`), Upload asset (`ai_studio.manage`), Use as reference (`ai_studio.generate`)

VERIFICATION:
Run and document results:
1. Syntax check on modified Blade views (`php -l` or blade compilation check `php artisan view:cache` and `php artisan view:clear`).
2. Run `php artisan test tests/Feature/AiStudio/AiStudioWebControllerTest.php`.
3. Document all file changes and verbatim outputs.
