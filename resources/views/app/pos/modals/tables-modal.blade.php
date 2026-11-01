<!-- ===================================================== -->
    <!-- MODAL: RESTAURANT TABLES & STOREFRONT RESERVATIONS   -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showTablesModal" x-cloak role="dialog" aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showTablesModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showTablesModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showTablesModal = false"></div>

            <!-- Centered Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6"
                @click.self="showTablesModal = false">
                <div class="pos-modal-panel relative w-full max-w-4xl bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-2xl text-black dark:text-white flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-black dark:text-white">{{ __('pos.table_reservations_mgmt_title') }}</h3>
                        <p class="text-xs text-black/50 dark:text-white/50">{{ __('pos.table_reservations_mgmt_desc') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('storefront.reservations.index') }}" target="_blank"
                        class="text-xs font-semibold text-[#5856D6] hover:underline flex items-center gap-1 hidden sm:flex"
                        title="{{ __('pos.open_reservation_module') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <span>{{ __('pos.reservations_data_link') }}</span>
                    </a>
                    <span class="text-black/20 dark:text-white/20 hidden sm:inline">•</span>
                    @if (\App\Support\Context::hasPermission('pos.tables'))
                        <a href="{{ route('pos.tables.index') }}" target="_blank"
                            class="text-xs font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                            <span>{{ __('pos.manage_tables_qr') }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    @endif
                    <button type="button" @click="showTablesModal = false"
                        class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Apple HIG Segmented Bar -->
            <div class="flex items-center justify-between gap-2 p-1 bg-black/[0.04] dark:bg-white/[0.06] rounded-[14px] shrink-0">
                <div class="flex items-center gap-1">
                    <button type="button" @click="tableModalTab = 'tables'"
                        :class="tableModalTab === 'tables' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                        class="px-3.5 py-1.5 rounded-[10px] text-xs transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#34C759]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                        </svg>
                        <span>{{ __('pos.table_status_bill') }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-black/5 dark:bg-white/10" x-text="tables.length"></span>
                    </button>

                    <button type="button" @click="tableModalTab = 'reservations'"
                        :class="tableModalTab === 'reservations' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white font-medium'"
                        class="px-3.5 py-1.5 rounded-[10px] text-xs transition flex items-center gap-1.5 relative">
                        <svg class="w-3.5 h-3.5 text-[#5856D6]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <span>{{ __('pos.today_storefront_reservations') }}</span>
                        <span x-show="todayReservations.length > 0" x-text="todayReservations.length"
                            class="px-1.5 py-0.2 rounded-full bg-[#5856D6] text-white font-bold text-[10px]"></span>
                    </button>
                </div>

                <div class="text-[11px] text-black/40 dark:text-white/40 hidden sm:block pr-2">
                    <span x-show="tableModalTab === 'tables'">{{ __('pos.click_table_to_connect') }}</span>
                    <span x-show="tableModalTab === 'reservations'">{{ __('pos.guest_arrived_checkin') }}</span>
                </div>
            </div>

            <!-- TAB 1: Tables Grid -->
            <div x-show="tableModalTab === 'tables'" class="flex-1 overflow-y-auto pr-1">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <template x-for="tbl in tables" :key="tbl.id">
                        <div class="p-3.5 rounded-[14px] border transition flex flex-col justify-between select-none"
                            :class="tbl.status === 'available' ? 'bg-[#34C759]/5 border-[#34C759]/30' : (tbl
                                .status === 'waiting_payment' ? 'bg-[#FF9500]/10 border-[#FF9500]/40' :
                                'bg-[#007AFF]/10 border-[#007AFF]/40')">
                            <div>
                                <div class="flex items-start justify-between gap-1">
                                    <span class="font-extrabold text-base text-black dark:text-white"
                                        x-text="'Meja ' + tbl.table_number"></span>
                                    <span class="text-[9px] font-bold uppercase px-1.5 py-0.5 rounded"
                                        :class="tbl.status === 'available' ? 'bg-[#34C759]/20 text-[#34C759]' : (tbl
                                            .status === 'waiting_payment' ? 'bg-[#FF9500]/20 text-[#FF9500]' :
                                             'bg-[#007AFF]/20 text-[#007AFF]')"
                                        x-text="tbl.status"></span>
                                </div>
                                <div class="text-[11px] text-black/50 dark:text-white/50 mt-0.5"
                                    x-text="(tbl.name ? tbl.name + ' • ' : '') + tbl.capacity + ' Kursi'"></div>

                                <!-- Reservation Indicator on Table Card -->
                                <div x-show="tbl.today_reservation"
                                    class="mt-2 p-1.5 rounded-[8px] bg-[#5856D6]/10 border border-[#5856D6]/20 text-[10px] text-[#5856D6] space-y-0.5">
                                    <div class="flex items-center justify-between font-bold">
                                        <span class="inline-flex items-center gap-1 truncate">
                                            <i data-lucide="calendar" class="w-3 h-3 text-[#5856D6]"></i>
                                            <span>{{ __('pos.today_booking') }}</span>
                                        </span>
                                        <span x-text="tbl.today_reservation?.time_slot"></span>
                                    </div>
                                    <div class="text-[10px] text-black/70 dark:text-white/70 truncate"
                                        x-text="(tbl.today_reservation?.customer_name || '') + ' (' + (tbl.today_reservation?.guest_count || 1) + ' org)'"></div>
                                </div>

                                <!-- Session info if occupied -->
                                <div x-show="tbl.active_session"
                                    class="mt-2 pt-2 border-t border-black/5 dark:border-white/5 space-y-1">
                                    <div class="text-[11px] font-bold text-black dark:text-white truncate"
                                        x-text="tbl.active_session?.customer_name"></div>
                                    <div class="text-xs font-extrabold text-[#007AFF] tabular-nums"
                                        x-text="formatRupiah(tbl.active_session?.total_amount || 0)"></div>
                                </div>
                            </div>

                            <!-- Card Actions -->
                            <div class="mt-3 pt-2 border-t border-black/5 dark:border-white/5 flex flex-col gap-1.5">
                                <template
                                    x-if="tbl.active_session && tbl.active_session.orders && tbl.active_session.orders.length > 0">
                                    <div class="flex flex-col gap-1.5">
                                        <button type="button" @click="loadTableOrderToCart(tbl)"
                                            class="w-full h-8 rounded-[8px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[11px] font-bold transition flex items-center justify-center gap-1.5 shadow-sm"
                                            title="{{ __('pos.load_table_orders_tooltip') }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                            </svg>
                                            <span>{{ __('pos.check_load_to_cart') }}</span>
                                        </button>
                                        <button type="button" @click="loadTableOrderToCart(tbl, true)"
                                            class="w-full h-7 rounded-[7px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-[11px] font-bold transition flex items-center justify-center gap-1">
                                            <span>{{ __('pos.quick_pay') }}</span>
                                        </button>
                                    </div>
                                </template>
                                <template
                                    x-if="!tbl.active_session || !tbl.active_session.orders || tbl.active_session.orders.length === 0">
                                    <button type="button" @click="selectTableForCart(tbl)"
                                        class="w-full h-8 rounded-[8px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black dark:text-white text-[11px] font-semibold transition">
                                        {{ __('pos.select_this_table_dine_in') }}
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- TAB 2: Today's Storefront Reservations -->
            <div x-show="tableModalTab === 'reservations'" class="flex-1 overflow-y-auto pr-1">
                <!-- Empty State -->
                <div x-show="!todayReservations || todayReservations.length === 0"
                    class="py-12 flex flex-col items-center justify-center text-center">
                    <div class="w-12 h-12 rounded-2xl bg-[#5856D6]/10 text-[#5856D6] flex items-center justify-center mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>
                    <h4 class="font-bold text-sm text-black dark:text-white">{{ __('pos.no_reservation_schedule_today') }}</h4>
                    <p class="text-xs text-black/50 dark:text-white/50 max-w-sm mt-1">{{ __('pos.no_today_storefront_bookings') }}</p>
                    <a href="{{ route('storefront.reservations.index') }}" target="_blank"
                        class="mt-4 px-3.5 py-1.5 rounded-[10px] bg-[#5856D6] hover:bg-[#4B49C2] text-white text-xs font-semibold transition inline-flex items-center gap-1.5 shadow-sm">
                        <span>{{ __('pos.open_reservation_module') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                    </a>
                </div>

                <!-- Reservations List -->
                <div x-show="todayReservations && todayReservations.length > 0" class="space-y-3">
                    <template x-for="rsv in todayReservations" :key="rsv.id">
                        <div class="p-4 rounded-[14px] border border-black/10 dark:border-white/10 bg-black/[0.01] dark:bg-white/[0.02] hover:bg-black/[0.02] dark:hover:bg-white/[0.04] transition flex flex-col md:flex-row md:items-center justify-between gap-3">
                            <!-- Left: Guest Details & Booking Meta -->
                            <div class="space-y-1.5 min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <!-- Time Slot Pill -->
                                    <div class="px-2.5 py-0.5 rounded-full bg-[#5856D6]/15 text-[#5856D6] text-[11px] font-bold flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span x-text="rsv.time_slot"></span>
                                    </div>

                                    <!-- Guest Count -->
                                    <div class="px-2 py-0.5 rounded-full bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70 text-[11px] font-semibold">
                                        <span x-text="rsv.guest_count + ' {{ __('pos.guest_count_suffix') }}'"></span>
                                    </div>

                                    <!-- Status Badge -->
                                    <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full"
                                        :class="{
                                            'bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]': rsv.status === 'seated',
                                            'bg-[#007AFF]/20 text-[#007AFF]': rsv.status === 'confirmed',
                                            'bg-[#FF9500]/20 text-[#FF9500]': rsv.status === 'pending_confirmation'
                                        }"
                                        x-text="rsv.status === 'seated' ? '{{ __('pos.seating_status_seated') }}' : (rsv.status === 'confirmed' ? '{{ __('pos.seating_status_confirmed') }}' : '{{ __('pos.seating_status_pending') }}')"></span>

                                    <!-- Reservation Code -->
                                    <span class="font-mono text-[10px] text-black/40 dark:text-white/40" x-text="'#' + rsv.reservation_code"></span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <h4 class="font-bold text-sm text-black dark:text-white truncate" x-text="rsv.customer_name"></h4>
                                    <!-- WhatsApp Direct Link -->
                                    <template x-if="rsv.customer_phone">
                                        <a :href="'https://wa.me/' + rsv.customer_phone.replace(/[^0-9]/g, '')" target="_blank"
                                            class="text-[11px] font-medium text-[#34C759] hover:underline flex items-center gap-1"
                                            title="{{ __('pos.chat_whatsapp_customer') }}">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                            <span x-text="rsv.customer_phone"></span>
                                        </a>
                                    </template>
                                </div>

                                <!-- Assigned Table Display & Selector -->
                                <div class="text-xs text-black/60 dark:text-white/60 flex items-center gap-1.5">
                                    <span>{{ __('pos.table_placement_label') }}</span>
                                    <template x-if="rsv.pos_table_name">
                                        <span class="font-bold text-[#007AFF] bg-[#007AFF]/10 px-2 py-0.5 rounded-[6px]" x-text="rsv.pos_table_name"></span>
                                    </template>
                                    <template x-if="!rsv.pos_table_name">
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="text-[#FF9500] font-semibold italic">{{ __('pos.not_assigned_yet') }}</span>
                                            <select x-model="rsv.pos_table_id"
                                                class="h-7 text-xs rounded-[6px] border border-black/15 dark:border-white/15 bg-white dark:bg-[#1C1C1E] text-black dark:text-white px-2 py-0.5 focus:outline-none focus:ring-1 focus:ring-[#007AFF]">
                                                <option value="">{{ __('pos.select_table_placeholder') }}</option>
                                                <template x-for="tbl in tables" :key="tbl.id">
                                                    <option :value="tbl.id" x-text="'{{ __('pos.table') }} ' + tbl.table_number + (tbl.name ? ' (' + tbl.name + ')' : '') + ' • ' + tbl.capacity + ' {{ __('pos.seats_suffix') }}'"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="rsv.notes" class="text-[11px] text-black/50 dark:text-white/50 italic" x-text="'{{ __('pos.order_note_label') }} ' + rsv.notes"></div>
                            </div>

                            <!-- Right: Check-In & Action Buttons -->
                            <div class="flex items-center gap-2 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-black/5 dark:border-white/5">
                                <template x-if="rsv.status !== 'seated'">
                                    <button type="button" @click="seatReservation(rsv)"
                                        :disabled="isSeatingReservation"
                                        class="h-9 px-4 rounded-[10px] bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm disabled:opacity-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        <span x-text="isSeatingReservation ? '{{ __('pos.processing_status') }}' : '{{ __('pos.checkin_and_seat') }}'"></span>
                                    </button>
                                </template>

                                <template x-if="rsv.status === 'seated'">
                                    <div class="flex items-center gap-2">
                                        <button type="button"
                                            @click="const tbl = tables.find(t => t.id === rsv.pos_table_id); if (tbl) { selectTableForCart(tbl); } else { showTablesModal = false; }"
                                            class="h-9 px-3.5 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                            </svg>
                                            <span>{{ __('pos.open_cashier') }}</span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
                </div>
            </div>
        </div>
    </template>
