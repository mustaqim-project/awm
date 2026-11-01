import os, re

root = r'c:\laragon\www\cooca_core'

# 1. sales-orders/index.blade.php
f_so = os.path.join(root, r'resources\views\app\sales-orders\index.blade.php')
c_so = open(f_so, 'r', encoding='utf-8').read()

target_so = """                                <td class="py-3 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('sales.orders.pdf', $so) }}" target="_blank"
                                            class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('common.download_pdf') }}"
                                            aria-label="{{ __('sales.unduh_pdf') }}">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>
                                        @if (\\App\\Support\\Context::hasPermission('sales.pipeline') || \\App\\Support\\Context::hasPermission('sales.view'))
                                        <a href="{{ route('sales.orders.edit', $so) }}"
                                            class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('sales.edit_pesanan_2') }}"
                                            aria-label="{{ __('sales.edit_pesanan_3') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('sales.orders.show', $so) }}"
                                            class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('sales.sales_orders.btn_detail') }}"
                                            aria-label="{{ __('sales.sales_orders.btn_detail') }}">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        @if ($so->status === 'draft' && \\App\\Support\\Context::hasPermission('sales.pipeline'))
                                        <button type="button"
                                            @click="openDelete('{{ route('sales.orders.destroy', $so) }}', '{{ $so->so_number }}')"
                                            class="w-8 h-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('sales.hapus_pesanan_penjualan') }}"
                                            aria-label="{{ __('sales.hapus_pesanan_penjualan_2') }}">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>"""

repl_so = """                                <td class="py-3 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('sales.orders.show', $so) }}"
                                            class="inline-flex items-center min-h-[44px] sm:min-h-0 h-9 px-3 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-xs font-semibold text-[#007AFF] transition-colors"
                                            title="{{ __('sales.sales_orders.btn_detail') }}">
                                            <i data-lucide="eye" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                                            <span>{{ __('sales.sales_orders.btn_detail') }}</span>
                                        </a>

                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button type="button" 
                                                    @click="open = !open" 
                                                    @click.outside="open = false"
                                                    aria-label="{{ __('common.more_actions') }}"
                                                    class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 w-9 h-9 rounded-[10px] flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors cursor-pointer">
                                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                            </button>

                                            <div x-show="open"
                                                 x-cloak
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="transform opacity-0 scale-95"
                                                 x-transition:enter-end="transform opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="transform opacity-100 scale-100"
                                                 x-transition:leave-end="transform opacity-0 scale-95"
                                                 class="absolute right-0 mt-1.5 w-48 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-2xl py-1.5 z-30 text-left">
                                                <a href="{{ route('sales.orders.pdf', $so) }}" target="_blank"
                                                   class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                                                    <i data-lucide="printer" class="w-4 h-4 text-gray-400"></i>
                                                    <span>{{ __('common.download_pdf') }}</span>
                                                </a>
                                                @if (\\App\\Support\\Context::hasPermission('sales.pipeline') || \\App\\Support\\Context::hasPermission('sales.view'))
                                                <a href="{{ route('sales.orders.edit', $so) }}"
                                                   class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                                                    <i data-lucide="pencil" class="w-4 h-4 text-gray-400"></i>
                                                    <span>{{ __('sales.edit_pesanan_2') }}</span>
                                                </a>
                                                @endif
                                                @if ($so->status === 'draft' && \\App\\Support\\Context::hasPermission('sales.pipeline'))
                                                <div class="h-px bg-black/5 dark:bg-white/10 my-1"></div>
                                                <button type="button"
                                                        @click="open = false; openDelete('{{ route('sales.orders.destroy', $so) }}', '{{ $so->so_number }}')"
                                                        class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors text-left cursor-pointer">
                                                    <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                                                    <span>{{ __('sales.hapus_pesanan_penjualan') }}</span>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>"""

if target_so in c_so:
    c_so = c_so.replace(target_so, repl_so)
    open(f_so, 'w', encoding='utf-8').write(c_so)
    print("[OK] sales-orders/index.blade.php updated")
else:
    print("[FAIL] sales-orders/index.blade.php target not found")

# 2. sales/returns/index.blade.php
f_ret = os.path.join(root, r'resources\views\app\sales\returns\index.blade.php')
c_ret = open(f_ret, 'r', encoding='utf-8').read()

target_ret = """                                <td class="py-3 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('sales.returns.pdf', $return) }}" target="_blank"
                                            class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('common.download_pdf') }}"
                                            aria-label="{{ __('sales.unduh_pdf') }}">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </a>
                                        @if ($return->status === 'draft' && (\\App\\Support\\Context::hasPermission('sales.returns') || \\App\\Support\\Context::hasPermission('sales.pipeline')))
                                        <a href="{{ route('sales.returns.edit', $return) }}"
                                            class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('sales.edit_retur_2') }}"
                                            aria-label="{{ __('sales.edit_retur_3') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('sales.returns.show', $return) }}"
                                            class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('sales.returns.btn_detail') }}"
                                            aria-label="{{ __('sales.returns.btn_detail') }}">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        @if ($return->status === 'draft' && (\\App\\Support\\Context::hasPermission('sales.returns') || \\App\\Support\\Context::hasPermission('sales.pipeline')))
                                        <button type="button"
                                            @click="openDelete('{{ route('sales.returns.destroy', $return) }}', '{{ $return->return_number }}')"
                                            class="w-8 h-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                            title="{{ __('sales.hapus_retur_penjualan') }}"
                                            aria-label="{{ __('sales.hapus_retur_penjualan_2') }}">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>"""

repl_ret = """                                <td class="py-3 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('sales.returns.show', $return) }}"
                                            class="inline-flex items-center min-h-[44px] sm:min-h-0 h-9 px-3 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-xs font-semibold text-[#007AFF] transition-colors"
                                            title="{{ __('sales.returns.btn_detail') }}">
                                            <i data-lucide="eye" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                                            <span>{{ __('sales.returns.btn_detail') }}</span>
                                        </a>

                                        <div x-data="{ open: false }" class="relative inline-block text-left">
                                            <button type="button" 
                                                    @click="open = !open" 
                                                    @click.outside="open = false"
                                                    aria-label="{{ __('common.more_actions') }}"
                                                    class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 w-9 h-9 rounded-[10px] flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors cursor-pointer">
                                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                            </button>

                                            <div x-show="open"
                                                 x-cloak
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="transform opacity-0 scale-95"
                                                 x-transition:enter-end="transform opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="transform opacity-100 scale-100"
                                                 x-transition:leave-end="transform opacity-0 scale-95"
                                                 class="absolute right-0 mt-1.5 w-48 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-2xl py-1.5 z-30 text-left">
                                                <a href="{{ route('sales.returns.pdf', $return) }}" target="_blank"
                                                   class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                                                    <i data-lucide="printer" class="w-4 h-4 text-gray-400"></i>
                                                    <span>{{ __('common.download_pdf') }}</span>
                                                </a>
                                                @if ($return->status === 'draft' && (\\App\\Support\\Context::hasPermission('sales.returns') || \\App\\Support\\Context::hasPermission('sales.pipeline')))
                                                <a href="{{ route('sales.returns.edit', $return) }}"
                                                   class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                                                    <i data-lucide="pencil" class="w-4 h-4 text-gray-400"></i>
                                                    <span>{{ __('sales.edit_retur_2') }}</span>
                                                </a>
                                                <div class="h-px bg-black/5 dark:bg-white/10 my-1"></div>
                                                <button type="button"
                                                        @click="open = false; openDelete('{{ route('sales.returns.destroy', $return) }}', '{{ $return->return_number }}')"
                                                        class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors text-left cursor-pointer">
                                                    <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                                                    <span>{{ __('sales.hapus_retur_penjualan') }}</span>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>"""

if target_ret in c_ret:
    c_ret = c_ret.replace(target_ret, repl_ret)
    open(f_ret, 'w', encoding='utf-8').write(c_ret)
    print("[OK] sales/returns/index.blade.php updated")
else:
    print("[FAIL] sales/returns/index.blade.php target not found")

# 3. quotations/index.blade.php
f_q = os.path.join(root, r'resources\views\app\quotations\index.blade.php')
c_q = open(f_q, 'r', encoding='utf-8').read()

target_q = """                        <td class="px-4 py-3 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('sales.quotations.pdf', $q) }}" target="_blank"
                                    class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                    title="{{ __('common.download_pdf') }}"
                                    aria-label="{{ __('sales.unduh_pdf') }}">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>
                                @if (\\App\\Support\\Context::hasPermission('sales.pipeline') || \\App\\Support\\Context::hasPermission('sales.view'))
                                <a href="{{ route('sales.quotations.edit', $q) }}"
                                    class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                    title="{{ __('sales.edit_penawaran_2') }}"
                                    aria-label="{{ __('sales.edit_penawaran_3') }}">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                @endif
                                <a href="{{ route('sales.quotations.show', $q) }}"
                                    class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                    title="{{ __('sales.quotations.btn_detail') }}"
                                    aria-label="{{ __('sales.quotations.btn_detail') }}">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @if($q->status === 'draft' && \\App\\Support\\Context::hasPermission('sales.pipeline'))
                                <button type="button" @click="openDelete('{{ route('sales.quotations.destroy', $q) }}', '{{ addslashes($q->quotation_number) }}')"
                                    class="w-8 h-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                    title="{{ __('admin.hapus_penawaran') }}"
                                    aria-label="{{ __('sales.hapus_penawaran') }}">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                                @endif
                            </div>
                        </td>"""

repl_q = """                        <td class="px-4 py-3 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('sales.quotations.show', $q) }}"
                                    class="inline-flex items-center min-h-[44px] sm:min-h-0 h-9 px-3 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-xs font-semibold text-[#007AFF] transition-colors"
                                    title="{{ __('sales.quotations.btn_detail') }}">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                                    <span>{{ __('sales.quotations.btn_detail') }}</span>
                                </a>

                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    <button type="button" 
                                            @click="open = !open" 
                                            @click.outside="open = false"
                                            aria-label="{{ __('common.more_actions') }}"
                                            class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 w-9 h-9 rounded-[10px] flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors cursor-pointer">
                                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                    </button>

                                    <div x-show="open"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute right-0 mt-1.5 w-48 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-2xl py-1.5 z-30 text-left">
                                        <a href="{{ route('sales.quotations.pdf', $q) }}" target="_blank"
                                           class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                                            <i data-lucide="printer" class="w-4 h-4 text-gray-400"></i>
                                            <span>{{ __('common.download_pdf') }}</span>
                                        </a>
                                        @if (\\App\\Support\\Context::hasPermission('sales.pipeline') || \\App\\Support\\Context::hasPermission('sales.view'))
                                        <a href="{{ route('sales.quotations.edit', $q) }}"
                                           class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                                            <i data-lucide="pencil" class="w-4 h-4 text-gray-400"></i>
                                            <span>{{ __('sales.edit_penawaran_2') }}</span>
                                        </a>
                                        @endif
                                        @if($q->status === 'draft' && \\App\\Support\\Context::hasPermission('sales.pipeline'))
                                        <div class="h-px bg-black/5 dark:bg-white/10 my-1"></div>
                                        <button type="button" @click="open = false; openDelete('{{ route('sales.quotations.destroy', $q) }}', '{{ addslashes($q->quotation_number) }}')"
                                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors text-left cursor-pointer">
                                            <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                                            <span>{{ __('admin.hapus_penawaran') }}</span>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>"""

if target_q in c_q:
    c_q = c_q.replace(target_q, repl_q)
    open(f_q, 'w', encoding='utf-8').write(c_q)
    print("[OK] quotations/index.blade.php updated")
else:
    print("[FAIL] quotations/index.blade.php target not found")

# 4. invoices/index.blade.php
f_inv = os.path.join(root, r'resources\views\app\invoices\index.blade.php')
c_inv = open(f_inv, 'r', encoding='utf-8').read()

target_inv = """                        <td class="py-3 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('invoices.pdf', $inv->id) }}" target="_blank"
                                    class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                    title="{{ __('sales.invoices.btn_download_pdf') }}"
                                    aria-label="{{ __('sales.invoices.btn_download_pdf') }}">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </a>
                                @if(!in_array($inv->status, ['paid', 'void'], true) && (float) $inv->paid_amount <= 0 && (\\App\\Support\\Context::hasPermission('invoices.edit') || \\App\\Support\\Context::hasPermission('invoices.create')))
                                <a href="{{ route('invoices.edit', $inv->id) }}"
                                    class="w-8 h-8 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/70 dark:text-white/70 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                    title="{{ __('sales.invoices.btn_edit_invoice') }}"
                                    aria-label="{{ __('sales.invoices.btn_edit_invoice') }}">
                                    <i data-lucide="pencil" class="w-4 h-4"></i>
                                </a>
                                @endif
                                <a href="{{ route('invoices.show', $inv->id) }}"
                                    class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                    title="{{ __('sales.invoices.btn_detail') }}"
                                    aria-label="{{ __('sales.invoices.btn_detail') }}">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @if($inv->status === 'draft' && \\App\\Support\\Context::hasPermission('invoices.delete'))
                                    <button type="button" @click="openDelete('{{ route('invoices.destroy', $inv->id) }}', '{{ addslashes($inv->invoice_number) }}')"
                                        class="w-8 h-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                        title="{{ __('sales.invoices.btn_delete') }}"
                                        aria-label="{{ __('sales.invoices.btn_delete') }}">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </div>
                        </td>"""

repl_inv = """                        <td class="py-3 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('invoices.show', $inv->id) }}"
                                    class="inline-flex items-center min-h-[44px] sm:min-h-0 h-9 px-3 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-xs font-semibold text-[#007AFF] transition-colors"
                                    title="{{ __('sales.invoices.btn_detail') }}">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                                    <span>{{ __('sales.invoices.btn_detail') }}</span>
                                </a>

                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    <button type="button" 
                                            @click="open = !open" 
                                            @click.outside="open = false"
                                            aria-label="{{ __('common.more_actions') }}"
                                            class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 w-9 h-9 rounded-[10px] flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors cursor-pointer">
                                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                    </button>

                                    <div x-show="open"
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute right-0 mt-1.5 w-48 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-2xl py-1.5 z-30 text-left">
                                        <a href="{{ route('invoices.pdf', $inv->id) }}" target="_blank"
                                           class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                                            <i data-lucide="printer" class="w-4 h-4 text-gray-400"></i>
                                            <span>{{ __('sales.invoices.btn_download_pdf') }}</span>
                                        </a>
                                        @if(!in_array($inv->status, ['paid', 'void'], true) && (float) $inv->paid_amount <= 0 && (\\App\\Support\\Context::hasPermission('invoices.edit') || \\App\\Support\\Context::hasPermission('invoices.create')))
                                        <a href="{{ route('invoices.edit', $inv->id) }}"
                                           class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors">
                                            <i data-lucide="pencil" class="w-4 h-4 text-gray-400"></i>
                                            <span>{{ __('sales.invoices.btn_edit_invoice') }}</span>
                                        </a>
                                        @endif
                                        @if($inv->status === 'draft' && \\App\\Support\\Context::hasPermission('invoices.delete'))
                                        <div class="h-px bg-black/5 dark:bg-white/10 my-1"></div>
                                        <button type="button" @click="open = false; openDelete('{{ route('invoices.destroy', $inv->id) }}', '{{ addslashes($inv->invoice_number) }}')"
                                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors text-left cursor-pointer">
                                            <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                                            <span>{{ __('sales.invoices.btn_delete') }}</span>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>"""

if target_inv in c_inv:
    c_inv = c_inv.replace(target_inv, repl_inv)
    open(f_inv, 'w', encoding='utf-8').write(c_inv)
    print("[OK] invoices/index.blade.php updated")
else:
    print("[FAIL] invoices/index.blade.php target not found")

# 5. materials/index.blade.php
f_mat = os.path.join(root, r'resources\views\app\materials\index.blade.php')
c_mat = open(f_mat, 'r', encoding='utf-8').read()

target_mat = """                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-1">
                                        @if (\\App\\Support\\Context::hasPermission('materials.edit'))
                                            <button type="button" @click="openPriceModal({{ Js::from($mat) }})"
                                                class="w-8 h-8 rounded-[8px] text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                                title="{{ __('materials.btn_update_price') }}"
                                                aria-label="{{ __('materials.btn_update_price') }}">
                                                <i data-lucide="badge-dollar-sign" class="w-4 h-4"></i>
                                            </button>
                                            <button type="button" @click="openEditModal({{ Js::from($mat) }})"
                                                class="w-8 h-8 rounded-[8px] text-black/70 dark:text-white/70 bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                                title="{{ __('materials.title_edit_spec') }}"
                                                aria-label="{{ __('materials.title_edit_spec') }}">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                        @if (\\App\\Support\\Context::hasPermission('materials.delete'))
                                            <button type="button"
                                                @click="openDelete('{{ $mat->id }}', {{ Js::from($mat->name) }})"
                                                class="w-8 h-8 rounded-[8px] text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 transition-colors flex items-center justify-center shrink-0 cursor-pointer"
                                                title="{{ __('materials.title_delete_material') }}"
                                                aria-label="{{ __('materials.title_delete_material') }}">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                            <form id="form-delete-{{ $mat->id }}" method="POST"
                                                action="{{ route('materials.destroy', $mat->id) }}" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                    </div>
                                </td>"""

repl_mat = """                                <td class="py-3.5 px-4 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">
                                    <div class="flex items-center justify-end gap-2">
                                        @if (\\App\\Support\\Context::hasPermission('materials.edit'))
                                            <button type="button" @click="openPriceModal({{ Js::from($mat) }})"
                                                class="inline-flex items-center min-h-[44px] sm:min-h-0 h-9 px-3 rounded-[10px] bg-[#007AFF]/10 hover:bg-[#007AFF]/20 text-xs font-semibold text-[#007AFF] transition-colors cursor-pointer"
                                                title="{{ __('materials.btn_update_price') }}">
                                                <i data-lucide="badge-dollar-sign" class="w-3.5 h-3.5 mr-1.5 shrink-0"></i>
                                                <span>{{ __('materials.btn_update_price') }}</span>
                                            </button>

                                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                                <button type="button" 
                                                        @click="open = !open" 
                                                        @click.outside="open = false"
                                                        aria-label="{{ __('common.more_actions') }}"
                                                        class="min-h-[44px] min-w-[44px] sm:min-h-0 sm:min-w-0 w-9 h-9 rounded-[10px] flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors cursor-pointer">
                                                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                                </button>

                                                <div x-show="open"
                                                     x-cloak
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="transform opacity-100 scale-100"
                                                     x-transition:leave-end="transform opacity-0 scale-95"
                                                     class="absolute right-0 mt-1.5 w-48 rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 shadow-2xl py-1.5 z-30 text-left">
                                                    <button type="button" @click="open = false; openEditModal({{ Js::from($mat) }})"
                                                       class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors text-left cursor-pointer">
                                                        <i data-lucide="pencil" class="w-4 h-4 text-gray-400"></i>
                                                        <span>{{ __('materials.title_edit_spec') }}</span>
                                                    </button>
                                                    @if (\\App\\Support\\Context::hasPermission('materials.delete'))
                                                    <div class="h-px bg-black/5 dark:bg-white/10 my-1"></div>
                                                    <button type="button"
                                                            @click="open = false; openDelete('{{ $mat->id }}', {{ Js::from($mat->name) }})"
                                                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors text-left cursor-pointer">
                                                        <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                                                        <span>{{ __('materials.title_delete_material') }}</span>
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                        @if (\\App\\Support\\Context::hasPermission('materials.delete'))
                                            <form id="form-delete-{{ $mat->id }}" method="POST"
                                                action="{{ route('materials.destroy', $mat->id) }}" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                    </div>
                                </td>"""

if target_mat in c_mat:
    c_mat = c_mat.replace(target_mat, repl_mat)
    open(f_mat, 'w', encoding='utf-8').write(c_mat)
    print("[OK] materials/index.blade.php updated")
else:
    print("[FAIL] materials/index.blade.php target not found")
