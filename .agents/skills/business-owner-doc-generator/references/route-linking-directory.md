# DIREKTORI RUTE & TAUTAN NAVIGASI SISTEM COOCA

Gunakan daftar rute resmi ini untuk menyisipkan tombol dan tautan tutorial interaktif ke dalam dokumen panduan pengguna COOCA. Seluruh rute dilindungi oleh izin peran (*Permission*) dan konteks bisnis aktif.

---

## 🤖 1. Ekosistem Kecerdasan Buatan (AI)
| Nama Rute | URL Target | Permission Wajib | Deskripsi Fungsional |
|---|---|---|---|
| `route('cooca-ai.pos')` / `route('pos.ai.index')` | `/cooca-ai/pos` / `/pos/ai` | `ai.access` | Tanya AI POS (Tanya Jawab Bahasa Alami Kasir) |
| `route('cooca-ai.index')` / `route('ai.office')` | `/cooca-ai` / `/ai` | `ai.access` | Lobi Eksekutif Intelijen AI (6 Kantor Spesialis) |
| `route('cooca-ai.actions')` / `route('ai.actions')` | `/cooca-ai/actions` / `/ai/actions` | `ai.access` | Agen Otonom & Eksekusi Otomatis Terjadwal |
| `route('cooca-ai.providers')` / `route('ai.providers')` | `/cooca-ai/providers` / `/ai/providers` | `ai.access` / Owner | Pengaturan Model LLM (Gemini, GPT-4o, Claude, Ollama) |
| `route('cooca-ai.history')` / `route('ai.history')` | `/cooca-ai/history` / `/ai/history` | `ai.access` | Log Riwayat Interaksi & Pertanyaan AI |
| `route('cooca-ai.live-metrics')` | `/cooca-ai/live-metrics` | `ai.access` | Metrik Real-Time Token & Pemakaian AI |

---

## 🛒 2. Kasir POS & Transaksi Harian
| Nama Rute | URL Target | Permission Wajib | Deskripsi Fungsional |
|---|---|---|---|
| `route('pos.terminal')` | `/pos/terminal` | `pos.terminal` | Layar Kasir POS Mesin Sentuh |
| `route('pos.shifts.index')` | `/pos/shifts` | `pos.shifts` | Manajemen Shift Kasir & Modal Laci |
| `route('pos.tables.index')` | `/pos/tables` | `pos.tables` | Denah Meja Restoran / Cafe |
| `route('pos.printers.index')` | `/pos/printers` | `pos.printers` | Konfigurasi Printer Bluetooth/Thermal |
| `route('pos.reports.index')` | `/pos/reports` | `pos.reports` | Laporan Transaksi Kasir Z-Report |

---

## 📦 3. Produk, Bahan Baku & Resep HPP
| Nama Rute | URL Target | Permission Wajib | Deskripsi Fungsional |
|---|---|---|---|
| `route('products.index')` | `/products` | `products.view` | Katalog Produk Jadi / Barang Jual |
| `route('materials.index')` | `/materials` | `materials.view` | Katalog Bahan Baku Mentah |
| `route('products.bom.index')` | `/products/bom` | `products.edit` | Formula Resep BOM Gramasi Makanan/HPP |
| `route('modifiers.index')` | `/modifiers` | `products.edit` | Varian Produk & Add-On Tambahan |
| `route('import.index')` | `/import` | `products.create` | Impor Data Massal Excel / CSV |

---

## 🏬 4. Gudang & Mutasi Stok
| Nama Rute | URL Target | Permission Wajib | Deskripsi Fungsional |
|---|---|---|---|
| `route('inventory.stocks')` | `/inventory/stocks` | `inventory.view` | Pantauan Stok Real-Time Cabang |
| `route('inventory.opname')` | `/inventory/opname` | `inventory.manage` | Stock Opname Fisik & Selisih Stok |
| `route('inventory.transfers')`| `/inventory/transfers` | `inventory.manage` | Transfer Antar-Gudang & Cabang |
| `route('warehouse.index')` | `/warehouse` | `warehouse.manage` | Pengaturan Multi-Gudang & Outlet |

---

## 🤝 5. Pembelian Supplier & Penjualan B2B
| Nama Rute | URL Target | Permission Wajib | Deskripsi Fungsional |
|---|---|---|---|
| `route('suppliers.index')` | `/suppliers` | `procurement.view` | Master Data Supplier & Vendor |
| `route('purchase-orders.index')` | `/purchase-orders` | `procurement.view` | Order Pembelian Bahan Baku (PO) |
| `route('quotations.index')` | `/quotations` | `sales.view` | Surat Penawaran Harga Resmi (B2B) |
| `route('sales-orders.index')` | `/sales-orders` | `sales.view` | Pesanan Penjualan B2B (SO) |
| `route('invoices.index')` | `/invoices` | `invoices.view` | Faktur Tagihan Invoice & Jatuh Tempo |

---

## 💰 6. Keuangan, Kas Bank & Pajak
| Nama Rute | URL Target | Permission Wajib | Deskripsi Fungsional |
|---|---|---|---|
| `route('finance.cash-bank')` | `/finance/cash-bank` | `finance.cash_bank`| Rekening Bank & Kas Operasional |
| `route('finance.expenses')` | `/finance/expenses` | `finance.expenses` | Pencatatan Biaya & Beban Usaha |
| `route('reports.index')` | `/reports` | `reports.view` | Laporan Laba Rugi, Neraca & Penjualan |
| `route('tax.index')` | `/tax` | `tax.view` | Kalkulator Pajak PB1/PPN & PPh21 |

---

## 👥 7. Karyawan, Gaji & CRM Pelanggan
| Nama Rute | URL Target | Permission Wajib | Deskripsi Fungsional |
|---|---|---|---|
| `route('hrm.index')` | `/hrm` | `hrm.view` | Data Karyawan, Shift & Kehadiran |
| `route('hrm.payroll.index')`| `/hrm/payroll` | `hrm.payroll` | Penggajian Otomatis Slip Gaji |
| `route('customers.index')` | `/customers` | `customers.view` | Master Pelanggan & Utang Piutang |
| `route('crm.members.index')`| `/crm/members` | `customers.view` | Member Poin Loyalitas & Diskon |
| `route('whatsapp.index')` | `/whatsapp` | `whatsapp.manage` | Blast Pesan WhatsApp & Gateway |

---

## ⚙️ 8. Pengaturan & Sistem
| Nama Rute | URL Target | Permission Wajib | Deskripsi Fungsional |
|---|---|---|---|
| `route('settings.index')` | `/settings` | `settings.view` | Pusat Pengaturan Profil & Pajak |
| `route('roles.index')` | `/roles` | `roles.view` | Hak Akses Staf & Kasir (RBAC) |
| `route('billing.index')` | `/billing` | Owner Only | Paket Langganan & Kuota Fitur |
| `route('docs.index')` | `/docs` | All Users | Pusat Dokumentasi & Panduan |
