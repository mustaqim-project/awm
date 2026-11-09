<!-- ===================================================== -->
<!-- SECTION: MODUL FAKTUR PENJUALAN & INVOICE COMMERCE   -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-invoices" class="scroll-mt-24 space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'sales_purchasing') && matchesSearch('invoice faktur penjualan tagihan tempo piutang ar jatuh tempo term payment termin cetak pdf surat jalan tender b2b kwitansi pelunasan cicilan')">

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL INVOICES -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-sky-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(0,122,255,0.35)] shrink-0">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Faktur Penjualan &amp; Penagihan Piutang (Invoices &amp; AR Hub)</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">Siklus Draf &rarr; Lunas</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Anti-Piutang Macet</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Pusat penagihan transaksi tempo B2B: terbitkan faktur resmi berkop toko, pantau jatuh tempo otomatis, catat cicilan pembayaran, dan kirim pengingat WhatsApp ke pelanggan.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('invoices.view'))
                <a href="{{ route('invoices.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                    <span>Daftar Faktur</span>
                </a>
            @endif
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('invoices.create'))
                <a href="{{ route('invoices.create') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-blue-500"></i>
                    <span>Terbitkan Faktur Baru</span>
                </a>
            @endif
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('customers.view'))
                <a href="{{ route('customers.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-indigo-500"></i>
                    <span>Buku Piutang Pelanggan</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Kendali Arus Kas B2B: Mencegah Kebocoran Piutang, Lupa Tagih, dan Dokumen Tagihan Hilang</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Banyak bisnis yang gulung tikar bukan karena sepi pembeli, melainkan karena <strong>piutang pelanggan menumpuk hingga ratusan juta dan lupa ditagih sampai lewat tempo</strong>. Dokumen faktur manual yang tercecer, kuitansi hilang, dan pelanggan yang mengelak saat ditagih adalah risiko fatal operasional UMKM. Modul Faktur COOCA memberikan kepastian penagihan: setiap pesanan tempo tercatat nomor resminya, termin pembayaran tertera jelas, cicilan DP terekam otomatis, dan dokumen PDF profesional siap kirim kapan saja.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Keunggulan Faktur COOCA bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-blue-500/[0.06] dark:bg-blue-500/[0.1] border border-blue-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-blue-700 dark:text-blue-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="calendar-clock" class="w-4 h-4"></i>
                    <span>1. Pengingat Jatuh Tempo</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem mendeteksi faktur yang mendekati atau telah melewati jatuh tempo (Net 7, 14, 30, atau 60 hari). Tombol pengingat otomatis memudahkan staf mengirim rekap ramah via WhatsApp.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <span>2. Pencatatan Pembayaran Parsial / DP</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Mendukung pembayaran bertahap (uang muka, termin progres pekerjaan, hingga pelunasan akhir). Riwayat setiap pembayaran tersimpan rapi lengkap dengan metode bayar dan referensi transfer.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-indigo-500/[0.06] dark:bg-indigo-500/[0.1] border border-indigo-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span>3. Proteksi Status Draf &amp; Rilis</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Faktur baru diawali dengan status <em>Draf</em> agar dapat diteliti ulang. Setelah dikonfirmasi rilis, faktur mengikat nilai piutang resmi dan tidak dapat dihapus sembarangan oleh staf kasir.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>4. Template Cetak &amp; PDF Eksekutif</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Format cetak bersih berstandar korporat yang memuat logo usaha, rekening bank pembayaran, rincian PPN, dan catatan syarat pembayaran (Terms &amp; Conditions) siap kirim ke mitra B2B.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA 4 LANGKAH PENGGUNAAN HARIAN -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold text-xs">
                SOP
            </span>
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Alur Operasional Penagihan 4 Langkah Mudah</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
            <!-- Langkah 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px]">1</span>
                    <span class="font-bold text-black dark:text-white">Buat Draf Faktur</span>
                </div>
                <p class="text-black/60 dark:text-white/60 leading-relaxed">
                    Buka <a href="{{ route('invoices.create') }}" class="text-[#007AFF] font-semibold underline">Terbitkan Faktur</a>. Pilih pelanggan dari kontak atau ketik nama baru, tentukan tanggal jatuh tempo (misal 30 hari), dan pilih produk atau jasa pesanan.
                </p>
            </div>

            <!-- Langkah 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px]">2</span>
                    <span class="font-bold text-black dark:text-white">Review &amp; Konfirmasi Rilis</span>
                </div>
                <p class="text-black/60 dark:text-white/60 leading-relaxed">
                    Periksa kembali kuantiti, diskon, biaya kirim, dan tarif pajak. Klik tombol <strong>Konfirmasi &amp; Rilis Faktur</strong> agar dokumen resmi terkunci dan piutang otomatis masuk ke neraca usaha.
                </p>
            </div>

            <!-- Langkah 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px]">3</span>
                    <span class="font-bold text-black dark:text-white">Kirim PDF &amp; Pantau Tempo</span>
                </div>
                <p class="text-black/60 dark:text-white/60 leading-relaxed">
                    Klik <strong>Cetak / Simpan PDF</strong> untuk mengirim dokumen ke bagian keuangan pelanggan. Gunakan tombol <strong>Kirim Pengingat</strong> bila faktur telah mendekati tanggal jatuh tempo.
                </p>
            </div>

            <!-- Langkah 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-[#007AFF] text-white flex items-center justify-center font-bold text-[11px]">4</span>
                    <span class="font-bold text-black dark:text-white">Catat Pelunasan Pembayaran</span>
                </div>
                <p class="text-black/60 dark:text-white/60 leading-relaxed">
                    Saat pelanggan mentransfer uang, klik <strong>Catat Pembayaran</strong> pada faktur terkait. Masukkan nominal yang diterima, pilih akun kas/bank penampung, dan status faktur otomatis berubah menjadi <em>Lunas</em>.
                </p>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS IMPLEMENTASI 20 SEKTOR INDUSTRI -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                    20
                </span>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Penerapan Faktur Penjualan di 20 Jenis Usaha Berbeda</h3>
            </div>
            <span class="text-[11px] font-semibold text-black/50 dark:text-white/50">6 Klaster Industri UMKM</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
            <!-- Klaster 1: Kuliner & F&B -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-bold">
                    <i data-lucide="utensils" class="w-4 h-4"></i>
                    <span>Klaster F&amp;B &amp; Katering</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>1. Restoran Dine-In:</strong> Penagihan acara gathering corporate atau private dining berkapasitas besar dengan DP di awal.</li>
                    <li><strong>2. Cafe &amp; Coffee Shop:</strong> Faktur bulanan pengadaan biji kopi roasted beans untuk cafe rekanan atau hotel.</li>
                    <li><strong>3. Bakery &amp; Toko Roti:</strong> Tagihan harian/mingguan konsinyasi roti manis ke minimarket dan kantin sekolah.</li>
                    <li><strong>4. Cloud Kitchen:</strong> Invoice langganan paket catering harian karyawan kantor (meal plan) tempo bulanan.</li>
                    <li><strong>5. Katering Prasmanan:</strong> Penagihan paket pernikahan 3 termin (DP 30%, H-7 50%, pelunasan hari-H 20%).</li>
                </ul>
            </div>

            <!-- Klaster 2: Manufaktur & HPP -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-bold">
                    <i data-lucide="factory" class="w-4 h-4"></i>
                    <span>Klaster Manufaktur &amp; HPP</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>6. Konveksi &amp; Garment:</strong> Faktur produksi 1.000 lusin seragam kerja korporat dengan rincian termin bahan dan jahit.</li>
                    <li><strong>7. Percetakan Offset:</strong> Penagihan cetak buku laporan tahunan, kalender, dan kemasan karton dengan termin 30 hari.</li>
                    <li><strong>8. Mebel Kayu:</strong> Faktur pesanan interior hotel/kantor bertahap sesuai progres pengiriman barang.</li>
                    <li><strong>9. Bengkel Bubut Logam:</strong> Tagihan pesanan komponen mesin presisi dan sparepart bubut pabrik rekanan.</li>
                    <li><strong>10. Kerajinan / Craft:</strong> Faktur ekspor atau pesanan souvenir seminar partai besar dengan lampiran rekening valuta/rupiah.</li>
                </ul>
            </div>

            <!-- Klaster 3: Ritel & Apotek -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-bold">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span>Klaster Ritel &amp; Apotek</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>11. Minimarket &amp; Grosir:</strong> Penagihan supply sembako mingguan ke warung-warung kelontong mitra binaan.</li>
                    <li><strong>12. Apotek &amp; Toko Obat:</strong> Faktur suplai obat berkala ke klinik pratama atau dokter praktik mandiri dengan nomor batch resmi.</li>
                </ul>
            </div>

            <!-- Klaster 4: Jasa Harian -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-teal-600 dark:text-teal-400 font-bold">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    <span>Klaster Jasa Servis &amp; Perawatan</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>13. Bengkel Mobil &amp; Motor:</strong> Faktur penagihan servis armada operasional perusahaan rekanan (fleet service) tempo akhir bulan.</li>
                    <li><strong>14. Salon &amp; Barbershop:</strong> Tagihan kerja sama penyewaan tempat dan layanan grooming reguler korporat.</li>
                    <li><strong>15. Laundry Kiloan / Satuan:</strong> Faktur cuci seprei &amp; linen hotel melati, kost-kostan, atau seragam pabrik bulanan.</li>
                    <li><strong>16. Cuci Mobil &amp; Detailing:</strong> Invoice pemeliharaan kendaraan operasional instansi atau rental mobil berkala.</li>
                </ul>
            </div>

            <!-- Klaster 5: Jasa Proyek -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400 font-bold">
                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                    <span>Klaster Jasa Proyek &amp; Agensi</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>17. Kontraktor &amp; Renovasi:</strong> Penagihan termin progres fisik pekerjaan lapangan (Termin 1: 30%, Termin 2: 40%, Retensi: 5%).</li>
                    <li><strong>18. Wedding Organizer:</strong> Faktur vendor all-in pengantin bertahap sejak booking tanggal hingga pelunasan H-1 acara.</li>
                    <li><strong>19. Digital &amp; IT Agency:</strong> Invoice retainer jasa bulanan social media, maintenance website, dan campaign ads.</li>
                </ul>
            </div>

            <!-- Klaster 6: Distribusi & Agro -->
            <div class="p-4 rounded-xl border border-black/5 dark:border-white/10 space-y-2.5 bg-black/[0.01] dark:bg-white/[0.02]">
                <div class="flex items-center gap-2 text-lime-600 dark:text-lime-400 font-bold">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                    <span>Klaster Distribusi &amp; Agro</span>
                </div>
                <ul class="space-y-1.5 text-black/70 dark:text-white/70 leading-relaxed text-[11.5px]">
                    <li><strong>20. Distributor Grosir B2B:</strong> Faktur pengiriman partai kontainer ke toko cabang agen luar kota lengkap dengan surat jalan dan batas kredit piutang.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 5. KEAMANAN, OTORISASI & KENDALI ANTI-FRAUD -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </span>
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Otorisasi &amp; Pengamanan Piutang Kasir</h3>
        </div>
        <p class="text-xs text-black/60 dark:text-white/60 leading-relaxed">
            Untuk mencegah staf kasir nakal yang menerbitkan faktur fiktif atau menghapus faktur yang uangnya sudah diambil sendiri, COOCA menerapkan sistem proteksi ketat:
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white">Hak Akses Berlapis (RBAC)</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60">Staf admin operasional hanya dapat melihat dan membuat draf. Pembatalan (Void) atau penghapusan faktur hanya dapat dilakukan oleh Owner atau Manajer berwenang.</p>
            </div>
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white">Kunci Faktur Non-Draft</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60">Faktur yang telah dikonfirmasi atau telah menerima pembayaran tidak dapat diubah nilainya secara sepihak untuk menjaga kepatuhan laporan pembukuan.</p>
            </div>
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white">Audit Trail Jejak Digital</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60">Setiap perubahan status faktur, pencatatan pembayaran, dan pengiriman invoice tercatat rapi di log jejak audit lengkap dengan nama pengguna dan jam transaksi.</p>
            </div>
        </div>
    </div>

    <!-- 6. PERTANYAAN UMUM PEMILIK USAHA (FAQ) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
            <i data-lucide="help-circle" class="w-4 h-4 text-[#007AFF]"></i>
            <span>Tanya Jawab Pemilik Usaha Seputar Faktur (FAQ)</span>
        </h3>

        <div class="space-y-3 text-xs">
            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white">T: Bagaimana jika pelanggan membayar lunas dalam beberapa kali transfer?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    J: Buka faktur tersebut, lalu gunakan tombol <strong>Catat Pembayaran</strong> setiap kali ada transfer masuk. Sistem akan mencatat saldo sisa piutang secara otomatis hingga nilainya Rp 0 dan status berubah menjadi Lunas.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white">T: Apakah nomor faktur bisa disesuaikan dengan format nomor perusahaan kami?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    J: Sistem secara otomatis membuatkan nomor urut unik anti-ganda (misal: INV-202611-0001). Anda juga dapat mengatur format penomoran di menu Pengaturan Usaha.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1">
                <span class="font-bold text-black dark:text-white">T: Bagaimana jika pelanggan membatalkan pesanan setelah faktur diterbitkan?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    J: Bila faktur masih berstatus Draf dan belum ada pembayaran, Anda dapat menghapusnya. Bila faktur sudah berstatus rilis/unpaid, gunakan fitur <strong>Void / Batalkan Faktur</strong> agar tetap meninggalkan jejak pembukuan yang sah bagi akuntan.
                </p>
            </div>
        </div>
    </div>
</section>

