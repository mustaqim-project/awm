@extends('layouts.admin')

@section('title', __('admin.title_simulasi_eskalasi_harga_tahunan_platform'))

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-400 mb-1">
                <a href="{{ route('admin.pricing-programs.index', ['tab' => 'schedule']) }}" class="hover:text-blue-600 transition-colors flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    Kembali ke Jadwal Kenaikan
                </a>
                <span>/</span>
                <span class="text-slate-600 dark:text-slate-300">Simulasi Eskalasi</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
                <span class="p-2 bg-amber-500/10 text-amber-600 rounded-xl">
                    <i data-lucide="calculator" class="w-6 h-6"></i>
                </span>
                Simulasi Eskalasi Harga Tahunan
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Pratinjau dampak penyesuaian tarif tahunan terhadap pelanggan Early Adopter yang jatuh tempo hari ini.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pricing-programs.index', ['tab' => 'schedule']) }}" class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-all flex items-center gap-1.5">
                <i data-lucide="x" class="w-4 h-4"></i>
                Batal
            </a>
            @if($count > 0)
                <form action="{{ route('admin.pricing-programs.run-escalation') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengeksekusi penyesuaian tarif untuk {{ $count }} pelanggan ini secara permanen?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-sm shadow-blue-500/20 transition-all flex items-center gap-1.5">
                        <i data-lucide="play" class="w-4 h-4"></i>
                        Eksekusi Kenaikan Real ({{ $count }} Pelanggan)
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Dry-Run Info Alert Banner -->
    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-3.5">
        <div class="p-2 rounded-xl bg-amber-500/20 text-amber-600 shrink-0">
            <i data-lucide="shield-alert" class="w-5 h-5"></i>
        </div>
        <div class="space-y-1">
            <h3 class="text-sm font-semibold text-amber-900 dark:text-amber-200">Mode Simulasi Aman (Dry-Run Preview)</h3>
            <p class="text-xs text-amber-800 dark:text-amber-300/90 leading-relaxed">
                Halaman ini hanya menghitung dan menampilkan proyeksi perubahan tarif berdasarkan kebijakan batch masing-masing pelanggan. <strong>Tidak ada perubahan data di database, tidak ada saldo terpotong, dan tagihan invoice yang telah terbit sebelumnya tidak terpengaruh sama sekali.</strong>
            </p>
        </div>
    </div>

    <!-- Bento Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-400">Total Pelanggan Jatuh Tempo</span>
            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1 tabular-nums">{{ number_format($count) }}</div>
            <p class="text-[11px] text-slate-500 mt-1">Pelanggan dengan tanggal kenaikan &le; hari ini</p>
        </div>
        @php
            $totalOld = collect($subscriptions)->sum('old_price');
            $totalNew = collect($subscriptions)->sum('new_price');
            $totalDelta = $totalNew - $totalOld;
        @endphp
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-400">Proyeksi Kenaikan Omzet</span>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1 tabular-nums">+Rp {{ number_format($totalDelta, 0, ',', '.') }}</div>
            <p class="text-[11px] text-slate-500 mt-1">Akumulasi selisih tarif per siklus</p>
        </div>
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-medium text-slate-400">Rata-rata Kenaikan</span>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1 tabular-nums">
                {{ $count > 0 ? number_format(collect($subscriptions)->avg('percentage'), 2) : '0.00' }}%
            </div>
            <p class="text-[11px] text-slate-500 mt-1">Maksimum batas sistem 10.00%</p>
        </div>
    </div>

    <!-- Table Details -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-slate-400"></i>
                Rincian Pelanggan & Rekalkulasi Tarif
            </h2>
            <span class="text-xs text-slate-400 tabular-nums">Menampilkan {{ count($subscriptions) }} data simulasi</span>
        </div>

        @if(empty($subscriptions))
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-3 text-slate-400">
                    <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-500"></i>
                </div>
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ __('admin.semua_tarif_sudah_terkini') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mt-1">
                    Tidak ditemukan pelanggan Early Adopter yang jadwal kenaikan tahunannya jatuh tempo untuk hari ini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/75 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 text-slate-500 font-medium">
                        <tr>
                            <th class="py-3 px-4">Nama Bisnis & ID</th>
                            <th class="py-3 px-4 text-right">Tarif Lama</th>
                            <th class="py-3 px-4 text-center">Persentase</th>
                            <th class="py-3 px-4 text-right">Tarif Baru (Simulasi)</th>
                            <th class="py-3 px-4 text-right">Selisih (+Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                        @foreach($subscriptions as $sub)
                            @php
                                $delta = $sub['new_price'] - $sub['old_price'];
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="py-3 px-4 font-sans">
                                    <div class="font-semibold text-slate-900 dark:text-white">{{ $sub['business_name'] }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">Sub ID: {{ substr($sub['subscription_id'], 0, 8) }}... &bull; Biz: {{ substr($sub['business_id'], 0, 8) }}...</div>
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums text-slate-600 dark:text-slate-300">
                                    Rp {{ number_format($sub['old_price'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                        +{{ number_format($sub['percentage'], 2) }}%
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($sub['new_price'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right tabular-nums text-emerald-600 dark:text-emerald-400">
                                    +Rp {{ number_format($delta, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
