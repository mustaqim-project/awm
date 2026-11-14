# Handoff Report  Explorer 2 (Mobile Architecture Explorer)

**Task:** Survey Mobile Architecture (`cooca_my_own`) for Fase 2 (Logistik & Gudang Toko) and Fase 3 (Customer Piutang & Omnichannel Digital)  
**Working Directory:** `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_2`  
**Handoff Type:** Hard (Task complete)

---

## 1. Observation

1. **Flutter SDK & Tooling**:
   - Tool execution `flutter --version` confirmed:
     `Flutter 3.47.2 • channel stable • https://github.com/flutter/flutter.git`
     `Tools • Dart 3.13.2 • DevTools 2.60.0`
2. **Dependency Baseline in `pubspec.yaml`**:
   - `c:\laragon\www\cooca_my_own\pubspec.yaml` lines 36-52 contains:
     - `cupertino_icons: ^1.0.8`
     - `intl: ^0.20.3`
     - `http: ^1.2.2`
     - `shared_preferences: ^2.3.2`
     - `camera: ^0.12.1`
     - `mobile_scanner: ^7.4.2`
   - `url_launcher` is NOT listed in `pubspec.yaml`.
   - Tool execution `dart pub add --dry-run url_launcher` in `c:\laragon\www\cooca_my_own` finished with code 0:
     `+ url_launcher 6.3.3`
     `Would change 8 dependencies.` with zero conflicts.
3. **Quality & Test Baseline**:
   - Tool execution `flutter analyze` in `c:\laragon\www\cooca_my_own` finished with:
     `Analyzing cooca_my_own... No issues found! (ran in 43.0s)`
   - Tool execution `flutter test` in `c:\laragon\www\cooca_my_own` passed all 33 tests across 6 files:
     `00:07 +33: All tests passed!`
4. **Existing State Management & Services**:
   - `ApiClient` in `lib/core/services/api_client.dart`: Singleton providing `get`, `post`, `put`, `delete` with Sanctum Bearer token and `X-Business-Id` header.
   - `MerchantState` in `lib/state/merchant_state.dart`: `ChangeNotifier` singleton holding `locations`, `selectedLocationId`, `customers`, and `pendingOnlineOrdersCount` (line 63).
   - `PosOfflineSyncManager` in `lib/core/services/pos_offline_sync_manager.dart`: Handles offline transaction persistence with UUID v4 idempotency.
   - `InventoryService` in `lib/core/services/inventory_service.dart`: Already handles stock adjustments, opname, instant stock-in, and movement logs. Needs additional methods for POs and Stock Transfers.
5. **Existing UI Points of Contact**:
   - `InventoryScreen` in `lib/screens/inventory_screen.dart` and `InventoryModals.showQuickActionSheet` in `lib/screens/inventory/inventory_modals.dart` (lines 429-487) currently provide actions for Add Product, Add Material, Add Service, Instant Stock In, and Write-Off.
   - `PosPaymentSheet` in `lib/screens/pos/pos_payment_sheet.dart`: Line 868 already lists `{'id': 'customer_credit', 'label': 'Kasbon / Piutang', 'icon': CupertinoIcons.clock}` and lines 1293-1402 implement credit limits and notes, but lacks a direct view of debt history and repayment.
   - `PosReceiptScreen` in `lib/screens/pos/pos_receipt_screen.dart`: Lines 1319-1344 currently copy WhatsApp receipt text to `Clipboard` without invoking official API or native intent.
   - `DashboardScreen` in `lib/screens/dashboard_screen.dart` and `PosScreen` in `lib/screens/pos_screen.dart` (lines 1355-1417): Have notification bell and action bars where online storefront orders badge and modal can be mounted.

---

## 2. Logic Chain

1. **Dependency Validation**:
   - Observation 2 demonstrates that `mobile_scanner` is already available for barcode scanning in Fase 2 (`GoodsReceiptScreen` and `StockTransferScreen`).
   - For Fase 3 WhatsApp fallback, Observation 2 confirms `url_launcher` is missing, but a dry-run test confirms `url_launcher: ^6.3.3` resolves cleanly without breaking any existing package constraints.
2. **Fase 2 Architectural Path**:
   - Based on Observation 4 and 5, `InventoryService` provides the pattern for multi-warehouse API operations.
   - Adding `getPendingPurchaseOrders()`, `getPurchaseOrderDetail()`, and `submitGoodsReceipt()` in `InventoryService` will feed the new `GoodsReceiptScreen` (`lib/screens/inventory/goods_receipt_screen.dart`).
   - Adding `getTransfers()`, `dispatchTransfer()`, and `receiveTransfer()` will feed the new `StockTransferScreen` (`lib/screens/inventory/stock_transfer_screen.dart`).
   - Tying these screens into `InventoryModals.showQuickActionSheet` ensures seamless access from the existing inventory catalog UI.
3. **Fase 3 Architectural Path**:
   - In `PosPaymentSheet`, `customer_credit` is already present. Hooking the customer credit banner to a new modal `PosCustomerDebtSheet` (`lib/screens/pos/pos_customer_debt_sheet.dart`) fulfills the debt history and repayment requirement.
   - In `PosReceiptScreen`, modifying `_shareViaWhatsApp` to first call `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt` and falling back to `launchUrl(Uri.parse('whatsapp://send?...'))` provides a resilient WhatsApp sharing experience.
   - Using `MerchantState.pendingOnlineOrdersCount` to trigger badges in `dashboard_screen.dart` and `pos_screen.dart` creates parity with the backend storefront/QR table orders.
4. **Zero Feature Leakage**:
   - As observed in `industry_gating_test.dart`, batch/expiry inputs in `GoodsReceiptScreen` must be gated for Pharmacy (`state.isPharmacy`) and F&B (`state.isFoodIndustry`), preventing feature pollution for retail/workshops.

---

## 3. Caveats

1. **Physical Device WhatsApp Intent**:
   - In Android emulator or headless test environments without WhatsApp installed, `canLaunchUrl(Uri.parse('whatsapp://...'))` returns false. The implementation must ensure graceful fallback to web WhatsApp or clipboard copy so tests and non-device runs never crash.
2. **Sanctum Backend Contract Synchronization**:
   - The exact JSON response keys for `GET /api/v1/inventory/purchase-orders/pending` and `GET /api/v1/customers/{id}/debts` must be aligned with Explorer 1's backend endpoints in `cooca_core`.

---

## 4. Conclusion

The mobile repository `c:\laragon\www\cooca_my_own` is in an exceptionally stable, clean state (`0 issues`, `33/33 tests passed`). All architectural patterns, token designs, and service layers are well-defined. The implementation agent can directly build Fase 2 (`GoodsReceiptScreen`, `StockTransferScreen`) and Fase 3 (`PosCustomerDebtSheet`, WhatsApp intent fallback, Storefront orders listener) by following the blueprint detailed in `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_2\report.md`.

---

## 5. Verification Method

To independently verify all findings and baseline claims:
1. **Analyze Baseline**:
   ```bash
   cd c:\laragon\www\cooca_my_own
   flutter analyze
   ```
   *Expected output:* `No issues found!`
2. **Run Test Baseline**:
   ```bash
   cd c:\laragon\www\cooca_my_own
   flutter test
   ```
   *Expected output:* `All tests passed!`
3. **Verify `url_launcher` compatibility**:
   ```bash
   cd c:\laragon\www\cooca_my_own
   dart pub add --dry-run url_launcher
   ```
   *Expected output:* Resolves `+ url_launcher 6.3.3` with exit code 0.
4. **Inspect Generated Report**:
   Inspect `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_2\report.md` for complete screen layouts, API contracts, and component designs.
