# Security & Access Control Audit: Modul Produk

| Aspek Keamanan | Status | Penilaian |
| :--- | :--- | :--- |
| **Controller-Level RBAC** | **Enforced** | `HasMiddleware` diimplementasikan di seluruh controller web terkait |
| **Multi-Tenant IDOR Guard** | **Hardened** | `BelongsToBusiness` global scope + explicit tenant assertion checks |
| **Audit Logging Trail** | **Active** | Transaksi kritis (CRUD produk, update harga, kategori) mencatat `AuditLog` |
| **CSRF & Mass Assignment Protection** | **Compliant** | `@csrf` aktif di semua Blade forms, `$fillable` terdefinisi ketat |

---

## 1. Controller-Level RBAC Enforcement
Sesuai arahan `/laravel-rbac` dan `/laravel-permission-audit`, seluruh perlindungan otorisasi wajib dideklarasikan di level Controller menggunakan kontrak `Illuminate\Routing\Controllers\HasMiddleware` untuk menghindari ketergantungan rapuh pada file route eksternal.

### A. `ProductWebController`
```php
public static function middleware(): array
{
    return [
        new Middleware('auth'),
        new Middleware('verified'),
        new Middleware('tenant.active'),
        new Middleware('require.permission:products.view', only: ['index', 'modifiers', 'branchPrices', 'bom']),
        new Middleware('require.permission:products.create', only: ['store']),
        new Middleware('require.permission:products.edit', only: ['update', 'toggleSetting', 'togglePosImageVisibility', 'deleteGalleryImage', 'updateBranchPrices', 'syncModifiers', 'addBomItem', 'removeBomItem']),
        new Middleware('require.permission:products.delete', only: ['destroy']),
    ];
}
```

### B. `ProductCategoryWebController` (Diperbaiki Saat Audit)
```php
public static function middleware(): array
{
    return [
        new Middleware('auth'),
        new Middleware('verified'),
        new Middleware('tenant.active'),
        new Middleware('require.permission:master_data.product_categories.manage', only: ['store', 'update', 'destroy']),
    ];
}
```

### C. `MasterDataWebController` (Diperbaiki Saat Audit)
```php
public static function middleware(): array
{
    return [
        new Middleware('auth'),
        new Middleware('verified'),
        new Middleware('tenant.active'),
        new Middleware('require.permission:master_data.product_categories.view', only: ['productCategories']),
        new Middleware('require.permission:master_data.material_categories.view', only: ['materialCategories']),
        new Middleware('require.permission:master_data.units.view', only: ['units']),
    ];
}
```

---

## 2. Pencegahan IDOR & Isolasi Data Multi-Tenant (Tenant Isolation)
Terdapat dua lapis pertahanan anti-IDOR pada model dan controller produk:
1. **Model Scope Layer (`BelongsToBusiness`):**
   - Model `Product`, `ProductCategory`, `ProductImage`, dan `CostModel` menggunakan trait `BelongsToBusiness`.
   - Trait ini menambahkan Global Scope yang secara otomatis menambahkan klausa `WHERE business_id = Context::business()->id` pada setiap query Eloquent.
   - Jika pengguna tenant A mencoba mengakses atau mengubah ID milik tenant B melalui URL, Laravel Implicit Route Model Binding akan gagal menemukan data dan mengembalikan status HTTP `404 Not Found`.
2. **Explicit Assertion Controller Layer:**
   - Pada method `update()`, `destroy()`, dan mutasi galeri, controller melakukan verifikasi eksplisit:
     ```php
     $business = Context::requireBusiness();
     abort_unless($product->business_id === $business->id, 403);
     ```
   - Lapis ganda ini memastikan jika ada pemanggilan internal tanpa global scope, akses lintas tenant tetap diblokir seketika.

---

## 3. Jejak Audit & Mitigasi Fraud (Audit Log Trail)
Perubahan data produk berdampak langsung pada nilai omzet, margin keuntungan, dan aset inventori. Oleh karena itu, modul ini dilengkapi pencatatan riwayat audit:

| Aksi | Level Risiko | Informasi Yang Dicatat |
| :--- | :--- | :--- |
| `product.created` | `LOW` | Data SKU, nama produk, harga jual awal, HPP dasar |
| `product.updated` | `LOW` | Perubahan atribut non-finansial produk |
| `product.price_updated` | `MEDIUM` | Nilai `old_selling_price`, `new_selling_price`, `old_base_cost`, `new_base_cost` |
| `product.branch_prices_updated` | `MEDIUM` | Daftar override harga dan ketersediaan per lokasi cabang |
| `product.deleted` | `MEDIUM` | Informasi soft delete produk dan pembersihan gambar |
| `product_category.created` | `LOW` | Nama kategori dan mapping marketplace |
| `product_category.updated` | `LOW` | Perubahan nama atau deskripsi kategori |
| `product_category.deleted` | `MEDIUM` | Penghapusan master kategori produk |
