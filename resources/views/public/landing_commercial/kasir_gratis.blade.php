@extends('layouts.public_marketing')

@php
    $pageSeo = \App\Services\Seo\SeoMetadataService::getPageSeo();
@endphp

@section('title', $pageSeo['title'] ?? 'Aplikasi Kasir Gratis Selamanya untuk UMKM | COOCA')
@section('description', $pageSeo['description'] ?? 'Aplikasi kasir gratis selamanya untuk UMKM. Kelola 1 toko, 50 produk, dan 100 transaksi/bulan tanpa kartu kredit. Bisa cetak struk dan terima QRIS.')
@section('og_title', $pageSeo['og_title'] ?? 'Aplikasi Kasir Gratis Selamanya untuk UMKM | COOCA')
@section('og_description', $pageSeo['og_description'] ?? 'Aplikasi kasir gratis selamanya untuk UMKM. Kelola 1 toko, 50 produk, dan 100 transaksi/bulan tanpa kartu kredit. Bisa cetak struk dan terima QRIS.')
@section('canonical', url('/aplikasi-kasir-gratis'))
@section('og_type', 'product')

@push('seo')
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA Aplikasi Kasir Gratis",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, iOS, Windows, macOS",
      "description": "Aplikasi kasir gratis selamanya untuk UMKM Indonesia tanpa kartu kredit dan tanpa biaya berlangganan tersembunyi.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "IDR"
      }
    }
    </script>
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "Apakah aplikasi kasir gratis COOCA benar-benar gratis selamanya?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Benar. Paket Gratis COOCA dapat digunakan selamanya tanpa batas masa trial (bukan uji coba 14 hari) dan tidak memerlukan kartu kredit sama sekali."
          }
        },
        {
          "@type": "Question",
          "name": "Apa saja batasan kuota pada paket aplikasi kasir gratis COOCA?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Paket Gratis mencakup 1 outlet toko, kapasitas hingga 50 master produk, dan kuota 100 transaksi penjualan per bulan. Sangat ideal untuk warung, kios, usaha rumahan, dan rintisan awal UMKM."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah bisa mencetak struk kasir menggunakan printer Bluetooth pada paket gratis?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Bisa. Paket Gratis mendukung pencetakan nota fisik ke printer thermal Bluetooth 58mm dan 80mm serta pengiriman nota digital langsung ke WhatsApp pelanggan."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah data penjualan saya akan hilang jika kuota transaksi 100 per bulan habis?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Data transaksi Anda 100% aman dan tersimpan permanen di cloud. Anda tetap dapat melihat seluruh riwayat laporan penjualan. Kuota transaksi akan di-reset otomatis setiap tanggal 1 awal bulan baru, atau Anda dapat upgrade ke paket Standard kapan saja."
          }
        },
        {
          "@type": "Question",
          "name": "Berapa lama waktu yang dibutuhkan untuk mendaftar dan mulai berjualan?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Hanya butuh 2 menit. Cukup daftarkan nama usaha dan nomor telepon Anda, masukkan produk dagangan, dan kasir langsung siap dipakai melayani pembeli."
          }
        }
      ]
    }
    </script>
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Beranda",
          "item": "https://cooca.id"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Aplikasi Kasir Gratis",
          "item": "https://cooca.id/aplikasi-kasir-gratis"
        }
      ]
    }
    </script>
@endpush

@section('content')
    <div class="bg-white dark:bg-[#070A14] text-slate-900 dark:text-white transition-colors duration-300">

        {{-- 1. HERO SECTION --}}
        <section class="relative bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(100svh-4rem)] lg:min-h-[calc(100svh-84px)] flex items-center">
            <div class="absolute top-1/4 -right-24 w-96 h-96 bg-[#007AFF]/20 rounded-full blur-[120px] pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#00C4D8]/15 rounded-full blur-[140px] pointer-events-none"></div>

            <div class="max-w-[1300px] mx-auto px-3.5 sm:px-6 lg:px-8 relative z-10 w-full pt-8 pb-16 lg:py-16">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    <div class="lg:col-span-7 space-y-6 text-left">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Bebas Biaya &bull; Tanpa Kartu Kredit &bull; Tanpa Batas Trial
                            </p>
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.14] text-balance">
                            {{ \App\Services\Seo\SeoMetadataService::getH1('Aplikasi Kasir Gratis Selamanya untuk Usaha UMKM') }}
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            Mulai digitalisasi kasir toko, warung, atau kedai kopi Anda hari ini tanpa keluar uang sepeser pun. Nikmati aplikasi kasir POS lengkap dengan cetak struk thermal, scan barcode kamera HP, pembayaran QRIS, dan rekap omzet otomatis langsung dari smartphone Anda.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-left w-full">
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">1 Toko & 50 Produk Gratis Selamanya</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Cetak Thermal Bluetooth & Nota WA</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="scan" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Scan Barcode via Kamera Smartphone</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-400/20">
                                    <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Terima QRIS & Tunai Otomatis</span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-4">
                            <a href="/register" class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#00C4D8] text-white font-bold text-sm sm:text-base shadow-lg shadow-[#007AFF]/30 hover:brightness-110 active:scale-[0.99] transition-all">
                                <span>Daftar Gratis 2 Menit</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="/pricing" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm sm:text-base border border-white/15 transition-all">
                                <span>Bandingkan Semua Paket</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Package Highlight Card --}}
                    <div class="lg:col-span-5 w-full">
                        <div class="relative rounded-2xl bg-gradient-to-b from-white/10 to-white/[0.03] border border-white/15 p-6 sm:p-8 backdrop-blur-xl shadow-2xl">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold uppercase tracking-wider mb-4">
                                <span>Paket Starter UMKM</span>
                            </div>
                            <div class="flex items-baseline gap-2 mb-2">
                                <span class="text-4xl sm:text-5xl font-black text-white">Rp 0</span>
                                <span class="text-slate-400 text-sm font-semibold">/ selamanya</span>
                            </div>
                            <p class="text-slate-300 text-sm mb-6 pb-6 border-b border-white/10">
                                Solusi terbaik untuk warung pemula, penjual makanan rumahan, dan wirausaha mikro yang baru memulai langkah digitalisasi.
                            </p>

                            <ul class="space-y-3.5 text-sm text-slate-200 mb-8">
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span><strong>1 Lokasi Toko</strong> (Outlet Tunggal)</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span><strong>Hingga 50 Produk</strong> Aktif</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span><strong>100 Transaksi Kasir</strong> per Bulan</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span>Pencatatan Tunai, Transfer & QRIS</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span>Cetak Struk Bluetooth & Kirim Nota WA</span>
                                </li>
                                <li class="flex items-center gap-3">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                    <span>Laporan Omzet Harian Otomatis</span>
                                </li>
                            </ul>

                            <a href="/register" class="w-full inline-flex items-center justify-center gap-2 py-3 px-5 rounded-xl bg-white text-slate-900 font-bold text-sm hover:bg-slate-100 transition-colors">
                                <span>Mulai Pakai Sekarang</span>
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. MASALAH PENCATATAN MANUAL YANG DITUNTASKAN --}}
        <section class="py-16 sm:py-20 max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12 text-left">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">Tantangan Bisnis UMKM</span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    Mengapa Masih Menggunakan Buku Kertas atau Kalkulator Manual?
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                    Banyak pemilik usaha kecil menunda menggunakan aplikasi kasir karena menganggap software kasir itu mahal, membutuhkan perangkat komputer canggih, atau biaya langganan bulanan yang membebani cash flow. Akibatnya, masalah-masalah klasik ini terus berulang:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center font-bold">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Uang Kas Sering Selisih</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Saat tutup kasir di sore atau malam hari, nominal uang di laci fisik sering tidak cocok dengan hitungan nota kertas. Mencari selisih nota yang tercecer membuang waktu berjam-jam.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Antrean Pembeli Lambat</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Menghitung harga belanjaan satu per satu dengan kalkulator meja rawan salah pencet, membuat antrean pembeli menumpuk dan pelanggan kehilangan kesabaran.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-bold">
                        <i data-lucide="trending-down" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tidak Tahu Untung Bersih Riil</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Omzet tampak ramai, tetapi tidak pernah tahu berapa laba bersih yang sesungguhnya karena uang pribadi dan uang operasional warung tercampur baur tanpa laporan pembukuan.
                    </p>
                </div>
            </div>
        </section>

        {{-- 3. PERBANDINGAN PAKET: APA YANG DIDAPAT DI VERSI GRATIS VS BERBAYAR --}}
        <section class="py-16 sm:py-20 bg-slate-50 dark:bg-white/[0.02] border-y border-slate-200 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-12 text-left">
                    <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">Transparansi Fitur</span>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        Perbandingan Paket Gratis vs Paket Berbayar COOCA
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Kami percaya setiap usaha berhak memulai digitalisasi tanpa beban. Mulai dengan paket Gratis, dan tingkatkan paket langganan Anda secara fleksibel saat usaha Anda semakin berkembang.
                    </p>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-white/10 shadow-sm bg-white dark:bg-[#060B1E]">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-white/10 bg-slate-100/70 dark:bg-white/5">
                                <th class="p-4 sm:p-5 font-bold text-slate-900 dark:text-white">Fitur & Kapasitas</th>
                                <th class="p-4 sm:p-5 font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10">Gratis (Rp 0)</th>
                                <th class="p-4 sm:p-5 font-bold text-slate-900 dark:text-white">Standard (Rp 39.000)</th>
                                <th class="p-4 sm:p-5 font-bold text-slate-900 dark:text-white">Premium (Rp 89.000)</th>
                                <th class="p-4 sm:p-5 font-bold text-slate-900 dark:text-white">Prestige (Rp 199.000)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-white/10 text-slate-700 dark:text-slate-300">
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">Jumlah Outlet Toko</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 font-semibold text-emerald-600 dark:text-emerald-400">1 Outlet</td>
                                <td class="p-4 sm:p-5">1 Outlet</td>
                                <td class="p-4 sm:p-5">Hingga 3 Cabang</td>
                                <td class="p-4 sm:p-5">Multi-Cabang Tak Terbatas</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">Kapasitas Master Produk</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 font-semibold text-emerald-600 dark:text-emerald-400">50 Produk</td>
                                <td class="p-4 sm:p-5">500 Produk</td>
                                <td class="p-4 sm:p-5">Tanpa Batas</td>
                                <td class="p-4 sm:p-5">Tanpa Batas</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">Batas Transaksi Kasir</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 font-semibold text-emerald-600 dark:text-emerald-400">100 Transaksi / Bulan</td>
                                <td class="p-4 sm:p-5">1.000 Transaksi / Bulan</td>
                                <td class="p-4 sm:p-5">Tanpa Batas</td>
                                <td class="p-4 sm:p-5">Tanpa Batas</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">Cetak Struk Bluetooth & Nota WA</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 text-emerald-600 dark:text-emerald-400 font-bold">&#10003; Ya</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">&#10003; Ya</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">&#10003; Ya</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">&#10003; Ya</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">Scan Barcode Kamera HP</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 text-emerald-600 dark:text-emerald-400 font-bold">&#10003; Ya</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">&#10003; Ya</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">&#10003; Ya</td>
                                <td class="p-4 sm:p-5 text-emerald-600 dark:text-emerald-400 font-bold">&#10003; Ya</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">Manajemen Akun Kasir & Hak Akses</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5 text-slate-400">1 Akun Owner</td>
                                <td class="p-4 sm:p-5">2 Akun Kasir</td>
                                <td class="p-4 sm:p-5">Hingga 5 Staf Kasir</td>
                                <td class="p-4 sm:p-5">Hak Akses Role Kustom Penuh</td>
                            </tr>
                            <tr>
                                <td class="p-4 sm:p-5 font-medium text-slate-900 dark:text-white">Laporan Keuangan & Laba Rugi</td>
                                <td class="p-4 sm:p-5 bg-emerald-500/5">Rekap Kas Harian</td>
                                <td class="p-4 sm:p-5">Laba Kotor & Margin</td>
                                <td class="p-4 sm:p-5">Laba Rugi & Arus Kas Penuh</td>
                                <td class="p-4 sm:p-5">Akuntansi Lengkap & Neraca</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- 4. CARA DAFTAR 2 MENIT --}}
        <section class="py-16 sm:py-20 max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12 text-left">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">Panduan Cepat</span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    Cara Daftar Aplikasi Kasir Gratis Hanya dalam 2 Menit
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                    Tidak ada syarat rumit, tidak memerlukan kartu kredit, dan tanpa proses instalasi server yang membingungkan. Ikuti 3 langkah praktis ini:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="relative p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10">
                    <span class="text-4xl font-black text-[#007AFF]/20 dark:text-[#00C4D8]/20 block mb-3">01</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Buat Akun Usaha</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Klik tombol pendaftaran, masukkan nama usaha Anda (misal: "Warung Berkah Ibu Sari") dan nomor WhatsApp atau alamat email aktif Anda.
                    </p>
                </div>

                <div class="relative p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10">
                    <span class="text-4xl font-black text-[#007AFF]/20 dark:text-[#00C4D8]/20 block mb-3">02</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Input Produk atau Scan Barcode</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Tambahkan menu atau barang dagangan Anda. Anda bisa mengetik nama produk dan harga jual, atau scan langsung barcode kemasan produk menggunakan kamera smartphone.
                    </p>
                </div>

                <div class="relative p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10">
                    <span class="text-4xl font-black text-[#007AFF]/20 dark:text-[#00C4D8]/20 block mb-3">03</span>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Langsung Siap Transaksi</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Buka layar POS Kasir di browser atau aplikasi Android, pilih barang yang dibeli pelanggan, dan terima pembayaran tunai atau scan QRIS langsung. Selesai!
                    </p>
                </div>
            </div>
        </section>

        {{-- 5. FAQ SECTION --}}
        <section class="py-16 sm:py-20 bg-slate-50 dark:bg-white/[0.02] border-t border-slate-200 dark:border-white/10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-12 text-center">
                    <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">Tanya Jawab</span>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Pertanyaan Seputar Aplikasi Kasir Gratis COOCA
                    </h2>
                </div>

                <div class="space-y-4">
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Apakah aplikasi kasir gratis COOCA benar-benar gratis selamanya?</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Benar. Paket Gratis COOCA dapat digunakan selamanya tanpa batas masa trial (bukan uji coba 14 hari) dan tidak memerlukan kartu kredit sama sekali. Kami mendedikasikan paket ini untuk mendukung pertumbuhan ekonomi wirausaha mikro dan UMKM di seluruh nusantara.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Apa saja batasan kuota pada paket aplikasi kasir gratis COOCA?</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Paket Gratis mencakup 1 outlet toko, kapasitas hingga 50 master produk, dan kuota 100 transaksi penjualan per bulan. Kapasitas ini sangat ideal untuk warung, kios sembako, gerobak kuliner, penjahit rumahan, dan bisnis rintisan awal.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Apakah bisa mencetak struk kasir menggunakan printer Bluetooth pada paket gratis?</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Bisa. Paket Gratis mendukung pencetakan nota fisik ke printer thermal Bluetooth 58mm dan 80mm serta fitur pengiriman nota digital langsung ke WhatsApp pelanggan tanpa tambahan biaya.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Apakah data penjualan saya akan hilang jika kuota transaksi 100 per bulan habis?</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Data transaksi Anda 100% aman dan tersimpan permanen di cloud. Anda tetap dapat membuka dashboard dan mengecek riwayat laporan penjualan. Kuota transaksi 100 akan otomatis di-reset menjadi baru setiap tanggal 1 awal bulan, atau Anda bisa upgrade ke paket Standard Rp39.000/bulan kapan saja tanpa kehilangan data.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Berapa lama waktu yang dibutuhkan untuk mendaftar dan mulai berjualan?</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Hanya butuh 2 menit. Cukup daftarkan nama usaha dan nomor telepon Anda, masukkan beberapa produk dagangan, dan kasir langsung siap dipakai melayani pembeli pertama Anda.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 6. FINAL CTA --}}
        <section class="py-16 sm:py-20 bg-gradient-to-br from-[#060B1E] via-[#081232] to-[#060B1E] text-white border-t border-white/10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-snug">
                    Tutup Kebocoran Kasir & Rapikan Usaha Anda Hari Ini
                </h2>
                <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    Bergabunglah bersama ribuan wirausaha UMKM di Indonesia yang telah meninggalkan cara catat manual. Gratis selamanya, tanpa resiko finansial, dan data milik Anda 100%.
                </p>
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/register" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#00C4D8] text-white font-bold text-base shadow-xl shadow-[#007AFF]/25 hover:brightness-110 transition-all">
                        <span>Buka Akun Kasir Gratis</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="/pricing" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-base border border-white/15 transition-all">
                        <span>Pelajari Paket Berbayar</span>
                    </a>
                </div>
            </div>
        </section>

    </div>
@endsection
