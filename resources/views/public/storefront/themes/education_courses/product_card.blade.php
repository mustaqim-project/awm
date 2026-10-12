{{--
    Education & Courses Course Card (`education_courses`)
    Academic program card with level badges, syllabus details, tuition fees, and direct enrollment action.
--}}
@php
    $pdpUrl = url('/' . $business->slug . '/produk/' . ($item->slug ?: $item->id));
    $hasPrice = ($item->show_price_on_web ?? true) && $item->selling_price > 0;
@endphp

<div class="group flex flex-col rounded-2xl overflow-hidden bg-white dark:bg-[#131D31] border border-indigo-100 dark:border-indigo-950/70 shadow-2xs hover:shadow-xl hover:border-indigo-400 dark:hover:border-indigo-700 transition-all duration-300">
    {{-- Course Visual Banner --}}
    <a href="{{ $pdpUrl }}" class="relative aspect-video overflow-hidden bg-indigo-50/50 dark:bg-slate-900 block">
        @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-indigo-300 dark:text-slate-600">
                <i data-lucide="graduation-cap" class="w-12 h-12 stroke-1 mb-1 text-indigo-400"></i>
                <span class="text-[10px] uppercase font-bold tracking-wider">Program Kursus</span>
            </div>
        @endif

        @if ($item->category)
            <span class="absolute top-3 left-3 px-3 py-1 rounded-lg text-[10px] font-bold bg-white/95 dark:bg-slate-950/95 text-indigo-800 dark:text-indigo-300 shadow-xs backdrop-blur-xs border border-indigo-200/50">
                {{ $item->category->name }}
            </span>
        @endif

        <span class="absolute bottom-3 right-3 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-600 text-white shadow-xs flex items-center gap-1">
            <i data-lucide="award" class="w-3 h-3 text-amber-300"></i>
            <span>Sertifikasi</span>
        </span>
    </a>

    {{-- Content --}}
    <div class="p-5 flex flex-col flex-1">
        <a href="{{ $pdpUrl }}"
            class="font-heading font-bold text-base sm:text-lg text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition line-clamp-1 leading-snug mb-1">
            {{ $item->name }}
        </a>

        @if ($item->description)
            <p class="text-xs text-slate-500 dark:text-indigo-200/60 line-clamp-2 leading-relaxed mb-4">
                {{ $item->description }}
            </p>
        @endif

        {{-- Tuition Fee & Enrollment CTA --}}
        <div class="mt-auto pt-3 border-t border-indigo-100 dark:border-indigo-950/60 flex items-center justify-between gap-3">
            <div>
                <span class="text-[10px] text-slate-400 dark:text-indigo-400/60 block font-medium">Biaya Program</span>
                <span class="font-extrabold text-base text-indigo-700 dark:text-indigo-300 tabular-nums">
                    {{ $hasPrice ? 'Rp ' . number_format((float) $item->selling_price, 0, ',', '.') : 'Beasiswa / Hubungi Kami' }}
                </span>
            </div>

            @if ($hasPrice)
                <button type="button"
                    @click="$store.cart.add({ id: '{{ $item->id }}', name: '{{ addslashes($item->name) }}', price: {{ (float) $item->selling_price }}, image_url: '{{ $item->image_url }}' }, 1)"
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs hover:shadow transition flex items-center gap-1.5 active:scale-95"
                    title="Daftar Kelas Ini">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Daftar</span>
                </button>
            @else
                <a href="{{ $pdpUrl }}"
                    class="px-3.5 py-2 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 text-xs font-semibold hover:bg-indigo-100 transition">
                    Silabus
                </a>
            @endif
        </div>
    </div>
</div>
