{{-- Google Trends Business Articles View --}}
@php
    $currentReport = $trendingReports[0] ?? null;
    $topics = $currentReport['topics'] ?? [];
@endphp

<div class="space-y-6">
    <!-- Top Summary Banner -->
    <div class="p-5 sm:p-6 rounded-[22px] bg-gradient-to-br from-[#007AFF]/10 via-[#007AFF]/5 to-transparent dark:from-[#007AFF]/20 dark:via-[#007AFF]/10 dark:to-transparent border border-[#007AFF]/20 shadow-[0_4px_24px_rgba(0,122,255,0.08)]">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#007AFF]/15 dark:bg-[#007AFF]/25 text-[#007AFF] text-[11px] font-bold tracking-wide uppercase">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    <span>Real-Time Business Intelligence</span>
                </div>
                <h2 class="text-[20px] sm:text-[22px] font-bold text-black dark:text-white tracking-tight">
                    Google Trends Indonesia: Kategori Bisnis
                </h2>
                <p class="text-[13px] text-black/60 dark:text-white/60 max-w-2xl">
                    10 Topik bisnis dan ekonomi terhangat hasil riset Google Trends Indonesia hari ini ({{ $currentReport['date'] ?? date('Y-m-d') }}), lengkap dengan strategi keyword SEO, search intent, dan artikel edukasi UMKM yang sudah terpublikasi.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <div class="text-right hidden sm:block">
                    <span class="text-[11px] font-medium text-black/40 dark:text-white/40 block">Total Topik Tren</span>
                    <span class="text-[20px] font-bold text-black dark:text-white">{{ count($topics) }} Topik</span>
                </div>
                <div class="w-11 h-11 rounded-[14px] bg-[#007AFF] text-white flex items-center justify-center shadow-[0_4px_14px_rgba(0,122,255,0.35)]">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <!-- Meta info pill bar -->
        <div class="mt-5 pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-wrap items-center gap-4 text-[12px] text-black/60 dark:text-white/60">
            <div class="inline-flex items-center gap-1.5">
                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Wilayah: <strong>Indonesia (ID)</strong></span>
            </div>
            <div class="inline-flex items-center gap-1.5">
                <i data-lucide="folder" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Kategori: <strong>3 - Bisnis & Keuangan</strong></span>
            </div>
            <div class="inline-flex items-center gap-1.5">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                <span>Tanggal Riset: <strong>{{ $currentReport['date'] ?? date('Y-m-d') }}</strong></span>
            </div>
            <div class="inline-flex items-center gap-1.5">
                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-[#34C759]"></i>
                <span>Status Artikel: <strong class="text-[#34C759]">100% Siap & Terpublikasi</strong></span>
            </div>
        </div>
    </div>

    <!-- 10 Topics Bento Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @forelse($topics as $index => $t)
            @php
                $matchingPost = \App\Models\Post::where('slug', $t['slug'] ?? '')->first();
            @endphp
            <div class="p-5 rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_4px_20px_rgba(0,0,0,0.03)] flex flex-col justify-between hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)] transition-all">
                <div class="space-y-3.5">
                    <!-- Card Top Header -->
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] text-black dark:text-white text-[12px] font-bold flex items-center justify-center">
                                #{{ $t['id'] ?? ($index + 251) }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-[6px] text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF]">
                                {{ $t['assigned_category'] ?? 'Edukasi' }}
                            </span>
                        </div>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#34C759] bg-[#34C759]/10 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            <span>{{ $matchingPost ? 'Published' : 'Generated' }}</span>
                        </span>
                    </div>

                    <!-- Topic Title & Cover Preview -->
                    <div class="flex gap-3.5 items-start">
                        <img src="/images/blog/{{ $t['slug'] }}.jpg" 
                             alt="{{ $t['topic'] }}" 
                             class="w-20 h-20 rounded-[12px] object-cover bg-black/5 dark:bg-white/5 shrink-0 border border-black/[0.05] dark:border-white/[0.05]"
                             onerror="this.src='https://cooca.id/images/blog/photo-1460925895917-afdab827c52f.jpg'">
                        <div class="space-y-1 min-w-0">
                            <h3 class="text-[15px] font-bold text-black dark:text-white leading-snug">
                                {{ $t['topic'] }}
                            </h3>
                            <div class="text-[12px] text-black/50 dark:text-white/50 flex flex-wrap gap-x-3 gap-y-1">
                                <span>Kueri Tren: <code class="px-1 py-0.5 rounded bg-black/5 dark:bg-white/10 font-mono text-black dark:text-white text-[11px]">{{ $t['trending_query'] }}</code></span>
                            </div>
                        </div>
                    </div>

                    <!-- Keywords & Intent Specs -->
                    <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.03] dark:border-white/[0.04] space-y-2 text-[12px]">
                        <div>
                            <span class="text-black/50 dark:text-white/50 font-medium">Primary Keyword:</span>
                            <span class="font-semibold text-black dark:text-white block mt-0.5">{{ $t['primary_keyword'] }}</span>
                        </div>
                        <div class="pt-1 border-t border-black/[0.04] dark:border-white/[0.04]">
                            <span class="text-black/50 dark:text-white/50 font-medium">Alasan Tren:</span>
                            <p class="text-black/80 dark:text-white/80 mt-0.5 text-[11.5px] leading-relaxed line-clamp-3">
                                {{ $t['trending_reason'] }}
                            </p>
                        </div>
                        <div class="pt-1 border-t border-black/[0.04] dark:border-white/[0.04]">
                            <span class="text-black/50 dark:text-white/50 font-medium">Sudut Pandang UMKM:</span>
                            <p class="text-black/80 dark:text-white/80 mt-0.5 text-[11.5px] leading-relaxed line-clamp-2">
                                {{ $t['article_angle'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="mt-4 pt-3.5 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-2">
                    <span class="text-[11px] text-black/40 dark:text-white/40">
                        Slug: <span class="font-mono text-black/70 dark:text-white/70">{{ $t['slug'] }}</span>
                    </span>
                    <div class="flex items-center gap-2">
                        @if($matchingPost)
                            <a href="{{ route('admin.posts.edit', $matchingPost->id) }}" 
                               class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 transition inline-flex items-center gap-1.5">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                <span>Edit</span>
                            </a>
                        @endif
                        <a href="https://cooca.id/blog/{{ $t['slug'] }}" 
                           target="_blank" 
                           rel="noopener"
                           class="h-8 px-3 rounded-[8px] text-[12px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] transition inline-flex items-center gap-1.5 shadow-sm">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            <span>Buka Artikel</span>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full p-12 text-center rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08]">
                <i data-lucide="search" class="w-8 h-8 mx-auto text-black/30 dark:text-white/30 mb-2"></i>
                <p class="text-[14px] text-black/50 dark:text-white/50">Belum ada laporan riset Google Trends yang dimuat.</p>
            </div>
        @endforelse
    </div>
</div>
