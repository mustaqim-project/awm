@php
    $videoTestimonials = config('marketing.video_testimonials', []);
@endphp

@if(!empty($videoTestimonials) && count($videoTestimonials) > 0)
<section class="py-16 sm:py-20 bg-white dark:bg-[#070A14] transition-colors">
    <div class="max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12 space-y-3">
            <span class="text-xs font-bold text-[#007AFF] uppercase tracking-wider block">
                {{ __('public_marketing.video_kicker') }}
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ __('public_marketing.video_title') }}
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                {{ __('public_marketing.video_desc') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($videoTestimonials as $v)
                <div class="rounded-2xl overflow-hidden bg-slate-900 border border-slate-200 dark:border-white/10 shadow-sm aspect-video relative group">
                    @if(($v['platform'] ?? 'youtube') === 'youtube')
                        <iframe class="w-full h-full"
                                src="https://www.youtube-nocookie.com/embed/{{ $v['embed_id'] }}"
                                title="{{ $v['title'] ?? 'Video Testimoni' }}"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                        </iframe>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

