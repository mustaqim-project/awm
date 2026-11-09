<!-- ===================================================== -->
<!-- SECTION: MODUL TARIF TENAGA KERJA & MESIN PRODUKSI    -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-labor-machines" class="scroll-mt-24 space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'inventory') && matchesSearch('labor machines tenaga kerja ongkos tukang tarif upah mesin produksi biaya listrik kwh depresiasi susut hpp manufaktur pabrik bengkel konveksi')">

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL LABOR & MACHINES -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-600 via-orange-600 to-yellow-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(245,158,11,0.35)] shrink-0">
                <i data-lucide="cog" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Tarif Upah Kerja &amp; Biaya Mesin (Labor &amp; Machine Costs)</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20">Akurasi HPP Manufaktur</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Anti-Boncos Produksi</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Kalkulasi biaya riil operasional: hitung tarif ongkos tukang per jam/unit, susut penyusutan mesin, dan konsumsi listrik per kilowatt-hour untuk penetapan HPP produk yang tepat.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('labor_machines.view'))
                <a href="{{ route('labor-machines.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                    <span>Kelola Upah &amp; Mesin</span>
                </a>
            @endif
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('inventory.view'))
                <a href="{{ route('inventory.products.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="boxes" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span>Katalog Resep BOM Produk</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-600 to-orange-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="calculator" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Jangan Jual Rugi: Perhitungan HPP Riil yang Menggabungkan Bahan Baku, Ongkos Tukang &amp; Penyusutan Mesin</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Kesalahan paling sering terjadi pada pemilik usaha manufaktur dan jasa adalah <strong>hanya menghitung harga beli bahan baku saat menentukan harga jual produk</strong>. Akibatnya, saat mesin pabrik rusak atau tagihan listrik industri datang di akhir bulan, keuntungan yang dibayangkan mendadak habis. Modul Upah &amp; Mesin COOCA memastikan setiap menit kerja tukang dan setiap detik putaran mesin pabrik terhitung proporsional ke dalam Harga Pokok Penjualan (HPP), sehingga margin keuntungan Anda benar-benar bersih dan aman.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Perhitungan Biaya COOCA bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    <span>1. Tarif Upah Fleksibel</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Dukung berbagai basis penggajian UMKM: gaji bulanan (dikonversi otomatis ke tarif per jam efektif), upah harian, borongan per unit, hingga ongkos subkontraktor luar.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-orange-500/[0.06] dark:bg-orange-500/[0.1] border border-orange-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-orange-700 dark:text-orange-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="cpu" class="w-4 h-4"></i>
                    <span>2. Penyusutan Mesin Otomatis</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Masukkan harga beli mesin, nilai sisa, dan estimasi umur pakai jam kerja. Sistem secara otomatis menghitung biaya depresiasi per jam mesin tanpa perlu rumus rumit.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-yellow-500/[0.06] dark:bg-yellow-500/[0.1] border border-yellow-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-yellow-700 dark:text-yellow-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                    <span>3. Alokasi Listrik &amp; Maintenance</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Hitung beban listrik mesin berdasarkan daya (kW) dan tarif PLN per kWh, ditambah alokasi biaya servis berkala untuk setiap jam mesin bekerja.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="pie-chart" class="w-4 h-4"></i>
                    <span>4. Integrasi Langsung ke BOM</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Data tarif tenaga kerja dan mesin ini langsung terhubung dengan formula Resep / Bill of Materials (BOM) produksi di modul Inventori untuk kalkulasi otomatis.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. RUMUS & LANGKAH KERJA PRAKTIS -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">
                RUMUS
            </span>
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Bagaimana COOCA Menghitung Biaya Operasional Anda?</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <span class="font-bold text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                    <i data-lucide="user" class="w-3.5 h-3.5"></i>
                    <span>Formula Upah Tenaga Kerja per Jam Efektif</span>
                </span>
                <p class="text-black/60 dark:text-white/60 leading-relaxed">
                    Bila staf digaji bulanan Rp 4.400.000 dengan 22 hari kerja dan 8 jam per hari, maka jam kerja standar adalah 176 jam/bulan.<br>
                    Jika tingkat utilisasi kerja produktif adalah 80%:
                </p>
                <div class="p-2.5 rounded-lg bg-black/5 dark:bg-white/5 font-mono text-[11px] text-black dark:text-white">
                    Tarif per Jam = Gaji / (Hari &times; Jam &times; Utilisasi)<br>
                    = Rp 4.400.000 / (22 &times; 8 &times; 0.8) = Rp 31.250 / jam
                </div>
            </div>

            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <span class="font-bold text-orange-700 dark:text-orange-400 flex items-center gap-1.5">
                    <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                    <span>Formula Total Biaya Mesin per Jam Operasi</span>
                </span>
                <p class="text-black/60 dark:text-white/60 leading-relaxed">
                    Biaya mesin per jam terdiri dari 3 komponen utama:
                </p>
                <div class="p-2.5 rounded-lg bg-black/5 dark:bg-white/5 font-mono text-[11px] text-black dark:text-white">
                    Biaya Mesin/Jam = Depresiasi + Listrik + Maintenance<br>
                    - Depresiasi = (Harga Beli - Nilai Sisa) / Umur Jam<br>
                    - Listrik = Daya (kW) &times; Tarif PLN per kWh<br>
                    - Maintenance = Alokasi servis berkala per jam
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS PENERAPAN PADA 20 SEKTOR INDUSTRI -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">
                    20
                </span>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Penerapan Biaya Upah &amp; Mesin di 20 Jenis Usaha</h3>
            </div>
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50">6 Klaster Industri UMKM</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
            <!-- Klaster 1: Kuliner & F&B -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-bold">
                    <i data-lucide="utensils" class="w-4 h-4"></i>
                    <span>F&amp;B &amp; Bakery</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>1. Restoran Dine-In:</strong> Alokasi upah chef/cook per batch masak catering prasmanan.</li>
                    <li><strong>2. Coffee Shop:</strong> Biaya listrik &amp; penyusutan mesin espresso 2-group per shot espresso.</li>
                    <li><strong>3. Bakery &amp; Roti:</strong> Tarif mesin oven deck gas/listrik dan planetary mixer adonan 20kg.</li>
                    <li><strong>4. Cloud Kitchen:</strong> Perhitungan upah staf prep kitchen per paket meal prep mingguan.</li>
                    <li><strong>5. Katering:</strong> Ongkos asisten masak harian dan susut kompor high-pressure catering.</li>
                </ul>
            </div>

            <!-- Klaster 2: Manufaktur & HPP -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-bold">
                    <i data-lucide="factory" class="w-4 h-4"></i>
                    <span>Manufaktur &amp; HPP Kunci</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>6. Konveksi &amp; Garment:</strong> Ongkos jahit obras per lusin, potong kain, dan susut mesin jahit industri.</li>
                    <li><strong>7. Percetakan Offset:</strong> Tarif mesin cetak Heidelberg 4 warna dan mesin potong kertas polar per rim.</li>
                    <li><strong>8. Mebel Kayu:</strong> Upah tukang serut/amplas kayu dan biaya listrik mesin spindle milling 3 kW.</li>
                    <li><strong>9. Bengkel Bubut Logam:</strong> Tarif mesin bubut lathe, frais CNC, dan upah operator bersertifikat.</li>
                    <li><strong>10. Kerajinan Tangan:</strong> Ongkos perajin borongan per buah produk ukiran atau anyaman.</li>
                </ul>
            </div>

            <!-- Klaster 3: Ritel & Apotek -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-bold">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span>Ritel &amp; Farmasi</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>11. Minimarket:</strong> Alokasi biaya repacking beras/gula dari karung 50kg ke kemasan 1kg.</li>
                    <li><strong>12. Apotek:</strong> Biaya jasa peracikan obat puyer/kapsul racikan oleh asisten apoteker.</li>
                </ul>
            </div>

            <!-- Klaster 4: Jasa Harian -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-teal-600 dark:text-teal-400 font-bold">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    <span>Jasa Servis &amp; Bengkel</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>13. Bengkel Mobil &amp; Motor:</strong> Tarif mekanik per jam kerja PKB dan susut alat hidrolik lift &amp; kompresor.</li>
                    <li><strong>14. Barbershop &amp; Salon:</strong> Biaya pemakaian mesin hair dryer, steamer rambut, dan komisi kapster.</li>
                    <li><strong>15. Laundry:</strong> Biaya listrik, air, dan penyusutan mesin cuci komersial 15kg serta dryer gas.</li>
                    <li><strong>16. Cuci Mobil:</strong> Tarif operator cuci dan biaya listrik pompa air high pressure washer 2.2 kW.</li>
                </ul>
            </div>

            <!-- Klaster 5: Jasa Proyek -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold">
                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                    <span>Jasa Proyek &amp; Desain</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>17. Kontraktor:</strong> Upah mandor, tukang batu, kenek harian, dan sewa mesin molen cor semen.</li>
                    <li><strong>18. Wedding Organizer:</strong> Biaya kru lapangan per event dan susut genset sound system.</li>
                    <li><strong>19. Digital &amp; IT Agency:</strong> Billing rate desainer/developer per jam kerja sprint proyek klien.</li>
                </ul>
            </div>

            <!-- Klaster 6: Distribusi & Agro -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-lime-600 dark:text-lime-400 font-bold">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                    <span>Distribusi &amp; Agro</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>20. Distributor Grosir B2B:</strong> Biaya operator forklift, buruh angkut muat kontainer, dan susut armada truk pendingin.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 5. KEAMANAN & OTORISASI RBAC -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs">
                <i data-lucide="lock" class="w-4 h-4"></i>
            </span>
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Otorisasi &amp; Perlindungan Data Biaya Rahasia</h3>
        </div>
        <p class="text-xs text-black/60 dark:text-white/60 leading-relaxed">
            Data nominal gaji staf dan harga beli aset mesin adalah informasi strategis bisnis yang harus dilindungi:
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white">Izin Khusus (labor_machines.manage)</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60">Hanya Owner dan Manajer Produksi dengan izin khusus yang dapat mengubah atau menghapus tarif upah dan data mesin. Staf operator hanya melihat daftar tarif untuk pemilihan resep.</p>
            </div>
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white">Pemisahan Antar-Tenant (Multi-Tenant Isolation)</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60">Setiap data upah dan mesin terkunci ke ID usaha aktif. Sistem mencegah akses silang data aset mesin antar cabang atau antar usaha yang berbeda.</p>
            </div>
        </div>
    </div>

    <!-- 6. PERTANYAAN UMUM PEMILIK USAHA (FAQ) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
            <i data-lucide="help-circle" class="w-4 h-4 text-amber-600"></i>
            <span>Tanya Jawab Seputar Tarif Upah &amp; Biaya Mesin (FAQ)</span>
        </h3>

        <div class="space-y-3 text-xs">
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white">T: Apakah saya wajib mengisi data mesin bila usaha saya hanya toko ritel biasa?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    J: Tidak wajib. Fitur ini bersifat modular. Jika usaha Anda adalah toko ritel murni tanpa proses produksi/perakitan, Anda dapat mengabaikan modul ini atau menonaktifkannya di pengaturan modul.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white">T: Apa yang terjadi pada produk yang sudah jadi jika saya mengubah tarif upah pekerja?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    J: Nilai HPP pada transaksi produksi masa lalu tetap terkunci untuk menjaga keakuratan laporan pembukuan historis. Perubahan tarif baru hanya akan diterapkan pada perintah kerja (Work Order) produksi berikutnya.
                </p>
            </div>
        </div>
    </div>
</section>

