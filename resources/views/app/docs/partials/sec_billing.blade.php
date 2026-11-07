<!-- ===================================================== -->
<!-- SECTION: MODUL PAKET LANGGANAN, KUOTA & BILLING      -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-billing" class="space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'billing' || activeTab === 'system') && matchesSearch('billing langganan kuota paket free solo standard premium prestige tripay qris transfer va bank virtual account buffer darurat faktur invoice pajak perpanjang kapasitas penyimpanan storage token ai')">

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL BILLING -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-600 via-[#5856D6] to-pink-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(88,86,214,0.35)] shrink-0">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Modul Paket Langganan, Kuota &amp; Billing</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20">TriPay Instant 24/7</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Transparansi paket langganan Cooca, pembayaran instan via QRIS/VA Bank, dan manajemen kuota penyimpanan awan tanpa biaya tersembunyi.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Billing Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @can('billing.view')
                <a href="{{ route('billing.limits') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#5856D6] hover:bg-[#4845B4] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(88,86,214,0.3)] transition active:scale-95">
                    <i data-lucide="gauge" class="w-3.5 h-3.5"></i>
                    <span>Cek Kuota &amp; Kapasitas</span>
                </a>
                <a href="{{ route('billing.history') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="receipt" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400"></i>
                    <span>Riwayat Pembayaran</span>
                </a>
            @endcan
            @can('billing.manage')
                <a href="{{ route('billing.checkout') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="zap" class="w-3.5 h-3.5 text-pink-500"></i>
                    <span>Pilih Paket &amp; Upgrade</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-purple-600 to-pink-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Ekosistem Billing Fleksibel Tanpa Biaya Tersembunyi</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem langganan Cooca dibangun dengan filosofi <strong>mendukung pertumbuhan UMKM dari nol</strong>. Tersedia 4 tingkatan kapasitas yang transparan: mulai dari <strong>Free Solo</strong> untuk perintis usaha tunggal, <strong>Standard</strong> untuk toko kasir harian, <strong>Premium</strong> untuk multi-cabang &amp; manufaktur, hingga <strong>Prestige</strong> untuk distributor skala besar. Setiap pembayaran terintegrasi otomatis 24 jam sehari via gateway TriPay (QRIS Dinamis dan Virtual Account Bank BCA, Mandiri, BNI, BRI, Permata). Anda tidak perlu menunggu konfirmasi admin manual, dan yang terpenting: kasir toko Anda <strong>dijamin tetap bisa memproses transaksi darurat melalui sistem Emergency Buffer</strong> meski kuota bulanan sementara tercapai.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Manfaat Finansial & Operasional bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-purple-500/[0.06] dark:bg-purple-500/[0.1] border border-purple-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-purple-700 dark:text-purple-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <span>4 Tingkat Paket Adil</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Free Solo, Standard, Premium, dan Prestige. Bayar sesuai kapasitas toko yang Anda butuhkan tanpa dipaksa membayar fitur yang tidak terpakai.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    <span>Aktivasi Instan 24/7 TriPay</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Scan QRIS langsung dari HP atau transfer nomor VA Bank. Paket langsung aktif otomatis dalam 10 detik tanpa kirim bukti foto WhatsApp.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-[#007AFF]/[0.06] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 space-y-1.5">
                <div class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs sm:text-sm">
                    <i data-lucide="shield-plus" class="w-4 h-4"></i>
                    <span>Buffer Darurat Transaksi</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Toko sedang ramai dan kuota pas habis? Kasir tetap bisa melayani struk transaksi darurat tanpa terblokir di depan pelanggan antre.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="database" class="w-4 h-4"></i>
                    <span>Jaminan Data Aman Utuh</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Tidak ada penghapusan data sepihak (*No-Data-Punishment*). Riwayat penjualan, master produk, dan laporan masa lalu Anda tetap tersimpan utuh.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA PRAKTIS (4 LANGKAH PENGOPERASIAN) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white tracking-tight">
                    4 Langkah Memilih Paket &amp; Melakukan Pembayaran Otomatis
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50">Prosedur ringkas perpanjangan langganan dan pencetakan faktur resmi untuk laporan pajak</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 self-start sm:self-auto">
                SOP Billing &amp; Checkout
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Langkah 1 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-purple-500/10 text-purple-600 flex items-center justify-center text-xs font-bold">1</span>
                            <span>Cek Status Penggunaan Kuota Toko</span>
                        </div>
                        @can('billing.view')
                            <a href="{{ route('billing.limits') }}" class="text-[11px] font-semibold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                                <span>Cek Kuota</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        @endcan
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Buka menu <strong>Langganan &amp; Kuota</strong>. Periksa meteran pemakaian kapasitas: total transaksi POS bulan berjalan, jumlah produk aktif, jumlah outlet/cabang, kapasitas penyimpanan berkas awan (*storage*), dan sisa kuota token asisten AI. Gunakan tombol hitung ulang (*recalculate*) bila Anda baru saja menghapus berkas lampiran lama.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Meteran visual real-time</span>
                    <span class="font-mono text-[10px] text-purple-600 dark:text-purple-400">Rute: billing.limits</span>
                </div>
            </div>

            <!-- Langkah 2 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-xs font-bold">2</span>
                            <span>Pilih Paket &amp; Masukkan Kode Promo</span>
                        </div>
                        @can('billing.manage')
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <span>Checkout</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        @endcan
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Buka layar <strong>Pilih Paket</strong>. Tentukan paket yang pas dengan volume penjualan Anda (Standard, Premium, atau Prestige). Pilih siklus penagihan bulanan atau tahunan (diskon hemat s/d 20%). Bila Anda memiliki kode voucher diskon dari event komunitas Cooca, masukkan di kolom kode promo untuk potongan langsung.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Validasi promo otomatis</span>
                    <span class="font-bold text-[#007AFF]">Hemat Tahunan 20%</span>
                </div>
            </div>

            <!-- Langkah 3 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xs font-bold">3</span>
                            <span>Bayar Instan via QRIS Dinamis atau VA Bank</span>
                        </div>
                        <span class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">Gerbang TriPay</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Pilih saluran pembayaran favorit Anda: <strong>QRIS</strong> untuk scan dari aplikasi m-banking/e-wallet (GoPay, OVO, ShopeePay, Dana, BCA Mobile) atau <strong>Virtual Account</strong> bank resmi (BCA, Mandiri, BNI, BRI, Permata). Sistem menampilkan QR code atau nomor rekening VA unik yang aktif seketika.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Konfirmasi webhook otomatis</span>
                    <span class="font-semibold text-emerald-600 dark:text-emerald-400">Verifikasi 10 Detik</span>
                </div>
            </div>

            <!-- Langkah 4 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-pink-500/10 text-pink-600 flex items-center justify-center text-xs font-bold">4</span>
                            <span>Unduh Bukti Faktur Resmi untuk Pembukuan</span>
                        </div>
                        @can('billing.view')
                            <a href="{{ route('billing.history') }}" class="text-[11px] font-semibold text-pink-600 dark:text-pink-400 hover:underline flex items-center gap-1">
                                <span>Riwayat Faktur</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        @endcan
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Setelah pembayaran berhasil, layar otomatis beralih dan paket baru Anda langsung aktif. Buka menu <strong>Riwayat Pembayaran</strong> untuk mengunduh berkas <strong>Faktur Resmi (Invoice PDF)</strong> bertanda tangan digital Cooca yang sah digunakan sebagai bukti biaya operasional perangkat lunak untuk pembukuan pajak usaha Anda.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Format PDF siap cetak</span>
                    <span class="font-semibold text-pink-600 dark:text-pink-400">Bukti Pengurang Pajak</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS PENERAPAN UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-6 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">Matriks Rekomendasi Paket Langganan untuk 20 Sektor Industri</h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">
                    Panduan memilih tingkat paket langganan dan fitur kunci yang paling menguntungkan sesuai karakteristik bisnis Anda di Cooca ERP
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 shrink-0 self-start sm:self-auto">
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
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Kecepatan transaksi POS meja, tiket dapur KDS, dan kuota resep bahan baku basah</p>
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
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-[10px]">Paket Premium</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Ratusan pesanan meja harian, multi-printer kasir + dapur KDS, serta integrasi stok resep bumbu gramasi.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Transaksi kasir POS tanpa batas dan kapasitas multi-meja lantai 1 &amp; lantai 2 tanpa lag.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                <span>Pilih Paket Restoran</span>
                            </a>
                        </div>
                    </div>

                    <!-- 2. Coffee Shop & Cafe -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">2. Coffee Shop &amp; Cafe</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-[10px]">Paket Standard</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Transaksi cepat QRIS di meja bar kopi, varian beans espresso, dan kartu member loyalitas pelanggan setia.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Kuota hingga 2.500 transaksi per bulan sangat ekonomis untuk kedai kopi modern dengan 2–3 barista.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="coffee" class="w-3 h-3"></i>
                                <span>Pilih Paket Cafe</span>
                            </a>
                        </div>
                    </div>

                    <!-- 3. Bakery & Toko Roti -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">3. Bakery &amp; Toko Roti</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-[10px]">Paket Standard</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Barcode scan roti satuan di etalase, pemotongan bahan baku terigu/ragi, dan cetak label kedaluwarsa.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Penyimpanan foto katalog kue ulang tahun tart dan pencatatan varian ukuran loyang.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="cake" class="w-3 h-3"></i>
                                <span>Pilih Paket Bakery</span>
                            </a>
                        </div>
                    </div>

                    <!-- 4. Cloud Kitchen Online -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">4. Cloud Kitchen Online</span>
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-[10px]">Paket Premium</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Mengelola beberapa brand kuliner berbeda dalam 1 dapur bersama (*multi-brand ghost kitchen*).
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Dukungan multi-brand, ribuan pesanan kurir takeaway bulanan, dan buffer darurat saat promo flash sale.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="utensils-crossed" class="w-3 h-3"></i>
                                <span>Pilih Paket Cloud Kitchen</span>
                            </a>
                        </div>
                    </div>

                    <!-- 5. Katering Prasmanan -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">5. Katering Prasmanan</span>
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-[10px]">Paket Premium</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Penerbitan faktur invoice bertahap (DP, termin 2, pelunasan) dan pencatatan belanja pasar basah besar.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Ekspor invoice PDF katering tanpa watermark dan integrasi WhatsApp reminder jatuh tempo tagihan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="clipboard-list" class="w-3 h-3"></i>
                                <span>Pilih Paket Katering</span>
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
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Penyimpanan awan file desain cetak/pola, multi-gudang bahan baku, dan formula HPP bertingkat</p>
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
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-[10px]">Paket Premium</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Menyimpan berkas mockup desain sablon, pola potong baju, dan surat jalan pengiriman lusinan kaos.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Kapasitas penyimpanan awan 5 GB + fitur BOM formula kain multi-level.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="scissors" class="w-3 h-3"></i>
                                <span>Pilih Paket Konveksi</span>
                            </a>
                        </div>
                    </div>

                    <!-- 7. Percetakan & Offset Digital -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">7. Percetakan &amp; Offset</span>
                                <span class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold text-[10px]">Paket Prestige</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Ratusan file artwork cetak resolusi tinggi (PDF, TIFF) dari pelanggan setiap harinya.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Kapasitas penyimpanan 20 GB dan opsi add-on storage fleksibel dengan harga terjangkau.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="printer" class="w-3 h-3"></i>
                                <span>Pilih Paket Percetakan</span>
                            </a>
                        </div>
                    </div>

                    <!-- 8. Mebel & Furniture Kayu -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">8. Mebel &amp; Furniture Kayu</span>
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-[10px]">Paket Premium</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Mengunggah foto 3D render pesanan lemari/meja dan arsip kontrak SPK pembeli.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Multi-user staf (admin showroom + mandor workshop) dalam 1 database sinkron.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="hammer" class="w-3 h-3"></i>
                                <span>Pilih Paket Mebel</span>
                            </a>
                        </div>
                    </div>

                    <!-- 9. Bengkel Bubut & Logam -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">9. Bengkel Bubut &amp; Logam</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-[10px]">Paket Standard</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Pembuatan nota tagihan jasa bubut gear mesin dan pelacakan pembayaran tempo bengkel mitra.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Dukungan pencatatan piutang pelanggan dan cetak kuitansi resmi bertanda tangan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="cog" class="w-3 h-3"></i>
                                <span>Pilih Paket Bengkel Bubut</span>
                            </a>
                        </div>
                    </div>

                    <!-- 10. Kerajinan Tangan / Craft -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">10. Kerajinan Tangan / Craft</span>
                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-[10px]">Paket Free Solo</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Perajin mandiri yang baru merintis jualan produk kerajinan di bazaar atau pameran lokal.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Gratis selamanya dengan kapasitas 100 produk dan kasir POS tanpa biaya bulanan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="sparkles" class="w-3 h-3"></i>
                                <span>Mulai Paket Gratis</span>
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
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Kapasitas master produk hingga puluhan ribu SKU dan ribuan struk kasir per hari</p>
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
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-[10px]">Paket Premium</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Menampung 5.000–10.000 SKU master barang sembako, snack, dan scan barcode kilat di 2 komputer kasir.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Transaksi tanpa kuota dan integrasi printer barcode thermal label rak toko.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="shopping-cart" class="w-3 h-3"></i>
                                <span>Pilih Paket Minimarket</span>
                            </a>
                        </div>
                    </div>

                    <!-- 12. Apotek & Toko Obat -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">12. Apotek &amp; Toko Obat</span>
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-[10px]">Paket Premium</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Pencatatan ribuan nomor batch obat, tanggal kedaluwarsa (*expired date*), dan faktur pembelian dari PBF.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Multi-satuan konversi (Box &rarr; Strip &rarr; Tablet) dan proteksi audit data BPOM.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="pill" class="w-3 h-3"></i>
                                <span>Pilih Paket Apotek</span>
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
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Penyimpanan foto kondisi fisik kendaraan/cucian dan broadcast WhatsApp notifikasi selesai</p>
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
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-[10px]">Paket Premium</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Mengunggah foto kilometer odometer mobil dan riwayat PKB kendaraan pelanggan yang rutin servis.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Penyimpanan foto awan lega dan integrasi modul pengingat servis otomatis WhatsApp.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="wrench" class="w-3 h-3"></i>
                                <span>Pilih Paket Bengkel</span>
                            </a>
                        </div>
                    </div>

                    <!-- 14. Barbershop & Salon -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">14. Barbershop &amp; Salon</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-[10px]">Paket Standard</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Komisi kapster per kepala potong dan manajemen nomor antrean pelanggan saat akhir pekan.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Perhitungan komisi gaji otomatis untuk 5 kapster dan nota struk Bluetooth kasir.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="scissors" class="w-3 h-3"></i>
                                <span>Pilih Paket Barbershop</span>
                            </a>
                        </div>
                    </div>

                    <!-- 15. Laundry Kiloan & Satuan -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">15. Laundry Kiloan &amp; Satuan</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-[10px]">Paket Standard</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Cetak nota nomor rak cucian dan pengiriman notifikasi otomatis <em>&ldquo;Cucian Anda sudah selesai disetrika&rdquo;</em>.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Kuota pesan WhatsApp gateway dan pencatatan kasir timbangan kilogram.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="waves" class="w-3 h-3"></i>
                                <span>Pilih Paket Laundry</span>
                            </a>
                        </div>
                    </div>

                    <!-- 16. Cuci Mobil & Detailing -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">16. Cuci Mobil &amp; Detailing</span>
                                <span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold text-[10px]">Paket Standard</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Pencatatan nopol mobil masuk stall dan komisi tim cuci berdasarkan jumlah kendaraan harian.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Rekap pembagian komisi cuci harian transparan tanpa selisih hitungan manual.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="car" class="w-3 h-3"></i>
                                <span>Pilih Paket Cuci Mobil</span>
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
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Lampiran kontrak SPK resmi, invoice termin bertahap, dan branding faktur kustom</p>
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
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-[10px]">Paket Premium</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Mengunggah foto opname progres fisik renovasi dan arsip kuitansi toko bahan bangunan.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Pembuatan invoice termin profesional dan modul akuntansi pencatatan biaya proyek.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="building" class="w-3 h-3"></i>
                                <span>Pilih Paket Kontraktor</span>
                            </a>
                        </div>
                    </div>

                    <!-- 18. Event & Wedding Organizer -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">18. Wedding Organizer</span>
                                <span class="px-2 py-0.5 rounded-md bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold text-[10px]">Paket Premium</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Jadwal pembayaran klien pengantin (DP, cicilan, pelunasan H-7) dan proposal rundown acara.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Notifikasi WhatsApp pengingat cicilan otomatis dan template invoice elegan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                <span>Pilih Paket Wedding</span>
                            </a>
                        </div>
                    </div>

                    <!-- 19. Digital IT & Creative Agency -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">19. Digital Creative Agency</span>
                                <span class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold text-[10px]">Paket Prestige</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Kebutuhan Usaha:</strong> Penggunaan token Asisten AI tingkat tinggi untuk riset pasar dan salinan kampanye iklan klien.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Manfaat Paket:</strong> Kuota token AI maksimal, invoice berulang (*recurring*), dan integrasi API lengkap.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                                <i data-lucide="monitor" class="w-3 h-3"></i>
                                <span>Pilih Paket Agency</span>
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
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Multi-gudang cabang, puluhan staf salesmen lapangan, dan sinkronisasi pesanan toko mitra</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/10 text-teal-700 dark:text-teal-400">1 Sektor</span>
                </div>

                <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-xs">20. Distributor Grosir B2B &amp; Agrobisnis</span>
                        <span class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold text-[10px]">Paket Prestige Enterprise</span>
                    </div>
                    <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                        <strong>Kebutuhan Usaha:</strong> Mengelola perputaran barang ratusan ton, sinkronisasi stok antara gudang pusat dan depo cabang, serta izin akses puluhan sales kanvaser.
                    </p>
                    <p class="text-[11px] text-black/70 dark:text-white/70">
                        <strong>Manfaat Paket:</strong> Tanpa batasan kuota transaksi, dukungan multi-gudang tak terbatas, dan jalur prioritas server dengan jaminan SLA uptime 99,9%.
                    </p>
                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                        <span class="text-[11px] text-black/50 dark:text-white/50">Kapasitas enterprise tanpa batas</span>
                        <a href="{{ route('billing.checkout') }}" class="text-[11px] font-semibold text-[#5856D6] hover:underline flex items-center gap-1">
                            <i data-lucide="truck" class="w-3 h-3"></i>
                            <span>Pilih Paket Distributor</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 5. KEAMANAN, OTORISASI AKSES (RBAC) & ANTI-FRAUD -->
    <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3 pb-2 border-b border-black/5 dark:border-white/10">
            <span class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Keamanan Transaksi Billing &amp; Perlindungan Finansial</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Enkripsi signature gateway pembayaran dan kontrol hak akses tagihan khusus pemilik usaha</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
            <div class="p-3.5 rounded-xl bg-purple-500/[0.04] dark:bg-purple-500/[0.08] border border-purple-500/15 space-y-1">
                <div class="flex items-center gap-1.5 text-purple-700 dark:text-purple-400 font-bold">
                    <i data-lucide="key" class="w-4 h-4"></i>
                    <span>Verifikasi HMAC TriPay</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap sinyal notifikasi pembayaran dari TriPay divalidasi dengan signature kriptografi HMAC SHA-256. Tidak ada pihak luar yang dapat memanipulasi status pembayaran paket Anda.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-emerald-500/[0.04] dark:bg-emerald-500/[0.08] border border-emerald-500/15 space-y-1">
                <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-bold">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    <span>Pemisahan Hak Akses Billing</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Hanya pengguna dengan izin <code>billing.manage</code> (Pemilik Usaha atau Direktur Finansial) yang berhak mengubah paket langganan dan melakukan pemesanan checkout.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-[#007AFF]/[0.04] dark:bg-[#007AFF]/[0.08] border border-[#007AFF]/15 space-y-1">
                <div class="flex items-center gap-1.5 text-[#007AFF] dark:text-[#0A84FF] font-bold">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Faktur Resmi Anti-Ubah</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Nomor faktur invoice terbit secara berurutan dengan kode unik permanen. Berkas PDF faktur tersimpan aman dan tidak dapat diubah oleh staf operasional manapun.
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
                    <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Tanya Jawab Pemilik Usaha Seputar Billing &amp; Kuota</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">Pertanyaan umum seputar metode bayar, buffer darurat, dan keamanan data toko</p>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">1. Apa yang terjadi pada kasir toko saya jika kuota transaksi bulanan tiba-tiba habis di tengah jam sibuk?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    <strong>Toko Anda tidak akan lumpuh.</strong> Cooca menyediakan sistem <strong>Emergency Buffer Transaksi</strong>. Saat kuota bulanan tercapai di jam ramai, kasir tetap dapat memproses struk penjualan darurat sehingga pelanggan di depan meja kasir tidak perlu menunggu. Sistem hanya akan menampilkan pengingat santun di bilah atas (*topbar*) agar pemilik usaha dapat meng-upgrade paket saat jam sepi.
                </p>
            </div>

            <!-- FAQ 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">2. Apakah data transaksi penjualan dan catatan pelanggan saya akan hilang jika saya telat memperpanjang paket?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    <strong>Pasti tidak akan hilang.</strong> Cooca menerapkan prinsip perlindungan data mutlak (*No-Data-Punishment*). Riwayat penjualan masa lalu, data ribuan pelanggan, dan katalog master produk Anda tetap tersimpan utuh dan aman di server awan kami. Begitu Anda memperpanjang langganan, seluruh akses kembali aktif seperti semula.
                </p>
            </div>

            <!-- FAQ 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">3. Berapa lama waktu yang dibutuhkan agar paket langganan aktif setelah saya membayar via QRIS?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Aktivasi berjalan secara <strong>otomatis dalam hitungan detik (rata-rata 5–10 detik)</strong>. Gateway TriPay langsung mengirimkan sinyal konfirmasi ke sistem Cooca segera setelah saldo Anda terpotong di aplikasi m-banking atau e-wallet Anda. Anda tidak perlu mengirim foto struk transfer ke admin WhatsApp.
                </p>
            </div>

            <!-- FAQ 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">4. Apakah saya bisa mendapatkan faktur resmi untuk laporan pembukuan atau pajak usaha?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sangat bisa. Setiap transaksi langganan otomatis menerbitkan <strong>Faktur Resmi (Invoice PDF)</strong> bertanda tangan digital Cooca lengkap dengan rincian nama bisnis Anda, tanggal transaksi, metode bayar, dan rincian harga. Anda dapat mengunduh atau mencetak faktur ini kapan saja dari menu <strong>Riwayat Pembayaran</strong>.
                </p>
            </div>
        </div>
    </div>

</section>
