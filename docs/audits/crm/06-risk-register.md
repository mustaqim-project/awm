# Risk Register - crm

> Modul: CRM & Loyalty | Tanggal: 2026-10-25

## Matriks Evaluasi Risiko

| ID Risiko | Deskripsi Risiko | Severity Awal | Mitigasi yang Diterapkan | Severity Pasca-Mitigasi | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **RSK-CRM-01** | **Fraud Kasbon Kasir (Lapping Scheme):** Kasir menerima uang pelunasan piutang pelanggan secara tunai namun tidak menyetorkannya ke kas toko. | High | Integrasi atomik ke `CashAccount`, mutasi kas masuk wajib terbit, dan rekaman permanen pada `AuditLog`. | Low | Mitigated |
| **RSK-CRM-02** | **Ketiadaan Audit Trail Voucher:** Staf membuat voucher diskon fiktif atau menonaktifkan promo tanpa jejak. | Medium | Perekaman `AuditLog::create` untuk aksi `voucher.created` dan `voucher.status_toggled`. | Low | Mitigated |
| **RSK-CRM-03** | **Bypass Otorisasi Controller:** Akses langsung ke aksi manage tanpa hak akses melalui request forgery. | Medium | Implementasi Controller-Level RBAC `implements HasMiddleware` dengan filter `crm.view` dan `crm.manage`. | Low | Mitigated |
| **RSK-CRM-04** | **Data Leakage Lintas Tenant (IDOR):** Pengguna melihat data member atau mengubah status voucher tenant lain. | High | Multi-tenant isolation via `BusinessScope` dan verifikasi manual `abort_unless(business_id === Context::business()?->id, 403)`. | Low | Mitigated |
| **RSK-CRM-05** | **Over-discounting Voucher:** Nilai potongan persentase merugikan omzet pada transaksi skala besar. | Medium | Input opsional pembatas rupiah maksimal (`max_discount_amount`) dan minimum order (`min_order_amount`). | Low | Mitigated |
| **RSK-CRM-06** | **Mis-tap pada Perangkat Kasir Layar Sentuh:** Tombol aksi terlalu kecil menyebabkan kesalahan tap kasir saat jam sibuk. | Low | Standarisasi ukuran tombol ke Apple HIG minimum 44px (`h-11 min-h-[44px]`). | Low | Mitigated |
