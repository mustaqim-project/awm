# 04. Cross-Platform Parity Mapping & Architectural Division

**Platform Ecosystem**: COOCA Web Backoffice (`cooca_core`) & Mobile Client (`cooca_my_own`)  
**Target Architecture**: Dual-Platform Multi-Tenant SaaS ERP  
**Document Classification**: Authoritative Engineering Parity Specification  
**Version**: 1.0.0 (Production Verified)  

---

## 1. Executive Parity Topology

The COOCA ecosystem is deliberately architected with a clear division of operational responsibilities between the Web Backoffice and the Mobile Application:
- **Web Backoffice (`cooca_core`)**: The strategic and administrative center of truth. Responsible for complex system configuration, full SAK EMKM double-entry general ledger accounting, multi-level BOM formulation, high-throughput spreadsheet data ingestion, omnichannel OAuth handshakes, visual storefront theme styling, and platform-wide superadmin governance.
- **Mobile Client (`cooca_my_own`)**: The operational edge runtime. Tailored for on-the-ground cashier speed, shop floor execution, native hardware interfacing (ESC/POS Bluetooth thermal printing, camera barcode scanning, GPS geofencing, native biometric selfie capture), and offline fault tolerance.

```
+-----------------------------------------------------------------------------------+
|                        COOCA DUAL-PLATFORM ARCHITECTURAL PARITY                   |
+-----------------------------------------------------------------------------------+
|                           COMMON CORE BUSINESS API                                |
|                        (48+ Endpoints under /api/v1/*)                            |
+-----------------------------------------+-----------------------------------------+
                                          |
                   +----------------------+----------------------+
                   |                                             |
                   v                                             v
+------------------------------------+       +------------------------------------+
| WEB BACKOFFICE SPECIALIZATION      |       | MOBILE EDGE SPECIALIZATION         |
| - Double-Entry General Ledger      |       | - Bluetooth Thermal ESC/POS (SPP)  |
| - SAK EMKM Chart of Accounts (COA) |       | - Camera Barcode Viewfinder Scanner|
| - Multi-Level Recipe BOM Builder   |       | - Offline SQLite Queue (Idempotent)|
| - Machine & Labor Costing Centers  |       | - GPS Radius Radar Clock-In (50m)  |
| - Bulk Excel Ingestion Wizards     |       | - Anti-Spoofing Native Telemetry   |
| - Visual Storefront Theme Designer |       | - Dynamic Industry KDS Boards      |
| - Platform Superadmin Governance   |       | - Ultra-Compact High-Speed POS UI  |
+------------------------------------+       +------------------------------------+
```

---

## 2. Shared & Dual-Platform Parity Matrix

The following matrix documents all features supported across both platforms, providing the exact file paths, API contracts, controllers, and Blade templates.

| Feature Domain | Mobile Screen & Dart File | Mobile Service & API Endpoint | Web Controller & Route | Web Blade View File | Parity Assessment & Operational Behavior |
|---|---|---|---|---|---|
| **Authentication & Workspace Bootstrap** | `LoginScreen`<br>`lib/screens/login_screen.dart` | `AuthService.login()`<br>`POST /api/v1/auth/login` | `AuthWebController`<br>`routes/auth.php`<br>`POST /login` | `resources/views/auth/login.blade.php` | **100% Parity**. Mobile uses native Google SSO SDK; Web uses Socialite. Both authenticate into identical backend user sessions. |
| **Workspace Tenant Switching** | `BusinessSwitcherSheet`<br>`lib/core/widgets/business_switcher_sheet.dart` | `AuthService.switchBusiness()`<br>`POST /api/v1/businesses/switch` | `BusinessController`<br>`routes/owner.php`<br>`POST /businesses/switch` | `resources/views/layouts/partials/topbar.blade.php` | **100% Parity**. Both trigger `Context::setBusiness()`. Mobile resets in-memory `MerchantState` cache synchronously. |
| **Executive Dashboard & Pulse** | `DashboardScreen`<br>`lib/screens/dashboard_screen.dart` | `ReportsService.getPulse()`<br>`GET /api/v1/mobile/dashboard/pulse` | `DashboardController`<br>`routes/owner.php`<br>`GET /dashboard` | `resources/views/app/dashboard.blade.php` | **100% Parity**. Live gross sales, transaction volume, active shift indicators, and hourly revenue curves. |
| **POS Terminal & Checkout** | `PosScreen`<br>`lib/screens/pos_screen.dart` | `PosService.checkout()`<br>`POST /api/v1/pos/terminal/checkout` | `PosTerminalWebController`<br>`routes/owner.php`<br>`POST /pos/terminal/checkout` | `resources/views/app/pos/terminal.blade.php` | **100% Parity**. Real-time SKU search, category browsing, cart management, taxes, multi-tender payment, and automatic stock deduction. |
| **POS Shift Float & Close** | `PosShiftModal`<br>`lib/screens/pos/pos_shift_modal.dart` | `PosService.openShift()`, `closeShift()`<br>`POST /api/v1/pos/shifts/open` | `PosShiftWebController`<br>`routes/owner.php`<br>`POST /pos/shifts/open` | `resources/views/app/pos/shifts.blade.php` | **100% Parity**. Opening cash drawer float, petty cash in/out, closing physical cash denomination counting, and discrepancy audit. |
| **Held Transactions (Park Cart)** | `PosScreen` (Held Drawer)<br>`lib/screens/pos_screen.dart` | `PosService.holdOrder()`, `resumeOrder()`<br>`POST /api/v1/pos/terminal/hold` | `PosTerminalWebController`<br>`routes/owner.php`<br>`POST /pos/terminal/hold` | `resources/views/app/pos/terminal.blade.php` | **100% Parity**. Suspends uncommitted cart sessions into temporary storage, allowing cashiers to interleave client checkouts. |
| **Order History & Reprint** | `PosReceiptScreen`<br>`lib/screens/pos/pos_receipt_screen.dart` | `PosService.getReceipt()`<br>`GET /api/v1/pos/orders/{id}/receipt` | `PosOrderWebController`<br>`routes/owner.php`<br>`GET /pos/orders/{id}` | `resources/views/app/pos/orders.blade.php` | **100% Parity**. Renders line items, discounts, taxes, and customer details with duplicate copy reprint tracking. |
| **Supervisor Void & Refund** | `PosHistoryModal`<br>`lib/screens/pos/pos_history_modal.dart` | `PosService.voidOrder()`, `refundOrder()`<br>`POST /api/v1/pos/orders/{id}/void` | `PosOrderWebController`<br>`routes/owner.php`<br>`POST /pos/orders/{id}/void` | `resources/views/app/pos/orders.blade.php` | **100% Parity**. Mandatory supervisor PIN authentication before voiding orders or reversing cash registers. |
| **Operational Kitchen / SPK Queue (KDS)** | `KdsScreen`<br>`lib/screens/kds_screen.dart` | `KdsService.getKitchenOrders()`<br>`GET /api/v1/pos/kitchen/orders` | `PosKitchenWebController`<br>`routes/owner.php`<br>`GET /pos/kitchen` | `resources/views/app/pos/kitchen.blade.php` | **100% Parity**. Auto-adapts by industry: 'Dapur' (F&B), 'Servis SPK' (Workshop), 'Cucian' (Laundry), 'Resep' (Pharmacy), 'Pabrik' (Mfg). |
| **Dine-In Table Management** | `PosTableModal`<br>`lib/screens/pos/pos_table_modal.dart` | `PosService.getTables()`<br>`GET /api/v1/pos/tables` | `PosTableWebController`<br>`routes/owner.php`<br>`GET /pos/tables` | `resources/views/app/pos/tables.blade.php` | **100% Parity**. Floor map table grid, occupancy status, customer billing linking, and QR self-order token generation. |
| **Multi-Warehouse Stock Balances** | `InventoryScreen`<br>`lib/screens/inventory_screen.dart` | `InventoryService.getStocks()`<br>`GET /api/v1/inventory/stocks` | `InventoryWebController`<br>`routes/owner.php`<br>`GET /inventory/stocks` | `resources/views/app/inventory/stocks.blade.php` | **100% Parity**. Real-time stock counts across multiple warehouse branches with low-stock warnings and valuation. |
| **Inter-Warehouse Stock Transfer** | `StockTransferScreen`<br>`lib/screens/inventory/stock_transfer_screen.dart` | `InventoryService.dispatchTransfer()`<br>`POST /api/v1/inventory/transfers/dispatch` | `WarehouseWebController`<br>`routes/owner.php`<br>`POST /warehouse/transfers` | `resources/views/app/inventory/transfers.blade.php` | **100% Parity**. Full 2-way workflow: Dispatch from Source -> In-Transit -> Inbound Receiving Confirmation at Destination. |
| **Warehouse Physical Goods Receipt (GRN)** | `GoodsReceiptScreen`<br>`lib/screens/inventory/goods_receipt_screen.dart` | `InventoryService.createGoodsReceipt()`<br>`POST /api/v1/inventory/goods-receipts` | `GoodsReceiptWebController`<br>`routes/owner.php`<br>`POST /purchasing/receipts` | `resources/views/app/purchasing/receipts/create.blade.php` | **100% Parity**. Inspects incoming shipments against approved POs, recording accepted vs damaged quantities. |
| **Master Finished Goods Catalog** | `InventoryScreen` (Produk Tab)<br>`lib/screens/inventory_screen.dart` | `InventoryService.getProducts()`<br>`GET /api/v1/products` | `ProductWebController`<br>`routes/owner.php`<br>`GET /products` | `resources/views/app/products/index.blade.php` | **100% Parity**. Product name, SKU, barcode, retail price, category, and active inventory tracking flags. |
| **Master Raw Materials Catalog** | `InventoryScreen` (Bahan Baku Tab)<br>`lib/screens/inventory_screen.dart` | `InventoryService.getMaterials()`<br>`GET /api/v1/materials` | `MaterialWebController`<br>`routes/owner.php`<br>`GET /materials` | `resources/views/app/materials/index.blade.php` | **100% Parity**. Raw materials catalog with purchase costs, units of measure, and minimum stock alerts. |
| **Customer Master Directory & Debt** | `PosCustomerDebtSheet`<br>`lib/screens/pos/pos_customer_debt_sheet.dart`| `PosService.getCustomers()`<br>`GET /api/v1/customers` | `CustomerWebController`<br>`routes/owner.php`<br>`GET /customers` | `resources/views/app/customers/index.blade.php` | **100% Parity**. Contact info, vehicle license plates (automotive), loyalty tier, and outstanding receivables balance. |
| **HR Employee Master Directory** | `HrProfileScreen` (Karyawan Tab)<br>`lib/screens/hr_profile_screen.dart` | `HrmService.getEmployees()`<br>`GET /api/v1/hrm/employees` | `HrmWebController`<br>`routes/owner.php`<br>`GET /hrm/employees` | `resources/views/app/hrm/index.blade.php` | **100% Parity**. Staff profiles, job designations, salary structures, assigned shift schedules, and branch assignments. |
| **Monthly Payroll Disbursement** | `HrProfileScreen` (Gaji Tab)<br>`lib/screens/hr_profile_screen.dart` | `HrmService.getPayroll()`<br>`GET /api/v1/hrm/payroll` | `HrmWebController`<br>`routes/owner.php`<br>`GET /hrm/payroll` | `resources/views/app/hrm/partials/tab-payrolls.blade.php` | **100% Parity**. Base salary calculation, attendance deductions, overtime pay, kasbon repayments, and net pay. |
| **Employee Kasbon Cash Advances** | `HrProfileScreen` (Kasbon Tab)<br>`lib/screens/hr_profile_screen.dart` | `HrmService.getLoans()`<br>`GET /api/v1/hrm/loans` | `HrmWebController`<br>`routes/owner.php`<br>`GET /hrm/loans` | `resources/views/app/hrm/partials/tab-loans.blade.php` | **100% Parity**. Loan applications, supervisor approvals, balance tracking, and automated payroll installments. |
| **Omnichannel Marketplace Hub** | `MarketplaceHubScreen`<br>`lib/screens/marketplace/marketplace_hub_screen.dart`| `MarketplaceHubService.getOverview()`<br>`GET /api/v1/marketplace-hub/overview` | `MarketplaceWebController`<br>`routes/owner.php`<br>`GET /marketplace` | `resources/views/app/marketplace/index.blade.php` | **100% Parity**. Multi-store metrics (Shopee, Tokopedia, TikTok Shop), order batch dispatching, and buyer chat. |
| **Social Media Hub & Scheduler** | `SocialMediaHubScreen`<br>`lib/screens/social_media/social_media_hub_screen.dart`| `SocialMediaService.getPosts()`<br>`GET /api/v1/social-media/posts` | `SocialMediaWebController`<br>`routes/owner.php`<br>`GET /social_media` | `resources/views/app/social_media/calendar.blade.php` | **100% Parity**. Editorial calendar, scheduled posts, AI copywriting assistant, and unified comment inbox. |
| **UMKM Community & Events** | `CommunityHubScreen`<br>`lib/screens/community/community_hub_screen.dart` | `CommunityService.getFeed()`<br>`GET /api/v1/community/feed` | `CommunityWebController`<br>`routes/owner.php`<br>`GET /community` | `resources/views/app/community/index.blade.php` | **100% Parity**. Peer discussion forum, webinar registrations, ticket booking, and barcode check-in. |

---

## 3. Mobile-Only Features (Edge Capabilities)

The following capabilities are implemented exclusively within the Flutter Mobile Client (`cooca_my_own`), capitalizing on mobile hardware peripherals:

```
+------------------------------------------------------------------------------------------+
|                                MOBILE-ONLY EDGE CAPABILITIES                             |
+------------------------------------+-----------------------------------------------------+
| Feature & Implementation           | Technical & Architectural Rationale                 |
+------------------------------------+-----------------------------------------------------+
| **1. Direct ESC/POS Bluetooth      | Browser security sandboxes strictly forbid raw      |
|    Thermal Printing & Cash Drawer**| Bluetooth socket communication (SPP/RFCOMM). The    |
|    `ThermalPrinterService.dart`    | Flutter app talks directly to 58mm/80mm thermal     |
|    `PosTerminalsScreen.dart`       | hardware, issuing ESC/POS binary commands and RJ11  |
|                                    | kick pulses with zero external print spoolers.      |
+------------------------------------+-----------------------------------------------------+
| **2. Native Camera Viewfinder      | Desktop web cameras cannot function as ergonomic,   |
|    Barcode & QR Code Scanner**     | handheld retail barcode scanners. The mobile app    |
|    `mobile_scanner ^7.4.2`         | integrates `mobile_scanner` with continuous         |
|    `PosBarcodeScannerSheet.dart`   | autofocus and LED torch toggles for rapid shelf     |
|                                    | inventory counting and checkout scanning.           |
+------------------------------------+-----------------------------------------------------+
| **3. Autonomous Offline SQLite POS | Web browsers rely on fragile IndexedDB or CacheAPI  |
|    Transaction Queue Engine**      | which can be evicted by the OS. Mobile leverages    |
|    `PosOfflineSyncManager.dart`    | a persistent SQLite database with UUID v4           |
|    `offline_pos_queue` table       | idempotency keys, enabling cashiers to ring sales   |
|                                    | during complete power/internet blackouts.           |
+------------------------------------+-----------------------------------------------------+
| **4. High-Accuracy GPS Geofencing  | Desktop browser geolocation is derived from IP      |
|    Clock-In Radar (50m Radius)**   | lookups, which are inaccurate by tens of kilometers.|
|    `geolocator ^14.1.1`            | The mobile app queries native GNSS GPS hardware     |
|    `AttendanceScreen.dart`         | to enforce a strict 50-meter store outlet radius.   |
+------------------------------------+-----------------------------------------------------+
| **5. Native Android Anti-Spoofing  | Eliminates fraudulent employee clock-ins ("titip    |
|    Security Telemetry**            | absen"). Uses native Kotlin `MainActivity.kt` to    |
|    `LocationBiometricService.dart` | inspect `Location.isFromMockProvider()`, Developer  |
|    `MethodChannel: com.cooca/sec`  | Options, Root binaries, and ADB debugging.          |
+------------------------------------+-----------------------------------------------------+
```

---

## 4. Web-Only Features (Backoffice Capabilities)

The following capabilities are implemented exclusively within the Laravel Web Backoffice (`cooca_core`), where deep screen real estate, intense processing, and system governance are required:

```
+------------------------------------------------------------------------------------------+
|                                 WEB-ONLY BACKOFFICE CAPABILITIES                         |
+------------------------------------+-----------------------------------------------------+
| Feature & Implementation           | Technical & Architectural Rationale                 |
+------------------------------------+-----------------------------------------------------+
| **1. Complete SAK EMKM Standard    | Designing hierarchical accounts (Assets, Liabilities|
|    Chart of Accounts (COA)**       | Equity, Revenue, COGS, Expense) with parent-child   |
|    `AccountingWebController.php`   | codes requires wide desktop table layouts and       |
|    `resources/views/app/finance/`  | deep validation unsuitable for smartphone screens.  |
+------------------------------------+-----------------------------------------------------+
| **2. Manual General Journal        | Manual accounting adjustments (depreciation, accrued|
|    Vouchers & Closing Entries**    | expenses, prepayments) require multi-row debit and  |
|    `AccountingWebController.php`   | credit balancing grids with instantaneous equality  |
|    `app/Domain/Accounting/`        | checks (`SUM(debit) == SUM(credit)`).               |
+------------------------------------+-----------------------------------------------------+
| **3. Financial Statement Reports   | Full-page audit sheets requiring multi-column       |
|    (Neraca, Buku Besar, Trial Bal)| comparative financial reporting, drill-down ledger  |
|    `AccountingWebController.php`   | cards, and certified A4 PDF generation.             |
|    `resources/views/app/finance/`  |                                                     |
+------------------------------------+-----------------------------------------------------+
| **4. Multi-Level Recipe & Industrial| Formulating complex bills of materials with         |
|    BOM Formula Explosion**         | sub-assemblies, unit scrap percentages, and         |
|    `ProductWebController.php`      | cascading ingredient costs requires extensive       |
|    `resources/views/app/products/` | modal builders and multi-tier calculation trees.    |
+------------------------------------+-----------------------------------------------------+
| **5. Machine & Labor Costing       | Setting up machine depreciation rates per hour,     |
|    Center Allocations**            | kilowatt consumption overheads, and worker grade    |
|    `LaborMachineWebController.php` | labor rates for industrial HPP absorption costing.  |
|    `resources/views/app/production`|                                                     |
+------------------------------------+-----------------------------------------------------+
| **6. Bulk Two-Way Excel Ingestion  | High-throughput file uploading, background parsing  |
|    & Data Cleaning Wizards**       | with PhpSpreadsheet, schema validation tables, and  |
|    `ImportWebController.php`       | reconciliation diff previews for thousands of SKUs. |
|    `resources/views/app/import/`   |                                                     |
+------------------------------------+-----------------------------------------------------+
| **7. Drag-and-Drop Storefront      | Interactive WYSIWYG website builder customizing     |
|    Theme Builder (42 Themes)**     | colors, typography, section visibility, and hero    |
|    `StorefrontThemeEditorController| banners across 42 responsive templates.              |
|    `resources/views/app/storefront`|                                                     |
+------------------------------------+-----------------------------------------------------+
| **8. Platform Superadmin Control   | Sensitive multi-tenant administrative plane:        |
|    Center (Admin Guard)**          | workspace suspensions, quota overrides, owner       |
|    `app/Http/Controllers/Admin/`   | impersonation, and TriPay billing audit trail.      |
|    `resources/views/admin/`        | Protected by dedicated `auth:admin` session guard.  |
+------------------------------------+-----------------------------------------------------+
```

---

## 5. Parity Audit Summary

The COOCA platform achieves an optimal balance between unified business logic and client-specific specialization:
1. **Zero Core Logic Fragmentation**: Core operational flows (POS sales, stock valuation, attendance records, KDS queues, customer loyalty) share 100% parity across Web and Mobile, powered by the exact same backend services and database constraints.
2. **Edge Hardware Supremacy**: The Mobile app delivers indispensable operational advantages (thermal Bluetooth printing, camera barcode scanning, offline SQLite resilience, and GPS biometric verification) that web browsers are technically incapable of matching.
3. **Deep Financial & Administrative Mastery**: The Web Backoffice maintains absolute control over financial compliance, SAK EMKM general ledger balancing, multi-level BOM explosion, bulk data ingestion, and platform-wide superadmin governance.
