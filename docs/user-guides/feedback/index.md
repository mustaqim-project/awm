# Panduan Pengguna: Pusat Masukan & Bantuan Produk (Platform Feedback)

> Modul: Masukan & Bantuan Produk (`feedback`) | Tanggal Pembaruan: 2026-10-25 | Status: Resmi

Selamat datang di panduan resmi penggunaan modul **Pusat Masukan & Bantuan Produk (Platform Feedback & Support)** di Cooca. Fitur ini dirancang khusus bagi pemilik usaha (Business Owner) untuk berkomunikasi langsung dengan tim pengembang Cooca dalam melaporkan kendala teknis (bug) serta mengajukan usulan fitur baru yang dibutuhkan untuk kemajuan bisnis Anda.

---

## 1. Peta Navigasi & Tautan Menu

Anda dapat mengakses fitur masukan platform melalui menu navigasi utama atau tautan langsung berikut:

| Nama Layanan | Alamat Akses (URL) | Kegunaan Utama | Hak Akses Minimal |
| :--- | :--- | :--- | :--- |
| **[Daftar Laporan Bug Kendala](/feedback/bugs)** | `/feedback/bugs` | Memantau seluruh tiket kendala teknis atau error sistem yang pernah Anda laporkan beserta progres penyelesaiannya oleh tim developer. | Pemilik Usaha (`Owner`) |
| **[Laporkan Kendala Bug Baru](/feedback/bugs/create)** | `/feedback/bugs/create` | Mengirimkan laporan kendala baru, langkah replikasi, dan bukti tangkapan layar (screenshot/log). | Pemilik Usaha (`Owner`) |
| **[Daftar Usulan Fitur Baru](/feedback/features)** | `/feedback/features` | Melihat daftar ide fitur yang pernah Anda ajukan serta status peninjauannya oleh tim produk Cooca. | Pemilik Usaha (`Owner`) |
| **[Ajukan Usulan Fitur Baru](/feedback/features/create)** | `/feedback/features/create` | Mengajukan permohonan penambahan fitur baru beserta nilai manfaat bisnis (*business value*) bagi toko Anda. | Pemilik Usaha (`Owner`) |

---

## 2. Fitur Utama & Alur Penggunaan

### A. Cara Melaporkan Kendala Teknis / Bug Sistem
1. Buka halaman **[Laporkan Kendala Bug Baru](/feedback/bugs/create)**.
2. Lengkapi formulir pelaporan dengan detail yang jelas:
   - **Judul Laporan:** Ringkasan kendala yang dialami (contoh: *"Gagal Cetak Struk Kasir via Bluetooth Printer"*).
   - **Kategori:** Pilih kategori terkait (misal: *POS/Kasir*, *Inventori*, *Keuangan*, *Kinerja/Performa*, atau *Keamanan*).
   - **Tingkat Keparahan (Severity):**
     - **Low:** Kendala minor pada tampilan/teks tanpa mengganggu operasional.
     - **Normal:** Fitur terganggu tetapi ada jalan keluar sementara.
     - **High:** Fitur utama terganggu dan menghambat operasional toko.
     - **Critical:** Sistem terhenti total atau data transaksi tidak tersimpan.
   - **Deskripsi Masalah:** Jelaskan secara rinci apa yang terjadi.
   - **Langkah-langkah Replikasi:** Urutkan langkah yang Anda lakukan sebelum error muncul (contoh: *"1. Buka menu POS, 2. Masukkan produk ke keranjang, 3. Klik tombol bayar"*).
   - **Hasil yang Diharapkan vs Hasil Aktual:** Tuliskan apa yang seharusnya terjadi dan apa yang sebenarnya terjadi di layar Anda.
   - **Lampiran Berkas (Opsional):** Unggah tangkapan layar (*screenshot*), foto struk, atau dokumen error pendukung (format JPG, PNG, WEBP, atau PDF hingga 5 MB).
3. Klik tombol **Kirim Laporan Bug**.
4. Tiket Anda akan otomatis terbit dan langsung masuk ke dashboard pemantauan teknis pengembang Cooca.

### B. Cara Memantau Progres Perbaikan Tiket
1. Kunjungi menu **[Daftar Laporan Bug Kendala](/feedback/bugs)**.
2. Kartu tiket Anda menampilkan status tahapan pengerjaan:
   - **Open (Terbuka):** Tiket baru diterima oleh sistem.
   - **Triaged (Ditelaah):** Tim teknis telah memverifikasi laporan Anda.
   - **In Progress (Sedang Dikerjakan):** Tim pengembang sedang memperbaiki kode program.
   - **Resolved (Selesai Diperbaiki):** Perbaikan telah diuji dan dirilis ke sistem.
   - **Closed (Ditutup):** Masalah telah terselesaikan sepenuhnya.
3. Klik pada salah satu kartu tiket untuk melihat linimasa percakapan, catatan perkembangan, dan persentase progres penyelesaian dari teknisi Cooca.

### C. Cara Mengajukan Usulan Fitur Baru (*Feature Request*)
1. Buka halaman **[Ajukan Usulan Fitur Baru](/feedback/features/create)**.
2. Isi formulir permohonan fitur:
   - **Judul Fitur:** Nama usulan fitur (contoh: *"Integrasi Pembayaran QRIS Dinamis Per Meja"*).
   - **Kategori Modul:** Pilih modul terkait (contoh: *Penjualan*, *Inventori*, *Akuntansi*, atau *Integrasi Eksternal*).
   - **Skala Prioritas:** Tentukan urgensi kebutuhan (*Low*, *Normal*, atau *High*).
   - **Manfaat untuk Bisnis (*Business Value*):** Jelaskan bagaimana fitur ini dapat menghemat waktu, mencegah kebocoran kas, atau meningkatkan omzet toko Anda.
   - **Kasus Penggunaan (*Use Case*):** Contoh skenario nyata di toko Anda saat fitur ini digunakan oleh kasir atau manajer.
3. Klik tombol **Kirim Request Fitur**. Usulan Anda akan dipelajari oleh tim perancang produk Cooca untuk dimasukkan ke dalam peta jalan pembaruan sistem (*product roadmap*).

---

## 3. Matriks Hak Akses Karyawan (RBAC)

Fitur masukan dan laporan teknis dibatasi secara ketat hanya untuk pemilik usaha (*Owner*) demi menjaga kerahasiaan operasional bisnis:

| Peran (Role) | Hak Akses Modul Feedback | Tindakan yang Diizinkan |
| :--- | :--- | :--- |
| **Owner / Pemilik Usaha** | Akses Penuh (`require.role:owner`) | Melihat riwayat masukan, melaporkan bug baru, mengunggah bukti kendala, dan mengajukan usulan fitur. |
| **Manager / Supervisor** | Tidak Ada Akses | Mengarahkan kendala kepada pemilik usaha untuk dilaporkan ke saluran resmi Cooca. |
| **Kasir / Staf Operasional** | Tidak Ada Akses | Menginformasikan kendala perangkat kasir kepada pemilik toko. |

---

## 4. Tautan Menu Terverifikasi
- `GET /feedback/bugs` -> `route('feedback.bugs.index')` (Terverifikasi aktif)
- `GET /feedback/bugs/create` -> `route('feedback.bugs.create')` (Terverifikasi aktif)
- `GET /feedback/features` -> `route('feedback.features.index')` (Terverifikasi aktif)
- `GET /feedback/features/create` -> `route('feedback.features.create')` (Terverifikasi aktif)
