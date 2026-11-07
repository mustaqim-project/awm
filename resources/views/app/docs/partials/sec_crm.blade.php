<!-- ===================================================== -->
<!-- SECTION: MODUL CRM, LOYALITAS MEMBER & VOUCHER PROMO  -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-crm" class="space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'crm' || activeTab === 'marketing') && matchesSearch('crm loyalitas member poin voucher diskon kupon tiering whatsapp wa broadcast repeat order struk kasbon pelunasan piutang')">

    <!-- Backward-compatibility anchor for quick links -->
    <div id="sec-loyalty" class="hidden"></div>

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL CRM -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-pink-600 via-rose-600 to-purple-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(225,29,72,0.35)] shrink-0">
                <i data-lucide="heart" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">CRM, Loyalitas Poin &amp; Kupon Diskon</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">Customer Retention Engine</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">WhatsApp Gateway</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Tingkatkan repeat order dan loyalitas pelanggan lewat program poin otomatis, tingkatan membership, kupon diskon, struk digital WhatsApp, dan pelunasan kasbon.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::hasPermission('crm.view') || \App\Support\Context::isOwner())
                <a href="{{ Route::has('crm.members') ? route('crm.members') : (Route::has('crm.members.index') ? route('crm.members.index') : url('/crm/members')) }}"
                    class="h-9 px-3.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(225,29,72,0.3)] transition active:scale-95">
                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                    <span>Kelola Member &amp; Poin</span>
                </a>
                <a href="{{ Route::has('crm.vouchers') ? route('crm.vouchers') : (Route::has('crm.vouchers.index') ? route('crm.vouchers.index') : url('/crm/vouchers')) }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="ticket" class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400"></i>
                    <span>Voucher Promo Diskon</span>
                </a>
            @endif
            @if(\App\Support\Context::hasPermission('customers.view') || \App\Support\Context::isOwner())
                <a href="{{ route('customers.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="users" class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400"></i>
                    <span>Master Pelanggan</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-pink-600 to-rose-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Rahasia Bisnis Bertumbuh: Pelanggan yang Kembali Membawa 70% Omzet Usaha Anda</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Mencari pelanggan baru membutuhkan biaya iklan dan promosi hingga <strong>5 kali lipat lebih besar</strong> dibandingkan membuat pelanggan lama belanja kembali (*Repeat Purchase*). Modul CRM COOCA mengubah pembeli satu kali menjadi pelanggan setia melalui <strong>sistem reward poin otomatis di kasir, kenaikan level membership (Silver, Gold, Platinum), voucher potongan harga terukur, serta tanda terima struk digital via WhatsApp</strong> yang langsung menyapa nama pelanggan secara personal.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Manfaat Finansial & Operasional bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-pink-500/[0.06] dark:bg-pink-500/[0.1] border border-pink-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-pink-700 dark:text-pink-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                    <span>Kalkulasi Poin POS Otomatis</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap rupiah belanja otomatis terakumulasi menjadi poin tanpa perlu kartu stempel kertas. Poin dapat langsung dipotongkan kasir sebagai pengurang nilai bayar di kasir.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-purple-500/[0.06] dark:bg-purple-500/[0.1] border border-purple-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-purple-700 dark:text-purple-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="crown" class="w-4 h-4"></i>
                    <span>Tingkatan Membership (Tier)</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Pelanggan otomatis naik status dari Silver ke Gold hingga Platinum berdasarkan total belanja bulanan, memberikan kebanggaan emosional dan reward diskon eksklusif.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="ticket-percent" class="w-4 h-4"></i>
                    <span>Kupon Diskon Strategis</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Buat kode voucher promo untuk dongkrak omzet di hari sepi (misal: Diskon Selasa Hemat). Tetapkan kuota batas klaim dan minimum belanja agar margin keuntungan tetap aman.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    <span>Struk WhatsApp &amp; Anti-Lapping</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Kirim nota resmi belanja dan bukti pelunasan kasbon via WhatsApp. Sistem Anti-Lapping memastikan uang pelunasan piutang langsung masuk ke kas toko tanpa bisa diselewengkan kasir.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA LANGKAH DEMI LANGKAH (4 LANGKAH PRAKTIS) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-black/60 dark:text-white/60 flex items-center gap-2">
                <i data-lucide="git-commit" class="w-4 h-4 text-pink-600"></i>
                <span>Alur Kerja Praktis Pengoperasian CRM &amp; Retensi Pelanggan (4 Langkah)</span>
            </h3>
            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">SOP Standar Loyalitas</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- Step 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-pink-500/15 text-pink-600 dark:text-pink-400 flex items-center justify-center font-bold text-xs">1</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Tentukan Aturan Poin &amp; Tier</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Buka menu CRM &rarr; Aturan Loyalitas. Tetapkan rasio perolehan poin (contoh: belanja Rp 10.000 = dapat 1 poin senilai Rp 100) dan ambang batas omzet untuk mencapai tingkatan Silver, Gold, atau Platinum.
                </p>
                <div class="pt-1 text-[11px] font-medium text-pink-600 dark:text-pink-400">
                    Setup sekali berlaku otomatis
                </div>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">2</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Terbitkan Voucher Promo</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Buat kode kupon diskon (misal: <code>HEMAT20</code> atau <code>KOPISEGAR</code>). Tentukan tipe diskon (persentase atau nominal rupiah tetap), batas masa berlaku tanggal, dan jumlah maksimal penggunaan.
                </p>
                <div class="pt-1 text-[11px] font-medium text-purple-600 dark:text-purple-400">
                    Dapat disebar via WA &amp; Medsos
                </div>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">3</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Identifikasi &amp; Klaim di Kasir</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Saat transaksi di POS, kasir mengetik nama atau nomor WhatsApp pembeli. Sistem langsung menampilkan status tier, saldo poin yang bisa ditukar diskon, dan field input voucher promo yang valid.
                </p>
                <div class="pt-1 text-[11px] font-medium text-blue-600 dark:text-blue-400">
                    Potong total belanja instan
                </div>
            </div>

            <!-- Step 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">4</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Kirim Nota WA &amp; Lacak Retensi</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Kirim struk digital ke WhatsApp pelanggan berikut ucapan terima kasih dan saldo poin terkini. Pantau grafik repeat order dan riwayat pelunasan kasbon secara real-time di dasbor CRM.
                </p>
                <div class="pt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                    Struk digital hemat kertas
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS PENERAPAN NYATA UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base font-extrabold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-pink-600"></i>
                    <span>Penerapan CRM, Poin &amp; Voucher pada 20 Jenis Usaha</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Strategi praktis meningkatkan kunjungan ulang pelanggan di 6 klaster bisnis UMKM.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-pink-500/10 text-pink-600 dark:text-pink-400 text-xs font-bold border border-pink-500/20 self-start sm:self-auto">
                20 Skenario Retensi Nyata
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
                    <p><strong>1. Restoran Dine-In:</strong> Poin akumulasi makan bersama keluarga; voucher diskon ulang tahun 20% otomatis via WhatsApp H-3 hari lahir.</p>
                    <p><strong>2. Coffee Shop &amp; Cafe:</strong> Kartu stempel digital tanpa kartu fisik (Beli 9 cup kopi gratis 1 cup kopi); voucher *happy hour* sore pukul 15.00 - 17.00.</p>
                    <p><strong>3. Bakery &amp; Toko Roti:</strong> Kupon *clearance sale* potongan Rp 10.000 untuk pembelian roti manis di atas jam 20.00 guna menghabiskan stok harian.</p>
                    <p><strong>4. Cloud Kitchen Online:</strong> Poin cashback untuk pemesan catering makan siang karyawan mingguan; voucher gratis biaya kirim pemesanan lewat toko online.</p>
                    <p><strong>5. Katering Prasmanan:</strong> Voucher cashback Rp 500.000 untuk pemesanan hajatan berikutnya, atau reward komisi bagi klien yang mereferensikan kerabat.</p>
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
                    <p><strong>6. Konveksi &amp; Garment:</strong> Tiering khusus pemesan grosir seragam sekolah; diskon volume otomatis 5% saat pesanan menyentuh 500 lusin.</p>
                    <p><strong>7. Percetakan &amp; Offset:</strong> Poin loyalitas untuk pemesanan cetak brosur rutin bulanan kantor; kupon diskon kalender akhir tahun untuk pelanggan lama.</p>
                    <p><strong>8. Mebel &amp; Furniture:</strong> Kupon diskon Rp 1.000.000 untuk pemesanan interior custom berikutnya bagi pembeli set meja makan jati.</p>
                    <p><strong>9. Bengkel Bubut &amp; Logam:</strong> Reward poin servis pemeliharaan mesin bubut industri; diskon termin bagi rekanan yang melunasi faktur sebelum jatuh tempo.</p>
                    <p><strong>10. Kerajinan Tangan / Craft:</strong> Diskon kuantiti khusus reseller suvenir pernikahan; voucher promo festival kriya nusantara bagi anggota loyalitas.</p>
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
                    <p><strong>11. Minimarket &amp; Kelontong:</strong> Member sembako hemat; setiap 100 poin belanja dapat ditukar langsung dengan 1 liter minyak goreng atau 1 kg gula pasir.</p>
                    <p><strong>12. Apotek &amp; Toko Obat:</strong> Poin pembelian produk kesehatan &amp; suplemen; notifikasi WhatsApp pengingat tebus obat rutin bulanan dengan kupon diskon konsultasi.</p>
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
                    <p><strong>13. Bengkel Mobil &amp; Motor:</strong> Poin servis berkala yang dapat ditukar dengan oli mesin gratis; voucher potongan spooring &amp; balancing di awal bulan.</p>
                    <p><strong>14. Barbershop &amp; Salon:</strong> Program potong rambut ke-10 gratis; kupon potongan harga paket perawatan creambath dan coloring bagi anggota Gold.</p>
                    <p><strong>15. Laundry Kiloan:</strong> Poin per kilogram cucian; voucher potongan Rp 15.000 untuk pencucian bedcover dan karpet saat musim penghujan.</p>
                    <p><strong>16. Cuci Mobil &amp; Detailing:</strong> Kartu cuci digital (cuci 5x gratis 1x); voucher diskon poles kaca anti-jamur saat cuci mobil paket lengkap.</p>
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
                    <p><strong>17. Kontraktor &amp; Renovasi:</strong> Voucher diskon instalasi kelistrikan atau kanopi bagi klien yang menandatangani kontrak renovasi rumah sebelum akhir bulan.</p>
                    <p><strong>18. Wedding Organizer:</strong> Reward cashback voucher bulan madu (*honeymoon*) bagi calon pengantin yang mereferensikan teman untuk paket all-in wedding.</p>
                    <p><strong>19. Digital IT &amp; Creative Agency:</strong> Potongan harga khusus perpanjangan kontrak retainer desain &amp; sosmed 1 tahun penuh; bonus poin konsultasi strategi branding.</p>
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
                    <p><strong>20. Distributor Grosir B2B &amp; Agrobisnis:</strong> Poin akumulasi tonase belanja pupuk dan bibit tani; kupon diskon potongan harga faktur bagi toko pengecer mitra yang melunasi piutang tepat waktu.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. ANTI-FRAUD, OTORISASI & KEAMANAN VOUCHER -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-pink-500/15 text-pink-600 dark:text-pink-400 flex items-center justify-center font-bold">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Keamanan Sistem Loyalitas: Mencegah Kecurangan Kasir &amp; Penyalahgunaan Voucher</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Mekanisme proteksi anti-fraud agar promosi toko tepat sasaran dan kas usaha tidak bocor.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs pt-1">
            <div class="p-4 rounded-xl border border-pink-500/20 bg-pink-500/[0.04] dark:bg-pink-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-pink-700 dark:text-pink-400">
                    <i data-lucide="user-x" class="w-4 h-4"></i>
                    <span>Cegah Kasir Gelembungkan Poin</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Kasir sering tergoda memasukkan nomor HP pribadi saat ada pembeli yang tidak mendaftar member. COOCA melacak frekuensi transaksi per nomor telepon dan melarang penambahan poin manual tanpa ada nomor struk transaksi kasir POS yang sah.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-pink-500/20 bg-pink-500/[0.04] dark:bg-pink-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-pink-700 dark:text-pink-400">
                    <i data-lucide="ticket-x" class="w-4 h-4"></i>
                    <span>Batas Limit Voucher Promo</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Setiap kupon promo memiliki proteksi kuota pemakaian global (<code>usage_limit</code>), batas pemakaian per akun pelanggan, dan batas minimal belanja. Kasir tidak bisa menggunakan kupon diskon berulang-ulang untuk transaksi fiktif.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-pink-500/20 bg-pink-500/[0.04] dark:bg-pink-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-pink-700 dark:text-pink-400">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    <span>Anti-Lapping Pelunasan Kasbon</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Saat pelanggan mencicil piutang di menu CRM, kasir wajib memilih shift kas laci POS atau transfer bank yang terverifikasi. Sistem otomatis mengirimkan tautan tanda terima resmi ke WhatsApp pelanggan sehingga uang cicilan tidak dapat ditilap kasir (*Lapping Fraud*).
                </p>
            </div>
        </div>
    </div>

    <!-- 6. TANYA JAWAB BISNIS OWNER (FAQ) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs"
        x-data="{ expandedFaq: null }">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="help-circle" class="w-4 h-4 text-pink-600"></i>
                <span>Tanya Jawab Seputar Program CRM, Poin &amp; Voucher (FAQ)</span>
            </h3>
            <span class="text-xs text-black/40 dark:text-white/40">5 Solusi Cepat</span>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 1 ? null : 1)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>1. Berapa rasio perolehan poin yang ideal bagi usaha saya agar tidak merugi?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 1" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Standar umum ritel dan kuliner berkisar antara 1% hingga 2% dari total nilai transaksi. Contohnya: setiap belanja Rp 10.000 mendapatkan 1 poin yang bernilai tukar Rp 100 saat dipotongkan di kasir. Rasio ini cukup menarik bagi pelanggan untuk kembali berbelanja tanpa membebani margin keuntungan bersih bisnis Anda.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 2 ? null : 2)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>2. Apakah pengiriman bukti struk belanja ke WhatsApp pelanggan dikenakan biaya pulsa?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 2" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>COOCA menyediakan tautan langsung WhatsApp (*Deep Link API*) yang dapat dibuka kasir dari browser atau tablet tanpa biaya per pesan tambahan. Kasir cukup mengklik tombol *"Kirim Struk WhatsApp"*, pesan dengan format rapi dan tautan nota digital langsung terbuka di aplikasi WhatsApp kasir untuk dikirimkan ke nomor pembeli.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 3 ? null : 3)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>3. Apa perbedaan voucher diskon tipe "Persentase" dengan "Nominal Tetap"?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 3" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Diskon persentase memotong nilai belanja secara proporsional (misal: 10% dari total belanja), sangat cocok untuk mendorong pelanggan membeli produk dalam jumlah banyak. Sedangkan nominal tetap memotong nilai rupiah pasti (misal: Potongan Rp 25.000 dengan minimal belanja Rp 100.000), sangat efektif untuk menaikkan nilai rata-rata keranjang belanja (*Average Basket Size*).</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 4 ? null : 4)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>4. Bagaimana cara mencatat pelunasan cicilan kasbon pelanggan di modul CRM?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 4" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Buka menu <strong>CRM &rarr; Member &amp; Poin</strong>, cari nama pelanggan yang memiliki label saldo piutang merah, lalu klik tombol *"Catat Pelunasan"*. Masukkan nominal yang dibayarkan dan metode pembayaran (Tunai ke laci kasir aktif atau Transfer Bank). Saldo piutang akan otomatis terpotong dan tautan struk pelunasan WhatsApp dapat langsung dikirimkan ke pelanggan.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 5 ? null : 5)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>5. Apakah staf kasir saya bisa membuat voucher diskon sendiri secara bebas?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 5" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Tidak bisa. Pembuatan dan pengaktifan kupon voucher diskon dikunci dengan izin <code>crm.manage</code>. Kasir hanya memiliki izin <code>crm.view</code> dan hanya berhak mengaplikasikan kode voucher resmi yang sudah dibuat oleh Owner saat memasukkan transaksi di mesin kasir.</p>
                </div>
            </div>
        </div>
    </div>

</section>
