# Buku Panduan Bisnis Owner: Manajemen Tim AI Otonom, Diagnosa Bisnis, & Otomasi Maker-Checker

| Profil Dokumen | Informasi |
| :--- | :--- |
| **Modul** | Cooca AI & Asisten Cerdas Bisnis (`cooca-ai`) |
| **Target Pembaca** | Pemilik Usaha (Business Owner), Direktur Operasional, Manajer Toko |
| **Level Teknis** | Non-Teknis / Praktis Operasional UMKM |
| **Versi Panduan** | 2.0 (Verified for Production) |

---

## 1. Mengapa Asisten AI Adalah Kunci Menghemat Waktu & Mencegah Kebocoran Bisnis?

Bagi pemilik bisnis UMKM, waktu Anda sangat berharga. Anda tidak punya waktu 4 jam setiap malam untuk memeriksa ribuan baris transaksi kasir atau menghitung sisa bahan baku di gudang.
1. **Mendeteksi Kecurangan Kasir Secara Mandiri:** AI membaca pola kasir yang sering melakukan pembatalan transaksi (*void*), diskon manual fiktif, atau laci kasir dibuka tanpa transaksi (*no-sale drawer open*).
2. **Kalkulasi Kebutuhan Stok Otomatis:** Mencegah Anda kehabisan stok saat jam ramai (*rush hour*) dengan memproyeksikan laju penjualan 14 hari ke depan berdasarkan data historis.
3. **Pusat Rekomendasi Tanpa Risiko (*Maker-Checker*):** AI bertindak sebagai pembuat proposal (*maker*), namun **uang dan keputusan tetap 100% di tangan Anda (*checker*)**. Tidak ada uang keluar atau pesanan terkirim tanpa persetujuan Anda!

---

## 2. Navigasi Cepat & Tautan Fitur Terverifikasi

- **[Lobi Komando Eksekutif](file:///c:/laragon/www/cooca_core/resources/views/app/ai/lobby.blade.php)**: Rapat Tim multi-agen otonom. (Rute web: `/cooca-ai/office`)
- **[Asisten Intelijen Kasir POS](file:///c:/laragon/www/cooca_core/resources/views/app/ai/index.blade.php)**: Analisis stok menipis dan anomali kasir. (Rute web: `/cooca-ai/pos`)
- **[Action Center (Persetujuan Proposal)](file:///c:/laragon/www/cooca_core/resources/views/app/ai/actions.blade.php)**: Validasi dan eksekusi draf tindakan. (Rute web: `/cooca-ai/actions`)
- **[Konfigurasi Kunci API AI](file:///c:/laragon/www/cooca_core/resources/views/app/ai/providers.blade.php)**: Atur kunci Gemini/OpenAI pribadi. (Rute web: `/cooca-ai/providers`)

---

## 3. Panduan Langkah Demi Langkah (Step-by-Step)

### A. Cara Mengadakan Rapat Strategis Tim AI (/teamwork)
1. Buka menu **Tim AI Bisnis** $\rightarrow$ pilih **Markas Eksekutif**.
2. Pada kolom topik rapat, ketikkan tantangan bisnis nyata Anda. Contoh:
   > *"Bahan baku daging ayam sering tersisa dan basi, sementara menu kopi susu sering kehabisan cup. Bagaimana solusinya?"*
3. Klik tombol biru **Jalankan Rapat Tim**.
4. Dalam 5-10 detik, Anda akan mendapatkan:
   - **Konsensus Eksekutif:** Ringkasan inti pemecahan masalah dari sudut pandang direksi.
   - **Analisa 4 Agen:** CEO (strategi), CFO (keuangan/HPP), COO (logistik gudang), dan CMO (penjualan).
   - **Proposal Aksi Langsung:** Contoh: Draf Purchase Order ke supplier cup baru dengan harga grosir hemat.

---

### B. Cara Menyetujui atau Menolak Proposal di Action Center (Maker-Checker)
1. Buka tab **Pusat Persetujuan (Action Center)** atau buka tautan `/cooca-ai/actions`.
2. Anda akan melihat kartu proposal berstatus **Menunggu Persetujuan**.
3. Periksa informasi penting:
   - **Judul & Deskripsi:** Apa yang akan dilakukan oleh sistem.
   - **Tingkat Risiko:** *Rendah* (misal: draf promo WhatsApp), *Sedang* (misal: draf PO), *Tinggi* (misal: perubahan harga jual).
   - **Estimasi Biaya:** Besaran dana yang dibutuhkan dalam Rupiah.
4. **Pilih Tindakan:**
   - Klik **Setujui & Eksekusi (Hijau):** Sistem langsung mengeksekusi aksi tersebut ke database.
   - Klik **Revisi (Kuning):** Ubah nominal kuantitas atau harga sebelum disetujui.
   - Klik **Tolak (Merah):** Masukkan alasan penolakan agar AI mempelajari preferensi Anda untuk proposal berikutnya.

---

## 4. Matriks Penerapan Nyata untuk 20 Jenis Usaha di COOCA

| Sektor Usaha | Contoh Pertanyaan / Perintah ke AI | Hasil & Tindakan Otomatis yang Didapat |
| :--- | :--- | :--- |
| **1. Restoran Dine-In** | "Menu apa yang marjin labanya paling tipis bulan ini?" | Menghitung HPP resep aktual vs harga jual; membuat proposal penyesuaian porsi bumbu. |
| **2. Coffee Shop & Cafe** | "Berapa liter susu segar yang harus dibeli untuk akhir pekan?" | Membaca riwayat penjualan Sabtu-Minggu lalu dan memproyeksikan kebutuhan stok susu. |
| **3. Bakery & Toko Roti** | "Buatkan draf promo roti sore buy 1 get 1 untuk kurangi sisa." | Menghasilkan copywriting pesan siaran WhatsApp resmi ke pelanggan member. |
| **4. Cloud Kitchen** | "Analisis jam sibuk pesanan online GoFood/GrabFood." | Menampilkan grafik lonjakan order jam 11:30 - 13:00 untuk penjadwalan staf masak. |
| **5. Katering Prasmanan** | "Hitung estimasi modal bahan untuk 300 porsi prasmanan." | Menguraikan daftar belanja beras, ayam, dan sayur lengkap dengan estimasi total harga pasar. |
| **6. Konveksi & Garment** | "Berapa yard kain katun combed tersisa di rak gudang?" | Cek stok multi-satuan dan mendeteksi bahan yang tidak cukup untuk order seragam aktif. |
| **7. Percetakan Offset** | "Kalkulasi HPP cetak 1.000 buku yasin & ongkos jilid." | Menjumlahkan biaya kertas art paper, tinta, dan susut mesin cetak secara rinci. |
| **8. Mebel & Furniture** | "Produk mebel mana yang perputarannya lambat (*dead stock*)?" | Mengidentifikasi meja jati yang mengendap >60 hari dan menyarankan diskon cuci gudang. |
| **9. Bengkel Bubut Logam** | "Berapa lama rata-rata lead time pengerjaan pesanan B2B?" | Menganalisis estimasi waktu pengerjaan mesin bubut per item pesanan. |
| **10. Kerajinan Tangan** | "Kapan jadwal restock manik-manik dan benang rajut?" | Memunculkan peringatan sebelum stok bahan aksesoris habis terpakai. |
| **11. Minimarket & Toko** | "Cek kasir mana yang paling sering melakukan void transaksi." | Menampilkan skor anomali kasir disertai jam dan nomor struk terkait untuk audit. |
| **12. Apotek & Toko Obat** | "Daftar obat apa saja yang akan kadaluwarsa dalam 30 hari?" | Menampilkan nomor batch, tanggal expired, dan nilai valuasi obat yang harus diretur. |
| **13. Bengkel Mobil & Motor**| "Pelanggan servis mana yang sudah 3 bulan belum ganti oli?" | Mengirimkan draf pengingat servis berkala otomatis via WhatsApp gateway. |
| **14. Barbershop & Salon** | "Siapa capster/terapis dengan total komisi tertinggi minggu ini?" | Merekap omzet per staf dan persentase komisi secara transparan. |
| **15. Laundry Kiloan** | "Apakah ada pakaian pelanggan yang menginap di rak lebih dari 5 hari?" | Mengeluarkan daftar nomor nota laundry yang belum diambil pemiliknya. |
| **16. Cuci Mobil & Detailing**| "Hitung sisa shampoo snow wash dan semir ban." | Menghitung pemakaian rata-rata per kendaraan dan memberi sinyal restock. |
| **17. Kontraktor Renovasi** | "Bandingkan estimasi anggaran belanja semen vs nota belanja riil." | Mendeteksi selisih anggaran over-budget pada termin proyek berjalan. |
| **18. Wedding Organizer** | "Buatkan rundown timeline acara pernikahan 5 jam." | Menyusun draf rundown terstruktur dari akad hingga resepsi lempar bunga. |
| **19. Digital Creative Agency**| "Buatkan penawaran paket social media management 3 bulan." | Menghasilkan draf Surat Penawaran (Quotation) resmi siap unduh PDF. |
| **20. Distributor Agro B2B** | "Toko mitra mana yang tagihan invoice-nya telah jatuh tempo?" | Menyusun daftar piutang macet dan tautan tombol tagih via invoice digital. |

---

## 5. Tips Aman Menjalankan Otomasi AI
1. **Gunakan Prinsip Human-in-the-Loop:** Jangan pernah mematikan persetujuan proposal untuk tindakan bernilai uang besar.
2. **Lindungi Kunci API Anda:** Jika menggunakan mode BYOAI (Gemini/OpenAI sendiri), pastikan kunci API Anda tidak dibagikan ke sembarang orang. Sistem COOCA mengenkripsi kunci API secara aman di tingkat database.
3. **Batasi Akses Staf:** Pastikan hanya akun pemilik (*Owner*) atau Manajer Utama yang memegang hak akses `ai.approve` dan `ai.manage`.

