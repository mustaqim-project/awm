# SEO · GEO · AIO · AEO Playbook (per page)

Contents: 1 What each term means and what is actually controllable · 2 Page brief (fill before designing each page) · 3 Technical SEO checklist · 4 Content structure for answer engines (AEO) · 5 Generative engines (GEO) · 6 Google AI Overviews / AI Mode (AIO) · 7 Structured data templates · 8 AI crawler policy · 9 Site-level files · 10 Recipes per page type · 11 Indonesia specifics · 12 Verification and honest reporting

## 1. Terms, and what you can actually do
| Term | Target | What you control |
|---|---|---|
| **SEO** | Classic search ranking (Google, Bing) | Crawlability, indexability, relevance, page experience, links, structured data |
| **AEO** (answer engine optimization) | Featured snippets, voice answers, "people also ask", assistants quoting a direct answer | Answer-first writing, question headings, concise definitions, lists/tables, visible FAQ |
| **GEO** (generative engine optimization) | Being retrieved and cited in answers from ChatGPT search, Perplexity, Claude, Gemini, Copilot | Entity clarity, citable and specific facts, structured chunking, freshness, crawler access, server-rendered content |
| **AIO** (AI Overviews / AI Mode in Google) | Appearing as a source in Google's AI-generated results | Normal Google SEO fundamentals, snippet eligibility, content that answers the question and its likely follow-ups |

Be honest in every report: **nobody can guarantee rankings or AI citations.** Google states that no special markup or "AI file" is needed for AI Overviews beyond standard SEO and helpful content. Academic work on generative engines suggests that specific, sourced, well-structured content gets cited more than keyword-stuffed content, but behavior varies by engine and changes quickly. The work here is to remove obstacles and make pages the clearest, most citable source on their topic, using only true statements.

## 2. Page brief (fill one per public page before designing it)
```
Route / URL (unchanged):
Page type: home | product | pricing | blog-index | blog-post | about | contact | legal | faq
Primary query + intent (Indonesian, as people actually search):
Secondary queries / questions (3-8):
Title (25-60 chars, includes primary term + brand, unique):
Meta description (70-160 chars, benefit + specifics, no clickbait):
H1 (one, matches intent, not identical to title unless natural):
Answer block (40-60 words directly under the H1 or first H2 answering the primary question):
Sections as H2/H3 (phrased as questions or clear nouns, each self-contained 120-300 words):
Visible FAQ (3-6 real questions, from sales/support/Search Console, never invented to fill space):
Entities mentioned (brand, product, people, places, standards):
Facts with sources (only verifiable; each with a visible citation or first-party basis):
Schema types (must match visible content):
Internal links in (from) / out (to), with descriptive anchor text:
Primary image (LCP) + alt text + OG image:
Freshness: datePublished / dateModified (real dates):
```
Derive content from the existing page copy and the existing product reality. If a required fact does not exist (a price, a statistic, a client), do not write it; flag it as "needs input" in the report.

## 3. Technical SEO checklist (every page)
**Crawl & index**
- Server-rendered HTML contains the primary content (Blade does this). Many AI crawlers and some search crawlers do not execute JavaScript, so never load main content via XHR or inject it with Alpine/JS. Content inside `x-show` or `<details>` is fine because it is in the HTML.
- 200 status, one canonical URL per page, self-referencing `rel="canonical"` (absolute, HTTPS, no query), consistent trailing-slash and www policy, redirects only where they already exist (never change existing URLs; if a URL must change, 301 it and report).
- `meta robots` default `index,follow,max-image-preview:large,max-snippet:-1`; never `nosnippet`/`max-snippet:0` on pages you want in AI answers. `noindex` only for thin utility pages (offline, thank-you, internal search, filtered duplicates).
- Pagination and category pages: crawlable `<a href>` links, self-canonical per page (do not canonicalize page 2 to page 1), unique titles with "Halaman 2" style suffix.
- Parameterized/filtered URLs: canonical to the base or `noindex,follow`; keep the filters working.
- Mobile parity: same content, same structured data, same meta on mobile and desktop.

**On-page**
- Exactly one `<h1>`; logical `h2`→`h3` order; landmarks (`header`, `nav`, `main`, `footer`, `article`, `aside`); `lang="id"`.
- Title, description, OG/Twitter tags from `partials/seo-head.blade.php`; unique per page; OG image 1200×630.
- Internal links: descriptive anchor text, no "klik di sini"; each important page linked from nav, footer, and 2+ relevant pages; breadcrumbs on inner pages.
- Images: descriptive file names, meaningful `alt` (empty `alt=""` only for decorative), explicit `width`/`height`, AVIF/WebP, `srcset`; LCP image `fetchpriority="high"` + preload, others `loading="lazy"`.
- Links: no `href="#"`/`javascript:` for navigation; every icon-only link has `aria-label`.

**Performance (Core Web Vitals at p75: LCP ≤ 2.5 s, INP ≤ 200 ms, CLS ≤ 0.1)**
- Self-host fonts with `font-display: swap` + metric-matched fallback; preload only the primary weight.
- Minimal JS (Alpine only), `defer`; no layout-shifting embeds; reserve space for images/iframes; no render-blocking third-party scripts.
- Compress and cache static assets (long `Cache-Control` on hashed files), enable gzip/brotli (Apache `mod_deflate`/`mod_brotli`), HTTP/2 if the host allows.

## 4. Content structure for answer engines (AEO)
- **Answer first**: under the H1, a 40–60-word direct answer to the main query ("X adalah …"), then detail.
- **Question headings**: H2/H3 phrased like real queries ("Berapa biaya …?", "Apa bedanya … dan …?") when natural; avoid forcing every heading into a question.
- **Scannable formats**: definitions, numbered steps, bullet lists, comparison tables with real column headers, short paragraphs (2–4 sentences). These are what snippets and assistants lift.
- **TL;DR / Ringkasan** box at the top of articles (3–5 bullets).
- **Visible FAQ** at the bottom of relevant pages (native `<details>`), each answer 30–80 words, written for humans first.
- **Self-contained passages**: each section makes sense if extracted alone (no "seperti disebutkan di atas"; name the subject explicitly).
- **Freshness cues** where honest: "Diperbarui: 8 Oktober 2026" with `<time datetime>`; only change `dateModified` when content truly changes.

## 5. Generative engines (GEO)
- **Entity clarity**: one canonical brand name, one-sentence description reused consistently on Home, About, footer, Organization schema, and social profiles; `sameAs` links to real official profiles only. A visible About page and contact details (company name, address or service area, email/WhatsApp).
- **Citable specifics**: concrete numbers, thresholds, steps, comparisons, named standards or regulations, with a visible source or first-party basis. Replace vague claims ("solusi terbaik") with checkable ones ("stok tersinkron antar outlet tiap transaksi", if true).
- **Original value**: first-party data, screenshots, process explanations, benchmarks, templates, glossaries; things an assistant cannot get from every competitor.
- **Authorship / E-E-A-T**: named authors with short bios and a link to an author page, reviewer lines for sensitive topics, publication and update dates, references.
- **Chunkable structure**: descriptive headings, short sections, lists/tables, consistent terminology (don't alternate three names for one feature).
- **Access**: allow search and citation crawlers (section 8), keep content in server-rendered HTML, fast responses, no login or interstitial walls on public content.
- **Presence beyond the site** (report as recommendations, not site changes): consistent profiles (Google Business Profile if there is a physical location, LinkedIn, YouTube), third-party mentions, reviews on neutral platforms, Bing Webmaster Tools verification (Bing's index feeds several assistants).
- **Do not**: stuff keywords, hide text, generate bulk thin pages, fabricate stats/quotes/reviews, or cloak content for AI bots (different content for bots than users violates search policies).

## 6. Google AI Overviews / AI Mode (AIO)
- Eligibility = indexed and snippet-eligible. Check Search Console for indexing and no `nosnippet`.
- Cover the question **and its likely follow-ups** on the page (the system issues related sub-queries): add sections for comparison, cost, how-to, limitations, alternatives, and local/regulatory context where relevant.
- Keep structured data consistent with visible content; it helps understanding but is not a ranking switch.
- Strong page experience and clear authorship matter as in normal search.
- Measure in Search Console (queries, impressions, clicks); AI feature traffic is mixed into Web reporting, so track trends, not attribution.

## 7. Structured data templates (JSON-LD, server-rendered, valid and visible-content-matched)
Rules: mark up only what is visible on the page; no fabricated ratings, reviews, prices, or counts; absolute URLs; ISO 8601 dates with offset (`2026-10-08T09:00:00+07:00`); `inLanguage: "id-ID"`; one `@graph` per page is fine. Escape safely: `json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG)`.

Laravel pattern (in the view, using existing model fields):
```blade
@push('schema')
<script type="application/ld+json">{!! json_encode([
  '@context' => 'https://schema.org',
  '@graph'   => [ /* nodes below */ ],
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG) !!}</script>
@endpush
```

**Home** — `Organization` + `WebSite` (+ `SoftwareApplication` if the product is software):
```json
[
  { "@type": "Organization", "@id": "https://example.id/#org", "name": "BRAND", "url": "https://example.id/",
    "logo": { "@type": "ImageObject", "url": "https://example.id/icons/icon-512.png" },
    "sameAs": ["REAL_PROFILE_URLS_ONLY"],
    "contactPoint": [{ "@type": "ContactPoint", "contactType": "customer support", "availableLanguage": "id", "telephone": "+62...", "url": "https://wa.me/62..." }],
    "areaServed": { "@type": "Country", "name": "Indonesia" } },
  { "@type": "WebSite", "@id": "https://example.id/#website", "url": "https://example.id/", "name": "BRAND", "inLanguage": "id-ID", "publisher": { "@id": "https://example.id/#org" } },
  { "@type": "SoftwareApplication", "name": "BRAND", "applicationCategory": "BusinessApplication", "operatingSystem": "Web",
    "description": "FROM_VISIBLE_COPY", "inLanguage": "id-ID", "provider": { "@id": "https://example.id/#org" } }
]
```
Add `offers` (`price`, `priceCurrency: "IDR"`) only when real prices are visible. Add `potentialAction` SearchAction on `WebSite` only if a site search exists. No `aggregateRating` without real, visible, verifiable reviews.

**Inner pages** — `WebPage` (or `AboutPage`/`ContactPage`/`CollectionPage`) + `BreadcrumbList`:
```json
[
  { "@type": "WebPage", "@id": "URL#webpage", "url": "URL", "name": "TITLE", "isPartOf": { "@id": "https://example.id/#website" }, "inLanguage": "id-ID", "dateModified": "ISO" },
  { "@type": "BreadcrumbList", "itemListElement": [
      { "@type": "ListItem", "position": 1, "name": "Beranda", "item": "https://example.id/" },
      { "@type": "ListItem", "position": 2, "name": "Blog", "item": "https://example.id/blog" },
      { "@type": "ListItem", "position": 3, "name": "TITLE" } ] }
]
```

**Blog post** — `BlogPosting`:
```json
{ "@type": "BlogPosting", "@id": "URL#article", "mainEntityOfPage": { "@type": "WebPage", "@id": "URL" },
  "headline": "≤110 chars", "description": "META_DESCRIPTION", "inLanguage": "id-ID",
  "image": ["https://…/16x9.jpg", "https://…/4x3.jpg", "https://…/1x1.jpg"],
  "datePublished": "ISO", "dateModified": "ISO",
  "author": { "@type": "Person", "name": "AUTHOR", "url": "https://example.id/penulis/slug" },
  "publisher": { "@id": "https://example.id/#org" }, "articleSection": "CATEGORY", "wordCount": 0 }
```
If the existing model has no author field, use the Organization as author and report the gap.

**FAQ** — `FAQPage` only when the same Q&A is visible on the page. Note: Google restricts FAQ rich results to a small set of sites, so expect no rich result for most sites; the visible FAQ and matching markup still clarify structure for engines and assistants.
```json
{ "@type": "FAQPage", "mainEntity": [
  { "@type": "Question", "name": "VISIBLE_QUESTION", "acceptedAnswer": { "@type": "Answer", "text": "VISIBLE_ANSWER" } } ] }
```

**How-to content**: write it as visible numbered steps; `HowTo` markup no longer yields Google rich results, so it is optional.

**Blog index** — `CollectionPage`/`Blog` with `hasPart` optional; the post list itself must be crawlable links.

## 8. AI crawler policy (`public/robots.txt`)
Crawlers differ by purpose; decide per purpose, and ask the owner about training:
| Purpose | Tokens | Default recommendation |
|---|---|---|
| Search / citation (appear in answers) | `OAI-SearchBot`, `Claude-SearchBot`, `PerplexityBot`, `Googlebot`, `Bingbot` | **Allow** |
| User-initiated fetch (a person asked the assistant to read your page) | `ChatGPT-User`, `Claude-User`, `Perplexity-User` | **Allow** |
| Model training | `GPTBot`, `ClaudeBot`, `Google-Extended`, `Applebot-Extended`, `CCBot` | **Owner decision** (ask; default to leaving unchanged) |
Notes: `Google-Extended` governs use for Gemini training/grounding and does not affect Search ranking or AI Overviews; blocking `Googlebot` removes you from Google entirely. Robots rules are voluntary; they are a signal, not security. Never block `/sw.js`, `/manifest.webmanifest`, CSS/JS needed for rendering.
```
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /login
# (existing private paths from the current robots.txt stay)

Sitemap: https://example.id/sitemap.xml
```
`llms.txt`: optional, low-cost curated index of key pages in Markdown at `/llms.txt`. No major engine has confirmed using it for ranking or citation, so present it as a harmless extra, never as a requirement.

## 9. Site-level files
- `robots.txt`: static in `public/`; keep every existing rule, add `Sitemap:`.
- `sitemap.xml`: canonical, indexable, 200 URLs only; accurate `lastmod` (from `updated_at`, not "now"); split by type if > 50,000 URLs. For a database-driven blog, either a scheduled artisan command that writes `public/sitemap.xml` (works with cron on shared hosting) or one **additive** route returning XML. Adding a route is a change to `routes/`; do it only with explicit approval and report it. Existing routes are never removed.
- Submit the sitemap in Google Search Console and Bing Webmaster Tools; IndexNow is optional for faster Bing/Yandex discovery.
- Custom 404 with helpful links and a real 404 status; no soft 404s.
- `humans.txt`/`security.txt`: optional.

## 10. Recipes per page type
- **Home**: title = brand + category + audience ("BRAND — Software ERP untuk UMKM di Indonesia" pattern, using the real category words); H1 states what it is and for whom; answer block "Apa itu BRAND?"; schema Organization + WebSite (+ SoftwareApplication); internal links to every main section; FAQ of 4–6 real questions.
- **Features / Product**: one H2 per capability phrased around the job to be done; each with a concrete example from real functionality; comparison table; links to related blog posts; SoftwareApplication feature list only from visible text.
- **Pricing**: prices in text (not images); billing terms, what's included, taxes (PPN) if real; FAQ on payment/cancellation; `Offer` only with real prices.
- **Blog index**: unique title/description per page of pagination; featured + list as crawlable links; category pages with intro paragraph; `CollectionPage`.
- **Blog post**: answer-first intro, TL;DR, TOC with `id` anchors, sources cited visibly, author + dates, related posts, `BlogPosting` + `BreadcrumbList` (+ `FAQPage` if visible FAQ), share links as real `<a>`, comments preserved.
- **About**: entity page; who/what/where/since when, team (real), contact; `AboutPage`.
- **Contact**: `ContactPage`; NAP consistent with the footer and Organization schema; WhatsApp link as `https://wa.me/62…`; form preserved exactly.
- **Legal (privacy, terms)**: plain content, clear dates, `noindex` only if the owner asks.
- **FAQ**: grouped by topic, each Q as an H2/H3 with the answer directly below; `FAQPage`.

## 11. Indonesia specifics
- `lang="id"`, `og:locale="id_ID"`, `inLanguage: "id-ID"`, `priceCurrency: "IDR"` (`Rp 1.500.000` formatting in text), dates in Indonesian with `<time datetime>` ISO.
- Write queries the way Indonesian users type them (mixing formal and colloquial: "aplikasi kasir", "software ERP", "harga", "cara", "terbaik", "gratis"), but only where the copy can honestly satisfy the intent.
- Mobile-first audience: speed on mid-range Android and slower networks matters; compress aggressively.
- WhatsApp as a primary contact channel: a tap-to-chat link with a prefilled message; keep an email or form alternative.
- Google dominates search; Bing still feeds several AI assistants, so verify there too.

## 12. Verification and honest reporting
1. `python scripts/seo_audit.py <urls…>` against the running site (local `php artisan serve` is fine); fix every ✖ and review every ⚠. It checks lang, title/description length, canonical, robots, viewport, H1, heading order, landmarks, content depth, OG/Twitter, image alt/dimensions, dead links, JSON-LD validity and required properties, FAQ text match, manifest, `robots.txt` (incl. AI-bot rules), and sitemap.
2. View source (not DevTools DOM) of key pages: content present, one canonical, JSON-LD present, no `noindex` by accident.
3. Rich Results Test and Schema Markup Validator on Home, a blog post, and a FAQ page.
4. PageSpeed Insights / Lighthouse (mobile) for LCP, INP, CLS.
5. After launch (owner tasks): Search Console and Bing Webmaster Tools coverage; track referrals from chatgpt.com, perplexity.ai, gemini.google.com, copilot.microsoft.com; spot-check target questions in assistants monthly and log whether the brand is cited.
6. In the report state: pages optimized, items needing owner input (real FAQs, stats, author data, logos, prices), what was verified by tools versus assumed, and that outcomes are not guaranteed.
