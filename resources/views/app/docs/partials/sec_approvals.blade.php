<!-- ===================================================== -->
<!-- SECTION: MODUL OTORISASI & PERSETUJUAN DOKUMEN (MAR)   -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-approvals" class="space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'approvals' || activeTab === 'security' || activeTab === 'finance') && matchesSearch('approval persetujuan otorisasi mar maker checker anti-fraud fraud manipulasi plafon nominal kasbon po spk purchase order aturan jenjang wewenang riwayat dokumen')">

    <!-- Backward-compatibility anchor for old links -->
    <div id="sec-security" class="hidden"></div>

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL APPROVALS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-600 to-cyan-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(16,185,129,0.35)] shrink-0">
                <i data-lucide="check-check" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Modul Otorisasi &amp; Persetujuan Dokumen (MAR)</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">Maker-Checker Anti-Fraud</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Mencegah fraud transaksi, kebocoran kasbon, dan diskon liar melalui sistem persetujuan berjenjang otomatis.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Approval Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @can('approvals.view')
                <a href="{{ route('approvals.inbox') }}"
                    class="h-9 px-3.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(16,185,129,0.3)] transition active:scale-95">
                    <i data-lucide="inbox" class="w-3.5 h-3.5"></i>
                    <span>Kotak Masuk Persetujuan</span>
                </a>
                <a href="{{ route('approvals.history') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="history" class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400"></i>
                    <span>Riwayat Otorisasi</span>
                </a>
            @endcan
            @can('approvals.manage')
                <a href="{{ route('approval-rules.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400"></i>
                    <span>Aturan Plafon (Rules)</span>
                </a>
            @endcan
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Benteng Pertahanan Kas Usaha: Mengapa Perlu Otorisasi Berjenjang?</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Kebocoran keuangan terbesar pada UMKM sering kali bukan karena pencurian uang fisik di laci kasir, melainkan <strong>pembelian fiktif tanpa pengawasan, kasbon pribadi staf operasional, diskon liar, atau perubahan stok barang opname yang diam-diam disusutkan</strong>. Modul Otorisasi MAR (*Multi-Approval Rules*) menerapkan prinsip tata kelola korporasi <strong>Maker-Checker</strong>: staf yang membuat pengajuan (*Maker*) tidak memiliki kuasa untuk mencairkan uang toko sebelum ditinjau dan disetujui oleh atasan atau pemilik bisnis (*Checker*).
                </p>
            </div>
        </div>

        <!-- 4 Pilar Manfaat Finansial & Operasional bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    <span>Prinsip Maker-Checker</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Memastikan pembuat draf pesanan pembelian atau pengeluaran kas kecil tidak bisa menyetujui transaksi mereka sendiri (*anti-self-approval*).
                </p>
            </div>

            <div class="p-4 rounded-xl bg-[#007AFF]/[0.06] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 space-y-1.5">
                <div class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs sm:text-sm">
                    <i data-lucide="scale" class="w-4 h-4"></i>
                    <span>Plafon Nominal Otomatis</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pembelian perlengkapan kecil di bawah plafon (misal &lt; Rp 500.000) langsung cair, sedangkan pembelian besar di atas batas otomatis memerlukan persetujuan Owner.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-purple-500/[0.06] dark:bg-purple-500/[0.1] border border-purple-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-purple-700 dark:text-purple-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="file-check-2" class="w-4 h-4"></i>
                    <span>Audit Trail Permanen</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap persetujuan mencatat secara permanen identitas penyetuju, tanggal, jam eksekusi, nominal disetujui, dan catatan audit yang tidak dapat dihapus.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="message-square-warning" class="w-4 h-4"></i>
                    <span>Penolakan Edukatif</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap penolakan tiket pengajuan mewajibkan alasan tertulis minimal 3 karakter, memberi arahan yang jelas bagi staf tanpa memicu kebingungan.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA PRAKTIS (4 LANGKAH PENGOPERASIAN) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white tracking-tight">
                    4 Langkah Menjalankan Kontrol Otorisasi Pengeluaran Toko
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50">Alur kerja terpadu dari penetapan aturan plafon hingga audit riwayat dokumen transaksi</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 self-start sm:self-auto">
                SOP Pengendalian Internal
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Langkah 1 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xs font-bold">1</span>
                            <span>Atur Aturan Plafon Nominal per Divisi</span>
                        </div>
                        @can('approvals.manage')
                            <a href="{{ route('approval-rules.index') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <span>Atur Rules</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        @endcan
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Buka menu <strong>Aturan Plafon (Rules)</strong>. Tentukan jenis dokumen yang ingin dikontrol (Pesanan Beli PO, Pengeluaran Biaya Kas Kecil, Faktur Penjualan, atau Koreksi Stok Opname). Tentukan batas nominal minimum dan maksimum (misal: Rp 1.000.000 s/d Rp 10.000.000) serta level persetujuan (1 hingga 3 tingkat berjenjang).
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Fleksibel per divisi usaha</span>
                    <span class="font-mono text-[10px] text-emerald-600 dark:text-emerald-400">Plafon: Min &amp; Max Amount</span>
                </div>
            </div>

            <!-- Langkah 2 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-xs font-bold">2</span>
                            <span>Staf Mengajukan Tiket Transaksi (Maker)</span>
                        </div>
                        <span class="text-[11px] font-semibold text-[#007AFF]">Pembuatan Draf</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Saat staf gudang membuat pesanan pembelian (PO) atau kasir mengajukan penggantian kasbon bensin, jika nominal transaksi melebihi aturan plafon, status transaksi otomatis beralih menjadi <strong>Pending Approval</strong>. Dana tidak dapat dicairkan dan stok barang belum bertambah di sistem.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Sistem terkunci otomatis</span>
                    <span class="font-bold text-[#007AFF]">Status: Pending Approval</span>
                </div>
            </div>

            <!-- Langkah 3 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-teal-500/10 text-teal-600 flex items-center justify-center text-xs font-bold">3</span>
                            <span>Notifikasi &amp; Review Persetujuan (Checker)</span>
                        </div>
                        @can('approvals.view')
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-teal-600 dark:text-teal-400 hover:underline flex items-center gap-1">
                                <span>Cek Inbox</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        @endcan
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Pemilik bisnis atau manajer yang berwenang membuka menu <strong>Kotak Masuk Persetujuan</strong>. Periksa rincian barang, supplier, kuantiti, dan total harga pengajuan. Klik <em>&ldquo;Setujui&rdquo;</em> untuk memvalidasi pencairan dana, atau klik <em>&ldquo;Tolak&rdquo;</em> dan ketikkan alasan penolakan secara jelas.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Keputusan transparan</span>
                    <span class="font-semibold text-teal-600 dark:text-teal-400">Modal Pop-up Responsif</span>
                </div>
            </div>

            <!-- Langkah 4 -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-cyan-500/10 text-cyan-600 flex items-center justify-center text-xs font-bold">4</span>
                            <span>Rekap Riwayat Audit &amp; Arsip Dokumen</span>
                        </div>
                        @can('approvals.view')
                            <a href="{{ route('approvals.history') }}" class="text-[11px] font-semibold text-cyan-600 dark:text-cyan-400 hover:underline flex items-center gap-1">
                                <span>Lihat Arsip</span>
                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                            </a>
                        @endcan
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Seluruh transaksi yang telah disetujui atau ditolak otomatis tersimpan di menu <strong>Riwayat Otorisasi</strong>. Setiap akhir bulan, auditor internal atau pemilik bisnis dapat mengunduh arsip bukti ini untuk mencocokkan catatan bank dengan dokumen pengadaan fisik toko.
                    </p>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Bukti pemeriksaan lengkap</span>
                    <span class="font-semibold text-cyan-600 dark:text-cyan-400">Log Waktu &amp; User ID</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS PENERAPAN UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-6 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">Matriks Otorisasi MAR untuk 20 Jenis Usaha (6 Klaster)</h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">
                    Contoh pengamanan transaksi berisiko tinggi dan skenario plafon persetujuan pada masing-masing sektor industri UMKM di Cooca ERP
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 shrink-0 self-start sm:self-auto">
                6 Klaster &bull; 20 Sektor Lengkap
            </span>
        </div>

        <div class="space-y-6">

            <!-- KLASTER 1: KULINER & F&B (5 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-amber-500/[0.03] dark:bg-amber-500/[0.05] border border-amber-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-amber-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="utensils" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 1: Kuliner &amp; Food &amp; Beverage (5 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Belanja bahan basah pasar, pembatalan pesanan kasir (*void*), dan kas kecil dapur</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-400">5 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">
                    <!-- 1. Restoran Dine-In -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">1. Restoran Dine-In</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">PO Daging &amp; Void</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pembelian daging impor atau seafood segar &gt; Rp 2.500.000 oleh Kepala Dapur (Head Chef).
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Manajer Operasional menyetujui level 1, Owner menyetujui level 2 sebelum kasir mengeluarkan dana tunai dari kas besar.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                <span>Verifikasi PO Restoran</span>
                            </a>
                        </div>
                    </div>

                    <!-- 2. Coffee Shop & Cafe -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">2. Coffee Shop &amp; Cafe</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Restok Kopi &amp; Sirup</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pembelian 20 kg green bean specialty coffee atau 5 karton sirup impor dari roastery rekanan.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Tiket PO wajib disetujui Head Barista dan Owner untuk memastikan beans yang dipesan sesuai jadwal roasting.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="coffee" class="w-3 h-3"></i>
                                <span>Approval Roastery Coffee</span>
                            </a>
                        </div>
                    </div>

                    <!-- 3. Bakery & Toko Roti -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">3. Bakery &amp; Toko Roti</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Butter &amp; Tepung Sak</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pengeluaran kasbon untuk pembelian 10 sak terigu protein tinggi dan 2 dus butter Prancis.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Pengeluaran bahan baku di atas Rp 3.000.000 wajib divalidasi dengan mencocokkan stok fisik di gudang tepung.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="package-check" class="w-3 h-3"></i>
                                <span>Validasi Bahan Bakery</span>
                            </a>
                        </div>
                    </div>

                    <!-- 4. Cloud Kitchen Online -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">4. Cloud Kitchen Online</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Box Kemasan &amp; Sambal</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pengadaan 5.000 pcs rice box cetak khusus dari pabrik percetakan kemasan makanan.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Wajib review invoice vendor dan bukti cetak sampel oleh Owner sebelum transfer pelunasan dilakukan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="box" class="w-3 h-3"></i>
                                <span>Otorisasi Kemasan Makanan</span>
                            </a>
                        </div>
                    </div>

                    <!-- 5. Katering Prasmanan -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">5. Katering Prasmanan</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Sewa Alat &amp; Pasar Basah</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pencairan dana belanja pasar basah katering pernikahan &gt; Rp 15.000.000 untuk 1.000 porsi prasmanan.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Tiket pengeluaran kas dicocokkan dengan nomor SPK pesanan katering dan diverifikasi pelunasan DP pengantin.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="clipboard-check" class="w-3 h-3"></i>
                                <span>Persetujuan Belanja Katering</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KLASTER 2: MANUFAKTUR & HPP (5 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-purple-500/[0.03] dark:bg-purple-500/[0.05] border border-purple-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-purple-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="factory" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 2: Manufaktur &amp; HPP Bengkel Produksi (5 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Pengadaan bahan baku rol/lembar bernilai besar, sub-kontraktor, dan koreksi stok susut</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-700 dark:text-purple-400">5 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">
                    <!-- 6. Konveksi & Garment -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">6. Konveksi &amp; Garment</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Rol Kain Katun</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pembelian 50 rol kain katun combed senilai Rp 45.000.000 ke pabrik tekstil.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Alur persetujuan 2 tingkat: Kepala Gudang (level 1) memeriksa gramasi, Direktur/Owner (level 2) merilis PO transfer.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="scissors" class="w-3 h-3"></i>
                                <span>Otorisasi Rol Kain</span>
                            </a>
                        </div>
                    </div>

                    <!-- 7. Percetakan & Offset Digital -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">7. Percetakan &amp; Offset</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Kertas Plano &amp; Plat</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pembelian 100 rim kertas art paper dan penggantian sparepart printhead mesin cetak digital.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Manajer Produksi wajib menyetujui tiket pengajuan sparepart mesin agar kontinuitas cetak terjamin.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="printer" class="w-3 h-3"></i>
                                <span>Persetujuan Kertas &amp; Tinta</span>
                            </a>
                        </div>
                    </div>

                    <!-- 8. Mebel & Furniture Kayu -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">8. Mebel &amp; Furniture Kayu</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Kayu Jati Log</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pengadaan kubikasi balok kayu jati atau lempengan besi hollow &gt; Rp 20.000.000.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Mengharuskan lampiran bukti foto legalitas kayu dan verifikasi ukuran kubikasi sebelum pencairan kas workshop.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="hammer" class="w-3 h-3"></i>
                                <span>Verifikasi Kubikasi Kayu</span>
                            </a>
                        </div>
                    </div>

                    <!-- 9. Bengkel Bubut & Logam -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">9. Bengkel Bubut &amp; Logam</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Mata Insert &amp; Baja As</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pengadaan mata insert karbida dan batang baja as VCN untuk pengerjaan poros gear pabrik.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Pengadaan perkakas potong mesin bernilai &gt; Rp 2.000.000 wajib divalidasi oleh Kepala Bengkel Bubut.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="cog" class="w-3 h-3"></i>
                                <span>Approval Perkakas Bubut</span>
                            </a>
                        </div>
                    </div>

                    <!-- 10. Kerajinan Tangan / Craft -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">10. Kerajinan Tangan / Craft</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Bahan Alami &amp; Logistik</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pembelian borongan rotan, kerang mutiara, atau resin bening dari pengepul luar pulau.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Pengajuan kasbon ekspedisi kontainer wajib melampirkan nomor resi surat jalan ekspedisi laut resmi.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="sparkles" class="w-3 h-3"></i>
                                <span>Persetujuan Bahan Kerajinan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KLASTER 3: RITEL & APOTEK (2 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-blue-500/[0.03] dark:bg-blue-500/[0.05] border border-blue-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-blue-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="store" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 3: Ritel Cepat &amp; Farmasi Apotek (2 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Penyesuaian stok opname rusak (*write-off*), diskon kasir manual, dan obat resep paten</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-700 dark:text-blue-400">2 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 text-xs">
                    <!-- 11. Minimarket & Kelontong -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">11. Minimarket &amp; Kelontong</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Opname Rusak &amp; Diskon</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pengurangan stok barang rusak bocor (*write-off*) atau pemberian diskon manual di atas 10% oleh kasir.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Penyesuaian stok opname minus bernilai &gt; Rp 100.000 wajib persetujuan Kepala Toko Minimarket untuk mencegah manipulasi selisih barang.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="shield-alert" class="w-3 h-3"></i>
                                <span>Otorisasi Koreksi Stok</span>
                            </a>
                        </div>
                    </div>

                    <!-- 12. Apotek & Toko Obat -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">12. Apotek &amp; Toko Obat</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Obat Keras &amp; Retur PBF</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Surat Pesanan (SP) obat keras daftar G ke Pedagang Besar Farmasi (PBF) atau retur obat mendekati masa kedaluwarsa.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Tiket pengadaan obat keras wajib disetujui Apoteker Penanggung Jawab (APJ) dengan verifikasi nomor SIPA sebelum faktur diterbitkan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="pill" class="w-3 h-3"></i>
                                <span>Approval SP Farmasi</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KLASTER 4: JASA HARIAN & WORKSHOP (4 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-sky-500/[0.03] dark:bg-sky-500/[0.05] border border-sky-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-sky-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="wrench" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 4: Jasa Pelayanan Harian &amp; Workshop (4 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Belanja drum oli mesin, servis mesin laundry, dan klaim kompensasi pelanggan</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-500/10 text-sky-700 dark:text-sky-400">4 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 text-xs">
                    <!-- 13. Bengkel Mobil & Motor -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">13. Bengkel Kendaraan</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Drum Oli &amp; Sparepart</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pembelian drum oli mesin 200 liter atau pengadaan ban mobil dari distributor resmi senilai belasan juta rupiah.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Service Advisor membuat draf pengajuan PO, Kepala Bengkel menyetujui, dan Owner merilis pembayaran transfer.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="wrench" class="w-3 h-3"></i>
                                <span>Verifikasi PO Bengkel</span>
                            </a>
                        </div>
                    </div>

                    <!-- 14. Barbershop & Salon -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">14. Barbershop &amp; Salon</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Krim Creambath &amp; Alat</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pembelian cat rambut premium, krim creambath galonan, atau clipper mesin cukur nirkabel baru.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Pengeluaran kas salon &gt; Rp 750.000 wajib persetujuan Manajer Cabang untuk mengontrol rasio biaya operasional.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="scissors" class="w-3 h-3"></i>
                                <span>Approval Produk Salon</span>
                            </a>
                        </div>
                    </div>

                    <!-- 15. Laundry Kiloan & Satuan -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">15. Laundry Kiloan &amp; Satuan</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Servis Boiler &amp; Klaim Pakaian</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Penggantian elemen pemanas boiler setrika uap atau klaim ganti rugi pakaian luntur/hilang milik pelanggan.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Pembayaran klaim pakaian di atas Rp 200.000 wajib diverifikasi foto nota asli dan disetujui Owner Laundry.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="waves" class="w-3 h-3"></i>
                                <span>Otorisasi Klaim Laundry</span>
                            </a>
                        </div>
                    </div>

                    <!-- 16. Cuci Mobil & Detailing -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">16. Cuci Mobil &amp; Detailing</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Shampoo Salju &amp; Kompresor</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pembelian 5 drum shampoo salju konsentrat atau perbaikan hidrolik cuci mobil yang mengalami kebocoran oli seal.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Tiket biaya perbaikan hidrolik wajib mencantumkan surat penawaran teknisi spesialis sebelum disetujui.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="car" class="w-3 h-3"></i>
                                <span>Persetujuan Perawatan Stall</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KLASTER 5: JASA PROYEK & KONTRAKTOR (3 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-emerald-500/[0.03] dark:bg-emerald-500/[0.05] border border-emerald-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-emerald-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 5: Jasa Proyek, Event &amp; Agensi (3 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Belanja material bangunan RAB, honor vendor eksternal, dan invoice termin</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">3 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
                    <!-- 17. Kontraktor & Renovasi -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">17. Kontraktor &amp; Bangunan</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Semen &amp; Besi Beton</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pembelian 200 sak semen dan besi beton bertulang &gt; Rp 30.000.000 ke toko bangunan rekanan.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Quantity Surveyor (QS) mencocokkan RAB proyek, Project Manager menyetujui level 1, Direktur menyetujui level 2.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="building" class="w-3 h-3"></i>
                                <span>Verifikasi Belanja Material</span>
                            </a>
                        </div>
                    </div>

                    <!-- 18. Event & Wedding Organizer -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">18. Wedding Organizer</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Vendor Dekor &amp; Sound</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pembayaran pelunasan sewa tenda dekorasi, lighting, panggung, dan sound system pernikahan.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Lead Event Organizer memverifikasi kelengkapan setup fisik di venue sebelum dana ditransfer ke vendor sound.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                <span>Approval Vendor Event</span>
                            </a>
                        </div>
                    </div>

                    <!-- 19. Digital IT & Creative Agency -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">19. Digital Creative Agency</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">KOL Influencer &amp; Ads</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Skenario Berisiko:</strong> Pengeluaran kasbon untuk pembayaran honor influencer/talent video komersial dan top-up saldo iklan Meta/Google Ads.
                            </p>
                            <p class="text-[11px] text-black/70 dark:text-white/70">
                                <strong>Aturan Plafon:</strong> Account Manager melampirkan proposal kampanye klien yang telah disetujui sebelum biaya iklan dicairkan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5">
                            <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="monitor" class="w-3 h-3"></i>
                                <span>Otorisasi Biaya Kampanye</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KLASTER 6: DISTRIBUSI & AGROBISNIS (1 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-teal-500/[0.03] dark:bg-teal-500/[0.05] border border-teal-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-teal-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="truck" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 6: Distribusi B2B &amp; Rantai Pasok Agro (1 Sektor Induk)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Batas plafon kredit toko mitra, muatan truk kontainer, dan retur muatan rusak</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/10 text-teal-700 dark:text-teal-400">1 Sektor</span>
                </div>

                <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-xs">20. Distributor Grosir B2B &amp; Agrobisnis</span>
                        <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Truk Kontainer &amp; Tempo</span>
                    </div>
                    <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                        <strong>Skenario Berisiko:</strong> Pengiriman pesanan barang sembako/pupuk 1 truk tronton senilai Rp 80.000.000 dengan tempo pembayaran 30 hari ke toko mitra baru.
                    </p>
                    <p class="text-[11px] text-black/70 dark:text-white/70">
                        <strong>Aturan Plafon:</strong> Sales Supervisor mengajukan permohonan plafon piutang, Manajer Keuangan memverifikasi histori pembayaran toko mitra, dan Direktur merilis surat perintah jalan.
                    </p>
                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                        <span class="text-[11px] text-black/50 dark:text-white/50">Kontrol plafon kredit toko mitra</span>
                        <a href="{{ route('approvals.inbox') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                            <i data-lucide="truck" class="w-3 h-3"></i>
                            <span>Otorisasi Pengiriman Grosir</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 5. KEAMANAN, OTORISASI AKSES (RBAC) & ANTI-FRAUD -->
    <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3 pb-2 border-b border-black/5 dark:border-white/10">
            <span class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="lock" class="w-5 h-5"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Arsitektur Keamanan &amp; Pencegahan Fraud Sistematis</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Guardrail mutlak di level controller dan database untuk mencegah penyalahgunaan kekuasaan staf</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
            <div class="p-3.5 rounded-xl bg-emerald-500/[0.04] dark:bg-emerald-500/[0.08] border border-emerald-500/15 space-y-1">
                <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-bold">
                    <i data-lucide="ban" class="w-4 h-4"></i>
                    <span>Pencegahan Self-Approval</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem mendeteksi <code>creator_id</code> pembuat draf dokumen. Pembuat draf dilarang keras menekan tombol setujui untuk dokumen buatannya sendiri, menjaga independensi evaluasi transaksi.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-[#007AFF]/[0.04] dark:bg-[#007AFF]/[0.08] border border-[#007AFF]/15 space-y-1">
                <div class="flex items-center gap-1.5 text-[#007AFF] dark:text-[#0A84FF] font-bold">
                    <i data-lucide="file-text" class="w-4 h-4"></i>
                    <span>Alasan Penolakan Wajib</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Formulir penolakan dokumen mewajibkan input teks alasan penolakan minimal 3 karakter. Sistem menolak perintah penolakan tanpa alasan agar staf pemohon memahami letak kekeliruan dokumen.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-purple-500/[0.04] dark:bg-purple-500/[0.08] border border-purple-500/15 space-y-1">
                <div class="flex items-center gap-1.5 text-purple-700 dark:text-purple-400 font-bold">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span>Multi-Tenant Boundary Guard</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Seluruh endpoint mutasi approval dilindungi verifikasi kepemilikan tenant: <code>abort_unless($approvalRequest->business_id === $business->id, 404)</code>, menjamin tidak ada manipulasi data antar-tenant.
                </p>
            </div>
        </div>
    </div>

    <!-- 6. TANYA JAWAB BISNIS OWNER (FAQ) -->
    <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-500 flex items-center justify-center font-bold text-xs">
                    <i data-lucide="help-circle" class="w-4 h-4"></i>
                </span>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Tanya Jawab Pemilik Usaha Seputar Otorisasi MAR</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">Solusi atas kekhawatiran alur persetujuan, kecepatan operasional, dan kondisi darurat</p>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">1. Apakah sistem otorisasi berjenjang ini akan membuat transaksi kasir atau operasional toko menjadi lambat?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    <strong>Sama sekali tidak.</strong> Anda memegang kendali penuh dalam menentukan nominal plafon di menu Aturan Plafon. Pengeluaran rutin kas kecil yang wajar (misal belanja es batu, bensin, atau galon di bawah Rp 500.000) dapat diatur otomatis disetujui (*auto-approved*) sehingga operasional berjalan tanpa hambatan. Sistem hanya menahan transaksi bernilai besar yang memang berisiko tinggi.
                </p>
            </div>

            <!-- FAQ 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">2. Bagaimana jika saya (pemilik bisnis) sedang di luar kota saat staf mengajukan pembelian bahan baku mendesak?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem Cooca berbasis web responsif yang dapat dibuka dengan lancar dari ponsel pintar Anda di mana saja. Anda cukup membuka peramban HP, login ke Cooca, membuka menu <strong>Kotak Masuk Persetujuan</strong>, dan menekan tombol <em>&ldquo;Setujui&rdquo;</em> dalam 5 detik tanpa perlu berada di meja kasir toko.
                </p>
            </div>

            <!-- FAQ 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">3. Apakah staf gudang atau kasir bisa menyetujui dokumen mereka sendiri saat toko sedang sepi?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    <strong>Pasti tidak bisa.</strong> Modul ini dilindungi pengaman ganda: sistem RBAC hanya menampilkan tombol persetujuan bagi pengguna dengan izin <code>approvals.manage</code>, dan backend secara mutlak memblokir upaya pengguna menyetujui transaksi yang dibuat oleh akun mereka sendiri (*anti-self-approval*).
                </p>
            </div>

            <!-- FAQ 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">4. Apa yang terjadi jika pengajuan transaksi ditolak oleh manajer atau owner?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Saat dokumen ditolak, statusnya otomatis berubah menjadi <strong>Rejected</strong> dan tersimpan di menu Riwayat Otorisasi lengkap dengan catatan alasan penolakan dari penyetuju. Dokumen tersebut terkunci permanen, dana tidak dapat dicairkan, dan staf pemohon dapat membaca catatan alasan untuk membuat draf perbaikan baru yang sesuai dengan arahan pimpinan.
                </p>
            </div>
        </div>
    </div>

</section>
