<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-[#121214]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Otorisasi Koneksi ChatGPT — COOCA ID</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4 antialiased text-slate-800 dark:text-slate-100">

    <div class="w-full max-w-lg bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-slate-200/90 dark:border-white/10 shadow-xl overflow-hidden p-6 sm:p-8 space-y-6">

        {{-- Brand Logos Connection Header --}}
        <div class="flex items-center justify-center gap-4 py-2">
            <div class="w-14 h-14 rounded-[16px] bg-black text-white p-2.5 flex items-center justify-center shadow-xs">
                <img src="{{ asset('assets/image/cooca-icon.png') }}" class="w-full h-full object-contain" alt="COOCA ID" />
            </div>

            <div class="flex items-center justify-center text-slate-400 dark:text-slate-500">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
            </div>

            <div class="w-14 h-14 rounded-[16px] bg-[#10A37F] text-white p-3 flex items-center justify-center shadow-xs">
                {{-- OpenAI / ChatGPT Icon SVG --}}
                <svg class="w-full h-full text-white" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.6669zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.6069 1.4997-2.602-1.4997z"/>
                </svg>
            </div>
        </div>

        {{-- Title & Info --}}
        <div class="text-center space-y-1.5">
            <h1 class="text-lg font-bold text-slate-900 dark:text-white">
                Hubungkan COOCA dengan ChatGPT
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                Aplikasi <strong>ChatGPT</strong> meminta izin untuk mengakses Model Context Protocol (MCP) pada bisnis Anda.
            </p>
        </div>

        {{-- Active Account Box --}}
        <div class="p-3.5 rounded-[16px] bg-slate-50 dark:bg-white/5 border border-slate-200/80 dark:border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-full bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">
                    {{ strtoupper(substr($business->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-semibold text-slate-900 dark:text-white truncate">
                        {{ $business->name }}
                    </div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                        {{ $user->email }}
                    </div>
                </div>
            </div>
            <span class="text-[10.5px] px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-medium">
                Bisnis Aktif
            </span>
        </div>

        {{-- Scopes and Abilities List --}}
        <div class="space-y-2 text-xs">
            <div class="font-semibold text-slate-700 dark:text-slate-300 text-[11.5px] uppercase tracking-wider">
                Izin yang Akan Diberikan:
            </div>
            <ul class="space-y-2 text-slate-600 dark:text-slate-300 text-[12px]">
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5">✓</span>
                    <span><strong>Laporan Keuangan & Laba Rugi:</strong> Membaca ikhtisar pendapatan, beban operasional, dan arus kas.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">✓</span>
                    <span><strong>Inventori & Stok Produk:</strong> Memeriksa ketersediaan stok, harga jual, dan pergerakan barang.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 mt-0.5">✓</span>
                    <span><strong>CRM & Pelanggan:</strong> Melihat riwayat transaksi dan data pelanggan toko Anda.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5">✓</span>
                    <span><strong>Model Context Protocol (MCP):</strong> Menjalankan fungsi otonom COOCA ID yang diizinkan melalui AI.</span>
                </li>
            </ul>
        </div>

        {{-- Warning Note --}}
        <div class="p-3 rounded-[12px] bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 text-[11px] text-amber-800 dark:text-amber-300 leading-relaxed">
            Data Anda tetap aman dan terisolasi secara ketat pada tenant bisnis Anda. Akses ini dapat dicabut kapan saja melalui menu <strong>Pengaturan > Integrasi MCP</strong>.
        </div>

        {{-- Actions Form --}}
        <form method="POST" action="{{ url('/api/v1/mcp/oauth/authorize') }}" class="space-y-3 pt-2">
            @csrf
            <input type="hidden" name="client_id" value="{{ $clientId }}" />
            <input type="hidden" name="redirect_uri" value="{{ $redirectUri }}" />
            <input type="hidden" name="state" value="{{ $state }}" />
            <input type="hidden" name="scope" value="{{ $scope }}" />
            <input type="hidden" name="code_challenge" value="{{ $codeChallenge }}" />
            <input type="hidden" name="code_challenge_method" value="{{ $codeChallengeMethod }}" />

            <div class="flex flex-col sm:flex-row items-center gap-2.5">
                <button
                    type="submit"
                    name="action"
                    value="deny"
                    class="w-full sm:w-1/3 py-2.5 px-4 rounded-[12px] border border-slate-200 dark:border-white/10 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-medium text-xs transition-colors">
                    Batalkan
                </button>
                <button
                    type="submit"
                    name="action"
                    value="approve"
                    class="w-full sm:w-2/3 py-2.5 px-4 rounded-[12px] bg-[#10A37F] hover:bg-[#0E8A6B] text-white font-bold text-xs shadow-sm transition-all active:scale-[0.99] flex items-center justify-center gap-1.5">
                    <span>Setujui & Hubungkan ke ChatGPT</span>
                </button>
            </div>
        </form>

    </div>

</body>
</html>
