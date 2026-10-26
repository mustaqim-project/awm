# Panduan Pengguna: Modul Pengaturan Bisnis & Ekosistem AI MCP

Selamat datang di Panduan Pengguna Modul Pengaturan Bisnis & Integrasi Kecerdasan Buatan (AI MCP) COOCA. Modul ini adalah pusat komando bagi pemilik usaha (**Business Owner**), direktur operasional, dan manajer toko untuk mengelola identitas resmi bisnis, rekening bank, parameter kasir POS, jaringan cabang multi-outlet, hingga integrasi asisten AI enterprise (**Claude**, **ChatGPT**, **Gemini**) via Model Context Protocol secara aman dan terkontrol.

---

## 1. Ikhtisar Arsitektur Pengaturan Bisnis

Modul Pengaturan COOCA menghubungkan seluruh aspek operasional toko dan ekosistem AI dalam satu dashboard terintegrasi:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   COOCA BUSINESS SETTINGS & AI COCKPIT                 │
├───────────────────────────────────┬────────────────────────────────────┤
│ 1. Identitas & Operasional Toko   │ 2. Ekosistem AI & Integrasi Luar   │
├───────────────────────────────────┼────────────────────────────────────┤
│ • Profil Perusahaan & Logo Merek  │ • Asisten AI Claude (Anthropic)    │
│ • Rekening Bank & Pajak Penjualan │ • Asisten AI ChatGPT (OpenAI)      │
│ • PIN Otorisasi Supervisor Kasir  │ • Asisten AI Gemini (Google)       │
│ • Master Cabang Toko & Gudang     │ • Master AI Kill Switch (Darurat)  │
│ • Geofencing Radius Presensi GPS  │ • Gateway WhatsApp Cloud API       │
│ • Template Industri & Modul Aktif │ • Buku Besar Jejak Audit Keamanan  │
└───────────────────────────────────┴────────────────────────────────────┘
```

---

## 2. Navigasi & Tautan Menu Resmi

Sebagai pemilik bisnis, Anda dapat mengakses seluruh fitur Pengaturan melalui tautan langsung berikut:

| Nama Fitur / Halaman | Tautan Langsung | Izin Akses Minimal | Deskripsi Fungsional |
|---|---|---|---|
| **Hub Pengaturan Utama** | [/settings](/settings) | `settings.view` | Dashboard pusat pengaturan profil usaha, logo, kontak, dan alamat toko. |
| **Cabang & Multi-Outlet** | [/settings?tab=branches](/settings?tab=branches) | `settings.view` | Kelola daftar toko cabang, gudang sentral, koordinat GPS, dan jam operasional. |
| **Keuangan & Kasir POS** | [/settings?tab=pos_financial](/settings?tab=pos_financial) | `settings.edit` | Rekening bank pencairan, pajak (PPN/PB1), pembulatan harga, dan PIN supervisor kasir. |
| **Modul & Template Industri** | [/settings?tab=modules](/settings?tab=modules) | `owner` | Pilih template industri (F&B, Retail, Jasa) dan aktifkan/nonaktifkan modul usaha. |
| **Cockpit AI MCP Hub** | [/settings/integrations/mcp](/settings/integrations/mcp) | `settings.view` | Integrasi kecerdasan buatan Model Context Protocol (Claude, ChatGPT, Gemini). |
| **WhatsApp Gateway** | [/settings/integrations/whatsapp](/settings/integrations/whatsapp) | `settings.view` | Konfigurasi sesi WhatsApp Cloud API untuk pengiriman otomatis struk belanja POS. |
| **Log Aktivitas AI MCP** | [/settings/integrations/mcp/logs](/settings/integrations/mcp/logs) | `settings.view` | Riwayat panggilan perkakas AI, waktu eksekusi, identitas tool, dan status. |

---

## 3. Prosedur Operasional Standar (SOP) Pemilik Usaha

### A. Mengatur Profil Bisnis & Rekening Bank Resmi
1. Buka halaman [Pengaturan Umum](/settings?tab=general).
2. Unggah logo resmi usaha Anda (format PNG/JPG transparan, maksimal 2MB). Logo ini akan otomatis dicetak pada faktur invoice, struk thermal kasir, dan katalog online.
3. Masukkan informasi legal: Nama Usaha, Nomor Telepon WhatsApp resmi, Email, dan Alamat Lengkap.
4. Buka tab [Keuangan & Kasir POS](/settings?tab=pos_financial).
5. Masukkan **Nama Bank**, **Nomor Rekening**, dan **Nama Pemilik Rekening**.
6. Atur **PIN Supervisor Kasir (6 Digit)**. PIN ini wajib dimasukkan oleh kasir setiap kali melakukan pembatalan pesanan (*void*) atau pengembalian dana (*refund*).
7. Klik **Simpan Pengaturan**. Sistem akan otomatis mencatat pembaruan ini ke dalam buku besar jejak audit dengan tingkat risiko tinggi (*RISK_HIGH*).

### B. Mendaftarkan Cabang Baru & Mengatur Geofencing GPS
1. Buka tab [Cabang & Multi-Outlet](/settings?tab=branches).
2. Klik tombol **Tambah Cabang**.
3. Masukkan Nama Cabang (misal: *Outlet Tebet*, *Kitchen Sentral Bekasi*).
4. Tentukan Tipe Lokasi: *Outlet*, *Toko Fisik*, atau *Dapur Sentral*.
5. Tentukan Titik Koordinat:
   - Masukkan nilai **Latitude** dan **Longitude** dari Google Maps.
   - Atur **Radius Toleransi Presensi (Geofence)**, misal: `50` meter. Karyawan hanya dapat melakukan absen jika berada di dalam radius ini.
6. Atur Jam Operasional toko per hari (Senin s/d Minggu).
7. Centang opsi:
   - `Pemenuhan Pesanan Online (Online Fulfillment)`: Jika cabang melayani kurir instan/ekspedisi.
   - `Ambil di Tempat (Storefront Pickup)`: Jika pelanggan boleh mengambil pesanan di outlet ini.
8. Klik **Simpan Cabang**.

### C. Menghubungkan Asisten AI (Claude, ChatGPT, Gemini) via MCP
Modul ini memungkinkan Anda bertanya tentang bisnis Anda kepada AI paling cerdas di dunia secara aman:

1. Buka halaman [Cockpit Integrasi AI MCP](/settings/integrations/mcp).
2. Pastikan **Master Switch AI** dalam posisi **Aktif**.
3. Klik tombol **Buat Token Baru**:
   - **Nama Token:** Beri nama yang mudah diingat (misal: *Claude Laptop Owner*, *ChatGPT Tim Marketing*).
   - **Platform AI:** Pilih antara `Universal`, `Claude Desktop & Code`, `ChatGPT & OpenAI`, atau `Google Gemini`.
   - **Masa Berlaku:** Pilih *30 Hari*, *90 Hari*, atau *Selamanya*.
   - **Hak Akses (Abilities):** Pilih domain data yang boleh diakses AI (misal: hanya membaca produk dan laporan laba rugi, tanpa akses data pelanggan).
4. Klik **Generate Token**.
5. **PENTING:** Salin teks token yang muncul (berawalan `cooca_mcp_...`). Token ini **hanya ditampilkan satu kali**.
6. Masukkan token tersebut ke dalam aplikasi AI pilihan Anda sesuai tab panduan konfigurasi yang tersedia di halaman:
   - **Claude Desktop:** Tambahkan ke file `claude_desktop_config.json`.
   - **ChatGPT:** Tambahkan ke Custom GPT Actions dengan skema OpenAPI yang disediakan.
   - **Google Gemini:** Tambahkan ke Antigravity / Gemini Extensions.

### D. SOP Darurat: Memutus Seluruh Akses AI (Master Kill Switch)
Jika terjadi insiden keamanan, perangkat laptop staf hilang, atau terjadi pergantian karyawan kunci:
1. Segera buka [Cockpit Integrasi AI MCP](/settings/integrations/mcp) dari ponsel atau laptop Anda.
2. Geser saklar **Master Switch** ke posisi **NONAKTIF**.
3. Dalam waktu kurang dari 1 detik, seluruh panggilan perkakas AI dari Claude, ChatGPT, dan Gemini akan otomatis ditolak oleh server COOCA.
4. Periksa tab [Log Aktivitas MCP](/settings/integrations/mcp/logs) untuk memastikan tidak ada aktivitas mencurigakan.
5. Hapus (*Revoke*) token yang dicurigai bocor.
6. Buat token baru yang aman dan aktifkan kembali Master Switch.

---

## 4. Matriks Implementasi Lintas 20 Sektor Usaha

| No | Sektor Usaha | Konfigurasi Utama Pengaturan | Penggunaan AI Assistant (MCP) |
|---|---|---|---|
| 1 | **Coffee Shop & Kafe** | Pembulatan harga kasir 100/500, struk WhatsApp, radius GPS 30m. | Claude: Analisis menu terlaris dan jam sibuk pelanggan. |
| 2 | **Restoran & Rumah Makan** | Pajak Restoran PB1 (10%), printer dapur sentral, PIN void kasir. | ChatGPT: Estimasi kebutuhan belanja bahan baku basah harian. |
| 3 | **Butik & Toko Pakaian** | Pemenuhan pesanan online, multi-cabang mall, katalog gambar POS. | Gemini: Rekomendasi restock ukuran/warna pakaian terfavorit. |
| 4 | **Minimarket & Kelontong** | Pembulatan HPP FIFO, integrasi scanner barcode, batas diskon kasir. | Claude: Deteksi barang dead-stock yang mendekati masa expired. |
| 5 | **Apotek & Toko Obat** | Nomor izin sarana, catatan kaki struk SIPA apoteker, radius GPS ketat. | ChatGPT: Rekapitulasi perputaran obat bebas vs obat resep. |
| 6 | **Bengkel Motor / Mobil** | Jam operasional shift mekanik, cetak nomor polisi di struk belanja. | Gemini: Analisis servis berkala dan pengingat jadwal oli pelanggan. |
| 7 | **Barbershop & Salon** | Jam operasional berjadwal, struk thermal kasir via WhatsApp. | Claude: Prediksi kapasitas kursi kosong di hari kerja vs akhir pekan. |
| 8 | **Laundry Kiloan & Satuan** | Catatan nota nomor rak cucian, notifikasi otomatis struk WhatsApp. | ChatGPT: Perhitungan margin deterjen dan parfum per kilogram cucian. |
| 9 | **Petshop & Klinik Hewan** | Multi-gudang makanan hewan, jadwal dokter hewan, PIN void POS. | Gemini: Analisis tren paket grooming dan perawatan hewan peliharaan. |
| 10 | **Toko Bangunan & Material** | Pembulatan nilai transaksi besar, titik koordinat armada kirim toko. | Claude: Proyeksi piutang kontraktor dan siklus semen/besi. |
| 11 | **Toko Elektronik & Gadget** | Pencatatan nomor rekening resmi PT/CV, batas diskon staf kasir (5%). | ChatGPT: Evaluasi margin produk aksesoris vs unit smartphone utama. |
| 12 | **Bakery & Toko Roti** | Jam buka pagi (06:00), dapur sentral terhubung ke outlet cabang. | Gemini: Perhitungan draf produksi adonan roti manis harian. |
| 13 | **Florist & Toko Bunga** | Alamat pengiriman Biteship cabang utama, catatan kartu ucapan struk. | Claude: Pemetaan pesanan papan bunga pada kalender acara. |
| 14 | **Optik & Toko Kacamata** | Cetak data resep lensa pada catatan invoice, geofence cabang mall. | ChatGPT: Analisis repeat order penggantian lensa tahunan pelanggan. |
| 15 | **Frozen Food & Daging** | Lokasi cold storage, toleransi jam buka, struk otomatis WhatsApp. | Gemini: Monitoring perputaran daging beku dan margin per kilogram. |
| 16 | **Cuci Sepatu & Care** | Foto kondisi awal di POS, catatan disclaimer garansi pada struk. | Claude: Rekap omzet layanan deep clean vs unyellowing mingguan. |
| 17 | **Toko Plastik & Kemasan** | Transaksi grosir vs eceran, penetapan cabang pemenuhan logistik. | ChatGPT: Analisis kuantitas pemesanan karton dus dan bubble wrap. |
| 18 | **Gym & Fitness Center** | Jam buka 24 jam / custom, pendaftaran geofence pintu masuk. | Gemini: Evaluasi tingkat retensi keanggotaan bulanan member gym. |
| 19 | **Car Wash & Detailing** | Pembagian komisi operator cuci, PIN supervisor untuk diskon promo. | Claude: Analisis perbandingan omzet cuci biasa vs nano ceramic coating. |
| 20 | **Jasa Servis Elektronik** | Cetak nomor tanda terima servis di nota kasir, rekening bank resmi. | ChatGPT: Rekap status suku cadang yang sedang dipesan ke supplier. |

---

## 5. Ringkasan Praktik Terbaik Keamanan

1. **Gunakan Nama Token Deskriptif:** Jangan menamai token sembarangan (seperti *token1*). Beri nama spesifik seperti *Claude Desktop Direksi* agar mudah diaudit.
2. **Batasi Hak Akses Token:** Jika AI hanya digunakan untuk riset produk, berikan izin `mcp:products:read` dan jangan berikan izin data pelanggan atau keuangan.
3. **Rahasiakan PIN Supervisor:** PIN kasir adalah benteng terakhir dari fraud pencurian uang kas toko. Jangan bagikan PIN kepada kasir magang.
4. **Periksa Audit Log Berkala:** Biasakan mengecek riwayat aktivitas di [Log Jejak Audit](/settings) setiap akhir pekan untuk memastikan tidak ada perubahan rekening mencurigakan.
