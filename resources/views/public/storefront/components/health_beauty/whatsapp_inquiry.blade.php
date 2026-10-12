{{-- ========================================================================= --}}
{{-- WHATSAPP INQUIRY: Health & Beauty (Klinik Kecantikan, Wellness, Optik & Layanan Kesehatan)        --}}
{{-- ========================================================================= --}}
@props([
    'business',
    'landingPage',
    'hasWhatsapp' => true,
    'productName' => null,
    'mode' => 'floating', // 'floating' or 'inline'
    'customLabel' => null,
])

@php
    $waNumber = preg_replace('/[^0-9]/', '', (string) ($landingPage->whatsapp_number ?: $business->phone));
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '62' . substr($waNumber, 1);
    }

    $defaultGreeting = $landingPage->whatsapp_welcome_message ?: 'Halo klinik kecantikan, saya ingin konsultasi perawatan kulit dan produk kecantikan.';
    
    if ($productName) {
        $defaultGreeting = 'Halo ' . $business->name . ', saya ingin konsultasi mengenai: *' . $productName . '* di ' . url()->current();
    }

    $waUrl = 'https://wa.me/' . $waNumber . '?text=' . urlencode($defaultGreeting);
@endphp

@if ($hasWhatsapp && $waNumber)
    @if ($mode === 'floating')
        <div class="hidden md:flex fixed bottom-6 right-6 z-30 flex-col items-end"
            x-data="{ waChatOpen: false }">
            
            <div x-show="waChatOpen" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-3 scale-95"
                class="mb-3 p-4 rounded-3xl bg-white/95 dark:bg-[#181E2A]/95 backdrop-blur-2xl shadow-[0_16px_36px_rgba(0,0,0,0.18)] border border-black/[0.08] dark:border-white/[0.1] max-w-xs text-xs space-y-3">
                
                <div class="flex items-center justify-between gap-3 border-b border-black/[0.06] dark:border-white/[0.08] pb-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-[#25D366] text-white flex items-center justify-center font-bold text-xs shadow-xs">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="font-bold text-neutral-900 dark:text-white block leading-tight">{{ $business->name }}</span>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Layanan Pelanggan Online
                            </span>
                        </div>
                    </div>
                    <button type="button" @click="waChatOpen = false"
                        class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 transition p-1">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <p class="text-neutral-600 dark:text-neutral-300 leading-relaxed text-xs">
                    {{ $landingPage->whatsapp_welcome_message ?: 'Halo klinik kecantikan, saya ingin konsultasi perawatan kulit dan produk kecantikan.' }}
                </p>

                <a href="{{ $waUrl }}" target="_blank"
                    class="w-full py-2.5 px-3 rounded-2xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-all duration-150 active:scale-95">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Chat Sekarang via WA</span>
                </a>
            </div>

            <button type="button" @click="waChatOpen = !waChatOpen"
                class="w-13 h-13 rounded-full text-white shadow-[0_10px_25px_rgba(37,211,102,0.4)] flex items-center justify-center hover:scale-105 active:scale-95 transition-all duration-200 cursor-pointer"
                style="background-color: #25D366;"
                aria-label="Chat WhatsApp">
                <i data-lucide="message-circle" class="w-6 h-6"></i>
            </button>
        </div>
    @else
        <a href="{{ $waUrl }}" target="_blank"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs sm:text-sm text-white shadow-xs transition-all active:scale-95"
            style="background-color: #25D366;">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span>{{ $customLabel ?: 'Tanya via WhatsApp' }}</span>
        </a>
    @endif
@endif