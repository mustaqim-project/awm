<!-- ======================================================== -->
    <!-- MODAL 1: DAFTARKAN BIOMETRIK WAJAH KARYAWAN              -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showFaceRegisterModal" 
            x-cloak
            @keydown.escape.window="stopFaceRegCamera(); showFaceRegisterModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showFaceRegisterModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="stopFaceRegCamera(); showFaceRegisterModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showFaceRegisterModal = false">
                <div x-show="showFaceRegisterModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    @click.stop
                    class="relative w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/10 dark:border-white/10 shadow-[0_28px_56px_rgba(0,0,0,0.3)] overflow-hidden flex flex-col max-h-[90vh]">
                    <div class="p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center shrink-0">
                                <i data-lucide="scan-face" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('hrm.face_registration_title') }}</h3>
                                <p class="text-[12px] text-black/60 dark:text-white/60">
                                    {{ __('hrm.staff_prefix') }} <strong class="text-black dark:text-white" x-text="selectedFaceEmployee?.user?.name"></strong>
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="stopFaceRegCamera(); showFaceRegisterModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <form method="POST" :action="faceRegisterAction" enctype="multipart/form-data"
                @submit="if(isSubmittingForm) { $event.preventDefault(); return false; } isSubmittingForm = true;"
                class="p-5 space-y-4 overflow-y-auto">
                @csrf
                <input type="hidden" name="photo" :value="faceRegPhotoData">

                <!-- Mode Switcher -->
                <div class="flex p-1 rounded-[12px] bg-black/[0.05] dark:bg-white/[0.08]">
                    <button type="button" @click="faceRegMode = 'camera'; startFaceRegCamera()"
                        :class="faceRegMode === 'camera' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 font-semibold'"
                        class="flex-1 py-2 text-[12.5px] rounded-[9px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="camera" class="w-4 h-4"></i>
                        <span>{{ __('hrm.capture_from_webcam') }}</span>
                    </button>
                    <button type="button" @click="faceRegMode = 'upload'; stopFaceRegCamera()"
                        :class="faceRegMode === 'upload' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white font-bold shadow-xs' : 'text-black/60 dark:text-white/60 font-semibold'"
                        class="flex-1 py-2 text-[12.5px] rounded-[9px] transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>{{ __('hrm.upload_photo_file') }}</span>
                    </button>
                </div>

                <!-- Webcam Capture View -->
                <template x-if="faceRegMode === 'camera'">
                    <div class="space-y-3">
                        <div class="relative w-full aspect-square max-w-[320px] mx-auto rounded-[20px] overflow-hidden bg-black flex items-center justify-center border-2 border-[#34C759]/40 shadow-inner">
                            <video id="faceRegVideo" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                            
                            <!-- Oval Face Frame Guide -->
                            <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                                <div class="w-[72%] h-[82%] rounded-[50%] border-2 border-dashed border-white/80 shadow-[0_0_0_9999px_rgba(0,0,0,0.45)] flex items-center justify-center">
                                    <span class="text-[11px] font-bold text-white/90 bg-black/60 px-3 py-1 rounded-full uppercase tracking-wider">{{ __('hrm.fit_face_here') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center">
                            <button type="button" @click="captureFaceRegSnapshot()"
                                class="h-11 px-6 rounded-[12px] bg-[#34C759] hover:bg-[#2FB34F] text-white text-[13px] font-bold flex items-center gap-2 shadow-[0_2px_8px_rgba(52,199,89,0.3)] transition active:scale-[0.98] cursor-pointer">
                                <i data-lucide="aperture" class="w-4.5 h-4.5"></i>
                                <span>{{ __('hrm.take_face_photo') }}</span>
                            </button>
                        </div>
                    </div>
                </template>

                <!-- File Upload View -->
                <template x-if="faceRegMode === 'upload'">
                    <div class="space-y-3">
                        <label class="block w-full p-6 border-2 border-dashed border-black/15 dark:border-white/15 rounded-[18px] text-center hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition cursor-pointer">
                            <i data-lucide="image" class="w-8 h-8 text-[#007AFF] mx-auto mb-2"></i>
                            <span class="text-[13px] font-bold text-black dark:text-white block">{{ __('hrm.choose_clear_photo') }}</span>
                            <span class="text-[11.5px] text-black/55 dark:text-white/55 block mt-0.5">{{ __('hrm.photo_format_hint') }}</span>
                            <input type="file" accept="image/*" @change="handleFaceRegUpload($event)" class="hidden">
                        </label>
                    </div>
                </template>

                <!-- Photo Preview Section -->
                <template x-if="faceRegPreview">
                    <div class="p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/20 flex items-center gap-3">
                        <img :src="faceRegPreview" class="w-14 h-14 rounded-[12px] object-cover border border-[#34C759]/40 shadow-xs">
                        <div class="min-w-0 flex-1">
                            <span class="text-[12.5px] font-bold text-[#34C759] flex items-center gap-1">
                                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                <span>{{ __('hrm.photo_ready_extract') }}</span>
                            </span>
                            <p class="text-[11px] text-black/60 dark:text-white/60">{{ __('hrm.photo_encrypt_hint') }}</p>
                        </div>
                    </div>
                </template>

                <div class="pt-3 flex justify-end gap-2 border-t border-black/5 dark:border-white/10">
                    <button type="button" @click="stopFaceRegCamera(); showFaceRegisterModal = false"
                        class="h-10 px-4.5 rounded-[11px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                        {{ __('hrm.btn_cancel') }}
                    </button>
                    <button type="submit" :disabled="!faceRegPhotoData || isSubmittingForm"
                        :class="faceRegPhotoData && !isSubmittingForm ? 'bg-[#34C759] hover:bg-[#2FB34F] text-white cursor-pointer shadow-[0_2px_8px_rgba(52,199,89,0.3)]' : 'bg-black/10 dark:bg-white/10 text-black/40 dark:text-white/40 cursor-not-allowed opacity-60'"
                        class="h-10 px-5 rounded-[11px] text-[13px] font-bold transition active:scale-[0.98] flex items-center justify-center gap-2">
                        <template x-if="!isSubmittingForm">
                            <span>{{ __('hrm.btn_save_biometric') }}</span>
                        </template>
                        <template x-if="isSubmittingForm">
                            <div class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('hrm.saving') }}</span>
                            </div>
                        </template>
                    </button>
            </form>
                </div>
            </div>
        </div>
    </template>
