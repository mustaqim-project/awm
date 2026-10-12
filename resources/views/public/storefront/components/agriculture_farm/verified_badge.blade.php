{{-- ========================================================================= --}}
{{-- TRUST & VERIFIED BADGES: Agriculture & Farm (Pertanian, Peternakan, Perkebunan, Perikanan & Hasil Panen) --}}
{{-- ========================================================================= --}}
@props([
    'type' => 'all', // 'all', 'organic', 'official', 'warranty', 'coldchain'
    'compact' => false,
])

<div class="w-full">
    @if ($type === 'all')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {{-- Badge 1 --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-[#0D1F12] border border-emerald-900/10 dark:border-emerald-500/15 shadow-2xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-emerald-700/10 dark:bg-emerald-400/10 text-emerald-700 dark:text-emerald-400 border border-emerald-700/20 dark:border-emerald-400/20">
                    <i data-lucide="sprout" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-emerald-50 block leading-tight font-heading">
                        Petani Terverifikasi
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-emerald-300/70">
                        Kemitraan Kebun Poktan
                    </span>
                </div>
            </div>

            {{-- Badge 2 --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-[#0D1F12] border border-emerald-900/10 dark:border-emerald-500/15 shadow-2xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-amber-500/10 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 dark:border-amber-400/20">
                    <i data-lucide="sun" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-emerald-50 block leading-tight font-heading">
                        100% Panen Segar
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-emerald-300/70">
                        Dipetik Pagi Subuh Tadi
                    </span>
                </div>
            </div>

            {{-- Badge 3 --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-[#0D1F12] border border-emerald-900/10 dark:border-emerald-500/15 shadow-2xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-emerald-700/10 dark:bg-emerald-400/10 text-emerald-700 dark:text-emerald-400 border border-emerald-700/20 dark:border-emerald-400/20">
                    <i data-lucide="leaf" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-emerald-50 block leading-tight font-heading">
                        Bebas Residu Kimia
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-emerald-300/70">
                        Nutrisi &amp; Kompos Hayati
                    </span>
                </div>
            </div>

            {{-- Badge 4 --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-[#0D1F12] border border-emerald-900/10 dark:border-emerald-500/15 shadow-2xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-emerald-700/10 dark:bg-emerald-400/10 text-emerald-700 dark:text-emerald-400 border border-emerald-700/20 dark:border-emerald-400/20">
                    <i data-lucide="shield-check" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-emerald-50 block leading-tight font-heading">
                        Garansi Tiba Segar
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-emerald-300/70">
                        100% Ganti Baru Jika Layu
                    </span>
                </div>
            </div>
        </div>
    @elseif ($type === 'official')
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-700/10 dark:bg-emerald-400/10 text-emerald-800 dark:text-emerald-300 border border-emerald-700/20 dark:border-emerald-400/25">
            <i data-lucide="badge-check" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            <span>Petani Binaan Terverifikasi</span>
        </div>
    @elseif ($type === 'warranty')
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-500/10 dark:bg-amber-400/10 text-amber-900 dark:text-amber-300 border border-amber-500/20 dark:border-amber-400/25">
            <i data-lucide="shield-check" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
            <span>Garansi Kesegaran 100%</span>
        </div>
    @else
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-700/10 dark:bg-emerald-400/10 text-emerald-800 dark:text-emerald-300 border border-emerald-700/20 dark:border-emerald-400/25">
            <i data-lucide="leaf" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
            <span>Hasil Panen Alami Terverifikasi</span>
        </div>
    @endif
</div>