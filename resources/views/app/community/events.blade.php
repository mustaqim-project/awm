@extends('layouts.app', [
    'title' => 'Agenda Event Komunitas - Cooca',
    'headerTitle' => 'Agenda Event Komunitas',
    'headerSubtitle' => 'Seminar bisnis, pelatihan kasir POS, dan meetup eksklusif untuk seluruh pengguna Cooca',
])

@section('content')
<div class="space-y-6 pb-16" x-data="{
    activeTab: 'upcoming',
    selectedTicket: null,
    ticketLoading: false,
    ticketError: null,
    locCheckinLoading: false,
    locCheckinMsg: null,
    barcodeInput: '',
    barcodeLoading: false,
    barcodeMsg: null,

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
                if (data.payment_url) {
                    if (confirm((data.message || 'Pembayaran tiket belum selesai.') + '\n\nBuka halaman pembayaran QRIS sekarang?')) {
                        window.location.href = data.payment_url;
                        return;
                    }
                }
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
            this.locCheckinMsg = { type: 'error', text: 'Izin lokasi GPS ditolak atau tidak tersedia: ' + err.message };
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
}">
    <!-- 0. Breadcrumb Bar -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 print:hidden" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
            <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
            <span>Dashboard</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <a href="{{ route('community.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors flex items-center gap-1.5">
            <i data-lucide="users" class="w-3.5 h-3.5"></i>
            <span>Komunitas</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-600"></i>
        <span class="text-slate-900 dark:text-white font-bold flex items-center gap-1.5">
            <span>Agenda Event</span>
        </span>
    </nav>

    <!-- 1. Header Banner -->
    <div class="bg-white dark:bg-slate-900/90 p-5 sm:p-6 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors">
        <div class="space-y-1 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="rounded-full px-2.5 py-0.5 text-[10px] sm:text-xs font-bold border inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border-emerald-200/90 dark:border-emerald-800/90">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>Terbuka Untuk Semua Paket Langganan</span>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Agenda &amp; Pelatihan Komunitas
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                Tingkatkan wawasan bisnis, strategi penjualan POS, dan perluas relasi dengan sesama pelaku usaha melalui seminar dan workshop resmi Cooca.
            </p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <a href="{{ route('community.index') }}"
                class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/80 transition flex items-center justify-center gap-1.5 flex-1 sm:flex-none">
                <i data-lucide="messages-square" class="w-4 h-4"></i>
                <span>Feed Komunitas</span>
            </a>
        </div>
    </div>

    <!-- 2. Segmented Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
        <button type="button" @click="activeTab = 'upcoming'"
            :class="activeTab === 'upcoming' ? 'bg-emerald-600 text-white shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium'"
            class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
            <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
            <span>Event Mendatang</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 font-mono">{{ $upcomingEvents->total() }}</span>
        </button>

        <button type="button" @click="activeTab = 'my_tickets'"
            :class="activeTab === 'my_tickets' ? 'bg-emerald-600 text-white shadow-2xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 font-medium'"
            class="px-4 py-2 rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
            <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
            <span>Tiket &amp; Pendaftaran Saya</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20 font-mono">{{ count($myRegistrations) }}</span>
        </button>
    </div>

    <!-- 3. Tab: Event Mendatang Grid -->
    <div x-show="activeTab === 'upcoming'" class="space-y-6">
        @if($upcomingEvents->isEmpty())
            <div class="bg-white dark:bg-slate-900/90 p-12 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 text-center space-y-2">
                <i data-lucide="calendar-x" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600"></i>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Belum Ada Event Mendatang</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    Saat ini belum ada agenda baru yang dijadwalkan. Agenda seminar dan workshop terbaru akan segera hadir.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($upcomingEvents as $evt)
                    @php
                        $userReg = $evt->getUserRegistration(auth()->user());
                        $userRegistered = $userReg !== null && $userReg->status !== \App\Models\CommunityEventRegistration::STATUS_CANCELLED;
                        $regOpen = $evt->isRegistrationOpen();
                    @endphp
                    <div class="bg-white dark:bg-slate-900/90 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs overflow-hidden flex flex-col justify-between hover:border-emerald-500/40 transition">
                        <div>
                            <!-- Banner Image -->
                            <div class="h-40 w-full bg-slate-100 dark:bg-slate-800 relative overflow-hidden">
                                @if($evt->banner_url)
                                    <img src="{{ $evt->banner_url }}" alt="{{ $evt->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-slate-100 via-slate-50 to-slate-200 dark:from-slate-800 dark:via-slate-800/80 dark:to-slate-900 flex flex-col items-center justify-center p-4 text-center">
                                        <div class="w-11 h-11 rounded-2xl bg-white dark:bg-white/10 shadow-xs flex items-center justify-center text-[#007AFF] mb-1.5">
                                            <i data-lucide="calendar-days" class="w-5 h-5"></i>
                                        </div>
                                        <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Cooca Community Event</span>
                                    </div>
                                @endif
                                <div class="absolute top-3 left-3 flex flex-wrap items-center gap-1.5">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider backdrop-blur-md {{ $evt->event_type === 'online' ? 'bg-indigo-600/95 text-white' : 'bg-emerald-600/95 text-white' }} shadow-xs inline-flex items-center gap-1">
                                        <i data-lucide="{{ $evt->event_type === 'online' ? 'video' : 'map-pin' }}" class="w-3 h-3"></i>
                                        <span>{{ $evt->event_type }}</span>
                                    </span>
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider backdrop-blur-md {{ $evt->isPaid() ? 'bg-amber-600/95 text-white' : 'bg-emerald-500/95 text-white' }} shadow-xs">
                                        {{ $evt->isPaid() ? 'Berbayar • ' . $evt->formatted_price : 'Gratis' }}
                                    </span>
                                </div>
                                <div class="absolute bottom-3 right-3">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-black/60 text-white backdrop-blur-xs">
                                        {{ $evt->start_time->translatedFormat('d M Y') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-5 space-y-3">
                                <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug">
                                    {{ $evt->title }}
                                </h3>

                                <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="clock" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                                        <span>{{ $evt->start_time->format('H:i') }} - {{ $evt->end_time->format('H:i') }} WIB</span>
                                    </div>

                                    <div class="flex items-start gap-2">
                                        <i data-lucide="{{ $evt->event_type === 'online' ? 'video' : 'map-pin' }}" class="w-3.5 h-3.5 text-rose-500 shrink-0 mt-0.5"></i>
                                        <span class="line-clamp-1 font-medium text-slate-800 dark:text-slate-200">
                                            {{ $evt->location_name }}
                                            @if($evt->event_type === 'online')
                                                <span class="text-[11px] text-indigo-600 dark:text-indigo-400 font-normal">(Online Meeting)</span>
                                            @endif
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <i data-lucide="tag" class="w-3.5 h-3.5 {{ $evt->isPaid() ? 'text-amber-500' : 'text-emerald-500' }} shrink-0"></i>
                                        <span class="font-semibold {{ $evt->isPaid() ? 'text-amber-700 dark:text-amber-300' : 'text-emerald-700 dark:text-emerald-300' }}">
                                            Biaya Tiket: {{ $evt->isPaid() ? $evt->formatted_price : 'Gratis (Rp 0)' }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <i data-lucide="users" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                        <span><strong>{{ $evt->active_registrations_count }}</strong> pendaftar @if($evt->quota) / {{ $evt->quota }} kursi @endif</span>
                                    </div>

                                    <div class="flex items-center gap-2 text-[11px] text-amber-600 dark:text-amber-400 pt-1">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Batas Daftar: {{ $evt->registration_deadline->translatedFormat('d M Y, H:i') }}</span>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed pt-2 border-t border-slate-100 dark:border-slate-800/80">
                                    {{ $evt->description }}
                                </p>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="p-5 pt-0">
                            @if($userRegistered)
                                @if($userReg && $userReg->isPendingPayment())
                                    <a href="{{ route('community.events.payment.show', $userReg) }}"
                                        class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 active:scale-[0.98] transition flex items-center justify-center gap-1.5 shadow-sm shadow-amber-600/20">
                                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                                        <span>Lanjutkan Bayar QRIS ({{ $userReg->getFormattedAmountPaid() }})</span>
                                    </a>
                                @else
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="viewTicket('{{ $evt->id }}')"
                                            class="flex-1 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] transition flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                                            <i data-lucide="ticket" class="w-4 h-4"></i>
                                            <span>Lihat Tiket &amp; Check-in</span>
                                        </button>
                                    </div>
                                @endif
                            @elseif($regOpen)
                                <form method="POST" action="{{ route('community.events.register', $evt) }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full py-2.5 rounded-xl text-xs font-bold text-white {{ $evt->isPaid() ? 'bg-amber-600 hover:bg-amber-500 shadow-amber-600/25' : 'bg-[#007AFF] hover:bg-[#0062CC] shadow-[#007AFF]/25' }} active:scale-[0.98] transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                                        <i data-lucide="{{ $evt->isPaid() ? 'credit-card' : 'user-plus' }}" class="w-4 h-4"></i>
                                        <span>{{ $evt->isPaid() ? 'Beli Tiket (' . $evt->formatted_price . ')' : 'Daftar Sekarang (Gratis)' }}</span>
                                    </button>
                                </form>
                            @else
                                <button disabled class="w-full py-2.5 rounded-xl text-xs font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 cursor-not-allowed">
                                    Pendaftaran Ditutup
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($upcomingEvents->hasPages())
                <div class="pt-4">
                    {{ $upcomingEvents->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- 4. Tab: Tiket & Pendaftaran Saya -->
    <div x-show="activeTab === 'my_tickets'" class="space-y-4">
        @if(empty($myRegistrations) || count($myRegistrations) === 0)
            <div class="bg-white dark:bg-slate-900/90 p-12 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 text-center space-y-2">
                <i data-lucide="ticket" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600"></i>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Belum Ada Tiket Event</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                    Anda belum mendaftar ke event apa pun. Buka tab "Event Mendatang" untuk mendaftar ke seminar dan workshop yang tersedia.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($myRegistrations as $reg)
                    <div class="bg-white dark:bg-slate-900/90 p-5 rounded-2xl border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex flex-col justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                    {{ $reg->ticket_code }}
                                </span>
                                @if($reg->isCheckedIn())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200">
                                        <i data-lucide="check" class="w-3 h-3 mr-1"></i> Hadir
                                    </span>
                                @elseif($reg->status === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700">
                                        Dibatalkan
                                    </span>
                                @elseif($reg->isPendingPayment())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                        <i data-lucide="clock" class="w-3 h-3 mr-1 text-amber-600"></i> Menunggu Bayar QRIS
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 border border-blue-200">
                                        <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i> Tiket Siap (Lunas)
                                    </span>
                                @endif
                            </div>

                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ $reg->event->title ?? 'Event Komunitas' }}
                            </h4>

                            <div class="text-xs text-slate-600 dark:text-slate-400 space-y-1">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                    <span>{{ $reg->event?->start_time?->translatedFormat('l, d F Y • H:i') }} WIB</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i>
                                    <span class="line-clamp-1">{{ $reg->event?->location_name }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 pt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $reg->event?->event_type === 'online' ? 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400' }}">
                                        <i data-lucide="{{ $reg->event?->event_type === 'online' ? 'video' : 'map-pin' }}" class="w-3 h-3 mr-1"></i>
                                        {{ strtoupper($reg->event?->event_type ?? 'offline') }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ ($reg->amount_paid > 0 || $reg->event?->isPaid()) ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                        {{ ($reg->amount_paid > 0 || $reg->event?->isPaid()) ? 'Berbayar (' . ($reg->event?->formatted_price ?? 'Rp ' . number_format((float)$reg->amount_paid, 0, ',', '.')) . ')' : 'Gratis' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            @if($reg->status !== 'cancelled')
                                @if($reg->isPendingPayment())
                                    <a href="{{ route('community.events.payment.show', $reg) }}"
                                        class="flex-1 py-2 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 transition flex items-center justify-center gap-1.5 shadow-xs">
                                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                                        <span>Bayar via QRIS</span>
                                    </a>
                                @else
                                    <button type="button" @click="viewTicket('{{ $reg->event_id }}')"
                                        class="flex-1 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i data-lucide="scan-barcode" class="w-4 h-4"></i>
                                        <span>Buka Tiket Digital</span>
                                    </button>
                                @endif
                                @if(! $reg->isCheckedIn())
                                    <form method="POST" action="{{ route('community.events.cancel', $reg->event) }}" onsubmit="return confirm('{{ $reg->isPaid() ? 'Batalkan pendaftaran event ini? Tiket berbayar yang sudah dibatalkan tidak otomatis di-refund.' : 'Batalkan pendaftaran event ini?' }}')">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer">
                                            Batal
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 5. Apple HIG Modal: Tiket Digital & Check-in Tools -->
    <template x-teleport="body">
        <div x-show="selectedTicket" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 overflow-y-auto">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                x-show="selectedTicket"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="selectedTicket = null"
                aria-hidden="true"></div>

            <div class="relative z-10 bg-white dark:bg-slate-900 w-full max-w-md rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden flex flex-col max-h-[90vh] my-auto"
                @click.away="selectedTicket = null"
                @keydown.escape.window="selectedTicket = null"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-ticket-events-title"
                x-show="selectedTicket"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                x-data="{ ticketMode: 'qr' }">
                
                <!-- Modal Header -->
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                            <i data-lucide="ticket" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h3 id="modal-ticket-events-title" class="text-sm font-bold text-slate-900 dark:text-white leading-tight">Tiket Digital Peserta</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-mono" x-text="selectedTicket?.ticket_code"></p>
                        </div>
                    </div>
                    <button type="button" @click="selectedTicket = null"
                        class="min-w-[44px] min-h-[44px] w-11 h-11 rounded-full text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition cursor-pointer"
                        aria-label="Tutup tiket">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

            <!-- Modal Body (Scrollable, Clean Spacing, Zero Overlap) -->
            <div class="p-5 sm:p-6 space-y-4 overflow-y-auto">
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
                            <span>Terdaftar (Menunggu Check-in)</span>
                        </div>
                    </template>
                </div>

                <!-- Event Details Brief -->
                <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 space-y-2.5 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase"
                            :class="selectedTicket?.is_online ? 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400'"
                            x-text="selectedTicket?.event_type?.toUpperCase()">
                        </span>
                        <span class="text-[11px] font-semibold"
                            :class="selectedTicket?.is_paid ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'"
                            x-text="selectedTicket?.is_paid ? 'Tiket: ' + selectedTicket?.formatted_price : 'Tiket Gratis'">
                        </span>
                    </div>

                    <h4 class="font-bold text-sm text-slate-900 dark:text-white leading-snug" x-text="selectedTicket?.event_title"></h4>
                    
                    <div class="space-y-1 text-slate-600 dark:text-slate-400">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                            <span x-text="selectedTicket?.start_time"></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i>
                            <span class="truncate" x-text="selectedTicket?.location_name"></span>
                        </div>
                    </div>

                    <!-- Online Meeting Link Action (Only for Online Events) -->
                    <template x-if="selectedTicket?.is_online && selectedTicket?.online_meeting_url">
                        <div class="p-3 rounded-xl bg-indigo-50/90 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800/80 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-indigo-900 dark:text-indigo-200 text-xs flex items-center gap-1.5">
                                    <i data-lucide="video" class="w-3.5 h-3.5 text-indigo-600"></i>
                                    <span>Tautan Meeting Online</span>
                                </span>
                                <span class="text-[10px] font-semibold text-indigo-600 dark:text-indigo-400">Live Streaming</span>
                            </div>
                            <p class="text-[11px] text-slate-600 dark:text-slate-400">
                                Gunakan tautan resmi di bawah ini untuk bergabung ke ruang pertemuan (Zoom / GMeet):
                            </p>
                            <a :href="selectedTicket?.online_meeting_url" target="_blank" rel="noopener noreferrer"
                                class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 active:scale-[0.98] transition flex items-center justify-center gap-2 shadow-xs cursor-pointer">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                <span>Buka Ruang Meeting Sekarang</span>
                            </a>
                        </div>
                    </template>

                    <div class="text-[11px] text-slate-500 dark:text-slate-400 pt-1.5 border-t border-slate-200/60 dark:border-slate-800/60 flex items-center justify-between">
                        <span>Peserta: <strong class="text-slate-900 dark:text-white" x-text="selectedTicket?.participant_name"></strong></span>
                        <span class="font-medium truncate max-w-[150px]" x-text="selectedTicket?.business_name"></span>
                    </div>
                </div>

                <!-- Barcode & QR Code Section (Clean Segmented Selector) -->
                <div class="bg-white dark:bg-slate-950 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 text-center space-y-3">
                    <div class="flex items-center justify-center p-0.5 rounded-xl bg-slate-100 dark:bg-slate-800 max-w-[200px] mx-auto text-xs font-semibold">
                        <button type="button" @click="ticketMode = 'qr'"
                            :class="ticketMode === 'qr' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'"
                            class="flex-1 py-1 rounded-lg transition cursor-pointer">
                            QR Code
                        </button>
                        <button type="button" @click="ticketMode = 'barcode'"
                            :class="ticketMode === 'barcode' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-2xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white'"
                            class="flex-1 py-1 rounded-lg transition cursor-pointer">
                            Barcode 1D
                        </button>
                    </div>

                    <!-- QR Code View -->
                    <div x-show="ticketMode === 'qr'" class="space-y-2">
                        <div class="w-44 h-44 mx-auto p-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs flex items-center justify-center overflow-hidden [&>svg]:w-full [&>svg]:h-full [&>svg]:max-w-full [&>svg]:block"
                            x-html="selectedTicket?.qr_code_svg">
                        </div>
                        <p class="text-[11px] text-slate-400">Tunjukkan QR code ini ke scanner panitia</p>
                    </div>

                    <!-- Barcode 1D View -->
                    <div x-show="ticketMode === 'barcode'" x-cloak class="space-y-2">
                        <div class="max-w-[280px] mx-auto p-3 bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden flex items-center justify-center [&>svg]:w-full [&>svg]:h-auto [&>svg]:max-w-full [&>svg]:block"
                            x-html="selectedTicket?.barcode_svg">
                        </div>
                        <p class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200" x-text="selectedTicket?.ticket_code"></p>
                    </div>
                </div>

                <!-- Check-in Actions Tools -->
                <div class="space-y-3 pt-1">
                    <template x-if="selectedTicket?.is_checked_in">
                        <div class="p-4 rounded-2xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-center space-y-1">
                            <i data-lucide="check-circle-2" class="w-6 h-6 mx-auto text-emerald-600"></i>
                            <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Presensi Kehadiran Telah Divalidasi</p>
                            <p class="text-[11px] text-emerald-600 dark:text-emerald-400">Selamat mengikuti rangkaian agenda event.</p>
                        </div>
                    </template>

                    <template x-if="!selectedTicket?.is_checked_in">
                        <div class="space-y-3">
                            <h5 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <i data-lucide="navigation" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>Pilihan Metode Check-in Mandiri:</span>
                            </h5>

                            <!-- Method A: Check-in via Geolocation GPS -->
                            <div class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2 bg-slate-50/50 dark:bg-slate-950/50">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">1. Check-in via Lokasi (GPS)</span>
                                    <span class="text-[10px] text-emerald-600 font-mono">Radius <span x-text="selectedTicket?.checkin_radius_meters || 200"></span>m</span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Deteksi apakah posisi Anda berada di sekitar venue acara.
                                </p>
                                <button type="button" @click="checkinGps()" :disabled="locCheckinLoading"
                                    class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                    <i data-lucide="crosshair" class="w-4 h-4"></i>
                                    <span x-show="!locCheckinLoading">Check-in via Lokasi Saya</span>
                                    <span x-show="locCheckinLoading">Mendeteksi Posisi GPS...</span>
                                </button>
                                <div x-show="locCheckinMsg" x-cloak
                                    :class="locCheckinMsg?.type === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                                    class="p-2.5 rounded-xl border text-[11px] font-semibold text-center" x-text="locCheckinMsg?.text"></div>
                            </div>

                            <!-- Method B: Check-in via Barcode Event Venue -->
                            <div class="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2 bg-slate-50/50 dark:bg-slate-950/50">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">2. Masukkan Kode QR Venue</span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Ketik kode QR event yang tertera di banner registrasi jika GPS tidak akurat.
                                </p>
                                <div class="flex items-center gap-2">
                                    <input type="text" x-model="barcodeInput" placeholder="Ketik kode (COOCA-EVT-...)"
                                        class="flex-1 h-9 px-3 rounded-xl text-xs font-mono bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white uppercase">
                                    <button type="button" @click="checkinBarcodeSubmit()" :disabled="barcodeLoading || !barcodeInput"
                                        class="px-4 h-9 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 transition cursor-pointer">
                                        Submit
                                    </button>
                                </div>
                                <div x-show="barcodeMsg" x-cloak
                                    :class="barcodeMsg?.type === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'"
                                    class="p-2.5 rounded-xl border text-[11px] font-semibold text-center" x-text="barcodeMsg?.text"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800 text-right">
                <button type="button" @click="selectedTicket = null"
                    class="min-h-[44px] px-5 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>
</div>
@endsection
