{{-- MODAL 4: FULLSCREEN LIGHTBOX PREVIEW BUKTI TRANSAKSI --}}
<div x-show="showImageModal" x-cloak
    class="fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4"
    @keydown.escape.window="showImageModal = false"
    x-data="{
        zoomLevel: 1,
        rotation: 0,
        zoomIn() { if (this.zoomLevel < 3) this.zoomLevel += 0.25; },
        zoomOut() { if (this.zoomLevel > 0.5) this.zoomLevel -= 0.25; },
        rotate() { this.rotation = (this.rotation + 90) % 360; },
        reset() { this.zoomLevel = 1; this.rotation = 0; }
    }">

    <div class="w-full max-w-4xl max-h-[92vh] flex flex-col items-center">
        {{-- Header Bar --}}
        <div class="w-full px-4 py-3 bg-white/10 backdrop-blur-md rounded-t-[20px] flex items-center justify-between text-white border-b border-white/10">
            <div class="flex items-center gap-2.5 min-w-0">
                <i data-lucide="file-check" class="w-4 h-4 text-[#30D158] shrink-0"></i>
                <span class="text-[13px] font-semibold truncate" x-text="modalImageTitle"></span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Controls --}}
                <button type="button" @click="zoomIn()"
                    class="w-8 h-8 rounded-[8px] bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 hover:text-white cursor-pointer"
                    title="Zoom In">
                    <i data-lucide="zoom-in" class="w-4 h-4"></i>
                </button>
                <button type="button" @click="zoomOut()"
                    class="w-8 h-8 rounded-[8px] bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 hover:text-white cursor-pointer"
                    title="Zoom Out">
                    <i data-lucide="zoom-out" class="w-4 h-4"></i>
                </button>
                <button type="button" @click="rotate()"
                    class="w-8 h-8 rounded-[8px] bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 hover:text-white cursor-pointer"
                    title="Putar 90 Derajat">
                    <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                </button>
                <button type="button" @click="reset()"
                    class="w-8 h-8 rounded-[8px] bg-white/10 hover:bg-white/20 flex items-center justify-center text-white/80 hover:text-white cursor-pointer"
                    title="Reset Tampilan">
                    <i data-lucide="refresh-ccw" class="w-3.5 h-3.5"></i>
                </button>

                {{-- Download / External --}}
                <a :href="modalImageUrl" target="_blank"
                    class="h-8 px-3 rounded-[8px] bg-white/15 hover:bg-white/25 text-[11px] font-bold text-white flex items-center gap-1.5 cursor-pointer ml-1" rel="noopener noreferrer">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Buka Penuh</span>
                </a>

                {{-- Close Button --}}
                <button type="button" @click="showImageModal = false; reset()"
                    class="w-8 h-8 rounded-[8px] bg-[#FF453A]/25 hover:bg-[#FF453A]/40 text-[#FF453A] flex items-center justify-center cursor-pointer ml-1">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        {{-- Canvas Viewer Area --}}
        <div class="w-full bg-black/60 rounded-b-[20px] p-4 flex items-center justify-center overflow-auto min-h-[300px] max-h-[78vh] relative">
            <template x-if="modalImageUrl && modalImageUrl.endsWith('.pdf')">
                <iframe :src="modalImageUrl" class="w-full h-[70vh] rounded-[12px] bg-white"></iframe>
            </template>
            <template x-if="modalImageUrl && !modalImageUrl.endsWith('.pdf')">
                <img :src="modalImageUrl" alt="Bukti Transaksi"
                    :style="`transform: scale(${zoomLevel}) rotate(${rotation}deg); transition: transform 0.2s ease-in-out;`"
                    class="max-h-[70vh] max-w-full object-contain rounded-[8px] shadow-2xl">
            </template>
        </div>
    </div>
</div>
