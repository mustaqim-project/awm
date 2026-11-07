## 2026-11-07T09:38:18Z
You are Worker 3: Documentation Worker.
Your working directory is: c:\laragon\www\cooca_core\.agents\teamwork\worker_m3_docs\

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

MANDATORY INSTRUCTIONS:
1. You MUST read the authoritative request at: c:\laragon\www\cooca_core\.agents\teamwork\ORIGINAL_REQUEST.md
2. You MUST read the project scope document at: c:\laragon\www\cooca_core\.agents\teamwork\orchestrator\PROJECT.md
3. You MUST read the explorer findings at: c:\laragon\www\cooca_core\.agents\teamwork\explorer_audit_docs_survey\handoff.md
4. You MUST follow the domain skill instructions at:
   - C:\Users\Ulfah Ramadhani\.gemini\config\skills\business-owner-doc-generator\SKILL.md
   - C:\Users\Ulfah Ramadhani\.gemini\config\skills\cooca-agent-directive\SKILL.md

EXCLUSIVE FILE OWNERSHIP:
You own and modify ONLY the following files:
- `resources/views/app/docs/index.blade.php`
- `resources/views/app/docs/partials/sec_analytics.blade.php`
- `resources/views/app/docs/partials/sec_approvals.blade.php`
- `resources/views/app/docs/partials/sec_billing.blade.php`

TASK DETAILS:
1. Reference Golden Standard:
   - Inspect `resources/views/app/docs/partials/sec_ai.blade.php` to understand the standard styling, Bento cards, Tailwind classes, typography, badges, and layout conventions used in Cooca docs.
2. Create `resources/views/app/docs/partials/sec_analytics.blade.php`:
   - Header Bento: Title "Modul Analitik & Tren Pertumbuhan", semantic icon `bar-chart-3`, subtitle "Menganalisis performa penjualan, margin kotor, tren jam sibuk, dan kesehatan kas bisnis Anda secara real-time.", and verified interactive action button linking to `route('analytics.index')` with permission guard `@can('reports.view')`.
   - Executive Summary: Problem solved for Business Owner (mencegah kebocoran omzet, memahami HPP riil vs laba kotor, mengatur jadwal shift karyawan saat jam sibuk kasir).
   - 4 Practical Steps: Persiapan filter periode & cabang -> Membaca 4 KPI utama -> Analisis heatmap jam sibuk & tren produk -> Ekspor laporan & evaluasi bisnis mingguan.
   - 20-Industry Application Matrix across 6 Clusters:
     1. Kuliner & F&B: Restoran, Coffee Shop, Bakery, Cloud Kitchen, Katering.
     2. Manufaktur & HPP: Konveksi, Percetakan, Mebel, Bengkel Bubut, Kerajinan.
     3. Ritel & Apotek: Minimarket, Apotek/Toko Obat.
     4. Jasa Harian: Bengkel Kendaraan, Barbershop/Salon, Laundry Kiloan, Cuci Mobil.
     5. Jasa Proyek: Kontraktor, Wedding Organizer, Digital Agency.
     6. Distribusi & Agro: Distributor Grosir B2B & Agrobisnis.
   - Anti-Fraud & Keamanan: Pembatasan akses metrik laba bersih untuk kasir/staf operasional, proteksi data margin.
   - FAQ: 4 pertanyaan umum pemilik usaha.
3. Create `resources/views/app/docs/partials/sec_approvals.blade.php`:
   - Header Bento: Title "Modul Otorisasi & Persetujuan Dokumen (MAR)", semantic icon `check-check`, subtitle "Mencegah fraud transaksi, kebocoran kasbon, dan diskon liar melalui sistem persetujuan berjenjang otomatis.", and direct buttons linking to `route('approvals.inbox')`, `route('approvals.history')`, and `route('approval-rules.index')`.
   - Executive Summary: Maker-Checker workflow, proteksi pengeluaran kas, kontrol diskon kasir, pembelian supplier besar.
   - 4 Practical Steps: Atur aturan plafon nominal per divisi -> Staf mengajukan tiket transaksi -> Notifikasi & review persetujuan berjenjang -> Rekap riwayat audit & arsip approval.
   - 20-Industry Application Matrix across 6 Clusters.
   - Anti-Fraud & Keamanan: Audit trail permanen, alasan penolakan wajib, pencegahan self-approval.
   - FAQ.
4. Create `resources/views/app/docs/partials/sec_billing.blade.php`:
   - Header Bento: Title "Modul Paket Langganan, Kuota & Billing", semantic icon `credit-card`, subtitle "Transparansi paket langganan Cooca, pembayaran instan via QRIS/VA Bank, dan manajemen kuota penyimpanan awan tanpa biaya tersembunyi.", and direct buttons linking to `route('billing.limits')`, `route('billing.checkout')`, and `route('billing.history')`.
   - Executive Summary: 4 Tiers (Free Solo, Standard, Premium, Prestige), aktivasi otomatis via TriPay payment gateway, penambahan kuota penyimpanan gambar/faktur, jaminan buffer darurat saat toko ramai.
   - 4 Practical Steps: Cek penggunaan kuota toko -> Pilih paket langganan sesuai kapasitas usaha -> Bayar otomatis via QRIS / VA Bank -> Cetak bukti invoice resmi untuk pembukuan pajak.
   - 20-Industry Application Matrix across 6 Clusters.
   - Anti-Fraud & Keamanan: Verifikasi webhook signature, enkripsi transaksi, pemisahan hak akses tagihan khusus Owner/Finance.
   - FAQ.
5. Update `resources/views/app/docs/index.blade.php`:
   - Add navigation links for `sec-analytics`, `sec-approvals`, `sec-billing` in Left Sidebar navigation TOC (under appropriate group or with clean badges).
   - Include `@include('app.docs.partials.sec_analytics')`, `@include('app.docs.partials.sec_approvals')`, and `@include('app.docs.partials.sec_billing')` in the document main content container.
6. VERIFICATION:
   - Run `php -l` on all 4 Blade files.
   - Test view compilation:
     `php artisan view:clear`
     `php artisan view:cache`
     `php artisan view:clear`
   - Document all changes, files created, and verification output in `c:\laragon\www\cooca_core\.agents\teamwork\worker_m3_docs\handoff.md`.
   - Send completion message back to orchestrator.
