{{--
    COOCA - Apple HIG Spotlight Command Palette Modal (Ctrl+K / Cmd+K)
    Sesuai mandat Bento Apple HIG v2.0 (macOS Sonoma / iOS 18)
    Diekstraksi dari topbar.blade.php untuk efisiensi dan modularitas arsitektur layout.
--}}
<div x-data="{
    isOpen: false,
    spotlightOpen: false,
    query: '',
    category: 'all',
    selectedIndex: 0,
    categories: [
        { id: 'all', label: '{{ __('common.all') }}' },
        { id: 'Dashboard', label: '{{ __('navigation.clusters.daily_ops') }}' },
        @if ($canAccessSales)
            { id: 'Kasir & Penjualan', label: '{{ ($activeBiz && $activeBiz->isFoodIndustry()) ? __('navigation.pos_resto') : __('navigation.pos_terminal') }}' },
        @endif
        @if ($canAccessInventory)
            { id: 'Produk & Stok', label: '{{ __('navigation.clusters.master_data') }}' },
        @endif
        @if ($canAccessPurchasing)
            { id: 'Pembelian & Supplier', label: '{{ __('navigation.suppliers') }}' },
        @endif
        @if ($canAccessMarketing)
            { id: 'Pelanggan & Pemasaran', label: '{{ __('navigation.crm_customers') }}' },
        @endif
        @if ($canAccessFinance)
            { id: 'Keuangan & Biaya', label: '{{ __('navigation.clusters.reports_finance') }}' },
        @endif
        @if ($canAccessReports)
            { id: 'Laporan & Analitik', label: '{{ __('navigation.sales_report') }}' },
        @endif
        @if ($canAccessSettings || $canAccessRoles || $canAccessBilling || \App\Support\Context::isOwner())
            { id: 'Pengaturan Usaha', label: '{{ __('navigation.clusters.settings_hub') }}' },
        @endif
    ],
    items: [
        // 1. Dashboards (Overview)
        @if (\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('dashboard.view'))
        { title: 'Beranda Dashboard', desc: 'Ringkasan performa penjualan, omset, dan laba kotor bisnis', category: 'Dashboard', route: '{{ route('dashboard') }}', icon: 'layout-dashboard', keywords: 'beranda home executive overview penjualan sales omset revenue laba profit summary' },
        @endif
        { title: 'Portal Karyawan & Presensi', desc: 'Jam kerja real-time, cuaca, dan absensi masuk/pulang', category: 'Dashboard', route: '{{ route('portal') }}', icon: 'clock', keywords: 'portal presensi absen attendance jam kerja clock in out kehadiran cuaca staff' },
        @if ($canAccessFinance)
            { title: 'Ringkasan Finansial & Kas', desc: 'Saldo kas, mutasi rekening bank, dan ringkasan arus kas', category: 'Dashboard', route: '{{ route('finance.cash-bank.index') }}', icon: 'wallet', keywords: 'keuangan kas bank cash bank accounting overview saldo balance flow' },
        @endif
        @if ($canAccessPos)
            { title: 'Ringkasan Kasir POS', desc: 'Aktivitas transaksi kasir toko, riwayat shift, dan pesanan kasir', category: 'Dashboard', route: '{{ route('pos.orders.index') }}', icon: 'receipt', keywords: 'kasir pos cashier transaksi shift kas orders meja receipt summary' },
        @endif
        @if ($canAccessB2bSales && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_B2B_SALES) ?? true))
            { title: 'Ringkasan Penjualan B2B', desc: 'Pesanan penjualan B2B, penawaran harga, dan faktur piutang', category: 'Dashboard', route: '{{ route('sales.orders.index') }}', icon: 'shopping-bag', keywords: 'b2b penjualan sales orders so faktur invoice pipeline' },
        @endif
        @if ($canAccessStorefront && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_STOREFRONT_CHECKOUT) ?? true))
            { title: 'Ringkasan Pesanan Toko Online', desc: 'Aktivitas pesanan checkout etalase toko online', category: 'Dashboard', route: '{{ route('storefront.orders.index') }}', icon: 'store', keywords: 'toko online etalase pesanan order web storefront checkout' },
        @endif
        @if ($canAccessFinance || \App\Support\Context::isOwner())
            { title: 'Ringkasan Karyawan & Payroll', desc: 'Daftar pegawai, kasbon, dan ringkasan gaji bulanan', category: 'Dashboard', route: '{{ route('hrm.index') }}', icon: 'users', keywords: 'hrm karyawan pegawai staf staff payroll gaji salary upah wages' },
        @endif
        @if (($canAccessCrm || $canAccessSales) && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CRM_LOYALTY) ?? true))
            { title: 'Ringkasan Pelanggan & Member', desc: 'Statistik basis pelanggan setia, poin loyalitas, dan piutang', category: 'Dashboard', route: '{{ route('crm.members.index') }}', icon: 'award', keywords: 'crm pelanggan member poin loyalty customer diskon discount reward' },
        @endif
        @if ($canAccessInventory && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_INVENTORY_WAREHOUSE) ?? true))
            { title: 'Ringkasan Gudang & Persediaan', desc: 'Monitoring level stok gudang, lokasi penyimpanan, dan stok kritis', category: 'Dashboard', route: '{{ route('warehouse.index') }}', icon: 'warehouse', keywords: 'gudang stok inventory warehouse persediaan stock alert habis' },
        @endif
        @if (\App\Support\Context::isOwner() && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CHANNELS_MARKETING) ?? true))
            { title: 'Ringkasan Pemasaran Digital', desc: 'Jadwal posting media sosial, kalender konten, dan inbox', category: 'Dashboard', route: '{{ route('social-media.index') }}', icon: 'share-2', keywords: 'marketing sosmed instagram tiktok facebook wa broadcast social media' },
        @endif
        { title: 'Asisten Cerdas AI Cooca', desc: 'Konsultasi bisnis, analisis penjualan, dan rekomendasi otomatis', category: 'Dashboard', route: '{{ route('pos.ai.index') }}', icon: 'bot', keywords: 'ai asisten bot analisa cerdas konsultasi pintar assistant intelligence' },

        @if ($canAccessSales)
            // 2. Kasir & Penjualan
            @if ($canAccessPos)
                { title: '{{ __('navigation.pos_open') }}', desc: 'Terminal kasir cepat untuk melayani transaksi kasir harian', category: 'Kasir & Penjualan', route: '{{ route('pos.terminal') }}', icon: 'calculator', keywords: 'kasir pos cashier terminal jualan sales bayar pay struk receipt checkout register' },
                { title: 'Riwayat Transaksi Kasir', desc: 'Daftar struk penjualan kasir POS dan rekap shift kasir', category: 'Kasir & Penjualan', route: '{{ route('pos.orders.index') }}', icon: 'receipt', keywords: 'transaksi transaction kasir cashier struk shift nota rekap history orders' },
                @if (($activeBiz && $activeBiz->isFoodIndustry()) && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_DINEIN) ?? true))
                    { title: 'Layar Dapur (KDS)', desc: 'Tampilan pesanan makanan & minuman langsung untuk staf dapur', category: 'Kasir & Penjualan', route: '{{ route('pos.kitchen.index') }}', icon: 'chef-hat', keywords: 'kitchen dapur kds order masak bar makanan resto food display' },
                    { title: 'Meja & QR Resto', desc: 'Tata kelola denah meja, nomor meja, dan cetak QR ordering', category: 'Kasir & Penjualan', route: '{{ route('pos.tables.index') }}', icon: 'layout-grid', keywords: 'meja table qr resto cafe dine in pesan self order' },
                @endif
            @endif
            @if ($canAccessB2bSales && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_B2B_SALES) ?? true))
                { title: 'Pesanan Penjualan (Sales Orders)', desc: 'Daftar pesanan penjualan produk ke pelanggan atau klien', category: 'Kasir & Penjualan', route: '{{ route('sales.orders.index') }}', icon: 'shopping-bag', keywords: 'pesanan penjualan so sales order b2b klien customer order' },
                { title: 'Surat Penawaran (Quotations)', desc: 'Buat dan kelola surat penawaran harga resmi untuk pelanggan', category: 'Kasir & Penjualan', route: '{{ route('sales.quotations.index') }}', icon: 'file-text', keywords: 'penawaran quotation harga proposal quote pricing' },
                { title: 'Faktur Penjualan (Invoices)', desc: 'Tagihan faktur resmi, status tempo, dan penerimaan pelunasan piutang', category: 'Kasir & Penjualan', route: '{{ route('invoices.index') }}', icon: 'file-check', keywords: 'faktur invoice piutang tagihan bayar cicil tempo due date' },
                { title: 'Retur Penjualan', desc: 'Pencatatan pengembalian barang atau komplain retur pelanggan', category: 'Kasir & Penjualan', route: '{{ route('sales.returns.index') }}', icon: 'rotate-ccw', keywords: 'retur penjualan sales return refund pengembalian komplain ganti' },
            @endif
        @endif

        @if ($canAccessInventory)
            // 3. Produk & Persediaan
            { title: 'Katalog Produk & Menu', desc: 'Daftar barang dagangan, harga jual, dan resep produk', category: 'Produk & Stok', route: '{{ route('products.index') }}', icon: 'package', keywords: 'produk product barang menu katalog catalog makanan minuman sku items' },
            { title: 'Integrasi Marketplace', desc: 'Hub multi-channel Shopee, TikTok Shop, dan Tokopedia dengan sinkronisasi harga & stok', category: 'Produk & Stok', route: '{{ route('marketplace-hub.index') }}', icon: 'shopping-bag', keywords: 'marketplace shopee tiktok shop tokopedia integrasi channel online pesanan sync' },
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_SERVICE_WORKSHOP) ?? true)
                { title: '{{ __('navigation.services') }}', desc: 'Katalog layanan, jasa pengerjaan, servis teknis, dan tarif per jam', category: 'Produk & Stok', route: '{{ route('services.index') }}', icon: '{{ ($activeBiz && $activeBiz->isWorkshop()) ? 'wrench' : 'briefcase' }}', keywords: 'jasa service layanan servis technical tarif ongkos kerja hourly spk bengkel workshop pkb task' },
            @endif
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true)
                { title: 'Bahan Baku & Resep (BOM)', desc: 'Master bahan mentah, komponen produksi, dan kartu resep', category: 'Produk & Stok', route: '{{ route('materials.index') }}', icon: 'boxes', keywords: 'bahan baku raw materials resep bom formula bumbu recipe ingredients' },
            @endif
            { title: 'Varian & Opsi Tambahan', desc: 'Topping, level pedas, ukuran cup, dan modifikasi pesanan', category: 'Produk & Stok', route: '{{ route('pos.modifiers.index') }}', icon: 'layers', keywords: 'varian modifier topping opsi pilihan ekstra add on variants' },
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_INVENTORY_WAREHOUSE) ?? true)
                { title: 'Stok Gudang & Saldo', desc: 'Informasi sisa fisik stok produk dan bahan baku di semua gudang', category: 'Produk & Stok', route: '{{ route('inventory.stocks') }}', icon: 'archive', keywords: 'stok gudang stock balance saldo inventory fisik sisa kuantitas' },
                { title: 'Lokasi Gudang', desc: 'Pengaturan multi-lokasi gudang, toko, outlet, dan cabang', category: 'Produk & Stok', route: '{{ route('warehouse.index') }}', icon: 'warehouse', keywords: 'lokasi gudang warehouse storage cabang outlet toko simpan location' },
                { title: 'Opname Stok Fisik', desc: 'Penyesuaian stok berkala dan rekonsiliasi selisih fisik gudang', category: 'Produk & Stok', route: '{{ route('inventory.opnames.index') }}', icon: 'clipboard-check', keywords: 'opname stock opname fisik cek selisih audit gudang cocok count' },
                { title: 'Transfer Stok Gudang', desc: 'Surat jalan perpindahan barang antar cabang atau lokasi gudang', category: 'Produk & Stok', route: '{{ route('inventory.transfers.index') }}', icon: 'arrow-left-right', keywords: 'transfer mutasi stock transfer antar gudang cabang kirim jalan' },
            @endif
            { title: 'Kategori Produk', desc: 'Pengelompokan jenis dan kategori barang jualan', category: 'Produk & Stok', route: '{{ route('product-categories.index') }}', icon: 'folder-tree', keywords: 'kategori produk product category kelompok barang jenis' },
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true)
            { title: 'Kategori Bahan Baku', desc: 'Pengelompokan jenis bahan mentah dan material produksi', category: 'Produk & Stok', route: '{{ route('material-categories.index') }}', icon: 'folder', keywords: 'kategori bahan material categories mentah produksi' },
            @endif
            { title: 'Satuan Ukur & Konversi', desc: 'Master unit satuan (kg, gram, pcs, liter) dan konversi rasio', category: 'Produk & Stok', route: '{{ route('units.index') }}', icon: 'scale', keywords: 'satuan ukur unit conversion kg gram liter pcs dus uom' },
            { title: 'Impor & Ekspor Excel', desc: 'Impor massal produk, bahan baku, resep, dan stok via Excel', category: 'Produk & Stok', route: '{{ route('import.index') }}', icon: 'file-spreadsheet', keywords: 'import ekspor excel csv download template massal bulk' },
        @endif

        @if ($canAccessPurchasing && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_PROCUREMENT) ?? true))
            // 4. Pembelian & Supplier
            { title: 'Pesanan Pembelian (PO)', desc: 'Surat pesanan pembelian pengadaan barang ke supplier pemasok', category: 'Pembelian & Supplier', route: '{{ route('purchase-orders.index') }}', icon: 'truck', keywords: 'po purchase order beli purchasing supplier pemasok pengadaan procurement' },
            { title: 'Tagihan Supplier (Bills)', desc: 'Daftar kewajiban utang dagang pembelian dari pemasok', category: 'Pembelian & Supplier', route: '{{ route('purchasing.bills.index') }}', icon: 'receipt', keywords: 'tagihan bill supplier bill utang supplier invoice pembelian tempo ap' },
            { title: 'Supplier & Pemasok', desc: 'Direktori kontak supplier, alamat, dan syarat pembayaran', category: 'Pembelian & Supplier', route: '{{ route('suppliers.index') }}', icon: 'building-2', keywords: 'supplier vendor pemasok distributor rekanan kulakan contacts' },
            { title: 'Retur Pembelian', desc: 'Pengembalian barang rusak atau cacat ke pihak supplier', category: 'Pembelian & Supplier', route: '{{ route('purchase.returns.index') }}', icon: 'rotate-ccw', keywords: 'retur beli purchase return supplier rusak kembalikan dana potong utang' },
        @endif

        @if ($canAccessMarketing)
            // 5. Pelanggan & Pemasaran
            @if ($canAccessCrm && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CRM_LOYALTY) ?? true))
                { title: 'Data Pelanggan (CRM)', desc: 'Buku kontak pelanggan, nomor WhatsApp, riwayat transaksi', category: 'Pelanggan & Pemasaran', route: '{{ route('customers.index') }}', icon: 'users', keywords: 'pelanggan customer contacts kontak wa crm pembeli langganan' },
                { title: 'Member & Loyalitas Poin', desc: 'Program keanggotaan member, perolehan poin, dan reward loyalitas', category: 'Pelanggan & Pemasaran', route: '{{ route('crm.members.index') }}', icon: 'award', keywords: 'member loyalitas poin loyalty reward kupon program tier' },
                { title: 'Voucher Diskon Promosi', desc: 'Kode kupon promosi, diskon persentase, dan voucher potongan harga', category: 'Pelanggan & Pemasaran', route: '{{ route('crm.vouchers.index') }}', icon: 'ticket', keywords: 'voucher diskon promo discount kupon coupon potongan harga promo' },
            @endif
            @if ($canAccessStorefront && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_STOREFRONT_CHECKOUT) ?? true))
                { title: 'Pesanan Toko Online', desc: 'Daftar pesanan masuk dari toko online dan status pembayaran', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.orders.index') }}', icon: 'shopping-bag', keywords: 'toko online etalase katalog web storefront checkout online orders' },
                { title: 'Desain Halaman Toko (Mini-Site)', desc: 'Kustomisasi tema, banner, warna, dan tampilan web toko online', category: 'Pelanggan & Pemasaran', route: '{{ route('landing-page.edit') }}', icon: 'globe', keywords: 'landing page website desain tema etalase toko online mini site web builder' },
                @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RESERVATION) ?? true)
                    { title: 'Reservasi & Booking Online', desc: 'Kelola pesanan reservasi meja atau booking layanan pelanggan', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.reservations.index') }}', icon: 'calendar-check', keywords: 'reservasi booking meja reservation jadwal reservasi janji temu' },
                @endif
                @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_MERCHANT_SHIPPING) ?? true)
                    { title: 'Pengaturan Ongkos Kirim', desc: 'Aturan ongkir kurir toko dan integrasi ekspedisi logistik pengiriman', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.shipping.index') }}', icon: 'truck', keywords: 'ongkir shipping rate pengiriman tarif kurir logistik biteship' },
                @endif
                { title: 'Pengaturan Toko Online & Pembayaran', desc: 'Aturan checkout, metode pembayaran transfer/QRIS, dan jam buka etalase', category: 'Pelanggan & Pemasaran', route: '{{ route('storefront.settings.index') }}', icon: 'store', keywords: 'toko online etalase qris transfer manual rekening checkout pengaturan jam buka' },
            @endif
            @if ($canAccessChannels && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CHANNELS_MARKETING) ?? true))
                { title: 'WhatsApp Bisnis Toko', desc: 'Koneksi perangkat WhatsApp dan pengiriman struk belanja otomatis', category: 'Pelanggan & Pemasaran', route: '{{ route('whatsapp.index') }}', icon: 'message-square', keywords: 'whatsapp wa koneksi qr barcode struk auto sender messaging' },
                { title: 'Pesan Siaran WhatsApp (Broadcast)', desc: 'Kirim pesan siaran promo massal langsung ke kontak pelanggan', category: 'Pelanggan & Pemasaran', route: '{{ route('whatsapp.broadcast.index') }}', icon: 'radio', keywords: 'broadcast siaran wa blast massal pesan promosi broadcast' },
                { title: 'Media Sosial Omnichannel', desc: 'Integrasi dan jadwal posting ke Instagram, Facebook, dan TikTok', category: 'Pelanggan & Pemasaran', route: '{{ route('social-media.index') }}', icon: 'share-2', keywords: 'sosmed media sosial instagram tiktok facebook threads posting schedule' },
            @endif
        @endif

        @if ($canAccessFinance)
            // 6. Keuangan & Biaya
            { title: 'Kas & Rekening Bank', desc: 'Pencatatan mutasi kas masuk, kas keluar, dan saldo bank', category: 'Keuangan & Biaya', route: '{{ route('finance.cash-bank.index') }}', icon: 'wallet', keywords: 'kas bank mutasi uang cash flow rekening tunai masuk keluar balance' },
            { title: 'Pengeluaran Operasional', desc: 'Beban biaya operasional toko, listrik, air, sewa, dan konsumsi', category: 'Keuangan & Biaya', route: '{{ route('finance.expenses.index') }}', icon: 'banknote', keywords: 'pengeluaran expense operational biaya listrik bon sewa gaji opex' },
            { title: 'Daftar Piutang Usaha', desc: 'Daftar tagihan yang belum dibayar oleh pelanggan atau rekanan', category: 'Keuangan & Biaya', route: '{{ route('finance.receivables') }}', icon: 'trending-up', keywords: 'piutang ar accounts receivable customer belum bayar tempo tagihan invoice' },
            { title: 'Daftar Utang Usaha', desc: 'Daftar kewajiban utang jatuh tempo kepada pihak pemasok', category: 'Keuangan & Biaya', route: '{{ route('finance.payables') }}', icon: 'trending-down', keywords: 'utang ap accounts payable supplier jatuh tempo bayar kewajiban bills' },
            { title: 'Pencairan Dana Penjualan (Settlement)', desc: 'Rekonsiliasi pencairan saldo gateway pembayaran ke rekening bank', category: 'Keuangan & Biaya', route: '{{ route('finance.settlements.index') }}', icon: 'landmark', keywords: 'settlement pencairan dana qris gateway tripay tarik saldo payout' },

            @php
                $topbarShowCorporateAccounting = $activeBiz ? $activeBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_ACCOUNTING_CORPORATE) : true;
            @endphp
            @if ($topbarShowCorporateAccounting)
                { title: 'Bagan Akun (Chart of Accounts)', desc: 'Daftar struktur akun buku besar hierarki multi-tier standar akuntansi', category: 'Keuangan & Biaya', route: '{{ route('finance.coa.index') }}', icon: 'list-tree', keywords: 'coa bagan akun chart of accounts perkiraan kode akun hierarki aktiva kewajiban ekuitas' },
                { title: 'Buku Jurnal Keuangan', desc: 'Jurnal umum debit-kredit otomatis dari seluruh transaksi usaha', category: 'Keuangan & Biaya', route: '{{ route('finance.journals.index') }}', icon: 'file-text', keywords: 'jurnal journal entry akuntansi debit kredit transaksi pembukuan balance' },
                { title: 'Buku Besar Umum (General Ledger)', desc: 'Rincian mutasi kronologis debit-kredit akun dengan audit trail lengkap', category: 'Keuangan & Biaya', route: '{{ route('finance.general-ledger') }}', icon: 'book-open', keywords: 'buku besar general ledger gl akun mutasi rincian saldo running' },
                { title: 'Rekonsiliasi Bank', desc: 'Pencocokan rekening koran bank dengan catatan kas & bank sistem', category: 'Keuangan & Biaya', route: '{{ route('finance.reconciliations.index') }}', icon: 'git-compare', keywords: 'rekonsiliasi bank bank reconciliation rekening koran statement matching' },
            @endif

            { title: 'Hitung HPP & Margin Produk', desc: 'Kalkulator biaya pokok produksi akurat berdasarkan formula ABC', category: 'Keuangan & Biaya', route: '{{ route('calculator.index') }}', icon: 'calculator', keywords: 'hpp cogs cost hitung biaya pokok margin harga modal abc costing' },
            { title: 'Simulator Harga Jual', desc: 'Simulasi target margin laba dan analisis sensitivitas harga', category: 'Keuangan & Biaya', route: '{{ route('simulator.index') }}', icon: 'gauge', keywords: 'simulator harga jual margin simulasi skenario what if price simulator' },
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_LABOR_MACHINES) ?? true)
                { title: 'Biaya Mesin & Tenaga Kerja', desc: 'Pengaturan tarif kerja per jam dan biaya operasional mesin', category: 'Keuangan & Biaya', route: '{{ route('labor-machines.index') }}', icon: 'cog', keywords: 'tarif mesin machine tenaga kerja labor upah listrik jam kerja overhead' },
            @endif
            { title: 'Data Karyawan & Slip Gaji', desc: 'Penggajian bulanan staf, kasbon pinjaman, dan cetak slip gaji', category: 'Keuangan & Biaya', route: '{{ route('hrm.index') }}', icon: 'badge-cent', keywords: 'gaji payroll slip gaji payslip karyawan upah hrm kasbon pinjaman' },
            { title: 'Perhitungan Pajak Karyawan', desc: 'Perhitungan tarif efektif rata-rata (TER) PPh 21 gaji karyawan', category: 'Keuangan & Biaya', route: '{{ route('tax.index') }}', icon: 'scale', keywords: 'pajak tax pph 21 ter karyawan gaji payroll tax potongan spt' },
        @endif

        @if ($canAccessReports)
            // 7. Laporan & Analitik
            { title: 'Pusat Laporan Bisnis', desc: 'Suite analitik dan ringkasan eksekutif seluruh laporan usaha', category: 'Laporan & Analitik', route: '{{ route('reports.index') }}', icon: 'bar-chart-3', keywords: 'laporan reports pusat report analitik analytics suite bisnis lengkap summary' },
            @if ($topbarShowCorporateAccounting ?? true)
                { title: 'Neraca Posisi Keuangan (Balance Sheet)', desc: 'Laporan posisi keuangan aset, liabilitas, dan ekuitas perusahaan', category: 'Laporan & Analitik', route: '{{ route('finance.balance-sheet') }}', icon: 'scale', keywords: 'neraca balance sheet posisi keuangan aset pasiva modal sak emkm ekuitas assets liabilities' },
                { title: 'Neraca Saldo (Trial Balance)', desc: 'Daftar saldo debit dan kredit seluruh akun buku besar periode berjalan', category: 'Laporan & Analitik', route: '{{ route('finance.trial-balance') }}', icon: 'table-properties', keywords: 'neraca saldo trial balance debit kredit pembukuan penutupan saldo awal mutasi' },
            @endif
            { title: 'Laporan Laba Rugi (Profit & Loss)', desc: 'Laporan pendapatan bersih, total beban, dan laba/rugi usaha', category: 'Laporan & Analitik', route: '{{ route('reports.index', ['tab' => 'income_statement']) }}', icon: 'pie-chart', keywords: 'laba rugi income statement profit loss net profit omset beban pnl' },
            { title: 'Laporan Arus Kas (Cash Flow)', desc: 'Laporan pergerakan kas dari operasional, investasi, dan pendanaan', category: 'Laporan & Analitik', route: '{{ route('reports.index', ['tab' => 'cash_flow']) }}', icon: 'line-chart', keywords: 'arus kas cash flow kas masuk kas keluar net cash aliran dana statement' },
            @if ($canAccessPos)
                { title: 'Laporan Penjualan Kasir POS', desc: 'Rincian omset kasir, metode pembayaran, kasir bertugas, dan item terlaris', category: 'Laporan & Analitik', route: '{{ route('pos.reports.index') }}', icon: 'receipt', keywords: 'laporan kasir pos sales report penjualan omset struk rekap kasir best seller' },
            @endif
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_INVENTORY_WAREHOUSE) ?? true)
                { title: 'Valuasi & Perputaran Stok', desc: 'Nilai aset persediaan gudang dan rasio perputaran barang', category: 'Laporan & Analitik', route: '{{ route('reports.index', ['tab' => 'stock']) }}', icon: 'boxes', keywords: 'valuasi inventory valuation persediaan nilai stok aset turnover gudang perputaran' },
                { title: 'Kartu Mutasi Stok Gudang', desc: 'Riwayat kronologis masuk, keluar, dan penyesuaian stok produk', category: 'Laporan & Analitik', route: '{{ route('inventory.movements') }}', icon: 'activity', keywords: 'mutasi stok stock card movement kartu stok log masuk keluar pergerakan kartu' },
            @endif
            { title: 'Analisis Margin & Titik Impas (BEP)', desc: 'Perhitungan Break Even Point unit & rupiah dan analisis profitabilitas', category: 'Laporan & Analitik', route: '{{ route('profitability.index') }}', icon: 'target', keywords: 'bep break even point titik impas margin analisa profit balik modal margin analysis' },
            { title: 'Kalkulasi & Kepatuhan Pajak', desc: 'Simulasi PPh Final UMKM 0.5%, PPh 21 TER karyawan, dan PPN', category: 'Laporan & Analitik', route: '{{ route('tax.index') }}', icon: 'scale', keywords: 'pajak tax compliance pph final umkm pph 21 ter ppn setoran pajak vat' },
            @if ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CHANNELS_MARKETING) ?? true)
                { title: 'Analitik Media Sosial', desc: 'Wawasan jangkauan, impresi, dan engagement konten media sosial', category: 'Laporan & Analitik', route: '{{ route('social-media.insights.index') }}', icon: 'trending-up', keywords: 'analitik medsos social media insights jangkauan impresi engagement statistik' },
            @endif
        @endif

        @if ($canAccessSettings || $canAccessRoles || $canAccessBilling || \App\Support\Context::isOwner())
            // 8. Pengaturan Usaha
            { title: 'Profil Pengguna', desc: 'Informasi akun pengguna, ganti password, dan kontak WhatsApp', category: 'Pengaturan Usaha', route: '{{ route('profile.edit') }}', icon: 'user', keywords: 'profil user profile akun password kontak hp whatsapp sandi account' },
            @if ($canAccessSettings)
                { title: 'Pengaturan Usaha & Cabang', desc: 'Identitas bisnis, logo usaha, mata uang, dan alamat toko', category: 'Pengaturan Usaha', route: '{{ route('settings.index') }}', icon: 'settings', keywords: 'pengaturan business settings usaha bisnis cabang toko logo nama alamat mata uang' },
            @endif
            @if ($canAccessRoles)
                { title: 'Hak Akses & Peran Staf (RBAC)', desc: 'Kelola peran kasir, admin gudang, finance, dan hak akses staf', category: 'Pengaturan Usaha', route: '{{ route('roles.index') }}', icon: 'shield-check', keywords: 'peran role permissions hak akses staf kasir rbac wewenang access control' },
            @endif
            @if (\App\Support\Context::hasPermission('approvals.view') || \App\Support\Context::isOwner())
                { title: 'Persetujuan Dokumen (MAR)', desc: 'Pusat otorisasi bertingkat Maker-Approver-Releaser dan tiket pengadaan', category: 'Pengaturan Usaha', route: '{{ route('approvals.inbox') }}', icon: 'stamp', keywords: 'approval persetujuan mar otorisasi po expense biaya maker approver releaser authorization' },
            @endif
            @if (\App\Support\Context::hasPermission('audit_logs.view') || \App\Support\Context::isOwner() || $canAccessSettings)
                { title: 'Jejak Audit & Anti-Fraud', desc: 'Penjelajah log forensik mutasi data sensitif, visual diff, dan deteksi kecurangan', category: 'Pengaturan Usaha', route: '{{ route('settings.audit-logs.index') }}', icon: 'shield-alert', keywords: 'audit log jejak forensik diff riwayat void fraud anti fraud keamanan ip log trail security' },
            @endif
            @if ($canAccessBilling)
                { title: 'Paket Berlangganan & Kuota', desc: 'Status paket langganan aktif, batas pemakaian fitur, dan upgrade', category: 'Pengaturan Usaha', route: '{{ route('billing.limits') }}', icon: 'credit-card', keywords: 'paket langganan subscription kuota billing limit quota upgrade tagihan perpanjang' },
            @endif
            { title: 'Buku Panduan & Dokumentasi', desc: 'Panduan lengkap cara penggunaan seluruh fitur ERP, Kasir POS, dan modul Cooca', category: 'Pengaturan Usaha', route: '{{ route('docs.index') }}', icon: 'book-open', keywords: 'panduan dokumentasi docs guide manual tutorial cara pakai buku petunjuk kasir pos stok akuntansi' },
            { title: 'Bantuan & Kontak Dukungan', desc: 'Layanan bantuan teknis dan laporan kendala penggunaan sistem', category: 'Pengaturan Usaha', route: '{{ route('feedback.bugs.index') }}', icon: 'help-circle', keywords: 'bantuan support help tiket cs panduan kendala error kontak bug tickets' },
            { title: 'Komunitas & Event Bisnis', desc: 'Wadah berbagi insight bisnis, pendaftaran event, dan presensi jejaring UMKM', category: 'Pengaturan Usaha', route: '{{ route('community.index') }}', icon: 'users', keywords: 'komunitas community owner event seminar workshop bazaar forum diskusi tips umkm kolaborasi jejaring bisnis' },
        @endif
    ],
    get filteredItems() {
        let q = this.query.trim().toLowerCase();
        let cat = this.category;
        return this.items.filter(item => {
            let matchCat = (cat === 'all') || (item.category === cat);
            if (!matchCat) return false;
            if (!q) return true;
            return item.title.toLowerCase().includes(q) ||
                   item.desc.toLowerCase().includes(q) ||
                   (item.keywords && item.keywords.toLowerCase().includes(q));
        });
    },
    open() {
        this.isOpen = true;
        this.spotlightOpen = true;
        this.query = '';
        this.category = 'all';
        this.selectedIndex = 0;
        document.body.classList.add('overflow-hidden');
        this.$nextTick(() => {
            this.$refs.searchInput?.focus();
            if (window.createCoocaIcons) window.createCoocaIcons();
            else if (window.lucide) window.lucide.createIcons();
        });
    },
    close() {
        this.isOpen = false;
        this.spotlightOpen = false;
        document.body.classList.remove('overflow-hidden');
    },
    selectNext() {
        if (this.filteredItems.length > 0) {
            this.selectedIndex = (this.selectedIndex + 1) % this.filteredItems.length;
            this.scrollToSelected();
        }
    },
    selectPrev() {
        if (this.filteredItems.length > 0) {
            this.selectedIndex = (this.selectedIndex - 1 + this.filteredItems.length) % this.filteredItems.length;
            this.scrollToSelected();
        }
    },
    executeSelected() {
        if (this.filteredItems.length > 0 && this.filteredItems[this.selectedIndex]) {
            window.location.href = this.filteredItems[this.selectedIndex].route;
        }
    },
    scrollToSelected() {
        this.$nextTick(() => {
            const el = document.getElementById('spotlight-item-' + this.selectedIndex);
            if (el) el.scrollIntoView({ block: 'nearest' });
        });
    }
}"
id="spotlight-modal-root"
role="dialog"
aria-modal="true"
aria-label="{{ __('navigation.search_placeholder') }}"
@keydown.window.prevent.cmd.k="open()"
@keydown.window.prevent.ctrl.k="open()"
@open-spotlight.window="open()">

    <!-- Backdrop Modal (Frosted Glass Apple Blur) -->
    <div x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex items-start justify-center pt-12 sm:pt-20 px-3 sm:px-4"
        style="display: none;">

        <!-- Modal Dialog Box (Apple HIG Bento Architecture) -->
        <div @click.stop
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150 transform"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
            class="w-full max-w-2xl bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-[20px] shadow-[0_24px_64px_rgba(0,0,0,0.3)] border border-black/10 dark:border-white/15 overflow-hidden flex flex-col max-h-[82vh] transition-all">

            <!-- Search Input Header Row -->
            <div class="p-3.5 sm:p-4 border-b border-black/5 dark:border-white/10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input x-ref="searchInput"
                    x-model="query"
                    @input="selectedIndex = 0; $nextTick(() => { if (window.createCoocaIcons) window.createCoocaIcons(); else if (window.lucide) window.lucide.createIcons(); })"
                    @keydown.arrow-down.prevent="selectNext()"
                    @keydown.arrow-up.prevent="selectPrev()"
                    @keydown.enter.prevent="executeSelected()"
                    @keydown.esc="close()"
                    type="text"
                    placeholder="{{ __('navigation.search_placeholder') }}"
                    class="flex-1 bg-transparent border-none text-base sm:text-[15px] font-medium text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-none focus:ring-0">
                <button type="button" @click="close()"
                    class="p-1.5 rounded-[7px] text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition-all cursor-pointer"
                    title="{{ __('common.close') }} (Esc)">
                    <kbd class="px-1.5 py-0.5 rounded-[5px] bg-black/[0.06] dark:bg-white/[0.1] text-[10px] font-semibold text-black/60 dark:text-white/60 font-mono tracking-tight shadow-sm">Esc</kbd>
                </button>
            </div>

            <!-- Category Filter Chips Bar -->
            <div class="px-3.5 sm:px-4 py-2 border-b border-black/5 dark:border-white/10 flex items-center gap-1.5 overflow-x-auto no-scrollbar scroll-smooth">
                <template x-for="cat in categories" :key="cat.id">
                    <button type="button"
                        @click="category = cat.id; selectedIndex = 0; $nextTick(() => { if (window.lucide) window.lucide.createIcons(); })"
                        :class="category === cat.id ? 'bg-[#007AFF] text-white shadow-sm font-semibold' : 'bg-black/[0.04] dark:bg-white/[0.06] text-black/65 dark:text-white/65 hover:bg-black/[0.08] dark:hover:bg-white/[0.1]'"
                        class="px-2.5 py-1 rounded-[7px] text-[11px] whitespace-nowrap transition-all shrink-0 cursor-pointer">
                        <span x-text="cat.label"></span>
                    </button>
                </template>
            </div>

            <!-- Results Scrollable List Area -->
            <div class="flex-1 overflow-y-auto p-2 sm:p-3 space-y-1 min-h-[160px] max-h-[50vh] overscroll-contain">
                <!-- Result Item Loop -->
                <template x-for="(item, idx) in filteredItems" :key="item.route">
                    <a :href="item.route"
                        :id="'spotlight-item-' + idx"
                        @mouseenter="selectedIndex = idx"
                        :class="selectedIndex === idx ? 'bg-[#007AFF] text-white shadow-[0_1px_3px_rgba(0,122,255,0.35)]' : 'text-black/80 dark:text-white/80 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]'"
                        class="flex items-center justify-between p-2.5 rounded-[12px] transition-all cursor-pointer group">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div :class="selectedIndex === idx ? 'bg-white/20 text-white' : 'bg-black/[0.04] dark:bg-white/[0.08] text-[#007AFF] dark:text-[#0A84FF]'"
                                class="w-8 h-8 rounded-[8px] flex items-center justify-center shrink-0 transition-colors">
                                <i :data-lucide="item.icon" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[13px] font-semibold truncate leading-tight" x-text="item.title"></span>
                                    <span :class="selectedIndex === idx ? 'bg-white/20 text-white' : 'bg-black/[0.05] dark:bg-white/[0.1] text-black/55 dark:text-white/55'"
                                        class="px-1.5 py-0.5 rounded-[5px] text-[10px] font-medium tracking-wide"
                                        x-text="item.category"></span>
                                </div>
                                <p :class="selectedIndex === idx ? 'text-white/80' : 'text-black/50 dark:text-white/50'"
                                    class="text-[11px] truncate leading-tight mt-0.5"
                                    x-text="item.desc"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 pl-2">
                            <span :class="selectedIndex === idx ? 'opacity-100 text-white' : 'opacity-0'"
                                class="text-[11px] font-medium transition-opacity flex items-center gap-1">
                                <span>{{ __('navigation.spotlight_open') }}</span>
                                <kbd class="px-1.5 py-0.5 rounded bg-white/25 text-[10px] font-mono">↵</kbd>
                            </span>
                        </div>
                    </a>
                </template>

                <!-- Empty State (No Results Found) -->
                <div x-show="filteredItems.length === 0"
                    class="py-10 px-4 text-center space-y-2">
                    <div class="w-12 h-12 mx-auto rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/40 dark:text-white/40">
                        <i data-lucide="search-x" class="w-6 h-6"></i>
                    </div>
                    <div class="text-[14px] font-semibold text-black dark:text-white">{{ __('navigation.search_no_results') }}</div>
                    <p class="text-[12px] text-black/50 dark:text-white/50 max-w-sm mx-auto">
                        <span x-text="'&quot;' + query + '&quot;'"></span>
                    </p>
                </div>
            </div>

            <!-- Footer Keyboard Navigation Helper Bar -->
            <div class="p-2.5 sm:px-4 bg-black/[0.02] dark:bg-white/[0.02] border-t border-black/5 dark:border-white/10 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50 select-none">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">↑</kbd>
                        <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">↓</kbd>
                        <span>{{ __('navigation.spotlight_navigate') }}</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">↵</kbd>
                        <span>{{ __('navigation.spotlight_open') }}</span>
                    </span>
                </div>
                <div class="flex items-center gap-1">
                    <kbd class="px-1 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.1] font-mono text-[10px]">Esc</kbd>
                    <span>{{ __('navigation.spotlight_close') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
