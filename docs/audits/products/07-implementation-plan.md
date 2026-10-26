# Implementation & Remediation Plan: Modul Produk

| Metadata | Nilai |
| :--- | :--- |
| **Modul** | Products |
| **Strategi** | Code-first In-place Hardening & Comprehensive Regression Testing |
| **Status Eksekusi** | **Selesai 100% (Completed)** |

---

## 1. Rencana Tahapan Eksekusi (Phase Strategy)

### Fase 1: Hardening Keamanan & Controller RBAC (P0)
1. Buka `app/Http/Controllers/Web/ProductCategoryWebController.php` dan implementasikan `HasMiddleware`.
2. Pasang middleware `require.permission:master_data.product_categories.manage` untuk aksi `store`, `update`, dan `destroy`.
3. Tambahkan validasi eksplisit kepemilikan tenant: `abort_unless($category->business_id === $business->id, 403);`.
4. Buka `app/Http/Controllers/Web/MasterDataWebController.php` dan terapkan `HasMiddleware` dengan proteksi permission view untuk kategori produk, kategori material, dan satuan unit.
5. Daftarkan event pencatatan `AuditLog` pada operasi mutasi kategori.

### Fase 2: Standardisasi Desain UI/UX & Apple HIG (P1)
1. Pindai seluruh template Blade di `resources/views/app/products/` terhadap emoji dan touch target sub-36px.
2. Ganti karakter Unicode `💡` dengan ikon SVG `<i data-lucide="lightbulb" ...></i>`.
3. Sesuaikan kelas utility Tailwind dari `h-8` menjadi `min-h-[36px] px-3.5 rounded-[9px]`.

### Fase 3: Pembuatan Test Suite & Otomasi Verifikasi (P1)
1. Buat test feature komprehensif `tests/Feature/ProductsModuleAuditTest.php`.
2. Uji 15 kasus pengujian yang mencakup autentikasi, permission redirect / 403, isolasi IDOR, kalkulasi HPP, dan manajemen kategori.
3. Jalankan PHPUnit hingga seluruh assertion hijau (0 failure, 0 error).

### Fase 4: Validasi Cache & Dokumentasi Lengkap (P2)
1. Jalankan `php artisan view:clear` dan `php artisan view:cache` untuk memastikan tidak ada sintaks Blade yang rusak.
2. Susun dokumentasi audit mendalam (`docs/audits/products/`) dan panduan pemilik bisnis (`docs/user-guides/products/`).
