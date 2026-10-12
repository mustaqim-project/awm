{{-- ========================================================================= --}}
{{-- TRUST & VERIFIED BADGES: Bakery & Cake (Bakery, Cake Shop, Pastry, Dessert & Custom Cake) --}}
{{-- ========================================================================= --}}
@props([
    'type' => 'all', // 'all', 'purebutter', 'official', 'warranty', 'freshbake'
    'compact' => false,
])

<div class="w-full">
    @if ($type === 'all')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {{-- Badge 1 --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-[#221810] border border-amber-900/10 dark:border-amber-500/15 shadow-2xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-amber-600/10 dark:bg-amber-400/10 text-amber-700 dark:text-amber-400 border border-amber-600/20 dark:border-amber-400/20">
                    <i data-lucide="cake" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-amber-50 block leading-tight font-heading">
                        Dapur Terverifikasi
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-amber-300/70">
                        Resep Klasik &amp; Higienis
                    </span>
                </div>
            </div>

            {{-- Badge 2 --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-[#221810] border border-amber-900/10 dark:border-amber-500/15 shadow-2xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-amber-500/10 dark:bg-amber-400/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 dark:border-amber-400/20">
                    <i data-lucide="sun" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-amber-50 block leading-tight font-heading">
                        Fresh Baked Subuh
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-amber-300/70">
                        Batch Baru Tiap Pagi
                    </span>
                </div>
            </div>

            {{-- Badge 3 --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-[#221810] border border-amber-900/10 dark:border-amber-500/15 shadow-2xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-rose-500/10 dark:bg-rose-400/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 dark:border-rose-400/20">
                    <i data-lucide="award" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-amber-50 block leading-tight font-heading">
                        100% Pure Butter
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-amber-300/70">
                        Mentega Alami Murni
                    </span>
                </div>
            </div>

            {{-- Badge 4 --}}
            <div class="p-3.5 rounded-2xl bg-white dark:bg-[#221810] border border-amber-900/10 dark:border-amber-500/15 shadow-2xs flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-amber-600/10 dark:bg-amber-400/10 text-amber-700 dark:text-amber-400 border border-amber-600/20 dark:border-amber-400/20">
                    <i data-lucide="package" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-neutral-900 dark:text-amber-50 block leading-tight font-heading">
                        Box Anti-Guncang
                    </span>
                    <span class="text-[10px] text-neutral-500 dark:text-amber-300/70">
                        Dekorasi Kue Utuh Indah
                    </span>
                </div>
            </div>
        </div>
    @elseif ($type === 'official')
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-600/10 dark:bg-amber-400/10 text-amber-800 dark:text-amber-300 border border-amber-600/20 dark:border-amber-400/25">
            <i data-lucide="badge-check" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
            <span>Dapur Roti Terverifikasi</span>
        </div>
    @elseif ($type === 'warranty')
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-500/10 dark:bg-rose-400/10 text-rose-900 dark:text-rose-300 border border-rose-500/20 dark:border-rose-400/25">
            <i data-lucide="shield-check" class="w-4 h-4 text-rose-600 dark:text-rose-400"></i>
            <span>Garansi Tiba Cantik &amp; Utuh</span>
        </div>
    @else
        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-600/10 dark:bg-amber-400/10 text-amber-800 dark:text-amber-300 border border-amber-600/20 dark:border-amber-400/25">
            <i data-lucide="award" class="w-4 h-4 text-amber-600 dark:text-amber-400"></i>
            <span>100% Bahan Alami Terverifikasi</span>
        </div>
    @endif
</div>