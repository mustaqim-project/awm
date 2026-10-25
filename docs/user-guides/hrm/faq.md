# Pertanyaan Umum (FAQ) - HRM & Penggajian

Berikut adalah kumpulan pertanyaan yang paling sering diajukan oleh pemilik usaha (**Business Owner**) mengenai pengelolaan sumber daya manusia, biometrik presensi, dan sistem penggajian di COOCA.

---

### Q1: Apakah presensi wajah COOCA bisa diakali dengan foto di ponsel lain atau foto cetak?
**Jawaban:** Tidak bisa. COOCA dilengkapi modul **Liveness Detection & Anti-Spoofing** cerdas yang menganalisis kedalaman wajah, pantulan cahaya, dan tekstur kulit secara real-time. Selain itu, presensi diverifikasi ganda dengan koordinat **GPS Geofencing** Haversine, sehingga staf wajib berada secara fisik di dalam radius toko yang telah ditentukan pada tab [Lokasi & Geofence GPS](/hrm?tab=locations).

---

### Q2: Bagaimana cara menghitung uang lembur dan komisi staf toko?
**Jawaban:** Saat Anda membuat batch penggajian di menu [Buat Penggajian Baru](/hrm/payrolls/create), sistem menampilkan tabel input interaktif untuk setiap staf. Anda cukup memasukkan nominal rupiah uang lembur (*Overtime Pay*) dan komisi target penjualan (*Commissions*) pada kolom yang tersedia. Sistem akan otomatis menambahkan nilai tersebut ke dalam perhitungan Gaji Kotor (*Gross Pay*) dan memperhitungkan kewajiban pajaknya.

---

### Q3: Apakah COOCA otomatis memotong iuran BPJS Ketenagakerjaan dan BPJS Kesehatan?
**Jawaban:** Ya. Jika opsi BPJS dicentang pada profil staf di tab [Karyawan & Profil Gaji](/hrm?tab=employees), engine `PayrollCalculationService` COOCA akan menghitung secara otomatis:
- **Porsi Perusahaan (Beban Usaha):** JKK (Jaminan Kecelakaan Kerja), JKM (Jaminan Kematian), JHT Perusahaan (3.7%), JP Perusahaan (2%), dan BPJS Kesehatan Perusahaan (4%).
- **Porsi Karyawan (Potongan Gaji):** JHT Karyawan (2%), JP Karyawan (1%), dan BPJS Kesehatan Karyawan (1%).
Seluruh rincian tertera jelas pada slip gaji karyawan dan laporan Excel akuntansi.

---

### Q4: Bagaimana cara kerja perhitungan Pajak Penghasilan PPh 21 TER?
**Jawaban:** COOCA menerapkan ketentuan resmi perpajakan Indonesia terbaru berbasis **Tarif Efektif Rata-Rata (TER)** PP No. 58 Tahun 2023 dan PMK No. 168 Tahun 2023. Berdasarkan status PTKP (seperti TK/0, K/1, dsb.) dan penghasilan bruto bulanan karyawan, sistem menentukan kategori TER (A, B, atau C) beserta persentase pajaknya secara otomatis.

---

### Q5: Bagaimana cara membagikan slip gaji resmi langsung ke WhatsApp karyawan?
**Jawaban:**
1. Masuk ke menu [Penggajian Bulanan](/hrm?tab=payrolls) dan klik batch yang sudah berstatus **Disetujui** atau **Dibayar**.
2. Klik nama karyawan untuk membuka slip digital.
3. Klik tombol **Kirim via WhatsApp**.
4. WhatsApp akan terbuka dengan draf pesan resmi yang memuat nama karyawan, periode bulan, rincian gaji bersih, dan tautan unik aman menuju slip gaji digital tanpa mengharuskan karyawan login ke sistem toko.

---

### Q6: Apa perbedaan antara berkas CSV Rekapitulasi dan CSV Transfer Bank Korporat?
**Jawaban:**
- **CSV Rekapitulasi Penggajian:** Berkas rekapitulasi menyeluruh yang memuat kolom lengkap (Gaji Pokok, Tunjangan, Lembur, Komisi, BPJS, PPh 21, Potongan Kasbon, Take Home Pay, dan Total Beban Usaha) untuk keperluan audit dan arsip pembukuan internal.
- **CSV Transfer Bank Korporat:** Berkas ringkas dengan struktur kolom standar sistem *Corporate Cash Management* (seperti KlikBCA Bisnis, Mandiri MCM, BRI CMS) yang hanya memuat Nomor Rekening Tujuan, Nama Penerima, Nama Bank, Nominal Transfer Bersih, dan Berita Acara, sehingga siap langsung diunggah (*bulk upload*) ke internet banking perusahaan Anda.

---

### Q7: Apakah pinjaman kasbon staf otomatis lunas jika periode cicilan berakhir?
**Jawaban:** Ya. Setiap kali penggajian bulanan ditandai **DIBAYAR**, sistem secara otomatis memotong cicilan bulanan dan mengurangi sisa saldo pinjaman staf. Ketika sisa saldo pinjaman mencapai Rp 0, status kasbon pada tab [Buku Besar Kasbon Staf](/hrm?tab=loans) akan otomatis beralih menjadi **Lunas (Paid)**.

---

### Q8: Siapa saja pengguna yang bisa melihat data gaji karyawan di toko saya?
**Jawaban:** Data gaji dan buku besar kasbon dilindungi oleh sistem otorisasi **Controller-Level RBAC** yang ketat. Hanya pengguna dengan role **Business Owner** atau staf yang diberi izin khusus `users.view` dan `users.manage` yang dapat membuka modul HRM. Karyawan kasir biasa atau staf toko tanpa izin tidak dapat mengakses menu ini dan akan otomatis dialihkan ke halaman portal.

---

## Navigasi Dokumen Terkait
- Baca [Panduan Utama HRM](index.md) untuk panduan menyeluruh.
- Baca [Panduan Troubleshooting](troubleshooting.md) jika Anda menemukan kendala teknis operasional.
