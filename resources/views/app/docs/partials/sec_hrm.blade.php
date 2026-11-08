<!-- ===================================================== -->
<!-- SECTION: MODUL HRM, PRESENSI & PENGGAJIAN KARYAWAN   -->
<!-- BUKU PANDUAN LENGKAP PENGGUNAAN UNTUK 20 JENIS USAHA  -->
<!-- ===================================================== -->
<section id="sec-hrm" class="scroll-mt-24 space-y-6"
    x-show="(activeTab === 'all' || activeTab === 'hrm') && matchesSearch('hrm karyawan staf pegawai absensi presensi gps selfie geofence wajah biometrik shift roster jadwal lembur cuti izin kasbon pinjaman gaji slip payroll bank payroll bca mandiri bri ptkp pph21 komisi')">

    <!-- Backward-compatibility anchor for quick links -->
    <div id="sec-payroll-hub" class="hidden"></div>

    <!-- 1. HEADER SEKSI & NAVIGASI TOMBOL CEPAT KE MODUL HRM -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/10">
        <div class="flex items-center gap-3.5">
            <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-sky-600 via-blue-600 to-indigo-600 text-white flex items-center justify-center font-black text-base shadow-[0_2px_12px_rgba(37,99,235,0.35)] shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </span>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-black dark:text-white tracking-tight">SDM, Presensi Geofence &amp; Penggajian Otomatis (HRM Hub)</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">POV Business Owner</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">Standar Enterprise</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Anti-Fraud Presensi</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">20 Sektor UMKM</span>
                </div>
                <p class="text-xs sm:text-[13px] text-black/50 dark:text-white/50 mt-0.5">Kendali penuh produktivitas tim: pantau presensi geofence radius outlet, biometrik anti-titip absen, roster shift fleksibel, pinjaman kasbon, hingga otomasi kalkulasi gaji dan distribusi slip digital WhatsApp sekali klik.</p>
            </div>
        </div>

        <!-- Tombol Tautan Langsung ke Fitur Terkait -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('users.view'))
                <a href="{{ route('hrm.index') }}"
                    class="h-9 px-3.5 rounded-xl bg-[#007AFF] hover:bg-[#0062CC] text-white text-[12px] font-semibold flex items-center gap-1.5 shadow-[0_2px_8px_rgba(0,122,255,0.3)] transition active:scale-95">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span>Pusat Karyawan &amp; HRM</span>
                </a>
            @endif
            @if(\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('users.manage'))
                <a href="{{ route('hrm.payrolls.create') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="badge-dollar-sign" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                    <span>Proses Gaji Baru</span>
                </a>
                <a href="{{ route('hrm.employees.template') }}"
                    class="h-9 px-3.5 rounded-xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/80 dark:text-white/80 text-[12px] font-semibold flex items-center gap-1.5 transition active:scale-95">
                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400"></i>
                    <span>Template Staf</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 2. RINGKASAN EKSEKUTIF (POV BISNIS OWNER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-sky-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i data-lucide="user-check" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1.5">
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Bebas Pusing Urusan Personalia: Absensi Tervalidasi Nyata &amp; Hitung Gaji Karyawan Selesai dalam 5 Menit</h3>
                <p class="text-xs sm:text-[13px] text-black/60 dark:text-white/60 leading-relaxed">
                    Bagi pemilik bisnis UMKM dengan banyak cabang atau staf lapangan, mengelola tim sering kali menghabiskan waktu berharga: <strong>buku absen fisik yang mudah dimanipulasi, saling titip absen antar-karyawan, staf yang mengaku sudah tiba di toko padahal masih di jalan, hingga malam-malam lembur menghitung potongan kasbon, denda telat, dan komisi penjualan</strong>. Modul HRM Cooca menghadirkan otomasi menyeluruh yang menjamin keadilan bagi staf sekaligus melindungi kas bisnis Anda dari pengeluaran gaji fiktif.
                </p>
            </div>
        </div>

        <!-- 4 Pilar Manajemen SDM COOCA bagi Owner -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <div class="p-4 rounded-xl bg-sky-500/[0.06] dark:bg-sky-500/[0.1] border border-sky-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-sky-700 dark:text-sky-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="user-cog" class="w-4 h-4"></i>
                    <span>1. Manajemen Staf Terpadu</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Satu pangkalan data karyawan lengkap: biodata, jabatan/role, outlet penugasan, nomor rekening bank payroll, gaji pokok, hingga tunjangan harian yang tersimpan rapi.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-emerald-500/[0.06] dark:bg-emerald-500/[0.1] border border-emerald-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>2. Presensi Geofence &amp; Wajah</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Karyawan hanya bisa clock-in saat berada di dalam radius koordinat GPS toko (geofencing) disertai swafoto (selfie) pencocokan biometrik wajah anti-fake GPS.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-amber-500/[0.06] dark:bg-amber-500/[0.1] border border-amber-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="calendar-range" class="w-4 h-4"></i>
                    <span>3. Roster &amp; Shift Fleksibel</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Atur jadwal shift pagi, siang, dan malam dengan toleransi menit keterlambatan otomatis. Kelola cuti, sakit, dan persetujuan koreksi absen berlampir foto surat dokter.
                </p>
            </div>

            <div class="p-4 rounded-xl bg-indigo-500/[0.06] dark:bg-indigo-500/[0.1] border border-indigo-500/20 space-y-1.5">
                <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-400 font-bold text-xs sm:text-sm">
                    <i data-lucide="badge-dollar-sign" class="w-4 h-4"></i>
                    <span>4. Payroll Otomatis &amp; Slip WA</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Hitung total gaji bersih otomatis: gaji pokok + tunjangan + komisi kasir dikurangi cicilan kasbon &amp; denda telat. Ekspor batch transfer bank dan kirim slip digital via WhatsApp.
                </p>
            </div>
        </div>
    </div>

    <!-- 3. ALUR KERJA LANGKAH DEMI LANGKAH (4 LANGKAH SOP PRAKTIS) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-black/60 dark:text-white/60 flex items-center gap-2">
                <i data-lucide="git-commit" class="w-4 h-4 text-[#007AFF]"></i>
                <span>SOP Pengelolaan Karyawan, Presensi &amp; Penggajian Bulanan (4 Langkah Praktis)</span>
            </h3>
            <span class="text-[11px] font-semibold text-black/40 dark:text-white/40">SOP Operasional HR</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- Step 1 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold text-xs">1</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Setup Outlet &amp; Radius Geofence</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Buka menu <strong>Lokasi Presensi</strong>, tentukan titik koordinat GPS cabang/toko Anda dan atur radius toleransi (misal: 50&ndash;100 meter). Karyawan di luar radius ini tidak dapat melakukan presensi clock-in.
                </p>
                <div class="pt-1 text-[11px] font-medium text-sky-600 dark:text-sky-400">
                    Cegah absensi fiktif dari rumah
                </div>
            </div>

            <!-- Step 2 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">2</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Atur Shift &amp; Roster Jadwal</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Tentukan jam masuk dan pulang di menu <strong>Shift Kerja</strong> (misal: Pagi 08.00&ndash;16.00, Siang 14.00&ndash;22.00) lengkap dengan toleransi keterlambatan (15 menit). Bagikan jadwal bergilir ke staf lewat menu <strong>Jadwal Kerja</strong>.
                </p>
                <div class="pt-1 text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                    Penjadwalan rolling adil &amp; transparan
                </div>
            </div>

            <!-- Step 3 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">3</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Perekaman Wajah &amp; Presensi Harian</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Daftarkan wajah karyawan sekali di profil staf. Setiap hari, karyawan membuka ponsel mereka di toko, melakukan swafoto selfie, dan sistem mencocokkan biometrik wajah serta lokasi secara instan.
                </p>
                <div class="pt-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                    Validasi 100% tanpa mesin fingerprint mahal
                </div>
            </div>

            <!-- Step 4 -->
            <div class="p-4 rounded-xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-2 relative overflow-hidden">
                <div class="w-6 h-6 rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs">4</div>
                <h4 class="font-bold text-black dark:text-white text-sm">Batch Penggajian &amp; Rilis Slip WA</h4>
                <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">
                    Pada akhir bulan, klik <strong>Proses Gaji Baru</strong>. Cooca merekap total kehadiran, lembur, kasbon, dan komisi kasir. Owner menyetujui, mengunduh file transfer bank (BCA/Mandiri/BRI), dan slip digital langsung terbit.
                </p>
                <div class="pt-1 text-[11px] font-medium text-amber-600 dark:text-amber-400">
                    Tutup buku gaji dalam 5 menit
                </div>
            </div>
        </div>
    </div>

    <!-- 4. PETA 8 SUB-FITUR UTAMA MODUL HRM COOCA -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-sky-600"></i>
                    <span>Direktori 8 Fitur Personalia &amp; Penggajian Terpadu</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Seluruh instrumen operasional SDM dari rekrutmen hingga pelaporan payroll dalam satu antarmuka.</p>
            </div>
            <span class="text-xs font-bold text-sky-600 dark:text-sky-400 bg-sky-500/10 border border-sky-500/20 px-2 py-0.5 rounded-md">8 Tab Terintegrasi</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
            <!-- 1. Data Karyawan -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="users" class="w-4 h-4 text-sky-600"></i>
                            1. Master Karyawan
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400">Database Staf</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Profil karyawan, status kontrak, jabatan, rekening bank, dan komponen gaji dasar.</p>
                </div>
                <a href="{{ route('hrm.index', ['tab' => 'employees']) }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Buka Data Staf</span> &rarr;
                </a>
            </div>

            <!-- 2. Log Presensi -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-4 h-4 text-emerald-600"></i>
                            2. Log Presensi Real-Time
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">GPS &amp; Selfie</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Riwayat jam clock-in/out harian, verifikasi swafoto, jarak radius meter dari toko, dan status hadir/telat.</p>
                </div>
                <a href="{{ route('hrm.index', ['tab' => 'attendance']) }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Lihat Log Presensi</span> &rarr;
                </a>
            </div>

            <!-- 3. Koreksi & Izin -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="clipboard-check" class="w-4 h-4 text-amber-500"></i>
                            3. Koreksi &amp; Perizinan
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400">Approval Mar</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Pengajuan lupa clock-in, cuti tahunan, sakit, atau dinas luar kota lengkap dengan lampiran dokumen.</p>
                </div>
                <a href="{{ route('hrm.index', ['tab' => 'corrections']) }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Tinjau Koreksi Absen</span> &rarr;
                </a>
            </div>

            <!-- 4. Lokasi Geofencing -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-4 h-4 text-rose-500"></i>
                            4. Lokasi Geofence
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400">Radius GPS</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Kelola koordinat GPS multi-outlet, batas radius presensi (50&ndash;200 meter), dan toleransi WFH/WFA.</p>
                </div>
                <a href="{{ route('hrm.index', ['tab' => 'locations']) }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Atur Radius Outlet</span> &rarr;
                </a>
            </div>

            <!-- 5. Shift Kerja -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-4 h-4 text-indigo-500"></i>
                            5. Konfigurasi Shift
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">Jam Kerja</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Master jam kerja (pagi, sore, malam), durasi istirahat, toleransi keterlambatan, dan jam batas lembur.</p>
                </div>
                <a href="{{ route('hrm.index', ['tab' => 'shifts']) }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Kelola Master Shift</span> &rarr;
                </a>
            </div>

            <!-- 6. Jadwal Kerja (Roster) -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="calendar-check" class="w-4 h-4 text-purple-600"></i>
                            6. Roster &amp; Jadwal
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-600 dark:text-purple-400">Roster Tim</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Penugasan shift per staf per tanggal dalam kalender visual untuk memastikan outlet selalu terisi.</p>
                </div>
                <a href="{{ route('hrm.index', ['tab' => 'schedules']) }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Atur Jadwal Roster</span> &rarr;
                </a>
            </div>

            <!-- 7. Payroll Run -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="badge-dollar-sign" class="w-4 h-4 text-teal-600"></i>
                            7. Mesin Payroll
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-teal-500/10 text-teal-600 dark:text-teal-400">Otomasi Gaji</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Generasi penggajian bulanan otomatis, slip digital PDF, dan ekspor CSV transfer bank resmi.</p>
                </div>
                <a href="{{ route('hrm.index', ['tab' => 'payrolls']) }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Buka Riwayat Gaji</span> &rarr;
                </a>
            </div>

            <!-- 8. Kasbon Karyawan -->
            <div class="p-3.5 rounded-xl border border-black/5 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] space-y-2 flex flex-col justify-between">
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-black dark:text-white text-[13px] flex items-center gap-1.5">
                            <i data-lucide="receipt" class="w-4 h-4 text-rose-500"></i>
                            8. Kasbon &amp; Pinjaman
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400">Cicilan Gaji</span>
                    </div>
                    <p class="text-black/60 dark:text-white/60 text-[11.5px] leading-relaxed">Pencatatan pinjaman darurat karyawan dan pemotongan otomatis nominal cicilan di slip gaji bulanan.</p>
                </div>
                <a href="{{ route('hrm.index', ['tab' => 'loans']) }}" class="text-[11.5px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 pt-1">
                    <span>Pantau Kasbon Staf</span> &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- 5. MATRIKS PENERAPAN NYATA UNTUK 20 JENIS USAHA BERBEDA (6 KLASTER) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-5 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-black/5 dark:border-white/10">
            <div>
                <h3 class="text-base font-extrabold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-sky-600"></i>
                    <span>Penerapan Modul HRM pada 20 Jenis Usaha</span>
                </h3>
                <p class="text-xs text-black/50 dark:text-white/50 mt-0.5">Skenario konkret penjadwalan shift, presensi biometrik, dan sistem komisi di 6 klaster UMKM Indonesia.</p>
            </div>
            <span class="px-2.5 py-1 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 text-xs font-bold border border-sky-500/20 self-start sm:self-auto">
                20 Praktik Manajemen SDM
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
                    <p><strong>1. Restoran Dine-In:</strong> Pengaturan split-shift koki dapur dan pramusaji (shift siang &amp; malam) dengan insentif komisi omzet target harian yang otomatis masuk ke slip gaji.</p>
                    <p><strong>2. Coffee Shop &amp; Cafe:</strong> Rotasi roster barista opening (07.00) dan closing (23.00), toleransi keterlambatan 10 menit saat antrean sarapan kopi sedang ramai.</p>
                    <p><strong>3. Bakery &amp; Toko Roti:</strong> Shift subuh (03.30) khusus kepala baker adonan dengan uang kehadiran makan subuh yang dihitung per hari kerja riil.</p>
                    <p><strong>4. Cloud Kitchen Online:</strong> Presensi geofence di lokasi dapur sentral bersama, memantau kedisiplinan staf kemas pesanan kurir ojek online.</p>
                    <p><strong>5. Katering Prasmanan:</strong> Penjadwalan pekerja lepas harian (daily worker) khusus hari H acara pernikahan dengan slip honorarium per event.</p>
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
                    <p><strong>6. Konveksi &amp; Garment:</strong> Penggabungan absensi kehadiran wajib dengan input upah borongan jahit per potong baju ke dalam komponen tunjangan slip gaji.</p>
                    <p><strong>7. Percetakan &amp; Offset:</strong> Shift malam operator mesin cetak berkecepatan tinggi saat mengejar deadline tender buku dan brosur pemilu.</p>
                    <p><strong>8. Mebel &amp; Furniture:</strong> Pengawasan kasbon mingguan tukang kayu amplas dan pemotongan teratur setiap tanggal gajian bulanan.</p>
                    <p><strong>9. Bengkel Bubut &amp; Logam:</strong> Validasi presensi selfie teknisi bubut presisi dengan insentif keselamatan kerja (K3 zero-accident bonus).</p>
                    <p><strong>10. Kerajinan Tangan / Craft:</strong> Pencatatan honor perajin anyaman bambu rumahan yang menyetor hasil karya per akhir pekan.</p>
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
                    <p><strong>11. Minimarket &amp; Kelontong:</strong> Shift kasir berpasangan, bonus ketepatan rekap kas laci tanpa selisih yang masuk ke slip gaji bulanan.</p>
                    <p><strong>12. Apotek &amp; Toko Obat:</strong> Penjadwalan wajib Apoteker Penanggung Jawab (APJ) dan asisten apoteker sesuai syarat regulasi Dinas Kesehatan setempat.</p>
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
                    <p><strong>13. Bengkel Mobil &amp; Motor:</strong> Gaji pokok ditambah komisi bagi hasil jasa servis mekanik per unit mobil/motor yang diselesaikan.</p>
                    <p><strong>14. Barbershop &amp; Salon:</strong> Perhitungan komisi kapster potong rambut per kepala dan persentase tips pelanggan non-tunai di slip payroll.</p>
                    <p><strong>15. Laundry Kiloan:</strong> Shift operator setrika uap kiloan dengan bonus lembur saat lonjakan cucian pasca hari libur panjang.</p>
                    <p><strong>16. Cuci Mobil &amp; Detailing:</strong> Uang makan harian kru cuci mobil ditambah insentif unit poles detailing premium mingguan.</p>
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
                    <p><strong>17. Kontraktor &amp; Renovasi:</strong> Presensi mandor dan tukang di titik lokasi proyek renovasi yang berpindah-pindah menggunakan multi-lokasi geofencing.</p>
                    <p><strong>18. Wedding Organizer:</strong> Kru event freelance yang clock-in di gedung resepsi pernikahan dengan uang saku acara yang dicairkan H+1.</p>
                    <p><strong>19. Digital IT &amp; Creative Agency:</strong> Kebijakan kerja fleksibel WFH / WFA dengan presensi selfie berbasis GPS tanpa pembatasan radius kantor kaku.</p>
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
                    <p><strong>20. Distributor Grosir B2B &amp; Agrobisnis:</strong> Presensi sopir truk armada kirim barang saat tiba di gudang tujuan dan pencatatan uang jalan sopir yang transparan.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. KEBIJAKAN OTORISASI RBAC & ANTI-FRAUD PRESENSI -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs">
        <div class="flex items-center gap-3">
            <span class="w-9 h-9 rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
            </span>
            <div>
                <h3 class="text-sm sm:text-base font-bold text-black dark:text-white">Pemisahan Wewenang (Segregation of Duties) &amp; Perlindungan Anti-Fraud Presensi</h3>
                <p class="text-xs text-black/50 dark:text-white/50">Mekanisme kontrol berlapis untuk menjamin catatan kehadiran jujur dan penggajian bebas kebocoran.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs pt-1">
            <div class="p-4 rounded-xl border border-sky-500/20 bg-sky-500/[0.04] dark:bg-sky-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-sky-700 dark:text-sky-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Isolasi Akses Slip &amp; Data Gaji</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Karyawan biasa hanya dapat melihat riwayat presensi dan slip gajinya sendiri. Wewenang membuat jadwal shift, menyetujui koreksi absen, memproses payroll, dan mengekspor CSV bank dikunci ketat untuk <strong>Owner atau HR Manager</strong> dengan guard <code>users.manage</code>.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-sky-500/20 bg-sky-500/[0.04] dark:bg-sky-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-sky-700 dark:text-sky-400">
                    <i data-lucide="smartphone" class="w-4 h-4"></i>
                    <span>Deteksi Fake GPS &amp; Mock Location</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Sistem mendeteksi penggunaan aplikasi Fake GPS atau manipulasi koordinat lokasi. Presensi dari perangkat yang terdeteksi memakai mock provider akan otomatis ditolak dan dicatat di audit log untuk investigasi supervisor toko.
                </p>
            </div>

            <div class="p-4 rounded-xl border border-sky-500/20 bg-sky-500/[0.04] dark:bg-sky-500/[0.08] space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-sky-700 dark:text-sky-400">
                    <i data-lucide="scan-face" class="w-4 h-4"></i>
                    <span>Pencocokan Biometrik Wajah</span>
                </div>
                <p class="text-[11.5px] text-black/60 dark:text-white/60 leading-relaxed">
                    Saat clock-in, swafoto selfie karyawan diverifikasi terhadap template wajah terdaftar. Foto selfie tidak dapat diambil dari galeri ponsel (wajib kamera langsung) untuk mengeliminasi modus titip absen menggunakan foto cetak.
                </p>
            </div>
        </div>
    </div>

    <!-- 7. TANYA JAWAB BISNIS OWNER (FAQ) -->
    <div class="p-5 sm:p-7 rounded-2xl bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 space-y-4 shadow-2xs"
        x-data="{ expandedFaq: null }">
        <div class="flex items-center justify-between pb-2 border-b border-black/5 dark:border-white/10">
            <h3 class="text-sm sm:text-base font-bold text-black dark:text-white flex items-center gap-2">
                <i data-lucide="help-circle" class="w-4 h-4 text-sky-600"></i>
                <span>Tanya Jawab Seputar SDM, Presensi &amp; Penggajian Toko (FAQ)</span>
            </h3>
            <span class="text-xs text-black/40 dark:text-white/40">5 Solusi Cepat</span>
        </div>

        <div class="space-y-3">
            <!-- FAQ 1 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 1 ? null : 1)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>1. Bagaimana cara Cooca mencegah karyawan saling menitipkan absen saat terlambat?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 1 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 1" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Cooca menerapkan proteksi ganda: <strong>Geofencing Radius GPS</strong> dan <strong>Swafoto Selfie Wajah Langsung</strong>. Karyawan hanya bisa clock-in saat berada dalam radius toko (misal: 50 meter) dan wajib mengambil foto wajah secara langsung melalui kamera smartphone saat itu juga. Sistem melarang upload dari galeri foto dan memblokir aplikasi fake GPS, sehingga praktik titip absen tidak mungkin dilakukan.</p>
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 2 ? null : 2)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>2. Apakah saya bisa mengatur komponen gaji yang bervariasi (gaji pokok, uang makan per kehadiran, dan komisi omzet)?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 2 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 2" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Sangat bisa. Pada master profil karyawan, Anda dapat menentukan gaji pokok bulanan serta tarif uang makan atau tunjangan harian yang otomatis dikalikan dengan total hari kehadiran riil. Khusus kasir atau tenaga penjual, komisi penjualan POS juga dapat dihitung otomatis dan dijumlahkan ke dalam slip gaji akhir bulan.</p>
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 3 ? null : 3)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>3. Bagaimana jika karyawan lupa melakukan presensi pulang saat buru-buru tutup toko?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 3 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 3" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Karyawan dapat mengajukan <strong>Koreksi Presensi</strong> melalui portal akun mereka dengan menjelaskan alasan dan jam kepulangan sebenarnya. Supervisor atau Owner akan menerima notifikasi di tab <strong>Koreksi &amp; Izin</strong> untuk menyetujui (Approve) atau menolak (Reject) pengajuan tersebut setelah mengecek kebenaran operasional toko.</p>
                </div>
            </div>

            <!-- FAQ 4 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 4 ? null : 4)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>4. Bagaimana cara mendistribusikan slip gaji digital ke karyawan tanpa perlu mencetak kertas?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 4 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 4" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Setelah batch payroll disetujui, Cooca secara otomatis menghasilkan tautan slip gaji digital unik berenkripsi token untuk masing-masing karyawan. Anda dapat mengirimkan tautan tersebut sekali klik ke nomor WhatsApp staf atau karyawan dapat langsung membukanya di portal mandiri staf Cooca tanpa bisa mengintip rincian gaji rekan kerjanya.</p>
                </div>
            </div>

            <!-- FAQ 5 -->
            <div class="rounded-xl border border-black/5 dark:border-white/10 overflow-hidden">
                <button type="button" @click="expandedFaq = (expandedFaq === 5 ? null : 5)"
                    class="w-full p-4 text-left flex items-center justify-between gap-3 font-bold text-xs sm:text-[13px] text-black dark:text-white hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                    <span>5. Apakah Cooca menyediakan format ekspor untuk transfer gaji massal via bank (BCA Payroll, Mandiri, BRI)?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform text-black/40 dark:text-white/40"
                        :class="expandedFaq === 5 ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="expandedFaq === 5" x-collapse class="px-4 pb-4 text-xs text-black/60 dark:text-white/60 space-y-2 border-t border-black/5 dark:border-white/10 pt-3">
                    <p>Ya. Pada detail batch payroll yang telah disetujui, klik tombol <strong>Ekspor CSV Bank</strong>. Berkas tersebut sudah disesuaikan dengan struktur kolom standar corporate internet banking (seperti KlikBCA Bisnis, Mandiri Cash Management / MCM, dan BRI CMS), sehingga Anda tinggal mengunggah satu berkas ke bank untuk mentransfer gaji seluruh tim sekaligus.</p>
                </div>
            </div>
        </div>
    </div>

</section>
