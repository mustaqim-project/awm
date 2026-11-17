import os
import json

BASE_DIR = r"C:\laragon\www\cooca_core"

# Load routes.json
try:
    routes_data = json.load(open(os.path.join(BASE_DIR, "routes.json"), encoding="utf-8"))
except UnicodeDecodeError:
    routes_data = json.load(open(os.path.join(BASE_DIR, "routes.json"), encoding="utf-16"))

routes_by_name = {r.get("name"): r for r in routes_data if r.get("name")}
routes_by_uri = {r.get("uri"): r for r in routes_data if r.get("uri")}

# Check route lookups
test_routes = [
    "pos.terminal", "pos.shifts.index", "pos.orders.index", "pos.tables.index", "pos.kitchen.index", "pos.printers.index",
    "services.index", "approval-rules.index", "approvals.inbox", "settings.audit-logs.index", "roles.index",
    "products.bom", "materials.index", "calculator.index", "labor-machines.index", "simulator.index", "profitability.index", "pos.modifiers.index",
    "sales.quotations.index", "sales.orders.index", "invoices.index", "finance.receivables", "sales.returns.index", "customer.orders.index",
    "inventory.movements", "inventory.stocks", "warehouse.index", "inventory.transfers.index", "inventory.opnames.index", "suppliers.index", "purchase-orders.index", "purchasing.receipts.create", "purchasing.bills.index", "purchase.returns.index",
    "finance.cash-bank.index", "finance.expenses.index", "finance.accounting.journals", "finance.accounting.coa", "finance.accounting.ledger", "finance.accounting.balance-sheet", "reports.index", "finance.settlements.index", "tax.index",
    "storefront.settings.index", "storefront.theme", "landing-page.theme-editor.edit", "storefront.orders.index", "storefront.shipping.index", "storefront.reservations.index", "marketplace-hub.index",
    "whatsapp.templates.index", "whatsapp.broadcast.index", "social-media.calendar", "social-media.inbox",
    "customers.index", "crm.loyalty.index", "crm.points.index", "crm.promotions.index", "crm.members.index", "crm.vouchers.index",
    "employees.index", "hrm.attendance.clock-in", "hrm.loans.index", "hrm.payrolls.index",
    "settings.integrations.mcp.index", "cooca-ai.index", "cooca-ai.actions",
    "dashboard", "pos.reports.index"
]

print(f"Testing {len(test_routes)} route names:")
for rn in test_routes:
    r = routes_by_name.get(rn)
    if r:
        print(f"  [OK] {rn:<35} -> {r['method']:<10} /{r['uri']}")
    else:
        # Search by partial match
        matching = [name for name in routes_by_name if rn.split(".")[-1] in name]
        print(f"  [MISSING] {rn:<35} -> alternatives: {matching[:3]}")
