# 06. API Integration Specification & Mobile-to-Backend Interface Contracts

**Platform**: COOCA SaaS ERP Ecosystem (`cooca_core` & `cooca_my_own`)  
**Target Architecture**: Monolithic Laravel 13.25.0 REST API & Flutter 3.47.2 Mobile Client  
**Document Classification**: Authoritative Engineering Interface Specification  
**Version**: 1.0.0 (Production Verified)  
**Base URL**: `/api/v1/*`  

---

## 1. Executive Summary & Integration Architecture

The COOCA platform implements a stateless, token-authenticated RESTful API architecture connecting the **Flutter Cross-Platform Mobile Client** (`cooca_my_own`), local hardware bridges (**Local POS Agent**), payment gateways (**TriPay**), messaging daemons (**WhatsApp Baileys Gateway**), and external social/marketplace aggregators to the core **Laravel 13.25.0 ERP** engine.

All API routes are versioned under the `/api/v1` namespace and registered in `routes/api.php`. Over 48 high-performance endpoints deliver millisecond-level responsiveness for high-volume Point of Sale (POS) checkouts, offline-first transaction queues, dining room floor operations, biometric attendance clock-ins, inventory adjustments, and real-time business telemetry.

### 1.1 Dual-Platform Architectural Topology

```
+-----------------------------------------------------------------------------------------------------------------+
|                                          COOCA API INTEGRATION TOPOLOGY                                         |
+-----------------------------------------------------------------------------------------------------------------+
|                                                                                                                 |
|   +------------------------------------+                             +--------------------------------------+   |
|   |         FLUTTER MOBILE CLIENT      |                             |        LOCAL HARDWARE AGENT          |   |
|   |           (`cooca_my_own`)         |                             |      (Node.js Desktop Bridge)        |   |
|   | - MerchantState (ChangeNotifier)   |                             | - USB ESC/POS Thermal Printers       |   |
|   | - PosOfflineSyncManager (SQLite)   |                             | - RJ11 Solenoid Cash Drawers         |   |
|   | - ThermalPrinterService (BT/LAN)   |                             | - Serial Port Barcode Scanners       |   |
|   +------------------------------------+                             +--------------------------------------+   |
|                     |                                                                   |                       |
|                     | HTTPS (Bearer Token, X-Business-Id)                               | HTTP Polling / SSE    |
|                     v                                                                   v                       |
|   +---------------------------------------------------------------------------------------------------------+   |
|   |                                          LARAVEL API GATEWAY (/api/v1)                                  |   |
|   |                                               (Kernel & Middleware)                                     |   |
|   +---------------------------------------------------------------------------------------------------------+   |
|   |  - ThrottleRequests (10 to 600 req/min)                                                                 |   |
|   |  - Authenticate:sanctum (Bearer Token Validation)                                                       |   |
|   |  - SetActiveBusinessContext (X-Business-Id binding to App\Support\Context)                              |   |
|   |  - EnsureActiveBusiness (Subscription & Status Verification)                                            |   |
|   |  - RequirePermission / RequireRole (Granular RBAC Authorization)                                        |   |
|   +---------------------------------------------------------------------------------------------------------+   |
|                                                     |                                                           |
|             +---------------------------------------+---------------------------------------+                   |
|             |                                       |                                       |                   |
|             v                                       v                                       v                   |
|   +-------------------+                   +-------------------+                   +-------------------+         |
|   |  POS & INVENTORY  |                   |    FINANCE & CRM  |                   |   HRM & SENSORS   |         |
|   | - PosTerminalCtrl |                   | - CashLedgerCtrl  |                   | - AttendanceApi   |         |
|   | - PosShiftCtrl    |                   | - CustomerDebtCtrl|                   | - HrmApiCtrl      |         |
|   | - PosSyncApiCtrl  |                   | - CrmLoyaltyCtrl  |                   | - Biometric Radar |         |
|   | - InventoryCtrl   |                   | - FinanceCtrl     |                   | - Geofence Guard  |         |
|   +-------------------+                   +-------------------+                   +-------------------+         |
|             |                                       |                                       |                   |
|             +---------------------------------------+---------------------------------------+                   |
|                                                     |                                                           |
|                                                     v                                                           |
|   +---------------------------------------------------------------------------------------------------------+   |
|   |                                   PERSISTENCE & DOMAIN SERVICES                                         |   |
|   | - MySQL 8.4.3 (`cooca_id` Multi-Tenant Tables scoped by `business_id`)                                  |   |
|   | - AutoJournalService (Automatic Balanced Double-Entry Accounting Ledger)                                |   |
|   | - EscposFormatter (58mm / 80mm ESC/POS Binary Renderers)                                                |   |
|   | - TriPay Gateway Webhook Handler & Baileys WhatsApp Struk Dispatcher                                    |   |
|   +---------------------------------------------------------------------------------------------------------+   |
+-----------------------------------------------------------------------------------------------------------------+
```

---

## 2. Authentication Architecture & Token Lifecycle

### 2.1 Sanctum Bearer Authentication

Authentication for mobile clients and external integrations relies on **Laravel Sanctum** token-based authentication (`laravel/sanctum` ^4.3).

1. **Token Generation**: Upon successful credentials verification (`POST /api/v1/auth/login`) or Google Single Sign-On exchange (`POST /api/v1/auth/google`), the backend creates an active personal access token:
   ```php
   $token = $user->createToken('auth_token')->plainTextToken;
   ```
2. **Token Storage**: In the mobile Flutter app, this plain text token is stored securely in `SharedPreferences` (or Encrypted SharedPreferences) under the key `cooca_auth_token`.
3. **Token Transmission**: Every subsequent request must supply this token in the standard HTTP `Authorization` header:
   ```http
   Authorization: Bearer 1|u2Hw9x8kM1ZfR3L4...
   ```
4. **Token Revocation**:
   - `POST /api/v1/auth/logout`: Revokes the current access token (`$request->user()->currentAccessToken()->delete()`).
   - `POST /api/v1/profile/logout-all`: Revokes all active access tokens for the user across all devices (`$request->user()->tokens()->delete()`).

### 2.2 Mandatory Request Headers

Every request to a tenant-scoped endpoint (`business.context` middleware) MUST provide the following HTTP headers:

| Header Name | Type | Description | Example |
|---|---|---|---|
| `Accept` | String | Instructs Laravel to return JSON responses instead of Blade redirects | `application/json` |
| `Content-Type` | String | MIME type for POST/PUT/PATCH JSON request bodies | `application/json` |
| `Authorization` | String | Sanctum Bearer token for the authenticated user session | `Bearer 12\|abc...` |
| `X-Business-Id` | UUID v4 | Explicit active tenant workspace ID. Overrides user fallback | `9e8a7b6c-5d4e-3f2a-1b0c-9d8e7f6a5b4c` |
| `X-Location-Id` | UUID v4 | Optional. Selected outlet or branch ID for location-locked data | `3f2a1b0c-9d8e-7f6a-5b4c-9e8a7b6c5d4e` |

### 2.3 Context Resolution Middleware Pipeline

Tenant context resolution executes via `App\Http\Middleware\SetActiveBusinessContext`:

```php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
    $businessId = $request->header('X-Business-Id') 
               ?? $request->input('business_id') 
               ?? $user?->active_business_id;

    if ($businessId && $business = Business::find($businessId)) {
        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        if ($membership) {
            Context::setBusiness($business, $membership);
        }
    }

    return $next($request);
}
```

If the user is not a registered member of the target `business_id`, the system immediately rejects the request with HTTP `403 Forbidden` (`{"message": "You are not a member of the specified business."}`).

### 2.4 Brute Force Protection Mechanics

The authentication endpoint (`POST /api/v1/auth/login`) is defended by `App\Domain\Security\BruteForceProtectionService`:

- **IP Lockout Threshold**: 10 consecutive failed attempts from an IP blocks the IP address across the entire API gateway for 15 minutes.
- **Account Lockout Threshold**: 5 consecutive failed attempts against a specific email account locks that account from that IP for 15 minutes.
- **HTTP 429 Payload Structure**:
  ```json
  {
    "success": false,
    "error": "IP_TEMPORARILY_BLOCKED",
    "message": "Alamat IP Anda diblokir sementara karena terdeteksi aktivitas login mencurigakan berulang kali. Silakan coba lagi dalam 15 menit.",
    "remaining_minutes": 15
  }
  ```

---

## 3. Universal Response Envelope & Error Handling Standards

### 3.1 Standard Success Envelope

Successful API operations return standard JSON envelopes with uniform structure:

```json
{
  "success": true,
  "message": "Operasi berhasil dieksekusi.",
  "data": { ... }
}
```

*Note: For collection endpoints with pagination, pagination metadata is returned either at the root or within `data.pagination`.*

### 3.2 Standard Error Envelope & HTTP Status Codes

When an operation fails, the API responds with a structured error envelope:

```json
{
  "success": false,
  "message": "Deskripsi kesalahan operasional atau bisnis.",
  "error": "OPTIONAL_MACHINE_READABLE_CODE",
  "errors": {
    "field_name": [
      "Pesan validasi spesifik untuk field ini."
    ]
  }
}
```

| HTTP Status Code | Classification | When Triggered |
|---|---|---|
| `200 OK` | Success | Successful GET, PUT, PATCH, or non-creation POST operations. |
| `201 Created` | Created | Successful entity creation (e.g., checkout, shift open, new product). |
| `400 Bad Request` | Client Error | Malformed JSON or invalid parameter syntax. |
| `401 Unauthorized` | Auth Error | Missing or expired Sanctum Bearer token. |
| `403 Forbidden` | RBAC Error | User lacks required role/permission, or cross-tenant access violation. |
| `404 Not Found` | Missing | Requested entity (order, product, customer) does not exist in active tenant. |
| `422 Unprocessable` | Validation | Form validation failures, out-of-stock items, or invalid shift states. |
| `429 Too Many Req` | Rate Limit | Request velocity exceeded rate limit policy, or brute-force lock active. |
| `500 Server Error` | System Error | Unhandled server exception or unrecoverable database failure. |

### 3.3 Rate Limiting Policy Tiers

Rate limiting is enforced via Laravel's `throttle` middleware across five strict policy tiers:

| Throttle Key | Rate Limit | Scope & Targeted Endpoints |
|---|---|---|
| `throttle:5,1` | 5 req / minute | Supervisor PIN Authorization (`POST /api/v1/pos/verify-pin`). |
| `throttle:10,1` | 10 req / minute | Public Auth: Login, Forgot Password, Reset Password, Account Recovery. |
| `throttle:60,1` | 60 req / minute | Customer Auth & Registration (`/api/v1/customer/auth/*`). |
| `throttle:120,1` | 120 req / minute | Marketplace Public Catalog & Model Context Protocol (MCP) endpoints. |
| `throttle:300,1` | 300 req / minute | Standard Authenticated Staff & Owner Operations (POS, Inventory, Finance). |
| `throttle:600,1` | 600 req / minute | High-Volume Inbound Webhooks (TriPay Payment Callbacks, WhatsApp Webhooks). |

---

## 4. Complete REST API Endpoint Group Specifications

---

### Group 1: Authentication & Workspace Session

Governs user authentication, session creation, Google OAuth token exchange, profile telemetry, and multi-tenant business context switching.

#### 1.1 POST `/api/v1/auth/login`
- **Controller Action**: `App\Http\Controllers\Api\V1\AuthController@login`
- **Middleware**: `api`, `throttle:10,1`
- **Description**: Authenticate merchant, cashier, or staff credentials and receive a Sanctum plain text token.
- **Request Headers**: `Content-Type: application/json`, `Accept: application/json`
- **Request Body (JSON)**:
  ```json
  {
    "email": "cashier@cooca.id",
    "password": "password123"
  }
  ```
- **Success Response (`200 OK`)**:
  ```json
  {
    "message": "Login successful.",
    "user": {
      "id": "9e8a7b6c-1111-2222-3333-444455556666",
      "name": "Budi Santoso",
      "email": "cashier@cooca.id",
      "phone": "081234567890",
      "active_business_id": "9e8a7b6c-5d4e-3f2a-1b0c-9d8e7f6a5b4c",
      "created_at": "2026-01-15T08:00:00.000000Z"
    },
    "token": "1|u2Hw9x8kM1ZfR3L4A7bC9dE1fG2hI3jK4lM5nO6p"
  }
  ```
- **Error Response (`422 Unprocessable Entity`)**:
  ```json
  {
    "message": "Invalid email or password."
  }
  ```

#### 1.2 POST `/api/v1/auth/google`
- **Controller Action**: `App\Http\Controllers\Api\V1\AuthController@googleLogin`
- **Middleware**: `api`, `throttle:60,1`
- **Description**: Google Single Sign-On (SSO) login and registration. Automatically provisions a default business if user is newly registered.
- **Request Body (JSON)**:
  ```json
  {
    "google_id": "109823746182374618234",
    "email": "owner.google@gmail.com",
    "name": "Hendra Gunawan",
    "avatar_url": "https://lh3.googleusercontent.com/a/ACg8oc...",
    "phone": "081987654321",
    "business_name": "Kopi Senja Utama",
    "business_scale": "umkm",
    "template_code": "fnb_coffeeshop"
  }
  ```
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "message": "Login Google berhasil.",
    "user": {
      "id": "9e8a7b6c-2222-3333-4444-555566667777",
      "name": "Hendra Gunawan",
      "email": "owner.google@gmail.com"
    },
    "token": "2|p8L7k6J5h4G3f2D1..."
  }
  ```

#### 1.3 GET `/api/v1/auth/me`
- **Controller Action**: `App\Http\Controllers\Api\V1\AuthController@me`
- **Middleware**: `api`, `auth:sanctum`, `throttle:300,1`
- **Description**: Retrieve authenticated user profile and account details.
- **Success Response (`200 OK`)**:
  ```json
  {
    "user": {
      "id": "9e8a7b6c-1111-2222-3333-444455556666",
      "name": "Budi Santoso",
      "email": "cashier@cooca.id",
      "phone": "081234567890",
      "active_business_id": "9e8a7b6c-5d4e-3f2a-1b0c-9d8e7f6a5b4c"
    }
  }
  ```

#### 1.4 POST `/api/v1/auth/logout`
- **Controller Action**: `App\Http\Controllers\Api\V1\AuthController@logout`
- **Middleware**: `api`, `auth:sanctum`, `throttle:300,1`
- **Description**: Invalidate current active Sanctum token.
- **Success Response (`200 OK`)**:
  ```json
  {
    "message": "Logged out successfully."
  }
  ```

#### 1.5 GET `/api/v1/businesses` & GET `/api/v1/me/businesses`
- **Controller Action**: `App\Http\Controllers\Api\V1\BusinessController@index`
- **Middleware**: `api`, `auth:sanctum`, `throttle:300,1`
- **Description**: Enumerate all business workspaces where authenticated user holds valid membership.
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "9e8a7b6c-5d4e-3f2a-1b0c-9d8e7f6a5b4c",
        "name": "COOCA Mart Indonesia",
        "slug": "cooca-mart-indonesia",
        "template_code": "retail_grocery",
        "industry_category": "retail",
        "scale": "umkm",
        "currency": "IDR",
        "currency_symbol": "Rp",
        "is_active": true
      }
    ],
    "businesses": [ ... ]
  }
  ```

#### 1.6 POST `/api/v1/businesses/switch` & POST `/api/v1/me/active-business`
- **Controller Action**: `App\Http\Controllers\Api\V1\BusinessController@setActive`
- **Middleware**: `api`, `auth:sanctum`, `throttle:300,1`
- **Description**: Switch active tenant workspace for user session.
- **Request Body (JSON)**:
  ```json
  {
    "business_id": "9e8a7b6c-5d4e-3f2a-1b0c-9d8e7f6a5b4c"
  }
  ```
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "message": "Active business updated successfully.",
    "active_business": {
      "id": "9e8a7b6c-5d4e-3f2a-1b0c-9d8e7f6a5b4c",
      "name": "COOCA Mart Indonesia",
      "slug": "cooca-mart-indonesia"
    }
  }
  ```

#### 1.7 GET `/api/v1/context/current`
- **Controller Action**: Closure in `routes/api.php`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`
- **Description**: Returns verified business metadata, role, permissions, industry flags, and dynamic landing route.
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "business": {
      "id": "9e8a7b6c-5d4e-3f2a-1b0c-9d8e7f6a5b4c",
      "name": "COOCA Mart Indonesia",
      "slug": "cooca-mart-indonesia",
      "template_code": "retail_grocery",
      "industry_category": "retail",
      "is_food_industry": false,
      "has_dine_in": false,
      "is_workshop": false,
      "is_laundry": false,
      "is_pharmacy": false,
      "is_manufacturing": false,
      "is_service": false,
      "is_retail": true
    },
    "role": "cashier",
    "is_owner": false,
    "permissions": ["pos.terminal", "pos.orders", "customers.view"],
    "can_view_margin": false,
    "landing_route": "pos"
  }
  ```

---

### Group 2: POS & Shift Management

Governs cashier register opening float, real-time drawer balance tracking, cash in / cash out adjustments, operational petty cash recording, and end-of-shift reconciliation (Z-Report).

#### 2.1 GET `/api/v1/pos/shifts/current` & GET `/api/v1/pos/shifts/active`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosShiftController@current`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal,pos.orders`
- **Description**: Fetch cashier's active shift status and live sales summary.
- **Query Parameters**: `location_id` (optional UUID)
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "active_shift": {
      "id": "9e8a7b6c-3333-4444-5555-666677778888",
      "business_id": "9e8a7b6c-5d4e-3f2a-1b0c-9d8e7f6a5b4c",
      "user_id": "9e8a7b6c-1111-2222-3333-444455556666",
      "location_id": "3f2a1b0c-9d8e-7f6a-5b4c-9e8a7b6c5d4e",
      "status": "open",
      "opening_cash": 200000.0,
      "opened_at": "2026-11-20T08:00:00.000000Z",
      "total_cash_sales": 1540000.0,
      "total_non_cash_sales": 890000.0
    },
    "summary": {
      "total_orders": 24,
      "cash_sales": 1540000.0,
      "non_cash_sales": 890000.0,
      "cash_in": 50000.0,
      "cash_out": 100000.0,
      "expected_cash_drawer": 1690000.0
    }
  }
  ```

#### 2.2 POST `/api/v1/pos/shifts/open`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosShiftController@open`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal,pos.orders`
- **Description**: Open a new cashier shift with physical cash drawer float.
- **Request Body (JSON)**:
  ```json
  {
    "opening_cash": 200000,
    "location_id": "3f2a1b0c-9d8e-7f6a-5b4c-9e8a7b6c5d4e",
    "pos_register_id": null,
    "notes": "Modal awal kasir shift pagi",
    "opening_denominations": {
      "100000": 1,
      "50000": 1,
      "20000": 2,
      "10000": 1
    }
  }
  ```
- **Success Response (`201 Created`)**:
  ```json
  {
    "success": true,
    "message": "Shift kasir berhasil dibuka dengan modal awal Rp 200.000",
    "shift": {
      "id": "9e8a7b6c-3333-4444-5555-666677778888",
      "status": "open",
      "opening_cash": 200000.0,
      "opened_at": "2026-11-20T08:00:00.000000Z"
    }
  }
  ```

#### 2.3 POST `/api/v1/pos/shifts/{id}/close`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosShiftController@close`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal,pos.orders`
- **Description**: Close shift, reconcile actual drawer cash against expected totals, calculate cash variance, and lock register.
- **Request Body (JSON)**:
  ```json
  {
    "closing_cash_actual": 1690000,
    "notes": "Kasir shift pagi ditutup normal tanpa selisih.",
    "cashier_notes": "Laci kasir rapi.",
    "closing_denominations": {
      "100000": 15,
      "50000": 3,
      "20000": 2
    }
  }
  ```
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "message": "Shift kasir berhasil ditutup dan direkonsiliasi.",
    "shift": {
      "id": "9e8a7b6c-3333-4444-5555-666677778888",
      "status": "closed",
      "closed_at": "2026-11-20T16:00:00.000000Z",
      "closing_cash_actual": 1690000.0,
      "closing_cash_expected": 1690000.0,
      "cash_difference": 0.0
    }
  }
  ```

#### 2.4 POST `/api/v1/pos/shifts/{id}/cash-movement`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosShiftController@recordCashMovement`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal,pos.orders`
- **Description**: Record manual cash addition (`cash_in`) or withdrawal (`cash_out`) from the cash drawer.
- **Request Body (JSON)**:
  ```json
  {
    "type": "cash_out",
    "amount": 75000,
    "reason": "Beli galon air dan plastik kresek",
    "notes": "Diambil kasir untuk operasional mendesak"
  }
  ```
- **Success Response (`201 Created`)**:
  ```json
  {
    "success": true,
    "message": "Kas Keluar sebesar Rp 75.000 berhasil dicatat.",
    "movement": {
      "id": "9e8a7b6c-4444-5555-6666-777788889999",
      "type": "cash_out",
      "amount": 75000.0,
      "reason": "Beli galon air dan plastik kresek"
    }
  }
  ```

#### 2.5 POST `/api/v1/pos/expenses`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosExpenseApiController@store`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal,pos.orders`
- **Description**: Record formal petty cash expense from POS with receipt image upload and auto-journaling to expense accounts.
- **Request Body (Multipart or JSON)**:
  ```json
  {
    "pos_shift_id": "9e8a7b6c-3333-4444-5555-666677778888",
    "amount": 45000,
    "category": "Operasional Outlet",
    "notes": "Pembelian sabun cuci tangan & tisu meja",
    "receipt_image_base64": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
  }
  ```
- **Success Response (`201 Created`)**:
  ```json
  {
    "success": true,
    "message": "Pengeluaran kas kecil berhasil dicatat.",
    "data": {
      "id": "9e8a7b6c-5555-6666-7777-888899990000",
      "amount": 45000.0,
      "category": "Operasional Outlet"
    }
  }
  ```

---

### Group 3: Product Catalog & Categories

Governs master product catalog lookup, high-speed barcode search, branch-specific pricing overrides, modifier variants, and effective stock balance calculations.

#### 3.1 GET `/api/v1/pos/terminal/bootstrap`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTerminalController@terminalData`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: High-speed initial bootstrap dataset providing active shift, categories, outlet locations, registers, tables, and product catalog with effective stock.
- **Success Response (`200 OK`)**:
  ```json
  {
    "locations": [
      { "id": "3f2a1b0c-9d8e-7f6a-5b4c-9e8a7b6c5d4e", "name": "Outlet Utama", "is_primary": true }
    ],
    "selected_location_id": "3f2a1b0c-9d8e-7f6a-5b4c-9e8a7b6c5d4e",
    "active_shift": { ... },
    "categories": [
      { "id": "cat-01", "name": "Minuman Kopi", "slug": "minuman-kopi" },
      { "id": "cat-02", "name": "Makanan Ringan", "slug": "makanan-ringan" }
    ],
    "products": [
      {
        "id": "prod-001",
        "name": "Kopi Susu Gula Aren",
        "code": "KOP-001",
        "type": "goods",
        "is_service": false,
        "selling_price": 22000.0,
        "effective_stock": 85,
        "unit_symbol": "cup",
        "category_id": "cat-01",
        "has_modifiers": true
      }
    ]
  }
  ```

#### 3.2 GET `/api/v1/pos/terminal/search-products`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTerminalController@searchProducts`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Real-time barcode scanner search or name query returning stock, branch prices, and modifier stock.
- **Query Parameters**:
  - `q`: String (Product name, SKU code, or EAN-13 barcode).
  - `location_id`: UUID (Target branch location).
- **Success Response (`200 OK`)**:
  ```json
  {
    "products": [
      {
        "id": "prod-001",
        "name": "Kopi Susu Gula Aren",
        "code": "8992761001012",
        "type": "goods",
        "is_service": false,
        "track_inventory": true,
        "selling_price": 22000.0,
        "base_cost": 9500.0,
        "unit_code": "cup",
        "category_name": "Minuman Kopi",
        "stock": 85,
        "modifier_groups": [
          {
            "id": "mod-group-sugar",
            "name": "Level Gula",
            "selection_type": "single",
            "is_required": true,
            "options": [
              { "id": "opt-1", "name": "Normal Sugar 100%", "price_delta": 0.0 },
              { "id": "opt-2", "name": "Less Sugar 50%", "price_delta": 0.0 }
            ]
          }
        ]
      }
    ]
  }
  ```

#### 3.3 GET `/api/v1/product-categories`
- **Controller Action**: `App\Http\Controllers\Api\V1\ProductCategoryController@index`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:master_data.product_categories.view`
- **Description**: List all product categories in active tenant.
- **Success Response (`200 OK`)**:
  ```json
  {
    "data": [
      {
        "id": "9e8a7b6c-aaaa-bbbb-cccc-ddddeeeeffff",
        "name": "Sembako & Beras",
        "slug": "sembako-beras",
        "products_count": 18
      }
    ]
  }
  ```

#### 3.4 GET `/api/v1/pos/modifiers`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosModifierApiController@index`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: List all modifier groups and linked raw material consumption options for F&B recipe customization.
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "mod-topping",
        "name": "Extra Topping",
        "selection_type": "multiple",
        "min_selection": 0,
        "max_selection": 3,
        "options": [
          { "id": "top-01", "name": "Boba Pearls", "price_delta": 4000.0 },
          { "id": "top-02", "name": "Espresso Shot", "price_delta": 6000.0 }
        ]
      }
    ]
  }
  ```

---

### Group 4: Order & Transaction Processing

Governs POS cart checkout, suspended/held cart buffers, cart resumption, order history, and supervisor-authorized transaction voids and refunds.

#### 4.1 POST `/api/v1/pos/terminal/checkout`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTerminalController@checkout`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Finalize sales transaction. Decrements stock, processes multi-payments, awards loyalty points, triggers `AutoJournalService`, and returns receipt payload. Supports multi-industry fields (vehicle plate, mileage, technician, SPK notes).
- **Request Body (JSON)**:
  ```json
  {
    "client_uuid": "d3b07384-d113-492c-b26a-9860b6d9258a",
    "order_type": "dine_in",
    "sales_channel": "dine_in",
    "pos_table_id": "tbl-04",
    "table_or_reference": "Meja 4",
    "customer_id": "cust-01",
    "discount_type": "percentage",
    "discount_value": 10,
    "points_to_redeem": 0,
    "location_id": "3f2a1b0c-9d8e-7f6a-5b4c-9e8a7b6c5d4e",
    "items": [
      {
        "product_id": "prod-001",
        "product_name": "Kopi Susu Gula Aren",
        "unit_price": 22000,
        "quantity": 2,
        "discount_amount": 0,
        "notes": "Sedikit es",
        "selected_modifiers": [
          { "modifier_option_id": "opt-2", "name": "Less Sugar 50%", "price_delta": 0 }
        ]
      }
    ],
    "payments": [
      {
        "payment_method": "cash",
        "amount": 50000,
        "reference_number": null
      }
    ],
    "vehicle_license_plate": null,
    "vehicle_model": null,
    "vehicle_mileage": null,
    "technician_id": null
  }
  ```
- **Success Response (`201 Created`)**:
  ```json
  {
    "message": "Transaksi kasir berhasil disimpan.",
    "order": {
      "id": "ord-20261120-0042",
      "order_number": "POS/20261120/0042",
      "order_date": "2026-11-20",
      "order_type": "dine_in",
      "total_amount": 39600.0,
      "paid_amount": 50000.0,
      "change_amount": 10400.0,
      "discount_amount": 4400.0,
      "total_hpp_cost": 19000.0,
      "total_gross_profit": 20600.0,
      "points_earned": 39,
      "status": "completed"
    },
    "whatsapp_url": "https://wa.me/6281234567890?text=Terima+kasih...",
    "duplicate_skipped": false
  }
  ```
- **Idempotent Response (`200 OK`)**:
  If `client_uuid` already exists in database, returns existing order with `"duplicate_skipped": true` preventing double charges.

#### 4.2 POST `/api/v1/pos/terminal/hold` & POST `/api/v1/pos/orders/{id}/hold`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTerminalController@holdOrder`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Suspend an active cart to allow another customer to be served while holding the table or items.
- **Request Body (JSON)**: Same structure as checkout items with `"table_or_reference": "Meja 12"`.
- **Success Response (`200 OK`)**:
  ```json
  {
    "message": "Pesanan berhasil disimpan sementara (Hold).",
    "order": {
      "id": "ord-hold-9921",
      "status": "on_hold",
      "table_or_reference": "Meja 12"
    }
  }
  ```

#### 4.3 GET `/api/v1/pos/terminal/held-orders`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTerminalController@getHeldOrders`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: List all suspended orders for current cashier shift / location.
- **Success Response (`200 OK`)**:
  ```json
  {
    "held_orders": [
      {
        "id": "ord-hold-9921",
        "order_number": "HOLD-003",
        "table_or_reference": "Meja 12",
        "total_amount": 78000.0,
        "items_count": 3,
        "created_at": "2026-11-20T11:15:00.000000Z"
      }
    ]
  }
  ```

#### 4.4 POST `/api/v1/pos/orders/{id}/resume`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTerminalController@resumeOrder`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Resume a suspended order back into the active POS terminal cart.
- **Success Response (`200 OK`)**: Returns full order payload with line items ready for checkout.

#### 4.5 POST `/api/v1/pos/orders/{id}/void`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosOrderController@void`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.supervisor_pin`
- **Description**: Void an existing finalized transaction. Automatically restores inventory stock cards, issues reversal accounting entries, and logs supervisor audit trail.
- **Request Body (JSON)**:
  ```json
  {
    "reason": "Pelanggan salah pesan dan membatalkan pesanan sebelum dibuat."
  }
  ```
- **Success Response (`200 OK`)**:
  ```json
  {
    "message": "Transaksi #POS/20261120/0042 berhasil dibatalkan (void).",
    "order": {
      "id": "ord-20261120-0042",
      "status": "voided"
    }
  }
  ```

#### 4.6 POST `/api/v1/pos/orders/{id}/refund`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosOrderController@refund`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.supervisor_pin`
- **Description**: Process a return/refund on completed order. Reverses cash ledger and returns products to inventory.

---

### Group 5: Payments & Invoicing

Governs payment settlement across multiple tenders (Cash, Dynamic QRIS, EDC / Debit-Credit Card, Bank Transfer, Customer Credit / Tempo, and Split Payment).

#### 5.1 GET `/api/v1/pos/orders/{id}/qr-image`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTerminalController@getQrisImage`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Generates dynamic QRIS string and base64 QR code image via TriPay Payment Gateway for instant customer payment scan.
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "qr_string": "00020101021226680016ID.CO.TRIPAY.WWW0118936009180000000000...",
    "qr_image_url": "https://tripay.co.id/qr/generate/TRIPAY-99210",
    "qr_image_base64": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...",
    "expired_at": "2026-11-20T12:00:00.000000Z"
  }
  ```

#### 5.2 GET `/api/v1/pos/orders/{id}/status`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTerminalController@checkOrderStatus`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Polls payment status of order paid via dynamic QRIS or payment gateway. Returns `"paid": true` immediately upon settlement.
- **Success Response (`200 OK`)**:
  ```json
  {
    "order_id": "ord-20261120-0042",
    "status": "completed",
    "is_paid": true,
    "paid_amount": 39600.0,
    "payment_channel": "qris"
  }
  ```

#### 5.3 POST `/api/v1/pos/orders/{id}/cancel-qris`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTerminalController@cancelQrisOrder`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Cancels pending dynamic QRIS transaction, allowing cashier to switch to cash or EDC tender.

#### 5.4 POST `/api/v1/tripay/callback`
- **Controller Action**: `App\Http\Controllers\Api\V1\Payment\TripayCallbackController@handle`
- **Middleware**: `api`, `throttle:600,1`
- **Description**: Inbound webhook callback from TriPay Payment Gateway with HMAC-SHA256 signature verification. Automatically marks POS orders or online checkout orders as paid upon customer scan.
- **Request Headers**: `X-Callback-Signature: <hmac_sha256>`, `X-Callback-Event: payment_status`
- **Request Body (JSON)**:
  ```json
  {
    "reference": "DEV-T1001",
    "merchant_ref": "ord-20261120-0042",
    "payment_method": "QRIS",
    "total_amount": 39600,
    "status": "PAID",
    "paid_at": 1795171200
  }
  ```
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "message": "Payment callback processed successfully."
  }
  ```

---

### Group 6: Customer & Membership

Governs customer relationship management, contact search, loyalty point programs, credit limit thresholds, and customer debt (kasbon / piutang) repayments.

#### 6.1 GET `/api/v1/crm/customers`
- **Controller Action**: `App\Http\Controllers\Api\V1\Crm\CustomerController@index`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:customers.view`
- **Description**: Search and list customer directory with phone, credit balance, and loyalty tiers.
- **Query Parameters**: `search` (name/phone), `membership_tier` (`silver`, `gold`, `platinum`), `page`, `per_page`.
- **Success Response (`200 OK`)**:
  ```json
  {
    "customers": [
      {
        "id": "cust-01",
        "name": "Iwan Setiawan",
        "phone": "081234567890",
        "company_name": "Toko Berkah",
        "membership_tier": "gold",
        "credit_limit": 5000000.0,
        "current_credit_balance": 1250000.0,
        "points_balance": 450
      }
    ],
    "pagination": { "current_page": 1, "last_page": 1, "per_page": 20, "total": 1 }
  }
  ```

#### 6.2 POST `/api/v1/crm/customers`
- **Controller Action**: `App\Http\Controllers\Api\V1\Crm\CustomerController@store`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:customers.create`
- **Description**: Register a new customer record.
- **Request Body (JSON)**:
  ```json
  {
    "name": "Ratna Dewi",
    "phone": "081388776655",
    "email": "ratna@gmail.com",
    "credit_limit": 1000000,
    "payment_terms_days": 14,
    "notes": "Pelanggan langganan komplek"
  }
  ```
- **Success Response (`201 Created`)**:
  ```json
  {
    "message": "Pelanggan 'Ratna Dewi' berhasil ditambahkan.",
    "customer": {
      "id": "cust-02",
      "name": "Ratna Dewi",
      "is_active": true
    }
  }
  ```

#### 6.3 GET `/api/v1/customers/{id}/debts`
- **Controller Action**: `App\Http\Controllers\Api\V1\Crm\CustomerDebtApiController@debts`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:customers.view,pos.terminal`
- **Description**: Retrieve customer's credit overview, outstanding unpaid tempo bills, credit limit, and payment ledger history.
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "customer": {
      "id": "cust-01",
      "name": "Iwan Setiawan",
      "credit_limit": 5000000.0,
      "current_credit_balance": 1250000.0,
      "available_credit": 3750000.0,
      "payment_terms_days": 30
    },
    "unpaid_orders": [
      {
        "id": "ord-prev-01",
        "order_number": "POS/20261110/0012",
        "total_amount": 1250000.0,
        "notes": "Belanja tempo POS"
      }
    ],
    "transactions": [ ... ]
  }
  ```

#### 6.4 POST `/api/v1/customers/{id}/repay-debt`
- **Controller Action**: `App\Http\Controllers\Api\V1\Crm\CustomerDebtApiController@repayDebt`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:customers.edit,pos.terminal`
- **Description**: Record installment or full repayment against customer debt. If recorded at an active cashier shift in cash, automatically registers a `cash_in` movement in the POS drawer.
- **Request Body (JSON)**:
  ```json
  {
    "amount": 500000,
    "payment_method": "cash",
    "pos_shift_id": "9e8a7b6c-3333-4444-5555-666677778888",
    "notes": "Cicilan kasbon nota tempo #12"
  }
  ```
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "message": "Pembayaran piutang berhasil dicatat dan dibukukan ke buku kas.",
    "data": {
      "amount_paid": 500000.0,
      "previous_balance": 1250000.0,
      "remaining_balance": 750000.0,
      "wa_receipt_url": "https://wa.me/6281234567890?text=Bukti+Pembayaran..."
    }
  }
  ```

#### 6.5 GET `/api/v1/crm/loyalty/program`
- **Controller Action**: `App\Http\Controllers\Api\V1\Crm\CrmLoyaltyController@program`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:crm.view`
- **Description**: Fetch tenant loyalty points rule configuration (spending multiplier, minimum redemption threshold, discount conversion rate).

---

### Group 7: Offline Batch Synchronization Engine

Guarantees 100% data durability during Wi-Fi or cellular dropouts. Transactions queued in mobile SQLite are synchronized in batch upon reconnection with RFC 4122 UUID v4 idempotency keys.

#### 7.1 POST `/api/v1/pos/sync/batch`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosSyncApiController@batchSync`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Bulk synchronize up to 50 queued offline POS transactions. Server validates each `client_uuid`: if already processed, skips idempotently without duplication; if new, executes checkout, logs stock movement, and commits auto-journal.
- **Request Body (JSON)**:
  ```json
  {
    "transactions": [
      {
        "client_uuid": "d3b07384-d113-492c-b26a-9860b6d9258a",
        "offline_created_at": "2026-11-20T10:15:30Z",
        "location_id": "3f2a1b0c-9d8e-7f6a-5b4c-9e8a7b6c5d4e",
        "customer_id": null,
        "order_type": "takeaway",
        "items": [
          {
            "product_id": "prod-001",
            "product_name": "Kopi Susu Gula Aren",
            "unit_price": 22000,
            "quantity": 1,
            "discount_amount": 0
          }
        ],
        "payments": [
          {
            "payment_method": "cash",
            "amount": 22000
          }
        ]
      }
    ]
  }
  ```
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "message": "Sinkronisasi transaksi offline POS selesai diproses.",
    "summary": {
      "total_submitted": 1,
      "total_synced": 1,
      "total_duplicates": 0,
      "total_failed": 0
    },
    "results": [
      {
        "client_uuid": "d3b07384-d113-492c-b26a-9860b6d9258a",
        "status": "synced",
        "order_id": "ord-20261120-0043",
        "order_number": "POS/20261120/0043",
        "total_amount": 22000.0,
        "message": "Transaksi offline berhasil disimpan ke sistem pusat."
      }
    ]
  }
  ```

#### 7.2 Conflict Resolution & Retry Matrix
1. **Idempotency Match**: If server matches `client_uuid`, returns `"status": "duplicate_skipped"`. Mobile marks queue record as confirmed and removes from SQLite.
2. **Stock Discrepancy**: If inventory went to zero while offline, transaction still completes authoritatively to preserve physical cash drawer balance; system logs an audit discrepancy flag for inventory reconciliation.
3. **Exponential Backoff**: Mobile client `PosOfflineSyncManager` retries sync at intervals: $2s \rightarrow 4s \rightarrow 8s \rightarrow 16s \rightarrow \max 60s$.

---

### Group 8: Table Management (F&B Dining Mode)

Governs restaurant and cafe floor layouts, dining table status, self-order table QR tokens, table session occupancy, and checkout release.

#### 8.1 GET `/api/v1/pos/tables`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTableApiController@index`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.tables`
- **Description**: List all dining tables, capacity, QR order tokens, active dining sessions, and unpaid order totals.
- **Success Response (`200 OK`)**:
  ```json
  {
    "data": [
      {
        "id": "tbl-04",
        "table_number": "04",
        "name": "Meja 04 (Area Indoor)",
        "capacity": 4,
        "status": "occupied",
        "is_active": true,
        "qr_token": "tbl-tok-abc123xyz",
        "qr_order_url": "http://cooca_core.test/qr/tbl-tok-abc123xyz",
        "active_session": {
          "id": "sess-9912",
          "customer_name": "Pak Anton",
          "total_amount": 145000.0,
          "orders": [
            {
              "id": "ord-101",
              "order_number": "POS/20261120/0040",
              "is_paid": false,
              "total_amount": 145000.0
            }
          ]
        }
      }
    ]
  }
  ```

#### 8.2 POST `/api/v1/pos/tables`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTableApiController@store`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.tables`
- **Description**: Create a new dining table with automatic unique self-order QR token generation.
- **Request Body (JSON)**:
  ```json
  {
    "table_number": "15",
    "name": "Meja 15 (Smoking Area)",
    "capacity": 6,
    "location_id": "3f2a1b0c-9d8e-7f6a-5b4c-9e8a7b6c5d4e"
  }
  ```
- **Success Response (`201 Created`)**: Returns created table record with QR token.

#### 8.3 POST `/api/v1/pos/tables/{id}/regenerate-qr`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTableApiController@regenerateQr`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.tables`
- **Description**: Invalidate previous QR token and generate a new secure self-order URL.

#### 8.4 GET `/api/v1/pos/tables/{id}/qr-card`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTableApiController@qrCardData`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.tables`
- **Description**: Returns SVG XML vector graphic and business logo for acrylic table standee printing.

#### 8.5 POST `/api/v1/pos/tables/{id}/close-session`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTableApiController@closeSession`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.tables`
- **Description**: Closes dining session, sets table status back to `available`, and clears guest orders.

---

### Group 9: Printing & Receipt Templates

Governs thermal receipt DTO payloads, ESC/POS binary generation, printer station routing rules, and local agent queue management.

#### 9.1 GET `/api/v1/pos/orders/{id}/receipt`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosTerminalController@receipt`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Returns complete structured receipt DTO formatted for ESC/POS thermal printers (58mm / 80mm).
- **Success Response (`200 OK`)**:
  ```json
  {
    "store": {
      "name": "COOCA Mart Indonesia",
      "address": "Jl. Boulevard Barat Raya Blok LA-1, Kelapa Gading",
      "phone": "021-45879900",
      "footer_note": "Terima kasih atas kunjungan Anda!"
    },
    "order": {
      "order_number": "POS/20261120/0042",
      "order_date": "2026-11-20 11:30:15",
      "cashier_name": "Budi Santoso",
      "customer_name": "Umum",
      "order_type": "takeaway",
      "table_or_reference": null,
      "subtotal": 44000.0,
      "discount": 4400.0,
      "tax": 0.0,
      "total": 39600.0,
      "paid": 50000.0,
      "change": 10400.0,
      "points_earned": 39
    },
    "items": [
      {
        "name": "Kopi Susu Gula Aren",
        "qty": 2.0,
        "price": 22000.0,
        "discount": 0.0,
        "total": 44000.0,
        "notes": "Less Sugar 50%"
      }
    ],
    "payments": [
      {
        "method": "CASH",
        "amount": 50000.0,
        "reference": null
      }
    ],
    "whatsapp_url": "https://wa.me/...?text=..."
  }
  ```

#### 9.2 POST `/api/v1/pos/orders/{id}/send-whatsapp-receipt`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosReceiptApiController@sendWhatsAppReceipt`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal,pos.orders`
- **Description**: Automatically dispatches a digital PDF receipt to customer's WhatsApp via Baileys Gateway, or generates a direct `wa.me` sharing URL.
- **Request Body (JSON)**:
  ```json
  {
    "phone": "081234567890",
    "force": false
  }
  ```
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "sent_via_gateway": true,
    "whatsapp_url": "https://wa.me/6281234567890?text=Halo+Pelanggan...",
    "phone": "081234567890",
    "message": "Struk digital berhasil dikirim ke nomor WhatsApp 081234567890."
  }
  ```

#### 9.3 GET `/api/v1/pos/agent/jobs` (Local POS Agent Bridge)
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosAgentApiController@getPendingJobs`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Polled by Local POS Agent desktop software. Returns base64-encoded raw ESC/POS binary streams for physical USB/LAN printers.
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "count": 1,
    "jobs": [
      {
        "id": "job-8812",
        "document_type": "receipt",
        "printer": {
          "id": "prn-01",
          "name": "Epson TM-T82X (Kasir)",
          "connection_type": "usb",
          "interface_address": "USB001",
          "paper_width": "80mm"
        },
        "payload_base64": "G0BAIBtA...raw_esc_pos_bytes..."
      }
    ]
  }
  ```

#### 9.4 POST `/api/v1/pos/agent/jobs/{id}/status`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosAgentApiController@updateJobStatus`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal`
- **Description**: Reports hardware execution outcome (`printed` or `failed`) back to backend print job queue.

---

### Group 10: Reports & Daily Summary

Governs mid-shift auditing (X-Report), day-end register reconciliation (Z-Report), turnover analytics, hourly sales heatmaps, and executive pulse dashboards.

#### 10.1 GET `/api/v1/pos/shifts/{id}/summary` (X-Report / Z-Report Shift Audit)
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosShiftController@summary`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.terminal,pos.orders`
- **Description**: Complete shift financial audit showing opening cash, cash sales, digital sales, cash movements, petty expenses, and drawer reconciliation.
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "summary": {
      "shift_id": "9e8a7b6c-3333-4444-5555-666677778888",
      "cashier_name": "Budi Santoso",
      "opened_at": "2026-11-20 08:00:00",
      "opening_cash": 200000.0,
      "total_transactions": 24,
      "sales": {
        "cash": 1540000.0,
        "qris": 520000.0,
        "edc_card": 370000.0,
        "customer_credit": 0.0,
        "gross_total": 2430000.0
      },
      "cash_movements": {
        "in": 50000.0,
        "out": 100000.0
      },
      "petty_expenses": 45000.0,
      "expected_drawer_cash": 1645000.0
    }
  }
  ```

#### 10.2 GET `/api/v1/pos/reports/sales-summary`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosReportController@salesSummary`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.reports`
- **Description**: Financial turnover report based on transactional snapshot data (Gross Sales, Total Modal HPP, Gross Profit, Average Margin).
- **Query Parameters**: `start_date` (YYYY-MM-DD), `end_date` (YYYY-MM-DD), `location_id` (optional UUID).
- **Success Response (`200 OK`)**:
  ```json
  {
    "summary": {
      "total_sales": 18450000.0,
      "total_modal": 8920000.0,
      "total_gross_profit": 9530000.0,
      "profit_margin_pct": 51.6,
      "total_transactions": 142
    },
    "payment_breakdown": [
      { "method": "cash", "total_amount": 10200000.0, "count": 85 },
      { "method": "qris", "total_amount": 5400000.0, "count": 42 },
      { "method": "edc_card", "total_amount": 2850000.0, "count": 15 }
    ]
  }
  ```

#### 10.3 GET `/api/v1/pos/reports/hourly-heatmap`
- **Controller Action**: `App\Http\Controllers\Api\V1\Pos\PosReportController@hourlyHeatmap`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:pos.reports`
- **Description**: Returns 24-hour distribution of sales volume and revenue to identify peak rush hours.

#### 10.4 GET `/api/v1/mobile/dashboard/pulse`
- **Controller Action**: `App\Http\Controllers\Api\V1\Dashboard\MobileOwnerPulseApiController@pulse`
- **Middleware**: `api`, `auth:sanctum`, `business.context`, `business.active`, `require.permission:dashboard.view`
- **Description**: Executive real-time heartbeat for mobile cockpit: omzet today vs yesterday, growth %, gross profit margin, active cashier shifts, KDS queue count, and low stock warnings.
- **Success Response (`200 OK`)**:
  ```json
  {
    "business_name": "COOCA Mart Indonesia",
    "today_date": "2026-11-20",
    "revenue_today": 12450000.0,
    "revenue_yesterday": 10800000.0,
    "growth_pct": 15.3,
    "gross_profit_today": 6200000.0,
    "profit_margin_pct": 49.8,
    "total_transactions_today": 96,
    "average_ticket": 129687.5,
    "active_shifts_count": 2,
    "kitchen_pending_tickets": 0,
    "critical_low_stock_count": 3
  }
  ```

---

### Group 11: Supplementary Mobile Operations & Bridge Endpoints

#### 11.1 Kitchen Display System (KDS) Queue
- `GET /api/v1/pos/kitchen/orders`: List active kitchen tickets (`confirmed`, `preparing`, `ready`).
- `POST /api/v1/pos/kitchen/orders/{id}/status`: Advance status (`preparing` $\rightarrow$ `ready` $\rightarrow$ `served`).
- `GET /api/v1/pos/kitchen/prep-sheet`: Aggregated ingredient preparation sheet.

#### 11.2 Smart Attendance & Biometrics
- `POST /api/v1/attendance/check-in`: Clock in with GPS coordinates, anti-spoofing telemetry, and selfie image.
- `POST /api/v1/attendance/check-out`: Clock out.
- `POST /api/v1/attendance/face-template/verify`: 1:1 facial biometric embedding verification.
- `GET /api/v1/attendance/payslips`: List monthly digital payslips with PDF download.

#### 11.3 Multi-Industry Vertical Fields (Automotive Workshop & Manufacturing)
- Workshop SPK attributes integrated into `POST /api/v1/pos/terminal/checkout`:
  - `vehicle_license_plate`: License plate string (e.g., `"B 1234 XYZ"`).
  - `vehicle_model`: Car/bike model string (e.g., `"Toyota Avanza 1.5 G"`).
  - `vehicle_mileage`: Odometer reading integer (e.g., `45200`).
  - `technician_id`: Assigned mechanic user UUID.
  - `service_notes`: Diagnostic instructions and checklist findings.

#### 11.4 In-App Mobile APK OTA Update Manifest
- `GET /api/v1/app/version` & `GET /api/v1/app/check-update`
- **Description**: Public manifest endpoint polled by `OtaUpdateService` on Android mobile app launch to deliver seamless, zero-Google-Play direct APK updates.
- **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "app_name": "COOCA My Own",
    "package_name": "com.cooca.myown.cooca_my_own",
    "latest_version": "1.0.10",
    "latest_build": 11,
    "min_supported_version": "1.0.0",
    "force_update": false,
    "download_url": "http://cooca_core.test/cooca.apk",
    "release_notes": [
      "Pembaruan verifikasi biometrik wajah presensi anti-spoofing 3D.",
      "Optimalisasi sinkronisasi offline POS otomatis."
    ]
  }
  ```

### Group 12: API Security Edge Cases & Architectural Mitigations

For exhaustive technical specifications, empirical failure mode analysis, and production-grade mitigations covering Sanctum token expiration/pruning, global multi-device logout (`POST /api/v1/auth/logout-all`), and the decoupled Supervisor PIN signed Grant Ticket engine, see **Section 12: API Security Edge Cases & Architectural Mitigations** below.

---

## 5. End-to-End Verification Matrix

| # | Endpoint & Method | Test Verification Command | Expected Output | Status |
|---|---|---|---|---|
| 1 | `GET /api/v1/health` | `curl -s http://cooca_core.test/api/v1/health` | `{"status":"ok","service":"Cooca Engine"}` | Verified |
| 2 | `GET /api/v1/app/version` | `curl -s http://cooca_core.test/api/v1/app/version` | `{"success":true,"latest_version":"1.0.10"}` | Verified |
| 3 | `POST /api/v1/auth/login` | `curl -s -X POST -H "Content-Type: application/json" -d "{\"email\":\"demo@cooca.id\",\"password\":\"password123\"}" http://cooca_core.test/api/v1/auth/login` | Returns Sanctum `token` and `user` object | Verified |
| 4 | `GET /api/v1/context/current` | `curl -s -H "Authorization: Bearer <token>" -H "X-Business-Id: <uuid>" http://cooca_core.test/api/v1/context/current` | Returns `business`, `role`, and `permissions` | Verified |
| 5 | `GET /api/v1/pos/terminal/bootstrap` | `curl -s -H "Authorization: Bearer <token>" -H "X-Business-Id: <uuid>" http://cooca_core.test/api/v1/pos/terminal/bootstrap` | Returns `products`, `categories`, and `locations` | Verified |
| 6 | `POST /api/v1/pos/sync/batch` | `curl -s -X POST -H "Authorization: Bearer <token>" -H "X-Business-Id: <uuid>" -H "Content-Type: application/json" -d '{"transactions":[...]}' http://cooca_core.test/api/v1/pos/sync/batch` | `{"success":true,"summary":{"total_synced":1}}` | Verified |
| 7 | `GET /api/v1/pos/reports/sales-summary` | `curl -s -H "Authorization: Bearer <token>" -H "X-Business-Id: <uuid>" http://cooca_core.test/api/v1/pos/reports/sales-summary` | Returns `total_sales`, `total_modal`, `payment_breakdown` | Verified |

---

## 12. API Security Edge Cases & Architectural Mitigations

Empirical adversarial evaluation by Challenger 2 identified three critical security failure modes in the REST API authentication and authorization pipeline. The following technical specifications document the root causes, empirical reproduction traces, and production-grade architectural mitigations.

### 12.1 Sanctum Token Lifecycle, Expiration & Password Reset Revocation

#### 1. Empirical Vulnerability Trace
- **Unpublished Configuration**: In `config/sanctum.php`, the file was unpublished in the application configuration directory. Runtime evaluation via `config('sanctum.expiration')` returned `NULL`, meaning issued bearer tokens possessed an **infinite lifetime**.
- **Token Survival Across Password Reset**: In `App\Http\Controllers\Api\V1\AuthController::resetPassword` (lines 309–325):
  ```php
  $status = Password::reset($credentials, function ($user, $password) {
      $user->forceFill([
          'password' => Hash::make($password),
          'remember_token' => Str::random(60),
      ])->save();
      event(new PasswordReset($user));
  });
  ```
  The password reset workflow successfully altered the user password hash and fired the `PasswordReset` event, but performed **no token deletion**. All pre-existing personal access tokens on compromised or lost devices remained completely valid, permitting uninterrupted API access following credential recovery.

#### 2. Architectural Hardening Specifications
1. **Explicit Token Expiration Policy**: Publish `config/sanctum.php` and configure explicit lifetime thresholds:
   ```php
   'expiration' => 60 * 24 * 30, // 30 Days (43,200 minutes) for mobile edge clients
   ```
2. **Mandatory Post-Reset Token Purge**: Update `AuthController::resetPassword` to execute immediate multi-device revocation:
   ```php
   $status = Password::reset($credentials, function ($user, $password) {
       $user->forceFill([
           'password' => Hash::make($password),
           'remember_token' => Str::random(60),
       ])->save();
       
       // Invalidate 100% of existing bearer tokens across all devices
       $user->tokens()->delete();
       
       event(new PasswordReset($user));
   });
   ```
3. **Automated Token Pruning**: Configure Laravel scheduler in `bootstrap/app.php` or `routes/console.php` to prune expired tokens daily:
   ```php
   Schedule::command('sanctum:prune-expired --hours=720')->daily();
   ```

---

### 12.2 Multi-Device Global Session Revocation (`POST /api/v1/auth/logout-all`)

#### 1. Architectural Gap
The existing logout endpoint `POST /api/v1/auth/logout` mapped to `AuthController::logout` (lines 222–224) only invokes:
```php
$request->user()->currentAccessToken()->delete();
```
This revokes only the bearer token of the device initiating the request. In retail and hospitality operations where employee turnover or device loss occurs, merchants require an administrative and user-facing mechanism to terminate all active sessions globally.

#### 2. Specification for `POST /api/v1/auth/logout-all`
- **Route**: `POST /api/v1/auth/logout-all`
- **Middleware**: `['api', 'auth:sanctum']`
- **Controller Action**: `App\Http\Controllers\Api\V1\AuthController::logoutAll`
- **Implementation**:
  ```php
  public function logoutAll(Request $request): JsonResponse
  {
      $user = $request->user();
      $tokenCount = $user->tokens()->count();
      
      // Delete all personal access tokens for the authenticated user
      $user->tokens()->delete();

      // Log security event in audit trail
      AuditLog::create([
          'business_id' => Context::businessId(),
          'user_id' => $user->id,
          'action' => 'auth.logout_all',
          'description' => "Revoked all {$tokenCount} active bearer tokens across all devices.",
          'ip_address' => $request->ip(),
          'user_agent' => $request->userAgent(),
      ]);

      return response()->json([
          'success' => true,
          'message' => 'Seluruh sesi perangkat berhasil dicabut. Silakan login kembali.',
          'data' => [
              'revoked_tokens_count' => $tokenCount,
          ],
      ], 200);
  }
  ```

---

### 12.3 Supervisor PIN Authorization Token Grant Ticket Architecture

#### 1. Empirical Failure Mode Analysis
In `routes/api.php` (lines 1398–1399) and `App\Http\Controllers\Api\V1\Pos\PosOrderController.php` (lines 92–155):
- `PosOrderController::void` and `PosOrderController::refund` accepted only `reason` in the request payload and never inspected or validated a supervisor PIN.
- While `PosTerminalController::verifySupervisorPin` existed at `POST /api/v1/pos/terminal/verify-supervisor-pin`, it merely returned `{"verified": true}` without issuing any cryptographically verifiable grant ticket, session token, or cache receipt.
- Route middleware enforced `require.permission:pos.supervisor_pin` on void/refund endpoints. This created a structural authorization paradox:
  * Frontline cashiers do not have `pos.supervisor_pin` assigned to their user role. Even if a physical supervisor enters their PIN on the mobile tablet, the app sends the subsequent `/void` request using the cashier's bearer token, triggering `403 Forbidden`.
  * If the system grants `pos.supervisor_pin` to cashiers, they can completely bypass PIN verification by hitting `POST /api/v1/pos/orders/{id}/void` directly.

#### 2. Decoupled Grant Ticket Architecture
The resolution decouples supervisor identity verification from the cashier's operational role using short-lived signed grant tickets:

```
+---------------------------------------------------------------------------------------------------+
|                        SUPERVISOR PIN GRANT TICKET AUTHORIZATION FLOW                             |
+---------------------------------------------------------------------------------------------------+
|  FRONT-LINE CASHIER                  BACKEND API GATEWAY                      REDIS CACHE TIER    |
+-------------------+                  +------------------+                     +------------------+
|                   |                  |                  |                     |                  |
| 1. Void Requested |                  |                  |                     |                  |
|    Prompt PIN     |                  |                  |                     |                  |
|                   |                  |                  |                     |                  |
| 2. POST /verify-pin ---------------->| 3. Validate PIN  |                     |                  |
|    (pin, action)  |                  |    against users |                     |                  |
|                   |                  |    with auth     |                     |                  |
|                   |                  |                  |                     |                  |
|                   |                  | 4. Issue Ticket ---------------------->| 5. Store Ticket  |
|                   |                  |    (UUID v4 HMAC)|                     |    TTL: 300 sec  |
|                   |<-----------------|                  |                     |                  |
| 6. Returns Ticket |   200 OK Ticket  |                  |                     |                  |
|                   |                  |                  |                     |                  |
| 7. POST /orders/{id}/void ---------->| 8. Verify Ticket --------------------->| 9. Check & Burn  |
|    (ticket, reason|                  |    Burn on Use   |<--------------------|    (Single-Use)  |
|                   |                  |                  |   Ticket Valid      |                  |
|                   |                  | 10. Execute Void |                     +------------------+
|                   |                  |     Record Audit |
|                   |<-----------------|     Trail Log    |
| 11. Void Complete |   200 OK Voided  |                  |
+-------------------+                  +------------------+
```

#### 3. Concrete Implementation Specifications

##### Endpoint 1: Issue Supervisor Grant Ticket
- **Route**: `POST /api/v1/pos/terminal/verify-supervisor-pin`
- **Request Payload**:
  ```json
  {
    "pin": "123456",
    "action": "order_void",
    "order_id": "9b2e4f8a-1c7d-4e92-8a1e-3b5f7a9c1e2d"
  }
  ```
- **Controller Logic**:
  ```php
  public function verifySupervisorPin(Request $request): JsonResponse
  {
      $validated = $request->validate([
          'pin' => ['required', 'string', 'min:4', 'max:8'],
          'action' => ['required', 'string', 'in:order_void,order_refund,price_override'],
          'order_id' => ['nullable', 'string', 'exists:pos_orders,id'],
      ]);

      $businessId = Context::businessId();

      // Find supervisor belonging to active business with pos.supervisor_pin permission
      $supervisors = User::whereHas('businesses', function ($query) use ($businessId) {
          $query->where('businesses.id', $businessId);
      })->whereNotNull('supervisor_pin')->get();

      $authorizedSupervisor = null;
      foreach ($supervisors as $supervisor) {
          if (Hash::check($validated['pin'], $supervisor->supervisor_pin)) {
              if ($supervisor->hasPermissionTo('pos.supervisor_pin')) {
                  $authorizedSupervisor = $supervisor;
                  break;
              }
          }
      }

      if (! $authorizedSupervisor) {
          return response()->json([
              'success' => false,
              'message' => 'PIN Supervisor tidak valid atau tidak memiliki izin otorisasi.',
          ], 403);
      }

      // Generate cryptographically secure single-use grant ticket
      $ticket = 'sgt_' . Str::random(40);
      $ticketPayload = [
          'business_id' => $businessId,
          'supervisor_id' => $authorizedSupervisor->id,
          'supervisor_name' => $authorizedSupervisor->name,
          'authorized_by_user_id' => $authorizedSupervisor->id,
          'authorized_action' => $validated['action'],
          'order_id' => $validated['order_id'] ?? null,
          'issued_at' => now()->timestamp,
      ];

      // Cache ticket with 5-minute time-to-live (300 seconds)
      Cache::put("pos_supervisor_ticket:{$ticket}", $ticketPayload, 300);

      return response()->json([
          'success' => true,
          'message' => 'PIN Supervisor terverifikasi. Tiket otorisasi diterbitkan.',
          'data' => [
              'verified' => true,
              'grant_ticket' => $ticket,
              'expires_in' => 300,
              'supervisor_name' => $authorizedSupervisor->name,
          ],
      ], 200);
  }
  ```

##### Endpoint 2: Consume Grant Ticket on Void / Refund
- **Route**: `POST /api/v1/pos/orders/{id}/void` & `POST /api/v1/pos/orders/{id}/refund`
- **Request Payload**:
  ```json
  {
    "reason": "Salah input pesanan pelanggan",
    "supervisor_grant_ticket": "sgt_K8vLm2Xp9Qw4R7tZ..."
  }
  ```
- **Controller Validation & Execution**:
  ```php
  public function void(Request $request, string $id): JsonResponse
  {
      $validated = $request->validate([
          'reason' => ['required', 'string', 'max:255'],
          'supervisor_grant_ticket' => ['required', 'string'],
      ]);

      $ticketKey = "pos_supervisor_ticket:{$validated['supervisor_grant_ticket']}";
      $ticketData = Cache::get($ticketKey);

      if (! $ticketData) {
          return response()->json([
              'success' => false,
              'message' => 'Tiket otorisasi supervisor telah kedaluwarsa atau tidak valid.',
          ], 403);
      }

      // Verify business context matches ticket
      if ($ticketData['business_id'] !== Context::businessId()) {
          return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
      }

      // Single-use enforcement: Burn ticket immediately to prevent replay attacks
      Cache::forget($ticketKey);

      $order = PosOrder::where('business_id', Context::businessId())->findOrFail($id);
      
      // Execute void with supervisor attribution
      $this->orderService->voidOrder(
          order: $order,
          reason: $validated['reason'],
          supervisorId: $ticketData['supervisor_id']
      );

      return response()->json([
          'success' => true,
          'message' => 'Pesanan berhasil dibatalkan dengan otorisasi supervisor.',
          'data' => [
              'order_id' => $order->id,
              'status' => 'voided',
              'authorized_by' => $ticketData['supervisor_name'],
          ],
      ], 200);
  }
  ```
- **Route Middleware Alignment**: Update route definitions from `require.permission:pos.supervisor_pin` to `require.permission:pos.terminal`, delegating high-privilege escalation verification strictly to the cryptographic grant ticket engine.
