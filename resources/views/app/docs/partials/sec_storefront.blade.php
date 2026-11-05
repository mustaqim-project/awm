<!-- ===================================================== -->
<!-- SECTION: TOKO ONLINE, LANDING PAGE CMS & 42 TEMA STOREFRONT -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-storefront" class="space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'sales_purchasing' || activeTab === 'getting_started') && matchesSearch('toko online landing page storefront tema theme katalog order checkout keranjang shipping ongkir biteship kurir reservasi dine in menu 42 tema arketipe minimalist brutalist editorial dark pastel industrial')">

    <!-- 1. HEADER SEKSI & TOMBOL TAUTAN CEPAT KE MODUL LANDING PAGE & STOREFRONT -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-500 via-[#007AFF] to-teal-400 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(0,122,255,0.35)] shrink-0">
                <i data-lucide="globe" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Toko Online &amp; 42 Tema Storefront Universal</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] border border-[#007AFF]/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">42 Tema &amp; 6 Arketipe</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Panduan praktis pemilik usaha meluncurkan etalase digital profesional, menerima pesanan online, dan memilih tema adaptif tanpa perlu keahlian koding.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <a href="{{ route('landing-page.edit') }}"
                class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                <i data-lucide="layout-template" class="w-3.5 h-3.5"></i>
                <span>Studio Landing Page</span>
            </a>
            <a href="{{ route('storefront.orders.index') }}"
                class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                <i data-lucide="shopping-bag" class="w-3.5 h-3.5 text-indigo-500"></i>
                <span>Pesanan Masuk</span>
            </a>
            <a href="{{ route('storefront.shipping.index') }}"
                class="h-9 px-3 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95"
                title="Atur Tarif Ongkir & Kurir">
                <i data-lucide="truck" class="w-3.5 h-3.5 text-emerald-500"></i>
                <span>Ongkir &amp; Kurir</span>
            </a>
            <a href="{{ route('storefront.settings.index') }}"
                class="h-9 px-3 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95"
                title="Metode Pembayaran Toko Online">
                <i data-lucide="credit-card" class="w-3.5 h-3.5 text-amber-500"></i>
                <span>Metode Bayar</span>
            </a>
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-500 to-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Mengapa Toko Online COOCA Berbeda dari Website Toko Biasa?</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Toko Online Cooca <strong>bukan brosur statis yang terpisah</strong>, melainkan <strong>cabang penjualan digital 24 jam</strong> yang terhubung langsung ke mesin kasir POS, stok inventori gudang, dan laporan laba rugi bisnis Anda. Ketika produk terjual di kasir fisik, stok di toko online otomatis berkurang saat itu juga. Sebaliknya, ketika pesanan masuk dari toko online, kasir toko langsung menerima notifikasi dan pesanan otomatis tercatat di pembukuan tanpa perlu input ulang.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Keunggulan Toko Online Cooca bagi Pemilik Usaha -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    <span>Langsung Siap Pakai 2 Menit</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Tidak perlu sewa jasa programmer, beli domain mahal, atau pusing koding. Aktifkan landing page dan toko online langsung aktif dengan alamat web instan resmi.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-[#007AFF]/[0.06] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 space-y-1.5">
                <div class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs sm:text-sm">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    <span>Sinkron Stok Kasir Real-Time</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Menghilangkan risiko <em>overselling</em> (produk sudah habis dibeli pembeli offline tapi masih bisa dipesan pembeli online). Stok selalu akurat 100%.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-purple-500/[0.06] dark:bg-purple-500/[0.1] border border-purple-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-purple-700 dark:text-purple-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="palette" class="w-4 h-4"></i>
                    <span>42 Tema &amp; 6 Arketipe Desain</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Dari gaya minimalis, editorial mewah, brutalist kontras, hingga modern tech. Bebas ganti tema kapan saja tanpa merusak data produk dan artikel.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>Auto-Hide &amp; Anti Tautan Rusak</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Jika usaha Anda belum memiliki galeri foto atau ulasan, menu tersebut otomatis disembunyikan. Etalase selalu tampak rapi dan profesional di mata pelanggan.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA 4 LANGKAH PENGATURAN TOKO ONLINE -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-6 shadow-2xs">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
            <div class="space-y-0.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Alur 4 Langkah Praktis Mengelola Toko Online</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Langkah mudah pemilik usaha mengatur etalase digital dari awal hingga siap menerima transaksi</p>
            </div>
            <a href="{{ route('landing-page.edit') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                <span>Buka Pengaturan Landing Page &rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Langkah 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-[#007AFF] text-white flex items-center justify-center font-bold text-xs">1</span>
                    <h4 class="text-xs sm:text-sm font-bold text-black dark:text-white">Pilih Arketipe &amp; Tema Visual</h4>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                    Masuk ke menu <strong>Toko Online / Landing Page</strong> lalu klik tab <strong>Tipografi &amp; Warna Tema</strong>. Pilih tema yang cocok dengan kepribadian brand usaha Anda dari 6 arketipe desain (misal: <em>Minimalist Clean</em> untuk klinik atau <em>Brutalist Bold</em> untuk bengkel).
                </p>
            </div>

            <!-- Langkah 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-indigo-500 text-white flex items-center justify-center font-bold text-xs">2</span>
                    <h4 class="text-xs sm:text-sm font-bold text-black dark:text-white">Isi Headline &amp; 3 Pilar Nilai Keunggulan</h4>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                    Buka tab <strong>Hero Section</strong>. Tulis kalimat pembuka (*Headline*) yang memikat pelanggan (contoh: <em>"Katering Sehat Harian Tanpa MSG &amp; Garansi Tepat Waktu"</em>) dan tentukan 3 pilar nilai keunggulan utama toko Anda.
                </p>
            </div>

            <!-- Langkah 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center font-bold text-xs">3</span>
                    <h4 class="text-xs sm:text-sm font-bold text-black dark:text-white">Tentukan Jalur Pesanan &amp; Pembayaran</h4>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pilih apakah pelanggan memesan via <strong>Direct WhatsApp Checkout</strong> (cocok untuk katering, laundry, atau bengkel) atau <strong>Web Cart Checkout</strong> mandiri dengan kalkulasi ongkir otomatis dan pembayaran QRIS/Transfer Bank.
                </p>
            </div>

            <!-- Langkah 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded-lg bg-purple-500 text-white flex items-center justify-center font-bold text-xs">4</span>
                    <h4 class="text-xs sm:text-sm font-bold text-black dark:text-white">Pratinjau Instan &amp; Bagikan Tautan Toko</h4>
                </div>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                    Periksa tampilan langsung di layar live preview sebelah kanan studio. Klik tombol <strong>Simpan &amp; Terbitkan</strong>, lalu pasang tautan toko Anda di bio Instagram, profil TikTok, dan status WhatsApp.
                </p>
            </div>
        </div>

        <!-- Tabel 6 Arketipe Desain Visual -->
        <div class="space-y-3 pt-2">
            <h4 class="text-xs font-bold text-black dark:text-white uppercase tracking-wider text-black/50 dark:text-white/50">Panduan 6 Arketipe Desain Visual</h4>
            <div class="overflow-x-auto rounded-xl border border-black/5 dark:border-white/10">
                <table class="w-full text-left text-xs">
                    <thead class="bg-black/[0.03] dark:bg-white/[0.04] text-black/70 dark:text-white/70 font-bold border-b border-black/5 dark:border-white/10">
                        <tr>
                            <th class="p-3">Arketipe Visual</th>
                            <th class="p-3">Karakteristik Desain</th>
                            <th class="p-3">Cocok untuk Jenis Usaha</th>
                            <th class="p-3">Contoh Tema</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/10 text-black/80 dark:text-white/80">
                        <tr>
                            <td class="p-3 font-bold text-[#007AFF]">Minimalist Clean</td>
                            <td class="p-3">Whitespace lapang, tipografi sans modern, border halus, tenang &amp; teratur.</td>
                            <td class="p-3">Klinik, Kantor Konsultan, Personal Brand, Toko Organik</td>
                            <td class="p-3"><code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">minimal_mono</code>, <code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">craft_artisan</code></td>
                        </tr>
                        <tr>
                            <td class="p-3 font-bold text-rose-500">Brutalist Bold</td>
                            <td class="p-3">Kontras tinggi, tipografi uppercase tegas, sudut tajam, berani &amp; bertenaga.</td>
                            <td class="p-3">Streetwear, Bengkel Motor/Mobil, Perkakas Hardware</td>
                            <td class="p-3"><code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">streetwear_culture</code>, <code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">automotive_service</code></td>
                        </tr>
                        <tr>
                            <td class="p-3 font-bold text-amber-500">Editorial Serif</td>
                            <td class="p-3">Tipografi serif anggun, tata letak majalah mewah, ornamen berkelas.</td>
                            <td class="p-3">Perhiasan &amp; Emas, Wedding Organizer, Buku &amp; Galeri</td>
                            <td class="p-3"><code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">luxury_jewelry</code>, <code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">wedding_organizer</code></td>
                        </tr>
                        <tr>
                            <td class="p-3 font-bold text-cyan-500">Modern Tech / Cyber Dark</td>
                            <td class="p-3">Mode gelap Apple HIG, ambient glow biru/cyan, badge status futuristik.</td>
                            <td class="p-3">Gadget &amp; Elektronik, Gaming Store, IT Solutions, Software</td>
                            <td class="p-3"><code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">tech_gadget</code>, <code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">saas_digital</code></td>
                        </tr>
                        <tr>
                            <td class="p-3 font-bold text-pink-500">Soft Warm / Pastel</td>
                            <td class="p-3">Sudut membulat lembut (squircle), warna pastel hangat, ramah keluarga.</td>
                            <td class="p-3">Baby &amp; Kids Shop, Kosmetik Kecantikan, Petshop, Bakery</td>
                            <td class="p-3"><code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">beauty_cosmetics</code>, <code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">baby_kids</code></td>
                        </tr>
                        <tr>
                            <td class="p-3 font-bold text-emerald-600">Industrial Heavy</td>
                            <td class="p-3">Grid fungsional terstruktur, kartu spesifikasi teknis, data armada.</td>
                            <td class="p-3">Manufaktur Pabrik, Logistik &amp; Kargo, Distributor Grosir</td>
                            <td class="p-3"><code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">manufacturing_plant</code>, <code class="text-[11px] px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10">wholesale_bulk</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS PENERAPAN PRAKTIS UNTUK 20 SEKTOR INDUSTRI BERBEDA -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-6 shadow-2xs">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Matriks Penerapan Toko Online untuk 20 Jenis Usaha di Indonesia</h3>
            </div>
            <p class="text-xs text-black/60 dark:text-white/60">Panduan implementasi konkret etalase digital untuk 20 sektor bisnis UMKM tanpa kebocoran fitur silang</p>
        </div>

        <div class="space-y-5">
            <!-- Klaster 1: Kuliner & F&B -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold text-amber-600 dark:text-amber-400">
                    <i data-lucide="utensils" class="w-4 h-4"></i>
                    <span>KLASTER 1: KULINER &amp; F&amp;B (5 Usaha)</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">1. Restoran Dine-In</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Aktifkan tombol reservasi meja online dan menu digital QR. Pelanggan dapat memesan tempat sebelum datang.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">2. Coffee Shop &amp; Cafe</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Pajang varian beans, minuman signature, dan live status jam operasional barista.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">3. Bakery &amp; Toko Roti</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Gunakan sistem pre-order roti hangat pagi/sore dengan batas jam pemesanan harian otomatis.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">4. Cloud Kitchen Online</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Checkout cepat terhubung ke kurir instan Biteship untuk pengantaran makanan panas.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">5. Katering Prasmanan</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Formulir pemesanan paket pax dengan pilihan DP 50% dan pelunasan menjelang hari H acara.</p>
                    </div>
                </div>
            </div>

            <!-- Klaster 2: Manufaktur & HPP -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold text-blue-600 dark:text-blue-400">
                    <i data-lucide="factory" class="w-4 h-4"></i>
                    <span>KLASTER 2: MANUFAKTUR &amp; WORKSHOP (5 Usaha)</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">6. Konveksi &amp; Garment</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Tampilkan katalog bahan kain, tabel ukuran (size chart), dan tombol <em>Minta Penawaran B2B</em>.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">7. Percetakan &amp; Offset</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Unggah portofolio cetak kalender, banner, dan box kemasan lengkap dengan opsi upload file desain.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">8. Mebel &amp; Furniture Kayu</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Tampilkan galeri foto hasil produksi meja/kursi kayu jati dan opsi kustom ukuran ruangan.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">9. Bengkel Bubut &amp; Logam</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Spesifikasi mesin CNC/bubut dan form konsultasi gambar teknik sparepart industri.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">10. Kerajinan Tangan / Craft</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Koleksi produk unik artisan dengan stok terbatas dan foto produk berkualitas tinggi.</p>
                    </div>
                </div>
            </div>

            <!-- Klaster 3 & 4: Ritel, Apotek & Jasa Harian -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    <span>KLASTER 3 &amp; 4: RITEL, APOTEK &amp; JASA HARIAN (6 Usaha)</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">11. Minimarket &amp; Kelontong</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Katalog kebutuhan pokok harian terkelompok rapi per kategori sembako, sabun, dan snack.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">12. Apotek &amp; Toko Obat</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Pajang suplemen &amp; alat kesehatan resmi, serta form konsultasi kirim resep dokter via WhatsApp.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">13. Bengkel Mobil &amp; Motor</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Menu booking servis berkala tanpa antre dan estimasi biaya paket ganti oli/tune up.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">14. Barbershop &amp; Salon</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Daftar harga potong rambut, perawatan wajah, dan tombol reservasi slot kapster favorit.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">15. Laundry Kiloan &amp; Satuan</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Pilihan paket cuci kiloan, cuci bedcover, dan tombol request antar-jemput cucian ke rumah.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">16. Cuci Mobil &amp; Detailing</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Paket cuci hidrolik, coating nano ceramic, dan jadwal booking slot antrean cuci.</p>
                    </div>
                </div>
            </div>

            <!-- Klaster 5 & 6: Proyek, Event & Distribusi B2B -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-3">
                <div class="flex items-center gap-2 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                    <span>KLASTER 5 &amp; 6: JASA PROYEK &amp; DISTRIBUSI B2B (4 Usaha)</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">17. Kontraktor &amp; Renovasi Bangunan</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Pamerkan proyek rumah/ruko yang selesai dibangun dan form jadwal survei gratis ke lokasi proyek.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">18. Event &amp; Wedding Organizer</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Pilihan paket pernikahan lengkap (gedung, rias, katering, dokumentasi) dan kalender ketersediaan tanggal.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">19. Creative &amp; Digital IT Agency</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Etalase studi kasus portofolio desain, branding, website, dan tombol jadwalkan sesi konsultasi.</p>
                    </div>
                    <div class="p-3 rounded-lg bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/10 space-y-1">
                        <span class="font-bold text-black dark:text-white">20. Distributor Grosir B2B &amp; Agrobisnis</span>
                        <p class="text-[11px] text-black/60 dark:text-white/60">Katalog karton/dus grosir, syarat minimum kuantitas pembelian (MOQ), dan opsi termin faktur tempo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. KEAMANAN, OTORISASI & ANTI-FRAUD TOKO ONLINE -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-2.5 pb-2 border-b border-black/5 dark:border-white/10">
            <span class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </span>
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Keamanan Transaksi &amp; Otorisasi Toko Online</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1">
                <span class="font-bold text-black dark:text-white">Validasi Bukti Bayar Manual</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pesanan transfer manual memerlukan verifikasi dari pemilik toko di menu <strong>Pesanan Masuk</strong> sebelum status berubah menjadi diproses, mencegah penipuan struk palsu.
                </p>
            </div>
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1">
                <span class="font-bold text-black dark:text-white">Isolasi Data Antar Bisnis (IDOR Shield)</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Data produk, pesanan, dan pelanggan Anda terisolasi total berdasarkan ID bisnis unik. Toko lain tidak dapat melihat atau mengakses data Anda sedikit pun.
                </p>
            </div>
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1">
                <span class="font-bold text-black dark:text-white">Otomasi Rekonsiliasi Kas</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap pembayaran online terverifikasi otomatis mencatatkan jurnal akuntansi ganda ke akun bank terkait tanpa risiko selisih buku kas manual.
                </p>
            </div>
        </div>
    </div>

    <!-- 6. TANYA JAWAB BISNIS OWNER (FAQ TOKO ONLINE) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-2.5 pb-2 border-b border-black/5 dark:border-white/10">
            <span class="w-7 h-7 rounded-lg bg-[#007AFF]/15 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-bold text-xs">
                <i data-lucide="help-circle" class="w-4 h-4"></i>
            </span>
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Tanya Jawab Pemilik Usaha Seputar Toko Online (FAQ)</h3>
        </div>

        <div class="space-y-3 text-xs">
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1">
                <span class="font-bold text-black dark:text-white">Q: Apakah saya harus membayar biaya hosting atau sewa domain tambahan?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    <strong>Jawab:</strong> Tidak perlu. Seluruh tema dan etalase toko online sudah termasuk dalam sistem Cooca lengkap dengan alamat link resmi (misal: <code>cooca.id/s/nama-toko-anda</code>).
                </p>
            </div>
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1">
                <span class="font-bold text-black dark:text-white">Q: Bagaimana jika usaha saya bergerak di bidang jasa (seperti bengkel, salon, atau kontraktor) yang tidak punya stok barang fisik?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    <strong>Jawab:</strong> Sistem otomatis beradaptasi. Cukup pilih arketipe <em>Minimalist Clean</em> atau <em>Brutalist Bold</em> dan sembunyikan keranjang belanja. Etalase Anda otomatis beralih fungsi menjadi Company Profile profesional dengan formulir booking jadwal atau konsultasi via WhatsApp.
                </p>
            </div>
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1">
                <span class="font-bold text-black dark:text-white">Q: Apakah pesanan pembeli bisa langsung terkirim ke nomor WhatsApp kasir atau admin toko?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    <strong>Jawab:</strong> Ya. Pada tab Pengaturan Landing Page, aktifkan opsi <em>Direct WhatsApp Checkout</em> dan masukkan nomor WhatsApp admin. Pesanan pelanggan akan otomatis dirangkum ke format pesan WhatsApp rapi siap kirim dalam 1 klik.
                </p>
            </div>
        </div>
    </div>

</section>

