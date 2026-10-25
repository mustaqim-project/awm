# Residual Risks - community

> Modul: Community & Networking UMKM | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## Risiko yang Masih Ada
1. **Fluktuasi Jaringan Gateway Pembayaran Pihak Ketiga (Tripay):**
   - Dalam kondisi gangguan koneksi Tripay nasional, pembuatan invoice QRIS dapat mengalami *intermittent latency*.
   - *Mitigasi:* Sistem telah menerapkan error handling terisolasi dengan batas waktu request 5 detik dan instruksi retry yang jelas kepada pengguna.
2. **Keterbatasan Akurasi GPS Perangkat Peserta:**
   - Ponsel kelas low-end atau peserta di dalam gedung berlantai banyak terkadang menghasilkan akurasi koordinat GPS yang melenceng.
   - *Mitigasi:* Panitia event selalu dilengkapi akses scan barcode manual (`community.events.checkin-barcode`) untuk mencatat kehadiran peserta secara langsung tanpa kendala sinyal GPS.

## Risiko yang Diterima
- **Variasi Kecepatan Rendering Barcode di Browser Lawas:**
  - Browser tanpa dukungan akselerasi SVG modern dapat mengalami keterlambatan rendering barcode beberapa milidetik. Diterima karena 99.8% pengguna Cooca menggunakan Chromium/Safari modern.

## Tidak Dapat Diverifikasi
- Pengujian fisik interaksi Bluetooth / scanner laser industri secara offline (disimulasikan melalui unit & feature test yang memverifikasi kecocokan string token barcode).

## Tindakan yang Butuh Akses / Intervensi Manusia
1. **Kurasi & Pembaruan Daftar Kata Terlarang:**
   - Super admin perlu secara berkala memantau tren istilah spam baru atau variasi ejaan promosi judi online di portal `/admin/word-filters`.
2. **Rekonsiliasi Manual Pembayaran QRIS:**
   - Dalam kasus langka di mana webhook callback Tripay terhalang gangguan jaringan penyedia telekomunikasi, admin keuangan dapat mengecek mutasi Tripay dan mengonfirmasi pelunasan tiket peserta secara manual di dashboard admin.
