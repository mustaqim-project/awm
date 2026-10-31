@extends('layouts.app', [
    'title' => __('customers.title') . ' - Cooca',
    'headerTitle' => __('customers.directory_title'),
    'headerSubtitle' => __('customers.subtitle'),
])

@php
    $canB2b = $business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_B2B_SALES);
    $isWorkshop = $business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_SERVICE_WORKSHOP) || $business->isWorkshop();
    $canCrmLoyalty = $business->isModuleEnabled(\App\Domain\Template\ModuleRegistry::MODULE_CRM_LOYALTY);
@endphp

@section('content')
    <script>
        window.COOCA_CUSTOMERS = @json($customers->items());
        window.COOCA_LOCALE = '{{ app()->getLocale() }}';
        window.COOCA_I18N = {
            locale: '{{ app()->getLocale() }}',
            net_days: @json(__('customers.net_days', ['days' => ':days'])),
            cash_terms: @json(__('customers.cash_terms')),
            points: @json(__('customers.points')),
            no_address: @json(__('customers.no_address_provided')),
            delete_confirm: @json(__('customers.delete_confirm_dialog'))
        };
    </script>

    <div class="space-y-6 pb-24 lg:pb-16" x-data="{
        customersList: window.COOCA_CUSTOMERS || [],
        showAddModal: false,
        showEditModal: false,
        showDetailModal: false,
        selectedCustomer: null,

        editCustomer: {
            id: '',
            slug: '',
            name: '',
            company_name: '',
            code: '',
            email: '',
            phone: '',
            vehicle_license_plate: '',
            vehicle_model: '',
            vehicle_mileage: '',
            billing_address: '',
            shipping_address: '',
            tax_identification_number: '',
            payment_terms_days: 30,
            notes: ''
        },

        openDetailModal(id) {
            this.selectedCustomer = this.customersList.find(c => c.id == id) || null;
            this.showDetailModal = true;
        },

        openEditModal(id) {
            const c = this.customersList.find(item => item.id == id);
            if (!c) return;
            this.editCustomer = {
                id: c.id || '',
                slug: c.slug || '',
                name: c.name || '',
                company_name: c.company_name || '',
                code: c.code || '',
                email: c.email || '',
                phone: c.phone || '',
                vehicle_license_plate: c.vehicle_license_plate || '',
                vehicle_model: c.vehicle_model || '',
                vehicle_mileage: c.vehicle_mileage !== undefined && c.vehicle_mileage !== null ? c.vehicle_mileage : '',
                billing_address: c.billing_address || '',
                shipping_address: c.shipping_address || '',
                tax_identification_number: c.tax_identification_number || '',
                payment_terms_days: c.payment_terms_days !== undefined ? c.payment_terms_days : 30,
                notes: c.notes || ''
            };
            this.showEditModal = true;
        },

        formatCurrency(num) {
            return new Intl.NumberFormat(window.COOCA_LOCALE === 'en' ? 'en-US' : 'id-ID').format(num || 0);
        },

        // Import Excel State
        showImportModal: false,
        importFile: null,
        importFileName: '',
        importLoading: false,
        importPreview: null,
        importFilter: 'all',
        importDuplicateStrategy: 'skip',
        importExecuting: false,

        handleImportFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.importFile = file;
                this.importFileName = file.name;
                this.uploadAndPreviewCustomers();
            }
        },

        uploadAndPreviewCustomers() {
            if (!this.importFile) return;
            this.importLoading = true;
            const formData = new FormData();
            formData.append('file', this.importFile);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('{{ route('customers.import.preview') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.message || 'Gagal memproses file Excel.'); });
                }
                return res.json();
            })
            .then(res => {
                this.importLoading = false;
                if (res.success) {
                    this.importPreview = res.data;
                } else {
                    if (typeof AppAlert !== 'undefined') {
                        AppAlert.error(res.message || 'Gagal menganalisis file.');
                    } else {
                        alert(res.message || 'Gagal menganalisis file.');
                    }
                }
            })
            .catch(err => {
                this.importLoading = false;
                if (typeof AppAlert !== 'undefined') {
                    AppAlert.error(err.message || '{{ __('purchasing.terjadi_kesalahan_saat_mengunggah_file') }}
                } else {
                    alert(err.message || '{{ __('purchasing.terjadi_kesalahan_saat_mengunggah_file') }}
                }
            });
        },

        async executeCustomerImport() {
            if (!this.importPreview || !this.importPreview.rows || this.importPreview.rows.length === 0) {
                return;
            }

            const totalToProcess = this.importDuplicateStrategy === 'skip' 
                ? this.importPreview.valid_count 
                : (this.importPreview.valid_count + this.importPreview.duplicate_count);

            if (totalToProcess <= 0) {
                if (typeof AppAlert !== 'undefined') {
                    AppAlert.warning('Tidak ada baris data yang siap diimport dengan strategi saat ini.');
                } else {
                    alert('Tidak ada baris data yang siap diimport.');
                }
                return;
            }

            if (typeof AppAlert !== 'undefined') {
                const confirmed = await AppAlert.confirm({
                    title: 'Import Pelanggan?',
                    message: 'Apakah Anda yakin ingin memproses ' + totalToProcess + ' data pelanggan ke database bisnis?',
                    type: 'info',
                    confirmText: 'Ya, Proses Import',
                    cancelText: 'Batal'
                });
                if (!confirmed) return;
            } else {
                if (!confirm('Apakah Anda yakin ingin memproses import pelanggan ini?')) return;
            }

            this.importExecuting = true;
            fetch('{{ route('customers.import.execute') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    rows: this.importPreview.rows,
                    duplicate_strategy: this.importDuplicateStrategy
                })
            })
            .then(res => res.json())
            .then(res => {
                this.importExecuting = false;
                if (res.success) {
                    if (typeof AppAlert !== 'undefined') {
                        AppAlert.success(res.message);
                    }
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    if (typeof AppAlert !== 'undefined') {
                        AppAlert.error(res.message || 'Gagal mengeksekusi import pelanggan.');
                    } else {
                        alert(res.message || 'Gagal mengeksekusi import pelanggan.');
                    }
                }
            })
            .catch(err => {
                this.importExecuting = false;
                if (typeof AppAlert !== 'undefined') {
                    AppAlert.error(err.message || '{{ __('purchasing.terjadi_kesalahan_sistem_saat_mengeksekusi_import') }}
                } else {
                    alert(err.message || '{{ __('purchasing.terjadi_kesalahan_sistem') }}
                }
            });
        },

        resetImport() {
            this.importFile = null;
            this.importFileName = '';
            this.importPreview = null;
            this.importFilter = 'all';
            this.importDuplicateStrategy = 'skip';
            this.importLoading = false;
            this.importExecuting = false;
        }
    }">

        <!-- 1. Unified Module Header -->
        <x-module-header
            module="crm"
            :title="__('customers.title')"
            :subtitle="__('customers.subtitle')">
            @if (\App\Support\Context::hasPermission('customers.export'))
            <a href="{{ route('customers.export') }}"
                class="h-10 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black dark:text-white text-[13px] font-medium border border-black/[0.08] dark:border-white/[0.1] transition flex items-center justify-center gap-1.5 w-full sm:w-auto"
                title="{{ __('customers.export_seluruh_direktori_pelanggan_ke_file') }}">
                <i data-lucide="download" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                <span>Export Excel</span>
            </a>
            @endif

            @if (\App\Support\Context::hasPermission('customers.create'))
                <button type="button" @click="showImportModal = true"
                    class="h-10 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black dark:text-white text-[13px] font-medium border border-black/[0.08] dark:border-white/[0.1] transition flex items-center justify-center gap-1.5 w-full sm:w-auto cursor-pointer"
                    title="{{ __('customers.import_data_pelanggan_massal_dari_file') }}">
                    <i data-lucide="upload" class="w-4 h-4 text-[#007AFF]"></i>
                    <span>Import Excel</span>
                </button>

                <button type="button" @click="showAddModal = true"
                    class="h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.97] transition flex items-center justify-center gap-2 w-full sm:w-auto cursor-pointer">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>{{ __('customers.add_customer') }}</span>
                </button>
            @endif
        </x-module-header>

        <!-- Canonical Module Tabs: Buku Pelanggan | Member & Tingkatan | Voucher Promo -->
        <x-module-tabs module="crm" />

        <!-- Flash & Alert Messages -->
        @if (session('success'))
            <div class="p-4 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 text-[#34C759] text-[13px] font-semibold flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="check-circle-2" class="w-5 h-5 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-[#34C759] hover:opacity-70 cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[#FF3B30] text-[13px] font-semibold flex items-start gap-2.5">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                <div class="space-y-1">
                    <div class="font-bold">{{ __('customers.errors_occurred') }}</div>
                    <ul class="list-disc list-inside font-normal text-[12px] space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- 2. Bento Hero Metrics (Context-Aware 3-Second Glanceability) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Bento Tile 1: Total Pelanggan -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2 transition hover:-translate-y-0.5">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                    <span>{{ __('customers.total_customers') }}</span>
                    <i data-lucide="users" class="w-4 h-4 text-[#007AFF]"></i>
                </div>
                <div class="text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white tabular-nums">
                    {{ number_format($totalCustomers, 0, ',', '.') }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">
                    @if ($canB2b)
                        {{ __('customers.corporate_clients', ['count' => $totalCorporate]) }}
                    @elseif ($isWorkshop)
                        {{ __('customers.workshop_client_base') }}
                    @else
                        {{ __('customers.registered_customers') }}
                    @endif
                </p>
            </div>

            <!-- Bento Tile 2: Rata-rata Tempo (B2B) atau Kontak Aktif (Non-B2B) -->
            @if ($canB2b)
                <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2 transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                        <span>{{ __('customers.avg_payment_terms') }}</span>
                        <i data-lucide="clock" class="w-4 h-4 text-[#007AFF]"></i>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white tabular-nums">
                        {{ $avgPaymentTerms }} <span class="text-base font-normal text-black/50 dark:text-white/50">{{ __('customers.days') }}</span>
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50">
                        {{ __('customers.b2b_terms_desc') }}
                    </p>
                </div>
            @else
                <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2 transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                        <span>{{ $isWorkshop ? __('customers.workshop_customers') : __('customers.active_contacts') }}</span>
                        <i data-lucide="{{ $isWorkshop ? 'wrench' : 'user-check' }}" class="w-4 h-4 text-[#34C759]"></i>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold tracking-tight text-black dark:text-white tabular-nums">
                        {{ number_format($totalCustomers, 0, ',', '.') }}
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50">
                        {{ $isWorkshop ? __('customers.vehicles_registered') : __('customers.ready_transact') }}
                    </p>
                </div>
            @endif

            <!-- Bento Tile 3: Total Piutang Kasbon -->
            <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2 transition hover:-translate-y-0.5">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                    <span>{{ __('customers.customer_credit_receivables') }}</span>
                    <i data-lucide="receipt" class="w-4 h-4 text-[#FF3B30]"></i>
                </div>
                <div class="text-xl sm:text-2xl font-bold tracking-tight text-[#FF3B30] tabular-nums">
                    Rp {{ number_format($totalCreditReceivable, 0, ',', '.') }}
                </div>
                <p class="text-[12px] text-black/50 dark:text-white/50">
                    {{ __('customers.outstanding_invoices_due') }}
                </p>
            </div>

            <!-- Bento Tile 4: Poin Loyalitas Beredar (CRM) atau Direktori Bisnis -->
            @if ($canCrmLoyalty)
                <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2 transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                        <span>{{ __('customers.loyalty_points_circulating') }}</span>
                        <i data-lucide="award" class="w-4 h-4 text-[#FF9500]"></i>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold tracking-tight text-[#FF9500] tabular-nums">
                        {{ number_format($totalPointsIssued, 0, ',', '.') }}
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50">
                        {{ __('customers.reward_ready_pos') }}
                    </p>
                </div>
            @else
                <div class="p-5 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] space-y-2 transition hover:-translate-y-0.5">
                    <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-semibold uppercase tracking-wider">
                        <span>{{ __('customers.business_directory') }}</span>
                        <i data-lucide="shield-check" class="w-4 h-4 text-[#5856D6]"></i>
                    </div>
                    <div class="text-2xl sm:text-3xl font-bold tracking-tight text-[#5856D6] tabular-nums">
                        100%
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50">
                        {{ __('customers.data_isolation_safe') }}
                    </p>
                </div>
            @endif
        </div>

        <!-- 3. Search & Filter Bar -->
        <div class="p-4 rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)]">
            <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ $isWorkshop ? __('customers.search_placeholder_workshop') : ($canB2b ? __('customers.search_placeholder_b2b') : __('customers.search_placeholder')) }}"
                        class="w-full h-11 pl-10 pr-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white placeholder-black/40 dark:placeholder-white/40 focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit"
                        class="h-11 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition cursor-pointer flex items-center justify-center gap-2 flex-1 sm:flex-none">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>{{ __('customers.search_button') }}</span>
                    </button>
                    @if (request('search'))
                        <a href="{{ route('customers.index') }}"
                            class="h-11 px-3.5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 hover:text-black dark:hover:text-white text-[13px] font-semibold transition flex items-center justify-center cursor-pointer">
                            <span>{{ __('common.reset') }}</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- 4. Customer Table Bento Container -->
        <div class="rounded-[20px] backdrop-blur-md bg-white/75 dark:bg-[#1C1C1E]/75 border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.02)] overflow-hidden">
            <div class="px-5 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-[14px] font-bold text-black dark:text-white">{{ __('customers.directory_title') }}</h2>
                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-[#007AFF]/10 text-[#007AFF]">{{ __('customers.contacts_count', ['count' => $customers->total()]) }}</span>
                </div>
            </div>

            @if ($customers->count() > 0)
                <!-- Desktop Table View (lg: screens and above) -->
                <div x-data="coocaTableSort()" class="hidden lg:block overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">
                                <x-table-sort-th :col="0" type="string" :title="$isWorkshop ? __('customers.table_customer_vehicle') : ($canB2b ? __('customers.table_customer_client') : __('customers.table_customer'))" class="py-3.5 px-4 sm:px-6" />
                                <x-table-sort-th :col="1" type="string" :title="__('customers.table_contact')" class="py-3.5 px-4" />
                                @if ($canB2b)
                                    <x-table-sort-th :col="2" type="number" :title="__('customers.table_terms')" align="center" class="py-3.5 px-4" />
                                @endif
                                <x-table-sort-th :col="$canB2b ? 3 : 2" type="number" :title="__('customers.table_transactions')" align="center" class="py-3.5 px-4" />
                                <th class="py-3.5 px-4 text-right pr-6 whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('customers.table_quick_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                            @foreach ($customers as $c)
                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors group">
                                    <!-- Customer Identity -->
                                    <td data-sort-value="{{ strtolower($c->name) }}" class="py-4 px-4 sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] font-bold flex items-center justify-center shrink-0 border border-[#007AFF]/20 text-[14px]">
                                                {{ strtoupper(substr($c->name, 0, 2)) }}
                                            </div>
                                            <div class="space-y-0.5">
                                                <div class="font-semibold text-black dark:text-white flex items-center gap-2">
                                                    <span>{{ $c->name }}</span>
                                                    @if ($c->code)
                                                        <span class="px-1.5 py-0.5 text-[10px] font-mono rounded-md bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60">{{ $c->code }}</span>
                                                    @endif
                                                </div>

                                                <!-- B2B Company Name -->
                                                @if ($canB2b && $c->company_name)
                                                    <div class="text-[12px] text-black/55 dark:text-white/55 flex items-center gap-1">
                                                        <i data-lucide="building" class="w-3 h-3 text-black/40 dark:text-white/40"></i>
                                                        <span>{{ $c->company_name }}</span>
                                                    </div>
                                                @endif

                                                <!-- Workshop Automotive Vehicle Badge -->
                                                @if ($isWorkshop && $c->vehicle_license_plate)
                                                    <div class="text-[11px] font-mono text-[#007AFF] flex items-center gap-1">
                                                        <i data-lucide="wrench" class="w-3 h-3"></i>
                                                        <span class="font-bold">{{ $c->vehicle_license_plate }}</span>
                                                        @if ($c->vehicle_model)
                                                             <span class="text-black/50 dark:text-white/50 font-sans">({{ $c->vehicle_model }})</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Contact Info & Quick WA -->
                                    <td data-sort-value="{{ strtolower($c->phone ?? '') }}" class="py-4 px-4">
                                        <div class="space-y-1">
                                            @if ($c->phone)
                                                @php
                                                    $cleanPhone = preg_replace('/[^0-9]/', '', $c->phone);
                                                    if (str_starts_with($cleanPhone, '0')) {
                                                        $cleanPhone = '62' . substr($cleanPhone, 1);
                                                    }
                                                @endphp
                                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                                                    class="inline-flex items-center gap-1.5 font-mono text-[12px] text-[#34C759] hover:underline">
                                                    <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                                    <span>{{ $c->phone }}</span>
                                                </a>
                                            @else
                                                <span class="text-black/30 dark:text-white/30 text-[11px]">{{ __('customers.phone_not_set') }}</span>
                                            @endif

                                            @if ($c->email)
                                                <div class="text-[12px] text-black/50 dark:text-white/50 flex items-center gap-1">
                                                    <i data-lucide="mail" class="w-3 h-3 text-black/30 dark:text-white/30"></i>
                                                    <span class="truncate max-w-[180px]">{{ $c->email }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Payment Terms (B2B Only) -->
                                    @if ($canB2b)
                                        <td data-sort-value="{{ (int) $c->payment_terms_days }}" class="py-4 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 tabular-nums">
                                                {{ $c->payment_terms_days > 0 ? __('customers.net_days', ['days' => $c->payment_terms_days]) : __('customers.cash_terms') }}
                                            </span>
                                        </td>
                                    @endif

                                    <!-- Transactions Count -->
                                    <td data-sort-value="{{ (int) $c->invoices_count }}" class="py-4 px-4 text-center">
                                        <div class="inline-flex flex-col text-[11px] font-mono">
                                            <span class="font-bold text-[#007AFF]">{{ __('customers.invoices_count', ['count' => $c->invoices_count]) }}</span>
                                            <span class="text-black/40 dark:text-white/40">{{ __('customers.po_count', ['count' => $c->purchase_orders_count]) }}</span>
                                        </div>
                                    </td>

                                    <!-- Action Buttons (Sticky Desktop & Icon-Only) -->
                                    <td class="py-4 px-4 text-right pr-6 whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button" @click="openDetailModal('{{ $c->id }}')"
                                                class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition cursor-pointer flex items-center justify-center shrink-0"
                                                title="{{ __('customers.btn_profile') }}"
                                                aria-label="{{ __('customers.btn_profile') }}">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </button>

                                            @if (\App\Support\Context::hasPermission('customers.edit'))
                                                <button type="button" @click="openEditModal('{{ $c->id }}')"
                                                    class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition cursor-pointer flex items-center justify-center shrink-0"
                                                    title="{{ __('common.edit') }}"
                                                    aria-label="{{ __('common.edit') }}">
                                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                                </button>
                                            @endif

                                            @if (\App\Support\Context::hasPermission('customers.delete'))
                                                <form method="POST" action="{{ route('customers.destroy', $c->slug) }}"
                                                    onsubmit="return confirm('{{ addslashes(__('customers.delete_confirm_dialog')) }}')" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-8 h-8 rounded-[8px] text-black/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition flex items-center justify-center cursor-pointer shrink-0"
                                                        title="{{ __('common.delete') }}"
                                                        aria-label="{{ __('common.delete') }}">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (Thumb Zone Optimized for 320px–390px screens) -->
                <div class="block lg:hidden divide-y divide-black/[0.05] dark:divide-white/[0.06]">
                    @foreach ($customers as $c)
                        <div class="p-4 sm:p-5 space-y-3.5">
                            <!-- Card Header: Identity, Code, Company/Vehicle & Count -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] font-bold flex items-center justify-center shrink-0 border border-[#007AFF]/20 text-[14px]">
                                        {{ strtoupper(substr($c->name, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 space-y-0.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <h3 class="font-bold text-[15px] text-black dark:text-white truncate">
                                                {{ $c->name }}
                                            </h3>
                                            @if ($c->code)
                                                <span class="px-1.5 py-0.5 text-[10px] font-mono font-medium rounded-md bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60">
                                                    {{ $c->code }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($canB2b && $c->company_name)
                                            <div class="text-[12px] text-black/60 dark:text-white/60 flex items-center gap-1 truncate">
                                                <i data-lucide="building" class="w-3.5 h-3.5 text-black/40 dark:text-white/40 shrink-0"></i>
                                                <span class="truncate">{{ $c->company_name }}</span>
                                            </div>
                                        @endif
                                        @if ($isWorkshop && $c->vehicle_license_plate)
                                            <div class="text-[11px] font-mono text-[#007AFF] flex items-center gap-1 font-bold">
                                                <i data-lucide="wrench" class="w-3.5 h-3.5 shrink-0"></i>
                                                <span>{{ $c->vehicle_license_plate }}</span>
                                                @if ($c->vehicle_model)
                                                    <span class="text-black/50 dark:text-white/50 font-sans font-normal">({{ $c->vehicle_model }})</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Transaction Count Badge -->
                                <div class="shrink-0 text-right font-mono text-[11px]">
                                    <span class="font-bold text-[#007AFF] block">{{ __('customers.invoices_count', ['count' => $c->invoices_count]) }}</span>
                                    <span class="text-black/40 dark:text-white/40">{{ __('customers.po_count', ['count' => $c->purchase_orders_count]) }}</span>
                                </div>
                            </div>

                            <!-- Contact & Metrics Summary -->
                            <div class="grid grid-cols-2 gap-2 text-[12px]">
                                <!-- WhatsApp Contact -->
                                <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] flex items-center gap-2 min-w-0">
                                    <i data-lucide="message-circle" class="w-4 h-4 text-[#34C759] shrink-0"></i>
                                    <div class="min-w-0 truncate">
                                        <span class="text-[10px] text-black/40 dark:text-white/40 block uppercase font-semibold">WhatsApp</span>
                                        @if ($c->phone)
                                            @php
                                                $cleanPhone = preg_replace('/[^0-9]/', '', $c->phone);
                                                if (str_starts_with($cleanPhone, '0')) {
                                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                                }
                                            @endphp
                                            <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                                                class="font-mono font-medium text-[#34C759] hover:underline truncate block">
                                                {{ $c->phone }}
                                            </a>
                                        @else
                                            <span class="text-black/30 dark:text-white/30 text-[11px]">-</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Email or B2B Terms -->
                                @if ($canB2b)
                                    <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06]">
                                        <span class="text-[10px] text-black/40 dark:text-white/40 block uppercase font-semibold">{{ __('customers.table_terms') }}</span>
                                        <span class="font-semibold text-black dark:text-white truncate block">
                                            {{ $c->payment_terms_days > 0 ? __('customers.net_days', ['days' => $c->payment_terms_days]) : __('customers.cash_terms') }}
                                        </span>
                                    </div>
                                @else
                                    <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] min-w-0">
                                        <span class="text-[10px] text-black/40 dark:text-white/40 block uppercase font-semibold">{{ __('customers.email') }}</span>
                                        <span class="text-black/70 dark:text-white/70 truncate block text-[11px]">
                                            {{ $c->email ?: '-' }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Thumb Zone Quick Actions (Ergonomic 44px min-height targets) -->
                            <div class="flex items-center gap-2 pt-1">
                                @if ($c->phone)
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $c->phone);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                                        class="flex-1 h-11 px-3 rounded-[12px] bg-[#34C759]/10 hover:bg-[#34C759]/20 text-[#34C759] font-bold text-[13px] flex items-center justify-center gap-1.5 active:scale-[0.98] transition"
                                        title="{{ __('customers.btn_chat_wa') }}">
                                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                                        <span>{{ __('customers.btn_chat_wa') }}</span>
                                    </a>
                                @else
                                    <div class="flex-1 h-11 px-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] text-black/30 dark:text-white/30 text-[13px] font-medium flex items-center justify-center gap-1.5 opacity-60">
                                        <i data-lucide="phone-off" class="w-4 h-4"></i>
                                        <span>{{ __('customers.btn_no_wa') }}</span>
                                    </div>
                                @endif

                                <button type="button" @click="openDetailModal('{{ $c->id }}')"
                                    class="flex-1 h-11 px-3 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white font-semibold text-[13px] flex items-center justify-center gap-1.5 active:scale-[0.98] transition cursor-pointer">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <span>{{ __('customers.btn_profile') }}</span>
                                </button>

                                @if (\App\Support\Context::hasPermission('customers.edit'))
                                    <button type="button" @click="openEditModal('{{ $c->id }}')"
                                        class="h-11 px-3.5 rounded-[12px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-[#007AFF] font-semibold text-[13px] flex items-center justify-center gap-1.5 active:scale-[0.98] transition cursor-pointer shrink-0">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        <span>{{ __('common.edit') }}</span>
                                    </button>
                                @endif

                                @if (\App\Support\Context::hasPermission('customers.delete'))
                                    <form method="POST" action="{{ route('customers.destroy', $c->slug) }}"
                                        onsubmit="return confirm('{{ addslashes(__('customers.delete_confirm_dialog')) }}')"
                                        class="shrink-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="h-11 w-11 rounded-[12px] text-black/40 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition flex items-center justify-center cursor-pointer"
                                            title="{{ __('common.delete') }}">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination Customers -->
                @if ($customers->hasPages())
                    <div class="p-4 border-t border-black/[0.05] dark:border-white/[0.06]">
                        {{ $customers->links() }}
                    </div>
                @endif
            @else
                <div class="py-16 text-center space-y-3">
                    <div class="w-14 h-14 rounded-full bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto text-black/40 dark:text-white/40">
                        <i data-lucide="users" class="w-7 h-7"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-[15px] font-bold text-black dark:text-white">{{ __('customers.empty_title') }}</h4>
                        <p class="text-[13px] text-black/50 dark:text-white/50 max-w-sm mx-auto">
                            {{ __('customers.empty_desc') }}
                        </p>
                    </div>
                    @if (\App\Support\Context::hasPermission('customers.create'))
                        <div class="pt-2">
                            <button type="button" @click="showAddModal = true"
                                class="h-11 px-5 rounded-[12px] bg-[#007AFF] text-white text-[13px] font-semibold hover:bg-[#0071E3] transition cursor-pointer">
                                {{ __('customers.add_first_customer') }}
                            </button>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- MODALS (APPLE HIG ACTION SHEETS & FLOATING DIALOGS)                       -->
        <!-- ========================================================================= -->

        <!-- MODAL 1: TAMBAH PELANGGAN (CANVAS XXL & MOBILE BOTTOM SHEET)              -->
        @if (\App\Support\Context::hasPermission('customers.create'))
            <template x-teleport="body">
                <div x-show="showAddModal" 
                    x-cloak
                    @keydown.escape.window="showAddModal = false"
                    class="fixed inset-0 z-[200] overflow-y-auto">
                    <!-- Backdrop -->
                    <div x-show="showAddModal"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="showAddModal = false"
                        class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

                    <div class="relative z-10 min-h-full flex items-end lg:items-center justify-center p-0 lg:p-6" @click.self="showAddModal = false">
                        <div x-show="showAddModal"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                            x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                            x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                            @click.stop
                            class="relative w-full bg-white dark:bg-[#1C1C1E] border-t lg:border border-black/[0.08] dark:border-white/[0.1] shadow-2xl overflow-y-auto
                                        rounded-t-[28px] lg:rounded-[28px] max-h-[92vh] lg:max-h-[88vh]
                                        p-5 sm:p-6 lg:p-8 lg:max-w-5xl space-y-6">
                            
                            <!-- Mobile Drag / Bottom Sheet Pill Indicator -->
                            <div class="w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mb-2 lg:hidden"></div>

                            <!-- Modal Header -->
                            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-black dark:text-white">{{ __('customers.add_customer_title') }}</h3>
                                        <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('customers.add_customer_subtitle') }}</p>
                                    </div>
                                </div>
                                <button type="button" @click="showAddModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                    <form method="POST" action="{{ route('customers.store') }}" class="space-y-6">
                        @csrf

                        <!-- Canvas XXL 2-Column Responsive Form Layout -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                            
                            <!-- Kolom 1: Identitas Pokok Pelanggan (Kaidah 3 Input Pokok Ramah Pengguna Usia 40–65 Tahun) -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 pb-1 border-b border-black/[0.05] dark:border-white/[0.06]">
                                    <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[11px] font-bold flex items-center justify-center">1</span>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white uppercase tracking-wider">{{ __('customers.section_identity') }}</h4>
                                </div>

                                <!-- Input 1: Nama Pelanggan -->
                                <div class="space-y-1">
                                    <label class="block text-[13px] font-bold text-black dark:text-white">
                                        {{ __('customers.name') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="text" name="name" required placeholder="{{ __('customers.name_placeholder') }}"
                                        class="w-full h-12 px-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                                </div>

                                <!-- Input 2: Nomor WhatsApp / Telepon -->
                                <div class="space-y-1">
                                    <label class="block text-[13px] font-bold text-black dark:text-white">
                                        {{ __('customers.phone') }} <span class="text-[#FF3B30]">*</span>
                                    </label>
                                    <input type="tel" name="phone" required placeholder="{{ __('customers.phone_placeholder') }}"
                                        class="w-full h-12 px-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] font-mono text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                                    <span class="text-[11px] text-black/50 dark:text-white/50">{{ __('customers.phone_hint') }}</span>
                                </div>

                                <!-- Input 3 B2B: Nama Perusahaan / Toko (B2B Only) -->
                                @if ($canB2b)
                                    <div class="space-y-1">
                                        <label class="block text-[13px] font-bold text-black dark:text-white">
                                            {{ __('customers.company_name') }} <span class="text-black/40 dark:text-white/40 text-[11px] font-normal">{{ __('customers.company_hint') }}</span>
                                        </label>
                                        <input type="text" name="company_name" placeholder="{{ __('customers.company_placeholder') }}"
                                            class="w-full h-12 px-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                                    </div>
                                @endif

                                <!-- Input Khusus Sektor Bengkel & Otomotif -->
                                @if ($isWorkshop)
                                    <div class="p-4 rounded-[18px] bg-[#007AFF]/5 border border-[#007AFF]/20 space-y-3">
                                        <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[12px]">
                                            <i data-lucide="wrench" class="w-4 h-4"></i>
                                            <span>{{ __('customers.vehicle_section_title') }}</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div class="space-y-1">
                                                <label class="block text-[12px] font-bold text-black dark:text-white">
                                                    {{ __('customers.vehicle_plate') }} <span class="text-[#FF3B30]">*</span>
                                                </label>
                                                <input type="text" name="vehicle_license_plate" placeholder="B 1234 XYZ"
                                                    class="w-full h-11 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.15] uppercase font-mono font-bold text-[15px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="block text-[12px] font-bold text-black dark:text-white">{{ __('customers.vehicle_model') }}</label>
                                                <input type="text" name="vehicle_model" placeholder="Contoh: Toyota Avanza 1.3 G"
                                                    class="w-full h-11 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.15] text-[14px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[12px] font-medium text-black/70 dark:text-white/70">{{ __('customers.vehicle_mileage') }}</label>
                                            <input type="number" name="vehicle_mileage" min="0" placeholder="Contoh: 45000"
                                                class="w-full h-11 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.15] font-mono text-[14px] text-black dark:text-white focus:outline-hidden">
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Kolom 2: Detail Tambahan & Alamat Pengiriman -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 pb-1 border-b border-black/[0.05] dark:border-white/[0.06]">
                                    <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[11px] font-bold flex items-center justify-center">2</span>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white uppercase tracking-wider">{{ __('customers.section_account_address') }}</h4>
                                </div>

                                <div class="grid grid-cols-1 {{ $canB2b ? 'sm:grid-cols-2' : '' }} gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('customers.customer_code') }}</label>
                                        <input type="text" name="code" placeholder="CUST-001"
                                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] font-mono text-black dark:text-white focus:outline-hidden">
                                    </div>
                                    @if ($canB2b)
                                        <div class="space-y-1">
                                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('customers.tax_id') }}</label>
                                            <input type="text" name="tax_identification_number" placeholder="01.234.567.8-901.000"
                                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] font-mono text-black dark:text-white focus:outline-hidden">
                                        </div>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 {{ $canB2b ? 'sm:grid-cols-2' : '' }} gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('customers.email') }}</label>
                                        <input type="email" name="email" placeholder="{{ __('customers.email_placeholder') }}"
                                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                                    </div>
                                    @if ($canB2b)
                                        <div class="space-y-1">
                                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('customers.payment_terms') }}</label>
                                            <input type="number" name="payment_terms_days" value="30" min="0" max="365"
                                                class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('customers.billing_address') }}</label>
                                    <textarea name="billing_address" rows="2" placeholder="{{ __('customers.address_placeholder') }}"
                                        class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden"></textarea>
                                </div>

                                <div class="space-y-1">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('customers.notes') }}</label>
                                    <textarea name="notes" rows="2" placeholder="{{ __('customers.notes_placeholder') }}"
                                        class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3">
                            <button type="button" @click="showAddModal = false"
                                class="h-12 px-6 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:text-black dark:hover:text-white transition cursor-pointer">
                                {{ __('common.cancel') }}
                            </button>
                            <button type="submit"
                                class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[14px] font-bold shadow-[0_2px_8px_rgba(0,122,255,0.3)] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span>{{ __('customers.save_customer') }}</span>
                            </button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </template>
        @endif

        <!-- MODAL 2: EDIT PELANGGAN (CANVAS XXL & MOBILE BOTTOM SHEET)                -->
        <template x-teleport="body">
            <div x-show="showEditModal" 
                x-cloak
                @keydown.escape.window="showEditModal = false"
                class="fixed inset-0 z-[200] overflow-y-auto">
                <!-- Backdrop -->
                <div x-show="showEditModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="showEditModal = false"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

                <div class="relative z-10 min-h-full flex items-end lg:items-center justify-center p-0 lg:p-6" @click.self="showEditModal = false">
                    <div x-show="showEditModal"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                        x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                        x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                        @click.stop
                        class="relative w-full bg-white dark:bg-[#1C1C1E] border-t lg:border border-black/[0.08] dark:border-white/[0.1] shadow-2xl overflow-y-auto
                                    rounded-t-[28px] lg:rounded-[28px] max-h-[92vh] lg:max-h-[88vh]
                                    p-5 sm:p-6 lg:p-8 lg:max-w-5xl space-y-6">
                        
                        <!-- Mobile Drag Pill Handle -->
                        <div class="w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mb-2 lg:hidden"></div>

                        <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-black dark:text-white">{{ __('customers.edit_customer') }}</h3>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">{{ __('customers.edit_customer_desc') }}</p>
                                </div>
                            </div>
                            <button type="button" @click="showEditModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                <form method="POST" :action="'/customers/' + (editCustomer.slug || editCustomer.id)" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Canvas XXL 2-Column Responsive Form Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
                        
                        <!-- Kolom 1: Identitas Pokok Pelanggan -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-1 border-b border-black/[0.05] dark:border-white/[0.06]">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[11px] font-bold flex items-center justify-center">1</span>
                                <h4 class="text-[13px] font-bold text-black dark:text-white uppercase tracking-wider">{{ __('customers.basic_identity') }}</h4>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-bold text-black dark:text-white">{{ __('customers.name') }} <span class="text-[#FF3B30]">*</span></label>
                                <input type="text" name="name" x-model="editCustomer.name" required
                                    class="w-full h-12 px-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-bold text-black dark:text-white">{{ __('customers.phone_whatsapp') }}</label>
                                <input type="text" name="phone" x-model="editCustomer.phone"
                                    class="w-full h-12 px-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] font-mono text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                            </div>

                            @if ($canB2b)
                                <div class="space-y-1">
                                    <label class="block text-[12px] font-bold text-black dark:text-white">{{ __('customers.company_name') }}</label>
                                    <input type="text" name="company_name" x-model="editCustomer.company_name"
                                        class="w-full h-12 px-4 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] focus:ring-2 focus:ring-[#007AFF]/20 transition">
                                </div>
                            @endif

                            <!-- Workshop Vehicle Input in Edit Modal -->
                            @if ($isWorkshop)
                                <div class="p-4 rounded-[18px] bg-[#007AFF]/5 border border-[#007AFF]/20 space-y-3">
                                    <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[12px]">
                                        <i data-lucide="wrench" class="w-4 h-4"></i>
                                        <span>{{ __('customers.workshop_vehicle_data') }}</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <label class="block text-[12px] font-bold text-black dark:text-white">{{ __('customers.license_plate') }}</label>
                                            <input type="text" name="vehicle_license_plate" x-model="editCustomer.vehicle_license_plate" placeholder="B 1234 XYZ"
                                                class="w-full h-11 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.15] uppercase font-mono font-bold text-[14px] text-black dark:text-white focus:outline-hidden">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="block text-[12px] font-bold text-black dark:text-white">{{ __('customers.vehicle_model') }}</label>
                                            <input type="text" name="vehicle_model" x-model="editCustomer.vehicle_model" placeholder="Toyota Avanza"
                                                class="w-full h-11 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.15] text-[14px] text-black dark:text-white focus:outline-hidden">
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[12px] font-medium text-black/70 dark:text-white/70">{{ __('customers.last_odometer') }}</label>
                                        <input type="number" name="vehicle_mileage" x-model="editCustomer.vehicle_mileage" min="0" placeholder="0"
                                            class="w-full h-11 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.15] font-mono text-[14px] text-black dark:text-white focus:outline-hidden">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Kolom 2: Detail Bisnis & Alamat -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-1 border-b border-black/[0.05] dark:border-white/[0.06]">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[11px] font-bold flex items-center justify-center">2</span>
                                <h4 class="text-[13px] font-bold text-black dark:text-white uppercase tracking-wider">{{ __('customers.account_address_detail') }}</h4>
                            </div>

                            <div class="grid grid-cols-1 {{ $canB2b ? 'sm:grid-cols-2' : '' }} gap-3">
                                <div class="space-y-1">
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('customers.email') }}</label>
                                    <input type="email" name="email" x-model="editCustomer.email"
                                        class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                                </div>
                                @if ($canB2b)
                                    <div class="space-y-1">
                                        <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('customers.payment_terms') }}</label>
                                        <input type="number" name="payment_terms_days" x-model="editCustomer.payment_terms_days" min="0" max="365"
                                            class="w-full h-11 px-3.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden">
                                    </div>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">{{ __('customers.billing_shipping_address') }}</label>
                                <textarea name="billing_address" x-model="editCustomer.billing_address" rows="3"
                                    class="w-full p-3 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-hidden"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3">
                        <button type="button" @click="showEditModal = false"
                            class="h-12 px-6 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] text-black/70 dark:text-white/70 font-semibold text-[13px] hover:text-black dark:hover:text-white transition cursor-pointer">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit"
                            class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[14px] font-bold shadow-[0_2px_8px_rgba(0,122,255,0.3)] active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>{{ __('customers.update_customer') }}</span>
                        </button>
                    </div>
                </form>
                    </div>
                </div>
            </div>
        </template>

        <!-- MODAL 3: DETAIL PROFIL PELANGGAN (CANVAS XXL & MOBILE BOTTOM SHEET)        -->
        <template x-teleport="body">
            <div x-show="showDetailModal" 
                x-cloak
                @keydown.escape.window="showDetailModal = false"
                class="fixed inset-0 z-[200] overflow-y-auto">
                <!-- Backdrop -->
                <div x-show="showDetailModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="showDetailModal = false"
                    class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

                <div class="relative z-10 min-h-full flex items-end lg:items-center justify-center p-0 lg:p-6" @click.self="showDetailModal = false">
                    <div x-show="showDetailModal"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                        x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                        x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                        @click.stop
                        class="relative w-full bg-white dark:bg-[#1C1C1E] border-t lg:border border-black/[0.08] dark:border-white/[0.1] shadow-2xl overflow-y-auto
                                    rounded-t-[28px] lg:rounded-[28px] max-h-[92vh] lg:max-h-[88vh]
                                    p-5 sm:p-6 lg:p-8 lg:max-w-3xl space-y-6">
                        
                        <!-- Mobile Drag Pill Handle -->
                        <div class="w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mb-2 lg:hidden"></div>

                        <template x-if="selectedCustomer">
                            <div class="space-y-6">
                                <!-- Header Profil -->
                                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="w-14 h-14 rounded-[18px] bg-[#007AFF]/10 text-[#007AFF] font-bold text-xl flex items-center justify-center border border-[#007AFF]/20">
                                            <span x-text="(selectedCustomer.name || 'PL').substring(0,2).toUpperCase()"></span>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-black dark:text-white" x-text="selectedCustomer.name"></h3>
                                            @if ($canB2b)
                                                <p class="text-[13px] text-black/50 dark:text-white/50" x-text="selectedCustomer.company_name || '{{ __('customers.individual_customer') }}'"></p>
                                            @else
                                                <p class="text-[13px] text-black/50 dark:text-white/50">{{ __('customers.registered_customer') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <button type="button" @click="showDetailModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                                        <i data-lucide="x" class="w-5 h-5"></i>
                                    </button>
                                </div>

                        <!-- Data Kendaraan Bengkel (Jika Ada) -->
                        @if ($isWorkshop)
                            <div class="p-4 rounded-[18px] bg-[#007AFF]/5 border border-[#007AFF]/20 space-y-2.5" x-show="selectedCustomer.vehicle_license_plate">
                                <div class="text-[11px] font-bold text-[#007AFF] uppercase flex items-center gap-1.5">
                                    <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('customers.workshop_vehicle_data') }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[13px]">
                                    <div>
                                        <span class="block text-[11px] text-black/50 dark:text-white/50">{{ __('customers.license_plate') }}</span>
                                        <span class="font-mono font-bold text-black dark:text-white uppercase" x-text="selectedCustomer.vehicle_license_plate"></span>
                                    </div>
                                    <div x-show="selectedCustomer.vehicle_model">
                                        <span class="block text-[11px] text-black/50 dark:text-white/50">{{ __('customers.vehicle_model') }}</span>
                                        <span class="text-black dark:text-white font-medium" x-text="selectedCustomer.vehicle_model"></span>
                                    </div>
                                    <div x-show="selectedCustomer.vehicle_mileage">
                                        <span class="block text-[11px] text-black/50 dark:text-white/50">{{ __('customers.last_odometer') }}</span>
                                        <span class="font-mono font-bold text-black dark:text-white" x-text="formatCurrency(selectedCustomer.vehicle_mileage) + ' KM'"></span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Detail Information Bento 2-Col Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/50 dark:text-white/50">{{ __('customers.phone_whatsapp') }}</span>
                                <div class="font-mono font-bold text-[14px] text-black dark:text-white" x-text="selectedCustomer.phone || '-'"></div>
                            </div>

                            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/50 dark:text-white/50">{{ __('customers.email') }}</span>
                                <div class="text-[14px] text-black dark:text-white truncate" x-text="selectedCustomer.email || '-'"></div>
                            </div>

                            @if ($canB2b)
                                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                                    <span class="text-[11px] uppercase tracking-wider font-semibold text-black/50 dark:text-white/50">{{ __('customers.payment_terms') }}</span>
                                    <div class="font-bold text-[14px] text-black dark:text-white" x-text="(selectedCustomer.payment_terms_days > 0 ? (window.COOCA_I18N.net_days.replace(':days', selectedCustomer.payment_terms_days)) : window.COOCA_I18N.cash_terms)"></div>
                                </div>
                            @endif

                            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/50 dark:text-white/50">{{ __('customers.credit_balance') }}</span>
                                <div class="font-mono font-bold text-[15px] text-[#FF3B30]" x-text="'Rp ' + formatCurrency(selectedCustomer.current_credit_balance || 0)"></div>
                            </div>

                            @if ($canCrmLoyalty)
                                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1">
                                    <span class="text-[11px] uppercase tracking-wider font-semibold text-black/50 dark:text-white/50">{{ __('customers.points') }}</span>
                                    <div class="font-mono font-bold text-[15px] text-[#FF9500]" x-text="formatCurrency(selectedCustomer.points_balance || 0) + ' ' + window.COOCA_I18N.points"></div>
                                </div>
                            @endif

                            <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.05] dark:border-white/[0.06] space-y-1 {{ $canCrmLoyalty ? '' : 'sm:col-span-2' }}">
                                <span class="text-[11px] uppercase tracking-wider font-semibold text-black/50 dark:text-white/50">{{ __('customers.billing_shipping_address') }}</span>
                                <div class="text-[13px] text-black/80 dark:text-white/80" x-text="selectedCustomer.billing_address || window.COOCA_I18N.no_address"></div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-end gap-3 flex-wrap">
                            <a :href="'https://wa.me/' + (selectedCustomer.phone || '').replace(/[^0-9]/g, '').replace(/^0/, '62') + '?text=' + encodeURIComponent('Halo *' + selectedCustomer.name + '*, kami dari *{{ $business->name }}* menginformasikan bahwa Anda memiliki saldo tagihan/kasbon aktif sebesar *Rp ' + formatCurrency(selectedCustomer.current_credit_balance || 0) + '*. Pembayaran dapat ditransfer atau diselesaikan di kasir toko. Terima kasih.')"
                                target="_blank"
                                x-show="selectedCustomer && Number(selectedCustomer.current_credit_balance || 0) > 0 && selectedCustomer.phone"
                                class="h-12 px-5 rounded-[14px] bg-[#25D366]/10 hover:bg-[#25D366]/20 text-[#25D366] font-semibold text-[13px] transition cursor-pointer flex items-center gap-2">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                <span>Tagih via WhatsApp</span>
                            </a>
                            <button type="button" @click="showDetailModal = false; openEditModal(selectedCustomer.id)"
                                class="h-12 px-6 rounded-[14px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white font-semibold text-[13px] transition cursor-pointer flex items-center gap-2">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                <span>{{ __('common.edit') }}</span>
                            </button>
                            <button type="button" @click="showDetailModal = false"
                                class="h-12 px-8 rounded-[14px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-semibold text-[13px] transition cursor-pointer">
                                {{ __('common.close') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

        <!-- ========================================================================= -->
        <!-- MODAL 4: INTERACTIVE PRE-COMMIT IMPORT EXCEL (APPLE HIG BENTO XXL)        -->
        <!-- ========================================================================= -->
        @if (\App\Support\Context::hasPermission('customers.create'))
            <template x-teleport="body">
                <div x-show="showImportModal" 
                    x-cloak
                    @keydown.escape.window="if (!importExecuting) showImportModal = false"
                    class="fixed inset-0 z-[200] overflow-y-auto">
                    <!-- Backdrop -->
                    <div x-show="showImportModal"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="if (!importExecuting) showImportModal = false"
                        class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

                    <div class="relative z-10 min-h-full flex items-end lg:items-center justify-center p-0 lg:p-6" @click.self="if (!importExecuting) showImportModal = false">
                        <div x-show="showImportModal"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                            x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
                            x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
                            @click.stop
                            class="relative w-full bg-white dark:bg-[#1C1C1E] border-t lg:border border-black/[0.08] dark:border-white/[0.1] shadow-2xl overflow-hidden flex flex-col
                                        rounded-t-[28px] lg:rounded-[28px] max-h-[94vh] lg:max-h-[90vh] lg:max-w-6xl">

                            <!-- Drag Handle Mobile -->
                            <div class="w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto my-2.5 lg:hidden shrink-0"></div>

                            <!-- Modal Top Bar -->
                            <div class="px-6 py-4 border-b border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F9F9FB] dark:bg-[#252528]">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-base sm:text-lg font-bold text-black dark:text-white flex items-center gap-2">
                                            <span>{{ __('customers.import_customers_via_excel') }}</span>
                                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF]">Interactive Preview</span>
                                        </h3>
                                        <p class="text-[12px] text-black/50 dark:text-white/50">Validasi otomatis anti-duplikat sebelum data masuk ke sistem buku besar CRM</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('customers.template') }}"
                                        class="h-9 px-3.5 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] hover:bg-black/[0.04] dark:hover:bg-white/[0.06] text-black dark:text-white text-[12px] font-medium transition flex items-center gap-1.5 shadow-sm">
                                        <i data-lucide="download" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>{{ __('purchasing.unduh_template_excel') }}</span>
                                    </a>
                                    <button type="button" @click="if (!importExecuting) showImportModal = false"
                                        class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                                        <i data-lucide="x" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>

                    <!-- Modal Body / Content -->
                    <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-5">

                        <!-- State 1: Dropzone (No File Analyzed Yet) -->
                        <div x-show="!importPreview" class="space-y-4">
                            <!-- Langkah 1: Unduh Template -->
                            <div class="p-4 rounded-[16px] bg-[#007AFF]/[0.05] dark:bg-[#007AFF]/[0.1] border border-[#007AFF]/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 text-[#007AFF] font-bold text-[13px]">
                                        <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 flex items-center justify-center text-[11px] font-bold">1</span>
                                        <span>Langkah 1: Unduh Format Template Excel</span>
                                    </div>
                                    <p class="text-[12px] text-black/60 dark:text-white/60">Gunakan format template resmi Cooca yang dilengkapi validasi dropdown tier & limit piutang.</p>
                                </div>
                                <a href="{{ route('customers.template') }}"
                                    class="h-9 px-4 rounded-[10px] bg-[#007AFF] hover:bg-[#0066D6] text-white text-[12px] font-semibold transition flex items-center justify-center gap-1.5 shadow-sm shrink-0 active:scale-[0.98]">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('purchasing.unduh_template_xlsx') }}</span>
                                </a>
                            </div>

                            <!-- Langkah 2: Upload Dropzone -->
                            <div class="flex items-center justify-between pt-1">
                                <div class="flex items-center gap-2 text-black/80 dark:text-white/80 font-bold text-[13px]">
                                    <span class="w-5 h-5 rounded-full bg-black/10 dark:bg-white/10 flex items-center justify-center text-[11px] font-bold">2</span>
                                    <span>Langkah 2: Unggah Berkas Excel Pelanggan</span>
                                </div>
                                <span class="text-[11px] text-black/40 dark:text-white/40">Maksimal 10 MB (.xlsx, .csv)</span>
                            </div>

                            <label class="relative block w-full border-2 border-dashed border-black/15 dark:border-white/15 hover:border-[#007AFF] dark:hover:border-[#007AFF] rounded-[20px] p-8 sm:p-12 text-center cursor-pointer transition bg-black/[0.01] dark:bg-white/[0.01] hover:bg-[#007AFF]/[0.02]">
                                <input type="file" class="hidden" accept=".xlsx, .xls, .csv" @change="handleImportFileSelect($event)" :disabled="importLoading">
                                
                                <div class="space-y-3">
                                    <div class="w-14 h-14 mx-auto rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                                        <i data-lucide="upload-cloud" class="w-7 h-7" x-show="!importLoading"></i>
                                        <i data-lucide="loader" class="w-7 h-7 animate-spin" x-show="importLoading"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="text-[15px] font-bold text-black dark:text-white" x-text="importLoading ? 'Membaca dan Menganalisis Berkas Excel...' : 'Pilih Berkas Excel atau Tarik ke Sini'"></div>
                                        <div class="text-[12px] text-black/50 dark:text-white/50">Mendukung format .XLSX, .XLS, atau .CSV resmi (Maksimal 10 MB)</div>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-[12px] font-semibold text-black/70 dark:text-white/70">
                                        <i data-lucide="file-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>Template Resmi Cooca Sudah Dilengkapi Dropdown Tier</span>
                                    </div>
                                </div>
                            </label>

                            <!-- Panduan Header Warna Excel -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="p-3.5 rounded-[16px] bg-emerald-500/[0.06] border border-emerald-500/20 space-y-1">
                                    <div class="flex items-center gap-2 text-[12px] font-bold text-emerald-700 dark:text-emerald-400">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        <span>Hijau (Wajib)</span>
                                    </div>
                                    <p class="text-[11px] text-black/60 dark:text-white/60">Nama Pelanggan wajib diisi. Tidak boleh kosong.</p>
                                </div>
                                <div class="p-3.5 rounded-[16px] bg-blue-500/[0.06] border border-blue-500/20 space-y-1">
                                    <div class="flex items-center gap-2 text-[12px] font-bold text-blue-700 dark:text-blue-400">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                        <span>Biru (Lookup/Dropdown)</span>
                                    </div>
                                    <p class="text-[11px] text-black/60 dark:text-white/60">Tier Pelanggan (Retail, Gold, VIP) & Syarat Bayar (TOP).</p>
                                </div>
                                <div class="p-3.5 rounded-[16px] bg-slate-500/[0.06] border border-slate-500/20 space-y-1">
                                    <div class="flex items-center gap-2 text-[12px] font-bold text-slate-700 dark:text-slate-300">
                                        <span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span>
                                        <span>Abu-abu (Opsional)</span>
                                    </div>
                                    <p class="text-[11px] text-black/60 dark:text-white/60">Kode/ID, No WA, Email, Limit Kredit, Alamat, Catatan.</p>
                                </div>
                            </div>
                        </div>

                        <!-- State 2: Interactive Pre-Commit Preview -->
                        <div x-show="importPreview" class="space-y-5">
                            
                            <!-- KPI Summary Cards -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-1">
                                    <span class="text-[11px] font-semibold text-black/50 dark:text-white/50 uppercase tracking-wider">Total Baris</span>
                                    <div class="text-2xl font-bold font-mono text-black dark:text-white" x-text="importPreview ? importPreview.total_rows : 0"></div>
                                </div>
                                <div class="p-4 rounded-[16px] bg-emerald-500/[0.08] border border-emerald-500/20 space-y-1">
                                    <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Siap Import</span>
                                    <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400" x-text="importPreview ? importPreview.valid_count : 0"></div>
                                </div>
                                <div class="p-4 rounded-[16px] bg-amber-500/[0.08] border border-amber-500/20 space-y-1">
                                    <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Data Duplikat</span>
                                    <div class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400" x-text="importPreview ? importPreview.duplicate_count : 0"></div>
                                </div>
                                <div class="p-4 rounded-[16px] bg-rose-500/[0.08] border border-rose-500/20 space-y-1">
                                    <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wider">Tidak Valid / Error</span>
                                    <div class="text-2xl font-bold font-mono text-rose-600 dark:text-rose-400" x-text="importPreview ? importPreview.error_count : 0"></div>
                                </div>
                            </div>

                            <!-- Filter & Duplicate Strategy Bar -->
                            <div class="p-4 rounded-[18px] bg-[#F5F5F7] dark:bg-[#252528] border border-black/[0.05] dark:border-white/[0.06] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                
                                <!-- Filter Segmented Control -->
                                <div class="flex items-center gap-1 bg-black/[0.05] dark:bg-white/[0.08] p-1 rounded-[12px] self-start sm:self-auto text-[12px] font-medium">
                                    <button type="button" @click="importFilter = 'all'"
                                        class="px-3 py-1.5 rounded-[8px] transition"
                                        :class="importFilter === 'all' ? 'bg-white dark:bg-[#1C1C1E] text-black dark:text-white shadow-sm font-semibold' : 'text-black/60 dark:text-white/60'">
                                        {{ __('common.all') }} (<span x-text="importPreview ? importPreview.total_rows : 0"></span>)
                                    </button>
                                    <button type="button" @click="importFilter = 'valid'"
                                        class="px-3 py-1.5 rounded-[8px] transition"
                                        :class="importFilter === 'valid' ? 'bg-white dark:bg-[#1C1C1E] text-emerald-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                        Siap (<span x-text="importPreview ? importPreview.valid_count : 0"></span>)
                                    </button>
                                    <button type="button" @click="importFilter = 'duplicate'"
                                        class="px-3 py-1.5 rounded-[8px] transition"
                                        :class="importFilter === 'duplicate' ? 'bg-white dark:bg-[#1C1C1E] text-amber-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                        Duplikat (<span x-text="importPreview ? importPreview.duplicate_count : 0"></span>)
                                    </button>
                                    <button type="button" @click="importFilter = 'error'"
                                        class="px-3 py-1.5 rounded-[8px] transition"
                                        :class="importFilter === 'error' ? 'bg-white dark:bg-[#1C1C1E] text-rose-600 font-semibold shadow-sm' : 'text-black/60 dark:text-white/60'">
                                        Error (<span x-text="importPreview ? importPreview.error_count : 0"></span>)
                                    </button>
                                </div>

                                <!-- Duplicate Resolution Strategy -->
                                <div class="flex items-center gap-3 text-[12px]">
                                    <span class="text-black/50 dark:text-white/50 font-medium">Jika data duplikat ditemukan:</span>
                                    <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                        <input type="radio" value="skip" x-model="importDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                        <span>Lewati (Skip)</span>
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer font-medium text-black dark:text-white">
                                        <input type="radio" value="update" x-model="importDuplicateStrategy" class="text-[#007AFF] focus:ring-0">
                                        <span>Perbarui (Update)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Preview Table Scrollable -->
                            <div class="border border-black/[0.08] dark:border-white/[0.1] rounded-[18px] overflow-hidden bg-white dark:bg-[#1C1C1E]">
                                <div class="max-h-[380px] overflow-y-auto">
                                    <table class="w-full text-left text-[12px] border-collapse">
                                        <thead class="sticky top-0 bg-[#F5F5F7] dark:bg-[#252528] border-b border-black/[0.08] dark:border-white/[0.1] text-black/60 dark:text-white/60 font-semibold z-10">
                                            <tr>
                                                <th class="py-2.5 px-3 w-14 text-center">Status</th>
                                                <th class="py-2.5 px-3">Nama Pelanggan</th>
                                                <th class="py-2.5 px-3">Kode / ID</th>
                                                <th class="py-2.5 px-3">WhatsApp / HP</th>
                                                <th class="py-2.5 px-3">Email</th>
                                                <th class="py-2.5 px-3">Tier</th>
                                                <th class="py-2.5 px-3 text-right">Limit Kredit</th>
                                                <th class="py-2.5 px-3">Keterangan / Diagnostik</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.04]">
                                            <template x-for="(row, idx) in (importPreview ? importPreview.rows.filter(r => importFilter === 'all' || r.status === importFilter) : [])" :key="idx">
                                                <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition">
                                                    <td class="py-2.5 px-3 text-center">
                                                        <span x-show="row.status === 'valid'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 border border-emerald-500/20">Siap</span>
                                                        <span x-show="row.status === 'duplicate'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 border border-amber-500/20">Duplikat</span>
                                                        <span x-show="row.status === 'error'" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-600 border border-rose-500/20">Error</span>
                                                    </td>
                                                    <td class="py-2.5 px-3 font-semibold text-black dark:text-white" x-text="row.name || '-'"></td>
                                                    <td class="py-2.5 px-3 font-mono text-black/70 dark:text-white/70" x-text="row.code || '-'"></td>
                                                    <td class="py-2.5 px-3 font-mono text-black/80 dark:text-white/80" x-text="row.phone || '-'"></td>
                                                    <td class="py-2.5 px-3 text-black/70 dark:text-white/70" x-text="row.email || '-'"></td>
                                                    <td class="py-2.5 px-3 text-black/70 dark:text-white/70" x-text="row.tier_name || '-'"></td>
                                                    <td class="py-2.5 px-3 text-right font-mono text-black/80 dark:text-white/80" x-text="row.credit_limit ? ('Rp ' + formatCurrency(row.credit_limit)) : '-'"></td>
                                                    <td class="py-2.5 px-3">
                                                        <div class="space-y-0.5">
                                                            <template x-for="(msg, mi) in row.messages" :key="mi">
                                                                <div class="text-[11px]" :class="row.status === 'error' ? 'text-rose-600 dark:text-rose-400' : (row.status === 'duplicate' ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400')" x-text="msg"></div>
                                                            </template>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Footer Actions -->
                    <div class="px-6 py-4 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between shrink-0 bg-[#F9F9FB] dark:bg-[#252528]">
                        <div>
                            <button type="button" x-show="importPreview" @click="resetImport()"
                                class="h-10 px-4 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer flex items-center gap-1.5"
                                :disabled="importExecuting">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                <span>Ganti Berkas</span>
                            </button>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <button type="button" @click="if (!importExecuting) showImportModal = false"
                                class="h-10 px-5 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] dark:hover:bg-white/[0.12] text-black dark:text-white text-[13px] font-semibold transition cursor-pointer"
                                :disabled="importExecuting">{{ __('common.cancel') }}</button>

                            <button type="button" x-show="importPreview" @click="executeCustomerImport()"
                                class="h-10 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-[13px] font-semibold shadow-sm shadow-[#007AFF]/25 active:scale-[0.98] transition cursor-pointer flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="importExecuting || !importPreview || (importDuplicateStrategy === 'skip' ? importPreview.valid_count : (importPreview.valid_count + importPreview.duplicate_count)) === 0">
                                <i data-lucide="loader" class="w-4 h-4 animate-spin" x-show="importExecuting"></i>
                                <i data-lucide="check" class="w-4 h-4" x-show="!importExecuting"></i>
                                <span x-text="importExecuting ? 'Sedang Memproses...' : ('Komit Import (' + (importDuplicateStrategy === 'skip' ? importPreview.valid_count : (importPreview.valid_count + importPreview.duplicate_count)) + ' Pelanggan)')"></span>
                            </button>
                        </div>
                        </div>
                    </div>
                </div>
            </template>
        @endif

    </div>
@endsection
