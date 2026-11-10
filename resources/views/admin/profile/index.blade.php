@extends('layouts.admin', [
    'title' => __('admin.profil_ganti_kata_sandi_administrator_admin'),
    'headerTitle' => 'Profil & Keamanan Akun Admin',
    'headerSubtitle' => 'Kelola identitas akun superadministrator dan perbarui kata sandi akses sistem',
])

@section('content')
<div class="max-w-5xl space-y-6" x-data="{
    showCurrent: false,
    showNew: false,
    showConfirm: false,
}">

    <!-- Bento Hero Card: Superadmin Identity Overview -->
    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div class="flex items-center gap-4 min-w-0">
                <div class="w-14 h-14 rounded-[18px] bg-gradient-to-tr from-[#007AFF] to-[#5856D6] flex items-center justify-center text-white text-[20px] font-extrabold shadow-md shadow-[#007AFF]/25 shrink-0">
                    {{ strtoupper(substr($admin->name ?? 'A', 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-[18px] sm:text-[20px] font-bold text-black dark:text-white truncate">{{ $admin->name }}</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                            <span>{{ strtoupper($admin->role ?? 'SUPER_ADMIN') }}</span>
                        </span>
                    </div>
                    <div class="text-[13px] text-black/55 dark:text-white/55 mt-0.5 flex items-center gap-2 flex-wrap">
                        <span>{{ $admin->email }}</span>
                        <span>•</span>
                        <span>Sesi Aktif Konsol</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                <div class="px-3.5 py-2 rounded-[14px] bg-black/[0.03] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06] text-right">
                    <div class="text-[10.5px] font-bold uppercase tracking-wider text-black/45 dark:text-white/45">Status 2FA TOTP</div>
                    @if($has2FA)
                    <div class="text-[12px] font-bold text-[#34C759] dark:text-[#30D158] flex items-center gap-1.5 justify-end mt-0.5">
                        <i data-lucide="shield-check" class="w-4 h-4" stroke-width="2"></i>
                        <span>Google 2FA Aktif</span>
                    </div>
                    @else
                    <div class="text-[12px] font-bold text-[#FF9500] dark:text-[#FF9F0A] flex items-center gap-1.5 justify-end mt-0.5">
                        <i data-lucide="shield-alert" class="w-4 h-4" stroke-width="2"></i>
                        <span>Belum Aktif</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 2-Column Bento Grid: Edit Profile & Change Password -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Form Update Profil Admin -->
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm flex flex-col justify-between space-y-5">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="w-10 h-10 rounded-[14px] bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center shrink-0">
                        <i data-lucide="user" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.profil_administrator') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Perbarui nama dan email login admin</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4 pt-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Nama Lengkap <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $admin->name) }}" required
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Alamat Email <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                            Role Administrator
                        </label>
                        <input type="text" value="{{ ucfirst(str_replace('_', ' ', $admin->role)) }}" disabled
                            class="w-full h-11 px-4 bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04] rounded-[12px] text-[16px] sm:text-[14px] text-black/50 dark:text-white/50 font-semibold cursor-not-allowed">
                    </div>

                    <div class="pt-2 flex justify-end">
                        @if(Auth::guard('admin')->user()?->hasPermission('admin.profile.update'))
                        <button type="submit"
                            class="w-full sm:w-auto h-12 sm:h-10 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-bold transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4" stroke-width="2"></i>
                            <span>{{ __('admin.simpan_profil') }}</span>
                        </button>
                        @endif
                    </div>
                </form>
            </div>

            <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-[11px] text-black/45 dark:text-white/45 flex items-center gap-1.5">
                <i data-lucide="info" class="w-3.5 h-3.5 text-[#007AFF] shrink-0" stroke-width="2"></i>
                <span>Email digunakan untuk menerima reset password &amp; notifikasi sistem</span>
            </div>
        </div>

        <!-- Form Ganti Password Admin -->
        <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm flex flex-col justify-between space-y-5">
            <div>
                <div class="flex items-center gap-3 pb-4 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="w-10 h-10 rounded-[14px] bg-[#5856D6]/10 text-[#5856D6] dark:text-[#5E5CE6] flex items-center justify-center shrink-0">
                        <i data-lucide="key-round" class="w-5 h-5" stroke-width="1.8"></i>
                    </div>
                    <div>
                        <h3 class="text-[16px] font-bold text-black dark:text-white">{{ __('admin.ganti_kata_sandi') }}</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Perbarui kata sandi untuk keamanan akses admin</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4 pt-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">
                                Kata Sandi Saat Ini <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                            </label>
                            <button type="button" @click="showCurrent = !showCurrent"
                                class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline cursor-pointer">
                                <span x-text="showCurrent ? 'Sembunyikan' : 'Lihat'"></span>
                            </button>
                        </div>
                        <input :type="showCurrent ? 'text' : 'password'" name="current_password" required placeholder="••••••••"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">
                                Kata Sandi Baru <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                            </label>
                            <button type="button" @click="showNew = !showNew"
                                class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline cursor-pointer">
                                <span x-text="showNew ? 'Sembunyikan' : 'Lihat'"></span>
                            </button>
                        </div>
                        <input :type="showNew ? 'text' : 'password'" name="password" required placeholder="{{ __('admin.minimal_8_karakter') }}"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-[13px] font-semibold text-black/75 dark:text-white/75">
                                Konfirmasi Kata Sandi Baru <span class="text-[#FF3B30] dark:text-[#FF453A]">*</span>
                            </label>
                            <button type="button" @click="showConfirm = !showConfirm"
                                class="text-[11px] font-semibold text-[#007AFF] dark:text-[#0A84FF] hover:underline cursor-pointer">
                                <span x-text="showConfirm ? 'Sembunyikan' : 'Lihat'"></span>
                            </button>
                        </div>
                        <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required placeholder="{{ __('admin.ulangi_kata_sandi_baru') }}"
                            class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[12px] text-[16px] sm:text-[14px] font-mono text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                    </div>

                    <div class="pt-2 flex justify-end">
                        @if(Auth::guard('admin')->user()?->hasPermission('admin.profile.update'))
                        <button type="submit"
                            class="w-full sm:w-auto h-12 sm:h-10 px-5 rounded-[12px] bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] text-white text-[13px] font-bold transition-all flex items-center justify-center gap-2 shadow-md shadow-[#007AFF]/25 cursor-pointer">
                            <i data-lucide="shield-check" class="w-4 h-4" stroke-width="2"></i>
                            <span>{{ __('admin.ubah_kata_sandi') }}</span>
                        </button>
                        @endif
                    </div>
                </form>
            </div>

            <div class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] text-[11px] text-black/45 dark:text-white/45 flex items-center gap-1.5">
                <i data-lucide="lock" class="w-3.5 h-3.5 text-[#34C759] shrink-0" stroke-width="2"></i>
                <span>Gunakan kombinasi huruf, angka, dan simbol untuk keamanan maksimal</span>
            </div>
        </div>

    </div>

    <!-- Google Authenticator (2FA) TOTP Management Bento Section -->
    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md p-6 sm:p-7 shadow-sm space-y-5" x-data="{
        copied: false,
        showDisableForm: false,
        copySecret() {
            navigator.clipboard.writeText('{{ $twoFactorSecret }}');
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-black/[0.04] dark:border-white/[0.06] gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[14px] {{ $has2FA ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-[#FF9500]/10 text-[#FF9500]' }} flex items-center justify-center shrink-0">
                    <i data-lucide="{{ $has2FA ? 'shield-check' : 'smartphone' }}" class="w-5 h-5" stroke-width="1.8"></i>
                </div>
                <div>
                    <h3 class="text-[16px] font-bold text-black dark:text-white">Otentikasi Dua Faktor (Google Authenticator 2FA)</h3>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Wajib untuk otorisasi tindakan kritis seperti hard delete bisnis dan pengguna</p>
                </div>
            </div>

            @if($has2FA)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] border border-[#34C759]/25 self-start sm:self-auto">
                <i data-lucide="check-circle" class="w-3.5 h-3.5" stroke-width="2"></i>
                <span>Aktif &amp; Terverifikasi</span>
            </span>
            @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold bg-[#FF9500]/15 text-[#C93400] dark:text-[#FF9F0A] border border-[#FF9500]/25 self-start sm:self-auto">
                <i data-lucide="alert-circle" class="w-3.5 h-3.5" stroke-width="2"></i>
                <span>Belum Dikonfigurasi</span>
            </span>
            @endif
        </div>

        @if($has2FA)
        <div class="space-y-4">
            <div class="p-4 rounded-[16px] bg-[#34C759]/[0.08] dark:bg-[#34C759]/[0.12] border border-[#34C759]/20 text-[13px] text-black/80 dark:text-white/90 space-y-1">
                <div class="font-bold text-[#248A3D] dark:text-[#30D158] flex items-center gap-2">
                    <i data-lucide="shield" class="w-4 h-4" stroke-width="2"></i>
                    <span>Akun Administrator Telah Dilindungi TOTP RFC 6238</span>
                </div>
                <p class="text-black/60 dark:text-white/60 text-[12px]">
                    Diverifikasi sejak: <span class="font-semibold text-black dark:text-white">{{ $admin->two_factor_confirmed_at?->format('d M Y, H:i') ?? '-' }} WIB</span>. Setiap permintaan penghapusan permanen tenant bisnis maupun pengguna akan meminta 6 digit kode dari aplikasi Google Authenticator Anda.
                </p>
            </div>

            <div>
                <button type="button" @click="showDisableForm = !showDisableForm"
                    class="text-[12px] font-semibold text-[#FF3B30] dark:text-[#FF453A] hover:underline flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="settings" class="w-3.5 h-3.5" stroke-width="2"></i>
                    <span x-text="showDisableForm ? 'Tutup Formulir Nonaktifkan' : 'Konfigurasi Ulang atau Nonaktifkan 2FA'"></span>
                </button>
            </div>

            <!-- Form Nonaktifkan 2FA (Collapsible) -->
            <div x-show="showDisableForm" x-cloak class="pt-3 border-t border-black/[0.04] dark:border-white/[0.06] space-y-3">
                <p class="text-[12px] text-black/60 dark:text-white/60">
                    Masukkan kata sandi akun dan 6 digit kode dari Google Authenticator untuk menonaktifkan 2FA:
                </p>
                <form method="POST" action="{{ route('admin.profile.2fa.disable') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @csrf
                    <div>
                        <input type="password" name="password" required placeholder="Kata Sandi Administrator"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[10px] text-[13px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition">
                    </div>
                    <div>
                        <input type="text" name="totp_code" maxlength="6" pattern="[0-9]{6}" required placeholder="6-digit OTP"
                            class="w-full h-10 px-3.5 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] rounded-[10px] text-[13px] font-mono tracking-widest text-center text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition">
                    </div>
                    <div>
                        <button type="submit"
                            class="w-full h-10 px-4 rounded-[10px] bg-[#FF3B30] hover:bg-[#D70015] text-white text-[12px] font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                            <i data-lucide="slash" class="w-4 h-4" stroke-width="2"></i>
                            <span>Nonaktifkan 2FA</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            <!-- QR Code Container -->
            <div class="md:col-span-4 flex flex-col items-center text-center p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.04] border border-black/[0.04] dark:border-white/[0.06]">
                <div class="w-44 h-44 p-2 bg-white rounded-[16px] shadow-sm flex items-center justify-center">
                    {!! $qrCodeSvg !!}
                </div>
                <div class="mt-2.5 text-[11px] font-medium text-black/50 dark:text-white/50">
                    Pindai dengan aplikasi Google Authenticator
                </div>
            </div>

            <!-- Instructions & Activation Form -->
            <div class="md:col-span-8 space-y-4">
                <div class="space-y-1.5">
                    <h4 class="text-[14px] font-bold text-black dark:text-white">Langkah 1: Hubungkan Aplikasi Authenticator</h4>
                    <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                        Buka aplikasi <strong>Google Authenticator</strong> (atau aplikasi TOTP pilihan Anda) di smartphone, lalu pilih <strong>Scan a QR code</strong> dan arahkan kamera ke kode di samping.
                    </p>
                    <div class="pt-1 flex items-center gap-2">
                        <span class="text-[11px] text-black/45 dark:text-white/45">Atau masukkan manual:</span>
                        <code class="px-2 py-0.5 rounded bg-black/[0.05] dark:bg-white/[0.08] text-[11px] font-mono font-bold text-black dark:text-white tracking-widest">{{ $twoFactorSecret }}</code>
                        <button type="button" @click="copySecret"
                            class="text-[11px] font-semibold text-[#007AFF] hover:underline flex items-center gap-1 cursor-pointer">
                            <i data-lucide="copy" class="w-3 h-3" stroke-width="2"></i>
                            <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
                        </button>
                    </div>
                </div>

                <div class="space-y-2 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
                    <h4 class="text-[14px] font-bold text-black dark:text-white">Langkah 2: Konfirmasi Kode 6 Digit</h4>
                    <p class="text-[12px] text-black/60 dark:text-white/60">
                        Ketik 6 digit angka yang muncul di Google Authenticator untuk memverifikasi pemasangan:
                    </p>

                    <form method="POST" action="{{ route('admin.profile.2fa.enable') }}" class="flex flex-col sm:flex-row items-center gap-3">
                        @csrf
                        <input type="hidden" name="secret" value="{{ $twoFactorSecret }}">

                        <div class="w-full sm:w-48">
                            <input type="text" name="totp_code" maxlength="6" pattern="[0-9]{6}" required placeholder="000000"
                                class="w-full h-11 px-4 text-center font-mono text-[18px] tracking-[0.25em] font-bold bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                        </div>

                        <button type="submit"
                            class="w-full sm:w-auto h-11 px-5 rounded-[12px] bg-[#34C759] hover:bg-[#2EB14F] active:scale-[0.98] text-white text-[13px] font-bold transition flex items-center justify-center gap-2 shadow-sm cursor-pointer shrink-0">
                            <i data-lucide="check" class="w-4 h-4" stroke-width="2.2"></i>
                            <span>Verifikasi &amp; Aktifkan 2FA</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Reassuring Microcopy Banner -->
    <div class="p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.04] dark:border-white/[0.04] text-[12px] text-black/55 dark:text-white/55 flex items-center gap-2.5">
        <i data-lucide="sparkles" class="w-4 h-4 text-[#007AFF] shrink-0" stroke-width="2"></i>
        <span>Tenang: Riwayat audit trail dan hak akses administrator Anda tetap terlindungi saat memperbarui informasi profil.</span>
    </div>

</div>
@endsection
