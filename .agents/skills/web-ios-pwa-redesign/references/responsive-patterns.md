# Responsive Patterns: Mobile (app-like), Tablet (purpose-built), Desktop

Contents: 1 Principle · 2 Breakpoints · 3 Layout matrix · 4 Mobile patterns · 5 Tablet patterns · 6 Desktop · 7 Recipes per page type · 8 Rules of thumb and failure tests · 9 Test matrix

## 1. Principle: one content, three presentations
Each page has one set of content and one set of URLs, but **three designed presentations**. Mobile is not a squeezed desktop, and tablet is neither a stretched phone nor a shrunken desktop. Before writing markup for any section, write its row in a layout matrix (section 3). Content stays in the HTML for every presentation (mobile-first indexing and AI fetchers read the HTML, not your breakpoints); presentation changes through CSS grid, order, disclosure widgets, and a few device-class components (tab bar vs top nav).

## 2. Breakpoints
| Class | Width | Typical devices | Nav | Grid |
|---|---|---|---|---|
| Mobile | < 768 | phones, small foldables | bottom tab bar + compact top bar | 4 col, 20 px margins |
| Tablet portrait | 768–1023 | iPad/Android tablets portrait, large foldables | glass top bar with inline links | 8 col, 24–32 px margins |
| Tablet landscape | 1024–1279 | tablets landscape, small laptops | top bar + sticky side rail where it helps | 12 col |
| Desktop | ≥ 1280 | laptops, desktops | top bar (apple.com-style) | 12 col, container 1200 |

Tailwind: `phone:` (max 767), `tablet:` (768–1279), plus default `md:`/`lg:`/`xl:`. Use `@media (hover: hover)` for hover effects and `@media (pointer: coarse)` to enlarge targets. Orientation changes and Split View/multi-window change width at runtime, so use CSS media/container queries, never user-agent or one-time JS checks.

## 3. Layout matrix (write this per page before coding)
| Section | Mobile | Tablet (768–1279) | Desktop |
|---|---|---|---|
| Hero | Title, subline, full-width CTA, visual below (cropped, 4:5) | Split: text 55% left, visual 45% right, CTA pair inline | Split or centered, large visual |
| Features | Snap rail (82% cards) or grouped list | **Feature explorer**: left segmented list, right live panel (all panels in DOM) | Bento 3-col with one lead tile |
| How it works | Vertical stepper | Horizontal 3-step with connecting line | Same, wider |
| Proof | Stacked quotes | 2 col | 3 col |
| Pricing | Segmented switch + one card at a time (snap) | 2–3 cards side by side, emphasized middle | 3 cards |
| Blog teaser | Snap rail | Featured large + 2 small | Featured + 3 |
| CTA | Sticky bottom CTA above tab bar | Inline band | Inline band |
| Footer | Grouped accordion list | 2×N link grid | 4–5 column sitemap |

Fill it with the real sections of the real page. The same matrix is the checklist for review.

## 4. Mobile: make it behave like an app (as a PWA)
**Shell**
- Bottom tab bar (`assets/bottom-tab-bar.blade.php`): 4–5 destinations, 24 px outline icons + 11 px labels, glass background, hairline top border, `env(safe-area-inset-bottom)`, active state via `aria-current="page"`. Hides when a text field is focused (keyboard). Body gets `has-tabbar` padding so content and footers aren't covered.
- Every existing top-level public route must be reachable: put overflow into a **Lainnya** sheet (grouped list). Never drop a route to make the bar fit.
- Compact top bar (48 px + safe area): logo or page title, one optional action. On inner pages show a back button (`history.back()` with a fallback `href` to the parent route, so it works for deep links and in standalone mode). Large title that collapses is optional; if used, keep the h1 in the DOM.
- Primary conversion (contact, demo, WhatsApp) as a persistent pill above the tab bar on content-heavy pages, or as the last tab. One persistent CTA only.

**Content**
- Sections become cards in horizontal **snap rails** (`.snap-rail`), **grouped lists** (`.group`) for link lists and settings-like content, **segmented controls** (`.segmented`) for filters/tabs, and **sheets** (`.sheet`) for secondary content (share, filters, more).
- Blog filters: horizontally scrollable chips (real `<a>` links to the existing category URLs so they stay crawlable).
- Accordions with `<details>`; long text blocks collapse behind "Baca selengkapnya" only if the full text is still in the DOM.
- Images: full-bleed or 20 px inset with 18–24 px radius, fixed aspect ratios to prevent layout shift.
- Forms: one column, labels above, 16–17 px text, 48–52 px fields, correct `type`, `inputmode`, `autocomplete`, sticky primary button when the form is long, keyboard-safe (use `dvh`, not `vh`).

**Touch & feel**
- 44 px minimum targets; `touch-action: manipulation`; `-webkit-tap-highlight-color: transparent`; pressed state (`:active` scale/opacity) on every tappable; `overscroll-behavior-y: none` on `body` unless pull-to-refresh is implemented; momentum scrolling in rails; scroll snapping where lists are horizontal.
- Standalone mode (`display-mode: standalone`): remove browser-like chrome from the page (install banners, "open in app" links), keep an in-app back affordance, apply top safe area padding, disable text selection on UI chrome but not on content.
- Use `100dvh`/`svh` for full-height pieces; fixed bars account for the iOS Safari dynamic toolbar and Android Chrome URL bar.

## 5. Tablet: designed for the canvas, not scaled
Tablets have width but a touch interaction model. Use the extra space for **structure**: two panes, side-by-side media and text, bento asymmetry, persistent filters, sticky tables of contents.
- **Navigation**: glass top bar with inline links (no bottom tab bar, no hamburger). In tablet landscape, content-heavy sections (blog, docs-like pages) may add a sticky side rail with categories/TOC.
- **Hero**: split layout, text at ~55% with a larger product visual at ~45% that bleeds off the edge or sits in a rounded panel.
- **Feature explorer** (replaces "3 cards in a row → 3 cards in a column"): a left list (segmented, vertical) and a right panel showing the selected feature's visual and copy. Implement with Alpine `x-data="{tab:0}"`, **all panels rendered in the DOM** (hidden via CSS/`x-show`, not removed), buttons with `aria-selected`; on mobile the same markup renders as stacked sections or a snap rail.
- **Bento**: 8-col grid with a 2-col-wide lead tile and 1-col tiles; tiles have different content densities, not identical icon+title+text.
- **Blog index**: featured post as a wide card, then a 2-column grid; categories as a persistent left rail (landscape) or chips (portrait); search field in the header zone.
- **Blog post**: reading column 640–680 px centered; at landscape add a right rail (sticky TOC, share, author) and keep the TL;DR above the fold; portrait uses a "Daftar isi" disclosure under the byline.
- **Pricing**: 2–3 tiers side by side with one emphasized, feature comparison as a table with a sticky header.
- **Contact**: two columns, form left, contact methods and map/WhatsApp right.
- **Footer**: sitemap grid.
- **Input**: support touch **and** pointer: hover styles only under `(hover: hover)`, visible focus rings, full keyboard operability (tablets are often used with keyboards).
- **Modals**: centered sheet (≈ 540 px), not a full-width bottom sheet.

## 6. Desktop
Apple-like marketing layout: larger type, wide bento, sticky glass top bar, mega-footer, hover affordances, reading rail for articles. Keep it faithful to the same tokens; desktop is the least interesting breakpoint and the easiest to get right, so don't spend the effort budget there first.

## 7. Recipes per page type
- **Home**: hero (split on tablet) → one-paragraph "what is it" answer block (also serves AEO) → feature bento/explorer → how it works → proof (only if real) → pricing teaser or primary CTA → blog teaser → CTA band.
- **Features/Product**: sticky in-page nav (segmented) → one section per capability (media + text alternating on tablet+) → comparison table → FAQ → CTA.
- **Pricing**: segmented billing switch → tiers → comparison table → FAQ (visible) → CTA. Prices exactly as existing.
- **Blog index**: featured → grid → categories → pagination with real links (`<a href>`; keep the existing pagination URLs).
- **Blog post**: breadcrumb → h1 → byline/date/reading time → TL;DR → content with h2 sections → FAQ (if the content has it) → author box → related posts → CTA.
- **About**: story, values, team (real people only), contact link.
- **Contact**: form (all existing fields, validation, CSRF, flash messages preserved), alternative channels (WhatsApp, email), address if real.
- **Legal/FAQ**: reading layout with sticky TOC on tablet+; plain and accessible.

## 8. Rules of thumb and failure tests
Fail the build if any is true:
1. The tablet layout is mobile-with-wider-columns, or desktop-with-fewer-columns. *Test: screenshot at 820×1180 and 1180×820; if it looks like a stretched phone or a squeezed desktop, redesign it.*
2. Cards merely turn into a list or a single column at tablet width, or only the column count changes.
3. A route or link existing in the old site is not reachable on mobile.
4. Any text, price, or form is only available on one breakpoint.
5. Horizontal page scroll at any width from 320 to 1440.
6. Content is hidden via `display:none` that no breakpoint ever reveals (SEO risk and dead content).
7. Different DOM copies of the same long content per breakpoint (duplicate content, double screen-reader announcements). Small chrome components (tab bar vs top nav) are the exception, and the hidden one uses `display:none`.
8. Hover-only functionality; tap targets < 44 px; text < 12 px.

## 9. Test matrix
Viewports (CSS px): 360×800, 390×844, 430×932 (phones) · 744×1133, 820×1180, 834×1194 (tablet portrait) · 1024×768, 1180×820, 1366×1024 (tablet landscape) · 1440×900 (desktop). Also a 320 px stress width and a 50% iPad Split View (~320–507 px).
Check each: no horizontal scroll, tab bar clears content and keyboard, safe areas, LCP element visible without layout shift, dark/light (if supported), 200% text zoom, reduced motion, keyboard-only navigation, standalone mode after install. In the final report list the sizes actually checked and how (browser devtools, real device, or "not run").
