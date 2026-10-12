# Anti-Slop: How Not to Look AI-Generated

Contents: 1 Why pages look like AI output · 2 Visual tells and their fixes · 3 Copy tells (Indonesian) · 4 Content honesty rules · 5 Craft details that signal human work · 6 The slop test (run before delivery)

## 1. Why pages look like AI output
Generated pages converge on the average of the web: the same gradient hero, three identical icon cards, vague superlatives, centered everything, decorative blobs, invented numbers. The fix is not "more style", it is **specificity and restraint**: real content, a consistent system, intentional asymmetry, and details that can only come from knowing the actual product.

## 2. Visual tells and their fixes
| Tell | Do instead |
|---|---|
| Purple/blue/pink gradient hero, gradient text, glowing orbs, mesh blobs | Flat brand-derived colors from the existing site; one accent; hierarchy by type scale |
| Glassmorphism on every card | Glass only on bars and sheets; cards use `--surface` with a hairline |
| Three identical feature cards: icon in a tinted circle + title + two lines | Unequal bento tiles or a feature explorer; each with a real UI fragment, not an icon |
| Everything centered, every section the same height | Mix left-aligned split sections, full-bleed bands, and tight/loose rhythm; vary section density on purpose |
| Emoji as icons or bullets (✨🚀⚡) | One consistent inline SVG icon family; most list items need no icon |
| "✨ Baru" pill badges, floating chips, sparkles | Remove; use plain eyebrow text only when it labels something real |
| Heavy drop shadows, colored glows, 3D tilt | Hairline + soft low-opacity shadow only on elevated elements |
| Random radii (6, 8, 10, 16, 20 mixed) | The token radii only (12 / 18–24 / 28–32 / pill) |
| Stock-style illustrations, abstract 3D shapes, isometric clip art | Real product screenshots or faithful HTML/CSS reconstructions of the actual UI in a simple frame |
| Fake dashboards with lorem data or round numbers | Use real labels, fields, and plausible data from the product's own screens; if unsure, show structure with neutral placeholders and flag it |
| Fake logos / "Dipercaya 10.000+ bisnis" / invented testimonials / avatar stacks | Omit the section unless the owner supplied real, permissioned content |
| Arrow pills on every button (`Mulai →`, `Pelajari →`) | One filled primary per view; secondary as a plain text link with `›` |
| Parallax, auto-carousels, typewriter text, cursor trails, looping animations | Quiet reveal and press states only; honor reduced motion |
| Default Tailwind blue-600 / indigo everywhere | The extracted brand accent; if the site has none, ask rather than default |
| Uniform 1fr grids with equal gaps everywhere | Intentional spans, a lead tile, alignment to a baseline grid |
| Dark mode forced with neon accents | Keep the site's existing theme |
| Cramped dense blocks of 4-line paragraphs | Short paragraphs, `text-wrap: pretty`, comfortable measure |
| Inconsistent spacing (13px here, 22px there) | 4-px scale tokens only |

## 3. Copy tells (Indonesian) and how to write instead
**Banned or heavily restricted phrases** unless the product truly earns them and the sentence continues with a concrete proof: *revolusioner, terdepan, solusi inovatif, solusi terbaik, all-in-one (bila tidak literal), tingkatkan bisnis Anda ke level selanjutnya, di era digital ini, seamless, powerful, cutting-edge, game-changer, unlock/unleash, tanpa batas, mengubah cara Anda…, one-stop solution, next-level, supercharge, streamline, empower, elevate*.
- **Keep the existing voice**: keep the site's current formality (Anda vs kamu) and terminology. Edit existing copy for clarity and rhythm; don't rewrite the brand into generic SaaS language.
- **Concrete over abstract**: name the job and the result. Weak: "Kelola bisnis Anda dengan mudah." Better (if true): "Catat penjualan, stok, dan piutang dalam satu layar." Use real nouns from the product: kasir, stok, faktur, outlet, laporan, shift, supplier.
- **Short sentences**, active voice, one idea per sentence; avoid stacked adjectives and the rule-of-three cadence on every line.
- **Natural Indonesian**, not translated English: avoid calques such as "Mengambil bisnis Anda ke level berikutnya" or "Kami berkomitmen untuk menyediakan…" as a default opener.
- **Headlines**: say what it is or what it does, ≤ 8 words where possible. Sublines add one specific detail. CTAs are verbs naming the outcome ("Coba gratis 14 hari" only if that trial exists; "Hubungi kami lewat WhatsApp").
- **No filler sections**: if there is nothing true to say, there is no section.
- Microcopy (errors, empty states, buttons) is specific and kind: say what happened and what to do next.

## 4. Content honesty rules (hard)
1. Never invent statistics, customer names, logos, testimonials, awards, ratings, certifications, client counts, uptime, or integrations.
2. Never claim a feature the site does not already describe. If marketing copy needs a capability, mark it **"perlu konfirmasi pemilik"** in the report instead of writing it.
3. Preserve existing facts exactly (prices, plans, addresses, phone/WhatsApp, legal text, dates).
4. Placeholders that must remain are visibly marked in the code (`<!-- TODO(owner): … -->`) and listed in the final report; never ship plausible-looking fake data as final.
5. Do not fabricate people (team photos, author bios). Use existing assets or neutral initials avatars and flag it.
6. Structured data and visible content must match; no hidden text for engines.

## 5. Craft details that signal human work
- **Typography as design**: tight tracking on large headings, `text-wrap: balance`, tabular numerals for prices/stats, proper typographic quotes and en dashes, non-breaking spaces between numbers and units ("Rp 150.000", "14 hari").
- **Alignment**: optical alignment of icons with text baselines; consistent left edges across sections; media aligned to the same grid as text.
- **Rhythm**: deliberate variation in section height and density; one moment of "air" per page (a big headline with nothing else).
- **System coherence**: every component has hover/focus/active/disabled/loading states; focus rings visible and on-brand.
- **Tactile polish**: hairline borders (1 px at 12% text color), inner highlight on filled buttons only if the brand has it, pressed scale .97, pointer cursor only on interactive elements.
- **Real data shape**: realistic string lengths in mocks, including long Indonesian words; numbers formatted as locale (`1.250.000`).
- **Empty/edge states** designed (no posts, no results, 404, offline).
- **Photography/screenshot treatment** consistent: same radius, same frame, same shadow, same crop logic.
- **Restraint check**: remove one decoration per section; if nothing is missed, it was slop.

## 6. The slop test (run before delivery; ≥ 9 of 10 must pass)
1. Cover the logo: could this page belong to any SaaS? If yes, add product-specific content or visuals.
2. Is there any gradient text, glow, blob, or mesh background not present in the original brand? (fail)
3. Do three or more consecutive sections use the same card pattern? (fail)
4. Any emoji used as icon or decoration? (fail)
5. Any invented stat, logo, testimonial, or claim? (fail, always)
6. Any banned phrase from section 3 without a concrete proof right after it? (fail)
7. Are all radii, spacings, and font sizes from tokens? (spot-check with devtools)
8. Does the tablet layout differ structurally from both mobile and desktop? (fail if just re-flowed)
9. Does each page have exactly one primary action per viewport?
10. Would a designer see intentional hierarchy: one clear focal point per section, consistent alignment, and generous whitespace?

In the delivery report, state the score honestly and list what could not be verified (e.g., "visual review in a real browser not run").
