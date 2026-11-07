<!-- ===================================================== -->
<!-- SECTION: AI STUDIO VISUAL & GENERATOR MATERI PROMOSI -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-ai-studio" class="space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'ai' || activeTab === 'marketing') && matchesSearch('ai studio visual foto produk katalog banner promosi iklan poster feed instagram canva gambar komersial render variasi inspirasi viral 20 industri usaha resto cafe bengkel konveksi laundry')">

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL AI STUDIO -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#FF2D55] via-[#FF9500] to-[#FFCC00] text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(255,45,85,0.35)] shrink-0">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">AI Studio Visual &amp; Inspirasi Viral</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">20 Sektor Industri</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Panduan praktis pemilik usaha membuat foto katalog produk kelas studio, banner promosi omnichannel, dan visual media sosial viral tanpa biaya fotografer mahal.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::hasPermission('ai_studio.generate') || \App\Support\Context::isOwner())
                <a href="{{ route('ai-studio.create') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Buat Proyek Visual</span>
                </a>
            @endif
            <a href="{{ route('ai-studio.index') }}"
                class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                <i data-lucide="layout-grid" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Galeri Proyek</span>
            </a>
            <a href="{{ route('ai-studio.inspiration.index') }}"
                class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                <i data-lucide="compass" class="w-3.5 h-3.5 text-amber-500"></i>
                <span>Jelajahi Inspirasi</span>
            </a>
            <a href="{{ route('ai-studio.credit-history') }}"
                class="h-9 px-3 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95"
                title="Buku Besar Saldo Kredit AI">
                <i data-lucide="coins" class="w-3.5 h-3.5 text-amber-500"></i>
                <span>Saldo Kredit</span>
            </a>
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-[#FF2D55] to-[#FF9500] text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="camera" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">Mengapa Visual Berkualitas Menentukan 80% Konversi Penjualan Anda?</h3>
                <p class="text-xs sm:text-[13px] text-black/70 dark:text-white/70 leading-relaxed">
                    Pelanggan modern membeli dengan mata terlebih dahulu sebelum membaca deskripsi. Sesi pemotretan foto produk tradisional membutuhkan biaya Rp 1.500.000 – Rp 5.000.000 per sesi, menyewa studio, model, dan menunggu editing berminggu-minggu. <strong>AI Studio COOCA</strong> memotong biaya tersebut hingga 95% dan menghasilkan visual komersial ultra-realistis dalam hitungan detik.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2">
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5">
                <div class="flex items-center gap-2 text-xs font-bold text-[#FF2D55]">
                    <i data-lucide="badge-percent" class="w-4 h-4"></i>
                    <span>Hemat Biaya 95%</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-normal">Tak perlu sewa studio atau fotografer mahal. Cukup unggah foto produk dari HP, AI akan menempatkannya di latar belakang mewah.</p>
            </div>

            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5">
                <div class="flex items-center gap-2 text-xs font-bold text-[#007AFF]">
                    <i data-lucide="share-2" class="w-4 h-4"></i>
                    <span>Omnichannel Ready</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-normal">Render multi-rasio otomatis: 1:1 untuk Katalog Kasir &amp; Feed Instagram, 9:16 untuk TikTok/Reels, 16:9 untuk Banner Web.</p>
            </div>

            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5">
                <div class="flex items-center gap-2 text-xs font-bold text-[#34C759]">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>1-Klik Simpan ke Katalog</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-normal">Hasil render dapat langsung dijadikan foto resmi produk di kasir POS dan dipublikasikan ke postingan WhatsApp/Instagram.</p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA PRAKTIS (3-4 LANGKAH PENGGUNAAN) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="workflow" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Alur Kerja Praktis Pemilik Usaha: Dari Ide Menjadi Visual Penjualan</span>
            </h3>
            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">3 Langkah Mudah</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Langkah 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <div class="w-7 h-7 rounded-lg bg-[#007AFF]/10 text-[#007AFF] font-bold text-xs flex items-center justify-center">1</div>
                <h4 class="text-xs font-bold text-black dark:text-white">Pilih Format &amp; Unggah Foto Produk</h4>
                <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                    Buka <a href="{{ route('ai-studio.create') }}" class="text-[#007AFF] font-semibold hover:underline">Buat Proyek Baru</a>. Pilih rasio gambar (Square 1:1 untuk katalog atau Vertical 9:16 untuk Reels). Unggah foto produk mentah dari kamera ponsel Anda.
                </p>
            </div>

            <!-- Langkah 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <div class="w-7 h-7 rounded-lg bg-[#FF9500]/10 text-[#FF9500] font-bold text-xs flex items-center justify-center">2</div>
                <h4 class="text-xs font-bold text-black dark:text-white">Pilih Gaya Tema / Jelajahi Inspirasi</h4>
                <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pilih suasana pencahayaan studio (Minimalist Studio, Sunlit Marble, Cozy Wood, atau Neon Streetwear) atau salin prompt dari galeri <a href="{{ route('ai-studio.inspiration.index') }}" class="text-[#FF9500] font-semibold hover:underline">Jelajahi Inspirasi</a>.
                </p>
            </div>

            <!-- Langkah 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <div class="w-7 h-7 rounded-lg bg-[#34C759]/10 text-[#34C759] font-bold text-xs flex items-center justify-center">3</div>
                <h4 class="text-xs font-bold text-black dark:text-white">Render, Simpan ke Katalog &amp; Posting</h4>
                <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                    Klik <strong>Render Variasi</strong>. Hasil visual resolusi tinggi siap diunduh, disimpan otomatis ke kartu master produk kasir POS, atau diposting langsung ke media sosial bisnis.
                </p>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS IMPLEMENTASI PRAKTIS UNTUK 20 SEKTOR INDUSTRI UMKM -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3 flex-wrap gap-2">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4 text-[#AF52DE]"></i>
                    <span>Matriks Penerapan Nyata AI Studio Lintas 20 Sektor Industri</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Contoh konkret penggunaan prompt dan konsep visual per klaster usaha di Indonesia</p>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[#AF52DE]/10 text-[#AF52DE]">6 Klaster Usaha</span>
        </div>

        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 text-black/40 dark:text-white/40 font-bold uppercase text-[10px] tracking-wider">
                        <th class="py-2.5 px-3">Klaster</th>
                        <th class="py-2.5 px-3">Jenis Usaha</th>
                        <th class="py-2.5 px-3">Kebutuhan Visual Kunci</th>
                        <th class="py-2.5 px-3">Contoh Implementasi Praktis di AI Studio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-black/80 dark:text-white/80">
                    {{-- KLASTER 1: F&B --}}
                    <tr>
                        <td class="py-3 px-3 font-bold text-[#FF2D55] align-top whitespace-nowrap" rowspan="5">1. Kuliner &amp; F&amp;B</td>
                        <td class="py-3 px-3 font-semibold">1. Restoran Dine-In</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Foto buku menu meja, suasana hangat estetik</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Render foto steak/hidangan di atas piring keramik mewah berlatar meja kayu restoran fine dining.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">2. Coffee Shop &amp; Cafe</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Cup kopi takeaway &amp; latte art sinematik</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Tampilkan cup kopi berembun di pagi hari dengan latar meja marmer kafe minimalis Jepang.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">3. Bakery &amp; Toko Roti</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Tekstur renyah pastry &amp; croissant emas</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Render pastry dengan taburan gula halus dan pencahayaan sinar matahari pagi dari jendela toko.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">4. Cloud Kitchen Online</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Foto banner GoFood/GrabFood menggugah selera</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Visual rice bowl dengan uap hangat, potongan ayam krispi mengkilap dan irisan cabai segar.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">5. Katering Prasmanan</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Brosur paket pesta pernikahan &amp; kantor</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Render deretan chaffing dish elegan dengan hiasan bunga segar di meja resepsi megah.</td>
                    </tr>

                    {{-- KLASTER 2: MANUFAKTUR --}}
                    <tr>
                        <td class="py-3 px-3 font-bold text-[#FF9500] align-top whitespace-nowrap" rowspan="5">2. Manufaktur &amp; HPP</td>
                        <td class="py-3 px-3 font-semibold">6. Konveksi &amp; Garment</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Foto katalog hoodie, kaos, &amp; seragam</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Render mockup baju dikenakan model fashion streetwear di latar kota urban modern.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">7. Percetakan Offset</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Mockup kemasan kardus packaging &amp; kartu nama</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Tampilkan desain box packaging custom dengan efek foil emas timbul (emboss) di meja studio.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">8. Mebel Kayu / Furniture</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Katalog interior ruang tamu &amp; kamar</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Tempatkan kursi jati buatan perajin di dalam ruang tamu bergaya Skandinavia bercahaya alami.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">9. Bengkel Bubut Logam</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Komponen presisi mesin &amp; sparepart</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Visual komponen baja presisi dengan pantulan cahaya industri metalik tajam tanpa cela.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">10. Kerajinan / Craft</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Produk etnik, tas anyaman, keramik</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Foto tas anyaman rotan dengan latar kain linen alami dan bayangan daun kelapa tropis.</td>
                    </tr>

                    {{-- KLASTER 3: RITEL & APOTEK --}}
                    <tr>
                        <td class="py-3 px-3 font-bold text-[#34C759] align-top whitespace-nowrap" rowspan="2">3. Ritel &amp; Apotek</td>
                        <td class="py-3 px-3 font-semibold">11. Minimarket &amp; Kelontong</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Poster promo diskon mingguan &amp; bundle</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Banner promosi sembako murah berlatar supermarket bersih dan ceria dengan badge harga menarik.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">12. Apotek &amp; Toko Obat</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Visual vitamin, suplemen, &amp; skincare</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Botol suplemen herbal di atas podium batu marmer putih bersih dengan tetesan air segar alami.</td>
                    </tr>

                    {{-- KLASTER 4: JASA HARIAN --}}
                    <tr>
                        <td class="py-3 px-3 font-bold text-[#007AFF] align-top whitespace-nowrap" rowspan="4">4. Jasa Harian</td>
                        <td class="py-3 px-3 font-semibold">13. Bengkel Mobil &amp; Motor</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Promo ganti oli, tune up, &amp; servis berkala</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Banner promo paket servis mesin mobil dengan latar garasi bengkel modern higienis.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">14. Barbershop &amp; Salon</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Model potongan rambut &amp; produk pomade</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Foto model gaya fade haircut maskulin di kursi barbershop vintage dengan pencahayaan dramatis.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">15. Laundry Kiloan &amp; Satuan</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Banner pakaian wangi, bersih, &amp; higienis</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Tumpukan handuk putih bersih lembut dengan bunga lavender segar melambangkan keharuman maksimal.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">16. Car Wash &amp; Detailing</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Kilau cat mobil wet-look nano ceramic</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Foto mobil mewah hitam mengkilap efek kaca (mirror finish) di dalam studio detailing berlampu neon.</td>
                    </tr>

                    {{-- KLASTER 5: JASA PROYEK --}}
                    <tr>
                        <td class="py-3 px-3 font-bold text-[#AF52DE] align-top whitespace-nowrap" rowspan="3">5. Jasa Proyek</td>
                        <td class="py-3 px-3 font-semibold">17. Kontraktor &amp; Renovasi</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Visual 3D tampak depan rumah &amp; interior</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Visual fasad rumah minimalis modern 2 lantai dengan pencahayaan senja hangat (*golden hour*).</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">18. Wedding &amp; Event Organizer</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Konsep dekorasi pelaminan &amp; panggung</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Visual dekorasi pelaminan rustic penuh bunga pastel di taman terbuka dengan lampu gantung kristal.</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-semibold">19. Digital &amp; Creative Agency</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Mockup kampanye iklan brand &amp; aplikasi</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Mockup layar smartphone dipegang pengguna modern berlatar kantor kreatif terbuka (open workspace).</td>
                    </tr>

                    {{-- KLASTER 6: DISTRIBUSI --}}
                    <tr>
                        <td class="py-3 px-3 font-bold text-[#30B0C7] align-top whitespace-nowrap">6. Distribusi &amp; Agro</td>
                        <td class="py-3 px-3 font-semibold">20. Distributor B2B &amp; Agrobisnis</td>
                        <td class="py-3 px-3 text-black/60 dark:text-white/60">Hasil panen segar &amp; armada logistik</td>
                        <td class="py-3 px-3 text-black/70 dark:text-white/70">Foto buah alpukat/kopi kualitas ekspor berlatar kebun hijau segar dan kemasan krat kayu ekspor.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. KEAMANAN, KUOTA KREDIT & KONTROL HAK AKSES (RBAC) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i>
            <span>Keamanan, Kuota Kredit &amp; Kontrol Akses Staf (Anti-Pemborosan)</span>
        </h3>
        <p class="text-xs sm:text-[13px] text-black/70 dark:text-white/70 leading-relaxed">
            Untuk mencegah pemborosan saldo kredit visual oleh staf yang tidak berwenang, AI Studio menerapkan pembatasan hak akses berbasis peran (*Role-Based Access Control*):
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1 text-xs">
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#007AFF]"></span>
                    Hak Akses Pembuatan (`ai_studio.generate`)
                </span>
                <p class="text-black/60 dark:text-white/60 text-[11px] leading-relaxed">Diberikan khusus kepada staf pemasaran (Marketing/Content Creator) untuk merancang draf dan merender variasi gambar.</p>
            </div>

            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Hak Akses Saldo &amp; Ekspor (`ai_studio.manage`)
                </span>
                <p class="text-black/60 dark:text-white/60 text-[11px] leading-relaxed">Hanya dapat diakses oleh Pemilik Bisnis (Owner) atau Manajer Toko untuk membeli paket kredit top up dan menghapus aset gambar.</p>
            </div>
        </div>
    </div>

    <!-- 6. TANYA JAWAB BISNIS OWNER (FAQ) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
            <i data-lucide="help-circle" class="w-4 h-4 text-amber-500"></i>
            <span>Tanya Jawab Pemilik Usaha (FAQ AI Studio)</span>
        </h3>

        <div class="space-y-3 text-xs">
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <h4 class="font-bold text-black dark:text-white">Q: Apakah gambar yang dihasilkan memiliki hak cipta komersial penuh?</h4>
                <p class="text-black/60 dark:text-white/60 leading-relaxed text-[11.5px]">
                    <strong>Ya, 100% Hak Milik Anda.</strong> Seluruh aset visual yang Anda render di AI Studio COOCA bebas royalti dan dapat digunakan secara bebas untuk keperluan komersial (katalog POS, billboard, banner Shopee/Tokopedia, maupun iklan bersponsor di Meta/TikTok Ads).
                </p>
            </div>

            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <h4 class="font-bold text-black dark:text-white">Q: Bagaimana jika hasil render kurang memuaskan? Apakah kredit terpotong sia-sia?</h4>
                <p class="text-black/60 dark:text-white/60 leading-relaxed text-[11.5px]">
                    Jika terjadi kegagalan sistem saat proses render, kuota kredit Anda <strong>otomatis dikembalikan (refund) seketika</strong>. Anda juga dapat menggunakan fitur penyempurnaan prompt otomatis (*Enhance Prompt*) untuk memastikan pencahayaan dan komposisi gambar sempurna pada render pertama.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <h4 class="font-bold text-black dark:text-white">Q: Berapa lama waktu yang dibutuhkan untuk merender 1 variasi gambar?</h4>
                <p class="text-black/60 dark:text-white/60 leading-relaxed text-[11.5px]">
                    Rata-rata waktu render adalah <strong>8 hingga 20 detik</strong> tergantung pada kompleksitas prompt dan beban server AI. Proses berjalan di latar belakang sehingga Anda dapat tetap menjalankan transaksi kasir atau aktivitas ERP lainnya tanpa hambatan.
                </p>
            </div>
        </div>
    </div>

</section>

