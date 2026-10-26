# System Baseline: Modul Inventory (Stok, Mutasi, Opname, & Transfer)

| Detail | Deskripsi |
| :--- | :--- |
| **Modul** | Inventory (Stok Real-Time, Mutasi, Stock Opname, & Transfer Antar-Gudang) |
| **Namespace Controller** | `App\Http\Controllers\Web\Inventory\InventoryWebController` |
| **Domain Services** | `App\Domain\Inventory\StockService`<br>`App\Domain\Accounting\AutoJournalService`<br>`App\Domain\Import\DataExchangeService` |
| **Model Terkait** | `InventoryStock`, `StockMovement`, `StockAdjustment`, `StockAdjustmentItem`, `StockOpname`, `StockOpnameItem`, `StockTransfer`, `StockTransferItem`, `Location`, `Product`, `Material`, `ApprovalRule`, `AuditLog` |
| **Blade Views** | `resources/views/app/inventory/stocks.blade.php`<br>`resources/views/app/inventory/movements.blade.php`<br>`resources/views/app/inventory/opname.blade.php`<br>`resources/views/app/inventory/transfers.blade.php` |
| **File Bahasa** | `lang/id/inventory.php`<br>`lang/en/inventory.php` |
| **Test Suite** | `tests/Feature/InventoryModuleAuditTest.php` |

---

## 1. Pemetaan Rute Sistem (Route Inventory)

| Nama Rute | HTTP Method | URI | Controller Action | Middleware Guard |
| :--- | :--- | :--- | :--- | :--- |
| `inventory.stocks` | `GET` | `/inventory/stocks` | `InventoryWebController@stocks` | `require.permission:inventory.view` |
| `inventory.movements` | `GET` | `/inventory/movements` | `InventoryWebController@movements` | `require.permission:inventory.view` |
| `inventory.opnames.index` | `GET` | `/inventory/opnames` | `InventoryWebController@opnames` | `require.permission:inventory.view` |
| `inventory.transfers.index` | `GET` | `/inventory/transfers` | `InventoryWebController@transfers` | `require.permission:inventory.view` |
| `inventory.stocks.adjust` | `POST` | `/inventory/stocks/adjust` | `InventoryWebController@quickAdjust` | `require.permission:inventory.manage` |
| `inventory.adjustments.approve` | `POST` | `/inventory/adjustments/{adjustment}/approve` | `InventoryWebController@approveAdjustment` | `require.permission:inventory.manage` |
| `inventory.adjustments.reject` | `POST` | `/inventory/adjustments/{adjustment}/reject` | `InventoryWebController@rejectAdjustment` | `require.permission:inventory.manage` |
| `inventory.opnames.store` | `POST` | `/inventory/opnames` | `InventoryWebController@storeOpname` | `require.permission:inventory.manage` |
| `inventory.opnames.export-blank` | `GET` | `/inventory/opnames/export-blank` | `InventoryWebController@exportBlankOpname` | `require.permission:inventory.view` |
| `inventory.opnames.import-preview` | `POST` | `/inventory/opnames/import-preview` | `InventoryWebController@previewOpnameImport` | `require.permission:inventory.manage` |
| `inventory.opnames.import-execute` | `POST` | `/inventory/opnames/import-execute` | `InventoryWebController@executeOpnameImport` | `require.permission:inventory.manage` |
| `inventory.opnames.reconcile` | `POST` | `/inventory/opnames/{opname}/reconcile` | `InventoryWebController@reconcileOpname` | `require.permission:inventory.manage` |
| `inventory.transfers.store` | `POST` | `/inventory/transfers` | `InventoryWebController@storeTransfer` | `require.permission:inventory.manage`, `entitlement:transfer_stock` |
| `inventory.transfers.receive` | `POST` | `/inventory/transfers/{transfer}/receive` | `InventoryWebController@receiveTransfer` | `require.permission:inventory.manage` |

---

## 2. Struktur Skema Database Terkait
1. **`inventory_stocks`**:
   - Master kuantitas stok per lokasi (`business_id`, `location_id`, `product_id`, `material_id`, `quantity`, `last_cost`, `min_stock_alert`). Dilengkapi indeks unik komposit untuk mencegah duplikasi baris.
2. **`stock_movements`**:
   - Buku besar mutasi (*immutable stock ledger*), mencatat `quantity_change`, `current_quantity`, `movement_type` (pos_sale, pos_refund, purchase_receive, purchase_return, transfer_in, transfer_out, opname, adjustment), `unit_cost`, dan `user_id`.
3. **`stock_adjustments` & `stock_adjustment_items`**:
   - Dokumen koreksi stok fisik dengan alur persetujuan maker-checker (`status`: `pending_approval`, `approved`, `rejected`, `total_loss_cost`, `approved_by`, `approved_at`).
4. **`stock_opnames` & `stock_opname_items`**:
   - Dokumen penghitungan fisik periodik (`status`: `counting`, `reconciled`), mencatat `system_quantity`, `physical_quantity`, `difference_quantity`, `unit_cost`, dan `total_difference_cost`.
5. **`stock_transfers` & `stock_transfer_items`**:
   - Dokumen perpindahan stok antar-gudang (`status`: `pending`, `received`, `cancelled`, `source_location_id`, `destination_location_id`, `sent_at`, `received_at`).
