{{-- Sheet Transparansi Prompt & Kuotasi Kredit AI Studio (Apple HIG Sheet Modal) --}}
<div x-show="promptModalOpen" x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/50 backdrop-blur-md"
    style="display: none;">
    <div @click.away="promptModalOpen = false"
        class="w-full max-w-2xl max-h-[90vh] flex flex-col rounded-[22px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">

        <!-- Sheet Header -->
        <div class="px-6 py-4 border-b border-black/5 dark:border-white/10 flex items-center justify-between bg-black/[0.01] dark:bg-white/[0.02]">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] dark:bg-[#007AFF]/20 dark:text-[#0A84FF] flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-black dark:text-white">Lembar Transparansi Prompt AI</h3>
                    <p class="text-[11px] text-black/50 dark:text-white/50">Tinjau, sesuaikan teks prompt, dan periksa estimasi kuotasi kredit</p>
                </div>
            </div>
            <button type="button" @click="promptModalOpen = false"
                class="p-1.5 rounded-full text-black/40 dark:text-white/40 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Sheet Content (Scrollable) -->
        <div class="p-6 overflow-y-auto space-y-5 flex-1">

            <!-- Positive Prompt Area (Editable AC-006) -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-[#007AFF]"></i>
                        <span>Prompt Positif (Teks Aktual ke Mesin AI)</span>
                    </label>
                    <span class="text-[10px] text-black/40 dark:text-white/40">Dapat diedit manual</span>
                </div>
                <textarea x-model="compiledPositivePrompt" rows="4"
                    class="w-full p-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white font-mono leading-relaxed focus:outline-none focus:ring-2 focus:ring-[#007AFF]"></textarea>
                <p class="text-[10px] text-black/50 dark:text-white/50">
                    Prompt ini dirakit otomatis dari nama produk, atribut visual referensi komersial, dan brief kreatif Anda.
                </p>
            </div>

            <!-- Negative Prompt Area -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                    <i data-lucide="ban" class="w-3.5 h-3.5 text-red-500"></i>
                    <span>Negative Prompt (Penolak Distorsi &amp; Noise)</span>
                </label>
                <div class="p-3 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/10 dark:border-white/10 text-xs font-mono text-black/60 dark:text-white/60 leading-relaxed"
                    x-text="compiledNegativePrompt || 'blurry, distorted, oversaturated, deformed proportions, bad anatomy, text watermark, low quality, artifacts'">
                </div>
            </div>

            <!-- Extracted Visual Feature Tokens (Bento Grid) -->
            <div class="p-4 rounded-[14px] bg-black/[0.02] dark:bg-white/[0.02] border border-black/5 dark:border-white/5 space-y-3">
                <span class="text-xs font-bold text-black dark:text-white flex items-center gap-1.5">
                    <i data-lucide="scan" class="w-3.5 h-3.5 text-[#5856D6]"></i>
                    <span>Fitur Visi Komputer &amp; Atribut Terpetakan</span>
                </span>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="(attr, idx) in visionAttributes" :key="idx">
                        <span class="px-2.5 py-1 rounded-[8px] bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 text-[11px] font-medium text-black/70 dark:text-white/80"
                            x-text="attr">
                        </span>
                    </template>
                    <span x-show="!visionAttributes || visionAttributes.length === 0" class="text-xs text-black/40 dark:text-white/40">
                        Tidak ada analisis visi eksternal (menggunakan preset murni).
                    </span>
                </div>
            </div>

            <!-- Dynamic Credit Quote & Ledger Hold Notice (AC-007) -->
            <div class="p-4 rounded-[14px] bg-[#007AFF]/5 dark:bg-[#007AFF]/10 border border-[#007AFF]/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-[#007AFF] dark:text-[#0A84FF]">
                        <i data-lucide="coins" class="w-4 h-4"></i>
                        <span>Reservasi Kredit AI (Two-Phase Hold)</span>
                    </div>
                    <p class="text-[11px] text-black/60 dark:text-white/60">
                        Kredit akan ditahan saat antrean dimulai. Jika generasi render gagal atau ditolak provider, saldo kredit <strong>100% otomatis dikembalikan</strong>.
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-xs text-black/50 dark:text-white/50 block">Estimasi Biaya:</span>
                    <span class="text-xl font-extrabold text-[#007AFF] dark:text-[#0A84FF]" x-text="quoteCost + ' Kredit'">15 Kredit</span>
                </div>
            </div>

            <!-- Balance Sufficiency Warning if needed -->
            <div x-show="!canAfford" class="p-3.5 rounded-[12px] bg-red-500/10 border border-red-500/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-red-600 dark:text-red-400">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span>Saldo kredit Anda (<strong x-text="userCredits"></strong>) tidak mencukupi untuk pemesanan ini (dibutuhkan <strong x-text="quoteCost"></strong> kredit).</span>
                </div>
                <button type="button" @click="$dispatch('open-ai-topup-modal', { required: quoteCost, available: userCredits, message: 'Saldo kredit Anda tidak mencukupi untuk pemesanan ini (' + userCredits + ' dari ' + quoteCost + ' kredit).' })"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[10px] bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-xs shadow-xs transition-all shrink-0">
                    <i data-lucide="zap" class="w-3 h-3 fill-white"></i>
                    <span>Top Up Sekarang</span>
                </button>
            </div>

        </div>

        <!-- Sheet Footer & Confirmation Actions -->
        <div class="px-6 py-4 border-t border-black/5 dark:border-white/10 flex items-center justify-end gap-3 bg-black/[0.01] dark:bg-white/[0.02]">
            <button type="button" @click="promptModalOpen = false"
                class="px-4 py-2 rounded-[10px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 transition-colors">
                Kembali Sunting
            </button>
            <button type="button" @click="confirmAndSubmitProject()" :disabled="!canAfford"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-[12px] bg-[#007AFF] hover:bg-[#0062cc] disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-[#007AFF]/25 transition-all active:scale-[0.98]">
                <i data-lucide="check" class="w-3.5 h-3.5"></i>
                <span>Konfirmasi &amp; Inisiasi Proyek</span>
            </button>
        </div>

    </div>
</div>
