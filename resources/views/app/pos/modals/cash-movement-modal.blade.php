<!-- ===================================================== -->
    <!-- 10. MODAL: CASH MOVEMENT (Apple Sheet)                -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showCashMovementModal" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showCashMovementModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showCashMovementModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showCashMovementModal = false"></div>

            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showCashMovementModal = false">
                <div class="pos-modal-panel relative w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 sm:p-6 space-y-4 shadow-[0_25px_60px_rgba(0,0,0,0.3)] text-black dark:text-white">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] flex items-center justify-center font-bold"
                        :class="cashMovementType === 'cash_in' ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-[#FF3B30]/15 text-[#FF3B30]'">
                        <i :data-lucide="cashMovementType === 'cash_in' ? 'arrow-down-left' : 'arrow-up-right'" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-[17px] text-black dark:text-white">{{ __('pos.cash_movement_modal_title') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('pos.cash_movement_modal_desc') }}</p>
                    </div>
                </div>
                <button type="button" @click="showCashMovementModal = false"
                    class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/50 dark:text-white/50 hover:text-black dark:hover:text-white flex items-center justify-center transition cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            {{-- Segmented Control Apple HIG --}}
            <div class="grid grid-cols-2 p-1 bg-black/[0.05] dark:bg-white/[0.08] rounded-[12px] gap-1">
                <button type="button" @click="cashMovementType = 'cash_out'; cashMovementCategory = 'operational'"
                    :class="cashMovementType === 'cash_out' ? 'bg-white dark:bg-[#1C1C1E] text-[#FF3B30] font-bold shadow-sm' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                    class="py-2 rounded-[9px] text-[13px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                    <span>{{ __('pos.cash_out_tab') }}</span>
                </button>
                <button type="button" @click="cashMovementType = 'cash_in'; cashMovementCategory = 'capital_injection'"
                    :class="cashMovementType === 'cash_in' ? 'bg-white dark:bg-[#1C1C1E] text-[#34C759] font-bold shadow-sm' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                    class="py-2 rounded-[9px] text-[13px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i>
                    <span>{{ __('pos.cash_in_tab') }}</span>
                </button>
            </div>

            <div class="space-y-3.5">
                {{-- Nominal XXL & Fast Pills --}}
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-2.5">
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70">{{ __('pos.movement_amount') }}</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-base">Rp</span>
                        <input type="number" x-model.number="cashMovementAmount" min="1" step="any" required placeholder="0"
                            class="w-full h-12 pl-12 pr-4 bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 rounded-[12px] text-xl font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF] shadow-inner">
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                        <span class="text-[11px] text-black/40 dark:text-white/40 font-medium mr-1">{{ __('pos.quick_amount') }}</span>
                        <button type="button" @click="cashMovementAmount = 10000" class="px-2 py-0.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11.5px] font-semibold transition cursor-pointer">10 rb</button>
                        <button type="button" @click="cashMovementAmount = 20000" class="px-2 py-0.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11.5px] font-semibold transition cursor-pointer">20 rb</button>
                        <button type="button" @click="cashMovementAmount = 50000" class="px-2 py-0.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11.5px] font-semibold transition cursor-pointer">50 rb</button>
                        <button type="button" @click="cashMovementAmount = 100000" class="px-2 py-0.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11.5px] font-semibold transition cursor-pointer">100 rb</button>
                        <button type="button" @click="cashMovementAmount = 200000" class="px-2 py-0.5 rounded-[7px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] text-[11.5px] font-semibold transition cursor-pointer">200 rb</button>
                    </div>
                </div>

                {{-- Kategori Dinamis --}}
                <div>
                    <label class="block text-[12px] font-bold text-black/70 dark:text-white/70 mb-1.5">
                        <span x-text="cashMovementType === 'cash_out' ? '{{ __('pos.expense_category_label') }}' : '{{ __('pos.inflow_category_label') }}'"></span>
                    </label>
                    <template x-if="cashMovementType === 'cash_out'">
                        <select x-model="cashMovementCategory"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="operational">{{ __('finance.categories.operational') }}</option>
                            <option value="consumption">{{ __('finance.categories.consumption') }}</option>
                            <option value="supplies">{{ __('finance.categories.supplies') }}</option>
                            <option value="logistics">{{ __('finance.categories.logistics') }}</option>
                            <option value="utilities">{{ __('finance.categories.utilities') }}</option>
                            <option value="maintenance">{{ __('finance.categories.maintenance') }}</option>
                            <option value="bank_admin">{{ __('finance.categories.bank_admin') }}</option>
                            <option value="other">{{ __('finance.categories.other') }}</option>
                        </select>
                    </template>
                    <template x-if="cashMovementType === 'cash_in'">
                        <select x-model="cashMovementCategory"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[12px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                            <option value="capital_injection">{{ __('finance.inflow_categories.capital_injection') }}</option>
                            <option value="sales_revenue">{{ __('finance.inflow_categories.sales_revenue') }}</option>
                            <option value="cash_refund">{{ __('finance.inflow_categories.cash_refund') }}</option>
                            <option value="receivable_payment">{{ __('finance.inflow_categories.receivable_payment') }}</option>
                            <option value="other">{{ __('finance.inflow_categories.other') }}</option>
                        </select>
                    </template>
                </div>

                {{-- Alert Callout Khusus Kategori Lainnya (Wajib Diisi) --}}
                <div x-show="cashMovementCategory === 'other'" x-transition
                    class="p-3.5 rounded-[14px] bg-[#FF9500]/10 border border-[#FF9500]/30 space-y-1.5">
                    <label class="block text-[12px] font-bold text-[#FF9500] dark:text-[#FF9F0A] flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        <span>{{ __('pos.other_desc_label') }}</span>
                    </label>
                    <input type="text" x-model="cashMovementOtherDesc"
                        placeholder="{{ __('pos.other_desc_placeholder') }}"
                        class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-[#FF9500]/30 rounded-[10px] px-3 text-[13px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#FF9500]">
                    <p class="text-[11px] text-[#FF9500]/80">{{ __('pos.other_desc_hint') }}</p>
                </div>

                {{-- Catatan / Keterangan Tambahan --}}
                <div>
                    <label class="block text-[12px] font-medium text-black/60 dark:text-white/60 mb-1.5">{{ __('pos.movement_reason_optional') }}</label>
                    <input type="text" x-model="cashMovementReason"
                        placeholder="{{ __('pos.movement_reason_placeholder') }}"
                        class="w-full h-10 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[10px] px-3.5 text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t border-black/10 dark:border-white/10">
                <button type="button" @click="showCashMovementModal = false"
                    class="h-10 px-5 rounded-[12px] text-[13.5px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition cursor-pointer">{{ __('common.cancel') }}</button>
                <button type="button" @click="submitCashMovement()"
                    class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] text-white font-bold text-[13.5px] transition shadow-[0_2px_10px_rgba(0,122,255,0.3)] cursor-pointer flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>{{ __('pos.save_cash_movement') }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
