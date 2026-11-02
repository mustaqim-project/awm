# Dispatch for Worker Fase 2 Mobile: Logistics & Warehouse UI

## Working Directory
`c:\laragon\www\cooca_core\.agents\teamwork\worker_fase2_mobile`

## Target Repository
`c:\laragon\www\cooca_my_own`

## Mission
Implement Milestone 2: Mobile Fase 2 (Logistik & Gudang Toko) on `c:\laragon\www\cooca_my_own` for production use.

## Authoritative Inputs
- `c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md` (Read first)
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_2\report.md` (Mobile investigation report)
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_3\report.md` (Cross-system API contracts)
- `c:\laragon\www\cooca_core\.agents\teamwork\worker_fase2_backend_2\handoff.md` (Backend endpoints implemented and verified)

## Mandatory Integrity Warning
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Detailed Scope of Work
1. **API Client & Inventory Service**:
   - In `c:\laragon\www\cooca_my_own\lib\core\services\inventory_service.dart`, add methods:
     - `getPendingPurchaseOrders({String? locationId})`
     - `getPurchaseOrder(String id)`
     - `submitGoodsReceipt(Map<String, dynamic> payload)`
     - `getTransfers({String? locationId, String? type, String? status})`
     - `dispatchTransfer(Map<String, dynamic> payload)`
     - `receiveTransfer(String transferId, {Map<String, dynamic>? payload})`
2. **Goods Receipt Screen**:
   - Create `c:\laragon\www\cooca_my_own\lib\screens\inventory\goods_receipt_screen.dart`:
     - PO dropdown/selector showing pending POs from API
     - Input delivery order / surat jalan number
     - Live camera barcode scan via `mobile_scanner` (matching scanned barcode with PO item list)
     - Actual received quantity vs damaged/reject quantity inputs
     - Lot/batch number and expiry date input fields (with date picker)
     - Submit button calling `submitGoodsReceipt` with success and error feedback
3. **Stock Transfer Screen**:
   - Create `c:\laragon\www\cooca_my_own\lib\screens\inventory\stock_transfer_screen.dart`:
     - 2 tabs (Apple HIG segmented control): Tab 0: Kirim Barang (Dispatch), Tab 1: Terima Barang (Inbound Scan)
     - Tab Kirim: Origin location locked to current active location, destination location picker, product selection with current stock indicator, driver/notes, dispatch button calling `dispatchTransfer`
     - Tab Terima: List of incoming in-transit transfers, detail view with barcode scan verification of items received, actual qty, receive button calling `receiveTransfer`
4. **Navigation Integration**:
   - In `c:\laragon\www\cooca_my_own\lib\screens\inventory\inventory_screen.dart`:
     - Add navigation actions in the quick action sheet / header to open `GoodsReceiptScreen` and `StockTransferScreen`
5. **Automated Verification**:
   - Implement widget and unit tests in `c:\laragon\www\cooca_my_own\test/goods_receipt_screen_test.dart` and `test/stock_transfer_screen_test.dart`
   - Run `flutter analyze` in `c:\laragon\www\cooca_my_own` and verify **0 issues found**
   - Run `flutter test` in `c:\laragon\www\cooca_my_own` and verify **100% tests pass**

## Deliverable
Write implementation report and handoff to `c:\laragon\www\cooca_core\.agents\teamwork\worker_fase2_mobile\handoff.md` and notify parent when complete.
