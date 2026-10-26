# Risk Register - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Tanggal: 2026-10-26

## Matriks Evaluasi Risiko Modul Pengaturan & AI
Dokumen ini mendokumentasikan risiko teknis, keamanan, dan operasional pada Modul Pengaturan Bisnis dan Cockpit AI MCP beserta strategi mitigasinya:

| ID Risiko | Deskripsi Risiko | Severity | Probabilitas | Dampak | Status Mitigasi | Bukti Verifikasi |
|---|---|---|---|---|---|---|
| **RSK-SET-01** | **Eksfiltrasi Data Bisnis via Token AI MCP Bocor:** Token akses MCP bocor ke publik atau dicuri dari perangkat pengguna, memungkinkan pihak luar memanggil tools keuangan/produk. | High | Sedang | Sangat Tinggi | **Termitigasi Penuh** | Token disimpan dalam bentuk SHA-256 hash. Plain token hanya tampil sekali saat dibuat. Tersedia Master Kill Switch instan. |
| **RSK-SET-02** | **Fraud Pengalihan Rekening Bank & PIN Kasir:** Anggota tim tanpa izin mengubah nomor rekening bank penerima pembayaran atau PIN supervisor kasir. | High | Rendah | Tinggi | **Termitigasi Penuh** | Controller-Level RBAC `HasMiddleware` membatasi izin `settings.edit`. Pencatatan otomatis ke `audit_logs` dengan level `RISK_HIGH` dan masking data. |
| **RSK-SET-03** | **Kerusakan Relasional Data Akibat Hapus Cabang Aktif:** Menghapus cabang toko yang masih memiliki stok inventori atau transaksi POS historis. | Medium | Sedang | Tinggi | **Termitigasi Penuh** | Validasi non-destruktif: menolak hapus jika ada stok; beralih otomatis ke *soft deactivate* jika ada riwayat transaksi. |
| **RSK-SET-04** | **Ketidakkonsistenan Pengalaman Pengguna (Emoji vs SVG):** Tampilan ikon emoji tidak seragam di Android, iOS, Windows, atau tampak tidak profesional bagi enterprise. | Low | Tinggi | Rendah | **Termitigasi Penuh** | Standardisasi 100% menggunakan Lucide SVG icons. Verifikasi scanner menemukan 0 emoji tersisa. |
| **RSK-SET-05** | **Human Error pada Checkbox Hari Operasional:** Checkbox terlalu kecil di layar sentuh ponsel/tablet kasir menyebabkan salah klik jam buka toko. | Low | Tinggi | Sedang | **Termitigasi Penuh** | Pembungkusan checkbox dalam kontainer label `min-h-[44px]` sesuai panduan Apple HIG Touch Target. |
| **RSK-SET-06** | **Teks Antarmuka Tidak Terjemahkan (i18n Glitch):** Teks bahasa Inggris muncul di aplikasi berbahasa Indonesia atau sebaliknya. | Low | Rendah | Rendah | **Termitigasi Penuh** | Penambahan kunci simetris di `lang/id/settings.php` dan `lang/en/settings.php`. Paritas 100% dari 736 translation keys. |

## Rencana Eskalasi & Kontinjensi Insiden AI
Jika terjadi kecurigaan anomali akses melalui asisten AI:
1. **Langkah 1 (Aksi Cepat):** Buka menu `Pengaturan -> Integrasi MCP`, matikan toggle **Master Switch**. Seluruh pemanggilan tool AI dari Claude, ChatGPT, dan Gemini akan otomatis tertolak seketika ($t < 1$ detik).
2. **Langkah 2 (Audit Investigasi):** Periksa riwayat di tab **Log Aktivitas MCP** untuk mengidentifikasi token ID, nama tool, alamat IP, dan waktu pemanggilan mencurigakan.
3. **Langkah 3 (Pembersihan):** Hapus/cabut (*revoke*) token yang bersangkutan.
4. **Langkah 4 (Pemulihan):** Buat token baru dengan cakupan izin yang lebih spesifik dan nyalakan kembali Master Switch.
