<!-- ======================================================== -->
    <!-- ======================================================== -->
    <!-- MODAL 2: 1-DIRECTION FRONTAL BIOMETRIC FACE ATTENDANCE   -->
    <!-- ======================================================== -->
    <template x-teleport="body">
        <div x-show="showFaceAttendanceModal" 
            x-cloak
            @keydown.escape.window="stopAttendanceCamera(); showFaceAttendanceModal = false"
            class="fixed inset-0 z-[200] overflow-y-auto">
            <!-- Backdrop -->
            <div x-show="showFaceAttendanceModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="stopAttendanceCamera(); showFaceAttendanceModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div class="relative z-10 min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4" @click.self="showFaceAttendanceModal = false">
                <div x-show="showFaceAttendanceModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.stop
                    class="relative w-full sm:max-w-lg bg-white dark:bg-[#1C1C1E] rounded-t-[28px] sm:rounded-[26px] border-t sm:border border-black/10 dark:border-white/10 shadow-[0_32px_64px_rgba(0,0,0,0.4)] overflow-hidden flex flex-col max-h-[92dvh] sm:max-h-[90vh]">
                    
                    <!-- Mobile Sheet Drag Indicator -->
                    <div class="sm:hidden w-12 h-1.5 bg-black/20 dark:bg-white/20 rounded-full mx-auto mt-2.5 mb-0.5 shrink-0"></div>

                    <!-- Header -->
                    <div class="p-4 sm:p-5 border-b border-black/5 dark:border-white/10 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                                <i data-lucide="scan-face" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h4 class="text-[14px] font-bold text-black dark:text-white">{{ __('hrm.face_attendance_title') ?? 'Presensi Wajah 1-Arah' }}</h4>
                                <span class="text-[11px] font-semibold text-black/50 dark:text-white/50" x-text="attendanceActionType === 'in' ? '{{ __('portal.clock_in_title') }}' : '{{ __('portal.clock_out_title') }}'"></span>
                            </div>
                        </div>
                        <button type="button" @click="stopAttendanceCamera(); showFaceAttendanceModal = false" class="w-11 h-11 min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 dark:hover:bg-white/15 transition cursor-pointer" aria-label="{{ __('common.close') }}">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

            <!-- Body Camera & 1-Direction Frontal Flow -->
            <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1">
                
                <!-- Guide Banner -->
                <div class="p-3 rounded-[12px] bg-[#007AFF]/8 border border-[#007AFF]/20 text-[11.5px] font-medium text-black/80 dark:text-white/80 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                    <span>{{ __('portal.face_frontal_guide') }}</span>
                </div>

                <!-- Video HUD Container -->
                <div class="relative w-full aspect-square max-w-[320px] mx-auto rounded-[20px] overflow-hidden bg-black flex items-center justify-center shadow-xl border-2 transition-colors duration-300"
                    :class="faceVerifiedSuccess ? 'border-[#34C759]' : (faceSteady ? 'border-[#007AFF]' : (faceDetected ? 'border-[#FF9500]' : 'border-[#FF3B30]/60'))">
                    
                    <video id="livenessVideo" autoplay playsinline muted class="w-full h-full object-cover transform -scale-x-100"></video>
                    
                    <!-- Oval Face Frame Guide Overlay -->
                    <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-between p-3.5">
                        <!-- Top status badge -->
                        <div class="px-3.5 py-1.5 rounded-full bg-black/75 backdrop-blur-md border border-white/20 text-white text-[11px] font-bold flex items-center gap-2 shadow-lg">
                            <span class="w-2 h-2 rounded-full"
                                :class="faceVerifiedSuccess ? 'bg-[#34C759]' : (faceSteady ? 'bg-[#007AFF] animate-pulse' : (faceDetected ? 'bg-[#FF9500]' : 'bg-[#FF3B30] animate-ping'))"></span>
                            <span x-text="liveDetectionStatus"></span>
                        </div>

                        <!-- Center Oval Target Ring -->
                        <div class="relative w-[72%] h-[82%] rounded-[50%] border-4 transition-all duration-300 flex items-center justify-center"
                            :class="faceVerifiedSuccess ? 'border-[#34C759] scale-105 bg-[#34C759]/20 shadow-[0_0_30px_rgba(52,199,89,0.5)]' : (faceSteady ? 'border-[#007AFF] scale-102 shadow-[0_0_24px_rgba(0,122,255,0.4)]' : (faceDetected ? 'border-[#FF9500] border-dashed shadow-[0_0_0_9999px_rgba(0,0,0,0.45)]' : 'border-[#FF3B30] border-dashed shadow-[0_0_0_9999px_rgba(0,0,0,0.6)]'))">
                            
                            <template x-if="faceVerifiedSuccess">
                                <div class="w-14 h-14 rounded-full bg-[#34C759] flex items-center justify-center mx-auto animate-pulse shadow-xl shadow-[#34C759]/50">
                                    <i data-lucide="check" class="w-8 h-8 text-white"></i>
                                </div>
                            </template>
                        </div>

                        <!-- Bottom Helper Indicator -->
                        <div class="w-full text-center py-2 px-3 rounded-[12px] bg-black/80 backdrop-blur-md border border-white/20 shadow-md">
                            <p class="text-[11.5px] font-bold text-white tracking-wide" x-text="liveGuideText"></p>
                        </div>
                    </div>
                </div>

                <!-- Stability Progress Bar -->
                <div class="space-y-1.5 max-w-[320px] mx-auto">
                    <div class="flex justify-between items-center text-[11px] font-bold text-black/70 dark:text-white/70">
                        <span class="flex items-center gap-1.5">
                            <i data-lucide="scan" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                            <span>{{ __('portal.face_stability_label') }}</span>
                        </span>
                        <span class="tabular-nums font-mono text-[#007AFF]" x-text="stabilityProgress + '%'"></span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-black/10 dark:bg-white/10 overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-[#007AFF] to-[#34C759] transition-all duration-150" :style="'width: ' + stabilityProgress + '%'"></div>
                    </div>
                </div>

                <!-- Error Alert Display -->
                <template x-if="attendanceErrorMessage">
                    <div class="p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[11.5px] font-semibold text-[#FF3B30] flex items-start gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
                        <span x-text="attendanceErrorMessage"></span>
                    </div>
                </template>

                <!-- Processing Spinner -->
                <div x-show="isSubmittingAttendance" class="text-center py-2 space-y-1">
                    <div class="inline-flex items-center gap-2 text-[#007AFF] font-bold text-[13px]">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        <span>{{ __('portal.map_submitting') }}</span>
                    </div>
                    <p class="text-[10.5px] text-black/50 dark:text-white/50">{{ __('portal.face_privacy_notice') }}</p>
            </div>
                </div>
            </div>
        </div>
    </template>
