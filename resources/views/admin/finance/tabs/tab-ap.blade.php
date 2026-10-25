{{-- TAB 9: ACCOUNTS PAYABLE (HUTANG USAHA & TAGIHAN VENDOR) --}}
<div x-show="activeTab === 'ap'" x-cloak class="space-y-6">

    {{-- TOP AGING BENTO CARDS --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        {{-- Total Hutang Berjalan --}}
        <div class="col-span-2 md:col-span-3 lg:col-span-2 rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Total Hutang Vendor (Outstanding)</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#FF3B30]/15 text-[#FF3B30] flex items-center justify-center">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[22px] sm:text-[26px] font-bold text-[#C41E17] dark:text-[#FF453A] tabular-nums tracking-tight">
                    Rp {{ number_format($apAging['totalBalance'], 0, ',', '.') }}
                </div>
                <div class="text-[11.5px] text-black/50 dark:text-white/50 mt-1 flex items-center justify-between">
                    <span>Terbayar: <strong class="text-[#248A3D] dark:text-[#30D158]">Rp {{ number_format($apAging['totalPaid'], 0, ',', '.') }}</strong></span>
                    <span class="font-bold text-[#FF3B30]">{{ $apAging['paymentRate'] }}%</span>
                </div>
            </div>
        </div>

        {{-- Belum Jatuh Tempo (Current) --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 flex flex-col justify-between shadow-xs">
            <div class="text-black/50 dark:text-white/50 text-[11.5px] font-medium flex items-center justify-between">
                <span>Belum Tempo</span>
                <span class="w-2 h-2 rounded-full bg-[#34C759]"></span>
            </div>
            <div class="mt-2">
                <div class="text-[16px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums">
                    Rp {{ number_format($apAging['buckets']['current'], 0, ',', '.') }}
                </div>
                <div class="text-[10.5px] text-black/40 dark:text-white/40 mt-0.5">Current Bill</div>
            </div>
        </div>

        {{-- Overdue 1 - 30 Hari --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 flex flex-col justify-between shadow-xs">
            <div class="text-black/50 dark:text-white/50 text-[11.5px] font-medium flex items-center justify-between">
                <span>1 - 30 Hari</span>
                <span class="w-2 h-2 rounded-full bg-[#FFCC00]"></span>
            </div>
            <div class="mt-2">
                <div class="text-[16px] font-bold text-[#B28900] dark:text-[#FFD60A] tabular-nums">
                    Rp {{ number_format($apAging['buckets']['1_30'], 0, ',', '.') }}
                </div>
                <div class="text-[10.5px] text-black/40 dark:text-white/40 mt-0.5">Overdue 1-30 hr</div>
            </div>
        </div>

        {{-- Overdue 31 - 60 Hari --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 flex flex-col justify-between shadow-xs">
            <div class="text-black/50 dark:text-white/50 text-[11.5px] font-medium flex items-center justify-between">
                <span>31 - 60 Hari</span>
                <span class="w-2 h-2 rounded-full bg-[#FF9500]"></span>
            </div>
            <div class="mt-2">
                <div class="text-[16px] font-bold text-[#C96B00] dark:text-[#FF9F0A] tabular-nums">
                    Rp {{ number_format($apAging['buckets']['31_60'], 0, ',', '.') }}
                </div>
                <div class="text-[10.5px] text-black/40 dark:text-white/40 mt-0.5">Overdue 31-60 hr</div>
            </div>
        </div>

        {{-- Overdue > 60 Hari (High Risk) --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 flex flex-col justify-between shadow-xs">
            <div class="text-black/50 dark:text-white/50 text-[11.5px] font-medium flex items-center justify-between">
                <span>&gt; 60 Hari</span>
                <span class="w-2 h-2 rounded-full bg-[#FF3B30]"></span>
            </div>
            <div class="mt-2">
                <div class="text-[16px] font-bold text-[#C41E17] dark:text-[#FF453A] tabular-nums">
                    Rp {{ number_format($apAging['buckets']['61_90'] + $apAging['buckets']['over_90'], 0, ',', '.') }}
                </div>
                <div class="text-[10.5px] text-[#FF3B30] font-semibold mt-0.5">Jatuh Tempo Kritis</div>
            </div>
        </div>
    </div>

    {{-- HEADER TABLE & ACTION BUTTON --}}
    <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
        <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-[15px] font-bold text-black dark:text-white flex items-center gap-2">
                    <i data-lucide="file-check" class="w-4 h-4 text-[#FF3B30]"></i>
                    <span>Daftar Hutang Usaha &amp; Tagihan Vendor (Accounts Payable)</span>
                </h3>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                    Catat kewajiban tagihan server Hostinger/AWS, lisensi OpenAI/Gemini, WhatsApp Gateway, dan beban operasional dengan rekonsiliasi otomatis ke Akun 2130.
                </p>
            </div>

            @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                <button type="button" @click="openCreateAp()"
                    class="h-9.5 px-4 rounded-[12px] bg-[#FF3B30] hover:bg-[#D70015] active:scale-[0.98] text-white text-[12.5px] font-bold transition-all shadow-xs cursor-pointer flex items-center justify-center gap-2 shrink-0">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Catat Tagihan Vendor</span>
                </button>
            @endif
        </div>

        {{-- TABLE OF PAYABLES --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/5 bg-black/[0.02] dark:bg-white/[0.02] text-[11.5px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50">
                        <th class="py-3 px-4">No. Tagihan &amp; Tgl</th>
                        <th class="py-3 px-4">Nama Vendor &amp; Kategori</th>
                        <th class="py-3 px-4">Keterangan &amp; Akun Beban</th>
                        <th class="py-3 px-4">Jatuh Tempo &amp; Aging</th>
                        <th class="py-3 px-4 text-right">Total Tagihan</th>
                        <th class="py-3 px-4 text-right">Sisa Hutang</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5 text-[12.5px]">
                    @forelse($payables as $ap)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02] transition-colors" x-data="{ openHistory: false }">
                            {{-- Bill No & Date --}}
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-black dark:text-white text-[12.5px]">
                                    {{ $ap->bill_number }}
                                </div>
                                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                                    {{ \Carbon\Carbon::parse($ap->bill_date)->format('d M Y') }}
                                </div>
                                @if($ap->reference_number)
                                    <div class="text-[10.5px] font-mono text-black/50 dark:text-white/50 mt-0.5">
                                        Ref: {{ $ap->reference_number }}
                                    </div>
                                @endif
                                @if($ap->journalEntry)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-mono text-[#FF3B30] bg-[#FF3B30]/10 px-1.5 py-0.2 rounded mt-1" title="Jurnal Pengakuan Hutang">
                                        <i data-lucide="book-open" class="w-2.5 h-2.5"></i>
                                        {{ $ap->journalEntry->entry_number }}
                                    </span>
                                @endif
                            </td>

                            {{-- Vendor Name & Category --}}
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-black dark:text-white">
                                    {{ $ap->vendor_name }}
                                </div>
                                <div class="text-[11px] text-black/50 dark:text-white/50 capitalize mt-0.5 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#FF3B30]"></span>
                                    <span>{{ str_replace('_', ' ', $ap->vendor_category) }}</span>
                                </div>
                            </td>

                            {{-- Title & Expense Account --}}
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-semibold text-black dark:text-white line-clamp-1">
                                    {{ $ap->title }}
                                </div>
                                @if($ap->expenseAccount)
                                    <div class="text-[11px] text-[#007AFF] font-medium mt-0.5">
                                        {{ $ap->expenseAccount->code }} - {{ $ap->expenseAccount->name }}
                                    </div>
                                @endif
                                @if($ap->attachment_url)
                                    <button type="button" @click="previewImage('{{ $ap->attachment_url }}', 'Tagihan {{ $ap->bill_number }} - {{ $ap->vendor_name }}')"
                                        class="inline-flex items-center gap-1 text-[10.5px] font-medium text-[#007AFF] hover:underline mt-1 cursor-pointer">
                                        <i data-lucide="paperclip" class="w-3 h-3"></i>
                                        <span>Lihat Faktur/Tagihan</span>
                                    </button>
                                @endif
                            </td>

                            {{-- Due Date & Aging Bucket --}}
                            <td class="py-3.5 px-4">
                                <div class="font-medium {{ $ap->is_overdue ? 'text-[#FF3B30] font-bold' : 'text-black dark:text-white' }}">
                                    {{ \Carbon\Carbon::parse($ap->due_date)->format('d M Y') }}
                                </div>
                                <div class="mt-1">
                                    @php
                                        $bucket = $ap->aging_bucket;
                                    @endphp
                                    @if($bucket === 'paid')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">Lunas</span>
                                    @elseif($bucket === 'current')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#007AFF]/15 text-[#007AFF]">Belum Tempo</span>
                                    @elseif($bucket === '1-30')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FFCC00]/20 text-[#B28900] dark:text-[#FFD60A]">Lewat 1-30 hr</span>
                                    @elseif($bucket === '31-60')
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF9500]/20 text-[#C96B00] dark:text-[#FF9F0A]">Lewat 31-60 hr</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-[#FF3B30]/20 text-[#C41E17] dark:text-[#FF453A]">Macet (&gt;60 hr)</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Total Amount --}}
                            <td class="py-3.5 px-4 text-right">
                                <div class="font-bold text-black dark:text-white tabular-nums">
                                    Rp {{ number_format((float) $ap->total_amount, 0, ',', '.') }}
                                </div>
                                @if((float) $ap->paid_amount > 0)
                                    <div class="text-[11px] text-[#248A3D] dark:text-[#30D158] tabular-nums mt-0.5">
                                        Dibayar: Rp {{ number_format((float) $ap->paid_amount, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>

                            {{-- Balance Due --}}
                            <td class="py-3.5 px-4 text-right">
                                <div class="text-[13px] font-bold {{ (float) $ap->balance_due > 0 ? 'text-[#FF3B30]' : 'text-[#34C759]' }} tabular-nums">
                                    Rp {{ number_format((float) $ap->balance_due, 0, ',', '.') }}
                                </div>
                                @if($ap->payments->count() > 0)
                                    <button type="button" @click="openHistory = !openHistory"
                                        class="text-[10.5px] text-[#007AFF] hover:underline font-semibold mt-0.5 inline-flex items-center gap-1 cursor-pointer">
                                        <span>{{ $ap->payments->count() }}x Pembayaran</span>
                                        <i data-lucide="chevron-down" class="w-3 h-3 transition-transform" :class="openHistory ? 'rotate-180' : ''"></i>
                                    </button>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3.5 px-4 text-center">
                                @if($ap->status === 'paid')
                                    <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        Lunas
                                    </span>
                                @elseif($ap->status === 'partially_paid')
                                    <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A]">
                                        Dicicil Sebagian
                                    </span>
                                @elseif($ap->status === 'overdue')
                                    <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-[#FF3B30]/15 text-[#C41E17] dark:text-[#FF453A]">
                                        Lewat Tempo
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-[8px] text-[11px] font-bold bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60">
                                        Belum Dibayar
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="py-3.5 px-4 text-center">
                                @if($ap->status !== 'paid' && (float) $ap->balance_due > 0.01)
                                    @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                                        <button type="button" @click="openPayAp({{ json_encode($ap) }})"
                                            class="h-8 px-3 rounded-[9px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[11.5px] font-bold transition-all shadow-xs cursor-pointer inline-flex items-center gap-1.5">
                                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                            <span>Bayar Tagihan</span>
                                        </button>
                                    @endif
                                @else
                                    <span class="text-[11.5px] text-black/40 dark:text-white/40 font-medium">Lunas</span>
                                @endif
                            </td>
                        </tr>

                        {{-- EXPANDABLE PAYMENT HISTORY ROW --}}
                        @if($ap->payments->count() > 0)
                            <tr x-show="openHistory" x-cloak class="bg-black/[0.02] dark:bg-white/[0.015]">
                                <td colspan="8" class="p-4 pl-12 border-b border-black/5 dark:border-white/5">
                                    <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-3.5 space-y-2.5 shadow-xs">
                                        <div class="text-[12px] font-bold text-black dark:text-white flex items-center gap-1.5">
                                            <i data-lucide="history" class="w-3.5 h-3.5 text-[#FF3B30]"></i>
                                            <span>Riwayat Pembayaran Tagihan {{ $ap->bill_number }}</span>
                                        </div>

                                        <div class="divide-y divide-black/5 dark:divide-white/5 text-[11.5px]">
                                            @foreach($ap->payments as $pay)
                                                <div class="py-2 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-mono font-bold text-black dark:text-white">{{ $pay->payment_number }}</span>
                                                        <span class="text-black/40 dark:text-white/40">•</span>
                                                        <span>{{ \Carbon\Carbon::parse($pay->payment_date)->format('d M Y') }}</span>
                                                        <span class="text-black/40 dark:text-white/40">•</span>
                                                        <span class="capitalize text-black/60 dark:text-white/60">{{ str_replace('_', ' ', $pay->payment_method) }}</span>
                                                        @if($pay->account)
                                                            <span class="text-[#007AFF] font-medium">({{ $pay->account->code }} - {{ $pay->account->name }})</span>
                                                        @endif
                                                    </div>

                                                    <div class="flex items-center gap-3">
                                                        <span class="font-bold text-[#C41E17] dark:text-[#FF453A] tabular-nums">
                                                            Rp {{ number_format((float) $pay->amount, 0, ',', '.') }}
                                                        </span>

                                                        @if($pay->attachment_url)
                                                            <button type="button" @click="previewImage('{{ $pay->attachment_url }}', 'Bukti Transfer Keluar {{ $pay->payment_number }}')"
                                                                class="text-[#007AFF] hover:underline font-semibold inline-flex items-center gap-1 cursor-pointer">
                                                                <i data-lucide="image" class="w-3 h-3"></i>
                                                                <span>Bukti Transfer Keluar</span>
                                                            </button>
                                                        @endif

                                                        @if($pay->journalEntry)
                                                            <span class="text-[10px] font-mono text-[#007AFF] bg-[#007AFF]/10 px-1.5 py-0.2 rounded" title="Jurnal Pengeluaran Kas">
                                                                {{ $pay->journalEntry->entry_number }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif

                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-black/40 dark:text-white/40">
                                <i data-lucide="file-check" class="w-10 h-10 mx-auto stroke-[1.2] opacity-40 mb-2"></i>
                                <div class="text-[13px] font-semibold">Belum Ada Tagihan Hutang Vendor (AP)</div>
                                <div class="text-[12px] text-black/35 dark:text-white/35 mt-0.5">
                                    Klik tombol "Catat Tagihan Vendor" untuk mendaftarkan tagihan vendor cloud, AI API, atau biaya operasional.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payables->hasPages())
            <div class="p-4 border-t border-black/5 dark:border-white/5">
                {{ $payables->links() }}
            </div>
        @endif
    </div>
</div>
