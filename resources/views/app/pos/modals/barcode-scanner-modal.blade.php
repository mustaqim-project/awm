<!-- ===================================================== -->
    <!-- 12. MODAL: CAMERA BARCODE SCANNER (Apple Sheet)       -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showBarcodeScanner" x-cloak role="dialog" aria-modal="true"
            class="fixed inset-0 z-[200] overflow-y-auto"
            @keydown.escape.window="closeBarcodeScanner()">
            <!-- Standalone Backdrop -->
            <div x-show="showBarcodeScanner"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="closeBarcodeScanner()"></div>

            <!-- Centered Dialog Container -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-3 sm:p-6"
                @click.self="closeBarcodeScanner()">
                <div
                    class="pos-modal-panel relative w-full max-w-lg bg-white dark:bg-[#2C2C2E] rounded-[16px] border border-black/10 dark:border-white/10 p-4 sm:p-5 space-y-3.5 shadow-2xl text-black dark:text-white">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-semibold text-[17px] text-black dark:text-white">{{ __('pos.scan_barcode_title') }}</h3>
                            <p class="text-[12px] text-black/45 dark:text-white/45 mt-0.5">{{ __('pos.point_camera_desc') }}</p>
                        </div>
                        <button type="button" @click="closeBarcodeScanner()"
                            class="w-7 h-7 rounded-full bg-black/[0.05] dark:bg-white/[0.08] text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div
                        class="relative aspect-video overflow-hidden rounded-[14px] bg-black border border-black/10 dark:border-white/10">
                        <video x-ref="barcodeVideo" autoplay muted playsinline class="w-full h-full object-cover"></video>
                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                            <div
                                class="w-[70%] h-[40%] rounded-[12px] border-2 border-[#007AFF] shadow-[0_0_0_9999px_rgba(0,0,0,.45)]">
                            </div>
                        </div>
                        <div x-show="scannerStarting"
                            class="absolute inset-0 flex items-center justify-center bg-black/70 text-xs text-white/60">
                            <span class="flex items-center gap-2">{{ __('pos.preparing_camera') }}</span>
                        </div>
                    </div>

                    <div x-show="scannerError"
                        class="p-3 rounded-[10px] bg-[#FF3B30]/12 border border-[#FF3B30]/25 text-xs text-[#FF3B30]"
                        x-text="scannerError"></div>

                    <div x-show="scannerDevices.length > 1" class="space-y-1">
                        <label class="block text-[11px] text-black/50 dark:text-white/50 font-medium">{{ __('pos.select_camera') }}</label>
                        <select x-model="selectedScannerDeviceId" @change="startBarcodeScanner()"
                            class="w-full h-9 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/10 rounded-[8px] px-3 text-xs text-black dark:text-white">
                            <template x-for="device in scannerDevices" :key="device.deviceId">
                                <option :value="device.deviceId"
                                    x-text="device.label || 'Kamera ' + (scannerDevices.indexOf(device) + 1)"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex items-center justify-between gap-3 pt-2 border-t border-black/10 dark:border-white/10">
                        <span class="text-[11px] text-black/40 dark:text-white/40">{{ __('pos.usb_scanner_active_hint') }}</span>
                        <button type="button" @click="closeBarcodeScanner()"
                            class="h-8 px-3 rounded-[8px] bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.1] dark:hover:bg-white/[0.12] text-black dark:text-white text-[12px] font-medium transition">{{ __('pos.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </template>
