@extends('layouts.app', [
    'title' => __('products.modifiers_title'),
    'headerTitle' => __('products.modifiers_title'),
    'headerSubtitle' => __('products.modifiers_subtitle'),
])

@section('content')
<div class="max-w-[1440px] mx-auto w-full px-4 sm:px-6 lg:px-8 space-y-6 pb-28 sm:pb-32 lg:pb-10" x-data="modifierManager()">

    <!-- ===================================================== -->
    <!-- 1. TOOLBAR / PAGE HEADER                              -->
    <!-- ===================================================== -->
    <x-module-header
        :title="__('products.modifiers_title')"
        :subtitle="__('products.modifiers_subtitle')"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => __('products.page_title'), 'url' => route('products.index')],
            ['label' => __('products.breadcrumb_modifiers'), 'url' => null],
        ]">
        <div class="flex items-center gap-2">
            <!-- Toggle Quick Visual Guide Button -->
            <button type="button" @click="toggleGuide()"
                class="min-h-[44px] sm:min-h-0 h-10 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 text-[13px] font-medium flex items-center justify-center gap-1.5 transition active:scale-[0.98] shrink-0"
                :title="showGuide ? '{{ __('products.modifiers_toggle_guide') }}' : '{{ __('products.modifiers_show_guide') }}'">
                <i data-lucide="help-circle" class="w-4 h-4 text-[#007AFF]"></i>
                <span class="hidden sm:inline" x-text="showGuide ? '{{ __('products.modifiers_toggle_guide') }}' : '{{ __('products.modifiers_show_guide') }}'"></span>
            </button>

            @if(\App\Support\Context::hasPermission('pos.modifiers'))
            <button type="button" @click="openAddGroup()"
                class="min-h-[44px] sm:min-h-0 h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-semibold flex items-center justify-center gap-2 transition shadow-sm shadow-[#007AFF]/25 shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('products.btn_create_group') }}</span>
            </button>
            @endif
        </div>
    </x-module-header>

    <!-- ===================================================== -->
    <!-- 2. UNIFIED SEGMENTED NAVIGATION                       -->
    <!-- ===================================================== -->
    <x-module-tabs module="products" />

    <!-- ===================================================== -->
    <!-- 3. EDUCATIONAL ONBOARDING BENTO (Cara Kerja Modifier) -->
    <!-- ===================================================== -->
    <div x-show="showGuide" x-collapse x-cloak>
        <div class="rounded-[20px] bg-gradient-to-br from-[#007AFF]/[0.04] via-white to-black/[0.02] dark:from-[#007AFF]/[0.08] dark:via-[#1C1C1E] dark:to-white/[0.02] border border-[#007AFF]/20 dark:border-[#007AFF]/30 p-5 sm:p-6 shadow-sm space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight">{{ __('products.modifiers_guide_title') }}</h3>
                        <p class="text-[12.5px] text-black/60 dark:text-white/60 leading-relaxed">{{ __('products.modifiers_guide_subtitle') }}</p>
                    </div>
                </div>
                <button type="button" @click="toggleGuide()" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white p-1 rounded-lg min-h-[36px] min-w-[36px] flex items-center justify-center">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- 3 Educational Columns Bento -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 pt-1">
                <!-- Col 1: Pilihan Tunggal -->
                <div class="p-4 rounded-[16px] bg-white dark:bg-[#252528] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                    <div class="flex items-center gap-2 text-[#007AFF]">
                        <i data-lucide="disc" class="w-4 h-4"></i>
                        <span class="text-[13px] font-bold">{{ __('products.modifiers_guide_single_title') }}</span>
                    </div>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        {{ __('products.modifiers_guide_single_desc') }}
                    </p>
                    <div class="pt-1.5 flex items-center gap-1.5 text-[11px] font-medium text-black/50 dark:text-white/50">
                        <span class="px-2 py-0.5 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06]">Ukuran: Regular</span>
                        <span class="px-2 py-0.5 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06]">Large (+5k)</span>
                    </div>
                </div>

                <!-- Col 2: Pilihan Ganda / Tambahan -->
                <div class="p-4 rounded-[16px] bg-white dark:bg-[#252528] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                    <div class="flex items-center gap-2 text-[#AF52DE] dark:text-[#BF5AF2]">
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                        <span class="text-[13px] font-bold">{{ __('products.modifiers_guide_multiple_title') }}</span>
                    </div>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        {{ __('products.modifiers_guide_multiple_desc') }}
                    </p>
                    <div class="pt-1.5 flex items-center gap-1.5 text-[11px] font-medium text-black/50 dark:text-white/50">
                        <span class="px-2 py-0.5 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06]">Boba (+3k)</span>
                        <span class="px-2 py-0.5 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06]">Grass Jelly (+3k)</span>
                    </div>
                </div>

                <!-- Col 3: Pemotongan Stok Bahan Baku -->
                <div class="p-4 rounded-[16px] bg-white dark:bg-[#252528] border border-black/[0.06] dark:border-white/[0.08] space-y-2">
                    <div class="flex items-center gap-2 text-[#34C759] dark:text-[#30D158]">
                        <i data-lucide="boxes" class="w-4 h-4"></i>
                        <span class="text-[13px] font-bold">{{ __('products.modifiers_guide_bom_title') }}</span>
                    </div>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        {{ __('products.modifiers_guide_bom_desc') }}
                    </p>
                    <div class="pt-1.5 flex items-center gap-1.5 text-[11px] font-medium text-black/50 dark:text-white/50">
                        <span class="px-2 py-0.5 rounded-[6px] bg-black/[0.04] dark:bg-white/[0.06]">Potong 200ml Susu Oat</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 4. KPI SUMMARY ROW (Apple Bento Cockpit)              -->
    <!-- ===================================================== -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 sm:gap-4">
        <!-- Tile 1: Total Grup Modifier -->
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_3px_rgba(0,0,0,0.03)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Grup Pilihan</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="sliders" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold tabular-nums text-black dark:text-white tracking-tight">
                    {{ $groups->count() }}
                </span>
                <span class="text-[11.5px] text-black/40 dark:text-white/40">Grup Konfigurasi</span>
            </div>
        </div>

        <!-- Tile 2: Total Opsi Varian / Add-On -->
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_3px_rgba(0,0,0,0.03)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Total Opsi Pilihan</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2] flex items-center justify-center">
                    <i data-lucide="list-plus" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold tabular-nums text-[#AF52DE] dark:text-[#BF5AF2] tracking-tight">
                    {{ $groups->sum(fn($g) => $g->options->count()) }}
                </span>
                <span class="text-[11.5px] font-semibold text-[#AF52DE] dark:text-[#BF5AF2]">Opsi Siap Kasir</span>
            </div>
        </div>

        <!-- Tile 3: Menu / Produk Terhubung -->
        <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] p-4 sm:p-5 flex flex-col justify-between shadow-[0_1px_3px_rgba(0,0,0,0.03)]">
            <div class="flex items-center justify-between">
                <span class="text-[12px] font-medium text-black/50 dark:text-white/50">Menu Terhubung</span>
                <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="coffee" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3 flex items-baseline justify-between">
                <span class="text-[26px] font-bold tabular-nums text-[#34C759] dark:text-[#30D158] tracking-tight">
                    {{ $products->filter(fn($p) => $groups->contains(fn($g) => $g->products->contains('id', $p->id)))->count() }}
                </span>
                <span class="text-[11.5px] font-semibold text-[#34C759] dark:text-[#30D158]">Dari {{ $products->count() }} Menu</span>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 5. MODIFIERS GROUP LIST                               -->
    <!-- ===================================================== -->
    @if($groups->isEmpty())
    <div class="p-12 text-center rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-dashed border-black/15 dark:border-white/15 space-y-4 shadow-sm">
        <div class="w-16 h-16 rounded-2xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center mx-auto shadow-inner">
            <i data-lucide="sliders" class="w-8 h-8"></i>
        </div>
        <div class="space-y-1.5 max-w-md mx-auto">
            <h3 class="font-bold text-[18px] text-black dark:text-white tracking-tight">{{ __('products.empty_modifier_groups') }}</h3>
            <p class="text-[13px] text-black/55 dark:text-white/55 leading-relaxed">
                {{ __('products.empty_modifier_groups_desc') }}
            </p>
        </div>
        @if(\App\Support\Context::hasPermission('pos.modifiers'))
        <div class="pt-2">
            <button type="button" @click="openAddGroup()"
                class="min-h-[44px] h-11 px-6 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13.5px] font-semibold inline-flex items-center gap-2 shadow-sm shadow-[#007AFF]/30 transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('products.btn_create_first_group') }}</span>
            </button>
        </div>
        @endif
    </div>
    @else
    <div class="space-y-6">
        @foreach($groups as $group)
        <div class="rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.03)] overflow-hidden transition"
             x-data="{ showProductsExpanded: false }">

            <!-- ===================================================== -->
            <!-- Group Header Bar (Bento Presentation)                 -->
            <!-- ===================================================== -->
            <div class="p-5 sm:p-6 bg-black/[0.015] dark:bg-white/[0.02] border-b border-black/[0.06] dark:border-white/[0.08] flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex items-center gap-3 flex-wrap">
                        <!-- Icon Reflecting Selection Type -->
                        <div class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0 {{ $group->selection_type === \App\Models\ModifierGroup::SELECTION_SINGLE ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2]' }}">
                            @if($group->selection_type === \App\Models\ModifierGroup::SELECTION_SINGLE)
                                <i data-lucide="disc" class="w-4 h-4"></i>
                            @else
                                <i data-lucide="check-square" class="w-4 h-4"></i>
                            @endif
                        </div>

                        <div>
                            <h2 class="text-[17px] sm:text-[18px] font-bold text-black dark:text-white tracking-tight leading-snug">{{ $group->name }}</h2>
                            @if($group->description)
                                <p class="text-[12.5px] text-black/55 dark:text-white/55 mt-0.5">{{ $group->description }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Meaningful Badges (No Ambiguity) -->
                    <div class="flex items-center gap-2 flex-wrap pt-1">
                        <!-- Badge 1: Selection Type -->
                        @if($group->selection_type === \App\Models\ModifierGroup::SELECTION_SINGLE)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                                <i data-lucide="disc" class="w-3 h-3"></i>
                                {{ __('products.modifiers_card_single_badge') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold bg-[#AF52DE]/10 text-[#AF52DE] dark:text-[#BF5AF2]">
                                <i data-lucide="check-square" class="w-3 h-3"></i>
                                {{ __('products.modifiers_card_multiple_badge') }}
                            </span>
                        @endif

                        <!-- Badge 2: Requirement Status -->
                        @if($group->is_required)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold bg-[#FF9500]/12 text-[#B25E00] dark:text-[#FF9F0A]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF9500]"></span>
                                {{ __('products.modifiers_card_required_badge') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-[11.5px] font-medium bg-black/[0.04] dark:bg-white/[0.06] text-black/60 dark:text-white/60">
                                <span class="w-1.5 h-1.5 rounded-full bg-black/30 dark:bg-white/30"></span>
                                {{ __('products.modifiers_card_optional_badge') }}
                            </span>
                        @endif

                        <!-- Badge 3: Rule Explanation -->
                        <span class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-medium bg-black/[0.03] dark:bg-white/[0.05] text-black/60 dark:text-white/60 tabular-nums">
                            @if($group->selection_type === \App\Models\ModifierGroup::SELECTION_SINGLE)
                                @if($group->is_required)
                                    Harus Pilih 1
                                @else
                                    Maksimal 1 Opsi
                                @endif
                            @else
                                Batas: {{ $group->min_selection }} s/d {{ $group->max_selection }} Pilihan
                            @endif
                        </span>

                        <!-- Badge 4: Options Count -->
                        <span class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] text-black/70 dark:text-white/70 tabular-nums">
                            {{ $group->options->count() }} Opsi
                        </span>
                    </div>

                    <!-- Linked Products Counter with Interactive Toggle -->
                    <div class="pt-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[12px] font-medium text-black/50 dark:text-white/50">
                                {{ __('products.applied_to_products') }}
                            </span>
                            @if($group->products->isEmpty())
                                <span class="text-[12px] italic text-[#FF9500] dark:text-[#FF9F0A] font-medium">
                                    {{ __('products.no_products_linked') }}
                                </span>
                            @else
                                <button type="button" @click="showProductsExpanded = !showProductsExpanded"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-[8px] text-[11.5px] font-semibold bg-[#007AFF]/8 text-[#007AFF] dark:text-[#0A84FF] hover:bg-[#007AFF]/15 transition min-h-[36px]">
                                    <span>{{ __('products.modifiers_linked_products_count', ['count' => $group->products->count()]) }}</span>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': showProductsExpanded }"></i>
                                </button>
                            @endif
                        </div>

                        <!-- Expandable Products List Box -->
                        <div x-show="showProductsExpanded" x-collapse x-cloak class="mt-2.5">
                            <div class="p-3 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] flex items-center gap-1.5 flex-wrap">
                                @foreach($group->products as $p)
                                    <span class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-medium bg-white dark:bg-[#252528] text-black/80 dark:text-white/80 border border-black/5 dark:border-white/5 shadow-2xs">
                                        {{ $p->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Group Actions Toolbar -->
                @if(\App\Support\Context::hasPermission('pos.modifiers'))
                <div class="flex items-center gap-2 shrink-0 self-start lg:self-center">
                    <button type="button" @click="openAddOption({{ json_encode($group) }})"
                        class="min-h-[44px] sm:min-h-0 h-10 px-4 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-semibold flex items-center gap-1.5 shadow-sm shadow-[#007AFF]/20 transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>{{ __('products.btn_add_option') }}</span>
                    </button>

                    <button type="button" @click="openEditGroup({{ json_encode($group) }})"
                        class="min-h-[44px] sm:min-h-0 h-10 px-3.5 rounded-[12px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-[13px] font-medium text-black/80 dark:text-white/80 transition active:scale-[0.98]"
                        title="{{ __('products.btn_edit_group') }}">
                        <i data-lucide="pencil" class="w-4 h-4 text-black/60 dark:text-white/60"></i>
                        <span class="hidden sm:inline ml-1">{{ __('products.btn_edit_group') }}</span>
                    </button>

                    <button type="button" @click="openDeleteGroup('{{ $group->id }}', '{{ addslashes($group->name) }}')"
                        class="min-h-[44px] sm:min-h-0 h-10 w-10 rounded-[12px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 flex items-center justify-center transition active:scale-[0.98]"
                        title="{{ __('products.btn_delete_group') }}">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>

                    <form id="form-delete-group-{{ $group->id }}" method="POST" action="{{ route('pos.modifiers.groups.destroy', $group->id) }}" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
                @endif
            </div>

            <!-- ===================================================== -->
            <!-- Options Table (Desktop & Tablet)                      -->
            <!-- ===================================================== -->
            <div class="hidden sm:block overflow-x-auto scrollbar-thin">
                <table class="w-full text-left text-[13px]">
                    <thead class="bg-black/[0.02] dark:bg-white/[0.025] border-b border-black/[0.06] dark:border-white/[0.08] text-[11px] font-semibold text-black/45 dark:text-white/45 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-5">{{ __('products.th_option_name') }}</th>
                            <th class="py-3 px-4">{{ __('products.th_price_delta') }}</th>
                            <th class="py-3 px-4">{{ __('products.th_material_consumption') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('products.th_status') }}</th>
                            <th class="py-3 px-5 text-right whitespace-nowrap sticky right-0 bg-[#FBFBFC] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('products.th_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($group->options as $opt)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors group">
                            <!-- Option Name -->
                            <td class="py-3.5 px-5 font-semibold text-black dark:text-white text-[13.5px]">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $opt->is_active ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20' }}"></span>
                                    <span>{{ $opt->name }}</span>
                                </div>
                            </td>

                            <!-- Price Delta / Extra Cost -->
                            <td class="py-3.5 px-4 tabular-nums font-semibold">
                                @if($opt->price_delta > 0)
                                    <span class="text-[#34C759] dark:text-[#30D158] bg-[#34C759]/10 px-2 py-0.5 rounded-[6px]">
                                        +{{ $business->currency_symbol }} {{ number_format($opt->price_delta, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-black/50 dark:text-white/50 bg-black/[0.04] dark:bg-white/[0.06] px-2 py-0.5 rounded-[6px]">
                                        {{ __('products.modifiers_free_badge') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Material Consumption (BOM) -->
                            <td class="py-3.5 px-4">
                                @if($opt->affects_material && $opt->materials->isNotEmpty())
                                    <div class="flex flex-col gap-1">
                                        @foreach($opt->materials as $mat)
                                            <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-black/75 dark:text-white/75">
                                                <i data-lucide="box" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                                                <span>{{ $mat->material?->name ?? 'Material' }}:</span>
                                                <strong class="text-black dark:text-white tabular-nums">{{ (float)$mat->quantity }} {{ $mat->unit?->name ?? $mat->material?->unit?->name ?? 'Unit' }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                @elseif($opt->affects_material)
                                    <span class="inline-flex items-center gap-1 text-[11.5px] text-[#FF9500] font-medium bg-[#FF9500]/10 px-2 py-0.5 rounded-[6px]">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                        {{ __('products.material_needs_mapping') }}
                                    </span>
                                @else
                                    <span class="text-[12px] text-black/40 dark:text-white/40 italic">
                                        {{ __('products.no_material_deduction') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Status Active/Inactive -->
                            <td class="py-3.5 px-4 text-center">
                                @if($opt->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                        {{ __('products.status_active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-medium bg-black/[0.05] dark:bg-white/[0.08] text-black/45 dark:text-white/45">
                                        {{ __('products.status_inactive') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Actions (Apple HIG min-target 36px) -->
                            <td class="py-3.5 px-5 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                @if(\App\Support\Context::hasPermission('pos.modifiers'))
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openEditOption({{ json_encode($group) }}, {{ json_encode($opt) }})"
                                        class="w-9 h-9 min-h-[36px] min-w-[36px] rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                        title="{{ __('products.btn_edit') }}"
                                        aria-label="{{ __('products.btn_edit') }}">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>
                                    <button type="button" @click="openDeleteOption('{{ $opt->id }}', '{{ addslashes($opt->name) }}')"
                                        class="w-9 h-9 min-h-[36px] min-w-[36px] rounded-[10px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                        title="{{ __('products.btn_delete') }}"
                                        aria-label="{{ __('products.btn_delete') }}">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                <form id="form-delete-option-{{ $opt->id }}" method="POST" action="{{ route('pos.modifiers.options.destroy', $opt->id) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-[13px] text-black/45 dark:text-white/45">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="list-plus" class="w-8 h-8 text-black/20 dark:text-white/20"></i>
                                    <span>{{ __('products.empty_options_in_group') }}</span>
                                    @if(\App\Support\Context::hasPermission('pos.modifiers'))
                                    <button type="button" @click="openAddOption({{ json_encode($group) }})"
                                        class="mt-1 text-[13px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('products.btn_add_option') }}</span>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- ===================================================== -->
            <!-- Options Mobile Inset Cards (< sm)                     -->
            <!-- ===================================================== -->
            <div class="sm:hidden divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                @forelse($group->options as $opt)
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $opt->is_active ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20' }}"></span>
                                <h4 class="text-[15px] font-semibold text-black dark:text-white">{{ $opt->name }}</h4>
                            </div>
                            <div class="text-[13px] font-bold tabular-nums">
                                @if($opt->price_delta > 0)
                                    <span class="text-[#34C759] dark:text-[#30D158]">+{{ $business->currency_symbol }} {{ number_format($opt->price_delta, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-black/50 dark:text-white/50">{{ __('products.modifiers_free_badge') }}</span>
                                @endif
                            </div>
                        </div>

                        @if(\App\Support\Context::hasPermission('pos.modifiers'))
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button type="button" @click="openEditOption({{ json_encode($group) }}, {{ json_encode($opt) }})"
                                class="h-9 px-3 rounded-[10px] text-[12.5px] font-medium text-[#007AFF] bg-[#007AFF]/10 flex items-center gap-1 min-h-[36px]">
                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                <span>{{ __('products.btn_edit') }}</span>
                            </button>
                            <button type="button" @click="openDeleteOption('{{ $opt->id }}', '{{ addslashes($opt->name) }}')"
                                class="h-9 px-3 rounded-[10px] text-[12.5px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 flex items-center gap-1 min-h-[36px]">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        @endif
                    </div>

                    @if($opt->affects_material && $opt->materials->isNotEmpty())
                    <div class="pt-2.5 border-t border-black/[0.04] dark:border-white/[0.06] text-[12px] space-y-1">
                        @foreach($opt->materials as $mat)
                            <div class="flex items-center justify-between text-black/70 dark:text-white/70">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="box" class="w-3 h-3 text-[#007AFF]"></i>
                                    <span>{{ $mat->material?->name ?? 'Material' }}</span>
                                </span>
                                <strong class="tabular-nums text-black dark:text-white">{{ (float)$mat->quantity }} {{ $mat->unit?->name ?? $mat->material?->unit?->name ?? 'Unit' }}</strong>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <div class="p-6 text-center text-[13px] text-black/45 dark:text-white/45">
                    {{ __('products.empty_options_in_group') }}
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: BUAT / EDIT MODIFIER GROUP (Smart & Anti-Ambiguitas)-->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.modifiers'))
    <template x-teleport="body">
        <div x-show="showAddGroupModal || showEditGroupModal" x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
             @keydown.escape.window="showAddGroupModal = false; showEditGroupModal = false;">
            <div class="w-full max-w-full sm:max-w-2xl md:max-w-3xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                 @click.outside="showAddGroupModal = false; showEditGroupModal = false;">

                <!-- Mobile Grab Bar -->
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

                <!-- Sticky Top Header -->
                <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="sliders" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate"
                                x-text="showEditGroupModal ? '{{ __('products.modal_edit_group_title') }}' : '{{ __('products.modal_create_group_title') }}'"></h3>
                            <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate">{{ __('products.modal_group_sub') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="showAddGroupModal = false; showEditGroupModal = false;"
                        class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0 min-w-[36px]">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Form Content with Smart Rule Synchronization -->
                <form method="POST" :action="showEditGroupModal ? '{{ url('pos/modifiers/groups') }}/' + groupForm.id : '{{ route('pos.modifiers.groups.store') }}'"
                    class="flex-1 overflow-y-auto p-6 sm:p-8 overscroll-contain sidebar-scroll flex flex-col justify-between space-y-6">
                    @csrf
                    <template x-if="showEditGroupModal">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Hidden inputs submitted to backend to satisfy backend validation -->
                    <input type="hidden" name="selection_type" :value="groupForm.selection_type">
                    <input type="hidden" name="min_selection" :value="groupForm.min_selection">
                    <input type="hidden" name="max_selection" :value="groupForm.max_selection">
                    <input type="hidden" name="is_required" :value="groupForm.is_required ? '1' : '0'">

                    <div class="space-y-6">

                        <!-- SECTION 1: NAMA & KETERANGAN -->
                        <div class="space-y-3.5">
                            <div>
                                <label class="block font-semibold text-black/80 dark:text-white/80 mb-1.5 text-[13px]">
                                    {{ __('products.label_group_name') }} <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="name" x-model="groupForm.name" required placeholder="{{ __('products.placeholder_group_name') }}"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <div>
                                <label class="block font-semibold text-black/80 dark:text-white/80 mb-1.5 text-[13px]">
                                    {{ __('products.label_group_desc') }}
                                </label>
                                <input type="text" name="description" x-model="groupForm.description" placeholder="{{ __('products.placeholder_group_desc') }}"
                                    class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>
                        </div>

                        <!-- SECTION 2: SMART PRESET BUTTONS (1-Klik Bebas Bingung) -->
                        <div class="p-4 rounded-[16px] bg-[#007AFF]/[0.03] dark:bg-[#007AFF]/[0.06] border border-[#007AFF]/15 space-y-2.5">
                            <span class="text-[11.5px] font-semibold text-[#007AFF] uppercase tracking-wider block">
                                {{ __('products.modifiers_preset_title') }}
                            </span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" @click="applyPreset('required_single')"
                                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-black dark:text-white transition active:scale-95 shadow-2xs">
                                    {{ __('products.modifiers_preset_required_single') }}
                                </button>
                                <button type="button" @click="applyPreset('optional_multi')"
                                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-black dark:text-white transition active:scale-95 shadow-2xs">
                                    {{ __('products.modifiers_preset_optional_multi') }}
                                </button>
                                <button type="button" @click="applyPreset('optional_single')"
                                    class="h-8 px-3 rounded-[8px] text-[12px] font-medium bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-black dark:text-white transition active:scale-95 shadow-2xs">
                                    {{ __('products.modifiers_preset_optional_single') }}
                                </button>
                            </div>
                        </div>

                        <!-- SECTION 3: ATURAN PEMILIHAN (Visual Radio Cards - Anti Ambiguitas) -->
                        <div class="space-y-3">
                            <label class="block font-semibold text-black/80 dark:text-white/80 text-[13px]">
                                Aturan Pemilihan Pelanggan <span class="text-[#FF3B30]">*</span>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <!-- Card A: Pilihan Tunggal -->
                                <div @click="setSelectionType('single')"
                                     class="p-4 rounded-[16px] border-2 cursor-pointer transition select-none flex flex-col justify-between space-y-3"
                                     :class="groupForm.selection_type === 'single' ? 'bg-[#007AFF]/[0.04] border-[#007AFF] dark:bg-[#007AFF]/[0.08]' : 'bg-black/[0.015] dark:bg-white/[0.02] border-black/[0.08] dark:border-white/[0.1] hover:border-black/20'">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                                 :class="groupForm.selection_type === 'single' ? 'bg-[#007AFF] text-white' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50'">
                                                <i data-lucide="disc" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-[14px] font-bold text-black dark:text-white">Pilihan Tunggal</h4>
                                                <p class="text-[11.5px] text-black/50 dark:text-white/50">Pilih tepat 1 opsi</p>
                                            </div>
                                        </div>
                                        <div class="w-5 h-5 rounded-full border flex items-center justify-center"
                                             :class="groupForm.selection_type === 'single' ? 'border-[#007AFF] bg-[#007AFF]' : 'border-black/20 dark:border-white/20'">
                                            <div class="w-2 h-2 rounded-full bg-white" x-show="groupForm.selection_type === 'single'"></div>
                                        </div>
                                    </div>
                                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                        Cocok untuk Ukuran Cup, Suhu Minuman, atau Level Pedas.
                                    </p>
                                </div>

                                <!-- Card B: Pilihan Tambahan / Multi -->
                                <div @click="setSelectionType('multiple')"
                                     class="p-4 rounded-[16px] border-2 cursor-pointer transition select-none flex flex-col justify-between space-y-3"
                                     :class="groupForm.selection_type === 'multiple' ? 'bg-[#AF52DE]/[0.04] border-[#AF52DE] dark:bg-[#AF52DE]/[0.08]' : 'bg-black/[0.015] dark:bg-white/[0.02] border-black/[0.08] dark:border-white/[0.1] hover:border-black/20'">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                                 :class="groupForm.selection_type === 'multiple' ? 'bg-[#AF52DE] text-white' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50'">
                                                <i data-lucide="check-square" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-[14px] font-bold text-black dark:text-white">Pilihan Tambahan</h4>
                                                <p class="text-[11.5px] text-black/50 dark:text-white/50">Bisa pilih banyak (Add-on)</p>
                                            </div>
                                        </div>
                                        <div class="w-5 h-5 rounded-full border flex items-center justify-center"
                                             :class="groupForm.selection_type === 'multiple' ? 'border-[#AF52DE] bg-[#AF52DE]' : 'border-black/20 dark:border-white/20'">
                                            <div class="w-2 h-2 rounded-full bg-white" x-show="groupForm.selection_type === 'multiple'"></div>
                                        </div>
                                    </div>
                                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                        Cocok untuk Extra Topping, Saus Tambahan, atau Side Dish.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: KEWAJIBAN & BATAS PEMILIHAN -->
                        <div class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                            <!-- Toggle Wajib atau Opsional -->
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-[13.5px] font-semibold text-black dark:text-white">{{ __('products.wajib_diisi_oleh_pelanggan') }}</h4>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">
                                        <span x-show="groupForm.is_required">{{ __('products.modifiers_rule_single_required_note') }}</span>
                                        <span x-show="!groupForm.is_required">{{ __('products.modifiers_rule_single_optional_note') }}</span>
                                    </p>
                                </div>
                                <button type="button" @click="toggleIsRequired()"
                                    class="w-13 h-7 min-h-[36px] min-w-[52px] rounded-full p-1 transition-colors duration-200 ease-in-out shrink-0 focus:outline-none cursor-pointer flex items-center"
                                    :class="groupForm.is_required ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20'">
                                    <div class="w-5 h-5 rounded-full bg-white shadow-sm transform transition-transform duration-200 ease-in-out"
                                         :class="groupForm.is_required ? 'translate-x-6' : 'translate-x-0'"></div>
                                </button>
                            </div>

                            <!-- Batas Pemilihan untuk Pilihan Ganda -->
                            <div x-show="groupForm.selection_type === 'multiple'" x-collapse class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    <div>
                                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[12.5px]">
                                            Minimal Pilihan (Min)
                                        </label>
                                        <input type="number" min="0" max="20" x-model.number="groupForm.min_selection"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] px-3 text-[14px] font-semibold tabular-nums text-black dark:text-white">
                                    </div>
                                    <div>
                                        <label class="block font-medium text-black/70 dark:text-white/70 mb-1 text-[12.5px]">
                                            Maksimal Pilihan yang Diizinkan (Max)
                                        </label>
                                        <input type="number" min="1" max="50" x-model.number="groupForm.max_selection"
                                            class="w-full h-10 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[10px] px-3 text-[14px] font-semibold tabular-nums text-black dark:text-white">
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 flex-wrap text-[11.5px] text-black/50 dark:text-white/50">
                                    <span>Batas Cepat:</span>
                                    <button type="button" @click="groupForm.max_selection = 2" class="px-2 py-0.5 rounded bg-black/5 dark:bg-white/5 hover:bg-black/10">Maks. 2</button>
                                    <button type="button" @click="groupForm.max_selection = 3" class="px-2 py-0.5 rounded bg-black/5 dark:bg-white/5 hover:bg-black/10">Maks. 3</button>
                                    <button type="button" @click="groupForm.max_selection = 5" class="px-2 py-0.5 rounded bg-black/5 dark:bg-white/5 hover:bg-black/10">Maks. 5</button>
                                    <button type="button" @click="groupForm.max_selection = 10" class="px-2 py-0.5 rounded bg-black/5 dark:bg-white/5 hover:bg-black/10">Maks. 10</button>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 5: BULK PRODUCT ASSIGNMENT (Interaktif & Jelas) -->
                        <div class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                                <div>
                                    <label class="block font-semibold text-black/80 dark:text-white/80 text-[13.5px]">
                                        {{ __('products.label_bulk_product_assignment') }}
                                    </label>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">
                                        {{ __('products.sub_bulk_product_assignment') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 self-start sm:self-auto shrink-0 flex-wrap">
                                    <span class="text-[11.5px] font-semibold px-2.5 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] tabular-nums">
                                        <span x-text="groupForm.product_ids.length"></span> / {{ $products->count() }} Terpilih
                                    </span>
                                    <button type="button" @click="selectAllProducts()"
                                        class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/8 hover:bg-[#007AFF]/15 transition min-h-[36px]">
                                        <span x-text="productSearch.trim() ? 'Pilih Hasil Cari' : 'Pilih Semua'"></span>
                                    </button>
                                    <button type="button" @click="clearAllProducts()"
                                        class="h-8 px-2.5 rounded-[8px] text-[12px] font-medium text-black/50 dark:text-white/50 hover:text-[#FF3B30] hover:bg-[#FF3B30]/10 transition min-h-[36px]">
                                        {{ __('products.modifiers_clear_all_selection') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Chips of Selected Products (Quick Remove Visual) -->
                            <div x-show="groupForm.product_ids.length > 0" class="p-2.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-1.5">
                                <span class="text-[11px] font-semibold text-black/40 dark:text-white/40 uppercase tracking-wider block">
                                    {{ __('products.modifiers_selected_products_chips') }}
                                </span>
                                <div class="flex items-center gap-1.5 flex-wrap max-h-24 overflow-y-auto">
                                    <template x-for="pid in groupForm.product_ids" :key="pid">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[8px] text-[11.5px] font-medium bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF]">
                                            <span x-text="getProductName(pid)"></span>
                                            <button type="button" @click="removeProduct(pid)" class="hover:text-[#FF3B30] transition">
                                                <i data-lucide="x" class="w-3 h-3"></i>
                                            </button>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Live Search Input Box -->
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                    <i data-lucide="search" class="w-4 h-4"></i>
                                </div>
                                <input type="text" x-model="productSearch"
                                    placeholder="{{ __('products.cari_nama_menu_atau_sku_contoh') }}"
                                    class="w-full h-10 pl-9 pr-8 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 placeholder:text-black/35 dark:placeholder:text-white/35 transition">
                                <button type="button" x-show="productSearch.length > 0" @click="productSearch = ''"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white transition">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>

                            <!-- Filtered Products List Box -->
                            <div class="max-h-56 overflow-y-auto p-1.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.06] dark:border-white/[0.08] space-y-0.5 text-[12.5px] divide-y divide-black/[0.03] dark:divide-white/[0.04]">
                                @forelse($products as $prod)
                                    <label x-show="isProductVisible('{{ $prod->id }}')"
                                        class="flex items-center gap-3 p-2.5 rounded-[10px] hover:bg-black/[0.03] dark:hover:bg-white/[0.04] cursor-pointer transition select-none group min-h-[44px]">
                                        <input type="checkbox" name="product_ids[]" value="{{ (string)$prod->id }}" x-model="groupForm.product_ids"
                                            class="w-4 h-4 rounded-[4px] border-black/20 dark:border-white/20 text-[#007AFF] focus:ring-[#007AFF] cursor-pointer shrink-0">
                                        <div class="min-w-0 flex-1">
                                            <div class="text-black dark:text-white font-medium truncate group-hover:text-[#007AFF] dark:group-hover:text-[#0A84FF] transition-colors">
                                                {{ $prod->name }}
                                            </div>
                                            @if($prod->sku)
                                                <div class="text-[11px] text-black/40 dark:text-white/40 font-mono">
                                                    SKU: {{ $prod->sku }}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="text-[12px] font-semibold text-black/70 dark:text-white/70 ml-auto tabular-nums shrink-0">
                                            {{ $business->currency_symbol }} {{ number_format($prod->selling_price, 0, ',', '.') }}
                                        </span>
                                    </label>
                                @empty
                                    <div class="p-6 text-center text-[12.5px] text-black/40 dark:text-white/40">
                                        {{ __('products.belum_ada_produk_aktif_yang_terdaftar') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    <!-- Sticky Footer -->
                    <div class="sticky bottom-0 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 mt-6 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 flex items-center justify-between gap-3 shrink-0">
                        <div>
                            <template x-if="showEditGroupModal">
                                <button type="button" @click="openDeleteGroup(groupForm.id, groupForm.name)"
                                    class="min-h-[44px] h-11 px-4 rounded-[12px] text-[13px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 transition active:scale-[0.98]">
                                    {{ __('products.btn_delete_group') }}
                                </button>
                            </template>
                        </div>

                        <div class="flex items-center gap-3">
                            <button type="button" @click="showAddGroupModal = false; showEditGroupModal = false;"
                                class="min-h-[44px] h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98]">
                                {{ __('products.btn_cancel') }}
                            </button>
                            <button type="submit"
                                class="min-h-[44px] h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25">
                                {{ __('products.btn_save') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @endif

    <!-- ========================================================= -->
    <!-- MODAL: TAMBAH / EDIT OPSI MODIFIER (Kalkulasi & Resep)   -->
    <!-- ========================================================= -->
    @if(\App\Support\Context::hasPermission('pos.modifiers'))
    <template x-teleport="body">
        <div x-show="showAddOptionModal || showEditOptionModal" x-cloak
             class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4 md:p-6 bg-black/60 dark:bg-black/75 backdrop-blur-md"
             @keydown.escape.window="showAddOptionModal = false; showEditOptionModal = false;">
            <div class="w-full max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl inset-x-0 bottom-0 sm:inset-auto sm:my-auto rounded-t-[28px] sm:rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] shadow-[0_25px_60px_rgba(0,0,0,0.3)] max-h-[94vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
                 @click.outside="showAddOptionModal = false; showEditOptionModal = false;">

                <!-- Mobile Grab Bar -->
                <div class="w-10 h-1.5 rounded-full bg-black/20 dark:bg-white/20 mx-auto my-2.5 sm:hidden shrink-0"></div>

                <!-- Sticky Top Header -->
                <div class="sticky top-0 z-20 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-b border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 sm:py-5 flex items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight leading-snug truncate"
                                x-text="showEditOptionModal ? '{{ __('products.modal_edit_option_title') }}' : '{{ __('products.modal_create_option_title') }}'"></h3>
                            <p class="text-[12px] sm:text-[13px] text-black/50 dark:text-white/50 truncate"
                                x-text="'Grup: ' + (selectedGroup ? selectedGroup.name : '')"></p>
                        </div>
                    </div>
                    <button type="button" @click="showAddOptionModal = false; showEditOptionModal = false;"
                        class="w-9 h-9 rounded-full bg-black/[0.05] dark:bg-white/[0.1] hover:bg-black/[0.1] text-black/60 dark:text-white/60 flex items-center justify-center active:scale-95 transition-all shrink-0 min-w-[36px]">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Form Content -->
                <form method="POST" :action="showEditOptionModal ? '{{ url('pos/modifiers/options') }}/' + optionForm.id : '{{ url('pos/modifiers/groups') }}/' + (selectedGroup ? selectedGroup.id : '') + '/options'"
                    class="flex-1 overflow-y-auto p-6 sm:p-8 overscroll-contain sidebar-scroll flex flex-col justify-between space-y-6">
                    @csrf
                    <template x-if="showEditOptionModal">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="space-y-5">
                        <!-- 1. Nama Opsi -->
                        <div>
                            <label class="block font-semibold text-black/80 dark:text-white/80 mb-1.5 text-[13px]">
                                {{ __('products.label_option_name') }} <span class="text-[#FF3B30]">*</span>
                            </label>
                            <input type="text" name="name" x-model="optionForm.name" required placeholder="{{ __('products.placeholder_option_name') }}"
                                class="w-full h-11 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] px-3.5 text-[15px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <!-- 2. Biaya Tambahan / Ekstra Harga dengan Preset Cepat -->
                        <div class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3">
                            <label class="block font-semibold text-black/80 dark:text-white/80 text-[13px]">
                                {{ __('products.label_price_delta') }} <span class="text-[#FF3B30]">*</span>
                            </label>

                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40 font-semibold text-[14px]">
                                    {{ $business->currency_symbol }}
                                </div>
                                <input type="number" name="price_delta" x-model.number="optionForm.price_delta" min="0" step="500" required
                                    class="w-full h-11 pl-12 pr-4 bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[16px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                            </div>

                            <!-- Quick Price Pills -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[11.5px] text-black/50 dark:text-white/50">Preset Cepat:</span>
                                <button type="button" @click="optionForm.price_delta = 0"
                                    class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-medium bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-black dark:text-white transition">{{ __('products.gratis_rp_0') }}</button>
                                <button type="button" @click="optionForm.price_delta = 2000"
                                    class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-medium bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-black dark:text-white transition">
                                    +Rp 2.000
                                </button>
                                <button type="button" @click="optionForm.price_delta = 3000"
                                    class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-medium bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-black dark:text-white transition">
                                    +Rp 3.000
                                </button>
                                <button type="button" @click="optionForm.price_delta = 5000"
                                    class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-medium bg-white dark:bg-[#252528] border border-black/10 dark:border-white/10 hover:border-[#007AFF] text-black dark:text-white transition">
                                    +Rp 5.000
                                </button>
                            </div>

                            <!-- Live Calculation Formula Note -->
                            <div class="text-[12px] text-black/50 dark:text-white/50 bg-[#007AFF]/5 dark:bg-[#007AFF]/10 p-2.5 rounded-[10px] flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                                <span>{{ __('products.modifiers_price_preview_formula') }}</span>
                            </div>
                        </div>

                        <!-- 3. Pemotongan Stok Bahan Baku (BOM Auto-Deduct) -->
                        <div class="p-4 sm:p-5 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-[13.5px] font-semibold text-black dark:text-white">{{ __('products.label_affects_material') }}</h4>
                                    <p class="text-[12px] text-black/50 dark:text-white/50">
                                        <span x-show="optionForm.affects_material">{{ __('products.affects_material_yes') }}</span>
                                        <span x-show="!optionForm.affects_material">{{ __('products.affects_material_no') }}</span>
                                    </p>
                                </div>
                                <button type="button" @click="toggleAffectsMaterial()"
                                    class="w-13 h-7 min-h-[36px] min-w-[52px] rounded-full p-1 transition-colors duration-200 ease-in-out shrink-0 focus:outline-none cursor-pointer flex items-center"
                                    :class="optionForm.affects_material ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20'">
                                    <div class="w-5 h-5 rounded-full bg-white shadow-sm transform transition-transform duration-200 ease-in-out"
                                         :class="optionForm.affects_material ? 'translate-x-6' : 'translate-x-0'"></div>
                                </button>
                            </div>
                            <input type="hidden" name="affects_material" :value="optionForm.affects_material ? '1' : '0'">

                            <!-- Sub-Section: Recipe Table if Affects Material is True -->
                            <div x-show="optionForm.affects_material" x-collapse class="pt-3 border-t border-black/[0.06] dark:border-white/[0.08] space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[12.5px] font-bold text-black dark:text-white flex items-center gap-1.5">
                                        <i data-lucide="boxes" class="w-4 h-4 text-[#007AFF]"></i>
                                        <span>{{ __('products.section_material_recipe_mapping') }}</span>
                                    </span>
                                    <button type="button" @click="addMaterialRow()"
                                        class="h-8 px-3 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] text-[12px] font-semibold hover:bg-[#007AFF]/20 transition flex items-center gap-1 min-h-[36px]">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        <span>{{ __('products.btn_add_material') }}</span>
                                    </button>
                                </div>

                                <div class="space-y-2.5">
                                    <template x-for="(matRow, idx) in optionForm.materials" :key="idx">
                                        <div class="flex items-center gap-2">
                                            <!-- Material Select -->
                                            <select :name="'materials[' + idx + '][material_id]'" x-model="matRow.material_id" required
                                                class="flex-1 h-10 px-3 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                                <option value="">{{ __('products.placeholder_select_material_option') }}</option>
                                                @foreach($materials as $mat)
                                                    <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit?->name ?? 'Unit' }})</option>
                                                @endforeach
                                            </select>

                                            <!-- Quantity Input -->
                                            <input type="number" :name="'materials[' + idx + '][quantity]'" x-model.number="matRow.quantity" step="0.0001" min="0.0001" placeholder="{{ __('products.placeholder_quantity') }}" required
                                                class="w-24 h-10 px-3 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[13px] font-semibold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">

                                            <!-- Unit Select -->
                                            <select :name="'materials[' + idx + '][unit_id]'" x-model="matRow.unit_id"
                                                class="w-24 h-10 px-2 rounded-[10px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50">
                                                <option value="">{{ __('products.placeholder_unit') }}</option>
                                                @foreach($units as $u)
                                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </select>

                                            <!-- Remove Row -->
                                            <button type="button" @click="removeMaterialRow(idx)"
                                                class="w-9 h-9 min-h-[36px] min-w-[36px] rounded-[10px] text-[#FF3B30] hover:bg-[#FF3B30]/10 flex items-center justify-center shrink-0 transition" title="{{ __('products.hapus_baris') }}">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </template>

                                    <div x-show="optionForm.materials.length === 0" class="text-center py-4 text-[12.5px] text-black/40 dark:text-white/40 bg-white dark:bg-[#1C1C1E] rounded-[12px] border border-dashed border-black/10 dark:border-white/10">
                                        {{ __('products.empty_option_materials') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Status Aktif / Nonaktif -->
                        <div class="flex items-center justify-between p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <div>
                                <h4 class="text-[13.5px] font-semibold text-black dark:text-white">{{ __('products.status_opsi') }}</h4>
                                <p class="text-[12px] text-black/50 dark:text-white/50">
                                    <span x-show="optionForm.is_active">{{ __('products.status_active') }} (Muncul di layar kasir)</span>
                                    <span x-show="!optionForm.is_active">{{ __('products.status_inactive') }} (Disembunyikan dari kasir)</span>
                                </p>
                            </div>
                            <button type="button" @click="optionForm.is_active = !optionForm.is_active"
                                class="w-13 h-7 min-h-[36px] min-w-[52px] rounded-full p-1 transition-colors duration-200 ease-in-out shrink-0 focus:outline-none cursor-pointer flex items-center"
                                :class="optionForm.is_active ? 'bg-[#34C759]' : 'bg-black/20 dark:bg-white/20'">
                                <div class="w-5 h-5 rounded-full bg-white shadow-sm transform transition-transform duration-200 ease-in-out"
                                     :class="optionForm.is_active ? 'translate-x-6' : 'translate-x-0'"></div>
                            </button>
                            <input type="hidden" name="is_active" :value="optionForm.is_active ? '1' : '0'">
                        </div>

                    </div>

                    <!-- Sticky Footer -->
                    <div class="sticky bottom-0 -mx-6 sm:-mx-8 -mb-6 sm:-mb-8 mt-6 backdrop-blur-xl bg-white/95 dark:bg-[#1C1C1E]/95 border-t border-black/[0.06] dark:border-white/[0.08] px-6 sm:px-8 py-4 flex items-center justify-end gap-3 shrink-0">
                        <button type="button" @click="showAddOptionModal = false; showEditOptionModal = false;"
                            class="min-h-[44px] h-11 px-5 rounded-[12px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition active:scale-[0.98]">
                            {{ __('products.btn_cancel') }}
                        </button>
                        <button type="submit"
                            class="min-h-[44px] h-11 px-6 rounded-[12px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition shadow-sm shadow-[#007AFF]/25">
                            {{ __('products.btn_save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
    @endif

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG (Hapus Grup Modifier - Teleport)    -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="deleteGroupModalOpen" x-cloak class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
            <div class="w-full max-w-[340px] rounded-[22px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
                @click.outside="closeDeleteGroup()">
                <div class="px-6 pt-6 pb-4">
                    <div class="w-12 h-12 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3.5">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('products.modal_delete_group_title') }}</h4>
                    <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                        Grup <span x-text="deleteGroupTarget.name" class="font-semibold text-black dark:text-white"></span> {{ __('products.modal_delete_group_desc') }}
                    </p>
                    <div class="mt-3 p-2.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] text-[11px] text-black/50 dark:text-white/50 text-left flex items-start gap-1.5">
                        <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0 mt-0.5"></i>
                        <span>{{ __('products.peace_of_mind_delete_group') }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.1] text-[15px] font-medium">
                    <button type="button" @click="closeDeleteGroup()" class="py-3 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.1] active:bg-black/5 dark:active:bg-white/5 transition-colors min-h-[44px]">
                        {{ __('products.btn_cancel') }}
                    </button>
                    <button type="button" @click="submitDeleteGroup()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors min-h-[44px]">
                        {{ __('products.btn_delete') }}
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- ===================================================== -->
    <!-- APPLE ALERT DIALOG (Hapus Opsi Modifier - Teleport)    -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="deleteOptionModalOpen" x-cloak class="fixed inset-0 z-[220] flex items-center justify-center p-4 bg-black/60 dark:bg-black/75 backdrop-blur-md">
            <div class="w-full max-w-[340px] rounded-[22px] bg-white/95 dark:bg-[#2C2C2E]/95 backdrop-blur-2xl overflow-hidden text-center shadow-2xl border border-black/[0.08] dark:border-white/[0.1]"
                @click.outside="closeDeleteOption()">
                <div class="px-6 pt-6 pb-4">
                    <div class="w-12 h-12 rounded-full bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center mx-auto mb-3.5">
                        <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-[17px] font-bold text-black dark:text-white tracking-tight">{{ __('products.modal_delete_option_title') }}</h4>
                    <p class="text-[13px] text-black/60 dark:text-white/60 mt-1.5 leading-snug">
                        Opsi <span x-text="deleteOptionTarget.name" class="font-semibold text-black dark:text-white"></span> {{ __('products.modal_delete_option_desc') }}
                    </p>
                </div>
                <div class="grid grid-cols-2 border-t border-black/[0.08] dark:border-white/[0.1] text-[15px] font-medium">
                    <button type="button" @click="closeDeleteOption()" class="py-3 text-[#007AFF] border-r border-black/[0.08] dark:border-white/[0.1] active:bg-black/5 dark:active:bg-white/5 transition-colors min-h-[44px]">
                        {{ __('products.btn_cancel') }}
                    </button>
                    <button type="button" @click="submitDeleteOption()" class="py-3 text-[#FF3B30] font-semibold active:bg-black/5 dark:active:bg-white/5 transition-colors min-h-[44px]">
                        {{ __('products.btn_delete') }}
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

@push('scripts')
<script>
    function modifierManager() {
        return {
            productsMap: @json($products->mapWithKeys(fn($p) => [(string)$p->id => strtolower($p->name . ' ' . ($p->sku ?? ''))])),
            productsNameMap: @json($products->mapWithKeys(fn($p) => [(string)$p->id => $p->name])),
            productSearch: '',
            showGuide: localStorage.getItem('cooca_modifier_guide_closed') !== 'true',
            showAddGroupModal: false,
            showEditGroupModal: false,
            showAddOptionModal: false,
            showEditOptionModal: false,
            selectedGroup: null,
            selectedOption: null,
            deleteGroupModalOpen: false,
            deleteGroupTarget: { id: '', name: '' },
            deleteOptionModalOpen: false,
            deleteOptionTarget: { id: '', name: '' },
            groupForm: {
                id: '',
                name: '',
                description: '',
                selection_type: 'single',
                min_selection: 1,
                max_selection: 1,
                is_required: true,
                is_active: true,
                sort_order: 0,
                product_ids: []
            },
            optionForm: {
                id: '',
                group_id: '',
                name: '',
                price_delta: 0,
                affects_material: false,
                sort_order: 0,
                is_active: true,
                materials: []
            },

            toggleGuide() {
                this.showGuide = !this.showGuide;
                localStorage.setItem('cooca_modifier_guide_closed', this.showGuide ? 'false' : 'true');
            },

            getProductName(id) {
                return this.productsNameMap[id] || 'Produk #' + id;
            },

            removeProduct(id) {
                this.groupForm.product_ids = this.groupForm.product_ids.filter(p => String(p) !== String(id));
            },

            setSelectionType(type) {
                this.groupForm.selection_type = type;
                if (type === 'single') {
                    this.groupForm.max_selection = 1;
                    this.groupForm.min_selection = this.groupForm.is_required ? 1 : 0;
                } else {
                    if (this.groupForm.max_selection < 2) {
                        this.groupForm.max_selection = 5;
                    }
                    this.groupForm.min_selection = this.groupForm.is_required ? 1 : 0;
                }
            },

            toggleIsRequired() {
                this.groupForm.is_required = !this.groupForm.is_required;
                if (this.groupForm.selection_type === 'single') {
                    this.groupForm.min_selection = this.groupForm.is_required ? 1 : 0;
                    this.groupForm.max_selection = 1;
                } else {
                    this.groupForm.min_selection = this.groupForm.is_required ? 1 : 0;
                }
            },

            applyPreset(preset) {
                if (preset === 'required_single') {
                    this.groupForm.selection_type = 'single';
                    this.groupForm.is_required = true;
                    this.groupForm.min_selection = 1;
                    this.groupForm.max_selection = 1;
                } else if (preset === 'optional_multi') {
                    this.groupForm.selection_type = 'multiple';
                    this.groupForm.is_required = false;
                    this.groupForm.min_selection = 0;
                    this.groupForm.max_selection = 5;
                } else if (preset === 'optional_single') {
                    this.groupForm.selection_type = 'single';
                    this.groupForm.is_required = false;
                    this.groupForm.min_selection = 0;
                    this.groupForm.max_selection = 1;
                }
            },

            isProductVisible(id) {
                if (!this.productSearch || !this.productSearch.trim()) return true;
                const q = this.productSearch.toLowerCase().trim();
                const searchStr = this.productsMap[id];
                return searchStr ? searchStr.includes(q) : true;
            },

            selectAllProducts() {
                if (this.productSearch && this.productSearch.trim()) {
                    const q = this.productSearch.toLowerCase().trim();
                    const matched = Object.entries(this.productsMap)
                        .filter(([id, searchStr]) => searchStr.includes(q))
                        .map(([id]) => id);
                    this.groupForm.product_ids = [...new Set([...this.groupForm.product_ids, ...matched])];
                } else {
                    this.groupForm.product_ids = Object.keys(this.productsMap);
                }
            },

            clearAllProducts() {
                if (this.productSearch && this.productSearch.trim()) {
                    const q = this.productSearch.toLowerCase().trim();
                    const matchedSet = new Set(
                        Object.entries(this.productsMap)
                            .filter(([id, searchStr]) => searchStr.includes(q))
                            .map(([id]) => id)
                    );
                    this.groupForm.product_ids = this.groupForm.product_ids.filter(id => !matchedSet.has(id));
                } else {
                    this.groupForm.product_ids = [];
                }
            },

            toggleAffectsMaterial() {
                this.optionForm.affects_material = !this.optionForm.affects_material;
                if (this.optionForm.affects_material && this.optionForm.materials.length === 0) {
                    this.addMaterialRow();
                } else if (!this.optionForm.affects_material) {
                    this.optionForm.materials = [];
                }
            },

            openAddGroup() {
                this.productSearch = '';
                this.selectedGroup = null;
                this.groupForm = {
                    id: '',
                    name: '',
                    description: '',
                    selection_type: 'single',
                    min_selection: 1,
                    max_selection: 1,
                    is_required: true,
                    is_active: true,
                    sort_order: 0,
                    product_ids: []
                };
                this.showAddGroupModal = true;
                this.refreshIcons();
            },

            openEditGroup(group) {
                this.productSearch = '';
                this.selectedGroup = group;
                this.groupForm = {
                    id: group.id,
                    name: group.name,
                    description: group.description || '',
                    selection_type: group.selection_type || 'single',
                    min_selection: Number(group.min_selection || 0),
                    max_selection: Number(group.max_selection || 1),
                    is_required: Boolean(group.is_required),
                    is_active: group.is_active !== undefined ? Boolean(group.is_active) : true,
                    sort_order: Number(group.sort_order || 0),
                    product_ids: (group.products || []).map(p => String(p.id))
                };
                this.showEditGroupModal = true;
                this.refreshIcons();
            },

            openAddOption(group) {
                this.selectedGroup = group;
                this.selectedOption = null;
                this.optionForm = {
                    id: '',
                    group_id: group.id,
                    name: '',
                    price_delta: 0,
                    affects_material: false,
                    sort_order: 0,
                    is_active: true,
                    materials: []
                };
                this.showAddOptionModal = true;
                this.refreshIcons();
            },

            openEditOption(group, opt) {
                this.selectedGroup = group;
                this.selectedOption = opt;
                this.optionForm = {
                    id: opt.id,
                    group_id: group.id,
                    name: opt.name,
                    price_delta: Number(opt.price_delta || 0),
                    affects_material: Boolean(opt.affects_material),
                    sort_order: Number(opt.sort_order || 0),
                    is_active: Boolean(opt.is_active),
                    materials: (opt.materials || []).map(m => ({
                        material_id: m.material_id,
                        quantity: Number(m.quantity || 1),
                        unit_id: m.unit_id || ''
                    }))
                };
                this.showEditOptionModal = true;
                this.refreshIcons();
            },

            addMaterialRow() {
                this.optionForm.materials.push({
                    material_id: '',
                    quantity: 1,
                    unit_id: ''
                });
                this.refreshIcons();
            },

            removeMaterialRow(index) {
                this.optionForm.materials.splice(index, 1);
            },

            openDeleteGroup(id, name) {
                this.deleteGroupTarget = { id: id, name: name };
                this.deleteGroupModalOpen = true;
                this.refreshIcons();
            },

            closeDeleteGroup() {
                this.deleteGroupModalOpen = false;
                this.deleteGroupTarget = { id: '', name: '' };
            },

            submitDeleteGroup() {
                if (this.deleteGroupTarget.id) {
                    const form = document.getElementById('form-delete-group-' + this.deleteGroupTarget.id);
                    if (form) form.submit();
                }
            },

            openDeleteOption(id, name) {
                this.deleteOptionTarget = { id: id, name: name };
                this.deleteOptionModalOpen = true;
                this.refreshIcons();
            },

            closeDeleteOption() {
                this.deleteOptionModalOpen = false;
                this.deleteOptionTarget = { id: '', name: '' };
            },

            submitDeleteOption() {
                if (this.deleteOptionTarget.id) {
                    const form = document.getElementById('form-delete-option-' + this.deleteOptionTarget.id);
                    if (form) form.submit();
                }
            },

            refreshIcons() {
                this.$nextTick(() => {
                    if (window.lucide && typeof window.lucide.createIcons === 'function') {
                        window.lucide.createIcons();
                    }
                });
            }
        };
    }

    if (window.Alpine) {
        Alpine.data('modifierManager', modifierManager);
    } else {
        document.addEventListener('alpine:init', () => {
            Alpine.data('modifierManager', modifierManager);
        });
    }
</script>
@endpush
@endsection
