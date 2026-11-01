<!-- ===================================================== -->
    <!-- MODAL: DETAIL ITEM & APOTEK / OBAT (BATCH & DOSIS)    -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showItemDetailModal" x-cloak role="dialog" aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showItemDetailModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showItemDetailModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showItemDetailModal = false"></div>

            <!-- Centered Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6"
                @click.self="showItemDetailModal = false">
                <div class="pos-modal-panel relative w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-2xl text-black dark:text-white">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[10px] {{ $isPharmacy ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#007AFF]/15 text-[#007AFF]' }} flex items-center justify-center">
                        @if ($isPharmacy)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                        </svg>
                        @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-[15px] text-black dark:text-white leading-tight">
                            @if ($isPharmacy)
                                {{ __('pos.item_detail_pharmacy') ?? 'Detail & Dosis Obat' }}
                            @else
                                {{ __('pos.item_detail_notes') ?? 'Detail & Catatan Item' }}
                            @endif
                        </h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50" x-text="editingItemIndex !== null && cart[editingItemIndex] ? cart[editingItemIndex].product_name : ''"></p>
                    </div>
                </div>
                <button type="button" @click="showItemDetailModal = false"
                    class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="space-y-3">
                <div class="space-y-1">
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('pos.item_notes_label') }}</label>
                    <input type="text" x-model="editingItemNotes" placeholder="{{ __('pos.item_notes_placeholder') }}"
                        class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                @if ($business->isPharmacy() || $business->isModuleEnabled('industry_pharmacy'))
                <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/5 space-y-2.5">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-[#007AFF]">{{ __('pos.pharmacy_mode') }}</div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60">{{ __('pos.batch_number') }}</label>
                            <input type="text" x-model="editingItemBatchNumber" placeholder="{{ __('pos.batch_placeholder') }}"
                                class="w-full h-8 rounded-[7px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 px-2.5 text-[12px] font-mono text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-medium text-black/60 dark:text-white/60">{{ __('pos.expiry_date') }}</label>
                            <input type="date" x-model="editingItemExpiredDate"
                                class="w-full h-8 rounded-[7px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 px-2 text-[11px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[11px] font-medium text-black/60 dark:text-white/60">{{ __('pos.dosage_instructions') }}</label>
                        <input type="text" x-model="editingItemDosage" placeholder="{{ __('pos.dosage_placeholder') }}"
                            class="w-full h-8 rounded-[7px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 px-2.5 text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                    </div>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                <button type="button" @click="showItemDetailModal = false"
                    class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">{{ __('pos.cancel') }}</button>
                <button type="button" @click="saveItemDetail()"
                    class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm">
                    {{ __('pos.confirm') }}
                </button>
            </div>
                </div>
            </div>
        </div>
    </template>
