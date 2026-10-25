# Residual Risks - Modul Faktur Penjualan & Piutang

> Modul: Invoices & Accounts Receivable | Tanggal Audit: 2026-10-26 | Target: `resources/views/app/invoices`

## 1. Identifikasi Risiko Sisa (Residual Risks Analysis)

Meskipun seluruh temuan audit utama telah diremediasi, terdapat beberapa risiko sisa operasional dan teknis yang berada di luar kendali langsung modul controller:

### 1. Ketergantungan Eksternal WhatsApp Gateway (WhatsApp API Reliability)
- **Karakteristik:** Fitur *Term Reminder* menggunakan URL WhatsApp web/app untuk mengirimkan tautan penagihan faktur ke nomor telepon pelanggan.
- **Risiko Sisa:** Jika nomor telepon pelanggan tidak valid, tidak memiliki akun WhatsApp, atau jika layanan pihak ketiga mengalami gangguan jaringan, pengiriman pengingat manual dapat tertunda.
- **Monitoring & Mitigasi Berkelanjutan:** Sistem menyediakan fallback saluran Email serta log riwayat pengingat di tabel `customer_term_reminders`.

### 2. Volume Ekspor Laporan Berukuran Sangat Besar (Massive Export Memory Limit)
- **Karakteristik:** Ekspor Excel/CSV saat ini memuat koleksi faktur penjualan menggunakan streaming response.
- **Risiko Sisa:** Bisnis dengan puluhan ribu faktur dalam satu tahun buku berpotensi mengalami keterbatasan memori PHP jika mengekspor seluruh data historis tanpa rentang tanggal (*date range filter*).
- **Rekomendasi Sprint Mendatang:** Menambahkan filter rentang tanggal default (misal: kuartal berjalan atau bulan berjalan) pada tombol ekspor laporan.

### 3. Batas Kuota Entitlement Pelanggan Gratis (Free Plan Quota Limits)
- **Karakteristik:** Fitur pembuatan faktur dilindungi oleh middleware `entitlement:invoice`.
- **Risiko Sisa:** Pemilik bisnis dengan paket langganan Free Solo yang telah mencapai batas kuota bulanan akan menerima respons pembatasan akses.
- **Penanganan:** Pesan antarmuka yang ramah telah disediakan untuk mengarahkan pengguna melakukan upgrade ke paket Pro/Corporate.
