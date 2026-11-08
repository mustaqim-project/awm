<!-- ===================================================== -->
<!-- SECTION: MODUL PUSAT KEUANGAN, AKUNTANSI & REKONSILIASI-->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-finance" class="scroll-mt-24 space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'finance') && matchesSearch('keuangan kas bank akuntansi laporan laba rugi neraca arus kas p&l jurnal buku besar coa chart of accounts biaya beban operasional hutang piutang ap ar rekonsiliasi edc settlement cooca pay')">

    <!-- Backward-compatibility anchor for quick links -->
    <div id="sec-accounting-hub" class="hidden"></div>

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL FINANCE -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-600 to-green-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(16,185,129,0.35)] shrink-0">
                <i data-lucide="landmark" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Pusat Keuangan, Akuntansi &amp; Rekonsiliasi (Finance &amp; Accounting Hub)</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">Standar Enterprise</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20">Double-Entry Balance</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">Anti-Fraud Safe</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Pusat kendali likuiditas bisnis: pantau saldo kas laci vs rekening bank harian, catat beban biaya, awasi umur hutang-piutang, dan otomatisasi tutup buku pembukuan ganda tanpa ribet.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('finance.cash_bank'))
                <a href="{{ route('finance.cash-bank.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                    <span>Kas &amp; Bank</span>
                </a>
            @endif
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('expenses.view'))
                <a href="{{ route('finance.expenses.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-rose-500 dark:text-rose-400"></i>
                    <span>Beban &amp; Biaya</span>
                </a>
            @endif
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('accounting.view'))
                <a href="{{ Route::has('finance.accounting.coa') ? route('finance.accounting.coa') : route('finance.coa.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="list-tree" class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400"></i>
                    <span>Bagan Akun (COA)</span>
                </a>
                <a href="{{ Route::has('finance.accounting.balance-sheet') ? route('finance.accounting.balance-sheet') : route('finance.balance-sheet') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="scale" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Neraca Keuangan</span>
                </a>
            @endif
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('finance.cash_bank'))
                <a href="{{ route('finance.settlements.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="badge-percent" class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400"></i>
                    <span>Pencairan Cooca Pay</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="pie-chart" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Selamat Tinggal Uang Campur Aduk: Fondasi Finansial Sehat untuk Mencegah Kebocoran &amp; Syarat Permodalan Bank</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Banyak pengusaha UMKM terjebak dalam ilusi <strong>omzet penjualan terlihat ramai ratusan juta rupiah, namun saat akhir bulan uang kas toko selalu habis tanpa jejak</strong>. Masalah klasik ini bersumber dari tiga hal: <em>uang pribadi bercampur uang toko, kasbon kecil kasir yang tidak tercatat rapi, serta piutang pelanggan dan tagihan supplier yang tidak terawasi jatuh temponya</em>. Modul Keuangan Cooca hadir untuk mengubah pembukuan yang rumit menjadi otomatis, akurat, dan dapat dipercaya oleh perbankan saat Anda mengajukan penambahan modal usaha (KUR/Kredit Kerja).
                </p>
            </div>
        </div>

        <!-- 4 Pilar Manajemen Finansial COOCA bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="wallet-cards" class="w-4 h-4"></i>
                    <span>1. Arus Kas &amp; Bank Real-Time</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pisahkan uang laci kasir, brankas toko, dan rekening operasional bank. Setiap uang masuk dari kasir POS atau transfer bank otomatis mencatat mutasi tanpa perlu rekap manual.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-rose-500/[0.06] dark:bg-rose-500/[0.1] border border-rose-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>2. Kontrol Beban Toko &amp; Opex</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Catat biaya listrik, sewa tempat, bensin kurir, hingga makan siang staf kasir lengkap dengan lampiran foto struk belanja untuk mencegah nota fiktif karyawan.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="calendar-clock" class="w-4 h-4"></i>
                    <span>3. Pengawasan Hutang &amp; Piutang</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Ketahui siapa saja pelanggan yang menunggak kasbon dan tanggal jatuh tempo faktur supplier bahan baku. Cegah gagal bayar sebelum merusak hubungan dengan pemasok.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-indigo-500/[0.06] dark:bg-indigo-500/[0.1] border border-indigo-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="scale" class="w-4 h-4"></i>
                    <span>4. Pembukuan Ganda &amp; Tutup Buku</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem otomatis menjurnal setiap transaksi kasir ke debit dan kredit yang seimbang. Cetak Neraca dan Laporan Laba Rugi resmi berstandar akuntansi tanpa perlu menggaji akuntan mahal.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA LANGKAH DEMI LANGKAH (4 LANGKAH SOP FINANSIAL) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-black/60 dark:text-white/60 flex items-center gap-2">
                <i data-lucide="git-commit" class="w-4 h-4 text-[#007AFF]"></i>
                <span>SOP Praktis Pengelolaan Finansial Toko Harian hingga Bulanan (4 Langkah)</span>
            </h3>
            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">SOP Keuangan UMKM</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- Step 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">1</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Buka Kasir &amp; Saldo Awal</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Awali hari dengan memasukkan modal kembalian di laci kasir (misal: Rp 200.000). Saat tutup toko di malam hari, kasir menghitung fisik uang dan sistem otomatis menghitung selisih kas secara transparan.
                </p>
                <div class="pt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                    Rekonsiliasi kas harian &lt; 5 menit
                </div>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs">2</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Catat Beban Operasional</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Setiap uang yang keluar untuk belanja perlengkapan, bensin, token listrik, atau air galon wajib diinput ke menu <a href="{{ route('finance.expenses.index') }}" class="text-[#007AFF] font-medium hover:underline">Beban Biaya</a> disertai foto struk fisik dari kamera HP kasir.
                </p>
                <div class="pt-1 text-[11px] font-medium text-rose-600 dark:text-rose-400">
                    Nol pengeluaran gelap tak tercatat
                </div>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">3</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Kelola Piutang &amp; Hutang</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Pantau tagihan jatuh tempo ke pelanggan di menu <a href="{{ Route::has('finance.receivables.index') ? route('finance.receivables.index') : route('finance.receivables') }}" class="text-[#007AFF] font-medium hover:underline">Piutang</a> dan jadwal pembayaran nota pemasok di menu <a href="{{ Route::has('finance.payables.index') ? route('finance.payables.index') : route('finance.payables') }}" class="text-[#007AFF] font-medium hover:underline">Hutang</a> agar reputasi bisnis selalu terjaga.
                </p>
                <div class="pt-1 text-[11px] font-medium text-amber-600 dark:text-amber-400">
                    Peringatan jatuh tempo otomatis
                </div>
            </div>

            <!-- Step 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">4</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Rekonsiliasi &amp; Tutup Buku</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Di akhir pekan atau akhir bulan, cocokkan mutasi rekening bank dengan catatan Cooca melalui menu <a href="{{ Route::has('finance.accounting.reconciliation') ? route('finance.accounting.reconciliation') : route('finance.reconciliations.index') }}" class="text-[#007AFF] font-medium hover:underline">Rekonsiliasi Bank</a>, lalu cetak laporan Neraca dan Laba Rugi bulanan.
                </p>
                <div class="pt-1 text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                    Laporan siap audit &amp; pajak
                </div>
            </div>
        </div>
    </div>

    <!-- 4. PANDUAN AKSES 15 SUB-MODUL FINANSIAL COOCA -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base font-extrabold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-emerald-600"></i>
                    <span>Direktori Lengkap 15 Sub-Modul Finansial &amp; Akuntansi COOCA</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Navigasi langsung ke seluruh instrumen kas, pembukuan, rekonsiliasi, hingga terminal pembayaran.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold border border-emerald-500/20 self-start sm:self-auto">
                15 Hub Terintegrasi
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">
            <!-- 1. Kas & Bank -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
                            1. Kas &amp; Bank
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Kas Harian</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Kelola rekening bank usaha (BCA, Mandiri, BRI) dan saldo kas tunai fisik toko dalam satu layar.</p>
                </div>
                <a href="{{ route('finance.cash-bank.index') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Buka Kas &amp; Bank</span> &rarr;
                </a>
            </div>

            <!-- 2. Buku Kas (Ledger) -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="book-marked" class="w-4 h-4 text-emerald-600"></i>
                            2. Buku Kas (Ledger)
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400">Mutasi Detail</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Catatan kronologis mutasi kas masuk, kas keluar, dan transfer antarrekening per detik.</p>
                </div>
                <a href="{{ route('finance.cash-bank.ledger') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Buka Buku Kas</span> &rarr;
                </a>
            </div>

            <!-- 3. Pengeluaran / Beban -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="receipt" class="w-4 h-4 text-rose-500"></i>
                            3. Pengeluaran (Beban)
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400">Opex Toko</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Pencatatan biaya operasional (listrik, gaji harian, sewa) lengkap dengan foto bukti struk fisik.</p>
                </div>
                <a href="{{ route('finance.expenses.index') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Kelola Pengeluaran</span> &rarr;
                </a>
            </div>

            <!-- 4. Hutang Usaha (Payables) -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="file-minus" class="w-4 h-4 text-amber-500"></i>
                            4. Hutang Usaha (AP)
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">Tagihan Masuk</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Daftar kewajiban bayar ke supplier atas pembelian bahan baku secara tempo beserta tanggal jatuh tempo.</p>
                </div>
                <a href="{{ Route::has('finance.payables.index') ? route('finance.payables.index') : route('finance.payables') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Pantau Hutang Supplier</span> &rarr;
                </a>
            </div>

            <!-- 5. Piutang Pelanggan (Receivables) -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="file-plus" class="w-4 h-4 text-blue-600"></i>
                            5. Piutang Usaha (AR)
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400">Tagihan Keluar</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Pengawasan tagihan kasbon pelanggan toko dan klien B2B agar tidak terjadi piutang macet.</p>
                </div>
                <a href="{{ Route::has('finance.receivables.index') ? route('finance.receivables.index') : route('finance.receivables') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Tagih Piutang Pelanggan</span> &rarr;
                </a>
            </div>

            <!-- 6. Jurnal Keuangan -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="file-text" class="w-4 h-4 text-indigo-500"></i>
                            6. Jurnal Umum
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">Debit &amp; Kredit</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Entri pembukuan ganda otomatis dari penjualan POS, pembayaran biaya, dan penyesuaian modal.</p>
                </div>
                <a href="{{ route('finance.journals.index') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Lihat Jurnal Pembukuan</span> &rarr;
                </a>
            </div>

            <!-- 7. Bagan Akun (COA) -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="list-tree" class="w-4 h-4 text-purple-600"></i>
                            7. Bagan Akun (COA)
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400">Struktur Akun</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Hierarki klasifikasi akun: Aset Lancar, Kewajiban, Ekuitas Modal, Pendapatan, dan HPP.</p>
                </div>
                <a href="{{ Route::has('finance.accounting.coa') ? route('finance.accounting.coa') : route('finance.coa.index') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Susun Bagan Akun</span> &rarr;
                </a>
            </div>

            <!-- 8. Neraca Keuangan (Balance Sheet) -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="scale" class="w-4 h-4 text-teal-600"></i>
                            8. Neraca Keuangan
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-500/10 text-teal-600 dark:text-teal-400">Kesehatan Bisnis</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Laporan posisi keuangan bisnis: total aset toko seimbang dengan total kewajiban dan modal modal disetor.</p>
                </div>
                <a href="{{ Route::has('finance.accounting.balance-sheet') ? route('finance.accounting.balance-sheet') : route('finance.balance-sheet') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Cetak Laporan Neraca</span> &rarr;
                </a>
            </div>

            <!-- 9. Neraca Saldo (Trial Balance) -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="table-properties" class="w-4 h-4 text-cyan-600"></i>
                            9. Neraca Saldo
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">Verifikasi Angka</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Pengecekan kesetaraan total debit dan total kredit seluruh akun buku besar sebelum tutup buku.</p>
                </div>
                <a href="{{ Route::has('finance.accounting.trial-balance') ? route('finance.accounting.trial-balance') : route('finance.trial-balance') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Buka Neraca Saldo</span> &rarr;
                </a>
            </div>

            <!-- 10. Buku Besar (General Ledger) -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="book-open" class="w-4 h-4 text-blue-700"></i>
                            10. Buku Besar (GL)
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400">Audit Trail</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Rangkuman riwayat mutasi per akun akuntansi individual yang dapat difilter berdasarkan rentang tanggal.</p>
                </div>
                <a href="{{ Route::has('finance.accounting.general-ledger') ? route('finance.accounting.general-ledger') : route('finance.general-ledger') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Telusuri Buku Besar</span> &rarr;
                </a>
            </div>

            <!-- 11. Rekonsiliasi Bank -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="git-compare" class="w-4 h-4 text-emerald-600"></i>
                            11. Rekonsiliasi Bank
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Pencocokan Rekening</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Unggah rekening koran bank (CSV) dan cocokkan otomatis transaksi debit/kredit dengan catatan toko.</p>
                </div>
                <a href="{{ Route::has('finance.accounting.reconciliation') ? route('finance.accounting.reconciliation') : route('finance.reconciliations.index') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Cocokkan Rekening Bank</span> &rarr;
                </a>
            </div>

            <!-- 12. Payout Hub & Settlement Cooca Pay -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="badge-percent" class="w-4 h-4 text-teal-600"></i>
                            12. Settlement Cooca Pay
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-500/10 text-teal-600 dark:text-teal-400">Pencairan Dana</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Pantau pencairan saldo hasil pembayaran non-tunai (QRIS &amp; EDC) yang masuk ke rekening bank toko.</p>
                </div>
                <a href="{{ route('finance.settlements.index') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Periksa Pencairan Dana</span> &rarr;
                </a>
            </div>

            <!-- 13. Rekonsiliasi Eksternal -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="link-2" class="w-4 h-4 text-sky-600"></i>
                            13. Rekonsiliasi Eksternal
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400">Multi-Channel</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Dashboard likuiditas dan identifikasi selisih pembukuan dengan payment gateway pihak ketiga.</p>
                </div>
                <a href="{{ Route::has('finance.external-reconciliation.index') ? route('finance.external-reconciliation.index') : route('finance.external-recon.index') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Buka Rekonsiliasi Eksternal</span> &rarr;
                </a>
            </div>

            <!-- 14. Mesin EDC Toko -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="credit-card" class="w-4 h-4 text-indigo-600"></i>
                            14. Mesin EDC Toko
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">Terminal Kartu</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Daftarkan mesin EDC fisik di kasir toko dan tautkan langsung ke akun rekening penampung masing-masing bank.</p>
                </div>
                <a href="{{ route('finance.edc-terminals.index') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Kelola Mesin EDC</span> &rarr;
                </a>
            </div>

            <!-- 15. Rekening Penarikan (Payout Accounts) -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="building" class="w-4 h-4 text-amber-600"></i>
                            15. Rekening Penarikan Dana
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">Rekening Resmi</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Daftarkan rekening bank pemilik bisnis yang telah terverifikasi untuk menerima pencairan dana omzet berkala.</p>
                </div>
                <a href="{{ route('finance.payout-accounts.index') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Atur Rekening Penarikan</span> &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- 5. MATRIKS PENERAPAN NYATA UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base font-extrabold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-emerald-600"></i>
                    <span>Penerapan Manajemen Finansial pada 20 Jenis Usaha</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Skenario konkret pembukuan kas, beban opex, dan rekonsiliasi bank di 6 klaster UMKM Indonesia.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold border border-emerald-500/20 self-start sm:self-auto">
                20 Praktik Bisnis Sehat
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
                    <p><strong>1. Restoran Dine-In:</strong> Rekonsiliasi harian antara penerimaan uang tunai kasir, gesek kartu mesin EDC BCA, dan QRIS statis meja makan terhadap total omzet closing harian.</p>
                    <p><strong>2. Coffee Shop &amp; Cafe:</strong> Mencatat beban pembelian biji kopi specialty dan susu pasteurisasi sebagai beban HPP operasional, memantau margin laba kotor minuman per cup.</p>
                    <p><strong>3. Bakery &amp; Toko Roti:</strong> Mencatat penerimaan uang muka (DP) 50% pesanan kue ulang tahun bertingkat sebagai pos Kewajiban Titipan Pesanan sampai kue diambil pelanggan.</p>
                    <p><strong>4. Cloud Kitchen Online:</strong> Memisahkan penerimaan pencairan mingguan dari platform kurir online ke rekening bank terpisah agar tidak terpakai modal belanja bumbu harian.</p>
                    <p><strong>5. Katering Prasmanan:</strong> Penjadwalan pembayaran termin tempo bahan daging sapi ke jagal langganan (TOP 14 hari) setelah acara pernikahan klien selesai dibayarkan.</p>
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
                    <p><strong>6. Konveksi &amp; Garment:</strong> Pembukuan ganda untuk pembelian benang dan kain roll: mendebit Persediaan Bahan Baku dan mengkredit Kas Bank saat faktur dilunasi.</p>
                    <p><strong>7. Percetakan &amp; Offset:</strong> Alokasi biaya listrik mesin cetak industri dan penyusutan mesin CTP ke dalam pos Beban Overhead Pabrik untuk perhitungan laba bersih akurat.</p>
                    <p><strong>8. Mebel &amp; Furniture:</strong> Pengawasan umur piutang (AR Aging) pesanan set interior hotel berbintang dengan termin pembayaran 30&ndash;60 hari.</p>
                    <p><strong>9. Bengkel Bubut &amp; Logam:</strong> Mencatat tagihan bahan besi baja dari distributor besar ke modul Hutang Usaha (AP) untuk menghindari denda keterlambatan bayar.</p>
                    <p><strong>10. Kerajinan Tangan / Craft:</strong> Pencatatan biaya ongkos kirim kontainer kargo dan biaya komisi pameran kerajinan ke akun Beban Pemasaran &amp; Distribusi.</p>
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
                    <p><strong>11. Minimarket &amp; Kelontong:</strong> Membatasi plafon kasbon warga sekitar maksimal Rp 500.000 dengan sistem penguncian otomatis di kasir saat plafon tersentuh.</p>
                    <p><strong>12. Apotek &amp; Toko Obat:</strong> Pencatatan faktur konsinyasi obat paten dari Pedagang Besar Farmasi (PBF) tempo 21 hari dengan verifikasi nomor faktur pajak resmi.</p>
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
                    <p><strong>13. Bengkel Mobil &amp; Motor:</strong> Pemisahan penerimaan pendapatan jasa servis mekanik dengan pendapatan penjualan sparepart oli/aki dalam pos akun pendapatan terpisah.</p>
                    <p><strong>14. Barbershop &amp; Salon:</strong> Pencatatan penarikan kas tunai harian untuk tips langsung kapster dan pemotongan stok vitamin rambut yang terpakai di ruang cuci.</p>
                    <p><strong>15. Laundry Kiloan:</strong> Penerimaan pembayaran deposit cuci kiloan prabayar (paket 50 kg) dicatat sebagai Titipan Pelanggan dan diakui bertahap saat cucian ditimbang.</p>
                    <p><strong>16. Cuci Mobil &amp; Detailing:</strong> Rekonsiliasi mesin EDC mandiri terhadap penerimaan paket coating poles mobil mewah bertarif jutaan rupiah per unit.</p>
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
                    <p><strong>17. Kontraktor &amp; Renovasi:</strong> Pelacakan arus kas proyek: membandingkan total termin pembayaran dari pemilik gedung dengan realisasi belanja semen, pasir, dan upah kuli.</p>
                    <p><strong>18. Wedding Organizer:</strong> Mengelola uang titipan pengantin untuk pembayaran vendor katering, dekorasi, dan fotografer agar tidak terpakai untuk kas operasional WO.</p>
                    <p><strong>19. Digital IT &amp; Creative Agency:</strong> Penagihan invoice fee bulanan retainer (Termin 14 hari) dengan rekonsiliasi transfer bank otomatis tanpa bukti resi manual.</p>
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
                    <p><strong>20. Distributor Grosir B2B &amp; Agrobisnis:</strong> Mengawasi perputaran piutang ratusan toko kelontong mitra grosir (plafon hingga Rp 25.000.000 per toko) dan pencocokan setoran uang jalan sopir armada kirim.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. KEBIJAKAN OTORISASI RBAC & ANTI-FRAUD KEUANGAN -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Standar Otorisasi Ketat, Pemisahan Tugas (Segregation of Duties) &amp; Perlindungan Anti-Fraud</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Mekanisme keamanan berlapis untuk menjamin uang toko tidak bocor dan catatan kas tidak dapat dimanipulasi.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs pt-1">
            <div class="p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.04] dark:bg-emerald-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-emerald-700 dark:text-emerald-400">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    <span>Pemisahan Tugas (Segregation of Duties)</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Staf kasir hanya memiliki wewenang input transaksi di POS. Wewenang membuat rekening bank baru, menghapus akun COA, atau menarik saldo penampung (payout) dikunci khusus akun <strong>Owner/Finance Manager</strong>. Kasir tidak bisa mengubah angka pembukuan buku besar.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.04] dark:bg-emerald-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-emerald-700 dark:text-emerald-400">
                    <i data-lucide="file-check-2" class="w-4 h-4"></i>
                    <span>Jejak Audit Mutasi Transaksi</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap mutasi kas masuk, keluar, atau transfer antarrekening otomatis mencatat stempel audit di tabel <code>audit_logs</code>: siapa pengguna yang menginput, alamat IP, waktu tepat per detik, beserta alasan pengeluaran kas. Catatan ini bersifat kekal dan tidak bisa dihapus.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.04] dark:bg-emerald-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-emerald-700 dark:text-emerald-400">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <span>Sanitasi Formula Ekspor Excel</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Saat Owner mengunduh laporan keuangan ke format Excel (XLSX/CSV), mesin ekspor Cooca membersihkan karakter berbahaya (seperti <code>=</code>, <code>+</code>, <code>-</code>, <code>@</code>) pada nama deskripsi biaya untuk mencegah eksekusi virus makro formula injection di komputer laptop Anda.
                </p>
            </div>
        </div>
    </div>

    <!-- 7. TANYA JAWAB BISNIS OWNER (FAQ) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs"
        x-data="{ expandedFaq: null }">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="help-circle" class="w-4 h-4 text-emerald-600"></i>
                <span>Tanya Jawab Seputar Keuangan, Kas &amp; Pembukuan Toko (FAQ)</span>
            </h3>
            <span class="text-xs text-black/40 dark:text-white/40">5 Solusi Cepat</span>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 1 ? null : 1)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>1. Saya tidak paham akuntansi debit dan kredit, apakah saya tetap bisa memakai modul Keuangan Cooca?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 1" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Tentu saja sangat bisa. Cooca dirancang dengan teknologi <em>Auto-Journaling</em>. Anda dan staf kasir hanya perlu mencatat penjualan seperti biasa di mesin kasir POS dan menginput pengeluaran harian di menu <strong>Beban &amp; Biaya</strong>. Di balik layar, sistem Cooca yang akan menyusun jurnal debit dan kredit secara otomatis dan menghasilkan Laporan Neraca serta Laba Rugi yang seimbang.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 2 ? null : 2)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>2. Mengapa saldo kas di sistem kadang berbeda dengan uang fisik di laci kasir saat tutup toko?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 2" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Selisih fisik kasir biasanya disebabkan oleh tiga hal: salah memberikan uang kembalian receh, staf mengambil kas laci untuk membeli barang operasional kecil tanpa mencatatnya di menu Beban, atau pembayaran QRIS/kartu yang tidak sengaja dipilih sebagai uang tunai. Cooca mencatat selisih tersebut ke dalam laporan pertanggungjawaban shift kasir agar Owner bisa mengevaluasi kedisiplinan staf toko.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 3 ? null : 3)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>3. Bagaimana cara mencocokkan mutasi rekening bank dengan catatan penjualan di Cooca?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 3" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Unduh mutasi rekening koran dari internet banking Anda (format CSV), lalu buka menu <strong>Keuangan &rarr; Rekonsiliasi Bank</strong>. Unggah berkas tersebut ke sistem. Fitur pencocokan Cooca akan otomatis membandingkan nominal dan tanggal transfer pelanggan dengan transaksi di toko Anda, menandai transaksi yang sudah lunas dan menampilkan selisih yang belum teridentifikasi.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 4 ? null : 4)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>4. Apakah laporan keuangan dari Cooca bisa dipakai untuk pengajuan pinjaman bank atau laporan pajak tahunan?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 4" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Sangat bisa. Laporan Neraca (<em>Balance Sheet</em>), Neraca Saldo (<em>Trial Balance</em>), dan Laporan Laba Rugi di Cooca disusun mengikuti Prinsip Standar Akuntansi Keuangan Entitas Tanpa Akuntabilitas Publik (SAK ETAP / EMKM). Seluruh laporan dapat diekspor langsung ke Excel (XLSX) resmi siap lampir untuk audit analis kredit bank maupun konsultan pajak usaha Anda.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 5 ? null : 5)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>5. Kapan uang hasil pembayaran QRIS dan EDC Cooca Pay cair ke rekening bank saya?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 5" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Pencairan dana non-tunai (Settlement) berjalan otomatis setiap hari kerja (H+1) langsung ke rekening penarikan terverifikasi yang Anda daftarkan di menu <strong>Rekening Penarikan</strong>. Anda dapat memantau rincian nomor batch pencairan dan potongan biaya transaksi MDR di menu <strong>Settlement Cooca Pay</strong>.</p>
                </div>
            </div>
        </div>
    </div>

</section>
