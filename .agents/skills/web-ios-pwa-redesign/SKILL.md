---
name: web-ios-pwa-redesign
description: Redesign multi-page public websites (home, features, pricing, blog, about, contact) in the Apple design language as a responsive, installable PWA with full SEO, GEO, AIO and AEO per page, without removing any route or function. Mobile gets an app-like layout with a bottom tab bar, tablet gets a purpose-built layout (not a stretched phone or shrunken desktop), desktop gets an Apple-style layout. Colors and theme come from the existing site, and anti-slop rules keep it from looking AI-generated. Use for COOCA public pages and whenever the user mentions redesign website, halaman publik, landing page, blog, web responsive, PWA, bottom bar, tablet layout, SEO, GEO, AEO, AIO, schema markup, Apple design for web, or "jangan kelihatan AI". Default stack is Laravel Blade, Tailwind and Alpine on shared hosting, adapt to the stack found.
---

# Web iOS-Look PWA Redesign (Public Pages)

Redesign the public pages of a site so they look like a considered Apple-style product, behave like an installed app on phones, use the canvas properly on tablets, and are structured so search engines, answer engines, and generative engines can find, understand, and cite them. **All existing routes and functions stay.**

## Non-negotiable constraints (read first)

1. **Redesign only.** Do not delete, rename, or change behavior of: routes and route names, URLs, controllers, middleware, form `action`/`method`, field `name`s, CSRF, validation messages (`@error`), `old()` values, flash messages, pagination, search/filters, comments, newsletter/contact flows, Alpine/JS handlers, Blade data bindings, `@section`/`@push` names, existing nav/footer links, and existing SEO tags (until replaced by a better equivalent for the same page). Restyle and restructure markup freely; never remove capability.
2. **Additive changes only** outside views: new CSS, partials, static files in `public/`. Anything touching `routes/`, controllers, or models (for example a dynamic `sitemap.xml` route or an SEO field) is proposed to the user first and reported as additive.
3. **Public pages only.** Admin, auth, and dashboard areas are untouched unless asked.
4. **Theme is inherited.** Extract the existing palette and theme; do not invent new brand colors, gradients, or a dark mode that does not exist.
5. **Truthful content.** No invented stats, logos, testimonials, ratings, prices, or features. Missing facts become visible TODOs and a list for the owner.
6. **Three real presentations.** Mobile = app-like with a bottom tab bar. Tablet = purpose-built layout. Desktop = Apple-style. Not one layout squeezed or stretched.
7. **Every page** gets the full SEO + GEO + AIO + AEO treatment (page brief, structure, schema, verification).

If a design idea conflicts with a constraint, keep the function and restyle it. If that is impossible, ask.

## Workflow

### Phase 0 — Intake
Detect the stack (`composer.json`, `package.json`, `tailwind.config.*`, layouts). Default assumption: Laravel Blade + Tailwind + Alpine, shared hosting (no Node on the server; build assets locally). State assumptions in one line. Ask at most one question, and only if something blocks progress (for example: no brand color found, or the owner's policy on AI training crawlers).

### Phase 1 — Discover and snapshot (before touching anything)
1. List routes (`php artisan route:list` or read `routes/web.php`); map each **public** route to its view; note page types.
2. Read layouts, partials, head, nav, footer, scripts, forms.
3. Snapshot parity: `python scripts/parity_check.py snapshot <project-root> --out .parity/before.json --guard routes`. Work on a git branch if the project uses git.
4. Extract the palette: `python scripts/parity_check.py colors <project-root>` and read `tailwind.config.*` plus the main CSS (see `references/apple-web-design-system.md` §2).
5. Baseline audit of the existing pages with `python scripts/seo_audit.py <urls>` (against `php artisan serve` or the staging URL) for a before/after comparison.

### Phase 2 — Plan
Build a page inventory: `route | view | page type | primary query | layout matrix (mobile/tablet/desktop) | schema types`. Use `references/seo-geo-aio-aeo.md` §2 (page brief) and `references/responsive-patterns.md` §3 (layout matrix). For large sites, finish Phases 3 and one sample page, then confirm direction before doing the rest.

### Phase 3 — Foundation (one time)
- `assets/tokens.css` → fill the `REPLACE_*` values from the extracted palette; wire Tailwind (`references/apple-web-design-system.md` §9). Self-host Inter.
- `assets/seo-head.blade.php` → merge into the layout head, keeping every existing `@yield`/`@section` name so no page loses metadata.
- Layout shell: glass top bar (tablet and desktop), compact top bar (mobile), `assets/bottom-tab-bar.blade.php` (mobile) using **existing route names only**, footer (grouped list on mobile, sitemap grid on tablet and desktop). Every existing top-level public route must be reachable on every device class.
- PWA: `assets/manifest.webmanifest`, `assets/sw.js`, `assets/offline.html`, `assets/pwa-register.js`, icons, `.htaccess` additions (`references/pwa.md`).
- `robots.txt` (keep existing rules, add Sitemap) and sitemap approach (`references/seo-geo-aio-aeo.md` §8–9).

### Phase 4 — Pages (one by one)
For each page:
1. Fill the page brief; write the answer block, headings, FAQ, and schema from **existing copy and real data**.
2. Build the three presentations per the layout matrix (`references/responsive-patterns.md` §4–7). One DOM; CSS grid, disclosure widgets, and small device-class components change the presentation.
3. Keep every Blade variable, loop, condition, `@include`, form field, ID, and handler. Move or restyle, never drop.
4. Add page-level SEO: title, description, canonical, OG image, `@push('schema')`, breadcrumbs, internal links, image attributes, LCP preload.
5. Apply the anti-slop rules (`references/anti-slop.md`) while writing, not after.
6. Quick parity check: `python scripts/parity_check.py diff .parity/before.json <project-root>`.

### Phase 5 — Verify
- `parity_check.py diff` exits 0, or each removal is intentional, acknowledged with `--allow`, and explained to the user.
- `seo_audit.py` on every public URL: no ✖, review every ⚠.
- Viewport matrix in `references/responsive-patterns.md` §9; anti-slop test (≥ 9/10); `grep -r REPLACE_ public resources` is empty; no horizontal scroll; keyboard and focus work; reduced motion honored; install and offline tested where possible.
- Be explicit about what was run versus assumed (no browser available is a legitimate limitation to state).

### Phase 6 — Report
1. **Parity table**: routes, links, forms, fields before/after (from the script), plus any additive changes.
2. **Palette used**: hex, role, source file.
3. **Per-page table**: URL, title, description, H1, schema types, answer block present, FAQ present.
4. **Owner input needed**: real FAQs, stats, authors, logos, prices, AI-crawler training policy, icons.
5. **Verified vs not verified**, risks, and next steps (Search Console/Bing submission, device testing).
6. State plainly that rankings and AI citations are not guaranteed.

## Design and build rules (summary; details in the references)

- **Apple language**: confident type, whitespace, one accent from the existing palette, hairlines and soft depth, glass only on bars and sheets, real product visuals, restrained motion. SF on Apple devices, self-hosted Inter elsewhere. 17 px body, 44 px targets.
- **Mobile**: bottom tab bar (4–5 items, overflow into a "Lainnya" sheet), compact top bar with back button on inner pages, snap rails, grouped lists, segmented controls, sheets, safe areas, press states, keyboard-aware bar, standalone-mode behavior.
- **Tablet (768–1279)**: top bar with inline links, split hero, feature explorer, asymmetric bento, side-by-side media and text, two-pane blog, sticky TOC/rail in landscape, centered sheets, hover and keyboard support. Test: if it looks like a stretched phone or a squeezed desktop, redo it.
- **Desktop**: Apple-like marketing layout on the same tokens.
- **PWA**: manifest, service worker (network-first pages, stale-while-revalidate assets, never authenticated areas), offline page, update toast, install UX, iOS hint.
- **SEO/GEO/AIO/AEO**: server-rendered content, unique title/description/canonical, one H1, answer-first intro, question headings, visible FAQ, matching JSON-LD, citable specifics, author and dates, internal links, AI crawler policy, sitemap, Core Web Vitals.
- **Anti-slop**: no gradient/glow/blob decoration, no emoji icons, no identical-card grids, no hype phrases, no invented proof, natural Indonesian that keeps the site's voice.

## Common mistakes to avoid

- Removing a link, field, or section because it "doesn't fit" the new design.
- Hiding the same content with `display:none` at every breakpoint, or loading main content via JS.
- A tab bar that drops routes, or more than 5 items without a "Lainnya" sheet.
- A sheet nested inside the blurred tab bar (`backdrop-filter` traps fixed children); keep it a sibling.
- `100vh` instead of `dvh`; fixed bars ignoring safe areas; inputs below 16 px.
- Reveal animations that start hidden without a `js` class, hiding content from crawlers.
- Caching authenticated pages in the service worker, or serving stale HTML.
- JSON-LD that describes content not visible on the page, or invented ratings.
- Adding dark mode, new hues, or gradients the brand never had.
- Declaring success without running the parity and SEO scripts.

## Reference files

- `references/apple-web-design-system.md` — principles, palette derivation, type, spacing, components, motion, Tailwind wiring
- `references/responsive-patterns.md` — breakpoints, layout matrix, mobile app patterns, tablet patterns, page recipes, failure tests, test matrix
- `references/pwa.md` — manifest, icons, service worker, install/update UX, Apache, iOS specifics, testing
- `references/seo-geo-aio-aeo.md` — page brief, technical checklist, AEO/GEO/AIO tactics, schema templates, AI crawler policy, sitemap, recipes, verification
- `references/anti-slop.md` — visual and copy tells, honesty rules, craft details, slop test
- `assets/tokens.css`, `assets/bottom-tab-bar.blade.php`, `assets/seo-head.blade.php`, `assets/manifest.webmanifest`, `assets/sw.js`, `assets/offline.html`, `assets/pwa-register.js` — templates with `REPLACE_*` placeholders to fill from the real site
- `scripts/parity_check.py` — `snapshot`, `diff`, `colors` (route/link/form/field parity guard and palette extraction)
- `scripts/seo_audit.py` — per-page SEO/GEO/AEO/PWA audit on rendered HTML, plus robots, sitemap, AI-bot rules, manifest
