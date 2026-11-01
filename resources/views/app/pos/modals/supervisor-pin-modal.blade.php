<!-- ===================================================== -->
    <!-- 10B. MODAL: SUPERVISOR PIN AUTHORIZATION (Apple Alert Style) -->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="showSupervisorPinModal" x-cloak
            role="dialog"
            aria-modal="true"
            class="fixed inset-0 z-[220] overflow-y-auto"
            @keydown.escape.window="if(!supervisorPinLoading) closeSupervisorPinModal()">
            <!-- Standalone Backdrop -->
            <div x-show="showSupervisorPinModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md transition-opacity"
                @click="if(!supervisorPinLoading) closeSupervisorPinModal()"></div>

            <!-- Centered Dialog Wrapper -->
            <div class="relative z-10 min-h-full flex items-center justify-center p-4" @click.self="if(!supervisorPinLoading) closeSupervisorPinModal()">
                <div class="pos-modal-panel relative w-full max-w-sm bg-white/95 dark:bg-[#1C1C1E]/95 backdrop-blur-2xl rounded-[18px] border border-black/10 dark:border-white/15 p-5 sm:p-6 space-y-4 shadow-[0_20px_50px_rgba(0,0,0,0.3)] text-black dark:text-white">
            <div class="text-center space-y-2">
                <div
                    class="w-12 h-12 mx-auto rounded-full bg-[#AF52DE]/15 text-[#AF52DE] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
                <h3 class="font-bold text-[17px] text-black dark:text-white">{{ __('pos.supervisor_pin_title') }}</h3>
                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                    {{ __('pos.supervisor_pin_desc') }}
                </p>
            </div>

            <form @submit.prevent="verifySupervisorPinSubmit()" class="space-y-3.5">
                <div>
                    <input x-ref="supervisorPinInputRef" type="password" inputmode="numeric" maxlength="8"
                        x-model="supervisorPinInput" placeholder="••••••" autocomplete="off"
                        :disabled="supervisorPinLoading"
                        class="w-full h-12 bg-black/[0.04] dark:bg-white/[0.06] border border-black/10 dark:border-white/15 rounded-[12px] text-center font-mono text-[22px] tracking-[0.35em] text-black dark:text-white placeholder:tracking-normal placeholder:font-sans placeholder:text-[14px] placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#AF52DE]/60 transition disabled:opacity-50">
                </div>

                <div x-show="supervisorPinError"
                    class="p-2.5 rounded-[10px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[12px] text-[#FF3B30] text-center font-medium leading-relaxed"
                    x-text="supervisorPinError"></div>

                <!-- No-Panic Microcopy & Security Note (Apple HIG Trust Badge) -->
                <div class="p-2.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/5 dark:border-white/5 space-y-1 text-center">
                    <div class="flex items-center justify-center gap-1.5 text-[11px] font-medium text-[#34C759]">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span>{{ __('pos.supervisor_pin_security_note') }}</span>
                    </div>
                    <p class="text-[10px] text-black/45 dark:text-white/45 leading-normal">
                        {{ __('pos.supervisor_pin_no_panic_guide') }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-1">
                    <button type="button" @click="closeSupervisorPinModal()" :disabled="supervisorPinLoading"
                        class="h-10 rounded-[10px] bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.1] text-black/70 dark:text-white/70 font-semibold text-[13px] transition disabled:opacity-50">
                        {{ __('pos.cancel') }}
                    </button>
                    <button type="submit" :disabled="supervisorPinLoading || !supervisorPinInput.trim()"
                        class="h-10 rounded-[10px] bg-[#AF52DE] hover:bg-[#9B42C8] active:scale-[0.98] text-white font-semibold text-[13px] transition disabled:opacity-50 flex items-center justify-center gap-1.5 shadow-sm">
                        <span x-show="!supervisorPinLoading">{{ __('pos.authorize') }}</span>
                        <span x-show="supervisorPinLoading" class="inline-block animate-spin">⏳</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>
