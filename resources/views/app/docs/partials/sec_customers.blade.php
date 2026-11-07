<!-- ===================================================== -->
<!-- SECTION: MODUL MASTER PELANGGAN & MANAJEMEN PIUTANG   -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-customers" class="space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'customers' || activeTab === 'crm') && matchesSearch('pelanggan customer master pelanggan kasbon piutang b2b hutang limit plafon nopol kendaraan mobil motor impor ekspor excel formula injection')">

    <!-- Backward-compatibility anchor for quick links -->
    <div id="sec-customer-data" class="hidden"></div>

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL CUSTOMERS -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-sky-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(37,99,235,0.35)] shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Master Data Pelanggan &amp; Manajemen Piutang</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">Multi-Industry 20 Sektor</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">Anti-Debt Fraud Guard</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Pusat kendali database pelanggan terpadu: histori transaksi POS, batas termin jatuh tempo (TOP), pagu kasbon kredit, hingga isolasi data multi-cabang.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::hasPermission('customers.view') || \App\Support\Context::isOwner())
                <a href="{{ route('customers.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Kelola Data Pelanggan</span>
                </a>
            @endif
            @if(\App\Support\Context::hasPermission('crm.view') || \App\Support\Context::isOwner())
                <a href="{{ Route::has('crm.members') ? route('crm.members') : (Route::has('crm.members.index') ? route('crm.members.index') : url('/crm/members')) }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="award" class="w-3.5 h-3.5 text-pink-500 dark:text-pink-400"></i>
                    <span>Member &amp; Loyalitas CRM</span>
                </a>
            @endif
            @if(\App\Support\Context::hasPermission('customers.export') || \App\Support\Context::isOwner())
                <a href="{{ route('customers.export') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Ekspor Excel</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Fondasi Retensi &amp; Keamanan Arus Kas: Mengapa Master Pelanggan Begitu Krusial?</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Banyak pemilik usaha UMKM mengeluhkan <strong>catatan piutang yang tercecer di buku kertas, kasbon pelanggan yang lupa ditagih, atau kasir yang sembarangan memberikan tempo tanpa batas plafon</strong>. Di COOCA, master data pelanggan bukan sekadar buku telepon, melainkan <strong>jantung operasional penjualan dan manajemen piutang (*Accounts Receivable*)</strong> yang terintegrasi otomatis dengan mesin kasir POS, faktur B2B, program loyalitas CRM, hingga penagihan invoice jatuh tempo via WhatsApp.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Manfaat Finansial & Operasional bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-blue-500/[0.06] dark:bg-blue-500/[0.1] border border-blue-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-blue-700 dark:text-blue-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="contact" class="w-4 h-4"></i>
                    <span>Profil 360° Pelanggan</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Rekam riwayat pembelian, total nominal omzet yang dihasilkan (*Customer Lifetime Value*), frekuensi kunjungan, dan preferensi produk favorit dalam satu kartu profil rapi.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                    <span>Plafon Kredit &amp; Kasbon Aman</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Kunci batas maksimal piutang per pelanggan. Kasir otomatis diblokir sistem jika mencoba memberikan kasbon tambahan saat saldo piutang telah menyentuh batas plafon.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-purple-500/[0.06] dark:bg-purple-500/[0.1] border border-purple-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-purple-700 dark:text-purple-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <span>Multi-Sektor Adaptif</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Kolom input menyesuaikan jenis usaha: bengkel memiliki kolom nopol &amp; odometer KM, toko grosir memiliki kolom NPWP &amp; termin tempo, katering memiliki catatan alamat antar.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span>Proteksi Anti-Pencurian Data</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Mantan karyawan dilarang mencuri kontak pelanggan. Tombol ekspor Excel dan penghapusan data dikunci ketat khusus Owner melalui sistem otorisasi RBAC berjenjang.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA LANGKAH DEMI LANGKAH (4 LANGKAH PRAKTIS) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-black/60 dark:text-white/60 flex items-center gap-2">
                <i data-lucide="git-commit" class="w-4 h-4 text-[#007AFF]"></i>
                <span>Alur Kerja Praktis Pengelolaan Pelanggan &amp; Piutang (4 Langkah)</span>
            </h3>
            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">SOP Standar Toko</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- Step 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">1</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Pendaftaran Profil &amp; Kontak</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Daftarkan nama, nomor WhatsApp aktif, dan alamat. Kasir dapat mendaftarkan pelanggan secara cepat di mesin kasir POS dengan mengetik nomor HP, atau admin menginput lengkap di modul pelanggan.
                </p>
                <div class="pt-1 text-[11px] font-medium text-blue-600 dark:text-blue-400">
                    Input cepat &lt; 30 detik
                </div>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">2</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Atur Plafon &amp; Termin (TOP)</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Untuk pelanggan grosir B2B atau langganan tetap, tentukan batas jatuh tempo pembayaran (*Terms of Payment*, misal 14 atau 30 hari) dan batas pagu kredit (*Credit Limit*) agar terhindar dari piutang macet.
                </p>
                <div class="pt-1 text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                    Plafon otomatis mengunci kasir
                </div>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">3</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Transaksi &amp; Akumulasi Poin</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Setiap nota belanja kasir otomatis tersambung ke profil pelanggan. Jika pelanggan membayar tunai/QRIS, poin loyalitas otomatis bertambah. Jika bertransaksi tempo, saldo piutang bertambah seketika.
                </p>
                <div class="pt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                    Otomasi sync POS &amp; Buku Besar
                </div>
            </div>

            <!-- Step 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">4</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Rekap Piutang &amp; Ekspor Excel</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Pantau daftar pelanggan yang memiliki saldo piutang berjalan. Ekspor data lengkap ke Excel dengan formula sanitasi aman untuk rekonsiliasi kasbon bulanan atau pengarsipan pajak tahunan.
                </p>
                <div class="pt-1 text-[11px] font-medium text-purple-600 dark:text-purple-400">
                    Ekspor CSV/XLSX siap audit
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS PENERAPAN NYATA UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base font-extrabold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-blue-600"></i>
                    <span>Penerapan Master Pelanggan &amp; Piutang pada 20 Jenis Usaha</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Contoh konkret pemanfaatan database pelanggan di 6 klaster industri UMKM Indonesia.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 text-xs font-bold border border-blue-500/20 self-start sm:self-auto">
                20 Model Bisnis Terverifikasi
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
                    <p><strong>1. Restoran Dine-In:</strong> Catat nomor meja favorit pelanggan keluarga, preferensi tempat duduk bebas asap rokok, serta pengingat tanggal ulang tahun untuk diskon makan gratis.</p>
                    <p><strong>2. Coffee Shop &amp; Cafe:</strong> Simpan profil pelanggan setia penyuka racikan *oat milk* atau *less sugar*, memudahkan barista melayani pesanan langganan tanpa perlu ditanya ulang.</p>
                    <p><strong>3. Bakery &amp; Toko Roti:</strong> Database pemesan kue ulang tahun dan tart custom; simpan tanggal acara dan status uang muka (DP) pesanan kue bertingkat.</p>
                    <p><strong>4. Cloud Kitchen Online:</strong> Catat alamat pengiriman detail langganan katering harian makan siang karyawan perkantoran di sekitarnya.</p>
                    <p><strong>5. Katering Prasmanan:</strong> Penagihan bertahap pesanan prasmanan pernikahan: DP 30%, termin H-7 acara 50%, dan pelunasan piutang H+1 acara setelah selesai.</p>
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
                    <p><strong>6. Konveksi &amp; Garment:</strong> Rekam data brand distro pemesan seragam kantor; tetapkan syarat pembayaran tempo (TOP 30 hari) dan batas pagu kredit pesanan kain.</p>
                    <p><strong>7. Percetakan &amp; Offset:</strong> Penagihan tempo cetak kalender dan brosur instansi; simpan file desain arsip untuk pesanan ulang (*re-order*) cepat.</p>
                    <p><strong>8. Mebel &amp; Furniture:</strong> Data klien perumahan custom; pelacakan termin pembayaran bertahap saat rangka selesai dan saat finishing cat melamik terkirim.</p>
                    <p><strong>9. Bengkel Bubut &amp; Logam:</strong> Data pabrik rekanan pemesan roda gigi presisi; simpan nomor NPWP perusahaan untuk penerbitan faktur pajak resmi.</p>
                    <p><strong>10. Kerajinan Tangan / Craft:</strong> Data toko suvenir reseller borongan antar-kota; simpan alamat ekspedisi kargo langganan untuk pengiriman volume besar.</p>
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
                    <p><strong>11. Minimarket &amp; Kelontong:</strong> Buku kasbon digital bagi warga tetangga terpercaya dengan batasan limit Rp 500.000, menggantikan buku catatan utang warung manual yang rentan hilang.</p>
                    <p><strong>12. Apotek &amp; Toko Obat:</strong> Riwayat penebusan obat rutin pasien kronis (hipertensi/diabetes), catatan riwayat alergi antibiotik, dan pengingat jadwal beli ulang obat bulanan.</p>
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
                    <p><strong>13. Bengkel Mobil &amp; Motor:</strong> Kolom khusus plat nomor kendaraan (Nopol), tipe mobil, dan kilometer odometer untuk pengingat ganti oli otomatis tiap 5.000 KM.</p>
                    <p><strong>14. Barbershop &amp; Salon:</strong> Catatan formula pewarnaan rambut langganan dan preferensi kapster favorit; kirim voucher potongan creambath saat lama tidak berkunjung.</p>
                    <p><strong>15. Laundry Kiloan:</strong> Riwayat nomor rak loker pakaian, saldo deposit cuci kiloan prabayar, dan notifikasi otomatis WhatsApp saat cucian selesai setrika.</p>
                    <p><strong>16. Cuci Mobil &amp; Detailing:</strong> Riwayat plat nomor mobil; program stempel cuci ke-6 gratis dan penagihan termin paket nano ceramic coating mobil.</p>
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
                    <p><strong>17. Kontraktor &amp; Renovasi:</strong> Profil pemilik rumah/gedung; penagihan termin bertahap SPK (DP 30%, Progres 50%, Retensi 20%) dengan tanggal jatuh tempo penagihan.</p>
                    <p><strong>18. Wedding Organizer:</strong> Kontak lengkap kedua mempelai dan orang tua; pencatatan termin pembayaran paket dekorasi, rias, katering, dan dokumentasi.</p>
                    <p><strong>19. Digital IT &amp; Creative Agency:</strong> Klien korporat langganan kontrak retainer bulanan; pencatatan NPWP, email divisi keuangan (*finance dept*), dan termin pembayaran 14 hari.</p>
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
                    <p><strong>20. Distributor Grosir B2B &amp; Agrobisnis:</strong> Mengelola ratusan toko kelontong mitra pengecer; plafon kredit ketat (misal maks Rp 15.000.000), penagihan faktur tempo 14 hari saat pengantaran barang berikutnya, dan impor/ekspor data ribuan toko via Excel dalam sekejap.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. KEAMANAN, OTORISASI STAF & PENCEGAHAN FRAUD -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold">
                <i data-lucide="shield-alert" class="w-4 h-4"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Keamanan Database &amp; Pencegahan Fraud Manipulasi Kasbon</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Mekanisme berlapis untuk menjaga integritas data pelanggan dan mencegah kebocoran piutang.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs pt-1">
            <div class="p-4 rounded-xl border border-rose-500/20 bg-rose-500/[0.04] dark:bg-rose-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Pemisahan Hak Akses (RBAC)</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Staf kasir hanya memiliki hak <code>customers.view</code> untuk memilih pembeli di kasir. Hak <code>customers.export</code> (mengunduh daftar nomor HP ke Excel) dan <code>customers.delete</code> wajib dikunci khusus akun Owner/Superadmin agar kontak pelanggan tidak dicuri karyawan yang berhenti kerja.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-rose-500/20 bg-rose-500/[0.04] dark:bg-rose-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-400">
                    <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                    <span>Anti-Deletion Deletion Guard</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem COOCA secara otomatis <strong>menolak penghapusan profil pelanggan</strong> yang masih memiliki saldo piutang berjalan (*active outstanding balance* &gt; 0). Hal ini mencegah kasir atau staf nakal menghapus data pelanggan demi menghilangkan jejak uang kasbon yang belum disetor.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-rose-500/20 bg-rose-500/[0.04] dark:bg-rose-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-rose-700 dark:text-rose-400">
                    <i data-lucide="file-code" class="w-4 h-4"></i>
                    <span>Excel Formula Injection Shield</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Fitur ekspor pelanggan menyaring karakter awalan berbahaya seperti tanda sama dengan (<code>=</code>), plus (<code>+</code>), atau at (<code>@</code>) dari nama pelanggan. Komputer Owner terlindungi dari virus makro spreadsheet saat membuka file laporan pelanggan di Microsoft Excel.
                </p>
            </div>
        </div>
    </div>

    <!-- 6. TANYA JAWAB BISNIS OWNER (FAQ) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs"
        x-data="{ expandedFaq: null }">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="help-circle" class="w-4 h-4 text-blue-600"></i>
                <span>Tanya Jawab Seputar Master Pelanggan &amp; Piutang (FAQ)</span>
            </h3>
            <span class="text-xs text-black/40 dark:text-white/40">5 Solusi Cepat</span>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 1 ? null : 1)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>1. Bagaimana cara memindahkan ratusan data pelanggan lama dari buku catatan fisik atau file Excel lama ke Cooca?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 1" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Buka menu <strong>Pelanggan &rarr; Tombol Impor Excel</strong>. Unduh template resmi yang disediakan oleh sistem, salin data nama, nomor telepon, alamat, dan saldo piutang awal Anda ke dalam kolom template, lalu unggah kembali berkas tersebut. Sistem akan memverifikasi formatnya dan memasukkan seluruh data dalam beberapa detik saja.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 2 ? null : 2)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>2. Mengapa kasir saya tidak bisa menghapus data pelanggan yang salah ketik?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 2" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Secara default sistem keamanan COOCA, staf kasir hanya diberikan izin <code>customers.view</code> dan <code>customers.edit</code> untuk mengoreksi ejaan nama atau nomor telepon. Izin menghapus (<code>customers.delete</code>) sengaja dirancang hanya untuk Owner guna melindungi aset kontak bisnis Anda dari tindakan sabotase atau penghapusan catatan kasbon yang tidak disengaja.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 3 ? null : 3)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>3. Apa yang terjadi jika pelanggan yang masih memiliki hutang/kasbon mencoba dihapus oleh Owner?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 3" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Sistem memiliki <em>Deletion Guard</em> otomatis yang akan menolak penghapusan dan menampilkan pesan error merah: *"Pelanggan tidak dapat dihapus karena masih memiliki saldo piutang berjalan"*. Pelanggan tersebut harus melunasi seluruh kewajibannya terlebih dahulu melalui menu pelunasan di CRM/Keuangan sebelum profilnya dapat dihapus dari sistem.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 4 ? null : 4)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>4. Bagaimana cara mengintegrasikan data pelanggan dengan kartu member dan perolehan poin belanja?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 4" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Integrasi ini berjalan secara otomatis. Begitu nama pelanggan didaftarkan di modul Pelanggan, profilnya langsung muncul di menu <strong>CRM &rarr; Member &amp; Poin</strong>. Setiap kali kasir memasukkan nama pelanggan pada saat transaksi di mesin kasir POS, nilai belanja akan dikonversi menjadi poin loyalitas sesuai aturan yang Anda tentukan di menu CRM.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 5 ? null : 5)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>5. Apakah data pelanggan saya bisa dilihat atau diintip oleh pemilik bisnis lain yang juga memakai Cooca?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 5" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Sama sekali tidak. COOCA menerapkan arsitektur <em>Strict Multi-Tenant Isolation</em>. Setiap data pelanggan terikat secara kriptografis pada ID bisnis Anda (<code>business_id</code>). Bisnis lain di server Cooca tidak memiliki akses sama sekali ke database pelanggan Anda.</p>
                </div>
            </div>
        </div>
    </div>

</section>
