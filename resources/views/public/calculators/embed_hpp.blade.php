<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget Kalkulator HPP - Cooca</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 p-4 antialiased">
    <div x-data="{
        bahan: 15000,
        upah: 3000,
        overhead: 2000,
        margin: 30,
        get hppTotal() {
            return (parseFloat(this.bahan) || 0) + (parseFloat(this.upah) || 0) + (parseFloat(this.overhead) || 0);
        },
        get hargaJual() {
            let m = parseFloat(this.margin) || 0;
            if (m >= 100) m = 99;
            return Math.round(this.hppTotal / (1 - (m / 100)));
        },
        get labaKotor() {
            return this.hargaJual - this.hppTotal;
        },
        formatRupiah(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }
    }" class="max-w-md mx-auto bg-white rounded-2xl border border-slate-200/80 shadow-md p-5">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#007AFF]"></span>
                <h3 class="font-bold text-sm text-slate-900 tracking-tight">Kalkulator HPP &amp; Harga Jual</h3>
            </div>
            <span class="text-[10px] font-semibold uppercase tracking-wider bg-[#007AFF]/10 text-[#007AFF] px-2 py-0.5 rounded-full">Gratis</span>
        </div>

        <!-- Input Fields -->
        <div class="space-y-3 text-xs">
            <div>
                <label class="block font-medium text-slate-700 mb-1">1. Biaya Bahan Baku / Produk (Rp)</label>
                <input type="number" x-model.number="bahan" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 focus:border-[#007AFF] text-slate-800 font-semibold text-xs">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-medium text-slate-700 mb-1">2. Biaya Tenaga (Rp)</label>
                    <input type="number" x-model.number="upah" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 focus:border-[#007AFF] text-slate-800 text-xs">
                </div>
                <div>
                    <label class="block font-medium text-slate-700 mb-1">3. Overhead / Kemasan</label>
                    <input type="number" x-model.number="overhead" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/20 focus:border-[#007AFF] text-slate-800 text-xs">
                </div>
            </div>
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="font-medium text-slate-700">Target Margin Laba (%)</label>
                    <span class="font-bold text-[#007AFF]" x-text="margin + '%'"></span>
                </div>
                <input type="range" min="5" max="80" step="5" x-model.number="margin" class="w-full accent-[#007AFF] cursor-pointer">
            </div>
        </div>

        <!-- Results Card -->
        <div class="mt-4 bg-slate-900 text-white rounded-xl p-3.5 space-y-2">
            <div class="flex justify-between items-center text-xs">
                <span class="text-slate-400">Total HPP per Porsi:</span>
                <span class="font-bold text-white text-sm" x-text="formatRupiah(hppTotal)"></span>
            </div>
            <div class="flex justify-between items-center text-xs pt-2 border-t border-white/10">
                <span class="text-amber-300 font-medium">Rekomendasi Harga Jual:</span>
                <span class="font-extrabold text-amber-300 text-base" x-text="formatRupiah(hargaJual)"></span>
            </div>
            <div class="flex justify-between items-center text-[11px] text-slate-400">
                <span>Perkiraan Laba Bersih:</span>
                <span class="text-emerald-400 font-semibold" x-text="formatRupiah(labaKotor)"></span>
            </div>
        </div>

        <!-- Backlink Attribution Badge -->
        <div class="mt-3.5 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
            <span>Disematkan via Cooca OS</span>
            <a href="https://cooca.id" target="_blank" rel="noopener" class="font-semibold text-[#007AFF] hover:underline flex items-center gap-1">
                Aplikasi Kasir Cooca &rarr;
            </a>
        </div>
    </div>
</body>
</html>
