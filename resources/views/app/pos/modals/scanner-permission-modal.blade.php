<!-- ===================================================== -->
    <!-- 11. MODAL: CAMERA PERMISSION (Apple Alert Style)      -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showScannerPermission" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[220] overflow-y-auto"
            @keydown.escape.window="showScannerPermission = false">
            <!-- Standalone Backdrop -->
            <div x-show="showScannerPermission"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="showScannerPermission = false"></div>

            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="showScannerPermission = false">
                <div class="pos-modal-panel relative w-[300px] rounded-[14px] bg-white dark:bg-[#2C2C2E] overflow-hidden text-center shadow-[0_20px_50px_rgba(0,0,0,0.25)] border border-black/10 dark:border-white/10 text-black dark:text-white">
                    <div class="px-5 pt-5 pb-4">
                        <div
                            class="w-10 h-10 rounded-full bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                            </svg>
                        </div>
                        <p class="text-[16px] font-semibold text-black dark:text-white">{{ __('pos.allow_camera_access_title') }}</p>
                        <p class="text-[12px] text-black/60 dark:text-white/60 mt-1 leading-snug">{{ __('pos.allow_camera_access_desc') }}</p>
                    </div>
                    <div class="grid grid-cols-2 border-t border-black/10 dark:border-white/10 text-[14px] font-medium">
                        <button type="button" @click="showScannerPermission = false"
                            class="py-2.5 text-[#007AFF] border-r border-black/10 dark:border-white/10 active:bg-black/5 dark:active:bg-white/5 transition">{{ __('pos.cancel') }}</button>
                        <button type="button" @click="confirmBarcodeScannerAccess()"
                            class="py-2.5 text-[#007AFF] font-semibold active:bg-black/5 dark:active:bg-white/5 transition">{{ __('pos.allow') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
