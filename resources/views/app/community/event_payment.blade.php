@extends('layouts.app', [
    'title' => 'Pembayaran Tiket ' . $event->title . ' - Cooca',
    'headerTitle' => 'Pembayaran Tiket Event',
    'headerSubtitle' => 'Scan QRIS untuk menyelesaikan pembayaran tiket komunitas',
])

@section('content')
<div class="max-w-5xl mx-auto pb-20 px-3 sm:px-6" x-data="{
    isPaid: {{ $registration->isPaid() ? 'true' : 'false' }},
    checkingStatus: false,
    feedbackMsg: '',
    countdownSeconds: {{ max(0, $registration->gateway_expired_at ? (int) now()->diffInSeconds($registration->gateway_expired_at, false) : 900) }},
    countdownFormatted: '15:00',
    pollInterval: null,
    countdownInterval: null,

    init() {
        this.updateCountdown();
        if (!this.isPaid) {
            this.startCountdown();
            this.startPolling();
        }
    },

    formatTime(seconds) {
        const m = Math.floor(seconds / 60);
        const s = seconds % 60;
        return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
    },

    updateCountdown() {
        this.countdownFormatted = this.formatTime(Math.max(0, this.countdownSeconds));
    },

    startCountdown() {
        this.countdownInterval = setInterval(() => {
            if (this.countdownSeconds > 0) {
                this.countdownSeconds--;
                this.updateCountdown();
            } else {
                clearInterval(this.countdownInterval);
                clearInterval(this.pollInterval);
            }
        }, 1000);
    },

    startPolling() {
        this.pollInterval = setInterval(() => {
            this.checkStatus(false);
        }, 6000);
    },

    async checkStatus(showFeedback = true) {
        if (this.isPaid) return;
        if (showFeedback) {
            this.checkingStatus = true;
            this.feedbackMsg = 'Memeriksa status gateway pembayaran...';
        }

        try {
            const res = await fetch('{{ route('community.events.payment.status', $registration) }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').content : ''
                }
            });
            const data = await res.json();
            if (data.is_paid) {
                this.isPaid = true;
                clearInterval(this.pollInterval);
                clearInterval(this.countdownInterval);
                this.feedbackMsg = 'Pembayaran terverifikasi! Mengalihkan ke tiket...';
                setTimeout(() => {
                    window.location.href = data.redirect_url || '{{ route('community.events.index') }}';
                }, 1500);
            } else if (showFeedback) {
                this.feedbackMsg = 'Pembayaran belum terdeteksi. Silakan selesaikan scan di e-Wallet/m-Banking Anda.';
                setTimeout(() => this.feedbackMsg = '', 4000);
            }
        } catch (e) {
            if (showFeedback) {
                this.feedbackMsg = 'Gagal memeriksa status pembayaran. Coba beberapa saat lagi.';
            }
        } finally {
            if (showFeedback) this.checkingStatus = false;
        }
    }
}">

    <!-- Top Navigation Breadcrumbs -->
    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('community.events.index') }}"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Agenda Event</span>
        </a>

        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
            :class="isPaid ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'">
            <span class="w-2 h-2 rounded-full" :class="isPaid ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse'"></span>
            <span x-text="isPaid ? 'Pembayaran Lunas' : 'Menunggu Pembayaran QRIS'"></span>
        </div>
    </div>

    <!-- Main Payment Bento Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- 1. KOLOM KIRI: QRIS BOX DISPLAY (7 COLS) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-slate-200/80 dark:border-white/10 p-5 sm:p-7 shadow-xs space-y-5">
                
                <!-- QRIS Header ala Standar BI -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-white/5 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold tracking-tighter text-xs shadow-2xs">
                            QRIS
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">
                                QRIS Standar Nasional
                            </h2>
                            <p class="text-[11px] text-slate-400">
                                NMID: ID1020304050 • PT Cooca Digital Solusindo
                            </p>
                        </div>
                    </div>

                    <!-- Countdown Timer Window -->
                    <div class="text-right">
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Batas Waktu</span>
                        <span class="font-mono text-sm font-black text-rose-600 dark:text-rose-400" x-text="countdownFormatted"></span>
                    </div>
                </div>

                <!-- QRIS Visual Card Area -->
                <div class="flex flex-col items-center justify-center py-2 space-y-4">
                    <div class="p-4 bg-white rounded-2xl border-2 border-slate-200 dark:border-white/10 shadow-md max-w-[320px] w-full flex flex-col items-center">
                        <!-- Top Official Badge -->
                        <div class="w-full flex items-center justify-between pb-2 mb-2 border-b border-slate-100 text-[10px] font-bold text-slate-600 uppercase tracking-wider">
                            <span>COOCA PAY</span>
                            <span class="text-rose-600">QRIS DINAMIS</span>
                        </div>

                        <!-- QR Code Container -->
                        <div class="w-full flex items-center justify-center overflow-hidden rounded-xl bg-white p-2">
                            @if(!empty($registration->gateway_qr_url))
                                <img src="{{ $registration->gateway_qr_url }}" alt="QRIS Tiket Event" class="w-64 h-64 object-contain">
                            @else
                                <div class="w-64 h-64 flex items-center justify-center">
                                    {!! $qrisSvg !!}
                                </div>
                            @endif
                        </div>

                        <!-- Supported Payment Brands -->
                        <div class="w-full pt-3 mt-1 border-t border-slate-100 text-center space-y-1">
                            <p class="text-[10px] font-bold text-slate-500">Dukungan Seluruh Aplikasi Pembayaran:</p>
                            <p class="text-[9px] text-slate-400 leading-tight">
                                GoPay • OVO • DANA • ShopeePay • LinkAja • BCA Mobile • Livin by Mandiri • BRImo • BNI Mobile • BSI • Jenius
                            </p>
                        </div>
                    </div>

                    <!-- Scan Instructions Alert -->
                    <div class="w-full p-3.5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/60 dark:border-indigo-800/40 flex items-start gap-3">
                        <i data-lucide="info" class="w-4 h-4 text-indigo-600 shrink-0 mt-0.5"></i>
                        <div class="text-xs text-indigo-950 dark:text-indigo-200 space-y-0.5">
                            <p class="font-bold">Cara Pembayaran:</p>
                            <p class="text-[11px] leading-relaxed opacity-90">
                                Buka aplikasi m-Banking atau e-Wallet di smartphone Anda, pilih menu Scan QRIS, lalu scan kode di atas. Nominal pembayaran akan terisi otomatis sebesar <strong>{{ $registration->getFormattedAmountPaid() }}</strong>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons & Polling Feedback -->
                <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-white/5">
                    <template x-if="feedbackMsg">
                        <div class="p-3 rounded-xl bg-slate-100 dark:bg-white/5 text-xs text-center font-medium text-slate-700 dark:text-slate-300 transition"
                            x-text="feedbackMsg"></div>
                    </template>

                    <div class="flex flex-col sm:flex-row items-center gap-2.5">
                        <button type="button" @click="checkStatus(true)" :disabled="checkingStatus || isPaid"
                            class="w-full sm:flex-1 py-3 rounded-xl text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center justify-center gap-2 shadow-xs cursor-pointer disabled:opacity-50">
                            <i data-lucide="refresh-cw" class="w-4 h-4" :class="checkingStatus ? 'animate-spin' : ''"></i>
                            <span x-text="checkingStatus ? 'Memeriksa...' : 'Cek Status Pembayaran'"></span>
                        </button>

                        <!-- Simulasi Bayar untuk Pengujian Instan / Sandbox -->
                        <form method="POST" action="{{ route('community.events.payment.simulate', $registration) }}" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-3 rounded-xl text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 border border-emerald-200/80 dark:border-emerald-800/80 transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-500"></i>
                                <span>Simulasi Bayar QRIS Sukses</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <!-- 2. KOLOM KANAN: RINCIAN EVENT & BIAYA (5 COLS) -->
        <div class="lg:col-span-5 space-y-4">
            
            <!-- Event Summary Card -->
            <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-slate-200/80 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-white/5">
                    <i data-lucide="calendar" class="w-4 h-4 text-[#007AFF]"></i>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Detail Event Komunitas
                    </h3>
                </div>

                @if($event->banner_url)
                    <div class="h-32 -mx-5 -mt-2 rounded-xl overflow-hidden mb-3">
                        <img src="{{ $event->banner_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $event->isOnline() ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/80 dark:text-indigo-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300' }}">
                        <i data-lucide="{{ $event->isOnline() ? 'video' : 'map-pin' }}" class="w-3 h-3"></i>
                        <span>{{ $event->event_type }}</span>
                    </span>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white leading-snug">
                        {{ $event->title }}
                    </h3>
                </div>

                <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 pt-2 border-t border-slate-100 dark:border-white/5">
                    <div class="flex items-start gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $event->start_time->translatedFormat('l, d F Y') }}</p>
                            <p class="text-[11px] text-slate-400">{{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }} WIB</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <i data-lucide="{{ $event->isOnline() ? 'video' : 'map-pin' }}" class="w-4 h-4 text-slate-400 shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $event->location_name }}</p>
                            @if($event->location_address)
                                <p class="text-[11px] text-slate-400 line-clamp-2">{{ $event->location_address }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Registration Meta -->
                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-white/[0.03] space-y-1.5 text-xs">
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Nama Peserta:</span>
                        <span class="font-bold text-slate-800 dark:text-white">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Bisnis / Brand:</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $registration->business->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Kode Tiket:</span>
                        <span class="font-mono font-bold text-[#007AFF]">{{ $registration->ticket_code }}</span>
                    </div>
                </div>
            </div>

            <!-- Price Breakdown Card -->
            <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl border border-slate-200/80 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Rincian Pembayaran Tiket
                </h4>

                <div class="space-y-2 text-xs border-b border-slate-100 dark:border-white/5 pb-3">
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span>Harga Tiket Event (1x)</span>
                        <span class="font-semibold text-slate-900 dark:text-white">{{ $registration->getFormattedAmountPaid() }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span>Biaya Gateway QRIS</span>
                        <span class="font-bold text-emerald-600">Rp 0 (Gratis)</span>
                    </div>
                </div>

                <div class="flex items-baseline justify-between pt-1">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Total Pembayaran</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white font-mono">
                        {{ $registration->getFormattedAmountPaid() }}
                    </span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
