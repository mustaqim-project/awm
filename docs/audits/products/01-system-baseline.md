# System Baseline: Modul Produk & Master Data Katalog

| Detail | Deskripsi |
| :--- | :--- |
| **Modul** | Products (Katalog Produk, Varian, Modifiers, Resep BOM, Bundel, & Harga Cabang) |
| **Namespace Controller** | `App\Http\Controllers\Web\ProductWebController`<br>`App\Http\Controllers\Web\ProductCategoryWebController`<br>`App\Http\Controllers\Web\MasterDataWebController` |
| **Model Terkait** | `Product`, `ProductCategory`, `ProductImage`, `ProductModifierGroup`, `ProductModifierOption`, `ProductBundleItem`, `BranchProductPrice`, `CostModel`, `BomHeader`, `BomItem`, `Unit`, `AuditLog` |
| **Blade Views** | `resources/views/app/products/index.blade.php`<br>`resources/views/app/products/bom.blade.php`<br>`resources/views/app/products/modifiers.blade.php` |
| **File Bahasa** | `lang/id/products.php`<br>`lang/en/products.php` |
| **Test Suite** | `tests/Feature/ProductsModuleAuditTest.php` |

---

## 1. Pemetaan Rute Sistem (Route Inventory)
Seluruh rute pada modul produk dikelompokkan dalam middleware group autentikasi tenant (`['auth', 'verified', 'tenant.active']`):

| Nama Rute | HTTP Method | URI | Controller Action | Middleware Guard |
| :--- | :--- | :--- | :--- | :--- |
| `products.index` | `GET` | `/products` | `ProductWebController@index` | `require.permission:products.view` |
| `products.store` | `POST` | `/products` | `ProductWebController@store` | `require.permission:products.create`, `entitlement:product` |
| `products.edit` | `GET` | `/products/{product}/edit` | `ProductWebController@edit` | `require.permission:products.edit` |
| `products.update` | `PUT` | `/products/{product}` | `ProductWebController@update` | `require.permission:products.edit` |
| `products.destroy` | `DELETE` | `/products/{product}` | `ProductWebController@destroy` | `require.permission:products.delete` |
| `products.toggle-setting` | `POST` | `/products/{product}/toggle-setting` | `ProductWebController@toggleSetting` | `require.permission:products.edit` |
| `products.toggle-pos-image-visibility` | `POST` | `/products/{product}/toggle-pos-image-visibility` | `ProductWebController@togglePosImageVisibility` | `require.permission:products.edit` |
| `products.gallery.destroy` | `DELETE` | `/products/{product}/gallery/{image}` | `ProductWebController@deleteGalleryImage` | `require.permission:products.edit` |
| `products.branch_prices.index` | `GET` | `/products/{product}/branch-prices` | `ProductWebController@branchPrices` | `require.permission:products.view` |
| `products.branch_prices.update` | `POST` | `/products/{product}/branch-prices` | `ProductWebController@updateBranchPrices` | `require.permission:products.edit` |
| `products.bom.index` | `GET` | `/products/{product}/bom` | `ProductWebController@bom` | `require.permission:products.view`, `entitlement:recipe` |
| `products.modifiers.index` | `GET` | `/products/{product}/modifiers` | `ProductWebController@modifiers` | `require.permission:products.view` |
| `products.modifiers.sync` | `POST` | `/products/{product}/modifiers` | `ProductWebController@syncModifiers` | `require.permission:products.edit` |
| `bom.items.store` | `POST` | `/bom-headers/{bomHeader}/items` | `ProductWebController@addBomItem` | `require.permission:products.edit`, `entitlement:recipe` |
| `bom.items.destroy` | `DELETE` | `/bom-items/{bomItem}` | `ProductWebController@removeBomItem` | `require.permission:products.edit` |
| `product-categories.index` | `GET` | `/product-categories` | `ProductCategoryWebController@index` | `require.permission:master_data.product_categories.view` |
| `product-categories.store` | `POST` | `/product-categories` | `ProductCategoryWebController@store` | `require.permission:master_data.product_categories.manage` |
| `product-categories.update` | `PUT` | `/product-categories/{productCategory}` | `ProductCategoryWebController@update` | `require.permission:master_data.product_categories.manage` |
| `product-categories.destroy` | `DELETE` | `/product-categories/{productCategory}` | `ProductCategoryWebController@destroy` | `require.permission:master_data.product_categories.manage` |

---

## 2. Struktur Skema Database Terkait
1. **`products`**:
   - `id` (UUID Primary Key)
   - `business_id` (UUID Foreign Key terindeks `BelongsToBusiness`)
   - `category_id` (UUID Foreign Key ke `product_categories`)
   - `name`, `code` (SKU unik per tenant), `barcode`
   - `selling_price`, `base_cost`, `min_stock`
   - `is_bundle`, `show_in_pos`, `show_in_website`, `is_active`
   - `output_unit_id` (UUID Foreign Key ke `units`)
   - `deleted_at` (SoftDeletes)
2. **`product_categories`**:
   - `id`, `business_id`, `name`, `slug`, `description`
   - `marketplace_category_id`, `marketplace_category_name`
   - `deleted_at` (SoftDeletes)
3. **`product_bundle_items`**:
   - `id`, `business_id`, `parent_product_id`, `child_product_id`, `quantity`
4. **`branch_product_prices`**:
   - `id`, `business_id`, `product_id`, `location_id`, `price`, `cost_price`, `is_available`
5. **`cost_models` & `bom_headers` & `bom_items`**:
   - Model HPP multi-metode (Simple, Recipe BOM, Production Order) yang menampung bahan baku (`materials`), kuantitas pemakaian, dan waste margin.

---

## 3. Layanan & Integrasi Terkait (Domain Services)
- **`App\Domain\Storage\StorageTrackingService`**: Mengelola kuota penyimpanan gambar galeri tenant dan penghapusan fisik file storage S3/local secara aman.
- **`App\Domain\Billing\EntitlementService`**: Memverifikasi batasan plan langganan tenant untuk fitur tingkat lanjut (Resep BOM, jumlah maksimum produk).
- **`App\Domain\Marketplace\MarketplaceCategoryRegistry`**: Sinkronisasi pemetaan kategori lokal COOCA ke taksonomi kategori marketplace eksternal (Shopee, Tokopedia, TikTok Shop).
- **`App\Support\Context`**: Mengelola state tenant aktif (`Context::requireBusiness()`, `Context::business()`).
