@extends('layouts.app', [
    'title' => __('approvals.rules_page_title'),
    'headerTitle' => __('approvals.rules_header_title'),
    'headerSubtitle' => __('approvals.rules_header_subtitle')
])

@section('content')
<div class="max-w-[1440px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12" x-data="approvalRulesManager()">

    <!-- Header Bar -->
    <header class="rounded-[20px] backdrop-blur-2xl bg-white/85 dark:bg-[#1C1C1E]/85 border border-black/[0.06] dark:border-white/[0.08] px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#0A84FF]/20 dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                <i data-lucide="sliders" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-[#8E8E93] dark:text-[#98989D]">
                    {{ __('approvals.badge_policy_settings') }}
                </div>
                <h1 class="text-xl font-bold text-black dark:text-white tracking-tight">
                    {{ __('approvals.rules_title') }}
                </h1>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ route('approvals.inbox') }}" class="min-h-[44px] px-4 rounded-[12px] text-xs font-semibold text-black/75 dark:text-white/75 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] active:scale-[0.98] transition-all flex items-center gap-1.5">
                <i data-lucide="inbox" class="w-4 h-4"></i>
                <span>{{ __('approvals.btn_to_inbox') }}</span>
            </a>
            <button
                type="button"
                @click="openCreateModal()"
                class="min-h-[44px] px-4 rounded-[12px] text-xs font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all shadow-sm flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('approvals.btn_add_rule') }}</span>
            </button>
        </div>
    </header>

    <!-- Rules List Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($rules as $rule)
            <div class="rounded-[22px] bg-white/90 dark:bg-[#1C1C1E]/90 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] p-5 shadow-sm space-y-4 relative flex flex-col justify-between hover:border-[#007AFF]/30 transition-all">
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] uppercase tracking-wider">
                            {{ match($rule->document_type) {
                                'purchase_order' => __('approvals.filter_po'),
                                'expense' => __('approvals.filter_expense'),
                                'supplier_invoice' => __('approvals.filter_supplier_invoice'),
                                'stock_adjustment' => __('approvals.doc_type_stock_adjustment'),
                                default => $rule->document_type
                            } }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $rule->is_active ? 'bg-[#34C759]/15 text-[#34C759]' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50' }}">
                            {{ $rule->is_active ? __('approvals.status_active') : __('approvals.status_inactive') }}
                        </span>
                    </div>

                    <div>
                        <h3 class="text-base font-bold text-black dark:text-white">{{ $rule->name }}</h3>
                        <p class="text-xs text-black/60 dark:text-white/60 mt-0.5 tabular-nums">
                            {{ __('approvals.amount_label') }} <strong class="text-black dark:text-white">Rp {{ number_format($rule->min_amount, 0, ',', '.') }}</strong>
                            @if($rule->max_amount)
                                {{ __('approvals.amount_to') }} <strong class="text-black dark:text-white">Rp {{ number_format($rule->max_amount, 0, ',', '.') }}</strong>
                            @else
                                <span class="text-black/40 dark:text-white/40 font-normal">{{ __('approvals.no_upper_limit') }}</span>
                            @endif
                        </p>
                    </div>

                    <!-- Steps info -->
                    <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06] space-y-2 text-xs">
                        <div class="font-bold text-black/70 dark:text-white/70 flex items-center justify-between">
                            <span>{{ __('approvals.approval_levels_label') }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF] text-[10.5px] font-bold">{{ __('approvals.levels_count', ['count' => $rule->required_levels]) }}</span>
                        </div>
                        <ul class="space-y-1.5 text-black/70 dark:text-white/70">
                            <li class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-[10px] font-bold flex items-center justify-center shrink-0">1</span>
                                <span>{{ __('approvals.level_1_label') }} <strong class="capitalize text-black dark:text-white">{{ $rule->approver_role_level_1 }}</strong></span>
                            </li>
                            @if ($rule->required_levels >= 2)
                            <li class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-[10px] font-bold flex items-center justify-center shrink-0">2</span>
                                <span>{{ __('approvals.level_2_label') }} <strong class="capitalize text-black dark:text-white">{{ $rule->approver_role_level_2 ?? '-' }}</strong></span>
                            </li>
                            @endif
                            @if ($rule->required_levels >= 3)
                            <li class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-[#007AFF]/15 text-[#007AFF] text-[10px] font-bold flex items-center justify-center shrink-0">3</span>
                                <span>{{ __('approvals.level_3_label') }} <strong class="capitalize text-black dark:text-white">{{ $rule->approver_role_level_3 ?? '-' }}</strong></span>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Footer Actions: Edit & Delete -->
                @if(\App\Support\Context::hasPermission('approvals.manage'))
                <div class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] flex items-center justify-between gap-2">
                    <button
                        type="button"
                        @click="openEditModal({{ Js::from($rule) }})"
                        class="min-h-[36px] px-3 rounded-[10px] text-xs text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 font-semibold transition-all active:scale-[0.98] flex items-center gap-1">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        <span>{{ __('approvals.btn_edit_rule') }}</span>
                    </button>
                    
                    <form method="POST" action="{{ route('approval-rules.destroy', $rule->id) }}" onsubmit="return confirm('{{ __('approvals.confirm_delete_rule') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="min-h-[36px] px-2 text-xs text-[#FF3B30] hover:underline font-semibold flex items-center gap-1">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>{{ __('approvals.btn_delete_rule') }}</span>
                        </button>
                    </form>
                </div>
                @endif
            </div>
        @empty
            <div class="col-span-full p-12 text-center rounded-[24px] bg-white/85 dark:bg-[#1C1C1E]/85 backdrop-blur-xl border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                <div class="w-14 h-14 rounded-full bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto">
                    <i data-lucide="sliders" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-bold text-black dark:text-white">{{ __('approvals.empty_rules_title') }}</h3>
                <p class="text-xs text-black/50 dark:text-white/50 max-w-md mx-auto">
                    {{ __('approvals.empty_rules_desc') }}
                </p>
                <div class="pt-2">
                    <button
                        type="button"
                        @click="openCreateModal()"
                        class="min-h-[44px] px-5 py-2.5 rounded-[12px] bg-[#007AFF] text-white text-xs font-semibold shadow-sm inline-flex items-center gap-1.5 active:scale-[0.98]">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('approvals.btn_create_first_rule') }}</span>
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- ======================================================= -->
    <!-- MODAL BUAT ATURAN BARU                                  -->
    <!-- ======================================================= -->
    <template x-teleport="body">
        <div
            x-show="createModalOpen"
            x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4"
            @keydown.escape.window="createModalOpen = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="create-rule-modal-title">
            
            <!-- Backdrop -->
            <div
                x-show="createModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="createModalOpen = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div
                x-show="createModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                @click.outside="createModalOpen = false"
                class="relative w-full sm:max-w-lg bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-2xl space-y-4 max-h-[94vh] flex flex-col overflow-hidden z-10">
                
                <!-- Mobile Grab Bar -->
                <div class="w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-2 mb-2 sm:hidden shrink-0"></div>

                <div class="flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="sliders" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 id="create-rule-modal-title" class="text-base font-bold text-black dark:text-white">{{ __('approvals.modal_create_rule_title') }}</h3>
                            <p class="text-xs text-black/55 dark:text-white/55">{{ __('approvals.modal_create_rule_desc') }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="createModalOpen = false"
                        class="min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-colors"
                        aria-label="{{ __('approvals.close_modal') }}">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('approval-rules.store') }}" class="space-y-4 overflow-y-auto pr-1">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('approvals.field_doc_type') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="document_type" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                            <option value="purchase_order">{{ __('approvals.doc_type_po') }}</option>
                            <option value="expense">{{ __('approvals.doc_type_expense') }}</option>
                            <option value="supplier_invoice">{{ __('approvals.doc_type_supplier_invoice') }}</option>
                            <option value="stock_adjustment">{{ __('approvals.doc_type_stock_adjustment') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('approvals.field_rule_name') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" required placeholder="{{ __('approvals.field_rule_name_placeholder') }}" class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('approvals.field_min_amount') }} <span class="text-[#FF3B30]">*</span></label>
                            <input type="number" step="1000" name="min_amount" required placeholder="10000000" class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('approvals.field_max_amount') }}</label>
                            <input type="number" step="1000" name="max_amount" placeholder="{{ __('approvals.field_max_amount_placeholder') }}" class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('approvals.field_required_levels') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="required_levels" x-model.number="formLevels" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                            <option value="1">{{ __('approvals.levels_option_1') }}</option>
                            <option value="2">{{ __('approvals.levels_option_2') }}</option>
                            <option value="3">{{ __('approvals.levels_option_3') }}</option>
                        </select>
                    </div>

                    <div class="space-y-2.5 p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">{{ __('approvals.field_approver_level_1') }} <span class="text-[#FF3B30]">*</span></label>
                            <select name="approver_role_level_1" required class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                                <option value="supervisor" selected>{{ __('approvals.role_supervisor') }}</option>
                                <option value="manager">{{ __('approvals.role_manager') }}</option>
                                <option value="admin">{{ __('approvals.role_admin') }}</option>
                                <option value="owner">{{ __('approvals.role_owner') }}</option>
                                @foreach($availableRoles as $r)
                                    @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                        <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div x-show="formLevels >= 2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">{{ __('approvals.field_approver_level_2') }} <span class="text-[#FF3B30]">*</span></label>
                            <select name="approver_role_level_2" :required="formLevels >= 2" class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                                <option value="manager" selected>{{ __('approvals.role_manager') }}</option>
                                <option value="supervisor">{{ __('approvals.role_supervisor') }}</option>
                                <option value="admin">{{ __('approvals.role_admin') }}</option>
                                <option value="owner">{{ __('approvals.role_owner') }}</option>
                                @foreach($availableRoles as $r)
                                    @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                        <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div x-show="formLevels >= 3">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">{{ __('approvals.field_approver_level_3') }} <span class="text-[#FF3B30]">*</span></label>
                            <select name="approver_role_level_3" :required="formLevels >= 3" class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                                <option value="owner" selected>{{ __('approvals.role_owner') }}</option>
                                <option value="admin">{{ __('approvals.role_admin') }}</option>
                                <option value="manager">{{ __('approvals.role_manager') }}</option>
                                @foreach($availableRoles as $r)
                                    @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                        <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-black/75 dark:text-white/75">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                            <span>{{ __('approvals.field_rule_active') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button
                            type="button"
                            @click="createModalOpen = false"
                            class="min-h-[44px] px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 active:scale-[0.98]">
                            {{ __('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="min-h-[44px] px-5 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold shadow-sm active:scale-[0.98]">
                            {{ __('approvals.btn_save_rule') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- ======================================================= -->
    <!-- MODAL EDIT ATURAN                                       -->
    <!-- ======================================================= -->
    <template x-teleport="body">
        <div
            x-show="editModalOpen"
            x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto flex items-end sm:items-center justify-center p-0 sm:p-4"
            @keydown.escape.window="editModalOpen = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="edit-rule-modal-title">
            
            <!-- Backdrop -->
            <div
                x-show="editModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="editModalOpen = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div
                x-show="editModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                @click.outside="editModalOpen = false"
                class="relative w-full sm:max-w-lg bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[24px] border border-black/[0.06] dark:border-white/[0.08] p-6 shadow-2xl space-y-4 max-h-[94vh] flex flex-col overflow-hidden z-10">
                
                <!-- Mobile Grab Bar -->
                <div class="w-10 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto -mt-2 mb-2 sm:hidden shrink-0"></div>

                <div class="flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 id="edit-rule-modal-title" class="text-base font-bold text-black dark:text-white">{{ __('approvals.modal_edit_rule_title') }}</h3>
                            <p class="text-xs text-black/55 dark:text-white/55" x-text="editForm.name"></p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="editModalOpen = false"
                        class="min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center text-black/50 dark:text-white/50 hover:bg-black/5 dark:hover:bg-white/10 active:scale-95 transition-colors"
                        aria-label="{{ __('approvals.close_modal') }}">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/settings/approval-rules') }}/' + editForm.id" class="space-y-4 overflow-y-auto pr-1">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('approvals.field_doc_type') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="document_type" x-model="editForm.document_type" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                            <option value="purchase_order">{{ __('approvals.doc_type_po') }}</option>
                            <option value="expense">{{ __('approvals.doc_type_expense') }}</option>
                            <option value="supplier_invoice">{{ __('approvals.doc_type_supplier_invoice') }}</option>
                            <option value="stock_adjustment">{{ __('approvals.doc_type_stock_adjustment') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('approvals.field_rule_name') }} <span class="text-[#FF3B30]">*</span></label>
                        <input type="text" name="name" x-model="editForm.name" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('approvals.field_min_amount') }} <span class="text-[#FF3B30]">*</span></label>
                            <input type="number" step="1000" name="min_amount" x-model="editForm.min_amount" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('approvals.field_max_amount') }}</label>
                            <input type="number" step="1000" name="max_amount" x-model="editForm.max_amount" placeholder="{{ __('approvals.field_max_amount_placeholder') }}" class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('approvals.field_required_levels') }} <span class="text-[#FF3B30]">*</span></label>
                        <select name="required_levels" x-model.number="editForm.required_levels" required class="w-full px-3.5 py-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                            <option value="1">{{ __('approvals.levels_option_1') }}</option>
                            <option value="2">{{ __('approvals.levels_option_2') }}</option>
                            <option value="3">{{ __('approvals.levels_option_3') }}</option>
                        </select>
                    </div>

                    <div class="space-y-2.5 p-3.5 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.06]">
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">{{ __('approvals.field_approver_level_1') }} <span class="text-[#FF3B30]">*</span></label>
                            <select name="approver_role_level_1" x-model="editForm.approver_role_level_1" required class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                                <option value="supervisor">{{ __('approvals.role_supervisor') }}</option>
                                <option value="manager">{{ __('approvals.role_manager') }}</option>
                                <option value="admin">{{ __('approvals.role_admin') }}</option>
                                <option value="owner">{{ __('approvals.role_owner') }}</option>
                                @foreach($availableRoles as $r)
                                    @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                        <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div x-show="editForm.required_levels >= 2">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">{{ __('approvals.field_approver_level_2') }} <span class="text-[#FF3B30]">*</span></label>
                            <select name="approver_role_level_2" x-model="editForm.approver_role_level_2" :required="editForm.required_levels >= 2" class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                                <option value="manager">{{ __('approvals.role_manager') }}</option>
                                <option value="supervisor">{{ __('approvals.role_supervisor') }}</option>
                                <option value="admin">{{ __('approvals.role_admin') }}</option>
                                <option value="owner">{{ __('approvals.role_owner') }}</option>
                                @foreach($availableRoles as $r)
                                    @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                        <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div x-show="editForm.required_levels >= 3">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-black/60 dark:text-white/60 mb-1">{{ __('approvals.field_approver_level_3') }} <span class="text-[#FF3B30]">*</span></label>
                            <select name="approver_role_level_3" x-model="editForm.approver_role_level_3" :required="editForm.required_levels >= 3" class="w-full px-3 py-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] text-[16px] sm:text-xs text-black dark:text-white outline-none">
                                <option value="owner">{{ __('approvals.role_owner') }}</option>
                                <option value="admin">{{ __('approvals.role_admin') }}</option>
                                <option value="manager">{{ __('approvals.role_manager') }}</option>
                                @foreach($availableRoles as $r)
                                    @if(!in_array($r->slug, ['supervisor', 'manager', 'admin', 'owner']))
                                        <option value="{{ $r->slug }}">{{ $r->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-black/75 dark:text-white/75">
                            <input type="checkbox" name="is_active" value="1" x-model="editForm.is_active" class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                            <span>{{ __('approvals.field_rule_active') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button
                            type="button"
                            @click="editModalOpen = false"
                            class="min-h-[44px] px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 active:scale-[0.98]">
                            {{ __('common.cancel') }}
                        </button>
                        <button
                            type="submit"
                            class="min-h-[44px] px-5 py-2 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-semibold shadow-sm active:scale-[0.98]">
                            {{ __('approvals.btn_save_changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

</div>

<script>
function approvalRulesManager() {
    return {
        createModalOpen: false,
        editModalOpen: false,
        formLevels: 1,
        editForm: {
            id: '',
            name: '',
            document_type: 'purchase_order',
            min_amount: 0,
            max_amount: null,
            required_levels: 1,
            approver_role_level_1: 'supervisor',
            approver_role_level_2: 'manager',
            approver_role_level_3: 'owner',
            is_active: true
        },

        openCreateModal() {
            this.formLevels = 1;
            this.createModalOpen = true;
        },

        openEditModal(rule) {
            this.editForm = {
                id: rule.id,
                name: rule.name,
                document_type: rule.document_type,
                min_amount: rule.min_amount,
                max_amount: rule.max_amount,
                required_levels: rule.required_levels,
                approver_role_level_1: rule.approver_role_level_1 || 'supervisor',
                approver_role_level_2: rule.approver_role_level_2 || 'manager',
                approver_role_level_3: rule.approver_role_level_3 || 'owner',
                is_active: Boolean(rule.is_active)
            };
            this.editModalOpen = true;
        }
    };
}
</script>
@endsection
