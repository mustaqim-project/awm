# Risk Register - customers

> Modul: Pelanggan (Customers Master Directory) | Tanggal: 2026-10-25

## Matriks Evaluasi Risiko

| ID Risiko | Deskripsi Risiko | Severity Awal | Mitigasi yang Diterapkan | Severity Pasca-Mitigasi | Status |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **RSK-CUST-01** | **Penghapusan Piutang Sepihak:** Pelanggan yang berhutang dihapus dari sistem sehingga tagihan kasbon tidak tertagih. | High | Penerapan Deletion Guard di `CustomerWebController::destroy` yang memblokir penghapusan jika `current_credit_balance > 0`. | Low | Mitigated |
| **RSK-CUST-02** | **Formula / CSV Injection (CWE-1236):** Data ekspor dieksploitasi dengan payload spreadsheet (`=cmd|' /C calc'!A0`). | Medium | Sanitasi otomatis pada `DataExchangeService::exportCustomers` menggunakan `CsvSanitizer::sanitize`. | Low | Mitigated |
| **RSK-CUST-03** | **Bypass Otorisasi Controller:** Akses langsung ke method store/update/destroy tanpa middleware level controller. | Medium | Implementasi Controller-Level RBAC `implements HasMiddleware` dengan mapping izin `customers.view`, `create`, `edit`, `delete`. | Low | Mitigated |
| **RSK-CUST-04** | **Data Leakage Lintas Tenant (IDOR):** Penyerang membaca atau mengedit data pelanggan tenant lain. | High | Multi-tenant isolation via `BusinessScope` dan verifikasi manual `abort_unless(business_id === $business->id, 403)`. | Low | Mitigated |
| **RSK-CUST-05** | **Duplikasi Kode Pelanggan:** Kode pelanggan yang sama terdaftar dua kali dalam satu bisnis. | Low | Validasi keunikan scoped per tenant `Rule::unique('customers', 'code')->where('business_id', ...)`. | Low | Mitigated |
| **RSK-CUST-06** | **N+1 Query pada Agregasi Pesanan:** Pemuatan relasi pesanan berulang kali di tabel daftar pelanggan. | Low | Penggunaan eager load `withCount(['invoices', 'purchaseOrders', 'posOrders'])`. | Low | Mitigated |
