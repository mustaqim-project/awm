# Panduan Penyelesaian Masalah (Troubleshooting) - Pengaturan Bisnis & MCP

> Modul: Pengaturan Bisnis & Ekosistem AI MCP | Target Pembaca: Pemilik Usaha (Business Owner) & Tim IT

Panduan ini membantu Anda menyelesaikan kendala teknis dan operasional yang paling sering dihadapi saat mengelola Pengaturan Bisnis dan Integrasi Asisten AI MCP di COOCA.

---

## 1. Kendala Asisten AI (Claude, ChatGPT, Gemini)

### Gejala 1: Pesan Error "401 Unauthorized" atau "Invalid Token" pada Asisten AI
- **Penyebab:**
  1. Token akses MCP telah dicabut (*revoked*) oleh admin.
  2. Token telah melewati masa berlaku (*expired*).
  3. Master Switch AI di COOCA sedang dimatikan.
  4. Terjadi kesalahan pengetikan atau spasi tambahan saat menyalin token.
- **Langkah Penyelesaian:**
  1. Buka [Cockpit Integrasi AI MCP](/settings/integrations/mcp).
  2. Periksa status **Master Switch**. Jika warnanya abu-abu/mati, geser tombol ke posisi **Aktif (Hijau)**.
  3. Periksa daftar token di tabel. Pastikan token yang digunakan memiliki badge **Aktif**.
  4. Jika statusnya kadaluarsa atau dicabut, klik tombol **Buat Token Baru**.
  5. Salin token baru tanpa spasi ekstra di awal atau akhir string.
  6. Perbarui konfigurasi di Claude Desktop (`claude_desktop_config.json`) atau Custom GPT Actions Anda.

---

### Gejala 2: Asisten AI Tidak Bisa Membaca Laporan Keuangan ("Tool Forbidden / Missing Ability")
- **Penyebab:** Token MCP yang dibuat tidak memiliki izin (*abilities*) untuk domain keuangan atau produk.
- **Langkah Penyelesaian:**
  1. Buka [Cockpit Integrasi AI MCP](/settings/integrations/mcp).
  2. Cari token yang bersangkutan di tabel, lalu klik tombol **Edit (Ikon Pensil)**.
  3. Pada pilihan **Hak Akses (Abilities)**, centang opsi `mcp:finance:read` atau pilih `Semua Hak Akses (*)`.
  4. Klik **Simpan Perubahan**.
  5. Coba panggil kembali perintah analisis keuangan di Claude/ChatGPT Anda tanpa perlu membuat token baru.

---

## 2. Kendala Operasional Kasir & Keuangan

### Gejala 3: Kasir Tidak Bisa Melakukan Void / Refund ("PIN Supervisor Salah")
- **Penyebab:** Kasir memasukkan PIN yang salah atau pemilik bisnis baru saja memperbarui PIN tanpa memberitahukannya kepada supervisor.
- **Langkah Penyelesaian:**
  1. Buka menu [Keuangan & Kasir POS](/settings?tab=pos_financial) menggunakan akun Owner.
  2. Pada kolom **PIN Otorisasi Supervisor Kasir**, masukkan 6 digit PIN baru yang mudah diingat oleh supervisor jaga (misal: `123456`).
  3. Klik **Simpan Pengaturan**.
  4. Berikan PIN baru tersebut hanya kepada penanggung jawab shift toko.
  5. Di aplikasi kasir POS, lakukan sinkronisasi atau refresh halaman agar PIN terbaru segera aktif.

---

### Gejala 4: Logo Usaha Tidak Muncul pada Cetakan Struk Belanja atau Faktur
- **Penyebab:** Berkas logo yang diunggah memiliki dimensi terlalu besar, berlatar belakang hitam gelap, atau format file korup.
- **Langkah Penyelesaian:**
  1. Buka [Pengaturan Umum](/settings?tab=general).
  2. Jika logo lama tampak rusak, centang opsi **Hapus Logo**, lalu klik Simpan.
  3. Siapkan file logo berlatar belakang transparan (format PNG) dengan resolusi ideal 500x500 pixel.
  4. Unggah kembali berkas logo tersebut dan klik **Simpan Pengaturan**.
  5. Coba cetak ulang struk belanja uji coba di kasir.

---

## 3. Kendala Cabang & Presensi GPS (Geofencing)

### Gejala 5: Karyawan Gagal Absen dengan Pesan "Anda Berada di Luar Jangkauan Toko"
- **Penyebab:** Titik koordinat latitude/longitude toko tidak akurat, atau radius geofencing disetel terlalu sempit (misal hanya 5 meter), sedangkan GPS ponsel staf mengalami deviasi akurasi di dalam gedung.
- **Langkah Penyelesaian:**
  1. Buka tab [Cabang & Multi-Outlet](/settings?tab=branches).
  2. Cari nama cabang yang bermasalah, lalu klik **Edit**.
  3. Buka Google Maps di browser, cari lokasi tepat toko Anda, klik kanan pada titik bangunan toko, lalu salin koordinat latitude dan longitude.
  4. Masukkan kembali koordinat tersebut ke kolom Latitude dan Longitude.
  5. Tingkatkan nilai **Radius Geofence (Meter)** menjadi `50` atau `100` meter untuk memberikan toleransi sinyal GPS perangkat karyawan.
  6. Klik **Simpan Cabang**.

---

### Gejala 6: Cabang Tidak Bisa Dihapus ("Cabang Memiliki Riwayat Operasional")
- **Penyebab:** Sistem COOCA menerapkan perlindungan data non-destruktif. Cabang yang pernah mencatat transaksi penjualan POS, pergerakan stok barang, atau absensi staf tidak boleh dihapus secara permanen guna menjaga validitas laporan keuangan dan audit pajak.
- **Langkah Penyelesaian:**
  1. Jangan khawatir, Anda tidak perlu menghapusnya secara paksa.
  2. Saat Anda mengonfirmasi hapus pada cabang yang memiliki riwayat, sistem secara cerdas akan otomatis mengubah status cabang tersebut menjadi **Nonaktif (Soft Deactivate)**.
  3. Cabang nonaktif tidak akan lagi muncul di pilihan kasir POS kasir baru, tidak bisa dipilih untuk pesanan online, dan staf tidak bisa absen di sana.
  4. Seluruh riwayat transaksi masa lalu tetap tersimpan rapi dan aman untuk pembukuan tahunan Anda.

---

## 4. Kendala WhatsApp Gateway

### Gejala 7: Struk Belanja Tidak Otomatis Terkirim ke WhatsApp Pelanggan
- **Penyebab:** Sesi WhatsApp Cloud API terputus, nomor telepon pelanggan tidak menggunakan format internasional (contoh: memasukkan `0812...` tanpa konversi `62812...`), atau template struk belum diaktifkan.
- **Langkah Penyelesaian:**
  1. Buka halaman [Pengaturan WhatsApp Gateway](/settings/integrations/whatsapp).
  2. Pastikan opsi **Kirim Struk Otomatis ke WhatsApp Pelanggan** dalam posisi centang aktif.
  3. Periksa status koneksi Meta Cloud API. Jika status menunjukkan *Disconnected*, lakukan otorisasi ulang token WABA Anda.
  4. Pastikan kasir selalu memasukkan nomor telepon pelanggan dengan benar saat bertransaksi di kasir POS.
