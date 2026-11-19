<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login - Cooca Console</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['-apple-system', '"SF Pro Text"', '"SF Pro Display"', 'Inter', 'system-ui', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'Menlo', 'monospace']
                    },
                    colors: {
                        cyber: {
                            blue: '#007AFF',
                            cyan: '#00F0FF',
                            indigo: '#5856D6',
                            violet: '#8B5CF6',
                            dark: '#07090E',
                            card: '#0B0F19'
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: -apple-system, "SF Pro Text", "SF Pro Display", Inter, system-ui, sans-serif;
            -webkit-font-smoothing: antialiased;
            background-color: #07090E;
        }

        /* Ambient Glow Animations */
        @keyframes pulse-slow {
            0%, 100% { opacity: 0.35; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.08); }
        }

        @keyframes laser-sweep {
            0% { transform: translateX(-150%) skewX(-20deg); }
            50%, 100% { transform: translateX(250%) skewX(-20deg); }
        }

        @keyframes radar-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes card-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }

        .animate-pulse-slow {
            animation: pulse-slow 8s infinite ease-in-out;
        }

        .animate-laser {
            animation: laser-sweep 3.5s infinite cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animate-radar {
            animation: radar-spin 12s linear infinite;
        }

        .cyber-grid {
            background-image: 
                linear-gradient(to right, rgba(0, 240, 255, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 240, 255, 0.04) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(ellipse 75% 75% at 50% 50%, #000 40%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 75% 75% at 50% 50%, #000 40%, transparent 100%);
        }

        .glass-cyber-card {
            background: rgba(11, 15, 25, 0.82);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
        }

        .cyber-border-wrap {
            position: relative;
            background: linear-gradient(135deg, rgba(0, 240, 255, 0.4) 0%, rgba(0, 122, 255, 0.15) 45%, rgba(139, 92, 246, 0.45) 100%);
            padding: 1px;
            border-radius: 32px;
            box-shadow: 
                0 0 50px -10px rgba(0, 122, 255, 0.35),
                0 25px 50px -12px rgba(0, 0, 0, 0.85);
        }

        .cyber-input {
            background: rgba(255, 255, 255, 0.035);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .cyber-input:focus {
            background: rgba(0, 122, 255, 0.06);
            border-color: #00F0FF;
            box-shadow: 0 0 20px -3px rgba(0, 240, 255, 0.25);
            outline: none;
        }

        .cyber-btn-glow {
            box-shadow: 0 0 25px -4px rgba(0, 122, 255, 0.55);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .cyber-btn-glow:hover {
            box-shadow: 0 0 40px -2px rgba(0, 240, 255, 0.7);
            transform: translateY(-1px);
        }

        /* Custom digit tracking in TOTP field */
        .totp-input {
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.38em;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-pulse-slow,
            .animate-laser,
            .animate-radar {
                animation: none !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-[#07090E] text-white flex items-center justify-center p-4 sm:p-6 antialiased selection:bg-[#00F0FF]/30 relative overflow-x-hidden">

    <!-- Interactive Neural Network Particles Canvas -->
    <canvas id="neuralCanvas" class="fixed inset-0 pointer-events-none z-0 opacity-60"></canvas>

    <!-- Futuristic Ambient Glow Orbs -->
    <div class="fixed top-[-10%] left-[-5%] w-[450px] sm:w-[600px] h-[450px] sm:h-[600px] bg-gradient-to-br from-[#00F0FF]/15 to-[#007AFF]/10 rounded-full blur-[130px] pointer-events-none animate-pulse-slow z-0"></div>
    <div class="fixed bottom-[-10%] right-[-5%] w-[450px] sm:w-[650px] h-[450px] sm:h-[650px] bg-gradient-to-tl from-[#8B5CF6]/15 via-[#5856D6]/10 to-transparent rounded-full blur-[140px] pointer-events-none animate-pulse-slow z-0" style="animation-delay: -4s;"></div>

    <!-- Cyber Digital Grid Overlay -->
    <div class="fixed inset-0 cyber-grid pointer-events-none z-0"></div>

    <!-- Main Container -->
    <div class="w-full max-w-[450px] my-auto relative z-10">

        <!-- Top Futuristic Cyber HUD Badge -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/[0.04] border border-white/10 shadow-[0_0_15px_rgba(0,240,255,0.15)] mb-4 backdrop-blur-md">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#00F0FF] opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-[#00F0FF]"></span>
                </span>
                <span class="font-mono text-[11px] font-semibold tracking-wider text-[#00F0FF] uppercase">
                    {{ __('admin.keamanan_konsol_aktif') }}
                </span>
            </div>

            <!-- Animated Hologram Shield -->
            <div class="relative inline-flex items-center justify-center w-20 h-20 mb-3">
                <!-- Concentric Rotating Radar Ring -->
                <div class="absolute inset-0 rounded-full border border-dashed border-[#00F0FF]/30 animate-radar"></div>
                <!-- Glowing Outer Pulse Ring -->
                <div class="absolute inset-1.5 rounded-full bg-gradient-to-tr from-[#007AFF]/30 to-[#8B5CF6]/30 blur-md"></div>
                <!-- Core Hex/Shield Squircle -->
                <div class="relative w-16 h-16 rounded-[22px] bg-gradient-to-tr from-[#007AFF] via-[#0051FF] to-[#00F0FF] flex items-center justify-center shadow-[0_0_30px_rgba(0,240,255,0.45)] border border-white/25">
                    <i data-lucide="shield-check" class="w-8 h-8 text-white drop-shadow-[0_2px_8px_rgba(0,0,0,0.5)]" stroke-width="2"></i>
                </div>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-1.5">Admin Console</h1>
            <p class="text-xs sm:text-sm text-white/60 max-w-sm mx-auto leading-relaxed">
                {{ __('admin.pusat_kontrol_admin_deskripsi') }}
            </p>
        </div>

        <!-- Futuristic Cyber-Border Card Wrap -->
        <div class="cyber-border-wrap">
            <div class="glass-cyber-card rounded-[31px] p-6 sm:p-8 relative overflow-hidden">

                <!-- Subtle Top Card Scanner Line Highlight -->
                <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-[#00F0FF]/80 to-transparent"></div>

                <!-- Status Alert -->
                @if (session('status'))
                    <div class="mb-5 p-3.5 rounded-[16px] bg-[#34C759]/10 border border-[#34C759]/30 text-[#30D158] text-xs sm:text-sm flex items-center gap-2.5 shadow-[0_0_20px_rgba(52,199,89,0.15)]">
                        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0" stroke-width="2"></i>
                        <span class="leading-relaxed">{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Errors Alert -->
                @if ($errors->any())
                    <div class="mb-5 p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/30 text-[#FF453A] text-xs sm:text-sm space-y-1.5 shadow-[0_0_20px_rgba(255,59,48,0.15)]">
                        <div class="font-semibold flex items-center gap-2">
                            <i data-lucide="alert-octagon" class="w-4 h-4 shrink-0" stroke-width="2"></i>
                            <span>{{ __('admin.otentikasi_gagal') }}:</span>
                        </div>
                        @foreach ($errors->all() as $error)
                            <p class="pl-6 text-xs text-white/90 leading-relaxed">• {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Login Form with Google Authenticator Support -->
                <form method="POST" action="{{ route('admin.login') }}" class="space-y-4" x-data="{ showPass: false, totpDigits: '{{ strlen(old('totp_code', '')) }}' }">
                    @csrf

                    <!-- Email Input (Anti-Zoom text-[16px]) -->
                    <div>
                        <label for="email" class="block text-xs sm:text-sm font-semibold text-white/85 mb-1.5 flex items-center justify-between">
                            <span>Email Administrator <span class="text-[#00F0FF]">*</span></span>
                            <span class="font-mono text-[10px] text-white/40 tracking-wider">ROOT ID</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-white/40">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email', '') }}" required
                                autofocus placeholder="admin@cooca.id"
                                class="w-full pl-10 pr-4 py-3 cyber-input rounded-[14px] text-white placeholder:text-white/25 text-[16px] sm:text-sm">
                        </div>
                    </div>

                    <!-- Password Input with Eye Toggle (Anti-Zoom text-[16px]) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs sm:text-sm font-semibold text-white/85 flex items-center gap-1.5">
                                <span>Kata Sandi <span class="text-[#00F0FF]">*</span></span>
                            </label>
                            <a href="{{ route('admin.password.request') }}"
                                class="text-xs font-semibold text-[#00F0FF] hover:text-white transition-colors duration-200">
                                {{ __('admin.lupa_kata_sandi') }}
                            </a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-white/40">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" name="password" id="password" required
                                placeholder="{{ __('admin.masukkan_sandi_superadmin') }}"
                                class="w-full pl-10 pr-11 py-3 cyber-input rounded-[14px] text-white placeholder:text-white/25 text-[16px] sm:text-sm">
                            <button type="button" @click="showPass = !showPass"
                                :aria-label="showPass ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                class="absolute right-0 top-0 bottom-0 w-11 flex items-center justify-center text-white/40 hover:text-white focus:outline-none transition-colors cursor-pointer">
                                <i :data-lucide="showPass ? 'eye-off' : 'eye'" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Google Authenticator (TOTP) Input Module -->
                    <div class="p-3.5 rounded-[18px] bg-white/[0.025] border border-white/[0.08] relative overflow-hidden">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="totp_code" class="block text-xs sm:text-sm font-semibold text-white/90 flex items-center gap-2">
                                <i data-lucide="key-round" class="w-4 h-4 text-[#00F0FF]"></i>
                                <span>{{ __('admin.kode_google_authenticator') }}</span>
                            </label>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-[#00F0FF]/15 border border-[#00F0FF]/30 text-[#00F0FF] font-mono text-[10px] font-bold">
                                2FA TOTP
                            </span>
                        </div>

                        <div class="relative mt-2">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#00F0FF]/70">
                                <i data-lucide="shield-alert" class="w-4 h-4"></i>
                            </div>
                            <input type="text" name="totp_code" id="totp_code" value="{{ old('totp_code', '') }}"
                                inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                                placeholder="000000"
                                x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').slice(0, 6); totpDigits = $el.value.length"
                                class="w-full pl-10 pr-4 py-3 cyber-input rounded-[14px] text-white text-center font-mono font-bold text-lg sm:text-xl tracking-[0.38em] placeholder:text-white/20 text-[16px]">
                        </div>

                        <!-- Dynamic 6-Digit Progress Indicator -->
                        <div class="flex items-center justify-center gap-1.5 mt-2.5">
                            <template x-for="i in 6" :key="i">
                                <span class="h-1 flex-1 rounded-full transition-all duration-300"
                                    :class="i <= totpDigits ? 'bg-[#00F0FF] shadow-[0_0_8px_#00F0FF]' : 'bg-white/10'"></span>
                            </template>
                        </div>

                        <p class="text-[11px] text-white/50 mt-2 flex items-center gap-1.5 leading-snug">
                            <i data-lucide="info" class="w-3.5 h-3.5 shrink-0 text-[#00F0FF]/70"></i>
                            <span>{{ __('admin.bantuan_totp_admin') }} (Google Authenticator / Authy).</span>
                        </p>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer text-white/70 hover:text-white text-xs sm:text-sm select-none">
                            <input type="checkbox" name="remember"
                                class="w-4 h-4 rounded-[5px] border-white/20 bg-white/5 text-[#007AFF] focus:ring-[#00F0FF] focus:ring-offset-0 focus:ring-1">
                            <span>{{ __('admin.ingat_sesi_masuk') }}</span>
                        </label>
                    </div>

                    <!-- Futuristic Animated Gradient CTA Button (min-h-[50px] Touch Target) -->
                    <button type="submit"
                        class="w-full min-h-[50px] py-3.5 px-4 rounded-[16px] cyber-btn-glow bg-gradient-to-r from-[#007AFF] via-[#0051FF] to-[#00F0FF] text-white font-bold text-sm sm:text-base shadow-xl flex items-center justify-center gap-2 relative overflow-hidden group active:scale-[0.98] transition-all duration-200">
                        <!-- Laser Light Beam Sweep Animation -->
                        <div class="absolute inset-0 w-full h-full pointer-events-none overflow-hidden">
                            <div class="w-1/2 h-full bg-gradient-to-r from-transparent via-white/35 to-transparent skew-x-[-20deg] animate-laser"></div>
                        </div>

                        <span class="relative z-10">{{ __('admin.masuk_ke_admin_console') }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 relative z-10 transition-transform group-hover:translate-x-1 duration-200"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Back to Public Link & Cryptographic Badge -->
        <div class="text-center mt-6 space-y-3">
            <div>
                <a href="{{ route('landing') }}"
                    class="inline-flex items-center gap-2 text-xs sm:text-sm text-[#00F0FF] hover:text-white font-medium transition-colors duration-200 group">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1 duration-200"></i>
                    <span>{{ __('admin.kembali_ke_halaman_publik_cooca') }}</span>
                </a>
            </div>
            <div class="text-[10px] font-mono text-white/35 tracking-widest uppercase">
                COOCA CORE // AES-256 GCM SECURE KERNEL
            </div>
        </div>
    </div>

    <!-- Neural Network Particles Canvas Animation Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }

            // Interactive Canvas Particle Constellation
            const canvas = document.getElementById('neuralCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let width = canvas.width = window.innerWidth;
            let height = canvas.height = window.innerHeight;

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (prefersReducedMotion) return;

            const particleCount = Math.min(Math.floor((width * height) / 24000), 65);
            const particles = [];
            const mouse = { x: -1000, y: -1000, radius: 140 };

            window.addEventListener('resize', () => {
                width = canvas.width = window.innerWidth;
                height = canvas.height = window.innerHeight;
            });

            window.addEventListener('mousemove', (e) => {
                mouse.x = e.clientX;
                mouse.y = e.clientY;
            });

            window.addEventListener('mouseleave', () => {
                mouse.x = -1000;
                mouse.y = -1000;
            });

            class Particle {
                constructor() {
                    this.x = Math.random() * width;
                    this.y = Math.random() * height;
                    this.vx = (Math.random() - 0.5) * 0.45;
                    this.vy = (Math.random() - 0.5) * 0.45;
                    this.radius = Math.random() * 1.8 + 1;
                    this.color = Math.random() > 0.4 ? 'rgba(0, 240, 255, ' : 'rgba(0, 122, 255, ';
                }

                update() {
                    this.x += this.vx;
                    this.y += this.vy;

                    if (this.x < 0 || this.x > width) this.vx *= -1;
                    if (this.y < 0 || this.y > height) this.vy *= -1;

                    // Mouse interaction
                    const dx = mouse.x - this.x;
                    const dy = mouse.y - this.y;
                    const distance = Math.sqrt(dx * dx + dy * dy);
                    if (distance < mouse.radius) {
                        const force = (mouse.radius - distance) / mouse.radius;
                        this.x -= (dx / distance) * force * 1.5;
                        this.y -= (dy / distance) * force * 1.5;
                    }
                }

                draw() {
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
                    ctx.fillStyle = this.color + '0.7)';
                    ctx.shadowBlur = 8;
                    ctx.shadowColor = '#00F0FF';
                    ctx.fill();
                    ctx.shadowBlur = 0;
                }
            }

            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }

            let animationFrameId;
            function animate() {
                ctx.clearRect(0, 0, width, height);

                for (let i = 0; i < particles.length; i++) {
                    particles[i].update();
                    particles[i].draw();

                    for (let j = i + 1; j < particles.length; j++) {
                        const dx = particles[i].x - particles[j].x;
                        const dy = particles[i].y - particles[j].y;
                        const dist = Math.sqrt(dx * dx + dy * dy);

                        if (dist < 110) {
                            ctx.beginPath();
                            ctx.moveTo(particles[i].x, particles[i].y);
                            ctx.lineTo(particles[j].x, particles[j].y);
                            const alpha = (1 - dist / 110) * 0.22;
                            ctx.strokeStyle = `rgba(0, 240, 255, ${alpha})`;
                            ctx.lineWidth = 0.75;
                            ctx.stroke();
                        }
                    }
                }

                animationFrameId = requestAnimationFrame(animate);
            }

            animate();

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    cancelAnimationFrame(animationFrameId);
                } else {
                    animate();
                }
            });
        });
    </script>
</body>

</html>
