{{-- ========================================================================= --}}
{{-- WHATSAPP INQUIRY: Bakery & Cake (Artisan Bakery, Pastry, Custom Cake)    --}}
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
    $rawPhone = $landingPage->whatsapp_number ?? ($business->phone ?? '');
    $waNumber = preg_replace('/[^0-9]/', '', (string) $rawPhone);
    if (str_starts_with($waNumber, '0')) {
        $waNumber = '62' . substr($waNumber, 1);
    }

    $defaultGreeting = $landingPage->whatsapp_welcome_message ?: 'Halo Baker ' . ($business->name ?? 'Artisan Bakery') . ', saya ingin konsultasi pemesanan roti fresh / custom cake untuk acara. Boleh info katalog dan jadwal slot oven?';
    
    if ($productName) {
        $defaultGreeting = 'Halo ' . ($business->name ?? 'Baker') . ', saya ingin menanyakan ketersediaan dan pemesanan untuk: *' . $productName . '* di ' . url()->current();
    }

    $waUrl = !empty($waNumber) ? 'https://wa.me/' . $waNumber . '?text=' . urlencode($defaultGreeting) : '#';
@endphp

@if ($hasWhatsapp && !empty($waNumber))
    @if ($mode === 'floating')
        {{-- Floating Concierge for Desktop / Tablet (Mobile is covered by Bottom Tab Bar) --}}
        <div class="hidden md:flex fixed bottom-6 right-6 z-40 flex-col items-end"
             x-data="{ waChatOpen: false }"
             @keydown.escape.window="waChatOpen = false">
            
            {{-- Chat Popover Card --}}
            <div x-show="waChatOpen" 
                 x-cloak
                 x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 @click.outside="waChatOpen = false"
                 class="mb-3.5 w-84 p-4.5 rounded-3xl bg-[#FFFDF5]/95 dark:bg-[#18110B]/95 backdrop-blur-xl shadow-[0_24px_50px_rgba(217,119,6,0.18)] dark:shadow-[0_24px_50px_rgba(0,0,0,0.6)] border border-amber-200/80 dark:border-amber-900/50 text-xs space-y-3.5">
                
                {{-- Header --}}
                <div class="flex items-center justify-between gap-3 border-b border-amber-100 dark:border-amber-950/60 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="relative w-9 h-9 rounded-2xl bg-amber-500/15 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold shadow-xs">
                            <i data-lucide="cake-slice" class="w-4.5 h-4.5"></i>
                            <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#18110B]"></span>
                        </div>
                        <div>
                            <div class="font-serif font-bold text-neutral-900 dark:text-amber-100 text-sm leading-tight">
                                {{ $business->name ?? 'Artisan Patisserie' }}
                            </div>
                            <div class="text-[10.5px] text-emerald-700 dark:text-emerald-400 font-medium flex items-center gap-1.5 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Baker & Pastry Concierge Online</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" 
                            @click="waChatOpen = false"
                            class="w-7 h-7 rounded-full flex items-center justify-center text-neutral-400 hover:text-neutral-700 dark:hover:text-amber-200 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer"
                            aria-label="{{ __('storefront.close_chat_window') }}">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                {{-- Baker Greeting Note --}}
                <div class="p-3 rounded-2xl bg-amber-500/8 dark:bg-amber-500/10 border border-amber-500/15 text-neutral-700 dark:text-neutral-300 leading-relaxed text-[11.5px]">
                    <p class="font-medium text-amber-900 dark:text-amber-200 mb-1 flex items-center gap-1">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Butuh Custom Cake atau Pesanan Khusus?</span>
                    </p>
                    <p>
                        {{ $landingPage->whatsapp_welcome_message ?: 'Tim pastry chef kami siap membantu konsultasi desain tart, request rasa, atau jadwal baking sourdough hangat.' }}
                    </p>
                </div>

                {{-- Direct CTA --}}
                <a href="{{ $waUrl }}" 
                   target="_blank"
                   rel="noopener noreferrer"
                   class="w-full py-3 px-4 rounded-2xl bg-[#25D366] hover:bg-[#20BD5A] text-white font-semibold text-xs flex items-center justify-center gap-2 shadow-[0_8px_20px_rgba(37,211,102,0.3)] transition-all duration-150 hover:scale-[1.02] active:scale-[0.98]">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>Mulai Chat WhatsApp</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-auto"></i>
                </a>
            </div>

            {{-- Floating Pill Trigger --}}
            <button type="button" 
                    @click="waChatOpen = !waChatOpen"
                    class="group flex items-center gap-2.5 h-12 px-4 rounded-full bg-[#18110B] dark:bg-[#22160E] text-amber-100 hover:text-white border border-amber-500/30 shadow-[0_12px_30px_rgba(24,17,11,0.35)] hover:shadow-[0_16px_36px_rgba(217,119,6,0.3)] transition-all duration-200 cursor-pointer hover:-translate-y-0.5 active:scale-95"
                    aria-label="{{ __('storefront.consult_cake_order_label') }}">
                <span class="w-7 h-7 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-xs">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                </span>
                <span class="text-xs font-semibold tracking-wide pr-1">
                    Tanya Baker
                </span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            </button>
        </div>
    @else
        {{-- Inline Mode (Cards, Product Detail, Footer) --}}
        <a href="{{ $waUrl }}" 
           target="_blank"
           rel="noopener noreferrer"
           class="inline-flex items-center justify-center gap-2.5 min-h-[44px] px-5 py-2.5 rounded-2xl font-semibold text-xs sm:text-sm text-white shadow-[0_4px_16px_rgba(37,211,102,0.25)] hover:shadow-[0_8px_24px_rgba(37,211,102,0.35)] transition-all duration-150 hover:scale-[1.02] active:scale-[0.98]"
           style="background-color: #25D366;">
            <i data-lucide="message-circle" class="w-4.5 h-4.5"></i>
            <span>{{ $customLabel ?: 'Konsultasi via WhatsApp' }}</span>
        </a>
    @endif
@endif