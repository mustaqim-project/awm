# Arsitektur & Alur Kerja Sistem - HRM & Penggajian

> Modul: Sumber Daya Manusia & Penggajian (HRM & Payroll) | Tanggal: 2026-10-25

## 1. Alur Kerja Siklus Hidup Karyawan (*Employee Lifecycle*)
```mermaid
sequenceDiagram
    autonumber
    actor HR as HR Admin / Owner
    participant Web as HrmWebController
    participant Ent as EntitlementService
    participant Face as FaceVerificationService
    participant DB as Database (Multi-Tenant)
    participant Log as AuditLog

    HR->>Web: Input Form Tambah Staf Baru (Data Profil, Gaji, BPJS, Bank)
    Web->>Ent: canAddMember(business)
    alt Kuota Staf Habis
        Ent-->>Web: false
        Web-->>HR: Error: Kuota paket tercapai (Batas Free Solo)
    else Kuota Tersedia
        Ent-->>Web: true
        Web->>DB: Buat/Cari User & BusinessMembership
        opt Ada Foto Wajah
            Web->>Face: registerFaceTemplate(business, user, photo)
            Face->>DB: Simpan Template Vektor Biometrik
        end
        Web->>Log: Catat AuditLog (hrm.employee_created)
        Web-->>HR: Sukses: Staf tersimpan & siap bertugas
    end
```

## 2. Alur Presensi Geofence & Biometrik Wajah
```mermaid
flowchart TD
    A[Staf Membuka Presensi Mobile] --> B{Izinkan Lokasi GPS?}
    B -- Tidak --> C[Gagal: Akses GPS Diperlukan]
    B -- Ya --> D[Hitung Jarak ke Koordinat Toko Fisik via Haversine]
    D --> E{Jarak <= Radius Geofence Toko?}
    E -- Tidak --> F[Ditolak: Berada di Luar Radius Lokasi Toko]
    E -- Ya --> G[Ambil Snapshot Foto Kamera Depan]
    G --> H[Pemeriksaan Liveness Anti-Spoofing]
    H --> I{Wajah Hidup & Bukan Layar/Kertas?}
    I -- Tidak --> J[Ditolak: Indikasi Pemalsuan Wajah]
    I -- Ya --> K[Pencocokan Vektor Wajah vs Template Terdaftar]
    K --> L{Cosine Similarity >= Ambang Batas?}
    L -- Tidak --> M[Ditolak: Wajah Tidak Cocok dengan Akun Terdaftar]
    L -- Ya --> N[Validasi Berhasil: Catat EmployeeAttendance Terverifikasi]
```

## 3. Siklus Hidup Kasbon Karyawan (*Loan Lifecycle*)
```mermaid
stateDiagram-v2
    [*] --> Active: Kasbon Dicatat & Disetujui (storeLoan)
    Active --> Deducting: Pemotongan Sebagian pada Batch Payroll
    Deducting --> Deducting: Cicilan Bulanan Berlanjut
    Deducting --> Paid: Sisa Saldo Rp 0 (Lunas)
    Active --> Cancelled: Dibatalkan Manual (cancelLoan)
    Paid --> [*]
    Cancelled --> [*]
```

## 4. Pipeline Pembuatan & Eksekusi Batch Penggajian (*Payroll Pipeline*)
```mermaid
sequenceDiagram
    autonumber
    actor Owner as Bisnis Owner / Direksi
    participant Ctrl as HrmWebController
    participant RunSvc as PayrollRunService
    participant CalcSvc as PayrollCalculationService
    participant DB as Database
    participant Audit as AuditLog

    Owner->>Ctrl: Buat Penggajian (Bulan & Tahun Periode)
    Ctrl->>RunSvc: generatePayrollRun()
    loop Setiap Anggota Tim Aktif
        RunSvc->>CalcSvc: calculateMonthlyPayroll() (Gaji, BPJS, PPh21 TER, Lembur, Kasbon)
        CalcSvc-->>RunSvc: Data Kalkulasi Komprehensif
        RunSvc->>DB: Simpan PayrollItem (Status: Draft) + Token Slip Unik
    end
    RunSvc->>Audit: Catat AuditLog (hrm.payroll_created)
    RunSvc-->>Ctrl: Batch Tersimpan dalam Status DRAFT

    Owner->>Ctrl: Tinjau & Klik Setujui (approvePayroll)
    Ctrl->>RunSvc: approvePayroll()
    RunSvc->>DB: Update Status Batch -> APPROVED
    RunSvc->>Audit: Catat AuditLog (hrm.payroll_approved)

    Owner->>Ctrl: Proses Pembayaran (payPayroll)
    Ctrl->>RunSvc: markPayrollPaid(metode: bank_transfer/cash)
    RunSvc->>DB: Update Status Batch & Items -> PAID
    RunSvc->>DB: Potong Saldo Kasbon Karyawan (Kurangi remaining_balance)
    RunSvc->>Audit: Catat AuditLog (hrm.payroll_paid)
    Ctrl-->>Owner: Selesai: Slip Siap Dikirim ke WhatsApp Karyawan
```

## 5. Pola Isolasi Multi-Tenant (*Tenant Isolation*)
Seluruh model data HRM (`BusinessMembership`, `EmployeeAttendance`, `AttendanceCorrection`, `EmployeeLoan`, `Payroll`, `PayrollItem`, `WorkShift`, `EmployeeSchedule`, `Location`) menggunakan arsitektur multi-tenant berbasis `business_id`:
1. **Global Scope `BelongsToBusiness`:** Memastikan setiap query Eloquent secara implisit memfilter `where business_id = Context::getBusinessId()`.
2. **Controller Assertions:** Pada setiap akses detail ID spesifik, dilakukan verifikasi eksplisit:
   ```php
   if ($payroll->business_id !== $business->id) {
       abort(404);
   }
   ```
   Pengecekan ini mencegah serangan *Insecure Direct Object Reference* (IDOR) lintas tenant.
