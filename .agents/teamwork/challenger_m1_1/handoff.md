# Empirical Challenge Report: Milestone 1 Layout Stability, Mobile Viewports & Asset Loading

**Agent**: `challenger_m1_1` (Teamwork Empirical Challenger: Critic / Specialist)  
**Date**: 2026-11-13T23:08:00Z  
**Workspace**: `C:\laragon\www\cooca_core`  
**State Folder**: `C:\laragon\www\cooca_core\.agents\teamwork\challenger_m1_1\`  
**Milestone**: Milestone 1 (M1)  
**Final Verdict**: **REJECT**

---

## 1. Observation

### 1.1 PHP Syntax Linting
Command:
```powershell
Set-Location C:\laragon\www\cooca_core
php -l resources/views/layouts/public_marketing.blade.php
php -l resources/views/layouts/partials/help_chatbot_widget.blade.php
php -l resources/views/layouts/partials/typography.blade.php
php -l resources/views/public/partials/live_chat_widget.blade.php
php -l resources/views/public/partials/cookie_consent_banner.blade.php
php -l resources/views/public/partials/tracking_pixels.blade.php
php -l resources/views/public/blog/show.blade.php
```
*Result*: Exit Code 0 across all 7 files:
- `No syntax errors detected in resources/views/layouts/public_marketing.blade.php`
- `No syntax errors detected in resources/views/layouts/partials/help_chatbot_widget.blade.php`
- `No syntax errors detected in resources/views/layouts/partials/typography.blade.php`
- `No syntax errors detected in resources/views/public/partials/live_chat_widget.blade.php`
- `No syntax errors detected in resources/views/public/partials/cookie_consent_banner.blade.php`
- `No syntax errors detected in resources/views/public/partials/tracking_pixels.blade.php`
- `No syntax errors detected in resources/views/public/blog/show.blade.php`

### 1.2 Production Vite Asset Pipeline & Manifest Verification
1. Manifest: `public/build/manifest.json` exists and maps:
   - `resources/css/app.css` -> `assets/app-DbWQsCgM.css` (831,307 bytes on disk)
   - `resources/js/app.js` -> `assets/app-rutYGsf-.js` (92,771 bytes on disk)
2. `resources/views/layouts/public_marketing.blade.php`:
   - Line 208 loads precompiled bundle: `@vite(['resources/css/app.css', 'resources/js/app.js'])`
   - Zero occurrences of `cdn.tailwindcss.com` runtime JIT compiler script
   - Zero occurrences of `console.warn` suppression monkey-patch
   - Line 2667 loads `<script defer src="{{ asset('js/app-alert.js') }}"></script>` with `defer` attribute.

### 1.3 Live Chat Widget & Chatbot Lazy Hydration Verification
1. `resources/views/public/partials/live_chat_widget.blade.php`:
   - Line 71 mounts modal container lazily: `<template x-if="isMounted || isOpen">`
   - Lines 347–360 define mobile bottom sheet layout:
     ```css
     @media (max-width: 639px) {
         .floating-live-chat-window {
             bottom: 0 !important;
             right: 0 !important;
             left: 0 !important;
             width: 100% !important;
             max-width: 100vw !important;
             height: 85vh !important;
             max-height: 85vh !important;
             border-bottom-left-radius: 0 !important;
             border-bottom-right-radius: 0 !important;
             padding-bottom: env(safe-area-inset-bottom, 0px);
         }
     }
     ```
   - Lines 344–346 safely elevate the FAB: `bottom: calc(5.75rem + env(safe-area-inset-bottom, 0px)) !important;`
   - Lines 500–533 isolate polling timers inside `startActivePolling()` and `stopActivePolling()`, eliminating initial background thread overhead.

2. `resources/views/layouts/partials/help_chatbot_widget.blade.php`:
   - Line 81 wraps modal container in `<template x-if="isMounted || isOpen">`
   - Synchronous `init()` parsing removed; background initialization deferred.

### 1.4 Touch Target Dimensions (WCAG 2.5.5)
In `resources/views/public/partials/cookie_consent_banner.blade.php` (lines 48–63):
1. Button `acceptAll()`:
   `class="... min-h-[44px] inline-flex items-center justify-center"`
2. Button `rejectOptional()`:
   `class="... min-h-[44px] inline-flex items-center justify-center"`
3. Privacy Link `route('public.privacy')`:
   `class="... py-2 px-1 inline-flex items-center min-h-[44px]"`
Chromium Playwright empirical measurement: All 3 interactive targets measure exactly **44px** in computed height.

### 1.5 EMPIRICAL DEFECT: Cookie Consent Banner Collision with Apple Floating Dock Navbar
Worker M1 claimed in `worker_m1/handoff.md` Section 1.1 (Item 4) and Section 2:
> "Changed mobile bottom offset to `bottom-20 sm:bottom-6` and button heights to `min-h-[44px]`."  
> "Inference: Complete elimination of touch collision; fully compliant with WCAG 2.5.5 / Apple HIG."

Empirical test executed via Chromium Playwright headless box-model simulation (`scripts/test_m1_mobile_viewports.mjs`):
1. **Viewport 320x568 (iPhone SE 1st Gen)**:
   - Mobile Dock: `y = 457px`, `height = 111px`, `bottom = 568px`. **Dock Top = 457px**.
   - Cookie Banner: `y = 224px`, `height = 264px`, `bottom = 488px`. **Banner Bottom = 488px**.
   - **Overlap / Collision = 31px** (`dockTop - bannerBottom = -31px`).
2. **Viewport 375x667 (iPhone 8 / SE 2nd Gen)**:
   - Mobile Dock: `y = 573px`, `height = 95px`, `bottom = 667px`. **Dock Top = 573px**.
   - Cookie Banner: `y = 363px`, `height = 224px`, `bottom = 587px`. **Banner Bottom = 587px**.
   - **Overlap / Collision = 14px** (`dockTop - bannerBottom = -14px`).
3. **Viewport 375x812 (iPhone 12 / 13)**:
   - Mobile Dock: `y = 718px`, `height = 95px`, `bottom = 812px`. **Dock Top = 718px**.
   - Cookie Banner: `y = 508px`, `height = 224px`, `bottom = 732px`. **Banner Bottom = 732px**.
   - **Overlap / Collision = 14px** (`dockTop - bannerBottom = -14px`).

---

## 2. Logic Chain

```
[Observation 1.5: Apple Mobile Floating Dock Navbar geometry in public_marketing.blade.php:2418]
  │
  ├─► The mobile dock declares `pb-[calc(0.75rem+env(safe-area-inset-bottom,0px))]` (12px minimum).
  ├─► The inner container has `py-2` (16px total) and 5 vertical navigation buttons.
  ├─► On 375px screens, total computed dock height = 95px (top at viewportHeight - 95px).
  ├─► On 320px screens, dock labels wrap, total computed dock height = 111px (top at viewportHeight - 111px).
  │
[Observation 1.5: Cookie Consent Banner positioning in cookie_consent_banner.blade.php:33]
  │
  ├─► Cookie banner specifies `class="fixed bottom-20 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 z-50 ..."`
  ├─► Tailwind CSS class `bottom-20` equals `bottom: 5rem` = 80px.
  ├─► Therefore, the banner's bottom edge is anchored at viewportHeight - 80px.
  │
[Mathematical Inevitability of Collision]
  │
  ├─► On 375px: Banner bottom (viewportHeight - 80px) extends 15px past Dock top (viewportHeight - 95px). Overlap = 14px–15px.
  ├─► On 320px: Banner bottom (viewportHeight - 80px) extends 31px past Dock top (viewportHeight - 111px). Overlap = 31px.
  ├─► Banner has z-50; Mobile Dock has z-40.
  ├─► Consequence: The action buttons ("Saya Setuju", "Hanya yang Diperlukan", "Kebijakan Privasi") on the bottom
  │   of the cookie banner physically cover the top icons and labels of the mobile dock ("Beranda", "Platform",
  │   "Solusi", "Fitur", "Menu").
  └─► Inference: The claim of "Complete elimination of touch collision" in worker_m1 handoff is empirically false.
```

---

## 3. Caveats

1. **Other M1 Components Are High Quality**:
   - The Vite migration, elimination of Tailwind Play CDN, deferral of non-critical JS (`app-alert.js`, tracking pixels), chatbot `<template>` lazy mounting, and live chat responsive bottom sheet (`bottom: 0`, `max-h-[85vh]`) are verified 100% sound, robust, and cleanly implemented.
2. **Exclusivity Constraint Respected**:
   - As an Empirical Challenger, under the review-only constraint, I did not modify `cookie_consent_banner.blade.php` myself. The required fix is trivial (one-line class change) and must be applied by the worker.

---

## 4. Conclusion

**Verdict**: **REJECT** (Milestone 1 rejected pending remediation of the Cookie Banner mobile dock collision).

### Actionable Remediation Required:
In `resources/views/public/partials/cookie_consent_banner.blade.php` line 33:
Change:
```blade
class="fixed bottom-20 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 z-50 max-w-md ...
```
To:
```blade
class="fixed bottom-28 left-4 right-4 sm:left-auto sm:right-6 sm:bottom-6 z-50 max-w-md ...
```
*(Or `bottom-[calc(7.25rem+env(safe-area-inset-bottom,0px))]`)*.  
`bottom-28` provides `7rem` (112px) of elevation from the screen bottom, leaving a safe 17px clearance on 375px screens and a 1px–5px clearance on 320px screens above the 95px–111px tall floating dock.

---

## 5. Verification Method

### 5.1 Headless Chromium Playwright Empirical Simulation
```powershell
Set-Location C:\laragon\www\cooca_core
node scripts/test_m1_mobile_viewports.mjs
```
*Current Result*: Exits with code 1; fails 3 out of 38 assertions due to 14px–31px overlap between banner and dock.  
*Pass Condition*: Once `bottom-28` is applied, this script exits with code 0 and reports `VERDICT: APPROVE`.

### 5.2 PHPUnit Feature Challenge Suite
```powershell
Set-Location C:\laragon\www\cooca_core
php vendor/phpunit/phpunit/phpunit tests/Feature/PublicMarketingMilestone1EmpiricalChallengeTest.php
```
*Result*: 5 tests, 49 assertions passed (verifies Vite bundle, PHP syntax, touch target heights, live chat sheet geometry, and mathematically records collision).

### 5.3 Automated PHPUnit Regression Suite
```powershell
Set-Location C:\laragon\www\cooca_core
php vendor/phpunit/phpunit/phpunit tests/Feature/Security/CleanUrlCanonicalTest.php tests/Feature/Public/PublicPricingAndEntitlementConsistencyTest.php tests/Feature/AdminPanelAndGoogleAuthTest.php tests/Feature/AdversarialCompilationAndLayoutTest.php
```
*Result*: 40 tests, 194 assertions passed (0 errors, 0 regressions).
