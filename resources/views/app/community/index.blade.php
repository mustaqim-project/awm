@extends('layouts.app', [
    'title' => 'Komunitas Owner - Cooca',
    'headerTitle' => 'Komunitas Owner',
    'headerSubtitle' => 'Wadah diskusi pengusaha UMKM dan agenda event bisnis',
])

@section('content')
<div class="max-w-7xl mx-auto pb-16 px-2 sm:px-4 lg:px-6" x-data="{
    copiedPostId: null,
    selectedTicket: null,
    ticketLoading: false,
    ticketError: null,
    locCheckinLoading: false,
    locCheckinMsg: null,
    barcodeInput: '',
    barcodeLoading: false,
    barcodeMsg: null,

    copyLink(postId) {
        const url = window.location.origin + '/community#' + postId;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url);
        }
        this.copiedPostId = postId;
        setTimeout(() => this.copiedPostId = null, 2500);
    },

    appendTag(tag) {
        const textarea = document.querySelector('textarea[name=\'content\']');
        if (textarea) {
            textarea.value = (textarea.value.trim() ? textarea.value.trim() + ' ' : '') + tag + ' ';
            textarea.focus();
        }
    },

    scrollSlider(dir) {
        const el = this.$refs.eventSlider;
        if (el) {
            el.scrollBy({ left: dir * 320, behavior: 'smooth' });
        }
    },

    focusComposer() {
        const textarea = document.querySelector('textarea[name=\'content\']');
        if (textarea) {
            textarea.scrollIntoView({ behavior: 'smooth', block: 'center' });
            textarea.focus();
        }
    },

    async markAllRead() {
        try {
            await fetch('{{ route('community.notifications.read') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            });
            const badge = document.getElementById('unread-notif-badge');
            if (badge) badge.remove();
        } catch (e) {}
    },

    async viewTicket(eventId) {
        this.ticketLoading = true;
        this.ticketError = null;
        this.locCheckinMsg = null;
        this.barcodeMsg = null;
        this.selectedTicket = null;
        try {
            const res = await fetch('/community/events/' + eventId + '/ticket');
            const data = await res.json();
            if (data.success) {
                this.selectedTicket = data.ticket;
                this.selectedTicket.event_id = eventId;
            } else {
                this.ticketError = data.message;
            }
        } catch (e) {
            this.ticketError = 'Gagal memuat tiket digital.';
        } finally {
            this.ticketLoading = false;
        }
    },

    checkinGps() {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung deteksi lokasi GPS.');
            return;
        }
        this.locCheckinLoading = true;
        this.locCheckinMsg = null;
        navigator.geolocation.getCurrentPosition(async (pos) => {
            try {
                const res = await fetch('/community/events/' + this.selectedTicket.event_id + '/checkin-location', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude
                    })
                });
                const data = await res.json();
                this.locCheckinMsg = {
                    type: data.success ? 'success' : 'error',
                    text: data.message
                };
                if (data.success) {
                    this.selectedTicket.is_checked_in = true;
                    this.selectedTicket.status = 'attended';
                    this.selectedTicket.checked_in_at = 'Baru saja';
                    this.selectedTicket.checkin_method = 'location';
                }
            } catch (e) {
                this.locCheckinMsg = { type: 'error', text: 'Gangguan koneksi saat check-in.' };
            } finally {
                this.locCheckinLoading = false;
            }
        }, (err) => {
            this.locCheckinLoading = false;
            this.locCheckinMsg = { type: 'error', text: 'Izin lokasi GPS ditolak: ' + err.message };
        }, { enableHighAccuracy: true, timeout: 10000 });
    },

    async checkinBarcodeSubmit() {
        if (!this.barcodeInput.trim()) return;
        this.barcodeLoading = true;
        this.barcodeMsg = null;
        try {
            const res = await fetch('/community/events/' + this.selectedTicket.event_id + '/checkin-barcode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ code: this.barcodeInput.trim() })
            });
            const data = await res.json();
            this.barcodeMsg = {
                type: data.success ? 'success' : 'error',
                text: data.message
            };
            if (data.success) {
                this.selectedTicket.is_checked_in = true;
                this.selectedTicket.status = 'attended';
                this.selectedTicket.checked_in_at = 'Baru saja';
                this.selectedTicket.checkin_method = 'barcode';
                this.barcodeInput = '';
            }
        } catch (e) {
            this.barcodeMsg = { type: 'error', text: 'Gangguan jaringan saat validasi barcode.' };
        } finally {
            this.barcodeLoading = false;
        }
    }
}" x-init="@if($activeTab === 'notifications' && $unreadNotificationsCount > 0) markAllRead(); @endif">

    <!-- Responsive Layout 3-Kolom ala Twitter / X.com -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- ========================================== -->
        <!-- 1. SIDEBAR KIRI (Navigasi Feed / Postingan Saya / Notifikasi) -->
        <!-- ========================================== -->
        <aside class="lg:col-span-3 space-y-4 lg:sticky lg:top-20">
            <!-- User Profile Card -->
            <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 shadow-2xs">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-emerald-600 dark:bg-emerald-500 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                        {{ strtoupper(substr(auth()->user()->name ?? 'O', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">
                            {{ auth()->user()->name ?? 'Owner Cooca' }}
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                            {{ $business->name ?? 'Usaha Terdaftar' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Navigation Rail -->
            <nav class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-2 shadow-2xs space-y-1">
                <!-- Tab: Feed Utama -->
                <a href="{{ route('community.index', ['tab' => 'feed']) }}"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ ($activeTab === 'feed' && !request('topic')) ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5' }}">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="messages-square" class="w-4 h-4 {{ ($activeTab === 'feed' && !request('topic')) ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Feed Komunitas</span>
                    </div>
                    @if($activeTab === 'feed' && !request('topic'))
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    @endif
                </a>

                <!-- Tab: Postingan Saya -->
                <a href="{{ route('community.index', ['tab' => 'my_posts']) }}"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ ($activeTab === 'my_posts') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5' }}">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="user" class="w-4 h-4 {{ ($activeTab === 'my_posts') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Postingan Saya</span>
                    </div>
                    @if($activeTab === 'my_posts')
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    @endif
                </a>

                <!-- Tab: Notifikasi -->
                <a href="{{ route('community.index', ['tab' => 'notifications']) }}"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition {{ ($activeTab === 'notifications') ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5' }}">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="bell" class="w-4 h-4 {{ ($activeTab === 'notifications') ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                        <span>Notifikasi</span>
                    </div>
                    @if($unreadNotificationsCount > 0)
                        <span id="unread-notif-badge" class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white animate-pulse">
                            {{ $unreadNotificationsCount }}
                        </span>
                    @elseif($activeTab === 'notifications')
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    @endif
                </a>

                <!-- Link: Agenda Event Resmi -->
                <a href="{{ route('community.events.index') }}"
                    class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-white/5 transition">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                        <span>Agenda &amp; Pelatihan</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">{{ $upcomingEvents->count() }}</span>
                </a>
            </nav>

            <!-- Quick Action: Tulis Postingan -->
            <button type="button" @click="focusComposer()"
                class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-[0.98] transition flex items-center justify-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                <i data-lucide="pen-tool" class="w-3.5 h-3.5"></i>
                <span>Tulis Ide Bisnis</span>
            </button>

            <!-- Storage Quota Widget -->
            <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-3.5 shadow-2xs space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold">
                    <span class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="hard-drive" class="w-3.5 h-3.5 text-amber-500"></i>
                        <span>Kapasitas Cloud</span>
                    </span>
                    <span class="font-mono text-slate-900 dark:text-white">{{ $storageSummary['percentage'] ?? 0 }}%</span>
                </div>
                <div class="w-full h-1.5 rounded-full bg-slate-100 dark:bg-white/10 overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ min(100, $storageSummary['percentage'] ?? 0) }}%"></div>
                </div>
                <div class="text-[10px] text-slate-400 flex items-center justify-between">
                    <span>Terpakai: {{ $storageSummary['formatted_used'] ?? '0 MB' }}</span>
                    <a href="{{ route('billing.limits') }}" class="text-[#007AFF] hover:underline font-semibold">Tingkatkan</a>
                </div>
            </div>
        </aside>

        <!-- ========================================== -->
        <!-- 2. KOLOM TENGAH (Main Thread Feed / Events Slider) -->
        <!-- ========================================== -->
        <main class="lg:col-span-6 space-y-5 min-w-0">

            <!-- 2A. SLIDER EVENT MENDATANG (Di Paling Atas Sesuai Mandat) -->
            <section class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-5 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">
                                Agenda Event Komunitas Terbaru
                            </h2>
                            <span class="text-[10px] text-slate-400 dark:text-slate-500">
                                Berbayar &amp; Free · Offline &amp; Online
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        @if($upcomingEvents->count() > 1)
                            <button type="button" @click="scrollSlider(-1)"
                                class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/15 text-slate-600 dark:text-slate-300 flex items-center justify-center transition active:scale-95 cursor-pointer"
                                title="Sebelumnya">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </button>
                            <button type="button" @click="scrollSlider(1)"
                                class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/15 text-slate-600 dark:text-slate-300 flex items-center justify-center transition active:scale-95 cursor-pointer"
                                title="Berikutnya">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </button>
                        @endif
                        <a href="{{ route('community.events.index') }}"
                            class="text-xs font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline ml-1">
                            Semua
                        </a>
                    </div>
                </div>

                @if($upcomingEvents->isEmpty())
                    <div class="py-6 rounded-xl border border-dashed border-slate-200 dark:border-white/10 text-center space-y-1">
                        <i data-lucide="calendar-x" class="w-6 h-6 mx-auto text-slate-300 dark:text-slate-600"></i>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Belum ada agenda event mendatang</p>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Event lampau disaring secara otomatis.</p>
                    </div>
                @else
                    <!-- Horizontal Carousel Slider -->
                    <div x-ref="eventSlider"
                        class="flex gap-3.5 overflow-x-auto scroll-smooth snap-x snap-mandatory no-scrollbar pb-1 pt-1 -mx-1 px-1">
                        @foreach($upcomingEvents as $evt)
                            @php
                                $userRegistered = $evt->isUserRegistered(auth()->user());
                                $regOpen = $evt->isRegistrationOpen();
                            @endphp
                            <div class="w-[280px] sm:w-[310px] shrink-0 snap-start bg-slate-50/90 dark:bg-white/[0.03] border border-slate-200/80 dark:border-white/10 rounded-xl p-3.5 flex flex-col justify-between gap-3 hover:border-[#007AFF]/40 dark:hover:border-[#0A84FF]/40 transition">
                                <div class="space-y-2">
                                    @if($evt->banner_url)
                                        <div class="h-28 -mx-3.5 -mt-3.5 mb-2 rounded-t-xl overflow-hidden relative">
                                            <img src="{{ $evt->banner_url }}" alt="{{ $evt->title }}" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                            <div class="absolute top-2 left-2 flex items-center gap-1.5 flex-wrap">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider backdrop-blur-md {{ $evt->isOnline() ? 'bg-indigo-600/90 text-white' : 'bg-emerald-600/90 text-white' }} inline-flex items-center gap-1">
                                                    <i data-lucide="{{ $evt->isOnline() ? 'video' : 'map-pin' }}" class="w-3 h-3"></i>
                                                    <span>{{ $evt->event_type }}</span>
                                                </span>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider backdrop-blur-md {{ $evt->isFree() ? 'bg-teal-600/90 text-white' : 'bg-amber-600/90 text-white' }}">
                                                    {{ $evt->isFree() ? 'Gratis' : $evt->formatted_price }}
                                                </span>
                                            </div>
                                            <span class="absolute bottom-2 right-2 text-[10px] font-mono font-bold bg-black/60 text-white px-2 py-0.5 rounded-md backdrop-blur-xs">
                                                {{ $evt->start_time->translatedFormat('d M Y') }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between gap-1.5 flex-wrap">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $evt->isOnline() ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/80 dark:text-indigo-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300' }} inline-flex items-center gap-1">
                                                    <i data-lucide="{{ $evt->isOnline() ? 'video' : 'map-pin' }}" class="w-3 h-3"></i>
                                                    <span>{{ $evt->event_type }}</span>
                                                </span>
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $evt->isFree() ? 'bg-teal-100 text-teal-700 dark:bg-teal-950/80 dark:text-teal-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/80 dark:text-amber-300' }}">
                                                    {{ $evt->isFree() ? 'Gratis' : $evt->formatted_price }}
                                                </span>
                                            </div>
                                            <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 font-mono">
                                                {{ $evt->start_time->translatedFormat('d M Y') }}
                                            </span>
                                        </div>
                                    @endif

                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug">
                                        {{ $evt->title }}
                                    </h3>

                                    <div class="space-y-1 text-[11px] text-slate-600 dark:text-slate-400 pt-1 border-t border-slate-200/50 dark:border-white/5">
                                        <div class="flex items-center gap-1.5">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                                            <span>{{ $evt->start_time->format('H:i') }} - {{ $evt->end_time->format('H:i') }} WIB</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <i data-lucide="{{ $evt->isOnline() ? 'video' : 'map-pin' }}" class="w-3.5 h-3.5 {{ $evt->isOnline() ? 'text-indigo-500' : 'text-rose-500' }} shrink-0"></i>
                                            <span class="truncate font-medium text-slate-800 dark:text-slate-200">{{ $evt->location_name }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                            <span><strong>{{ $evt->active_registrations_count }}</strong> Pendaftar @if($evt->quota) (Maks {{ $evt->quota }}) @endif</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-1">
                                    @if($userRegistered)
                                        <button type="button" @click="viewTicket('{{ $evt->id }}')"
                                            class="w-full py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer">
                                            <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                                            <span>Tiket &amp; Check-in</span>
                                        </button>
                                    @elseif($regOpen)
                                        <form method="POST" action="{{ route('community.events.register', $evt) }}">
                                            @csrf
                                            <button type="submit"
                                                class="w-full py-2 rounded-xl text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center justify-center gap-1.5 shadow-2xs active:scale-[0.98] cursor-pointer">
                                                <i data-lucide="{{ $evt->isPaid() ? 'credit-card' : 'user-plus' }}" class="w-3.5 h-3.5"></i>
                                                <span>{{ $evt->isFree() ? 'Daftar Event (Gratis)' : 'Beli Tiket (' . $evt->formatted_price . ')' }}</span>
                                            </button>
                                        </form>
                                    @else
                                        <button disabled class="w-full py-2 rounded-xl text-xs font-bold text-slate-400 bg-slate-200/80 dark:bg-white/5 cursor-not-allowed">
                                            Pendaftaran Ditutup
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <!-- 2B. BANNER ACTIVE FILTER (Jika memfilter Topik Tren atau Pencarian) -->
            @if(request('topic') || request('q'))
                <div class="p-3.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <i data-lucide="filter" class="w-4 h-4 text-indigo-600 shrink-0"></i>
                        <span class="text-xs text-indigo-950 dark:text-indigo-200 truncate">
                            Memfilter postingan: <strong>{{ request('topic') ?? request('q') }}</strong>
                        </span>
                    </div>
                    <a href="{{ route('community.index', ['tab' => $activeTab]) }}"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold bg-white dark:bg-slate-900 text-indigo-600 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-50 transition shrink-0">
                        Hapus Filter
                    </a>
                </div>
            @endif

            <!-- 2C. KONTEN TAB NOTIFIKASI -->
            @if($activeTab === 'notifications')
                <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-5 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/5 pb-3">
                        <div class="flex items-center gap-2">
                            <i data-lucide="bell" class="w-4 h-4 text-emerald-600"></i>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Notifikasi Aktivitas</h3>
                        </div>
                        <span class="text-[11px] text-slate-400">Interaksi postingan Anda</span>
                    </div>

                    @forelse($notifications as $notif)
                        <div class="p-3 rounded-xl {{ $notif->is_read ? 'bg-slate-50/60 dark:bg-white/[0.02]' : 'bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/60' }} flex items-start gap-3 transition">
                            <!-- Type Icon -->
                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $notif->type === 'like' ? 'bg-rose-100 text-rose-600 dark:bg-rose-950/60' : ($notif->type === 'repost' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60' : 'bg-sky-100 text-sky-600 dark:bg-sky-950/60') }}">
                                @if($notif->type === 'like')
                                    <i data-lucide="heart" class="w-4 h-4 fill-current"></i>
                                @elseif($notif->type === 'repost')
                                    <i data-lucide="repeat-2" class="w-4 h-4"></i>
                                @else
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1 space-y-0.5">
                                <p class="text-xs text-slate-900 dark:text-white leading-relaxed">
                                    <strong>{{ $notif->actor->name ?? 'Pengguna' }}</strong>
                                    <span class="text-slate-600 dark:text-slate-400">{{ $notif->message }}</span>
                                </p>
                                @if($notif->post)
                                    <a href="#{{ $notif->post_id }}" class="text-[11px] text-slate-500 dark:text-slate-400 italic line-clamp-1 hover:text-[#007AFF] transition">
                                        "{{ $notif->post->content }}"
                                    </a>
                                @endif
                                <span class="text-[10px] text-slate-400 block pt-0.5">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center space-y-2">
                            <i data-lucide="bell-off" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600"></i>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Belum ada notifikasi</h4>
                            <p class="text-[11px] text-slate-400">Interaksi suka, komentar, dan repost dari owner lain akan muncul di sini.</p>
                        </div>
                    @endforelse
                </div>

            <!-- 2D. KONTEN TAB FEED & POSTINGAN SAYA -->
            @else
                <!-- Post Composer (Desain Ramping ala Threads / X.com) -->
                <div id="composer-section"
                    class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-5 shadow-2xs"
                    x-data="{
                        previewUrl: null,
                        fileSelected: false,
                        charCount: 0,
                        isSubmitting: false,
                        onInput(e) {
                            this.charCount = e.target.value.length;
                        }
                    }">

                    <form method="POST" action="{{ route('community.store') }}" enctype="multipart/form-data"
                        @submit="isSubmitting = true" class="space-y-3">
                        @csrf

                        <div class="flex items-start gap-3">
                            <!-- User Avatar -->
                            <div class="w-10 h-10 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-sm flex items-center justify-center shrink-0 shadow-2xs">
                                {{ strtoupper(substr(auth()->user()->name ?? 'O', 0, 1)) }}
                            </div>

                            <!-- Borderless Textarea Input -->
                            <div class="flex-1 min-w-0">
                                <textarea name="content" required rows="3" maxlength="5000" @input="onInput($event)"
                                    placeholder="Apa yang sedang Anda pikirkan untuk bisnis hari ini? Bagikan tips, pertanyaan, atau update toko..."
                                    class="w-full bg-transparent border-0 p-0 text-[14px] sm:text-[15px] text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:outline-none focus:ring-0 resize-none leading-relaxed"></textarea>

                                <!-- Live Attached Image Preview -->
                                <div x-show="previewUrl" x-cloak class="relative rounded-2xl overflow-hidden border border-slate-200 dark:border-white/10 mt-3">
                                    <img :src="previewUrl" alt="Pratinjau Foto" class="max-h-72 w-full object-cover">
                                    <button type="button"
                                        @click="previewUrl = null; fileSelected = false; $refs.imageInput.value = ''"
                                        class="absolute top-2.5 right-2.5 p-1.5 rounded-full bg-black/60 text-white hover:bg-black/80 transition"
                                        title="Hapus Gambar">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Toolbar ala X / Threads -->
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-white/5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <!-- Image Upload Button -->
                                <label class="p-2 rounded-full text-slate-500 hover:text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-white/10 transition cursor-pointer"
                                    title="Sisipkan Foto">
                                    <i data-lucide="image" class="w-4 h-4"></i>
                                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
                                        x-ref="imageInput"
                                        @change="previewUrl = URL.createObjectURL($event.target.files[0]); fileSelected = true"
                                        class="hidden">
                                </label>

                                <!-- Quick Tag Pills -->
                                <button type="button" @click="appendTag('#TipsBisnis')"
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 transition">
                                    #TipsBisnis
                                </button>
                                <button type="button" @click="appendTag('#ManajemenPOS')"
                                    class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 transition">
                                    #ManajemenPOS
                                </button>
                                <button type="button" @click="appendTag('#ArusKas')"
                                    class="hidden sm:inline-block px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 hover:bg-slate-200 dark:bg-white/5 dark:hover:bg-white/10 text-slate-600 dark:text-slate-300 transition">
                                    #ArusKas
                                </button>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="text-[11px] font-mono text-slate-400" x-show="charCount > 0" x-text="charCount + '/5000'"></span>
                                <button type="submit" :disabled="isSubmitting"
                                    class="px-5 py-2 rounded-full text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] active:scale-95 transition disabled:opacity-50 cursor-pointer shadow-xs">
                                    <span x-text="isSubmitting ? 'Memposting...' : 'Posting'"></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Feed Thread List -->
                <div class="space-y-4">
                    @forelse($posts as $post)
                        <article class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-5 shadow-2xs hover:border-slate-300 dark:hover:border-white/20 transition"
                            id="{{ $post->id }}"
                            x-data="postCard('{{ $post->id }}', {{ $post->liked_by_me ? 'true' : 'false' }}, {{ $post->likes_count }}, {{ $post->comments_count }}, {{ $post->reposted_by_me ? 'true' : 'false' }}, {{ $post->reposts_count }})">

                            <!-- Repost Header Indicator (Jika di-repost oleh orang lain atau user) -->
                            @if($post->reposts_count > 0 && ($post->reposted_by_me || $activeTab === 'my_posts'))
                                <div class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 pb-2 border-b border-slate-100 dark:border-white/5 mb-3">
                                    <i data-lucide="repeat-2" class="w-3.5 h-3.5"></i>
                                    <span>Di-repost oleh {{ $post->reposted_by_me ? 'Anda' : ($post->reposts->first()?->user->name ?? 'Pengguna') }}</span>
                                </div>
                            @endif

                            <div class="flex items-start gap-3">
                                <!-- Author Avatar -->
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-white/10 text-slate-800 dark:text-white font-bold text-sm flex items-center justify-center shrink-0 border border-slate-200/60 dark:border-white/10">
                                    {{ strtoupper(substr($post->owner->name ?? 'O', 0, 1)) }}
                                </div>

                                <!-- Post Details & Content -->
                                <div class="flex-1 min-w-0 space-y-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                            <span class="text-sm font-bold text-slate-900 dark:text-white truncate">
                                                {{ $post->owner->name ?? 'Owner Cooca' }}
                                            </span>
                                            <span class="text-[11px] font-semibold text-slate-400 truncate">
                                                · {{ $post->business->name ?? 'UMKM' }}
                                            </span>
                                            <span class="text-[11px] text-slate-400">
                                                · {{ $post->created_at->diffForHumans(null, true) }}
                                            </span>
                                        </div>

                                        <!-- Share / Delete Menu -->
                                        <div class="flex items-center gap-1 shrink-0">
                                            <button type="button" @click="copyLink('{{ $post->id }}')"
                                                class="p-1 rounded-full text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/10 transition"
                                                title="Salin Tautan">
                                                <i data-lucide="share-2" class="w-3.5 h-3.5" x-show="copiedPostId !== '{{ $post->id }}'"></i>
                                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-500" x-show="copiedPostId === '{{ $post->id }}'" x-cloak></i>
                                            </button>

                                            @if ((string) $post->owner_id === (string) auth()->id())
                                                <form method="POST" action="{{ route('community.destroy', $post) }}"
                                                    onsubmit="return confirm('Hapus postingan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="p-1 rounded-full text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition"
                                                        title="Hapus Postingan">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Post Content Text (dengan Link Hashtag Otomatis) -->
                                    <div class="text-[14px] sm:text-[15px] text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-line font-sans">
                                        {!! preg_replace('/#([a-zA-Z0-9_]+)/u', '<a href="' . route('community.index') . '?topic=%23$1" class="text-[#007AFF] hover:underline font-semibold">#$1</a>', e($post->content)) !!}
                                    </div>

                                    <!-- Attached Image (Jika ada) -->
                                    @if ($post->image_url)
                                        <div class="rounded-2xl overflow-hidden border border-slate-200/80 dark:border-white/10 mt-2.5 max-h-[460px]">
                                            <img src="{{ $post->image_url }}" alt="Foto postingan"
                                                class="w-full object-cover hover:scale-[1.01] transition duration-300 cursor-pointer"
                                                onclick="window.open('{{ $post->image_url }}', '_blank')">
                                        </div>
                                    @endif

                                    <!-- Threads/X Action Bar (Reply, Repost, Like, Share) -->
                                    <div class="flex items-center justify-between pt-2 text-slate-500 dark:text-slate-400 text-xs max-w-md">
                                        <!-- Reply / Comment Action -->
                                        <button type="button" @click="commentsOpen = !commentsOpen"
                                            class="inline-flex items-center gap-1.5 hover:text-[#007AFF] transition cursor-pointer group">
                                            <div class="p-1.5 rounded-full group-hover:bg-[#007AFF]/10 transition">
                                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                            </div>
                                            <span x-text="commentsCount"></span>
                                        </button>

                                        <!-- Repost Action (Twitter-Style) -->
                                        <button type="button" @click="toggleRepost()"
                                            class="inline-flex items-center gap-1.5 hover:text-emerald-500 transition cursor-pointer group"
                                            :class="reposted ? 'text-emerald-500 font-bold' : ''">
                                            <div class="p-1.5 rounded-full group-hover:bg-emerald-500/10 transition">
                                                <i data-lucide="repeat-2" class="w-4 h-4 transition active:rotate-180"
                                                    :class="reposted ? 'text-emerald-500' : ''"></i>
                                            </div>
                                            <span x-text="repostsCount"></span>
                                        </button>

                                        <!-- Like Action -->
                                        <button type="button" @click="toggleLike()"
                                            class="inline-flex items-center gap-1.5 hover:text-rose-500 transition cursor-pointer group"
                                            :class="liked ? 'text-rose-500 font-bold' : ''">
                                            <div class="p-1.5 rounded-full group-hover:bg-rose-500/10 transition">
                                                <i data-lucide="heart" class="w-4 h-4 transition active:scale-125"
                                                    :class="liked ? 'fill-rose-500 text-rose-500' : ''"></i>
                                            </div>
                                            <span x-text="likesCount"></span>
                                        </button>

                                        <!-- Share Action -->
                                        <button type="button" @click="copyLink('{{ $post->id }}')"
                                            class="inline-flex items-center gap-1.5 hover:text-slate-700 dark:hover:text-white transition cursor-pointer group"
                                            title="Salin Tautan">
                                            <div class="p-1.5 rounded-full group-hover:bg-slate-100 dark:group-hover:bg-white/10 transition">
                                                <i data-lucide="share" class="w-4 h-4"></i>
                                            </div>
                                        </button>
                                    </div>

                                    <!-- Inline Reply Thread Box -->
                                    <div x-show="commentsOpen" x-cloak
                                        class="pt-3 mt-2 border-t border-slate-100 dark:border-white/5 space-y-3">

                                        <!-- Comments Stream -->
                                        <div class="space-y-2.5">
                                            <template x-for="c in clientComments" :key="c.id">
                                                <div class="flex items-start gap-2.5">
                                                    <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center shrink-0">
                                                        <span x-text="c.user.name.charAt(0).toUpperCase()"></span>
                                                    </div>
                                                    <div class="flex-1 bg-slate-50 dark:bg-white/[0.04] rounded-xl p-2.5 text-xs space-y-0.5">
                                                        <div class="flex items-center justify-between gap-1">
                                                            <span class="font-bold text-slate-900 dark:text-white" x-text="c.user.name"></span>
                                                            <span class="text-[10px] text-slate-400" x-text="c.created_at"></span>
                                                        </div>
                                                        <p class="text-slate-700 dark:text-slate-300" x-text="c.content"></p>
                                                    </div>
                                                </div>
                                            </template>

                                            @forelse($post->comments as $comment)
                                                <div class="flex items-start gap-2.5">
                                                    <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-white/10 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center shrink-0">
                                                        {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div class="flex-1 bg-slate-50 dark:bg-white/[0.04] rounded-xl p-2.5 text-xs space-y-0.5">
                                                        <div class="flex items-center justify-between gap-1">
                                                            <span class="font-bold text-slate-900 dark:text-white">{{ $comment->user->name ?? 'Owner' }}</span>
                                                            <span class="text-[10px] text-slate-400 font-mono">{{ $comment->created_at->diffForHumans(null, true) }}</span>
                                                        </div>
                                                        <p class="text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ $comment->content }}</p>
                                                    </div>
                                                </div>
                                            @empty
                                                <div x-show="clientComments.length === 0" class="text-center py-2 text-xs text-slate-400 italic">
                                                    Belum ada balasan. Jadilah yang pertama membalas!
                                                </div>
                                            @endforelse
                                        </div>

                                        <!-- Reply Input -->
                                        <form class="flex items-center gap-2 pt-1" @submit.prevent="submitComment($el)">
                                            @csrf
                                            <input type="text" name="content" required maxlength="1000"
                                                placeholder="Tulis balasan Anda..."
                                                class="flex-1 bg-slate-50 dark:bg-white/[0.05] border border-slate-200 dark:border-white/10 rounded-full px-4 py-1.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-[#007AFF]">
                                            <button type="submit"
                                                class="px-4 py-1.5 rounded-full bg-[#007AFF] hover:bg-[#0062CC] text-white text-xs font-bold transition cursor-pointer shrink-0">
                                                Balas
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-12 text-center space-y-2">
                            <i data-lucide="messages-square" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600"></i>
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                {{ $activeTab === 'my_posts' ? 'Anda belum memiliki postingan atau repost' : 'Belum ada postingan komunitas' }}
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                {{ $activeTab === 'my_posts' ? 'Tuliskan insight bisnis pertama Anda melalui form postingan di atas.' : 'Mulai diskusi pertama Anda bersama ribuan pemilik usaha Cooca.' }}
                            </p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($posts->hasPages())
                    <div class="pt-2">
                        {{ $posts->links() }}
                    </div>
                @endif
            @endif
        </main>

        <!-- ========================================== -->
        <!-- 3. SIDEBAR KANAN (Top 5 Tren 24 Jam & Ringkasan ala Twitter) -->
        <!-- ========================================== -->
        <aside class="lg:col-span-3 space-y-5 lg:sticky lg:top-20">
            <!-- Search Bar Komunitas -->
            <form method="GET" action="{{ route('community.index') }}" class="relative">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari topik diskusi..."
                    class="w-full h-10 pl-9 pr-3.5 rounded-2xl text-xs bg-white dark:bg-[#1C1C1E] border border-slate-200/80 dark:border-white/10 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-[#007AFF] shadow-2xs">
            </form>

            <!-- WIDGET TOP 5 TREN 24 JAM (Twitter / X.com Style) -->
            <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 sm:p-5 shadow-2xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/5 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white leading-tight">
                                Tren 24 Jam
                            </h3>
                            <p class="text-[10px] text-slate-400">Topik terhangat saat ini</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400">
                        Top 5
                    </span>
                </div>

                <div class="space-y-1 divide-y divide-slate-100 dark:divide-white/5 -mx-1 px-1">
                    @foreach($trendingTopics as $idx => $trend)
                        <a href="{{ route('community.index', ['topic' => $trend['topic'], 'tab' => 'feed']) }}"
                            class="block py-2.5 hover:bg-slate-50 dark:hover:bg-white/[0.03] rounded-xl px-2.5 transition group">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-medium text-slate-400">
                                    {{ $idx + 1 }} · {{ $trend['category'] }}
                                </span>
                                <i data-lucide="chevron-right" class="w-3 h-3 text-slate-300 group-hover:text-[#007AFF] group-hover:translate-x-0.5 transition"></i>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-[#007AFF] transition leading-snug pt-0.5">
                                {{ $trend['topic'] }}
                            </h4>
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                {{ $trend['count'] }} diskusi terkait
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Widget Ringkas: Info Agenda Terdekat -->
            @if($upcomingEvents->isNotEmpty())
                <div class="bg-white dark:bg-[#1C1C1E] rounded-2xl border border-slate-200/80 dark:border-white/10 p-4 shadow-2xs space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span>Event Paling Dekat</span>
                        </h4>
                        <a href="{{ route('community.events.index') }}" class="text-[11px] text-[#007AFF] font-bold hover:underline">Semua</a>
                    </div>
                    @php $firstEvt = $upcomingEvents->first(); @endphp
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.03] border border-slate-200/60 dark:border-white/10 space-y-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $firstEvt->isPaid() ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $firstEvt->isPaid() ? $firstEvt->formatted_price : 'Gratis' }}
                        </span>
                        <h5 class="text-xs font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug">
                            {{ $firstEvt->title }}
                        </h5>
                        <p class="text-[10px] text-slate-500 flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3 h-3 text-emerald-600"></i>
                            <span>{{ $firstEvt->start_time->translatedFormat('d M Y') }}</span>
                        </p>
                        <a href="{{ route('community.events.index') }}"
                            class="w-full py-1.5 rounded-lg text-xs font-bold text-white bg-slate-900 dark:bg-white dark:text-slate-900 flex items-center justify-center transition">
                            Lihat Tiket
                        </a>
                    </div>
                </div>
            @endif

            <!-- Footer Hak Cipta Ringkas -->
            <div class="px-2 text-[11px] text-slate-400 space-y-1">
                <p>&copy; 2026 Cooca Indonesia. Wadah Kolaborasi UMKM.</p>
                <div class="flex items-center gap-2 text-[10px]">
                    <a href="{{ route('community.events.index') }}" class="hover:underline">Agenda Event</a>
                    <span>·</span>
                    <a href="{{ route('billing.limits') }}" class="hover:underline">Batas &amp; Kuota</a>
                </div>
            </div>
        </aside>

    </div>

    <!-- ========================================== -->
    <!-- 4. MODAL TIKET DIGITAL PESERTA (Apple HIG) -->
    <!-- ========================================== -->
    <div x-show="selectedTicket" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs transition-opacity"
        @keydown.escape.window="selectedTicket = null">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
            @click.outside="selectedTicket = null">

            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                        <i data-lucide="ticket" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tiket Digital Peserta</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono" x-text="selectedTicket?.ticket_code"></p>
                    </div>
                </div>
                <button type="button" @click="selectedTicket = null"
                    class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 space-y-4 overflow-y-auto">
                <!-- Status Badge -->
                <div class="text-center">
                    <template x-if="selectedTicket?.is_checked_in">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            <span>Sudah Hadir (<span x-text="selectedTicket?.checkin_method?.toUpperCase()"></span>)</span>
                        </div>
                    </template>
                    <template x-if="!selectedTicket?.is_checked_in">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            <span>Terdaftar (Menunggu Presensi)</span>
                        </div>
                    </template>
                </div>

                <!-- Event Brief -->
                <div class="bg-slate-50 dark:bg-white/[0.04] p-3.5 rounded-2xl border border-slate-200/80 dark:border-white/10 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase"
                            :class="selectedTicket?.is_online ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/80 dark:text-indigo-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300'"
                            x-text="selectedTicket?.event_type?.toUpperCase()">
                        </span>
                        <span class="text-[11px] font-semibold"
                            :class="selectedTicket?.is_paid ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'"
                            x-text="selectedTicket?.is_paid ? 'Tiket: ' + selectedTicket?.formatted_price : 'Tiket Gratis'">
                        </span>
                    </div>
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white" x-text="selectedTicket?.event_title"></h4>
                    <div class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span x-text="selectedTicket?.start_time"></span>
                    </div>
                    <div class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500"></i>
                        <span x-text="selectedTicket?.location_name"></span>
                    </div>

                    <!-- Online Meeting URL Banner -->
                    <template x-if="selectedTicket?.is_online && selectedTicket?.online_meeting_url">
                        <div class="p-3 rounded-xl bg-indigo-50/90 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800/80 space-y-2 pt-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-indigo-900 dark:text-indigo-200 text-xs flex items-center gap-1.5">
                                    <i data-lucide="video" class="w-3.5 h-3.5 text-indigo-600"></i>
                                    <span>Tautan Meeting Online</span>
                                </span>
                                <span class="text-[10px] font-semibold text-indigo-600 dark:text-indigo-400">Live Streaming</span>
                            </div>
                            <a :href="selectedTicket?.online_meeting_url" target="_blank" rel="noopener noreferrer"
                                class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] transition flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                <span>Buka Ruang Meeting (Zoom/GMeet)</span>
                            </a>
                        </div>
                    </template>
                </div>

                <!-- Barcode & QR Code Section -->
                <div class="bg-white dark:bg-black/30 p-4 rounded-2xl border border-slate-200 dark:border-white/10 text-center space-y-3"
                    x-data="{ ticketMode: 'qr' }">
                    <div class="flex items-center justify-center p-0.5 rounded-xl bg-slate-100 dark:bg-white/10 max-w-[200px] mx-auto text-xs font-semibold">
                        <button type="button" @click="ticketMode = 'qr'"
                            :class="ticketMode === 'qr' ? 'bg-white dark:bg-white/20 text-slate-900 dark:text-white shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'"
                            class="flex-1 py-1 rounded-lg transition cursor-pointer">
                            QR Code
                        </button>
                        <button type="button" @click="ticketMode = 'barcode'"
                            :class="ticketMode === 'barcode' ? 'bg-white dark:bg-white/20 text-slate-900 dark:text-white shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'"
                            class="flex-1 py-1 rounded-lg transition cursor-pointer">
                            Barcode 1D
                        </button>
                    </div>

                    <!-- QR Code View -->
                    <div x-show="ticketMode === 'qr'" class="space-y-1">
                        <div class="w-44 h-44 mx-auto p-2 bg-white rounded-2xl border border-slate-200 shadow-xs flex items-center justify-center overflow-hidden [&>svg]:w-full [&>svg]:h-full [&>svg]:max-w-full [&>svg]:block"
                            x-html="selectedTicket?.qr_code_svg">
                        </div>
                        <p class="text-[11px] text-slate-400">Tunjukkan QR code ini ke scanner panitia</p>
                    </div>

                    <!-- Barcode 1D View -->
                    <div x-show="ticketMode === 'barcode'" x-cloak class="space-y-1">
                        <div class="max-w-[260px] mx-auto p-2.5 bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden flex items-center justify-center [&>svg]:w-full [&>svg]:h-auto [&>svg]:max-w-full [&>svg]:block"
                            x-html="selectedTicket?.barcode_svg">
                        </div>
                        <p class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300" x-text="selectedTicket?.ticket_code"></p>
                    </div>
                </div>

                <!-- Self Presensi Check-in Methods (Hanya jika belum hadir) -->
                <template x-if="!selectedTicket?.is_checked_in">
                    <div class="space-y-2 pt-1">
                        <h5 class="text-xs font-bold text-slate-900 dark:text-white">Presensi Mandiri di Lokasi:</h5>

                        <!-- Metode 1: GPS Geolocation -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-800 dark:text-slate-200">1. Presensi via Lokasi (GPS)</span>
                                <span class="text-[10px] text-emerald-600 font-mono">Radius <span x-text="selectedTicket?.checkin_radius_meters || 200"></span>m</span>
                            </div>
                            <button type="button" @click="checkinGps()" :disabled="locCheckinLoading"
                                class="w-full py-2 rounded-xl text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center justify-center gap-2 cursor-pointer shadow-xs disabled:opacity-50">
                                <i data-lucide="locate-fixed" class="w-4 h-4" x-show="!locCheckinLoading"></i>
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="locCheckinLoading" x-cloak></i>
                                <span x-text="locCheckinLoading ? 'Mengecek Koordinat...' : 'Presensi Lokasi Saya Sekarang'"></span>
                            </button>
                            <template x-if="locCheckinMsg">
                                <p class="text-[11px] font-medium text-center"
                                    :class="locCheckinMsg.type === 'success' ? 'text-emerald-600' : 'text-rose-600'"
                                    x-text="locCheckinMsg.text"></p>
                            </template>
                        </div>

                        <!-- Metode 2: Barcode / Kode QR Venue -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-white/[0.04] border border-slate-200 dark:border-white/10 space-y-1.5">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <i data-lucide="scan-barcode" class="w-4 h-4 text-slate-500"></i>
                                <span>2. Ketik Kode Venue</span>
                            </span>
                            <div class="flex items-center gap-2">
                                <input type="text" x-model="barcodeInput" placeholder="Ketik kode (COOCA-EVT-...)"
                                    class="flex-1 bg-white dark:bg-[#1C1C1E] border border-slate-200 dark:border-white/10 rounded-xl px-3 py-1.5 text-xs text-slate-900 dark:text-white uppercase font-mono">
                                <button type="button" @click="checkinBarcodeSubmit()" :disabled="barcodeLoading || !barcodeInput"
                                    class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 text-xs font-bold transition disabled:opacity-50 cursor-pointer">
                                    <span x-text="barcodeLoading ? 'Cek...' : 'Validasi'"></span>
                                </button>
                            </div>
                            <template x-if="barcodeMsg">
                                <p class="text-[11px] font-medium text-center"
                                    :class="barcodeMsg.type === 'success' ? 'text-emerald-600' : 'text-rose-600'"
                                    x-text="barcodeMsg.text"></p>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-white/10 flex items-center justify-between">
                <template x-if="!selectedTicket?.is_checked_in">
                    <form method="POST" :action="'/community/events/' + selectedTicket?.event_id + '/cancel'"
                        onsubmit="return confirm('Batalkan pendaftaran event ini?')">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">
                            Batalkan Pendaftaran
                        </button>
                    </form>
                </template>
                <div x-show="selectedTicket?.is_checked_in"></div>
                <button type="button" @click="selectedTicket = null"
                    class="px-5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-white/10 dark:hover:bg-white/15 transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function postCard(postId, initialLiked, initialLikesCount, initialCommentsCount, initialReposted, initialRepostsCount) {
        return {
            liked: initialLiked,
            likesCount: initialLikesCount,
            reposted: initialReposted,
            repostsCount: initialRepostsCount,
            commentsCount: initialCommentsCount,
            commentsOpen: false,
            clientComments: [],
            csrfToken: document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '',

            async toggleLike() {
                try {
                    const res = await fetch('/community/' + postId + '/like', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data.success) {
                        this.liked = data.liked;
                        this.likesCount = data.likes_count;
                    }
                } catch (e) {
                    console.error('Like error:', e);
                }
            },

            async toggleRepost() {
                try {
                    const res = await fetch('/community/' + postId + '/repost', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data.success) {
                        this.reposted = data.reposted;
                        this.repostsCount = data.reposts_count;
                    }
                } catch (e) {
                    console.error('Repost error:', e);
                }
            },

            async submitComment(form) {
                const input = form.querySelector('input[name="content"]');
                const content = input.value.trim();
                if (!content) return;

                try {
                    const res = await fetch('/community/' + postId + '/comment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ content: content })
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data.success) {
                        input.value = '';
                        if (data.comment) {
                            this.clientComments.push(data.comment);
                            this.commentsCount = data.comments_count;
                        } else {
                            window.location.reload();
                        }
                    }
                } catch (e) {
                    console.error('Comment error:', e);
                }
            }
        };
    }
</script>
@endpush
