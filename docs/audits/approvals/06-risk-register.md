# Risk Register - approvals

> Modul: approvals | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

| ID Risiko | Kategori | Deskripsi Risiko | Dampak Awal | Probabilitas | Mitigasi yang Diterapkan | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **RSK-APP-01** | Integritas Audit | Kegagalan konfirmasi PO pada skala UMKM akibat constraint `auditable_type` NOT NULL | High | High | Memperbaiki pemanggilan `AuditLog::create` di `PurchaseOrderWebController.php:270` dengan menyertakan `auditable_type` dan `auditable_id`. | **Resolved** |
| **RSK-APP-02** | Keamanan / RBAC | Ketiadaan controller-level middleware membuka celah otorisasi jika deklarasi route dipindahkan | Medium | Medium | Mengimplementasikan `HasMiddleware` pada `ApprovalWebController` dengan guard `approvals.view` dan `approvals.manage`. | **Resolved** |
| **RSK-APP-03** | Keamanan / Multi-Tenant | Manipulasi IDOR lintas bisnis pada tiket atau aturan otorisasi | High | Low | Validasi kepemilikan tenant eksplisit `abort_unless(model->business_id === $business->id, 404)` pada seluruh endpoint mutasi. | **Resolved** |
| **RSK-APP-04** | Performa Database | Redundant queries pada inbox akibat pemanggilan 5x `getPendingRequestsForUser` | Medium | High | Penyatuan query tunggal dan pengolahan tab counts via In-Memory Collection Filtering. | **Resolved** |
| **RSK-APP-05** | Operasional Bisnis | Alasan penolakan kosong atau ambigu sehingga menghambat alur kerja draf | Medium | Medium | Validasi server-side dan client-side bahwa catatan alasan penolakan wajib diisi (minimal 3 karakter). | **Resolved** |
| **RSK-APP-06** | Audit Trail Anti-Fraud | Perubahan aturan plafon otorisasi tidak terekam pada audit log sistem | Medium | Low | Penambahan pencatatan `AuditLog::create` pada store, update, dan delete approval rules. | **Resolved** |
