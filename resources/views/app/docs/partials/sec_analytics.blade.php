<!-- ===================================================== -->
<!-- SECTION: MODUL ANALITIK & TREN PERTUMBUHAN BISNIS    -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-analytics" class="space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'analytics' || activeTab === 'finance') && matchesSearch('analitik analytics tren pertumbuhan omzet margin laba kotor kpi jam sibuk peak hours grafik produk terlaris metode bayar cabang 20 industri laporan performa kasir')">

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL ANALITIK -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 via-[#007AFF] to-indigo-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(0,122,255,0.35)] shrink-0">
                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Modul Analitik &amp; Tren Pertumbuhan</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">20 Sektor Industri</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Menganalisis performa penjualan, margin kotor, tren jam sibuk, dan kesehatan kas bisnis Anda secara real-time.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Analitik Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @can('reports.view')
                <a href="{{ route('analytics.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="line-chart" class="w-3.5 h-3.5"></i>
                    <span>Buka Dashboard Analitik</span>
                </a>
                <a href="{{ route('analytics.index', ['period' => 'today']) }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="clock-4" class="w-3.5 h-3.5 text-blue-500"></i>
                    <span>Jam Sibuk Hari Ini</span>
                </a>
                <a href="{{ route('reports.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-500"></i>
                    <span>Laporan Keuangan</span>
                </a>
            @else
                <span class="text-xs text-black/40 dark:text-white/40 italic font-medium">Hanya dapat diakses pemilik bisnis / otorisasi laporan</span>
            @endcan
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="activity" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Mengapa Pemilik Usaha Wajib Membaca Dashboard Analitik Cooca?</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Banyak pemilik usaha terjebak dalam ilusi <em>&ldquo;toko terlihat ramai, tapi kas di rekening tidak bertambah saat akhir bulan&rdquo;</em>. Modul Analitik Cooca mengeliminasi tebak-tebakan dengan menghubungkan setiap transaksi kasir, pengurangan bahan baku resep (BOM), dan biaya operasional toko menjadi satu kesatuan grafik yang mudah dibaca. Tanpa perlu membuka berkas spreadsheet rumit, Anda langsung mengetahui berapa rupiah margin bersih nyata yang dihasilkan hari ini, kapan jam puncak antrean kasir tiba, dan produk mana yang paling menguntungkan.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Manfaat Finansial & Operasional bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                    <span>Margin Kotor &amp; HPP Riil</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Memisahkan omzet kotor dari Harga Pokok Penjualan (HPP) riil bahan baku, sehingga Anda tahu persis berapa keuntungan kotor bersih yang layak dibawa pulang.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-[#007AFF]/[0.06] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 space-y-1.5">
                <div class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs sm:text-sm">
                    <i data-lucide="clock-alert" class="w-4 h-4"></i>
                    <span>Heatmap Jam Sibuk Kasir</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Grafik per jam 24/7 memperlihatkan titik waktu teramai pelanggan, memudahkan Anda mengatur shift staf, persiapan meja, dan kesiapan dapur tanpa kewalahan.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-purple-500/[0.06] dark:bg-purple-500/[0.1] border border-purple-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-purple-700 dark:text-purple-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    <span>Distribusi Metode Bayar</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Memantau perbandingan uang masuk lewat QRIS Dinamis, Transfer Bank, EDC Kartu, dan Tunai, sekaligus melacak piutang tempo kasir yang belum lunas.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                    <span>Peringkat 10 Produk Cuan</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Mengurutkan produk paling laris dan produk penyumbang margin tertinggi, membantu Anda menentukan menu unggulan dan menghentikan stok lambat bergerak (*slow-moving*).
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA PRAKTIS (4 LANGKAH PENGOPERASIAN) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white tracking-tight">
                    4 Langkah Praktis Mengambil Keputusan Bisnis Berbasis Data
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50">Panduan rutin mingguan bagi pemilik bisnis untuk mengevaluasi kesehatan omzet toko</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] self-start sm:self-auto">
                SOP Evaluasi Rutin
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Langkah 1 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                            <span>Filter Periode &amp; Pilih Cabang Toko</span>
                        </div>
                        @can('reports.view')
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <span>Buka Layar</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        @endcan
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Pilih rentang tanggal yang ingin dianalisis: <strong>Hari Ini</strong> untuk evaluasi shift harian, <strong>Pekan Ini</strong> untuk jadwal belanja supplier, <strong>Bulan Ini</strong> untuk evaluasi gaji &amp; sewa, atau <strong>Rentang Kustom</strong>. Bila Anda memiliki lebih dari satu toko/cabang, pilih cabang spesifik pada dropdown lokasi untuk membandingkan produktivitas antar-outlet.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Mendukung filter multi-cabang</span>
                    <span class="font-mono text-[10px] text-blue-600 dark:text-blue-400">Parameter: period &amp; location_id</span>
                </div>
            </div>

            <!-- Langkah 2 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xs font-bold">2</span>
                            <span>Membaca 4 Indikator Utama (KPI Cards)</span>
                        </div>
                        <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">Ringkasan Angka</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Tinjau 4 kartu metrik di bagian atas:
                        <br>&bull; <strong>Total Omzet Penjualan:</strong> Nilai kotor seluruh transaksi kasir &amp; faktur.
                        <br>&bull; <strong>Estimasi Margin Kotor:</strong> Omzet dikurangi HPP bahan baku modal.
                        <br>&bull; <strong>Rata-Rata Transaksi (AOV):</strong> Nilai rata-rata belanja per struk belanja pelanggan.
                        <br>&bull; <strong>Jumlah Transaksi Sukses:</strong> Total transaksi lunas tanpa nota batal/retur.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Dilengkapi persentase pertumbuhan</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">Vs Periode Sebelumnya</span>
                </div>
            </div>

            <!-- Langkah 3 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-purple-500/10 text-purple-600 flex items-center justify-center text-xs font-bold">3</span>
                            <span>Analisis Heatmap Jam Sibuk &amp; Tren Harian</span>
                        </div>
                        <span class="text-[11px] font-semibold text-purple-600 dark:text-purple-400">Pola Waktu</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Amati diagram balok per jam (00:00 - 23:00). Kolom tertinggi menandakan jam paling padat pelanggan (*peak hours*). Manfaatkan data ini untuk menugaskan kasir tambahan saat jam sibuk dan merencanakan waktu istirahat atau persiapan stok di luar jam tersebut agar antrean pelanggan tidak mengular.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Optimasi jadwal shift kerja</span>
                    <span class="font-semibold text-purple-600 dark:text-purple-400">Efisiensi Staf Kasir</span>
                </div>
            </div>

            <!-- Langkah 4 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center text-xs font-bold">4</span>
                            <span>Evaluasi Produk Cuan &amp; Ekspor Laporan</span>
                        </div>
                        @can('reports.view')
                            <a href="{{ route('reports.index') }}" class="text-[11px] font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                                <span>Laporan Detail</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        @endcan
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Gulir ke tabel <strong>Top 10 Produk Terlaris</strong>. Jangan hanya melihat produk dengan kuantiti terbanyak, perhatikan kolom kontribusi margin. Jika suatu produk terjual banyak namun marginnya tipis, pertimbangkan paket bundling (*upselling*) dengan minuman/aksesori bermargin tebal untuk mendongkrak profit toko.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Siap dicetak atau diunduh</span>
                    <span class="font-semibold text-amber-600 dark:text-amber-400">PDF &amp; Spreadsheet</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS PENERAPAN UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-6 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">Matriks Penerapan Analitik untuk 20 Jenis Usaha (6 Klaster)</h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">
                    Skenario riil pemanfaatan modul analitik dan keputusan taktis yang dapat diambil langsung oleh pemilik usaha di masing-masing bidang industri
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] shrink-0 self-start sm:self-auto">
                6 Klaster &bull; 20 Sektor Lengkap
            </span>
        </div>

        <div class="space-y-6">

            <!-- KLASTER 1: KULINER & F&B (5 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-amber-500/[0.03] dark:bg-amber-500/[0.05] border border-amber-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-amber-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="utensils" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 1: Kuliner &amp; Food &amp; Beverage (5 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Jam makan siang vs malam, food cost HPP bumbu, meja terlaris, dan paket katering</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-400">5 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">
                    <!-- 1. Restoran Dine-In -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">1. Restoran Dine-In</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Meja &amp; Jam Makan</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Membaca lonjakan jam makan siang (12:00–14:00) vs makan malam (19:00–21:30) untuk mengatur pembagian staf pelayan meja dan juru masak dapur.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Jika rata-rata struk (AOV) di jam makan siang rendah, buat promo paket makan siang hemat (*lunch combo*) dengan margin minuman manis yang tinggi.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="line-chart" class="w-3 h-3"></i>
                                <span>Cek Jam Sibuk Resto</span>
                            </a>
                        </div>
                    </div>

                    <!-- 2. Coffee Shop & Cafe -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">2. Coffee Shop &amp; Cafe</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Kopi &amp; Add-on</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Mengetahui minuman signature paling laris dan kontribusi ekstra shot/oatmilk terhadap margin kotor harian.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Evaluasi jam sepi (14:00–16:00) untuk memberlakukan program *Happy Hour* kopi susu guna menjaga perputaran kas harian.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="coffee" class="w-3 h-3"></i>
                                <span>Analisis Menu Signature</span>
                            </a>
                        </div>
                    </div>

                    <!-- 3. Bakery & Toko Roti -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">3. Bakery &amp; Toko Roti</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Produksi &amp; Kedaluwarsa</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Menghindari sisa roti basi di rak pajangan dengan mencocokkan jumlah produksi harian terhadap tren penjualan hari sebelumnya.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Turunkan kuota oven untuk varian roti yang grafik penjualannya melemah, alihkan bahan baku ke roti tawar yang perputarannya cepat.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="trending-up" class="w-3 h-3"></i>
                                <span>Peringkat Roti Terlaris</span>
                            </a>
                        </div>
                    </div>

                    <!-- 4. Cloud Kitchen Online -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">4. Cloud Kitchen Online</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Pesanan Takeaway</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Memantau lonjakan orderan kurir online di jam makan malam dan rasio biaya kemasan box makanan terhadap omzet.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Siapkan wadah saus dan bahan setengah matang 30 menit sebelum lonjakan kurir online tiba untuk mempercepat waktu serah terima.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="package" class="w-3 h-3"></i>
                                <span>Tren Orderan Online</span>
                            </a>
                        </div>
                    </div>

                    <!-- 5. Katering Prasmanan -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">5. Katering Prasmanan</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Invoice DP &amp; Lunas</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Memantau realisasi kas masuk dari pelunasan invoice katering hajatan/kantor vs belanja bahan basah di pasar induk.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Periksa grafik distribusi metode pembayaran untuk memastikan uang muka (DP) pelanggan sudah masuk ke rekening bank sebelum belanja dapur dimulai.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="calendar-check" class="w-3 h-3"></i>
                                <span>Pantau Kas Katering</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KLASTER 2: MANUFAKTUR & HPP (5 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-purple-500/[0.03] dark:bg-purple-500/[0.05] border border-purple-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-purple-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="factory" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 2: Manufaktur &amp; HPP Bengkel Produksi (5 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: HPP bahan baku rol/lembar, ongkos tukang, efisiensi mesin, dan termin borongan</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-700 dark:text-purple-400">5 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">
                    <!-- 6. Konveksi & Garment -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">6. Konveksi &amp; Garment</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Rol Kain &amp; Jahit</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Membandingkan omzet pesanan seragam instansi vs ongkos potong, jahit, dan kancing per lusin.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Pantau margin kotor per artikel; jika pesanan polo bordir menghasilkan margin 45% sedangkan kaos oblong hanya 18%, fokuskan pemasaran ke pesanan polo seragam.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="scissors" class="w-3 h-3"></i>
                                <span>Margin Per Kategori Seragam</span>
                            </a>
                        </div>
                    </div>

                    <!-- 7. Percetakan & Offset Digital -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">7. Percetakan &amp; Offset</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Kertas &amp; Tinta</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Mengetahui rasio cetak spanduk outdoor banner vs cetak brosur A3+ yang menyumbang kas masuk terbesar.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Bila nilai rata-rata transaksi (AOV) percetakan digital cenderung kecil, dorong staf kasir untuk menawarkan laminasi doff/glossy dan finishing potong sudut.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="printer" class="w-3 h-3"></i>
                                <span>Evaluasi Produk Percetakan</span>
                            </a>
                        </div>
                    </div>

                    <!-- 8. Mebel & Furniture Kayu -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">8. Mebel &amp; Furniture Kayu</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Kayu &amp; Finishing</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Melacak laba riil pembuatan pesanan kitchen set kustom setelah memperhitungkan bahan multipleks, HPL, lem, dan upah tukang.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Periksa pertumbuhan penjualan bulanan untuk memastikan lead-time produksi tidak menumpuk di gudang workshop.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="hammer" class="w-3 h-3"></i>
                                <span>Profitabilitas Pesanan Mebel</span>
                            </a>
                        </div>
                    </div>

                    <!-- 9. Bengkel Bubut & Logam -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">9. Bengkel Bubut &amp; Logam</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Sparepart Custom</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Menghitung rasio pendapatan jasa bubut mesin vs penjualan material besi as dan plat stainless steel.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Pantau grafik mingguan untuk mengalokasikan jam lembur teknisi operator bubut saat menerima pesanan sparepart pabrikasi mendesak.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="cog" class="w-3 h-3"></i>
                                <span>Kinerja Jasa Bubut</span>
                            </a>
                        </div>
                    </div>

                    <!-- 10. Kerajinan Tangan / Craft -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">10. Kerajinan Tangan / Craft</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Souvenir &amp; Ekspor</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Mengidentifikasi produk souvenir pernikahan atau kerajinan anyaman yang memberikan margin tertinggi untuk penjualan pameran.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Amati lonjakan penjualan musiman untuk mempersiapkan stok bahan baku serat alami 2 bulan sebelum musim pernikahan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="sparkles" class="w-3 h-3"></i>
                                <span>Peringkat Cuan Souvenir</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KLASTER 3: RITEL & APOTEK (2 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-blue-500/[0.03] dark:bg-blue-500/[0.05] border border-blue-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-blue-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="store" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 3: Ritel Cepat &amp; Farmasi Apotek (2 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Perputaran ribuan SKU, jam pulang kantor kasir, dan kontribusi obat generik vs paten</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-700 dark:text-blue-400">2 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 text-xs">
                    <!-- 11. Minimarket & Kelontong -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">11. Minimarket &amp; Kelontong</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Multi SKU &amp; Scan Cepat</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Memantau jam belanja warga sepulang kantor (17:00–20:00) dan mengevaluasi produk fast-moving sembako vs produk snack bermargin tebal.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Jika heatmap jam sibuk menunjukkan antrean menumpuk di petang hari, pastikan kasir kedua aktif dan stok galon/beras di etalase depan sudah penuh.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="shopping-cart" class="w-3 h-3"></i>
                                <span>Jam Sibuk Kasir Minimarket</span>
                            </a>
                        </div>
                    </div>

                    <!-- 12. Apotek & Toko Obat -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">12. Apotek &amp; Toko Obat</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Resep Dokter &amp; OTC</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Menganalisis proporsi penjualan obat bebas (OTC) vs obat resep dokter dan suplemen vitamin yang memberikan margin kotor tertinggi.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Pasang suplemen daya tahan tubuh terlaris di meja kasir apotek untuk meningkatkan nilai rata-rata struk (AOV) setiap pasien.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="pill" class="w-3 h-3"></i>
                                <span>Analisis Penjualan Obat</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KLASTER 4: JASA HARIAN & WORKSHOP (4 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-sky-500/[0.03] dark:bg-sky-500/[0.05] border border-sky-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-sky-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="wrench" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 4: Jasa Pelayanan Harian &amp; Workshop (4 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Rasio jasa teknisi vs sparepart, lonjakan hari Sabtu-Minggu, dan kapasitas kursi/stall</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-500/10 text-sky-700 dark:text-sky-400">4 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 text-xs">
                    <!-- 13. Bengkel Mobil & Motor -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">13. Bengkel Kendaraan</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">PKB Nopol &amp; Oli</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Memisahkan omzet jasa servis tune-up mekanik dari penjualan suku cadang (oli, kampas rem, ban) untuk memastikan kedua sumber pendapatan seimbang.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Jika omzet suku cadang lebih dominan dari jasa, beri insentif mekanik untuk melakukan pemeriksaan komprehensif 20 titik keselamatan kendaraan pelanggan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="wrench" class="w-3 h-3"></i>
                                <span>Margin Servis vs Sparepart</span>
                            </a>
                        </div>
                    </div>

                    <!-- 14. Barbershop & Salon -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">14. Barbershop &amp; Salon</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Kursi Potong &amp; Pomade</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Mengamati tren kunjungan pelanggan pria/wanita di akhir pekan dan penjualan produk styling rambut (pomade, tonik, vitamin).
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Evaluasi kapasitas kursi kapster di hari Jumat malam dan Sabtu untuk meminimalkan waktu tunggu pelanggan yang berisiko batal potong.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="scissors" class="w-3 h-3"></i>
                                <span>Kepadatan Kursi Barbershop</span>
                            </a>
                        </div>
                    </div>

                    <!-- 15. Laundry Kiloan & Satuan -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">15. Laundry Kiloan &amp; Satuan</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Kilogram &amp; Dry Clean</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Membandingkan volume kilogram cucian harian vs pendapatan paket cuci kilat/ekspres dan dry clean bed cover.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Analisis hari dengan timbangan masuk terberat (biasanya Senin pagi) untuk memastikan stok deterjen cair dan gas pengering boiler tidak kehabisan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="waves" class="w-3 h-3"></i>
                                <span>Volume Timbangan Laundry</span>
                            </a>
                        </div>
                    </div>

                    <!-- 16. Cuci Mobil & Detailing -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">16. Cuci Mobil &amp; Detailing</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Antrean Stall &amp; Coating</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Mengetahui pengaruh cuaca hujan/panas terhadap antrean stall hidrolik serta kontribusi paket poles bodi/coating interior.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Jika jumlah kendaraan cuci salju reguler sedang menurun di musim kemarau, gencarkan paket detailing jamur kaca dan pembersihan ruang mesin.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="car" class="w-3 h-3"></i>
                                <span>Kunjungan Stall Cuci</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KLASTER 5: JASA PROYEK & KONTRAKTOR (3 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-emerald-500/[0.03] dark:bg-emerald-500/[0.05] border border-emerald-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-emerald-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 5: Jasa Proyek, Event &amp; Agensi (3 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Tagihan invoice termin bertahap, realisasi biaya belanja vs RAB, dan margin per project</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">3 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
                    <!-- 17. Kontraktor & Renovasi -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">17. Kontraktor &amp; Bangunan</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Termin &amp; Belanja Fisik</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Memantau pencairan tagihan invoice termin proyek renovasi dan membandingkannya dengan pengeluaran riil material semen/besi di lapangan.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Periksa margin bersih kumulatif agar proyek tidak mengalami kebocoran biaya di tengah proses konstruksi berjalan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="building" class="w-3 h-3"></i>
                                <span>Realisasi Termin Proyek</span>
                            </a>
                        </div>
                    </div>

                    <!-- 18. Event & Wedding Organizer -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">18. Wedding Organizer</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Paket Pernikahan</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Mengevaluasi paket pernikahan all-in yang paling diminati calon pengantin dan margin keuntungan bersih setelah memotong sewa dekor &amp; MUA.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Pantau tren musiman bulan pernikahan untuk mengatur strategi diskon booking awal (*early bird*).
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                <span>Tren Paket Wedding</span>
                            </a>
                        </div>
                    </div>

                    <!-- 19. Digital IT & Creative Agency -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">19. Digital Creative Agency</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Retainer &amp; Campaign</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Owner:</strong> Menganalisis pendapatan bulanan berulang (*recurring retainer*) jasa kelola medsos vs proyek one-off pembuatan website/video komersial.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Tindakan Taktis:</strong> Jaga rasio klien retainer minimal 60% dari total omzet untuk menstabilkan arus kas pembayaran gaji tim kreatif.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="monitor" class="w-3 h-3"></i>
                                <span>Analisis Retainer Klien</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KLASTER 6: DISTRIBUSI & AGROBISNIS (1 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-teal-500/[0.03] dark:bg-teal-500/[0.05] border border-teal-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-teal-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="truck" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 6: Distribusi B2B &amp; Rantai Pasok Agro (1 Sektor Induk)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Volume grosir kartonan, perputaran piutang toko mitra, dan hasil panen musiman</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/10 text-teal-700 dark:text-teal-400">1 Sektor</span>
                </div>

                <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-xs">20. Distributor Grosir B2B &amp; Agrobisnis</span>
                        <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Surat Jalan &amp; Grosir</span>
                    </div>
                    <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                        <strong>Kebutuhan Owner:</strong> Memantau perputaran stok pupuk, pakan ternak, atau sembako grosir kartonan antar-gudang dan rasio pembayaran tunai vs piutang tempo 14–30 hari dari warung mitra.
                    </p>
                    <p class="text-[11px] text-black/70 dark:text-white/70">
                        <strong>Tindakan Taktis:</strong> Jika metode pembayaran piutang kasbon toko mitra melebihi 40% dari omzet bulan berjalan, segera perketat plafon kredit toko sebelum mengirim pesanan berikutnya.
                    </p>
                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                        <span class="text-[11px] text-black/50 dark:text-white/50">Manajemen piutang toko mitra</span>
                        <a href="{{ route('analytics.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <i data-lucide="truck" class="w-3 h-3"></i>
                            <span>Evaluasi Distribusi Grosir</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 5. KEAMANAN, OTORISASI AKSES (RBAC) & ANTI-FRAUD -->
    <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3 pb-2 border-b border-black/5 dark:border-white/10">
            <span class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Privasi Finansial &amp; Proteksi Rahasia Margin (RBAC)</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Menjaga kerahasiaan keuntungan riil bisnis dari pandangan kasir dan staf operasional</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
            <div class="p-3.5 rounded-xl bg-emerald-500/[0.04] dark:bg-emerald-500/[0.08] border border-emerald-500/15 space-y-1">
                <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-bold">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Isolasi Laba Bersih untuk Owner</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Akses ke menu analitik dan pembacaan persentase margin kotor dilindungi hak akses ketat (<code>reports.view</code> / <code>analytics.view</code>). Akun staf kasir biasa tidak dapat melihat total saldo laba toko.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-[#007AFF]/[0.04] dark:bg-[#007AFF]/[0.08] border border-[#007AFF]/15 space-y-1">
                <div class="flex items-center gap-1.5 text-[#007AFF] dark:text-[#0A84FF] font-bold">
                    <i data-lucide="database" class="w-4 h-4"></i>
                    <span>Multi-Tenant Strict Isolation</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap kalkulasi transaksi diisolasi secara permanen berdasarkan <code>business_id</code> aktif. Tidak ada celah data performa toko Anda tercampur atau terbaca oleh tenant bisnis lain di Cooca ERP.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-purple-500/[0.04] dark:bg-purple-500/[0.08] border border-purple-500/15 space-y-1">
                <div class="flex items-center gap-1.5 text-purple-700 dark:text-purple-400 font-bold">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span>Pencegahan Manipulasi Laporan</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Data analitik ditarik langsung dari tabel transaksi resmi (order pos dan invoice). Jika kasir menghapus atau membatalkan nota tanpa izin, log sistem mencatat anomali tersebut secara otomatis.
                </p>
            </div>
        </div>
    </div>

    <!-- 6. TANYA JAWAB BISNIS OWNER (FAQ) -->
    <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-500 flex items-center justify-center font-bold text-xs">
                    <i data-lucide="help-circle" class="w-4 h-4"></i>
                </span>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Tanya Jawab Pemilik Usaha Seputar Analitik</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">Pertanyaan umum seputar keakuratan angka, pembaruan real-time, dan akses multi-cabang</p>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">1. Mengapa omzet toko saya terlihat tinggi di kasir tetapi estimasi margin kotor di dashboard analitik relatif kecil?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Hal ini terjadi karena modul analitik secara cerdas mengurangkan <strong>Harga Pokok Penjualan (HPP) modal bahan baku</strong> dari total omzet kotor. Jika toko Anda menjual produk dengan harga yang terlalu dekat dengan biaya modal bahan basah atau bahan baku, margin kotor akan terlihat tipis. Gunakan data tabel produk cuan untuk menaikkan harga jual produk tersebut atau mencari supplier bahan baku yang lebih hemat.
                </p>
            </div>

            <!-- FAQ 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">2. Apakah staf kasir atau pelayan saya bisa melihat angka keuntungan bersih ini di HP mereka?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    <strong>Tidak bisa.</strong> Sistem Cooca memisahkan hak akses peran (*Role-Based Access Control*). Kasir hanya diizinkan melihat data perputaran uang modal shift mereka sendiri saat buka dan tutup kasir. Seluruh grafik laba kotor, margin bisnis, dan analitik multi-cabang terproteksi dan hanya terbuka bagi peran Pemilik Usaha (*Business Owner*) atau Manajer Keuangan.
                </p>
            </div>

            <!-- FAQ 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">3. Seberapa sering data analitik ini diperbarui? Apakah real-time?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Data analitik Cooca diperbarui secara <strong>instan dan real-time</strong>. Begitu kasir menekan tombol bayar dan nota transaksi tercetak di mesin kasir POS, angka omzet, grafik per jam, dan pemotongan stok bahan langsung teragregasi secara otomatis tanpa perlu menunggu proses tutup buku malam hari.
                </p>
            </div>

            <!-- FAQ 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">4. Bisakah saya membandingkan performa penjualan antara cabang toko A dan cabang B?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sangat bisa. Anda cukup memilih nama cabang pada dropdown <strong>Semua Cabang / Pilih Lokasi</strong> di bilah atas dashboard analitik. Sistem akan secara otomatis memfilter seluruh metrik, grafik jam sibuk, dan daftar produk terlaris khusus untuk cabang terpilih, memudahkan Anda mengevaluasi outlet mana yang paling produktif.
                </p>
            </div>
        </div>
    </div>

</section>
