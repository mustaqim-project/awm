# Audit Basis Data & Performa - HRM

> Modul: Sumber Daya Manusia & Penggajian (HRM & Payroll) | Tanggal: 2026-10-25

## 1. Skema Tabel & Strategi Pengindeksan (*Indexing*)
Struktur tabel modul HRM dirancang untuk skalabilitas tinggi dari UMKM gerai tunggal hingga jaringan retail multi-cabang dengan ratusan staf:

| Nama Tabel | Kolom Pengindeksan Utama (*Indexes*) | Tujuan Optimasi |
|---|---|---|
| `business_memberships` | `business_id`, `user_id`, `role_id`, `status` | Mempercepat lookup profil staf per cabang dan filter role. |
| `employee_attendances` | `business_id`, `user_id`, `date`, `status` | Mengoptimalkan agregasi kehadiran bulanan saat kalkulasi payroll. |
| `employee_loans` | `business_id`, `user_id`, `status` | Query instan kasbon aktif yang harus dipotong pada periode berjalan. |
| `payrolls` | `business_id`, `period_year`, `period_month`, `status` | Mencegah scan tabel penuh saat membuka riwayat penggajian tahunan. |
| `payroll_items` | `payroll_id`, `user_id`, `business_id`, `payslip_token` | Lookup instan slip gaji via token publik acak dan kalkulasi rekapitulasi. |
| `locations` | `business_id`, `is_active` | Pengecekan radius geofence lokasi outlet aktif. |
| `work_shifts` | `business_id` | Pemetaan jadwal shift operasional toko. |

---

## 2. Mitigasi Masalah Kueri N+1 (*Eager Loading*)
Pada controller HRM, seluruh pemanggilan relasi antar-model dioptimasi menggunakan *eager loading* eksplisit untuk mencegah overhead ratusan kueri SQL berulang:
- Pada `showPayroll(Payroll $payroll)`:
  ```php
  $payroll->load(['items.user', 'processedBy', 'approvedBy']);
  ```
- Pada `exportPayrollCsv` dan `exportPayrollBankCsv`:
  ```php
  $payroll->load(['items.user']);
  ```
- Pada `showPayslip(PayrollItem $item)`:
  ```php
  $item->load(['payroll.business', 'user']);
  ```
- Pada halaman utama `index(Request $request)`:
  Data anggota tim dimuat dengan eager loading relasi user, lokasi utama, dan shift bawaan dalam satu *batch query*.

---

## 3. Transaksi Atomik Database (*Atomic Transactions*)
Operasi keuangan dan penggajian melibatkan perubahan multi-tabel yang harus berhasil seluruhnya atau dibatalkan sama sekali (*all-or-nothing*):
Pada saat payroll ditandai dibayar (`markPayrollPaid` dalam `PayrollRunService.php`):
1. Mengubah status batch `payrolls` menjadi `PAID`.
2. Mengubah status seluruh baris `payroll_items` menjadi `PAID`.
3. Mengurangi sisa saldo kasbon `employee_loans` sejumlah potongan cicilan bulan bersangkutan.
4. Jika sisa saldo pinjaman menjadi nol, mengubah status pinjaman menjadi `STATUS_PAID`.
5. Menghasilkan entri audit log.

Seluruh proses di atas dibungkus dalam blok `DB::transaction(function () { ... })` sehingga jika terjadi kegagalan jaringan atau server crash di tengah proses, saldo kasbon karyawan dan status batch dipastikan tidak akan berada dalam kondisi data *corrupt* atau *inconsistent*.

---

## 4. Integritas Data & Proteksi Penghapusan (*Soft/Hard Deletes*)
- **Draf Penggajian (`destroyPayroll`):** Hanya batch berstatus `DRAFT` yang diizinkan untuk dihapus. Jika batch telah disetujui (`APPROVED`) atau dibayar (`PAID`), sistem menolak penghapusan dengan exception/abort demi menjaga integritas pembukuan.
- **Pencabutan Anggota Staf (`destroyEmployee`):** Menghapus relasi membership toko tanpa menghapus riwayat presensi lampau atau slip gaji historis karyawan yang telah terbit.
