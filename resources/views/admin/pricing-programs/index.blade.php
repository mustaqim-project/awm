@extends('layouts.admin')

@section('title', 'Early Adopter & Program Harga Khusus | Platform Admin')

@section('content')
<div x-data="{
    activeTab: '{{ $tab }}',
    showProgramModal: false,
    showEditProgramModal: false,
    showBatchModal: false,
    showEditBatchModal: false,
    showNextBatchModal: false,
    showEditBasePricesModal: false,
    currentProgram: { id: '', name: '', code: '', description: '', is_active: true },
    currentBatch: {
        id: '', program_id: '', name: '', status: 'draft', trigger_mode: 'hybrid',
        hybrid_operator: 'either',
        quota_limit: '', starts_at: '', ends_at: '', fallback_behavior: 'base_pricing',
        standard_monthly_price: '', standard_annual_price: '',
        premium_monthly_price: '', premium_annual_price: '',
        prestige_monthly_price: '', prestige_annual_price: '',
        annual_increase_percentage: '0.00', annual_increase_basis: 'subscription_anniversary',
        annual_increase_effective_month: '', annual_increase_effective_day: '',
        rounding_mode: 'none', resubscribe_policy: 'grace_period', resubscribe_grace_days: 7,
        upgrade_downgrade_policy: 'inherit_active_batch', notes: '', next_batch_id: ''
    },
    selectedProgramIdForBatch: '',
    validateMaxIncrease(val) {
        let num = parseFloat(val);
        if (isNaN(num) || num < 0) return 0;
        if (num > 10.0) {
            Swal.fire({
                icon: 'warning',
                title: 'Batas Kenaikan Maksimal 10%',
                text: 'Sistem membatasi kenaikan harga tahunan maksimal 10.00% per tahun untuk melindungi pelanggan Early Adopter.',
                confirmButtonColor: '#007AFF'
            });
            return 10.00;
        }
        return num;
    },
    openEditProgram(prog) {
        this.currentProgram = { ...prog };
        this.showEditProgramModal = true;
        this.refreshIcons();
    },
    openCreateBatch(programId) {
        this.selectedProgramIdForBatch = programId;
        this.currentBatch = {
            id: '', program_id: programId, name: '', status: 'draft', trigger_mode: 'hybrid',
            hybrid_operator: 'either',
            quota_limit: '1000', starts_at: '', ends_at: '', fallback_behavior: 'base_pricing',
            standard_monthly_price: '29000', standard_annual_price: '290000',
            premium_monthly_price: '69000', premium_annual_price: '690000',
            prestige_monthly_price: '159000', prestige_annual_price: '1590000',
            annual_increase_percentage: '10.00', annual_increase_basis: 'subscription_anniversary',
            annual_increase_effective_month: '', annual_increase_effective_day: '',
            rounding_mode: 'none', resubscribe_policy: 'grace_period', resubscribe_grace_days: 7,
            upgrade_downgrade_policy: 'inherit_active_batch', notes: '', next_batch_id: ''
        };
        this.showBatchModal = true;
        this.refreshIcons();
    },
    openEditBatch(batch) {
        this.currentBatch = {
            ...batch,
            hybrid_operator: batch.hybrid_operator || 'either',
            starts_at: batch.starts_at ? batch.starts_at.substring(0, 16) : '',
            ends_at: batch.ends_at ? batch.ends_at.substring(0, 16) : '',
        };
        this.showEditBatchModal = true;
        this.refreshIcons();
    },
    openNextBatchLink(batch) {
        this.currentBatch = { ...batch };
        this.showNextBatchModal = true;
        this.refreshIcons();
    },
    refreshIcons() {
        this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
    }
}" x-init="refreshIcons()" class="space-y-6">

    <!-- 1. Header & Navigation Pills -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF]">
                    <i data-lucide="sparkles" class="w-4 h-4" stroke-width="2"></i>
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-black dark:text-white tracking-tight">
                    Early Adopter &amp; Program Harga Khusus
                </h1>
            </div>
            <p class="text-xs sm:text-sm text-black/60 dark:text-white/60 mt-1">
                Kelola program harga khusus, batas kuota pendaftaran bertahap (batch), proteksi penguncian harga pelanggan lama, dan eskalasi tahunan terukur (maks. 10%).
            </p>
        </div>

        <div class="flex items-center gap-2 self-start md:self-auto">
            @if(Auth::guard('admin')->user()?->hasPermission('admin.pricing_programs.manage'))
                <button type="button" @click="showProgramModal = true; refreshIcons()"
                    class="h-9 px-3.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition active:scale-[0.98]">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Buat Program Baru</span>
                </button>
            @endif
        </div>
    </div>

    <!-- 2. Cockpit Metrics Bento -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                <span>Total Program</span>
                <i data-lucide="folder-kanban" class="w-4 h-4 text-[#007AFF]"></i>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white mt-1.5 tabular-nums">
                {{ $totalPrograms }}
            </div>
            <div class="text-[11px] text-black/50 dark:text-white/50 mt-1 flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-[#34C759]"></span>
                <span>{{ $activePrograms }} program aktif</span>
            </div>
        </div>

        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                <span>Batch Berjalan</span>
                <i data-lucide="layers" class="w-4 h-4 text-[#AF52DE]"></i>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white mt-1.5 tabular-nums">
                {{ $activeBatches }}
            </div>
            <div class="text-[11px] text-black/50 dark:text-white/50 mt-1">
                Tahapan pendaftaran aktif saat ini
            </div>
        </div>

        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                <span>Pelanggan Terkunci</span>
                <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white mt-1.5 tabular-nums">
                {{ $totalEarlyAdopterCustomers }}
            </div>
            <div class="text-[11px] text-black/50 dark:text-white/50 mt-1">
                Grandfathered price aktif
            </div>
        </div>

        <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                <span>Kenaikan Jatuh Tempo</span>
                <i data-lucide="calendar-clock" class="w-4 h-4 text-[#FF9500]"></i>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-black dark:text-white mt-1.5 tabular-nums">
                {{ $dueEscalationsCount }}
            </div>
            <div class="text-[11px] text-black/50 dark:text-white/50 mt-1">
                Siap dieksekusi scheduler hari ini
            </div>
        </div>
    </div>

    <!-- 3. Base Reference Pricing Card (Single Source of Truth) -->
    <div class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-black/[0.05] dark:border-white/[0.06]">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-[10px] bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="badge-percent" class="w-4 h-4"></i>
                </span>
                <div>
                    <h2 class="text-sm font-bold text-black dark:text-white flex items-center gap-2">
                        <span>Pusat Pengelolaan Harga Normal Publik &amp; Promo Tahunan</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">Single Source of Truth</span>
                    </h2>
                    <p class="text-[11px] text-black/50 dark:text-white/50">
                        Harga acuan resmi yang berlaku di landing page (/pricing), checkout (/billing/checkout), batas kuota (/billing/limits), dan fallback jika promo batch berakhir.
                    </p>
                </div>
            </div>

            @if(Auth::guard('admin')->user()?->hasPermission('admin.pricing_programs.manage'))
                <button type="button" @click="showEditBasePricesModal = true; refreshIcons()"
                    class="h-8 px-3 rounded-[10px] bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-2xs flex items-center gap-1.5 transition active:scale-[0.98] shrink-0 cursor-pointer">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    <span>Ubah Harga Normal &amp; Promo</span>
                </button>
            @endif
        </div>

        <!-- 4 Grid Columns for Plans & Promo Badge -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Free Plan -->
            <div class="p-3.5 rounded-[14px] bg-slate-50 dark:bg-white/[0.02] border border-black/[0.04] dark:border-white/[0.05] space-y-1">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 font-mono">Free Solo</div>
                <div class="text-lg font-black text-slate-900 dark:text-white font-mono">Rp 0</div>
                <div class="text-[11px] text-slate-500">Selamanya gratis &bull; Tanpa AI</div>
            </div>

            <!-- Standard Plan -->
            <div class="p-3.5 rounded-[14px] bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/50 dark:border-emerald-900/40 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 font-mono">Standard UMKM</span>
                </div>
                <div class="text-lg font-black text-emerald-700 dark:text-emerald-400 font-mono tabular-nums">
                    Rp {{ number_format($basePrices['standard_monthly'], 0, ',', '.') }}<span class="text-xs font-normal text-slate-500">/bln</span>
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-400 font-mono">
                    Tahunan: Rp {{ number_format($basePrices['standard_annual'], 0, ',', '.') }}/thn
                </div>
            </div>

            <!-- Premium Plan -->
            <div class="p-3.5 rounded-[14px] bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-200/50 dark:border-indigo-900/40 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 font-mono">Premium Scale-Up</span>
                </div>
                <div class="text-lg font-black text-indigo-700 dark:text-indigo-400 font-mono tabular-nums">
                    Rp {{ number_format($basePrices['premium_monthly'], 0, ',', '.') }}<span class="text-xs font-normal text-slate-500">/bln</span>
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-400 font-mono">
                    Tahunan: Rp {{ number_format($basePrices['premium_annual'], 0, ',', '.') }}/thn
                </div>
            </div>

            <!-- Prestige Plan -->
            <div class="p-3.5 rounded-[14px] bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200/50 dark:border-purple-900/40 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-purple-700 dark:text-purple-400 font-mono">Prestige Enterprise</span>
                </div>
                <div class="text-lg font-black text-purple-700 dark:text-purple-400 font-mono tabular-nums">
                    Rp {{ number_format($basePrices['prestige_monthly'], 0, ',', '.') }}<span class="text-xs font-normal text-slate-500">/bln</span>
                </div>
                <div class="text-[11px] text-slate-600 dark:text-slate-400 font-mono">
                    Tahunan: Rp {{ number_format($basePrices['prestige_annual'], 0, ',', '.') }}/thn
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-black/[0.04] dark:border-white/[0.05] flex flex-wrap items-center justify-between text-xs text-black/60 dark:text-white/60">
            <div class="flex items-center gap-2">
                <i data-lucide="tag" class="w-3.5 h-3.5 text-emerald-600"></i>
                <span>Teks Badge Promo Paket Tahunan: <strong class="text-emerald-700 dark:text-emerald-400 font-mono bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-[6px] border border-emerald-200/60 dark:border-emerald-800/60">{{ $basePrices['annual_discount_badge'] }}</strong></span>
            </div>
            <div class="text-[11px] text-slate-400 font-mono">
                Perubahan tersimpan otomatis di SystemSettings &amp; BillingPackages
            </div>
        </div>
    </div>

    <!-- 4. Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-black/[0.06] dark:border-white/[0.08] pb-1 overflow-x-auto">
        <a href="{{ route('admin.pricing-programs.index', ['tab' => 'programs']) }}"
            class="px-3.5 py-2 rounded-[12px] text-xs font-semibold transition flex items-center gap-1.5 shrink-0 {{ $tab === 'programs' ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.03] dark:hover:bg-white/[0.05]' }}">
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            <span>Program &amp; Batch Konfigurasi</span>
        </a>

        <a href="{{ route('admin.pricing-programs.index', ['tab' => 'customers']) }}"
            class="px-3.5 py-2 rounded-[12px] text-xs font-semibold transition flex items-center gap-1.5 shrink-0 {{ $tab === 'customers' ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.03] dark:hover:bg-white/[0.05]' }}">
            <i data-lucide="users" class="w-3.5 h-3.5"></i>
            <span>Pelanggan per Batch</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'customers' ? 'bg-white/20 text-white' : 'bg-black/[0.06] dark:bg-white/[0.1] text-black/70 dark:text-white/70' }}">
                {{ $totalEarlyAdopterCustomers }}
            </span>
        </a>

        <a href="{{ route('admin.pricing-programs.index', ['tab' => 'schedule']) }}"
            class="px-3.5 py-2 rounded-[12px] text-xs font-semibold transition flex items-center gap-1.5 shrink-0 {{ $tab === 'schedule' ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.03] dark:hover:bg-white/[0.05]' }}">
            <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
            <span>Jadwal Kenaikan Tahunan (Maks 10%)</span>
            @if($dueEscalationsCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-[#FF3B30] text-white">
                    {{ $dueEscalationsCount }}
                </span>
            @endif
        </a>

        <a href="{{ route('admin.pricing-programs.index', ['tab' => 'logs']) }}"
            class="px-3.5 py-2 rounded-[12px] text-xs font-semibold transition flex items-center gap-1.5 shrink-0 {{ $tab === 'logs' ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/[0.03] dark:hover:bg-white/[0.05]' }}">
            <i data-lucide="history" class="w-3.5 h-3.5"></i>
            <span>Riwayat &amp; Audit Log Harga</span>
        </a>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 1: PROGRAMS & BATCHES -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    @if ($tab === 'programs')
        <div class="space-y-6">
            @forelse ($programs as $program)
                <div class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-5">
                    <!-- Program Top Row -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-black/[0.05] dark:border-white/[0.06]">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex w-3 h-3 rounded-full {{ $program->is_active ? 'bg-[#34C759]' : 'bg-[#8E8E93]' }}"></span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-black dark:text-white">{{ $program->name }}</h3>
                                    <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-mono bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                                        {{ $program->code }}
                                    </span>
                                </div>
                                @if($program->description)
                                    <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">{{ $program->description }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 self-end sm:self-auto">
                            @if(Auth::guard('admin')->user()?->hasPermission('admin.pricing_programs.manage'))
                                <button type="button" @click="openCreateBatch('{{ $program->id }}')"
                                    class="h-8 px-3 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] text-xs font-semibold flex items-center gap-1 transition">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Tambah Batch</span>
                                </button>

                                <button type="button" @click="openEditProgram({{ json_encode($program) }})"
                                    class="h-8 px-2.5 rounded-[10px] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black/70 dark:text-white/70 text-xs font-medium transition"
                                    title="Edit Program">
                                    <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                                </button>

                                <form method="POST" action="{{ route('admin.pricing-programs.toggle', $program) }}">
                                    @csrf
                                    <button type="submit"
                                        class="h-8 px-2.5 rounded-[10px] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-xs font-medium transition {{ $program->is_active ? 'text-[#FF3B30]' : 'text-[#34C759]' }}"
                                        title="{{ $program->is_active ? 'Nonaktifkan Program' : 'Aktifkan Program' }}">
                                        <i data-lucide="{{ $program->is_active ? 'pause' : 'play' }}" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Batches Table / Cards -->
                    @if ($program->batches->isEmpty())
                        <div class="py-8 text-center text-xs text-black/40 dark:text-white/40">
                            Belum ada batch pada program ini. Klik "Tambah Batch" untuk menentukan kuota dan harga khusus.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-black/[0.04] dark:border-white/[0.06] text-black/50 dark:text-white/50 text-[11px] font-semibold">
                                        <th class="py-2.5 px-3">Batch #</th>
                                        <th class="py-2.5 px-3">Nama &amp; Pemicu</th>
                                        <th class="py-2.5 px-3">Kuota Pelanggan</th>
                                        <th class="py-2.5 px-3">Harga Standard</th>
                                        <th class="py-2.5 px-3">Harga Premium</th>
                                        <th class="py-2.5 px-3">Harga Prestige</th>
                                        <th class="py-2.5 px-3">Kenaikan Tahunan</th>
                                        <th class="py-2.5 px-3">Batch Berikutnya</th>
                                        <th class="py-2.5 px-3 text-right">Status &amp; Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                                    @foreach ($program->batches as $batch)
                                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition">
                                            <td class="py-3 px-3 font-bold text-black dark:text-white tabular-nums">
                                                Batch {{ $batch->batch_number }}
                                            </td>
                                            <td class="py-3 px-3">
                                                <div class="font-semibold text-black dark:text-white">{{ $batch->name }}</div>
                                                <div class="text-[10px] text-black/50 dark:text-white/50 mt-0.5">
                                                    Mode: <span class="capitalize">{{ str_replace('_', ' ', $batch->trigger_mode) }}</span>
                                                    @if($batch->trigger_mode === 'hybrid')
                                                        <span class="font-bold text-[#007AFF]">({{ $batch->hybrid_operator === 'both' ? 'Wajib Keduanya' : 'Salah Satu' }})</span>
                                                    @endif
                                                    @if($batch->ends_at)
                                                        • s/d {{ $batch->ends_at->format('d M Y') }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-3 px-3">
                                                <div class="tabular-nums font-bold text-black dark:text-white">
                                                    {{ $batch->quota_used }} / {{ $batch->quota_limit ?? '∞' }}
                                                </div>
                                                @if($batch->quota_limit)
                                                    <div class="w-24 bg-black/10 dark:bg-white/10 rounded-full h-1.5 mt-1 overflow-hidden">
                                                        @php $pct = min(100, round(($batch->quota_used / max(1, $batch->quota_limit)) * 100)); @endphp
                                                        <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-[#FF3B30]' : ($pct >= 80 ? 'bg-[#FF9500]' : 'bg-[#007AFF]') }}"
                                                            style="width: {{ $pct }}%"></div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 tabular-nums">
                                                @if($batch->standard_monthly_price !== null)
                                                    <div class="font-semibold text-black dark:text-white">Rp {{ number_format($batch->standard_monthly_price, 0, ',', '.') }}/bln</div>
                                                    <div class="text-[10px] text-black/50 dark:text-white/50">Rp {{ number_format($batch->standard_annual_price ?? 0, 0, ',', '.') }}/thn</div>
                                                @else
                                                    <span class="text-black/30 dark:text-white/30"> </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 tabular-nums">
                                                @if($batch->premium_monthly_price !== null)
                                                    <div class="font-semibold text-black dark:text-white">Rp {{ number_format($batch->premium_monthly_price, 0, ',', '.') }}/bln</div>
                                                    <div class="text-[10px] text-black/50 dark:text-white/50">Rp {{ number_format($batch->premium_annual_price ?? 0, 0, ',', '.') }}/thn</div>
                                                @else
                                                    <span class="text-black/30 dark:text-white/30"> </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 tabular-nums">
                                                @if($batch->prestige_monthly_price !== null)
                                                    <div class="font-semibold text-black dark:text-white">Rp {{ number_format($batch->prestige_monthly_price, 0, ',', '.') }}/bln</div>
                                                    <div class="text-[10px] text-black/50 dark:text-white/50">Rp {{ number_format($batch->prestige_annual_price ?? 0, 0, ',', '.') }}/thn</div>
                                                @else
                                                    <span class="text-black/30 dark:text-white/30"> </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3">
                                                <span class="inline-flex items-center gap-1 font-bold text-[#007AFF] dark:text-[#0A84FF] tabular-nums">
                                                    +{{ $batch->annual_increase_percentage }}%
                                                </span>
                                                <div class="text-[10px] text-black/50 dark:text-white/50">per thn</div>
                                            </td>
                                            <td class="py-3 px-3">
                                                @if ($batch->nextBatch)
                                                    <span class="text-xs font-semibold text-black dark:text-white">
                                                        Batch {{ $batch->nextBatch->batch_number }}
                                                    </span>
                                                @else
                                                    <span class="text-[11px] text-black/50 dark:text-white/50 italic">
                                                        Fallback: {{ str_replace('_', ' ', $batch->fallback_behavior) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <!-- Status Badge -->
                                                    <span class="px-2 py-0.5 rounded-[8px] text-[10px] font-bold uppercase tracking-wider shrink-0
                                                        {{ $batch->status === 'active' ? 'bg-[#34C759]/15 text-[#34C759]' : ($batch->status === 'completed' ? 'bg-[#007AFF]/15 text-[#007AFF]' : ($batch->status === 'expired' ? 'bg-[#8E8E93]/15 text-[#8E8E93]' : 'bg-[#FF9500]/15 text-[#FF9500]')) }}">
                                                        {{ $batch->status }}
                                                    </span>

                                                    @if(Auth::guard('admin')->user()?->hasPermission('admin.pricing_programs.manage'))
                                                        <button type="button" @click="openEditBatch({{ json_encode($batch) }})"
                                                            class="p-1 rounded-[6px] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] text-black/60 dark:text-white/60 transition"
                                                            title="Edit Konfigurasi Batch">
                                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                                        </button>

                                                        <button type="button" @click="openNextBatchLink({{ json_encode($batch) }})"
                                                            class="p-1 rounded-[6px] hover:bg-black/[0.05] dark:hover:bg-white/[0.08] text-black/60 dark:text-white/60 transition"
                                                            title="Tentukan Batch Berikutnya">
                                                            <i data-lucide="arrow-right-circle" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-12 text-center rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs space-y-3">
                    <i data-lucide="folder-plus" class="w-10 h-10 text-black/30 dark:text-white/30 mx-auto"></i>
                    <h3 class="text-base font-bold text-black dark:text-white">Belum Ada Program Early Adopter</h3>
                    <p class="text-xs text-black/60 dark:text-white/60 max-w-md mx-auto">
                        Mulai dengan membuat program pendaftaran berharga khusus (misal Early Adopter UMKM 2026) untuk memberikan promosi kuota terbatas dan harga terkunci.
                    </p>
                    @if(Auth::guard('admin')->user()?->hasPermission('admin.pricing_programs.manage'))
                        <button type="button" @click="showProgramModal = true; refreshIcons()"
                            class="h-9 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-semibold shadow-xs inline-flex items-center gap-1.5 transition">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Buat Program Baru</span>
                        </button>
                    @endif
                </div>
            @endforelse
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 2: GRANDFATHERED CUSTOMERS PER BATCH -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    @if ($tab === 'customers')
        <div class="space-y-4">
            <!-- Filter & Search Toolbar -->
            <div class="p-4 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <form method="GET" action="{{ route('admin.pricing-programs.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
                    <input type="hidden" name="tab" value="customers">

                    <div class="relative flex-1 min-w-[200px]">
                        <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3 top-2.5"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama usaha atau email..."
                            class="w-full pl-9 pr-3 py-1.5 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <select name="batch_id" onchange="this.form.submit()"
                        class="px-3 py-1.5 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-black dark:text-white focus:outline-hidden">
                        <option value="">Semua Batch</option>
                        @foreach ($allBatches as $b)
                            <option value="{{ $b->id }}" {{ $selectedBatchId === $b->id ? 'selected' : '' }}>
                                Batch {{ $b->batch_number }} - {{ $b->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="h-8 px-3 rounded-[10px] bg-[#007AFF] text-white text-xs font-semibold">
                        Filter
                    </button>
                    @if(request('search') || request('batch_id'))
                        <a href="{{ route('admin.pricing-programs.index', ['tab' => 'customers']) }}" class="h-8 px-2.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-xs flex items-center">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Customers Table -->
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-black/[0.04] dark:border-white/[0.06] bg-black/[0.02] dark:bg-white/[0.02] text-black/50 dark:text-white/50 text-[11px] font-semibold">
                                <th class="py-3 px-4">Nama Usaha</th>
                                <th class="py-3 px-3">Batch &amp; Program</th>
                                <th class="py-3 px-3">Paket Terkunci</th>
                                <th class="py-3 px-3">Siklus</th>
                                <th class="py-3 px-3">Harga Disepakati</th>
                                <th class="py-3 px-3">Status Hak Harga</th>
                                <th class="py-3 px-3">Kenaikan Thn.</th>
                                <th class="py-3 px-4">Eskalasi Berikutnya</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @forelse ($customers as $sub)
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition">
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-black dark:text-white">{{ $sub->business?->name ?? 'Usaha #' . $sub->business_id }}</div>
                                        <div class="text-[10px] text-black/50 dark:text-white/50">{{ $sub->business?->email }}</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="font-semibold text-black dark:text-white">Batch {{ $sub->batch?->batch_number ?? '-' }}</div>
                                        <div class="text-[10px] text-black/50 dark:text-white/50">{{ $sub->batch?->name }}</div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold uppercase tracking-wider
                                            {{ $sub->locked_tier === 'prestige' ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400' : ($sub->locked_tier === 'premium' ? 'bg-purple-500/15 text-purple-600 dark:text-purple-400' : 'bg-blue-500/15 text-blue-600 dark:text-blue-400') }}">
                                            {{ $sub->locked_tier ?? $sub->getTier() }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 capitalize text-black/70 dark:text-white/70">
                                        {{ $sub->locked_cycle ?? 'monthly' }}
                                    </td>
                                    <td class="py-3 px-3 tabular-nums font-bold text-black dark:text-white">
                                        Rp {{ number_format((float) $sub->price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3">
                                        @if ($sub->is_price_lock_eligible)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#34C759]">
                                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                                <span>Terkunci (Eligible)</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#FF3B30]" title="{{ $sub->price_lock_forfeited_reason }}">
                                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                                <span>Hangus (Forfeited)</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 tabular-nums font-semibold text-[#007AFF] dark:text-[#0A84FF]">
                                        +{{ $sub->annual_increase_percentage }}%
                                    </td>
                                    <td class="py-3 px-4 tabular-nums text-black/70 dark:text-white/70">
                                        {{ $sub->next_price_increase_at ? $sub->next_price_increase_at->format('d M Y') : ' ' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-xs text-black/40 dark:text-white/40">
                                        Belum ada pelanggan dengan harga Early Adopter terkunci pada filter ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($customers->hasPages())
                    <div class="p-4 border-t border-black/[0.04] dark:border-white/[0.06]">
                        {{ $customers->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 3: ANNUAL ESCALATION SCHEDULE & SIMULATOR -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    @if ($tab === 'schedule')
        <div class="space-y-6">
            <!-- Action Cockpit -->
            <div class="p-5 rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-black dark:text-white">Eksekusi Eskalasi Harga Tahunan Otomatis</h3>
                    <p class="text-xs text-black/60 dark:text-white/60">
                        Scheduler berjalan harian pada pukul 01:00 WIB. Anda juga dapat menjalankan simulasi atau mengeksekusi manual untuk pelanggan yang sudah jatuh tempo hari ini.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.pricing-programs.simulate-escalation') }}"
                        class="h-9 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-xs font-semibold flex items-center gap-1.5 transition">
                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        <span>Simulasi Hari Ini</span>
                    </a>

                    @if(Auth::guard('admin')->user()?->hasPermission('admin.pricing_programs.manage'))
                        <form method="POST" action="{{ route('admin.pricing-programs.run-escalation') }}"
                            onsubmit="return confirm('Apakah Anda yakin ingin mengeksekusi eskalasi harga tahunan untuk pelanggan yang jatuh tempo hari ini?');">
                            @csrf
                            <button type="submit"
                                class="h-9 px-3.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-semibold shadow-xs flex items-center gap-1.5 transition active:scale-[0.98]">
                                <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                <span>Jalankan Sekarang ({{ $dueEscalationsCount }})</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Upcoming Escalations List (Next 90 Days) -->
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden">
                <div class="p-4 border-b border-black/[0.04] dark:border-white/[0.06] flex items-center justify-between">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                        Timeline Eskalasi 90 Hari Mendatang
                    </h4>
                    <span class="text-xs text-black/50 dark:text-white/50">
                        Total {{ $upcomingEscalations->count() }} pelanggan
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-black/[0.04] dark:border-white/[0.06] bg-black/[0.02] dark:bg-white/[0.02] text-black/50 dark:text-white/50 text-[11px] font-semibold">
                                <th class="py-3 px-4">Tanggal Efektif</th>
                                <th class="py-3 px-3">Nama Usaha</th>
                                <th class="py-3 px-3">Batch Asal</th>
                                <th class="py-3 px-3">Harga Sekarang</th>
                                <th class="py-3 px-3">Kenaikan (%)</th>
                                <th class="py-3 px-3">Proyeksi Harga Baru</th>
                                <th class="py-3 px-4">Status Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @forelse ($upcomingEscalations as $sub)
                                @php
                                    $pct = min(10.0, (float) $sub->annual_increase_percentage);
                                    $rawNext = (float) $sub->price * (1 + ($pct / 100));
                                    $projectedPrice = $sub->batch ? $sub->batch->calculateRoundedPrice($rawNext) : round($rawNext, 2);
                                    $isTodayOrPast = $sub->next_price_increase_at?->isPast() || $sub->next_price_increase_at?->isToday();
                                @endphp
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition {{ $isTodayOrPast ? 'bg-amber-50/50 dark:bg-amber-950/20' : '' }}">
                                    <td class="py-3 px-4 tabular-nums font-bold text-black dark:text-white">
                                        {{ $sub->next_price_increase_at?->format('d M Y') }}
                                    </td>
                                    <td class="py-3 px-3 font-semibold text-black dark:text-white">
                                        {{ $sub->business?->name ?? 'Usaha #' . $sub->business_id }}
                                    </td>
                                    <td class="py-3 px-3 text-black/70 dark:text-white/70">
                                        Batch {{ $sub->batch?->batch_number ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3 tabular-nums font-medium text-black dark:text-white">
                                        Rp {{ number_format((float) $sub->price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 tabular-nums font-bold text-[#007AFF] dark:text-[#0A84FF]">
                                        +{{ $pct }}%
                                    </td>
                                    <td class="py-3 px-3 tabular-nums font-bold text-[#34C759]">
                                        Rp {{ number_format($projectedPrice, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($isTodayOrPast)
                                            <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                                Jatuh Tempo Hari Ini
                                            </span>
                                        @else
                                            <span class="text-[11px] text-black/50 dark:text-white/50">
                                                Dalam {{ Carbon::today()->diffInDays($sub->next_price_increase_at) }} hari
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-xs text-black/40 dark:text-white/40">
                                        Tidak ada pelanggan yang dijadwalkan naik harga dalam 90 hari ke depan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- TAB 4: AUDIT LOG & PRICE HISTORIES -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    @if ($tab === 'logs')
        <div class="space-y-4">
            <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-black/[0.04] dark:border-white/[0.06] bg-black/[0.02] dark:bg-white/[0.02] text-black/50 dark:text-white/50 text-[11px] font-semibold">
                                <th class="py-3 px-4">Waktu &amp; Tanggal</th>
                                <th class="py-3 px-3">Nama Usaha</th>
                                <th class="py-3 px-3">Peristiwa (Event)</th>
                                <th class="py-3 px-3">Harga Sebelumnya</th>
                                <th class="py-3 px-3">Harga Baru</th>
                                <th class="py-3 px-3">Perubahan (%)</th>
                                <th class="py-3 px-3">Eksekutor</th>
                                <th class="py-3 px-4">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                            @forelse ($priceHistories as $log)
                                <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition">
                                    <td class="py-3 px-4 tabular-nums text-black/70 dark:text-white/70">
                                        {{ $log->created_at?->format('d M Y H:i') }}
                                    </td>
                                    <td class="py-3 px-3 font-semibold text-black dark:text-white">
                                        {{ $log->business?->name ?? 'Usaha #' . $log->business_id }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded-[6px] text-[10px] font-bold uppercase tracking-wider
                                            {{ $log->event_type === 'annual_escalation' ? 'bg-[#007AFF]/15 text-[#007AFF]' : ($log->event_type === 'initial_lock' ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF3B30]/15 text-[#FF3B30]') }}">
                                            {{ str_replace('_', ' ', $log->event_type) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 tabular-nums text-black/60 dark:text-white/60">
                                        Rp {{ number_format((float) $log->previous_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 tabular-nums font-bold text-black dark:text-white">
                                        Rp {{ number_format((float) $log->new_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 tabular-nums font-bold text-[#007AFF] dark:text-[#0A84FF]">
                                        {{ $log->increase_percentage > 0 ? '+' . $log->increase_percentage . '%' : ' ' }}
                                    </td>
                                    <td class="py-3 px-3 capitalize text-black/70 dark:text-white/70">
                                        {{ str_replace('_', ' ', $log->performed_by_type) }}
                                    </td>
                                    <td class="py-3 px-4 text-black/60 dark:text-white/60 text-[11px]">
                                        {{ $log->notes }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-xs text-black/40 dark:text-white/40">
                                        Belum ada riwayat perubahan harga khusus tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($priceHistories->hasPages())
                    <div class="p-4 border-t border-black/[0.04] dark:border-white/[0.06]">
                        {{ $priceHistories->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════════════════════ -->
    <!-- MODALS (Apple HIG Bento Sheets)                                           -->
    <!-- ══════════════════════════════════════════════════════════════════════════ -->

    <!-- Modal 1: Buat Program Baru -->
    <div x-show="showProgramModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showProgramModal = false" class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <h3 class="text-base font-bold text-black dark:text-white">Buat Program Early Adopter Baru</h3>
                <button type="button" @click="showProgramModal = false" class="p-1 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/10 text-black/50 dark:text-white/50">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.pricing-programs.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">Nama Program</label>
                    <input type="text" name="name" required placeholder="Contoh: Program Early Adopter UMKM Juara 2026"
                        class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">Kode Unik Program (Opsional)</label>
                    <input type="text" name="code" placeholder="early-adopter-2026"
                        class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">Deskripsi &amp; Kebijakan Program</label>
                    <textarea name="description" rows="3" placeholder="Jelaskan tujuan program, segmen penerima manfaat, dan aturan umum..."
                        class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="prog_active" value="1" checked
                        class="w-4 h-4 rounded text-[#007AFF] focus:ring-0">
                    <label for="prog_active" class="text-xs text-black/80 dark:text-white/80">Aktifkan program ini sekarang</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showProgramModal = false" class="px-4 py-2 rounded-[10px] text-xs font-semibold hover:bg-black/5 dark:hover:bg-white/10 text-black/70 dark:text-white/70">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-semibold shadow-xs">
                        Simpan Program
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Edit Program -->
    <div x-show="showEditProgramModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showEditProgramModal = false" class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <h3 class="text-base font-bold text-black dark:text-white">Edit Program</h3>
                <button type="button" @click="showEditProgramModal = false" class="p-1 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/10 text-black/50 dark:text-white/50">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" :action="'{{ url('admin/pricing-programs') }}/' + currentProgram.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">Nama Program</label>
                    <input type="text" name="name" x-model="currentProgram.name" required
                        class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">Deskripsi</label>
                    <textarea name="description" rows="3" x-model="currentProgram.description"
                        class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" id="edit_prog_active" value="1" :checked="currentProgram.is_active"
                        class="w-4 h-4 rounded text-[#007AFF] focus:ring-0">
                    <label for="edit_prog_active" class="text-xs text-black/80 dark:text-white/80">Program Aktif</label>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showEditProgramModal = false" class="px-4 py-2 rounded-[10px] text-xs font-semibold hover:bg-black/5 dark:hover:bg-white/10 text-black/70 dark:text-white/70">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-semibold shadow-xs">
                        Perbarui Program
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Buat Batch Baru -->
    <div x-show="showBatchModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showBatchModal = false" class="w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-5 my-8">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div>
                    <h3 class="text-base font-bold text-black dark:text-white">Tambah Batch Kuota &amp; Harga Khusus</h3>
                    <p class="text-xs text-black/50 dark:text-white/50">Admin mengonfigurasi nominal harga, kuota, dan batasan kenaikan tahunan (maks. 10%).</p>
                </div>
                <button type="button" @click="showBatchModal = false" class="p-1 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/10 text-black/50 dark:text-white/50">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" :action="'{{ url('admin/pricing-programs') }}/' + selectedProgramIdForBatch + '/batches'" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Nama Batch</label>
                        <input type="text" name="name" x-model="currentBatch.name" required placeholder="Contoh: Batch 1 - 1000 Pelanggan Pertama"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Status Awal</label>
                        <select name="status" x-model="currentBatch.status"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden">
                            <option value="draft">Draft (Disiapkan)</option>
                            <option value="active">Active (Langsung Berjalan)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Mode Pemicu Peralihan</label>
                        <select name="trigger_mode" x-model="currentBatch.trigger_mode"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden">
                            <option value="quota_based">Berdasarkan Kuota Saja</option>
                            <option value="time_based">Berdasarkan Waktu Saja</option>
                            <option value="hybrid">Hybrid (Kuota / Waktu)</option>
                        </select>
                    </div>

                    <div x-show="currentBatch.trigger_mode === 'hybrid'">
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Operator Logika Hybrid</label>
                        <select name="hybrid_operator" x-model="currentBatch.hybrid_operator"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden">
                            <option value="either">Salah Satu (ATAU) - Default</option>
                            <option value="both">Keduanya Wajib (DAN)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Kuota Maksimal Pelanggan</label>
                        <input type="number" name="quota_limit" x-model="currentBatch.quota_limit" placeholder="Contoh: 1000" min="1"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Fallback Bila Batch Selesai</label>
                        <select name="fallback_behavior" x-model="currentBatch.fallback_behavior"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden">
                            <option value="base_pricing">Kembali ke Harga Normal</option>
                            <option value="next_batch">Otomatis Aktifkan Batch Selanjutnya</option>
                            <option value="stay_active">Tetap Aktif (Pemberitahuan)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Tanggal Mulai (Opsional)</label>
                        <input type="datetime-local" name="starts_at" x-model="currentBatch.starts_at"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Tanggal Berakhir (Opsional)</label>
                        <input type="datetime-local" name="ends_at" x-model="currentBatch.ends_at"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden">
                    </div>
                </div>

                <!-- Special Pricing Matrix per Tier -->
                <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-3">
                    <div class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Harga Khusus per Paket (Nominal Ditentukan Admin)</span>
                    </div>

                    <!-- Standard -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Standard Bulanan (Rp)</label>
                            <input type="number" name="standard_monthly_price" x-model="currentBatch.standard_monthly_price" placeholder="29000"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Standard Tahunan (Rp)</label>
                            <input type="number" name="standard_annual_price" x-model="currentBatch.standard_annual_price" placeholder="290000"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                    </div>

                    <!-- Premium -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Premium Bulanan (Rp)</label>
                            <input type="number" name="premium_monthly_price" x-model="currentBatch.premium_monthly_price" placeholder="69000"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Premium Tahunan (Rp)</label>
                            <input type="number" name="premium_annual_price" x-model="currentBatch.premium_annual_price" placeholder="690000"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                    </div>

                    <!-- Prestige -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Prestige Bulanan (Rp)</label>
                            <input type="number" name="prestige_monthly_price" x-model="currentBatch.prestige_monthly_price" placeholder="159000"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Prestige Tahunan (Rp)</label>
                            <input type="number" name="prestige_annual_price" x-model="currentBatch.prestige_annual_price" placeholder="1590000"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Annual Escalation Rules (Capped at 10.00%) -->
                <div class="p-3.5 rounded-[16px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/50 dark:border-blue-900/40 space-y-3">
                    <div class="text-xs font-bold text-blue-950 dark:text-blue-200 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="percent" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>Kebijakan Kenaikan Harga Tahunan (Maksimal 10.00%)</span>
                        </span>
                        <span class="text-[10px] font-bold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-full">
                            Anti-Overescalation Guard
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Persentase Kenaikan (%)</label>
                            <input type="number" step="0.01" min="0" max="10" name="annual_increase_percentage"
                                x-model="currentBatch.annual_increase_percentage"
                                @change="currentBatch.annual_increase_percentage = validateMaxIncrease($event.target.value)"
                                required
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white font-bold">
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Basis Ulang Tahun</label>
                            <select name="annual_increase_basis" x-model="currentBatch.annual_increase_basis"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                                <option value="subscription_anniversary">Ulang Tahun Langganan Tenant</option>
                                <option value="calendar_anniversary">Awal Tahun Kalender (1 Jan)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Aturan Pembulatan</label>
                            <select name="rounding_mode" x-model="currentBatch.rounding_mode"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                                <option value="none">Tanpa Pembulatan</option>
                                <option value="nearest_1000">Ke Rp 1.000 Terdekat</option>
                                <option value="nearest_100">Ke Rp 100 Terdekat</option>
                                <option value="ceiling_1000">Bulatkan ke Atas (1.000)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Resubscribe / Cancellation Policy -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Ketentuan Berlangganan Kembali</label>
                        <select name="resubscribe_policy" x-model="currentBatch.resubscribe_policy"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                            <option value="grace_period">Masa Tenggang Toleransi</option>
                            <option value="immediate_forfeit">Langsung Hangus (Harga Normal)</option>
                            <option value="always_retain">Selalu Pertahankan Harga</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Masa Toleransi (Hari)</label>
                        <input type="number" name="resubscribe_grace_days" x-model="currentBatch.resubscribe_grace_days" min="0" max="365"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Kebijakan Upgrade/Downgrade</label>
                        <select name="upgrade_downgrade_policy" x-model="currentBatch.upgrade_downgrade_policy"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                            <option value="inherit_active_batch">Ikuti Harga Batch Aktif</option>
                            <option value="forfeit_to_normal">Beralih ke Harga Normal</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showBatchModal = false" class="px-4 py-2 rounded-[10px] text-xs font-semibold hover:bg-black/5 dark:hover:bg-white/10 text-black/70 dark:text-white/70">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-semibold shadow-xs">
                        Simpan Batch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 4: Edit Batch -->
    <div x-show="showEditBatchModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showEditBatchModal = false" class="w-full max-w-2xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-5 my-8">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <h3 class="text-base font-bold text-black dark:text-white">Edit Konfigurasi Batch #<span x-text="currentBatch.batch_number"></span></h3>
                <button type="button" @click="showEditBatchModal = false" class="p-1 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/10 text-black/50 dark:text-white/50">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" :action="'{{ url('admin/pricing-programs/batches') }}/' + currentBatch.id" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Nama Batch</label>
                        <input type="text" name="name" x-model="currentBatch.name" required
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Status Batch</label>
                        <select name="status" x-model="currentBatch.status"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="expired">Expired</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Mode Pemicu</label>
                        <select name="trigger_mode" x-model="currentBatch.trigger_mode"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                            <option value="quota_based">Berdasarkan Kuota</option>
                            <option value="time_based">Berdasarkan Waktu</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <div x-show="currentBatch.trigger_mode === 'hybrid'">
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Operator Hybrid</label>
                        <select name="hybrid_operator" x-model="currentBatch.hybrid_operator"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                            <option value="either">Salah Satu (ATAU) - Default</option>
                            <option value="both">Keduanya Wajib (DAN)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Kuota Limit</label>
                        <input type="number" name="quota_limit" x-model="currentBatch.quota_limit"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Fallback Behavior</label>
                        <select name="fallback_behavior" x-model="currentBatch.fallback_behavior"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                            <option value="base_pricing">Harga Normal</option>
                            <option value="next_batch">Batch Berikutnya</option>
                            <option value="stay_active">Tetap Aktif</option>
                        </select>
                    </div>
                </div>

                <!-- Special Pricing Matrix per Tier -->
                <div class="p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-3">
                    <div class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Harga Khusus per Paket</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Standard Bulanan (Rp)</label>
                            <input type="number" name="standard_monthly_price" x-model="currentBatch.standard_monthly_price"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Standard Tahunan (Rp)</label>
                            <input type="number" name="standard_annual_price" x-model="currentBatch.standard_annual_price"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Premium Bulanan (Rp)</label>
                            <input type="number" name="premium_monthly_price" x-model="currentBatch.premium_monthly_price"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Premium Tahunan (Rp)</label>
                            <input type="number" name="premium_annual_price" x-model="currentBatch.premium_annual_price"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Prestige Bulanan (Rp)</label>
                            <input type="number" name="prestige_monthly_price" x-model="currentBatch.prestige_monthly_price"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Prestige Tahunan (Rp)</label>
                            <input type="number" name="prestige_annual_price" x-model="currentBatch.prestige_annual_price"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- Annual Escalation Rules -->
                <div class="p-3.5 rounded-[16px] bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/50 dark:border-blue-900/40 space-y-3">
                    <div class="text-xs font-bold text-blue-950 dark:text-blue-200 flex items-center justify-between">
                        <span>Kebijakan Kenaikan Harga Tahunan (Maks. 10.00%)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Persentase (%)</label>
                            <input type="number" step="0.01" min="0" max="10" name="annual_increase_percentage"
                                x-model="currentBatch.annual_increase_percentage"
                                @change="currentBatch.annual_increase_percentage = validateMaxIncrease($event.target.value)"
                                required
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white font-bold">
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Basis Kenaikan</label>
                            <select name="annual_increase_basis" x-model="currentBatch.annual_increase_basis"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                                <option value="subscription_anniversary">Ulang Tahun Langganan Tenant</option>
                                <option value="calendar_anniversary">Awal Tahun Kalender</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Pembulatan</label>
                            <select name="rounding_mode" x-model="currentBatch.rounding_mode"
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white">
                                <option value="none">Tanpa Pembulatan</option>
                                <option value="nearest_1000">Ke Rp 1.000 Terdekat</option>
                                <option value="nearest_100">Ke Rp 100 Terdekat</option>
                                <option value="ceiling_1000">Bulatkan ke Atas (1.000)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Ketentuan Berhenti/Lapse</label>
                        <select name="resubscribe_policy" x-model="currentBatch.resubscribe_policy"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                            <option value="grace_period">Masa Tenggang Toleransi</option>
                            <option value="immediate_forfeit">Langsung Hangus</option>
                            <option value="always_retain">Selalu Pertahankan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Toleransi (Hari)</label>
                        <input type="number" name="resubscribe_grace_days" x-model="currentBatch.resubscribe_grace_days" min="0" max="365"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black dark:text-white mb-1">Upgrade/Downgrade</label>
                        <select name="upgrade_downgrade_policy" x-model="currentBatch.upgrade_downgrade_policy"
                            class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white">
                            <option value="inherit_active_batch">Ikuti Batch Aktif</option>
                            <option value="forfeit_to_normal">Beralih ke Normal</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showEditBatchModal = false" class="px-4 py-2 rounded-[10px] text-xs font-semibold hover:bg-black/5 dark:hover:bg-white/10 text-black/70 dark:text-white/70">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-semibold shadow-xs">
                        Perbarui Batch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 5: Tautkan Batch Berikutnya -->
    <div x-show="showNextBatchModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showNextBatchModal = false" class="w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-5">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <h3 class="text-base font-bold text-black dark:text-white">Tautkan Batch Berikutnya</h3>
                <button type="button" @click="showNextBatchModal = false" class="p-1 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/10 text-black/50 dark:text-white/50">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" :action="'{{ url('admin/pricing-programs/batches') }}/' + currentBatch.id + '/next'" class="space-y-4">
                @csrf
                <p class="text-xs text-black/60 dark:text-white/60">
                    Pilih batch yang akan diaktifkan secara otomatis begitu kuota atau batas waktu dari <strong>Batch #<span x-text="currentBatch.batch_number"></span></strong> tercapai.
                </p>

                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">Batch Selanjutnya</label>
                    <select name="next_batch_id" x-model="currentBatch.next_batch_id"
                        class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden">
                        <option value="none">Tidak Ada (Gunakan Fallback Harga Normal)</option>
                        @foreach ($allBatches as $cand)
                            <option value="{{ $cand->id }}">
                                Batch {{ $cand->batch_number }}   {{ $cand->name }} ({{ $cand->status }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showNextBatchModal = false" class="px-4 py-2 rounded-[10px] text-xs font-semibold hover:bg-black/5 dark:hover:bg-white/10 text-black/70 dark:text-white/70">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-xs font-semibold shadow-xs">
                        Simpan Tautan Batch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Ubah Harga Normal Acuan & Badge Promo -->
    <div x-show="showEditBasePricesModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-black/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="showEditBasePricesModal = false" class="w-full max-w-xl bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-2xl p-6 space-y-5 my-8">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-[8px] bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-base font-bold text-black dark:text-white">Ubah Harga Normal &amp; Promo Tahunan</h3>
                </div>
                <button type="button" @click="showEditBasePricesModal = false" class="p-1 rounded-[8px] hover:bg-black/5 dark:hover:bg-white/10 text-black/50 dark:text-white/50 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.pricing-programs.base-prices.update') }}" class="space-y-4">
                @csrf
                <p class="text-xs text-black/60 dark:text-white/60">
                    Perubahan harga di sini akan langsung menjadi harga resmi di seluruh sistem tanpa mengubah kode program. Pelanggan lama yang sudah mengunci harga tetap terlindungi.
                </p>

                <!-- Standard -->
                <div class="p-3.5 rounded-[16px] bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-200/50 dark:border-emerald-800/40 space-y-2">
                    <div class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Paket Standard UMKM</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Harga Bulanan (Rp)</label>
                            <input type="number" name="standard_monthly_price" value="{{ (int)$basePrices['standard_monthly'] }}" min="0" required
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Harga Tahunan (Rp)</label>
                            <input type="number" name="standard_annual_price" value="{{ (int)$basePrices['standard_annual'] }}" min="0" required
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white font-mono">
                        </div>
                    </div>
                </div>

                <!-- Premium -->
                <div class="p-3.5 rounded-[16px] bg-indigo-50/40 dark:bg-indigo-950/20 border border-indigo-200/50 dark:border-indigo-800/40 space-y-2">
                    <div class="text-xs font-bold text-indigo-800 dark:text-indigo-300">Paket Premium Scale-Up</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Harga Bulanan (Rp)</label>
                            <input type="number" name="premium_monthly_price" value="{{ (int)$basePrices['premium_monthly'] }}" min="0" required
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Harga Tahunan (Rp)</label>
                            <input type="number" name="premium_annual_price" value="{{ (int)$basePrices['premium_annual'] }}" min="0" required
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white font-mono">
                        </div>
                    </div>
                </div>

                <!-- Prestige -->
                <div class="p-3.5 rounded-[16px] bg-purple-50/40 dark:bg-purple-950/20 border border-purple-200/50 dark:border-purple-800/40 space-y-2">
                    <div class="text-xs font-bold text-purple-800 dark:text-purple-300">Paket Prestige Enterprise</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Harga Bulanan (Rp)</label>
                            <input type="number" name="prestige_monthly_price" value="{{ (int)$basePrices['prestige_monthly'] }}" min="0" required
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-[11px] font-medium text-black/70 dark:text-white/70 mb-1">Harga Tahunan (Rp)</label>
                            <input type="number" name="prestige_annual_price" value="{{ (int)$basePrices['prestige_annual'] }}" min="0" required
                                class="w-full px-3 py-1.5 text-xs rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-black dark:text-white font-mono">
                        </div>
                    </div>
                </div>

                <!-- Annual Discount Badge -->
                <div>
                    <label class="block text-xs font-semibold text-black dark:text-white mb-1">Label / Teks Badge Promo Tahunan</label>
                    <input type="text" name="annual_discount_badge" value="{{ $basePrices['annual_discount_badge'] }}" required maxlength="50"
                        placeholder="Contoh: Hemat 2 Bulan"
                        class="w-full px-3 py-2 text-xs rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden">
                    <p class="text-[11px] text-slate-400 mt-1">Ditampilkan pada switcher bulanan/tahunan di /pricing dan /billing/limits.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <button type="button" @click="showEditBasePricesModal = false" class="px-4 py-2 rounded-[10px] text-xs font-semibold hover:bg-black/5 dark:hover:bg-white/10 text-black/70 dark:text-white/70 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-[10px] bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-xs cursor-pointer">
                        Simpan Perubahan Harga Normal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
