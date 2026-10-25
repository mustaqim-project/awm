# Panduan Pengguna: Pusat Otorisasi Dokumen (MAR)

> Modul: approvals | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Selamat datang di panduan resmi penggunaan Pusat Otorisasi Dokumen (*Maker, Approver, Releaser* - MAR) di Cooca. Fitur ini dirancang khusus untuk membantu pemilik bisnis (Business Owner) dan tim manajemen mengendalikan pengeluaran keuangan, pengadaan barang, dan penyesuaian stok gudang agar tidak terjadi pemborosan atau kebocoran dana (*fraud*).

---

## 1. Peta Navigasi & Tautan Menu

Anda dapat mengakses fitur otorisasi melalui menu navigasi utama atau tautan langsung berikut:

| Nama Layanan | Alamat Akses (URL) | Kegunaan Utama | Hak Akses Minimal |
| :--- | :--- | :--- | :--- |
| **[Kotak Masuk Persetujuan](/approvals)** | `/approvals` | Melihat daftar dokumen transaksi yang sedang menunggu persetujuan tindakan Anda. | Supervisor / Manager / Owner |
| **[Riwayat Otorisasi](/approvals/history)** | `/approvals/history` | Memeriksa arsip audit trail dokumen yang telah disetujui atau ditolak di masa lalu. | Supervisor / Manager / Owner |
| **[Aturan Plafon Approval](/settings/approval-rules)** | `/settings/approval-rules` | Mengatur batas nominal (plafon) transaksi dan menentukan siapa yang berhak menyetujui. | Owner / Admin |

---

## 2. Mengenal Konsep MAR (Maker, Approver, Releaser)

Tata kelola Cooca membagi alur transaksi menjadi tiga peran penting:
1. **Pembuat Draf (*Maker*):** Staf operasional (misalnya staf gudang atau kasir) yang membuat pesanan pengadaan (*Purchase Order*) atau mencatat pengeluaran kas operasional.
2. **Penyetuju (*Approver*):** Pimpinan tingkat pertama atau menengah (Supervisor / Manager) yang memeriksa kewajaran kebutuhan dan kesesuaian anggaran.
3. **Pelepas / Pengesah Akhir (*Releaser*):** Pemilik toko (*Owner*) atau Direktur yang mengesahkan pencairan dana untuk transaksi dalam jumlah besar.

> **Prinsip Anti-Kecurangan (Segregation of Duties):**
> Staf yang membuat draf transaksi dilarang keras menyetujui dokumen buatannya sendiri. Sistem Cooca secara otomatis mengunci tombol persetujuan bagi pembuat draf, sehingga setiap pengeluaran uang wajib diperiksa oleh pihak kedua. Khusus bagi pemilik bisnis tunggal (*Owner*), sistem memberikan hak pengecualian otomatis demi kelancaran operasional.

---

## 3. Langkah Praktis Penggunaan

### A. Cara Menyetujui Dokumen
1. Buka halaman **[Kotak Masuk Persetujuan](/approvals)**.
2. Anda akan melihat kartu-kartu permohonan yang dikelompokkan berdasarkan jenis dokumen:
   - **Purchase Order (PO):** Pengadaan barang ke supplier.
   - **Biaya Kas (Expense):** Biaya listrik, sewa, operasional toko.
   - **Faktur Supplier:** Tagihan jatuh tempo dari pemasok.
   - **Penyesuaian Stok:** Koreksi selisih barang gudang.
3. Klik tombol **Buka Dokumen Asli** jika ingin melihat rincian nota atau pesanan secara lengkap.
4. Jika data sudah sesuai, klik tombol hijau **Setujui (Level X)**.
5. Pada jendela konfirmasi yang muncul, Anda dapat menyertakan catatan persetujuan (opsional), lalu klik **Konfirmasi Setujui**.

### B. Cara Menolak Dokumen
1. Jika pengeluaran dirasa tidak wajar, melebihi kuota, atau salah hitung, klik tombol merah **Tolak**.
2. Masukkan alasan penolakan pada kolom yang tersedia (wajib diisi minimal 3 karakter, contoh: *"Harga satuan terlalu tinggi, tolong cari supplier alternatif"*).
3. Klik **Tolak Dokumen**. Dokumen akan langsung dibatalkan dan alasan penolakan Anda akan tercatat rapi di log audit.

### C. Cara Mengatur Aturan Plafon Nominal
1. Masuk ke menu **[Aturan Plafon Approval](/settings/approval-rules)**.
2. Klik tombol biru **Tambah Aturan Baru**.
3. Pilih jenis dokumen (contoh: *Purchase Order*).
4. Tentukan batas nominal:
   - **Nominal Minimal:** Contoh Rp 5.000.000.
   - **Nominal Maksimal:** Kosongkan jika berlaku tanpa batas atas.
5. Tentukan jumlah tingkat persetujuan:
   - **1 Tingkat:** Cukup disetujui Supervisor.
   - **2 Tingkat:** Membutuhkan persetujuan Supervisor (Level 1) kemudian Manager (Level 2).
   - **3 Tingkat:** Membutuhkan Supervisor (Level 1), Manager (Level 2), dan Owner (Level 3).
6. Klik **Simpan Aturan**.

---

## 4. Terakhir Diverifikasi Terhadap Route
- `GET /approvals` -> `route('approvals.inbox')` (Terverifikasi aktif)
- `GET /approvals/history` -> `route('approvals.history')` (Terverifikasi aktif)
- `GET /settings/approval-rules` -> `route('approval-rules.index')` (Terverifikasi aktif)
