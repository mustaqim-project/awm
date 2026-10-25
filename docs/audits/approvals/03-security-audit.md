# Security Audit - approvals

> Modul: approvals | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## 1. Controller-Level RBAC Enforcement
- `ApprovalWebController` menerapkan interface `Illuminate\Routing\Controllers\HasMiddleware` dengan deklarasi statis:
  ```php
  public static function middleware(): array
  {
      return [
          new Middleware('require.permission:approvals.view', only: ['inbox', 'history']),
          new Middleware('require.permission:approvals.manage', only: [
              'approve',
              'reject',
              'rulesIndex',
              'rulesStore',
              'rulesUpdate',
              'rulesDestroy',
          ]),
      ];
  }
  ```
- **Hasil Pengujian RBAC:**
  - Pengguna tanpa permission `approvals.view` diarahkan ke `portal` dengan status 302 (Web) dan 403 Forbidden (API/JSON).
  - Pengguna dengan permission `approvals.view` saja tidak dapat mengakses atau memanipulasi aturan `approval-rules.*` (403/Redirect).

## 2. Multi-Tenant Scoping & Anti-IDOR
- Setiap aksi modifikasi entitas (`ApprovalRequest` atau `ApprovalRule`) diverifikasi terhadap `Context::businessId()`:
  - `abort_unless($approvalRequest->business_id === $business->id, 404);`
  - `abort_unless($approvalRule->business_id === $business->id, 404);`
- **Hasil Pengujian Cross-Tenant:**
  - Percobaan Owner Bisnis A menyetujui tiket milik Bisnis B ditolak dengan status HTTP 404.
  - Percobaan Owner Bisnis A menolak tiket milik Bisnis B ditolak dengan status HTTP 404.
  - Percobaan Owner Bisnis A memodifikasi/menghapus aturan milik Bisnis B ditolak dengan status HTTP 404.

## 3. Proteksi CSRF & Mass Assignment
- Seluruh form (Approve, Reject, Tambah Aturan, Ubah Aturan, Hapus Aturan) memiliki directive `@csrf`.
- Form modal penolakan dan persetujuan menggunakan metode POST eksplisit.
- Validasi ketat pada `rulesStore` dan `rulesUpdate` mencakup pembatasan tipe dokumen (`in:purchase_order,expense,supplier_invoice,stock_adjustment`), rentang nominal positif (`gte:0`, `gte:min_amount`), dan batasan tingkat otorisasi (`between:1,3`).

## 4. Audit Trail Anti-Fraud
- Setiap aksi mutasi menghasilkan rekaman polimorfik pada tabel `audit_logs`:
  - `approval.approved`: Pencatatan siapa, level berapa, IP address, user agent, dan timestamp.
  - `approval.rejected`: Pencatatan alasan penolakan, kategori risiko Medium, dan referensi dokumen.
  - `approval_rule.created`, `approval_rule.updated`, `approval_rule.deleted`: Pelacakan perubahan kebijakan otorisasi pimpinan.
