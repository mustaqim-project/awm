# Risk Register - Modul Platform Backoffice & Superadmin (`admin`)

> Modul: `admin` | Commit: `a17c5ae` | Terakhir Diperbarui: 2026-10-26

---

## 1. Matriks Penilaian Risiko

| ID Risiko | Kategori | Deskripsi Risiko | Severity | Probabilitas | Dampak | Status Mitigasi | Risiko Residual |
| :---: | :--- | :--- | :---: | :---: | :---: | :---: | :---: |
| **RSK-01** | Keamanan | *Reverse Tabnabbing* pada 47 tautan eksternal `target="_blank"` | HIGH | Sedang | Tinggi | **SELESAI (MITIGATED)** | **NIL** |
| **RSK-02** | Fungsional | Model `Admin` mengembalikan `is_active = null` saat instansiasi default | MEDIUM | Tinggi | Sedang | **SELESAI (MITIGATED)** | **NIL** |
| **RSK-03** | Otorisasi | Penyalahgunaan fitur impersonasi tenant oleh administrator | HIGH | Rendah | Tinggi | **TERKONTROL** | **LOW** |
| **RSK-04** | Keamanan Data | Pengajuan berkas identitas pemulihan akun palsu untuk pembajakan akun | HIGH | Sedang | Tinggi | **TERKONTROL** | **LOW** |
| **RSK-05** | Operasional | Kegagalan koneksi pihak ketiga (TriPay Payment / Meta WhatsApp API) | MEDIUM | Sedang | Sedang | **TERKONTROL** | **LOW** |
| **RSK-06** | Infrastruktur | Akumulasi berkas log error di `/admin/error-logs` membebani disk | LOW | Tinggi | Rendah | **TERKONTROL** | **LOW** |
| **RSK-07** | Keuangan | Rekening transfer manual tidak dapat divalidasi pada checkout langganan | MEDIUM | Rendah | Sedang | **SELESAI (MITIGATED)** | **NIL** |

---

## 2. Uraian Detail Risiko & Tindakan Mitigasi

### RSK-01: Reverse Tabnabbing Vulnerability
- **Akar Masalah:** Tautan eksternal dokumentasi atau tautan sosial pada antarmuka admin menggunakan `target="_blank"` tanpa `rel="noopener noreferrer"`.
- **Tindakan Perbaikan:** Menjalankan perbaikan otomatis pada 47 tag anchor di 20 berkas view Blade admin.
- **Status Akhir:** Tertutup penuh.

### RSK-02: Model Admin Default Attributes Discrepancy
- **Akar Masalah:** Model `Admin` tidak mendefinisikan array `$attributes` default sehingga kueri instansiasi baru mengembalikan `is_active` null, yang memicu penolakan oleh `EnsureAdminIsActive`.
- **Tindakan Perbaikan:** Menambahkan `$attributes = ['role' => 'super_admin', 'is_active' => true]` dan mendukung varian alias `super_admin`/`superadmin` pada metode `isSuperAdmin()`.
- **Status Akhir:** Tertutup penuh. 14 test suite lulus 100%.

### RSK-03: Penyalahgunaan Fitur Impersonasi Tenant
- **Akar Masalah:** Admin masuk ke dashboard tenant tanpa pengawasan dan mengubah data sensitif transaksi.
- **Mitigasi Terpasang:**
  1. Otorisasi dibatasi ketat ke izin `admin.businesses.impersonate`.
  2. Jejak audit mencatat identitas admin, tenant ID, dan timestamp ke dalam basis data.
  3. Pita merah persisten hadir di bagian atas layar selama impersonasi berlangsung.

### RSK-04: Pengajuan Berkas Pemulihan Akun Palsu
- **Akar Masalah:** Pihak tidak bertanggung jawab mengajukan tiket permohonan pemulihan nomor WhatsApp/email akun bisnis orang lain.
- **Mitigasi Terpasang:**
  1. Meja Uji Bukti Otentik mewajibkan 3 berkas terpisah: Foto KTP Asli, Dokumen Legal Usaha (NIB/SIUP), dan Foto Selfie pemegang KTP.
  2. Pengambilan berkas dikunci di balik route dokumen privat bersandi token.
  3. Verifikasi manual oleh Superadmin sebelum aksi `approve` dapat dieksekusi.

### RSK-05: Kegagalan Koneksi Pihak Ketiga (TriPay & Meta WhatsApp)
- **Akar Masalah:** Token API kedaluwarsa atau server gateway mengalami downtime sesaat.
- **Mitigasi Terpasang:**
  1. Halaman `/admin/settings` menyediakan tombol pengujian koneksi langsung (*Diagnostic Test Connection*).
  2. Pesan galat terperinci disajikan di antarmuka admin saat pengujian gagal untuk mempermudah investigasi.
  3. Sistem tetap mendukung pembayaran transfer manual sebagai *fallback*.
