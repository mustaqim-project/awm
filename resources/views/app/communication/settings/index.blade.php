@extends('layouts.app', [
    'title' => __('communication.settings_title'),
    'headerTitle' => __('communication.module_title'),
    'headerSubtitle' => __('communication.settings_subtitle')
])

@section('content')
<div class="max-w-[1200px] mx-auto space-y-6 pb-20" x-data="{
    activeChannel: 'default',
    testModalOpen: false,
    testChannel: 'default',
    testRecipient: '{{ auth()->user()->email ?? '' }}',
    isTesting: false,
    testFeedback: null,
    showMailPassword: false,
    showImapPassword: false,
    openTestModal(channel) {
        this.testChannel = channel;
        this.testFeedback = null;
        this.testModalOpen = true;
    },
    async runTestEmail() {
        if (!this.testRecipient) return;
        this.isTesting = true;
        this.testFeedback = null;
        try {
            let res = await fetch('{{ route('communication.settings.smtp.test') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    channel_type: this.testChannel,
                    recipient_email: this.testRecipient
                })
            });
            let data = await res.json();
            this.testFeedback = data;
        } catch (err) {
            this.testFeedback = { success: false, message: 'Gagal menghubungi server: ' + err.message };
        } finally {
            this.isTesting = false;
        }
    }
}">
    <!-- 1. NAVIGATION TABS -->
    @include('app.communication.partials.nav-tabs')

    <!-- 2. CHANNEL SELECTOR CARDS (Bento Apple HIG) -->
    @php
        $channelsMeta = [
            'default' => [
                'title' => __('communication.channel_default'),
                'desc' => __('communication.channel_default_desc'),
                'account' => $defaultAccount,
                'icon' => 'globe',
                'badge' => 'Master / Fallback'
            ],
            'quotation' => [
                'title' => __('communication.channel_quotation'),
                'desc' => __('communication.channel_quotation_desc'),
                'account' => $quotationAccount,
                'icon' => 'file-text',
                'badge' => 'Sales'
            ],
            'invoice' => [
                'title' => __('communication.channel_invoice'),
                'desc' => __('communication.channel_invoice_desc'),
                'account' => $invoiceAccount,
                'icon' => 'receipt',
                'badge' => 'Finance'
            ],
            'purchase_order' => [
                'title' => __('communication.channel_purchase_order'),
                'desc' => __('communication.channel_purchase_order_desc'),
                'account' => $poAccount,
                'icon' => 'shopping-cart',
                'badge' => 'Purchasing'
            ],
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        @foreach($channelsMeta as $key => $meta)
            @php $acc = $meta['account']; @endphp
            <button type="button"
                    @click="activeChannel = '{{ $key }}'"
                    class="p-4 rounded-[16px] text-left transition-all border flex flex-col justify-between gap-3 relative cursor-pointer"
                    :class="activeChannel === '{{ $key }}'
                        ? 'bg-white dark:bg-[#1C1C1E] border-[#007AFF] shadow-[0_4px_12px_rgba(0,122,255,0.12)] ring-2 ring-[#007AFF]/20'
                        : 'bg-white/60 dark:bg-[#1C1C1E]/60 border-black/5 dark:border-white/5 hover:bg-white dark:hover:bg-[#1C1C1E]'">
                <div class="flex items-start justify-between gap-2">
                    <span class="w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0"
                          :class="activeChannel === '{{ $key }}' ? 'bg-[#007AFF] text-white' : 'bg-black/5 dark:bg-white/10 text-black/70 dark:text-white/70'">
                        <i data-lucide="{{ $meta['icon'] }}" class="w-4 h-4"></i>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wide"
                          :class="'{{ $acc ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/5 dark:bg-white/5 text-black/40 dark:text-white/40' }}'">
                        {{ $acc ? __('communication.status_configured') : __('communication.status_not_configured') }}
                    </span>
                </div>

                <div>
                    <div class="text-[14px] font-semibold text-black dark:text-white leading-tight">
                        {{ $meta['title'] }}
                    </div>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-1 line-clamp-2 leading-relaxed">
                        {{ $meta['desc'] }}
                    </p>
                </div>

                <div class="pt-2 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] text-black/60 dark:text-white/60">
                    <span class="font-mono truncate max-w-[140px]">
                        {{ $acc?->from_address ?? ($key !== 'default' ? 'Mengikuti default' : '-') }}
                    </span>
                    <span class="text-[10px] font-semibold text-[#007AFF] uppercase">{{ $meta['badge'] }}</span>
                </div>
            </button>
        @endforeach
    </div>

    <!-- 3. CONFIGURATION FORMS PER CHANNEL -->
    @foreach($channelsMeta as $key => $meta)
        @php
            $acc = $meta['account'];
            $isOverride = ($key !== 'default');
        @endphp
        <div x-show="activeChannel === '{{ $key }}'" x-transition.opacity.duration.200ms class="space-y-6">
            @if($isOverride && ! $acc)
                <div class="rounded-[14px] p-4 bg-[#007AFF]/8 dark:bg-[#007AFF]/15 border border-[#007AFF]/20 text-[#007AFF] dark:text-[#0A84FF] flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <div class="text-[13px] leading-relaxed">
                        <strong class="font-semibold">{{ __('communication.channel_default') }} Aktif:</strong>
                        {{ __('communication.using_default_smtp') }}
                    </div>
                </div>
            @endif

            <form action="{{ route('communication.settings.smtp.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="channel_type" value="{{ $key }}">

                <!-- SMTP Host & Credentials Bento Card -->
                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8 space-y-6 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-black/5 dark:border-white/10 pb-4">
                        <div>
                            <h2 class="text-[17px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                                <i data-lucide="mail" class="w-4 h-4 text-[#007AFF]"></i>
                                <span>{{ $meta['title'] }}</span>
                            </h2>
                            <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">{{ $meta['desc'] }}</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ ($acc?->is_active ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-black/10 peer-focus:outline-none rounded-full peer dark:bg-white/10 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                                <span class="ms-2.5 text-[13px] font-medium text-black/80 dark:text-white/80">{{ __('communication.is_active_label') }}</span>
                            </label>
                            @if($acc)
                                <button type="button"
                                        @click="openTestModal('{{ $key }}')"
                                        class="h-9 px-3.5 rounded-[10px] text-[12px] font-medium text-[#007AFF] bg-[#007AFF]/10 hover:bg-[#007AFF]/15 transition-all flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                    <span>{{ __('communication.test_connection') }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <!-- Host -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.mail_host') }} *</label>
                            <input type="text" name="mail_host" value="{{ old('mail_host', $acc?->mail_host ?? 'smtp.gmail.com') }}" required placeholder="smtp.gmail.com"
                                   class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- Port -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.mail_port') }} *</label>
                            <input type="number" name="mail_port" value="{{ old('mail_port', $acc?->mail_port ?? 587) }}" required placeholder="587"
                                   class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- Encryption -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.mail_encryption') }}</label>
                            <select name="mail_encryption" class="w-full h-11 px-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <option value="tls" {{ old('mail_encryption', $acc?->mail_encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                <option value="ssl" {{ old('mail_encryption', $acc?->mail_encryption) === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                <option value="null" {{ old('mail_encryption', $acc?->mail_encryption) === null ? 'selected' : '' }}>None (Port 25)</option>
                            </select>
                        </div>

                        <!-- Username -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.mail_username') }} *</label>
                            <input type="text" name="mail_username" value="{{ old('mail_username', $acc?->mail_username) }}" required placeholder="user@domain.com"
                                   class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.mail_password') }}</label>
                            <div class="relative">
                                <input :type="showMailPassword ? 'text' : 'password'" name="mail_password" placeholder="{{ $acc ? '••••••••••••' : 'Password / App Password' }}"
                                       class="w-full h-11 px-3.5 pr-10 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <button type="button" @click="showMailPassword = !showMailPassword" class="absolute right-3 top-3 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                                    <i data-lucide="eye" class="w-4 h-4" x-show="!showMailPassword"></i>
                                    <i data-lucide="eye-off" class="w-4 h-4" x-show="showMailPassword"></i>
                                </button>
                            </div>
                            <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">{{ __('communication.mail_password_help') }}</p>
                        </div>

                        <!-- From Address -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.from_address') }} *</label>
                            <input type="email" name="from_address" value="{{ old('from_address', $acc?->from_address ?? $business->email) }}" required placeholder="billing@domain.com"
                                   class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- From Name -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.from_name') }} *</label>
                            <input type="text" name="from_name" value="{{ old('from_name', $acc?->from_name ?? $business->name) }}" required placeholder="Nama Bisnis Anda"
                                   class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- Reply-To -->
                        <div class="sm:col-span-2">
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.reply_to_address') }} (Opsional)</label>
                            <input type="email" name="reply_to_address" value="{{ old('reply_to_address', $acc?->reply_to_address) }}" placeholder="support@domain.com"
                                   class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>
                    </div>
                </div>

                <!-- Inbound IMAP Detection Bento Card -->
                <div class="rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-6 sm:p-8 space-y-6 shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
                    <div class="border-b border-black/5 dark:border-white/10 pb-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                                    <i data-lucide="inbox" class="w-4 h-4 text-[#34C759]"></i>
                                    <span>{{ __('communication.enable_inbound_reading') }}</span>
                                </h3>
                                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">{{ __('communication.inbound_help') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_inbound_reading" value="1" class="sr-only peer" {{ ($acc?->enable_inbound_reading ?? false) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-black/10 peer-focus:outline-none rounded-full peer dark:bg-white/10 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        <!-- IMAP Host -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.imap_host') }}</label>
                            <input type="text" name="imap_host" value="{{ old('imap_host', $acc?->imap_host ?? 'imap.gmail.com') }}" placeholder="imap.gmail.com"
                                   class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- IMAP Port -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.imap_port') }}</label>
                            <input type="number" name="imap_port" value="{{ old('imap_port', $acc?->imap_port ?? 993) }}" placeholder="993"
                                   class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- IMAP Username -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.imap_username') }}</label>
                            <input type="text" name="imap_username" value="{{ old('imap_username', $acc?->imap_username ?? $acc?->mail_username) }}" placeholder="user@domain.com"
                                   class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                        </div>

                        <!-- IMAP Password -->
                        <div>
                            <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.imap_password') }}</label>
                            <div class="relative">
                                <input :type="showImapPassword ? 'text' : 'password'" name="imap_password" placeholder="{{ $acc?->imap_password ? '••••••••••••' : 'Password / App Password' }}"
                                       class="w-full h-11 px-3.5 pr-10 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                                <button type="button" @click="showImapPassword = !showImapPassword" class="absolute right-3 top-3 text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                                    <i data-lucide="eye" class="w-4 h-4" x-show="!showImapPassword"></i>
                                    <i data-lucide="eye-off" class="w-4 h-4" x-show="showImapPassword"></i>
                                </button>
                            </div>
                            <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">{{ __('communication.imap_password_help') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit" class="h-11 px-6 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-[0_2px_8px_rgba(0,122,255,0.25)]">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>{{ __('communication.save_smtp') }}</span>
                    </button>
                </div>
            </form>
        </div>
    @endforeach

    <!-- 4. MODAL UJI COBA KONEKSI SMTP -->
    <div x-show="testModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="testModalOpen = false"></div>
        <div class="relative w-full max-w-[480px] rounded-[20px] bg-white dark:bg-[#1C1C1E] p-6 shadow-2xl border border-black/10 dark:border-white/10 space-y-5" @click.stop>
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-[17px] font-bold text-black dark:text-white tracking-tight flex items-center gap-2">
                        <i data-lucide="send" class="w-4 h-4 text-[#007AFF]"></i>
                        <span>{{ __('communication.test_connection') }}</span>
                    </h3>
                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-0.5">
                        Saluran: <strong class="text-black/80 dark:text-white/80 uppercase" x-text="testChannel"></strong>
                    </p>
                </div>
                <button type="button" @click="testModalOpen = false" class="text-black/40 dark:text-white/40 hover:text-black dark:hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">{{ __('communication.test_recipient') }} *</label>
                    <input type="email" x-model="testRecipient" required placeholder="nama@domain.com"
                           class="w-full h-11 px-3.5 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]">
                </div>

                <div x-show="testFeedback" class="p-3.5 rounded-[10px] text-[13px]"
                     :class="testFeedback?.success ? 'bg-[#34C759]/12 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]'">
                    <p class="font-medium" x-text="testFeedback?.message"></p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" @click="testModalOpen = false" class="h-10 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition-all">
                    {{ __('communication.cancel') }}
                </button>
                <button type="button" @click="runTestEmail()" :disabled="isTesting" class="h-10 px-5 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] transition-all flex items-center gap-2 shadow-[0_2px_6px_rgba(0,122,255,0.25)] disabled:opacity-50">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin" x-show="isTesting"></i>
                    <i data-lucide="send" class="w-4 h-4" x-show="!isTesting"></i>
                    <span x-text="isTesting ? '{{ __('communication.testing') }}' : '{{ __('communication.send_now') }}'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
