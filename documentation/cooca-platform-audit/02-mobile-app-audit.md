# 02. Mobile Application Technical Audit & Architecture Report

**Platform**: COOCA My Own Mobile Client (`cooca_my_own`)  
**Target Environment**: `c:\laragon\www\cooca_my_own`  
**Backend API Reference**: `c:\laragon\www\cooca_core`  
**Document Classification**: Authoritative Engineering Audit  
**Version**: 1.0.0 (Production Verified)  

---

## 1. System Overview & Flutter SDK Environment

The COOCA Mobile Application (`cooca_my_own`) serves as a high-performance, cross-platform edge client engineered specifically for on-the-ground operational execution (Point of Sale, Kitchen Display, Inventory Barcode Counting, and Smart Biometric Attendance). Built with Google Flutter, it interfaces seamlessly with the Laravel backend via standard JSON REST endpoints over HTTPS and WebSockets.

```
+-----------------------------------------------------------------------------------+
|                        COOCA MY OWN MOBILE CLIENT TOPOLOGY                        |
+-----------------------------------------------------------------------------------+
|  OPERATIONAL RUNTIME (Android 14 API 34 Pixel 6 / iOS 17 / macOS / Windows / Web) |
+-----------------------------------------+-----------------------------------------+
                                          | Reactive ListenableBuilder Binding
                                          v
+-----------------------------------------------------------------------------------+
|  CENTRAL STATE ENGINE: MerchantState (ChangeNotifier Singleton, 2,850 Lines)      |
|  - In-Memory Cart & Held Transactions      - Active Cashier Shift & Float State   |
|  - Offline Queue Buffer (Idempotent UUID)  - Real-Time Pulse & Notifications      |
|  - Active Tenant Isolation (resetState())  - User Profile & Dynamic Permissions   |
+-------------------+---------------------+--------------------+--------------------+
                    |                     |                    |
                    v                     v                    v
+-------------------------+ +---------------------+ +------------------------------+
| HARDWARE BRIDGES        | | OFFLINE SYNC ENGINE | | REST API INTEGRATION LAYER   |
| - Bluetooth Thermal SPP | | - PosOfflineSyncMgr | | - ApiClient (http ^1.2.2)    |
| - Mobile Camera Scanner | | - SQLite Queue DB   | | - Bearer Token Authentication|
| - GPS Radar (Geolocator)| | - UUID v4 Idempotent| | - X-Business-Id Multi-Tenant |
| - Native MethodChannel  | | - Exp. Backoff Retry| | - Dynamic Server URL Switch  |
+-------------------------+ +---------------------+ +------------------------------+
```

### Detailed Mobile Build & Tooling Specifications

| Platform Attribute | Technical Value | Specification & Engineering Details |
|---|---|---|
| **Framework** | Flutter | `3.47.2` (Channel: stable, Revision: `d3b14c8769`, 2026-08-26). |
| **Dart SDK** | Dart | `3.13.2` (DevTools: `2.60.0`). Strict typing with `flutter_lints`. |
| **Application ID** | `com.cooca.myown` | Configured in `android/app/build.gradle.kts` (Namespace & applicationId). |
| **iOS Bundle Identifier** | `com.cooca.myown` | Display Name: `COOCA` in `ios/Runner/Info.plist`. |
| **Release Version** | `1.0.10+11` | Semantic versioning: Major 1, Minor 0, Patch 10, Build Number 11. |
| **JVM Target** | JVM 17 | `compileOptions { sourceCompatibility = JavaVersion.VERSION_17 }`. |
| **Android Desugaring** | Java 8+ Desugaring | Enabled via `com.android.tools:desugar_jdk_libs:2.1.4`. |
| **Code Shrinking & Proguard**| Enabled | `isMinifyEnabled = true`, `isShrinkResources = true`, `proguard-rules.pro`. |
| **Codebase Size** | 125 Dart files in lib/ (147 Dart files repository-wide) | Modularly organized into `core/`, `screens/`, `state/`, and `widgets/`. |
| **Primary State Holder** | `MerchantState.dart`| 2,850 lines of robust, singleton-based business logic without 3rd-party bloat. |

---

## 2. Complete Screen & Navigation Audit

The mobile client contains **23 dedicated operational screen widgets** located in `lib/screens/`, augmented by modal bottom sheets for micro-interactions (e.g. modifier selections, quick-add, drawer cash-in/out).

```
+------------------------------------------------------------------------------------------+
|                       COOCA MOBILE SCREEN & NAVIGATION TOPOLOGY                          |
+------------------------------------------------------------------------------------------+
|  ENTRY POINT: SplashScreen (lib/screens/splash_screen.dart)                              |
|  - Token validation via LocalStorageService                                              |
|  - Routes to LoginScreen (if unauthenticated) or MainNav (if valid session exists)       |
+--------------------------------------------+---------------------------------------------+
                                             | Authenticated Session
                                             v
+------------------------------------------------------------------------------------------+
|  MAIN SHELL: MainNav (lib/screens/main_nav.dart) - Dynamic Apple HIG Cupertino Tab Bar   |
+-------------------+--------------------+--------------------+--------------------+-------+
| Tab 0: Dashboard  | Tab 1: KDS Queue   | Tab 2: Attendance  | Tab 3: Alerts      | Tab 4:|
| DashboardScreen   | KdsScreen (Dynamic)| AttendanceScreen   | NotificationScreen |Profile|
+-------------------+--------------------+--------------------+--------------------+-------+
```

### 2.1 Complete 23 Operational Screens Inventory

| # | Screen Name | File Path | Functional Purpose & Capabilities | Navigation Trigger & Guard |
|---|---|---|---|---|
| 1 | **SplashScreen** | `lib/screens/splash_screen.dart` | App initialization, logo reveal animation, local storage credentials validation, and automatic session restoration. | Application entry point (`home: SplashScreen()`). |
| 2 | **LoginScreen** | `lib/screens/login_screen.dart` | Staff authentication via email/password credentials or Google SSO (`google_sign_in: ^7.2.0`), with tenant workspace bootstrap. | `Navigator.pushReplacement` upon session expiry or user logout. |
| 3 | **MainNav** | `lib/screens/main_nav.dart` | Root operational scaffold rendering an adaptive Cupertino 5-tab bar filtered by active user permissions and tenant business model. | Root container replacing LoginScreen upon successful authentication. |
| 4 | **DashboardScreen** | `lib/screens/dashboard_screen.dart` | Executive business cockpit featuring live omzet pulse, gross profit metrics, hourly sales line charts (`fl_chart`), low-stock badges, and quick-action launcher. | Primary Tab (Index 0) of `MainNav`. |
| 5 | **PosScreen** | `lib/screens/pos_screen.dart` | High-speed POS terminal with real-time catalog search, category filters, camera barcode scanner sheet, slide-over cart, multi-payment tender (Cash, QRIS, Transfer, Kasbon), and change calculator. | Dedicated Cashier primary tab or launched via Dashboard quick action. |
| 6 | **KdsScreen** | `lib/screens/kds_screen.dart` | Dynamic operational queue adapting automatically to tenant industry: 'Dapur KDS' (F&B), 'Servis SPK' (Workshop), 'Cucian Rak' (Laundry), 'Resep Obat' (Pharmacy), 'Papan SPK' (Manufacturing). Zero feature leakage on pure retail. | Operational Tab (Index 1) of `MainNav`. |
| 7 | **AttendanceScreen** | `lib/screens/attendance_screen.dart` | Smart Attendance clock-in terminal: GPS geofencing radar, front-facing selfie camera capture, monthly attendance calendar, leave balance, and digital payslips. | Tab 2 of `MainNav` or Dashboard quick action. Protected by `AccessGuard`. |
| 8 | **InventoryScreen** | `lib/screens/inventory_screen.dart` | Multi-warehouse stock management across 4 unified tabs: Finished Goods (`Produk`), Raw Materials (`Bahan Baku`), Labor Services (`Layanan Jasa`), and Physical Opname (`Stok Opname`). | Dashboard quick action. Protected by `AccessGuard.canAccessInventory`. |
| 9 | **StockTransferScreen** | `lib/screens/inventory/stock_transfer_screen.dart` | Inter-branch and inter-warehouse stock transfer dispatching, transit tracking, and inbound receiving verification. | Action button within `InventoryScreen`. |
| 10 | **GoodsReceiptScreen** | `lib/screens/inventory/goods_receipt_screen.dart` | Warehouse physical receiving against approved Purchase Orders (PO), generating Goods Receipt Notes (GRN) with discrepancy notes. | Action button within `InventoryScreen`. |
| 11 | **ReportsScreen** | `lib/screens/reports_screen.dart` | Executive financial and operational reports: Sales summary, payment channel breakdown, top-selling items, hourly heatmaps, and interactive what-if HPP margin simulator. | Dashboard quick action. Protected by `AccessGuard.canAccessReports`. |
| 12 | **HrProfileScreen** | `lib/screens/hr_profile_screen.dart` | Complete HRM management cockpit across 6 sub-tabs: Employee Directory (`Karyawan`), Attendance (`Presensi`), Exceptions (`Koreksi`), Work Shifts (`Shift`), Payroll (`Gaji`), and Cash Advances (`Kasbon`). | Profile screen navigation or Dashboard quick action. |
| 13 | **MarketplaceHubScreen**| `lib/screens/marketplace/marketplace_hub_screen.dart` | Omnichannel e-commerce cockpit: Connected store overview (Shopee, Tokopedia, TikTok Shop), multi-channel product catalog mapping, order batch fulfillment, and unified customer chat. | Dashboard quick action. |
| 14 | **SocialMediaHubScreen**| `lib/screens/social_media/social_media_hub_screen.dart` | Omnichannel social media manager: Connected channels (Instagram, TikTok, Facebook), scheduled content calendar, post publisher with AI copywriting, and unified direct message inbox. | Dashboard quick action. |
| 15 | **CommunityHubScreen** | `lib/screens/community/community_hub_screen.dart` | Indonesian UMKM business community: Discussion forum feed, peer posting, webinar directory, ticket booking, and barcode check-in. | Profile screen navigation or Dashboard quick action. |
| 16 | **OwnerApprovalScreen**| `lib/screens/approvals/owner_approval_screen.dart` | Supervisor override approval inbox: Real-time authorization for cashier transaction voids, refunds, manual discounts, and employee attendance corrections. | Triggered by notification tap or Profile menu. |
| 17 | **NotificationScreen** | `lib/screens/notification_screen.dart` | Real-time system alert feed: Out-of-stock warnings, incoming online orders, cashier shift opening notices, and supervisor override approvals. | Tab 3 of `MainNav` or Dashboard bell icon. |
| 18 | **ProfileScreen** | `lib/screens/profile_screen.dart` | User credentials, branch workspace switcher, hardware thermal printer settings, system preferences, and secure logout. | Tab 4 of `MainNav`. |
| 19 | **SystemSettingsScreen**| `lib/screens/settings/system_settings_screen.dart` | App settings: Haptic feedback toggles, sound effects, dark/light theme switcher, language preference (ID/EN), device permissions inspector, and OTA version updates. | Triggered from Profile menu. |
| 20 | **PosTerminalsScreen** | `lib/screens/settings/pos_terminals_screen.dart` | Bluetooth ESC/POS thermal printer discovery, pairing manager, 58mm/80mm paper width selector, test print invoice generator, and RJ11 cash drawer kick test. | Triggered from Profile settings. |
| 21 | **HelpCenterScreen** | `lib/screens/settings/help_center_screen.dart` | Comprehensive in-app knowledge base: Illustrated cashier onboarding guides, video tutorial links, frequently asked questions, and direct WhatsApp live support bridge. | Triggered from Profile settings. |
| 22 | **PosReceiptScreen** | `lib/screens/pos/pos_receipt_screen.dart` | Thermal receipt visual preview, Bluetooth printing engine, system PDF sharing, and direct WhatsApp receipt dispatch with customer phone prefill. | Triggered automatically after POS checkout or via order history reprint. |
| 23 | **NoAccessScreen** | `lib/screens/no_access_screen.dart` | Defensive authorization fallback screen displayed whenever a user attempts to access an unauthorized route or screen without proper RBAC permissions. | Rendered by `AccessGuard` upon permission failure. |

---

## 3. State Management Architecture: Centralized `MerchantState`

The application enforces a high-performance, single-source-of-truth architecture centered on `MerchantState` (`lib/state/merchant_state.dart`). Rather than introducing heavy external dependencies like Bloc or Riverpod, `MerchantState` extends Flutter's native `ChangeNotifier`, providing maximum execution speed and zero unnecessary rebuild overhead.

```
+------------------------------------------------------------------------------------------+
|                       MERCHANTSTATE REACTIVE ARCHITECTURE                                |
+------------------------------------------------------------------------------------------+
|                                    MerchantState                                         |
|                               (ChangeNotifier Singleton)                                 |
|                                                                                          |
|  +------------------------+  +------------------------+  +----------------------------+  |
|  |     POS CART STATE     |  |   CASHIER SHIFT STATE  |  |    ACTIVE BUSINESS STATE   |  |
|  | - List<CartItem> items |  | - PosShift activeShift |  | - Business currentBusiness |  |
|  | - Customer activeCust  |  | - double startingCash  |  | - String activeBusinessId  |  |
|  | - Table activeTable    |  | - double expectedCash  |  | - List<Business> workspaces|  |
|  | - HeldOrders queue     |  | - List<ShiftMovement>  |  | - UserProfile currentUser  |  |
|  +-----------+------------+  +-----------+------------+  +-------------+--------------+  |
|              |                           |                             |                 |
|              +---------------------------+-----------------------------+                 |
|                                          v                                               |
|                                  notifyListeners()                                       |
+------------------------------------------+-----------------------------------------------+
                                           | Reactive UI Re-render
                                           v
+------------------------------------------------------------------------------------------+
|  ListenableBuilder(listenable: MerchantState(), builder: (context, _) { ... })           |
|  - MainNav: Updates badge counters and visible tabs dynamically                          |
|  - PosScreen: Re-renders cart drawer, subtotal, discount, and tender calculator          |
|  - InventoryScreen: Reflects real-time stock deductions immediately                      |
+------------------------------------------------------------------------------------------+
```

### 3.1 Tenant Context Isolation & Zero Cross-Tenant Leakage

To prevent catastrophic cross-tenant data contamination when a user manages multiple businesses:
1. **The `resetState()` Protocol**: When a user logs out or switches businesses via `BusinessSwitcherSheet`, `MerchantState().resetState()` is invoked synchronously (`merchant_state.dart`, lines 141-210).
2. **Purge Scope**:
   - In-memory cart items and active held transactions are wiped.
   - Cached product catalogs, modifiers, and raw material inventories are evicted.
   - Active cashier shift references are cleared.
   - Notifications and real-time pulse metrics are zeroed.
3. **Local Storage Synchronization**: `LocalStorageService` synchronously updates `cooca_active_business_id`, ensuring all subsequent API calls immediately transmit the new tenant ID in the `X-Business-Id` header.

---

## 4. Offline POS Synchronization Engine

The mobile client contains an autonomous offline transaction engine managed by `PosOfflineSyncManager` (`lib/core/services/pos_offline_sync_manager.dart`). This enables cashiers to continue ringing up sales during internet outages without data loss.

```
+------------------------------------------------------------------------------------------+
|                             OFFLINE POS TRANSACTION ENGINE                               |
+------------------------------------------------------------------------------------------+
| POS Terminal Checkout (Network Offline)                                                  |
| 1. Generate UUID v4 Idempotency Key (e.g. 9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d)          |
| 2. Serialize full transaction payload (items, prices, tax, discounts, tender)            |
| 3. Enqueue payload into Local SQLite Database (`offline_pos_queue` table)                |
| 4. Update local inventory balance immediately in MerchantState                          |
| 5. Print ESC/POS thermal receipt locally with OFFLINE watermark                          |
+--------------------------------------------+---------------------------------------------+
                                             | Connectivity Restored
                                             v
+------------------------------------------------------------------------------------------+
| PosOfflineSyncManager Background Sync Loop                                               |
| 1. Query pending offline records (status = 'PENDING') ordered by created_at ASC          |
| 2. Transmit batch payload to POST /api/v1/pos/sync/batch with X-Business-Id              |
| 3. Server processes orders idempotently via DB::transaction with UUID conflict checks    |
| 4. Server returns successful transaction IDs -> Local records marked 'SYNCED'            |
| 5. On server 5xx or network dropout: Exponential backoff retry (2s, 4s, 8s, max 60s)    |
+------------------------------------------------------------------------------------------+
```

### Offline Queue Schema Specifications

| Column Name | SQLite Data Type | Constraints & Purpose |
|---|---|---|
| `id` | `INTEGER` | Primary Key, Auto-increment. |
| `idempotency_key` | `TEXT` | `UNIQUE NOT NULL`. UUID v4 generated on client device to prevent duplicate orders. |
| `business_id` | `TEXT` | `NOT NULL`. Tenant identifier ensuring orders sync to the correct workspace. |
| `payload` | `TEXT` | `NOT NULL`. Complete JSON representation of cart items, customer ID, payment method. |
| `sync_status` | `TEXT` | `DEFAULT 'PENDING'`. State machine: `PENDING` -> `SYNCING` -> `SYNCED` / `FAILED`. |
| `retry_count` | `INTEGER` | `DEFAULT 0`. Incremented on each failed sync attempt. |
| `created_at` | `TEXT` | ISO-8601 creation timestamp for FIFO queue processing. |
| `last_error` | `TEXT` | `NULLABLE`. Error message returned by server if validation failed. |

---

## 5. Hardware Integrations (Mobile Edge Capabilities)

The Flutter mobile application leverages native device hardware capabilities to deliver an optimal retail and workshop edge experience:

```
+------------------------------------------------------------------------------------------+
|                                HARDWARE INTEGRATION MATRIX                               |
+-------------------+------------------------------+---------------------------------------+
| Hardware Domain   | Flutter Plugins Used         | Operational Capability & Protocol     |
+-------------------+------------------------------+---------------------------------------+
| **Thermal POS**   | `print_bluetooth_thermal`    | Bluetooth Classic SPP & BLE 4.0/5.0   |
|                   | `esc_pos_utils_plus ^2.0.4`  | ESC/POS byte generator (58mm / 80mm)  |
|                   | `printing ^5.15.1`           | System print dialog & network TCP:9100|
|                   | `pdf ^3.13.1`                | In-memory PDF rasterizer for receipts |
+-------------------+------------------------------+---------------------------------------+
| **Barcode Scanner** `mobile_scanner ^7.4.2`      | Real-time camera viewfinder scanner   |
|                   | Camera autofocus & torch     | EAN-13, Code 128, QR Code parsing     |
+-------------------+------------------------------+---------------------------------------+
| **Biometric Face**| `camera ^0.12.1`             | Front-facing camera frame streaming   |
|                   | `image_picker ^1.2.4`        | High-res selfie capture for clock-in  |
+-------------------+------------------------------+---------------------------------------+
| **GPS Geofencing**| `geolocator ^14.1.1`         | High-accuracy GPS location telemetry  |
|                   | DistanceTo() calculation     | 50m outlet radius geofence gate       |
+-------------------+------------------------------+---------------------------------------+
| **Native Security** `MainActivity.kt`            | MethodChannel: `com.cooca.myown/sec`  |
|                   | Kotlin Native Bridge         | Root, Mock GPS, ADB debugging checks  |
+-------------------+------------------------------+---------------------------------------+
```

### 5.1 Bluetooth Thermal Printing Architecture

Managed via `ThermalPrinterService` (`lib/core/services/thermal_printer_service.dart`):
- **Printer Pairing**: Automatically scans Bluetooth devices filtering for SPP serial profiles.
- **ESC/POS Formatting**:
  - Center-aligned store branding and header text.
  - Multi-column receipt layout: Item name, quantity, unit price, and line subtotal.
  - Surcharge, service charge, PPN 11% tax, and rounding rows.
  - Footer receipt message and dynamic QRIS payload bitmap rendering.
  - ESC/POS RJ11 kick pulse command (`[0x1B, 0x70, 0x00, 0x19, 0xFA]`) to pop the physical cash drawer.
  - Cut paper feed commands (`[0x1D, 0x56, 0x41, 0x10]`).

### 5.2 Anti-Spoofing & Biometric Attendance Telemetry

To eliminate fraudulent employee clock-ins ("titip absen" or fake GPS apps):
- **Android Native Security MethodChannel**: Before submitting a clock-in payload, `LocationBiometricService` invokes native Kotlin methods in `MainActivity.kt`:
  - `isMockLocationEnabled`: Inspects `Location.isFromMockProvider()` on Android API 31+.
  - `isDeviceRooted`: Checks for known root binaries (`/system/app/Superuser.apk`, `/system/xbin/su`).
  - `isDevOptionsEnabled`: Queries `Settings.Global.DEVELOPMENT_SETTINGS_ENABLED`.
  - `isAdbEnabled`: Queries `Settings.Global.ADB_ENABLED`.
- **Enforcement Policy**: If mock location or root tampering is detected, the clock-in request is rejected on the client side immediately with a high-security alert dialog.

---

## 6. Native Platform Configurations & Hardware Permissions

### 6.1 Android Manifest Permissions (`android/app/src/main/AndroidManifest.xml`)

```xml
<manifest xmlns:android="http://schemas.android.com/apk/res/android"
    package="com.cooca.myown">

    <!-- Camera Access for Barcode Scanner and Attendance Selfie -->
    <uses-permission android:name="android.permission.CAMERA" />
    <uses-feature android:name="android.hardware.camera" android:required="false" />
    <uses-feature android:name="android.hardware.camera.autofocus" android:required="false" />

    <!-- Bluetooth Classic & Low Energy for ESC/POS Thermal Printers -->
    <uses-permission android:name="android.permission.BLUETOOTH" android:maxSdkVersion="30" />
    <uses-permission android:name="android.permission.BLUETOOTH_ADMIN" android:maxSdkVersion="30" />
    <uses-permission android:name="android.permission.BLUETOOTH_SCAN" android:usesPermissionFlags="neverForLocation" />
    <uses-permission android:name="android.permission.BLUETOOTH_CONNECT" />

    <!-- High-Accuracy Geolocation for Smart Geofenced Attendance -->
    <uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
    <uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />

    <!-- Network & Internet -->
    <uses-permission android:name="android.permission.INTERNET" />
    <uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />

    <!-- Android 13+ Notification Permission -->
    <uses-permission android:name="android.permission.POST_NOTIFICATIONS" />
</manifest>
```

### 6.2 iOS Info.plist Declarations (`ios/Runner/Info.plist`)

```xml
<dict>
    <key>CFBundleDisplayName</key>
    <string>COOCA</string>
    <key>NSCameraUsageDescription</key>
    <string>COOCA membutuhkan akses kamera untuk memindai barcode produk dan verifikasi foto presensi karyawan.</string>
    <key>NSLocationWhenInUseUsageDescription</key>
    <string>COOCA membutuhkan akses lokasi untuk memastikan Anda berada di dalam radius presensi outlet yang valid.</string>
    <key>NSBluetoothAlwaysUsageDescription</key>
    <string>COOCA membutuhkan koneksi Bluetooth untuk mencetak struk belanja ke printer thermal POS.</string>
    <key>NSBluetoothPeripheralUsageDescription</key>
    <string>COOCA membutuhkan koneksi Bluetooth untuk menghubungkan mesin printer kasir dan laci kasir.</string>
</dict>
```

---

## 7. Audit Conclusion & Cross-Platform Synergy

The COOCA Mobile Application (`cooca_my_own`) represents an exceptional, production-grade Flutter implementation:
1. **Full Architectural Parity**: 23 comprehensive screens deliver complete operational coverage across POS, KDS, Multi-Warehouse Inventory, Smart Attendance, HRM, Marketplace Hub, Social Media, and Executive Reporting.
2. **Specialized Mobile Advantages**: Incorporates high-value edge features that web browsers cannot provide (ESC/POS Bluetooth direct printing, native hardware camera barcode scanning, offline SQLite order queuing, and Android native anti-spoofing security telemetry).
3. **Clean Reactive Design**: Standard `ListenableBuilder` reactive binding driven by the centralized 2,850-line `MerchantState` guarantees rock-solid stability, zero third-party framework churn, and total tenant context isolation.
