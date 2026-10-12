<!-- ===================================================== -->
<!-- SECTION: ASISTEN PINTAR AI & INTELIJEN BISNIS COOCA   -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-ai" class="space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'ai') && matchesSearch('ai bot asisten pintar kecerdasan buatan analisa prediksi restock fraud prompt openai gemini claude llm ollama 20 industri usaha restoran cafe bakery bengkel toko apotek laundry konveksi mebel bubut catering katering kontraktor digital')">

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL AI -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-cyan-500 via-[#007AFF] to-[#5856D6] text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(0,122,255,0.35)] shrink-0">
                <i data-lucide="bot" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">Asisten Pintar AI &amp; Intelijen Bisnis</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">20 Sektor Industri</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Panduan praktis pemilik usaha memaksimalkan asisten AI untuk kontrol omzet, kalkulasi HPP otomatis, cegah kebocoran kasir, dan eksekusi tindakan otonom.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur AI Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::hasPermission('ai.access'))
                <a href="{{ route('cooca-ai.pos') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Tanya AI POS</span>
                </a>
                <a href="{{ route('cooca-ai.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-cyan-500"></i>
                    <span>Lobi Kantor AI</span>
                </a>
                <a href="{{ route('cooca-ai.actions') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="check-square" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span>Action Center</span>
                </a>
            @endif
            @if(\App\Support\Context::isOwner())
                <a href="{{ route('cooca-ai.providers') }}"
                    class="h-9 px-3 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95"
                    title="Atur Kunci API Model AI (Gemini, GPT-4o, Claude, Ollama)">
                    <i data-lucide="settings-2" class="w-3.5 h-3.5 text-purple-500"></i>
                    <span>Pengaturan Model</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-cyan-500 to-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="line-chart" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Mengapa Asisten AI Cooca Menjadi Otak Pengendali Bisnis Anda?</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    AI Cooca <strong>bukan sekadar chatbot obrolan umum</strong>. Sistem ini bekerja layaknya <strong>Direktur Operasional &amp; Analis Keuangan Pribadi</strong> yang terhubung langsung ke basis data kasir POS, mutasi stok gudang, buku kas, invoice pelanggan, dan shift kerja staf Anda. Anda tidak perlu lagi berkutat dengan rumus Excel yang rumit atau menunggu laporan mingguan staf yang sering terlambat. Cukup ketik pertanyaan dalam bahasa sehari-hari, dan sistem menyajikan jawaban angka akurat serta usulan tindakan siap eksekusi.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Manfaat Finansial & Operasional bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    <span>Hemat 2–3 Jam Tiap Malam</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Tinggalkan rekap kasir manual berjam-jam. Rekap total omzet, laba kotor, dan rincian metode bayar (QRIS, Tunai, EDC) otomatis tersaji dalam hitungan detik.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-[#007AFF]/[0.06] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 space-y-1.5">
                <div class="flex items-center gap-2 text-[#007AFF] dark:text-[#0A84FF] font-bold text-xs sm:text-sm">
                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    <span>Cegah Kebocoran Kasir</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Deteksi anomali (*fraud alert*) secara proaktif: kasir yang sering membatalkan transaksi setelah nota tercetak, diskon misterius, atau selisih modal laci kasir.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-purple-500/[0.06] dark:bg-purple-500/[0.1] border border-purple-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-purple-700 dark:text-purple-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="boxes" class="w-4 h-4"></i>
                    <span>Restok Sebelum Kehabisan</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    AI mempelajari pola kecepatan penjualan dan menghitung estimasi hari habis (*stock runway*), lalu otomatis menyusun draf Pesanan Beli (PO) ke supplier.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="calculator" class="w-4 h-4"></i>
                    <span>Presisi HPP &amp; Margin Bersih</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    AI mengurai formula resep BOM (gramasi bumbu, kain yard, bahan baku) dan menghitung biaya modal riil per produk agar Anda tidak menjual di bawah HPP.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. PANDUAN PENGOPERASIAN HARIAN (4 PILAR EKOSISTEM AI COOCA) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white tracking-tight">
                    4 Cara Utama Memanfaatkan Asisten AI Sehari-hari
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50">Panduan alur operasional dari tanya jawab cepat di meja kasir hingga persetujuan aksi strategis di ruang kerja digital</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] self-start sm:self-auto">
                Alur Operasional Praktis
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Pilar 1: Tanya AI POS -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xs font-bold">1</span>
                            <span>Tanya Jawab Bahasa Alami (Tanya AI POS)</span>
                        </div>
                        <a href="{{ route('cooca-ai.pos') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>Buka Layar</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Fitur yang dirancang untuk kecepatan kasir dan manajer toko. Ketik pertanyaan seperti mengobrol biasa atau pilih tombol cepat yang disediakan:
                    </p>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-black/5 dark:border-white/10 text-[10.5px] text-black/75 dark:text-white/75 italic">&ldquo;Berapa omzet hari ini dan jam teramai kasir?&rdquo;</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-black/5 dark:border-white/10 text-[10.5px] text-black/75 dark:text-white/75 italic">&ldquo;Bahan masakan apa yang kritis mau habis?&rdquo;</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-black/5 dark:border-white/10 text-[10.5px] text-black/75 dark:text-white/75 italic">&ldquo;Apakah kasir mencatat diskon mencurigakan?&rdquo;</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-800 border border-black/5 dark:border-white/10 text-[10.5px] text-black/75 dark:text-white/75 italic">&ldquo;Produk apa yang margin labanya paling tinggi?&rdquo;</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Respons real-time &lt; 2 detik</span>
                    <a href="{{ route('cooca-ai.pos') }}" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Mulai Tanya POS &rarr;</a>
                </div>
            </div>

            <!-- Pilar 2: Tiga Kantor Intelijen AI Spesialis -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-xs font-bold">2</span>
                            <span>Tiga Kantor Intelijen Digital Terpadu</span>
                        </div>
                        <a href="{{ route('cooca-ai.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>Lobi Markas</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Pusat kerja terbagi dalam 3 kantor spesialis yang beroperasi secara kolaboratif:
                    </p>
                    <div class="space-y-1.5 text-[11.5px]">
                        <div class="p-2 rounded-lg bg-amber-500/5 border border-amber-500/15">
                            <strong class="text-amber-700 dark:text-amber-400 font-semibold block">Executive Office (CEO &amp; CFO AI):</strong>
                            <span class="text-black/60 dark:text-white/60">Pusat penasihat strategi profit, audit arus kas laci vs bank, dan target pendapatan bulanan.</span>
                        </div>
                        <div class="p-2 rounded-lg bg-blue-500/5 border border-blue-500/15">
                            <strong class="text-blue-700 dark:text-blue-400 font-semibold block">Operations Office (Supply Chain &amp; POS Floor):</strong>
                            <span class="text-black/60 dark:text-white/60">Pengawas stok gudang, kecepatan dapur, rasio sisa bahan, dan jadwal pergantian shift.</span>
                        </div>
                        <div class="p-2 rounded-lg bg-purple-500/5 border border-purple-500/15">
                            <strong class="text-purple-700 dark:text-purple-400 font-semibold block">Growth Office (Marketing &amp; CRM):</strong>
                            <span class="text-black/60 dark:text-white/60">Perancang program loyalitas member, promo happy-hour, dan aktivasi pelanggan yang lama tidak datang.</span>
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>3 Kantor Spesialis Aktif</span>
                    <a href="{{ route('cooca-ai.office.executive') }}" class="font-semibold text-[#007AFF] hover:underline">Masuk Executive Office &rarr;</a>
                </div>
            </div>

            <!-- Pilar 3: Action Center & Proposal Otonom -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center text-xs font-bold">3</span>
                            <span>Action Center &amp; Proposal Otonom</span>
                        </div>
                        <a href="{{ route('cooca-ai.actions') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>Tinjau Aksi</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Sistem <strong>Human-in-the-Loop</strong> yang menjamin kontrol 100% di tangan Anda:
                    </p>
                    <ul class="space-y-1.5 text-[11.5px] text-black/70 dark:text-white/70">
                        <li class="flex items-start gap-1.5">
                            <i data-lucide="file-plus" class="w-3.5 h-3.5 text-blue-500 mt-0.5 shrink-0"></i>
                            <span><strong>AI Menyusun Usulan:</strong> Saat stok bahan kritis, AI membuat draf PO ke supplier lengkap dengan kuantiti dan nama vendor.</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <i data-lucide="bell-ring" class="w-3.5 h-3.5 text-amber-500 mt-0.5 shrink-0"></i>
                            <span><strong>Notifikasi Masuk ke Owner:</strong> Anda menerima ringkasan proposal aksi tanpa perlu membuat pesanan dari nol.</span>
                        </li>
                        <li class="flex items-start gap-1.5">
                            <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-500 mt-0.5 shrink-0"></i>
                            <span><strong>Eksekusi 1-Klik:</strong> Klik <em>&ldquo;Setujui&rdquo;</em> untuk memproses, <em>&ldquo;Revisi&rdquo;</em> untuk koreksi nominal, atau <em>&ldquo;Tolak&rdquo;</em> bila tidak diperlukan.</span>
                        </li>
                    </ul>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Aman tanpa eksekusi liar</span>
                    <a href="{{ route('cooca-ai.actions') }}" class="font-semibold text-amber-600 dark:text-amber-400 hover:underline">Kelola Proposal &rarr;</a>
                </div>
            </div>

            <!-- Pilar 4: Konfigurasi Model AI & Kuota Token -->
            <div class="p-4 sm:p-5 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white text-sm">
                            <span class="w-6 h-6 rounded-lg bg-purple-500/10 text-purple-600 flex items-center justify-center text-xs font-bold">4</span>
                            <span>Penyedia Model AI (LLM) &amp; Biaya Token</span>
                        </div>
                        @if(\App\Support\Context::isOwner())
                            <a href="{{ route('cooca-ai.providers') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <span>Atur Model</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                        @endif
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                        Fleksibilitas penuh memilih mesin kecerdasan buatan sesuai kebutuhan dan anggaran toko:
                    </p>
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="p-2 rounded-lg bg-white dark:bg-zinc-800 border border-black/5 dark:border-white/10 space-y-0.5">
                            <span class="font-bold text-black dark:text-white block">Google Gemini</span>
                            <span class="text-black/50 dark:text-white/50 text-[10px]">Paling hemat, ada kuota gratis harian, kilat di kasir.</span>
                        </div>
                        <div class="p-2 rounded-lg bg-white dark:bg-zinc-800 border border-black/5 dark:border-white/10 space-y-0.5">
                            <span class="font-bold text-black dark:text-white block">OpenAI (GPT-4o)</span>
                            <span class="text-black/50 dark:text-white/50 text-[10px]">Akurasi tinggi untuk audit keuangan &amp; penawaran.</span>
                        </div>
                        <div class="p-2 rounded-lg bg-white dark:bg-zinc-800 border border-black/5 dark:border-white/10 space-y-0.5">
                            <span class="font-bold text-black dark:text-white block">Anthropic Claude</span>
                            <span class="text-black/50 dark:text-white/50 text-[10px]">Analisis logis mendalam untuk strategi bisnis.</span>
                        </div>
                        <div class="p-2 rounded-lg bg-white dark:bg-zinc-800 border border-black/5 dark:border-white/10 space-y-0.5">
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 block">Local Ollama</span>
                            <span class="text-black/50 dark:text-white/50 text-[10px]">100% Offline, gratis selamanya di PC toko.</span>
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                    <span>Enkripsi Kunci API AES-256</span>
                    <a href="{{ route('billing.limits') }}" class="font-semibold text-purple-600 dark:text-purple-400 hover:underline">Cek Kuota Paket &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MATRIKS IMPLEMENTASI LENGKAP UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-6 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight">
                    Matriks Penerapan Praktis untuk 20 Sektor Industri UMKM
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">
                    Contoh konkret masalah bisnis, contoh prompt yang dapat langsung ditanyakan ke AI, serta tautan langsung ke modul terkait untuk masing-masing bidang usaha Anda
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] shrink-0 self-start sm:self-auto">
                6 Klaster &bull; 20 Usaha Lengkap
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
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Manajemen meja, kecepatan dapur, susut bahan, dan kalkulasi resep gramasi bumbu</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-400">5 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">
                    
                    <!-- 1. Restoran Dine-In -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">1. Restoran Dine-In</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Meja &amp; Tiket Dapur</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Antrean meja menumpuk di akhir pekan, daging dan bumbu dapur bocor tanpa takaran, kasir lupa mencatat pesanan susulan.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Berapa perkiraan tamu meja malam minggu besok dan berapa kilo daging sirloin yang harus disiapkan di dapur?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Menghitung rata-rata keterisian meja 4 minggu terakhir, mengkalkulasi kebutuhan gramasi daging, dan menyiapkan draf PO supplier.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('pos.tables.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="layout-grid" class="w-3 h-3"></i>
                                <span>Denah Meja</span>
                            </a>
                            <a href="{{ route('pos.terminal') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="calculator" class="w-3 h-3"></i>
                                <span>Buka Kasir</span>
                            </a>
                        </div>
                    </div>

                    <!-- 2. Coffee Shop & Cafe -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">2. Coffee Shop &amp; Cafe</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Beans &amp; Modifier Rasa</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Susut biji kopi espresso &amp; susu segar, racikan add-on (less sugar, oat milk) tidak tercatat di stok, barista kewalahan saat jam sibuk.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Hitung sisa gramasi beans House Blend dan kapan susu oat habis berdasarkan penjualan 3 hari terakhir.&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Mengurai jumlah cup terjual ke gramasi biji kopi, mengingatkan restok susu oat, dan menyarankan pengaturan shift barista.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('pos.modifiers.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="sliders" class="w-3 h-3"></i>
                                <span>Varian Add-On</span>
                            </a>
                            <a href="{{ route('inventory.stocks') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="boxes" class="w-3 h-3"></i>
                                <span>Stok Beans</span>
                            </a>
                        </div>
                    </div>

                    <!-- 3. Bakery & Toko Roti -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">3. Bakery &amp; Toko Roti</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Batch Adonan &amp; Expired</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Roti sisa basi dan dibuang sore hari, adonan pagi terlambat dipanggang, fluktuasi harga mentega dan ragi impor.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Berapa loyang roti tawar dan manis yang ideal dipanggang besok pagi agar sisa display di bawah 5%?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Memprediksi kuota loyang adonan pagi berdasarkan cuaca dan hari, serta mengusulkan jam mulai promo diskon roti sore hari.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('products.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="cake" class="w-3 h-3"></i>
                                <span>Resep Roti</span>
                            </a>
                            <a href="{{ route('pos.reports.index') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="bar-chart-2" class="w-3 h-3"></i>
                                <span>Sisa Display</span>
                            </a>
                        </div>
                    </div>

                    <!-- 4. Cloud Kitchen Online -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">4. Cloud Kitchen Online</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Multi-Brand &amp; Ojol</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Pesanan ojek online menumpuk bersamaan, waktu masak (*prep time*) melebihi 15 menit, ruang cold-storage terbatas.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Jam berapa lonjakan pesanan antar hari ini dan paket bundling menu apa yang margin profitnya paling tebal?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Mengelompokkan data riwayat order per jam, menghitung biaya kemasan packaging, dan membuat paket kombo bernilai tinggi.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('pos.kitchen.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="chef-hat" class="w-3 h-3"></i>
                                <span>Tiket Dapur KDS</span>
                            </a>
                            <a href="{{ route('inventory.stocks') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="snowflake" class="w-3 h-3"></i>
                                <span>Cold Storage</span>
                            </a>
                        </div>
                    </div>

                    <!-- 5. Katering Prasmanan & Nasi Kotak -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">5. Katering Prasmanan &amp; Nasi Kotak</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">DP Acara &amp; Belanja Pasar</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Pembayaran DP dan termin pelunasan H-1 sering lolos dari pengawasan, belanja pasar ratusan porsi salah takar.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Rincikan daftar belanja bumbu pasar untuk pesanan 600 porsi prasmanan tanggal 18 dan periksa status DP pelunasannya.&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Mengalikan resep gramasi per porsi ke 600 porsi secara otomatis dan memunculkan pengingat invoice DP yang belum dilunasi.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('invoices.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="receipt" class="w-3 h-3"></i>
                                <span>Invoice DP &amp; Pelunasan</span>
                            </a>
                            <a href="{{ route('materials.index') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="shopping-bag" class="w-3 h-3"></i>
                                <span>Bahan Mentah</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- KLASTER 2: MANUFAKTUR & HPP (5 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-indigo-500/[0.03] dark:bg-indigo-500/[0.05] border border-indigo-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-indigo-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="factory" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 2: Manufaktur, Workshop &amp; HPP Multi-Tier (5 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Bill of Materials (BOM), upah borongan, susut bahan, dan surat penawaran harga</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-700 dark:text-indigo-400">5 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">

                    <!-- 6. Konveksi & Garment -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">6. Konveksi &amp; Garment</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Roll Kain &amp; Borongan</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Sisa kain perca terbuang tanpa terhitung, upah potong dan jahit per lusin salah hitung, deadline seragam kantor sering molor.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Berapa yard kain katun combed 30s dan berapa total ongkos jahit borongan untuk pesanan 400 kaos polo seragam ini?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Mengkalkulasi pola potong yard kain, estimasi sisa perca, dan menghitung total upah borongan per penjahit secara otomatis.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('sales.orders.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="clipboard-list" class="w-3 h-3"></i>
                                <span>Order Seragam B2B</span>
                            </a>
                            <a href="{{ route('products.index') }}" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <i data-lucide="scissors" class="w-3 h-3"></i>
                                <span>Formula BOM Pakaian</span>
                            </a>
                        </div>
                    </div>

                    <!-- 7. Percetakan & Offset Digital -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">7. Percetakan &amp; Offset Digital</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Tarif m² &amp; Tinta Meteran</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Perhitungan harga cetak meteran (m²) sering tekor karena lupa menghitung susut tinta dan biaya lembar finishing laminasi.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Hitung estimasi HPP cetak spanduk flexi 5x2 meter sebanyak 20 lembar termasuk tinta outdoor dan mata ayam.&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Mengkalkulasi total luas m², kebutuhan tinta per mililiter, biaya aksesoris, dan menyusun surat penawaran harga resmi.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('sales.quotations.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="file-text" class="w-3 h-3"></i>
                                <span>Surat Penawaran m²</span>
                            </a>
                            <a href="{{ route('inventory.stocks') }}" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <i data-lucide="layers" class="w-3 h-3"></i>
                                <span>Stok Roll Media</span>
                            </a>
                        </div>
                    </div>

                    <!-- 8. Mebel & Furniture Kayu -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">8. Mebel &amp; Furniture Kayu</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Multiplek HPL &amp; Termin</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Biaya material kayu log &amp; HPL membengkak di tengah pengerjaan, penagihan DP dan termin progress ke pemilik rumah molor.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Buatkan draf penawaran kitchen set HPL 4 meter dan hitung margin laba jika upah tukang mebel Rp 2.000.000.&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Merinci lembaran multiplek, engsel slow-motion, pelapis HPL, dan membuat struktur termin penagihan (DP 40% - 30% - 30%).
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('sales.quotations.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="file-signature" class="w-3 h-3"></i>
                                <span>Penawaran Interior</span>
                            </a>
                            <a href="{{ route('finance.expenses.index') }}" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <i data-lucide="banknote" class="w-3 h-3"></i>
                                <span>Biaya Upah Tukang</span>
                            </a>
                        </div>
                    </div>

                    <!-- 9. Bengkel Bubut & Logam Presisi -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">9. Bengkel Bubut &amp; Logam Presisi</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Tarif Jam Mesin &amp; As Besi</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Mesin bubut dan milling menyedot listrik besar, bahan baku as besi mahal, waktu pengerjaan part custom sering meleset.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Berapa total jam kerja mesin bubut minggu ini dan apakah ada deviasi biaya listrik serta bahan besi as?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Merekap jam kerja mesin bubut (*machine hours*), menghitung depresiasi mesin per pesanan, dan mendeteksi selisih bahan logam.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('inventory.stocks') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="shield" class="w-3 h-3"></i>
                                <span>Stok Besi &amp; Logam</span>
                            </a>
                            <a href="{{ route('purchase-orders.index') }}" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <i data-lucide="truck" class="w-3 h-3"></i>
                                <span>Order Beli Supplier</span>
                            </a>
                        </div>
                    </div>

                    <!-- 10. Kerajinan Tangan & Craft -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">10. Kerajinan Tangan &amp; Craft</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Souvenir &amp; Upah Perajin</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Pesanan ribuan souvenir pernikahan dikejar tenggat waktu ketat, stok pernak-pernik pita/lem sering terlambat dibeli.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Kapan deadline pengerjaan 1.500 souvenir anyaman dan apakah stok pita satin serta box mika masih aman?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Menghitung kapasitas perajin per hari, memetakan risiko keterlambatan, dan membuat daftar belanja aksesoris kemasan.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('sales.orders.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="gift" class="w-3 h-3"></i>
                                <span>Order Souvenir</span>
                            </a>
                            <a href="{{ route('inventory.stocks') }}" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                                <i data-lucide="package" class="w-3 h-3"></i>
                                <span>Stok Kemasan Box</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- KLASTER 3: RITEL & APOTEK (2 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-emerald-500/[0.03] dark:bg-emerald-500/[0.05] border border-emerald-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-emerald-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 3: Ritel FMCG &amp; Farmasi / Apotek (2 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Ribuan SKU barcode, pencegahan barang kadaluarsa (ED), dan deteksi barang macet</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">2 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 text-xs">

                    <!-- 11. Minimarket & Toko Kelontong -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">11. Minimarket &amp; Toko Kelontong</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Multi-Satuan Dus/Renteng/Pcs</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Ribuan barang menumpuk di rak etalase, selisih stok fisik vs catatan kasir (*shrinkage* / dicuri), barang macet tidak laku memakan tempat.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Barang apa di rak etalase yang tidak laku (slow-moving) dalam 30 hari terakhir agar bisa dibuat promo bundling diskon?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Menyaring produk dengan perputaran terendah, menghitung nilai modal tertahan, dan membuat draf kupon diskon kasir untuk cuci gudang.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('inventory.opnames.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="check-square" class="w-3 h-3"></i>
                                <span>Stock Opname Fisik</span>
                            </a>
                            <a href="{{ route('crm.members.index') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="tag" class="w-3 h-3"></i>
                                <span>Buat Diskon Bundling</span>
                            </a>
                        </div>
                    </div>

                    <!-- 12. Apotek & Toko Obat -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">12. Apotek &amp; Toko Obat</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">No. Batch &amp; Tanggal Expired</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Obat kadaluarsa tidak terdeteksi sehingga hangus modal puluhan juta, aturan ketat nomor batch BPOM, dan piutang tagihan PBF.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Tampilkan seluruh obat sirup dan antibiotik yang tanggal kadaluarsanya kurang dari 90 hari dan buat draf retur ke PBF.&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Memindai seluruh nomor batch stok rak, mengelompokkan barang mendekati expired, dan menyusun faktur draf retur barang ke distributor PBF.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('inventory.stocks') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                <span>Pantau Batch &amp; Expired</span>
                            </a>
                            <a href="{{ route('purchasing.bills.index') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="receipt" class="w-3 h-3"></i>
                                <span>Faktur Tagihan PBF</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- KLASTER 4: JASA OPERASIONAL HARIAN (4 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-sky-500/[0.03] dark:bg-sky-500/[0.05] border border-sky-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-sky-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="wrench" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 4: Jasa Operasional Harian &amp; Otomotif (4 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Pelacakan nopol, komisi mekanik/kapster, timbangan kiloan, dan notifikasi WhatsApp</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-500/10 text-sky-700 dark:text-sky-400">4 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">

                    <!-- 13. Bengkel Mobil & Motor -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">13. Bengkel Otomotif</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">PKB &amp; Nopol Kendaraan</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah:</strong> Pelanggan lupa riwayat servis lama, pemisahan ongkos jasa montir vs suku cadang part rawan selisih, oli sering hilang di gudang.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-sky-600 dark:text-sky-400 block uppercase">Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Cari riwayat servis nopol B 1234 XYZ: servis apa saja yang pernah dilakukan dan kapan jadwal ganti oli berikutnya?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Mengurai seluruh riwayat PKB, sparepart yang diganti, komisi montir, dan menyiapkan draf WA pengingat servis.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('pos.terminal') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="file-plus" class="w-3 h-3"></i>
                                <span>Input PKB Kasir</span>
                            </a>
                            <a href="{{ route('whatsapp.index') }}" class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                                <i data-lucide="message-circle" class="w-3 h-3"></i>
                                <span>Pengingat Servis WA</span>
                            </a>
                        </div>
                    </div>

                    <!-- 14. Barbershop & Salon Kecantikan -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">14. Barbershop &amp; Salon</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Komisi Kapster / Stylist</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah:</strong> Antrean potong rambut membludak di akhir pekan, perhitungan bagi hasil komisi jasa kapster rawan salah hitung, pomade/shampo boros.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-sky-600 dark:text-sky-400 block uppercase">Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Hitung total komisi potong rambut dan creambath untuk masing-masing kapster shift hari ini.&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Mengalikan persentase komisi tiap stylist secara otomatis, memisahkan omzet toko, dan menyusun slip rekap komisi harian.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('pos.shifts.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="users" class="w-3 h-3"></i>
                                <span>Shift &amp; Kursi</span>
                            </a>
                            <a href="{{ route('hrm.payrolls.index') }}" class="text-[11px] font-semibold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1">
                                <i data-lucide="wallet" class="w-3 h-3"></i>
                                <span>Slip Komisi Staf</span>
                            </a>
                        </div>
                    </div>

                    <!-- 15. Laundry Kiloan & Satuan -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">15. Laundry Kiloan &amp; Satuan</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Timbangan Kg &amp; Loker Baju</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah:</strong> Baju pelanggan tertukar atau belum diambil berhari-hari sehingga rak loker penuh sesak, nota cucian belum lunas saat baju diambil.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-sky-600 dark:text-sky-400 block uppercase">Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Daftar nota cucian yang sudah selesai setrika lebih dari 3 hari di rak loker tapi belum diambil pelanggan.&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Menyusun daftar pakaian siap ambil di rak, nomor kontak pelanggan, dan menyiapkan draf pesan WhatsApp notifikasi otomatis.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('whatsapp.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="send" class="w-3 h-3"></i>
                                <span>Broadcast WA Baju Siap</span>
                            </a>
                            <a href="{{ route('customers.index') }}" class="text-[11px] font-semibold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1">
                                <i data-lucide="contact" class="w-3 h-3"></i>
                                <span>Data Pelanggan</span>
                            </a>
                        </div>
                    </div>

                    <!-- 16. Cuci Mobil & Detailing -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">16. Cuci Mobil &amp; Detailing</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Antrean Hidrolik &amp; Poles</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah:</strong> Antrean hidrolik membludak di cuaca cerah namun sepi saat hujan, perhitungan komisi tim cuci harian rawan perselisihan.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-sky-600 dark:text-sky-400 block uppercase">Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Berapa rata-rata mobil dicuci per hari minggu ini dan paket poles nano ceramic apa yang paling diminati?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Menganalisis utilisasi hidrolik cuci, menghitung komisi tim cuci, dan menyarankan promosi paket poles kaca di hari kerja yang sepi.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('pos.terminal') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="car" class="w-3 h-3"></i>
                                <span>Kasir Cuci Mobil</span>
                            </a>
                            <a href="{{ route('finance.cash-bank.index') }}" class="text-[11px] font-semibold text-sky-600 dark:text-sky-400 hover:underline flex items-center gap-1">
                                <i data-lucide="coins" class="w-3 h-3"></i>
                                <span>Kas Operasional</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- KLASTER 5: JASA PROYEK & KREATIF (3 USAHA) -->
            <div class="rounded-2xl p-4 sm:p-5 bg-purple-500/[0.03] dark:bg-purple-500/[0.05] border border-purple-500/20 space-y-4">
                <div class="flex items-center justify-between gap-3 pb-2 border-b border-purple-500/20">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-xs">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 5: Jasa Proyek, Event &amp; Agensi Kreatif (3 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Rencana Anggaran Biaya (RAB), penagihan invoice termin progress, dan koordinasi vendor</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/10 text-purple-700 dark:text-purple-400">3 Sektor</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 text-xs">

                    <!-- 17. Kontraktor & Renovasi Bangunan -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">17. Kontraktor &amp; Renovasi</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">RAB vs Belanja Aktual</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Biaya belanja material semen/besi melebihi budget RAP awal tanpa disadari, penagihan termin pemilik rumah terlambat.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Bandingkan belanja aktual semen dan besi Proyek Renovasi Pak Budi terhadap RAB awal, apakah ada deviasi biaya?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Menghitung deviasi persentase biaya belanja terhadap budget dan menyusun draf invoice penagihan termin 50% lengkap dengan no rekening.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('invoices.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="receipt" class="w-3 h-3"></i>
                                <span>Tagihan Termin Fisik</span>
                            </a>
                            <a href="{{ route('finance.expenses.index') }}" class="text-[11px] font-semibold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                                <i data-lucide="hard-hat" class="w-3 h-3"></i>
                                <span>Beban Belanja Material</span>
                            </a>
                        </div>
                    </div>

                    <!-- 18. Event Organizer & Wedding Organizer -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">18. Event / Wedding Organizer</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Koordinasi Puluhan Vendor</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Puluhan vendor (tenda, katering, sound system, MC) harus dikoordinasi dengan tenggat bayar berbeda, budget pengantin ketat.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Rekap status pembayaran DP dari calon pengantin dan jadwal pelunasan ke vendor katering serta dekorasi untuk Sabtu besok.&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Memetakan jadwal jatuh tempo pelunasan vendor, sisa piutang klien, dan menghitung estimasi profit bersih event secara presisi.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('purchasing.bills.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="file-check" class="w-3 h-3"></i>
                                <span>Tagihan Vendor Mitra</span>
                            </a>
                            <a href="{{ route('invoices.index') }}" class="text-[11px] font-semibold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                                <i data-lucide="receipt" class="w-3 h-3"></i>
                                <span>Invoice Klien Event</span>
                            </a>
                        </div>
                    </div>

                    <!-- 19. Digital IT & Creative Agency -->
                    <div class="p-3.5 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-2.5 flex flex-col justify-between shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-xs">19. Digital IT &amp; Creative Agency</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Scope Creep &amp; Piutang Invoice</span>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Owner:</strong> Klien meminta revisi di luar kontrak (*scope creep*), jam kerja desainer/developer tidak terlacak, piutang invoice tertahan.
                            </p>
                            <div class="p-2 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-purple-600 dark:text-purple-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11px] italic text-black/75 dark:text-white/75">&ldquo;Klien mana yang invoice terminnya jatuh tempo lebih dari 7 hari dan buatkan draf pesan penagihan profesional siap kirim?&rdquo;</p>
                            </div>
                            <p class="text-[11px] text-black/60 dark:text-white/60">
                                <strong>Hasil AI:</strong> Menyaring daftar piutang jatuh tempo, menghitung nilai tertahan, dan merangkum draf surat penagihan termin resmi.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between gap-1">
                            <a href="{{ route('invoices.index') }}" class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                <i data-lucide="receipt" class="w-3 h-3"></i>
                                <span>Daftar Invoice Klien</span>
                            </a>
                            <a href="{{ route('finance.receivables') }}" class="text-[11px] font-semibold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                                <i data-lucide="scale" class="w-3 h-3"></i>
                                <span>Buku Piutang Usaha</span>
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
                            <h4 class="font-bold text-sm text-black dark:text-white">Klaster 6: Distribusi Grosir B2B &amp; Agrobisnis (1 Usaha)</h4>
                            <p class="text-[11px] text-black/50 dark:text-white/50">Fokus: Surat jalan kurir, plafon limit kredit toko mitra, dan panen musiman</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/10 text-teal-700 dark:text-teal-400">1 Sektor</span>
                </div>

                <div class="grid grid-cols-1 text-xs">

                    <!-- 20. Distributor Grosir B2B & Agrobisnis -->
                    <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-zinc-900 border border-black/5 dark:border-white/5 space-y-3 shadow-2xs">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-black dark:text-white text-sm">20. Distributor Grosir B2B &amp; Agrobisnis (Hasil Tani &amp; Ternak)</span>
                                <span class="text-[10px] font-mono text-black/40 dark:text-white/40">Surat Jalan (DO) &amp; Credit Limit</span>
                            </div>
                            <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                                <strong>Masalah Utama Owner:</strong> Ratusan toko kelontong mitra memiliki piutang jatuh tempo yang menumpuk, barang retur karena rusak di perjalanan, dan harga komoditas panen musiman fluktuatif.
                            </p>
                            <div class="p-2.5 rounded-lg bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5">
                                <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400 block uppercase">Contoh Prompt AI:</span>
                                <p class="text-[11.5px] italic text-black/75 dark:text-white/75">&ldquo;Warung mitra mana saja yang plafon limit kreditnya sudah habis dan faktur mana yang jatuh tempo minggu ini sebelum sales mengirim barang baru?&rdquo;</p>
                            </div>
                            <p class="text-[11.5px] text-black/60 dark:text-white/60">
                                <strong>Hasil Analisis AI:</strong> Memeriksa batas kredit setiap toko pelanggan, menandai pesanan sales order yang berisiko gagal bayar, dan mengusulkan surat jalan pengiriman hanya untuk toko yang tertib melunasi piutang.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('sales.orders.index') }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                                    <span>Sales Order (SO) &amp; Surat Jalan</span>
                                </a>
                                <a href="{{ route('customers.index') }}" class="text-[11.5px] font-semibold text-teal-600 dark:text-teal-400 hover:underline flex items-center gap-1">
                                    <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                                    <span>Plafon Limit Kredit Toko</span>
                                </a>
                            </div>
                            <a href="{{ route('cooca-ai.actions') }}" class="text-[11.5px] font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                                <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
                                <span>Tinjau Rekomendasi Pengiriman AI &rarr;</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- 5. KEAMANAN, OTORISASI AKSES (RBAC) & PRIVASI DATA BISNIS -->
    <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3 pb-2 border-b border-black/5 dark:border-white/10">
            <span class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Privasi Data Usaha &amp; Kontrol Hak Akses Multi-Level (RBAC)</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Jaminan kerahasiaan formula resep rahasia dapur, margin keuntungan, dan catatan kas bisnis Anda</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
            <div class="p-3.5 rounded-xl bg-emerald-500/[0.04] dark:bg-emerald-500/[0.08] border border-emerald-500/15 space-y-1">
                <div class="flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400 font-bold">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Zero Training Guarantee</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Data transaksi, formula resep masakan, dan angka pembukuan Anda <strong>TIDAK PERNAH digunakan untuk melatih model AI publik</strong>. Data Anda terenkripsi dan terisolasi per tenant.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-[#007AFF]/[0.04] dark:bg-[#007AFF]/[0.08] border border-[#007AFF]/15 space-y-1">
                <div class="flex items-center gap-1.5 text-[#007AFF] dark:text-[#0A84FF] font-bold">
                    <i data-lucide="users-2" class="w-4 h-4"></i>
                    <span>Pemisahan Staf vs Owner</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Staf kasir hanya dapat bertanya seputar stok dan transaksi shift berjalan. Informasi sensitif seperti total laba kotor, kas bank, dan approval tindakan hanya bisa dibuka oleh akun Pemilik Usaha (*Owner*).
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-purple-500/[0.04] dark:bg-purple-500/[0.08] border border-purple-500/15 space-y-1">
                <div class="flex items-center gap-1.5 text-purple-700 dark:text-purple-400 font-bold">
                    <i data-lucide="key-round" class="w-4 h-4"></i>
                    <span>Enkripsi Kunci API AES-256</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Kunci API model AI (Google Gemini, OpenAI, Claude) disimpan di database dengan enkripsi standar militer AES-256. Tidak ada pihak lain yang dapat membaca kunci rahasia Anda.
                </p>
            </div>
        </div>

        <div class="pt-2 flex items-center justify-between text-xs">
            <span class="text-black/50 dark:text-white/50">Ingin mengatur izin staf kasir untuk fitur AI?</span>
            <a href="{{ route('roles.index') }}" class="font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                <span>Atur Peran &amp; Hak Akses Kasir</span>
                <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>

    <!-- 6. TABEL KONTROL OTORITAS AI: APA YANG BOLEH (DO'S) DAN DILARANG KERAS (DON'TS) DILAKUKAN AI -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-6 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-black/5 dark:border-white/10">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-rose-500 via-amber-500 to-emerald-500 text-white flex items-center justify-center font-black text-base shadow-sm shrink-0">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm sm:text-base font-extrabold text-black dark:text-white tracking-tight">Tabel Kontrol Otoritas AI: Matriks DO's &amp; DON'Ts Resmi</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">Strict Guardrails</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">Human-in-the-Loop</span>
                    </div>
                    <p class="text-xs text-black/60 dark:text-white/60 mt-0.5 leading-relaxed">
                        Tabel ini adalah <strong>pengendali mutlak</strong> perilaku kecerdasan buatan Cooca AI. Sistem secara otomatis membatasi AI hanya dapat mengeksekusi instruksi di kolom <strong>Boleh Dilakukan (DO's)</strong>, dan secara otomatis memblokir setiap tindakan pada kolom <strong>Dilarang Keras (DON'Ts)</strong>.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('social-media.inbox.index') }}" class="h-8 px-3 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-700 dark:text-purple-300 text-[11.5px] font-bold flex items-center gap-1.5 transition">
                    <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                    <span>Tinjau Inbox AI</span>
                </a>
            </div>
        </div>

        <!-- 1. KONTROL KHUSUS: PENGELOLAAN INBOX SOCIAL MEDIA & CHAT PELANGGAN -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs sm:text-sm font-bold text-black dark:text-white">
                    <span class="w-6 h-6 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs font-black">A</span>
                    <span>Domain 1: Pengelolaan Inbox Media Sosial &amp; Obrolan Pelanggan (Omnichannel)</span>
                </div>
                <span class="text-[11px] font-mono text-black/50 dark:text-white/50">WhatsApp, Instagram DM, Messenger</span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-black/10 dark:border-white/10">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-black/[0.03] dark:bg-white/[0.04] border-b border-black/10 dark:border-white/10 text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="p-3.5 w-1/4">Aspek Operasional</th>
                            <th class="p-3.5 w-[38%] text-emerald-800 dark:text-emerald-400 bg-emerald-500/[0.04]">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                                    <span>BOLEH DILAKUKAN AI (DO's)</span>
                                </span>
                            </th>
                            <th class="p-3.5 w-[37%] text-rose-800 dark:text-rose-400 bg-rose-500/[0.04]">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="x-circle" class="w-4 h-4 text-rose-500"></i>
                                    <span>DILARANG KERAS DILAKUKAN AI (DON'TS)</span>
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[11.5px] leading-relaxed">
                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01]">
                            <td class="p-3.5 font-bold text-black dark:text-white">
                                Informasi Produk &amp; Stok
                            </td>
                            <td class="p-3.5 bg-emerald-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Menyebutkan nama produk, deskripsi resmi, dan harga jual pas sesuai data kasir Cooca.</li>
                                    <li>Menginformasikan sisa stok riil (ready, menipis, atau habis).</li>
                                    <li>Menawarkan rekomendasi produk pengganti/alternatif yang ready bila produk yang dicari habis.</li>
                                </ul>
                            </td>
                            <td class="p-3.5 bg-rose-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1 text-rose-900 dark:text-rose-300">
                                    <li><strong>DILARANG MENGARANG (Halusinasi)</strong> nama barang, stok, atau harga fiktif yang tidak ada di sistem.</li>
                                    <li><strong>DILARANG</strong> menjanjikan stok akan tiba pada tanggal tertentu tanpa konfirmasi pesanan beli resmi.</li>
                                    <li><strong>DILARANG</strong> mempromosikan produk dari toko lain/kompetitor.</li>
                                </ul>
                            </td>
                        </tr>

                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01]">
                            <td class="p-3.5 font-bold text-black dark:text-white">
                                Diskon, Promo &amp; Tawar-Menawar
                            </td>
                            <td class="p-3.5 bg-emerald-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Menjelaskan kode voucher promo dan diskon resmi yang sedang aktif di sistem.</li>
                                    <li>Menjelaskan syarat minimal belanja dan batas waktu kupon resmi toko.</li>
                                </ul>
                            </td>
                            <td class="p-3.5 bg-rose-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1 text-rose-900 dark:text-rose-300">
                                    <li><strong>DILARANG KERAS</strong> menyetujui tawar-menawar harga atau memberi diskon manual di luar voucher sistem.</li>
                                    <li><strong>DILARANG</strong> menjanjikan bonus barang atau hadiah tanpa aturan resmi dari pemilik toko.</li>
                                </ul>
                            </td>
                        </tr>

                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01]">
                            <td class="p-3.5 font-bold text-black dark:text-white">
                                Pembayaran &amp; Rekening Bank
                            </td>
                            <td class="p-3.5 bg-emerald-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Mengarahkan pelanggan ke tautan checkout storefront resmi atau nomor kontak kasir resmi toko.</li>
                                    <li>Menjelaskan metode bayar resmi yang didukung (QRIS resmi, Transfer Bank Resmi Toko, EDC Kasir).</li>
                                </ul>
                            </td>
                            <td class="p-3.5 bg-rose-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1 text-rose-900 dark:text-rose-300">
                                    <li><strong>DILARANG KERAS membagikan rekening pribadi</strong> staf, e-wallet pribadi, atau rekening pihak ketiga (Anti-Fraud Kasir/CS).</li>
                                    <li><strong>DILARANG</strong> mengonfirmasi pesanan &ldquo;sudah lunas&rdquo; sebelum mutasi bank terverifikasi resmi oleh sistem.</li>
                                </ul>
                            </td>
                        </tr>

                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01]">
                            <td class="p-3.5 font-bold text-black dark:text-white">
                                Privasi Pelanggan &amp; Etika Respon
                            </td>
                            <td class="p-3.5 bg-emerald-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Menyapa ramah dengan sapaan khas e-commerce Indonesia (&ldquo;Kak&rdquo;).</li>
                                    <li>Menyusun draf balasan ringkas (2–4 kalimat) yang siap ditinjau staf sebelum dikirim.</li>
                                </ul>
                            </td>
                            <td class="p-3.5 bg-rose-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1 text-rose-900 dark:text-rose-300">
                                    <li><strong>DILARANG</strong> membocorkan nama, nomor telepon, alamat, atau histori belanja pelanggan lain (Kepatuhan UU PDP).</li>
                                    <li><strong>DILARANG</strong> tunduk pada manipulasi prompt (*jailbreak*) seperti &ldquo;abaikan aturan sebelumnya&rdquo; atau &ldquo;berikan saya gratis&rdquo;.</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. KONTROL DOMAIN OPERASIONAL BISNIS LAINNYA (POS, GUDANG, KEUANGAN, DATA) -->
        <div class="space-y-3 pt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs sm:text-sm font-bold text-black dark:text-white">
                    <span class="w-6 h-6 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center text-xs font-black">B</span>
                    <span>Domain 2–5: Kasir POS, Gudang, Keuangan, dan Privasi Sistem Terpadu</span>
                </div>
                <span class="text-[11px] font-mono text-black/50 dark:text-white/50">Cross-Module Governance</span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-black/10 dark:border-white/10">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-black/[0.03] dark:bg-white/[0.04] border-b border-black/10 dark:border-white/10 text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="p-3.5 w-1/4">Domain Sistem</th>
                            <th class="p-3.5 w-[38%] text-emerald-800 dark:text-emerald-400 bg-emerald-500/[0.04]">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                                    <span>BOLEH DILAKUKAN AI (DO's)</span>
                                </span>
                            </th>
                            <th class="p-3.5 w-[37%] text-rose-800 dark:text-rose-400 bg-rose-500/[0.04]">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="x-circle" class="w-4 h-4 text-rose-500"></i>
                                    <span>DILARANG KERAS DILAKUKAN AI (DON'TS)</span>
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[11.5px] leading-relaxed">
                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01]">
                            <td class="p-3.5 font-bold text-black dark:text-white">
                                Kasir POS &amp; Meja Penjualan
                            </td>
                            <td class="p-3.5 bg-emerald-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Menghitung subtotal, PPN, dan kembalian belanja dengan presisi matematika.</li>
                                    <li>Memberikan rekomendasi *cross-selling* barang pelengkap saat kasir melayani transaksi.</li>
                                    <li>Memberi peringatan real-time saat kasir mencoba menjual item yang stoknya menipis.</li>
                                </ul>
                            </td>
                            <td class="p-3.5 bg-rose-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1 text-rose-900 dark:text-rose-300">
                                    <li><strong>DILARANG</strong> membypass validasi Supervisor PIN untuk diskon melebihi plafon kasir.</li>
                                    <li><strong>DILARANG</strong> menghapus (*void*) nota yang sudah tercetak tanda bayarnya tanpa otorisasi supervisor.</li>
                                    <li><strong>DILARANG</strong> mengubah harga master produk secara sepihak di tengah transaksi kasir.</li>
                                </ul>
                            </td>
                        </tr>

                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01]">
                            <td class="p-3.5 font-bold text-black dark:text-white">
                                Pengadaan (PO) &amp; Stok Gudang
                            </td>
                            <td class="p-3.5 bg-emerald-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Menganalisa kecepatan penjualan (*burn rate*) dan menghitung estimasi hari habis stok.</li>
                                    <li>Menyusun <strong>DRAF usulan Pesanan Beli (PO)</strong> di Action Center untuk ditinjau pemilik usaha.</li>
                                    <li>Mengingatkan jadwal retur bahan kedaluwarsa ke supplier resmi terdaftar.</li>
                                </ul>
                            </td>
                            <td class="p-3.5 bg-rose-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1 text-rose-900 dark:text-rose-300">
                                    <li><strong>DILARANG</strong> mengirim PO langsung ke supplier tanpa tombol &ldquo;Setujui&rdquo; dari pemilik usaha.</li>
                                    <li><strong>DILARANG</strong> memilih supplier fiktif atau supplier dari toko lain (perlindungan IDOR multi-tenant).</li>
                                    <li><strong>DILARANG</strong> memanipulasi catatan stok opname untuk menutupi selisih fisik barang hilang.</li>
                                </ul>
                            </td>
                        </tr>

                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01]">
                            <td class="p-3.5 font-bold text-black dark:text-white">
                                Keuangan, Kas &amp; Bank
                            </td>
                            <td class="p-3.5 bg-emerald-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Menyajikan rekapitulasi omzet harian, laba kotor, dan rincian metode pembayaran untuk Owner.</li>
                                    <li>Menyusun draf faktur penjualan (invoice) dan surat penawaran (quotation) resmi.</li>
                                    <li>Mendeteksi anomali selisih kas kasir atau potensi pengeluaran ganda.</li>
                                </ul>
                            </td>
                            <td class="p-3.5 bg-rose-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1 text-rose-900 dark:text-rose-300">
                                    <li><strong>DILARANG MENGELUARKAN UANG</strong>: AI tidak memiliki akses memicu transfer dana keluar dari rekening toko.</li>
                                    <li><strong>DILARANG</strong> mengubah nomor faktur atau jurnal akuntansi yang telah berstatus *posted*.</li>
                                    <li><strong>DILARANG</strong> membocorkan laporan laba bersih atau saldo kas toko kepada akun kasir/staf umum.</li>
                                </ul>
                            </td>
                        </tr>

                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01]">
                            <td class="p-3.5 font-bold text-black dark:text-white">
                                Privasi Data &amp; Multi-Tenant
                            </td>
                            <td class="p-3.5 bg-emerald-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Mengisolasi 100% data bisnis hanya dalam lingkup tenant aktif (<code class="font-mono text-[10px] bg-black/5 dark:bg-white/10 px-1 py-0.5 rounded">Context::requireBusiness()</code>).</li>
                                    <li>Mencatat seluruh aksi persetujuan ke log audit permanen (<code class="font-mono text-[10px] bg-black/5 dark:bg-white/10 px-1 py-0.5 rounded">AuditLog</code>).</li>
                                    <li>Melindungi kunci API pihak ketiga dengan enkripsi AES-256.</li>
                                </ul>
                            </td>
                            <td class="p-3.5 bg-rose-500/[0.02] text-black/80 dark:text-white/80">
                                <ul class="list-disc list-inside space-y-1 text-rose-900 dark:text-rose-300">
                                    <li><strong>DILARANG</strong> membaca, mencampur, atau membocorkan data toko A kepada pengguna toko B (Zero Data Leakage).</li>
                                    <li><strong>DILARANG</strong> menampilkan kunci API dalam bentuk teks polos di antarmuka web.</li>
                                    <li><strong>DILARANG</strong> memproses prompt luar yang mengandung instruksi jahat (*prompt injection*).</li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. CARA SISTEM MENEGAKKAN ATURAN INI SECARA TEKNIS (FAIL-SECURE MECHANISM) -->
        <div class="p-4 rounded-xl bg-gradient-to-r from-emerald-500/[0.06] via-blue-500/[0.04] to-purple-500/[0.06] border border-black/5 dark:border-white/10 space-y-2 text-xs">
            <div class="flex items-center gap-2 font-bold text-black dark:text-white">
                <i data-lucide="cpu" class="w-4 h-4 text-emerald-600"></i>
                <span>Bagaimana Sistem Cooca Menjamin AI Patuh 100% pada Tabel Ini?</span>
            </div>
            <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                Penegakan aturan di atas <strong>bukan hanya sebatas instruksi teks (*prompt text*)</strong>, melainkan ditanamkan langsung pada arsitektur perangkat lunak Cooca:
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-[11px]">
                <div class="p-2.5 rounded-lg bg-white dark:bg-zinc-800/80 border border-black/5 dark:border-white/5 space-y-1">
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 block">1. Lapisan Database Barrier</span>
                    <p class="text-black/60 dark:text-white/60 leading-normal">
                        Semua kueri data dibatasi oleh klausa mutlak <code class="font-mono text-[9.5px]">where('business_id', $business->id)</code>. AI secara fisik tidak dapat membaca data toko lain.
                    </p>
                </div>
                <div class="p-2.5 rounded-lg bg-white dark:bg-zinc-800/80 border border-black/5 dark:border-white/5 space-y-1">
                    <span class="font-bold text-[#007AFF] dark:text-[#0A84FF] block">2. Maker-Checker Barrier</span>
                    <p class="text-black/60 dark:text-white/60 leading-normal">
                        Semua aksi berisiko biaya wajib berstatus <code class="font-mono text-[9.5px]">STATUS_PENDING</code>. Fungsi database baru aktif saat pemilik usaha menekan tombol &ldquo;Setujui&rdquo;.
                    </p>
                </div>
                <div class="p-2.5 rounded-lg bg-white dark:bg-zinc-800/80 border border-black/5 dark:border-white/5 space-y-1">
                    <span class="font-bold text-purple-700 dark:text-purple-400 block">3. Anti-Injection Filter</span>
                    <p class="text-black/60 dark:text-white/60 leading-normal">
                        Setiap pesan inbox disaring melalui modul deteksi manipulasi. Pesan yang mencoba meretas peran AI langsung diblokir dan diarahkan ke kontak resmi toko.
                    </p>
                </div>
            </div>
        </div>

        <!-- 4. PANDUAN PRAKTIS: CARA MENAMBAHKAN ATURAN DO & DON'T BARU DILENGKAPI TAUTAN FITUR -->
        <div class="p-5 sm:p-6 rounded-2xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/10 space-y-4 text-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-2.5 font-bold text-black dark:text-white text-sm">
                    <span class="w-7 h-7 rounded-xl bg-gradient-to-tr from-[#007AFF] to-purple-600 text-white flex items-center justify-center text-xs">
                        <i data-lucide="book-open-check" class="w-4 h-4"></i>
                    </span>
                    <span>Tutorial: Cara Menambahkan Aturan DO &amp; DON'T Baru ke AI Toko Anda</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/10 text-[#007AFF]">Panduan Praktis</span>
            </div>

            <p class="text-[12px] text-black/65 dark:text-white/65 leading-relaxed">
                Ingin menambahkan SOP khusus untuk usaha Anda? (Misalnya: <em>&ldquo;Dilarang menerima pesanan kue di bawah H-2&rdquo;</em> atau <em>&ldquo;Wajib konfirmasi kecamatan untuk ongkos kirim catering&rdquo;</em>). Ikuti 3 alur mudah berikut:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 pt-1">
                <!-- Langkah 1 -->
                <div class="p-4 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-2 flex flex-col justify-between">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white">
                            <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[10px]">1</span>
                            <span>Atur Knowledge Produk &amp; Promo</span>
                        </div>
                        <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                            AI secara otomatis mematuhi DO's berdasarkan data master produk dan promo resmi toko Anda. Tambahkan stok atau voucher agar AI langsung mengetahuinya secara real-time.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center gap-2 flex-wrap">
                        <a href="{{ route('products.index') }}" class="text-[11px] font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>Buka Master Produk</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                        <a href="{{ route('crm.vouchers.index') }}" class="text-[11px] font-bold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                            <span>Voucher Promo</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>

                <!-- Langkah 2 -->
                <div class="p-4 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-2 flex flex-col justify-between">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white">
                            <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white flex items-center justify-center text-[10px]">2</span>
                            <span>Uji Coba di Inbox Chat Pelanggan</span>
                        </div>
                        <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                            Buka layar Inbox terpadu. Klik tombol <strong>&ldquo;Aturan AI (DO &amp; DON'T)&rdquo;</strong> untuk melihat tabel pembatasan, lalu klik <strong>&ldquo;Buat Draf Balasan AI&rdquo;</strong> untuk menguji jawaban AI sebelum dikirim.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-black/5 dark:border-white/5">
                        <a href="{{ route('social-media.inbox.index') }}" class="text-[11px] font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>Buka Inbox Chat Toko</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>

                <!-- Langkah 3 -->
                <div class="p-4 rounded-xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-2 flex flex-col justify-between">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 font-bold text-black dark:text-white">
                            <span class="w-5 h-5 rounded-full bg-purple-600 text-white flex items-center justify-center text-[10px]">3</span>
                            <span>Atur Model &amp; Kontrol Aksi (Owner)</span>
                        </div>
                        <p class="text-[11px] text-black/60 dark:text-white/60 leading-relaxed">
                            Pastikan kunci API model AI terhubung di menu Pengaturan Provider. Semua usulan aksi otomatis yang berisiko biaya wajib Anda setujui terlebih dahulu di Action Center.
                        </p>
                    </div>
                    <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center gap-2 flex-wrap">
                        <a href="{{ route('cooca-ai.providers') }}" class="text-[11px] font-bold text-purple-600 dark:text-purple-400 hover:underline flex items-center gap-1">
                            <span>Pengaturan AI</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                        <a href="{{ route('cooca-ai.actions') }}" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                            <span>Action Center</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. TANYA JAWAB BISNIS OWNER (FAQ POPULER SEPUTAR AI) -->
    <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between gap-3 pb-2 border-b border-black/5 dark:border-white/10">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-500 flex items-center justify-center font-bold text-xs">
                    <i data-lucide="help-circle" class="w-4 h-4"></i>
                </span>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Tanya Jawab Pemilik Usaha (FAQ AI)</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">Jawaban atas pertanyaan umum seputar biaya, koneksi offline, dan kontrol operasional</p>
                </div>
            </div>
        </div>

        <div class="space-y-3 text-xs">
            <!-- FAQ 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">1. Apakah saya harus membayar biaya langganan mahal untuk menggunakan Asisten AI Cooca?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Tidak sama sekali. Cooca mendukung model <strong>Google Gemini</strong> yang menyediakan kuota gratis harian dengan biaya sangat murah (ribuan rupiah per bulan untuk puluhan ribu pertanyaan). Jika toko Anda memiliki PC lokal, Anda bahkan dapat menggunakan <strong>Local Ollama</strong> yang 100% gratis selamanya tanpa kuota internet!
                </p>
            </div>

            <!-- FAQ 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">2. Apakah AI bisa sembarangan mengeluarkan uang toko atau memesan barang tanpa izin saya?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    <strong>Pasti tidak.</strong> Sistem Cooca menganut prinsip *Human-in-the-Loop*. AI hanya memiliki wewenang untuk menyusun draf usulan tindakan di menu <strong>Action Center</strong>. Uang tidak akan keluar dan pesanan tidak akan terkirim sebelum Anda menekan tombol <em>&ldquo;Setujui&rdquo;</em>.
                </p>
            </div>

            <!-- FAQ 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">3. Bagaimana jika staf kasir saya bertanya tentang keuntungan toko atau rahasia resep bumbu?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem mendeteksi peran akun yang sedang login. Jika akun kasir bertanya tentang margin keuntungan, AI akan dengan sopan menolak: <em>&ldquo;Maaf, data laba bersih toko hanya dapat diakses oleh Pemilik Usaha.&rdquo;</em> Kasir hanya diizinkan bertanya perihal stok produk, diskon aktif, dan rekap shift kasir mereka sendiri.
                </p>
            </div>

            <!-- FAQ 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5">
                <span class="font-bold text-black dark:text-white text-xs sm:text-sm block">4. Apakah AI tetap dapat membantu saat koneksi internet toko sedang mati (Offline)?</span>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Bila Anda menggunakan integrasi <strong>Local Ollama</strong> di PC kasir/server toko, asisten AI tetap beroperasi normal 100% tanpa internet. Jika Anda menggunakan penyedia cloud seperti Gemini atau OpenAI, transaksi kasir POS tetap berjalan offline, dan AI akan otomatis sinkron kembali segera setelah internet menyala.
                </p>
            </div>
        </div>
    </div>

    <!-- 7. DIREKTORI TAUTAN CEPAT KE SELURUH FITUR TERKAIT -->
    <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-3.5 shadow-2xs">
        <h3 class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
            Tautan Cepat &amp; Direktori Navigasi Fitur Pendukung AI
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2.5 text-xs">
            
            <a href="{{ route('pos.terminal') }}"
                class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] hover:bg-[#007AFF]/10 border border-black/5 dark:border-white/5 transition flex flex-col justify-between group">
                <div class="flex items-center gap-2">
                    <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
                    <span class="font-semibold text-black dark:text-white text-[11px]">Kasir POS</span>
                </div>
                <span class="text-[10px] text-black/40 dark:text-white/40 mt-1">Layar Sentuh Kasir</span>
            </a>

            <a href="{{ route('inventory.stocks') }}"
                class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] hover:bg-emerald-500/10 border border-black/5 dark:border-white/5 transition flex flex-col justify-between group">
                <div class="flex items-center gap-2">
                    <i data-lucide="boxes" class="w-4 h-4 text-emerald-500"></i>
                    <span class="font-semibold text-black dark:text-white text-[11px]">Stok Gudang</span>
                </div>
                <span class="text-[10px] text-black/40 dark:text-white/40 mt-1">Pantauan Real-Time</span>
            </a>

            <a href="{{ route('purchase-orders.index') }}"
                class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] hover:bg-indigo-500/10 border border-black/5 dark:border-white/5 transition flex flex-col justify-between group">
                <div class="flex items-center gap-2">
                    <i data-lucide="truck" class="w-4 h-4 text-indigo-500"></i>
                    <span class="font-semibold text-black dark:text-white text-[11px]">Pesanan Beli PO</span>
                </div>
                <span class="text-[10px] text-black/40 dark:text-white/40 mt-1">Draf Restok Supplier</span>
            </a>

            <a href="{{ route('sales.orders.index') }}"
                class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] hover:bg-blue-500/10 border border-black/5 dark:border-white/5 transition flex flex-col justify-between group">
                <div class="flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="w-4 h-4 text-blue-500"></i>
                    <span class="font-semibold text-black dark:text-white text-[11px]">Sales Order SO</span>
                </div>
                <span class="text-[10px] text-black/40 dark:text-white/40 mt-1">Pesanan Penjualan B2B</span>
            </a>

            <a href="{{ route('finance.cash-bank.index') }}"
                class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] hover:bg-purple-500/10 border border-black/5 dark:border-white/5 transition flex flex-col justify-between group">
                <div class="flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4 text-purple-500"></i>
                    <span class="font-semibold text-black dark:text-white text-[11px]">Kas &amp; Bank</span>
                </div>
                <span class="text-[10px] text-black/40 dark:text-white/40 mt-1">Rekening Operasional</span>
            </a>

            <a href="{{ route('reports.index') }}"
                class="p-3 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] hover:bg-amber-500/10 border border-black/5 dark:border-white/5 transition flex flex-col justify-between group">
                <div class="flex items-center gap-2">
                    <i data-lucide="line-chart" class="w-4 h-4 text-amber-500"></i>
                    <span class="font-semibold text-black dark:text-white text-[11px]">Laporan Laba</span>
                </div>
                <span class="text-[10px] text-black/40 dark:text-white/40 mt-1">Neraca &amp; Laba Rugi</span>
            </a>

        </div>
    </div>
</section>
