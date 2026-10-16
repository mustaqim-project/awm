@extends('layouts.app', [
    'title' => 'Jejak Audit & Anti-Fraud - Cooca',
    'headerTitle' => 'Jejak Audit & Forensik Keamanan',
    'headerSubtitle' => 'Deteksi kecurangan internal, pantau mutasi data sensitif, dan rekam jejak digital bisnis.'
])

@section('content')
<div class="max-w-[1400px] mx-auto space-y-5 pb-28 lg:pb-12" x-data="auditLogExplorer()">

    <!-- Top Bento Header -->
    <header class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-[14px] bg-[#FF3B30]/10 text-[#FF3B30] dark:bg-[#FF453A]/15 dark:text-[#FF453A] flex items-center justify-center shrink-0">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <div>
                <nav class="flex items-center gap-1.5 text-[11.5px] text-black/45 dark:text-white/45 mb-0.5" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#007AFF] transition-colors">Dashboard</a>
                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                    <span>Pengaturan</span>
                    <i data-lucide="chevron-right" class="w-3 h-3 opacity-40"></i>
                    <span class="text-black/75 dark:text-white/75 font-medium">Jejak Audit</span>
                </nav>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold text-black dark:text-white tracking-tight">
                        Jejak Audit &amp; Anti-Fraud
                    </h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <i data-lucide="lock" class="w-3 h-3"></i>
                        <span>Append-Only</span>
                    </span>
                </div>
                <p class="text-[13px] text-black/55 dark:text-white/55 mt-0.5">
                    Rekam jejak forensik digital perubahan data penting bisnis yang tidak dapat dimanipulasi atau dihapus.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('settings.audit-logs.export', request()->query()) }}"
               class="min-h-[40px] px-4 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] text-black/80 dark:text-white/80 font-semibold text-[13px] flex items-center gap-2 active:scale-95 transition-all border border-black/5 dark:border-white/10">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Ekspor CSV</span>
            </a>
        </div>
    </header>

    <!-- 3-Column Bento Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4">
        <!-- Metric 1: Total Logs -->
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-[13px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/15 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                <i data-lucide="activity" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[12px] font-medium text-black/50 dark:text-white/50">
                    Aktivitas Tercatat (30 Hari)
                </div>
                <div class="text-2xl font-bold text-black dark:text-white tracking-tight mt-0.5">
                    {{ number_format($totalLogsCount, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- Metric 2: High Risk -->
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-[13px] bg-[#FF3B30]/10 text-[#FF3B30] dark:bg-[#FF453A]/15 dark:text-[#FF453A] flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[12px] font-medium text-black/50 dark:text-white/50">
                    Aktivitas Risiko Tinggi
                </div>
                <div class="text-2xl font-bold text-[#FF3B30] dark:text-[#FF453A] tracking-tight mt-0.5 flex items-center gap-2">
                    <span>{{ number_format($highRiskCount, 0, ',', '.') }}</span>
                    @if($highRiskCount > 0)
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 uppercase tracking-wider">
                        Perhatian
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Metric 3: WA Alerts Sent -->
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 p-4 sm:p-5 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-[13px] bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="smartphone" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[12px] font-medium text-black/50 dark:text-white/50">
                    Peringatan WA Terkirim
                </div>
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 tracking-tight mt-0.5">
                    {{ number_format($alertSentCount, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Container with Integrated Streamlined Toolbar -->
    <div class="rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
        
        <!-- Filter & Search Toolbar -->
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 space-y-3.5">
            <form method="GET" action="{{ route('settings.audit-logs.index') }}" class="space-y-3">
                @if(!empty($filters['risk_level']))
                <input type="hidden" name="risk_level" value="{{ $filters['risk_level'] }}">
                @endif

                <!-- Primary Toolbar Row -->
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                    
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                               placeholder="Cari alasan, modul, pelaku, atau IP..."
                               class="w-full min-h-[40px] pl-10 pr-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF] transition-all">
                    </div>

                    <!-- Right Controls: Risk Segmented Control + Advanced Toggle -->
                    <div class="flex items-center gap-2 flex-wrap">
                        
                        <!-- Risk Filter Segmented Control -->
                        <div class="inline-flex items-center p-1 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/5 dark:border-white/10 text-[12px] font-semibold">
                            <a href="{{ route('settings.audit-logs.index', array_merge(request()->except('risk_level', 'page'), [])) }}"
                               class="px-3 py-1.5 rounded-[9px] transition-all {{ empty($filters['risk_level']) ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                                Semua
                            </a>
                            <a href="{{ route('settings.audit-logs.index', array_merge(request()->except('page'), ['risk_level' => 'high'])) }}"
                               class="px-3 py-1.5 rounded-[9px] transition-all flex items-center gap-1 {{ ($filters['risk_level'] ?? '') === 'high' ? 'bg-[#FF3B30] text-white shadow-sm font-bold' : 'text-red-600 dark:text-red-400 hover:bg-red-500/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span>Tinggi</span>
                            </a>
                            <a href="{{ route('settings.audit-logs.index', array_merge(request()->except('page'), ['risk_level' => 'medium'])) }}"
                               class="px-3 py-1.5 rounded-[9px] transition-all flex items-center gap-1 {{ ($filters['risk_level'] ?? '') === 'medium' ? 'bg-amber-500 text-white shadow-sm font-bold' : 'text-amber-600 dark:text-amber-400 hover:bg-amber-500/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span>Sedang</span>
                            </a>
                            <a href="{{ route('settings.audit-logs.index', array_merge(request()->except('page'), ['risk_level' => 'low'])) }}"
                               class="px-3 py-1.5 rounded-[9px] transition-all flex items-center gap-1 {{ ($filters['risk_level'] ?? '') === 'low' ? 'bg-emerald-600 text-white shadow-sm font-bold' : 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/10' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                <span>Rendah</span>
                            </a>
                        </div>

                        <!-- Toggle Advanced Filter Button -->
                        <button type="button" @click="advancedOpen = !advancedOpen"
                                class="min-h-[38px] px-3.5 rounded-[12px] border text-[12.5px] font-semibold inline-flex items-center gap-1.5 transition-all active:scale-95"
                                :class="advancedOpen || {{ (!empty($filters['module']) || !empty($filters['user_id']) || !empty($filters['start_date']) || !empty($filters['end_date'])) ? 'true' : 'false' }} ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-[#007AFF]' : 'bg-black/[0.03] dark:bg-white/[0.05] border-black/10 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/10'">
                            <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                            <span>Filter</span>
                            @php
                                $advancedCount = count(array_filter([$filters['module'] ?? null, $filters['user_id'] ?? null, $filters['start_date'] ?? null, $filters['end_date'] ?? null]));
                            @endphp
                            @if($advancedCount > 0)
                            <span class="w-4 h-4 rounded-full bg-[#007AFF] text-white text-[10px] font-bold flex items-center justify-center ml-0.5">
                                {{ $advancedCount }}
                            </span>
                            @endif
                        </button>

                        <!-- Reset Button -->
                        @if(!empty(array_filter($filters)))
                        <a href="{{ route('settings.audit-logs.index') }}"
                           title="Reset Semua Filter"
                           class="min-h-[38px] px-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white text-[12.5px] font-semibold inline-flex items-center gap-1 transition-all">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                            <span class="hidden sm:inline">Reset</span>
                        </a>
                        @endif

                        <!-- Submit Search Button -->
                        <button type="submit" class="min-h-[38px] px-4 rounded-[12px] bg-[#007AFF] text-white text-[12.5px] font-semibold hover:bg-[#007AFF]/90 active:scale-95 transition-all inline-flex items-center gap-1.5 shrink-0">
                            <span>Terapkan</span>
                        </button>
                    </div>
                </div>

                <!-- Secondary / Advanced Filters Drawer -->
                <div x-show="advancedOpen"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="pt-3 border-t border-black/5 dark:border-white/10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    
                    <!-- Kategori Modul -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-black/55 dark:text-white/55 mb-1">
                            Kategori Modul
                        </label>
                        <select name="module" class="w-full min-h-[38px] px-3 rounded-[11px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">Semua Modul</option>
                            <option value="pos" {{ ($filters['module'] ?? '') === 'pos' ? 'selected' : '' }}>Kasir POS (Void & Diskon)</option>
                            <option value="supplier" {{ ($filters['module'] ?? '') === 'supplier' ? 'selected' : '' }}>Pemasok / Rekening Bank</option>
                            <option value="finance" {{ ($filters['module'] ?? '') === 'finance' ? 'selected' : '' }}>Keuangan & Jurnal</option>
                            <option value="inventory" {{ ($filters['module'] ?? '') === 'inventory' ? 'selected' : '' }}>Stok & Produk</option>
                            <option value="users" {{ ($filters['module'] ?? '') === 'users' ? 'selected' : '' }}>Pengguna & Role</option>
                        </select>
                    </div>

                    <!-- Pelaku / Staf -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-black/55 dark:text-white/55 mb-1">
                            Pelaku (User)
                        </label>
                        <select name="user_id" class="w-full min-h-[38px] px-3 rounded-[11px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="">Semua Staf / Pengguna</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') === $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-black/55 dark:text-white/55 mb-1">
                            Dari Tanggal
                        </label>
                        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                               class="w-full min-h-[38px] px-3 rounded-[11px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <!-- Tanggal Selesai -->
                    <div>
                        <label class="block text-[11.5px] font-semibold text-black/55 dark:text-white/55 mb-1">
                            Sampai Tanggal
                        </label>
                        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                               class="w-full min-h-[38px] px-3 rounded-[11px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[12.5px] text-black dark:text-white font-medium focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>
            </form>
        </div>

        <!-- Desktop & Tablet Streamlined Data Table (>= 640px) -->
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] text-[11.5px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                        <th class="py-3 px-5 w-[180px]">Waktu &amp; IP</th>
                        <th class="py-3 px-5">Aktivitas &amp; Modul</th>
                        <th class="py-3 px-5 w-[200px]">Pelaku</th>
                        <th class="py-3 px-5 w-[160px]">Tingkat Risiko</th>
                        <th class="py-3 px-5 w-[110px] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/10 text-[13px]">
                    @forelse($logs as $log)
                    <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors {{ $log->isHighRisk() ? 'bg-red-500/[0.015]' : '' }}">
                        
                        <!-- Kolom 1: Waktu & IP -->
                        <td class="py-3.5 px-5 align-top whitespace-nowrap">
                            <div class="font-semibold text-black dark:text-white text-[13px]">
                                {{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') : '-' }}
                            </div>
                            <div class="text-[11.5px] text-black/45 dark:text-white/45 font-mono flex items-center gap-1 mt-0.5">
                                <span>{{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->format('H:i:s') : '-' }} WIB</span>
                                <span class="opacity-30">•</span>
                                <span>{{ $log->ip_address ?: '127.0.0.1' }}</span>
                            </div>
                        </td>

                        <!-- Kolom 2: Aktivitas & Modul -->
                        <td class="py-3.5 px-5 align-top">
                            <div class="space-y-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-[6px] text-[11px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 border border-black/5 dark:border-white/10">
                                        {{ $log->getModuleLabel() }}
                                    </span>
                                    @if($log->action === 'created')
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                            DIBUAT
                                        </span>
                                    @elseif($log->action === 'updated')
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                            DIUBAH
                                        </span>
                                    @elseif($log->action === 'deleted')
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-red-500/10 text-red-600 dark:text-red-400">
                                            DIHAPUS
                                        </span>
                                    @else
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-black/5 text-black/60 dark:bg-white/10 dark:text-white/60">
                                            {{ strtoupper($log->action) }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[13.5px] font-semibold text-black dark:text-white leading-snug">
                                    {{ $log->risk_reason ?: ($log->action . ' ' . $log->getModuleLabel()) }}
                                </div>
                                @if($log->notes)
                                <div class="text-[12px] text-black/60 dark:text-white/60 flex items-center gap-1.5 pt-0.5">
                                    <i data-lucide="message-square" class="w-3 h-3 opacity-40 shrink-0"></i>
                                    <span class="italic line-clamp-1" title="{{ $log->notes }}">{{ $log->notes }}</span>
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Kolom 3: Pelaku -->
                        <td class="py-3.5 px-5 align-top whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/80 font-bold text-[11px] flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-black dark:text-white text-[13px] truncate max-w-[140px]">
                                        {{ $log->user?->name ?? 'Sistem / Otomatis' }}
                                    </div>
                                    <div class="text-[11.5px] text-black/45 dark:text-white/45 truncate max-w-[140px]">
                                        {{ $log->user?->email ?? ($log->user ? '-' : 'Otomatisasi Sistem') }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Kolom 4: Tingkat Risiko & Alert WA -->
                        <td class="py-3.5 px-5 align-top whitespace-nowrap">
                            <div>
                                @if($log->isHighRisk())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] dark:bg-[#FF453A]/15 dark:text-[#FF453A] border border-[#FF3B30]/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                        <span>Risiko Tinggi</span>
                                    </span>
                                @elseif($log->isMediumRisk())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Sedang</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-medium bg-black/[0.04] text-black/60 dark:bg-white/[0.06] dark:text-white/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/30"></span>
                                        <span>Rendah</span>
                                    </span>
                                @endif

                                @if($log->alert_sent_at)
                                <div class="mt-1 flex items-center gap-1 text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold">
                                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                    <span>WA Terkirim {{ $log->alert_sent_at->timezone('Asia/Jakarta')->format('H:i') }}</span>
                                </div>
                                @endif
                            </div>
                        </td>

                        <!-- Kolom 5: Aksi -->
                        <td class="py-3.5 px-5 align-top text-right whitespace-nowrap">
                            <button type="button" @click="openDiffModal('{{ $log->id }}')"
                                    class="min-h-[36px] px-3 rounded-[10px] text-[12.5px] font-semibold text-black/75 dark:text-white/80 bg-black/[0.03] dark:bg-white/[0.06] hover:bg-[#007AFF]/10 hover:text-[#007AFF] dark:hover:bg-[#0A84FF]/15 dark:hover:text-[#0A84FF] active:scale-95 transition-all inline-flex items-center gap-1.5 border border-black/5 dark:border-white/10">
                                <i data-lucide="git-compare" class="w-3.5 h-3.5"></i>
                                <span>Detail</span>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-14 text-center text-black/45 dark:text-white/45">
                            <div class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 mx-auto flex items-center justify-center mb-3">
                                <i data-lucide="inbox" class="w-6 h-6 opacity-60"></i>
                            </div>
                            <p class="font-semibold text-sm text-black/75 dark:text-white/75">Tidak ada log aktivitas audit yang ditemukan.</p>
                            <p class="text-xs mt-1">Coba sesuaikan kata kunci, filter modul, atau rentang tanggal.</p>
                            @if(!empty(array_filter($filters)))
                            <a href="{{ route('settings.audit-logs.index') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-[11px] bg-black/[0.04] dark:bg-white/[0.06] text-[12.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/10 transition-all">
                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                <span>Reset Filter</span>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card Feed View (< 640px) -->
        <div class="block sm:hidden divide-y divide-black/5 dark:divide-white/10">
            @forelse($logs as $log)
            <div class="p-4 space-y-2.5 hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors {{ $log->isHighRisk() ? 'bg-red-500/[0.02]' : '' }}">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[11.5px] font-mono text-black/45 dark:text-white/45">
                        {{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : '-' }} WIB
                    </span>
                    @if($log->isHighRisk())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/10 text-[#FF3B30] dark:bg-[#FF453A]/15 dark:text-[#FF453A] border border-[#FF3B30]/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                            <span>Tinggi</span>
                        </span>
                    @elseif($log->isMediumRisk())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400">
                            <span>Sedang</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/[0.04] text-black/60 dark:bg-white/[0.06] dark:text-white/60">
                            <span>Rendah</span>
                        </span>
                    @endif
                </div>

                <div>
                    <div class="flex items-center gap-1.5 mb-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70">
                            {{ $log->getModuleLabel() }}
                        </span>
                        <span class="text-[10px] font-bold text-black/40 dark:text-white/40 uppercase">
                            {{ $log->action }}
                        </span>
                    </div>
                    <div class="text-[13.5px] font-semibold text-black dark:text-white leading-snug">
                        {{ $log->risk_reason ?: ($log->action . ' ' . $log->getModuleLabel()) }}
                    </div>
                    @if($log->notes)
                    <p class="text-[12px] text-black/60 dark:text-white/60 italic mt-1">
                        "{{ $log->notes }}"
                    </p>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-3 pt-1 border-t border-black/[0.03] dark:border-white/[0.04]">
                    <div class="text-[12px] text-black/60 dark:text-white/60 flex items-center gap-1.5 truncate">
                        <div class="w-5 h-5 rounded-full bg-black/5 dark:bg-white/10 font-bold text-[9px] flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                        </div>
                        <span class="truncate">{{ $log->user?->name ?? 'Sistem' }}</span>
                    </div>

                    <button type="button" @click="openDiffModal('{{ $log->id }}')"
                            class="min-h-[36px] px-3.5 rounded-[10px] text-[12px] font-semibold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 active:scale-95 transition-all inline-flex items-center gap-1.5 shrink-0">
                        <i data-lucide="git-compare" class="w-3.5 h-3.5"></i>
                        <span>Lihat Diff</span>
                    </button>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-black/45 dark:text-white/45">
                <p class="font-semibold text-sm text-black/75 dark:text-white/75">Tidak ada log ditemukan.</p>
                <p class="text-xs mt-1">Coba sesuaikan filter pencarian.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="p-4 border-t border-black/5 dark:border-white/10">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <!-- Apple HIG Bento Visual Diff Viewer Modal -->
    <template x-teleport="body">
        <div x-show="showModal"
             x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center p-4 overflow-y-auto">

            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                x-show="showModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showModal = false"
                aria-hidden="true"></div>

            <div @click.away="showModal = false"
                 @keydown.escape.window="showModal = false"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="modal-visual-diff-title"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative z-10 w-full max-w-[850px] max-h-[90vh] flex flex-col rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden my-auto">

                <!-- Modal Header -->
                <div class="p-5 sm:p-6 border-b border-black/5 dark:border-white/10 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[13px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="git-compare" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 id="modal-visual-diff-title" class="text-base sm:text-lg font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                                <span>Visual Diff Viewer (Komparasi Data)</span>
                                <template x-if="activeLog && activeLog.risk_level === 'high'">
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-500/15 text-red-600 dark:text-red-400 uppercase">
                                        Risiko Tinggi
                                    </span>
                                </template>
                            </h3>
                            <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5" x-text="activeLog ? activeLog.module_label + ' • ' + activeLog.created_at_formatted : 'Memuat data...'"></p>
                        </div>
                    </div>

                    <button type="button" @click="showModal = false"
                            class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center text-black/60 dark:text-white/60 transition-all cursor-pointer"
                            aria-label="Tutup visual diff">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

            <!-- Modal Content (Scrollable) -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-5 flex-1">
                <template x-if="isLoading">
                    <div class="py-12 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-3 border-[#007AFF] border-r-transparent"></div>
                        <p class="text-xs text-black/50 dark:text-white/50 mt-3 font-medium">Mengambil jejak perubahan data...</p>
                    </div>
                </template>

                <template x-if="!isLoading && activeLog">
                    <div class="space-y-5">
                        <!-- Forensics Overview Box -->
                        <div class="rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/10 p-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-[12.5px]">
                            <div>
                                <span class="font-bold text-black/45 dark:text-white/45 text-[11.5px] block">Pelaku (Performer)</span>
                                <span class="font-semibold text-black dark:text-white mt-0.5 block" x-text="activeLog.user_name"></span>
                                <span class="text-black/45 dark:text-white/45 text-[11px] block truncate" x-text="activeLog.user_email"></span>
                            </div>
                            <div>
                                <span class="font-bold text-black/45 dark:text-white/45 text-[11.5px] block">Identitas Forensik Digital</span>
                                <span class="font-mono text-black dark:text-white mt-0.5 block" x-text="'IP: ' + activeLog.ip_address"></span>
                                <span class="text-black/45 dark:text-white/45 text-[11px] block truncate" :title="activeLog.user_agent" x-text="activeLog.user_agent"></span>
                            </div>
                            <div>
                                <span class="font-bold text-black/45 dark:text-white/45 text-[11.5px] block">Status Peringatan WhatsApp</span>
                                <template x-if="activeLog.alert_sent_at">
                                    <span class="font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5 block" x-text="'Terkirim ke ' + activeLog.alert_recipient + ' (' + activeLog.alert_sent_at + ')'"></span>
                                </template>
                                <template x-if="!activeLog.alert_sent_at">
                                    <span class="text-black/45 dark:text-white/45 mt-0.5 block">Tidak memicu notifikasi darurat</span>
                                </template>
                            </div>
                        </div>

                        <!-- Risk Reason and User Note -->
                        <template x-if="activeLog.risk_reason || activeLog.notes">
                            <div class="rounded-[15px] p-4 border"
                                 :class="activeLog.risk_level === 'high' ? 'bg-red-500/[0.04] border-red-500/20 text-red-900 dark:text-red-200' : 'bg-amber-500/[0.04] border-amber-500/20 text-amber-900 dark:text-amber-200'">
                                <div class="font-bold text-[13px] flex items-center gap-1.5" x-text="activeLog.risk_reason"></div>
                                <div class="text-[12px] mt-1 text-black/75 dark:text-white/80" x-text="'Alasan/Catatan: ' + (activeLog.notes || '-')"></div>
                            </div>
                        </template>

                        <!-- Visual Diff Table -->
                        <div>
                            <h4 class="text-[12px] font-bold text-black/60 dark:text-white/60 uppercase tracking-wider mb-2">
                                Perbandingan Atribut Data (Old vs. New)
                            </h4>

                            <div class="rounded-[15px] border border-black/5 dark:border-white/10 overflow-hidden">
                                <table class="w-full text-left text-[12.5px]">
                                    <thead>
                                        <tr class="bg-black/[0.03] dark:bg-white/[0.04] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold text-[11px] uppercase tracking-wider">
                                            <th class="py-2.5 px-4 w-1/3">Nama Atribut</th>
                                            <th class="py-2.5 px-4 w-1/3">Nilai Lama (Sebelumnya)</th>
                                            <th class="py-2.5 px-4 w-1/3">Nilai Baru (Setelahnya)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-black/5 dark:divide-white/10">
                                        <template x-for="item in activeLog.diffs" :key="item.key">
                                            <tr :class="item.is_different ? 'bg-amber-500/[0.02]' : ''">
                                                <td class="py-2.5 px-4 font-mono font-medium text-black dark:text-white text-[12px]" x-text="item.label"></td>
                                                <td class="py-2.5 px-4">
                                                    <span :class="item.is_different ? 'line-through text-red-600 dark:text-red-400 bg-red-500/10 px-2 py-0.5 rounded font-mono text-[11.5px]' : 'text-black/50 dark:text-white/50 font-mono text-[11.5px]'"
                                                          x-text="item.old"></span>
                                                </td>
                                                <td class="py-2.5 px-4">
                                                    <span :class="item.is_different ? 'font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded font-mono text-[11.5px]' : 'text-black/75 dark:text-white/75 font-mono text-[11.5px]'"
                                                          x-text="item.new"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-black/5 dark:border-white/10 flex justify-end">
                <button type="button" @click="showModal = false"
                        class="min-h-[44px] px-5 rounded-[12px] bg-black dark:bg-white text-white dark:text-black font-semibold text-[13px] hover:opacity-90 active:scale-95 transition-all cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>

</div>

<script>
function auditLogExplorer() {
    return {
        showModal: false,
        isLoading: false,
        activeLog: null,
        advancedOpen: {{ (!empty($filters['module']) || !empty($filters['user_id']) || !empty($filters['start_date']) || !empty($filters['end_date'])) ? 'true' : 'false' }},

        async openDiffModal(logId) {
            this.showModal = true;
            this.isLoading = true;
            this.activeLog = null;

            try {
                const response = await fetch(`{{ url('/settings/audit-logs') }}/${logId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Gagal mengambil data log.');
                }

                this.activeLog = await response.json();
                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat memuat visual diff data.');
                this.showModal = false;
            } finally {
                this.isLoading = false;
            }
        }
    };
}
</script>
@endsection
