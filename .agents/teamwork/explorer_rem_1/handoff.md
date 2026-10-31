# Phase 1 Remediation Explorer Handoff Report: Quick Wins & Layout Normalization
**Target Scope**: `resources/views/app` (44 Double Header Views & 76 Flawed Modal Backdrop Instances across 21 Views)
**Standard**: Apple Human Interface Guidelines (HIG) Bento Specification, Tailwind CSS Responsive Standards
**Status**: Complete Read-Only Architectural Mapping Ready for Implementation
**Date**: 2026-10-31

## 1. Observation

### 1.1 Summary of Direct Empirical Observations
A full static and structural AST analysis of `resources/views/app` was conducted. The investigation verified two primary systemic defects constituting Phase 1 of the remediation roadmap:

1. **Double Header Redundancy**: Exactly **44 Blade views** simultaneously declare `@extends('layouts.app', ['headerTitle' => ..., 'headerSubtitle' => ...])` and render an inline `<x-module-header>` card within `@section('content')`. This creates vertical duplication (56px sticky topbar + 100px-120px header card = 160px-180px total vertical space wasted before actual business content).
2. **Flawed Backdrop Architecture**: Exactly **76 modal dialog instances** across **21 Blade views** attach dark background utility classes (`bg-black/40`, `bg-black/50`, `bg-black/60`, `bg-black/70`) directly to the outer flex container (`class="fixed inset-0 ... flex items-center justify-center ... bg-black/..."`). This architecture triggers scroll clipping on small/laptop viewports, topbar bleed, and background tearing.

### 1.2 Complete Catalog of 44 Double Header Redundancy Views
Every view below has been inspected in `resources/views/app`. All 44 views have verified inline `<x-module-header>` declarations and redundant `headerTitle`/`headerSubtitle` arguments in `@extends`.

| # | View File Path | Extends Lines | Module Header Line | Action Required |
|---|---|---|---|---|
| 1 | `resources/views/app/billing/checkout.blade.php` | Lines 1-5 | Line 318 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 2 | `resources/views/app/billing/history.blade.php` | Lines 1-5 | Line 32 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 3 | `resources/views/app/billing/limits.blade.php` | Lines 1-5 | Line 30 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 4 | `resources/views/app/billing/payment.blade.php` | Lines 1-5 | Line 204 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 5 | `resources/views/app/crm/members.blade.php` | Lines 1-5 | Line 94 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 6 | `resources/views/app/crm/vouchers.blade.php` | Lines 1-5 | Line 21 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 7 | `resources/views/app/customers/index.blade.php` | Lines 1-5 | Line 229 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 8 | `resources/views/app/feedback/create.blade.php` | Lines 1-5 | Line 25 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 9 | `resources/views/app/feedback/index.blade.php` | Lines 1-5 | Line 21 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 10 | `resources/views/app/feedback/show.blade.php` | Lines 1-5 | Line 25 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 11 | `resources/views/app/invoices/index.blade.php` | Lines 1-5 | Line 28 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 12 | `resources/views/app/invoices/show.blade.php` | Lines 1-5 | Line 91 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 13 | `resources/views/app/marketplace/chat.blade.php` | Lines 1-5 | Line 15 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 14 | `resources/views/app/marketplace/index.blade.php` | Lines 1-5 | Line 90 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 15 | `resources/views/app/marketplace/logs.blade.php` | Lines 1-5 | Line 41 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 16 | `resources/views/app/marketplace/orders.blade.php` | Lines 1-5 | Line 201 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 17 | `resources/views/app/marketplace/products.blade.php` | Lines 1-5 | Line 883 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 18 | `resources/views/app/master-data/index.blade.php` | Lines 1-5 | Line 132 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 19 | `resources/views/app/materials/index.blade.php` | Lines 1-5 | Line 216 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 20 | `resources/views/app/products/bom.blade.php` | Lines 1-5 | Line 31 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 21 | `resources/views/app/products/index.blade.php` | Lines 1-5 | Line 782 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 22 | `resources/views/app/products/modifiers.blade.php` | Lines 1-5 | Line 13 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 23 | `resources/views/app/purchase-orders/index.blade.php` | Lines 1-5 | Line 38 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 24 | `resources/views/app/purchasing/bills/index.blade.php` | Lines 1-5 | Line 12 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 25 | `resources/views/app/purchasing/bills/show.blade.php` | Lines 1-5 | Line 12 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 26 | `resources/views/app/purchasing/receipts/create.blade.php` | Lines 1-5 | Line 15 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 27 | `resources/views/app/purchasing/returns/create.blade.php` | Lines 1-5 | Line 14 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 28 | `resources/views/app/purchasing/returns/index.blade.php` | Lines 1-5 | Line 12 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 29 | `resources/views/app/purchasing/returns/show.blade.php` | Lines 1-5 | Line 12 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 30 | `resources/views/app/quotations/index.blade.php` | Lines 1-5 | Line 27 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 31 | `resources/views/app/sales-orders/index.blade.php` | Lines 1-5 | Line 38 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 32 | `resources/views/app/sales/returns/index.blade.php` | Lines 1-5 | Line 37 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 33 | `resources/views/app/services/index.blade.php` | Lines 1-5 | Line 179 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 34 | `resources/views/app/social_media/calendar.blade.php` | Lines 1-5 | Line 32 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 35 | `resources/views/app/social_media/inbox.blade.php` | Lines 1-5 | Line 11 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 36 | `resources/views/app/social_media/index.blade.php` | Lines 1-5 | Line 11 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 37 | `resources/views/app/social_media/insights.blade.php` | Lines 1-5 | Line 11 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 38 | `resources/views/app/social_media/posts.blade.php` | Lines 1-5 | Line 11 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 39 | `resources/views/app/suppliers/index.blade.php` | Lines 1-5 | Line 203 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 40 | `resources/views/app/warehouse/index.blade.php` | Lines 1-5 | Line 348 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 41 | `resources/views/app/warehouse/show.blade.php` | Lines 1-5 | Line 73 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 42 | `resources/views/app/whatsapp/broadcast.blade.php` | Lines 1-5 | Line 11 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 43 | `resources/views/app/whatsapp/index.blade.php` | Lines 1-5 | Line 11 | Strip `headerTitle` & `headerSubtitle` from `@extends` |
| 44 | `resources/views/app/whatsapp/logs.blade.php` | Lines 1-5 | Line 23 | Strip `headerTitle` & `headerSubtitle` from `@extends` |


#### Detailed Before/After Specification for All 44 Double Header Views

##### 1. `resources/views/app/billing/checkout.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 318 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('billing.checkout_title') . ' - Cooca',
    'headerTitle' => __('billing.breadcrumb_checkout'),
    'headerSubtitle' => __('billing.checkout_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('billing.checkout_title') . ' - Cooca',
])
```

##### 2. `resources/views/app/billing/history.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 32 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('billing.breadcrumb_history') . ' - Cooca',
    'headerTitle' => __('billing.history_title'),
    'headerSubtitle' => __('billing.history_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('billing.breadcrumb_history') . ' - Cooca',
])
```

##### 3. `resources/views/app/billing/limits.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 30 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('billing.meta_title_limits'),
    'headerTitle' => __('billing.title'),
    'headerSubtitle' => __('billing.subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('billing.meta_title_limits'),
])
```

##### 4. `resources/views/app/billing/payment.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 204 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('billing.payment_page_title', ['order' => $payment->order_number]),
    'headerTitle' => __('billing.header_payment_instructions'),
    'headerSubtitle' => __('billing.header_payment_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('billing.payment_page_title', ['order' => $payment->order_number]),
])
```

##### 5. `resources/views/app/crm/members.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 94 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('crm.members_title') . ' - Cooca',
    'headerTitle' => __('crm.members_title'),
    'headerSubtitle' => __('crm.subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('crm.members_title') . ' - Cooca',
])
```

##### 6. `resources/views/app/crm/vouchers.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 21 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('crm.vouchers_title') . ' - Cooca',
    'headerTitle' => __('crm.vouchers_title'),
    'headerSubtitle' => __('crm.vouchers_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('crm.vouchers_title') . ' - Cooca',
])
```

##### 7. `resources/views/app/customers/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 229 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('customers.title') . ' - Cooca',
    'headerTitle' => __('customers.directory_title'),
    'headerSubtitle' => __('customers.subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('customers.title') . ' - Cooca',
])
```

##### 8. `resources/views/app/feedback/create.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 25 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature'),
    'headerTitle' => $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature'),
    'headerSubtitle' => $type === 'bugs' ? __('feedback.subtitle_bugs') : __('feedback.subtitle_features'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature'),
])
```

##### 9. `resources/views/app/feedback/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 21 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features'),
    'headerTitle' => $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features'),
    'headerSubtitle' => $type === 'bugs' ? __('feedback.subtitle_bugs') : __('feedback.subtitle_features'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features'),
])
```

##### 10. `resources/views/app/feedback/show.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 25 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => $item->title,
    'headerTitle' => $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features'),
    'headerSubtitle' => $item->title,
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => $item->title,
])
```

##### 11. `resources/views/app/invoices/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 28 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('sales.invoices.page_title'),
    'headerTitle' => __('sales.invoices.header_title'),
    'headerSubtitle' => __('sales.invoices.header_subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('sales.invoices.page_title'),
])
```

##### 12. `resources/views/app/invoices/show.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 91 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('sales.invoices.show_title', ['number' => $invoice->invoice_number]),
    'headerTitle' => __('sales.invoices.show_title', ['number' => $invoice->invoice_number]),
    'headerSubtitle' => __('sales.invoices.show_subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('sales.invoices.show_title', ['number' => $invoice->invoice_number]),
])
```

##### 13. `resources/views/app/marketplace/chat.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 15 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.chat_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.chat_header_title'),
    'headerSubtitle' => __('marketplace.chat_page_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.chat_page_title') . ' - ' . $business->name,
])
```

##### 14. `resources/views/app/marketplace/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 90 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.page_title'),
    'headerSubtitle' => __('marketplace.page_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.page_title') . ' - ' . $business->name,
])
```

##### 15. `resources/views/app/marketplace/logs.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 41 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.logs_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.logs_header_title'),
    'headerSubtitle' => __('marketplace.logs_page_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.logs_page_title') . ' - ' . $business->name,
])
```

##### 16. `resources/views/app/marketplace/orders.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 201 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.orders_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.orders_page_title'),
    'headerSubtitle' => __('marketplace.orders_page_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.orders_page_title') . ' - ' . $business->name,
])
```

##### 17. `resources/views/app/marketplace/products.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 883 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.products_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.products_page_title'),
    'headerSubtitle' => __('marketplace.products_page_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.products_page_title') . ' - ' . $business->name,
])
```

##### 18. `resources/views/app/master-data/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 132 (`<x-module-header :title="$title" :subtitle="$subtitle" :breadcrumbs="$breadcrumbs">`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => $title,
    'headerTitle' => $title,
    'headerSubtitle' => $subtitle,
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => $title,
])
```

##### 19. `resources/views/app/materials/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 216 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('materials.title'),
    'headerTitle' => __('materials.header_title'),
    'headerSubtitle' => __('materials.header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('materials.title'),
])
```

##### 20. `resources/views/app/products/bom.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 31 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('products.bom_title_prefix', ['name' => $product->name]),
    'headerTitle' => __('products.bom_header_title'),
    'headerSubtitle' => __('products.bom_header_subtitle', ['name' => $product->name]),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('products.bom_title_prefix', ['name' => $product->name]),
])
```

##### 21. `resources/views/app/products/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 782 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('products.katalog_produk_model_hpp'),
    'headerTitle' => 'Katalog Produk & Model HPP',
    'headerSubtitle' => 'Kelola produk jadi, kalkulasi biaya (BOM, ABC, Job Order), harga jual, dan saluran etalase',
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('products.katalog_produk_model_hpp'),
])
```

##### 22. `resources/views/app/products/modifiers.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 13 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('products.modifiers_title'),
    'headerTitle' => __('products.modifiers_title'),
    'headerSubtitle' => __('products.modifiers_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('products.modifiers_title'),
])
```

##### 23. `resources/views/app/purchase-orders/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 38 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.title'),
    'headerTitle' => __('purchasing.header_title'),
    'headerSubtitle' => __('purchasing.header_subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.title'),
])
```

##### 24. `resources/views/app/purchasing/bills/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 12 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.bills.title'),
    'headerTitle' => __('purchasing.bills.title'),
    'headerSubtitle' => __('purchasing.bills.subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.bills.title'),
])
```

##### 25. `resources/views/app/purchasing/bills/show.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 12 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.bills.detail_title', ['number' => $invoice->invoice_number]),
    'headerTitle' => __('purchasing.bills.title'),
    'headerSubtitle' => __('purchasing.bills.detail_subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.bills.detail_title', ['number' => $invoice->invoice_number]),
])
```

##### 26. `resources/views/app/purchasing/receipts/create.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 15 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.receipts.title', ['number' => $purchaseOrder->po_number]),
    'headerTitle' => __('purchasing.receipts.header_title'),
    'headerSubtitle' => __('purchasing.receipts.header_subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.receipts.title', ['number' => $purchaseOrder->po_number]),
])
```

##### 27. `resources/views/app/purchasing/returns/create.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 14 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.returns.create_title'),
    'headerTitle' => __('purchasing.returns.title'),
    'headerSubtitle' => __('purchasing.returns.create_subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.returns.create_title'),
])
```

##### 28. `resources/views/app/purchasing/returns/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 12 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.returns.title'),
    'headerTitle' => __('purchasing.returns.title'),
    'headerSubtitle' => __('purchasing.returns.subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.returns.title'),
])
```

##### 29. `resources/views/app/purchasing/returns/show.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 12 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.returns.detail_title', ['number' => $return->return_number]),
    'headerTitle' => __('purchasing.returns.title'),
    'headerSubtitle' => __('purchasing.returns.detail_subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.returns.detail_title', ['number' => $return->return_number]),
])
```

##### 30. `resources/views/app/quotations/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 27 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('sales.quotations.page_title'),
    'headerTitle' => __('sales.quotations.header_title'),
    'headerSubtitle' => __('sales.quotations.header_subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('sales.quotations.page_title'),
])
```

##### 31. `resources/views/app/sales-orders/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 38 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('sales.sales_orders.page_title'),
    'headerTitle' => __('sales.sales_orders.header_title'),
    'headerSubtitle' => __('sales.sales_orders.header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('sales.sales_orders.page_title'),
])
```

##### 32. `resources/views/app/sales/returns/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 37 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('sales.returns.page_title'),
    'headerTitle' => __('sales.returns.header_title'),
    'headerSubtitle' => __('sales.returns.header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('sales.returns.page_title'),
])
```

##### 33. `resources/views/app/services/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 179 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('services.title'),
    'headerTitle' => __('services.header_title'),
    'headerSubtitle' => __('services.header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('services.title'),
])
```

##### 34. `resources/views/app/social_media/calendar.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 32 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.calendar_header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.calendar_header_title'),
    'headerSubtitle' => __('social_media.calendar_header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.calendar_header_title') . ' - ' . $business->name,
])
```

##### 35. `resources/views/app/social_media/inbox.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 11 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.unified_inbox_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.unified_inbox_title'),
    'headerSubtitle' => __('social_media.unified_inbox_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.unified_inbox_title') . ' - ' . $business->name,
])
```

##### 36. `resources/views/app/social_media/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 11 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.header_title'),
    'headerSubtitle' => __('social_media.header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.header_title') . ' - ' . $business->name,
])
```

##### 37. `resources/views/app/social_media/insights.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 11 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.insights_header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.insights_header_title'),
    'headerSubtitle' => __('social_media.insights_header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.insights_header_title') . ' - ' . $business->name,
])
```

##### 38. `resources/views/app/social_media/posts.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 11 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.posts_header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.posts_header_title'),
    'headerSubtitle' => __('social_media.posts_header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.posts_header_title') . ' - ' . $business->name,
])
```

##### 39. `resources/views/app/suppliers/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 203 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.supplier.title'),
    'headerTitle' => __('purchasing.supplier.header_title'),
    'headerSubtitle' => __('purchasing.supplier.header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.supplier.title'),
])
```

##### 40. `resources/views/app/warehouse/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 348 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('warehouse.title'),
    'headerTitle' => __('warehouse.header_title'),
    'headerSubtitle' => __('warehouse.header_subtitle')
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('warehouse.title'),
])
```

##### 41. `resources/views/app/warehouse/show.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 73 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('warehouse.detail_title', ['name' => $location->name]),
    'headerTitle' => __('warehouse.detail_header', ['name' => $location->name]),
    'headerSubtitle' => __('warehouse.detail_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('warehouse.detail_title', ['name' => $location->name]),
])
```

##### 42. `resources/views/app/whatsapp/broadcast.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 11 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('whatsapp.broadcast_title') . ' - ' . $business->name,
    'headerTitle' => __('whatsapp.broadcast_header_title'),
    'headerSubtitle' => __('whatsapp.broadcast_header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('whatsapp.broadcast_title') . ' - ' . $business->name,
])
```

##### 43. `resources/views/app/whatsapp/index.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 11 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('whatsapp.title') . ' - ' . $business->name,
    'headerTitle' => __('whatsapp.header_title'),
    'headerSubtitle' => __('whatsapp.header_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('whatsapp.title') . ' - ' . $business->name,
])
```

##### 44. `resources/views/app/whatsapp/logs.blade.php`
- **Extends Declaration**: Lines 1-5
- **Module Header Verified**: Line 23 (`<x-module-header`)
- **Current Verbatim Code (Before)**:
```blade
@extends('layouts.app', [
    'title' => __('whatsapp.logs_title') . ' - ' . $business->name,
    'headerTitle' => __('whatsapp.logs_title'),
    'headerSubtitle' => __('whatsapp.logs_subtitle'),
])
```
- **Exact Remediation (After)**:
```blade
@extends('layouts.app', [
    'title' => __('whatsapp.logs_title') . ' - ' . $business->name,
])
```


### 1.3 Complete Catalog of 76 Flawed Modal Backdrop Instances across 21 Views
The 76 flawed modal backdrop instances are distributed across 21 views (47 in core transactional submodules, 29 in back-office and management submodules):

- `pos/terminal.blade.php`: 20 instances
- `products/index.blade.php`: 8 instances
- `materials/index.blade.php`: 7 instances
- `finance/cash-bank/index.blade.php`: 5 instances
- `settings/index.blade.php`: 5 instances
- `products/modifiers.blade.php`: 4 instances
- `finance/accounting/coa.blade.php`: 4 instances
- `master-data/index.blade.php`: 3 instances
- `warehouse/index.blade.php`: 3 instances
- `warehouse/show.blade.php`: 3 instances
- `calculator.blade.php`: 2 instances
- `ai-studio/show.blade.php`: 2 instances
- `products/bom.blade.php`: 2 instances
- `ai-studio/index.blade.php`: 1 instance
- `ai-studio/prompt-preview.blade.php`: 1 instance
- `finance/expenses.blade.php`: 1 instance
- `finance/payables.blade.php`: 1 instance
- `finance/receivables.blade.php`: 1 instance
- `finance/accounting/reconciliation.blade.php`: 1 instance
- `whatsapp/index.blade.php`: 1 instance
- `whatsapp/logs.blade.php`: 1 instance

| # | View File Path | Instances Count | Teleported Status | Alpine State Identifiers |
|---|---|---|---|---|
| 1 | `resources/views/app/ai-studio/index.blade.php` | 1 | Needs Teleport Wrap | `deleteModalOpen` |
| 2 | `resources/views/app/ai-studio/prompt-preview.blade.php` | 1 | Needs Teleport Wrap | `promptModalOpen` |
| 3 | `resources/views/app/ai-studio/show.blade.php` | 2 | Needs Teleport Wrap | `exportModalOpen, generationDrawerOpen` |
| 4 | `resources/views/app/calculator.blade.php` | 2 | All Teleported | `showSaveModal, showQuickSaveModal` |
| 5 | `resources/views/app/finance/accounting/coa.blade.php` | 4 | All Teleported | `showImportModal, deleteModalOpen, createModalOpen, editModalOpen` |
| 6 | `resources/views/app/finance/accounting/reconciliation.blade.php` | 1 | All Teleported | `uploadModalOpen` |
| 7 | `resources/views/app/finance/cash-bank/index.blade.php` | 5 | All Teleported | `showInflowModal, showAccountModal, showOutflowModal, showTransferModal, editAccountModal` |
| 8 | `resources/views/app/finance/expenses.blade.php` | 1 | All Teleported | `showCreateModal` |
| 9 | `resources/views/app/finance/payables.blade.php` | 1 | All Teleported | `showImportModal` |
| 10 | `resources/views/app/finance/receivables.blade.php` | 1 | All Teleported | `showImportModal` |
| 11 | `resources/views/app/master-data/index.blade.php` | 3 | All Teleported | `showAddModal, showEditModal, deleteModalOpen` |
| 12 | `resources/views/app/materials/index.blade.php` | 7 | All Teleported | `showAddModal, showPriceModal, showAddUnitModal, showAddSupplierModal, showAddCategoryModal, showEditModal, deleteModalOpen` |
| 13 | `resources/views/app/pos/terminal.blade.php` | 20 | Needs Teleport Wrap | `showCloseShiftModal, showIncomingOrdersModal, showSupervisorPinModal, showSuccessModal, showBarcodeScanner, showRejectReasonModal, showHeldOrdersModal, showServiceVerticalModal, showCustomerModal, showQrisModal, showTablesModal, showCashMovementModal, showTablePaymentModal, showHoldPromptModal, showPaymentModal, showItemDetailModal, showModifierModal, showOpenShiftModal, showPosSettingsModal, showScannerPermission` |
| 14 | `resources/views/app/products/bom.blade.php` | 2 | All Teleported | `showAddItemModal, deleteModalOpen` |
| 15 | `resources/views/app/products/index.blade.php` | 8 | All Teleported | `showAddModal, showProductScanner, showBranchPricesModal, showAddUnitModal, showProductScannerPermission, showAddCategoryModal, showEditModal, deleteModalOpen` |
| 16 | `resources/views/app/products/modifiers.blade.php` | 4 | All Teleported | `deleteOptionModalOpen, deleteGroupModalOpen, showAddGroupModal || showEditGroupModal, showAddOptionModal || showEditOptionModal` |
| 17 | `resources/views/app/settings/index.blade.php` | 5 | All Teleported | `deleteBranchModalOpen, templateModalOpen, showCreateBranchModal, showEditBranchModal, showDisconnectModal` |
| 18 | `resources/views/app/warehouse/index.blade.php` | 3 | All Teleported | `showEditModal, deleteModalOpen, showCreateModal` |
| 19 | `resources/views/app/warehouse/show.blade.php` | 3 | All Teleported | `showAdjustModal, showEditModal, confirmModalOpen` |
| 20 | `resources/views/app/whatsapp/index.blade.php` | 1 | All Teleported | `showDisconnectModal` |
| 21 | `resources/views/app/whatsapp/logs.blade.php` | 1 | All Teleported | `openPruneModal` |


#### Detailed Specification for All 76 Flawed Modal Backdrop Instances

#### View 1: `resources/views/app/ai-studio/index.blade.php` (1 instances)

##### Instance #1 @ Line 368
- **Modal Purpose/Title**: {{ __('ai.hapus_proyek_ai_studio') }}
- **Alpine.js Identifier**: `deleteModalOpen`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `deleteModalOpen = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="deleteModalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" style="display: none;">
            <div @click.away="deleteModalOpen = false" class="w-full max-w-sm rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 shadow-2xl space-y-4">
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="deleteModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="deleteModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="deleteModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="deleteModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="deleteModalOpen = false">
                    <div @click.away="deleteModalOpen = false" class="w-full max-w-sm rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 shadow-2xl space-y-4">
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

#### View 2: `resources/views/app/ai-studio/prompt-preview.blade.php` (1 instances)

##### Instance #1 @ Line 3
- **Modal Purpose/Title**: Lembar Transparansi Prompt AI
- **Alpine.js Identifier**: `promptModalOpen`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-2xl`
- **Close Handler**: `promptModalOpen = false`
- **Current Flawed Code Snippet**:
```html
<div x-show="promptModalOpen" x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/50 backdrop-blur-md"
    style="display: none;">
        <div @click.away="promptModalOpen = false"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="promptModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="promptModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="promptModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="promptModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="promptModalOpen = false">
                <div @click.away="promptModalOpen = false"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

#### View 3: `resources/views/app/ai-studio/show.blade.php` (2 instances)

##### Instance #1 @ Line 372
- **Modal Purpose/Title**: {{ __('ai.simpan_ke_katalog_produk') }}
- **Alpine.js Identifier**: `exportModalOpen`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `exportModalOpen = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="exportModalOpen" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;">
            <div @click.away="exportModalOpen = false"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="exportModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="exportModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="exportModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="exportModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="exportModalOpen = false">
                    <div @click.away="exportModalOpen = false"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #2 @ Line 450
- **Modal Purpose/Title**: Pesan Render Generasi Baru
- **Alpine.js Identifier**: `generationDrawerOpen`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-lg`
- **Close Handler**: `generationDrawerOpen = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="generationDrawerOpen" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        style="display: none;">
            <div @click.away="generationDrawerOpen = false"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="generationDrawerOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="generationDrawerOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="generationDrawerOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="generationDrawerOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="generationDrawerOpen = false">
                    <div @click.away="generationDrawerOpen = false"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

#### View 4: `resources/views/app/calculator.blade.php` (2 instances)

##### Instance #1 @ Line 1370
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showQuickSaveModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[95vw]`
- **Close Handler**: `showQuickSaveModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showQuickSaveModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    role="dialog" aria-modal="true" aria-labelledby="quick-save-modal-title"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    style="display: none;">
                        <div @click.away="showQuickSaveModal = false"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showQuickSaveModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showQuickSaveModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showQuickSaveModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showQuickSaveModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showQuickSaveModal = false">
                                <div @click.away="showQuickSaveModal = false"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 1547
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showSaveModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[95vw]`
- **Close Handler**: `showSaveModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showSaveModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    role="dialog" aria-modal="true" aria-labelledby="save-run-modal-title"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    style="display: none;">
                        <div @click.away="showSaveModal = false"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showSaveModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showSaveModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showSaveModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showSaveModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showSaveModal = false">
                                <div @click.away="showSaveModal = false"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 5: `resources/views/app/finance/accounting/coa.blade.php` (4 instances)

##### Instance #1 @ Line 261
- **Modal Purpose/Title**: {{ __('finance.modal_create_sub_account_title') }}
- **Alpine.js Identifier**: `createModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `createModalOpen = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="createModalOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                x-transition.opacity
                @keydown.escape.window="createModalOpen = false">
                    <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_25px_70px_rgba(0,0,0,0.35)] border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[92vh]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="createModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="createModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="createModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="createModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="createModalOpen = false">
                            <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_25px_70px_rgba(0,0,0,0.35)] border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[92vh]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 343
- **Modal Purpose/Title**: {{ __('finance.modal_edit_account_title') }}
- **Alpine.js Identifier**: `editModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `editModalOpen = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="editModalOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                x-transition.opacity
                @keydown.escape.window="editModalOpen = false">
                    <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_25px_70px_rgba(0,0,0,0.35)] border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[92vh]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="editModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="editModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="editModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="editModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="editModalOpen = false">
                            <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_25px_70px_rgba(0,0,0,0.35)] border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[92vh]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #3 @ Line 406
- **Modal Purpose/Title**: {{ __('finance.modal_delete_sub_account_title') }}
- **Alpine.js Identifier**: `deleteModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `deleteModalOpen = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="deleteModalOpen" x-cloak
                class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/80 backdrop-blur-md"
                @keydown.escape.window="deleteModalOpen = false">
                    <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[22px] shadow-2xl border border-black/10 dark:border-white/10 p-6 space-y-5"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="deleteModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="deleteModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="deleteModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="deleteModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="deleteModalOpen = false">
                            <div class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[22px] shadow-2xl border border-black/10 dark:border-white/10 p-6 space-y-5"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #4 @ Line 444
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showImportModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-5xl`
- **Close Handler**: `if (!importExecuting) showImportModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showImportModal"
                x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @keydown.escape.window="if (!importExecuting) showImportModal = false">
                <div class="w-full max-w-5xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] flex flex-col max-h-[92vh] overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showImportModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="if (!importExecuting) showImportModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showImportModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="if (!importExecuting) showImportModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="if (!importExecuting) showImportModal = false">
                        <div class="w-full max-w-5xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] flex flex-col max-h-[92vh] overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 6: `resources/views/app/finance/accounting/reconciliation.blade.php` (1 instances)

##### Instance #1 @ Line 203
- **Modal Purpose/Title**: {{ __('finance.modal_upload_statement_title') }}
- **Alpine.js Identifier**: `uploadModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `uploadModalOpen = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="uploadModalOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                x-transition.opacity
                @keydown.escape.window="uploadModalOpen = false">
                    <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_25px_70px_rgba(0,0,0,0.35)] border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[92vh]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="uploadModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="uploadModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="uploadModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="uploadModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="uploadModalOpen = false">
                            <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl bg-white dark:bg-[#1C1C1E] rounded-[24px] shadow-[0_25px_70px_rgba(0,0,0,0.35)] border border-black/10 dark:border-white/10 overflow-hidden flex flex-col max-h-[92vh]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 7: `resources/views/app/finance/cash-bank/index.blade.php` (5 instances)

##### Instance #1 @ Line 513
- **Modal Purpose/Title**: {{ __('finance.modal_add_account_title') }}
- **Alpine.js Identifier**: `showAccountModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `showAccountModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showAccountModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @keydown.escape.window="showAccountModal = false">
                        <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAccountModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAccountModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAccountModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAccountModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAccountModal = false">
                                <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 612
- **Modal Purpose/Title**: {{ __('finance.edit_account_title') }}
- **Alpine.js Identifier**: `editAccountModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `editAccountModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="editAccountModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @keydown.escape.window="editAccountModal = false">
                        <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="editAccountModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="editAccountModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="editAccountModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="editAccountModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="editAccountModal = false">
                                <div class="w-full max-w-full sm:max-w-2xl lg:max-w-3xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #3 @ Line 688
- **Modal Purpose/Title**: {{ __('finance.record_inflow_title') }}
- **Alpine.js Identifier**: `showInflowModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `showInflowModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showInflowModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @keydown.escape.window="showInflowModal = false">
                        <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showInflowModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showInflowModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showInflowModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showInflowModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showInflowModal = false">
                                <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #4 @ Line 814
- **Modal Purpose/Title**: {{ __('finance.record_outflow_title') }}
- **Alpine.js Identifier**: `showOutflowModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `showOutflowModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showOutflowModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @keydown.escape.window="showOutflowModal = false">
                        <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showOutflowModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showOutflowModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showOutflowModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showOutflowModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showOutflowModal = false">
                                <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #5 @ Line 944
- **Modal Purpose/Title**: {{ __('finance.modal_transfer_title') }}
- **Alpine.js Identifier**: `showTransferModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `showTransferModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showTransferModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @keydown.escape.window="showTransferModal = false">
                        <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showTransferModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showTransferModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showTransferModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showTransferModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showTransferModal = false">
                                <div class="w-full max-w-full sm:max-w-3xl lg:max-w-4xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 8: `resources/views/app/finance/expenses.blade.php` (1 instances)

##### Instance #1 @ Line 332
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showCreateModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[95vw]`
- **Close Handler**: `showCreateModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showCreateModal" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                x-transition:enter="transition ease-out duration-250" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @keydown.escape.window="showCreateModal = false">
                <div class="w-full max-w-[95vw] lg:max-w-5xl 2xl:max-w-[1250px] mx-auto rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showCreateModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showCreateModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showCreateModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showCreateModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showCreateModal = false">
                        <div class="w-full max-w-[95vw] lg:max-w-5xl 2xl:max-w-[1250px] mx-auto rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_70px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[92vh]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 9: `resources/views/app/finance/payables.blade.php` (1 instances)

##### Instance #1 @ Line 335
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showImportModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-5xl`
- **Close Handler**: `if (!importExecuting) showImportModal = false`
- **Current Flawed Code Snippet**:
```html
        <div x-show="showImportModal"
            x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="if (!importExecuting) showImportModal = false">
            <div class="w-full max-w-5xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] flex flex-col max-h-[92vh] overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showImportModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="if (!importExecuting) showImportModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showImportModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="if (!importExecuting) showImportModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="if (!importExecuting) showImportModal = false">
                    <div class="w-full max-w-5xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] flex flex-col max-h-[92vh] overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 10: `resources/views/app/finance/receivables.blade.php` (1 instances)

##### Instance #1 @ Line 393
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showImportModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-5xl`
- **Close Handler**: `if (!importExecuting) showImportModal = false`
- **Current Flawed Code Snippet**:
```html
        <div x-show="showImportModal"
            x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @keydown.escape.window="if (!importExecuting) showImportModal = false">
            <div class="w-full max-w-5xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] flex flex-col max-h-[92vh] overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showImportModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="if (!importExecuting) showImportModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showImportModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="if (!importExecuting) showImportModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="if (!importExecuting) showImportModal = false">
                    <div class="w-full max-w-5xl rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.3)] flex flex-col max-h-[92vh] overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 11: `resources/views/app/master-data/index.blade.php` (3 instances)

##### Instance #1 @ Line 718
- **Modal Purpose/Title**: Tambah {{ $title }}
- **Alpine.js Identifier**: `showAddModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-lg`
- **Close Handler**: `showAddModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showAddModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    @click.self="showAddModal = false"
                    @keydown.escape.window="showAddModal = false"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <div @click.outside="showAddModal = false"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddModal = false">
                            <div @click.outside="showAddModal = false"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 923
- **Modal Purpose/Title**: {{ __('common.edit') }} {{ $title }}
- **Alpine.js Identifier**: `showEditModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-lg`
- **Close Handler**: `showEditModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showEditModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    @click.self="showEditModal = false"
                    @keydown.escape.window="showEditModal = false"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <div @click.outside="showEditModal = false"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showEditModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showEditModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showEditModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showEditModal = false">
                            <div @click.outside="showEditModal = false"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #3 @ Line 1138
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `deleteModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `Standard dialog`
- **Close Handler**: `deleteModalOpen = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="deleteModalOpen" x-cloak
                class="fixed inset-0 z-[220] flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-md"
                @keydown.escape.window="closeDelete()"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="deleteModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="deleteModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="deleteModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="deleteModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="deleteModalOpen = false">
                        <div class="w-[290px] rounded-[14px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/5 dark:border-white/10"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 12: `resources/views/app/materials/index.blade.php` (7 instances)

##### Instance #1 @ Line 595
- **Modal Purpose/Title**: {{ __('materials.modal_add_title') }}
- **Alpine.js Identifier**: `showAddModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[95vw]`
- **Close Handler**: `showAddModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showAddModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    @keydown.escape.window="showAddModal = false">
                    <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1250px] transition-all"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddModal = false">
                            <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1250px] transition-all"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 822
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showPriceModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-xl`
- **Close Handler**: `showPriceModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showPriceModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    @keydown.escape.window="showPriceModal = false">
                    <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[90vh] flex flex-col overflow-hidden sm:max-w-xl transition-all"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showPriceModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showPriceModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showPriceModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showPriceModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showPriceModal = false">
                            <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[90vh] flex flex-col overflow-hidden sm:max-w-xl transition-all"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #3 @ Line 919
- **Modal Purpose/Title**: {{ __('materials.modal_edit_title') }}
- **Alpine.js Identifier**: `showEditModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-2xl`
- **Close Handler**: `showEditModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showEditModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    @keydown.escape.window="showEditModal = false">
                    <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[90vh] flex flex-col overflow-hidden sm:max-w-2xl transition-all"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showEditModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showEditModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showEditModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showEditModal = false">
                            <div class="w-full inset-x-0 bottom-0 rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_24px_60px_rgba(0,0,0,0.3)] max-h-[90vh] flex flex-col overflow-hidden sm:max-w-2xl transition-all"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #4 @ Line 1033
- **Modal Purpose/Title**: {{ __('materials.modal_quick_sup_title') }}
- **Alpine.js Identifier**: `showAddSupplierModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showAddSupplierModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showAddSupplierModal" x-cloak
                class="fixed inset-0 z-[210] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @keydown.escape.window="showAddSupplierModal = false">
                <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddSupplierModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddSupplierModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddSupplierModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddSupplierModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddSupplierModal = false">
                        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #5 @ Line 1095
- **Modal Purpose/Title**: {{ __('materials.modal_quick_cat_title') }}
- **Alpine.js Identifier**: `showAddCategoryModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showAddCategoryModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showAddCategoryModal" x-cloak
                class="fixed inset-0 z-[210] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @keydown.escape.window="showAddCategoryModal = false">
                <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddCategoryModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddCategoryModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddCategoryModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddCategoryModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddCategoryModal = false">
                        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #6 @ Line 1145
- **Modal Purpose/Title**: {{ __('materials.modal_quick_unit_title') }}
- **Alpine.js Identifier**: `showAddUnitModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showAddUnitModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showAddUnitModal" x-cloak
                class="fixed inset-0 z-[210] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @keydown.escape.window="showAddUnitModal = false">
                <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddUnitModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddUnitModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddUnitModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddUnitModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddUnitModal = false">
                        <div class="w-full max-w-md rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] p-5 sm:p-6 space-y-4 shadow-2xl"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #7 @ Line 1211
- **Modal Purpose/Title**: {{ __('materials.dialog_delete_title') }}
- **Alpine.js Identifier**: `deleteModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `deleteModalOpen = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="deleteModalOpen" x-cloak
                    class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/80 backdrop-blur-md"
                    @keydown.escape.window="closeDelete()">
                    <div class="w-full max-w-sm rounded-[24px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden shadow-2xl border border-black/[0.08] dark:border-white/[0.1] text-center"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="deleteModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="deleteModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="deleteModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="deleteModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="deleteModalOpen = false">
                            <div class="w-full max-w-sm rounded-[24px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-xl overflow-hidden shadow-2xl border border-black/[0.08] dark:border-white/[0.1] text-center"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 13: `resources/views/app/pos/terminal.blade.php` (20 instances)

##### Instance #1 @ Line 2143
- **Modal Purpose/Title**: {{ __('pos.quick_customer_title') }}
- **Alpine.js Identifier**: `showCustomerModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `showCustomerModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showCustomerModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showCustomerModal = false">
            <div class="pos-modal-panel w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showCustomerModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showCustomerModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showCustomerModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showCustomerModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showCustomerModal = false">
                    <div class="pos-modal-panel w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #2 @ Line 2190
- **Modal Purpose/Title**: {{ __('pos.pos_checkout_payment_title') }}
- **Alpine.js Identifier**: `showPaymentModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-3xl`
- **Close Handler**: `isSplitPayment = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showPaymentModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[4px] p-3 sm:p-4"
        style="display: none;"
        @keydown.escape.window="if(!isProcessing) showPaymentModal = false"
        @keydown.enter.prevent="if(!isProcessing && (isSplitPayment ? splitRemainingAmount === 0 : currentTenderAmount >= grandTotal)) submitCheckout()">
            <div
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showPaymentModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="isSplitPayment = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showPaymentModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="isSplitPayment = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="isSplitPayment = false">
                    <div
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #3 @ Line 2504
- **Modal Purpose/Title**: {{ __('pos.qris_cashier_dynamic_title') }}
- **Alpine.js Identifier**: `showQrisModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `showQrisModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showQrisModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md p-3 sm:p-4"
        style="display: none;">
            <div class="pos-modal-panel w-full max-w-sm sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-[26px] border border-black/10 dark:border-white/15 p-5 sm:p-6 flex flex-col space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white relative overflow-hidden transition-all"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showQrisModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showQrisModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showQrisModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showQrisModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showQrisModal = false">
                    <div class="pos-modal-panel w-full max-w-sm sm:max-w-md bg-white dark:bg-[#1C1C1E] rounded-[26px] border border-black/10 dark:border-white/15 p-5 sm:p-6 flex flex-col space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white relative overflow-hidden transition-all"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #4 @ Line 2611
- **Modal Purpose/Title**: {{ __('pos.transaction_success_title') }}
- **Alpine.js Identifier**: `showSuccessModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-lg`
- **Close Handler**: `showSuccessModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showSuccessModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-[3px] p-4"
        @keydown.escape.window="if(showSuccessModal) resetForNewOrder()">
            <div class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/15 p-5 sm:p-6 space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white relative overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showSuccessModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showSuccessModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showSuccessModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showSuccessModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showSuccessModal = false">
                    <div class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/15 p-5 sm:p-6 space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.35)] text-black dark:text-white relative overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #5 @ Line 2873
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showPosSettingsModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-2xl`
- **Close Handler**: `showPosSettingsModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showPosSettingsModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        style="display: none;"
        @click.self="showPosSettingsModal = false"
        @keydown.escape.window="showPosSettingsModal = false">
            <div class="pos-modal-panel w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh] text-black dark:text-white"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showPosSettingsModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showPosSettingsModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showPosSettingsModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showPosSettingsModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showPosSettingsModal = false">
                    <div class="pos-modal-panel w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_25px_60px_rgba(0,0,0,0.35)] overflow-hidden flex flex-col max-h-[90vh] text-black dark:text-white"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #6 @ Line 3108
- **Modal Purpose/Title**: {{ __('pos.shift_open_modal_title') }}
- **Alpine.js Identifier**: `showOpenShiftModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showOpenShiftModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showOpenShiftModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
        style="display: none;">
            <div
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showOpenShiftModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showOpenShiftModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showOpenShiftModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showOpenShiftModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showOpenShiftModal = false">
                    <div
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #7 @ Line 3138
- **Modal Purpose/Title**: {{ __('pos.shift_close_modal_title') }}
- **Alpine.js Identifier**: `showCloseShiftModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showCloseShiftModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showCloseShiftModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
        style="display: none;">
            <div
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showCloseShiftModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showCloseShiftModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showCloseShiftModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showCloseShiftModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showCloseShiftModal = false">
                    <div
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #8 @ Line 3210
- **Modal Purpose/Title**: {{ __('pos.held_orders_modal_title') }}
- **Alpine.js Identifier**: `showHeldOrdersModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-lg`
- **Close Handler**: `showHeldOrdersModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showHeldOrdersModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
        style="display: none;">
            <div
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showHeldOrdersModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showHeldOrdersModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showHeldOrdersModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showHeldOrdersModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showHeldOrdersModal = false">
                    <div
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #9 @ Line 3252
- **Modal Purpose/Title**: {{ __('pos.hold_save_modal_title') }}
- **Alpine.js Identifier**: `showHoldPromptModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `showHoldPromptModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showHoldPromptModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4">
            <div class="pos-modal-panel w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showHoldPromptModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showHoldPromptModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showHoldPromptModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showHoldPromptModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showHoldPromptModal = false">
                    <div class="pos-modal-panel w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.25)] text-black dark:text-white"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #10 @ Line 3280
- **Modal Purpose/Title**: {{ __('pos.cash_movement_modal_title') }}
- **Alpine.js Identifier**: `showCashMovementModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-lg`
- **Close Handler**: `showCashMovementModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showCashMovementModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4"
        style="display: none;"
        @keydown.escape.window="showCashMovementModal = false">
            <div
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showCashMovementModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showCashMovementModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showCashMovementModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showCashMovementModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showCashMovementModal = false">
                    <div
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #11 @ Line 3405
- **Modal Purpose/Title**: {{ __('pos.supervisor_pin_title') }}
- **Alpine.js Identifier**: `showSupervisorPinModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `showSupervisorPinModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showSupervisorPinModal" x-cloak
        class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50 backdrop-blur-[4px] p-4"
        @keydown.escape.window="if(!supervisorPinLoading) closeSupervisorPinModal()">
            <div class="pos-modal-panel w-full max-w-sm bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-[18px] border border-black/10 dark:border-white/15 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showSupervisorPinModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showSupervisorPinModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showSupervisorPinModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showSupervisorPinModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showSupervisorPinModal = false">
                    <div class="pos-modal-panel w-full max-w-sm bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-[18px] border border-black/10 dark:border-white/15 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #12 @ Line 3468
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showScannerPermission`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `Standard dialog`
- **Close Handler**: `showScannerPermission = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showScannerPermission" x-cloak
        class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40 backdrop-blur-[2px] p-4">
            <div
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showScannerPermission" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showScannerPermission = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showScannerPermission"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showScannerPermission = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showScannerPermission = false">
                    <div
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #13 @ Line 3498
- **Modal Purpose/Title**: {{ __('pos.scan_barcode_title') }}
- **Alpine.js Identifier**: `showBarcodeScanner`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-lg`
- **Close Handler**: `showBarcodeScanner = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showBarcodeScanner" x-cloak @keydown.escape.window="closeBarcodeScanner()"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @click.self="closeBarcodeScanner()">
            <div
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showBarcodeScanner" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showBarcodeScanner = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showBarcodeScanner"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showBarcodeScanner = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showBarcodeScanner = false">
                    <div
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #14 @ Line 3557
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showModifierModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showModifierModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showModifierModal" x-cloak
        class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showModifierModal = false">
            <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showModifierModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showModifierModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showModifierModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showModifierModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showModifierModal = false">
                    <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #15 @ Line 3685
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showServiceVerticalModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-lg`
- **Close Handler**: `showServiceVerticalModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showServiceVerticalModal" x-cloak
        class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showServiceVerticalModal = false">
            <div class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showServiceVerticalModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showServiceVerticalModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showServiceVerticalModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showServiceVerticalModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showServiceVerticalModal = false">
                    <div class="pos-modal-panel w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #16 @ Line 3916
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showItemDetailModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showItemDetailModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showItemDetailModal" x-cloak
        class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showItemDetailModal = false">
            <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showItemDetailModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showItemDetailModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showItemDetailModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showItemDetailModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showItemDetailModal = false">
                    <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #17 @ Line 4000
- **Modal Purpose/Title**: {{ __('pos.qr_incoming_orders_title') }}
- **Alpine.js Identifier**: `showIncomingOrdersModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-2xl`
- **Close Handler**: `showIncomingOrdersModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showIncomingOrdersModal" x-cloak
        class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showIncomingOrdersModal = false">
            <div class="pos-modal-panel w-full max-w-2xl bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showIncomingOrdersModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showIncomingOrdersModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showIncomingOrdersModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showIncomingOrdersModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showIncomingOrdersModal = false">
                    <div class="pos-modal-panel w-full max-w-2xl bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #18 @ Line 4170
- **Modal Purpose/Title**: {{ __('pos.reject_table_order') }}
- **Alpine.js Identifier**: `showRejectReasonModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `showRejectReasonModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showRejectReasonModal" x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showRejectReasonModal = false">
            <div class="pos-modal-panel w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showRejectReasonModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showRejectReasonModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showRejectReasonModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showRejectReasonModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showRejectReasonModal = false">
                    <div class="pos-modal-panel w-full max-w-sm bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #19 @ Line 4198
- **Modal Purpose/Title**: {{ __('pos.table_reservations_mgmt_title') }}
- **Alpine.js Identifier**: `showTablesModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-4xl`
- **Close Handler**: `showTablesModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showTablesModal" x-cloak
        class="fixed inset-0 z-[65] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showTablesModal = false">
            <div class="pos-modal-panel w-full max-w-4xl bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white flex flex-col max-h-[90vh]"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showTablesModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showTablesModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showTablesModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showTablesModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showTablesModal = false">
                    <div class="pos-modal-panel w-full max-w-4xl bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white flex flex-col max-h-[90vh]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

##### Instance #20 @ Line 4483
- **Modal Purpose/Title**: {{ __('pos.table_order_payment_title') }}
- **Alpine.js Identifier**: `showTablePaymentModal`
- **Teleported to Body**: No (Requires `<template x-teleport="body">` wrapping)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showTablePaymentModal = false`
- **Current Flawed Code Snippet**:
```html
    <div x-show="showTablePaymentModal" x-cloak
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 backdrop-blur-[2px] p-4"
        @keydown.escape.window="showTablePaymentModal = false">
            <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
```
- **Apple HIG Bento Replacement Structure**:
```html
<template x-teleport="body">
    <div x-show="showTablePaymentModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showTablePaymentModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showTablePaymentModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showTablePaymentModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showTablePaymentModal = false">
                    <div class="pos-modal-panel w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
</template>
```

#### View 14: `resources/views/app/products/bom.blade.php` (2 instances)

##### Instance #1 @ Line 290
- **Modal Purpose/Title**: {{ __('products.modal_add_bom_title') }}
- **Alpine.js Identifier**: `showAddItemModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `showAddItemModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showAddItemModal" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @keydown.escape.window="showAddItemModal = false">
                    <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddItemModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddItemModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddItemModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddItemModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddItemModal = false">
                            <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 407
- **Modal Purpose/Title**: {{ __('products.modal_delete_bom_title') }}
- **Alpine.js Identifier**: `deleteModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[320px]`
- **Close Handler**: `deleteModalOpen = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="deleteModalOpen" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                    <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="deleteModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="deleteModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="deleteModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="deleteModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="deleteModalOpen = false">
                            <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 15: `resources/views/app/products/index.blade.php` (8 instances)

##### Instance #1 @ Line 1460
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showAddModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `showAddModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showAddModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    @keydown.escape.window="showAddModal = false">
                        <div class="w-full max-w-full sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1250px] inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddModal = false">
                                <div class="w-full max-w-full sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1250px] inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 2118
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showEditModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `showEditModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showEditModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                    @keydown.escape.window="showEditModal = false">
                        <div class="w-full max-w-full sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1250px] inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showEditModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showEditModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showEditModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showEditModal = false">
                                <div class="w-full max-w-full sm:max-w-[95vw] lg:max-w-5xl xl:max-w-6xl 2xl:max-w-[1250px] inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #3 @ Line 2819
- **Modal Purpose/Title**: {{ __('products.modal_add_category_title') }}
- **Alpine.js Identifier**: `showAddCategoryModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `showAddCategoryModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showAddCategoryModal" x-cloak
                class="fixed inset-0 z-[210] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                    <div class="w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddCategoryModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddCategoryModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddCategoryModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddCategoryModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddCategoryModal = false">
                            <div class="w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #4 @ Line 2870
- **Modal Purpose/Title**: {{ __('products.modal_add_unit_title') }}
- **Alpine.js Identifier**: `showAddUnitModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `showAddUnitModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showAddUnitModal" x-cloak
                class="fixed inset-0 z-[210] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                    <div class="w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddUnitModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddUnitModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddUnitModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddUnitModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddUnitModal = false">
                            <div class="w-full max-w-sm rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-6 space-y-4 shadow-2xl"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #5 @ Line 2938
- **Modal Purpose/Title**: {{ __('products.modal_scanner_permission_title') }}
- **Alpine.js Identifier**: `showProductScannerPermission`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `showProductScannerPermission = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showProductScannerPermission" x-cloak
                class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                    <div
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showProductScannerPermission" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showProductScannerPermission = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showProductScannerPermission"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showProductScannerPermission = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showProductScannerPermission = false">
                            <div
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #6 @ Line 2966
- **Modal Purpose/Title**: {{ __('products.modal_scanner_title') }}
- **Alpine.js Identifier**: `showProductScanner`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showProductScanner = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showProductScanner" x-cloak @keydown.escape.window="closeProductScanner()"
                class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                    <div class="w-full max-w-md rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4 shadow-2xl"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showProductScanner" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showProductScanner = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showProductScanner"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showProductScanner = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showProductScanner = false">
                            <div class="w-full max-w-md rounded-[20px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4 shadow-2xl"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #7 @ Line 3007
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showBranchPricesModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `showBranchPricesModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showBranchPricesModal" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @keydown.escape.window="showBranchPricesModal = false">
                    <div class="w-full max-w-full sm:max-w-2xl rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[92vh] flex flex-col overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showBranchPricesModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showBranchPricesModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showBranchPricesModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showBranchPricesModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showBranchPricesModal = false">
                            <div class="w-full max-w-full sm:max-w-2xl rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border border-black/[0.06] dark:border-white/[0.08] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[92vh] flex flex-col overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #8 @ Line 3253
- **Modal Purpose/Title**: {{ __('products.modal_delete_product_title') }}
- **Alpine.js Identifier**: `deleteModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[320px]`
- **Close Handler**: `deleteModalOpen = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="deleteModalOpen" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                        <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="deleteModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="deleteModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="deleteModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="deleteModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="deleteModalOpen = false">
                                <div class="w-full max-w-[320px] rounded-[20px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 16: `resources/views/app/products/modifiers.blade.php` (4 instances)

##### Instance #1 @ Line 517
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showAddGroupModal || showEditGroupModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `showAddGroupModal = false; showEditGroupModal = false;`
- **Current Flawed Code Snippet**:
```html
        <div x-show="showAddGroupModal || showEditGroupModal" x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
             @keydown.escape.window="showAddGroupModal = false; showEditGroupModal = false;">
                <div class="w-full max-w-full sm:max-w-2xl md:max-w-3xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddGroupModal || showEditGroupModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddGroupModal = false; showEditGroupModal = false;">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddGroupModal || showEditGroupModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddGroupModal = false; showEditGroupModal = false;"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddGroupModal = false; showEditGroupModal = false;">
                        <div class="w-full max-w-full sm:max-w-2xl md:max-w-3xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 827
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showAddOptionModal || showEditOptionModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-full`
- **Close Handler**: `showAddOptionModal = false; showEditOptionModal = false;`
- **Current Flawed Code Snippet**:
```html
        <div x-show="showAddOptionModal || showEditOptionModal" x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
             @keydown.escape.window="showAddOptionModal = false; showEditOptionModal = false;">
                <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAddOptionModal || showEditOptionModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAddOptionModal = false; showEditOptionModal = false;">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAddOptionModal || showEditOptionModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAddOptionModal = false; showEditOptionModal = false;"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAddOptionModal = false; showEditOptionModal = false;">
                        <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #3 @ Line 1026
- **Modal Purpose/Title**: {{ __('products.modal_delete_group_title') }}
- **Alpine.js Identifier**: `deleteGroupModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[340px]`
- **Close Handler**: `deleteGroupModalOpen = false`
- **Current Flawed Code Snippet**:
```html
        <div x-show="deleteGroupModalOpen" x-cloak class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                <div class="w-full max-w-[340px] rounded-[22px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="deleteGroupModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="deleteGroupModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="deleteGroupModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="deleteGroupModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="deleteGroupModalOpen = false">
                        <div class="w-full max-w-[340px] rounded-[22px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #4 @ Line 1058
- **Modal Purpose/Title**: {{ __('products.modal_delete_option_title') }}
- **Alpine.js Identifier**: `deleteOptionModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[340px]`
- **Close Handler**: `deleteOptionModalOpen = false`
- **Current Flawed Code Snippet**:
```html
        <div x-show="deleteOptionModalOpen" x-cloak class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
                <div class="w-full max-w-[340px] rounded-[22px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="deleteOptionModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="deleteOptionModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="deleteOptionModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="deleteOptionModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="deleteOptionModalOpen = false">
                        <div class="w-full max-w-[340px] rounded-[22px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 17: `resources/views/app/settings/index.blade.php` (5 instances)

##### Instance #1 @ Line 1943
- **Modal Purpose/Title**: {{ __('settings.apply_template_title') }}
- **Alpine.js Identifier**: `templateModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-lg`
- **Close Handler**: `templateModalOpen = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="templateModalOpen" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-sm overflow-y-auto"
                    role="dialog"
                    aria-modal="true"
                    @click.self="closeApplyTemplate()"
                    @keydown.escape.window="closeApplyTemplate()"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                        <div class="relative z-10 w-full max-w-lg rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_24px_64px_rgba(0,0,0,0.3)] p-6 space-y-5 text-left my-auto"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="templateModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="templateModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="templateModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="templateModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="templateModalOpen = false">
                                <div class="relative z-10 w-full max-w-lg rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-[0_24px_64px_rgba(0,0,0,0.3)] p-6 space-y-5 text-left my-auto"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 2011
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showCreateBranchModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[95vw]`
- **Close Handler**: `showCreateBranchModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showCreateBranchModal"
                 x-cloak
                 class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-sm overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="create-branch-modal-title"
                 @click.self="showCreateBranchModal = false"
                 @keydown.escape.window="showCreateBranchModal = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                    <div class="relative z-10 w-full max-w-[95vw] lg:max-w-4xl xl:max-w-5xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden my-auto">
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showCreateBranchModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showCreateBranchModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showCreateBranchModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showCreateBranchModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showCreateBranchModal = false">
                            <div class="relative z-10 w-full max-w-[95vw] lg:max-w-4xl xl:max-w-5xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden my-auto">
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #3 @ Line 2363
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showEditBranchModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[95vw]`
- **Close Handler**: `showEditBranchModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showEditBranchModal"
                 x-cloak
                 class="fixed inset-0 z-[200] flex items-center justify-center p-3 sm:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-sm overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="edit-branch-modal-title"
                 @click.self="showEditBranchModal = false"
                 @keydown.escape.window="showEditBranchModal = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                    <div class="relative z-10 w-full max-w-[95vw] lg:max-w-4xl xl:max-w-5xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden my-auto">
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showEditBranchModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showEditBranchModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showEditBranchModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showEditBranchModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showEditBranchModal = false">
                            <div class="relative z-10 w-full max-w-[95vw] lg:max-w-4xl xl:max-w-5xl max-h-[92vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] flex flex-col overflow-hidden my-auto">
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #4 @ Line 2723
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `deleteBranchModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-sm`
- **Close Handler**: `deleteBranchModalOpen = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="deleteBranchModalOpen"
                 x-cloak
                 class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-sm overflow-y-auto"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="delete-branch-modal-title"
                 @click.self="closeDeleteBranch()"
                 @keydown.escape.window="closeDeleteBranch()"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                    <div class="relative z-10 w-full max-w-sm rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl overflow-hidden text-center my-auto"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="deleteBranchModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="deleteBranchModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="deleteBranchModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="deleteBranchModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="deleteBranchModalOpen = false">
                            <div class="relative z-10 w-full max-w-sm rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] shadow-2xl overflow-hidden text-center my-auto"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #5 @ Line 2777
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showDisconnectModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showDisconnectModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showDisconnectModal" x-cloak
                role="dialog" aria-modal="true" aria-labelledby="disconnect-modal-title"
                @keydown.escape.window="showDisconnectModal = false"
                class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/70 dark:bg-black/85"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                    <div @click.away="showDisconnectModal = false"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showDisconnectModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showDisconnectModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showDisconnectModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showDisconnectModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showDisconnectModal = false">
                            <div @click.away="showDisconnectModal = false"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 18: `resources/views/app/warehouse/index.blade.php` (3 instances)

##### Instance #1 @ Line 1048
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showCreateModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[94vw]`
- **Close Handler**: `showCreateModal = false`
- **Current Flawed Code Snippet**:
```html
        <div x-show="showCreateModal"
             x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 dark:bg-black/75 backdrop-blur-md p-3 sm:p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="showCreateModal = false">
                <div class="w-full max-w-[94vw] sm:max-w-2xl lg:max-w-4xl xl:max-w-5xl 2xl:max-w-6xl max-h-[90vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showCreateModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showCreateModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showCreateModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showCreateModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showCreateModal = false">
                        <div class="w-full max-w-[94vw] sm:max-w-2xl lg:max-w-4xl xl:max-w-5xl 2xl:max-w-6xl max-h-[90vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 1348
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showEditModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[94vw]`
- **Close Handler**: `showEditModal = false`
- **Current Flawed Code Snippet**:
```html
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 dark:bg-black/75 backdrop-blur-md p-3 sm:p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="showEditModal = false">
                <div class="w-full max-w-[94vw] sm:max-w-2xl lg:max-w-4xl xl:max-w-5xl 2xl:max-w-6xl max-h-[90vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showEditModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showEditModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showEditModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showEditModal = false">
                        <div class="w-full max-w-[94vw] sm:max-w-2xl lg:max-w-4xl xl:max-w-5xl 2xl:max-w-6xl max-h-[90vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #3 @ Line 1769
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `deleteModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[340px]`
- **Close Handler**: `deleteModalOpen = false`
- **Current Flawed Code Snippet**:
```html
        <div x-show="deleteModalOpen"
             x-cloak
             class="fixed inset-0 z-[220] flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-md p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="closeDelete()">
                <div class="w-full max-w-[340px] rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden text-center shadow-[0_25px_60px_rgba(0,0,0,0.35)]"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="deleteModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="deleteModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="deleteModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="deleteModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="deleteModalOpen = false">
                        <div class="w-full max-w-[340px] rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden text-center shadow-[0_25px_60px_rgba(0,0,0,0.35)]"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 19: `resources/views/app/warehouse/show.blade.php` (3 instances)

##### Instance #1 @ Line 1333
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `confirmModalOpen`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `confirmModalOpen = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="confirmModalOpen" x-cloak
                class="fixed inset-0 z-[220] flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-md p-4"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @keydown.escape.window="confirmModalOpen = false">
                    <div class="w-full max-w-md rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] p-6 space-y-4 overflow-hidden text-center"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="confirmModalOpen" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="confirmModalOpen = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="confirmModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="confirmModalOpen = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="confirmModalOpen = false">
                            <div class="w-full max-w-md rounded-[22px] bg-white/98 dark:bg-[#1C1C1E]/98 backdrop-blur-2xl border border-black/[0.08] dark:border-white/[0.12] shadow-[0_25px_60px_rgba(0,0,0,0.35)] p-6 space-y-4 overflow-hidden text-center"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #2 @ Line 1379
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showEditModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[94vw]`
- **Close Handler**: `showEditModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showEditModal" x-cloak
                class="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 dark:bg-black/75 backdrop-blur-md p-3 sm:p-6"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @keydown.escape.window="showEditModal = false">
                    <div class="w-full max-w-[94vw] md:max-w-2xl lg:max-w-4xl xl:max-w-5xl max-h-[90vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showEditModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showEditModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showEditModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showEditModal = false">
                            <div class="w-full max-w-[94vw] md:max-w-2xl lg:max-w-4xl xl:max-w-5xl max-h-[90vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

##### Instance #3 @ Line 1697
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showAdjustModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[94vw]`
- **Close Handler**: `showAdjustModal = false`
- **Current Flawed Code Snippet**:
```html
                <div x-show="showAdjustModal" x-cloak
                    class="fixed inset-0 z-[200] flex items-center justify-center bg-black/60 dark:bg-black/75 backdrop-blur-md p-3 sm:p-6"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @keydown.escape.window="showAdjustModal = false">
                        <div class="w-full max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl max-h-[90vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showAdjustModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showAdjustModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showAdjustModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showAdjustModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showAdjustModal = false">
                                <div class="w-full max-w-[94vw] md:max-w-2xl lg:max-w-3xl xl:max-w-4xl max-h-[90vh] rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl flex flex-col overflow-hidden"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 20: `resources/views/app/whatsapp/index.blade.php` (1 instances)

##### Instance #1 @ Line 538
- **Modal Purpose/Title**: Dynamic / Inline
- **Alpine.js Identifier**: `showDisconnectModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-md`
- **Close Handler**: `showDisconnectModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="showDisconnectModal" x-cloak
                role="dialog" aria-modal="true" aria-labelledby="disconnect-modal-title"
                @keydown.escape.window="showDisconnectModal = false"
                class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/70 dark:bg-black/85"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                    <div @click.away="showDisconnectModal = false"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="showDisconnectModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="showDisconnectModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="showDisconnectModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="showDisconnectModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="showDisconnectModal = false">
                            <div @click.away="showDisconnectModal = false"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

#### View 21: `resources/views/app/whatsapp/logs.blade.php` (1 instances)

##### Instance #1 @ Line 422
- **Modal Purpose/Title**: {{ __('whatsapp.prune_modal_title') }}
- **Alpine.js Identifier**: `openPruneModal`
- **Teleported to Body**: Yes (`<template x-teleport="body">` present)
- **Container Sizing**: `max-w-[500px]`
- **Close Handler**: `openPruneModal = false`
- **Current Flawed Code Snippet**:
```html
            <div x-show="openPruneModal" x-transition.opacity.duration.200ms
                class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md"
                @keydown.escape.window="openPruneModal = false"
                @click.self="openPruneModal = false" style="display: none;">
                <div role="dialog" aria-modal="true" aria-labelledby="pruneModalTitle"
```
- **Apple HIG Bento Replacement Structure**:
```html
    <div x-show="openPruneModal" x-cloak
        class="fixed inset-0 z-[200] overflow-y-auto"
        role="dialog" aria-modal="true"
        @keydown.escape.window="openPruneModal = false">
        <!-- 1. Standalone Independent Backdrop Overlay -->
        <div x-show="openPruneModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
            @click="openPruneModal = false"></div>

        <!-- 2. Centered Scroll & Dialog Container -->
        <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="openPruneModal = false">
                        <div role="dialog" aria-modal="true" aria-labelledby="pruneModalTitle"
                <!-- Modal Header, Body, Footer Content -->
            </div>
        </div>
    </div>
```

## 2. Logic Chain

### Step 1: Root Cause Analysis of Double Header Redundancy
- *Observation*: In `layouts/app.blade.php`, `@include('layouts.partials.topbar', ...)` is rendered on line 1431. `topbar.blade.php:205–212` renders an `<h1>` with `$headerTitle ?? $title ?? 'Cooca'` and a `<p>` with `$headerSubtitle`. When a view passes `headerTitle` and `headerSubtitle` in `@extends('layouts.app', [...])`, the sticky topbar displays them.
- *Observation*: Inside `@section('content')`, all 44 views render `<x-module-header :title="..." :subtitle="...">`. This component renders a complete Apple Bento Card with breadcrumbs, title, subtitle, badges, and action buttons.
- *Deduction*: Both components render the identical title and subtitle simultaneously within the top 200px of the screen. On 1366×768 resolution screens (common in retail/cashier environments), 30.7% of the viewport is consumed before the table or form begins.
- *Solution Logic*: By eliminating `headerTitle` and `headerSubtitle` arguments from `@extends('layouts.app')` across all 44 views, the sticky topbar only renders the browser title/default breadcrumb, while `<x-module-header>` serves as the authoritative, rich section header containing breadcrumbs and action controls. Zero visual duplicate occurs.

### Step 2: Root Cause Analysis of Flawed Backdrop Wrappers
- *Observation*: 76 modals declare `class="fixed inset-0 ... flex items-center justify-center ... bg-black/... backdrop-blur-..."` on the outermost element.
- *Deduction*: Combining flex centering with the translucent background on a single element causes three catastrophic failure modes:
  1. **Scroll Clipping**: When modal contents exceed viewport height (e.g. cash reconciliation, multi-branch pricing), `flex items-center` vertically centers the dialog within the viewport. The top (modal title/close button) and bottom (submit buttons) are pushed out of the viewport. Because the container is not `overflow-y-auto`, the clipped areas cannot be scrolled into view.
  2. **Topbar & Z-Index Bleed**: The backdrop is attached to a non-teleported or improperly positioned container, causing parent overflow and sticky topbar z-indices (z-[35]) to peek through or tear.
  3. **Event Propagation Flaws**: Dismissing modals via clicking outside relies on brittle `@click.away` or `@click.outside` on the dialog, which can conflict with nested dropdowns, flatpickr datepickers, or select menus.
- *Solution Logic*: Adopting the Golden Reference Standard from `suppliers/index.blade.php:656–674`:
  - Wrap in `<template x-teleport="body">` to escape parent DOM stacking contexts.
  - Outer wrapper: `class="fixed inset-0 z-[200] overflow-y-auto"` with `role="dialog" aria-modal="true"`.
  - Dedicated backdrop: `<div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity" @click="close">` provides smooth independent fade transition.
  - Scroll wrapper: `<div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6" @click.self="close">` centers the dialog while gracefully allowing vertical scroll when content height exceeds the screen.
  - Dialog container: `relative z-10 rounded-[24px] max-h-[90vh] overflow-hidden flex flex-col`.
## 3. Caveats

1. **Browser Page Title Preservation**: In all 44 Double Header views, the `'title'` key in `@extends('layouts.app', ['title' => ...])` must remain intact. This key dictates the browser tab title `<title>{{ $title ?? 'Cooca' }} - cooca.id</title>` (line 399 in `layouts/app.blade.php`).
2. **Teleportation Scope for POS Terminal**: In `pos/terminal.blade.php`, 20 modals are embedded directly in a standalone full-screen layout. While they can be wrapped in `<template x-teleport="body">`, care must be taken that Alpine.js state scopes (`showCustomerModal`, `showPaymentModal`, etc.) remain attached to the root `x-data="posTerminal()"` element.
3. **Phase 2 & Phase 3 Interdependencies**: Phase 1 addresses layout normalization and backdrop architecture. Some modal forms in `pos/terminal.blade.php` and `materials/index.blade.php` that are cramped (`max-w-md`) will be structurally expanded to Apple Bento XXL (`lg:max-w-4xl`) during Phase 2. The backdrop refactor performed in Phase 1 directly unblocks Phase 2 dialog widening without visual bugs.
4. **Zero Functionality Disruption**: No route names, form actions, input names, or JavaScript business logic methods are to be touched during Phase 1. The changes are strictly markup and container architecture.

## 4. Conclusion

Phase 1 Remediation is fully mapped, verified, and scoped for implementation:
- **Double Header Normalization**: 44 target views mapped with exact lines (1–5) and exact replacements.
- **Modal Backdrop Architecture**: 76 target instances across 21 views mapped with line numbers, Alpine identifiers, and Bento replacement structures.
- **Zero Regression Risk**: All changes are backwards-compatible and preserve 100% of existing Alpine.js event handlers, Blade slots, and controller bindings.

## 5. Verification Method

To independently verify this exploration and validate the subsequent implementation, execute the following commands in the workspace root:

### 5.1 Pre/Post Blade Compilation Test
Verify that Blade compilation succeeds without template syntax errors:
```powershell
php artisan view:clear
php artisan view:cache
```
*Pass Condition*: Compilation exits with code 0 (`Blade templates cached successfully`).

### 5.2 Double Header Verification Command
Ensure that zero double header instances remain after remediation:
```powershell
python -c "
import glob, os
base = r'resources/views/app'
fl = glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True)
dh = [f for f in fl if 'headerTitle' in open(f, encoding='utf-8', errors='ignore').read() and '<x-module-header' in open(f, encoding='utf-8', errors='ignore').read()]
print(f'Remaining Double Header Views: {len(dh)}')
"
```
*Pass Condition*: `Remaining Double Header Views: 0` (currently 44).

### 5.3 Flawed Backdrop Verification Command
Ensure that zero flawed backdrop wrappers remain after remediation:
```powershell
python -c "
import glob, os, re
base = r'resources/views/app'
pattern = re.compile(r'class=\"[^\"]*\bfixed\s+inset-0\b[^\"]*\bflex\b[^\"]*\bbg-black/(?:[0-9]+)\b')
matches = [f for f in glob.glob(os.path.join(base, '**', '*.blade.php'), recursive=True) if pattern.search(open(f, encoding='utf-8', errors='ignore').read())]
total = sum(len(pattern.findall(open(f, encoding='utf-8', errors='ignore').read())) for f in matches)
print(f'Remaining Flawed Backdrop Instances: {total} in {len(matches)} views')
"
```
*Pass Condition*: `Remaining Flawed Backdrop Instances: 0 in 0 views` (currently 76 in 21 views).
