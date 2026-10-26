# Architecture & Deep Business Workflow Audit: Modul Purchasing (Procure-to-Pay)

| Aspek | Spesifikasi |
| :--- | :--- |
| **Fokus Workflow** | Alur Pengadaan Lengkap (Procure-to-Pay), 3-Way Matching, Approval Multi-Level, & Solo-Mode Instant Stock-In |
| **Modul Terkait** | Products (Katalog Hulu) $\rightarrow$ **Purchasing** $\rightarrow$ Inventory (Stok Gudang) $\rightarrow$ Finance (Buku Kas & Jurnal AP) |
| **Status Verifikasi** | **Verified & Compliant** |

---

## 1. Arsitektur Alur Bisnis Pengadaan (Procure-to-Pay Cycle)

```
[1. PEMBUATAN PO]
Staf membuat Purchase Order (Vendor / Client Order)
  ├── Item: Produk Jadi atau Bahan Baku (Materials)
  ├── Evaluasi Plafon Entitlement PO Bulanan
  └── Evaluasi Aturan Approval Multi-Level (Bila Aktif di Bisnis Corporate)
            │
            ▼
[2. OTORISASI / APPROVAL WORKFLOW]
Maker -> Approver (Supervisor / Manajer) -> Releaser (Direktur / Owner)
  ├── Jika Nilai PO >= Ambang Batas Rule: Status Menunggu Persetujuan
  └── Setelah Disetujui: PO Berstatus CONFIRMED
            │
            ▼
[3. PENERIMAAN BARANG FISIK (GOODS RECEIPT)]
Staf Gudang memeriksa kiriman vendor di lokasi cabang
  ├── Penerimaan Parsial (Partially Invoiced/Received) atau Lengkap (Completed)
  ├── Pencatatan Batch Number & Expiry Date (Bahan Makanan/Obat)
  ├── Auto-Update Stok Masuk (StockMovement: TYPE_GOODS_RECEIPT)
  └── Auto-Create Tagihan Hutang (SupplierInvoice: STATUS_UNPAID)
            │
            ▼
[4. PENYELESAIAN TAGIHAN SUPPLIER (ACCOUNTS PAYABLE)]
Bagian Keuangan / Owner membayar hutang ke supplier
  ├── Proteksi Anti-Fraud: Jika Pembayaran >= Rp 5.000.000, Wajib PIN Supervisor
  ├── Pemotongan Saldo Kas / Bank (CashLedgerService Outflow)
  └── Auto-Jurnal Akuntansi: Debit Hutang Usaha, Kredit Kas/Bank
            │
            ▼
[5. KASUS RETUR PEMBELIAN (PURCHASE RETURN)]
Jika terdapat barang cacat/rusak/kadaluarsa saat inspeksi
  ├── Pembuatan Dokumen Retur berbasis Goods Receipt Asal
  ├── Verifikasi PIN Supervisor saat Approval
  └── Pemotongan Otomatis Stok Gudang saat Retur Diselesaikan (Completed)
```

---

## 2. Audit Workflow Mendalam (Deep Dive Workflows)

### Workflow 1: 3-Way Matching (Kesesuaian PO, Penerimaan, & Tagihan)
1. **Integritas Kuantitas & Harga:**
   - Saat vendor mengirim barang, staf gudang membuka `/purchasing/receipts/{po}/create`.
   - `GoodsReceiptService` secara ketat memvalidasi kuantitas yang diterima terhadap sisa kuantitas PO yang belum dikirim. Jika kuantitas melebihi pesanan (`quantity > remaining`), sistem secara otomatis melempar `ValidationException` dan menolak transaksi.
2. **Otomatisasi Tagihan Hutang (AP Automation):**
   - Begitu `GoodsReceipt` berhasil tersimpan, sistem secara atomik via database transaction (`DB::transaction`) menerbitkan `SupplierInvoice` dengan nomor faktur `AP-{receipt_number}` dengan nilai total persis sama dengan hasil kali kuantitas diterima $\times$ harga satuan.
   - Ini mencegah tagihan fiktif (*phantom invoicing*) karena tagihan hanya bisa terbit dari bukti fisik barang masuk.

### Workflow 2: Otorisasi Multi-Level Dokumen (MAR Workflow)
- **Maker-Approver-Releaser:**
  - Pada entitas bisnis berskala Korporat / Menengah (`Business::SCALE_CORPORATE`), transaksi PO bernilai besar tidak dapat dikonfirmasi langsung oleh kasir atau staf pembelian.
  - `ApprovalWorkflowService::evaluateAndCreateRequest` mengevaluasi total nominal PO terhadap aturan `approval_rules`.
  - Jika memerlukan approval, status PO ditahan hingga seluruh level menyetujui. Method `confirm()` di controller memiliki guard:
    ```php
    if ($req && $req->isPending()) {
        return back()->with('error', "Pesanan tidak dapat dikonfirmasi karena masih menunggu otorisasi (Level {$req->current_level}).");
    }
    ```

### Workflow 3: Solo-Owner 1-Click Instant Stock-In (Beli Langsung ke Stok)
- **Konteks UMKM Indonesia:**
  - Warung makan, kedai kopi, dan toko retail kecil seringkali membeli bahan baku langsung di pasar tradisional (bayar tunai di tempat) tanpa membuat PO formal terlebih dahulu.
- **Mekanisme Fitur:**
  - Melalui endpoint `/purchasing/instant-stock-in`, pemilik usaha cukup memilih barang (produk atau bahan mentah), kuantitas, harga beli per unit, dan akun kas yang dipakai (misal: *Kas Kecil Toko*).
  - Sistem dalam 1 transaksi atomik langsung:
    1. Menambah saldo stok di gudang (`StockMovement::TYPE_GOODS_RECEIPT`).
    2. Mencatat pengeluaran uang tunai di buku kas (`CashLedgerService::recordOutflow`).
    3. Memperbarui harga modal rata-rata bergerak (*Moving Average Cost*).

### Workflow 4: Pengendalian Retur Pembelian & Proteksi PIN Supervisor
- Saat staf gudang menemukan barang rusak atau salah kirim dari supplier:
  1. Dokumen retur dibuat via `/purchasing/returns/create` terhubung ke ID `goods_receipt` yang valid.
  2. Saat dokumen disetujui (`approve`) atau diselesaikan (`complete`), jika bisnis memiliki `pos_supervisor_pin`, controller memverifikasi hash PIN sebelum eksekusi.
  3. Saat diselesaikan, stok gudang dipotong kembali (`StockMovement::TYPE_RETURN`) dan dicatat dalam `AuditLog`.
