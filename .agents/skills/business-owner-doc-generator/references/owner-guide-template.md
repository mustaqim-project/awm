# TEMPLATE STANDAR DOKUMEN PANDUAN BISNIS OWNER (BLADE & CONTENT SPEC)

Dokumen referensi ini menyajikan struktur markup Blade dan gaya penulisan standar untuk membuat seksi dokumentasi baru di `resources/views/app/docs/` yang kompatibel dengan **Bento Apple HIG**, live search Alpine.js, dan responsive design.

---

## 🏛️ Template Struktur Blade untuk `resources/views/app/docs/`

```blade
<!-- ===================================================== -->
<!-- SECTION: [NAMA MODUL]                                 -->
<!-- ===================================================== -->
<section id="sec-[modul-slug]" class="space-y-4"
    x-show="(activeTab === 'all' || activeTab === '[modul-slug]') && matchesSearch('[kata kunci pencarian]')">

    <!-- 1. Header Seksi & Tombol Navigasi Langsung -->
    <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center font-bold text-sm">
                [Nomor Urut]
            </span>
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-black dark:text-white tracking-tight">[Judul Modul Lengkap]</h2>
                <p class="text-xs text-black/50 dark:text-white/50">[1 Baris Ringkasan Manfaat Utama bagi Pemilik Usaha]</p>
            </div>
        </div>

        <!-- Tombol Aksi Langsung ke Halaman Fitur (route) -->
        <div class="flex items-center gap-2">
            @if(\App\Support\Context::hasPermission('[permission_name]'))
                <a href="{{ route('[route.name]') }}"
                   class="h-8 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[11.5px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.25)] transition active:scale-95">
                    <i data-lucide="[icon-name]" class="w-3.5 h-3.5"></i>
                    <span>Buka [Nama Fitur]</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. Ringkasan Eksekutif & Nilai Bisnis Owner -->
    <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-3 shadow-2xs">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#007AFF] to-[#5856D6] text-white flex items-center justify-center shrink-0">
                <i data-lucide="[icon-lucide]" class="w-5 h-5"></i>
            </div>
            <div class="flex-1 space-y-1">
                <h3 class="text-sm font-bold text-black dark:text-white">Mengapa Fitur Ini Penting untuk Bisnis Anda?</h3>
                <p class="text-xs text-black/60 dark:text-white/60 leading-relaxed">
                    [Deskripsikan masalah nyata yang sering dialami owner UMKM: kerugian, salah hitung, waktu terbuang, atau kecurangan staf, dan bagaimana modul ini mengatasinya secara otomatis].
                </p>
            </div>
        </div>

        <!-- Metrik Keuntungan Finansial / Waktu -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-xs">
            <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                <span class="text-[10.5px] font-medium text-black/50 dark:text-white/50 block">Efisiensi Waktu</span>
                <strong class="text-xs font-bold text-emerald-600 dark:text-emerald-400">[Nilai Penghematan, mis: Hemat 2 Jam/Hari]</strong>
            </div>
            <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                <span class="text-[10.5px] font-medium text-black/50 dark:text-white/50 block">Kontrol Keuangan</span>
                <strong class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]">[Nilai Kontrol, mis: 100% Tercatat Otomatis]</strong>
            </div>
            <div class="p-2.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5">
                <span class="text-[10.5px] font-medium text-black/50 dark:text-white/50 block">Pencegahan Kebocoran</span>
                <strong class="text-xs font-bold text-purple-600 dark:text-purple-400">[Nilai Keamanan, mis: Deteksi Fraud Instan]</strong>
            </div>
        </div>
    </div>

    <!-- 3. Alur Kerja Langkah demi Langkah (Step-by-Step Walkthrough) -->
    <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Cara Penggunaan Langkah demi Langkah</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
            <!-- Langkah 1 -->
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5">
                <div class="flex items-center gap-1.5 font-bold text-[#007AFF]">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[11px]">1</span>
                    <span>[Langkah 1: Nama Alur]</span>
                </div>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    [Instruksi praktis yang mudah dijalankan staf atau pemilik].
                </p>
            </div>

            <!-- Langkah 2 -->
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5">
                <div class="flex items-center gap-1.5 font-bold text-[#007AFF]">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[11px]">2</span>
                    <span>[Langkah 2: Nama Alur]</span>
                </div>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    [Instruksi praktis].
                </p>
            </div>

            <!-- Langkah 3 -->
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5">
                <div class="flex items-center gap-1.5 font-bold text-[#007AFF]">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[11px]">3</span>
                    <span>[Langkah 3: Nama Alur]</span>
                </div>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    [Instruksi praktis].
                </p>
            </div>

            <!-- Langkah 4 -->
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1.5">
                <div class="flex items-center gap-1.5 font-bold text-[#007AFF]">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-[11px]">4</span>
                    <span>[Langkah 4: Nama Alur]</span>
                </div>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    [Instruksi praktis].
                </p>
            </div>
        </div>
    </div>

    <!-- 4. Matriks Penerapan Nyata untuk 20 Jenis Usaha Berbeda -->
    <div class="p-5 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                Penerapan Nyata untuk 20 Jenis Usaha di Cooca
            </h3>
            <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                6 Klaster Industri
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">
            <!-- Klaster 1: Kuliner & F&B -->
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <span class="font-bold text-[#007AFF] block pb-1 border-b border-black/5 dark:border-white/5">🍽️ Kuliner &amp; F&amp;B (5 Usaha)</span>
                <ul class="space-y-1.5 text-[11.5px] text-black/70 dark:text-white/70">
                    <li><strong>Restoran Dine-In:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Coffee Shop / Cafe:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Bakery / Roti:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Cloud Kitchen:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Katering Prasmanan:</strong> [Contoh penerapan spesifik]</li>
                </ul>
            </div>

            <!-- Klaster 2: Manufaktur & HPP -->
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <span class="font-bold text-indigo-600 dark:text-indigo-400 block pb-1 border-b border-black/5 dark:border-white/5">🏭 Manufaktur &amp; HPP (5 Usaha)</span>
                <ul class="space-y-1.5 text-[11.5px] text-black/70 dark:text-white/70">
                    <li><strong>Konveksi / Garment:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Percetakan Offset:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Mebel / Furniture:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Bengkel Bubut / Logam:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Kerajinan / Craft:</strong> [Contoh penerapan spesifik]</li>
                </ul>
            </div>

            <!-- Klaster 3: Ritel & Apotek -->
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <span class="font-bold text-emerald-600 dark:text-emerald-400 block pb-1 border-b border-black/5 dark:border-white/5">🛒 Ritel &amp; Apotek (2 Usaha)</span>
                <ul class="space-y-1.5 text-[11.5px] text-black/70 dark:text-white/70">
                    <li><strong>Minimarket / Kelontong:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Apotek / Toko Obat:</strong> [Contoh penerapan spesifik]</li>
                </ul>
            </div>

            <!-- Klaster 4: Jasa Operasional Harian -->
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <span class="font-bold text-amber-600 dark:text-amber-400 block pb-1 border-b border-black/5 dark:border-white/5">🔧 Jasa Harian (4 Usaha)</span>
                <ul class="space-y-1.5 text-[11.5px] text-black/70 dark:text-white/70">
                    <li><strong>Bengkel Mobil &amp; Motor:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Barbershop &amp; Salon:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Laundry Kiloan:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Cuci Mobil &amp; Detailing:</strong> [Contoh penerapan spesifik]</li>
                </ul>
            </div>

            <!-- Klaster 5: Jasa Proyek & Kreatif -->
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <span class="font-bold text-purple-600 dark:text-purple-400 block pb-1 border-b border-black/5 dark:border-white/5">📐 Jasa Proyek (3 Usaha)</span>
                <ul class="space-y-1.5 text-[11.5px] text-black/70 dark:text-white/70">
                    <li><strong>Kontraktor / Renovasi:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Event / Wedding Organizer:</strong> [Contoh penerapan spesifik]</li>
                    <li><strong>Digital IT Agency:</strong> [Contoh penerapan spesifik]</li>
                </ul>
            </div>

            <!-- Klaster 6: Distribusi & Agrobisnis -->
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <span class="font-bold text-teal-600 dark:text-teal-400 block pb-1 border-b border-black/5 dark:border-white/5">🚛 Distribusi &amp; Agro (1 Usaha)</span>
                <ul class="space-y-1.5 text-[11.5px] text-black/70 dark:text-white/70">
                    <li><strong>Distributor Grosir / Agro:</strong> [Contoh penerapan spesifik]</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 5. Callout Otorisasi & Tips Pemilik Usaha -->
    <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/50 flex items-start gap-3 text-xs text-blue-900 dark:text-blue-200">
        <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
        <div>
            <strong class="font-bold">Otorisasi &amp; Kontrol Akses:</strong>
            <span> [Jelaskan perizinan RBAC atau siapa yang boleh mengakses fitur ini (Owner only vs Staf)].</span>
        </div>
    </div>
</section>
```
