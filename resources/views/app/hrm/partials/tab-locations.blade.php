<!-- ======================================================== -->
    <!-- TAB 4: PENGATURAN LOKASI & GEOFENCE                      -->
    <!-- ======================================================== -->
    <div x-show="activeTab === 'locations'" class="space-y-4" x-transition.opacity style="display: none;">
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 overflow-hidden shadow-[0_2px_12px_rgba(0,0,0,0.03)]">
            <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#FF2D55]/10 text-[#FF2D55] flex items-center justify-center shrink-0">
                        <i data-lucide="map-pin" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.locations_geofence_title') }}</h3>
                        <p class="text-[12px] text-black/60 dark:text-white/60">{{ __('hrm.locations_geofence_sub') }}</p>
                    </div>
                </div>
                <button type="button" @click="showAddLocationModal = true"
                    class="h-10 px-4 rounded-[12px] bg-[#FF2D55] hover:bg-[#E02648] text-white text-[13px] font-bold flex items-center justify-center gap-2 shadow-[0_2px_8px_rgba(255,45,85,0.3)] transition active:scale-[0.98] cursor-pointer shrink-0">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>{{ __('hrm.btn_add_location') }}</span>
                </button>
            </div>

            <!-- Desktop View (>= 768px) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse min-w-[850px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.03] text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60">
                            <th class="py-3.5 px-4 sm:px-5">{{ __('hrm.col_location_name') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_address_city') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_gps_coords') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_geofence_radius') }}</th>
                            <th class="py-3.5 px-3">{{ __('hrm.col_primary_status') }}</th>
                            <th class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('hrm.col_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($allLocations as $loc)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                                <td class="py-3.5 px-4 sm:px-5 font-bold text-black dark:text-white">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $loc->name }}</span>
                                        @if($loc->is_primary)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF2D55]/15 text-[#FF2D55] border border-[#FF2D55]/30">{{ __('hrm.badge_primary_office') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 text-black/70 dark:text-white/70 text-[12.5px]">
                                    <div>{{ $loc->address ?: __('hrm.address_not_set') }}</div>
                                    <div class="text-[11px] text-black/50 dark:text-white/50">{{ $loc->city ?: __('hrm.field_city') }}</div>
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-mono text-[12px] text-black/80 dark:text-white/80">
                                    @if($loc->latitude && $loc->longitude)
                                        <div>Lat: {{ number_format((float)$loc->latitude, 6) }}</div>
                                        <div>Lng: {{ number_format((float)$loc->longitude, 6) }}</div>
                                    @else
                                        <span class="text-[#FF9500] font-sans text-[11.5px] font-semibold">{{ __('hrm.not_available') }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 tabular-nums font-bold text-black dark:text-white">
                                    {{ $loc->geofence_radius_meters ?: 50 }} {{ __('hrm.meters_unit') }}
                                </td>
                                <td class="py-3.5 px-3">
                                    @if($loc->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#34C759]">
                                            {{ __('hrm.status_active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50">
                                            {{ __('hrm.status_inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            @click="openEditLocation({{ Js::from($loc) }}, '{{ route('hrm.locations.update', $loc->id) }}')"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('hrm.btn_edit') }}"
                                            aria-label="{{ __('hrm.btn_edit') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        @if(! $loc->is_primary && $allLocations->count() > 1)
                                            <form method="POST" action="{{ route('hrm.locations.destroy', $loc->id) }}"
                                                onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_location')) }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#FF3B30] hover:bg-[#FF3B30]/10 dark:hover:bg-[#FF3B30]/20 transition-all cursor-pointer shrink-0"
                                                    title="{{ __('hrm.tooltip_delete_location') }}"
                                                    aria-label="{{ __('hrm.tooltip_delete_location') }}">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-black/50 dark:text-white/50 font-medium">
                                    {{ __('hrm.empty_locations_list') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Bento Card View (< 768px) -->
            <div class="md:hidden divide-y divide-black/5 dark:divide-white/5">
                @forelse($allLocations as $loc)
                    <div class="p-4 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-bold text-black dark:text-white text-[14px] truncate">{{ $loc->name }}</span>
                                @if($loc->is_primary)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF2D55]/15 text-[#FF2D55] border border-[#FF2D55]/30 shrink-0">{{ __('hrm.badge_primary_office') }}</span>
                                @endif
                            </div>
                            @if($loc->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#34C759] shrink-0">
                                    {{ __('hrm.status_active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50 shrink-0">
                                    {{ __('hrm.status_inactive') }}
                                </span>
                            @endif
                        </div>

                        <div class="text-[12px] text-black/70 dark:text-white/70">
                            <div>{{ $loc->address ?: __('hrm.address_not_set') }}</div>
                            <div class="text-[11px] text-black/50 dark:text-white/50">{{ $loc->city ?: __('hrm.field_city') }}</div>
                        </div>

                        <!-- Coords & Radius Bento -->
                        <div class="p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/5 grid grid-cols-2 gap-2 text-[12px]">
                            <div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.col_gps_coords') }}</span>
                                @if($loc->latitude && $loc->longitude)
                                    <span class="font-mono text-[11px] font-bold text-black/80 dark:text-white/80 block">
                                        {{ number_format((float)$loc->latitude, 4) }}, {{ number_format((float)$loc->longitude, 4) }}
                                    </span>
                                @else
                                    <span class="text-[#FF9500] text-[11px] font-semibold">{{ __('hrm.not_available') }}</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-[10.5px] text-black/50 dark:text-white/50 block font-medium">{{ __('hrm.col_geofence_radius') }}</span>
                                <span class="font-bold text-black dark:text-white">
                                    {{ $loc->geofence_radius_meters ?: 50 }} {{ __('hrm.meters_unit') }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-1 flex items-center gap-2">
                            <button type="button"
                                @click="openEditLocation({{ Js::from($loc) }}, '{{ route('hrm.locations.update', $loc->id) }}')"
                                class="flex-1 h-11 px-3 rounded-[12px] text-[13px] font-bold text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#0071E3] hover:text-white active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                <span>{{ __('hrm.edit_office_location') }}</span>
                            </button>

                            @if(! $loc->is_primary && $allLocations->count() > 1)
                                <form method="POST" action="{{ route('hrm.locations.destroy', $loc->id) }}"
                                    onsubmit="return confirm('{{ addslashes(__('hrm.confirm_delete_location')) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="h-11 w-11 rounded-[12px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/15 active:scale-[0.98] transition-all flex items-center justify-center cursor-pointer shrink-0"
                                        title="{{ __('hrm.tooltip_delete_location') }}"
                                        aria-label="{{ __('hrm.tooltip_delete_location') }}">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-black/50 dark:text-white/50 font-medium">
                        <i data-lucide="map-pin-off" class="w-8 h-8 mx-auto mb-1.5 text-black/30 dark:text-white/30"></i>
                        <p class="text-xs">{{ __('hrm.empty_locations_list') }}</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
