<!-- ===================================================== -->
    <!-- MODAL: DATA LAYANAN INDUSTRI (BENGKEL & LAUNDRY) -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showServiceVerticalModal" x-cloak role="dialog" aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showServiceVerticalModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showServiceVerticalModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showServiceVerticalModal = false"></div>

            <!-- Centered Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6"
                @click.self="showServiceVerticalModal = false">
                <div class="pos-modal-panel relative w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-2xl text-black dark:text-white">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.32l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.32 4.486c.049.58.025 1.193-.14 1.743" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-[15px] text-black dark:text-white leading-tight">
                            @if ($isWorkshop && !$isLaundry)
                                {{ __('pos.workshop_spk_title') }}
                            @elseif ($isLaundry && !$isWorkshop)
                                {{ __('pos.laundry_service_title') }}
                            @elseif ($isWorkshop && $isLaundry)
                                {{ __('pos.workshop_laundry_title') }}
                            @else
                                {{ __('pos.service_notes_staff_title') }}
                            @endif
                        </h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50">
                            @if ($isWorkshop && !$isLaundry)
                                {{ __('pos.workshop_form_desc') }}
                            @elseif ($isLaundry && !$isWorkshop)
                                {{ __('pos.laundry_form_desc') }}
                            @elseif ($isWorkshop && $isLaundry)
                                {{ __('pos.workshop_laundry_form_desc') }}
                            @else
                                {{ __('pos.staff_assignment_form_desc') }}
                            @endif
                        </p>
                    </div>
                </div>
                <button type="button" @click="showServiceVerticalModal = false"
                    class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Segmented Tab Selector (Apple HIG) - Displayed only if hybrid workshop and laundry -->
            @if ($isWorkshop && $isLaundry)
            <div class="inline-flex w-full p-0.5 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] text-[12px] font-medium">
                <button type="button" @click="serviceVerticalTab = 'workshop'"
                    :class="serviceVerticalTab === 'workshop' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60'"
                    class="flex-1 py-1.5 rounded-[8px] transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V6.75A2.25 2.25 0 0012 4.5H6.75A2.25 2.25 0 004.5 6.75v12" />
                    </svg>
                    <span>{{ __('pos.workshop_vehicle') }}</span>
                </button>
                <button type="button" @click="serviceVerticalTab = 'laundry'"
                    :class="serviceVerticalTab === 'laundry' ? 'bg-white dark:bg-[#3A3A3C] text-black dark:text-white shadow-[0_1px_2px_rgba(0,0,0,0.08)]' : 'text-black/60 dark:text-white/60'"
                    class="flex-1 py-1.5 rounded-[8px] transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <span>{{ __('pos.laundry_service') }}</span>
                </button>
            </div>
            @endif

            <!-- Tab 1 Content: Bengkel Otomotif -->
            @if ($isWorkshop)
            <div x-show="serviceVerticalTab === 'workshop'" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.license_plate') }}</label>
                            @feature('vertical.workshop_service_history')
                            <template x-if="vehicleLicensePlate && vehicleLicensePlate.length >= 3">
                                <button type="button" @click="checkVehicleHistory()" class="text-[11px] font-bold text-[#007AFF] hover:underline inline-flex items-center gap-1">
                                    <i data-lucide="history" class="w-3 h-3"></i> {{ __('pos.check_history') }}
                                </button>
                            </template>
                            @endfeature
                        </div>
                        <input type="text" x-model="vehicleLicensePlate" placeholder="Cth: B 1234 XYZ"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[13px] font-mono font-bold uppercase text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.vehicle_model') }}</label>
                        <input type="text" x-model="vehicleModel" placeholder="Cth: Honda Vario 160"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.odometer_km') }}</label>
                        <input type="number" x-model="vehicleMileage" placeholder="Cth: 24500"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[13px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.mechanic_technician') }}</label>
                        <select x-model="technicianId"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] cursor-pointer">
                            <option value="" class="bg-white dark:bg-[#1C1C1E]">{{ __('pos.select_mechanic_placeholder') }}</option>
                            <template x-for="t in technicians" :key="t.id">
                                <option :value="t.id" :selected="technicianId === t.id" x-text="t.name" class="bg-white dark:bg-[#1C1C1E]"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.service_complaint_spk_label') }}</label>
                    <textarea x-model="serviceNotes" rows="2" placeholder="Cth: Servis berkala, ganti oli mesin, cek bunyi di roda depan..."
                        class="w-full rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 p-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                </div>

                @feature('vertical.workshop_service_history')
                <!-- POPUP / MINI ACCORDION RIWAYAT SERVIS KENDARAAN TERDAHULU -->
                <div x-show="showVehicleHistoryPanel" class="rounded-[10px] bg-[#007AFF]/5 border border-[#007AFF]/15 p-3 space-y-2 mt-2" x-cloak>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="history" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span class="text-[12px] font-bold text-black dark:text-white">Riwayat Servis Kendaraan</span>
                            <span class="text-[11px] font-mono text-[#007AFF]" x-text="'(' + vehicleLicensePlate + ')'"></span>
                        </div>
                        <button type="button" @click="showVehicleHistoryPanel = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <div class="space-y-1.5 max-h-40 overflow-y-auto no-scrollbar">
                        <template x-if="vehicleHistoryRecords.length === 0">
                            <p class="text-[11px] text-black/50 dark:text-white/50 italic py-1">{{ __('pos.belum_ada_riwayat_servis_tercatat_untuk') }}</p>
                        </template>
                        <template x-for="(record, rIdx) in vehicleHistoryRecords" :key="rIdx">
                            <div class="p-2 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/5 dark:border-white/5 space-y-0.5 text-[11px]">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-black dark:text-white" x-text="record.date"></span>
                                    <span class="font-mono text-[#007AFF]" x-text="record.mileage ? record.mileage + ' KM' : '-'"></span>
                                </div>
                                <p class="text-black/60 dark:text-white/60 line-clamp-1" x-text="record.notes || '-'"></p>
                                <div class="text-[10px] text-black/40 dark:text-white/40 flex items-center justify-between">
                                    <span x-text="'Mekanik: ' + (record.mechanic || '-')"></span>
                                    <span class="font-semibold text-[#34C759]" x-text="record.total"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                @endfeature
            </div>
            @endif

            <!-- Tab 2 Content: Laundry -->
            @if ($isLaundry)
            <div x-show="serviceVerticalTab === 'laundry'" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.total_scale_kg') }}</label>
                        <input type="number" step="0.01" min="0" x-model="laundryWeightKg" placeholder="Cth: 4.50"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[13px] font-bold font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.laundry_rack_location') }}</label>
                        <input type="text" x-model="rackLocation" placeholder="Cth: Rak B-02 / Loker 05"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.estimated_finish_time') }}</label>
                        <input type="datetime-local" x-model="estimatedCompletionAt"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.laundry_status') }}</label>
                        <select x-model="laundryStatus"
                            class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] cursor-pointer">
                            <option value="received" class="bg-white dark:bg-[#1C1C1E]">{{ __('pos.status_received') }}</option>
                            <option value="washing" class="bg-white dark:bg-[#1C1C1E]">{{ __('pos.status_washing') }}</option>
                            <option value="drying" class="bg-white dark:bg-[#1C1C1E]">{{ __('pos.status_drying') }}</option>
                            <option value="ironing" class="bg-white dark:bg-[#1C1C1E]">{{ __('pos.status_ironing') }}</option>
                            <option value="ready" class="bg-white dark:bg-[#1C1C1E]">{{ __('pos.status_ready') }}</option>
                            <option value="completed" class="bg-white dark:bg-[#1C1C1E]">{{ __('pos.status_completed_taken') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tab 3 Content: Layanan Umum Non-Bengkel/Non-Laundry -->
            @if (!$isWorkshop && !$isLaundry)
            <div class="space-y-3">
                <div class="space-y-1">
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.service_staff_executor') }}</label>
                    <select x-model="technicianId"
                        class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] cursor-pointer">
                        <option value="" class="bg-white dark:bg-[#1C1C1E]">{{ __('pos.select_service_staff_placeholder') }}</option>
                        <template x-for="t in technicians" :key="t.id">
                            <option :value="t.id" :selected="technicianId === t.id" x-text="t.name" class="bg-white dark:bg-[#1C1C1E]"></option>
                        </template>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.service_execution_notes_label') }}</label>
                    <textarea x-model="serviceNotes" rows="3" placeholder="{{ __('pos.service_notes_placeholder') }}"
                        class="w-full rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 p-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                </div>
            </div>
            @endif

            <!-- Modal Footer -->
            <div class="flex items-center justify-between pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" @click="vehicleLicensePlate = ''; vehicleModel = ''; vehicleMileage = null; technicianId = ''; serviceNotes = ''; laundryWeightKg = null; rackLocation = ''; estimatedCompletionAt = ''; laundryStatus = 'received';"
                    class="h-9 px-3 rounded-[10px] text-xs font-semibold text-[#FF3B30] hover:bg-[#FF3B30]/10 transition">
                    {{ __('pos.delete_service_data') }}
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showServiceVerticalModal = false"
                        class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">{{ __('pos.cancel') }}</button>
                    <button type="button" @click="showServiceVerticalModal = false"
                        class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm">
                        {{ __('pos.save_service_data') }}
                    </button>
                </div>
            </div>
                </div>
            </div>
        </div>
    </template>
