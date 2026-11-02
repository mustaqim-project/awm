Implementasikan Fase 1 dari rencana integrasi fitur di C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\acf5365f-47d8-4edf-9b08-e7eb3389da96\plan_core_to_mobile_features.md, yang mencakup fitur Petty Cash / Quick Expense Kasir dan Owner Approval Center secara terintegrasi antara backend cooca_core dan mobile cooca_my_own untuk penggunaan production.

Working directory: c:\laragon\www\cooca_core
Integrity mode: development

Dokumen acuan: C:\Users\Ulfah Ramadhani\.gemini\antigravity\brain\acf5365f-47d8-4edf-9b08-e7eb3389da96\plan_core_to_mobile_features.md

## Requirements

### R1. Petty Cash / Quick Expense Kasir
Implementasikan fitur Petty Cash / Quick Expense Kasir terintegrasi pada backend `c:\laragon\www\cooca_core` dan mobile `c:\laragon\www\cooca_my_own`:
- Backend `cooca_core`: Buat endpoint API `POST /api/v1/pos/expenses` yang dilindungi autentikasi Sanctum, memvalidasi shift POS yang sedang aktif/terbuka, mencatat mutasi pengeluaran kas kecil pada cash ledger (`CashLedgerWebController` / `CashLedgerEntry`), serta mendukung upload foto/bukti nota struk.
- Mobile `cooca_my_own`: Buat komponen modal `PosExpenseModal` (`lib/screens/pos/pos_expense_modal.dart`) yang mendukung pengambilan foto nota menggunakan kamera HP, sediakan tombol akses di menu shift POS kasir, dan integrasikan dengan `pos_shift_modal.dart` sehingga pengeluaran kas kecil otomatis memotong nilai expected cash saat penutupan shift.

### R2. Owner Approval Center
Implementasikan fitur Owner Approval Center terintegrasi pada backend `c:\laragon\www\cooca_core` dan mobile `c:\laragon\www\cooca_my_own`:
- Backend `cooca_core`: Buat endpoint API `GET /api/v1/owner/approvals/pending` untuk mengambil daftar permintaan persetujuan yang tertunda dan `POST /api/v1/owner/approvals/{id}/action` dengan payload aksi (`approve` atau `reject`) beserta alasan untuk persetujuan pembatalan transaksi (*void*), diskon di atas batas, dan koreksi absensi melalui model persetujuan terkait.
- Mobile `cooca_my_own`: Buat layar `OwnerApprovalScreen` (`lib/screens/approvals/owner_approval_screen.dart`), tambahkan indikator/badge jumlah pending approval pada `dashboard_screen.dart`, serta sediakan interaksi kartu geser (swipe to approve / reject) dengan haptic feedback Apple HIG.

### R3. Automated Testing and Verification
Buat dan jalankan test suite otomatis baru sesuai konvensi testing pada masing-masing repositori:
- Backend `cooca_core`: Buat test suite otomatis menggunakan Pest atau PHPUnit untuk menguji validasi, otorisasi, pencatatan mutasi cash ledger saat petty cash dicatat, serta alur approval pending dan aksi approve/reject.
- Mobile `cooca_my_own`: Buat automated test (unit/widget test di direktori `test/`) untuk form/modal petty cash dan interaksi approval, serta pastikan `flutter analyze` dan `flutter test` berjalan bersih tanpa error.

## Acceptance Criteria

### Petty Cash / Quick Expense Kasir
- [ ] Endpoint `POST /api/v1/pos/expenses` di `cooca_core` memvalidasi shift aktif, merekam mutasi pengeluaran kas kecil pada cash ledger, menyimpan file/bukti nota, dan mengembalikan respon sukses
- [ ] Komponen `PosExpenseModal` di `cooca_my_own` memungkinkan kasir memasukkan data pengeluaran dan foto nota, mengirimkannya ke backend, serta memperbarui kalkulasi expected cash pada `pos_shift_modal.dart` saat tutup shift

### Owner Approval Center
- [ ] Endpoint `GET /api/v1/owner/approvals/pending` dan `POST /api/v1/owner/approvals/{id}/action` di `cooca_core` berhasil mengembalikan data approval tertunda dan memproses aksi persetujuan/penolakan (approve/reject)
- [ ] Layar `OwnerApprovalScreen` di `cooca_my_own` menampilkan daftar permintaan pending, mendukung interaksi swipe approve/reject, dan badge pending approvals terintegrasi pada `dashboard_screen.dart`

### Automated Verification
- [ ] Test suite baru (Pest/PHPUnit) di `cooca_core` mencakup endpoint petty cash dan approval center, serta seluruh pengujian lolos eksekusi
- [ ] Perintah `flutter analyze` pada `cooca_my_own` menghasilkan 0 issues/error, dan `flutter test` lolos pengujian tanpa kegagalan
