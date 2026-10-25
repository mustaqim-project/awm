@extends('layouts.admin', [
    'title' => 'Kelola Event Komunitas - Cooca Admin',
    'headerTitle' => 'Event Komunitas',
    'headerSubtitle' => 'Manajemen agenda seminar, workshop UMKM, pendaftaran peserta & check-in digital',
])

@section('content')
<div class="space-y-6 pb-12">
    <!-- 1. Header Banner -->
    <div class="bg-white dark:bg-[#1C1C1E] p-5 sm:p-6 rounded-[20px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] border border-[#007AFF]/20">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>Agenda &amp; Edukasi Komunitas</span>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-black dark:text-white tracking-tight">
                Kelola Event Komunitas
            </h1>
            <p class="text-xs sm:text-sm text-black/60 dark:text-white/60 mt-1 max-w-2xl leading-relaxed">
                Kelola jadwal meetup, seminar bisnis, workshop kasir POS, pendaftaran peserta, dan pemantauan kehadiran via barcode serta geolokasi GPS.
            </p>
        </div>
        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('admin.events.create') }}"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0062CC] shadow-sm shadow-[#007AFF]/25 transition active:scale-[0.98]">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Buat Event Baru</span>
            </a>
        </div>
    </div>

    <!-- 2. Bento KPI Cards (4 Kolom) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-[#1C1C1E] p-4.5 rounded-[16px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">Total Event</span>
                <span class="w-8 h-8 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center text-black/70 dark:text-white/70">
                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-black dark:text-white tabular-nums tracking-tight">
                {{ number_format($stats['total'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Keseluruhan agenda tercatat</p>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] p-4.5 rounded-[16px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#34C759] dark:text-[#30D158]">Event Mendatang</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:bg-[#30D158]/20 dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-[#34C759] dark:text-[#30D158] tabular-nums tracking-tight">
                {{ number_format($stats['upcoming'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Aktif &amp; pendaftaran dibuka</p>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] p-4.5 rounded-[16px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF] dark:text-[#0A84FF]">Total Pendaftar</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-black dark:text-white tabular-nums tracking-tight">
                {{ number_format($stats['total_participants'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Pemilik bisnis terdaftar</p>
        </div>

        <div class="bg-white dark:bg-[#1C1C1E] p-4.5 rounded-[16px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#AF52DE] dark:text-[#BF5AF2]">Total Hadir</span>
                <span class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] dark:bg-[#BF5AF2]/20 dark:text-[#BF5AF2] flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-black text-black dark:text-white tabular-nums tracking-tight">
                {{ number_format($stats['total_attended'], 0, ',', '.') }}
            </div>
            <p class="text-[11px] text-black/45 dark:text-white/45 mt-1">Check-in via barcode/lokasi</p>
        </div>
    </div>

    <!-- 3. Filter Bar & Search -->
    <div class="bg-white dark:bg-[#1C1C1E] p-4 rounded-[16px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 md:pb-0">
            @php
                $tabFilters = [
                    'all' => 'Semua Event',
                    'upcoming' => 'Mendatang',
                    'past' => 'Sudah Lewat',
                    'published' => 'Dipublikasikan',
                    'draft' => 'Draft',
                ];
            @endphp
            @foreach($tabFilters as $k => $label)
                <a href="{{ route('admin.events.index', array_merge(request()->query(), ['status' => $k, 'page' => 1])) }}"
                    class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold whitespace-nowrap transition {{ ($status === $k) ? 'bg-[#007AFF] text-white shadow-2xs' : 'text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.events.index') }}" class="flex items-center gap-2">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative w-full md:w-64">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul, lokasi..."
                    class="w-full h-9 pl-9 pr-3 rounded-[10px] text-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-hidden focus:border-[#007AFF]">
            </div>
            <button type="submit" class="px-3 h-9 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[12px] font-semibold transition shrink-0">
                Filter
            </button>
        </form>
    </div>

    <!-- 4. Events Table -->
    <div class="bg-white dark:bg-[#1C1C1E] rounded-[20px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.06] bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                        <th class="py-3 px-4">Event &amp; Tipe</th>
                        <th class="py-3 px-4">Waktu Pelaksanaan</th>
                        <th class="py-3 px-4">Lokasi / Venue</th>
                        <th class="py-3 px-4">Pendaftar / Kuota</th>
                        <th class="py-3 px-4">Batas Daftar</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.05]">
                    @forelse($events as $event)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition">
                            <!-- Event Title -->
                            <td class="py-3.5 px-4 min-w-[240px]">
                                <div class="flex items-start gap-3">
                                    <div class="w-12 h-12 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] overflow-hidden shrink-0 border border-black/[0.06] dark:border-white/[0.06]">
                                        @if($event->banner_url)
                                            <img src="{{ $event->banner_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-black/30 dark:text-white/30">
                                                <i data-lucide="image" class="w-5 h-5"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('admin.events.show', $event) }}" class="font-bold text-black dark:text-white hover:text-[#007AFF] dark:hover:text-[#0A84FF] transition line-clamp-1">
                                            {{ $event->title }}
                                        </a>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-[6px] text-[10px] font-bold {{ $event->event_type === 'online' ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#34C759]/10 text-[#34C759]' }}">
                                                <i data-lucide="{{ $event->event_type === 'online' ? 'video' : 'map-pin' }}" class="w-3 h-3 mr-0.5"></i>
                                                {{ strtoupper($event->event_type) }}
                                            </span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-[6px] text-[10px] font-bold {{ $event->isPaid() ? 'bg-[#FF9500]/10 text-[#FF9500] dark:text-[#FF9F0A]' : 'bg-[#34C759]/10 text-[#34C759]' }}">
                                                {{ $event->isPaid() ? 'BERBAYAR • ' . $event->formatted_price : 'GRATIS' }}
                                            </span>
                                            <span class="text-[11px] font-mono text-black/40 dark:text-white/40">
                                                {{ $event->checkin_code }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Event Date -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-semibold text-black dark:text-white">
                                    {{ $event->start_time->translatedFormat('d M Y') }}
                                </div>
                                <div class="text-[11px] text-black/50 dark:text-white/50 tabular-nums">
                                    {{ $event->start_time->format('H:i') }} - {{ $event->end_time->format('H:i') }} WIB
                                </div>
                            </td>

                            <!-- Location -->
                            <td class="py-3.5 px-4 min-w-[180px]">
                                <div class="font-semibold text-black dark:text-white line-clamp-1">
                                    {{ $event->location_name }}
                                </div>
                                @if($event->latitude && $event->longitude)
                                    <div class="text-[10px] text-[#007AFF] dark:text-[#0A84FF] flex items-center gap-1 mt-0.5 font-mono">
                                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                                        <span>GPS (R: {{ $event->checkin_radius_meters }}m)</span>
                                    </div>
                                @elseif($event->event_type === 'online')
                                    <div class="text-[10px] text-black/45 dark:text-white/45">Online Webinar</div>
                                @else
                                    <div class="text-[10px] text-black/40 dark:text-white/40">Tanpa GPS</div>
                                @endif
                            </td>

                            <!-- Registered Count & Quota -->
                            <td class="py-3.5 px-4 whitespace-nowrap tabular-nums">
                                <div class="flex items-center gap-1.5">
                                    <strong class="font-black text-black dark:text-white">{{ $event->active_registrations_count }}</strong>
                                    <span class="text-black/40 dark:text-white/40">/</span>
                                    <span class="text-black/60 dark:text-white/60">{{ $event->quota ? $event->quota . ' kursi' : 'Tanpa Batas' }}</span>
                                </div>
                                @if($event->quota)
                                    <div class="w-24 h-1.5 bg-black/[0.06] dark:bg-white/[0.08] rounded-full mt-1.5 overflow-hidden">
                                        <div class="h-full bg-[#007AFF] rounded-full" style="width: {{ min(100, round(($event->active_registrations_count / $event->quota) * 100)) }}%"></div>
                                    </div>
                                @endif
                            </td>

                            <!-- Registration Deadline -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="text-[12px] {{ $event->registration_deadline->isPast() ? 'text-[#FF3B30] font-bold' : 'text-black dark:text-white' }}">
                                    {{ $event->registration_deadline->translatedFormat('d M Y, H:i') }}
                                </div>
                                <div class="text-[10px] text-black/40 dark:text-white/40">
                                    {{ $event->registration_deadline->isPast() ? 'Pendaftaran Tutup' : $event->registration_deadline->diffForHumans() }}
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($event->isPast())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-black/[0.06] text-black/60 dark:bg-white/[0.08] dark:text-white/60">
                                        Selesai
                                    </span>
                                @elseif($event->status === 'published')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759] border border-[#34C759]/30">
                                        Dipublikasikan
                                    </span>
                                @elseif($event->status === 'draft')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/15 text-[#FF9500] border border-[#FF9500]/30">
                                        Draft
                                    </span>
                                @elseif($event->status === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] border border-[#FF3B30]/30">
                                        Dibatalkan
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.events.show', $event) }}"
                                        class="px-2.5 py-1.5 rounded-[8px] text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition"
                                        title="Detail & Peserta">
                                        Peserta &amp; Scan
                                    </a>
                                    <a href="{{ route('admin.events.edit', $event) }}"
                                        class="p-1.5 rounded-[8px] text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition"
                                        title="Edit Event">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    @if(Auth::guard('admin')->user()?->hasPermission('admin.events.delete'))
                                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus event ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 rounded-[8px] text-[#FF3B30] hover:bg-[#FF3B30]/10 transition cursor-pointer"
                                            title="Hapus Event">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-black/40 dark:text-white/40">
                                <i data-lucide="calendar-x" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
                                <p class="font-bold text-[14px]">Belum ada event komunitas.</p>
                                <p class="text-[12px] mt-0.5">Klik tombol "Buat Event Baru" untuk menambahkan agenda seminar atau meetup.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.06]">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
