# Daftar Risiko & Mitigasi - HRM

> Modul: Sumber Daya Manusia & Penggajian (HRM & Payroll) | Tanggal: 2026-10-25

## Matriks Penilaian Risiko (Risk Matrix)
| ID Risiko | Kategori | Deskripsi Risiko | Dampak Awal | Mitigasi yang Diterapkan | Status Risiko |
|---|---|---|---|---|---|
| **RSK-HRM-01** | Keamanan | Spreadsheet Formula Injection (CWE-1236) pada ekspor rekap CSV, transfer bank, dan Excel | **High** | Sanitasi seluruh teks pengguna via `CsvSanitizer::sanitize()` | **Mitigated (Low)** |
| **RSK-HRM-02** | Otorisasi | Tidak adanya Controller-Level RBAC pada `HrmWebController` (hanya mengandalkan route middleware) | **High** | Implementasi `HasMiddleware` dengan permission `users.view` dan `users.manage` | **Mitigated (Low)** |
| **RSK-HRM-03** | Integritas Finansial | Potongan kasbon ganda atau saldo pinjaman tidak terupdate saat pembayaran payroll | **Medium** | Penguncian transaksi database atomik `DB::transaction` pada `markPayrollPaid` | **Mitigated (Low)** |
| **RSK-HRM-04** | Keamanan Multi-Tenant | Kebocoran data slip gaji atau kasbon antar-tenant (IDOR) | **Medium** | Pengecekan eksplisit kepemilikan tenant `$item->business_id !== $business->id` menghasilkan abort 404 | **Mitigated (Low)** |
| **RSK-HRM-05** | Kepatuhan Audit | Hilangnya jejak audit pada perubahan profil gaji, pencatatan kasbon, dan approval payroll | **Medium** | Integrasi `AuditLog::create` otomatis pada 9 titik mutasi kritis di controller | **Mitigated (Low)** |
| **RSK-HRM-06** | Operasional Presensi | Kecurangan titip absen atau pemalsuan foto wajah (*Face Spoofing*) | **Medium** | Verifikasi liveness anti-spoofing dan validasi geofence GPS Haversine radius toko fisik | **Mitigated (Low)** |

## Rincian Mitigasi Risiko Kritis
### 1. RSK-HRM-01: Formula Injection Defense
- **Potensi Ancaman:** Penyerang mendaftarkan nama karyawan atau rekening bank dengan prefix formula Excel (misal `=CMD(...)` atau `+6281...`) untuk memicu DDE execution saat admin HR membuka berkas di perangkat desktop.
- **Tindakan Pencegahan:** `CsvSanitizer::sanitize` mendeteksi karakter prefix berbahaya dan menambahkan karakter `'` di awal sel.

### 2. RSK-HRM-02: Controller Guard Enforcement
- **Potensi Ancaman:** Pemanggilan method controller secara langsung atau pengubahan konfigurasi rute web dapat menyebabkan celah akses tak terotorisasi bagi pengguna non-manajer.
- **Tindakan Pencegahan:** `HrmWebController::middleware()` mendefinisikan guard pada level kelas kontroler itu sendiri.

### 3. RSK-HRM-03: Atomic Payroll Settlement
- **Potensi Ancaman:** Saldo kasbon berkurang tetapi status payroll gagal disimpan (atau sebaliknya).
- **Tindakan Pencegahan:** Seluruh update status payroll, item slip gaji, dan saldo kasbon karyawan dieksekusi dalam satu transaksi database tunggal yang otomatis di-rollback jika terjadi kesalahan runtime.
