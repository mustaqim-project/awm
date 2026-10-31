# Comprehensive Mapping of 44 Double Header Views

| # | View Path | @extends Lines | Target Content | Replacement Content | <x-module-header> Line |
|---|---|---|---|---|---|
| 1 | `resources/views/app/billing/checkout.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('billing.chec...` | `@extends('layouts.app')` | 318 |
| 2 | `resources/views/app/billing/history.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('billing.brea...` | `@extends('layouts.app')` | 32 |
| 3 | `resources/views/app/billing/limits.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('billing.meta...` | `@extends('layouts.app')` | 30 |
| 4 | `resources/views/app/billing/payment.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('billing.paym...` | `@extends('layouts.app')` | 204 |
| 5 | `resources/views/app/crm/members.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('crm.members_...` | `@extends('layouts.app')` | 94 |
| 6 | `resources/views/app/crm/vouchers.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('crm.vouchers...` | `@extends('layouts.app')` | 21 |
| 7 | `resources/views/app/customers/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('customers.ti...` | `@extends('layouts.app')` | 229 |
| 8 | `resources/views/app/feedback/create.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => $type === 'bugs'...` | `@extends('layouts.app')` | 25 |
| 9 | `resources/views/app/feedback/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => $type === 'bugs'...` | `@extends('layouts.app')` | 21 |
| 10 | `resources/views/app/feedback/show.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => $item->title,   ...` | `@extends('layouts.app')` | 25 |
| 11 | `resources/views/app/invoices/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('sales.invoic...` | `@extends('layouts.app')` | 28 |
| 12 | `resources/views/app/invoices/show.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('sales.invoic...` | `@extends('layouts.app')` | 91 |
| 13 | `resources/views/app/marketplace/chat.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('marketplace....` | `@extends('layouts.app')` | 15 |
| 14 | `resources/views/app/marketplace/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('marketplace....` | `@extends('layouts.app')` | 90 |
| 15 | `resources/views/app/marketplace/logs.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('marketplace....` | `@extends('layouts.app')` | 41 |
| 16 | `resources/views/app/marketplace/orders.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('marketplace....` | `@extends('layouts.app')` | 201 |
| 17 | `resources/views/app/marketplace/products.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('marketplace....` | `@extends('layouts.app')` | 883 |
| 18 | `resources/views/app/master-data/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => $title,     'hea...` | `@extends('layouts.app')` | 132 |
| 19 | `resources/views/app/materials/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('materials.ti...` | `@extends('layouts.app')` | 216 |
| 20 | `resources/views/app/products/bom.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('products.bom...` | `@extends('layouts.app')` | 31 |
| 21 | `resources/views/app/products/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('products.kat...` | `@extends('layouts.app')` | 782 |
| 22 | `resources/views/app/products/modifiers.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('products.mod...` | `@extends('layouts.app')` | 13 |
| 23 | `resources/views/app/purchase-orders/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('purchasing.t...` | `@extends('layouts.app')` | 38 |
| 24 | `resources/views/app/purchasing/bills/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('purchasing.b...` | `@extends('layouts.app')` | 12 |
| 25 | `resources/views/app/purchasing/bills/show.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('purchasing.b...` | `@extends('layouts.app')` | 12 |
| 26 | `resources/views/app/purchasing/receipts/create.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('purchasing.r...` | `@extends('layouts.app')` | 15 |
| 27 | `resources/views/app/purchasing/returns/create.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('purchasing.r...` | `@extends('layouts.app')` | 14 |
| 28 | `resources/views/app/purchasing/returns/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('purchasing.r...` | `@extends('layouts.app')` | 12 |
| 29 | `resources/views/app/purchasing/returns/show.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('purchasing.r...` | `@extends('layouts.app')` | 12 |
| 30 | `resources/views/app/quotations/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('sales.quotat...` | `@extends('layouts.app')` | 27 |
| 31 | `resources/views/app/sales-orders/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('sales.sales_...` | `@extends('layouts.app')` | 38 |
| 32 | `resources/views/app/sales/returns/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('sales.return...` | `@extends('layouts.app')` | 37 |
| 33 | `resources/views/app/services/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('services.tit...` | `@extends('layouts.app')` | 179 |
| 34 | `resources/views/app/social_media/calendar.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('social_media...` | `@extends('layouts.app')` | 32 |
| 35 | `resources/views/app/social_media/inbox.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('social_media...` | `@extends('layouts.app')` | 11 |
| 36 | `resources/views/app/social_media/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('social_media...` | `@extends('layouts.app')` | 11 |
| 37 | `resources/views/app/social_media/insights.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('social_media...` | `@extends('layouts.app')` | 11 |
| 38 | `resources/views/app/social_media/posts.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('social_media...` | `@extends('layouts.app')` | 11 |
| 39 | `resources/views/app/suppliers/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('purchasing.s...` | `@extends('layouts.app')` | 203 |
| 40 | `resources/views/app/warehouse/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('warehouse.ti...` | `@extends('layouts.app')` | 348 |
| 41 | `resources/views/app/warehouse/show.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('warehouse.de...` | `@extends('layouts.app')` | 73 |
| 42 | `resources/views/app/whatsapp/broadcast.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('whatsapp.bro...` | `@extends('layouts.app')` | 11 |
| 43 | `resources/views/app/whatsapp/index.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('whatsapp.tit...` | `@extends('layouts.app')` | 11 |
| 44 | `resources/views/app/whatsapp/logs.blade.php` | 1-5 | `@extends('layouts.app', [     'title' => __('whatsapp.log...` | `@extends('layouts.app')` | 23 |

## Detailed View Specifications

### 1. `resources/views/app/billing/checkout.blade.php`
- **File Path**: `resources/views/app/billing/checkout.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 318
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('billing.checkout_title') . ' - Cooca',
    'headerTitle' => __('billing.breadcrumb_checkout'),
    'headerSubtitle' => __('billing.checkout_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 2. `resources/views/app/billing/history.blade.php`
- **File Path**: `resources/views/app/billing/history.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 32
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('billing.breadcrumb_history') . ' - Cooca',
    'headerTitle' => __('billing.history_title'),
    'headerSubtitle' => __('billing.history_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 3. `resources/views/app/billing/limits.blade.php`
- **File Path**: `resources/views/app/billing/limits.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 30
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('billing.meta_title_limits'),
    'headerTitle' => __('billing.title'),
    'headerSubtitle' => __('billing.subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 4. `resources/views/app/billing/payment.blade.php`
- **File Path**: `resources/views/app/billing/payment.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 204
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('billing.payment_page_title', ['order' => $payment->order_number]),
    'headerTitle' => __('billing.header_payment_instructions'),
    'headerSubtitle' => __('billing.header_payment_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 5. `resources/views/app/crm/members.blade.php`
- **File Path**: `resources/views/app/crm/members.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 94
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('crm.members_title') . ' - Cooca',
    'headerTitle' => __('crm.members_title'),
    'headerSubtitle' => __('crm.subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 6. `resources/views/app/crm/vouchers.blade.php`
- **File Path**: `resources/views/app/crm/vouchers.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 21
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('crm.vouchers_title') . ' - Cooca',
    'headerTitle' => __('crm.vouchers_title'),
    'headerSubtitle' => __('crm.vouchers_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 7. `resources/views/app/customers/index.blade.php`
- **File Path**: `resources/views/app/customers/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 229
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('customers.title') . ' - Cooca',
    'headerTitle' => __('customers.directory_title'),
    'headerSubtitle' => __('customers.subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 8. `resources/views/app/feedback/create.blade.php`
- **File Path**: `resources/views/app/feedback/create.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 25
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature'),
    'headerTitle' => $type === 'bugs' ? __('feedback.create_bug') : __('feedback.create_feature'),
    'headerSubtitle' => $type === 'bugs' ? __('feedback.subtitle_bugs') : __('feedback.subtitle_features'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 9. `resources/views/app/feedback/index.blade.php`
- **File Path**: `resources/views/app/feedback/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 21
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features'),
    'headerTitle' => $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features'),
    'headerSubtitle' => $type === 'bugs' ? __('feedback.subtitle_bugs') : __('feedback.subtitle_features'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 10. `resources/views/app/feedback/show.blade.php`
- **File Path**: `resources/views/app/feedback/show.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 25
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => $item->title,
    'headerTitle' => $type === 'bugs' ? __('feedback.title_bugs') : __('feedback.title_features'),
    'headerSubtitle' => $item->title,
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 11. `resources/views/app/invoices/index.blade.php`
- **File Path**: `resources/views/app/invoices/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 28
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('sales.invoices.page_title'),
    'headerTitle' => __('sales.invoices.header_title'),
    'headerSubtitle' => __('sales.invoices.header_subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 12. `resources/views/app/invoices/show.blade.php`
- **File Path**: `resources/views/app/invoices/show.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 91
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('sales.invoices.show_title', ['number' => $invoice->invoice_number]),
    'headerTitle' => __('sales.invoices.show_title', ['number' => $invoice->invoice_number]),
    'headerSubtitle' => __('sales.invoices.show_subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 13. `resources/views/app/marketplace/chat.blade.php`
- **File Path**: `resources/views/app/marketplace/chat.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 15
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.chat_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.chat_header_title'),
    'headerSubtitle' => __('marketplace.chat_page_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 14. `resources/views/app/marketplace/index.blade.php`
- **File Path**: `resources/views/app/marketplace/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 90
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.page_title'),
    'headerSubtitle' => __('marketplace.page_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 15. `resources/views/app/marketplace/logs.blade.php`
- **File Path**: `resources/views/app/marketplace/logs.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 41
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.logs_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.logs_header_title'),
    'headerSubtitle' => __('marketplace.logs_page_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 16. `resources/views/app/marketplace/orders.blade.php`
- **File Path**: `resources/views/app/marketplace/orders.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 201
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.orders_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.orders_page_title'),
    'headerSubtitle' => __('marketplace.orders_page_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 17. `resources/views/app/marketplace/products.blade.php`
- **File Path**: `resources/views/app/marketplace/products.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 883
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('marketplace.products_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.products_page_title'),
    'headerSubtitle' => __('marketplace.products_page_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 18. `resources/views/app/master-data/index.blade.php`
- **File Path**: `resources/views/app/master-data/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 132
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => $title,
    'headerTitle' => $title,
    'headerSubtitle' => $subtitle,
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 19. `resources/views/app/materials/index.blade.php`
- **File Path**: `resources/views/app/materials/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 216
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('materials.title'),
    'headerTitle' => __('materials.header_title'),
    'headerSubtitle' => __('materials.header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 20. `resources/views/app/products/bom.blade.php`
- **File Path**: `resources/views/app/products/bom.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 31
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('products.bom_title_prefix', ['name' => $product->name]),
    'headerTitle' => __('products.bom_header_title'),
    'headerSubtitle' => __('products.bom_header_subtitle', ['name' => $product->name]),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 21. `resources/views/app/products/index.blade.php`
- **File Path**: `resources/views/app/products/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 782
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('products.katalog_produk_model_hpp'),
    'headerTitle' => 'Katalog Produk & Model HPP',
    'headerSubtitle' => 'Kelola produk jadi, kalkulasi biaya (BOM, ABC, Job Order), harga jual, dan saluran etalase',
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 22. `resources/views/app/products/modifiers.blade.php`
- **File Path**: `resources/views/app/products/modifiers.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 13
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('products.modifiers_title'),
    'headerTitle' => __('products.modifiers_title'),
    'headerSubtitle' => __('products.modifiers_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 23. `resources/views/app/purchase-orders/index.blade.php`
- **File Path**: `resources/views/app/purchase-orders/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 38
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.title'),
    'headerTitle' => __('purchasing.header_title'),
    'headerSubtitle' => __('purchasing.header_subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 24. `resources/views/app/purchasing/bills/index.blade.php`
- **File Path**: `resources/views/app/purchasing/bills/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 12
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.bills.title'),
    'headerTitle' => __('purchasing.bills.title'),
    'headerSubtitle' => __('purchasing.bills.subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 25. `resources/views/app/purchasing/bills/show.blade.php`
- **File Path**: `resources/views/app/purchasing/bills/show.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 12
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.bills.detail_title', ['number' => $invoice->invoice_number]),
    'headerTitle' => __('purchasing.bills.title'),
    'headerSubtitle' => __('purchasing.bills.detail_subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 26. `resources/views/app/purchasing/receipts/create.blade.php`
- **File Path**: `resources/views/app/purchasing/receipts/create.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 15
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.receipts.title', ['number' => $purchaseOrder->po_number]),
    'headerTitle' => __('purchasing.receipts.header_title'),
    'headerSubtitle' => __('purchasing.receipts.header_subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 27. `resources/views/app/purchasing/returns/create.blade.php`
- **File Path**: `resources/views/app/purchasing/returns/create.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 14
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.returns.create_title'),
    'headerTitle' => __('purchasing.returns.title'),
    'headerSubtitle' => __('purchasing.returns.create_subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 28. `resources/views/app/purchasing/returns/index.blade.php`
- **File Path**: `resources/views/app/purchasing/returns/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 12
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.returns.title'),
    'headerTitle' => __('purchasing.returns.title'),
    'headerSubtitle' => __('purchasing.returns.subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 29. `resources/views/app/purchasing/returns/show.blade.php`
- **File Path**: `resources/views/app/purchasing/returns/show.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 12
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.returns.detail_title', ['number' => $return->return_number]),
    'headerTitle' => __('purchasing.returns.title'),
    'headerSubtitle' => __('purchasing.returns.detail_subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 30. `resources/views/app/quotations/index.blade.php`
- **File Path**: `resources/views/app/quotations/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 27
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('sales.quotations.page_title'),
    'headerTitle' => __('sales.quotations.header_title'),
    'headerSubtitle' => __('sales.quotations.header_subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 31. `resources/views/app/sales-orders/index.blade.php`
- **File Path**: `resources/views/app/sales-orders/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 38
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('sales.sales_orders.page_title'),
    'headerTitle' => __('sales.sales_orders.header_title'),
    'headerSubtitle' => __('sales.sales_orders.header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 32. `resources/views/app/sales/returns/index.blade.php`
- **File Path**: `resources/views/app/sales/returns/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 37
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('sales.returns.page_title'),
    'headerTitle' => __('sales.returns.header_title'),
    'headerSubtitle' => __('sales.returns.header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 33. `resources/views/app/services/index.blade.php`
- **File Path**: `resources/views/app/services/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 179
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('services.title'),
    'headerTitle' => __('services.header_title'),
    'headerSubtitle' => __('services.header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 34. `resources/views/app/social_media/calendar.blade.php`
- **File Path**: `resources/views/app/social_media/calendar.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 32
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.calendar_header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.calendar_header_title'),
    'headerSubtitle' => __('social_media.calendar_header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 35. `resources/views/app/social_media/inbox.blade.php`
- **File Path**: `resources/views/app/social_media/inbox.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 11
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.unified_inbox_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.unified_inbox_title'),
    'headerSubtitle' => __('social_media.unified_inbox_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 36. `resources/views/app/social_media/index.blade.php`
- **File Path**: `resources/views/app/social_media/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 11
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.header_title'),
    'headerSubtitle' => __('social_media.header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 37. `resources/views/app/social_media/insights.blade.php`
- **File Path**: `resources/views/app/social_media/insights.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 11
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.insights_header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.insights_header_title'),
    'headerSubtitle' => __('social_media.insights_header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 38. `resources/views/app/social_media/posts.blade.php`
- **File Path**: `resources/views/app/social_media/posts.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 11
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('social_media.posts_header_title') . ' - ' . $business->name,
    'headerTitle' => __('social_media.posts_header_title'),
    'headerSubtitle' => __('social_media.posts_header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 39. `resources/views/app/suppliers/index.blade.php`
- **File Path**: `resources/views/app/suppliers/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 203
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('purchasing.supplier.title'),
    'headerTitle' => __('purchasing.supplier.header_title'),
    'headerSubtitle' => __('purchasing.supplier.header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 40. `resources/views/app/warehouse/index.blade.php`
- **File Path**: `resources/views/app/warehouse/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 348
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('warehouse.title'),
    'headerTitle' => __('warehouse.header_title'),
    'headerSubtitle' => __('warehouse.header_subtitle')
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 41. `resources/views/app/warehouse/show.blade.php`
- **File Path**: `resources/views/app/warehouse/show.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 73
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('warehouse.detail_title', ['name' => $location->name]),
    'headerTitle' => __('warehouse.detail_header', ['name' => $location->name]),
    'headerSubtitle' => __('warehouse.detail_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 42. `resources/views/app/whatsapp/broadcast.blade.php`
- **File Path**: `resources/views/app/whatsapp/broadcast.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 11
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('whatsapp.broadcast_title') . ' - ' . $business->name,
    'headerTitle' => __('whatsapp.broadcast_header_title'),
    'headerSubtitle' => __('whatsapp.broadcast_header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 43. `resources/views/app/whatsapp/index.blade.php`
- **File Path**: `resources/views/app/whatsapp/index.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 11
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('whatsapp.title') . ' - ' . $business->name,
    'headerTitle' => __('whatsapp.header_title'),
    'headerSubtitle' => __('whatsapp.header_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```

### 44. `resources/views/app/whatsapp/logs.blade.php`
- **File Path**: `resources/views/app/whatsapp/logs.blade.php`
- **@extends Range**: Lines 1 to 5
- **`<x-module-header>` Line**: Line 23
- **Target Content**:
```blade
@extends('layouts.app', [
    'title' => __('whatsapp.logs_title') . ' - ' . $business->name,
    'headerTitle' => __('whatsapp.logs_title'),
    'headerSubtitle' => __('whatsapp.logs_subtitle'),
])
```
- **Replacement Content**:
```blade
@extends('layouts.app')
```
