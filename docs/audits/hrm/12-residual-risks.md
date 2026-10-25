# Analisis Risiko Residual & Rekomendasi Jangka Panjang - HRM

> Modul: Sumber Daya Manusia & Penggajian (HRM & Payroll) | Tanggal: 2026-10-25

## 1. Identifikasi Risiko Residual (*Residual Risks*)
Meskipun seluruh kerentanan kode, otorisasi RBAC, injeksi formula spreadsheet, dan jejak audit telah dimitigasi secara penuh, terdapat beberapa risiko residual operasional yang berada di luar kendali murni aplikasi web:

### A. Ketergantungan Akurasi Sensor GPS & Kamera Perangkat Staf
- **Deskripsi:** Presensi geofencing dan biometrik wajah mengandalkan sinyal GPS dan kamera depan ponsel karyawan. Pada ruangan tertutup (*indoor mall/basement*), sinyal satelit GPS dapat mengalami deviasi (*drift*) hingga puluhan meter.
- **Dampak:** Staf yang secara fisik berada di toko mungkin terdeteksi berada sedikit di luar radius toleransi geofence.
- **Rekomendasi Mitigasi Operasional:** Owner disarankan mengatur radius geofence yang realistis (antara 50 s/d 100 meter untuk outlet dalam gedung) dan memanfaatkan fitur pengajuan koreksi presensi (*attendance corrections*) untuk kejadian khusus.

### B. Penerusan (*Forwarding*) Tautan Slip Gaji Digital oleh Staf
- **Deskripsi:** Slip gaji digital dapat diakses via token publik aman 48-karakter tanpa perlu login, memudahkan pengiriman via WhatsApp. Namun, jika karyawan sendiri secara sengaja meneruskan tautan tersebut kepada pihak ketiga, rincian slip dapat terbaca.
- **Rekomendasi Peningkatan Jangka Panjang:** Menambahkan opsi autentikasi sekunder pada pembukaan slip publik, misalnya memasukkan 4-digit tanggal lahir (DDMM) atau 4-digit terakhir nomor WhatsApp karyawan.

### C. Variasi Format Berkas Transfer Korporat Antar-Bank
- **Deskripsi:** Format CSV transfer massal bank (BCA KlikBisnis, Mandiri MCM, BRI Cash Management) dapat mengalami penyesuaian tata letak kolom oleh pihak bank dari waktu ke waktu.
- **Rekomendasi Peningkatan Jangka Panjang:** Menyediakan modul pengaturan template kolom ekspor bank yang dapat disesuaikan (*configurable export mapping*) oleh akuntan bisnis.

---

## 2. Rangkuman Penilaian Akhir
Modul HRM COOCA telah memenuhi standar keamanan, keandalan, dan ergonomi enterprise-grade. Risiko residual yang tersisa berada pada level rendah (*Low / Acceptable Operational Risk*) dan dapat dikelola dengan SOP operasional standar.
