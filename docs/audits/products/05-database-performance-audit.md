# Database & Performance Audit: Modul Produk

| Aspek Database | Evaluasi | Status |
| :--- | :--- | :--- |
| **Indexing Coverage** | Indeks pada `business_id`, `category_id`, `code`, `barcode`, `deleted_at` | **Optimal** |
| **N+1 Query Prevention** | Eager loading relasi master (`category`, `outputUnit`, `images`, `costModel`) | **Verified** |
| **Soft Deletes Integrity** | `deleted_at` terindeks, relasi cascade terkelola aman | **Optimal** |
| **Pagination & Filtering** | Paginate 25 item per halaman dengan filter terindeks | **Performant** |

---

## 1. Audit Indeks Tabel & Skema Relasional
Katalog produk adalah salah satu tabel dengan volume pembacaan (*read*) dan pemindaian (*lookup*) paling intensif di COOCA karena diakses terus-menerus oleh kasir POS dan pengunjung toko online.

Berikut adalah evaluasi indeks tabel `products`:
```sql
-- Indeks komposit untuk multi-tenant query & pencarian cepat SKU
INDEX `products_business_id_index` (`business_id`),
INDEX `products_category_id_index` (`category_id`),
INDEX `products_business_id_code_index` (`business_id`, `code`),
INDEX `products_business_id_barcode_index` (`business_id`, `barcode`),
INDEX `products_deleted_at_index` (`deleted_at`)
```
- Pencarian produk berdasarkan SKU (`code`) atau `barcode` selalu menyertakan `business_id` (via `BelongsToBusiness`), sehingga indeks gabungan `(business_id, code)` dieksekusi secara instan dengan *Index Seek* (O(log N)).

---

## 2. Pencegahan Masalah N+1 Queries
Pada `ProductWebController@index`, pengambilan data produk untuk tampilan daftar menggunakan eager loading yang komprehensif:
```php
$products = Product::where('business_id', $business->id)
    ->with([
        'category:id,name',
        'outputUnit:id,name,code',
        'images:id,product_id,image_path,is_primary,sort_order',
        'costModel:id,product_id,method,total_cost',
        'bundleItems.childProduct:id,name,code',
    ])
    ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
    ->when($request->search, function ($q, $search) {
        $q->where(function ($sub) use ($search) {
            $sub->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%");
        });
    })
    ->paginate(25);
```
- Eager loading ini membatasi jumlah query database menjadi tetap ($O(1)$) terlepas dari berapa banyak produk yang ditampilkan di halaman (menghindari 25-50 query terpisah untuk unit, kategori, dan foto).

---

## 3. Strategi Pengelolaan Transaksi Finansial & HPP
- Perhitungan HPP Resep BOM (`RecipeBOM`) dilakukan secara terkomputasi dan di-cache pada kolom `cost_models.total_cost` dan `products.base_cost`.
- Dengan demikian, kalkulasi agregasi bahan baku tidak perlu dihitung ulang setiap kali produk dipanggil oleh kasir POS, menjaga responsivitas transaksi kasir di bawah 50 milidetik.
