# Original User Request

## Approved Task Prompt — 2026-11-13T21:36:43Z

Perform a comprehensive, code-level audit and optimization of the entire public frontend of the COOCA website across all specified marketing layouts, templates, and partials. Deliver a structured audit report with concrete findings and implement high-impact optimizations directly in the codebase.

Working directory: C:\laragon\www\cooca_core
Integrity mode: development

## Requirements

### R1. Mandatory Scope and Architecture Mapping
Audit every file and nested file inside the following paths without exception:
- Layouts and partials:
  - `resources/views/layouts/partials/help_chatbot_widget.blade.php`
  - `resources/views/layouts/partials/typography.blade.php`
  - `resources/views/layouts/public_marketing.blade.php`
- Public directories (recursively inspect all files contained within):
  - `resources/views/public/blog`
  - `resources/views/public/business-operating-system`
  - `resources/views/public/calculators`
  - `resources/views/public/contact`
  - `resources/views/public/content-automation`
  - `resources/views/public/discovery`
  - `resources/views/public/home`
  - `resources/views/public/landing_commercial`
  - `resources/views/public/marketplace`
  - `resources/views/public/omnichannel`
  - `resources/views/public/omnichannel-erp`
  - `resources/views/public/partials`
  - `resources/views/public/resources`
  - `resources/views/public/solutions`
  - `resources/views/public/templates`
- Public pages:
  - `resources/views/public/about.blade.php`
  - `resources/views/public/business_landing.blade.php`
  - `resources/views/public/demo.blade.php`
  - `resources/views/public/pricing.blade.php`
  - `resources/views/public/privacy.blade.php`
  - `resources/views/public/security.blade.php`
  - `resources/views/public/sitemap.blade.php`
  - `resources/views/public/support.blade.php`
  - `resources/views/public/terms.blade.php`
  - `resources/views/landing.blade.php`
Trace dependencies between `public_marketing.blade.php`, shared components, typography, chatbot, CSS/JS loading, theme sources of truth, and localization. Map shared issues affecting multiple pages back to their root cause in shared partials or layouts.

### R2. Code-Level Audit and Structured Findings Report
Inspect the actual codebase implementation across 7 dimensions and provide a structured findings report (maximum 10 prioritized findings per file with significant issues):
1. **Responsive Design:** Mobile-first behavior, navigation on small screens, horizontal overflow, responsive grids, typography scaling, touch target sizes, fixed dimensions.
2. **Core Web Vitals & LCP:** Identify likely LCP elements, above-the-fold media, render-blocking CSS/JS, explicit image dimensions, modern formats, preloading only critical assets.
3. **TBT & JavaScript:** Unnecessary JS on initial load, long tasks, render-blocking/duplicate/unused scripts, defer/lazy initialization of widgets (especially chatbot).
4. **CSS, Fonts & Assets:** Unused/duplicate CSS, render-blocking styles, font file/weight bloat, SVG/image sizing, layout shift prevention.
5. **SEO:** Title, meta descriptions, canonical URLs, heading hierarchy, semantic HTML, internal links, structured data, image alt attributes, language metadata.
6. **Light Theme and Dark Theme:** Line-by-line code audit of background/text colors, borders, shadows, icons/SVGs, forms, buttons, cards, hover/focus/active states. Ensure text readability, accessible contrast, no theme-specific visual breakage, no theme flicker, and adherence to the single source of truth.
7. **Multilingual and Localization:** Line-by-line audit of headings, copy, navigation labels, buttons, placeholders, aria-labels, alt attributes, and error messages to eliminate hardcoded user-facing strings and missing/duplicate translation keys without breaking existing Laravel localization architecture.
Each reported finding must specify: Priority (Critical/High/Medium/Low), Category, Affected File, Code Location, Root Cause, Impact, Concrete Fix, and Regression Risk.

### R3. Implementation of High-Impact Optimizations
Apply concrete, maintainable fixes in the codebase prioritizing highest-impact items:
- Target Core Web Vitals: LCP <= 2.5s, TBT <= 200ms, CLS <= 0.1, INP within Good threshold.
- Resolve render-blocking assets, lazy-load below-the-fold assets, defer non-critical widgets (including chatbot) so initial page rendering and interactivity are not delayed.
- Eliminate horizontal overflow and layout shifts across viewports (mobile, tablet, laptop, desktop).
- Fix theme contrast and color mismatches in both Light and Dark themes without introducing duplicate theme systems.
- Replace hardcoded user-facing strings with proper Laravel localization keys and ensure layout stability across languages.
- Maintain compatibility with existing Laravel Blade architecture and preserve existing functionality unless a change is justified.

## Verification Resources

Inspect the repository for existing build scripts, test suites, linters, static analysis tools, and performance or SEO validation workflows. Use the relevant existing verification tools whenever available. Do not invent verification commands that are not configured in the repository. After implementation, run all relevant checks to verify syntax, build integrity, and prevent regressions.

## Acceptance Criteria

### Audit Scope and Findings Report
- [ ] Every listed layout, partial, public page, and nested directory file is audited and included in the architecture dependency map.
- [ ] A structured audit findings report is generated detailing up to 10 findings per file with significant issues, including Priority, Category, Affected File, Code Location, Root Cause, Impact, Concrete Fix, and Regression Risk.

### Responsive Design and Layout Stability
- [ ] Public templates render without unintended horizontal overflow or broken layouts across mobile, tablet, and desktop viewports.
- [ ] Explicit width and height attributes or CSS aspect ratios are defined for media elements to keep CLS <= 0.1.

### Performance and Core Web Vitals
- [ ] Above-the-fold critical rendering paths are optimized (LCP element not lazy-loaded, critical assets prioritized) targeting LCP <= 2.5s.
- [ ] Non-critical JavaScript and third-party widgets (including help chatbot) are deferred or lazily initialized to maintain TBT <= 200ms.
- [ ] No unnecessary duplicate CSS or unused scripts are loaded during the initial page load.

### Theme and Accessibility
- [ ] All UI elements (text, backgrounds, borders, icons, form controls, cards, interactive states) maintain accessible contrast and consistent appearance in both Light and Dark themes.
- [ ] Theme toggle and initialization operate from a single source of truth without visual flash of incorrect theme.

### Localization and SEO
- [ ] User-facing text in public marketing views is localized using Laravel translation keys without hardcoded strings breaking i18n support.
- [ ] Semantic HTML headings (single H1, logical hierarchy), canonical links, meta descriptions, and image alt attributes are present and properly structured across all audited pages.

### Code Integrity and Verification
- [ ] All modified Blade and PHP files pass PHP syntax validation (`php -l`) with 0 errors.
- [ ] Repository build scripts (e.g., `npm run build`) execute cleanly with 0 errors.
- [ ] All existing automated tests and linting suites configured in the repository pass without regressions.


## Follow-up — 2026-11-14T00:13:11Z

User Request / Additional Quality Requirements:

Please resume work following the server restart and incorporate the user's additional quality requirements for the remaining milestones:

1. Do not consider a milestone complete based solely on successful PHP syntax checks, builds, or automated unit tests.
2. Measure actual frontend performance using Lighthouse or equivalent tools. Provide before-and-after results for LCP, TBT, CLS, Speed Index, and overall performance scores wherever baseline measurements are available.
3. Test all public pages in both Light Theme and Dark Theme, and in all supported languages (Indonesian and English).
4. Perform responsive testing at viewport widths of 320px, 360px, 390px, 414px, 768px, 1024px, 1440px, and 1920px.
5. Verify that shared component changes do not introduce regressions on other public pages.
6. Do not report an issue as fixed unless the implementation has been inspected and the relevant behavior has been verified.
7. Maintain a complete audit inventory showing every scoped file, its audit status, identified issues, and implementation status. Explicitly report any files that could not be inspected or tested.
8. At final delivery, provide:
   - Files audited and modified.
   - Before-and-after performance benchmarks.
   - Remaining issues and limitations.
   - Test execution results.
   - Light/Dark Theme verification.
   - Indonesian/English localization verification.
   - SEO validation results.

Do not sacrifice existing functionality, accessibility, visual consistency, or maintainability for performance improvements.

Please confirm receipt, resume execution, and continue through the remaining milestones adhering strictly to these requirements.
