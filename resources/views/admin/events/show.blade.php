@extends('layouts.admin', [
    'title' => __('admin.event_detail') . ': ' . $event->title . ' - Cooca Admin',
    'headerTitle' => 'Detail & Presensi Event',
    'headerSubtitle' => 'Pemantauan pendaftar, check-in barcode scanner, dan rekapitulasi kehadiran peserta',
])

@section('content')
<div class="space-y-6 pb-16" x-data="{
    scanCode: '',
    isScanning: false,
    scannerFeedback: null,
    async submitScan() {
        if (!this.scanCode.trim()) return;
        this.isScanning = true;
        this.scannerFeedback = null;
        try {
            const res = await fetch('{{ route('admin.events.checkin', $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({ ticket_code: this.scanCode.trim() })
            });
            const data = await res.json();
            if (data.success) {
                this.scannerFeedback = { type: 'success', text: data.message };
                this.scanCode = '';
                setTimeout(() => window.location.reload(), 1200);
            } else {
                this.scannerFeedback = { type: 'error', text: data.message || 'Check-in gagal.' };
            }
        } catch (e) {
            this.scannerFeedback = { type: 'error', text: 'Terjadi gangguan jaringan.' };
        } finally {
            this.isScanning = false;
        }
    }
}">
    <!-- 1. Top Navigation Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.events.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-black/60 dark:text-white/60 hover:text-[#007AFF] transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>{{ __('admin.kembali_ke_daftar_event') }}</span>
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.events.export', $event) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-[10px] text-xs font-semibold bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-black dark:text-white transition">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                <span>{{ __('admin.unduh_csv_kehadiran') }}</span>
            </a>
            <a href="{{ route('admin.events.edit', $event) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-[10px] text-xs font-semibold bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                <span>{{ __('admin.edit_event_3') }}</span>
            </a>
        </div>
    </div>

    <!-- 2. Event Summary Hero Banner (Apple HIG Bento) -->
    <div class="bg-white dark:bg-[#1C1C1E] p-6 sm:p-7 rounded-[22px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="space-y-2 max-w-3xl">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $event->event_type === 'online' ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#34C759]/10 text-[#34C759]' }}">
                    <i data-lucide="{{ $event->event_type === 'online' ? 'video' : 'map-pin' }}" class="w-3.5 h-3.5 mr-1"></i>
                    {{ strtoupper($event->event_type) }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $event->isPaid() ? 'bg-[#FF9500]/15 text-[#FF9500]' : 'bg-[#34C759]/15 text-[#34C759]' }}">
                    <i data-lucide="{{ $event->isPaid() ? 'credit-card' : 'check' }}" class="w-3.5 h-3.5 mr-1"></i>
                    {{ $event->isPaid() ? 'BERBAYAR (' . $event->formatted_price . ')' : 'GRATIS' }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $event->status === 'published' ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF9500]/15 text-[#FF9500]' }}">
                    {{ ucfirst($event->status) }}
                </span>
                <span class="text-[11px] font-mono text-black/45 dark:text-white/45">
                    Kode Event: {{ $event->checkin_code }}
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-black dark:text-white tracking-tight">
                {{ $event->title }}
            </h1>
            <div class="flex flex-wrap items-center gap-4 text-xs text-black/60 dark:text-white/60 pt-1">
                <div class="flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ $event->start_time->translatedFormat('l, d F Y') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>{{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }} WIB</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="w-4 h-4 text-[#34C759]"></i>
                    <span class="font-medium text-black dark:text-white">{{ $event->location_name }}</span>
                </div>
                @if($event->online_meeting_url)
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="video" class="w-4 h-4 text-[#007AFF]"></i>
                        <a href="{{ $event->online_meeting_url }}" target="_blank" rel="noopener noreferrer" class="text-[#007AFF] hover:underline font-medium inline-flex items-center gap-1">
                            <span>Link Pertemuan Online</span>
                            <i data-lucide="external-link" class="w-3 h-3"></i>
                        </a>
                    </div>
                @endif
                <div class="flex items-center gap-1.5">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-[#FF9500]"></i>
                    <span>Batas Daftar: {{ $event->registration_deadline->translatedFormat('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Attendance KPI Pill -->
        <div class="bg-black/[0.03] dark:bg-white/[0.04] p-4 rounded-[16px] border border-black/[0.06] dark:border-white/[0.06] shrink-0 w-full sm:w-auto flex items-center justify-between sm:justify-start gap-6">
            <div>
                <span class="text-[10px] uppercase font-bold text-black/45 dark:text-white/45 tracking-wider block">Tingkat Hadir</span>
                <span class="text-2xl font-black text-[#007AFF] dark:text-[#0A84FF] tabular-nums">{{ $attendanceRate }}%</span>
                <span class="text-[11px] text-black/50 dark:text-white/50 block">{{ $attendedCount }} dari {{ $registeredCount }} hadir</span>
            </div>
            <div class="w-16 h-16 rounded-full bg-white dark:bg-[#2C2C2E] border border-black/[0.08] dark:border-white/[0.08] flex items-center justify-center p-2 shrink-0">
                <div class="text-center">
                    <span class="text-[14px] font-black text-black dark:text-white leading-none block">{{ $registeredCount }}</span>
                    <span class="text-[9px] text-black/40 dark:text-white/40 block mt-0.5">Peserta</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Two Columns: Live Check-in Scanner Box (Kiri) & QR Event (Kanan) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Live Barcode Scanner Tool (8 Cols) -->
        <div class="lg:col-span-8 bg-white dark:bg-[#1C1C1E] p-6 rounded-[20px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.06] pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                        <i data-lucide="scan-barcode" class="w-4.5 h-4.5"></i>
                    </span>
                    <div>
                        <h2 class="text-[14px] font-bold text-black dark:text-white">Live Barcode Check-in Scanner</h2>
                        <p class="text-[11px] text-black/50 dark:text-white/50">Arahkan scanner barcode fisik atau masukkan kode tiket peserta.</p>
                    </div>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759]">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#34C759] mr-1.5 animate-pulse"></span>
                    Siap Scan
                </span>
            </div>

            <!-- Input Box -->
            @if(Auth::guard('admin')->user()?->hasPermission('admin.events.checkin'))
            <form @submit.prevent="submitScan()" class="space-y-3">
                <div class="relative">
                    <input type="text" x-model="scanCode" placeholder="Scan Barcode atau ketik Tiket Code (contoh: EVT-2610-ABC123)..." autofocus
                        class="w-full h-12 pl-4 pr-32 rounded-[14px] text-[14px] font-mono bg-black/[0.03] dark:bg-white/[0.05] border-2 border-[#007AFF]/30 dark:border-[#0A84FF]/30 text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-hidden focus:border-[#007AFF]">
                    <button type="submit" :disabled="isScanning"
                        class="absolute right-2 top-1/2 -translate-y-1/2 px-4 h-8 rounded-[10px] text-[12px] font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <span x-show="!isScanning">Check-in</span>
                        <span x-show="isScanning">Memproses...</span>
                    </button>
                </div>

                <!-- Feedback Toast Banner -->
                <div x-show="scannerFeedback" x-cloak
                    :class="scannerFeedback?.type === 'success' ? 'bg-[#34C759]/15 text-[#34C759] border-[#34C759]/30' : 'bg-[#FF3B30]/15 text-[#FF3B30] border-[#FF3B30]/30'"
                    class="p-3 rounded-[12px] border text-[12px] font-semibold flex items-center gap-2 transition">
                    <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
                    <span x-text="scannerFeedback?.text"></span>
                </div>

                <div class="flex items-center justify-between text-[11px] text-black/50 dark:text-white/50 pt-1">
                    <span>* Input otomatis mendeteksi scanner barcode USB/Bluetooth (Enter key).</span>
                    <span>Format Tiket: <strong class="font-mono text-black dark:text-white">EVT-XXXX-XXXXXX</strong></span>
                </div>
            </form>
            @endif
        </div>

        <!-- Event Venue QR Display (4 Cols) -->
        <div class="lg:col-span-4 bg-white dark:bg-[#1C1C1E] p-6 rounded-[20px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs text-center space-y-3">
            <h3 class="text-[13px] font-bold text-black dark:text-white">QR Code Check-in Venue</h3>
            <p class="text-[11px] text-black/50 dark:text-white/50">Tampilkan atau cetak QR ini di meja registrasi venue untuk peserta scan mandiri:</p>
            <div class="w-44 h-44 mx-auto p-3 bg-white rounded-[16px] border border-black/[0.08] shadow-2xs flex items-center justify-center">
                {!! $eventQrSvg !!}
            </div>
            <div class="font-mono text-[11px] font-bold text-black dark:text-white">
                {{ $event->checkin_code }}
            </div>
            <p class="text-[10px] text-black/40 dark:text-white/40">Peserta dapat memindai via menu Komunitas &gt; Check-in Barcode.</p>
        </div>
    </div>

    <!-- 4. Roster of Registered Participants Table -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-black/[0.06] dark:border-white/[0.06] flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-[#007AFF]"></i>
                <h3 class="text-[14px] font-bold text-black dark:text-white">{{ __('admin.daftar_peserta_terdaftar') }}</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70">
                    {{ $participants->total() }} Peserta
                </span>
            </div>

            <form method="GET" action="{{ route('admin.events.show', $event) }}" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.cari_nama_bisnis_tiket') }}"
                    class="h-8 px-3 rounded-[8px] text-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-black dark:text-white">
                <button type="submit" class="px-3 h-8 rounded-[8px] bg-black/[0.05] text-[11px] font-bold text-black dark:text-white">{{ __('common.search') }}</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-[12.5px] border-collapse">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.06] bg-black/[0.02] dark:bg-white/[0.02] text-[10.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-4">Kode Tiket</th>
                        <th class="py-3 px-4">Peserta &amp; Kontak</th>
                        <th class="py-3 px-4">Workspace Bisnis</th>
                        <th class="py-3 px-4">Waktu Daftar</th>
                        <th class="py-3 px-4">Status Kehadiran</th>
                        <th class="py-3 px-4">Waktu &amp; Metode Check-in</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.05]">
                    @forelse($participants as $idx => $reg)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition">
                            <td class="py-3 px-4 text-black/40 dark:text-white/40 tabular-nums">
                                {{ $participants->firstItem() + $idx }}
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-black dark:text-white whitespace-nowrap">
                                {{ $reg->ticket_code }}
                            </td>
                            <td class="py-3 px-4 min-w-[180px]">
                                <div class="font-bold text-black dark:text-white">{{ $reg->user->name ?? '-' }}</div>
                                <div class="text-[11px] text-black/50 dark:text-white/50">{{ $reg->user->email ?? '-' }}</div>
                                @if($reg->user->phone ?? null)
                                    <div class="text-[10px] text-black/40 dark:text-white/40 font-mono">{{ $reg->user->phone }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-medium text-black dark:text-white">
                                {{ $reg->business->name ?? '-' }}
                            </td>
                            <td class="py-3 px-4 text-[11px] text-black/60 dark:text-white/60 whitespace-nowrap">
                                {{ $reg->registered_at->translatedFormat('d M Y, H:i') }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($reg->isCheckedIn())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30">
                                        <i data-lucide="check" class="w-3 h-3 mr-1"></i> Hadir
                                    </span>
                                @elseif($reg->status === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]">
                                        Dibatalkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                        Belum Hadir
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-[11px] whitespace-nowrap">
                                @if($reg->isCheckedIn())
                                    <div class="font-semibold text-black dark:text-white">
                                        {{ $reg->checked_in_at?->translatedFormat('d M Y, H:i') }} WIB
                                    </div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45 uppercase font-mono">
                                        Via {{ $reg->checkin_method }}
                                    </span>
                                @else
                                    <span class="text-black/30 dark:text-white/30">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if(Auth::guard('admin')->user()?->hasPermission('admin.events.checkin'))

                                <form method="POST" action="{{ route('admin.events.checkin', $event) }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="registration_id" value="{{ $reg->id }}">
                                    @if($reg->isCheckedIn())
                                        <button type="submit"
                                            class="px-2.5 py-1 rounded-[7px] text-[11px] font-semibold text-[#FF3B30] hover:bg-[#FF3B30]/10 transition cursor-pointer"
                                            title="Batalkan kehadiran">{{ __('admin.batal_hadir') }}</button>
                                    @else
                                        <button type="submit"
                                            class="px-2.5 py-1 rounded-[7px] text-[11px] font-semibold text-white bg-[#34C759] hover:bg-[#28A745] transition cursor-pointer shadow-2xs">
                                            Check-in Manual
                                        </button>
                                    @endif
                                </form>

                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-black/40 dark:text-white/40">
                                {{ __('admin.belum_ada_peserta_yang_mendaftar_pada') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($participants->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.06]">
                {{ $participants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
