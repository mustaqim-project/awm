{{--
    COOCA - Apple HIG Quick Action Modals & Mobile Action Sheet
    Sesuai mandat Bento Apple HIG v2.0 (macOS Sonoma / iOS 18)
    Format: Full Layout XXL (sm:max-w-2xl lg:max-w-3xl) di Desktop + Adaptive Bottom Sheet (max-h-[92vh] rounded-t-[28px]) di Mobile.
--}}
<div id="global-modals-container" x-data="{
    toastList: [],
    showExpenseModal: false,
    showIncomeModal: false,
    showStockInModal: false,
    showMaterialModal: false,
    showMobileActionSheet: false,
    isSubmitting: false,
    materialsList: [],
    isLoadingMaterials: false,
    requiresExpenseSupervisorPin: false,
    supervisorPin: '',
    displayExpenseAmount: '',
    displayIncomeAmount: '',
    displayStockInUnitCost: '',
    displayMaterialCost: '',
    expenseForm: { name: '', amount: '', category: 'Operasional Harian', other_description: '', payment_method: 'cash', notes: '' },
    incomeForm: { name: '', amount: '', category: 'Penjualan / Pendapatan Usaha', other_description: '', payment_method: 'cash', notes: '' },
    stockInForm: { material_id: '', product_id: '', quantity: 1, unit_cost: '', supplier_name: '', notes: '' },
    materialForm: { name: '', cost_per_unit: '', unit_id: '', category_id: '', sku: '' },

    init() {
        window.addEventListener('cooca-toast', (e) => {
            this.addToast(e.detail.message, e.detail.type || 'success');
        });
        window.addEventListener('open-quick-expense', () => { 
            this.showExpenseModal = true; 
        });
        window.addEventListener('open-quick-income', () => { 
            this.showIncomeModal = true; 
        });
        window.addEventListener('open-quick-stockin', () => { 
            this.openStockInModal(); 
        });
        window.addEventListener('open-quick-material', () => { 
            this.showMaterialModal = true; 
        });
        window.addEventListener('open-mobile-actions', () => { 
            this.showMobileActionSheet = true; 
        });
    },

    formatRupiah(val) {
        if (val === undefined || val === null || val === '') return '';
        const loc = (window.COOCA_LOCALE === 'en') ? 'en-US' : 'id-ID';
        return new Intl.NumberFormat(loc).format(val);
    },

    parseNumber(str) {
        if (!str) return 0;
        return parseFloat(String(str).replace(/[^0-9]/g, '')) || 0;
    },

    handleExpenseAmountInput(e) {
        const raw = this.parseNumber(e.target.value);
        this.expenseForm.amount = raw;
        this.displayExpenseAmount = raw > 0 ? this.formatRupiah(raw) : '';
        this.requiresExpenseSupervisorPin = raw >= 500000;
    },

    handleIncomeAmountInput(e) {
        const raw = this.parseNumber(e.target.value);
        this.incomeForm.amount = raw;
        this.displayIncomeAmount = raw > 0 ? this.formatRupiah(raw) : '';
    },

    handleStockInCostInput(e) {
        const raw = this.parseNumber(e.target.value);
        this.stockInForm.unit_cost = raw;
        this.displayStockInUnitCost = raw > 0 ? this.formatRupiah(raw) : '';
    },

    handleMaterialCostInput(e) {
        const raw = this.parseNumber(e.target.value);
        this.materialForm.cost_per_unit = raw;
        this.displayMaterialCost = raw > 0 ? this.formatRupiah(raw) : '';
    },

    getIdempotencyKey() {
        return typeof crypto !== 'undefined' && crypto.randomUUID 
            ? crypto.randomUUID 
                ? crypto.randomUUID() 
                : 'idemp-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9)
            : 'idemp-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9);
    },

    async fetchMaterials() {
        if (this.materialsList.length > 0) return;
        this.isLoadingMaterials = true;
        try {
            const res = await fetch('{{ route('dashboard.quick-materials-list') }}', {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (data.success) {
                this.materialsList = data.materials || [];
            }
        } catch (e) {
            console.error('Failed to load materials', e);
        } finally {
            this.isLoadingMaterials = false;
        }
    },

    openStockInModal() {
        this.showStockInModal = true;
        this.fetchMaterials();
    },

    addToast(msg, type = 'success') {
        const id = Date.now();
        this.toastList.push({ id, msg, type });
        setTimeout(() => {
            this.toastList = this.toastList.filter(t => t.id !== id);
        }, 4000);
    },

    async submitQuickExpense() {
        if (this.isSubmitting) return;
        if (!this.expenseForm.name || !this.expenseForm.amount) return;
        if ((this.expenseForm.category === 'Lainnya' || this.expenseForm.category === 'other') && !this.expenseForm.other_description?.trim()) {
            this.addToast(window.COOCA_I18N?.quick_actions?.expense?.other_desc_required || '{{ __('quick_actions.expense.other_desc_required') }}', 'error');
            return;
        }
        this.isSubmitting = true;
        const idempotencyKey = this.getIdempotencyKey();
        try {
            const payload = {
                ...this.expenseForm,
                supervisor_pin: this.supervisorPin
            };
            const res = await fetch('{{ route('dashboard.quick-expense') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Idempotency-Key': idempotencyKey,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.expense?.success_msg || '{{ __('quick_actions.expense.success_msg') }}'), 'success');
                this.showExpenseModal = false;
                this.expenseForm = { name: '', amount: '', category: 'Operasional Harian', other_description: '', payment_method: 'cash', notes: '' };
                this.displayExpenseAmount = '';
                this.supervisorPin = '';
                this.requiresExpenseSupervisorPin = false;
                window.dispatchEvent(new CustomEvent('expense-added', { detail: data }));
                window.dispatchEvent(new CustomEvent('cooca-data-mutated', { detail: { type: 'expense', data } }));
            } else {
                if (data.requires_pin) {
                    this.requiresExpenseSupervisorPin = true;
                }
                this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.expense?.error_msg || '{{ __('quick_actions.expense.error_msg') }}'), 'error');
            }
        } catch (err) {
            this.addToast(window.COOCA_I18N?.common?.connection_error || '{{ __('common.connection_error') }}', 'error');
        } finally {
            this.isSubmitting = false;
        }
    },

    async submitQuickIncome() {
        if (this.isSubmitting) return;
        if (!this.incomeForm.name || !this.incomeForm.amount) return;
        if ((this.incomeForm.category === 'Lainnya' || this.incomeForm.category === 'other') && !this.incomeForm.other_description?.trim()) {
            this.addToast(window.COOCA_I18N?.quick_actions?.income?.other_desc_required || '{{ __('quick_actions.income.other_desc_required') }}', 'error');
            return;
        }
        this.isSubmitting = true;
        const idempotencyKey = this.getIdempotencyKey();
        try {
            const payload = {
                ...this.incomeForm
            };
            const res = await fetch('{{ route('dashboard.quick-inflow') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Idempotency-Key': idempotencyKey,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.income?.success_msg || '{{ __('quick_actions.income.success_msg') }}'), 'success');
                this.showIncomeModal = false;
                this.incomeForm = { name: '', amount: '', category: 'Penjualan / Pendapatan Usaha', other_description: '', payment_method: 'cash', notes: '' };
                this.displayIncomeAmount = '';
                window.dispatchEvent(new CustomEvent('income-added', { detail: data }));
                window.dispatchEvent(new CustomEvent('cooca-data-mutated', { detail: { type: 'income', data } }));
            } else {
                this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.income?.error_msg || '{{ __('quick_actions.income.error_msg') }}'), 'error');
            }
        } catch (err) {
            this.addToast(window.COOCA_I18N?.common?.connection_error || '{{ __('common.connection_error') }}', 'error');
        } finally {
            this.isSubmitting = false;
        }
    },

    async submitQuickStockIn() {
        if (this.isSubmitting) return;
        if (!this.stockInForm.quantity || !this.stockInForm.unit_cost) return;
        this.isSubmitting = true;
        const idempotencyKey = this.getIdempotencyKey();
        try {
            const res = await fetch('{{ route('dashboard.quick-stock-in') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Idempotency-Key': idempotencyKey,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.stockInForm)
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.stock_in?.success_msg || '{{ __('quick_actions.stock_in.success_msg') }}'), 'success');
                this.showStockInModal = false;
                this.stockInForm = { material_id: '', product_id: '', quantity: 1, unit_cost: '', supplier_name: '', notes: '' };
                this.displayStockInUnitCost = '';
                window.dispatchEvent(new CustomEvent('stock-in-added', { detail: data }));
                window.dispatchEvent(new CustomEvent('cooca-data-mutated', { detail: { type: 'stock', data } }));
            } else {
                this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.stock_in?.error_msg || '{{ __('quick_actions.stock_in.error_msg') }}'), 'error');
            }
        } catch (err) {
            this.addToast(window.COOCA_I18N?.common?.connection_error || '{{ __('common.connection_error') }}', 'error');
        } finally {
            this.isSubmitting = false;
        }
    },

    async submitQuickMaterial() {
        if (this.isSubmitting) return;
        if (!this.materialForm.name || !this.materialForm.cost_per_unit) return;
        this.isSubmitting = true;
        const idempotencyKey = this.getIdempotencyKey();
        try {
            const res = await fetch('{{ route('dashboard.quick-material') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Idempotency-Key': idempotencyKey,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.materialForm)
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.material?.success_msg || '{{ __('quick_actions.material.success_msg') }}'), 'success');
                this.showMaterialModal = false;
                this.materialForm = { name: '', cost_per_unit: '', unit_id: '', category_id: '', sku: '' };
                this.displayMaterialCost = '';
                this.materialsList = []; // Invalidate cached list
                window.dispatchEvent(new CustomEvent('material-added', { detail: data.material }));
                window.dispatchEvent(new CustomEvent('cooca-data-mutated', { detail: { type: 'material', data: data.material } }));
            } else {
                this.addToast(data.message || (window.COOCA_I18N?.quick_actions?.material?.error_msg || '{{ __('quick_actions.material.error_msg') }}'), 'error');
            }
        } catch (err) {
            this.addToast(window.COOCA_I18N?.common?.connection_error || '{{ __('common.connection_error') }}', 'error');
        } finally {
            this.isSubmitting = false;
        }
    }
}">

    <!-- Floating Toasts Container (Apple Centered Top Banner with Tap & Swipe-Up Dismiss - Layer z-[250]) -->
    <div id="alpine-toast-container"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[250] flex flex-col items-center gap-2 pointer-events-none w-full max-w-sm px-4">
        <template x-for="t in toastList" :key="t.id">
            <div x-data="{ touchStartY: 0 }"
                @touchstart="touchStartY = $event.touches[0].clientY"
                @touchend="if (touchStartY - $event.changedTouches[0].clientY > 25) { toastList = toastList.filter(item => item.id !== t.id); }"
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-90"
                class="p-3 px-4 rounded-[14px] border border-black/5 dark:border-white/10 shadow-[0_8px_30px_rgba(0,0,0,0.12)] backdrop-blur-xl pointer-events-auto flex items-center gap-2.5 text-[13px] font-medium bg-white/95 dark:bg-[#2C2C2E]/95 text-black dark:text-white transition-all select-none">
                <span class="w-2 h-2 rounded-full shrink-0"
                    :class="t.type === 'error' ? 'bg-[#FF3B30]' : 'bg-[#34C759]'"></span>
                <span x-text="t.msg" class="flex-1 leading-snug"></span>
                <button type="button" @click="toastList = toastList.filter(item => item.id !== t.id)"
                    class="p-1 -mr-1 rounded-md text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                    aria-label="{{ __('common.close') }}">
                    <i data-lucide="x" class="w-3.5 h-3.5" x-init="$nextTick(() => { if (window.createCoocaIcons) window.createCoocaIcons(); else if (window.lucide) lucide.createIcons(); })"></i>
                </button>
            </div>
        </template>
    </div>

<!-- Modal 1: Quick Expense (Apple Full XXL & Mobile Bottom Sheet) -->
<div x-show="showExpenseModal" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/35 backdrop-blur-sm"
    style="display: none;">
    <div class="w-full sm:max-w-2xl lg:max-w-3xl max-h-[92vh] sm:max-h-[88vh] flex flex-col rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl border border-black/5 dark:border-white/10 shadow-[0_24px_60px_rgba(0,0,0,0.25)] overflow-hidden"
        role="dialog" aria-modal="true" aria-labelledby="modal-title-expense"
        @click.outside="if (!isSubmitting) showExpenseModal = false">
        
        <!-- Header (Sticky Top) -->
        <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 shrink-0 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center shrink-0">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="modal-title-expense" class="text-[16px] font-bold text-black dark:text-white tracking-tight leading-tight">
                        {{ __('quick_actions.expense.title') }}
                    </h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 leading-tight">{{ __('quick_actions.expense.subtitle') }}</p>
                </div>
            </div>
            <button type="button" @click="showExpenseModal = false" :disabled="isSubmitting"
                class="w-8 h-8 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors disabled:opacity-40 cursor-pointer"
                aria-label="{{ __('common.close') }}">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Form & Scrollable Body -->
        <form @submit.prevent="submitQuickExpense" class="flex flex-col flex-1 min-h-0">
            <div class="p-5 overflow-y-auto space-y-4 overscroll-contain flex-1 text-[13px]">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.expense.name_label') }}</label>
                        <input type="text" x-model="expenseForm.name" required :disabled="isSubmitting"
                            placeholder="{{ __('quick_actions.expense.name_placeholder') }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.expense.amount_label') }}</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[13px] font-bold text-black/40 dark:text-white/40">Rp</span>
                            <input type="text" inputmode="numeric"
                                x-model="displayExpenseAmount"
                                @input="handleExpenseAmountInput($event)"
                                required :disabled="isSubmitting"
                                placeholder="{{ __('quick_actions.expense.amount_placeholder') }}"
                                class="w-full h-11 pl-10 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-base sm:text-sm font-semibold text-black dark:text-white tabular-nums placeholder:text-black/30 dark:placeholder:text-white/30 focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.expense.payment_method_label') }}</label>
                        <select x-model="expenseForm.payment_method" :disabled="isSubmitting"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                            <option value="cash">{{ __('quick_actions.expense.method_cash') }}</option>
                            <option value="bank">{{ __('quick_actions.expense.method_bank') }}</option>
                            <option value="qris">{{ __('quick_actions.expense.method_qris') }}</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.expense.category_label') }}</label>
                        <select x-model="expenseForm.category" :disabled="isSubmitting"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                            <option value="Operasional Harian">{{ __('quick_actions.expense.cat_operational') }}</option>
                            <option value="Listrik, Air & Utilitas">{{ __('quick_actions.expense.cat_utilities') }}</option>
                            <option value="Pulsa, Internet & Komunikasi">{{ __('quick_actions.expense.cat_internet_phone') }}</option>
                            <option value="Bahan Habis Pakai & Perlengkapan">{{ __('quick_actions.expense.cat_consumables') }}</option>
                            <option value="Gaji & Upah Kasir/Karyawan">{{ __('quick_actions.expense.cat_salaries') }}</option>
                            <option value="Konsumsi, Snack & Makan Tim">{{ __('quick_actions.expense.cat_consumption') }}</option>
                            <option value="Transportasi & Logistik">{{ __('quick_actions.expense.cat_logistics') }}</option>
                            <option value="Sewa Tempat & Gedung">{{ __('quick_actions.expense.cat_rent') }}</option>
                            <option value="Perbaikan & Perawatan Aset">{{ __('quick_actions.expense.cat_maintenance') }}</option>
                            <option value="Iklan & Promosi Toko">{{ __('quick_actions.expense.cat_marketing') }}</option>
                            <option value="Pajak, Retribusi & Izin">{{ __('quick_actions.expense.cat_taxes_legal') }}</option>
                            <option value="Biaya Admin Bank & Transaksi">{{ __('quick_actions.expense.cat_bank_admin') }}</option>
                            <option value="Kas Bon Karyawan">{{ __('quick_actions.expense.cat_cash_advance') }}</option>
                            <option value="Lainnya">{{ __('quick_actions.expense.cat_other') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Alert Callout Khusus Kategori Lainnya (Wajib Diisi) -->
                <div x-show="expenseForm.category === 'Lainnya'" x-transition class="p-3.5 bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/30 rounded-[14px] space-y-1.5">
                    <label class="block text-[11.5px] font-bold text-amber-700 dark:text-amber-300 flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span>{{ __('quick_actions.expense.other_desc_label') }} *</span>
                    </label>
                    <input type="text" x-model="expenseForm.other_description" :required="expenseForm.category === 'Lainnya'" :disabled="isSubmitting"
                        placeholder="{{ __('quick_actions.expense.other_desc_placeholder') }}"
                        class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-amber-500/30 rounded-[10px] px-3.5 text-base sm:text-sm text-black dark:text-white outline-none focus:ring-2 focus:ring-amber-500/50 disabled:opacity-50">
                    <p class="text-[11px] text-amber-700/80 dark:text-amber-300/80">{{ __('quick_actions.expense.other_desc_required') }}</p>
                </div>

                <!-- Supervisor PIN Guard (Maker-Checker Threshold >= Rp 500.000) -->
                <div x-show="requiresExpenseSupervisorPin || (expenseForm.amount >= 500000)" x-transition class="p-3.5 bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/20 rounded-[14px] space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-[12px] font-bold text-amber-700 dark:text-amber-300">{{ __('quick_actions.expense.supervisor_pin_label') }}</label>
                        <span class="text-[11px] text-amber-600 dark:text-amber-400 font-medium">{{ __('quick_actions.expense.supervisor_pin_hint') }}</span>
                    </div>
                    <input type="password" maxlength="6" x-model="supervisorPin" :disabled="isSubmitting" placeholder="{{ __('quick_actions.expense.supervisor_pin_placeholder') }}"
                        class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-amber-500/30 rounded-[10px] px-3.5 text-center tracking-widest text-base sm:text-sm font-bold text-black dark:text-white outline-none focus:ring-2 focus:ring-amber-500/50 disabled:opacity-50">
                </div>
            </div>

            <!-- Footer Aksi (Sticky Bottom) -->
            <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] shrink-0 flex items-center justify-end gap-2.5">
                <button type="button" @click="showExpenseModal = false" :disabled="isSubmitting" class="btn-apple-gray disabled:opacity-40">
                    {{ __('common.cancel') }}
                </button>
                <button type="submit" :disabled="isSubmitting" class="btn-apple-filled flex items-center gap-2">
                    <svg x-show="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isSubmitting ? '{{ __('common.saving') }}' : '{{ __('quick_actions.expense.submit_btn') }}'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Quick Instant Inflow / Pemasukan Kas (Apple Full XXL & Mobile Bottom Sheet) -->
<div x-show="showIncomeModal" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/35 backdrop-blur-sm"
    style="display: none;">
    <div class="w-full sm:max-w-2xl lg:max-w-3xl max-h-[92vh] sm:max-h-[88vh] flex flex-col rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl border border-black/5 dark:border-white/10 shadow-[0_24px_60px_rgba(0,0,0,0.25)] overflow-hidden"
        role="dialog" aria-modal="true" aria-labelledby="modal-title-income"
        @click.outside="if (!isSubmitting) showIncomeModal = false">
        
        <!-- Header (Sticky Top) -->
        <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 shrink-0 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="modal-title-income" class="text-[16px] font-bold text-black dark:text-white tracking-tight leading-tight">
                        {{ __('quick_actions.income.title') }}
                    </h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 leading-tight">{{ __('quick_actions.income.subtitle') }}</p>
                </div>
            </div>
            <button type="button" @click="showIncomeModal = false" :disabled="isSubmitting"
                class="w-8 h-8 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors disabled:opacity-40 cursor-pointer"
                aria-label="{{ __('common.close') }}">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Form & Scrollable Body -->
        <form @submit.prevent="submitQuickIncome" class="flex flex-col flex-1 min-h-0">
            <div class="p-5 overflow-y-auto space-y-4 overscroll-contain flex-1 text-[13px]">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.income.name_label') }}</label>
                        <input type="text" x-model="incomeForm.name" required :disabled="isSubmitting"
                            placeholder="{{ __('quick_actions.income.name_placeholder') }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:ring-2 focus:ring-[#34C759]/50 outline-none transition disabled:opacity-50">
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.income.amount_label') }}</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[13px] font-bold text-black/40 dark:text-white/40">Rp</span>
                            <input type="text" inputmode="numeric"
                                x-model="displayIncomeAmount"
                                @input="handleIncomeAmountInput($event)"
                                required :disabled="isSubmitting"
                                placeholder="{{ __('quick_actions.income.amount_placeholder') }}"
                                class="w-full h-11 pl-10 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-base sm:text-sm font-semibold text-black dark:text-white tabular-nums placeholder:text-black/30 dark:placeholder:text-white/30 focus:ring-2 focus:ring-[#34C759]/50 outline-none transition disabled:opacity-50">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.income.payment_method_label') }}</label>
                        <select x-model="incomeForm.payment_method" :disabled="isSubmitting"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#34C759]/50 outline-none transition disabled:opacity-50">
                            <option value="cash">{{ __('quick_actions.income.method_cash') }}</option>
                            <option value="bank">{{ __('quick_actions.income.method_bank') }}</option>
                            <option value="qris">{{ __('quick_actions.income.method_qris') }}</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.income.category_label') }}</label>
                        <select x-model="incomeForm.category" :disabled="isSubmitting"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#34C759]/50 outline-none transition disabled:opacity-50">
                            <option value="Penjualan / Pendapatan Usaha">{{ __('quick_actions.income.cat_sales') }}</option>
                            <option value="Tambahan Modal Pemilik">{{ __('quick_actions.income.cat_capital') }}</option>
                            <option value="Tambah Modal Kasir / Pecahan">{{ __('quick_actions.income.cat_float') }}</option>
                            <option value="Pendapatan Jasa / Non-POS">{{ __('quick_actions.income.cat_service') }}</option>
                            <option value="Pembayaran Piutang Pelanggan">{{ __('quick_actions.income.cat_receivable') }}</option>
                            <option value="Bunga Bank / Bagi Hasil">{{ __('quick_actions.income.cat_bank_interest') }}</option>
                            <option value="Investasi / Pendanaan Masuk">{{ __('quick_actions.income.cat_investment') }}</option>
                            <option value="Penjualan Aset / Barang Bekas">{{ __('quick_actions.income.cat_asset_sale') }}</option>
                            <option value="Pengembalian Dana (Refund)">{{ __('quick_actions.income.cat_refund') }}</option>
                            <option value="Restitusi Pajak">{{ __('quick_actions.income.cat_tax_refund') }}</option>
                            <option value="Titipan Dana / Talangan">{{ __('quick_actions.income.cat_deposit') }}</option>
                            <option value="Lainnya">{{ __('quick_actions.income.cat_other') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Alert Callout Khusus Kategori Lainnya (Wajib Diisi) -->
                <div x-show="incomeForm.category === 'Lainnya'" x-transition class="p-3.5 bg-emerald-500/10 dark:bg-emerald-500/15 border border-emerald-500/30 rounded-[14px] space-y-1.5">
                    <label class="block text-[11.5px] font-bold text-emerald-700 dark:text-emerald-300 flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        <span>{{ __('quick_actions.income.other_desc_label') }} *</span>
                    </label>
                    <input type="text" x-model="incomeForm.other_description" :required="incomeForm.category === 'Lainnya'" :disabled="isSubmitting"
                        placeholder="{{ __('quick_actions.income.other_desc_placeholder') }}"
                        class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-emerald-500/30 rounded-[10px] px-3.5 text-base sm:text-sm text-black dark:text-white outline-none focus:ring-2 focus:ring-emerald-500/50 disabled:opacity-50">
                    <p class="text-[11px] text-emerald-700/80 dark:text-emerald-300/80">{{ __('quick_actions.income.other_desc_required') }}</p>
                </div>
            </div>

            <!-- Footer Aksi (Sticky Bottom) -->
            <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] shrink-0 flex items-center justify-end gap-2.5">
                <button type="button" @click="showIncomeModal = false" :disabled="isSubmitting" class="btn-apple-gray disabled:opacity-40">
                    {{ __('common.cancel') }}
                </button>
                <button type="submit" :disabled="isSubmitting" class="min-h-[40px] px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#2DBE50] active:scale-[0.98] transition shadow-[0_2px_8px_rgba(52,199,89,0.3)] flex items-center gap-2 cursor-pointer">
                    <svg x-show="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isSubmitting ? '{{ __('common.saving') }}' : '{{ __('quick_actions.income.submit_btn') }}'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Quick Instant Stock-In (Apple Full XXL & Mobile Bottom Sheet) -->
<div x-show="showStockInModal" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/35 backdrop-blur-sm"
    style="display: none;">
    <div class="w-full sm:max-w-2xl lg:max-w-3xl max-h-[92vh] sm:max-h-[88vh] flex flex-col rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl border border-black/5 dark:border-white/10 shadow-[0_24px_60px_rgba(0,0,0,0.25)] overflow-hidden"
        role="dialog" aria-modal="true" aria-labelledby="modal-title-stockin"
        @click.outside="if (!isSubmitting) showStockInModal = false">
        
        <!-- Header (Sticky Top) -->
        <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 shrink-0 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center shrink-0">
                    <i data-lucide="package-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="modal-title-stockin" class="text-[16px] font-bold text-black dark:text-white tracking-tight leading-tight">
                        {{ __('quick_actions.stock_in.title') }}
                    </h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 leading-tight">{{ __('quick_actions.stock_in.subtitle') }}</p>
                </div>
            </div>
            <button type="button" @click="showStockInModal = false" :disabled="isSubmitting"
                class="w-8 h-8 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors disabled:opacity-40 cursor-pointer"
                aria-label="{{ __('common.close') }}">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Form & Scrollable Body -->
        <form @submit.prevent="submitQuickStockIn" class="flex flex-col flex-1 min-h-0">
            <div class="p-5 overflow-y-auto space-y-4 overscroll-contain flex-1 text-[13px]">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.stock_in.material_label') }}</label>
                        <select x-model="stockInForm.material_id" required :disabled="isSubmitting || isLoadingMaterials"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                            <option value="">{{ __('quick_actions.stock_in.select_material') }}</option>
                            <option value="" disabled x-show="isLoadingMaterials">{{ __('quick_actions.stock_in.loading_materials') }}</option>
                            <template x-for="m in materialsList" :key="m.id">
                                <option :value="m.id" x-text="`${m.name} (HPP: Rp ${formatRupiah(m.price)})`"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.stock_in.qty_label') }}</label>
                        <input type="number" x-model.number="stockInForm.quantity" required :disabled="isSubmitting"
                            min="0.01" step="any" placeholder="{{ __('quick_actions.stock_in.qty_placeholder') }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.stock_in.unit_cost_label') }}</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[13px] font-bold text-black/40 dark:text-white/40">Rp</span>
                            <input type="text" inputmode="numeric"
                                x-model="displayStockInUnitCost"
                                @input="handleStockInCostInput($event)"
                                required :disabled="isSubmitting"
                                placeholder="{{ __('quick_actions.stock_in.unit_cost_placeholder') }}"
                                class="w-full h-11 pl-10 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-base sm:text-sm font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.stock_in.supplier_label') }}</label>
                        <input type="text" x-model="stockInForm.supplier_name" :disabled="isSubmitting"
                            placeholder="{{ __('quick_actions.stock_in.supplier_placeholder') }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                    </div>
                </div>
            </div>

            <!-- Footer Aksi (Sticky Bottom) -->
            <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] shrink-0 flex items-center justify-end gap-2.5">
                <button type="button" @click="showStockInModal = false" :disabled="isSubmitting" class="btn-apple-gray disabled:opacity-40">
                    {{ __('common.cancel') }}
                </button>
                <button type="submit" :disabled="isSubmitting" class="btn-apple-filled flex items-center gap-2">
                    <svg x-show="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isSubmitting ? '{{ __('common.processing') }}' : '{{ __('quick_actions.stock_in.submit_btn') }}'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 4: Quick Create Material (Apple Full XXL & Mobile Bottom Sheet) -->
<div x-show="showMaterialModal" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/35 backdrop-blur-sm"
    style="display: none;">
    <div class="w-full sm:max-w-2xl lg:max-w-3xl max-h-[92vh] sm:max-h-[88vh] flex flex-col rounded-t-[28px] sm:rounded-[24px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl border border-black/5 dark:border-white/10 shadow-[0_24px_60px_rgba(0,0,0,0.25)] overflow-hidden"
        role="dialog" aria-modal="true" aria-labelledby="modal-title-material"
        @click.outside="if (!isSubmitting) showMaterialModal = false">
        
        <!-- Header (Sticky Top) -->
        <div class="px-5 py-4 border-b border-black/5 dark:border-white/10 shrink-0 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="modal-title-material" class="text-[16px] font-bold text-black dark:text-white tracking-tight leading-tight">
                        {{ __('quick_actions.material.title') }}
                    </h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 leading-tight">{{ __('quick_actions.material.subtitle') }}</p>
                </div>
            </div>
            <button type="button" @click="showMaterialModal = false" :disabled="isSubmitting"
                class="w-8 h-8 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors disabled:opacity-40 cursor-pointer"
                aria-label="{{ __('common.close') }}">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Form & Scrollable Body -->
        <form @submit.prevent="submitQuickMaterial" class="flex flex-col flex-1 min-h-0">
            <div class="p-5 overflow-y-auto space-y-4 overscroll-contain flex-1 text-[13px]">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.material.name_label') }}</label>
                        <input type="text" x-model="materialForm.name" required :disabled="isSubmitting"
                            placeholder="{{ __('quick_actions.material.name_placeholder') }}"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.material.cost_label') }}</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-[13px] font-bold text-black/40 dark:text-white/40">Rp</span>
                            <input type="text" inputmode="numeric"
                                x-model="displayMaterialCost"
                                @input="handleMaterialCostInput($event)"
                                required :disabled="isSubmitting"
                                placeholder="{{ __('quick_actions.material.cost_placeholder') }}"
                                class="w-full h-11 pl-10 pr-3.5 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] text-base sm:text-sm font-semibold text-black dark:text-white tabular-nums focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70 mb-1.5">{{ __('quick_actions.material.unit_label') }}</label>
                        <select x-model="materialForm.unit_id" :disabled="isSubmitting"
                            class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[12px] px-3.5 text-base sm:text-sm text-black dark:text-white focus:ring-2 focus:ring-[#007AFF]/50 outline-none transition disabled:opacity-50">
                            <option value="">{{ __('quick_actions.material.select_unit') }}</option>
                            @php
                                $modalUnits = $activeBiz
                                    ? \Illuminate\Support\Facades\Cache::remember(
                                        "layout_modal_units_{$activeBiz->id}",
                                        300,
                                        function () use ($activeBiz) {
                                            return \App\Models\Unit::where('business_id', $activeBiz->id)
                                                ->orWhereNull('business_id')
                                                ->orderBy('name')
                                                ->get()
                                                ->map(
                                                    fn($u) => [
                                                        'id' => (string) $u->id,
                                                        'name' => (string) $u->name,
                                                        'code' => (string) $u->code,
                                                    ],
                                                )
                                                ->all();
                                        },
                                    )
                                    : [];
                            @endphp
                            @foreach ($modalUnits as $u)
                                <option value="{{ $u['id'] }}">{{ $u['name'] }}
                                    ({{ $u['code'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Footer Aksi (Sticky Bottom) -->
            <div class="px-5 py-3.5 border-t border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] shrink-0 flex items-center justify-end gap-2.5">
                <button type="button" @click="showMaterialModal = false" :disabled="isSubmitting" class="btn-apple-gray disabled:opacity-40">
                    {{ __('common.cancel') }}
                </button>
                <button type="submit" :disabled="isSubmitting" class="btn-apple-filled flex items-center gap-2">
                    <svg x-show="isSubmitting" class="animate-spin h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isSubmitting ? '{{ __('common.saving') }}' : '{{ __('quick_actions.material.submit_btn') }}'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 5: Mobile Action Sheet Bottom Modal (iOS 18 Sheet) -->
<div x-show="showMobileActionSheet" x-transition:enter="transition-opacity ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-150"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-end justify-center bg-black/35 backdrop-blur-sm lg:hidden"
    style="display: none;">
    <div @click.outside="showMobileActionSheet = false"
        x-show="showMobileActionSheet"
        role="dialog" aria-modal="true" aria-labelledby="modal-title-actionsheet"
        x-transition:enter="transition ease-out duration-250 transform"
        x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
        class="w-full max-h-[85vh] rounded-t-[28px] bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl border-t border-black/5 dark:border-white/10 shadow-[0_-12px_40px_rgba(0,0,0,0.2)] p-5 pb-[max(1.5rem,env(safe-area-inset-bottom))] space-y-4 overflow-y-auto">
        
        <div class="w-10 h-1 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-1 mb-2"></div>
        <div class="flex items-center justify-between">
            <h3 id="modal-title-actionsheet" class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('quick_actions.sheet.title') }}</h3>
            <button type="button" @click="showMobileActionSheet = false"
                class="w-7 h-7 rounded-full flex items-center justify-center text-black/40 hover:text-black/70 dark:text-white/40 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors cursor-pointer"
                aria-label="{{ __('common.close') }}">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-2.5 text-[13px]">
            @if (\App\Support\Context::hasPermission('expenses.manage') || \App\Support\Context::hasPermission('expenses.view') || \App\Support\Context::isOwner())
                <button type="button" @click="showMobileActionSheet = false; showExpenseModal = true"
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition active:scale-[0.97] cursor-pointer">
                    <div class="w-8 h-8 rounded-[8px] bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-black dark:text-white">{{ __('quick_actions.sheet.expense_title') }}</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.expense_desc') }}</div>
                    </div>
                </button>
            @endif

            @if (\App\Support\Context::hasPermission('finance.cash_bank') || \App\Support\Context::hasPermission('expenses.manage') || \App\Support\Context::isOwner())
                <button type="button" @click="showMobileActionSheet = false; showIncomeModal = true"
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition active:scale-[0.97] cursor-pointer">
                    <div class="w-8 h-8 rounded-[8px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                        <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-black dark:text-white">{{ __('quick_actions.sheet.income_title') }}</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.income_desc') }}</div>
                    </div>
                </button>
            @endif

            @if ((\App\Support\Context::hasPermission('inventory.manage') || \App\Support\Context::hasPermission('inventory.view') || \App\Support\Context::isOwner()) && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_INVENTORY_WAREHOUSE) ?? true))
                <button type="button" @click="showMobileActionSheet = false; showStockInModal = true"
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition active:scale-[0.97] cursor-pointer">
                    <div class="w-8 h-8 rounded-[8px] bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                        <i data-lucide="package-plus" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-black dark:text-white">{{ __('quick_actions.sheet.stock_title') }}</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.stock_desc') }}</div>
                    </div>
                </button>
            @endif

            @if ((\App\Support\Context::hasPermission('materials.create') || \App\Support\Context::hasPermission('materials.view') || \App\Support\Context::isOwner()) && ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true))
                <button type="button" @click="showMobileActionSheet = false; showMaterialModal = true"
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition active:scale-[0.97] cursor-pointer">
                    <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                        <i data-lucide="boxes" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-black dark:text-white">{{ __('quick_actions.sheet.material_title') }}</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.material_desc') }}</div>
                    </div>
                </button>
            @endif

            @if ((\App\Support\Context::hasPermission('costing.view_margin') || \App\Support\Context::hasPermission('costing.manage') || \App\Support\Context::isOwner()) && (($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_RECIPE_BOM) ?? true) || ($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_LABOR_MACHINES) ?? true)))
                <a href="{{ route('calculator.index') }}"
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                    <div class="w-8 h-8 rounded-[8px] bg-[#AF52DE]/12 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-black dark:text-white">{{ __('quick_actions.sheet.hpp_title') }}</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.hpp_desc') }}</div>
                    </div>
                </a>
            @endif

            @if ($activeBiz && $activeBiz->hasDineInFeature() && $activeBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_DINEIN) && (\App\Support\Context::hasPermission('pos.kitchen') || \App\Support\Context::isOwner()))
                <a href="{{ route('pos.kitchen.index') }}"
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                    <div class="w-8 h-8 rounded-[8px] bg-[#FF9500]/12 text-[#FF9500] dark:text-[#FF9F0A] flex items-center justify-center">
                        <i data-lucide="chef-hat" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-black dark:text-white">{{ __('quick_actions.sheet.kds_title') }}</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.kds_desc') }}</div>
                    </div>
                </a>
            @endif

            @if ($activeBiz && $activeBiz->hasDineInFeature() && $activeBiz->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_POS_DINEIN) && (\App\Support\Context::hasPermission('pos.tables') || \App\Support\Context::isOwner()))
                <a href="{{ route('pos.tables.index') }}"
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                    <div class="w-8 h-8 rounded-[8px] bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-black dark:text-white">{{ __('quick_actions.sheet.tables_title') }}</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.tables_desc') }}</div>
                    </div>
                </a>
            @endif

            @if (($activeBiz?->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_SERVICE_WORKSHOP) ?? true) && (\App\Support\Context::hasPermission('products.view') || \App\Support\Context::hasPermission('products.create') || \App\Support\Context::isOwner()))
                <a href="{{ route('services.index') }}"
                    class="p-3.5 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] active:bg-black/[0.06] dark:active:bg-white/[0.08] text-left space-y-1.5 transition block active:scale-[0.97]">
                    <div class="w-8 h-8 rounded-[8px] bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                        <i data-lucide="{{ ($activeBiz && $activeBiz->isWorkshop()) ? 'wrench' : 'briefcase' }}" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-black dark:text-white">{{ __('quick_actions.sheet.services_title') }}</div>
                        <div class="text-[11px] text-black/45 dark:text-white/45">{{ __('quick_actions.sheet.services_desc') }}</div>
                    </div>
                </a>
            @endif
        </div>
    </div>
</div>
</div>
