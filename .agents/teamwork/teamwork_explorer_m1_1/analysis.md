# Detailed Analysis: Universal Translations in `lang/id/storefront.php` & `lang/en/storefront.php`

**Explorer ID**: `teamwork_explorer_m1_1`  
**Milestone**: Milestone 1 (Backend & Service Normalization)  
**Target Files**:
- `c:\laragon\www\cooca_core\lang\id\storefront.php`
- `c:\laragon\www\cooca_core\lang\en\storefront.php`  
**Related Specifications**:
- `STANDARISASI_DAN_IMPLEMENTASI_TEMA_UNIVERSAL.md` (Blueprint §1, §2, §3, §5)
- `PROJECT.md` (Milestone 1, Feature 1)

---

## 1. Executive Summary

This investigation analyzed the translation dictionary requirements for the **Universal Storefront Theme Standardization** in COOCA. The goal is to eliminate hardcoded sector-specific text from all 42 storefront themes by providing a robust, Apple HIG Bento-compliant 3rd-tier smart fallback:
$$\text{User CMS Input } (\$landingPage) \longrightarrow \text{Business Profile } (\$business) \longrightarrow \text{Universal i18n } (\_\_('storefront...'))$$

### Key Findings from Codebase Audit
1. **`storefront.badges.*`**: Completely missing from both `lang/id/storefront.php` and `lang/en/storefront.php`. Needs to be created.
2. **`storefront.features.*`**: Completely missing from both files (only `backoffice.tab_features` exists). Needs to be created for the 4-pillar trust banner.
3. **`storefront.hero.*`**: Already contains all 5 required keys (`tagline`, `explore_catalog`, `contact_us`, `verified_official`, `active_status`) in both ID and EN, with high-quality semantic phrasing. These must be preserved.
4. **`storefront.catalog.*`**: Contains standard catalog filters, but is missing `pilih_kategori_filter`, `semua_produk`, and `kategori_pilihan` inside the `catalog` array. (Note: `kategori_pilihan` and `semua_produk` currently exist at the root level of `storefront.php`). Dual-registration is recommended for 100% backward & forward compatibility.
5. **`storefront.empty.*`**: Currently missing as a structured array. Existing views use hardcoded Indonesian text mixed with loose root keys (`no_products_found`, `cart_is_empty`, `reset_all_filters`). A comprehensive empty state structure covering `catalog`, `products`, and `cart` with title, description, and action CTA is designed.

---

## 2. Detailed Array Architecture & Specifications

### 2.1 `storefront.badges.*` (Trust Pills & Hero Highlights)
Used in `hero.blade.php`, brand identity Bento cards, and announcement pills across all themes.

| Translation Key | Bahasa Indonesia (`id`) | English (`en`) | Usage Context |
|---|---|---|---|
| `quality_guaranteed` | `Kualitas Terjamin` | `Quality Guaranteed` | Pillar 1 badge fallback (`$cmsValues[0]['title']`) |
| `secure_transaction` | `Transaksi Aman` | `Secure Transaction` | Pillar 2 badge fallback (`$cmsValues[1]['title']`) |
| `fast_service` | `Layanan Cepat` | `Fast Service` | Pillar 3 badge fallback (`$cmsValues[2]['title']`) |
| `verified_official` | `Toko Resmi Terverifikasi` | `Verified Official Store` | Eyebrow/announcement pill fallback |
| `active_status` | `Aktif Beroperasi` | `Operating Actively` | Store status indicator in Bento graphic |

#### Recommended PHP Code (`badges`):
```php
'badges' => [
    'quality_guaranteed' => 'Kualitas Terjamin',
    'secure_transaction' => 'Transaksi Aman',
    'fast_service' => 'Layanan Cepat',
    'verified_official' => 'Toko Resmi Terverifikasi',
    'active_status' => 'Aktif Beroperasi',
],
```

---

### 2.2 `storefront.features.*` (4-Pillar Universal Trust Banner)
Used in `features_banner.blade.php` when the business owner has not populated custom values in CMS Tab 3 (*About / Keunggulan*).

| Translation Key | Bahasa Indonesia (`id`) | English (`en`) | Icon Token |
|---|---|---|---|
| `official_store` | `Outlet Resmi Terverifikasi` | `Verified Official Store` | `shield-check` |
| `official_desc` | `Produk dan layanan 100% original langsung dari merchant resmi terpercaya.` | `100% authentic products and dependable services directly from verified merchants.` | — |
| `easy_payment` | `Pembayaran Mudah & Aman` | `Seamless & Secure Payment` | `credit-card` |
| `payment_desc` | `Mendukung QRIS instan, transfer bank, e-wallet, serta metode pembayaran aman lainnya.` | `Supports instant QRIS, bank transfers, e-wallets, and trusted payment methods.` | — |
| `secure_delivery` | `Pengiriman Cepat & Aman` | `Fast & Reliable Delivery` | `truck` |
| `delivery_desc` | `Layanan pengantaran andal dengan nomor resi dan pelacakan status pesanan real-time.` | `Dependable courier and logistics services with automated real-time order tracking.` | — |
| `customer_care` | `Layanan Pelanggan Siaga` | `Dedicated Customer Support` | `headset` / `message-circle` |
| `care_desc` | `Konsultasi ramah dan respon cepat langsung bersama tim kami melalui WhatsApp resmi.` | `Friendly assistance and prompt responses directly from our team via official WhatsApp.` | — |

#### Recommended PHP Code (`features`):
```php
'features' => [
    'official_store' => 'Outlet Resmi Terverifikasi',
    'official_desc' => 'Produk dan layanan 100% original langsung dari merchant resmi terpercaya.',
    'easy_payment' => 'Pembayaran Mudah & Aman',
    'payment_desc' => 'Mendukung QRIS instan, transfer bank, e-wallet, serta metode pembayaran aman lainnya.',
    'secure_delivery' => 'Pengiriman Cepat & Aman',
    'delivery_desc' => 'Layanan pengantaran andal dengan nomor resi dan pelacakan status pesanan real-time.',
    'customer_care' => 'Layanan Pelanggan Siaga',
    'care_desc' => 'Konsultasi ramah dan respon cepat langsung bersama tim kami melalui WhatsApp resmi.',
],
```

---

### 2.3 `storefront.hero.*` (Hero Copy & CTAs)
Audited against Blueprint §3.1. All required keys already exist in `lang/id/storefront.php` and `lang/en/storefront.php`.

| Translation Key | Bahasa Indonesia (`id`) | English (`en`) | Status in Codebase |
|---|---|---|---|
| `tagline` | `Belanja aneka produk dan layanan berkualitas langsung dari toko resmi kami dengan jaminan mutu dan pengiriman terpercaya.` | `Shop a curated collection of quality products and services directly from our official store with guaranteed satisfaction and trusted delivery.` | Existing (Valid) |
| `explore_catalog` | `Jelajahi Katalog` | `Explore Catalog` | Existing (Valid) |
| `contact_us` | `Hubungi Kami` | `Contact Us` | Existing (Valid) |
| `verified_official` | `Toko Resmi Terverifikasi` | `Verified Official Store` | Existing (Valid) |
| `active_status` | `Aktif Beroperasi` | `Operating Actively` | Existing (Valid) |

*Action Required*: **Preserve existing keys**. No modifications or deletions needed; verify presence in automated test suite.

---

### 2.4 `storefront.catalog.*` (Navigation, Filtering & Category Chips)
Referenced in Blueprint §3.3 (`category_chips.blade.php`). Currently missing inside `'catalog' => [...]`.

| Translation Key | Bahasa Indonesia (`id`) | English (`en`) | Usage Context |
|---|---|---|---|
| `pilih_kategori_filter` | `Pilih kategori untuk memfilter etalase belanja` | `Select a category to filter the store catalog` | Subtitle below category title in category chips |
| `semua_produk` | `Semua Produk` | `All Products` | First chip to reset filter / show all items |
| `kategori_pilihan` | `Kategori Pilihan` | `Featured Categories` | Default header for category chips section |

#### Recommended PHP Code additions to `'catalog'`:
```php
'catalog' => [
    // Existing keys...
    'all_categories' => 'Semua Kategori',
    'pilih_kategori_filter' => 'Pilih kategori untuk memfilter etalase belanja',
    'semua_produk' => 'Semua Produk',
    'kategori_pilihan' => 'Kategori Pilihan',
    // ...
],
```

#### Backward Compatibility Recommendation:
In addition to nesting under `catalog`, preserve/define root aliases:
```php
'pilih_kategori_filter' => 'Pilih kategori untuk memfilter etalase belanja',
'kategori_pilihan' => 'Kategori Pilihan',
'semua_produk' => 'Semua Produk',
```
This ensures views using either `__('storefront.catalog.kategori_pilihan')` or `__('storefront.kategori_pilihan')` will resolve cleanly without exceptions.

---

### 2.5 `storefront.empty.*` (Universal Empty States)
Covers empty states for Catalog, Products, and Cart. Designed to support both flat access (`__('storefront.empty.cart_title')`) and nested access (`__('storefront.empty.cart.title')`).

| Sub-Domain | Key | Bahasa Indonesia (`id`) | English (`en`) |
|---|---|---|---|
| **Catalog** (No search/filter match) | `catalog_title` / `catalog.title` | `Produk Tidak Ditemukan` | `No Products Found` |
| | `catalog_desc` / `catalog.desc` | `Coba ubah kata kunci pencarian Anda atau reset filter untuk menampilkan seluruh produk.` | `Try adjusting your search keywords or reset filters to display all products.` |
| | `catalog_action` / `catalog.action` | `Reset Semua Filter` | `Reset All Filters` |
| **Products** (Store/Category has 0 items) | `products_title` / `products.title` | `Belum Ada Produk Tersedia` | `No Products Available Yet` |
| | `products_desc` / `products.desc` | `Koleksi produk dan layanan untuk etalase ini akan segera ditambahkan. Hubungi kami untuk informasi ketersediaan.` | `Our collection of products and services will be available soon. Please contact us for inquiries.` |
| | `products_action` / `products.action` | `Hubungi Penjual` | `Contact Store` |
| **Cart** (Checkout / Cart drawer empty) | `cart_title` / `cart.title` | `Keranjang Belanja Masih Kosong` | `Your Shopping Cart is Empty` |
| | `cart_desc` / `cart.desc` | `Silakan pilih produk favorit Anda dari katalog untuk melanjutkan ke proses pemesanan.` | `Browse our catalog and pick your favorite items before proceeding to checkout.` |
| | `cart_action` / `cart.action` | `Mulai Belanja` | `Start Shopping` |

#### Recommended PHP Code (`empty`):
```php
'empty' => [
    // Flat keys for convenient direct access
    'catalog_title' => 'Produk Tidak Ditemukan',
    'catalog_desc' => 'Coba ubah kata kunci pencarian Anda atau reset filter untuk menampilkan seluruh produk.',
    'catalog_action' => 'Reset Semua Filter',

    'products_title' => 'Belum Ada Produk Tersedia',
    'products_desc' => 'Koleksi produk dan layanan untuk etalase ini akan segera ditambahkan. Hubungi kami untuk informasi ketersediaan.',
    'products_action' => 'Hubungi Penjual',

    'cart_title' => 'Keranjang Belanja Masih Kosong',
    'cart_desc' => 'Silakan pilih produk favorit Anda dari katalog untuk melanjutkan ke proses pemesanan.',
    'cart_action' => 'Mulai Belanja',

    // Nested sub-arrays for structured access
    'catalog' => [
        'title' => 'Produk Tidak Ditemukan',
        'desc' => 'Coba ubah kata kunci pencarian Anda atau reset filter untuk menampilkan seluruh produk.',
        'action' => 'Reset Semua Filter',
    ],
    'products' => [
        'title' => 'Belum Ada Produk Tersedia',
        'desc' => 'Koleksi produk dan layanan untuk etalase ini akan segera ditambahkan. Hubungi kami untuk informasi ketersediaan.',
        'action' => 'Hubungi Penjual',
    ],
    'cart' => [
        'title' => 'Keranjang Belanja Masih Kosong',
        'desc' => 'Silakan pilih produk favorit Anda dari katalog untuk melanjutkan ke proses pemesanan.',
        'action' => 'Mulai Belanja',
    ],
],
```

---

## 3. Full Proposed Code Replacement for Implementer

Below are the exact code chunks to be inserted into `lang/id/storefront.php` and `lang/en/storefront.php`.

### 3.1 Indonesian Chunk (`lang/id/storefront.php`)
Insert right after `'nav' => [...]` (around line 23):

```php
    'badges' => [
        'quality_guaranteed' => 'Kualitas Terjamin',
        'secure_transaction' => 'Transaksi Aman',
        'fast_service' => 'Layanan Cepat',
        'verified_official' => 'Toko Resmi Terverifikasi',
        'active_status' => 'Aktif Beroperasi',
    ],
    'features' => [
        'official_store' => 'Outlet Resmi Terverifikasi',
        'official_desc' => 'Produk dan layanan 100% original langsung dari merchant resmi terpercaya.',
        'easy_payment' => 'Pembayaran Mudah & Aman',
        'payment_desc' => 'Mendukung QRIS instan, transfer bank, e-wallet, serta metode pembayaran aman lainnya.',
        'secure_delivery' => 'Pengiriman Cepat & Aman',
        'delivery_desc' => 'Layanan pengantaran andal dengan nomor resi dan pelacakan status pesanan real-time.',
        'customer_care' => 'Layanan Pelanggan Siaga',
        'care_desc' => 'Konsultasi ramah dan respon cepat langsung bersama tim kami melalui WhatsApp resmi.',
    ],
```

And inside `'catalog' => [...]`:
```php
        'pilih_kategori_filter' => 'Pilih kategori untuk memfilter etalase belanja',
        'semua_produk' => 'Semua Produk',
        'kategori_pilihan' => 'Kategori Pilihan',
```

And insert after `'catalog' => [...]`:
```php
    'empty' => [
        'catalog_title' => 'Produk Tidak Ditemukan',
        'catalog_desc' => 'Coba ubah kata kunci pencarian Anda atau reset filter untuk menampilkan seluruh produk.',
        'catalog_action' => 'Reset Semua Filter',

        'products_title' => 'Belum Ada Produk Tersedia',
        'products_desc' => 'Koleksi produk dan layanan untuk etalase ini akan segera ditambahkan. Hubungi kami untuk informasi ketersediaan.',
        'products_action' => 'Hubungi Penjual',

        'cart_title' => 'Keranjang Belanja Masih Kosong',
        'cart_desc' => 'Silakan pilih produk favorit Anda dari katalog untuk melanjutkan ke proses pemesanan.',
        'cart_action' => 'Mulai Belanja',

        'catalog' => [
            'title' => 'Produk Tidak Ditemukan',
            'desc' => 'Coba ubah kata kunci pencarian Anda atau reset filter untuk menampilkan seluruh produk.',
            'action' => 'Reset Semua Filter',
        ],
        'products' => [
            'title' => 'Belum Ada Produk Tersedia',
            'desc' => 'Koleksi produk dan layanan untuk etalase ini akan segera ditambahkan. Hubungi kami untuk informasi ketersediaan.',
            'action' => 'Hubungi Penjual',
        ],
        'cart' => [
            'title' => 'Keranjang Belanja Masih Kosong',
            'desc' => 'Silakan pilih produk favorit Anda dari katalog untuk melanjutkan ke proses pemesanan.',
            'action' => 'Mulai Belanja',
        ],
    ],
```

And at the root level of `lang/id/storefront.php` (for legacy views):
```php
    'pilih_kategori_filter' => 'Pilih kategori untuk memfilter etalase belanja',
```

---

### 3.2 English Chunk (`lang/en/storefront.php`)
Insert right after `'nav' => [...]`:

```php
    'badges' => [
        'quality_guaranteed' => 'Quality Guaranteed',
        'secure_transaction' => 'Secure Transaction',
        'fast_service' => 'Fast Service',
        'verified_official' => 'Verified Official Store',
        'active_status' => 'Operating Actively',
    ],
    'features' => [
        'official_store' => 'Verified Official Store',
        'official_desc' => '100% authentic products and dependable services directly from verified merchants.',
        'easy_payment' => 'Seamless & Secure Payment',
        'payment_desc' => 'Supports instant QRIS, bank transfers, e-wallets, and trusted payment methods.',
        'secure_delivery' => 'Fast & Reliable Delivery',
        'delivery_desc' => 'Dependable courier and logistics services with automated real-time order tracking.',
        'customer_care' => 'Dedicated Customer Support',
        'care_desc' => 'Friendly assistance and prompt responses directly from our team via official WhatsApp.',
    ],
```

And inside `'catalog' => [...]`:
```php
        'pilih_kategori_filter' => 'Select a category to filter the store catalog',
        'semua_produk' => 'All Products',
        'kategori_pilihan' => 'Featured Categories',
```

And insert after `'catalog' => [...]`:
```php
    'empty' => [
        'catalog_title' => 'No Products Found',
        'catalog_desc' => 'Try adjusting your search keywords or reset filters to display all products.',
        'catalog_action' => 'Reset All Filters',

        'products_title' => 'No Products Available Yet',
        'products_desc' => 'Our collection of products and services will be available soon. Please contact us for inquiries.',
        'products_action' => 'Contact Store',

        'cart_title' => 'Your Shopping Cart is Empty',
        'cart_desc' => 'Browse our catalog and pick your favorite items before proceeding to checkout.',
        'cart_action' => 'Start Shopping',

        'catalog' => [
            'title' => 'No Products Found',
            'desc' => 'Try adjusting your search keywords or reset filters to display all products.',
            'action' => 'Reset All Filters',
        ],
        'products' => [
            'title' => 'No Products Available Yet',
            'desc' => 'Our collection of products and services will be available soon. Please contact us for inquiries.',
            'action' => 'Contact Store',
        ],
        'cart' => [
            'title' => 'Your Shopping Cart is Empty',
            'desc' => 'Browse our catalog and pick your favorite items before proceeding to checkout.',
            'action' => 'Start Shopping',
        ],
    ],
```

And at the root level of `lang/en/storefront.php`:
```php
    'pilih_kategori_filter' => 'Select a category to filter the store catalog',
```

---

## 4. Downstream Impact & Compatibility Verification

1. **Theme Batch Refactoring (Milestones M2A–M2D)**:
   - When 42 themes are refactored, hero templates can immediately call `__('storefront.badges.quality_guaranteed')` and `features_banner.blade.php` can iterate over the 4 default pillars using `__('storefront.features.*')`.
2. **CMS Value Normalization (Feature 2 in M1)**:
   - `BusinessLandingPage::getUniversalValues()` will map any empty CMS slots to `__('storefront.features.*')`.
3. **No Breaking Changes**:
   - Existing keys (like `hero.tagline`, `hero.explore_catalog`, `catalog.all_categories`, `storefront.kategori_pilihan`) remain 100% backward-compatible.
