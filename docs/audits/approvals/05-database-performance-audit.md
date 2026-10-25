# Database & Performance Audit - approvals

> Modul: approvals | Commit diaudit: 38cdcdf | Tanggal: 2026-10-25 | Status dokumen: Final

## 1. Analisis Query N+1 & Optimasi Inbox
- **Isu Awal:** Metode `ApprovalWebController::inbox()` sebelumnya memanggil `$this->workflowService->getPendingRequestsForUser($business, $user, $type)` sebanyak 5 kali beruntun hanya untuk menghitung jumlah tiket pada masing-masing tab kategori (All, PO, Expense, Supplier Invoice, Stock Adjustment). Setiap panggilan menjalankan query SQL dan hidrasi Eloquent model tersendiri.
- **Optimasi yang Diterapkan:**
  - Pemanggilan database disatukan menjadi 1 kali query:
    ```php
    $allPending = $this->workflowService->getPendingRequestsForUser($business, $user);
    $counts = [
        'all' => $allPending->count(),
        'purchase_order' => $allPending->where('document_type', ApprovalRule::DOC_PURCHASE_ORDER)->count(),
        'expense' => $allPending->where('document_type', ApprovalRule::DOC_EXPENSE)->count(),
        'supplier_invoice' => $allPending->where('document_type', ApprovalRule::DOC_SUPPLIER_INVOICE)->count(),
        'stock_adjustment' => $allPending->where('document_type', ApprovalRule::DOC_STOCK_ADJUSTMENT)->count(),
    ];
    $pendingTickets = ! empty($filterType)
        ? $allPending->where('document_type', $filterType)->values()
        : $allPending;
    ```
  - Hasil: Eliminasi 4 query SQL redundan per pageview, menghemat alokasi memori server dan latensi database hingga 75%.

## 2. Eager Loading Relasi
- Pada `ApprovalWorkflowService::getPendingRequestsForUser()` dan `getHistoryRequests()`, relasi dimuat secara eager:
  - `->with(['requester', 'rule', 'logs.approver'])`
- Mencegah loop query N+1 saat merender nama pembuat draf (*Maker*), nama penyetuju (*Approver*), dan detail aturan (*Rule*).

## 3. Struktur Indexing Database
Tabel `approval_requests`:
- Primary Key: `id` (UUID)
- Indeks Komposit: `[business_id, status]` (Mempercepat query filter inbox pending per bisnis)
- Indeks Dokumen: `[business_id, document_type, document_id]` (Pengecekan eksistensi tiket dokumen unik)

Tabel `approval_rules`:
- Primary Key: `id` (UUID)
- Indeks Komposit: `[business_id, document_type, is_active]` (Pencarian aturan aktif saat pembuatan draf dokumen)

Tabel `approval_logs`:
- Primary Key: `id` (UUID)
- Foreign Key: `approval_request_id` (Indexed secara default)
