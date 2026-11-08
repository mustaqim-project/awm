<!-- ===================================================== -->
<!-- SECTION: MODUL IMPORT MASSAL EXCEL & CSV (IMPORT HUB) -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-import" class="scroll-mt-24 space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'master' || activeTab === 'inventory') && matchesSearch('import impor migrasi excel spreadsheet csv upload unggah massal bahan baku produk resep bom saldo awal stok living lookup template data')">

    <!-- Backward-compatibility anchor for quick links -->
    <div id="sec-data-exchange-hub" class="hidden"></div>

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL IMPORT -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-600 to-cyan-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(13,148,136,0.35)] shrink-0">
                <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Pusat Import Massal Excel &amp; Migrasi Data (Import Hub)</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">Migrasi Cepat</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">Smart Living Lookup</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">20 Sektor UMKM</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Pindahkan ribuan master data bahan baku, katalog produk, formula resep (BOM), dan saldo awal stok gudang dari file Excel/CSV ke Cooca dalam hitungan detik tanpa input manual satu per satu.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('materials.view') || \App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('inventory.view'))
                <a href="{{ route('import.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                    <span>Buka Import Hub</span>
                </a>
            @endif
            <a href="{{ route('import.materials.template') }}"
                class="h-9 px-3 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1 transition active:scale-95" title="Download Template Bahan Baku">
                <i data-lucide="download" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                <span>Tpl Bahan</span>
            </a>
            <a href="{{ route('import.products.template') }}"
                class="h-9 px-3 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1 transition active:scale-95" title="Download Template Produk">
                <i data-lucide="download" class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400"></i>
                <span>Tpl Produk</span>
            </a>
            <a href="{{ route('import.recipes.template') }}"
                class="h-9 px-3 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1 transition active:scale-95" title="Download Template Resep BOM">
                <i data-lucide="download" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400"></i>
                <span>Tpl Resep</span>
            </a>
            <a href="{{ route('import.inventory.template') }}"
                class="h-9 px-3 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1 transition active:scale-95" title="Download Template Saldo Stok">
                <i data-lucide="download" class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400"></i>
                <span>Tpl Stok</span>
            </a>
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-cyan-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="zap" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Migrasi Cepat Tanpa Ribet: Bawa Ribuan Barang &amp; Resep Lama Anda ke Cooca Hari Ini Juga</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Hambatan terbesar pemilik bisnis saat beralih ke software kasir modern adalah <strong>ketakutan menghabiskan berminggu-minggu mengetik ribuan produk, nama bumbu masakan, takaran resep, dan saldo stok gudang satu per satu</strong>. Pusat Import Cooca dirancang untuk menyelesaikan masalah tersebut: unduh template Excel resmi kami, salin daftar barang lama Anda, unggah ke sistem, dan lihat preview verifikasi sebelum disimpan. Dalam hitungan detik, seluruh toko Anda siap melayani transaksi kasir POS secara langsung.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Keunggulan Mesin Import COOCA bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="file-check" class="w-4 h-4"></i>
                    <span>1. Format Template Standar</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap berkas Excel dilengkapi 3 lembar kerja: <code>INPUT_*</code> (lembar entri data), <code>DATA_REFERENSI</code> (kategori &amp; unit aktif), dan <code>PANDUAN_IMPORT</code> (contoh baris benar).
                </p>
            </div>

            <div class="p-4 rounded-xl bg-teal-500/[0.06] dark:bg-teal-500/[0.1] border border-teal-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-teal-700 dark:text-teal-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>2. Smart Living Lookups</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Kolom kategori barang, satuan (Kg/Pcs/Liter), dan nama outlet otomatis disinkronkan dari database toko Anda, mencegah saltik (typo) nama satuan yang merusak pembukuan.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-cyan-500/[0.06] dark:bg-cyan-500/[0.1] border border-cyan-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-cyan-700 dark:text-cyan-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>3. Resolusi Duplikat Cerdas</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Jika produk sudah ada di sistem, pilih strategi sesuai kebutuhan: <em>Lewati (Skip)</em> untuk mempertahankan harga lama, atau <em>Perbarui (Update)</em> untuk menimpa harga jual dan modal terbaru.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-indigo-500/[0.06] dark:bg-indigo-500/[0.1] border border-indigo-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>4. Pre-Commit Sandbox</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem memvalidasi setiap baris di memori terlebih dahulu. Anda dapat melihat ringkasan baris valid vs baris bermasalah (merah) sebelum menyetujui penyimpanan ke database.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA LANGKAH DEMI LANGKAH (4 LANGKAH SOP IMPORT) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-black/60 dark:text-white/60 flex items-center gap-2">
                <i data-lucide="git-commit" class="w-4 h-4 text-[#007AFF]"></i>
                <span>SOP Praktis Migrasi Data Massal via Excel (4 Langkah Aman)</span>
            </h3>
            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">SOP Migrasi Data UMKM</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- Step 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">1</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Unduh Template Excel Resmi</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Klik tombol <strong>Template</strong> sesuai domain data yang ingin diimpor (Bahan Baku, Produk, Resep, atau Saldo Stok). Selalu gunakan template resmi Cooca agar nama kolom dan lembar kerja cocok sempurna.
                </p>
                <div class="pt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                    Template otomatis berisi data referensi toko
                </div>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-xs">2</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Isi Lembar Kerja Sesuai Panduan</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Buka lembar kerja <code>INPUT_*</code>. Masukkan kode SKU/barcode unik, nama barang, harga beli acuan, dan harga jual. Pastikan satuan (misal: <em>kg, gram, pcs</em>) sama persis dengan lembar <code>DATA_REFERENSI</code>.
                </p>
                <div class="pt-1 text-[11px] font-medium text-teal-600 dark:text-teal-400">
                    Mendukung ribuan baris data sekaligus
                </div>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-xs">3</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Unggah &amp; Periksa Preview Data</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Unggah berkas di menu <strong>Import Hub</strong> dan klik <strong>Unggah &amp; Pratinjau</strong>. Sistem memeriksa integritas format baris per baris. Jika ada harga kosong atau satuan tidak dikenal, baris akan ditandai dengan warna merah.
                </p>
                <div class="pt-1 text-[11px] font-medium text-cyan-600 dark:text-cyan-400">
                    100% aman: data belum disimpan ke database
                </div>
            </div>

            <!-- Step 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">4</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Pilih Strategi &amp; Eksekusi</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Tentukan tindakan jika ada barang kembar: <em>Lewati Duplikat</em> atau <em>Perbarui Data Lama</em>. Klik <strong>Eksekusi Impor</strong>. Data langsung aktif dan siap digunakan untuk transaksi kasir POS atau pemotongan resep otomatis.
                </p>
                <div class="pt-1 text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                    Proses instan &lt; 30 detik untuk 1.000 SKU
                </div>
            </div>
        </div>
    </div>

    <!-- 4. PETA 4 DOMAIN DATA UTAMA PADA IMPORT HUB -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="boxes" class="w-4 h-4 text-emerald-600"></i>
                    <span>Direktori 4 Domain Data yang Didukung Import Hub</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Panduan kolom dan tautan unduh template resmi per domain operasional bisnis Anda.</p>
            </div>
            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-md">4 Pintu Migrasi</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- 1. Bahan Baku -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="package-search" class="w-4 h-4 text-emerald-600"></i>
                            1. Bahan Baku
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Materials</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Impor daftar komoditas mentah seperti biji kopi, beras, tepung, kain, atau oli mesin.
                    </p>
                    <div class="text-[11px] font-mono text-black/50 dark:text-white/50 space-y-0.5 pt-1">
                        <div>&bull; Kolom: Kode SKU, Nama, Satuan, Kategori, Harga Beli Acuan, Stok Min.</div>
                    </div>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                    <a href="{{ route('import.materials.template') }}" class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                        <i data-lucide="download" class="w-3 h-3"></i>
                        <span>Unduh Template</span>
                    </a>
                    <a href="{{ route('import.index', ['tab' => 'materials']) }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline">
                        Impor &rarr;
                    </a>
                </div>
            </div>

            <!-- 2. Produk Jadi -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="shopping-bag" class="w-4 h-4 text-teal-600"></i>
                            2. Katalog Produk
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-500/10 text-teal-600 dark:text-teal-400">Products</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Impor menu restoran, pakaian, produk minimarket, atau paket layanan jasa yang dijual di POS.
                    </p>
                    <div class="text-[11px] font-mono text-black/50 dark:text-white/50 space-y-0.5 pt-1">
                        <div>&bull; Kolom: Barcode, Nama Produk, Kategori, Harga Jual, Tipe (Fisik/Jasa/Resep).</div>
                    </div>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                    <a href="{{ route('import.products.template') }}" class="text-[11px] font-bold text-teal-600 dark:text-teal-400 hover:underline flex items-center gap-1">
                        <i data-lucide="download" class="w-3 h-3"></i>
                        <span>Unduh Template</span>
                    </a>
                    <a href="{{ route('import.index', ['tab' => 'products']) }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline">
                        Impor &rarr;
                    </a>
                </div>
            </div>

            <!-- 3. Resep BOM -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="git-fork" class="w-4 h-4 text-amber-500"></i>
                            3. Formula Resep (BOM)
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">Recipes</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Tautkan produk jadi dengan takaran gramasi bahan baku penyusunnya untuk potong stok kasir otomatis.
                    </p>
                    <div class="text-[11px] font-mono text-black/50 dark:text-white/50 space-y-0.5 pt-1">
                        <div>&bull; Kolom: SKU Produk Jadi, SKU Bahan Baku, Kuantiti Bahan, Satuan Takaran.</div>
                    </div>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                    <a href="{{ route('import.recipes.template') }}" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                        <i data-lucide="download" class="w-3 h-3"></i>
                        <span>Unduh Template</span>
                    </a>
                    <a href="{{ route('import.index', ['tab' => 'recipes']) }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline">
                        Impor &rarr;
                    </a>
                </div>
            </div>

            <!-- 4. Saldo Awal Stok -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3 flex flex-col justify-between">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="layers" class="w-4 h-4 text-cyan-600"></i>
                            4. Saldo Awal Stok
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">Inventory</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Masukkan saldo awal stok fisik barang dan bahan di seluruh gudang/outlet cabang Anda saat pertama kali memakai Cooca.
                    </p>
                    <div class="text-[11px] font-mono text-black/50 dark:text-white/50 space-y-0.5 pt-1">
                        <div>&bull; Kolom: Kode SKU, Nama Outlet/Gudang, Kuantiti Fisik, Harga Pokok Awal.</div>
                    </div>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/10 flex items-center justify-between">
                    <a href="{{ route('import.inventory.template') }}" class="text-[11px] font-bold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1">
                        <i data-lucide="download" class="w-3 h-3"></i>
                        <span>Unduh Template</span>
                    </a>
                    <a href="{{ route('import.index', ['tab' => 'inventory']) }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline">
                        Impor &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. MATRIKS PENERAPAN NYATA UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base font-extrabold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-emerald-600"></i>
                    <span>Penerapan Import Massal pada 20 Jenis Usaha</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Skenario konkret migrasi daftar barang, formula bahan, dan saldo stok gudang di 6 klaster UMKM.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold border border-emerald-500/20 self-start sm:self-auto">
                20 Praktik Migrasi Cepat
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
                    <p><strong>1. Restoran Dine-In:</strong> Impor 150+ menu makanan, minuman, dan takaran bahan baku saus serta potongan daging ayam/sapi dalam 1 file Excel resep BOM.</p>
                    <p><strong>2. Coffee Shop &amp; Cafe:</strong> Unggah daftar varian biji kopi specialty, susu UHT/oatmilk, sirup perisa, cup plastik, dan sedotan sekaligus.</p>
                    <p><strong>3. Bakery &amp; Toko Roti:</strong> Impor formula resep adonan 50 jenis roti manis: takaran gramasi tepung cakra, ragi instan, gula pasir, dan butter anchor.</p>
                    <p><strong>4. Cloud Kitchen Online:</strong> Sinkronisasi ratusan item menu dari beberapa brand kuliner berbeda yang diproduksi di dapur terpusat.</p>
                    <p><strong>5. Katering Prasmanan:</strong> Impor paket menu prasmanan pernikahan per 100 porsi dan daftar bumbu dapur basah/kering partai besar.</p>
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
                    <p><strong>6. Konveksi &amp; Garment:</strong> Impor daftar roll kain katun combed, benang bordir, ritsleting YKK, serta BOM formula pemakaian kain per ukuran kaos (S/M/L/XL).</p>
                    <p><strong>7. Percetakan &amp; Offset:</strong> Impor master jenis kertas (Art Paper, HVS, Ivory), drum tinta CMYK, plat cetak CTP, dan jasa finishing jilid.</p>
                    <p><strong>8. Mebel &amp; Furniture:</strong> Impor kubikasi kayu jati oven, kaleng pernis melamin, paku tembak, dan aksesoris engsel pintu lemari.</p>
                    <p><strong>9. Bengkel Bubut &amp; Logam:</strong> Impor spesifikasi batang as baja, baut baja hitam, cairan pendingin coolant, dan mata pisau bubut industri.</p>
                    <p><strong>10. Kerajinan Tangan / Craft:</strong> Impor stok manik-manik hias, benang rajut wol, lem tembak, dan kardus packaging souvenir premium.</p>
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
                    <p><strong>11. Minimarket &amp; Kelontong:</strong> Impor massal 3.000+ SKU barang dagangan lengkap dengan barcode pabrik EAN-13 siap tembak scanner kasir.</p>
                    <p><strong>12. Apotek &amp; Toko Obat:</strong> Impor katalog obat paten, sirup anak, obat bebas, dan suplemen lengkap dengan nomor izin edar BPOM dan satuan multi-tier (Box/Strip/Tablet).</p>
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
                    <p><strong>13. Bengkel Mobil &amp; Motor:</strong> Impor master sparepart oli mesin, busi, kampas rem, filter udara, serta master paket jasa tune-up servis berkala.</p>
                    <p><strong>14. Barbershop &amp; Salon:</strong> Impor menu potong rambut, pewarnaan rambut, creambath, dan stok pemakaian botol pomade serta vitamin rambut.</p>
                    <p><strong>15. Laundry Kiloan:</strong> Impor paket cuci komplit, dry clean jas, serta stok persediaan jeriken deterjen cair dan parfum laundry.</p>
                    <p><strong>16. Cuci Mobil &amp; Detailing:</strong> Impor paket cuci hidrolik, bahan obat poles compound, cairan coating nano ceramic, dan lap microfiber.</p>
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
                    <p><strong>17. Kontraktor &amp; Renovasi:</strong> Impor daftar material semen zak, hebel bata ringan, pasir dump truck, dan besi beton per lonjor.</p>
                    <p><strong>18. Wedding Organizer:</strong> Impor katalog paket resepsi pernikahan lengkap dan inventaris perlengkapan dekorasi pelaminan yang disewakan.</p>
                    <p><strong>19. Digital IT &amp; Creative Agency:</strong> Impor paket layanan retainer media sosial, pembuatan website korporat, dan paket sewa server cloud bulanan.</p>
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
                    <p><strong>20. Distributor Grosir B2B &amp; Agrobisnis:</strong> Impor ribuan SKU produk pupuk pertanian, benih unggul, pakan ternak, dan beras karungan di multi-gudang regional dalam satu kali unggah.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. KEBIJAKAN OTORISASI RBAC & ANTI-MANIPULASI DATA -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Pemisahan Hak Akses (RBAC) &amp; Perlindungan Integritas Database</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Mekanisme keamanan ketat untuk mencegah staf toko memanipulasi harga katalog atau mereset saldo stok massal.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs pt-1">
            <div class="p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.04] dark:bg-emerald-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-emerald-700 dark:text-emerald-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Controller-Level RBAC Protection</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Akses eksekusi impor dilindungi izin manajerial: <code>materials.manage</code> untuk bahan baku, <code>products.manage</code> untuk produk &amp; resep, dan <code>inventory.manage</code> untuk saldo stok. Kasir dengan hak baca (read-only) dilarang keras mengunggah berkas.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.04] dark:bg-emerald-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-emerald-700 dark:text-emerald-400">
                    <i data-lucide="database-zap" class="w-4 h-4"></i>
                    <span>Transaksi Atomik (DB Transaction)</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap proses impor dieksekusi di dalam blok <code>DB::transaction(...)</code>. Jika koneksi terputus atau terdapat kegagalan baris di tengah proses, seluruh perubahan otomatis dibatalkan (rollback) tanpa meninggalkan data cacat separuh.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.04] dark:bg-emerald-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-emerald-700 dark:text-emerald-400">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span>Pencegahan Formula Injection (CSV)</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Saat memvalidasi sel spreadsheet, mesin impor Cooca membersihkan karakter berbahaya (seperti tanda sama dengan <code>=</code> atau tanda tambah <code>+</code> di awal nama barang) untuk mencegah eksekusi formula injection berbahaya pada komputer bisnis Anda.
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
                <span>Tanya Jawab Seputar Migrasi Data &amp; Import Excel (FAQ)</span>
            </h3>
            <span class="text-xs text-black/40 dark:text-white/40">5 Solusi Cepat</span>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 1 ? null : 1)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>1. Bagaimana jika file Excel saya susunan kolomnya berbeda dengan template Cooca?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 1" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Sistem Cooca mewajibkan penggunaan struktur kolom sesuai template resmi agar data dapat dipetakan secara akurat. Langkah termudah adalah mengunduh template resmi Cooca terlebih dahulu, lalu menyalin (<em>copy-paste</em>) kolom nama barang, harga, dan SKU dari file lama Anda ke kolom template Cooca yang bersesuaian.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 2 ? null : 2)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>2. Apa perbedaan opsi "Lewati Duplikat" (Skip) vs "Perbarui Data Lama" (Update)?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 2" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Jika kode SKU atau Barcode dalam file impor sudah terdaftar di database Cooca: opsi <strong>Lewati (Skip)</strong> akan mengabaikan baris tersebut dan mempertahankan data lama toko Anda. Sebaliknya, opsi <strong>Perbarui (Update)</strong> akan menimpa harga jual, harga beli, dan nama produk dengan nilai terbaru dari file Excel yang Anda unggah.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 3 ? null : 3)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>3. Mengapa muncul baris berwarna merah dengan status "Error" saat Preview Impor?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 3" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Baris merah menandakan data tidak memenuhi kriteria validasi: misalnya nama barang dikosongkan, harga bernilai negatif, atau satuan unit (misal: <em>kilo</em>) tidak terdaftar di lembar <code>DATA_REFERENSI</code> (seharusnya <em>kg</em>). Anda dapat memperbaiki sel tersebut di Excel dan mengunggah ulang berkasnya.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 4 ? null : 4)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>4. Berapa batas maksimal baris data yang bisa diunggah dalam satu kali proses impor?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 4" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Cooca mendukung hingga 5.000 baris data per berkas dengan ukuran file maksimal 5MB. Jika bisnis retail atau grosir Anda memiliki puluhan ribu SKU, bagi berkas menjadi beberapa file per kategori (misal: file Makanan Ringan, file Minuman, file Sabun) untuk memastikan proses pratinjau berjalan lancar.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 5 ? null : 5)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>5. Apakah nilai rupiah dari saldo stok yang saya impor otomatis masuk ke Laporan Neraca Keuangan?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 5" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Tepat sekali. Saat Anda mengimpor Saldo Awal Stok, sistem Cooca secara otomatis mengalikan jumlah fisik dengan harga pokok awal, mencatat pergerakan mutasi masuk, dan memperbarui akun <strong>Aset Persediaan Barang</strong> di Laporan Neraca Keuangan secara seimbang dengan akun Saldo Awal Ekuitas Modal.</p>
                </div>
            </div>
        </div>
    </div>

</section>
