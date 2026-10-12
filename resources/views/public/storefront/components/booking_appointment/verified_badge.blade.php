{{-- ========================================================================= --}}
{{-- TRUST & VERIFIED BADGES: Booking & Appointment                            --}}
{{-- ========================================================================= --}}
@props([
    'type' => 'all', // 'all', 'official', 'warranty', 'slot', 'hygiene'
    'compact' => false,
])

<div class="w-full">
    @if ($type === 'all')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {{-- Badge 1: Confirmed Schedule --}}
            <div class="p-3.5 rounded-2xl bg-white/70 dark:bg-[#161224] border border-purple-200/70 dark:border-purple-900/50 flex items-center gap-3 shadow-2xs hover:border-purple-400 transition">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-2xs bg-purple-100 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300">
                    <i data-lucide="calendar-check" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 dark:text-purple-100 block leading-tight font-heading">
                        Jadwal Pasti
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-purple-300/70">
                        Slot Waktu Terkunci
                    </span>
                </div>
            </div>

            {{-- Badge 2: Certified Specialist --}}
            <div class="p-3.5 rounded-2xl bg-white/70 dark:bg-[#161224] border border-purple-200/70 dark:border-purple-900/50 flex items-center gap-3 shadow-2xs hover:border-purple-400 transition">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-2xs bg-rose-100 dark:bg-rose-950/80 text-rose-600 dark:text-rose-300">
                    <i data-lucide="user-check" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 dark:text-purple-100 block leading-tight font-heading">
                        Praktisi Bersertifikat
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-purple-300/70">
                        Terapis / Ahli Berlisensi
                    </span>
                </div>
            </div>

            {{-- Badge 3: Zero Queue --}}
            <div class="p-3.5 rounded-2xl bg-white/70 dark:bg-[#161224] border border-purple-200/70 dark:border-purple-900/50 flex items-center gap-3 shadow-2xs hover:border-purple-400 transition">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-2xs bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300">
                    <i data-lucide="clock" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 dark:text-purple-100 block leading-tight font-heading">
                        Tanpa Antrean
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-purple-300/70">
                        Datang Langsung Sesi
                    </span>
                </div>
            </div>

            {{-- Badge 4: Flexible Reschedule --}}
            <div class="p-3.5 rounded-2xl bg-white/70 dark:bg-[#161224] border border-purple-200/70 dark:border-purple-900/50 flex items-center gap-3 shadow-2xs hover:border-purple-400 transition">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 shadow-2xs bg-indigo-100 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300">
                    <i data-lucide="refresh-cw" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 dark:text-purple-100 block leading-tight font-heading">
                        Reschedule Mudah
                    </span>
                    <span class="text-[10px] text-slate-500 dark:text-purple-300/70">
                        Atur Ulang Tanggal
                    </span>
                </div>
            </div>
        </div>
    @elseif ($type === 'official' || $type === 'slot')
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-500/20">
            <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i>
            <span>Jadwal Terkonfirmasi</span>
        </div>
    @elseif ($type === 'warranty')
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-500/10 text-rose-700 dark:text-rose-300 border border-rose-500/20">
            <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
            <span>Praktisi Bersertifikat</span>
        </div>
    @else
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <span>Terverifikasi Resmi</span>
        </div>
    @endif
</div>