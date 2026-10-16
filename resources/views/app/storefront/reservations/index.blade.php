@extends('layouts.app', ['title' => __('storefront.backoffice.reservations.page_title')])

@section('content')
    <div class="space-y-6 max-w-[1250px] mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 pb-28 sm:pb-32 lg:pb-10"
        x-data="{
            showAssignModal: false,
            selectedReservation: null,
            cancelModalOpen: false,
            reservationToCancel: null,
            openAssign(rsv) {
                this.selectedReservation = rsv;
                this.showAssignModal = true;
            }
        }">
        {{-- UNIFIED STOREFRONT HUB NAVIGATION --}}
        @include('app.storefront.partials.navigation', ['title' => __('storefront.backoffice.reservations.nav_title')])

        <!-- Feedback Alerts -->
        @if (session('success'))
            <div
                class="p-4 rounded-[18px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158] text-sm flex items-center gap-3 font-medium">
                <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div
                class="p-4 rounded-[18px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-sm flex items-center gap-3 font-medium">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- POS & Table Floor Plan Quick Action Strip -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-[18px] bg-[#007AFF]/[0.06] border border-[#007AFF]/15">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF] text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="monitor" class="w-4.5 h-4.5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-[13.5px] text-black dark:text-white">{{ __('storefront.backoffice.reservations.pos_strip_title') }}</h3>
                    <p class="text-[11.5px] text-black/60 dark:text-white/60">{{ __('storefront.backoffice.reservations.pos_strip_desc') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                <a href="{{ route('pos.tables.index') }}"
                    class="h-9 px-3.5 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5 text-black dark:text-white text-[12px] font-semibold flex items-center gap-1.5 transition shadow-xs">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                    <span>{{ __('storefront.backoffice.reservations.btn_pos_tables') }}</span>
                </a>
                <a href="{{ route('pos.terminal') }}"
                    class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[12px] font-semibold flex items-center gap-1.5 transition shadow-xs active:scale-95">
                    <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                    <span>{{ __('storefront.backoffice.reservations.btn_pos_terminal') }}</span>
                </a>
            </div>
        </div>

        <!-- Apple Bento Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div
                class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-2">
                <div class="flex items-center justify-between text-[#007AFF]">
                    <span class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('storefront.backoffice.reservations.kpi_today') }}</span>
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div class="text-3xl font-extrabold text-black dark:text-white tabular-nums tracking-tight">
                    {{ $todayCount }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('storefront.backoffice.reservations.kpi_today_desc') }}</p>
            </div>

            <div
                class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-2">
                <div class="flex items-center justify-between text-amber-500">
                    <span class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('storefront.backoffice.reservations.kpi_pending') }}</span>
                    <i data-lucide="clock" class="w-4 h-4"></i>
                </div>
                <div class="text-3xl font-extrabold text-amber-500 tabular-nums tracking-tight">
                    {{ $pendingCount }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('storefront.backoffice.reservations.kpi_pending_desc') }}</p>
            </div>

            <div
                class="bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/5 dark:border-white/10 p-5 shadow-sm space-y-2">
                <div class="flex items-center justify-between text-[#5856D6]">
                    <span class="text-xs font-bold uppercase tracking-wider text-black/50 dark:text-white/50">{{ __('storefront.backoffice.reservations.kpi_tables') }}</span>
                    <i data-lucide="layout-grid" class="w-4 h-4"></i>
                </div>
                <div class="text-3xl font-extrabold text-[#5856D6] tabular-nums tracking-tight">
                    {{ $tables->count() }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('storefront.backoffice.reservations.kpi_tables_desc') }}</p>
            </div>
        </div>

        <!-- Filters & Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
                <a href="{{ route('storefront.reservations.index', ['tab' => 'all']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'all' ? 'bg-black dark:bg-white text-white dark:text-black shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    {{ __('storefront.backoffice.reservations.tab_all') }}
                </a>
                <a href="{{ route('storefront.reservations.index', ['tab' => 'today']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'today' ? 'bg-[#007AFF] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    {{ __('storefront.backoffice.reservations.tab_today', ['count' => $todayCount]) }}
                </a>
                <a href="{{ route('storefront.reservations.index', ['tab' => 'pending']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    {{ __('storefront.backoffice.reservations.tab_pending', ['count' => $pendingCount]) }}
                </a>
                <a href="{{ route('storefront.reservations.index', ['tab' => 'confirmed']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'confirmed' ? 'bg-[#34C759] text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    {{ __('storefront.backoffice.reservations.tab_confirmed') }}
                </a>
                <a href="{{ route('storefront.reservations.index', ['tab' => 'completed']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'completed' ? 'bg-black dark:bg-white text-white dark:text-black shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    {{ __('storefront.backoffice.reservations.tab_completed') }}
                </a>
                <a href="{{ route('storefront.reservations.index', ['tab' => 'cancelled']) }}"
                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition whitespace-nowrap {{ $statusTab === 'cancelled' ? 'bg-red-500 text-white shadow-sm' : 'bg-black/5 dark:bg-white/5 text-black/60 dark:text-white/60 hover:bg-black/10' }}">
                    {{ __('storefront.backoffice.reservations.tab_cancelled') }}
                </a>
            </div>

            <form method="GET" action="{{ route('storefront.reservations.index') }}" class="relative min-w-[240px]">
                <input type="hidden" name="tab" value="{{ $statusTab }}">
                <i data-lucide="search"
                    class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ __('storefront.backoffice.reservations.search_placeholder') }}"
                    class="w-full h-10 pl-9 pr-3 rounded-full bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
            </form>
        </div>

        <!-- Reservations Table / Cards -->
        <div
            class="bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/5 dark:border-white/10 shadow-sm overflow-hidden">
            @if ($reservations->isEmpty())
                <div class="p-12 text-center space-y-3">
                    <div
                        class="w-12 h-12 rounded-full bg-black/5 dark:bg-white/5 flex items-center justify-center mx-auto text-black/30 dark:text-white/30">
                        <i data-lucide="calendar-x" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-bold text-black dark:text-white">{{ __('storefront.backoffice.reservations.empty_title') }}</h3>
                    <p class="text-xs text-black/50 dark:text-white/50 max-w-sm mx-auto">
                        {{ __('storefront.backoffice.reservations.empty_desc') }}
                    </p>
                </div>
            @else
                {{-- DESKTOP TABLE VIEW (>= md) --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 text-black/50 dark:text-white/50 uppercase font-bold text-[11px] tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4">{{ __('storefront.backoffice.reservations.col_code_time') }}</th>
                                <th class="py-3.5 px-4">{{ __('storefront.backoffice.reservations.col_customer') }}</th>
                                <th class="py-3.5 px-4">{{ __('storefront.backoffice.reservations.col_guests') }}</th>
                                <th class="py-3.5 px-4">{{ __('storefront.backoffice.reservations.col_table_service') }}</th>
                                <th class="py-3.5 px-4">{{ __('storefront.backoffice.reservations.col_status') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('storefront.backoffice.reservations.col_action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5 dark:divide-white/5">
                            @foreach ($reservations as $rsv)
                                <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                                    <td class="py-4 px-4 align-top">
                                        <span
                                            class="font-bold text-black dark:text-white font-mono text-[13px] block">{{ $rsv->reservation_code }}</span>
                                        <span
                                            class="text-black/60 dark:text-white/60 block mt-0.5">{{ $rsv->reservation_date->translatedFormat('d M Y') }}</span>
                                        <span
                                            class="inline-block px-2 py-0.5 rounded bg-[#007AFF]/10 text-[#007AFF] font-bold text-[10.5px] mt-1">
                                            {{ $rsv->time_slot }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 align-top">
                                        <span
                                            class="font-bold text-black dark:text-white block">{{ $rsv->customer_name }}</span>
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $rsv->customer_phone);
                                            if (str_starts_with($cleanPhone, '0')) {
                                                $cleanPhone = '62' . substr($cleanPhone, 1);
                                            }
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($rsv->customer_name) }},%20konfirmasi%20reservasi%20{{ $rsv->reservation_code }}..."
                                            target="_blank"
                                            class="inline-flex items-center gap-1 text-[#25D366] hover:underline text-[11.5px] mt-0.5">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                            <span>+{{ $rsv->customer_phone }}</span>
                                        </a>
                                        @if ($rsv->notes)
                                            <p
                                                class="text-[11.5px] text-black/50 dark:text-white/50 italic mt-1 max-w-xs line-clamp-2">
                                                "{{ $rsv->notes }}"</p>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 align-top font-semibold text-black dark:text-white">
                                        <span class="inline-flex items-center gap-1">
                                            <i data-lucide="users" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                            <span class="tabular-nums">{{ __('storefront.backoffice.reservations.guest_count_format', ['count' => $rsv->guest_count]) }}</span>
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 align-top">
                                        @if ($rsv->posTable)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-bold bg-[#5856D6]/10 text-[#5856D6]">
                                                <i data-lucide="layout-grid" class="w-3 h-3"></i>
                                                <span>{{ __('storefront.backoffice.reservations.table_label', ['num' => $rsv->posTable->table_number, 'name' => $rsv->posTable->name]) }}
                                                </span>
                                            </span>
                                        @elseif($rsv->product)
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11.5px] font-bold bg-[#007AFF]/10 text-[#007AFF]">
                                                <i data-lucide="sparkles" class="w-3 h-3"></i>
                                                <span>{{ $rsv->product->name }}</span>
                                            </span>
                                        @else
                                            <button type="button" @click="openAssign({{ Js::from($rsv) }})"
                                                class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-[#007AFF] hover:underline">
                                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('storefront.backoffice.reservations.btn_select_table') }}</span>
                                            </button>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 align-top">
                                        @if ($rsv->status === 'confirmed')
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">{{ __('storefront.backoffice.reservations.status_confirmed') }}</span>
                                        @elseif($rsv->status === 'seated')
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#5856D6]/15 text-[#5856D6]">{{ __('storefront.backoffice.reservations.status_seated') }}</span>
                                        @elseif($rsv->status === 'completed')
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">{{ __('storefront.backoffice.reservations.status_completed') }}</span>
                                        @elseif($rsv->status === 'cancelled')
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-500/15 text-red-600">{{ __('storefront.backoffice.reservations.status_cancelled') }}</span>
                                        @elseif($rsv->status === 'no_show')
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-500/15 text-gray-600">{{ __('storefront.backoffice.reservations.status_no_show') }}</span>
                                        @else
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400">{{ __('storefront.backoffice.reservations.status_pending') }}</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 align-top text-right">
                                        <div class="flex items-center justify-end gap-1.5" x-data="{ openMenu: false }">
                                            @if ($rsv->status === 'pending_confirmation')
                                                <form action="{{ route('storefront.reservations.status', $rsv) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button type="submit"
                                                        class="px-2.5 py-1 rounded-[8px] bg-[#34C759] hover:bg-[#2EB04E] text-white font-bold text-[11px] transition">
                                                        {{ __('storefront.backoffice.reservations.action_confirm') }}
                                                    </button>
                                                </form>
                                            @elseif($rsv->status === 'confirmed')
                                                <form action="{{ route('storefront.reservations.status', $rsv) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="seated">
                                                    <button type="submit"
                                                        class="px-2.5 py-1 rounded-[8px] bg-[#5856D6] hover:bg-[#4745B8] text-white font-bold text-[11px] transition">
                                                        {{ __('storefront.backoffice.reservations.action_seat') }}
                                                    </button>
                                                </form>
                                            @elseif($rsv->status === 'seated')
                                                <form action="{{ route('storefront.reservations.status', $rsv) }}"
                                                    method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit"
                                                        class="px-2.5 py-1 rounded-[8px] bg-black dark:bg-white text-white dark:text-black font-bold text-[11px] transition">
                                                        {{ __('storefront.backoffice.reservations.action_complete') }}
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($rsv->pos_table_id)
                                                <a href="{{ route('pos.terminal', ['table_id' => $rsv->pos_table_id, 'reservation_id' => $rsv->id]) }}"
                                                    class="px-2.5 py-1 rounded-[8px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] font-bold text-[11px] transition flex items-center gap-1"
                                                    title="Buka Meja ini di Terminal POS Kasir">
                                                    <i data-lucide="calculator" class="w-3 h-3"></i>
                                                    <span>{{ __('storefront.backoffice.reservations.btn_open_pos') }}</span>
                                                </a>
                                            @endif

                                            {{-- Dropdown for other actions --}}
                                            <div class="relative">
                                                <button type="button" @click="openMenu = !openMenu"
                                                    class="p-1 rounded hover:bg-black/5 dark:hover:bg-white/10 text-black/50">
                                                    <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                                </button>
                                                <div x-show="openMenu" @click.away="openMenu = false" x-transition
                                                    class="absolute right-0 mt-1 w-36 bg-white dark:bg-[#2C2C2E] rounded-[14px] shadow-lg border border-black/10 dark:border-white/10 p-1.5 z-20 text-left text-[11.5px] space-y-0.5">
                                                    @if ($rsv->pos_table_id)
                                                        <a href="{{ route('pos.terminal', ['table_id' => $rsv->pos_table_id, 'reservation_id' => $rsv->id]) }}"
                                                            class="w-full px-2.5 py-1.5 rounded-[8px] hover:bg-black/5 flex items-center gap-2 text-[#007AFF] font-semibold">
                                                            <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                                                            <span>{{ __('storefront.backoffice.reservations.btn_open_pos') }}</span>
                                                        </a>
                                                    @endif
                                                    <button type="button"
                                                        @click="openMenu = false; openAssign({{ Js::from($rsv) }})"
                                                        class="w-full px-2.5 py-1.5 rounded-[8px] hover:bg-black/5 flex items-center gap-2 text-black dark:text-white">
                                                        <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                                                        <span>{{ __('storefront.backoffice.reservations.btn_change_table') }}</span>
                                                    </button>
                                                    @if (!in_array($rsv->status, ['cancelled', 'completed', 'no_show'], true))
                                                        <button type="button"
                                                            @click="openMenu = false; reservationToCancel = {{ json_encode(['id' => $rsv->id, 'code' => $rsv->reservation_code, 'customer_name' => $rsv->customer_name]) }}; cancelModalOpen = true;"
                                                            class="w-full px-2.5 py-1.5 rounded-[8px] hover:bg-red-50 dark:hover:bg-red-950/30 text-red-600 dark:text-red-400 flex items-center gap-2 cursor-pointer transition">
                                                            <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                                            <span>{{ __('storefront.backoffice.reservations.btn_cancel_rsv') }}</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- MOBILE CARD LIST (< md) --}}
                <div class="block md:hidden divide-y divide-black/5 dark:divide-white/5">
                    @foreach ($reservations as $rsv)
                        <div class="p-4 space-y-3" x-data="{ openMenu: false }">
                            {{-- Top row: Code, Date & Status --}}
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span
                                            class="font-bold text-black dark:text-white font-mono text-[13px]">{{ $rsv->reservation_code }}</span>
                                        <span
                                            class="px-2 py-0.5 rounded bg-[#007AFF]/10 text-[#007AFF] font-bold text-[10.5px]">
                                            {{ $rsv->time_slot }}
                                        </span>
                                    </div>
                                    <span
                                        class="text-xs text-black/55 dark:text-white/55 block mt-0.5">{{ $rsv->reservation_date->translatedFormat('d M Y') }}</span>
                                </div>
                                <div>
                                    @if ($rsv->status === 'confirmed')
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">{{ __('storefront.reservations.status_confirmed') }}</span>
                                    @elseif($rsv->status === 'seated')
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#5856D6]/15 text-[#5856D6]">{{ __('storefront.reservations.status_seated') }}</span>
                                    @elseif($rsv->status === 'completed')
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70">{{ __('storefront.reservations.status_completed') }}</span>
                                    @elseif($rsv->status === 'cancelled')
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-red-500/15 text-red-600">{{ __('storefront.reservations.status_cancelled') }}</span>
                                    @elseif($rsv->status === 'no_show')
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-gray-500/15 text-gray-600">{{ __('storefront.reservations.status_no_show') }}</span>
                                    @else
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400">{{ __('storefront.reservations.status_pending') }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Middle row: Customer & Table info --}}
                            <div
                                class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-1.5 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-black dark:text-white">{{ $rsv->customer_name }}</span>
                                    <span
                                        class="font-semibold text-black/70 dark:text-white/70 inline-flex items-center gap-1">
                                        <i data-lucide="users" class="w-3.5 h-3.5 text-black/40 dark:text-white/40"></i>
                                        <span class="tabular-nums">{{ __('storefront.reservations.guest_count_mobile', ['count' => $rsv->guest_count]) }}</span>
                                    </span>
                                </div>

                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $rsv->customer_phone);
                                    if (str_starts_with($cleanPhone, '0')) {
                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                    }
                                @endphp
                                <div class="flex items-center justify-between">
                                    <a href="https://wa.me/{{ $cleanPhone }}?text=Halo%20{{ urlencode($rsv->customer_name) }},%20konfirmasi%20reservasi%20{{ $rsv->reservation_code }}..."
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-[#25D366] hover:underline text-[11.5px]">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        <span>+{{ $rsv->customer_phone }}</span>
                                    </a>

                                    <div>
                                        @if ($rsv->posTable)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#5856D6]/10 text-[#5856D6]">
                                                <i data-lucide="layout-grid" class="w-3 h-3"></i>
                                                <span>{{ __('storefront.reservations.table_label_short', ['num' => $rsv->posTable->table_number, 'name' => $rsv->posTable->name]) }}</span>
                                            </span>
                                        @elseif($rsv->product)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF]">
                                                <i data-lucide="sparkles" class="w-3 h-3"></i>
                                                <span>{{ $rsv->product->name }}</span>
                                            </span>
                                        @else
                                            <button type="button" @click="openAssign({{ Js::from($rsv) }})"
                                                class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-[#007AFF] hover:underline">
                                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('storefront.reservations.btn_select_table') }}</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if ($rsv->notes)
                                    <p
                                        class="text-[11.5px] text-black/50 dark:text-white/50 italic pt-1 border-t border-black/5 dark:border-white/5">
                                        "{{ $rsv->notes }}"
                                    </p>
                                @endif
                            </div>

                            {{-- Bottom row: Quick Actions --}}
                            <div class="flex items-center justify-between gap-2 pt-1">
                                <div>
                                    @if ($rsv->status === 'pending_confirmation')
                                        <form action="{{ route('storefront.reservations.status', $rsv) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit"
                                                class="px-3.5 py-1.5 rounded-[10px] bg-[#34C759] hover:bg-[#2EB04E] text-white font-bold text-xs transition">
                                                {{ __('storefront.reservations.action_confirm') }}
                                            </button>
                                        </form>
                                    @elseif($rsv->status === 'confirmed')
                                        <form action="{{ route('storefront.reservations.status', $rsv) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="seated">
                                            <button type="submit"
                                                class="px-3.5 py-1.5 rounded-[10px] bg-[#5856D6] hover:bg-[#4745B8] text-white font-bold text-xs transition">
                                                {{ __('storefront.reservations.action_seat') }}
                                            </button>
                                        </form>
                                    @elseif($rsv->status === 'seated')
                                        <form action="{{ route('storefront.reservations.status', $rsv) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit"
                                                class="px-3.5 py-1.5 rounded-[10px] bg-black dark:bg-white text-white dark:text-black font-bold text-xs transition">
                                                {{ __('storefront.reservations.action_complete') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <div class="relative">
                                    <button type="button" @click="openMenu = !openMenu"
                                        class="p-2 rounded-[10px] border border-black/10 dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/10 text-black/60 dark:text-white/60">
                                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                    </button>
                                    <div x-show="openMenu" @click.away="openMenu = false" x-transition
                                        class="absolute right-0 bottom-full mb-1 w-40 bg-white dark:bg-[#2C2C2E] rounded-[14px] shadow-lg border border-black/10 dark:border-white/10 p-1.5 z-20 text-left text-xs space-y-0.5">
                                        @if ($rsv->pos_table_id)
                                            <a href="{{ route('pos.terminal', ['table_id' => $rsv->pos_table_id, 'reservation_id' => $rsv->id]) }}"
                                                class="w-full px-2.5 py-1.5 rounded-[8px] hover:bg-black/5 flex items-center gap-2 text-[#007AFF] font-semibold">
                                                <i data-lucide="calculator" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('storefront.reservations.btn_open_pos') }}</span>
                                            </a>
                                        @endif
                                        <button type="button"
                                            @click="openMenu = false; openAssign({{ Js::from($rsv) }})"
                                            class="w-full px-2.5 py-1.5 rounded-[8px] hover:bg-black/5 flex items-center gap-2 text-black dark:text-white">
                                            <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                                            <span>{{ __('storefront.reservations.btn_change_table') }}</span>
                                        </button>
                                        @if (!in_array($rsv->status, ['cancelled', 'completed', 'no_show'], true))
                                            <button type="button"
                                                @click="openMenu = false; reservationToCancel = {{ json_encode(['id' => $rsv->id, 'code' => $rsv->reservation_code, 'customer_name' => $rsv->customer_name]) }}; cancelModalOpen = true;"
                                                class="w-full px-2.5 py-1.5 rounded-[8px] hover:bg-red-50 dark:hover:bg-red-950/30 text-red-600 dark:text-red-400 flex items-center gap-2 cursor-pointer transition">
                                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                                <span>{{ __('storefront.reservations.btn_cancel_rsv') }}</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="p-4 border-t border-black/5 dark:border-white/5">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Assign Table (Apple HIG Multi-Device Dialog) -->
        <template x-teleport="body">
            <div x-show="showAssignModal" x-cloak
                class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4"
                role="dialog"
                aria-modal="true">

                {{-- Backdrop Blur Overlay --}}
                <div x-show="showAssignModal"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @click="showAssignModal = false"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

                <div @click.away="showAssignModal = false"
                    class="relative z-10 w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4 max-h-[92vh] overflow-y-auto">
                    {{-- Mobile Grab Bar --}}
                    <div class="w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mb-1 sm:hidden"></div>

                    <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                        <h3 class="text-base font-bold text-black dark:text-white">{{ __('storefront.reservations.modal_assign_title') }}</h3>
                        <button type="button" @click="showAssignModal = false"
                            class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white cursor-pointer transition">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <template x-if="selectedReservation">
                        <form :action="'/storefront/reservations/' + selectedReservation.id + '/assign-table'" method="POST"
                            class="space-y-4">
                            @csrf
                            <div>
                                <span class="text-[12px] text-black/50 dark:text-white/50 block">{{ __('storefront.reservations.modal_assign_rsv_label') }}</span>
                                <p class="font-bold text-sm text-black dark:text-white"
                                    x-text="selectedReservation.customer_name + ' (' + selectedReservation.guest_count + ' ' + ({{ Js::from(trim(str_replace(':count', '', __('storefront.reservations.guest_count_mobile', ['count' => ''])))) }}) + ')'">
                                </p>
                            </div>

                            <div>
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('storefront.reservations.modal_assign_select_table') }}</label>
                                <select name="pos_table_id" required
                                    class="w-full h-11 px-3.5 rounded-[12px] bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 text-[16px] sm:text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                    <option value="">{{ __('storefront.reservations.modal_assign_choose_placeholder') }}</option>
                                    @foreach ($tables as $tbl)
                                        <option value="{{ $tbl->id }}">
                                            {{ __('storefront.reservations.modal_assign_table_option', ['num' => $tbl->table_number, 'name' => $tbl->name, 'capacity' => $tbl->capacity]) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" @click="showAssignModal = false"
                                    class="px-4 py-2 rounded-full text-xs font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white transition cursor-pointer">{{ __('storefront.reservations.modal_assign_btn_cancel') }}</button>
                                <button type="submit"
                                    class="px-5 py-2 rounded-full bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold transition shadow-sm cursor-pointer">{{ __('storefront.reservations.modal_assign_btn_save') }}</button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </template>

        {{-- APPLE ALERT CONFIRMATION DIALOG (CANCEL RESERVATION) --}}
        <template x-teleport="body">
            <div x-show="cancelModalOpen" x-cloak
                class="fixed inset-0 z-[220] flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true">

                {{-- Backdrop Blur Overlay --}}
                <div x-show="cancelModalOpen"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    @click="cancelModalOpen = false"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

                <div class="relative z-10 w-full max-w-md bg-white dark:bg-[#1C1C1E] rounded-[24px] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-4"
                    @click.outside="cancelModalOpen = false">
                    <div class="flex items-start gap-3.5">
                        <div
                            class="w-10 h-10 rounded-[14px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="calendar-x" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">{{ __('storefront.reservations.modal_cancel_title') }}</h3>
                            <p class="text-[13px] text-black/60 dark:text-white/60 mt-1">
                                {!! str_replace(
                                    [':code', ':name'],
                                    ['<strong class="text-black dark:text-white font-semibold" x-text="reservationToCancel?.code"></strong>', '<span class="font-medium text-black dark:text-white" x-text="reservationToCancel?.customer_name"></span>'],
                                    e(__('storefront.reservations.modal_cancel_desc', ['code' => ':code', 'name' => ':name']))
                                ) !!}
                            </p>
                        </div>
                    </div>

                    {{-- PENENANG JIWA MICROCOPY --}}
                    <div
                        class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-start gap-2.5 text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759] shrink-0 mt-0.5"></i>
                        <span>{{ __('storefront.reservations.modal_cancel_peace_of_mind') }}</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="cancelModalOpen = false"
                            class="px-4 py-2 rounded-full text-[13px] font-semibold text-black/60 hover:text-black dark:text-white/60 dark:hover:text-white transition cursor-pointer">
                            {{ __('storefront.reservations.modal_cancel_btn_close') }}
                        </button>
                        <template x-if="reservationToCancel">
                            <form :action="'/storefront/reservations/' + reservationToCancel.id + '/status'" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit"
                                    class="px-5 py-2.5 rounded-full bg-[#FF3B30] hover:bg-[#E0352B] text-white text-[13px] font-bold transition shadow-sm cursor-pointer">
                                    {{ __('storefront.reservations.modal_cancel_btn_confirm') }}
                                </button>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection
