<!-- ===================================================== -->
<!-- SECTION: MODUL PUSAT FEEDBACK & LAPORAN MASALAH       -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-feedback" class="scroll-mt-24 space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'feedback' || activeTab === 'system') && matchesSearch('feedback masukan kendala bug request fitur pengembang teknis prioritas status perbaikan saran tiket komplain bantuan engineering')">

    <!-- Backward-compatibility anchor for quick links -->
    <div id="sec-bugs-feedback" class="hidden"></div>

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL FEEDBACK -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 via-rose-500 to-red-500 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(244,63,94,0.35)] shrink-0">
                <i data-lucide="message-square-plus" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Pusat Feedback &amp; Laporan Masalah (Bug &amp; Feature Request)</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">Kesiapan Operasional</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20">Jalur Langsung Engineering</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Saluran komunikasi langsung pemilik bisnis ke tim pengembang Cooca: laporkan kendala operasional kasir/stok, ajukan ide fitur baru untuk kemajuan bisnis, dan pantau progres perbaikan transparan.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('feedback.create'))
                <a href="{{ route('feedback.bugs.create') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="bug" class="w-3.5 h-3.5"></i>
                    <span>Laporkan Kendala (Bug)</span>
                </a>
                <a href="{{ route('feedback.features.create') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400"></i>
                    <span>Ajukan Usulan Fitur</span>
                </a>
            @endif
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('feedback.view'))
                <a href="{{ route('feedback.bugs.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="list-checks" class="w-3.5 h-3.5 text-rose-500 dark:text-rose-400"></i>
                    <span>Riwayat Laporan</span>
                </a>
                <a href="{{ route('feedback.features.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="lightbulb" class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400"></i>
                    <span>Daftar Usulan Fitur</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-rose-500 to-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="life-buoy" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Bebas Frustrasi Teknis: Kendala Toko Terhubung Langsung ke Meja Pengembang Tanpa Perantara</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sebagai pemilik usaha UMKM, tidak ada yang lebih menguras tenaga selain <strong>printer kasir yang tiba-tiba macet saat antrean pelanggan sedang mengular, perhitungan diskon yang membingungkan kasir, atau ide perbaikan aplikasi yang hilang begitu saja di obrolan grup WhatsApp</strong>. Pusat Feedback Cooca dirancang sebagai <strong>meja eskalasi terintegrasi</strong>: setiap laporan kendala teknis (<em>Bug Report</em>) dan usulan kebutuhan fitur (<em>Feature Request</em>) langsung tercatat di sistem pemantauan pengembang dengan pelacakan status terbuka hingga masalah tuntas diselesaikan.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Manfaat Operasional & Kepastian bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="git-pull-request" class="w-4 h-4"></i>
                    <span>Jalur Langsung Engineering</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Tiket laporan Anda tidak mengendap di tangan sales atau customer support pihak ketiga. Laporan langsung masuk ke dashboard tim teknis inti Cooca untuk direproduksi dan diperbaiki.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-blue-500/[0.06] dark:bg-blue-500/[0.1] border border-blue-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-blue-700 dark:text-blue-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>Pelacakan Progres 0-100%</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Status penanganan transparan setiap saat: mulai dari <em>Open</em> (diterima), <em>Investigating</em> (dianalisis), <em>In Progress</em> (sedang diperbaiki), hingga <em>Resolved</em> (siap digunakan di toko).
                </p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="file-check" class="w-4 h-4"></i>
                    <span>Bukti Layar &amp; Log Aman</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sertakan foto struk keliru, screenshot layar kasir, atau berkas log transaksi hingga 5MB. Seluruh berkas disimpan terenkripsi di penyimpanan lokal privat khusus bisnis Anda.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-purple-500/[0.06] dark:bg-purple-500/[0.1] border border-purple-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-purple-700 dark:text-purple-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Pengembangan Bersama (Co-Creation)</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sampaikan fitur impian yang dibutuhkan bidang usaha Anda (misal: rumus susut kain konveksi atau barcode BPOM apotek). Permintaan populer diprioritaskan dalam siklus pembaruan rutin Cooca.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA LANGKAH DEMI LANGKAH (4 LANGKAH PRAKTIS SOP) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-black/60 dark:text-white/60 flex items-center gap-2">
                <i data-lucide="git-commit" class="w-4 h-4 text-[#007AFF]"></i>
                <span>SOP Praktis Penanganan Masalah &amp; Pengajuan Fitur (4 Langkah)</span>
            </h3>
            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">Alur Standar Pemilik Usaha</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- Step 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-red-500/15 text-red-600 dark:text-red-400 flex items-center justify-center font-bold text-xs">1</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Laporkan Kendala Teknis (Bug)</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Buka formulir <a href="{{ route('feedback.bugs.create') }}" class="text-[#007AFF] font-medium hover:underline">Laporkan Kendala</a>. Pilih kategori masalah (POS, Inventori, Keuangan, Laporan, atau Akun), tentukan tingkat urgensi (Rendah, Sedang, Tinggi, atau Kritis), dan jelaskan kronologi saat kendala muncul.
                </p>
                <div class="pt-1 text-[11px] font-medium text-red-600 dark:text-red-400">
                    Sertakan foto layar/struk
                </div>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">2</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Ajukan Usulan Kebutuhan Fitur</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Bila ada alur kerja spesifik di toko Anda yang belum terfasilitasi, gunakan formulir <a href="{{ route('feedback.features.create') }}" class="text-[#007AFF] font-medium hover:underline">Request Fitur Baru</a>. Jelaskan manfaat bisnisnya bagi toko dan bagaimana fitur tersebut dapat menghemat waktu operasional harian.
                </p>
                <div class="pt-1 text-[11px] font-medium text-amber-600 dark:text-amber-400">
                    Bantu bentuk masa depan Cooca
                </div>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">3</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Pantau Progres Tiket Terbuka</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Buka halaman <a href="{{ route('feedback.bugs.index') }}" class="text-[#007AFF] font-medium hover:underline">Riwayat Laporan</a>. Pantau badge status tiket Anda secara real-time. Setiap perubahan status dan catatan diagnosa dari tim engineering akan langsung terbarui di layar detail laporan Anda.
                </p>
                <div class="pt-1 text-[11px] font-medium text-blue-600 dark:text-blue-400">
                    Status diperbarui tim teknis
                </div>
            </div>

            <!-- Step 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">4</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Verifikasi Solusi &amp; Rilis</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Setelah tim pengembang menyelesaikan perbaikan atau merilis fitur baru (status berganti menjadi <em>Resolved</em> atau <em>Released</em>), lakukan uji coba langsung pada terminal kasir atau backoffice toko Anda untuk memastikan kelancaran operasional.
                </p>
                <div class="pt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                    Operasional kembali optimal
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS PENERAPAN NYATA UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base font-extrabold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-rose-500"></i>
                    <span>Penerapan Feedback &amp; Laporan Kendala pada 20 Jenis Usaha</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Skenario nyata pelaporan kendala dan usulan fitur spesifik per sektor UMKM Indonesia.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 text-xs font-bold border border-rose-500/20 self-start sm:self-auto">
                20 Kebutuhan Sektor Nyata
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
            <!-- Klaster 1: Kuliner & F&B -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-amber-700 dark:text-amber-400">
                        <i data-lucide="utensils" class="w-4 h-4"></i>
                        <span>1. Kuliner &amp; F&amp;B</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400">5 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>1. Restoran Dine-In:</strong> Melaporkan tiket printer dapur (KDS) yang terpotong saat pesanan meja melebihi 10 item menu berbeda.</p>
                    <p><strong>2. Coffee Shop &amp; Cafe:</strong> Mengajukan usulan fitur tombol pintas pilihan rasa sirup (ekstra vanila/karamel) langsung dari layar mesin kasir tanpa membuka popup.</p>
                    <p><strong>3. Bakery &amp; Toko Roti:</strong> Mengajukan permintaan integrasi pengingat kadaluarsa kue basah (H+2) agar stok sisa di etalase otomatis dipotong diskon sore.</p>
                    <p><strong>4. Cloud Kitchen Online:</strong> Melaporkan kendala sinkronisasi waktu cetak pesanan terpisah antara menu minuman dan menu gorengan saat jam makan siang sibuk.</p>
                    <p><strong>5. Katering Prasmanan:</strong> Mengusulkan penambahan kolom input jadwal jam pengiriman dan nama penanggung jawab gedung pada form pesanan katering.</p>
                </div>
            </div>

            <!-- Klaster 2: Manufaktur & HPP -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-blue-700 dark:text-blue-400">
                        <i data-lucide="factory" class="w-4 h-4"></i>
                        <span>2. Manufaktur &amp; HPP</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">5 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>6. Konveksi &amp; Garment:</strong> Mengusulkan rumus susut kain otomatis (persentase shrinkage 3-5%) saat mengonversi gelondongan kain yard menjadi lusin kaos oblong.</p>
                    <p><strong>7. Percetakan &amp; Offset:</strong> Melaporkan kendala desimal pada penghitungan ongkos potong kertas plano per rim di kalkulator HPP pesanan custom.</p>
                    <p><strong>8. Mebel &amp; Furniture:</strong> Meminta fitur pencatatan multi-tahap produksi (kayu mentah &rarr; perakitan kerangka &rarr; finishing cat duco) dalam modul pesanan kerja.</p>
                    <p><strong>9. Bengkel Bubut &amp; Logam:</strong> Melaporkan format ekspor nota penawaran bubut presisi yang membutuhkan tampilan logo usaha ukuran lebih besar.</p>
                    <p><strong>10. Kerajinan Tangan / Craft:</strong> Mengusulkan integrasi pilihan ekspedisi kargo khusus volumetrik besar untuk pengiriman suvenir luar pulau.</p>
                </div>
            </div>

            <!-- Klaster 3: Ritel & Apotek -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-emerald-700 dark:text-emerald-400">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        <span>3. Ritel &amp; Apotek</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">2 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>11. Minimarket &amp; Kelontong:</strong> Melaporkan keterlambatan pembacaan barcode scanner nirkabel saat memindai cepat rentengan saset bumbu dapur.</p>
                    <p><strong>12. Apotek &amp; Toko Obat:</strong> Mengajukan usulan fitur pembacaan barcode 2D e-Katalog BPOM dan kolom pencatatan nomor izin SIP dokter untuk obat resep keras.</p>
                </div>
            </div>

            <!-- Klaster 4: Jasa Harian -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-purple-700 dark:text-purple-400">
                        <i data-lucide="wrench" class="w-4 h-4"></i>
                        <span>4. Jasa Harian</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400">4 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>13. Bengkel Mobil &amp; Motor:</strong> Melaporkan kendala pencarian plat nomor kendaraan yang menggunakan spasi ganda pada form riwayat servis berkala.</p>
                    <p><strong>14. Barbershop &amp; Salon:</strong> Mengusulkan penambahan tombol kalkulasi otomatis bagi hasil komisi kapster 60:40 saat cetak rekap tutup kasir harian.</p>
                    <p><strong>15. Laundry Kiloan:</strong> Mengajukan usulan integrasi bot WhatsApp agar pelanggan otomatis menerima pesan saat status cucian berganti jadi 'Selesai Setrika'.</p>
                    <p><strong>16. Cuci Mobil &amp; Detailing:</strong> Meminta penambahan checklist inspeksi kondisi bodi kendaraan (foto baret awal) sebelum pengerjaan paket salon mobil.</p>
                </div>
            </div>

            <!-- Klaster 5: Jasa Proyek -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-cyan-700 dark:text-cyan-400">
                        <i data-lucide="hard-hat" class="w-4 h-4"></i>
                        <span>5. Jasa Proyek</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-cyan-500/10 text-cyan-600 dark:text-cyan-400">3 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>17. Kontraktor &amp; Renovasi:</strong> Mengajukan permohonan fitur penagihan bertahap 4 termin (Uang Muka 30%, Termin Plester 30%, Termin Atap 30%, Retensi 10%) dalam satu SPK.</p>
                    <p><strong>18. Wedding Organizer:</strong> Melaporkan kendala format tanggal acara pernikahan tahun depan yang belum terdaftar di kalender pemesanan gedung.</p>
                    <p><strong>19. Digital IT &amp; Creative Agency:</strong> Meminta fitur unggah bukti persetujuan brief desain (lampiran PDF) pada modul faktur penagihan klien korporat.</p>
                </div>
            </div>

            <!-- Klaster 6: Distribusi & Agro -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
                    <div class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-400">
                        <i data-lucide="truck" class="w-4 h-4"></i>
                        <span>6. Distribusi &amp; Agro</span>
                    </div>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-600 dark:text-rose-400">1 Usaha</span>
                </div>
                <div class="space-y-2 text-[11.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    <p><strong>20. Distributor Grosir B2B &amp; Agrobisnis:</strong> Melaporkan kendala pembulatan timbangan tonase gabah/sayur saat membuat surat jalan pengiriman dari gudang ke pasar induk.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. CATATAN PRIVASI & KEAMANAN DATA (DATA PRIVACY & SAFE UPLOAD) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Standar Privasi, Sanitasi Data &amp; Keamanan Berkas Lampiran</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Pedoman penting saat mengunggah tangkapan layar dan data operasional ke sistem feedback.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs pt-1">
            <div class="p-4 rounded-xl border border-rose-500/20 bg-rose-500/[0.04] dark:bg-rose-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-400">
                    <i data-lucide="eye-off" class="w-4 h-4"></i>
                    <span>Sanitasi Informasi Sensitif</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sebelum melampirkan foto tangkapan layar atau nota, <strong>tutup atau buramkan informasi rahasia</strong> seperti nomor PIN kasir, kata sandi, saldo tabungan pribadi, atau nomor KTP pelanggan. Tim teknis hanya membutuhkan tampilan pesan error dan tombol yang bermasalah.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-rose-500/20 bg-rose-500/[0.04] dark:bg-rose-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Penyimpanan Terisolasi (Multi-Tenant)</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Seluruh tiket laporan kendala terikat ketat pada ID bisnis Anda (<code>business_id</code>). Pemilik bisnis lain di server Cooca <strong>tidak dapat melihat</strong> tiket laporan, attachment gambar, atau riwayat pesan Anda ke pengembang.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-rose-500/20 bg-rose-500/[0.04] dark:bg-rose-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-400">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span>Validasi Ekstensi Berkas Aman</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem secara ketat hanya menerima berkas lampiran dengan format gambar (<code>.jpg</code>, <code>.png</code>, <code>.webp</code>) dan dokumen pendukung (<code>.pdf</code>, <code>.txt</code>, <code>.log</code>) dengan batas maksimal 5MB. Berkas executable berbahaya (<code>.exe</code>, <code>.bat</code>, <code>.sh</code>) otomatis ditolak server.
                </p>
            </div>
        </div>
    </div>

    <!-- 6. TANYA JAWAB BISNIS OWNER (FAQ) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs"
        x-data="{ expandedFaq: null }">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="help-circle" class="w-4 h-4 text-rose-500"></i>
                <span>Tanya Jawab Seputar Feedback &amp; Laporan Masalah (FAQ)</span>
            </h3>
            <span class="text-xs text-black/40 dark:text-white/40">5 Solusi Cepat</span>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 1 ? null : 1)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>1. Berapa lama rata-rata respon dan perbaikan setelah saya mengirim laporan kendala (bug)?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 1" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Untuk kendala berkategori <strong>Kritis</strong> (misal: mesin kasir tidak bisa memproses pembayaran sama sekali atau database gagal memuat), tim teknis menanggapi dalam waktu 1&ndash;4 jam kerja. Untuk kendala berkategori <strong>Sedang/Rendah</strong> (tampilan tabel tergeser atau salah ketik label teks), perbaikan diselesaikan dalam kurun 1&ndash;2 hari kerja pada siklus pembaruan patch berkala.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 2 ? null : 2)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>2. Apakah staf kasir saya bisa mengirimkan laporan kendala atau usulan fitur langsung ke Cooca?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 2" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Secara default, modul Feedback hanya dapat diakses oleh peran <strong>Owner</strong> dan pengguna yang memiliki hak <code>feedback.create</code> / <code>feedback.view</code>. Hal ini dimaksudkan agar setiap laporan teknis atau usulan fitur yang diajukan ke Cooca telah terverifikasi terlebih dahulu oleh pimpinan usaha dan relevan dengan arah strategi toko Anda.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 3 ? null : 3)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>3. Mengapa screenshot atau foto struk sangat disarankan saat melaporkan kendala teknis?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 3" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Foto layar mempercepat waktu diagnosa teknis hingga 70%. Melalui screenshot, tim pengembang dapat melihat langsung pesan kode error, nomor invoice terkait, tipe peramban web (browser), dan resolusi layar yang Anda gunakan tanpa harus bertanya bolak-balik via pesan teks.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 4 ? null : 4)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>4. Bagaimana saya tahu jika usulan fitur baru yang saya ajukan sudah disetujui atau dirilis?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 4" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Buka menu <strong>Riwayat Request Fitur</strong>. Setiap usulan yang diajukan memiliki badge status: <em>Submitted</em> (terkirim), <em>Planned</em> (masuk rencana rilis tim), <em>In Development</em> (sedang dikerjakan), hingga <em>Released</em> (telah aktif di aplikasi). Anda juga akan melihat catatan rilis dari tim engineering pada tiket tersebut.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 5 ? null : 5)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>5. Apakah ada biaya tambahan untuk meminta bantuan teknis atau melaporkan bug di Cooca?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 5" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Tidak ada biaya sama sekali (100% Gratis). Seluruh perbaikan bug, penyesuaian stabilitas sistem, dan pembaruan fitur merupakan bagian dari komitmen layanan Cooca untuk mendukung kesuksesan operasional UMKM Indonesia.</p>
                </div>
            </div>
        </div>
    </div>

</section>
