@extends('layouts.public_marketing', ['title' => __('auth.recovery.create_meta_title'), 'noindex' => true])

@section('content')
    <div class="min-h-[calc(100vh-16rem)] flex flex-col justify-center py-10 sm:py-16 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
            <!-- Official COOCA Branding & Header -->
            <div class="text-center mb-8">
                @include('auth.partials.brand-logo', ['class' => 'mb-5'])
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ __('auth.recovery.create_heading') }}</h1>
                <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-lg mx-auto">
                    {{ __('auth.recovery.create_subheading') }}
                </p>
            </div>

            <!-- Main Auth Card -->
            <div
                class="bg-white dark:bg-[#151B2B] border border-slate-200/80 dark:border-white/10 rounded-2xl sm:rounded-3xl p-6 sm:p-9 shadow-sm dark:shadow-2xl dark:shadow-black/40 transition-all">
                @if ($errors->any())
                    <div id="error-summary-box"
                        class="mb-6 p-4 rounded-[20px] bg-[#FF3B30]/10 border border-[#FF3B30]/25 text-[#FF3B30] dark:text-[#FF453A] text-sm animate-shake">
                        <div class="flex items-center gap-2 font-bold mb-2">
                            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
                            <span>{{ __('auth.recovery.error_summary_title', ['count' => $errors->count()]) }}</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs mb-3 pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <div
                            class="p-3 rounded-[14px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-xs flex items-start gap-2">
                            <i data-lucide="info" class="w-4 h-4 shrink-0 mt-0.5"></i>
                            <span><strong>{{ __('auth.recovery.file_notice_title') }}</strong> {{ __('auth.recovery.file_notice_desc') }}</span>
                        </div>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const el = document.getElementById('error-summary-box');
                            if (el) el.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        });
                    </script>
                @endif

                <form method="POST" action="{{ route('account-recovery.store') }}" enctype="multipart/form-data"
                    class="space-y-6" x-data="{
                        issueType: '{{ old('issue_type', 'both') }}',
                        submitting: false,
                        ktpPreview: null,
                        bizPreview: null,
                        selfiePreview: null,
                        handleFilePreview(e, target) {
                            const file = e.target.files[0];
                            if (!file) return;
                            if (file.type.startsWith('image/')) {
                                const reader = new FileReader();
                                reader.onload = (event) => {
                                    this[target] = event.target.result;
                                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                                };
                                reader.readAsDataURL(file);
                            } else {
                                this[target] = 'pdf';
                                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                            }
                        }
                    }" @submit="submitting = true">
                    @csrf

                    <!-- 1. Pilihan Kendala Bento Grid -->
                    <div>
                        <label
                            class="block text-xs sm:text-sm font-bold text-black/80 dark:text-white/85 uppercase tracking-wider mb-3">
                            {{ __('auth.recovery.section_1_title') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label
                                class="relative flex flex-col p-4 rounded-[18px] border cursor-pointer transition-all text-left"
                                :class="issueType === 'phone_lost' ?
                                    'bg-[#007AFF]/10 border-[#007AFF] ring-2 ring-[#007AFF]/30 text-black dark:text-white shadow-sm' :
                                    'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08] text-black/60 dark:text-white/60 hover:border-black/20 dark:hover:border-white/20'">
                                <input type="radio" name="issue_type" value="phone_lost" x-model="issueType"
                                    class="sr-only">
                                <div
                                    class="flex items-center gap-2 mb-1.5 font-bold text-xs sm:text-sm text-black dark:text-white">
                                    <i data-lucide="smartphone" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                                    <span>{{ __('auth.recovery.issue_phone_lost_title') }}</span>
                                </div>
                                <span class="text-xs leading-relaxed opacity-80">{{ __('auth.recovery.issue_phone_lost_desc') }}</span>
                            </label>

                            <label
                                class="relative flex flex-col p-4 rounded-[18px] border cursor-pointer transition-all text-left"
                                :class="issueType === 'email_inaccessible' ?
                                    'bg-[#007AFF]/10 border-[#007AFF] ring-2 ring-[#007AFF]/30 text-black dark:text-white shadow-sm' :
                                    'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08] text-black/60 dark:text-white/60 hover:border-black/20 dark:hover:border-white/20'">
                                <input type="radio" name="issue_type" value="email_inaccessible" x-model="issueType"
                                    class="sr-only">
                                <div
                                    class="flex items-center gap-2 mb-1.5 font-bold text-xs sm:text-sm text-black dark:text-white">
                                    <i data-lucide="mail-x" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                                    <span>{{ __('auth.recovery.issue_email_inaccessible_title') }}</span>
                                </div>
                                <span class="text-xs leading-relaxed opacity-80">{{ __('auth.recovery.issue_email_inaccessible_desc') }}</span>
                            </label>

                            <label
                                class="relative flex flex-col p-4 rounded-[18px] border cursor-pointer transition-all text-left"
                                :class="issueType === 'both' ?
                                    'bg-[#007AFF]/10 border-[#007AFF] ring-2 ring-[#007AFF]/30 text-black dark:text-white shadow-sm' :
                                    'bg-black/[0.02] dark:bg-white/[0.03] border-black/[0.06] dark:border-white/[0.08] text-black/60 dark:text-white/60 hover:border-black/20 dark:hover:border-white/20'">
                                <input type="radio" name="issue_type" value="both" x-model="issueType" class="sr-only">
                                <div
                                    class="flex items-center gap-2 mb-1.5 font-bold text-xs sm:text-sm text-black dark:text-white">
                                    <i data-lucide="shield-alert" class="w-4 h-4 text-[#FF9500] dark:text-[#FF9F0A]"></i>
                                    <span>{{ __('auth.recovery.issue_both_title') }}</span>
                                </div>
                                <span class="text-xs leading-relaxed opacity-80">{{ __('auth.recovery.issue_both_desc') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Data Akun Terdaftar Saat Ini -->
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <label
                            class="block text-xs sm:text-sm font-bold text-black/80 dark:text-white/85 uppercase tracking-wider mb-3">
                            {{ __('auth.recovery.section_2_title') }}
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="applicant_name"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    {{ __('auth.recovery.applicant_name_label') }} <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="applicant_name" id="applicant_name" required
                                    value="{{ old('applicant_name', $prefill['applicant_name']) }}"
                                    placeholder="{{ __('auth.recovery.applicant_name_placeholder') }}"
                                    class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('applicant_name') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                @error('applicant_name')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="business_name"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    {{ __('auth.recovery.business_name_label') }} <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="text" name="business_name" id="business_name" required
                                    value="{{ old('business_name', $prefill['business_name']) }}"
                                    placeholder="{{ __('auth.recovery.business_name_placeholder') }}"
                                    class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('business_name') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                @error('business_name')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="old_email"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    {{ __('auth.recovery.old_email_label') }} <span class="text-[#FF3B30]">*</span>
                                </label>
                                <input type="email" name="old_email" id="old_email" required
                                    value="{{ old('old_email', $prefill['old_email']) }}"
                                    placeholder="{{ __('auth.recovery.old_email_placeholder') }}"
                                    class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('old_email') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                @error('old_email')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="old_phone"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    {{ __('auth.recovery.old_phone_label') }}
                                </label>
                                <input type="text" name="old_phone" id="old_phone"
                                    value="{{ old('old_phone', $prefill['old_phone']) }}"
                                    placeholder="{{ __('auth.recovery.old_phone_placeholder') }}"
                                    class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('old_phone') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                @error('old_phone')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 3. Kontak Baru Yang Aktif -->
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                            <label
                                class="block text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">
                                {{ __('auth.recovery.section_3_title') }}
                            </label>
                        </div>
                        <p class="text-xs text-black/55 dark:text-white/55 mb-3">{{ __('auth.recovery.section_3_subtitle') }}</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="new_email"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    {{ __('auth.recovery.new_email_label') }} <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                        <i data-lucide="mail" class="w-4 h-4"></i>
                                    </div>
                                    <input type="email" name="new_email" id="new_email" required
                                        value="{{ old('new_email') }}" placeholder="{{ __('auth.recovery.new_email_placeholder') }}"
                                        class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('new_email') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                </div>
                                @error('new_email')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block font-medium">{{ $message }}</span>
                                @else
                                    <p class="mt-1.5 text-[11px] sm:text-xs text-black/50 dark:text-white/50">{{ __('auth.recovery.new_email_hint') }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="new_phone"
                                    class="block font-semibold text-black/75 dark:text-white/80 text-xs sm:text-sm mb-1.5">
                                    {{ __('auth.recovery.new_phone_label') }} <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-black/40 dark:text-white/40">
                                        <i data-lucide="phone" class="w-4 h-4"></i>
                                    </div>
                                    <input type="text" name="new_phone" id="new_phone" required
                                        value="{{ old('new_phone') }}" placeholder="{{ __('auth.recovery.new_phone_placeholder') }}"
                                        class="w-full pl-11 pr-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('new_phone') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 transition-all text-[16px] sm:text-sm outline-none">
                                </div>
                                @error('new_phone')
                                    <span
                                        class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block font-medium">{{ $message }}</span>
                                @else
                                    <p class="mt-1.5 text-[11px] sm:text-xs text-black/50 dark:text-white/50">{{ __('auth.recovery.new_phone_hint') }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- 4. Kronologi & Alasan -->
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <label for="reason_description"
                            class="block text-xs sm:text-sm font-bold text-black/80 dark:text-white/85 uppercase tracking-wider mb-2">
                            {{ __('auth.recovery.section_4_title') }} <span class="text-[#FF3B30]">*</span>
                        </label>
                        <textarea name="reason_description" id="reason_description" rows="3" required
                            placeholder="{{ __('auth.recovery.reason_placeholder') }}"
                            class="w-full px-4 py-3 bg-black/[0.03] dark:bg-white/[0.05] border @error('reason_description') border-[#FF3B30] ring-4 ring-[#FF3B30]/15 @else border-black/10 dark:border-white/10 focus:border-[#007AFF] focus:ring-4 focus:ring-[#007AFF]/15 @enderror rounded-[14px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 text-[16px] sm:text-sm transition-all outline-none leading-relaxed">{{ old('reason_description') }}</textarea>
                        @error('reason_description')
                            <span
                                class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1 block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- 5. Upload Berkas Bukti Otentik -->
                    <div class="pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="paperclip" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]"></i>
                            <label
                                class="block text-xs sm:text-sm font-bold text-black dark:text-white uppercase tracking-wider">
                                {{ __('auth.recovery.section_5_title') }}
                            </label>
                        </div>
                        <p class="text-xs text-black/55 dark:text-white/55 mb-3.5">{{ __('auth.recovery.section_5_subtitle') }}</p>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <!-- Bukti KTP -->
                            <div
                                class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border @error('identity_card') border-[#FF3B30] ring-2 ring-[#FF3B30]/30 @else border-black/[0.06] dark:border-white/[0.08] @enderror flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs sm:text-sm font-bold text-black dark:text-white">{{ __('auth.recovery.ktp_title') }}</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] uppercase">{{ __('auth.recovery.required_badge') }}</span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mb-3">{{ __('auth.recovery.ktp_desc') }}</p>
                                </div>
                                <div>
                                    <input type="file" name="identity_card" id="identity_card" required
                                        accept="image/*,.pdf" @change="handleFilePreview($event, 'ktpPreview')"
                                        class="w-full max-w-full block text-xs text-black/60 dark:text-white/60 file:mr-2 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-xs file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0071E3] file:cursor-pointer">
                                    @error('identity_card')
                                        <span
                                            class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1.5 block font-medium">{{ $message }}</span>
                                    @enderror
                                    <template x-if="ktpPreview && ktpPreview !== 'pdf'">
                                        <div
                                            class="mt-2.5 relative rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 max-h-24 bg-black/5">
                                            <img :src="ktpPreview" class="w-full h-24 object-cover">
                                        </div>
                                    </template>
                                    <template x-if="ktpPreview === 'pdf'">
                                        <div
                                            class="mt-2.5 p-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-xs text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                            <i data-lucide="file-text" class="w-4 h-4 text-[#FF3B30]"></i>
                                            <span>{{ __('auth.recovery.pdf_selected') }}</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Bukti Usaha -->
                            <div
                                class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border @error('business_proof') border-[#FF3B30] ring-2 ring-[#FF3B30]/30 @else border-black/[0.06] dark:border-white/[0.08] @enderror flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs sm:text-sm font-bold text-black dark:text-white">{{ __('auth.recovery.biz_proof_title') }}</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#FF3B30]/10 text-[#FF3B30] dark:text-[#FF453A] uppercase">{{ __('auth.recovery.required_badge') }}</span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mb-3">{{ __('auth.recovery.biz_proof_desc') }}</p>
                                </div>
                                <div>
                                    <input type="file" name="business_proof" id="business_proof" required
                                        accept="image/*,.pdf" @change="handleFilePreview($event, 'bizPreview')"
                                        class="w-full max-w-full block text-xs text-black/60 dark:text-white/60 file:mr-2 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-xs file:font-semibold file:bg-[#007AFF] file:text-white hover:file:bg-[#0071E3] file:cursor-pointer">
                                    @error('business_proof')
                                        <span
                                            class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1.5 block font-medium">{{ $message }}</span>
                                    @enderror
                                    <template x-if="bizPreview && bizPreview !== 'pdf'">
                                        <div
                                            class="mt-2.5 relative rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 max-h-24 bg-black/5">
                                            <img :src="bizPreview" class="w-full h-24 object-cover">
                                        </div>
                                    </template>
                                    <template x-if="bizPreview === 'pdf'">
                                        <div
                                            class="mt-2.5 p-2 rounded-[10px] bg-black/[0.04] dark:bg-white/[0.06] text-xs text-black/70 dark:text-white/70 flex items-center gap-1.5">
                                            <i data-lucide="file-text" class="w-4 h-4 text-[#FF3B30]"></i>
                                            <span>{{ __('auth.recovery.pdf_selected') }}</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Selfie KTP (Opsional) -->
                            <div
                                class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border @error('selfie_proof') border-[#FF3B30] ring-2 ring-[#FF3B30]/30 @else border-black/[0.06] dark:border-white/[0.08] @enderror flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs sm:text-sm font-bold text-black dark:text-white">{{ __('auth.recovery.selfie_title') }}</span>
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] uppercase">{{ __('auth.recovery.recommended_badge') }}</span>
                                    </div>
                                    <p class="text-[11px] text-black/50 dark:text-white/50 mb-3">{{ __('auth.recovery.selfie_desc') }}</p>
                                </div>
                                <div>
                                    <input type="file" name="selfie_proof" id="selfie_proof" accept="image/*"
                                        @change="handleFilePreview($event, 'selfiePreview')"
                                        class="w-full max-w-full block text-xs text-black/60 dark:text-white/60 file:mr-2 file:py-2 file:px-3 file:rounded-[10px] file:border-0 file:text-xs file:font-semibold file:bg-black/[0.08] dark:file:bg-white/[0.1] file:text-black dark:file:text-white hover:file:bg-black/[0.12] file:cursor-pointer">
                                    @error('selfie_proof')
                                        <span
                                            class="text-[#FF3B30] dark:text-[#FF453A] text-xs mt-1.5 block font-medium">{{ $message }}</span>
                                    @enderror
                                    <template x-if="selfiePreview">
                                        <div
                                            class="mt-2.5 relative rounded-[12px] overflow-hidden border border-black/10 dark:border-white/10 max-h-24 bg-black/5">
                                            <img :src="selfiePreview" class="w-full h-24 object-cover">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notice & Submit Button -->
                    <div class="pt-4">
                        <div
                            class="p-4 rounded-[18px] bg-[#007AFF]/10 border border-[#007AFF]/25 text-[#007AFF] dark:text-[#0A84FF] text-xs sm:text-sm flex items-start gap-3 mb-5">
                            <i data-lucide="info" class="w-5 h-5 shrink-0 mt-0.5"></i>
                            <div class="leading-relaxed">
                                {!! __('auth.recovery.auditor_notice') !!}
                            </div>
                        </div>

                        <button type="submit" :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                            class="w-full min-h-[52px] py-4 px-5 rounded-[16px] bg-[#007AFF] hover:bg-[#0071E3] text-white font-bold text-sm sm:text-base shadow-lg shadow-[#007AFF]/25 transition-all flex items-center justify-center gap-2.5 active:scale-[0.98] cursor-pointer">
                            <i data-lucide="shield-check" class="w-5 h-5" x-show="!submitting"></i>
                            <i data-lucide="loader-2" class="w-5 h-5 animate-spin" x-show="submitting"
                                style="display: none;"></i>
                            <span
                                x-text="submitting ? {{ json_encode(__('auth.recovery.submitting_btn')) }} : {{ json_encode(__('auth.recovery.submit_btn')) }}"></span>
                        </button>
                    </div>
                </form>

                <!-- Footer Links -->
                <div
                    class="mt-8 pt-6 border-t border-black/[0.06] dark:border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs sm:text-sm">
                    <a href="{{ route('account-recovery.check') }}"
                        class="text-[#007AFF] dark:text-[#0A84FF] hover:underline transition-colors flex items-center gap-1.5 font-semibold py-1">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>{{ __('auth.recovery.has_ticket_check') }}</span>
                    </a>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('login') }}"
                            class="text-black/55 dark:text-white/55 hover:text-black dark:hover:text-white transition-colors py-1">
                            {{ __('auth.recovery.cancel_to_login') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
