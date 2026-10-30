{{--
    Education & Courses Features Banner (`education_courses`)
    Academic pillars: industry curriculum, expert mentorship, collaborative community, official certification.
--}}
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 sm:p-6 rounded-3xl bg-white dark:bg-[#131D31] border border-indigo-100 dark:border-indigo-950/70 shadow-2xs transition-colors">

        {{-- 1: Industry Curriculum --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-900/60 flex items-center justify-center text-indigo-700 dark:text-indigo-300 shrink-0">
                <i data-lucide="laptop" class="w-5 h-5 text-indigo-600"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Kurikulum Praktik</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Relevan kebutuhan industri</p>
            </div>
        </div>

        {{-- 2: Expert Mentors --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-900/60 flex items-center justify-center text-indigo-700 dark:text-indigo-300 shrink-0">
                <i data-lucide="users" class="w-5 h-5 text-indigo-600"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Mentor Praktisi</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Bimbingan langsung dari ahli</p>
            </div>
        </div>

        {{-- 3: Discussion & Group --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-900/60 flex items-center justify-center text-indigo-700 dark:text-indigo-300 shrink-0">
                <i data-lucide="messages-square" class="w-5 h-5 text-orange-500"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">Forum Diskusi</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Tanya jawab &amp; jejaring siswa</p>
            </div>
        </div>

        {{-- 4: Verified Certificate --}}
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-900/60 flex items-center justify-center text-indigo-700 dark:text-indigo-300 shrink-0">
                <i data-lucide="file-badge" class="w-5 h-5 text-emerald-500"></i>
            </div>
            <div>
                <h4 class="font-heading font-bold text-xs sm:text-sm text-slate-900 dark:text-white">{{ __('storefront.sertifikat_resmi') }}</h4>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Bukti keahlian terverifikasi</p>
            </div>
        </div>

    </div>
</section>
