{{-- TAB 7: INTEGRASI BILLING & SETTLEMENT --}}
<div x-show="activeTab === 'sync'" class="space-y-6">
    <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/5">
            <div>
                <h2 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.rekonsiliasi_langganan_pencairan_saldo_merchant') }}</h2>
                <p class="text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">
                    Sinkronisasi data multi-modul secara instan antara sistem tagihan billing SaaS dan transfer pencairan saldo merchant.
                </p>
            </div>
            @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.sync'))
                <form action="{{ route('admin.finance.sync') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="h-10 px-4.5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm shadow-[#007AFF]/25 cursor-pointer">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>Sinkronkan Sekarang</span>
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
            {{-- Modul Billing Subscriptions --}}
            <div class="rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4.5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-[10px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-full {{ $unsyncedSubscriptionsCount > 0 ? 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] font-bold' : 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' }}">
                            {{ $unsyncedSubscriptionsCount > 0 ? $unsyncedSubscriptionsCount . ' Belum Tersinkron' : 'Semua Tersinkron' }}
                        </span>
                    </div>

                    <h3 class="text-[15px] font-bold text-black dark:text-white mt-3">Langganan &amp; Billing (SaaS Inflow)</h3>
                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                        Penerimaan pembayaran dari paket Starter, Pro, Scale, dan Enterprise yang disetujui otomatis dicatat sebagai Kas Masuk beserta bukti transfer aslinya.
                    </p>
                </div>

                <div class="mt-4 pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span class="text-[11px] text-black/45 dark:text-white/45">Aturan: Status Approved</span>
                    <a href="{{ route('admin.subscriptions.index') }}" class="text-[12px] font-bold text-[#007AFF] hover:underline flex items-center gap-1">
                        <span>{{ __('admin.buka_modul_billing') }}</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            {{-- Modul Merchant Settlements --}}
            <div class="rounded-[18px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 p-4.5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="w-9 h-9 rounded-[10px] bg-[#AF52DE]/15 text-[#AF52DE] flex items-center justify-center">
                            <i data-lucide="arrow-down-to-dot" class="w-5 h-5"></i>
                        </div>
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-full {{ $unsyncedSettlementsCount > 0 ? 'bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] font-bold' : 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' }}">
                            {{ $unsyncedSettlementsCount > 0 ? $unsyncedSettlementsCount . ' Belum Tersinkron' : 'Semua Tersinkron' }}
                        </span>
                    </div>

                    <h3 class="text-[15px] font-bold text-black dark:text-white mt-3">Pencairan Merchant (Settlement Outflow)</h3>
                    <p class="text-[12px] text-black/55 dark:text-white/55 mt-1 leading-relaxed">
                        Pencairan saldo dompet QRIS/Online tenant UMKM yang berstatus transfer selesai dibukukan otomatis sebagai Kas Keluar dan mengurangi titipan saldo merchant.
                    </p>
                </div>

                <div class="mt-4 pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                    <span class="text-[11px] text-black/45 dark:text-white/45">Aturan: Status Completed</span>
                    <a href="{{ route('admin.settlements.index') }}" class="text-[12px] font-bold text-[#AF52DE] hover:underline flex items-center gap-1">
                        <span>{{ __('admin.buka_modul_settlement') }}</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
