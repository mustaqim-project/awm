<!-- ===================================================== -->
<!-- SECTION: MODUL INVENTORI, GUDANG, OPNAME & RESEP HPP  -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-inventory" class="scroll-mt-24 space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'inventory') && matchesSearch('inventori inventory stok stock barang bahan baku gudang multi-gudang opname selisih susut transfer mutasi kartu stok hpp fifo average supplier kds resep bom blind count')">

    <!-- Backward-compatibility anchor for quick links -->
    <div id="sec-warehouse-inventory-hub" class="hidden"></div>

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL INVENTORY -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-500 to-amber-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(245,158,11,0.35)] shrink-0">
                <i data-lucide="package" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Inventori, Multi-Gudang &amp; Kalkulasi HPP Resep (Inventory Hub)</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20">Zero Stock Leaks</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">HPP Akurat Real-Time</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">20 Sektor UMKM</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Kendali persediaan tanpa bocor: pantau mutasi fisik multi-gudang, otomatisasi potong bahan berbasis resep (BOM), eksekusi stock opname digital blind count, dan proteksi otorisasi selisih stok dengan PIN Supervisor.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('inventory.view'))
                <a href="{{ route('inventory.stocks') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                    <span>Katalog Stok</span>
                </a>
                <a href="{{ route('inventory.movements') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400"></i>
                    <span>Mutasi Stok</span>
                </a>
                <a href="{{ Route::has('inventory.opnames.index') ? route('inventory.opnames.index') : (Route::has('inventory.opname') ? route('inventory.opname') : route('inventory.stocks')) }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400"></i>
                    <span>Stock Opname</span>
                </a>
                <a href="{{ Route::has('inventory.transfers.index') ? route('inventory.transfers.index') : (Route::has('inventory.transfers') ? route('inventory.transfers') : route('inventory.stocks')) }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="truck" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Transfer Cabang</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Kunci Rapat Celah Kebocoran Stok: Transparansi Total Bahan Baku &amp; HPP yang Selalu Akurat</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Kebocoran persediaan adalah pembunuh tersembunyi profit bisnis UMKM: <strong>daging steak atau bumbu yang basi di sudut freezer karena lupa diputar, barang yang raib saat pergantian shift kasir, bahan terbuang tanpa dicatat, hingga harga pokok yang keliru dihitung sehingga pemilik merasa jualan untung padahal modal tergerus</strong>. Modul Inventori Cooca mengunci seluruh celah tersebut dengan sistem kartu stok mutasi kronologis, metode stock opname blind count tanpa celah rekayasa, dan perhitungan HPP otomatis berbasis pergerakan harga beli supplier.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Keamanan Persediaan COOCA bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="warehouse" class="w-4 h-4"></i>
                    <span>1. Multi-Gudang Terpisah</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pisahkan stok fisik antara Gudang Sentral, Dapur Pengolahan, Display Rak Kasir, dan Truk Armada. Anda selalu tahu di mana posisi setiap unit barang berada.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    <span>2. Potong Resep (BOM) &amp; HPP</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap menu yang terjual di kasir otomatis memotong stok bahan baku penyusunnya secara gramasi. HPP dihitung real-time menggunakan metode Moving Average / FIFO.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-blue-500/[0.06] dark:bg-blue-500/[0.1] border border-blue-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-blue-700 dark:text-blue-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="eye-off" class="w-4 h-4"></i>
                    <span>3. Opname Blind Count</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Staf lapangan tidak diperlihatkan angka saldo sistem saat menghitung stok fisik, mengeliminasi kecurangan staf menyamakan angka tanpa mengecek barang sebenarnya.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-rose-500/[0.06] dark:bg-rose-500/[0.1] border border-rose-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="key-round" class="w-4 h-4"></i>
                    <span>4. PIN Otorisasi Selisih</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Penyesuaian stok yang bernilai di atas Rp 100.000 atau selisih kuantiti besar wajib memasukkan PIN Supervisor/Owner dan otomatis tercatat di audit anti-fraud.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA LANGKAH DEMI LANGKAH (4 LANGKAH SOP INVENTORI) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-black/60 dark:text-white/60 flex items-center gap-2">
                <i data-lucide="git-commit" class="w-4 h-4 text-[#007AFF]"></i>
                <span>SOP Praktis Pengelolaan Stok, Opname &amp; Transfer Cabang (4 Langkah Aman)</span>
            </h3>
            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">SOP Inventori UMKM</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- Step 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">1</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Monitoring Kartu Stok &amp; Alert Minimum</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Buka menu <strong>Katalog Stok</strong>. Pantau kuantiti fisik barang per lokasi gudang. Sistem otomatis menampilkan badge peringatan merah jika stok menyentuh titik kritis batas minimum (<em>Reorder Point</em>).
                </p>
                <div class="pt-1 text-[11px] font-medium text-amber-600 dark:text-amber-400">
                    Cegah stok habis saat toko sedang ramai
                </div>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs">2</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Penyesuaian Cepat Terkendali (Quick Adjust)</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Jika ada bahan basi, botol pecah, atau barang tumpah, catat segera via tombol <strong>Penyesuaian Cepat</strong> dengan memilih alasan (Basi / Rusak / Sample Promosi). Selisih besar mewajibkan PIN Supervisor.
                </p>
                <div class="pt-1 text-[11px] font-medium text-rose-600 dark:text-rose-400">
                    Setiap barang rusak terdokumentasi rapi
                </div>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">3</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Siklus Rutin Stock Opname Cabang</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Jadwalkan stock opname mingguan atau bulanan. Cetak lembar hitung kosong (<em>Blind Sheet</em>). Staf menginput hitungan fisik riil ke sistem, dan sistem otomatis menghitung selisih serta nilai rupiah susut.
                </p>
                <div class="pt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                    Rekonsiliasi stok fisik akurat &amp; transparan
                </div>
            </div>

            <!-- Step 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">4</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Mutasi &amp; Transfer Cabang Ber-Barcode</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Kirim stok dari gudang pusat ke outlet cabang via menu <strong>Transfer Stok</strong>. Kuantiti transit terkunci dan tidak bisa dijual sebelum staf cabang penerima menekan tombol konfirmasi terima fisik barang.
                </p>
                <div class="pt-1 text-[11px] font-medium text-blue-600 dark:text-blue-400">
                    Nol kehilangan barang di perjalanan
                </div>
            </div>
        </div>
    </div>

    <!-- 4. DIAGRAM RESEP BILL OF MATERIALS (BOM) & HPP OTOMATIS -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="git-fork" class="w-4 h-4 text-emerald-600"></i>
                    <span>Bagaimana Resep (Bill of Materials) &amp; Potong Stok Otomatis Bekerja?</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Satu transaksi penjualan kasir memotong banyak bahan baku sekaligus secara presisi.</p>
            </div>
            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md">Otomasi Kasir POS</span>
        </div>

        <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3">
            <div class="flex items-center justify-between flex-wrap gap-2 text-xs font-bold text-black dark:text-white">
                <span class="flex items-center gap-1.5 text-sm">
                    <i data-lucide="coffee" class="w-4 h-4 text-amber-600"></i>
                    Contoh: Kasir Menjual 1 Cup "Kopi Susu Gula Aren"
                </span>
                <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1 text-[11px]">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                    Sistem Otomatis Mengurangi Bahan Baku:
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2.5 text-xs">
                <div class="p-3 rounded-xl bg-white dark:bg-black/20 border border-black/5 dark:border-white/10 space-y-1">
                    <span class="font-bold text-black dark:text-white text-[12px] block">1. Biji Kopi Espresso</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-mono font-bold text-xs">-18 Gram</span>
                    <p class="text-[10.5px] text-black/50 dark:text-white/50">HPP Rp 1.800</p>
                </div>

                <div class="p-3 rounded-xl bg-white dark:bg-black/20 border border-black/5 dark:border-white/10 space-y-1">
                    <span class="font-bold text-black dark:text-white text-[12px] block">2. Susu Fresh Milk</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-mono font-bold text-xs">-120 mL</span>
                    <p class="text-[10.5px] text-black/50 dark:text-white/50">HPP Rp 2.400</p>
                </div>

                <div class="p-3 rounded-xl bg-white dark:bg-black/20 border border-black/5 dark:border-white/10 space-y-1">
                    <span class="font-bold text-black dark:text-white text-[12px] block">3. Gula Aren Cair</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-mono font-bold text-xs">-25 mL</span>
                    <p class="text-[10.5px] text-black/50 dark:text-white/50">HPP Rp 750</p>
                </div>

                <div class="p-3 rounded-xl bg-white dark:bg-black/20 border border-black/5 dark:border-white/10 space-y-1">
                    <span class="font-bold text-black dark:text-white text-[12px] block">4. Cup &amp; Lid Sablon</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-mono font-bold text-xs">-1 Pcs</span>
                    <p class="text-[10.5px] text-black/50 dark:text-white/50">HPP Rp 850</p>
                </div>

                <div class="p-3 rounded-xl bg-white dark:bg-black/20 border border-black/5 dark:border-white/10 space-y-1">
                    <span class="font-bold text-black dark:text-white text-[12px] block">5. Sedotan Bio</span>
                    <span class="text-emerald-600 dark:text-emerald-400 font-mono font-bold text-xs">-1 Pcs</span>
                    <p class="text-[10.5px] text-black/50 dark:text-white/50">HPP Rp 150</p>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-emerald-500/[0.08] dark:bg-emerald-500/[0.12] border border-emerald-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                <span class="text-black/70 dark:text-white/70">
                    <strong>Hasil Kalkulasi HPP Otomatis:</strong> Total Modal Bahan Baku = <strong>Rp 5.950</strong>. Jika dijual Rp 18.000, margin laba kotor Anda adalah <strong>Rp 12.050 (66,9%)</strong>.
                </span>
                <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 shrink-0">
                    Jika harga susu naik, HPP otomatis ter-update!
                </span>
            </div>
        </div>
    </div>

    <!-- 5. DIREKTORI 4 SUB-FITUR UTAMA MODUL INVENTORI -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-amber-600"></i>
                    <span>Direktori 4 Modul Operasional Inventori Terpadu</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Instrumen lengkap pengelolaan stok dari pemantauan saldo hingga mutasi antar-lokasi.</p>
            </div>
            <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-md">4 Layar Utama</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- 1. Katalog Stok -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="boxes" class="w-4 h-4 text-amber-500"></i>
                            1. Katalog Stok
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">Saldo Fisik</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Daftar stok seluruh barang dan bahan baku per gudang lengkap dengan nilai aset persediaan rupiah.</p>
                </div>
                <a href="{{ route('inventory.stocks') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Lihat Saldo Stok</span> &rarr;
                </a>
            </div>

            <!-- 2. Kartu Mutasi -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="arrow-left-right" class="w-4 h-4 text-blue-600"></i>
                            2. Kartu Mutasi
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400">Audit Trail</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Buku besar mutasi: rekaman setiap detik stok masuk (pembelian PO), stok keluar (kasir POS), dan penyesuaian.</p>
                </div>
                <a href="{{ route('inventory.movements') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Telusuri Mutasi</span> &rarr;
                </a>
            </div>

            <!-- 3. Stock Opname -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="clipboard-list" class="w-4 h-4 text-emerald-600"></i>
                            3. Stock Opname
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Blind Count</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Sesi penghitungan fisik periodik, rekonsiliasi selisih barang, dan penerbitan jurnal penyesuaian otomatis.</p>
                </div>
                <a href="{{ Route::has('inventory.opnames.index') ? route('inventory.opnames.index') : (Route::has('inventory.opname') ? route('inventory.opname') : route('inventory.stocks')) }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Mulai Opname Fisik</span> &rarr;
                </a>
            </div>

            <!-- 4. Transfer Gudang -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="truck" class="w-4 h-4 text-indigo-600"></i>
                            4. Transfer Cabang
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">Antar-Gudang</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Pengiriman pasokan barang antar-outlet dengan surat jalan resmi dan konfirmasi terima aman.</p>
                </div>
                <a href="{{ Route::has('inventory.transfers.index') ? route('inventory.transfers.index') : (Route::has('inventory.transfers') ? route('inventory.transfers') : route('inventory.stocks')) }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Kirim Transfer Stok</span> &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- 6. MATRIKS PENERAPAN NYATA UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base font-extrabold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-amber-600"></i>
                    <span>Penerapan Modul Inventori pada 20 Jenis Usaha</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Skenario konkret kontrol susut barang, kartu stok, dan opname fisik di 6 klaster UMKM Indonesia.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-bold border border-amber-500/20 self-start sm:self-auto">
                20 Praktik Nol Kebocoran
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
            <!-- Klaster 1: Kuliner & F&B -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-amber-700 dark:text-amber-400">
                        <i data-lucide="utensils" class="w-4 h-4"></i>
                        <span>1. Kuliner &amp; F&amp;B</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400">5 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>1. Restoran Dine-In:</strong> Potong stok daging steak &amp; bumbu dapur per porsi meja saji, blind opname harian daging beku di chiller sebelum tutup restoran.</p>
                    <p><strong>2. Coffee Shop &amp; Cafe:</strong> Pelacakan gramasi biji kopi specialty per shot espresso, pemakaian literan susu UHT, dan stok sirup rasa.</p>
                    <p><strong>3. Bakery &amp; Toko Roti:</strong> Pemotongan bahan adonan saat proses baking subuh dan pencatatan roti sisa *waste/spoilage* malam hari.</p>
                    <p><strong>4. Cloud Kitchen Online:</strong> Manajemen stok multi-brand dalam satu freezer sentral dengan kartu stok terpisah untuk masing-masing merk.</p>
                    <p><strong>5. Katering Prasmanan:</strong> Alokasi bahan per jadwal pesanan acara dan pengembalian sisa logistik piring, sendok, dan chafing dish.</p>
                </div>
            </div>

            <!-- Klaster 2: Manufaktur & HPP -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-blue-700 dark:text-blue-400">
                        <i data-lucide="factory" class="w-4 h-4"></i>
                        <span>2. Manufaktur &amp; HPP</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">5 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>6. Konveksi &amp; Garment:</strong> Pengawasan susut kain roll per yard, stok kancing, ritsleting, benang bordir, dan kartu stok pakaian jadi per ukuran.</p>
                    <p><strong>7. Percetakan &amp; Offset:</strong> Stok rim kertas plano, drum tinta CMYK, plat cetak CTP, dan cairan kimia pencuci mesin.</p>
                    <p><strong>8. Mebel &amp; Furniture Kayu:</strong> Stok kubikasi kayu jati oven, kaleng thinner, pernis finishing melamin, dan sekrup engsel pintu.</p>
                    <p><strong>9. Bengkel Bubut &amp; Logam:</strong> Batang besi as baja, mata bor frais, mata pisau bubut, dan pelumas coolant pemotong besi.</p>
                    <p><strong>10. Kerajinan Tangan / Craft:</strong> Bahan baku manik-manik, pita kemasan, lem tembak silikon, dan karton packaging souvenir.</p>
                </div>
            </div>

            <!-- Klaster 3: Ritel & Apotek -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-emerald-700 dark:text-emerald-400">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        <span>3. Ritel &amp; Apotek</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">2 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>11. Minimarket &amp; Kelontong:</strong> Scan barcode fisik opname malam per lorong rak snack, sabun, dan sembako dengan selisih yang otomatis terjurnal.</p>
                    <p><strong>12. Apotek &amp; Toko Obat:</strong> Manajemen nomor batch, tanggal kadaluarsa obat (FEFO-FIFO), dan brankas obat keras psikotropika terkunci.</p>
                </div>
            </div>

            <!-- Klaster 4: Jasa Harian -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-purple-700 dark:text-purple-400">
                        <i data-lucide="wrench" class="w-4 h-4"></i>
                        <span>4. Jasa Harian</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400">4 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>13. Bengkel Mobil &amp; Motor:</strong> Stok oli botolan, aki kering, ban luar/dalam, kampas rem, dan filter udara yang otomatis terpotong saat form PKB servis diselesaikan.</p>
                    <p><strong>14. Barbershop &amp; Salon:</strong> Pemakaian botol pomade, cat pewarna rambut, tonik rambut, dan stok handuk bersih.</p>
                    <p><strong>15. Laundry Kiloan:</strong> Stok jeriken deterjen cair konsentrat, softener pelembut, pewangi pakaian, dan plastik packing jinjing.</p>
                    <p><strong>16. Cuci Mobil &amp; Detailing:</strong> Stok shampoo salju, semir ban silikon, obat poles jamur kaca, dan kain microfiber bersih.</p>
                </div>
            </div>

            <!-- Klaster 5: Jasa Proyek -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-cyan-700 dark:text-cyan-400">
                        <i data-lucide="hard-hat" class="w-4 h-4"></i>
                        <span>5. Jasa Proyek</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">3 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>17. Kontraktor &amp; Renovasi:</strong> Pemisahan stok material di depo transit utama vs material semen dan pasir yang langsung dikirim ke lokasi proyek.</p>
                    <p><strong>18. Wedding Organizer:</strong> Inventaris modul tenda pesta, kain backdrop pelaminan, lampu sorot, vas bunga, dan sound system.</p>
                    <p><strong>19. Digital IT &amp; Creative Agency:</strong> Inventaris perangkat keras laptop tim, kamera produksi video, lensa cinema, dan lighting studio.</p>
                </div>
            </div>

            <!-- Klaster 6: Distribusi & Agro -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-400">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                        <span>6. Distribusi &amp; Agro</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-600 dark:text-rose-400">1 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>20. Distributor Grosir B2B &amp; Agrobisnis:</strong> Penyimpanan karung beras/jagung di multi-gudang regional, susut timbangan gabah musiman, dan pengawasan muatan di dalam bak truk kirim.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. KEBIJAKAN MAKER-CHECKER & ANTI-FRAUD INVENTORI -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Otorisasi PIN Supervisor, Maker-Checker &amp; Anti-Fraud Selisih Stok</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Mekanisme kontrol keamanan berlapis untuk menjamin angka pembukuan persediaan tidak dapat dimanipulasi.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs pt-1">
            <div class="p-4 rounded-xl border border-amber-500/20 bg-amber-500/[0.04] dark:bg-amber-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-amber-700 dark:text-amber-400">
                    <i data-lucide="key-round" class="w-4 h-4"></i>
                    <span>PIN Supervisor untuk Selisih Signifikan</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap tindakan penyesuaian cepat (Quick Adjust) yang mengurangi nilai stok lebih dari Rp 100.000 atau selisih kuantiti lebih dari 10 unit wajib menginput PIN Supervisor/Owner. Selisih besar oleh non-owner dialihkan ke status <em>Pending Approval</em>.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-amber-500/20 bg-amber-500/[0.04] dark:bg-amber-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-amber-700 dark:text-amber-400">
                    <i data-lucide="eye-off" class="w-4 h-4"></i>
                    <span>Integritas Hitung Blind Count</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pada lembar kerja opname fisik staf lapangan, kolom "Saldo Komputer" disembunyikan secara sengaja. Staf wajib menghitung fisik nyata di rak toko, sehingga tidak ada celah staf hanya menyalin angka sistem tanpa memeriksa barang.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-amber-500/20 bg-amber-500/[0.04] dark:bg-amber-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-amber-700 dark:text-amber-400">
                    <i data-lucide="scale" class="w-4 h-4"></i>
                    <span>Auto-Journaling Selisih ke Laba Rugi</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Saat sesi stock opname disetujui (Reconcile), sistem otomatis menerbitkan jurnal akuntansi pembukuan ganda ke akun <em>Beban Kerugian Selisih Inventori</em> vs <em>Persediaan Barang Dagang</em> tanpa perlu entri manual akuntan.
                </p>
            </div>
        </div>
    </div>

    <!-- 8. TANYA JAWAB BISNIS OWNER (FAQ) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs"
        x-data="{ expandedFaq: null }">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="help-circle" class="w-4 h-4 text-amber-600"></i>
                <span>Tanya Jawab Seputar Inventori, Opname &amp; HPP Resep (FAQ)</span>
            </h3>
            <span class="text-xs text-black/40 dark:text-white/40">5 Solusi Cepat</span>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 1 ? null : 1)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>1. Bagaimana Cooca menghitung HPP saat harga beli bahan baku dari supplier sering naik turun?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 1" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Cooca menggunakan metode <strong>Rata-Rata Bergerak (Moving Average)</strong> atau <strong>FIFO (First In, First Out)</strong>. Setiap kali barang masuk dari pesanan pembelian supplier (PO / Goods Receipt), sistem menghitung ulang nilai rata-rata tertimbang harga pokok bahan. Dengan demikian, margin laba bersih di dashboard Anda selalu akurat sesuai harga modal pembelian terbaru.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 2 ? null : 2)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>2. Apa yang harus saya lakukan jika hasil stock opname fisik lebih sedikit dari catatan komputer?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 2" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Masukkan hasil hitungan fisik apa adanya ke formulir Stock Opname. Cooca akan menampilkan selisih minus beserta nilai kerugian rupiahnya. Saat Anda mengonfirmasi tombol <strong>Rekonsiliasi (Reconcile)</strong>, sistem akan menyesuaikan saldo komputer agar persis sama dengan fisik nyata dan mencatat selisih tersebut ke pos Kerugian Inventori untuk dievaluasi dalam audit internal toko.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 3 ? null : 3)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>3. Mengapa saya sangat disarankan menggunakan metode "Blind Count" saat stock opname?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 3" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Metode <em>Blind Count</em> adalah standar emas pencegahan fraud ritel di seluruh dunia. Jika staf gudang mengetahui bahwa sistem mencatat 20 kaleng susu, staf malas cenderung hanya menulis angka 20 tanpa repot menghitung fisik. Dengan lembar kosong, staf terpaksa menghitung barang nyata di rak, sehingga selisih barang hilang atau basi dapat terdeteksi sejak dini.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 4 ? null : 4)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>4. Bagaimana cara memastikan barang yang dikirim antar-cabang tidak hilang atau berkurang di jalan?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 4" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Gunakan fitur <strong>Transfer Antar-Gudang</strong>. Saat gudang asal menerbitkan surat jalan pengiriman (<em>Dispatch</em>), stok keluar dari gudang asal dan masuk ke status "Dalam Transit". Stok tersebut terkunci dan belum dapat dijual oleh cabang tujuan sampai staf cabang penerima menghitung fisik nyata yang tiba dan mengklik tombol <strong>Konfirmasi Terima (Receive)</strong>.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 5 ? null : 5)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>5. Apakah bahan baku yang kadaluarsa atau rusak bisa dicatat tanpa merusak Laporan Laba Rugi?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 5" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Bisa dan justru wajib dicatat. Gunakan tombol <strong>Penyesuaian Cepat (Quick Adjust)</strong> dan pilih alasan <em>Bahan Basi / Waste</em>. Nilai modal barang yang dibuang akan otomatis dibukukan sebagai beban operasional susut (Beban Spoilage) secara transparan, sehingga angka aset persediaan di neraca tetap mencerminkan barang riil yang masih layak pakai.</p>
                </div>
            </div>
        </div>
    </div>

</section>
