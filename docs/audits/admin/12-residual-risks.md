# Residual Risks & Operational Maintenance - Modul Platform Backoffice & Superadmin (`admin`)

> Modul: `admin` | Commit: `a17c5ae` | Siklus Tinjauan: Triwulanan (Quarterly Review)

---

## 1. Daftar Risiko Residual yang Diterima (*Accepted Residual Risks*)

Meskipun seluruh kerentanan utama dan galat fungsional telah dituntaskan 100%, sistem backoffice tetap memiliki beberapa risiko residual operasional berderajat rendah (*Low Residual Risks*) yang perlu dipantau secara berkala oleh tim operasional:

| ID Risiko | Area Risiko | Deskripsi Risiko Residual | Tingkat Risiko | Tindakan Pemantauan Berkala |
| :---: | :--- | :--- | :---: | :--- |
| **RES-01** | Penyimpanan | Akumulasi berkas log Laravel lama pada direktori `storage/logs/` dan tabel `error_logs`. | **LOW** | Menjadwalkan tugas cron otomatis pembersihan log yang berusia lebih dari 60 hari. |
| **RES-02** | Integrasi API | Deprecasi versi Meta WhatsApp Graph API oleh Facebook Developer setiap 2 tahun sekali. | **LOW** | Memeriksa changelog Graph API Meta setiap awal kuartal dan memperbarui variabel environment. |
| **RES-03** | Keamanan Akun | Kecerobohan kata sandi administrator (kata sandi lemah atau dibagikan ke staf lain). | **LOW** | Menegakkan kebijakan penggantian kata sandi berkala dan audit log login pada `/admin/error-logs`. |
| **RES-04** | Latensi Gateway | Lonjakan latensi pada server gerbang pembayaran TriPay saat jam sibuk transaksi nasional. | **LOW** | Batas waktu (*timeout*) HTTP client disetel pada batas aman 5 detik dengan pesan penanganan fallback yang informatif. |

---

## 2. Jadwal Pemeliharaan Berkala (*Maintenance Cadence*)

1. **Pemeriksaan Mingguan:**
   - Meninjau ringkasan log galat di menu `/admin/error-logs` untuk mendeteksi anomali kueri atau bug yang tidak terduga.
   - Memeriksa antrean permohonan pemulihan akun di `/admin/account-recoveries` agar waktu tunggu penyewa di bawah 24 jam.
2. **Pemeriksaan Bulanan:**
   - Melakukan uji coba diagnostik kirim email (SMTP) dan kirim pesan WhatsApp di `/admin/settings`.
   - Memeriksa integritas kuota token AI dan saldo API pada penyedia model.
3. **Pemeriksaan Triwulanan:**
   - Rotasi token akses Meta WhatsApp Cloud API dan kunci rahasia TriPay.
   - Audit daftar akun administrator aktif di `/admin/users` dan mencabut akses staf yang telah mutasi atau purna tugas.
