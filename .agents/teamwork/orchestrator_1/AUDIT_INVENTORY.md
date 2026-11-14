# COOCA Public Marketing Frontend Audit Inventory

| # | Scoped Path / File | Milestone | Audit Status | Identified Issues | Implementation Status | Notes |
|---|-------------------|-----------|--------------|-------------------|-----------------------|-------|
| 1 | `resources/views/layouts/public_marketing.blade.php` | M1 | AUDITED | Play CDN used, defer missing on app-alert.js, mobile theme switch hidden, hreflang query leak | IMPLEMENTED | Vite integrated, defer added, mobile theme switch active |
| 2 | `resources/views/layouts/partials/help_chatbot_widget.blade.php` | M1 | AUDITED | Heavy modal DOM (>340 lines) mounted eagerly, synchronous init | IMPLEMENTED | Deferred with `<template x-if="isMounted \|\| isOpen">` |
| 3 | `resources/views/layouts/partials/typography.blade.php` | M1 | AUDITED | Verified font styling & scale | AUDITED_OK | Clean typography scale |
| 4 | `resources/views/public/partials/live_chat_widget.blade.php` | M1 | AUDITED | 748px mobile height trap, initial 3500ms intervals | IMPLEMENTED | Responsive bottom sheet (`bottom: 0`, `max-h-[85vh]`), deferred DOM, idle polling removed |
| 5 | `resources/views/public/partials/cookie_consent_banner.blade.php` | M1 | AUDITED_AND_OPTIMIZED | Sub-44px touch targets; initial bottom-20 dock collision | IMPLEMENTED | Elevated to `bottom-28` (112px clearance), `min-h-[44px]` touch targets, dialog semantics, 8 viewports verified with 0px collision |
| 6 | `resources/views/public/partials/tracking_pixels.blade.php` | M1 | AUDITED | Injected tracking scripts | AUDITED_OK | Deferred analytics scripts |
| 7 | `resources/views/public/partials/seo.blade.php` | M1 | AUDITED | Canonical, OpenGraph, Twitter cards | AUDITED_OK | Clean metadata partial |
| 8 | `resources/views/public/partials/subpage_layout.blade.php` | M1 | AUDITED | Missing translation keys `common.home`, `common.details` | QUEUED_M4 | Missing translation keys tracked for M4 |
| 9 | `resources/views/public/blog/show.blade.php` | M1 | AUDITED | Redundant 236 lines of inline CSS in `@push('late-styles')` | IMPLEMENTED | Removed redundant inline CSS; 12KB saved |
| 10 | `resources/views/landing.blade.php` | M2 | AUDITED | Hero LCP image sizing, heading hierarchy, theme contrast | QUEUED_M2 | Planned in M2 |
| 11 | `resources/views/public/business_landing.blade.php` | M2 | AUDITED | Play CDN on line 149, `__('storefront.tracking')` array TypeError at line 8624, touch targets | QUEUED_M2 | Planned in M2 |
| 12 | `resources/views/public/about.blade.php` | M2 | AUDITED | Missing schema JSON-LD, heading hierarchy skip | QUEUED_M2 | Planned in M2 |
| 13 | `resources/views/public/demo.blade.php` | M2 | AUDITED | Form touch targets, validation accessibility | QUEUED_M2 | Planned in M2 |
| 14 | `resources/views/public/pricing.blade.php` | M2 | AUDITED | Currency formatting, theme contrast on badges, missing canonical | QUEUED_M2 | Planned in M2 |
| 15 | `resources/views/public/privacy.blade.php` | M2 | AUDITED | Heading hierarchy, legal prose typography, missing canonical | QUEUED_M2 | Planned in M2 |
| 16 | `resources/views/public/security.blade.php` | M2 | AUDITED | Missing canonical, schema markup | QUEUED_M2 | Planned in M2 |
| 17 | `resources/views/public/sitemap.blade.php` | M2 | AUDITED | Missing canonical, list semantics | QUEUED_M2 | Planned in M2 |
| 18 | `resources/views/public/support.blade.php` | M2 | AUDITED | Contact channels, FAQ accordion | QUEUED_M2 | Planned in M2 |
| 19 | `resources/views/public/terms.blade.php` | M2 | AUDITED | Heading hierarchy, typography scale | QUEUED_M2 | Planned in M2 |
| 20 | `resources/views/public/landing_commercial/kasir_android.blade.php` | M2 | AUDITED | Heading skip (H1->H3), image dimensions, play store badge | QUEUED_M2 | Planned in M2 |
| 21 | `resources/views/public/landing_commercial/kasir_gratis.blade.php` | M2 | AUDITED | Heading hierarchy, LCP image | QUEUED_M2 | Planned in M2 |
| 22 | `resources/views/public/landing_commercial/kasir_offline.blade.php` | M2 | AUDITED | Heading skip, image dimensions | QUEUED_M2 | Planned in M2 |
| 23 | `resources/views/public/discovery/index.blade.php` | M2 | AUDITED | Grid responsive layout, search filter | QUEUED_M2 | Planned in M2 |
| 24 | `resources/views/public/solutions/fnb.blade.php` | M2 | AUDITED | Industry solution template, bottom CTA hierarchy | QUEUED_M2 | Planned in M2 |
| 25 | `resources/views/public/solutions/laundry.blade.php` | M2 | AUDITED | Industry solution template, bottom CTA hierarchy | QUEUED_M2 | Planned in M2 |
| 26 | `resources/views/public/solutions/manufacturing.blade.php` | M2 | AUDITED | Industry solution template, bottom CTA hierarchy | QUEUED_M2 | Planned in M2 |
| 27 | `resources/views/public/solutions/retail.blade.php` | M2 | AUDITED | Industry solution template, bottom CTA hierarchy | QUEUED_M2 | Planned in M2 |
| 28 | `resources/views/public/solutions/services.blade.php` | M2 | AUDITED | Industry solution template, bottom CTA hierarchy | QUEUED_M2 | Planned in M2 |
| 29 | `resources/views/public/solutions/show.blade.php` | M2 | AUDITED | Generic solution router, meta tags | QUEUED_M2 | Planned in M2 |
| 30 | `resources/views/public/solutions/workshop.blade.php` | M2 | AUDITED | Industry solution template, bottom CTA hierarchy | QUEUED_M2 | Planned in M2 |
| 31 | `resources/views/public/blog/` (Index, Category, Articles) | M3 | AUDITED | Card image dimensions, pagination accessibility, 111 articles | QUEUED_M3 | Planned in M3 |
| 32 | `resources/views/public/business-operating-system/` (3 views) | M3 | AUDITED | Interactive BOS diagram, canonical tags, responsive bento | QUEUED_M3 | Planned in M3 |
| 33 | `resources/views/public/calculators/` (Index + 9 calculators) | M3 | AUDITED | Redundant lucide icons scan on Alpine init, input accessibility | QUEUED_M3 | Planned in M3 |
| 34 | `resources/views/public/contact/index.blade.php` | M3 | AUDITED | Form touch targets, validation styling, map embed CLS | QUEUED_M3 | Planned in M3 |
| 35 | `resources/views/public/content-automation/` (3 views) | M3 | AUDITED | Feature showcases, plan comparison cards | QUEUED_M3 | Planned in M3 |
| 36 | `resources/views/public/marketplace/` (4 views) | M3 | AUDITED | Product cards image dimensions, missing breadcrumb keys | QUEUED_M3 | Planned in M3 |
| 37 | `resources/views/public/omnichannel/` (2 views) | M3 | AUDITED | Channel architecture diagram, responsive table | QUEUED_M3 | Planned in M3 |
| 38 | `resources/views/public/omnichannel-erp/` (3 views) | M3 | AUDITED | 48 missing translation keys in `public_erp.php`, badge contrast | QUEUED_M3 | Planned in M3 |
| 39 | `resources/views/public/resources/` (3 views) | M3 | AUDITED | Download cards, guide layout | QUEUED_M3 | Planned in M3 |
| 40 | `resources/views/public/templates/` (3 views) | M3 | AUDITED | Template gallery cards, modal preview | QUEUED_M3 | Planned in M3 |
| 41 | `lang/id/` & `lang/en/` (10 translation modules) | M4 | AUDITED | 50 missing translation keys, hardcoded strings in marketing views | QUEUED_M4 | Planned in M4 |
| 42 | Full Website Audit Report & Production Verification | M5 | PENDING | Comprehensive audit findings report, Lighthouse benchmarks, E2E test verification | QUEUED_M5 | Planned in M5 |
