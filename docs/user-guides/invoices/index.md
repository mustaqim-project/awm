# Panduan Pengguna: Modul Faktur Penjualan & Piutang Dagang (Invoices)

Selamat datang di Panduan Pengguna Modul Faktur Penjualan & Piutang Dagang (*Invoices & Accounts Receivable*) COOCA. Modul ini dirancang khusus bagi pemilik usaha (**Business Owner**), manajer keuangan, dan admin penjualan untuk menerbitkan dokumen tagihan komersial resmi, mengonversi pesanan pembelian (*Purchase Order*), mengontrol pelepasan stok barang, memantau umur piutang, mencatat pembayaran bertahap, serta mengirimkan pengingat jatuh tempo otomatis kepada pelanggan.

---

## 1. Ikhtisar Arsitektur Modul Faktur

Modul Faktur Penjualan terintegrasi erat dengan modul inventori gudang dan sistem akuntansi keuangan:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   COOCA INVOICES & RECEIVABLES SYSTEM                  │
├───────────────────────────────────┬────────────────────────────────────┤
│ 1. Penerbitan & Dokumen Komersial │ 2. Pengendalian Stok & Piutang     │
├───────────────────────────────────┼────────────────────────────────────┤
│ • Faktur Langsung dari Produk     │ • Konfirmasi & Rilis Stok Gudang   │
│ • Konversi dari Customer PO       │ • Jurnal Piutang Dagang Otomatis   │
│ • Estimasi Margin Kalkulator HPP  │ • Monitoring Umur Piutang (Aging)  │
│ • Cetak Faktur PDF Standar A4     │ • Pencatatan Cicilan / Pelunasan   │
│ • Format Pajak PPN & Ongkir       │ • Pengingat Jatuh Tempo WhatsApp   │
│ • Pengendalian Void Berizin       │ • Ekspor Excel XLSX & CSV Sanitasi │
└───────────────────────────────────┴────────────────────────────────────┘
```

---

## 2. Navigasi & Tautan Menu Resmi

Sebagai pemilik bisnis, Anda dapat mengakses seluruh fungsionalitas modul faktur melalui tautan resmi berikut:

| Nama Layanan / Fitur | Tautan Langsung | Izin Akses Minimal | Deskripsi Fungsional |
|---|---|---|---|
| **Hub Faktur & Dashboard** | [/invoices](/invoices) | `invoices.view` | Dashboard ringkasan Bento keuangan (Total Difakturkan, Diterima, Piutang, Estimasi Laba) dan tabel riwayat faktur. |
| **Buat Faktur Baru** | [/invoices/create](/invoices/create) | `invoices.create` | Formulir penerbitan faktur baru dari katalog produk, konversi PO pelanggan, atau kalkulator HPP. |
| **Ekspor Laporan Excel** | [/invoices/export-excel](/invoices/export-excel) | `invoices.export` | Unduh rekapitulasi lengkap faktur penjualan dalam format Microsoft Excel (.xlsx) dengan proteksi Formula Injection. |
| **Ekspor Laporan CSV** | [/invoices/export-excel?format=csv](/invoices/export-excel?format=csv) | `invoices.export` | Unduh data faktur dalam format teks CSV ber-BOM UTF-8 untuk impor ke aplikasi akuntansi pihak ketiga. |
| **Pelanggan & CRM** | [/customers](/customers) | `customers.view` | Manajemen data pelanggan B2B, penetapan limit kredit, dan termin pembayaran default (*Net 30, COD*). |
| **Pesanan Pelanggan (PO)** | [/purchase-orders](/purchase-orders) | `purchasing.view` | Daftar purchase order dari pelanggan yang siap diterbitkan menjadi faktur penjualan. |
| **Buku Kas & Bank** | [/finance/cash-bank](/finance/cash-bank) | `finance.cash_bank` | Pemantauan mutasi saldo kas dan rekening bank penerima pelunasan faktur. |
| **Jejak Audit Keamanan** | [/audit-logs](/audit-logs) | `audit.view` | Pemantauan catatan aktivitas pembuatan, pengeditan, konfirmasi, pembatalan (void), dan pelunasan faktur. |

---

## 3. Alur Kerja Harian Pemilik Usaha

### A. Menerbitkan Faktur Baru
1. Masuk ke halaman [Buat Faktur Baru](/invoices/create).
2. Tentukan pihak transaksi:
   - Pilih **Nama Pelanggan** dari database atau buat pelanggan baru.
   - Tentukan **Lokasi Gudang** tempat barang akan dikeluarkan saat faktur dirilis.
   - *(Opsional)* Jika menagih pesanan pelanggan, pilih nomor referensi **Purchase Order (PO)** untuk mengisi seluruh baris barang secara otomatis.
3. Masukkan nomor faktur (atau gunakan nomor otomatis sistem berurutan).
4. Tentukan **Tanggal Faktur** dan **Tanggal Jatuh Tempo** (misal termin pembayaran *Net 30* atau *Cash on Delivery*).
5. Pada tabel rincian item:
   - Pilih barang dari daftar produk atau ketik manual nama jasa/pekerjaan.
   - Masukkan kuantitas, satuan, harga jual satuan, dan persentase/nominal diskon item.
6. Tentukan diskon global, tarif PPN (misal 11%), dan ongkos kirim jika ada.
7. Pilih status awal:
   - **Simpan sebagai Draf:** Jika faktur masih dinegosiasikan atau menunggu persetujuan pelanggan (stok gudang belum terpotong).
   - **Terbitkan Sekarang (Sent/Unpaid):** Faktur langsung berstatus aktif dan siap dikirimkan.
8. Klik **Terbitkan Faktur**. Sistem menyimpan transaksi dan otomatis mencatat jejak audit `invoices.invoice_created`.

### B. Mengonfirmasi & Merilis Faktur (Potong Stok & Jurnal Piutang)
1. Buka faktur berstatus draf melalui halaman [Daftar Faktur](/invoices).
2. Periksa kesesuaian kuantitas barang dan gudang pengeluaran.
3. Klik tombol **Konfirmasi & Rilis Faktur**.
4. Sistem mengecek ketersediaan fisik barang di lokasi gudang. Jika stok mencukupi, sistem akan:
   - Memotong kuantitas stok produk di gudang secara otomatis.
   - Mencatat mutasi kartu stok keluar.
   - Membentuk jurnal akuntansi piutang dagang dan pendapatan penjualan.
   - Mencatat log audit berisiko sedang `invoices.invoice_confirmed`.

### C. Mencatat Pembayaran / Pelunasan Bertahap
1. Buka detail faktur yang ditagihkan.
2. Pada bagian bawah halaman, klik tombol **Catat Pembayaran**.
3. Masukkan nominal uang yang diterima:
   - **Pembayaran Penuh (Lunas):** Masukkan nominal sesuai sisa saldo tagihan (`balance_due`). Status faktur otomatis berubah menjadi `PAID` (Lunas).
   - **Pembayaran Bertahap (Cicilan / Uang Muka):** Masukkan nominal yang disetor. Status faktur berubah menjadi `PARTIALLY_PAID` dan saldo piutang berkurang secara real-time.
4. Pilih metode pembayaran: Transfer Bank, Tunai (Cash), QRIS, Kartu Kredit, atau Cek/Giro.
5. Masukkan nomor referensi bank (misal: nomor mutasi BCA/Mandiri) untuk rekonsiliasi.
6. Klik **Simpan Pembayaran**. Jejak audit `invoices.payment_recorded` tersimpan permanen.

### D. Mengirim Pengingat Jatuh Tempo (Term Reminder)
1. Untuk faktur aktif yang mendekati atau telah melampaui tanggal jatuh tempo, buka detail faktur.
2. Klik tombol **Kirim Pengingat Termin**.
3. Pilih saluran komunikasi: WhatsApp, Email, atau Keduanya.
4. Tuliskan pesan tambahan opsional jika diperlukan.
5. Klik **Kirim Pengingat**. Sistem menyusun tautan pesan WhatsApp resmi yang mencantumkan nomor faktur, sisa saldo tagihan, dan batas jatuh tempo langsung ke nomor telepon PIC pelanggan.

### E. Pembatalan Faktur Resmi (VOID)
1. Jika terjadi pembatalan pesanan atau perselisihan komersial pada faktur yang telah dirilis:
   - Faktur yang sudah terbit **dilarang dihapus** demi menjaga integritas pembukuan akuntansi.
   - Buka halaman faktur dan klik tombol **Batalkan Faktur (VOID)**.
2. Masukkan alasan pembatalan secara tertulis (misal: "Barang ditolak oleh pelanggan karena spesifikasi tidak cocok").
3. Klik **Konfirmasi VOID**.
4. Sistem otomatis:
   - Mengembalikan kuantitas barang ke stok gudang asal.
   - Membuat jurnal pembalik pembukuan.
   - Mengubah status faktur menjadi `VOID`.
   - Mencatat log keamanan berisiko tinggi `invoices.invoice_voided`.

---

## 4. Matriks Implementasi Lintas 20 Sektor Industri

| No | Sektor Industri | Skenario Penggunaan Faktur | Fitur Kunci yang Digunakan |
|---|---|---|---|
| 1 | **Kuliner (Restoran/Kafe/Katering)** | Penagihan katering korporasi, pesanan prasmanan kantor, dan *event organizer*. | Termin *Net 14*, pemisahan ongkir pengiriman katering, uang muka 50%. |
| 2 | **Retail & Minimarket** | Pasokan grosir ke warung mitra (*reseller / agen ranting*). | Konversi dari Customer PO, diskon kuantitas grosir, cetak surat jalan lampiran. |
| 3 | **Fashion, Butik & Distro** | Penjualan konsinyasi ke departemen store atau distro rekanan. | Termin konsinyasi bulanan, rincian varian ukuran/warna, pengingat WhatsApp. |
| 4 | **Jasa Bengkel & Reparasi** | Faktur gabungan penggantian suku cadang dan ongkos jasa montir. | Item produk (sparepart) + Item manual (jasa perbaikan), garansi nota A4. |
| 5 | **Percetakan & Advertising** | Tagihan cetak spanduk, kalender, merchandise, dan billboard promosi. | Uang muka draf 50%, lampiran berkas desain, pelunasan saat serah terima. |
| 6 | **Klinik Kesehatan & Farmasi** | Tagihan pengadaan obat dan resep massal ke instansi / perusahaan rekanan. | Validasi termin korporat, pencatatan batch exp date pada catatan faktur. |
| 7 | **Kontraktor & Toko Material** | Penagihan bahan bangunan proyek per termin (*progres fisik lapangan 30%, 70%, 100%*). | Termin bertahap, pemilihan gudang material cabang, catatan spesifikasi truk. |
| 8 | **Agribisnis & Peternakan** | Pasokan hasil panen, beras, atau daging potong ke hotel, restoran, dan pasar induk. | Bobot timbangan dinamis (satuan Kg/Ton), penyesuaian susut pada diskon item. |
| 9 | **Grosir & Distributor B2B** | Distribusi barang konsumsi (FMCG) dengan ratusan toko langganan. | Ekspor Excel laporan umur piutang, pelunasan transfer bank massal, limit kredit. |
| 10 | **Ekspedisi & Logistik** | Penagihan ongkos angkut armada truk dan jasa pergudangan per bulan. | Lampiran nomor resi pengiriman / surat jalan pada `attachment_info`. |
| 11 | **Konveksi & Garmen** | Penagihan pesanan seragam kantor, jaket komunitas, atau pakaian militer. | Konversi dari PO tender, kalkulasi HPP bahan kain + jasa potong jahit. |
| 12 | **Salon Kecantikan & Barbershop** | Penjualan produk perawatan rambut (*hair care*) partai besar ke reseller salon. | Diskon paket bundle, pengingat jatuh tempo berkala ke reseller. |
| 13 | **Car Wash & Auto Detailing** | Tagihan paket perawatan kendaraan dinas instansi atau rental mobil bulanan. | Faktur bulanan akumulatif dengan lampiran nomor pelat armada kendaraan. |
| 14 | **Event Organizer & Dokumentasi** | Penagihan paket pernikahan, seminar, dan jasa fotografer/videografer. | Termin uang muka (DP), termin pelunasan H-1 acara, cetak faktur elegan. |
| 15 | **Bimbel, Kursus & Pendidikan** | Penagihan biaya pelatihan karyawan perusahaan (*corporate training*). | Pajak PPh/PPN jasa pelatihan, faktur resmi ber-kop bisnis untuk klaim HRD. |
| 16 | **FnB Katering Diet & Bento** | Tagihan langganan katering harian pekerja kantor per 2 minggu / 1 bulan. | Rekap kuantitas boks mingguan, pembayaran digital via QRIS / Bank Transfer. |
| 17 | **Gadget & Servis Elektronik** | Penjualan unit laptop, komputer kantor, dan jasa pemeliharaan IT tahunan. | Rincian nomor seri unit (SKU) pada deskripsi item, syarat garansi tertulis. |
| 18 | **Laundry Kiloan & Satuan** | Penagihan jasa pencucian linen dan seragam hotel melati atau guest house. | Satuan Kilogram/Pcs, penagihan terjadwal tiap akhir pekan (*weekend invoice*). |
| 19 | **Pet Shop & Klinik Hewan** | Pasokan pakan hewan ternak/peliharaan ke breeder atau sub-distributor. | Termin COD (*Cash on Delivery*), diskon volume karungan, cetak faktur thermal/A4. |
| 20 | **Ekspor & Impor (Forwarding)** | Penagihan kontainer ekspor komoditas, bea masuk, dan *handling fee*. | Multi-mata uang, snapshot kurs valas, klausul termin perdagangan internasional. |

---

## 5. Tautan Dokumen Pendukung
- [Panduan Penyelesaian Masalah (Troubleshooting)](troubleshooting.md)
- [Pertanyaan yang Sering Diajukan (FAQ)](faq.md)
- [Dokumen Audit Teknis Modul Invoices](../../audits/invoices/00-executive-summary.md)
