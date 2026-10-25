# Audit Keamanan & Kepatuhan Otorisasi - HRM

> Modul: Sumber Daya Manusia & Penggajian (HRM & Payroll) | Tanggal: 2026-10-25

## 1. Controller-Level RBAC Enforcement (F-HRM-001 - Resolved)
### Latar Belakang Masalah
Sebelum perbaikan, otorisasi akses modul HRM semata-mata mengandalkan deklarasi middleware pada level route grup `routes/web.php`. Jika di kemudian hari rute dipanggil dari konteks lain, diubah prefix-nya, atau dipanggil secara internal tanpa melalui pipeline route group, kontroler rentan mengalami *authorization bypass*.

### Implementasi Perbaikan
`HrmWebController.php` kini secara formal mengimplementasikan antarmuka `Illuminate\Routing\Controllers\HasMiddleware` dengan pemetaan izin granular:
```php
public static function middleware(): array
{
    return [
        new Middleware('auth'),
        new Middleware('require.permission:users.view', only: [
            'index',
            'showPayroll',
            'showPayslip',
            'exportPayrollExcel',
            'exportPayrollCsv',
            'exportPayrollBankCsv',
            'exportEmployees',
            'exportAttendance',
        ]),
        new Middleware('require.permission:users.manage', only: [
            'storeEmployee',
            'updateEmployee',
            'destroyEmployee',
            'registerFace',
            'storeLoan',
            'cancelLoan',
            'createPayroll',
            'storePayroll',
            'approvePayroll',
            'payPayroll',
            'destroyPayroll',
            'storeLocation',
            'destroyLocation',
            'storeShift',
            'destroyShift',
            'storeSchedule',
            'reviewCorrection',
        ]),
    ];
}
```

### Verifikasi Otomatis
Diuji dalam `HrmModuleAuditTest.php`:
- `test_guest_is_redirected_to_login`: Memverifikasi tamu tanpa autentikasi dialihkan ke login (HTTP 302).
- `test_user_without_users_view_is_blocked_with_forbidden`: Memverifikasi staf dengan role kasir tanpa permission `users.view` dialihkan ke portal.

---

## 2. Pencegahan Formula / CSV Injection CWE-1236 (F-HRM-002 - Resolved)
### Latar Belakang Masalah
Pada ekspor file CSV Rekap Penggajian, CSV Transfer Bank, ekspor data karyawan, dan ekspor presensi, nilai input pengguna (nama staf, jabatan, nomor rekening berawalan `+62`, catatan kasbon) dituliskan langsung ke baris CSV. Dalam software spreadsheet (Microsoft Excel, LibreOffice, Google Sheets), sel yang berawalan karakter `=`, `+`, `-`, atau `@` diinterpretasikan sebagai formula dan dapat mengeksekusi perintah eksternal melalui DDE (*Dynamic Data Exchange*).

### Implementasi Perbaikan
Seluruh penulisan kolom teks yang bersumber dari pengguna disanitasi menggunakan `App\Support\Security\CsvSanitizer::sanitize()`:
```php
fputcsv($handle, [
    CsvSanitizer::sanitize((string) $bankAccount),
    CsvSanitizer::sanitize((string) $item->employee_name),
    CsvSanitizer::sanitize((string) $bankName),
    (int) $item->take_home_pay,
    CsvSanitizer::sanitize((string) $desc),
    CsvSanitizer::sanitize((string) ($item->user?->email ?? '-')),
]);
```

### Verifikasi Otomatis
Diuji dalam `HrmModuleAuditTest::test_formula_injection_defense_sanitizes_payroll_csv_export`:
- Input berbahaya seperti `=CMD('calc.exe')!A0`, `-EXEC('calc')`, `+628123456789`, `@SUM(1+1)*cmd` terbukti diawali tanda kutip tunggal (`'`) pada berkas keluaran CSV, menonaktifkan eksekusi formula di aplikasi spreadsheet.

---

## 3. Jejak Audit Ketenagakerjaan & Finansial (F-HRM-003 - Resolved)
### Latar Belakang Masalah
Data SDM, besaran gaji, dan cicilan kasbon merupakan data dengan risiko liabilitas dan hukum ketenagakerjaan tinggi. Perubahan data karyawan tanpa jejak audit menyulitkan investigasi saat terjadi sengketa ketenagakerjaan atau penyalahgunaan hak akses.

### Implementasi Perbaikan
`AuditLog::create` diintegrasikan pada 9 operasi mutasi kunci di `HrmWebController`:
1. `hrm.employee_created`: Saat penambahan profil staf baru.
2. `hrm.employee_updated`: Saat pengubahan parameter gaji, BPJS, atau status kerja.
3. `hrm.employee_deleted`: Saat pencabutan status anggota staf dari bisnis.
4. `hrm.loan_created`: Saat pencatatan pinjaman kasbon baru staf.
5. `hrm.loan_cancelled`: Saat pembatalan transaksi kasbon aktif.
6. `hrm.payroll_created`: Saat kalkulasi draf batch penggajian bulanan.
7. `hrm.payroll_approved`: Saat otorisasi/persetujuan batch oleh owner.
8. `hrm.payroll_paid`: Saat eksekusi pembayaran gaji dan update buku besar kasbon.
9. `hrm.payroll_deleted`: Saat penghapusan draf penggajian yang belum dibayar.

---

## 4. Keamanan Slip Gaji Publik (*Public Digital Payslip Token*)
### Mekanisme Keamanan Token
Karyawan dapat melihat slip gaji tanpa perlu login ke sistem utama melalui URL publik:
`/hrm/payslip/public/{token}`.
- **Kekuatan Token:** Token slip gaji dibuat menggunakan kombinasi UUID v4 dan string acak berkriptografi aman (`Str::random(48)`), menghasilkan entropi 256-bit yang mustahil ditebak via *brute-force*.
- **Akses Read-Only:** Endpoint publik slip gaji bersifat murni baca (*read-only*), tanpa form aksi mutasi atau tombol manipulasi database.
- **Validasi Tenant:** Slip tetap terikat secara ketat pada data bisnis valid via relasi `payroll.business`.

---

## 5. Audit XSS, CSRF, & Ergonomi Template Blade
Hasil pemindaian otomatis via script analitik:
- **Scan XSS (`scan_hrm_security.py`):** Ditemukan 0 tag `{!! !!}` tanpa sanitasi dan 0 direktif `x-html` Alpine.js yang memuat data pengguna dinamis. Seluruh output teks di-escape aman via `{{ }}` standar Laravel.
- **Scan CSRF:** Seluruh form mutasi HTML di 4 berkas Blade memuat direktif `@csrf` secara lengkap.
- **Scan Unicode Emoji (`scan_hrm_emojis.py`):** 0 Unicode emoji ditemukan. Seluruh ikon menggunakan SVG Lucide yang konsisten.
