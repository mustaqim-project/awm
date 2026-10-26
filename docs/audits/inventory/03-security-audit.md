# Security & Authorization Audit: Modul Inventory (Stok, Mutasi, Opname, & Transfer)

## 1. Controller-Level RBAC & HasMiddleware Enforcement

Sebelum audit ini dilakukan, rute inventory di `routes/owner.php` mengandalkan middleware grup, namun controller `InventoryWebController` tidak mengimplementasikan interface `HasMiddleware`. Kini, controller telah diperkeras dengan pertahanan berlapis (*defense-in-depth*):

```php
final class InventoryWebController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('require.permission:inventory.view', only: [
                'stocks',
                'movements',
                'opnames',
                'transfers',
                'exportBlankOpname',
            ]),
            new Middleware('require.permission:inventory.manage', only: [
                'quickAdjust',
                'approveAdjustment',
                'rejectAdjustment',
                'storeOpname',
                'previewOpnameImport',
                'executeOpnameImport',
                'reconcileOpname',
                'storeTransfer',
                'receiveTransfer',
            ]),
        ];
    }
}
```

---

## 2. Maker-Checker Anti-Fraud & Otorisasi Penyesuaian Stok

Untuk mencegah manipulasi kehilangan inventori fiktif atau *theft covering*, COOCA menerapkan mekanisme **Maker-Checker** otomatis:
1. **Pengecekan Nilai Kehilangan (*Loss Cost Threshold*):**
   - Jika penurunan stok menimbulkan nilai kerugian melebihi ambang batas `min_amount` pada `ApprovalRule` bisnis:
     $$\text{Loss Amount} = (\text{Stok Sistem} - \text{Stok Baru}) \times \text{HPP}$$
   - Sistem **TIDAK AKAN** memotong stok secara langsung, melainkan menerbitkan dokumen `StockAdjustment` berstatus `pending_approval`.
2. **Otorisasi Berjenjang:**
   - Hanya pengguna dengan peran `owner`, `supervisor`, `admin`, atau peran yang didefinisikan pada aturan (`approver_role_level_1`) serta pemegang izin `approvals.manage` yang dapat mengeksekusi persetujuan atau penolakan.
   - Staf biasa yang mencoba menyetujui akan ditolak dengan respons `403 Forbidden` (`__('inventory.unauthorized_approval')`).

---

## 3. Pencegahan IDOR (Insecure Direct Object Reference) Multi-Tenant

Isolasi data antar-tenant diuji secara ketat untuk mencegah akses silang:
1. **Global Scope `BelongsToBusiness`:**
   - Model `InventoryStock`, `StockAdjustment`, `StockOpname`, `StockTransfer`, dan `Location` menerapkan trait `BelongsToBusiness`.
2. **Explicit Assertion di Controller:**
   - Pada metode persetujuan adjustment:
     `abort_unless($adjustment->business_id === $business->id, 404);`
   - Pada metode penolakan adjustment:
     `abort_unless($adjustment->business_id === $business->id, 404);`
   - Pada metode rekonsiliasi opname:
     `abort_unless($opname->business_id === $business->id, 404);`
   - Pada metode penerimaan transfer:
     `abort_unless($transfer->business_id === $business->id, 404);`
3. **Pengujian IDOR:**
   - Pengujian `test_adjustment_tenant_isolation_prevents_idor` membuktikan bahwa upaya persetujuan atau penolakan dokumen bisnis lain seketika menghasilkan HTTP 404 Not Found.

---

## 4. Audit Logging & Compliance Trail

Setiap aksi kritis yang mengubah kuantitas atau nilai buku stok dicatat ke dalam tabel `audit_logs` melalui `AuditLogService::log`:
- `STOCK_TRANSFER_CREATED`: Nomor transfer, gudang asal, gudang tujuan, jumlah item.
- `STOCK_TRANSFER_RECEIVED`: Identitas staf penerima, stempel waktu kedatangan barang fisik.
- `STOCK_OPNAME_RECONCILED`: Dokumen opname yang direkonsiliasi dan staf eksekutor.
- `STOCK_ADJUSTMENT_REJECTED`: Alasan penolakan dari supervisor dan identitas penolak.
- Mutasi stok tercatat permanen pada buku besar mutasi `stock_movements`.
