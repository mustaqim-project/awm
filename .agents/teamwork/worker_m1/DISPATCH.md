## 2026-11-13T22:31:26Z
You are worker_m1, a teamwork_worker agent.
Your working directory is: C:\laragon\www\cooca_core
Your dedicated state folder is: C:\laragon\www\cooca_core\.agents\teamwork\worker_m1\
You MUST read the original request before doing anything else:
Path: C:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md

Scope Document: C:\laragon\www\cooca_core\.agents\teamwork\orchestrator_1\PROJECT.md
Survey Analysis: C:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\handoff.md

DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

You EXCLUSIVELY own and may modify these files:
- `resources/views/layouts/public_marketing.blade.php`
- `resources/views/layouts/partials/help_chatbot_widget.blade.php`
- `resources/views/layouts/partials/typography.blade.php`
- `resources/views/public/partials/live_chat_widget.blade.php`
- `resources/views/public/partials/cookie_consent_banner.blade.php`
- `resources/views/public/partials/tracking_pixels.blade.php`
- `resources/views/public/blog/show.blade.php`

Your Milestone 1 Tasks:
1. Asset Pipeline & Core Web Vitals (CWV) in `public_marketing.blade.php`:
   - Eliminate client-side runtime Tailwind Play CDN (`<script defer src="https://cdn.tailwindcss.com"></script>`) and the `console.warn` suppression hack.
   - Use precompiled production Vite assets (`@vite(['resources/css/app.css', 'resources/js/app.js'])`) or verify the compiled bundle is cleanly integrated.
   - Add `defer` to `<script src="{{ asset('js/app-alert.js') }}"></script>`.
   - Prevent font layout shift (CLS): ensure font stack prioritizes system fonts and clean fallbacks.
2. Chatbot and Live Chat Deferral:
   - In `resources/views/layouts/partials/help_chatbot_widget.blade.php` and `resources/views/public/partials/live_chat_widget.blade.php`:
     - Defer Alpine initialization and heavy DOM mounting until user trigger (click on button) or idle time (`requestIdleCallback`).
     - Eliminate background polling intervals (`setInterval(..., 3500)`) and instant WebSocket/Pusher initialization on initial page load to eliminate TBT overhead.
3. Mobile Chat Window Viewport Trap & Collisions Fix:
   - Fix `floating-live-chat-window` on mobile:
     Remove fixed 748px height overflow trap (`bottom: 10.5rem` + 580px fixed height). Make it a responsive bottom sheet/modal on mobile (`bottom: 0`, `max-h-[85vh]`, safe-area insets).
     Ensure header bar, title, and close button are 100% visible and accessible on mobile screens < 740px.
   - Resolve collision between mobile bottom dock and Cookie Consent Banner.
4. Mobile Theme Switch Accessibility:
   - Make theme toggle accessible on mobile screens (inside mobile navigation menu drawer or header bar), not restricted to `hidden sm:inline-flex`.
5. Cookie Consent Banner Touch Targets:
   - In `resources/views/public/partials/cookie_consent_banner.blade.php`:
     Upgrade button touch targets from `min-h-[34px]` to `min-h-[44px]` per WCAG 2.5.5 / Apple HIG.
6. Blog Show Inline CSS Optimization:
   - In `resources/views/public/blog/show.blade.php`:
     Clean up and optimize the ~17.8 KB inline `<style>` CSS in `<head>`, removing redundant overrides.
7. Verification:
   - Run `php -l` on all modified Blade files to verify 0 syntax errors.
   - Run `npm run build` to verify clean build with 0 errors.
   - Run existing automated test suites to ensure zero regressions.

Document commands and results in your handoff report at:
`C:\laragon\www\cooca_core\.agents\teamwork\worker_m1\handoff.md`
And message parent when complete.
