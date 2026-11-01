<!-- ======================================================== -->
    <!-- MODAL 4: EDIT LOKASI KANTOR                              -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showEditLocationModal" 
            x-cloak
            @keydown.escape.window="showEditLocationModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showEditLocationModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="showEditLocationModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showEditLocationModal = false">
                <div x-show="showEditLocationModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[22px] border border-black/10 dark:border-white/10 shadow-[0_24px_48px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col max-h-[88vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.modal_edit_location_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.modal_edit_location_subtitle') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showEditLocationModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" :action="locationEditAction"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-5 overflow-y-auto space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_location_name') }} <span class="text-[#FF3B30]">*</span></label>
                    <input type="text" name="name" required :value="selectedLocation?.name || ''"
                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13.5px] font-semibold text-black dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_city') }}</label>
                        <input type="text" name="city" :value="selectedLocation?.city || ''"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_geofence_radius') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="number" name="geofence_radius_meters" required min="10" max="5000" :value="selectedLocation?.geofence_radius_meters || 50"
                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] font-semibold text-black dark:text-white tabular-nums">
                    </div>
                </div>

                <div>
                    <label class="block text-[12px] font-bold uppercase tracking-wider text-black/70 dark:text-white/70 mb-1.5">{{ __('hrm.field_address') }}</label>
                    <textarea name="address" rows="2" :value="selectedLocation?.address || ''"
                        class="w-full p-3 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 text-[16px] sm:text-[13px] text-black dark:text-white"></textarea>
                </div>

                <!-- 1-Click GPS Coordinate Detector -->
                <div class="p-3.5 rounded-[14px] bg-[#007AFF]/[0.04] border border-[#007AFF]/15 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[12px] font-bold text-[#007AFF] flex items-center gap-1.5">
                            <i data-lucide="crosshair" class="w-4 h-4"></i>
                            <span>{{ __('hrm.gps_coordinates_label') }}</span>
                        </span>
                        <button type="button" @click="fillGpsToLocationForm('edit')"
                            class="h-8 px-3 rounded-[9px] bg-[#007AFF] text-white text-[11.5px] font-bold flex items-center gap-1 hover:bg-[#0071E3] transition active:scale-[0.98] cursor-pointer">
                            <i data-lucide="navigation" class="w-3.5 h-3.5"></i>
                            <span>{{ __('hrm.btn_use_my_gps') }}</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">Latitude</label>
                            <input type="number" step="any" name="latitude" required :value="selectedLocation?.latitude"
                                class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-bold text-black dark:text-white">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-1">Longitude</label>
                            <input type="number" step="any" name="longitude" required :value="selectedLocation?.longitude"
                                class="w-full h-11 sm:h-10 px-3 rounded-[10px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[16px] sm:text-[12.5px] font-mono font-bold text-black dark:text-white">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <label class="flex items-center gap-2 text-[12.5px] font-bold text-black dark:text-white cursor-pointer">
                        <input type="checkbox" name="is_primary" value="1" :checked="selectedLocation?.is_primary" class="w-4.5 h-4.5 rounded-[5px] text-[#FF2D55]">
                        <span>{{ __('hrm.opt_make_primary') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-[12.5px] font-bold text-black dark:text-white cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" :checked="selectedLocation?.is_active" class="w-4.5 h-4.5 rounded-[5px] text-[#34C759]">
                        <span>{{ __('hrm.opt_status_active') }}</span>
                    </label>
                </div>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="showEditLocationModal = false"
                        class="h-11 sm:h-10 px-4.5 rounded-[11px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="isSubmittingForm"
                        :class="isSubmittingForm ? 'opacity-60 cursor-not-allowed' : ''"
                        class="h-11 sm:h-10 px-5 rounded-[11px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] transition active:scale-[0.98] shadow-[0_2px_8px_rgba(0,122,255,0.3)] cursor-pointer flex items-center justify-center gap-2">
                        <template x-if="!isSubmittingForm">
                            <span>{{ __('hrm.btn_update_location') }}</span>
                        </template>
                        <template x-if="isSubmittingForm">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('hrm.saving') }}</span>
                            </div>
                        </template>
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </template>
