# Dispatch for Worker Fase 3 Backend: Customer Debts & Omnichannel Digital APIs

## Working Directory
`c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_backend`

## Target Repository
`c:\laragon\www\cooca_core`

## Mission
Implement Milestone 3: Backend Fase 3 (Customer Piutang & Omnichannel Digital) on `c:\laragon\www\cooca_core` for production use.

## Authoritative Inputs
- `c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md` (Read first)
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_1\report.md` (Backend investigation report)
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_3\report.md` (Cross-system API contracts)

## Mandatory Integrity Warning
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Detailed Scope of Work
1. **Customer Debts & Kasbon APIs**:
   - `GET /api/v1/customers/{id}/debts`: Return customer debt overview (`credit_limit`, `current_credit_balance`, `payment_terms_days`) and history of credit transactions (`CustomerCreditTransaction`).
   - `POST /api/v1/customers/{id}/repay-debt`:
     - Payload: `amount`, `payment_method` (cash/transfer/qris), optional `notes`, optional `pos_shift_id`.
     - Business logic: Atomic execution, invokes `LoyaltyService::recordCustomerCreditPayment`, decrements `current_credit_balance`, creates `CustomerCreditTransaction::TYPE_PAYMENT`, records cash entry in `CashTransaction`, and returns updated balance and `whatsapp_url`.
   - Controller: `app/Http/Controllers/Api/V1/Crm/CustomerDebtApiController.php` (or under `Customers`).
2. **Digital WhatsApp Receipt API**:
   - `POST /api/v1/pos/orders/{id}/send-whatsapp-receipt`:
     - Payload: optional `phone`.
     - Business logic: Generates formatted receipt text via `LoyaltyService::generateWhatsAppReceiptUrl`, attempts dispatch via `WhatsAppGatewayService::sendReceipt`, and returns `{ success: true, message: '...', whatsapp_url: 'https://wa.me/...' }`.
   - Controller: `app/Http/Controllers/Api/V1/Pos/PosReceiptApiController.php`.
3. **Storefront & QR Incoming Orders APIs**:
   - `GET /api/v1/pos/incoming-orders` (or `GET /api/v1/commerce/orders/incoming`):
     - Returns pending orders from web storefront (`CommerceOrder`) and QR table orders (`PosOrder` with `order_source = qr_table`).
   - `POST /api/v1/pos/orders/{id}/accept` and `POST /api/v1/pos/orders/{id}/reject`:
     - Updates status, updates kitchen queue if needed.
4. **Route Registration**:
   - Register all endpoints in `routes/api.php` under proper prefixes with `auth:sanctum` and `business.active` middleware.
5. **PHPUnit Automated Tests**:
   - Implement comprehensive feature tests in `tests/Feature/Crm/CustomerDebtAndRepayTest.php`, `tests/Feature/Pos/PosWhatsAppReceiptApiTest.php`, and `tests/Feature/Pos/IncomingStorefrontOrdersApiTest.php`.
   - Run tests via `php artisan test --filter=...` and verify 100% pass with exit code 0.

## Deliverable
Write implementation report and handoff to `c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_backend\handoff.md` and notify parent when complete.


## 2026-11-02T09:56:22Z
You are Worker Fase 3 Backend (Customer Debts & Omnichannel Digital APIs Implementer).
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_backend
Target repository: c:\laragon\www\cooca_core
Your identity: Archetype teamwork_worker.
Authoritative user request path (MANDATORY TO READ FIRST): c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Your detailed task assignment is in: c:\laragon\www\cooca_core\.agents\teamwork\worker_fase3_backend\DISPATCH.md
Architectural scope & contracts:
- c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_1\report.md (Backend exploration report)
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_3\report.md (Cross-system API contracts)

Scope:
Implement all Backend Fase 3 endpoints in cooca_core:
1. GET /api/v1/customers/{id}/debts: Customer debt overview and debt history transactions.
2. POST /api/v1/customers/{id}/repay-debt: Debt repayment recording via LoyaltyService, update credit balance, record cash entry, return WhatsApp receipt URL.
3. POST /api/v1/pos/orders/{id}/send-whatsapp-receipt: WhatsApp receipt delivery via Meta API / Gateway with fallback URL.
4. GET /api/v1/pos/incoming-orders: Incoming orders from web storefront (CommerceOrder) and QR tables (PosOrder with order_source = qr_table), plus accept/reject actions.
5. Register routes in routes/api.php with auth:sanctum and business.active.
6. Write and run PHPUnit feature tests in tests/Feature/Crm/ and tests/Feature/Pos/ (verify 100% pass).
