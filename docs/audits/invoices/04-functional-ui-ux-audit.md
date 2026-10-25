# Functional & UI/UX Audit - Modul Faktur Penjualan & Piutang

> Modul: Invoices & Accounts Receivable | Tanggal Audit: 2026-10-26 | Target: `resources/views/app/invoices`

## 1. Penilaian Kepatuhan Apple Human Interface Guidelines (HIG)
Pemeriksaan antarmuka dilakukan pada 5 berkas tampilan Blade dengan standar Apple HIG:
- **Ukuran Touch Target (Ergonomi Jari):** Seluruh elemen interaktif (tombol aksi, input field, dropdown seleksi, pagination) memiliki tinggi dan lebar area sentuh minimum 36px hingga 44px (`h-9`, `h-10`, `h-11`, `py-2.5 px-4`). Tidak ada tombol yang terlalu rapat atau sulit ditekan di layar sentuh perangkat mobile/tablet.
- **Hierarki Tipografi & Spacing:** Menggunakan skala teks `text-xs font-semibold uppercase tracking-wider` untuk label metadata dan header kolom, serta `text-sm font-medium` untuk data transaksi.
- **Kartu Metrik Bento Grid:** Halaman `index.blade.php` menyajikan 4 kartu metrik keuangan:
  1. *Total Difakturkan:* Total omzet komersial yang telah diterbitkan.
  2. *Total Diterima:* Realisasi kas masuk yang telah berhasil ditagih.
  3. *Total Piutang Usaha (Outstanding):* Saldo piutang aktif yang harus ditagih.
  4. *Laba Kotor Riil:* Estimasi profitabilitas berdasarkan kalkulasi HPP produk.
- **Zero Unicode Emoji Rule:** Dikonfirmasi dengan skrip scanner `scan_invoices.py`, **0 emoji Unicode** ditemukan. Seluruh ikon visual menggunakan SVG Lucide yang konsisten dan profesional lintas platform (iOS, Android, macOS, Windows).

---

## 2. Hasil Audit i18n & Lokalisasi Multi-Bahasa
Pemeriksaan menyeluruh dilakukan menggunakan skrip `scan_invoices_i18n.py` berbasis AST regex:

| Berkas Tampilan | Jumlah Panggilan i18n | Temuan String Hardcoded | Status Kepatuhan |
|---|---|---|---|
| `resources/views/app/invoices/index.blade.php` | 84 | 0 | 100% Clean |
| `resources/views/app/invoices/create.blade.php` | 108 | 0 (Setelah Remediasi) | 100% Clean |
| `resources/views/app/invoices/edit.blade.php` | 76 | 0 (Setelah Remediasi) | 100% Clean |
| `resources/views/app/invoices/show.blade.php` | 82 | 0 (Setelah Remediasi) | 100% Clean |
| `resources/views/app/invoices/print.blade.php` | 8 | 0 | 100% Clean |
| **Total** | **358 Panggilan** | **0 Isu** | **100% Patuh** |

Kunci kamus translasi simetris ditambahkan pada `lang/id/sales.php` dan `lang/en/sales.php`:
- `sales.invoices.payment_terms_placeholder_short`: `'Net 30 / COD'`
- `sales.invoices.attachment_info_label`: `'Lampiran'` / `'Attachment'`
- `sales.invoices.attachment_info_default`: `'(Default: -)'`
- `sales.invoices.attachment_info_placeholder`: `'Contoh: - atau 1 Berkas / Surat Jalan'` / `'Example: - or 1 Document / Waybill'`
- `sales.invoices.tax_percentage_placeholder`: `'11'`
- `sales.invoices.payment_ref_placeholder`: `'Contoh: TRX-BCA-981203'` / `'Example: TRX-BCA-981203'`
- `sales.invoices.btn_delete_draft`: `'Hapus Draf'` / `'Delete Draft'`
- `sales.invoices.delete_draft_dialog_title`: `'Hapus Draf Faktur?'` / `'Delete Draft Invoice?'`
- `sales.invoices.delete_draft_dialog_prompt`: `'Apakah Anda yakin ingin menghapus draf faktur :number? Tindakan ini tidak dapat dibatalkan.'` / `'Are you sure you want to delete draft invoice :number? This action cannot be undone.'`

---

## 3. Pencegahan Kesalahan Manusia (Human Error Prevention)
1. **Konfirmasi Penghapusan Draf (Double Confirmation Modal):** Tombol hapus draf faktur tidak mengeksekusi penghapusan instan; modal konfirmasi Apple HIG muncul dengan menampilkan nomor faktur dan peringatan bahwa tindakan tidak dapat dibatalkan.
2. **Penguncian Faktur yang Memiliki Pembayaran:** Tombol edit otomatis disembunyikan dan di-disable jika faktur telah memiliki riwayat cicilan/pembayaran tercatat untuk mencegah manipulasi saldo piutang.
3. **Peringatan Stok Kurang saat Konfirmasi:** Jika stok produk di gudang tidak mencukupi saat faktur hendak dirilis, sistem menampilkan notifikasi kesalahan yang jelas dan mencegah pemotongan stok minus.
4. **Validasi Tanggal Jatuh Tempo:** Input tanggal jatuh tempo divalidasi tidak boleh lebih awal dari tanggal faktur.
