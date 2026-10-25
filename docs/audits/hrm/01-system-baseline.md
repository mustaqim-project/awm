# System Baseline & Component Inventory - HRM

> Modul: Sumber Daya Manusia & Penggajian (HRM & Payroll) | Tanggal: 2026-10-25

## 1. Ikhtisar Arsitektur Modul
Modul HRM COOCA dibangun dengan pendekatan berlapis (*Domain-Driven & Multi-Tenant Architecture*) di dalam ekosistem Laravel 11. Modul ini bertanggung jawab atas:
- Manajemen profil anggota staf dan parameter penggajian multi-cabang.
- Pendaftaran biometrik wajah (*Face Verification*) dan pencegahan pemalsuan (*anti-spoofing*).
- Presensi berbasis lokasi (*Geofencing radius*) dan jadwal shift kerja.
- Manajemen kasbon staf (*Employee Loans*) dengan pemotongan otomatis pada payroll bulanan.
- Engine kalkulasi payroll bulanan terintegrasi (Gaji Pokok, Lembur, Komisi, BPJS TK & Kes, PPh 21 TER, THR).
- Penerbitan slip gaji digital dengan token publik aman dan integrasi API WhatsApp.
- Ekspor rekapitulasi penggajian multi-format (2-Sheet Excel Workbook, CSV Rekapitulasi, CSV Bank Transfer).

## 2. Inventaris Controller & Handler
| File Path | Deskripsi & Peran | Guard / Middleware RBAC |
|---|---|---|
| `app/Http/Controllers/Web/Hrm/HrmWebController.php` | Controller utama yang menangani seluruh antarmuka web HRM, pendaftaran staf, presensi, kasbon, pembuatan batch payroll, persetujuan, pembayaran, dan ekspor data. | `implements HasMiddleware` (`auth`, `require.permission:users.view`, `require.permission:users.manage`) |

## 3. Inventaris Domain Services
| Service Class | Lokasi Berkas | Fungsi Utama |
|---|---|---|
| `PayrollRunService` | `app/Domain/HRM/PayrollRunService.php` | Orkestrasi batch penggajian, agregasi nilai gaji, pembuatan token slip, pembuatan draf, approval, pembayaran, dan penghapusan draf. |
| `PayrollCalculationService` | `app/Domain/HRM/PayrollCalculationService.php` | Kalkulasi numerik gaji, kalkulasi iuran BPJS Ketenagakerjaan (JKK, JKM, JHT, JP), BPJS Kesehatan, perhitungan pajak PPh 21 TER bulanan, dan THR proporsional. |
| `FaceVerificationService` | `app/Domain/HRM/FaceVerificationService.php` | Registrasi template wajah awal, pencocokan biometrik presensi real-time, liveness check, dan komparasi cosine similarity. |
| `PayrollTwoPartExcelExport` | `app/Domain/HRM/Exports/PayrollTwoPartExcelExport.php` | Generator berkas Excel 2-sheet (Sheet 1: Ringkasan Eksekutif & KPI; Sheet 2: Buku Besar Penggajian Detil). |
| `DataExchangeService` | `app/Domain/Import/DataExchangeService.php` | Ekspor dan impor data karyawan serta rekap presensi dalam format spreadsheet. |

## 4. Inventaris Model & Entitas Database
| Model Eloquent | Tabel Database | Keterangan Relasi Multi-Tenant |
|---|---|---|
| `User` | `users` | Akun pengguna sistem dengan nomor telepon, email, dan status verifikasi. |
| `BusinessMembership` | `business_memberships` | Profil keanggotaan bisnis, jabatan, tipe kerja, gaji pokok, tunjangan, data BPJS, dan rekening bank. |
| `EmployeeAttendance` | `employee_attendances` | Catatan presensi masuk/pulang, foto verifikasi, koordinat GPS, jarak dari lokasi toko, dan status validitas. |
| `AttendanceCorrection` | `attendance_corrections` | Pengajuan koreksi jam presensi yang membutuhkan persetujuan manajer/owner. |
| `EmployeeLoan` | `employee_loans` | Buku besar kasbon karyawan, plafon pinjaman, tenor cicilan, sisa saldo, dan status persetujuan. |
| `Payroll` | `payrolls` | Header batch penggajian bulanan, periode tahun/bulan, total gaji kotor/bersih, total potongan, dan status (*draft, approved, paid*). |
| `PayrollItem` | `payroll_items` | Baris slip gaji per karyawan dalam batch, menyimpan snapshot penghitungan dan token slip gaji digital. |
| `WorkShift` | `work_shifts` | Master shift kerja (jam masuk, jam pulang, toleransi keterlambatan). |
| `EmployeeSchedule` | `employee_schedules` | Penugasan jadwal shift kerja harian/mingguan per karyawan. |
| `Location` | `locations` | Master lokasi outlet toko fisik dengan titik koordinat latitude, longitude, dan radius geofence (meter). |
| `AuditLog` | `audit_logs` | Jejak audit keamanan mencatat siapa melakukan apa, waktu, IP address, dan level risiko. |

## 5. Inventaris Rute Web (`routes/web.php`)
```
GET    /hrm                         hrm.index
POST   /hrm/employees               hrm.employees.store
PUT    /hrm/employees/{membership}  hrm.employees.update
DELETE /hrm/employees/{membership}  hrm.employees.destroy
POST   /hrm/face-register/{user}    hrm.face.register
POST   /hrm/loans                   hrm.loans.store
POST   /hrm/loans/{loan}/cancel     hrm.loans.cancel
GET    /hrm/payrolls                hrm.payrolls.index
GET    /hrm/payrolls/create         hrm.payrolls.create
POST   /hrm/payrolls                hrm.payrolls.store
GET    /hrm/payrolls/{payroll}      hrm.payrolls.show
POST   /hrm/payrolls/{payroll}/approve  hrm.payrolls.approve
POST   /hrm/payrolls/{payroll}/pay      hrm.payrolls.pay
DELETE /hrm/payrolls/{payroll}          hrm.payrolls.destroy
GET    /hrm/payrolls/{payroll}/export-excel hrm.payrolls.export-excel
GET    /hrm/payrolls/{payroll}/export-csv   hrm.payrolls.export
GET    /hrm/payrolls/{payroll}/export-bank  hrm.payrolls.export-bank
GET    /hrm/payslip/{item}          hrm.payslip.show
GET    /hrm/payslip/public/{token}  hrm.payslip.public (Public Route)
GET    /hrm/export/employees        hrm.export.employees
GET    /hrm/export/attendance       hrm.export.attendance
```

## 6. Inventaris Blade Views
1. `resources/views/app/hrm/index.blade.php`: Dashboard Hub HRM terpadu dengan Bento KPI Metrics, tab navigasi Karyawan, Presensi, Koreksi, Kasbon, Penggajian, dan Lokasi Geofence.
2. `resources/views/app/hrm/payroll/create.blade.php`: Wizard pembuatan batch penggajian bulanan dengan input dinamis gaji, tunjangan, lembur, dan preview otomatis.
3. `resources/views/app/hrm/payroll/show.blade.php`: Tampilan detail batch penggajian, ringkasan biaya perusahaan, daftar slip karyawan, dan aksi approval/pembayaran.
4. `resources/views/app/hrm/payroll/payslip.blade.php`: Slip gaji digital interaktif Apple HIG dengan generator pesan WhatsApp dan mode publik aman.
