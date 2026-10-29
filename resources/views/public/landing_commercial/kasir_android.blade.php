@extends('layouts.public_marketing')

@php
    $pageSeo = \App\Services\Seo\SeoMetadataService::getPageSeo();
@endphp

@section('title', $pageSeo['title'] ?? 'Aplikasi Kasir Android untuk Toko & Warung | COOCA')
@section('description', $pageSeo['description'] ?? 'Aplikasi kasir Android ringan untuk HP dan tablet. Download APK gratis, hubungkan printer Bluetooth thermal, dan catat transaksi kasir secara cepat.')
@section('og_title', $pageSeo['og_title'] ?? 'Aplikasi Kasir Android untuk Toko & Warung | COOCA')
@section('og_description', $pageSeo['og_description'] ?? 'Aplikasi kasir Android ringan untuk HP dan tablet. Download APK gratis, hubungkan printer Bluetooth thermal, dan catat transaksi kasir secara cepat.')
@section('canonical', url('/aplikasi-kasir-android'))
@section('og_type', 'product')

@push('seo')
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "COOCA Aplikasi Kasir Android",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Android 7.0 ke atas",
      "description": "Aplikasi kasir Android ringan untuk smartphone dan tablet dengan dukungan printer Bluetooth thermal dan scanner barcode kamera HP.",
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
          "name": "Berapa spesifikasi smartphone Android minimum untuk menjalankan aplikasi kasir COOCA?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "COOCA dirancang sangat ringan sehingga dapat berjalan lancar di smartphone Android versi 7.0 (Nougat) ke atas, RAM minimal 2GB, dan sisa ruang penyimpanan internal hanya 50MB."
          }
        },
        {
          "@type": "Question",
          "name": "Merk printer Bluetooth apa saja yang didukung oleh aplikasi kasir Android COOCA?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Semua printer struk kasir thermal Bluetooth berstandar ESC/POS ukuran 58mm maupun 80mm didukung secara langsung, termasuk merk Panda, BellaV, Iware, Eppos, Xprinter, Blueprint, dan VSC."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah pemilik toko harus membeli alat barcode scanner eksternal?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Tidak perlu. Aplikasi kasir Android COOCA dapat memanfaatkan kamera smartphone bawaan untuk memindai barcode produk secara instan dan akurat."
          }
        },
        {
          "@type": "Question",
          "name": "Bagaimana cara mengunduh dan memasang file APK aplikasi kasir Android COOCA?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Anda dapat mengunduh file APK resmi langsung melalui tautan cooca.id/download/apk di browser HP Anda, lalu ikuti petunjuk pemasangan dalam 1 menit."
          }
        },
        {
          "@type": "Question",
          "name": "Bisakah aplikasi kasir Android digunakan di tablet dalam posisi landscape (horizontal)?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sangat bisa. Tampilan aplikasi kasir Android COOCA otomatis menyesuaikan orientasi layar ponsel tegak (portrait) maupun tablet horizontal (landscape) untuk kenyamanan kasir cafe dan restoran."
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
          "name": "Aplikasi Kasir Android",
          "item": "https://cooca.id/aplikasi-kasir-android"
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
                            <span class="inline-flex w-2.5 h-2.5 rounded-full bg-[#00C4D8] animate-pulse"></span>
                            <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                                Ringan &bull; Android 7.0+ &bull; Printer Bluetooth &bull; Unduh APK
                            </p>
                        </div>

                        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.25rem] font-extrabold text-white tracking-tight leading-[1.14] text-balance">
                            {{ \App\Services\Seo\SeoMetadataService::getH1('Aplikasi Kasir Android untuk Toko, Warung & Cafe') }}
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal text-pretty max-w-2xl">
                            Ubah smartphone atau tablet Android Anda menjadi mesin kasir POS profesional dalam hitungan detik. Cepat mencatat pesanan, scan barcode kemasan sembako dengan kamera HP, dan cetak struk pembayaran thermal tanpa kabel komputer.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 text-left w-full">
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-500/20">
                                    <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Kompatibel HP RAM 2GB & Android 7+</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-[#00C4D8] flex items-center justify-center shrink-0 mt-0.5 border border-sky-400/20">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Support Semua Printer Thermal 58/80mm</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-400 flex items-center justify-center shrink-0 mt-0.5 border border-purple-400/20">
                                    <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Scan Barcode Cepat Kamera Bawaan</span>
                            </div>
                            <div class="p-3 rounded-xl bg-white/[0.05] border border-white/10 flex items-start gap-2.5">
                                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5 border border-amber-400/20">
                                    <i data-lucide="wifi-off" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs sm:text-sm font-semibold text-slate-200">Mode Kasir Offline Tetap Bekerja</span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5 pt-4">
                            <a href="/download/apk" class="inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#00C4D8] text-white font-bold text-sm sm:text-base shadow-lg shadow-[#007AFF]/30 hover:brightness-110 active:scale-[0.99] transition-all">
                                <i data-lucide="download" class="w-5 h-5"></i>
                                <span>Download APK Android</span>
                            </a>
                            <a href="/register" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-sm sm:text-base border border-white/15 transition-all">
                                <span>Daftar Akun Gratis</span>
                            </a>
                        </div>
                    </div>

                    {{-- Right Column: Specs Card --}}
                    <div class="lg:col-span-5 w-full">
                        <div class="relative rounded-2xl bg-gradient-to-b from-white/10 to-white/[0.03] border border-white/15 p-6 sm:p-8 backdrop-blur-xl shadow-2xl">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-500/20 text-[#00C4D8] border border-sky-400/30 text-xs font-bold uppercase tracking-wider mb-4">
                                <span>Spesifikasi Hardware</span>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-2">Persyaratan Minimum HP</h3>
                            <p class="text-slate-300 text-sm mb-6 pb-6 border-b border-white/10">
                                Tidak perlu modal jutaan rupiah untuk beli mesin kasir khusus. Smartphone Android bekas atau yang sedang Anda pakai saat ini sudah lebih dari cukup.
                            </p>

                            <div class="space-y-4 text-sm text-slate-200 mb-8">
                                <div class="flex items-start gap-3">
                                    <i data-lucide="cpu" class="w-4 h-4 text-[#00C4D8] mt-1 shrink-0"></i>
                                    <div>
                                        <div class="font-bold text-white">Sistem Operasi</div>
                                        <div class="text-slate-400 text-xs">Android 7.0 (Nougat) atau yang lebih baru (Android 10/11/12/13/14)</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i data-lucide="hard-drive" class="w-4 h-4 text-[#00C4D8] mt-1 shrink-0"></i>
                                    <div>
                                        <div class="font-bold text-white">Memori RAM & Kapasitas</div>
                                        <div class="text-slate-400 text-xs">RAM Minimal 2GB (Rekomendasi 3GB-4GB), Sisa Memori Internal 50MB</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i data-lucide="camera" class="w-4 h-4 text-[#00C4D8] mt-1 shrink-0"></i>
                                    <div>
                                        <div class="font-bold text-white">Kamera Belakang</div>
                                        <div class="text-slate-400 text-xs">Minimal 5 MP dengan fitur Autofokus untuk pembacaan barcode</div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <i data-lucide="bluetooth" class="w-4 h-4 text-[#00C4D8] mt-1 shrink-0"></i>
                                    <div>
                                        <div class="font-bold text-white">Konektivitas Bluetooth</div>
                                        <div class="text-slate-400 text-xs">Bluetooth 4.0 atau 5.0 BLE untuk sambungan printer thermal tanpa kabel</div>
                                    </div>
                                </div>
                            </div>

                            <a href="/download/apk" class="w-full inline-flex items-center justify-center gap-2 py-3 px-5 rounded-xl bg-white text-slate-900 font-bold text-sm hover:bg-slate-100 transition-colors">
                                <span>Unduh APK COOCA (12 MB)</span>
                                <i data-lucide="arrow-down" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. DUKUNGAN PRINTER BLUETOOTH THERMAL LENGKAP --}}
        <section class="py-16 sm:py-20 max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12 text-left">
                <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">Hardware Kompatibel</span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                    Dukungan Luas Printer Kasir Thermal Bluetooth 58mm & 80mm
                </h2>
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                    Aplikasi kasir Android COOCA menggunakan protokol standar industri ESC/POS. Anda bebas memilih printer thermal mini portable saku atau printer desktop meja tanpa khawatir terkunci merk tertentu:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center font-bold">
                        <i data-lucide="printer" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Printer Thermal 58mm Mini</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Cocok untuk warung kopi, gerobak kuliner, laundry, dan kasir keliling. Ukuran kertas ekonomis 58mm, baterai tahan lama, dan mudah dibawa ke mana saja. Merk teruji: BellaV, Panda, Iware, Eppos.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Printer Thermal 80mm Desktop</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Pilihan tepat untuk minimarket, cafe dine-in, dan restoran dengan daftar pesanan panjang. Kertas lebar 80mm dengan auto-cutter pisau pemotong nota otomatis. Merk teruji: Xprinter, Blueprint, Epson, VSC.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Struk Digital WhatsApp (Paperless)</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Habis kertas thermal saat jam sibuk? Cukup masukkan nomor WhatsApp pembeli dan kirimkan struk digital resmi lengkap dengan logo dan rincian belanja dalam satu kali klik.
                    </p>
                </div>
            </div>
        </section>

        {{-- 3. FITUR UNGGULAN APLIKASI KASIR ANDROID --}}
        <section class="py-16 sm:py-20 bg-slate-50 dark:bg-white/[0.02] border-y border-slate-200 dark:border-white/10">
            <div class="max-w-[1300px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-12 text-left">
                    <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#00C4D8] block mb-2">Desain Sentuh Modern</span>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-snug">
                        Didesain Khusus untuk Layar Sentuh yang Cepat & Nyaman
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 mt-3 leading-relaxed">
                        Antarmuka aplikasi kasir Android COOCA mengikuti prinsip desain ergonomis: tombol sentuh besar ramah jari jempol, respons kilat tanpa lag, dan alur pembayaran kasir selesai dalam 3 ketukan layar:
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="p-5 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-[#007AFF] dark:text-[#00C4D8] flex items-center justify-center font-bold mb-3">
                            <i data-lucide="zap" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">Quick Cash Button</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Tombol nominal pecahan uang pas (Rp10rb, Rp20rb, Rp50rb, Rp100rb) untuk menghitung uang kembalian secara kilat.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold mb-3">
                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">QRIS Dinamis Otomatis</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Tampilkan QR code QRIS dinamis langsung di layar HP kasir, pelanggan cukup scan menggunakan e-wallet atau m-banking.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center font-bold mb-3">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">PIN Karyawan & Shift Kasir</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Karyawan kasir login dengan PIN rahasia 4 digit. Laporan rekap pergantian shift kasir transparan mencegah kecurangan laci kas.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10 space-y-2">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold mb-3">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-slate-900 dark:text-white">Simpan Pesanan & Kasbon</h4>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                            Tahan pesanan sementara saat pembeli menambah belanjaan, atau catat sebagai transaksi kasbon hutang pelanggan dalam satu sentuhan.
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
                    Pertanyaan Seputar Aplikasi Kasir Android COOCA
                </h2>
            </div>

            <div class="space-y-4">
                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Berapa spesifikasi smartphone Android minimum untuk menjalankan aplikasi kasir COOCA?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        COOCA dirancang sangat ringan sehingga dapat berjalan lancar di smartphone Android versi 7.0 (Nougat) ke atas, RAM minimal 2GB, dan sisa ruang penyimpanan internal hanya 50MB. Anda tidak perlu membeli ponsel keluaran terbaru.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Merk printer Bluetooth apa saja yang didukung oleh aplikasi kasir Android COOCA?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Semua printer struk kasir thermal Bluetooth berstandar ESC/POS ukuran 58mm maupun 80mm didukung secara langsung, termasuk merk Panda, BellaV, Iware, Eppos, Xprinter, Blueprint, dan VSC.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Apakah pemilik toko harus membeli alat barcode scanner eksternal?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Tidak perlu. Aplikasi kasir Android COOCA dapat memanfaatkan kamera smartphone bawaan untuk memindai barcode kemasan produk secara instan dan akurat. Namun jika toko Anda memiliki scanner USB/Bluetooth eksternal, alat tersebut tetap dapat disambungkan dengan lancar.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Bagaimana cara mengunduh dan memasang file APK aplikasi kasir Android COOCA?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Anda dapat mengunduh file APK resmi langsung melalui tautan cooca.id/download/apk di browser HP Anda. Setelah file selesai terunduh, buka file tersebut dan izinkan pemasangan dari sumber ini. Aplikasi akan terpasang dalam waktu kurang dari 1 menit.
                    </p>
                </div>

                <div class="p-6 rounded-2xl bg-white dark:bg-[#060B1E] border border-slate-200 dark:border-white/10">
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-2">Bisakah aplikasi kasir Android digunakan di tablet dalam posisi landscape (horizontal)?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Sangat bisa. Tampilan aplikasi kasir Android COOCA otomatis menyesuaikan orientasi layar ponsel tegak (portrait) maupun tablet horizontal (landscape) untuk kenyamanan barista cafe, kasir minimarket, dan restoran.
                    </p>
                </div>
            </div>
        </section>

        {{-- 5. FINAL CTA --}}
        <section class="py-16 sm:py-20 bg-gradient-to-br from-[#060B1E] via-[#081232] to-[#060B1E] text-white border-t border-white/10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white tracking-tight leading-snug">
                    Pasang Aplikasi Kasir di HP Android Anda Sekarang
                </h2>
                <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                    Download file APK resmi atau daftar akun gratis via web browser. Praktis, ringan, dan siap pakai melayani pelanggan toko Anda.
                </p>
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/download/apk" class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-gradient-to-r from-[#007AFF] to-[#00C4D8] text-white font-bold text-base shadow-xl shadow-[#007AFF]/25 hover:brightness-110 transition-all">
                        <i data-lucide="download" class="w-5 h-5"></i>
                        <span>Unduh APK COOCA Gratis</span>
                    </a>
                    <a href="/register" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-4 rounded-xl bg-white/10 hover:bg-white/15 text-white font-semibold text-base border border-white/15 transition-all">
                        <span>Daftar Akun Web Browser</span>
                    </a>
                </div>
            </div>
        </section>

    </div>
@endsection
