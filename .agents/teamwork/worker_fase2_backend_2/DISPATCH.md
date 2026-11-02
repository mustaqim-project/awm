# Dispatch for Worker Fase 2 Backend (Replacement)

## Working Directory
`c:\laragon\www\cooca_core\.agents\teamwork\worker_fase2_backend_2`

## Mission
Implement Milestone 1: Backend Fase 2 (Logistik & Gudang Toko) on `c:\laragon\www\cooca_core` for production use.

## Authoritative Inputs
- `c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md` (Read first)
- `c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md`
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_1\report.md` (Backend investigation report)
- `c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_3\report.md` (API contracts & payload schemas)

## Mandatory Integrity Warning
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

## Detailed Scope of Work
1. **Purchase Orders API**:
   - `GET /api/v1/inventory/purchase-orders/pending`: Return confirmed POs awaiting receipt for the active business/location with supplier info and remaining item quantities.
   - `GET /api/v1/inventory/purchase-orders/{id}`: Return PO detail, items with ordered qty, received qty, and remaining qty.
2. **Goods Receipt API**:
   - `POST /api/v1/inventory/goods-receipts`:
     - Payload: `purchase_order_id`, `location_id`, `delivery_order_number`, `received_date`, `items` (array with `purchase_order_item_id`, `product_id`, `quantity_received`, `quantity_damaged`, optional `batch_number`, optional `expiry_date`).
     - Business logic: Atomic transaction, updates PO items received qty, updates PO status, records stock movement (`TYPE_GOODS_RECEIPT`), and records/updates `ProductBatch` when `batch_number` and `expiry_date` are present.
3. **Stock Transfer 2-Step Lifecycle**:
   - `GET /api/v1/inventory/transfers`: List transfers with status filter (`all`, `inbound`, `outbound`).
   - `POST /api/v1/inventory/transfers/dispatch`: Deduct stock from source location (`TYPE_TRANSFER_OUT`), set status `in_transit`, set `sent_at = now()`.
   - `POST /api/v1/inventory/transfers/{id}/receive`: Increment stock at destination location (`TYPE_TRANSFER_IN`), set status `received`, set `received_at = now()`.
4. **Routes**:
   - Register routes under `/api/v1/inventory/` in `routes/api.php` with `auth:sanctum` and `business.active` middleware.
5. **PHPUnit Automated Tests**:
   - Create comprehensive tests in `tests/Feature/Inventory/GoodsReceiptAndPoReceiveTest.php` and `tests/Feature/Inventory/StockTransferDispatchAndReceiveTest.php`.
   - Run the tests via `php artisan test --filter=...` and ensure 100% pass with exit code 0.

## Deliverable
Write implementation report and handoff to `c:\laragon\www\cooca_core\.agents\teamwork\worker_fase2_backend_2\handoff.md` and notify parent when complete.


## 2026-11-02T09:21:05Z
You are Worker Fase 2 Backend Replacement (Backend Logistics Implementer).
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_fase2_backend_2
Your identity: Archetype teamwork_worker.
Authoritative user request path (MANDATORY TO READ FIRST): c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
Your detailed task assignment is in: c:\laragon\www\cooca_core\.agents\teamwork\worker_fase2_backend_2\DISPATCH.md
Architectural scope & contracts:
- c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_1\report.md (Backend exploration report)
- c:\laragon\www\cooca_core\.agents\teamwork\explorer_fase23_3\report.md (Cross-system API contracts)

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Scope:
Implement all Backend Fase 2 endpoints in cooca_core:
1. GET /api/v1/inventory/purchase-orders/pending
2. GET /api/v1/inventory/purchase-orders/{id}
3. POST /api/v1/inventory/goods-receipts (atomic, update PO received qty, stock movement, batch & expiry date tracking in product_batches)
4. GET /api/v1/inventory/transfers
5. POST /api/v1/inventory/transfers/dispatch (deduct source stock, in_transit status)
6. POST /api/v1/inventory/transfers/{id}/receive (add destination stock, received status)
7. Register routes in routes/api.php
8. Write and run automated PHPUnit tests in tests/Feature/Inventory/ (ensure 100% pass)

Write your report to c:\laragon\www\cooca_core\.agents\teamwork\worker_fase2_backend_2\report.md and create handoff.md. Send a message to parent when complete.


## 2026-11-02T09:42:15Z
**Context**: Status check on Milestone 1 (Backend Fase 2)
**Content**: Heartbeat check — please update on your implementation progress and update progress.md.
**Action**: Report current status or ETA.
