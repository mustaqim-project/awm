<div x-data="aiStudioTopupModal()"
    @open-ai-topup-modal.window="openModal($event.detail)"
    x-show="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
    x-cloak>

    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
        @click="closeModal()"
        x-show="isOpen"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"></div>

    <!-- Modal Dialog -->
    <div class="min-h-full flex items-center justify-center p-4 text-center sm:p-0">
        <div class="relative w-full max-w-xl rounded-[24px] backdrop-blur-xl bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-7 text-left shadow-2xl transition-all transform overflow-hidden"
            @click.away="closeModal()"
            x-show="isOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95">

            <!-- Decorative Ambient Glow -->
            <div class="absolute -top-24 -right-24 w-60 h-60 rounded-full bg-gradient-to-br from-amber-500/15 via-orange-500/10 to-transparent blur-3xl pointer-events-none"></div>

            <!-- ========================================== -->
            <!-- STEP 1: SELECT PACKAGE & PAYMENT METHOD    -->
            <!-- ========================================== -->
            <div x-show="step === 'select'" class="space-y-5">
                <!-- Header -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-[14px] bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center text-white shadow-md shadow-orange-500/25 shrink-0">
                            <i data-lucide="zap" class="w-5 h-5 fill-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                                <span>Top Up Kredit AI Studio</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                    Instan &amp; Otomatis
                                </span>
                            </h3>
                            <p class="text-xs text-black/60 dark:text-white/60 mt-0.5">
                                Isi ulang saldo kredit untuk melanjutkan render visual katalog &amp; materi promosi.
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="closeModal()"
                        class="w-8 h-8 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/15 flex items-center justify-center text-black/60 dark:text-white/60 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Shortage Alert Banner (If triggered by Insufficient Credits) -->
                <div x-show="shortageMessage"
                    class="p-3.5 rounded-[14px] bg-amber-500/10 border border-amber-500/25 flex items-start gap-2.5 text-xs text-amber-800 dark:text-amber-300">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                    <div class="space-y-0.5 leading-relaxed">
                        <span class="font-bold block" x-text="shortageTitle">Kredit Bulanan Telah Habis</span>
                        <span x-text="shortageMessage">Kredit Anda tidak mencukupi untuk memproses render ini. Silakan top up paket di bawah untuk melanjutkan.</span>
                    </div>
                </div>

                <!-- Current Balance Pill -->
                <div class="p-3 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-medium text-black/70 dark:text-white/70">
                        <i data-lucide="wallet" class="w-4 h-4 text-amber-500"></i>
                        <span>Saldo Kredit Aktif Saat Ini:</span>
                    </div>
                    <div class="flex items-baseline gap-1">
                        <span class="text-base font-extrabold"
                            :class="currentCredits <= 0 ? 'text-red-500' : 'text-[#007AFF] dark:text-[#0A84FF]'"
                            x-text="currentCredits">0</span>
                        <span class="text-[11px] text-black/50 dark:text-white/50 font-medium">kredit</span>
                    </div>
                </div>

                <!-- Packages Grid (Bento) -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-black/80 dark:text-white/80 block">Pilih Paket Isi Ulang Kredit:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <template x-for="pkg in packages" :key="pkg.code">
                            <div @click="selectPackage(pkg)"
                                class="relative p-3.5 rounded-[16px] border cursor-pointer transition-all text-left flex flex-col justify-between"
                                :class="selectedTierCode === pkg.code
                                    ? 'bg-[#007AFF]/5 dark:bg-[#007AFF]/15 border-[#007AFF] shadow-sm shadow-[#007AFF]/15 ring-2 ring-[#007AFF]/20'
                                    : 'bg-white/60 dark:bg-[#2C2C2E]/60 border-black/10 dark:border-white/10 hover:border-black/20 dark:hover:border-white/20'">

                                <!-- Badge (e.g. Terpopuler) -->
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="text-xs font-bold text-black dark:text-white" x-text="pkg.name"></span>
                                    <span x-show="pkg.badge"
                                        class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wide bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-xs"
                                        x-text="pkg.badge"></span>
                                </div>

                                <div class="space-y-1 mb-2">
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-xl font-black text-black dark:text-white" x-text="pkg.credits"></span>
                                        <span class="text-xs font-semibold text-black/60 dark:text-white/60">Kredit Visual</span>
                                    </div>
                                    <p class="text-[11px] text-black/55 dark:text-white/55 line-clamp-2 leading-tight" x-text="pkg.description"></p>
                                </div>

                                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between">
                                    <span class="text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]" x-text="pkg.formatted_price"></span>
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center transition-colors"
                                        :class="selectedTierCode === pkg.code ? 'border-[#007AFF] bg-[#007AFF] text-white' : 'border-black/20 dark:border-white/20'">
                                        <i data-lucide="check" class="w-2.5 h-2.5" x-show="selectedTierCode === pkg.code"></i>
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Payment Channel Selector -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-black/80 dark:text-white/80 block">Metode Pembayaran (Otomatis &amp; Realtime):</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <template x-for="ch in paymentChannels" :key="ch.code">
                            <button type="button" @click="selectedPaymentMethod = ch.code"
                                class="p-2.5 rounded-[12px] border text-left flex flex-col justify-between transition-all"
                                :class="selectedPaymentMethod === ch.code
                                    ? 'bg-[#007AFF]/10 dark:bg-[#007AFF]/20 border-[#007AFF] text-[#007AFF] font-bold shadow-xs'
                                    : 'bg-black/[0.02] dark:bg-white/[0.04] border-black/5 dark:border-white/10 text-black/70 dark:text-white/70 hover:bg-black/[0.05]'">
                                <span class="text-[11px] font-semibold truncate block" x-text="ch.name"></span>
                                <span class="text-[9px] text-black/50 dark:text-white/50 block mt-1" x-text="ch.code === 'qris' ? 'Scan & Pay' : 'Transfer VA'"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Footer Summary & CTA -->
                <div class="pt-3 border-t border-black/10 dark:border-white/10 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="text-left">
                        <span class="text-[11px] text-black/50 dark:text-white/50 block">Total Tagihan:</span>
                        <span class="text-base font-extrabold text-black dark:text-white" x-text="selectedPackagePrice">Rp 25.000</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="closeModal()"
                            class="px-4 py-2.5 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">{{ __('common.cancel') }}</button>
                        <button type="button" @click="submitOrder()" :disabled="submitting"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-[12px] bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-orange-500/20 active:scale-[0.98] transition-all">
                            <i data-lucide="shield-check" class="w-4 h-4" x-show="!submitting"></i>
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="submitting" style="display: none;"></i>
                            <span x-text="submitting ? 'Memproses Gateway...' : 'Bayar Sekarang'">{{ __('ai.bayar_sekarang') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- STEP 2: WAITING FOR PAYMENT (QRIS / VA)    -->
            <!-- ========================================== -->
            <div x-show="step === 'payment'" class="space-y-5 text-center" style="display: none;">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                    <div class="flex items-center gap-2 text-left">
                        <span class="w-7 h-7 rounded-[8px] bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h4 class="text-xs font-bold text-black dark:text-white">{{ __('ai.menunggu_pembayaran') }}</h4>
                            <span class="text-[10px] text-black/50 dark:text-white/50" x-text="'No. Pesanan: #' + activeOrder?.order_number"></span>
                        </div>
                    </div>
                    <div class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[11px] font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                        <span x-text="countdownText">15:00</span>
                    </div>
                </div>

                <!-- Total Payable Card -->
                <div class="p-3.5 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/5 dark:border-white/10 space-y-1">
                    <span class="text-[11px] text-black/60 dark:text-white/60">Total yang harus dibayar:</span>
                    <div class="text-2xl font-black text-black dark:text-white tracking-tight" x-text="activeOrder?.formatted_amount"></div>
                    <span class="text-[11px] text-[#007AFF] dark:text-[#0A84FF] font-semibold block" x-text="'+' + activeOrder?.credits + ' Kredit AI Studio'"></span>
                </div>

                <!-- QRIS Visual Render -->
                <div x-show="activeOrder?.qr_url" class="space-y-3">
                    <div class="inline-block p-4 rounded-[20px] bg-white border border-black/10 shadow-md">
                        <img :src="activeOrder?.qr_url" alt="{{ __('ai.qris_dinamis') }}" class="w-56 h-56 mx-auto object-contain">
                    </div>
                    <p class="text-xs text-black/60 dark:text-white/60 max-w-sm mx-auto leading-relaxed">
                        Buka aplikasi <strong>BCA, Livin, BRImo, GoPay, OVO, ShopeePay, DANA</strong> atau aplikasi m-Banking mana pun, lalu scan kode QRIS di atas.
                    </p>
                </div>

                <!-- Virtual Account Code (If VA chosen) -->
                <div x-show="activeOrder?.pay_code && !activeOrder?.qr_url" class="space-y-3">
                    <div class="p-4 rounded-[16px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 space-y-2">
                        <span class="text-xs text-black/60 dark:text-white/60 block">Nomor Virtual Account:</span>
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-xl font-mono font-bold text-black dark:text-white tracking-wider" x-text="activeOrder?.pay_code"></span>
                            <button type="button" @click="copyPayCode()"
                                class="px-2.5 py-1.5 rounded-[8px] bg-[#007AFF] text-white text-[10px] font-bold shadow-xs hover:bg-[#0062cc]">
                                <span x-text="copiedCode ? 'Disalin!' : 'Salin'">Salin</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Live Polling Notice -->
                <div class="flex items-center justify-center gap-2 text-xs text-black/50 dark:text-white/50 pt-2">
                    <i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin text-[#007AFF]"></i>
                    <span>Sistem memeriksa pembayaran secara otomatis...</span>
                </div>

                <!-- Fallback Actions -->
                <div class="pt-3 border-t border-black/10 dark:border-white/10 flex items-center justify-between gap-2">
                    <button type="button" @click="step = 'select'; stopPolling();"
                        class="text-xs text-black/60 dark:text-white/60 hover:underline">{{ __('ai.larr_ganti_paket_metode') }}</button>

                    <a :href="activeOrder?.payment_url" target="_blank"
                        class="text-xs font-semibold text-[#007AFF] hover:underline inline-flex items-center gap-1">
                        <span>{{ __('ai.buka_halaman_tagihan_lengkap') }}</span>
                        <i data-lucide="external-link" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- STEP 3: SUCCESS STATE                      -->
            <!-- ========================================== -->
            <div x-show="step === 'success'" class="space-y-5 py-6 text-center" style="display: none;">
                <div class="w-16 h-16 rounded-full bg-[#34C759]/15 text-[#34C759] flex items-center justify-center mx-auto shadow-md shadow-[#34C759]/20">
                    <i data-lucide="check-circle-2" class="w-9 h-9"></i>
                </div>

                <div class="space-y-1.5 max-w-sm mx-auto">
                    <h3 class="text-xl font-bold text-black dark:text-white">{{ __('ai.pembayaran_berhasil') }}</h3>
                    <p class="text-xs text-black/60 dark:text-white/60 leading-relaxed">
                        Top up kredit telah berhasil diverifikasi oleh sistem. Saldo kredit AI Studio Anda telah ditambahkan secara instan.
                    </p>
                </div>

                <div class="p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 inline-block px-6">
                    <span class="text-xs text-black/70 dark:text-white/70 block">Saldo Kredit Anda Sekarang:</span>
                    <span class="text-2xl font-black text-[#34C759]" x-text="currentCredits + ' Kredit'"></span>
                </div>

                <div class="pt-3">
                    <button type="button" @click="closeModal()"
                        class="w-full py-3 rounded-[14px] bg-[#007AFF] hover:bg-[#0062cc] text-white text-xs font-bold shadow-md shadow-[#007AFF]/25 transition-all">{{ __('ai.lanjut_generate_visual_produk') }}</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function aiStudioTopupModal() {
    return {
        isOpen: false,
        step: 'select', // 'select' | 'payment' | 'success'
        currentCredits: {{ (int) ($creditsBalance ?? ($stats['credits_balance'] ?? 0)) }},
        shortageTitle: '',
        shortageMessage: '',
        selectedTierCode: 'growth',
        selectedPackageId: null,
        selectedPaymentMethod: 'qris',
        submitting: false,
        activeOrder: null,
        pollingInterval: null,
        countdownSeconds: 900,
        countdownInterval: null,
        countdownText: '15:00',
        copiedCode: false,

        packages: [
            {
                code: 'starter',
                name: 'Starter Pack',
                credits: 50,
                price: 25000,
                formatted_price: 'Rp 25.000',
                badge: null,
                description: 'Uji coba 3-5 visual katalog produk & materi promosi sosmed.',
            },
            {
                code: 'growth',
                name: 'Growth Pack',
                credits: 150,
                price: 65000,
                formatted_price: 'Rp 65.000',
                badge: 'Terpopuler',
                description: 'Photoshoot katalog mingguan & kampanye visual omnichannel.',
            },
            {
                code: 'pro',
                name: 'Pro Studio',
                credits: 300,
                price: 120000,
                formatted_price: 'Rp 120.000',
                badge: 'Paling Hemat',
                description: 'Photoshoot katalog lengkap, multi-variasi rasio, & seasonal campaign.',
            },
            {
                code: 'sultan',
                name: 'Sultan Pack',
                credits: 1000,
                price: 350000,
                formatted_price: 'Rp 350.000',
                badge: 'Nilai Terbaik',
                description: 'Kapasitas maksimal untuk studio foto produk e-commerce & agensi.',
            }
        ],

        paymentChannels: [
            { code: 'qris', name: 'QRIS Dinamis', is_default: true },
            { code: 'bcava', name: 'BCA VA', is_default: false },
            { code: 'briva', name: 'BRI VA', is_default: false },
            { code: 'mandiriva', name: 'Mandiri VA', is_default: false },
        ],

        get selectedPackagePrice() {
            const pkg = this.packages.find(p => p.code === this.selectedTierCode);
            return pkg ? pkg.formatted_price : (this.packages[0]?.formatted_price || 'Rp 25.000');
        },

        selectPackage(pkg) {
            this.selectedTierCode = pkg.code;
            this.selectedPackageId = pkg.id || null;
        },

        init() {
            this.fetchPackages();
        },

        async fetchPackages() {
            try {
                const res = await fetch('{{ route("ai-studio.topup.packages") }}');
                const data = await res.json();
                if (data.success) {
                    if (data.packages && data.packages.length > 0) {
                        this.packages = data.packages;
                        const found = this.packages.find(p => p.code === this.selectedTierCode);
                        if (found) {
                            this.selectedPackageId = found.id || null;
                        } else {
                            this.selectedTierCode = this.packages[0].code;
                            this.selectedPackageId = this.packages[0].id || null;
                        }
                    }
                    if (data.payment_channels && data.payment_channels.length > 0) {
                        this.paymentChannels = data.paymentChannels || data.payment_channels;
                    }
                    if (typeof data.credits_balance !== 'undefined') {
                        this.currentCredits = data.credits_balance;
                    }
                }
            } catch (e) {
                // Keep default fallback packages
            }
        },

        openModal(detail = {}) {
            this.isOpen = true;
            this.step = 'select';
            if (detail.message) {
                this.shortageTitle = detail.required ? `Dibutuhkan ${detail.required} Kredit` : 'Kredit Bulanan Telah Habis';
                this.shortageMessage = detail.message;
            } else {
                this.shortageTitle = '';
                this.shortageMessage = '';
            }
            if (typeof detail.available !== 'undefined') {
                this.currentCredits = detail.available;
            }
            this.fetchPackages();
            this.$nextTick(() => lucide.createIcons());
        },

        closeModal() {
            this.isOpen = false;
            this.stopPolling();
            this.stopCountdown();
        },

        async submitOrder() {
            this.submitting = true;

            try {
                const selectedPkg = this.packages.find(p => p.code === this.selectedTierCode);
                const payload = {
                    tier_code: this.selectedTierCode,
                    package_id: this.selectedPackageId || (selectedPkg ? selectedPkg.id : null),
                    payment_method: this.selectedPaymentMethod,
                };

                const res = await fetch('{{ route("ai-studio.topup.order") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal membuat tagihan pembayaran.');
                }

                this.activeOrder = data.payment;
                this.step = 'payment';
                this.startCountdown();
                this.startPolling(data.payment.status_url);
                this.$nextTick(() => lucide.createIcons());
            } catch (e) {
                alert('Gagal: ' + e.message);
            } finally {
                this.submitting = false;
            }
        },

        startPolling(statusUrl) {
            this.stopPolling();
            this.pollingInterval = setInterval(async () => {
                try {
                    const res = await fetch(statusUrl);
                    const data = await res.json();

                    if (data.success && (data.is_paid || data.status === 'approved')) {
                        this.stopPolling();
                        this.stopCountdown();
                        this.currentCredits = data.credits_balance;
                        this.step = 'success';

                        // Dispatch global event so page components update credit counter immediately
                        window.dispatchEvent(new CustomEvent('ai-credits-updated', {
                            detail: { credits: data.credits_balance }
                        }));
                        this.$nextTick(() => lucide.createIcons());
                    }
                } catch (e) {
                    console.error('Polling error:', e);
                }
            }, 3000);
        },

        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        },

        startCountdown() {
            this.stopCountdown();
            this.countdownSeconds = 900;
            this.updateCountdownText();
            this.countdownInterval = setInterval(() => {
                this.countdownSeconds--;
                if (this.countdownSeconds <= 0) {
                    this.stopCountdown();
                    this.stopPolling();
                    this.countdownText = 'Kadaluarsa';
                } else {
                    this.updateCountdownText();
                }
            }, 1000);
        },

        stopCountdown() {
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
            }
        },

        updateCountdownText() {
            const m = Math.floor(this.countdownSeconds / 60);
            const s = this.countdownSeconds % 60;
            this.countdownText = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        },

        copyPayCode() {
            if (!this.activeOrder?.pay_code) return;
            navigator.clipboard.writeText(this.activeOrder.pay_code).then(() => {
                this.copiedCode = true;
                setTimeout(() => { this.copiedCode = false; }, 2000);
            });
        }
    };
}
</script>
