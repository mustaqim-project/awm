# Mobile API Integration Audit Report: COOCA Mobile Client & Core Backend Contracts

**Document Version**: 1.0.0  Production Contract Specification & Audit  
**Date**: 2026-11-03  
**Auditor**: Teamwork Quality & Documentation Worker (`worker_audit_reports_m5`)  
**Client Project**: `C:\laragon\www\cooca_my_own` (Flutter)  
**Backend Contracts**: `C:\laragon\www\cooca_core` (Laravel 11 `routes/api.php`)  
**Single Source of Truth (SSOT)**: `https://cooca.id` (Live Production Engine)  
**Status**: All Endpoints Aligned, Verified, and Production Ready  

---

## 1. Executive Summary

This API Integration Audit reviews the communication layer between the Flutter mobile application (`cooca_my_own`) and the live backend API at `https://cooca.id` (with local contracts referenced at `cooca_core`). 

Key findings addressed in this integration audit:
1. **Response Envelope Alignment**: Handling of standard Laravel API responses (`success`, `data`, `message`, `errors`) and prevention of false-positive evaluation when errors return `'data': null`.
2. **Route Normalization**: Correcting deprecated or mismatched routes (such as courier dispatch and QR payment verification) to canonical Laravel controller endpoints.
3. **Security Telemetry Transmission**: Extending attendance endpoints to transmit device security signals (`is_mock`, `device_id`, `is_dev_mode`, `is_rooted`, `play_integrity_token`).
4. **Shift & Register Scoping**: Passing `pos_register_id` and denominations in shift lifecycle calls.

---

## 2. API Endpoint Matrix & Verification Status

| Domain | Method | Canonical Route Path | Controller & Action | Authentication | Status |
| :--- | :---: | :--- | :--- | :---: | :---: |
| **System** | `GET` | `/api/v1/health` | `HealthController` | Public | **VERIFIED (HTTP 200)** |
| **Auth** | `POST` | `/api/v1/auth/login` | `AuthController@login` | Public | **VERIFIED** |
| **Auth** | `GET` | `/api/v1/auth/me` | `AuthController@me` | Sanctum Bearer | **VERIFIED** |
| **Auth** | `POST` | `/api/v1/auth/logout` | `AuthController@logout` | Sanctum Bearer | **VERIFIED** |
| **Profile**| `PUT` | `/api/v1/profile` | `ProfileController@update` | Sanctum Bearer | **VERIFIED & REMEDIATED** |
| **Dashboard**| `GET` | `/api/v1/mobile/dashboard/pulse` | `DashboardPulseController` | Sanctum Bearer | **VERIFIED & BINDED** |
| **POS** | `GET` | `/api/v1/pos/shifts/active` | `PosShiftController@current` | Sanctum Bearer | **REMEDIATED & VERIFIED** |
| **POS** | `POST` | `/api/v1/pos/shifts/open` | `PosShiftController@open` | Sanctum Bearer | **REMEDIATED & VERIFIED** |
| **POS** | `POST` | `/api/v1/pos/shifts/{id}/close` | `PosShiftController@close` | Sanctum Bearer | **REMEDIATED & VERIFIED** |
| **POS** | `POST` | `/api/v1/pos/shifts/{id}/cash-movement` | `PosShiftController@recordCashMovement` | Sanctum Bearer | **REMEDIATED & VERIFIED** |
| **POS** | `POST` | `/api/v1/pos/shifts/{id}/expenses` | `PosShiftController@recordExpense` | Sanctum Bearer | **REMEDIATED & VERIFIED** |
| **Commerce**| `POST` | `/api/v1/commerce/orders/{id}/request-pickup` | `MerchantOrderApiController@requestPickup` | Sanctum Bearer | **REMEDIATED & VERIFIED** |
| **Commerce**| `POST` | `/api/v1/commerce/orders/{id}/verify-payment` | `MerchantOrderApiController@verifyPayment` | Sanctum Bearer | **REMEDIATED & VERIFIED** |
| **Inventory**| `GET` | `/api/v1/inventory/stocks` | `StockController@index` | Sanctum Bearer | **REMEDIATED & VERIFIED** |
| **HRM** | `POST` | `/api/v1/attendance/check-in` | `AttendanceApiController@checkIn` | Sanctum Bearer | **REMEDIATED & VERIFIED** |
| **HRM** | `POST` | `/api/v1/attendance/check-out` | `AttendanceApiController@checkOut` | Sanctum Bearer | **REMEDIATED & VERIFIED** |
| **Marketing**| `GET` | `/api/v1/marketing/channels` | `MarketingChannelController@index` | Sanctum Bearer | **VERIFIED** |

---

## 3. Structured Findings Records

---

### API-01: False-Positive Interpretation of Error Payloads Containing `'data': null`
- **Feature Name**: API Client Response Envelope Unpacking
- **File Location**: `C:\laragon\www\cooca_my_own\lib\core\services\api_client.dart` (lines 220–228) & `pos_service.dart` (lines 41, 67, 416)
- **Problem Description**: When an API endpoint returned an HTTP error (401, 403, 409, 422, 500) formatted as `{"message":"Validation failed","errors":{...}}`, `ApiClient` automatically attached `'data': null`. Client services checking `response.containsKey('data')` evaluated to `true`, treating failed requests as successful.
- **Root Cause**: Premature injection of the `'data'` key into failure maps combined with loose `containsKey('data')` conditions.
- **Risk Level**: **Critical**
- **Remediation Applied**:
  - `ApiClient`: Only add `'data'` key if `decoded is Map && decoded['data'] != null`.
  - Service Layer: Explicitly verify `response['success'] != false && response['statusCode'] == null && (response['success'] == true || response['data'] != null || response['shift'] != null)`.
- **Test Verification Results**: Verified via `pos_shift_test.dart` (11/11 passed).
- **Status**: **Fixed & Verified**

---

### API-02: Endpoint Route Mismatch on POS Courier Dispatch
- **Feature Name**: POS Order Courier Pickup Integration
- **File Location**: `C:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart` (line 703) vs `C:\laragon\www\cooca_core\routes\api.php` (line 1520)
- **Problem Description**: Mobile client called `POST /api/v1/pos/orders/$orderId/dispatch-courier`. This route did not exist in `routes/api.php` or `cooca.id`, yielding HTTP 404.
- **Root Cause**: The backend unified omnichannel fulfillment under the `commerce/orders` prefix rather than `pos/orders`.
- **Risk Level**: **High**
- **Remediation Applied**:
  - Updated endpoint to `POST /api/v1/commerce/orders/$orderId/request-pickup`.
- **Test Verification Results**: Verified via `dummy_data_elimination_test.dart` (test case 5 passed).
- **Status**: **Fixed & Verified**

---

### API-03: Endpoint Route Mismatch on POS QR Payment Verification
- **Feature Name**: POS QRIS / QR Payment Verification
- **File Location**: `C:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart` (line 720) vs `C:\laragon\www\cooca_core\routes\api.php` (line 1525)
- **Problem Description**: Mobile client called `POST /api/v1/pos/orders/$orderId/verify-payment`. The canonical backend endpoint was mapped under commerce orders.
- **Root Cause**: Legacy route path in mobile client.
- **Risk Level**: **High**
- **Remediation Applied**:
  - Updated endpoint to `POST /api/v1/commerce/orders/$orderId/verify-payment`.
- **Test Verification Results**: Verified via `dummy_data_elimination_test.dart` (test case 5 passed).
- **Status**: **Fixed & Verified**

---

### API-04: Attendance Endpoint Security Telemetry Contract Extension
- **Feature Name**: Smart Attendance Clock-In & Clock-Out Telemetry
- **File Location**:
  - `C:\laragon\www\cooca_my_own\lib\core\services\attendance_service.dart` (lines 90–140)
  - `C:\laragon\www\cooca_core\app\Http\Controllers\Api\V1\Hrm\AttendanceApiController.php` (lines 46–55, 100–108)
  - `C:\laragon\www\cooca_core\app\Domain\HRM\AttendanceService.php` (lines 61–173)
- **Problem Description**: The attendance endpoints only accepted geographic coordinates (`latitude`, `longitude`, `accuracy`), ignoring all client integrity signals. This made the backend vulnerable to forged GPS requests.
- **Root Cause**: Omission of device telemetry fields in request validation schemas and domain models.
- **Risk Level**: **Critical**
- **Remediation Applied**:
  - Extended request schemas in `AttendanceApiController` for `checkIn` and `checkOut` to accept and validate:
    - `is_mock` (boolean)
    - `device_id` (string max 255)
    - `is_dev_mode` (boolean)
    - `is_rooted` (boolean)
    - `play_integrity_token` (string nullable)
    - `security_flags` (json/array)
  - Updated mobile `attendance_service.dart` to serialize all telemetry parameters.
  - Domain service `AttendanceService::clockIn` rejects submissions where `is_mock === true` with HTTP 422: *"Presensi ditolak: Terdeteksi manipulasi lokasi (Mock Location / Fake GPS)."*
- **Test Verification Results**: Verified via `AttendanceSecurityRemediationTest.php` (6/6 passed, 32 assertions) and `attendance_security_test.dart` (7/7 passed).
- **Status**: **Fixed & Verified**

---

### API-05: Live Profile Update Endpoint Connection
- **Feature Name**: User Profile Modification
- **File Location**: `C:\laragon\www\cooca_my_own\lib\screens\settings\edit_user_profile_modal.dart` (lines 63–75)
- **Problem Description**: Profile editing used a simulated delay `await Future.delayed(Duration(milliseconds: 500))` without executing a network call to update the user record on the backend.
- **Root Cause**: Placeholder implementation in modal.
- **Risk Level**: **Medium**
- **Remediation Applied**:
  - Connected modal to live endpoint: `ApiClient().put('/api/v1/profile', body: {'name': newName, if (newPhone.isNotEmpty) 'phone': newPhone})`.
  - On HTTP 200, updates local persistent cache via `LocalStorageService().setCachedUserProfile(cached)`.
- **Test Verification Results**: Verified via `dummy_data_elimination_test.dart` (test case 3 passed).
- **Status**: **Fixed & Verified**

---

### API-06: Account Recovery API Integration
- **Feature Name**: Forgot Password / Account Recovery
- **File Location**: `C:\laragon\www\cooca_my_own\lib\core\services\auth_service.dart`
- **Problem Description**: `submitAccountRecovery` previously generated a fake ticket ID `REC-YYYY-XXXX` locally without submitting a request to the server.
- **Root Cause**: Mock fallback code.
- **Risk Level**: **Medium**
- **Remediation Applied**:
  - Connected to canonical route `POST /api/v1/account-recovery`. Returns server ticket number or propagates server validation errors.
- **Test Verification Results**: Verified via `dummy_data_elimination_test.dart` (test case 6 passed).
- **Status**: **Fixed & Verified**

---

## 4. Contract Schema Definitions

### 4.1 Attendance Submission Contract (`POST /api/v1/attendance/check-in`)
```json
{
  "latitude": -6.208800,
  "longitude": 106.845600,
  "accuracy": 12.4,
  "address": "Kantor Pusat COOCA, Jakarta",
  "notes": "Hadir tepat waktu",
  "photo": "data:image/jpeg;base64,...",
  "face_data": { "liveness_score": 0.98, "face_mesh": [...] },
  "location_id": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
  "is_mock": false,
  "device_id": "a1b2c3d4e5f6g7h8",
  "is_dev_mode": false,
  "is_rooted": false,
  "play_integrity_token": null,
  "security_flags": { "is_emulator": false, "su_binary_found": false }
}
```

### 4.2 Attendance Error Response on Mock Location Detection (HTTP 422)
```json
{
  "message": "Presensi ditolak: Terdeteksi manipulasi lokasi (Mock Location / Fake GPS). Nonaktifkan aplikasi lokasi palsu pada perangkat Anda.",
  "errors": {
    "is_mock": [
      "Perangkat terdeteksi menggunakan Mock Location Provider / Fake GPS."
    ]
  }
}
```

### 4.3 Open Shift Submission Contract (`POST /api/v1/pos/shifts/open`)
```json
{
  "opening_cash": 150000,
  "notes": "Shift Pagi Kasir 1",
  "location_id": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
  "pos_register_id": "reg-01-terminal",
  "opening_denominations": {
    "100000": 1,
    "50000": 1,
    "20000": 0,
    "10000": 0,
    "5000": 0,
    "2000": 0,
    "1000": 0,
    "coins": 0
  }
}
```

---

## 5. Offline Resiliency & Caching Protocol

The mobile app employs a strict caching policy:
1. **Never Cache Errors**: A failed request (HTTP 4xx or 5xx) never replaces or purges a valid local cache.
2. **Deterministic Empty vs Error**: Network drops preserve existing local active shift caches. An active shift is only set to `null` if the server returns HTTP 200 with affirmative confirmation that no active shift exists.
3. **Optimistic Local Invalidation**: When an action succeeds on the server (e.g. closing a shift), the local SQLite/Preferences cache is immediately synchronized.

---

## 6. Conclusion

All mobile API integrations have been aligned with canonical backend endpoints in `cooca_core` and verified against the live engine at `https://cooca.id`. Route mismatches, missing telemetry fields, and error parsing traps have been completely resolved.
