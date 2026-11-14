# Dispatch: explorer_survey_1
Role: Public Layouts and Assets Surveyor
State folder: C:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\
Working directory: C:\laragon\www\cooca_core
Original Request: C:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md

## 2026-11-13T21:52:02Z
You are explorer_survey_1, a teamwork_explorer agent.
Your working directory is: C:\laragon\www\cooca_core
Your dedicated state folder is: C:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\
You MUST read the original request before doing anything else:
Path: C:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md

Task: Public Marketing Layouts, Assets, Typography, Chatbot & Tooling Survey
1. Map and inspect every file under your assigned scope:
   - Layouts & partials:
     - `resources/views/layouts/public_marketing.blade.php`
     - `resources/views/layouts/partials/help_chatbot_widget.blade.php`
     - `resources/views/layouts/partials/typography.blade.php`
     - `resources/views/public/partials/` (recursively inspect all files: header, footer, navigation, cta, banners, etc.)
2. Analyze the global asset pipeline and dependencies:
   - How Vite, CSS, JS, and Google/local fonts are loaded in `public_marketing.blade.php`.
   - Check for render-blocking assets, font bloat, layout shifts (CLS), preloading of critical assets vs non-critical.
   - Deep dive into `help_chatbot_widget.blade.php`: how it is loaded/initialized, whether it blocks initial interactivity/TBT, and design a non-blocking/lazy-loading deferral mechanism.
   - Deep dive into `typography.blade.php`: font family declarations, font-display: swap, layout shift risks.
   - Theme system: inspect dark/light theme toggle, localStorage keys, class `.dark` application, flash of incorrect theme (FOUC) prevention, and verify adherence to a single source of truth without duplicated theme definitions.
3. Discover verification resources:
   - Check `package.json`, build scripts (`npm run build`, `npm run dev`), linters, test commands.
   - Check PHP linting (`php -l`) and any existing test suites or static analysis tools in the repository.
4. Conduct a code-level audit across the 7 dimensions for your scoped files:
   (1) Responsive Design, (2) Core Web Vitals & LCP, (3) TBT & JavaScript, (4) CSS/Fonts/Assets, (5) SEO, (6) Light/Dark Theme contrast, (7) Multilingual/Localization.
   Specify for each finding: Priority (Critical/High/Medium/Low), Category, Affected File, Code Location, Root Cause, Impact, Concrete Fix, Regression Risk.
5. Write your complete handoff report to:
   `C:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_1\handoff.md`
   And send a completion message to parent with the summary and path.

