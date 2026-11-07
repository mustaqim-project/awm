<!-- ===================================================== -->
<!-- SECTION: MODUL KOMUNITAS BISNIS, EVENT & TIKET QRIS   -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-community" class="space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'community' || activeTab === 'events') && matchesSearch('komunitas community event seminar workshop bazaar tiket qris repost komen diskusi jejaring barcode checkin gps')">

    <!-- Backward-compatibility anchor for quick links -->
    <div id="sec-forum" class="hidden"></div>

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL COMMUNITY -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-teal-600 via-emerald-600 to-cyan-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(13,148,136,0.35)] shrink-0">
                <i data-lucide="messages-square" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Komunitas Bisnis, Event &amp; Tiket QRIS</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Jejaring Bisnis &amp; Kolaborasi</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">Tiket Event &amp; Presensi QRIS</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Perluas koneksi antarpemilik usaha, diskusikan strategi omzet, dan hadiri workshop tatap muka dengan tiket digital QR &amp; scan presensi.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <a href="{{ route('community.index') }}"
                class="h-9 px-3.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(13,148,136,0.3)] transition active:scale-95">
                <i data-lucide="messages-square" class="w-3.5 h-3.5"></i>
                <span>Buka Forum Komunitas</span>
            </a>
            <a href="{{ Route::has('community.events') ? route('community.events') : (Route::has('community.events.index') ? route('community.events.index') : url('/community/events')) }}"
                class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400"></i>
                <span>Jadwal Event &amp; Workshop</span>
            </a>
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-teal-600 to-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="users-round" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Bisnis Tidak Bisa Maju Sendirian: Kekuatan Kolaborasi Antarpengusaha UMKM</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Banyak pengusaha UMKM menghadapi tantangan serupa: <strong>kebingungan mengurus sertifikasi Halal/BPOM, kesulitan mencari pemasok bahan baku berkualitas dengan harga tangan pertama, atau minim ide promosi saat penjualan melambat</strong>. Modul Komunitas COOCA adalah <strong>lingkungan eksklusif dan terpercaya sesama pemilik bisnis</strong> untuk saling bertukar pengalaman nyata, menjalin kerjasama pasokan, hingga menghadiri seminar dan workshop tatap muka dengan tiket digital QRIS instan.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Manfaat Finansial & Operasional bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-teal-500/[0.06] dark:bg-teal-500/[0.1] border border-teal-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-teal-700 dark:text-teal-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Forum Diskusi Praktis</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Ajukan pertanyaan seputar perizinan, tips HPP, atau rekrutmen karyawan. Dapatkan saran langsung dari pengusaha yang telah sukses mempraktikkannya.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="badge-check" class="w-4 h-4"></i>
                    <span>Verifikasi Pengusaha Asli</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap anggota forum memiliki identitas bisnis terverifikasi. Bebas dari akun anonim, promosi spam, atau penipuan investasi yang marak di grup media sosial publik.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-cyan-500/[0.06] dark:bg-cyan-500/[0.1] border border-cyan-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-cyan-700 dark:text-cyan-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="qr-code" class="w-4 h-4"></i>
                    <span>Pendaftaran Event &amp; QRIS</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Daftar seminar atau workshop bisnis dalam hitungan detik. Pembayaran tiket berbayar terverifikasi otomatis via QRIS TriPay tanpa perlu konfirmasi manual transfer bank.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-blue-500/[0.06] dark:bg-blue-500/[0.1] border border-blue-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-blue-700 dark:text-blue-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="scan-line" class="w-4 h-4"></i>
                    <span>Check-in Tiket &amp; Radius GPS</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Masuk ke ruang seminar cukup tunjukkan QR tiket digital di layar smartphone untuk di-scan panitia, atau gunakan check-in presensi otomatis berbasis radius lokasi venue.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA LANGKAH DEMI LANGKAH (4 LANGKAH PRAKTIS) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-black/60 dark:text-white/60 flex items-center gap-2">
                <i data-lucide="git-commit" class="w-4 h-4 text-teal-600"></i>
                <span>Alur Kerja Praktis Berjejaring &amp; Menghadiri Event (4 Langkah)</span>
            </h3>
            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">SOP Standar Komunitas</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- Step 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-xs">1</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Masuk &amp; Lengkapi Profil Usaha</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Buka menu Komunitas. Profil usaha Anda otomatis terhubung dengan nama toko dan kota asal, memudahkan rekan pengusaha lain mengenali sektor usaha dan bidang keahlian Anda.
                </p>
                <div class="pt-1 text-[11px] font-medium text-teal-600 dark:text-teal-400">
                    Otomatis aktif bagi pengguna Cooca
                </div>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">2</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Berdiskusi &amp; Jalin Kolaborasi</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Unggah postingan seputar pencarian supplier, pengalaman menghadapi inflasi harga bahan, atau tips pelayanan pelanggan. Berikan like, komentar, atau repost gagasan yang menginspirasi.
                </p>
                <div class="pt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                    Didukung tagar #TipsBisnis
                </div>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-xs">3</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Pilih Event &amp; Bayar QRIS</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Buka tab Jadwal Event. Pilih seminar offline atau webinar yang ingin Anda ikuti. Untuk event berbayar, scan barcode QRIS TriPay di layar, sistem langsung mengonfirmasi status tiket Anda.
                </p>
                <div class="pt-1 text-[11px] font-medium text-cyan-600 dark:text-cyan-400">
                    Konfirmasi pembayaran 24/7 otomatis
                </div>
            </div>

            <!-- Step 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">4</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Check-in Tiket Digital di Lokasi</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Saat tiba di gedung acara, buka menu Tiket Saya di ponsel. Tunjukkan barcode unik ke panitia untuk di-scan, atau lakukan check-in instan dengan mengizinkan deteksi lokasi GPS venue.
                </p>
                <div class="pt-1 text-[11px] font-medium text-blue-600 dark:text-blue-400">
                    Tiket digital anti-palsu
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS PENERAPAN NYATA UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base font-extrabold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-teal-600"></i>
                    <span>Manfaat Komunitas &amp; Event pada 20 Jenis Usaha</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Skenario kolaborasi antarpemilik bisnis di 6 klaster industri UMKM.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 text-xs font-bold border border-teal-500/20 self-start sm:self-auto">
                20 Skenario Kolaborasi
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
                    <p><strong>1. Restoran Dine-In:</strong> Diskusi efisiensi biaya bahan pangan (*food cost*), seminar kepatuhan sertifikasi Halal MUI, dan barter kontak distributor daging segar lokal.</p>
                    <p><strong>2. Coffee Shop &amp; Cafe:</strong> Workshop teknik *cupping* biji kopi nusantara, informasi lelang green beans murah dari petani langsung, dan kolaborasi event musik akustik kafe.</p>
                    <p><strong>3. Bakery &amp; Toko Roti:</strong> Seminar tren pastry dan roti viral, tips memilih oven deck hemat daya listrik, dan sharing kontak vendor kemasan boks kardus ramah lingkungan.</p>
                    <p><strong>4. Cloud Kitchen Online:</strong> Berbagi strategi algoritma aplikasi pesan-antar makanan online (*merchant optimization*), dan sinergi menyewa dapur bersama (*shared kitchen*).</p>
                    <p><strong>5. Katering Prasmanan:</strong> Kolaborasi dengan vendor dekorasi dan wedding organizer di komunitas, serta sewa armada mobil pendingin saat jadwal hajatan padat.</p>
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
                    <p><strong>6. Konveksi &amp; Garment:</strong> Informasi distributor kain katun combed tangan pertama murah, workshop pola potong digital otomatis, dan sharing order jahitan seragam besar.</p>
                    <p><strong>7. Percetakan &amp; Offset:</strong> Kerjasama sub-kontrak saat mesin cetak sedang *overload* di musim pemilu/kalender, dan seminar standarisasi kalibrasi warna mesin cetak.</p>
                    <p><strong>8. Mebel &amp; Furniture:</strong> Temu pengrajin kayu Jepara di forum, seminar sertifikasi kelayakan kayu SVLK untuk ekspor, dan kolaborasi pameran interior perumahan.</p>
                    <p><strong>9. Bengkel Bubut &amp; Logam:</strong> Info lelang mesin bubut dan frais CNC seken berkualitas, sharing suku cadang mata pisau bubut presisi tinggi, dan seminar K3 keselamatan kerja.</p>
                    <p><strong>10. Kerajinan Tangan / Craft:</strong> Pengadaan massal bahan baku anyaman rotan/resin untuk harga grosir, dan pelatihan kurasi produk kriya siap ekspor ke mancanegara.</p>
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
                    <p><strong>11. Minimarket &amp; Kelontong:</strong> Diskusi tata letak rak barang (*planogram*) pemikat pembeli, negosiasi retur barang konsinyasi rusak, dan seminar pencegahan kehilangan stok (*shrinkage*).</p>
                    <p><strong>12. Apotek &amp; Toko Obat:</strong> Diskusi regulasi BPOM dan Kemenkes terbaru seputar pelaporan obat prekursor, dan seminar integrasi sistem resep digital dengan klinik kesehatan.</p>
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
                    <p><strong>13. Bengkel Mobil &amp; Motor:</strong> Pelatihan mekanik servis kendaraan listrik (*EV*), berbagi distributor sparepart resmi, dan workshop kalibrasi alat scanner injeksi.</p>
                    <p><strong>14. Barbershop &amp; Salon:</strong> Workshop tren potongan rambut pria dan pewarnaan rambut terkini, tips menjaga sterilisasi gunting dan pisau cukur, dan seminar manajemen komisi kapster.</p>
                    <p><strong>15. Laundry Kiloan:</strong> Standarisasi deterjen ramah lingkungan tanpa merusak serat kain, tips penanganan komplain pakaian terkena luntur, dan seminar konversi mesin pengering gas.</p>
                    <p><strong>16. Cuci Mobil &amp; Detailing:</strong> Sertifikasi teknisi nano ceramic coating, pelatihan detailing ruang mesin mobil mewah, dan seminar instalasi sistem daur ulang air cuci mobil.</p>
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
                    <p><strong>17. Kontraktor &amp; Renovasi:</strong> Sinergi konsorsium untuk memenangkan tender renovasi gedung besar, kolaborasi arsitek dengan kontraktor sipil, dan sharing mandor tukang terpercaya.</p>
                    <p><strong>18. Wedding Organizer:</strong> Wadah perkumpulan vendor dekorasi, MUA, fotografer, katering, dan sound system untuk menciptakan paket pernikahan terpadu yang kompetitif.</p>
                    <p><strong>19. Digital IT &amp; Creative Agency:</strong> Diskusi penerapan AI untuk mempercepat produksi konten klien, seminar strategi pitching perusahaan multinasional, dan kontrak kerja sama legal.</p>
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
                    <p><strong>20. Distributor Grosir B2B &amp; Agrobisnis:</strong> Pertemuan langsung antara kelompok tani komoditas pangan dengan pengusaha katering dan restoran besar, serta seminar manajemen logistik rantai pasok dingin (*cold chain*).</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. KEAMANAN MODERASI KONTEN, ANTI-SPAM & VERIFIKASI TIKET -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Keamanan Komunitas: Ruang Diskusi Terbuka yang Aman &amp; Bebas Penipuan</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Sistem penyaringan otomatis dan proteksi transaksi tiket untuk kenyamanan seluruh anggota.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs pt-1">
            <div class="p-4 rounded-xl border border-teal-500/20 bg-teal-500/[0.04] dark:bg-teal-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-teal-700 dark:text-teal-400">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Filter Kata Terlarang Otomatis</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem <em>Word Filter Engine</em> otomatis memindai dan memblokir postingan maupun komentar yang memuat promosi judi online (judol), pinjol ilegal, tautan phishing, pelecehan, atau penipuan investasi bodong.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-teal-500/20 bg-teal-500/[0.04] dark:bg-teal-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-teal-700 dark:text-teal-400">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span>Proteksi IDOR &amp; Kepemilikan Post</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pengguna hanya dapat menyunting atau menghapus postingan milik akunnya sendiri. Tidak ada pihak lain yang dapat memanipulasi komentar Anda. Penghapusan konten yang melanggar aturan dipusatkan pada tim moderator resmi Cooca.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-teal-500/20 bg-teal-500/[0.04] dark:bg-teal-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-teal-700 dark:text-teal-400">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    <span>Verifikasi QRIS &amp; Anti-Tiket Palsu</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Tiket seminar berbayar hanya terbit setelah gateway TriPay mengonfirmasi status dana transfer berhasil. Setiap barcode tiket dienkripsi unik sekali pakai (*single-use*) dan diverifikasi langsung di gerbang masuk lokasi acara.
                </p>
            </div>
        </div>
    </div>

    <!-- 6. TANYA JAWAB BISNIS OWNER (FAQ) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs"
        x-data="{ expandedFaq: null }">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="help-circle" class="w-4 h-4 text-teal-600"></i>
                <span>Tanya Jawab Seputar Komunitas, Event &amp; Tiket (FAQ)</span>
            </h3>
            <span class="text-xs text-black/40 dark:text-white/40">5 Solusi Cepat</span>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 1 ? null : 1)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>1. Siapa saja yang bisa melihat postingan dan profil saya di Komunitas Cooca?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 1" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Postingan di Komunitas Cooca bersifat terbuka bagi seluruh pengguna aktif ekosistem Cooca di Indonesia (baik pengguna paket Gratis, Standard, Premium, maupun Prestige). Namun, data rahasia seperti laporan laba rugi, nilai omzet kasir, dan daftar inventori toko Anda tetap tersimpan rahasia dan tidak akan pernah dibagikan ke publik.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 2 ? null : 2)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>2. Bagaimana cara mendaftar event berbayar dan di mana saya bisa melihat tiket digital saya?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 2" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Buka menu <strong>Komunitas &rarr; Jadwal Event</strong>, pilih acara yang ingin Anda ikuti lalu klik tombol *"Daftar Sekarang"*. Sistem akan menampilkan kode QRIS resmi TriPay. Anda dapat memindainya melalui BCA Mobile, Mandiri Livin', GoPay, OVO, atau aplikasi m-banking apa pun. Begitu pembayaran selesai, tiket digital ber-barcode akan langsung tersedia di tab *"Tiket Saya"*.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 3 ? null : 3)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>3. Bagaimana cara melakukan check-in saat tiba di venue acara tatap muka?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 3" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Ada dua metode check-in: (1) Buka tiket di ponsel Anda dan tunjukkan barcode kepada panitia di meja registrasi untuk di-scan secara instan, atau (2) Klik tombol *"Check-in via Lokasi GPS"* di dalam aplikasi Cooca ponsel Anda saat berada di dalam radius lokasi gedung acara.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 4 ? null : 4)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>4. Apakah saya bisa membatalkan pendaftaran event jika berhalangan hadir?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 4" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Untuk event gratis, Anda dapat membatalkan pendaftaran kapan saja melalui menu tiket agar kuota kursi dapat dialihkan kepada rekan pemilik usaha lain. Untuk event seminar berbayar, ketentuan pengembalian dana (*refund*) mengikuti syarat &amp; ketentuan yang ditetapkan oleh panitia penyelenggara acara terkait.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 5 ? null : 5)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>5. Apa yang harus saya lakukan jika menemukan postingan spam atau promosi mencurigakan?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 5" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Klik tombol titik tiga di sudut postingan dan pilih *"Laporkan Konten"*, atau hubungi tim bantuan Cooca. Tim pengawas kami akan meninjau laporan Anda dalam waktu maksimal 1x24 jam dan dapat menonaktifkan akun yang terbukti melanggar norma komunitas atau melakukan penipuan.</p>
                </div>
            </div>
        </div>
    </div>

</section>
