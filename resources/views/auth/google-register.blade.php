@extends('layouts.public_marketing', ['title' => 'Lengkapi Pendaftaran Google - COOCA', 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-12rem)] py-8 sm:py-12 lg:py-16 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl mx-auto space-y-6 sm:space-y-8"
            x-data="googleRegisterForm()">

            <!-- Official COOCA Branding & Header -->
            <div class="text-center space-y-3">
                @include('auth.partials.brand-logo', ['class' => ''])
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Lengkapi Pendaftaran Bisnis
                </h1>
                
                <div class="flex flex-wrap items-center justify-center gap-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                    <span>Akun Google terhubung:</span>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white dark:bg-white/10 border border-slate-200/80 dark:border-white/10 text-slate-900 dark:text-white font-medium shadow-xs">
                        @if (!empty($pending['avatar']))
                            <img src="{{ $pending['avatar'] }}" alt="{{ $pending['name'] ?? 'User' }}" class="w-5 h-5 rounded-full object-cover">
                        @else
                            <div class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[10px] font-bold flex items-center justify-center">
                                {{ strtoupper(substr($pending['name'] ?? $pending['email'] ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        <span class="font-semibold text-xs sm:text-[13px]">{{ $pending['email'] }}</span>
                        <span class="inline-flex items-center text-[10px] font-semibold text-[#248A3D] dark:text-[#30D158] bg-[#34C759]/15 px-1.5 py-0.5 rounded-full">
                            <i data-lucide="check" class="w-3 h-3 mr-0.5"></i>
                            Terverifikasi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Error Notification Banner (Fallback server validation) -->
            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-xs sm:text-sm shadow-xs">
                    <div class="font-semibold mb-1.5 flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span>Mohon periksa kembali formulir berikut:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs opacity-90 pl-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Main Single-Column Focused Register Card -->
            <div class="bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.08] rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-sm dark:shadow-2xl dark:shadow-black/40">
                <form method="POST" action="{{ route('register.google.submit') }}" id="register-google-form" @submit="handleSubmit($event)" class="space-y-4 sm:space-y-5">
                    @csrf

                    <div class="flex items-center gap-3 pb-3 border-b border-black/[0.06] dark:border-white/[0.08]">
                        <div class="w-9 h-9 rounded-xl bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="store" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white leading-tight">Profil Usaha Baru</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Atur nama toko dan WhatsApp penerima OTP</p>
                        </div>
                    </div>

                    <!-- Input Nama Usaha (Realtime Validation) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="business_name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Nama Usaha / Perusahaan <span class="text-[#FF3B30]">*</span>
                            </label>
                            <span x-show="businessStatus === 'checking'" class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF] inline-flex items-center gap-1">
                                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                Memeriksa...
                            </span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="store" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="business_name" id="business_name"
                                x-model="businessName"
                                @input="onBusinessInput"
                                required autofocus
                                class="w-full pl-10 pr-10 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                :class="{
                                    'border-[#34C759] focus:border-[#34C759] focus:ring-4 focus:ring-[#34C759]/15': businessStatus === 'available',
                                    'border-[#FF3B30] focus:border-[#FF3B30] focus:ring-4 focus:ring-[#FF3B30]/15': businessStatus === 'unavailable' || businessStatus === 'invalid',
                                    'border-[#007AFF] focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15': businessStatus === 'checking',
                                    'border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15': businessStatus === 'idle'
                                }"
                                placeholder="Contoh: Kedai Kopi Sukses / CV Berkah">

                            <!-- Status Icon (Kanan Input) -->
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                                <template x-if="businessStatus === 'checking'">
                                    <svg class="w-4 h-4 text-[#007AFF] animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </template>
                                <template x-if="businessStatus === 'available'">
                                    <div class="w-5 h-5 rounded-full bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </template>
                                <template x-if="businessStatus === 'unavailable' || businessStatus === 'invalid'">
                                    <div class="w-5 h-5 rounded-full bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Realtime Status Message -->
                        <div x-show="businessMessage" x-transition.opacity.duration.200ms
                            class="mt-1.5 flex items-center gap-1.5 text-xs font-medium"
                            :class="{
                                'text-[#34C759] dark:text-[#30D158]': businessStatus === 'available',
                                'text-[#FF3B30] dark:text-[#FF453A]': businessStatus === 'unavailable' || businessStatus === 'invalid',
                                'text-slate-500 dark:text-slate-400': businessStatus === 'checking'
                            }">
                            <template x-if="businessStatus === 'available'">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="businessStatus === 'unavailable' || businessStatus === 'invalid'">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </template>
                            <span x-text="businessMessage"></span>
                        </div>
                    </div>

                    <!-- Input Nomor WhatsApp (Realtime Validation) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="phone" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Nomor WhatsApp Pemilik <span class="text-[#FF3B30]">*</span>
                            </label>
                            <span x-show="phoneStatus === 'checking'" class="text-[11px] font-medium text-[#007AFF] dark:text-[#0A84FF] inline-flex items-center gap-1">
                                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                Memeriksa...
                            </span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <i data-lucide="phone" class="w-4 h-4"></i>
                            </div>
                            <input type="tel" name="phone" id="phone"
                                x-model="phone"
                                @input="onPhoneInput"
                                required inputmode="tel" autocomplete="tel"
                                class="w-full pl-10 pr-10 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border rounded-xl text-[16px] sm:text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 transition-all outline-none"
                                :class="{
                                    'border-[#34C759] focus:border-[#34C759] focus:ring-4 focus:ring-[#34C759]/15': phoneStatus === 'available',
                                    'border-[#FF3B30] focus:border-[#FF3B30] focus:ring-4 focus:ring-[#FF3B30]/15': phoneStatus === 'unavailable' || phoneStatus === 'invalid',
                                    'border-[#007AFF] focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15': phoneStatus === 'checking',
                                    'border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15': phoneStatus === 'idle'
                                }"
                                placeholder="081234567890">

                            <!-- Status Icon (Kanan Input) -->
                            <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none">
                                <template x-if="phoneStatus === 'checking'">
                                    <svg class="w-4 h-4 text-[#007AFF] animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </template>
                                <template x-if="phoneStatus === 'available'">
                                    <div class="w-5 h-5 rounded-full bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158] flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </template>
                                <template x-if="phoneStatus === 'unavailable' || phoneStatus === 'invalid'">
                                    <div class="w-5 h-5 rounded-full bg-[#FF3B30]/15 text-[#FF3B30] dark:text-[#FF453A] flex items-center justify-center">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Realtime Status Message / Hint -->
                        <div x-show="phoneMessage" x-transition.opacity.duration.200ms
                            class="mt-1.5 flex items-center gap-1.5 text-xs font-medium"
                            :class="{
                                'text-[#34C759] dark:text-[#30D158]': phoneStatus === 'available',
                                'text-[#FF3B30] dark:text-[#FF453A]': phoneStatus === 'unavailable' || phoneStatus === 'invalid',
                                'text-slate-500 dark:text-slate-400': phoneStatus === 'checking'
                            }">
                            <template x-if="phoneStatus === 'available'">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="phoneStatus === 'unavailable' || phoneStatus === 'invalid'">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </template>
                            <span x-text="phoneMessage"></span>
                        </div>
                        <p x-show="!phoneMessage" class="mt-1.5 text-[11px] text-slate-500 dark:text-slate-400 leading-snug">
                            Kode OTP aktivasi akun akan dikirim ke WhatsApp ini.
                        </p>
                    </div>

                    <!-- Pilihan Kategori Bisnis (Preset Sederhana) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="template_code" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                                Kategori / Bidang Usaha
                            </label>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">Opsional</span>
                        </div>
                        <select name="template_code" id="template_code" x-model="selectedTemplate"
                            class="w-full px-3.5 py-2.5 sm:py-3 bg-slate-50 dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 rounded-xl text-[16px] sm:text-sm font-medium text-slate-900 dark:text-white transition-all outline-none cursor-pointer">
                            <option value="">-- Bisnis Umum (Semua Modul Standar) --</option>
                            @foreach ($templates as $tmpl)
                                <option value="{{ $tmpl->code }}" {{ old('template_code') == $tmpl->code ? 'selected' : '' }}>
                                    {{ $tmpl->name }} ({{ strtoupper($tmpl->industry_category) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Skala & Model Operasional Bisnis (Grid Kiri Kanan Baik Mobile Maupun Desktop) -->
                    <div class="space-y-2 pt-1">
                        <label class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Skala &amp; Model Operasional Bisnis <span class="text-[#FF3B30]">*</span>
                        </label>

                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3.5">
                            <!-- Kartu Kiri: UMKM -->
                            <label class="relative flex flex-col justify-between p-3 sm:p-4 rounded-xl sm:rounded-2xl border cursor-pointer transition-all active:scale-[0.98]"
                                :class="businessScale === 'umkm' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'bg-slate-50 dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                <input type="radio" name="business_scale" value="umkm" x-model="businessScale" class="sr-only">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-2">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-[#34C759]/12 text-[#34C759] dark:text-[#30D158] flex items-center justify-center shrink-0">
                                            <i data-lucide="store" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                        </div>
                                        <span x-show="businessScale === 'umkm'" class="text-[9.5px] sm:text-[10px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full">Pilihan</span>
                                    </div>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-tight">UMKM &amp; Toko Mandiri</h4>
                                    <p class="text-[10.5px] sm:text-[11.5px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">
                                        Warung, kafe, butik, bengkel, 1–3 cabang.
                                    </p>
                                </div>
                            </label>

                            <!-- Kartu Kanan: Multi-Cabang / Korporasi -->
                            <label class="relative flex flex-col justify-between p-3 sm:p-4 rounded-xl sm:rounded-2xl border cursor-pointer transition-all active:scale-[0.98]"
                                :class="businessScale === 'corporate' ? 'bg-[#007AFF]/5 border-[#007AFF] shadow-xs ring-1 ring-[#007AFF]/30' : 'bg-slate-50 dark:bg-[#2C2C2E] border-black/10 dark:border-white/10 hover:border-slate-300 dark:hover:border-white/20'">
                                <input type="radio" name="business_scale" value="corporate" x-model="businessScale" class="sr-only">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-2">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-[#007AFF]/12 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                                            <i data-lucide="building-2" class="w-3.5 h-3.5 sm:w-4 sm:h-4"></i>
                                        </div>
                                        <span x-show="businessScale === 'corporate'" class="text-[9.5px] sm:text-[10px] font-bold text-[#007AFF] dark:text-[#0A84FF] bg-[#007AFF]/10 dark:bg-[#0A84FF]/15 px-2 py-0.5 rounded-full">Pilihan</span>
                                    </div>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white leading-tight">Korporasi &amp; Multi-Cabang</h4>
                                    <p class="text-[10.5px] sm:text-[11.5px] text-slate-500 dark:text-slate-400 mt-1 leading-snug">
                                        Distributor, waralaba, &amp; multi-gudang.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit"
                            :disabled="isSubmitDisabled()"
                            :class="isSubmitDisabled() ? 'opacity-50 cursor-not-allowed filter grayscale-[25%]' : 'hover:from-[#1cd0ff] hover:to-[#006fe6] cursor-pointer shadow-[0_2px_12px_rgba(0,194,255,0.3)] hover:shadow-[0_4px_20px_rgba(0,194,255,0.5)] active:scale-[0.99]'"
                            class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-gradient-to-r from-[#00C2FF] via-[#00A3FF] to-[#007AFF] text-white font-bold text-sm sm:text-[15px] min-h-[50px] transition-all focus:outline-none focus:ring-4 focus:ring-[#007AFF]/25">
                            <span x-show="!submitting" class="inline-flex items-center gap-2">
                                <span>Lanjutkan &amp; Kirim OTP WhatsApp</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </span>
                            <span x-show="submitting" class="inline-flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                                <span>Memverifikasi data &amp; Mengirim OTP...</span>
                            </span>
                        </button>
                    </div>

                    <!-- Cancel Link -->
                    <div class="text-center pt-2 border-t border-black/[0.04] dark:border-white/[0.06] text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        <a href="{{ route('login') }}" class="font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 rounded">
                            Batalkan dan kembali ke halaman masuk
                        </a>
                    </div>
                </form>
            </div>

            <!-- Trust & Privacy Security Callout -->
            <div class="text-center space-y-1 text-slate-500 dark:text-slate-400 text-xs">
                <div class="inline-flex items-center gap-1.5 font-medium text-[#34C759]">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    <span>Data usaha Anda terisolasi privat &amp; terenkripsi aman</span>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                    Dengan mendaftar, Anda menyetujui Ketentuan Layanan &amp; Kebijakan Privasi COOCA.
                </p>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function googleRegisterForm() {
            return {
                checkUrl: '{{ route('register.check-availability') }}',
                businessName: @json(old('business_name', 'Usaha ' . ($pending['name'] ?? 'Saya'))),
                phone: @json(old('phone', '')),
                businessScale: @json(old('business_scale', 'umkm')),
                selectedTemplate: @json(old('template_code', '')),

                businessStatus: 'idle', // 'idle' | 'checking' | 'available' | 'unavailable' | 'invalid'
                businessMessage: '',

                phoneStatus: 'idle', // 'idle' | 'checking' | 'available' | 'unavailable' | 'invalid'
                phoneMessage: '',

                submitting: false,
                _bizTimer: null,
                _phoneTimer: null,

                init() {
                    if (this.businessName && this.businessName.trim().length >= 2) {
                        this.checkBusinessName();
                    }
                    if (this.phone && this.phone.trim().length >= 8) {
                        this.checkPhone();
                    }
                },

                onBusinessInput() {
                    clearTimeout(this._bizTimer);
                    const val = this.businessName.trim();
                    if (val === '') {
                        this.businessStatus = 'invalid';
                        this.businessMessage = 'Nama usaha wajib diisi.';
                        return;
                    }
                    if (val.length < 3) {
                        this.businessStatus = 'invalid';
                        this.businessMessage = 'Nama usaha minimal 3 karakter.';
                        return;
                    }

                    this.businessStatus = 'checking';
                    this.businessMessage = 'Memeriksa ketersediaan nama usaha...';

                    this._bizTimer = setTimeout(() => {
                        this.checkBusinessName();
                    }, 350);
                },

                async checkBusinessName() {
                    const val = this.businessName.trim();
                    if (val.length < 3) return;

                    this.businessStatus = 'checking';
                    try {
                        const res = await fetch(`${this.checkUrl}?business_name=${encodeURIComponent(val)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.business_name) {
                            this.businessStatus = data.business_name.status;
                            this.businessMessage = data.business_name.message;
                        }
                    } catch (e) {
                        this.businessStatus = 'idle';
                        this.businessMessage = '';
                    }
                },

                onPhoneInput() {
                    clearTimeout(this._phoneTimer);
                    const val = this.phone.trim();
                    if (val === '') {
                        this.phoneStatus = 'invalid';
                        this.phoneMessage = 'Nomor WhatsApp wajib diisi.';
                        return;
                    }
                    const digits = val.replace(/\D/g, '');
                    if (digits.length < 9) {
                        this.phoneStatus = 'invalid';
                        this.phoneMessage = 'Nomor WhatsApp terlalu pendek (minimal 10 digit).';
                        return;
                    }

                    this.phoneStatus = 'checking';
                    this.phoneMessage = 'Memeriksa nomor WhatsApp...';

                    this._phoneTimer = setTimeout(() => {
                        this.checkPhone();
                    }, 350);
                },

                async checkPhone() {
                    const val = this.phone.trim();
                    const digits = val.replace(/\D/g, '');
                    if (digits.length < 9) return;

                    this.phoneStatus = 'checking';
                    try {
                        const res = await fetch(`${this.checkUrl}?phone=${encodeURIComponent(val)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.phone) {
                            this.phoneStatus = data.phone.status;
                            this.phoneMessage = data.phone.message;
                        }
                    } catch (e) {
                        this.phoneStatus = 'idle';
                        this.phoneMessage = '';
                    }
                },

                isSubmitDisabled() {
                    if (this.submitting) return true;
                    if (this.businessStatus === 'checking' || this.phoneStatus === 'checking') return true;
                    if (this.businessStatus === 'unavailable' || this.businessStatus === 'invalid') return true;
                    if (this.phoneStatus === 'unavailable' || this.phoneStatus === 'invalid') return true;
                    if (this.businessName.trim().length < 3) return true;
                    if (this.phone.trim().replace(/\D/g, '').length < 9) return true;
                    return false;
                },

                handleSubmit(e) {
                    if (this.isSubmitDisabled()) {
                        e.preventDefault();
                        return;
                    }
                    this.submitting = true;
                }
            };
        }
    </script>
@endpush
