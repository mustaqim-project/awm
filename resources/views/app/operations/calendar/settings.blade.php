@extends('layouts.app', [
    'title' => __('operations.calendar_settings_title') . ' - Cooca',
    'headerTitle' => __('operations.calendar_settings_title'),
    'headerSubtitle' => __('operations.calendar_settings_subtitle'),
])

@section('content')
<div class="space-y-6 pb-24 lg:pb-16 max-w-4xl" x-data="{
    isSyncing: false,
    async triggerGoogleSync() {
        this.isSyncing = true;
        try {
            const res = await fetch('{{ route('operations.calendar.google.sync-now') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            });
            const data = await res.json();
            alert(data.message);
            window.location.reload();
        } catch (e) {
            console.error(e);
        } finally {
            this.isSyncing = false;
        }
    }
}">

    <div class="flex items-center gap-4">
        <a href="{{ route('operations.calendar.index') }}" class="w-10 h-10 rounded-2xl bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] flex items-center justify-center shrink-0 text-[#8E8E93] hover:text-black dark:hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-xl font-bold text-black dark:text-white">{{ __('operations.calendar_settings_title') }}</h2>
            <p class="text-xs text-[#8E8E93]">{{ __('operations.calendar_settings_subtitle') }}</p>
        </div>
    </div>

    {{-- Google Calendar Integration Card --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm space-y-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                <i data-lucide="calendar" class="w-7 h-7"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-black dark:text-white">{{ __('operations.my_google_calendar') }}</h3>
                <p class="text-xs text-[#8E8E93] mt-0.5">
                    Hubungkan akun Google Calendar pribadi Anda untuk menyinkronkan tugas, agenda, dan deadline kegiatan secara langsung ke kalender ponsel dan laptop Anda.
                </p>
            </div>
        </div>

        @if(!empty($myGoogleAccount) && $myGoogleAccount->is_active)
            {{-- Connected Personal Account Details --}}
            <div class="p-4 bg-[#34C759]/5 border border-[#34C759]/20 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    @if($myGoogleAccount->avatar_url)
                        <img src="{{ $myGoogleAccount->avatar_url }}" alt="Google Avatar" class="w-10 h-10 rounded-full border border-black/10">
                    @else
                        <div class="w-10 h-10 rounded-full bg-[#34C759] text-white font-bold flex items-center justify-center">G</div>
                    @endif
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-bold text-black dark:text-white">{{ $myGoogleAccount->name ?: 'Akun Google' }}</h4>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759]/10 text-[#34C759]">Terhubung</span>
                        </div>
                        <p class="text-xs text-[#8E8E93]">{{ $myGoogleAccount->email }}</p>
                        <p class="text-[11px] text-[#8E8E93] mt-1">
                            Terakhir disinkronkan: {{ $myGoogleAccount->last_synced_at ? $myGoogleAccount->last_synced_at->diffForHumans() : 'Belum pernah' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <button type="button" @click="triggerGoogleSync()" :disabled="isSyncing"
                        class="px-3.5 py-2 bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 rounded-xl text-xs font-semibold hover:bg-black/[0.02] flex items-center gap-1.5 transition-all">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="isSyncing ? 'animate-spin' : ''"></i>
                        <span>{{ __('operations.sync_now') }}</span>
                    </button>

                    <form action="{{ route('operations.calendar.google.disconnect') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan koneksi Google Calendar?')">
                        @csrf
                        <input type="hidden" name="account_id" value="{{ $myGoogleAccount->id }}">
                        <button type="submit" class="px-3.5 py-2 text-[#FF3B30] hover:bg-[#FF3B30]/10 rounded-xl text-xs font-semibold transition-all">
                            {{ __('operations.disconnect_google') }}
                        </button>
                    </form>
                </div>
            </div>
        @else
            {{-- Connect Prompt --}}
            <div class="p-6 bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 rounded-2xl text-center space-y-4">
                <div class="max-w-md mx-auto">
                    <h4 class="text-sm font-bold text-black dark:text-white">Belum Terhubung ke Google Calendar Pribadi</h4>
                    <p class="text-xs text-[#8E8E93] mt-1 leading-relaxed">
                        Dengan menautkan akun Google Anda, setiap kegiatan atau deadline tugas yang ditugaskan kepada Anda akan otomatis masuk ke aplikasi Google Calendar di HP Anda beserta notifikasi pengingat.
                    </p>
                </div>

                <a href="{{ route('operations.calendar.google.connect') }}"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#007AFF] hover:bg-[#007AFF]/90 text-white rounded-xl text-xs font-bold shadow-sm shadow-[#007AFF]/25 transition-all active:scale-95">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    <span>{{ __('operations.connect_my_google') }}</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Team Google Calendars (Only for Owner or Managers) --}}
    @if((\App\Support\Context::isOwner() || \App\Support\Context::hasPermission('calendar.manage')) && !empty($allAccounts) && $allAccounts->count() > 0)
    <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-[#8E8E93]">{{ __('operations.team_google_calendars') }}</h3>
                <p class="text-xs text-[#8E8E93] mt-0.5">Daftar akun Google Calendar staf dan anggota tim yang terhubung di bisnis ini.</p>
            </div>
            <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-[#007AFF]/10 text-[#007AFF]">
                {{ $allAccounts->count() }} Akun Terhubung
            </span>
        </div>

        <div class="divide-y divide-black/5 dark:divide-white/10">
            @foreach($allAccounts as $acc)
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        @if($acc->avatar_url)
                            <img src="{{ $acc->avatar_url }}" alt="Avatar" class="w-9 h-9 rounded-full border border-black/10">
                        @else
                            <div class="w-9 h-9 rounded-full bg-[#007AFF] text-white font-bold flex items-center justify-center text-xs">
                                {{ strtoupper(substr($acc->name ?: $acc->email, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-xs font-bold text-black dark:text-white">{{ $acc->name ?: 'Google User' }}</h4>
                                @if($acc->user)
                                    <span class="text-[10px] text-[#8E8E93]">({{ $acc->user->name }})</span>
                                @endif
                                @if(!empty($myGoogleAccount) && $myGoogleAccount->id === $acc->id)
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-[#34C759]/10 text-[#34C759]">Akun Saya</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-[#8E8E93]">{{ $acc->email }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-[11px] text-[#8E8E93]">
                            {{ $acc->last_synced_at ? $acc->last_synced_at->diffForHumans() : 'Belum sync' }}
                        </span>
                        <form action="{{ route('operations.calendar.google.sync-now') }}" method="POST">
                            @csrf
                            <input type="hidden" name="account_id" value="{{ $acc->id }}">
                            <button type="submit" class="p-1.5 hover:bg-black/5 dark:hover:bg-white/5 rounded-lg text-[#8E8E93] hover:text-black dark:hover:text-white transition-all" title="Sinkronkan Akun Ini">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Multi-Channel Reminders Overview --}}
    <div class="bg-white dark:bg-[#1C1C1E] rounded-3xl p-6 border border-black/5 dark:border-white/10 shadow-sm space-y-4">
        <h3 class="text-sm font-bold uppercase tracking-wider text-[#8E8E93]">Pengingat Otomatis Multi-Channel</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-[#34C759]/5 border border-[#34C759]/10 space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-[#34C759]">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>WhatsApp Otomatis</span>
                </div>
                <p class="text-[#8E8E93] leading-relaxed">
                    Notifikasi pesan pengingat dikirim ke nomor WhatsApp Anda 15 menit sebelum acara dimulai via Meta Cloud API resmi.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-[#007AFF]/5 border border-[#007AFF]/10 space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-[#007AFF]">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                    <span>Email & File .ics</span>
                </div>
                <p class="text-[#8E8E93] leading-relaxed">
                    Pengingat dikirim ke kotak masuk email beserta file lampiran kalender standar (.ics) yang otomatis dapat ditambahkan ke Apple Calendar / Outlook.
                </p>
            </div>

            <div class="p-4 rounded-2xl bg-[#AF52DE]/5 border border-[#AF52DE]/10 space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-[#AF52DE]">
                    <i data-lucide="bell" class="w-4 h-4"></i>
                    <span>In-App Realtime</span>
                </div>
                <p class="text-[#8E8E93] leading-relaxed">
                    Banner peringatan di dashboard dan pop-up in-app yang muncul di layar kasir maupun backoffice COOCA secara realtime.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
