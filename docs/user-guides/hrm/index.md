# Panduan Pengguna: Modul SDM & Penggajian (HRM & Payroll)

Selamat datang di Panduan Pengguna Modul Sumber Daya Manusia & Penggajian COOCA. Modul ini dirancang khusus bagi pemilik usaha (**Business Owner**), manajer operasional, dan admin HR untuk mengelola staf, presensi biometrik wajah anti-kecurangan, geofencing radius outlet, pinjaman kasbon karyawan, hingga kalkulasi penggajian bulanan dan penerbitan slip gaji digital secara otomatis dan profesional.

---

## 1. Ikhtisar Arsitektur Modul HRM

Modul HRM COOCA terintegrasi langsung dengan operasional kasir POS dan sistem keuangan toko:

```
┌────────────────────────────────────────────────────────────────────────┐
│                   COOCA HUMAN RESOURCE MANAGEMENT (HRM)                │
├───────────────────────────────────┬────────────────────────────────────┤
│ 1. Manajemen Tim & Presensi       │ 2. Kompensasi & Penggajian Bulanan │
├───────────────────────────────────┼────────────────────────────────────┤
│ • Profil Staf & Parameter Gaji    │ • Batch Penggajian Otomatis (Run)  │
│ • Presensi Biometrik Wajah Liveness│ • Kalkulasi BPJS TK & Kesehatan    │
│ • Geofencing Radius Lokasi GPS    │ • Pemotongan PPh 21 TER Otomatis   │
│ • Penugasan Shift Kerja Staf      │ • Pemotongan Kasbon Karyawan       │
│ • Pengajuan Koreksi Presensi      │ • Slip Gaji Digital Resmi (Apple)  │
│ • Buku Besar Kasbon (Staff Loans) │ • Ekspor 2-Sheet Excel & Bank CSV  │
└───────────────────────────────────┴────────────────────────────────────┘
```

---

## 2. Navigasi & Tautan Menu Resmi

Sebagai pemilik bisnis, Anda dapat mengakses seluruh fitur HRM melalui tautan langsung berikut:

| Nama Fitur / Layanan | Tautan Langsung | Izin Akses Minimal | Deskripsi Fungsional |
|---|---|---|---|
| **Hub HRM & Dashboard** | [/hrm](/hrm) | `users.view` | Dashboard ringkasan Bento KPI, total staf, sisa saldo kasbon, dan status biometrik wajah. |
| **Karyawan & Profil Gaji** | [/hrm?tab=employees](/hrm?tab=employees) | `users.view` | Daftar anggota tim, tambah staf baru, edit profil gaji, BPJS, dan nomor rekening. |
| **Presensi & Log Biometrik** | [/hrm?tab=attendance](/hrm?tab=attendance) | `users.view` | Log riwayat kehadiran staf masuk/pulang, foto selfie verifikasi wajah, dan koordinat GPS. |
| **Pengajuan Koreksi Presensi** | [/hrm?tab=corrections](/hrm?tab=corrections) | `users.view` | Persetujuan atau penolakan pengajuan perubahan jam kerja dari staf yang lupa absen. |
| **Buku Besar Kasbon Staf** | [/hrm?tab=loans](/hrm?tab=loans) | `users.view` | Pencatatan kasbon staf, persetujuan tenor cicilan bulanan, dan pemantauan sisa saldo. |
| **Penggajian Bulanan** | [/hrm?tab=payrolls](/hrm?tab=payrolls) | `users.view` | Riwayat batch penggajian, status draf, persetujuan direksi, dan bukti pembayaran. |
| **Buat Penggajian Baru** | [/hrm/payrolls/create](/hrm/payrolls/create) | `users.manage` | Wizard pembuatan batch gaji karyawan periode berjalan dengan kalkulasi otomatis. |
| **Lokasi & Geofence GPS** | [/hrm?tab=locations](/hrm?tab=locations) | `users.manage` | Pendaftaran koordinat latitude/longitude toko fisik dan batas toleransi radius presensi. |
| **Ekspor Data Karyawan** | [/hrm/export/employees](/hrm/export/employees) | `users.view` | Unduh rekapitulasi seluruh profil karyawan dalam format CSV aman anti-formula injection. |
| **Ekspor Rekap Presensi** | [/hrm/export/attendance](/hrm/export/attendance) | `users.view` | Unduh rekap presensi seluruh staf untuk keperluan audit operasional toko. |

---

## 3. Alur Kerja Harian Pemilik Usaha

### A. Mendaftarkan Karyawan Baru & Profil Gaji
1. Buka tab [Karyawan & Profil Gaji](/hrm?tab=employees).
2. Klik tombol **Tambah Karyawan**.
3. Isi data profil: Nama lengkap, email, nomor WhatsApp, jabatan, dan role sistem.
4. Tentukan parameter kompensasi:
   - **Tipe Kerja:** Karyawan Tetap, Kontrak, atau Pekerja Harian Lepas.
   - **Gaji Pokok / Upah Harian:** Nominal upah pokok bulanan atau tarif harian.
   - **Tunjangan:** Tunjangan tetap (jabatan/keahlian) dan tunjangan variabel (makan/transport).
   - **Kepesertaan BPJS:** Centang jika diikutsertakan pada BPJS Ketenagakerjaan dan BPJS Kesehatan.
   - **Data Bank:** Nama bank, nomor rekening, dan nama pemilik rekening untuk transfer gaji.
5. Unggah foto wajah awal staf untuk pendaftaran biometrik.
6. Klik **Simpan Data**. Data tersimpan rapi dan otomatis mencatat jejak audit `hrm.employee_created`.

### B. Memantau Presensi Wajah & Koreksi Jam Kerja
1. Buka tab [Presensi & Log Biometrik](/hrm?tab=attendance).
2. Tinjau foto selfie staf yang diambil kamera depan saat check-in/check-out.
3. Periksa status validitas: Sistem otomatis mengecek jarak GPS terhadap titik toko fisik. Jika staf berada di luar radius, presensi otomatis ditolak.
4. Jika ada staf yang lupa absen karena kendala baterai habis atau dinas luar kota, staf dapat mengajukan koreksi yang akan muncul pada tab [Pengajuan Koreksi Presensi](/hrm?tab=corrections) untuk Anda setujui (*Approve*) atau tolak (*Reject*).

### C. Mengelola Kasbon & Pinjaman Karyawan
1. Buka tab [Buku Besar Kasbon Staf](/hrm?tab=loans).
2. Klik **Catat Kasbon Baru**.
3. Pilih nama staf peminjam, masukkan nominal pinjaman, tanggal pencairan, dan tenor cicilan (misal 3 bulan).
4. Sistem otomatis membagi cicilan per bulan.
5. Setiap kali Anda memproses penggajian bulanan, sistem secara otomatis memotong cicilan kasbon dari slip gaji staf bersangkutan tanpa risiko lupa potong.

### D. Menjalankan Penggajian Bulanan (Payroll Run)
1. Buka menu [Buat Penggajian Baru](/hrm/payrolls/create).
2. Pilih bulan dan tahun periode berjalan (misal: Oktober 2026).
3. Centang opsi **Sertakan THR** jika periode bertepatan dengan hari raya keagamaan.
4. Klik **Kalkulasi Batch Gaji**. Sistem akan membaca hari kerja hadir staf, menghitung lembur, potongan BPJS, PPh 21 TER, dan potongan kasbon.
5. Halaman detail batch penggajian akan terbuka dalam status **DRAFT**.
6. Anda dapat memeriksa rincian slip per individu. Jika nominal sudah sesuai, klik tombol **Setujui Penggajian (Approve)**.
7. Saat dana ditransfer ke rekening karyawan, klik tombol **Tandai DIBAYAR (Pay)**. Sistem otomatis memotong saldo sisa kasbon staf.

---

## 4. Berbagi Slip Gaji Resmi ke WhatsApp Karyawan
1. Pada detail batch penggajian yang sudah disetujui, klik nama karyawan untuk membuka slip digital.
2. Tampilan slip gaji resmi berstandar Apple HIG akan menyajikan rincian pendapatan kotor, potongan resmi, dan *Take Home Pay* bersih.
3. Klik tombol **Kirim via WhatsApp**: Sistem otomatis membuka aplikasi WhatsApp dengan pesan rapi yang memuat salam resmi, ringkasan gaji bersih, dan tautan slip digital publik yang dapat dibuka langsung oleh karyawan dari ponselnya.

---

## 5. Ekspor Dokumen Resmi
Dari halaman detail batch penggajian, Anda dapat mengunduh dokumen berikut:
1. **Excel 2-Sheet Workbook (`.xlsx`):** Sheet 1 berisi Ringkasan Eksekutif & Total Biaya Perusahaan; Sheet 2 berisi Buku Besar Lengkap seluruh komponen gaji.
2. **CSV Rekapitulasi Penggajian (`.csv`):** Rekapitulasi gaji berstandar UTF-8 BOM untuk pelaporan pembukuan internal.
3. **CSV Transfer Bank Korporat (`.csv`):** Berkas daftar transfer massal siap unggah untuk KlikBCA Bisnis, Mandiri MCM, atau Cash Management Bank lainnya.

---

## 6. Panduan Terkait
- Ingin mengatasi kendala presensi wajah atau radius toko? Baca [Panduan Pemecahan Masalah (Troubleshooting)](troubleshooting.md).
- Memiliki pertanyaan seputar BPJS, PPh 21 TER, atau batasan paket? Baca [Pertanyaan Umum (FAQ)](faq.md).
