{{-- TAB 10: PAJAK & FISKAL PLATFORM (PPN LANGGANAN & TOP-UP) --}}
<div x-show="activeTab === 'tax'" class="space-y-6">

    {{-- 1. PENGATURAN SAKELAR & TARIF PPN PLATFORM --}}
    <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 shadow-xs backdrop-blur-xl">
        <form method="POST" action="{{ route('admin.finance.tax-settings.update') }}" class="space-y-5">
            @csrf
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-black/5 dark:border-white/5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-[12px] bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] flex items-center justify-center shrink-0">
                        <i data-lucide="receipt-percent" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-[16px] font-bold text-black dark:text-white">Pengaturan Pajak Pertambahan Nilai (PPN) Platform</h2>
                        <p class="text-[12.5px] text-black/50 dark:text-white/50 mt-0.5">
                            Konfigurasi pemungutan PPN resmi atas penjualan langganan software SaaS, katalog paket, serta kuota top-up AI &amp; storage tenant.
                        </p>
                    </div>
                </div>

                @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                    <button type="submit"
                        class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#FF9500] hover:bg-[#E08500] active:scale-[0.98] transition-all flex items-center gap-2 shadow-sm shadow-[#FF9500]/25 cursor-pointer shrink-0">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Pengaturan PPN</span>
                    </button>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 pt-1">
                {{-- Toggle Sakelar PPN --}}
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-[13px] font-bold text-black dark:text-white">Status Pemungutan PPN</span>
                            <span class="px-2 py-0.5 rounded-full text-[10.5px] font-bold {{ $platformTaxSummary['tax_enabled'] ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50' }}">
                                {{ $platformTaxSummary['tax_enabled'] ? 'Aktif Terpungut' : 'Nonaktif' }}
                            </span>
                        </div>
                        <p class="text-[11.5px] text-black/50 dark:text-white/50 mt-1 leading-relaxed">
                            Bila aktif, sistem akan menghitung PPN pada invoice checkout langganan dan top-up, serta membukukannya ke Akun COA <strong class="text-black dark:text-white font-mono">2140</strong>.
                        </p>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer select-none pt-2">
                        <input type="checkbox" name="platform_tax_enabled" value="1" {{ $platformTaxSummary['tax_enabled'] ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-black/15 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[10px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                        <span class="ml-3 text-[12.5px] font-bold text-black/80 dark:text-white/80">
                            Aktifkan Pemungutan PPN
                        </span>
                    </label>
                </div>

                {{-- Tarif & Nama Label PPN --}}
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3">
                    <label class="block text-[13px] font-bold text-black dark:text-white">Tarif PPN (%) &amp; Nama Pajak</label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <div class="relative">
                                <input type="number" name="platform_tax_rate" step="0.1" min="0" max="100"
                                    value="{{ $platformTaxSummary['tax_rate'] }}"
                                    class="w-full h-10 px-3.5 pr-8 rounded-[12px] bg-white dark:bg-black/30 border border-black/10 dark:border-white/10 text-[14px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF9500]/40">
                                <span class="absolute right-3 top-2.5 text-[13px] font-bold text-black/40 dark:text-white/40">%</span>
                            </div>
                            <div class="flex items-center gap-1 mt-1.5 text-[10.5px]">
                                <span class="text-black/45 dark:text-white/45">Preset:</span>
                                <button type="button" onclick="document.querySelector('input[name=platform_tax_rate]').value = 11" class="text-[#007AFF] hover:underline font-bold">11%</button>
                                <span class="text-black/30 dark:text-white/30">|</span>
                                <button type="button" onclick="document.querySelector('input[name=platform_tax_rate]').value = 12" class="text-[#007AFF] hover:underline font-bold">12% (HPP)</button>
                            </div>
                        </div>

                        <div>
                            <input type="text" name="platform_tax_name"
                                value="{{ $platformTaxSummary['tax_name'] }}" placeholder="PPN"
                                class="w-full h-10 px-3.5 rounded-[12px] bg-white dark:bg-black/30 border border-black/10 dark:border-white/10 text-[13px] font-semibold text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF9500]/40">
                            <span class="block text-[10.5px] text-black/45 dark:text-white/45 mt-1.5">Label pada Invoice</span>
                        </div>
                    </div>

                    {{-- Skema Inklusif vs Eksklusif --}}
                    <div class="pt-2 border-t border-black/5 dark:border-white/5">
                        <label class="block text-[11.5px] font-semibold text-black/70 dark:text-white/70 mb-1.5">Skema Pengenaan Pajak:</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-2 rounded-[10px] bg-white dark:bg-black/30 border border-black/5 dark:border-white/10 cursor-pointer text-[12px]">
                                <input type="radio" name="platform_tax_inclusive" value="0" {{ !$platformTaxSummary['tax_inclusive'] ? 'checked' : '' }} class="text-[#FF9500] focus:ring-0">
                                <span class="font-medium text-black/80 dark:text-white/80">Eksklusif (+ PPN)</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-[10px] bg-white dark:bg-black/30 border border-black/5 dark:border-white/10 cursor-pointer text-[12px]">
                                <input type="radio" name="platform_tax_inclusive" value="1" {{ $platformTaxSummary['tax_inclusive'] ? 'checked' : '' }} class="text-[#FF9500] focus:ring-0">
                                <span class="font-medium text-black/80 dark:text-white/80">Inklusif (Nett)</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Cakupan Objek Pajak & Identitas PKP --}}
                <div class="p-4 rounded-[16px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3">
                    <label class="block text-[13px] font-bold text-black dark:text-white">Cakupan Objek Pajak Platform</label>
                    <div class="space-y-2 text-[12px]">
                        <label class="flex items-center gap-2 text-black/80 dark:text-white/80 cursor-pointer">
                            <input type="checkbox" name="platform_tax_apply_subscription" value="1" {{ $platformTaxSummary['apply_subscription'] ? 'checked' : '' }} class="rounded text-[#FF9500] focus:ring-0">
                            <span>Langganan Paket SaaS (Core / Standard / Premium / Prestige)</span>
                        </label>
                        <label class="flex items-center gap-2 text-black/80 dark:text-white/80 cursor-pointer">
                            <input type="checkbox" name="platform_tax_apply_topup" value="1" {{ $platformTaxSummary['apply_topup'] ? 'checked' : '' }} class="rounded text-[#FF9500] focus:ring-0">
                            <span>Top-Up Kuota Instan (Token AI &amp; Tambahan Storage Disk)</span>
                        </label>
                    </div>

                    <div class="pt-2 border-t border-black/5 dark:border-white/5 space-y-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">NPWP Perusahaan Cooca</label>
                            <input type="text" name="platform_tax_company_npwp" value="{{ $platformTaxSummary['company_npwp'] }}"
                                class="w-full h-8.5 px-3 rounded-[9px] bg-white dark:bg-black/30 border border-black/10 dark:border-white/10 text-[12px] font-mono text-black dark:text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-black/60 dark:text-white/60 mb-0.5">Nama PKP / Badan Usaha</label>
                            <input type="text" name="platform_tax_company_name" value="{{ $platformTaxSummary['company_name'] }}"
                                class="w-full h-8.5 px-3 rounded-[9px] bg-white dark:bg-black/30 border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- 2. DASHBOARD KPI PAJAK & REKAP SPT MASA PPN --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Total Omzet Bruto --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Omzet Bruto Periode Ini</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center">
                    <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold text-black dark:text-white tabular-nums tracking-tight">
                    Rp {{ number_format($platformTaxSummary['total_gross_revenue'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                    DPP Pokok + PPN Terkumpul
                </div>
            </div>
        </div>

        {{-- Dasar Pengenaan Pajak (DPP) --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>DPP Pokok Penjualan</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold text-[#248A3D] dark:text-[#30D158] tabular-nums tracking-tight">
                    Rp {{ number_format($platformTaxSummary['total_dpp'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                    Diakui sebagai Pendapatan SaaS (4110)
                </div>
            </div>
        </div>

        {{-- Total PPN Terkumpul (Output VAT) --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>PPN Keluaran Terkumpul</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#FF9500]/15 text-[#B25E00] dark:text-[#FF9F0A] flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold text-[#FF9500] dark:text-[#FF9F0A] tabular-nums tracking-tight">
                    Rp {{ number_format($platformTaxSummary['total_tax_collected'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                    {{ $platformTaxSummary['taxable_count'] }} Transaksi Berfaktur Pajak
                </div>
            </div>
        </div>

        {{-- Saldo Terutang Akun COA 2140 --}}
        <div class="rounded-[18px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-4 sm:p-5 flex flex-col justify-between shadow-xs">
            <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[12px] font-medium">
                <span>Saldo Hutang PPN (COA 2140)</span>
                <div class="w-7 h-7 rounded-[9px] bg-[#AF52DE]/15 text-[#AF52DE] flex items-center justify-center">
                    <i data-lucide="landmark" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-[20px] sm:text-[24px] font-bold {{ $platformTaxSummary['vat_payable_balance'] > 0 ? 'text-[#FF3B30]' : 'text-black dark:text-white' }} tabular-nums tracking-tight">
                    Rp {{ number_format($platformTaxSummary['vat_payable_balance'], 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-black/45 dark:text-white/45 mt-0.5">
                    Kewajiban Setor ke Kas Negara (DJP)
                </div>
            </div>
        </div>
    </div>

    {{-- 3. SPLIT SECTION: FORM SETOR PAJAK & REKAPITULASI SPT MASA --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form Catat Setoran PPN ke Kas Negara (DJP) --}}
        <div class="lg:col-span-1 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2.5 pb-3 border-b border-black/5 dark:border-white/5">
                    <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] flex items-center justify-center">
                        <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-[14px] font-bold text-black dark:text-white">Catat Setor PPN ke DJP</h3>
                        <p class="text-[11px] text-black/50 dark:text-white/50">Pelunasan e-Billing Kas Negara</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.finance.tax-settle') }}" enctype="multipart/form-data" class="space-y-3 pt-4">
                    @csrf
                    <div>
                        <label class="block text-[11.5px] font-bold text-black/70 dark:text-white/70 mb-1">Nominal Setoran (Rp) *</label>
                        <input type="number" name="amount" min="1" step="100"
                            value="{{ $platformTaxSummary['vat_payable_balance'] > 0 ? $platformTaxSummary['vat_payable_balance'] : '' }}"
                            placeholder="Contoh: 1500000" required
                            class="w-full h-9.5 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[13px] font-bold tabular-nums text-black dark:text-white focus:outline-none focus:ring-1 focus:ring-[#34C759]">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-black/70 dark:text-white/70 mb-1">Tanggal Setor *</label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                class="w-full h-9 px-2.5 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[11.5px] text-black dark:text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-black/70 dark:text-white/70 mb-1">Kode NTPN / Billing *</label>
                            <input type="text" name="reference_number" placeholder="NTPN-XXXXXXXX" required
                                class="w-full h-9 px-2.5 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[11.5px] font-mono text-black dark:text-white focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-black/70 dark:text-white/70 mb-1">Catatan Setoran</label>
                        <input type="text" name="notes" placeholder="Setor SPT Masa PPN..."
                            class="w-full h-9 px-3 rounded-[9px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/10 dark:border-white/10 text-[12px] text-black dark:text-white focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-black/70 dark:text-white/70 mb-1">Bukti BPN / Bukti Setor Bank (Opsional)</label>
                        <input type="file" name="attachment" accept="image/*,.pdf"
                            class="w-full text-[11px] text-black/60 dark:text-white/60 file:mr-2.5 file:py-1 file:px-2.5 file:rounded-[8px] file:border-0 file:text-[11px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 cursor-pointer">
                    </div>

                    @if(Auth::guard('admin')->user()?->hasPermission('admin.finance.manage'))
                        <button type="submit"
                            class="w-full h-9.5 mt-2 rounded-[10px] bg-[#34C759] hover:bg-[#2EB04E] text-white text-[12.5px] font-bold transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Posting Pelunasan Pajak</span>
                        </button>
                    @endif
                </form>
            </div>

            <div class="mt-4 pt-3 border-t border-black/5 dark:border-white/5 text-[11px] text-black/45 dark:text-white/45">
                <i data-lucide="info" class="w-3.5 h-3.5 inline mr-1 text-[#007AFF]"></i>
                Otomatis mendebit Akun 2140 dan mengkredit Kas &amp; Bank 1120.
            </div>
        </div>

        {{-- Tabel Rekapitulasi SPT Masa PPN per Bulan --}}
        <div class="lg:col-span-2 rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs">
            <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
                <div>
                    <h3 class="text-[14px] font-bold text-black dark:text-white">Rekapitulasi SPT Masa PPN (Output VAT)</h3>
                    <p class="text-[11.5px] text-black/50 dark:text-white/50">Akumulasi Dasar Pengenaan Pajak &amp; PPN per masa pajak</p>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FF9500]/12 text-[#FF9500]">
                    Formulir 1111 Cooca
                </span>
            </div>

            <div class="overflow-x-auto mt-3">
                <table class="w-full text-left text-[12.5px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/5 text-black/45 dark:text-white/45 text-[11px] uppercase tracking-wider">
                            <th class="py-2.5 font-bold">Masa Pajak</th>
                            <th class="py-2.5 text-center font-bold">Jumlah Faktur</th>
                            <th class="py-2.5 text-right font-bold">DPP Pokok (Rp)</th>
                            <th class="py-2.5 text-right font-bold">PPN Terutang (Rp)</th>
                            <th class="py-2.5 text-right font-bold">Total Bruto (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5 dark:divide-white/5">
                        @forelse($platformTaxSummary['monthly_recap'] as $m)
                            <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02]">
                                <td class="py-2.5 font-bold text-black dark:text-white">{{ $m['period'] }}</td>
                                <td class="py-2.5 text-center font-mono">{{ $m['count'] }}</td>
                                <td class="py-2.5 text-right font-mono font-semibold text-[#248A3D] dark:text-[#30D158]">
                                    Rp {{ number_format($m['dpp'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 text-right font-mono font-bold text-[#FF9500] dark:text-[#FF9F0A]">
                                    Rp {{ number_format($m['tax'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 text-right font-mono font-bold text-black dark:text-white">
                                    Rp {{ number_format($m['gross'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-black/40 dark:text-white/40 text-[12px]">
                                    Belum ada transaksi kena pajak pada rentang tanggal ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 4. TABEL RINCIAN TRANSAKSI LANGGANAN & TOP-UP BERPAJAK --}}
    <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 shadow-xs">
        <div class="flex items-center justify-between pb-3 border-b border-black/5 dark:border-white/5">
            <div>
                <h3 class="text-[14px] font-bold text-black dark:text-white">Daftar Transaksi Langganan &amp; Top-Up Berfaktur Pajak</h3>
                <p class="text-[11.5px] text-black/50 dark:text-white/50">Rincian faktur PPN keluaran transaksi tenant berstatus approved</p>
            </div>
            <span class="text-[11.5px] text-black/50 dark:text-white/50">
                Total: <strong class="text-black dark:text-white">{{ count($platformTaxSummary['taxable_payments']) }}</strong> pesanan
            </span>
        </div>

        <div class="overflow-x-auto mt-3">
            <table class="w-full text-left text-[12px]">
                <thead>
                    <tr class="border-b border-black/5 dark:border-white/5 text-black/45 dark:text-white/45 text-[11px] uppercase tracking-wider">
                        <th class="py-2.5 font-bold">Waktu &amp; No. Order</th>
                        <th class="py-2.5 font-bold">Tenant Bisnis</th>
                        <th class="py-2.5 font-bold">Paket / Layanan</th>
                        <th class="py-2.5 text-right font-bold">DPP Pokok</th>
                        <th class="py-2.5 text-center font-bold">Tarif</th>
                        <th class="py-2.5 text-right font-bold">PPN (11%)</th>
                        <th class="py-2.5 text-right font-bold">Total Tagihan</th>
                        <th class="py-2.5 text-center font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($platformTaxSummary['taxable_payments'] as $tx)
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.02]">
                            <td class="py-3">
                                <div class="font-mono font-bold text-[#007AFF] text-[12.5px]">{{ $tx['order_number'] }}</div>
                                <div class="text-[10.5px] text-black/40 dark:text-white/40">{{ $tx['approved_at'] }}</div>
                            </td>
                            <td class="py-3 font-semibold text-black dark:text-white">
                                {{ $tx['business_name'] }}
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-[6px] text-[10.5px] font-bold bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6]">
                                    {{ $tx['plan_name'] }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-mono font-medium text-black/80 dark:text-white/80">
                                Rp {{ number_format($tx['dpp'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 text-center font-mono">
                                {{ $tx['tax_rate'] }}%
                                <span class="block text-[9.5px] text-black/40 dark:text-white/40">{{ $tx['is_inclusive'] ? 'Inklusif' : 'Eksklusif' }}</span>
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-[#FF9500] dark:text-[#FF9F0A]">
                                Rp {{ number_format($tx['tax_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-black dark:text-white">
                                Rp {{ number_format($tx['total_payable'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 text-center">
                                <a href="{{ route('admin.subscriptions.show', $tx['id']) }}"
                                    class="h-7 px-2.5 rounded-[8px] bg-black/[0.04] dark:bg-white/[0.06] hover:bg-[#007AFF]/15 text-[#007AFF] text-[11px] font-bold transition-all inline-flex items-center gap-1">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Faktur</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-black/40 dark:text-white/40 text-[12.5px]">
                                <i data-lucide="receipt" class="w-8 h-8 mx-auto mb-2 opacity-30"></i>
                                <div>Belum ada transaksi kena pajak pada rentang tanggal ini.</div>
                                <div class="text-[11px] mt-0.5">Aktifkan sakelar PPN di atas untuk mulai memungut PPN pada pesanan langganan berikutnya.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

