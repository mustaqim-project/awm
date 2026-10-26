# Functional & UI/UX Audit: Modul Produk

| Standar UI/UX | Target Kepatuhan | Status Evaluasi |
| :--- | :--- | :--- |
| **Apple HIG Touch Target** | $\ge 36\text{px}$ (Secondary/Icon) / $\ge 44\text{px}$ (Primary Action) | **100% Compliant** |
| **Zero Unicode Emoji** | 0 Unicode Emoji di seluruh Blade templates | **100% Compliant** (0 emoji tersisa) |
| **Responsivitas Layout** | Phone, Tablet POS (768-1024px), Desktop Wide | **Optimal & Bento-grid Styled** |
| **Lokalisasi Dwibahasa** | ID / EN parity 100% tanpa hardcoded text | **100% Symmetrical (354 keys)** |

---

## 1. Pembersihan Emoji & Standardisasi Ikon Lucide SVG
Selama audit awal pada `resources/views/app/products/index.blade.php`, ditemukan sisa karakter emoji dekoratif pada tips panduan modal:
- Baris 1594: Karakter emoji lampu (`💡 Tips:`) di modal varian.
- Baris 2210: Karakter emoji lampu (`💡 Tips:`) di modal marketplace mapping.

**Tindakan Remediasi:**
Kedua karakter tersebut diganti dengan ikon vektor scalable Lucide SVG yang selaras dengan tema sistem:
```html
<i data-lucide="lightbulb" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
```
Hasil scan scanner Python `scratch/scan_products.py` mengonfirmasi: **0 Unicode Emojis ditemukan di seluruh file Blade Produk.**

---

## 2. Hardening Touch Target (Apple Human Interface Guidelines)
Untuk mengakomodasi penggunaan tablet kasir dan smartphone pemilik toko saat berpindah di lantai toko atau gudang:
1. **Shortcut Navigasi & Action Buttons:**
   - Tombol-tombol navigasi internal dan filter kategori sebelumnya berukuran `h-8` ($32\text{px}$).
   - Telah ditingkatkan menjadi `min-h-[36px] px-3.5 py-1.5 rounded-[9px]` dengan area sentuh yang nyaman bagi ibu jari (*thumb-friendly*).
2. **Cockpit Marketplace Shortcut:**
   - Tombol aksi cepat sinkronisasi marketplace di baris 2650 ditingkatkan menjadi `min-h-[36px] px-3.5 rounded-[9px]`.
3. **Form Controls & Checkbox Inputs:**
   - Checkbox visibilitas (POS, Website, Status Aktif) dibungkus dalam container berukuran tinggi minimum $44\text{px}$ dengan label interaktif yang dapat diklik di seluruh area baris.

---

## 3. Komponen Dinamis & State Management
- **Modal Modifiers & Resep BOM:**
  - Form penambahan bahan baku BOM menggunakan dropdown pencarian dinamis dengan validasi input angka positif (`min="0.0001"`).
- **Galeri Foto Produk:**
  - Komponen upload gambar terintegrasi dengan validasi ukuran berkas, pratinjau thumbnail instan, dan penandaan foto utama (*primary cover*).
- **Branch Price Override Drawer:**
  - Menyediakan modal tabel interaktif berbasis Alpine.js yang menampilkan seluruh cabang aktif bisnis beserta input harga khusus per cabang dengan format mata uang otomatis.
