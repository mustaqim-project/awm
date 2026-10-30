@extends('layouts.public_marketing')

@section('title', 'Keamanan Sistem & Perlindungan Data Usaha | COOCA')
@section('description', 'Komitmen keamanan COOCA untuk melindungi operasional bisnis UMKM Indonesia: isolasi data multi-tenant, enkripsi AES-256, audit log aktivitas, dan kepatuhan UU PDP No. 27/2022.')
@section('og_title', 'Keamanan Sistem & Perlindungan Data | COOCA')
@section('og_description', 'Pelajari bagaimana COOCA mengamankan data transaksi kasir, keuangan, dan data pelanggan UMKM dengan arsitektur multi-tenant berstandar enterprise.')
@section('canonical', url('/security'))

@push('seo')
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebPage",
      "name": "Keamanan Sistem & Perlindungan Data COOCA",
      "description": "Dokumentasi standar keamanan, isolasi tenant, enkripsi, dan kepatuhan regulasi UU PDP No. 27/2022 pada platform COOCA.",
      "url": "{{ url('/security') }}",
      "publisher": {
        "@type": "Organization",
        "name": "COOCA",
        "url": "{{ url('/') }}"
      }
    }
    </script>
@endpush

@section('content')
<div class="w-full font-sans antialiased bg-[#F2F2F7] dark:bg-[#000000] text-[#1D1D1F] dark:text-[#F5F5F7] transition-colors pb-24">

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 1. HERO SECTION (Midnight Blue Bento Cockpit) ═════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="relative w-full min-w-full bg-[#060B1E] text-white overflow-hidden border-b border-white/10 min-h-[calc(80svh-4rem)] flex items-center">
        <!-- Ambient Glows -->
        <div class="absolute top-1/4 -right-24 w-96 h-96 bg-[#007AFF]/20 rounded-full blur-[120px] pointer-events-none -z-0"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-[#00C4D8]/15 rounded-full blur-[140px] pointer-events-none -z-0"></div>

        <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full pt-10 pb-16 lg:py-20 text-center lg:text-left">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8 space-y-5">
                    <div class="flex items-center justify-center lg:justify-start gap-2.5">
                        <span class="inline-flex w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <p class="text-xs sm:text-[13px] font-bold uppercase tracking-wider text-[#00C4D8]">
                            TRANSPARANSI KEAMANAN SISTEM &amp; PRIVASI
                        </p>
                    </div>

                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-[1.15]">
                        Data Usaha Anda Adalah Milik Anda 100%. Kami Menjaganya Tanpa Kompromi.
                    </h1>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal max-w-2xl">
                        Dari transaksi struk kasir, data pelanggan, hingga pembukuan laba rugi—arsitektur COOCA dirancang dengan isolasi ketat antar-usaha, enkripsi berlapis, dan kepatuhan penuh terhadap Undang-Undang Perlindungan Data Pribadi (UU PDP No. 27/2022).
                    </p>

                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-2 text-xs text-slate-300">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Isolasi Multi-Tenant</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="lock" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Enkripsi AES-256</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="file-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                            <span>Audit Log Lengkap</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="scale" class="w-4 h-4 text-[#00C4D8] shrink-0"></i>
                            <span>Kepatuhan UU PDP RI</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 flex justify-center">
                    <div class="w-44 h-44 sm:w-52 sm:h-52 rounded-3xl bg-gradient-to-tr from-[#007AFF]/20 via-[#00C4D8]/20 to-emerald-500/10 border border-white/15 backdrop-blur-xl flex flex-col items-center justify-center text-center p-6 shadow-2xl">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-3">
                            <i data-lucide="shield" class="w-8 h-8"></i>
                        </div>
                        <div class="text-lg font-bold text-white">Zero Trust Architecture</div>
                        <div class="text-xs text-slate-400 mt-1">Tenant-Scoped Isolation</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 2. 5 PILAR KEAMANAN UTAMA (Bento Cards) ═════════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="py-16 sm:py-20 max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">
                ARSITEKTUR &amp; PERLINDUNGAN
            </span>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                5 Pilar Keamanan yang Aktif Berjalan di Setiap Transaksi
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Setiap baris kode di COOCA diuji untuk memastikan data usaha Anda terlindungi dari kebocoran, modifikasi tanpa izin, maupun akses lintas toko.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Pilar 1: Tenant Data Isolation -->
            <div class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4 hover:border-[#007AFF]/30 transition-all flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                        <i data-lucide="layers" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        1. Isolasi Data Multi-Tenant yang Ketat
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Setiap kueri database pada sistem COOCA secara otomatis dibatasi pada <code>business_id</code> pemilik usaha. Data penjualan, produk, dan laporan keuangan toko Anda terisolasi secara komputasional dan mustahil diakses atau diintip oleh pemilik usaha lain.
                    </p>
                </div>
                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Tervalidasi di Seluruh Model Data</span>
                </div>
            </div>

            <!-- Pilar 2: Cryptography & Encryption -->
            <div class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4 hover:border-purple-500/30 transition-all flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-[14px] bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                        <i data-lucide="key" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        2. Enkripsi Standar Perbankan (AES-256 &amp; Bcrypt)
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Kata sandi pengguna di-hash dengan algoritma Bcrypt irreversibel. Seluruh kredensial sensitif, token integrasi marketplace, dan webhook dienkripsi menggunakan AES-256-CBC. Jalur transmisi data dilindungi protokol TLS/HTTPS 1.3 berkecepatan tinggi.
                    </p>
                </div>
                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Enkripsi Rest &amp; Transit</span>
                </div>
            </div>

            <!-- Pilar 3: Audit Trail -->
            <div class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4 hover:border-amber-500/30 transition-all flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-[14px] bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i data-lucide="activity" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        3. Rekam Jejak Audit Log Lengkap
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Aktivitas operasional penting—seperti pembatalan transaksi kasir (void), perubahan harga produk, pergantian stok, hingga perubahan hak akses staf—tercatat dalam modul <code>AuditLog</code> dengan stempel waktu, identitas pengguna, dan alamat IP.
                    </p>
                </div>
                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Pencegahan Kecurangan Kasir</span>
                </div>
            </div>

            <!-- Pilar 4: OWASP & Rate Limiting -->
            <div class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4 hover:border-emerald-500/30 transition-all flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-[14px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="shield-alert" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        4. Proteksi Web Terhadap Serangan Siber (OWASP)
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Sistem dilengkapi perlindungan otomatis terhadap serangan Cross-Site Request Forgery (CSRF), SQL Injection via PDO parameterized query, XSS sanitization, serta rate limiting (pembatasan frekuensi request) untuk mencegah upaya tebak sandi paksa (brute-force).
                    </p>
                </div>
                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Perlindungan Otomatis 24/7</span>
                </div>
            </div>

            <!-- Pilar 5: UU PDP Compliance -->
            <div class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4 hover:border-sky-500/30 transition-all flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-[14px] bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                        <i data-lucide="book-check" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        5. Kepatuhan UU Perlindungan Data Pribadi (UU PDP)
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        COOCA patuh terhadap ketentuan UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi Republik Indonesia. Kami menjamin hak subjek data—mulai dari hak melihat data, memperbarui data, hingga hak meminta penghapusan akun usaha sewaktu-waktu.
                    </p>
                </div>
                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Kedaulatan Data UMKM</span>
                </div>
            </div>

            <!-- Pilar 6: Cloud Infrastructure Snapshot Backup -->
            <div class="p-6 sm:p-7 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4 hover:border-slate-500/30 transition-all flex flex-col justify-between">
                <div class="space-y-3">
                    <div class="w-12 h-12 rounded-[14px] bg-slate-500/10 text-slate-600 dark:text-slate-400 flex items-center justify-center">
                        <i data-lucide="cloud" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                        Pencadangan Data Cloud (Infrastructure Snapshot)
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Basis data aplikasi dicadangkan secara berkala di level infrastruktur cloud server melalui volume snapshot terenkripsi. Hal ini memastikan ketahanan data dari insiden kegagalan perangkat keras (disaster recovery) dengan integritas data yang terjaga.
                    </p>
                </div>
                <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Pencadangan Level Infrastruktur Cloud</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- ═══ 3. TRANSPARANSI KEBIJAKAN & ROADMAP KEAMANAN ═════════════════════════ -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <section class="py-10 max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-6 sm:p-8 rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">KOMITMEN KEJUJURAN KONTEN</span>
                <h3 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white mt-1">
                    Prinsip Transparansi Keamanan COOCA
                </h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                <div class="space-y-2">
                    <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
                        <span>Apa yang Kami Jamin:</span>
                    </h4>
                    <p>
                        Kami tidak pernah dan tidak akan pernah menjual atau menyewakan data operasional, nomor kontak pelanggan, riwayat belanja, atau omzet bisnis Anda kepada pihak pengiklan mana pun. Seluruh data transaksi adalah hak milik eksklusif pemilik usaha yang bersangkutan.
                    </p>
                </div>
                <div class="space-y-2">
                    <h4 class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                        <span>Roadmap Keamanan Selanjutnya:</span>
                    </h4>
                    <p>
                        Otentikasi Dua Faktor berbasis aplikasi (MFA TOTP via Google Authenticator) dan enkripsi file nota di level storage mandiri saat ini sedang dalam tahapan pengujian tim pengembang kami dan akan dirilis secara bertahap untuk akun paket tingkat lanjut.
                    </p>
                </div>
            </div>

            <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-wrap items-center justify-between gap-4">
                <span class="text-xs text-slate-500">Pertanyaan seputar keamanan data? Hubungi tim privasi kami.</span>
                <div class="flex items-center gap-3">
                    <a href="{{ route('public.privacy') }}" class="text-xs font-semibold text-[#007AFF] hover:underline">
                        Baca Kebijakan Privasi Lengkap &rarr;
                    </a>
                    <a href="{{ route('public.contact.index') }}" class="px-4 py-2 rounded-xl bg-[#007AFF] text-white text-xs font-bold hover:bg-[#0066DF] transition">
                        Hubungi Tim Teknis
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
