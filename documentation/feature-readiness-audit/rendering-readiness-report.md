# UI Rendering Readiness Audit Report: 90 Features

**Document Version**: 1.0  
**Audit Date**: November 17, 2026  
**Auditor**: Teamwork Worker M1 (`teamwork_worker_m1_1`)  
**Scope**: Systematic Rendering Evaluation for all 90 COOCA SaaS ERP Features  
**Target Codebase**: `C:\laragon\www\cooca_core`  
**Execution Standard**: Requirement R5 (Strict Separation of Actual Screenshot, Reconstructed Render, Blocked, and Proposed UI)  

---

## 1. Executive Summary & Rendering Strategy

This rendering readiness report establishes the definitive technical roadmap and rendering decision for every single one of the **90 functional features** in COOCA SaaS ERP. In compliance with Requirement R5 of the project charter, the audit strictly classifies each feature into one of four mutually exclusive rendering readiness categories:
1. **ACTUAL_UI_SCREENSHOT**: The feature possesses an active, production-ready Blade view extending `layouts/app.blade.php` or `layouts/pos.blade.php`, connects directly to Eloquent models, and renders completely within the local application environment using seeded demo tenant data (`Warung Berkah Nusantara`).
2. **SOURCE_BASED_RECONSTRUCTION**: The feature's Blade template and backend logic are fully developed, but rendering full live UI state requires active external third-party production credentials (e.g. Meta Cloud API, Shopee/TikTok Developer Portals, external MCP LLM clients). For documentation, high-fidelity source-based reconstructions generated in `storage/app/public/features_ui_render/` (via `scripts/build_ui_renders.py`) are authorized.
3. **BLOCKED**: The feature strictly requires physical hardware attachment (e.g. physical ESC/POS Bluetooth/USB thermal receipt printer) to complete its display cycle.
4. **PROPOSED_UI**: Reserved for unimplemented features or placeholder stubs (**0 features found**).

```
┌─────────────────────────────────────────────────────────────┐
│                 RENDERING DECISION BREAKDOWN                │
├───────────────────────────────┬─────────────┬───────────────┤
│ Decision Category             │ Count       │ Percentage    │
├───────────────────────────────┼─────────────┼───────────────┤
│ ACTUAL_UI_SCREENSHOT          │ 84 Features │  93.3%        │
│ SOURCE_BASED_RECONSTRUCTION   │  5 Features │   5.6%        │
│ BLOCKED                       │  1 Feature  │   1.1%        │
│ PROPOSED_UI                   │  0 Features │   0.0%        │
├───────────────────────────────┼─────────────┼───────────────┤
│ TOTAL                         │ 90 Features │ 100.0%        │
└───────────────────────────────┴─────────────┴───────────────┘
```

---

## 2. Rendering Infrastructure & Pre-Existing Assets

### 2.1 Available Render Engines in `scripts/`
The COOCA repository contains dedicated automation scripts designed for rendering and UI image synthesis:
1. `scripts/build_ui_renders.py` (88,877 bytes, 1,356 lines): High-fidelity Python renderer utilizing Playwright to construct macOS Sonoma window frames (1440x900 and 1600x1000) with Apple HIG typography, SF Pro / Inter font pairings, dark/light canvas support, and base64 asset injection.
2. `scripts/render_all_m2_views.php` (4,003 bytes, 121 lines): Boots Laravel in-memory SQLite, seeds tenant business context `Warung Berkah Nusantara`, and simulates HTTP requests for backoffice and public storefront views, writing rendered HTML snapshots to `scripts/rendered_m2_views/`.
3. `scripts/render_all_features_master_ui.py` (5,369 bytes): Generates wide composite master panorama renders for marketing deliverables (`cooca_master_all_features_ui.png`).
4. `scripts/capture_finance_sidebar.mjs` (1,084 bytes): Playwright script capturing live sidebar navigation states.

### 2.2 Verified High-Fidelity Rendered Assets in `storage/app/public/features_ui_render/`
The following 14 production-grade PNG assets exist in the repository, representing verified visual proof of core modules:
- `01_pos_operasional.png` (Terminal POS, Barcode Scan, Cart, Modifiers, Split Payment)
- `02_katalog_produk_jasa.png` (Product Bento Grid, Multi-Outlet Pricing, UOM)
- `03_inventory_pergudangan.png` (Multi-Warehouse Stock Summary, Valuation, Movements)
- `04_pembelian_supplier_ap.png` (Purchase Order Lifecycle, Goods Receipt, AP Aging)
- `05_penjualan_b2b_ar.png` (Commercial Quotation, Sales Order, Invoice PDF, AR Aging)
- `06_keuangan_kas_bank.png` (Multi-Bank Cash Cards, Ledger, Expense Attachments)
- `07_marketplace_omnichannel.png` (Shopee/Tokopedia/TikTok Centralized Order Matrix)
- `08_whatsapp_gateway_crm.png` (Baileys QR Pairing, Multi-Device, Broadcast Campaign)
- `09_hrm_payroll_absensi.png` (GPS Geofenced Presensi, PPh 21 TER Slip, Staff Roster)
- `10_social_media_hub.png` (Unified Inbox, Feed Publisher, Content Calendar)
- `11_ai_copilot_office.png` (Virtual Executive Office, 4 C-Level Agents, Action Gate)
- `12_laporan_eksekutif_analitik.png` (Income Statement, 15-Tab POS Analytics, Tax 0.5%)
- `00_dashboard_bento_executive.png` (Executive Overview Bento Grid)
- `13_saas_billing_limits.png` (Subscription Tiers, Resource Usage Gauges)

---

## 3. Comprehensive 90-Feature Rendering Readiness Decision Matrix

The table below catalogs all 90 features, mapping each to its primary Blade template, rendering decision, technical rationale, and designated asset capture path:

| ID | Feature Name | Category | Primary Blade View | Rendering Decision | Technical Rationale & Asset Path |
|:---:|---|---|---|:---:|---|
| **1** | Kalkulator HPP Interaktif | Costing Engine | `resources/views/app/calculator.blade.php` | **ACTUAL_UI_SCREENSHOT** | Live Alpine.js math, fully rendered; capture at `/calculator` |
| **2** | Simulasi Harga Jual & Margin | Costing Engine | `resources/views/app/simulator/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Interactive sensitivity sliders; capture at `/simulator` |
| **3** | Biaya Tenaga Kerja & Mesin | Costing Engine | `resources/views/app/labor-machines/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Complete CRUD bento modal sheets; capture at `/labor-machines` |
| **4** | Analisa Titik Impas BEP | Costing Engine | `resources/views/app/profitability/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Chart.js visual BEP curve; capture at `/profitability` |
| **5** | Master Bahan Baku & Konversi | Bahan Baku & BOM | `resources/views/app/materials/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Full inventory table, ROP badges; capture at `/materials` |
| **6** | Kategori Bahan Baku | Bahan Baku & BOM | `resources/views/app/master-data/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Unified master-data view (`$type='material-categories'`) |
| **7** | Resep BOM & Yield Factor | Bahan Baku & BOM | `resources/views/app/products/bom.blade.php` | **ACTUAL_UI_SCREENSHOT** | Multi-tier recipe ingredient tree; capture at `/products/{id}/bom` |
| **8** | Mesin Impor Massal Excel/CSV | Bahan Baku & BOM | `resources/views/app/import/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Dropzone upload with live validation table; capture at `/import` |
| **9** | Katalog Produk & Multi-Outlet | Produk & Layanan | `resources/views/app/products/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Bento card grid, price overrides; capture at `/products` |
| **10** | Layanan Servis & SPK Bengkel | Produk & Layanan | `resources/views/app/services/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Work order pipeline & technician assignment; capture at `/services` |
| **11** | Varian Produk & Modifiers | Produk & Layanan | `resources/views/app/products/modifiers.blade.php` | **ACTUAL_UI_SCREENSHOT** | Modifier group builder & option pills; capture at `/pos/modifiers` |
| **12** | Kategori Produk & Hierarki | Produk & Layanan | `resources/views/app/master-data/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Unified master-data view (`$type='product-categories'`) |
| **13** | Satuan Ukur Standar (UOM) | Produk & Layanan | `resources/views/app/master-data/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Unified master-data view (`$type='units'`) |
| **14** | Terminal Kasir POS & Scanner | Point of Sale (POS) | `resources/views/app/pos/terminal.blade.php` | **ACTUAL_UI_SCREENSHOT** | Sub-100ms Alpine.js POS canvas; capture at `/pos` |
| **15** | Pembayaran Multi-Metode & Split | Point of Sale (POS) | `resources/views/app/pos/terminal.blade.php` | **ACTUAL_UI_SCREENSHOT** | Payment modal dialog (`payment-modal.blade.php`) |
| **16** | Parkir & Lanjutkan Pesanan | Point of Sale (POS) | `resources/views/app/pos/terminal.blade.php` | **ACTUAL_UI_SCREENSHOT** | Held orders queue drawer (`held-orders-modal.blade.php`) |
| **17** | Otorisasi PIN Supervisor POS | Point of Sale (POS) | `resources/views/app/pos/terminal.blade.php` | **ACTUAL_UI_SCREENSHOT** | Virtual PIN keypad modal (`supervisor-pin-modal.blade.php`) |
| **18** | Manajemen Shift & Laci Kas | Point of Sale (POS) | `resources/views/app/pos/shifts.blade.php` | **ACTUAL_UI_SCREENSHOT** | Shift list, blind close cash modal; capture at `/pos/shifts` |
| **19** | Riwayat Transaksi & Struk | Point of Sale (POS) | `resources/views/app/pos/orders.blade.php` | **ACTUAL_UI_SCREENSHOT** | Order history drawer, reprint watermark; capture at `/pos/orders` |
| **20** | Denah Meja & Pesan Mandiri QR | Point of Sale (POS) | `resources/views/app/pos/tables.blade.php` | **ACTUAL_UI_SCREENSHOT** | Interactive floor plan (Meja 1-12) & SVG QR card generator |
| **21** | Kitchen Display System (KDS) | Point of Sale (POS) | `resources/views/app/pos/kitchen.blade.php` | **ACTUAL_UI_SCREENSHOT** | Real-time kitchen ticket cards with aging timers; capture at `/pos/kitchen` |
| **22** | Integrasi Printer Thermal ESC/POS | Point of Sale (POS) | `resources/views/app/pos/printers/index.blade.php` | **BLOCKED** | Requires physical ESC/POS hardware; document via settings UI + diagram |
| **23** | Multi-Lokasi Gudang & Outlet | Gudang & Stok | `resources/views/app/warehouse/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Multi-warehouse card grid; capture at `/warehouse` |
| **24** | Ringkasan Stok & Valuasi | Gudang & Stok | `resources/views/app/inventory/stocks.blade.php` | **ACTUAL_UI_SCREENSHOT** | Stock balances & valuation cards; capture at `/inventory/stocks` |
| **25** | Kartu Stok & Jejak Mutasi | Gudang & Stok | `resources/views/app/inventory/movements.blade.php` | **ACTUAL_UI_SCREENSHOT** | Chronological audit trail; capture at `/inventory/movements` |
| **26** | Sesi Stok Opname & Rekonsiliasi| Gudang & Stok | `resources/views/app/inventory/opname.blade.php` | **ACTUAL_UI_SCREENSHOT** | Blind counting form & auto variance table; capture at `/inventory/opname` |
| **27** | Mutasi Transfer Stok Antar Gudang| Gudang & Stok | `resources/views/app/inventory/transfers.blade.php` | **ACTUAL_UI_SCREENSHOT** | Two-way dispatch/receive tracker; capture at `/inventory/transfers` |
| **28** | Surat Pesanan Pembelian (PO) | Pembelian (AP) | `resources/views/app/purchase-orders/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | PO lifecycle pipeline, printable PDF; capture at `/purchase-orders` |
| **29** | Penerimaan Barang Gudang | Pembelian (AP) | `resources/views/app/purchasing/receipts/create.blade.php`| **ACTUAL_UI_SCREENSHOT** | Physical goods receipt receiving form; capture at `/purchasing/receipts/create` |
| **30** | Tagihan Pembelian (Vendor Bills)| Pembelian (AP) | `resources/views/app/purchasing/bills/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Vendor bill tracking, 3-way matching; capture at `/purchasing/bills` |
| **31** | Manajemen Hutang & AP Aging | Pembelian (AP) | `resources/views/app/finance/payables.blade.php` | **ACTUAL_UI_SCREENSHOT** | 4 Aging buckets table; capture at `/finance/payables` |
| **32** | Retur Pembelian Supplier | Pembelian (AP) | `resources/views/app/purchasing/returns/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Debit note return voucher; capture at `/purchasing/returns` |
| **33** | Surat Penawaran Harga (Quotation)| Penjualan B2B (AR) | `resources/views/app/quotations/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Commercial quotation, 1-click convert; capture at `/quotations` |
| **34** | Surat Perintah Penjualan (SO) | Penjualan B2B (AR) | `resources/views/app/sales-orders/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Sales order tracker & stock lock; capture at `/sales-orders` |
| **35** | Faktur Penjualan B2B (Invoices) | Penjualan B2B (AR) | `resources/views/app/invoices/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Apple HIG commercial invoice; capture at `/invoices` |
| **36** | Manajemen Piutang & AR Aging | Penjualan B2B (AR) | `resources/views/app/finance/receivables.blade.php` | **ACTUAL_UI_SCREENSHOT** | Customer receivable aging buckets; capture at `/finance/receivables` |
| **37** | Retur Penjualan B2B | Penjualan B2B (AR) | `resources/views/app/sales/returns/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Credit note PDF generator; capture at `/sales/returns` |
| **38** | Kas Utama & Rekening Bank | Keuangan & Kas | `resources/views/app/finance/cash-bank/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Multi-bank liquidity cards; capture at `/finance/cash-bank` |
| **39** | Buku Kas Berjalan & Arus Dana | Keuangan & Kas | `resources/views/app/finance/cash-bank/ledger.blade.php` | **ACTUAL_UI_SCREENSHOT** | Chronological cash flow ledger; capture at `/finance/cash-bank/ledger` |
| **40** | Beban Operasional & Struk | Keuangan & Kas | `resources/views/app/finance/expenses.blade.php` | **ACTUAL_UI_SCREENSHOT** | Expense voucher with receipt preview; capture at `/finance/expenses` |
| **41** | Transfer Saldo Antar Rekening | Keuangan & Kas | `resources/views/app/finance/cash-bank/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Dual mutation transfer modal; capture in `/finance/cash-bank` |
| **42** | Mesin Jurnal Otomatis 9 Jalur | Akuntansi Umum | `resources/views/app/finance/journals.blade.php` | **ACTUAL_UI_SCREENSHOT** | Double-entry journal voucher ledger; capture at `/finance/journals` |
| **43** | Bagan Akun SAK EMKM (COA) | Akuntansi Umum | `resources/views/app/finance/accounting/coa.blade.php` | **ACTUAL_UI_SCREENSHOT** | SAK EMKM hierarchical account tree; capture at `/finance/coa` |
| **44** | Buku Besar (General Ledger) | Akuntansi Umum | `resources/views/app/finance/accounting/general-ledger.blade.php`| **ACTUAL_UI_SCREENSHOT** | Account-by-account transaction drilldown; capture at `/finance/general-ledger` |
| **45** | Neraca Saldo & Posisi Keuangan | Akuntansi Umum | `resources/views/app/finance/accounting/trial-balance.blade.php`| **ACTUAL_UI_SCREENSHOT** | Balance sheet & trial balance; capture at `/finance/trial-balance` |
| **46** | Rekonsiliasi Bank & Rekening Koran| Akuntansi Umum | `resources/views/app/finance/accounting/reconciliation.blade.php`| **ACTUAL_UI_SCREENSHOT** | Split-view bank statement matcher; capture at `/finance/reconciliation` |
| **47** | Payout Hub & Rekening Pencairan | Payout & EDC | `resources/views/app/finance/payout-accounts/index.blade.php`| **ACTUAL_UI_SCREENSHOT** | Bank account verification list; capture at `/finance/payout-accounts` |
| **48** | Batch Settlement Transaksi | Payout & EDC | `resources/views/app/finance/settlements/index.blade.php`| **ACTUAL_UI_SCREENSHOT** | Gateway escrow payout batches; capture at `/finance/settlements` |
| **49** | Manajemen Mesin EDC Kasir | Payout & EDC | `resources/views/app/finance/edc-terminals/index.blade.php`| **ACTUAL_UI_SCREENSHOT** | Physical EDC terminal cards (TID/MID); capture at `/finance/edc-terminals` |
| **50** | Database Pelanggan & LTV | CRM & Loyalitas | `resources/views/app/customers/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Customer profiles & LTV tracking; capture at `/customers` |
| **51** | Tingkatan Membership & Poin | CRM & Loyalitas | `resources/views/app/crm/members.blade.php` | **ACTUAL_UI_SCREENSHOT** | Loyalty tier cards & point log; capture at `/crm/members` |
| **52** | Plafon Piutang Kasbon Pelanggan| CRM & Loyalitas | `resources/views/app/crm/members.blade.php` | **ACTUAL_UI_SCREENSHOT** | Customer store credit manager; capture inside `/crm/members` |
| **53** | Kupon Diskon & Voucher Promo | CRM & Loyalitas | `resources/views/app/crm/vouchers.blade.php` | **ACTUAL_UI_SCREENSHOT** | Promo voucher cards & quota progress; capture at `/crm/vouchers` |
| **54** | WhatsApp Gateway Baileys Scan | WhatsApp Gateway | `resources/views/app/whatsapp/index.blade.php` | **SOURCE_BASED_RECONSTRUCTION** | Needs live Baileys Node daemon; use `features_ui_render/08_*.png` |
| **55** | E-Receipt Struk Kasir via WA | WhatsApp Gateway | `resources/views/app/whatsapp/index.blade.php` | **SOURCE_BASED_RECONSTRUCTION** | Needs live session; use `features_ui_render/08_*.png` |
| **56** | Broadcast Promosi Massal WA | WhatsApp Gateway | `resources/views/app/whatsapp/broadcast.blade.php` | **ACTUAL_UI_SCREENSHOT** | Smartphone previewer & composer; capture at `/whatsapp/broadcast` |
| **57** | Integrasi Akun Media Sosial | Media Sosial | `resources/views/app/social_media/index.blade.php` | **SOURCE_BASED_RECONSTRUCTION** | Live OAuth requires developer portal; use `10_social_media_hub.png` |
| **58** | Inbox Komentar & DM Terpadu | Media Sosial | `resources/views/app/social_media/inbox.blade.php` | **ACTUAL_UI_SCREENSHOT** | Split-pane chat interface; capture at `/social-media/inbox` |
| **59** | Publikasi Feed & Caption AI | Media Sosial | `resources/views/app/social_media/posts.blade.php` | **ACTUAL_UI_SCREENSHOT** | Instagram carousel previewer; capture at `/social-media/posts` |
| **60** | Kalender Jadwal Publikasi Konten| Media Sosial | `resources/views/app/social_media/calendar.blade.php` | **ACTUAL_UI_SCREENSHOT** | Monthly editorial grid; capture at `/social-media/calendar` |
| **61** | Website Profil Toko (`/b/{slug}`) | Toko Online & CMS | `resources/views/app/storefront/settings.blade.php` | **ACTUAL_UI_SCREENSHOT** | Live mobile preview & public site; capture at `/storefront/settings` & `/b/{slug}`|
| **62** | Storefront Theme Editor (25 Ind) | Toko Online & CMS | `resources/views/app/storefront/theme_editor.blade.php` | **ACTUAL_UI_SCREENSHOT** | 25 Industry theme switcher & live preview; capture at `/storefront/theme-editor` |
| **63** | Manajemen Pesanan Toko Online | Toko Online & CMS | `resources/views/app/storefront/orders/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Web order queue & transfer proof preview; capture at `/storefront/orders` |
| **64** | Ongkir Logistik & Label Resi | Toko Online & CMS | `resources/views/app/storefront/shipping/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Shipping rate setup & AWB label; capture at `/storefront/shipping` |
| **65** | Reservasi Meja & Booking Layanan | Toko Online & CMS | `resources/views/app/storefront/reservations/index.blade.php`| **ACTUAL_UI_SCREENSHOT** | Outlet reservation calendar; capture at `/storefront/reservations` |
| **66** | Koneksi Multi-Channel Marketpl | Marketplace Hub | `resources/views/app/marketplace/index.blade.php` | **SOURCE_BASED_RECONSTRUCTION** | Live partner keys needed; use `07_marketplace_omnichannel.png` |
| **67** | Sinkronisasi Stok & Harga Live | Marketplace Hub | `resources/views/app/marketplace/products.blade.php` | **ACTUAL_UI_SCREENSHOT** | Channel price multiplier & stock buffers; capture at `/marketplace/products` |
| **68** | Pesanan Marketplace Terpusat | Marketplace Hub | `resources/views/app/marketplace/orders.blade.php` | **ACTUAL_UI_SCREENSHOT** | Centralized order matrix with channel badges; capture at `/marketplace/orders` |
| **69** | Rekonsiliasi Escrow & Komisi | Marketplace Hub | `resources/views/app/marketplace/finances.blade.php` | **ACTUAL_UI_SCREENSHOT** | Channel fee breakdown & net payout audit; capture at `/marketplace/finances` |
| **70** | Chat Pembeli Marketplace | Marketplace Hub | `resources/views/app/marketplace/chat.blade.php` | **ACTUAL_UI_SCREENSHOT** | Omnichannel buyer chat feed; capture at `/marketplace/chat` |
| **71** | Database Master Karyawan | HRM & Payroll | `resources/views/app/hrm/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Staff directory & salary structure cards; capture at `/hrm` |
| **72** | Presensi GPS Geofence & Selfie | HRM & Payroll | `resources/views/app/portal/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Mobile employee workstation, camera & GPS canvas; capture at `/portal` |
| **73** | Cuti, Lembur & Koreksi Absensi| HRM & Payroll | `resources/views/app/portal/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Leave request modal drawer; capture inside `/portal` |
| **74** | Penggajian PPh 21 TER & Slip WA| HRM & Payroll | `resources/views/app/hrm/payroll/payslip.blade.php` | **ACTUAL_UI_SCREENSHOT** | Indonesian PP 58/2023 payslip; capture at `/hrm/payroll/{id}/payslip` |
| **75** | Kotak Masuk Approval MAR | Pusat Persetujuan | `resources/views/app/approvals/inbox.blade.php` | **ACTUAL_UI_SCREENSHOT** | Maker-Checker pending approval cards; capture at `/approvals/inbox` |
| **76** | Riwayat Keputusan Approval | Pusat Persetujuan | `resources/views/app/approvals/history.blade.php` | **ACTUAL_UI_SCREENSHOT** | Immutable decision audit log; capture at `/approvals/history` |
| **77** | Aturan Plafon Nominal MAR | Pusat Persetujuan | `resources/views/app/approvals/rules.blade.php` | **ACTUAL_UI_SCREENSHOT** | Threshold amount & approver tiers; capture at `/settings/approval-rules` |
| **78** | Laporan Laba Rugi & Arus Kas | Laporan Eksekutif | `resources/views/app/reports/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | Executive financial performance dashboard; capture at `/reports` |
| **79** | Analitika Penjualan Kasir POS | Laporan Eksekutif | `resources/views/app/pos/reports.blade.php` | **ACTUAL_UI_SCREENSHOT** | 15 specialized analytics tabs; capture at `/pos/reports` |
| **80** | Laporan Kepatuhan Pajak 0.5% | Laporan Eksekutif | `resources/views/app/tax/index.blade.php` | **ACTUAL_UI_SCREENSHOT** | PP 55/2022 gross turnover calculation; capture at `/tax` |
| **81** | Analitika Perputaran Stok | Laporan Eksekutif | `resources/views/app/inventory/movements.blade.php` | **ACTUAL_UI_SCREENSHOT** | Slow-moving inventory turnover analysis; capture at `/inventory/movements` |
| **82** | Markas AI Bisnis & Virtual Office| Cooca AI Copilot | `resources/views/app/ai/lobby.blade.php` | **ACTUAL_UI_SCREENSHOT** | Apple HIG virtual office, 4 C-Level avatars; capture at `/ai/company` |
| **83** | Meja Departemen AI Spesialis | Cooca AI Copilot | `resources/views/app/ai/offices/operations.blade.php` | **ACTUAL_UI_SCREENSHOT** | COO/CMO departmental desks; capture at `/ai/office/operations` |
| **84** | Eksekusi Tindakan AI & Approval | Cooca AI Copilot | `resources/views/app/ai/actions.blade.php` | **ACTUAL_UI_SCREENSHOT** | AI Action Proposal review center; capture at `/ai/actions` |
| **85** | Generator Visual Banner AI | AI Studio Visual | `resources/views/app/ai-studio/create.blade.php` | **ACTUAL_UI_SCREENSHOT** | Visual style preset picker & banner generator; capture at `/ai-studio/create` |
| **86** | Kuota Kredit & Token AI Studio | AI Studio Visual | `resources/views/app/ai-studio/credit-history.blade.php`| **ACTUAL_UI_SCREENSHOT** | AI token usage audit ledger; capture at `/ai-studio/credit-history` |
| **87** | MCP Server & Klien AI Eksternal| MCP Server | `resources/views/app/settings/integrations/mcp.blade.php`| **SOURCE_BASED_RECONSTRUCTION** | Live SSE needs external Claude/Cursor; UI settings ready at `/settings/mcp` |
| **88** | Paket Berlangganan & Checkout | SaaS Billing | `resources/views/app/billing/checkout.blade.php` | **ACTUAL_UI_SCREENSHOT** | Tier pricing cards & payment checkout; capture at `/billing/checkout` |
| **89** | Pengendalian Kuota & Batas Tier | SaaS Billing | `resources/views/app/billing/limits.blade.php` | **ACTUAL_UI_SCREENSHOT** | Resource usage gauges & upgrade modals; capture at `/billing/limits` |
| **90** | Isolasi Multi-Tenant & RBAC | Multi-Tenant & RBAC | `resources/views/app/security/audit-logs/index.blade.php`| **ACTUAL_UI_SCREENSHOT** | Security audit trail & role management; capture at `/settings/audit-logs` |

---

## 4. Execution Guidelines for Milestone M2 (Visual Capture)

1. **Pre-Capture Database Seeding**:
   - Execute the unified demo seed script:
     ```powershell
     php artisan db:seed --class=DemoBusinessSeeder
     ```
   - This populates `Warung Berkah Nusantara` with comprehensive sample records across all 22 modules (orders, shifts, stock batches, employees, accounts, and invoices).
2. **Automated Viewport Standards**:
   - **Desktop Frames**: Window size `1440x900` or `1600x1000` (macOS Sonoma dark/light theme).
   - **POS Terminal**: Full screen `1920x1080` or iPad Pro aspect ratio `1366x1024`.
   - **Mobile Presensi / Portal**: iPhone 15 Pro frame `393x852`.
3. **Execution Script**:
   - Utilize `scripts/build_ui_renders.py` and `scripts/render_all_m2_views.php` to automate high-resolution screen rendering into `documentation/screenshots/`.
