@php
    $verifiedTestimonials = config('marketing.verified_testimonials', []);
@endphp

@if(!empty($verifiedTestimonials) && count($verifiedTestimonials) > 0)
<section class="py-16 sm:py-20 bg-slate-50 dark:bg-[#070B18] border-t border-b border-slate-200/80 dark:border-white/5 transition-colors">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 space-y-3">
            <span class="text-xs font-bold text-[#007AFF] uppercase tracking-wider block">
                Pelanggan Kami
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                Kisah &amp; Pengalaman Nyata Pengguna COOCA
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Pengalaman langsung pemilik usaha yang telah mendigitalisasi operasional kasir dan pembukuan bersama COOCA.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($verifiedTestimonials as $t)
                <div class="p-6 rounded-2xl bg-white dark:bg-[#101726] border border-slate-200/80 dark:border-white/10 shadow-xs flex flex-col justify-between">
                    <div>
                        @if(!empty($t['logo_url']))
                            <img src="{{ $t['logo_url'] }}" alt="{{ $t['business_name'] }}" class="h-8 w-auto object-contain mb-4">
                        @endif
                        <blockquote class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed italic mb-4">
                            "{{ $t['quote'] }}"
                        </blockquote>
                    </div>
                    <div class="pt-4 border-t border-slate-100 dark:border-white/5 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $t['owner_name'] }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $t['business_name'] }} &bull; {{ $t['city'] }}</p>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Terverifikasi
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

