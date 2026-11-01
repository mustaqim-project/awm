<!-- ===================================================== -->
    <!-- MODAL: CASHIER PRODUCT MODIFIER & VARIANT PICKER      -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showModifierModal" x-cloak role="dialog" aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="showModifierModal = false">
            <!-- Standalone Backdrop -->
            <div x-show="showModifierModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showModifierModal = false"></div>

            <!-- Centered Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6"
                @click.self="showModifierModal = false">
                <div class="pos-modal-panel relative w-full max-w-md bg-white dark:bg-[#2C2C2E] rounded-[20px] border border-black/10 dark:border-white/10 p-5 space-y-4 shadow-2xl text-black dark:text-white">
            <template x-if="activeModifierProduct">
                <div class="space-y-4">
                    <!-- Product Header -->
                    <div
                        class="flex items-start justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <template x-if="activeModifierProduct.image_url">
                                <img :src="activeModifierProduct.image_url"
                                    class="w-12 h-12 rounded-[10px] object-cover border border-black/5 dark:border-white/10 shrink-0"
                                    alt="">
                            </template>
                            <div class="min-w-0">
                                <h3 class="font-bold text-[15px] text-black dark:text-white leading-snug truncate"
                                    x-text="activeModifierProduct.name"></h3>
                                <div class="text-xs text-[#007AFF] font-bold tabular-nums mt-0.5"
                                    x-text="formatRupiah(getProductPrice(activeModifierProduct))"></div>
                            </div>
                        </div>
                        <button type="button" @click="showModifierModal = false"
                            class="w-8 h-8 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modifier Groups List -->
                    <div class="space-y-3.5 max-h-[50vh] overflow-y-auto pr-1">
                        <template x-for="group in activeModifierProduct.modifier_groups" :key="group.id">
                            <div
                                class="space-y-2 p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-xs text-black dark:text-white"
                                            x-text="group.name"></span>
                                        <span x-show="group.is_required"
                                            class="text-[9px] font-bold text-[#FF3B30] bg-[#FF3B30]/10 px-1.5 py-0.2 rounded">Wajib</span>
                                    </div>
                                    <span class="text-[10px] text-black/45 dark:text-white/45"
                                        x-text="group.selection_type === 'single' ? 'Pilih 1' : ('Pilih maks ' + group.max_selection)"></span>
                                </div>

                                <div class="space-y-1.5">
                                    <template x-for="opt in group.options" :key="opt.id">
                                        <label
                                            class="flex items-center justify-between p-2 rounded-[8px] border transition cursor-pointer text-xs select-none"
                                            :class="isModifierSelected(group.id, opt.id) ?
                                                'bg-[#007AFF]/10 border-[#007AFF] text-[#007AFF] font-semibold' :
                                                ((opt.is_in_stock ?? opt.is_available ?? true) ?
                                                    'border-black/[0.06] dark:border-white/10 hover:bg-black/[0.03] dark:hover:bg-white/[0.05] text-black dark:text-white' :
                                                    'opacity-40 cursor-not-allowed border-dashed')">
                                            <div class="flex items-center gap-2">
                                                <input
                                                    :type="group.selection_type === 'single' ? 'radio' : 'checkbox'"
                                                    :name="'mod_group_' + group.id"
                                                    :disabled="!(opt.is_in_stock ?? opt.is_available ?? true)"
                                                    :checked="isModifierSelected(group.id, opt.id)"
                                                    @change="toggleModifierOption(group, opt)"
                                                    class="w-4 h-4 text-[#007AFF] focus:ring-0 rounded">
                                                <span x-text="opt.name"></span>
                                                <span x-show="!(opt.is_in_stock ?? opt.is_available ?? true)"
                                                    class="text-[9px] text-[#FF3B30] font-bold bg-[#FF3B30]/15 px-1 py-0.2 rounded">Stok
                                                    Habis</span>
                                            </div>
                                            <span class="tabular-nums font-medium"
                                                x-text="opt.price_delta > 0 ? ('+' + formatRupiah(opt.price_delta)) : 'Gratis'"></span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Item Notes Input -->
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-black dark:text-white">Catatan Item
                                (Opsional)</label>
                            <input type="text" x-model="modifierItemNotes"
                                placeholder="Cth: Less Sugar, No Ice, Pisah Saus..."
                                class="w-full h-9 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 px-3 text-xs text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- Quantity Stepper -->
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs font-semibold text-black dark:text-white">Jumlah</span>
                            <div
                                class="flex items-center gap-2 bg-black/[0.04] dark:bg-white/[0.06] rounded-[8px] p-1 border border-black/5 dark:border-white/10">
                                <button type="button" @click="modifierItemQty = Math.max(1, modifierItemQty - 1)"
                                    class="w-7 h-7 rounded-[6px] bg-white dark:bg-white/[0.1] text-black dark:text-white flex items-center justify-center font-bold text-sm shadow-xs">-</button>
                                <span class="w-8 text-center font-bold tabular-nums text-xs"
                                    x-text="modifierItemQty"></span>
                                <button type="button" @click="modifierItemQty++"
                                    class="w-7 h-7 rounded-[6px] bg-white dark:bg-white/[0.1] text-black dark:text-white flex items-center justify-center font-bold text-sm shadow-xs">+</button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div
                        class="flex items-center justify-between gap-3 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <div>
                            <div class="text-[10px] text-black/50 dark:text-white/50 font-medium">{{ __('pos.subtotal_item') }}</div>
                            <div class="text-sm font-extrabold text-black dark:text-white tabular-nums"
                                x-text="formatRupiah(calculateModifierTotalPrice() * modifierItemQty)"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showModifierModal = false"
                                class="h-9 px-3.5 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/[0.05] dark:hover:bg-white/[0.08]">{{ __('pos.cancel') }}</button>
                            <button type="button" @click="addModifierItemToCart()"
                                class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-xs font-bold transition shadow-sm">
                                {{ __('pos.add_to_order') }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
                </div>
            </div>
        </div>
    </template>
