# Handoff Report — OpenShift Lifecycle & Bug Investigation

**Working Directory**: `c:\laragon\www\cooca_core\.agents\teamwork\explorer_survey_openshift_1\`  
**Agent ID**: `explorer_survey_openshift_1`  
**Parent Agent**: `73d9d035-59a2-45d3-a207-90bc9a5adcc4`  
**Mission**: Deep-dive investigation into OpenShift lifecycle, bugs, and root causes across Flutter mobile app (`C:\laragon\www\cooca_my_own`), backend contracts (`C:\laragon\www\cooca_core`), and live production (`https://cooca.id`).  
**Handoff Type**: Hard Handoff (Investigation Complete)  

---

## 1. Observation

### Obs 1.1: Error Response False-Positive Success Trap in Mobile `PosService`
- **File**: `c:\laragon\www\cooca_my_own\lib\core\services\api_client.dart`
  - Lines 220–228:
    ```dart
    return {
      'success': false,
      'statusCode': response.statusCode,
      'message': decoded is Map && decoded.containsKey('message')
          ? decoded['message']
          : 'Terjadi kendala server (Kode: ${response.statusCode})',
      'errors': decoded is Map ? decoded['errors'] : null,
      'data': decoded is Map ? decoded['data'] : null,
    };
    ```
- **File**: `c:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart`
  - Lines 41–49 (`openShift`):
    ```dart
    final response = await _api.post('/api/v1/pos/shifts/open', body: body);
    if (response is Map && (response['success'] == true || response.containsKey('shift') || response.containsKey('data'))) {
      final shiftData = response['data'] ?? response['shift'];
      return {
        'success': true,
        'data': shiftData,
        'shift': shiftData,
        'message': response['message'] ?? 'Shift kasir berhasil dibuka.',
      };
    }
    ```
  - Lines 67–75 (`closeShift`):
    ```dart
    final response = await _api.post('/api/v1/pos/shifts/$shiftId/close', body: {...});
    if (response is Map && (response['success'] == true || response.containsKey('shift') || response.containsKey('data'))) {
      final shiftData = response['data'] ?? response['shift'];
      return {
        'success': true,
        'data': shiftData, ...
    ```
  - Lines 416, 439 (`recordCashMovement`, `recordExpense`):
    ```dart
    if (response is Map && (response['success'] == true || response.containsKey('cash_movement') || response.containsKey('data')))
    ```
- **File**: `c:\laragon\www\cooca_my_own\lib\state\merchant_state.dart`
  - Lines 1809–1818 (`openShift`):
    ```dart
    if (res['success'] == true) {
      final sData = res['data'] ?? res['shift'];
      if (sData != null && sData is Map) {
        activeShift = PosShift.fromJson(Map<String, dynamic>.from(sData));
        await LocalStorageService().setCachedPosActiveShift(Map<String, dynamic>.from(sData));
        notifyListeners();
      }
      await refreshPosCatalog();
      return {'success': true, 'message': res['message'] ?? 'Shift kasir berhasil dibuka.'};
    }
    ```
- **File**: `c:\laragon\www\cooca_my_own\lib\screens\pos\pos_shift_modal.dart`
  - Lines 183–193 (`_handleShiftAction`):
    ```dart
    final res = await widget.state.openShift(
      openingCash: _enteredCash,
      notes: _notesController.text.trim(),
    );
    final success = res['success'] == true;
    if (mounted) {
      setState(() => _isLoading = false);
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: success ? const Color(0xFF34C759) : const Color(0xFFFF3B30),
          content: Text(res['message']?.toString() ?? (success ? 'Shift kasir berhasil dibuka.' : 'Gagal membuka shift.')),
        ),
      );
    }
    ```

### Obs 1.2: Destruction of Local Shift Cache on Network Dropout
- **File**: `c:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart`
  - Lines 389–401 (`getActiveShift`):
    ```dart
    Future<Map<String, dynamic>> getActiveShift({String? locationId}) async {
      final params = locationId != null ? {'location_id': locationId} : null;
      final response = await _api.get('/api/v1/pos/shifts/active', queryParams: params);
      if (response is Map) {
        final rawShift = response['active_shift'] ?? response['shift'] ?? response['data'];
        return {
          'success': true,
          'active_shift': rawShift,
          'summary': response['summary'],
        };
      }
      return {'success': false, 'active_shift': null};
    }
    ```
- **File**: `c:\laragon\www\cooca_my_own\lib\screens\pos\pos_shift_modal.dart`
  - Lines 83–93 (`_initShiftData`):
    ```dart
    final activeRes = await _posService.getActiveShift(locationId: widget.state.selectedLocationId);
    if (activeRes['success'] == true) {
      final raw = activeRes['active_shift'];
      if (raw is Map) {
        final liveShift = PosShift.fromJson(Map<String, dynamic>.from(raw));
        widget.state.updateActiveShift(liveShift);
        await LocalStorageService().setCachedPosActiveShift(Map<String, dynamic>.from(raw));
      } else {
        widget.state.updateActiveShift(null);
        await LocalStorageService().setCachedPosActiveShift(null);
      }
    }
    ```

### Obs 1.3: Cross-Cashier Shift Hijacking in Backend `PosShiftService`
- **File**: `c:\laragon\www\cooca_core\app\Domain\Pos\PosShiftService.php`
  - Lines 21–36 (`getActiveShift`):
    ```php
    public function getActiveShift(Business $business, User $user, ?string $locationId = null): ?PosShift
    {
        $query = PosShift::where('business_id', $business->id)
            ->where('status', PosShift::STATUS_OPEN);

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        $userShift = (clone $query)->where('user_id', $user->id)->first();
        if ($userShift) {
            return $userShift;
        }

        return $query->first();
    }
    ```
- **File**: `c:\laragon\www\cooca_core\app\Http\Controllers\Api\V1\Pos\PosShiftController.php`
  - Lines 84–115 (`current`):
    Calls `$activeShift = $this->shiftService->getActiveShift($business, $user, $locationId);` and returns `$activeShift` to any requesting user.

### Obs 1.4: Missing Terminal / POS Register Identifier on Mobile
- **File**: `c:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart`
  - Lines 32–40: `body` in `openShift` only contains `opening_cash`, `notes`, and optional `location_id`. It never contains `pos_register_id`.
- **File**: `c:\laragon\www\cooca_core\app\Domain\Pos\PosShiftService.php`
  - Lines 52–62: Concurrency check supports `pos_register_id`, but mobile omits it completely.

### Obs 1.5: Static Opening Cash Float & Missing Denominations Breakdown
- **File**: `c:\laragon\www\cooca_my_own\lib\screens\pos\pos_shift_modal.dart`
  - Line 105: `_cashController.text = '100000'; // Default opening cash float Rp 100.000`
- **File**: `c:\laragon\www\cooca_core\resources\views\app\pos\shifts.blade.php`
  - Lines 20–22, 439–485: Web supports location picker, register picker, denominations breakdown (`openDenoms: {'100000': 0, '50000': 0, ...}`), and direct float input. Mobile only supports direct text input.

### Obs 1.6: Unconditional Modal Dismissal on Failure
- **File**: `c:\laragon\www\cooca_my_own\lib\screens\pos\pos_shift_modal.dart`
  - Lines 168, 186: `Navigator.pop(context);` is called unconditionally on both failure and success.

### Obs 1.7: Live API Status
- **URL**: `https://cooca.id/api/v1/health`
  - Response: `{"status":"ok","service":"Cooca Engine","timestamp":"2026-11-03T09:27:42+07:00","version":"1.0.0"}` (HTTP 200).

---

## 2. Logic Chain

1. **Step 1 (From Obs 1.1)**: In `ApiClient._processResponse`, whenever an HTTP error response is returned (e.g., 401, 403, 409, 422, 500), the returned Map always contains the key `'data': null`.
2. **Step 2 (From Obs 1.1)**: `PosService.openShift` uses the condition `(response['success'] == true || response.containsKey('shift') || response.containsKey('data'))` to decide if the request succeeded. Because `response.containsKey('data')` evaluates to `true` (despite `data` being `null` and `success` being `false`), `PosService.openShift` executes the success branch and returns `{'success': true, 'data': null, 'message': response['message']}`.
3. **Step 3 (From Obs 1.1)**: `MerchantState.openShift` receives `res['success'] == true`. It checks `if (sData != null && sData is Map)`, which is false because `sData` is `null`. As a result, `activeShift` is **NOT updated** and remains `null`. However, `MerchantState` returns `{'success': true, 'message': res['message']}` to the UI.
4. **Step 4 (From Obs 1.1 & Obs 1.6)**: `PosShiftModal._handleShiftAction` receives `success == true`. It displays a **green success SnackBar** containing the backend error text (e.g., "The selected location id is invalid." or "Modul fitur ini sedang dinonaktifkan") and calls `Navigator.pop(context)` to close the modal.
5. **Step 5 (From Steps 1–4)**: The user believes the shift was opened because of the green SnackBar and modal closing. When the user attempts to checkout, `_openPaymentSheet` in `pos_screen.dart` checks `if (!state.hasActiveShift)` and blocks the transaction with *"Shift kasir belum dibuka"*, trapping the user.
6. **Step 6 (From Obs 1.2)**: When offline or experiencing transient network drops, `_api.get('/api/v1/pos/shifts/active')` catches an exception and returns `{'success': false, 'message': '...'}`. `PosService.getActiveShift` receives this Map, checks `if (response is Map)` (which is true), ignores `response['success'] == false`, extracts `rawShift = null`, and returns `{'success': true, 'active_shift': null}`.
7. **Step 7 (From Obs 1.2 & Step 6)**: `PosShiftModal._initShiftData` receives `activeRes['success'] == true` and `raw == null`. It executes `widget.state.updateActiveShift(null)` and `await LocalStorageService().setCachedPosActiveShift(null)`. Thus, **any transient network error destroys the persistent active shift cache**, locking the user out of offline POS operation.
8. **Step 8 (From Obs 1.3)**: In `PosShiftService::getActiveShift`, if user A has an open shift and user B requests active shift, line 35 returns `$query->first()`. User B receives User A's shift. User B's mobile app marks `activeShift` as active, preventing User B from opening their own shift, allowing User B to close User A's shift, and associating User B's sales with User A's shift.
9. **Step 9 (From Obs 1.4 & Obs 1.5)**: Mobile never sends `pos_register_id`, preventing multi-terminal isolation, and hardcodes `'100000'` without loading previous drawer ending balance.

---

## 3. Caveats

- **Caveat 1**: Hardware ESC/POS thermal printing for shifts (`POST /api/v1/pos/shifts/{posShift}/print`) exists on backend and web, but mobile does not implement a shift summary print button; investigation was limited to API contracts.
- **Caveat 2**: Multi-industry features (F&B tables, service SPK, laundry rack) hook into checkout orders, but their shift lifecycle dependencies (cash movement and petty cash deduction) use the standard `pos_shifts` table.
- **Caveat 3**: No changes have been implemented to source code during this read-only investigation.

---

## 4. Conclusion

The root causes of OpenShift failures are **concrete, reproducible software defects**:
1. **Critical Client Bug**: `PosService` incorrectly evaluates `response.containsKey('data')` on error responses, misclassifying all backend HTTP validation and authorization errors as successful shift openings with `data: null`.
2. **Critical Client Bug**: `PosService.getActiveShift()` treats network exceptions as `active_shift: null` with `success: true`, triggering local storage purge of active shifts during temporary offline periods.
3. **Critical Backend Bug**: `PosShiftService::getActiveShift()` line 35 unconditionally falls back to `$query->first()`, leaking other cashiers' open shifts to unauthorized users and blocking new shift openings.
4. **Architectural Gap**: Mobile omits `pos_register_id` in `openShift`, breaking multi-terminal drawer isolation.
5. **UI Flaw**: `PosShiftModal` dismisses unconditionally on failure and hardcodes `'100000'` instead of retrieving previous drawer closing float.

---

## 5. Verification Method

To independently verify these findings:

1. **Verify Root Cause 1 (Error misclassification)**:
   - Inspect `c:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart` line 41.
   - Run a mock test in Flutter passing `{'success': false, 'statusCode': 422, 'message': 'Invalid', 'data': null}` to `openShift`. Observe that `res['success']` evaluates to `true`.
2. **Verify Root Cause 2 (Cache destruction on network drop)**:
   - Inspect `c:\laragon\www\cooca_my_own\lib\core\services\pos_service.dart` line 392 and `pos_shift_modal.dart` lines 83–92.
   - Set mock network failure in `pos_service.dart`: observe `setCachedPosActiveShift(null)` is called.
3. **Verify Root Cause 3 (Cross-cashier shift leak)**:
   - Inspect `c:\laragon\www\cooca_core\app\Domain\Pos\PosShiftService.php` lines 30–36.
   - Run: `php artisan test --filter=PosMultiRegisterShiftAndStockFeatureTest`.
4. **Verify Existing Tests**:
   - Mobile: `cd C:\laragon\www\cooca_my_own; flutter test test/pos_shift_expense_test.dart` (4/4 tests pass).
   - Live API: `read_url_content` on `https://cooca.id/api/v1/health` returns HTTP 200 OK.
