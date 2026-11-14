# Progress: Worker M2

Last visited: 2026-11-14T01:05:00Z
Status: IN_PROGRESS
Phase: Implementation

## Task Checklist
- [ ] Part 1: Standalone Marketing Pages
  - [ ] `resources/views/public/business_landing.blade.php` (Play CDN, TypeError, scripts, sticky H2, media dims, hamburger touch)
  - [ ] Heading hierarchy skips (`about`, `privacy`, `terms`, `pricing`, `demo`, `security`, `sitemap`, `support`, `landing`)
  - [ ] SEO Canonicals & Schema.org JSON-LD (`pricing`, `privacy`, `terms`, `sitemap`)
  - [ ] Theme Contrast & Touch Targets (`pricing`, `layouts/public_marketing.blade.php`, `sitemap`, `privacy`, `terms`)
- [ ] Part 2: Commercial Landings & Discovery
  - [ ] `kasir_android.blade.php` (Specs card H2, feature cards H3)
  - [ ] `kasir_offline.blade.php` (Architecture bento H2)
  - [ ] `kasir_gratis.blade.php` (Table cell contrast)
  - [ ] `discovery/index.blade.php` (H1 SEO, empty state H2, merchant banner H2, logo dims, touch targets, contrast, breadcrumb JSON-LD)
  - [ ] `lang/id/public_discovery.php` (Line 47 'Toko Tidak Ditemukan')
- [ ] Part 3: Industry Solutions Views
  - [ ] `show.blade.php` (DOM orphan tags, parent heading H2, cross-links H2/H3, bottom CTA H2, JSON-LD hardening)
  - [ ] 6 Industry views (`fnb`, `laundry`, `manufacturing`, `retail`, `services`, `workshop`):
    - [ ] Bottom CTA H2
    - [ ] Flow step headings H3
    - [ ] Mobile grid 1-col on <=390px
    - [ ] Feature icon contrast
    - [ ] Cross-link headings H2 / cards H3
    - [ ] FAQ summary 44px touch targets
    - [ ] SoftwareApplication JSON-LD attributes (url, brand, inLanguage)
    - [ ] Decorative Lucide `aria-hidden="true"`
    - [ ] Truncation guards in `fnb.blade.php`
- [ ] Part 4: Mandatory Verification
  - [ ] PHP syntax check (`php -l`) on all modified views and PHP files
  - [ ] `npm run build`
  - [ ] Automated tests: `PublicMarketingFrontendE2ETest.php`, `PublicBusinessDiscoveryTest.php`
  - [ ] DOM tag balance check on all 7 solutions views
  - [ ] Compile comprehensive `handoff.md`
