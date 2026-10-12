{{-- ========================================================================= --}}
{{-- TRUST & VERIFIED BADGES: Clinical Pure (Apotek & Alkes) (Apotek, Obat Resep Dokter, Vitamin & Alat Medis) --}}
{{-- ========================================================================= --}}
@props([
    'type' => 'all', // 'all', 'halal', 'bpom', 'kemenkes', 'official', 'warranty'
    'compact' => false,
])

<div class="w-full">
    @if ($type === 'all')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {{-- Badge 1 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #0D948818; color: #0D9488;">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        Apotek Resmi Berizin (SIPA)
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Didampingi Apoteker Berlisensi
                    </span>
                </div>
            </div>

            {{-- Badge 2 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #0D948818; color: #0D9488;">
                    <i data-lucide="award" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        100% Terdaftar BPOM RI
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Obat & Vitamin Asli Tersegel
                    </span>
                </div>
            </div>

            {{-- Badge 3 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #0D948818; color: #0D9488;">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        Masa Kedaluwarsa Terjaga
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Penyimpanan Suhu Standar Farmasi
                    </span>
                </div>
            </div>

            {{-- Badge 4 --}}
            <div class="p-3 rounded-2xl bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 shadow-2xs"
                     style="background-color: #0D948818; color: #0D9488;">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-white block leading-tight font-heading">
                        Kemas Rapi & Privasi Aman
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                        Pengiriman Cepat Obat Urgent
                    </span>
                </div>
            </div>
        </div>
    @elseif ($type === 'official')
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold border"
             style="background-color: #0D948815; color: #0D9488; border-color: #0D948830;">
            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
            <span>Apotek Resmi Berizin (SIPA)</span>
        </div>
    @elseif ($type === 'warranty')
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl text-[11px] font-medium border"
             style="background-color: #0D948812; color: #0D9488; border-color: #0D948825;">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <span>100% Terdaftar BPOM RI</span>
        </div>
    @else
        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[11px] font-semibold">
            <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
            <span>Terverifikasi Resmi</span>
        </div>
    @endif
</div>