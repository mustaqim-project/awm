@extends('layouts.public_marketing')

@php
    $pageSeo = \App\Services\Seo\SeoMetadataService::getPageSeo();
@endphp

@section('title', $pageSeo['title'] ?? 'Aplikasi Kasir Offline, Jalan Tanpa Internet | COOCA')
@section('description', $pageSeo['description'] ?? 'Aplikasi kasir offline yang tetap berfungsi saat internet mati. Transaksi kasir tersimpan aman di perangkat dan otomatis sinkron saat kembali online.')
@section('og_title', $pageSeo['og_title'] ?? 'Aplikasi Kasir Offline, Jalan Tanpa Internet | COOCA')
@section('og_description', $pageSeo['og_description'] ?? 'Aplikasi kasir offline yang tetap berfungsi saat internet mati. Transaksi kasir tersimpan aman di perangkat dan otomatis sinkron saat kembali online.')
@section('canonical', url('/aplikasi-kasir-offline'))
@section('og_type', 'product')

@push('seo')
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA Aplikasi Kasir Offline",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web, Android, Windows, macOS",
      "description": "Aplikasi kasir offline yang tetap dapat mencatat penjualan dan mencetak struk thermal saat sinyal internet mati atau lambat.",
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
          "name": "Bagaimana cara kerja aplikasi kasir offline COOCA saat koneksi internet terputus?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "COOCA menyimpan katalog produk dan daftar harga di memori lokal perangkat (IndexedDB / Local Cache). Saat offline, transaksi kasir tetap dicatat normal dengan nomor nota lokal dan dimasukkan ke antrean sinkronisasi (sync queue)."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah printer struk thermal Bluetooth tetap bisa mencetak saat tidak ada internet?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Bisa 100%. Komunikasi antara smartphone atau tablet kasir dengan printer thermal menggunakan sambungan Bluetooth lokal peer-to-peer tanpa bergantung pada koneksi internet cloud."
          }
        },
        {
          "@type": "Question",
          "name": "Apa yang terjadi ketika koneksi internet di toko kembali online?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sistem otomatis mendeteksi koneksi internet yang pulih dan langsung mengunggah seluruh antrean transaksi offline ke server cloud tanpa ada data yang hilang atau terduplikasi."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah metode pembayaran QRIS dinamis bisa digunakan dalam mode kasir offline?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Tidak bisa. QRIS dinamis membutuhkan koneksi internet aktif untuk memverifikasi settlement status pembayaran bank secara real-time. Namun, pembayaran tunai (cash) dan QRIS statis tetap dapat dicatat lancar saat offline."
          }
        },
        {
          "@type": "Question",
          "name": "Berapa banyak transaksi penjualan offline yang dapat disimpan di dalam perangkat HP?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Memori lokal perangkat mampu menampung ribuan antrean transaksi offline. Anda tidak perlu khawatir saat listrik padam atau jaringan provider bermasalah sepanjang hari."
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
          "name": "Aplikasi Kasir Offline",
          "item": "https://cooca.id/aplikasi-kasir-offline"
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
                            <span class="inline-flex w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Tanpa Internet &bull; Auto Sinkron Cloud &bull; Cetak Bluetooth Lokal
                            </p>
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.14] text-balance">
                            {{ \App\Services\Seo\SeoMetadataService::getH1('Aplikasi Kasir Offline: Tetap Jalan Tanpa Sinyal Internet') }}
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            Jangan biarkan mati lampu atau sinyal internet lambat menghentikan antrean kasir toko Anda. COOCA tetap bekerja normal mencatat penjualan tunai, menghitung kembalian, dan mencetak struk thermal meskipun tanpa koneksi internet sama sekali.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-left w-full">
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-500/20">
                                    <i data-lucide="wifi-off" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Tetap Transaksi Saat Internet Mati Total</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Auto-Sync Otomatis Begitu Online</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Cetak Thermal Bluetooth Tanpa Sinyal</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="database" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Enkripsi Penyimpanan Lokal Aman</span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-4">
                            <a href="/register" class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#00C4D8] text-white font-bold text-sm sm:text-base shadow-lg shadow-[#007AFF]/30 hover:brightness-110 active:scale-[0.99] transition-all">
                                <span>Coba Kasir Offline Gratis</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                            <a href="/aplikasi-kasir-android" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm sm:text-base border border-white/15 transition-all">
                                <span>Lihat Versi Android</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Architecture Sync Diagram Bento --}}
                    <div class="lg:col-span-5 w-full">
                        <div class="relative rounded-2xl bg-gradient-to-b from-white/10 to-white/[0.03] border border-white/15 p-6 sm:p-8 backdrop-blur-xl shadow-2xl space-y-5">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-400/30 text-xs font-bold uppercase tracking-wider">
                                <span>Arsitektur Sinkronisasi</span>
                            </div>
                            <h3 class="text-2xl font-bold text-white">Bagaimana Mode Offline Bekerja?</h3>
                            
                            <div class="space-y-4 text-xs sm:text-sm text-slate-200">
                                <div class="p-4 rounded-xl bg-white/5 border border-white/10 flex items-start gap-3">
                                    <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center shrink-0 font-bold text-xs">1</span>
                                    <div>
                                        <div class="font-bold text-white text-sm">Penyimpanan Katalog Lokal</div>
                                        <div class="text-slate-400 text-xs mt-0.5">Daftar produk, harga, dan stok dicadangkan di IndexedDB perangkat saat online pertama kali.</div>
                                    </div>
                                </div>

                                <div class="p-4 rounded-xl bg-white/5 border border-white/10 flex items-start gap-3">
                                    <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-900 flex items-center justify-center shrink-0 font-bold text-xs">2</span>
                                    <div>
                                        <div class="font-bold text-white text-sm">Antrean Transaksi Tanpa Sinyal</div>
                                        <div class="text-slate-400 text-xs mt-0.5">Setiap penjualan tunai menghasilkan nomor nota unik lokal dan dimasukkan ke Antrean Sinkronisasi (Sync Queue).</div>
                                    </div>
                                </div>

                                <div class="p-4 rounded-xl bg-white/5 border border-white/10 flex items-start gap-3">
                                    <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-900 flex items-center justify-center shrink-0 font-bold text-xs">3</span>
                                    <div>
                                        <div class="font-bold text-white text-sm">Pengunggahan Otomatis ke Cloud</div>
                                        <div class="text-slate-400 text-xs mt-0.5">Begitu internet kembali tersambung, background worker otomatis mengunggah seluruh nota tanpa ada data hilang.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PENJELASAN JUJUR & REALISTIS --}}
        <section class="py-16 sm:py-20 max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12 text-left">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">Transparansi Operasional</span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    Apa yang Berjalan Lancar Saat Offline & Apa yang Membutuhkan Internet?
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                    Kami menjunjung tinggi kejujuran teknologi. Tidak semua fitur cloud dapat berjalan tanpa internet. Berikut panduan transparan tentang batasan dan kapabilitas mode offline COOCA:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Bisa Berjalan Offline --}}
                <div class="p-6 sm:p-8 rounded-2xl bg-emerald-500/[0.04] border border-emerald-500/20 space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 font-bold text-xs uppercase">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        <span>100% Berfungsi Tanpa Internet</span>
                    </div>
                    <ul class="space-y-3 text-sm text-slate-700 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"></i>
                            <span><strong>Pencarian Produk & Scan Barcode:</strong> Database produk tersimpan lokal sehingga scan barcode tetap instan tanpa loading.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"></i>
                            <span><strong>Pembayaran Tunai (Cash):</strong> Menghitung total belanja, diskon, dan uang kembalian secara presisi.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"></i>
                            <span><strong>Cetak Struk Printer Thermal:</strong> Sambungan Bluetooth peer-to-peer mencetak nota kasir secara langsung tanpa perantara server.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500 mt-0.5 shrink-0"></i>
                            <span><strong>Rekap Kas Shift Kasir:</strong> Laporan ringkas total penjualan per shift kasir tetap dapat dihitung secara lokal di perangkat.</span>
                        </li>
                    </ul>
                </div>

                {{-- Memerlukan Internet --}}
                <div class="p-6 sm:p-8 rounded-2xl bg-amber-500/[0.04] border border-amber-500/20 space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/15 text-amber-700 dark:text-amber-400 font-bold text-xs uppercase">
                        <i data-lucide="wifi" class="w-3.5 h-3.5"></i>
                        <span>Membutuhkan Koneksi Internet</span>
                    </div>
                    <ul class="space-y-3 text-sm text-slate-700 dark:text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="info" class="w-4 h-4 text-amber-500 mt-0.5 shrink-0"></i>
                            <span><strong>QRIS Dinamis Real-Time:</strong> Pengecekan status dana masuk otomatis dari bank memerlukan koneksi internet aktif. Pembeli tetap bisa bayar QRIS statis dan kasir mencatat manual.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="info" class="w-4 h-4 text-amber-500 mt-0.5 shrink-0"></i>
                            <span><strong>Kirim Nota ke WhatsApp Pelanggan:</strong> Pengiriman pesan API WhatsApp membutuhkan jaringan internet untuk menjangkau nomor pembeli.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i data-lucide="info" class="w-4 h-4 text-amber-500 mt-0.5 shrink-0"></i>
                            <span><strong>Sinkronisasi Multi-Cabang & Toko Online:</strong> Pemotongan stok di cabang lain atau marketplace Shopee/TikTok baru akan terupdate setelah kasir online kembali.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        {{-- 3. ALUR OPERASIONAL DI TOKO SAAT LISTRIK PADAM --}}
        <section class="py-16 sm:py-20 bg-slate-50 dark:bg-white/[0.02] border-y border-slate-200 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-12 text-left">
                    <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">Simulasi Lapangan</span>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        SOP Kasir Saat Internet Padam di Toko
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Dengan COOCA, staf kasir Anda tidak perlu panik mengeluarkan buku tulis manual saat modem wifi mati. Cukup ikuti prosedur standar ini:
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-500 block">Langkah 1</span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Lanjutkan Transaksi Kasir</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Aplikasi akan memunculkan lencana kuning "Mode Offline". Kasir tetap memindai produk, menerima pembayaran tunai, dan menyerahkan kembalian seperti biasa.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-sky-500 block">Langkah 2</span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Cetak Struk Bukti Belanja</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Printer thermal Bluetooth tetap mencetak struk fisik untuk diserahkan ke pelanggan sebagai bukti transaksi yang sah.
                        </p>
                    </div>

                    <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-500 block">Langkah 3</span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Sambungkan Kembali Sinyal</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            Saat wifi menyala kembali atau HP tersambung paket data tethering, seluruh transaksi otomatis tersinkronisasi dan laporan omzet cloud langsung terupdate.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 4. FAQ SECTION --}}
        <section class="py-16 sm:py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">Tanya Jawab</span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Pertanyaan Seputar Fitur Kasir Offline COOCA
                </h2>
            </div>

            <div class="space-y-4">
                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Bagaimana cara kerja aplikasi kasir offline COOCA saat koneksi internet terputus?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        COOCA menyimpan katalog produk dan daftar harga di memori lokal perangkat (IndexedDB / Local Cache). Saat offline, transaksi kasir tetap dicatat normal dengan nomor nota lokal dan dimasukkan ke antrean sinkronisasi (sync queue).
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Apakah printer struk thermal Bluetooth tetap bisa mencetak saat tidak ada internet?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Bisa 100%. Komunikasi antara smartphone atau tablet kasir dengan printer thermal menggunakan sambungan Bluetooth lokal peer-to-peer tanpa bergantung pada koneksi internet cloud.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Apa yang terjadi ketika koneksi internet di toko kembali online?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Sistem otomatis mendeteksi koneksi internet yang pulih dan langsung mengunggah seluruh antrean transaksi offline ke server cloud tanpa ada data yang hilang atau terduplikasi.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Apakah metode pembayaran QRIS dinamis bisa digunakan dalam mode kasir offline?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Tidak bisa. QRIS dinamis membutuhkan koneksi internet aktif untuk memverifikasi settlement status pembayaran bank secara real-time. Namun, pembayaran tunai (cash) dan QRIS statis tetap dapat dicatat lancar saat offline.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Berapa banyak transaksi penjualan offline yang dapat disimpan di dalam perangkat HP?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Memori lokal perangkat mampu menampung ribuan antrean transaksi offline. Anda tidak perlu khawatir saat listrik padam atau jaringan provider bermasalah sepanjang hari.
                    </p>
                </div>
            </div>
        </section>

        {{-- 5. FINAL CTA --}}
        <section class="py-16 sm:py-20 bg-gradient-to-br from-[#060B1E] via-[#081232] to-[#060B1E] text-white border-t border-white/10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-snug">
                    Kasir Tetap Melayani, Toko Tetap Menghasilkan Omzet
                </h2>
                <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    Dapatkan keandalan aplikasi kasir POS offline yang siap melindungi operasional bisnis toko Anda dari kendala jaringan internet.
                </p>
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/register" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#00C4D8] text-white font-bold text-base shadow-xl shadow-[#007AFF]/25 hover:brightness-110 transition-all">
                        <span>Coba Kasir Offline Sekarang</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="/aplikasi-kasir-gratis" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-base border border-white/15 transition-all">
                        <span>Pelajari Paket Gratis</span>
                    </a>
                </div>
            </div>
        </section>

    </div>
@endsection
