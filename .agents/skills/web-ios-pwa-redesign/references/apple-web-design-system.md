# Apple-Style Web Design System

Contents: 1 What "Apple design" means on the web · 2 Derive the palette from the existing site · 3 Typography · 4 Spacing & layout · 5 Shape, depth, materials · 6 Components · 7 Motion · 8 Imagery & icons · 9 Tailwind wiring · 10 Dark mode

## 1. What "Apple design" means on the web
Adopt the principles, not a copy of apple.com: **clarity** (one idea per section, type does the heavy lifting), **deference** (UI steps back so content leads), **depth** (subtle layering: translucency, hairlines, soft elevation). Concretely:
- Large, confident, tightly-tracked headlines; short sublines; generous vertical rhythm.
- Restraint: one accent color, neutral surfaces, few borders, almost no decoration.
- Product-led visuals: real UI in device-like frames, not stock art or abstract blobs.
- Consistent system: the same radii, spacing steps, and type styles everywhere.
- Quiet motion that explains hierarchy; nothing that exists only to impress.

## 2. Derive the palette from the existing site (never invent)
The brief says the color character and theme stay as they are. So the palette is **extracted**, then mapped to roles.
1. Run `python scripts/parity_check.py colors <project-root>`; read `tailwind.config.*` (`theme.extend.colors`), the main CSS, and the layout `<style>` blocks.
2. Identify: **accent** (the color on primary buttons/links), **neutrals** (page, alternate section, card, text, secondary text, border), and any **semantic** colors already used (success/danger).
3. Map to tokens in `assets/tokens.css`:
   | Token | Source |
   |---|---|
   | `--accent`, `--accent-press` | existing primary + its existing hover/darker shade (derive a darker shade by mixing with black ≈ 12% only if none exists) |
   | `--bg` / `--bg-alt` / `--surface` | existing page / alternate band / card backgrounds |
   | `--text` / `--text-2` | existing body / muted text, adjusted only if contrast < 4.5:1 |
   | `--line` | derived from `--text` at ~12% alpha |
4. Check contrast: body text ≥ 4.5:1, large text and UI ≥ 3:1. If the brand accent fails as text on white, use it for fills/icons and a darker shade for text links, and say so in the report.
5. Gradients, textures, or illustration styles are kept only if the existing brand already uses them. Do not add new hues, rainbow gradients, or purple/blue AI-default palettes.
6. Show the extracted palette in the final report (hex + role + where it came from) so the user can confirm fidelity.

## 3. Typography
- **Stack**: `-apple-system, BlinkMacSystemFont, "SF Pro Text/Display", "Inter", "Inter Fallback", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif`. Apple devices render SF natively; elsewhere self-hosted Inter (variable woff2, `font-display: swap`, metric-matched fallback in `tokens.css`) keeps the look. Do not host or reference SF files (license is Apple-platform only) and never load fonts from third-party CDNs (privacy, speed, CSP).
- **Scale** (fluid, defined in `tokens.css`): display 40→80 px, h1 32→56, h2 28→44, h3 20→28, body 17, small 13.
- **Rules**: tight tracking on large type (−0.02 to −0.04em), line-height 1.04–1.12 for display, ~1.47 for body; `text-wrap: balance` on headings, `pretty` on paragraphs; reading measure 60–75 characters (`--reading: 680px`); `font-variant-numeric: tabular-nums` for prices and stats.
- Hierarchy through size and weight (400/500/600/700), not through color or all-caps everywhere. Small `eyebrow` labels in accent are allowed once per section at most.
- Indonesian copy tends to run ~15–25% longer than English; test headings at the widest copy.

## 4. Spacing & layout
- 4-px base grid; common steps 4, 8, 12, 16, 20, 24, 32, 48, 64, 96, 128.
- Section padding: 48–72 px (mobile), 72–104 (tablet), 96–160 (desktop) via `.section` (clamp). Alternate `--bg` and `--bg-alt` bands to create rhythm without lines.
- Containers: `.container-x` max 1200 px; reading column 680 px; full-bleed media allowed.
- Grid: 4 columns (mobile), 8 (tablet), 12 (desktop). Prefer CSS grid with named areas for the tablet and desktop presentations of the same content.
- Touch targets ≥ 44×44 px; spacing between adjacent targets ≥ 8 px.

## 5. Shape, depth, materials
- Radii: 12 (controls), 18–24 (cards), 28–32 (hero panels and sheets), full pill for buttons and chips. Use one radius family per component class; do not mix 6, 8, 10, 16, 20 at random.
- Elevation: prefer a 1 px hairline (`--line`) plus a very soft shadow (`--shadow-1`). No heavy drop shadows, no colored glows.
- Translucent bars: `.glass` (backdrop blur 20 px, saturate 180%) for the top bar, tab bar, and sheets only. Not for content cards. Always provide the solid fallback (`@supports not`).
- Optional progressive enhancement: `corner-shape: squircle` where supported; verify support before relying on it.

## 6. Components (spec in one line each)
- **Top bar**: 48 px + safe-area top, glass, hairline bottom; logo left, 4–6 text links (tablet+), one primary pill CTA right. On mobile: compact bar with logo/title, optional single CTA, and the bottom tab bar carries navigation.
- **Hero**: eyebrow (optional) → h1 → one-sentence subline → CTA pair (filled pill + `link-arrow` text link) → real product visual. Left-aligned on tablet/desktop split layouts, centered only on mobile if the visual is below.
- **Section header**: h2 + one short paragraph; max two lines each.
- **Feature row**: media and text side by side (alternating) on tablet+; stacked on mobile with the media first.
- **Bento grid**: 2–3 tile sizes only, unequal on purpose (one 2×2 lead tile), each tile one idea + one visual. Use for tablet and desktop; mobile uses a snap rail or stacked tiles.
- **Stat strip / logo strip / testimonial**: only with real, supplied data (see anti-slop). If none exists, omit the section.
- **Pricing**: tiers as cards with one visually emphasized (not three identical); segmented control for monthly/yearly if both exist; every price in `tabular-nums`; IDR formatting `Rp 150.000`.
- **FAQ**: native `<details><summary>` (works without JS, crawlable, accessible), styled as a grouped list.
- **CTA band**: full-width `--bg-alt` band, h2 + single button.
- **Footer**: sitemap-style multi-column on tablet+ (all existing links kept), collapsible grouped-list accordion on mobile, small legal row.
- **Blog card**: image (fixed aspect ratio), category chip, title (2 lines max), date, reading time; title is the link target with a full-card hit area.
- **Article**: reading column, byline with author and `<time>`, TL;DR box, TOC (sticky on tablet landscape+, disclosure on mobile), related posts, share row, end CTA.
- **Forms**: 16–17 px inputs (prevents iOS zoom), 48–52 px height, 12 px radius, visible labels, inline validation message under the field, primary button full width on mobile.

## 7. Motion
- Easing `cubic-bezier(.32,.72,0,1)`, 200–400 ms. Press: scale .97 / opacity .85. Sheets slide up from the bottom (mobile) or fade-scale (tablet).
- Scroll reveal: opacity + 12 px translateY, once per element, via IntersectionObserver, gated by the `js` class so no-JS and crawlers see everything.
- Cross-document view transitions (`@view-transition { navigation: auto; }`) as enhancement only.
- Respect `prefers-reduced-motion`. No parallax, no auto-playing carousels, no cursor effects, no looping decorative animation.

## 8. Imagery & icons
- Visuals come from the real product: screenshots, or HTML/CSS reconstructions of actual UI. Use a simple device/window frame (rounded rect, hairline, soft shadow). If no assets exist, build lean UI fragments from real labels and fields of the app, and flag that the user should replace them with real captures.
- `<picture>` with AVIF/WebP + fallback, `srcset`/`sizes`, explicit `width`/`height`, `loading="lazy"` except the LCP image (`fetchpriority="high"`, preloaded).
- Icons: one inline SVG family (Lucide/Heroicons-style outline, 1.5–1.75 px stroke), `currentColor`, 24 px grid. SF Symbols cannot be used on the web. No emoji as icons.

## 9. Tailwind wiring (the COOCA stack is Tailwind + Alpine)
Tailwind v3 (`tailwind.config.js`):
```js
theme: { extend: {
  screens: { phone: { max: '767px' }, tablet: { min: '768px', max: '1279px' } }, // md/lg/xl stay as defaults
  colors: { accent: 'var(--accent)', surface: 'var(--surface)', bg: 'var(--bg)', 'bg-alt': 'var(--bg-alt)', ink: 'var(--text)', 'ink-2': 'var(--text-2)' },
  fontFamily: { sans: 'var(--font)', display: 'var(--font-display)' },
  borderRadius: { sm: 'var(--r-sm)', md: 'var(--r-md)', lg: 'var(--r-lg)', xl: 'var(--r-xl)' },
}}
```
Tailwind v4 (CSS):
```css
@import "tailwindcss";
@custom-variant phone (@media (max-width: 767px));
@custom-variant tablet (@media (min-width: 768px) and (max-width: 1279px));
@theme { --color-accent: var(--accent); --color-surface: var(--surface); --font-sans: var(--font); }
```
Notes: opacity modifiers like `bg-accent/10` work with CSS-variable colors in v4 but not in v3 (use `.btn-quiet` from `tokens.css` or `color-mix`). Shared hosting without Node: build assets locally and deploy `public/build`; do not require `npm` on the server. Keep Alpine for interactions already present; do not add a framework.

## 10. Dark mode
Only if the existing site already ships a dark theme. Then define the same tokens under `@media (prefers-color-scheme: dark)` (or the existing `.dark` class strategy), use elevated surfaces lighter than the base, and keep the accent legible. If the site is light-only, keep it light-only, structure tokens so dark can be added later, and mention it as an option rather than adding it unasked.
