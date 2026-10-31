{{-- TAB 6: BAGAN AKUN STANDAR (PLATFORM CHART OF ACCOUNTS) --}}
<div x-show="activeTab === 'coa'" class="space-y-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.bagan_akun_perkiraan_chart_of_accounts') }}</h2>
            <p class="text-[12.5px] text-black/50 dark:text-white/50">
                Struktur klasifikasi kode akun pembukuan resmi platform Cooca untuk pengelompokan debit dan kredit.
            </p>
        </div>
        @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
            <button type="button" @click="openCreateAccount()"
                class="h-9.5 px-4 rounded-[12px] text-[12.5px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>{{ __('admin.tambah_akun_baru') }}</span>
            </button>
        @endif
    </div>

    @foreach(['asset', 'liability', 'equity', 'revenue', 'expense'] as $typeKey)
        @php
            $groupList = $accountsGrouped->get($typeKey, collect());
            $typeTitle = \App\Models\PlatformChartOfAccount::typeLabels()[$typeKey] ?? ucfirst($typeKey);
            $typeColor = match($typeKey) {
                'asset' => 'text-[#007AFF]',
                'liability' => 'text-[#FF9500]',
                'equity' => 'text-[#AF52DE]',
                'revenue' => 'text-[#34C759]',
                'expense' => 'text-[#FF3B30]',
                default => 'text-black dark:text-white'
            };
        @endphp

        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
            <div class="px-5 py-3.5 bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/5 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ str_replace('text-', 'bg-', $typeColor) }}"></span>
                    <h3 class="text-[13px] font-bold uppercase tracking-wider text-black dark:text-white">{{ $typeTitle }}</h3>
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-black/[0.05] dark:bg-white/[0.07] font-mono font-bold text-black/60 dark:text-white/60">
                        {{ $groupList->count() }} Akun
                    </span>
                </div>
                <span class="text-[11px] font-medium text-black/40 dark:text-white/40">
                    Posisi Normal Standar: {{ strtoupper(\App\Models\PlatformChartOfAccount::defaultNormalBalance($typeKey)) }}
                </span>
            </div>

            <div class="divide-y divide-black/5 dark:divide-white/5">
                @forelse($groupList as $acc)
                    <div class="px-5 py-3.5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                        <div class="flex items-start sm:items-center gap-3">
                            <span class="font-mono text-[13px] font-bold text-black dark:text-white min-w-[55px]">
                                {{ $acc->code }}
                            </span>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[13px] font-semibold text-black dark:text-white">{{ $acc->name }}</span>
                                    @if($acc->is_system)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-[#007AFF]/12 text-[#007AFF]">Sistem</span>
                                    @endif
                                    @if(!$acc->is_active)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50">Non-Aktif</span>
                                    @endif
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-mono text-black/40 dark:text-white/40">
                                        {{ $acc->journal_lines_count }} Baris Jurnal
                                    </span>
                                </div>
                                @if($acc->description)
                                    <div class="text-[11.5px] text-black/45 dark:text-white/45 mt-0.5">
                                        {{ $acc->description }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                            <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded {{ $acc->normal_balance === 'debit' ? 'bg-[#007AFF]/10 text-[#007AFF]' : 'bg-[#AF52DE]/10 text-[#AF52DE]' }}">
                                {{ strtoupper($acc->normal_balance) }}
                            </span>

                            @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                                <button type="button" @click="openEditAccount(@js($acc))"
                                    class="w-7 h-7 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center transition-all cursor-pointer"
                                    title="{{ __('admin.ubah_akun') }}">
                                    <i data-lucide="pencil" class="w-3 h-3"></i>
                                </button>

                                @if(!$acc->is_system)
                                    <form action="{{ route('admin.finance.accounts.toggle', $acc) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="w-7 h-7 rounded-[8px] bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.08] dark:hover:bg-white/[0.1] {{ $acc->is_active ? 'text-[#34C759] hover:text-[#FF3B30]' : 'text-black/40 hover:text-[#34C759]' }} flex items-center justify-center transition-all cursor-pointer"
                                            title="{{ $acc->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                            <i data-lucide="{{ $acc->is_active ? 'check-circle' : 'circle' }}" class="w-3 h-3"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-black/40 dark:text-white/40 text-[12px]">
                        {{ __('admin.belum_ada_akun_terdaftar_pada_kategori') }}
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
