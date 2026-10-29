@if (isset($business) && (bool) ($business->is_demo ?? false))
    <div class="sticky top-0 z-[101] w-full bg-amber-500 text-amber-950 font-semibold text-xs py-2 px-4 text-center flex items-center justify-center gap-2 shadow-sm border-b border-amber-600/30">
        <i data-lucide="info" class="w-4 h-4 shrink-0 text-amber-900"></i>
        <span>Contoh toko demo COOCA &bull; Transaksi di toko ini bersifat simulasi untuk demonstrasi sistem.</span>
    </div>
@endif

