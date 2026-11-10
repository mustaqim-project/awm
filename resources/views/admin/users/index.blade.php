@extends('layouts.admin', [
    'title' => __('admin.kelola_pengguna_admin_console'),
    'headerTitle' => __('admin.manajemen_pengguna_ekspor_data'),
    'headerSubtitle' => __('admin.kelola_seluruh_pengguna_terdaftar_pantau'),
])

@section('content')
    <div class="space-y-6" x-data="adminUsersManager()">

        <!-- Toolbar: Search + Export -->
        <div
            class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 p-3 sm:p-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1 max-w-md">
                <div class="relative">
                    <i data-lucide="search"
                        class="w-4 h-4 text-black/35 dark:text-white/35 absolute left-3 top-1/2 -translate-y-1/2"
                        stroke-width="1.5"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="{{ __('admin.cari_nama_atau_email_pengguna') }}"
                        class="w-full h-9 pl-9 pr-3 bg-black/[0.04] dark:bg-white/[0.06] border-none rounded-[10px] text-[13px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/50 transition">
                </div>
            </form>

            @if (Auth::guard('admin')->user()?->hasPermission('admin.users.export'))
                <a href="{{ route('admin.users.export') }}"
                    class="h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/80 dark:text-white/80 bg-black/[0.06] dark:bg-white/[0.08] hover:bg-black/[0.09] dark:hover:bg-white/[0.12] active:scale-[0.97] active:opacity-80 transition-all flex items-center justify-center gap-1.5 shrink-0">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-[#007AFF] dark:text-[#0A84FF]" stroke-width="1.5"></i>
                    <span>{{ __('admin.ekspor_csv_email_nama') }}</span>
                </a>
            @endif
        </div>

        <!-- Users Table -->
        <div class="rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-black/5 dark:border-white/10">
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('admin.nama_pengguna') }}</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('admin.alamat_email') }}</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('admin.bisnis_aktif') }}</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('admin.metode_login') }}</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40">{{ __('admin.tanggal_registrasi') }}</th>
                            <th class="px-4 py-2.5 text-[11px] font-semibold text-black/40 dark:text-white/40 text-right">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($users as $user)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition-colors">
                                <td class="px-4 py-3 font-medium text-black dark:text-white">
                                    <div class="flex items-center gap-2.5">
                                        @if ($user->avatar)
                                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                                class="w-7 h-7 rounded-full object-cover">
                                        @else
                                            <div
                                                class="w-7 h-7 rounded-full bg-[#007AFF]/10 text-[#007AFF] dark:text-[#0A84FF] flex items-center justify-center font-semibold text-[11px] shrink-0">
                                                {{ substr($user->name, 0, 1) }}</div>
                                        @endif
                                        <span>{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-black/60 dark:text-white/60 font-mono text-[12px]">{{ $user->email }}</td>
                                <td class="px-4 py-3 text-black/60 dark:text-white/60">
                                    @if ($user->activeBusiness)
                                        <span
                                            class="font-medium text-black dark:text-white">{{ $user->activeBusiness->name }}</span>
                                    @else
                                        <span class="text-black/40 dark:text-white/40 italic">{{ __('admin.belum_ada') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($user->google_id)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FF3B30]/12 text-[#C41E17] dark:text-[#FF453A]"><i
                                                data-lucide="globe" class="w-3 h-3"
                                                stroke-width="1.5"></i><span>Google</span></span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-black/6 dark:bg-white/8 text-black/55 dark:text-white/55"><i
                                                data-lucide="key-round" class="w-3 h-3"
                                                stroke-width="1.5"></i><span>Password</span></span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-[12px] text-black/50 dark:text-white/50 tabular-nums">
                                    {{ $user->created_at?->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if (Auth::guard('admin')->user()?->hasPermission('admin.users.delete'))
                                        <button type="button"
                                            @click="openDeleteModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ route('admin.users.destroy', $user) }}')"
                                            class="h-7 px-2 rounded-[6px] text-[12px] font-medium text-[#FF3B30] hover:bg-[#FF3B30]/10 active:scale-[0.97] transition-all inline-flex items-center gap-1 cursor-pointer"
                                            title="Hapus Permanen Pengguna (Hard Delete dengan 2FA)">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5" stroke-width="1.5"></i>
                                            <span>{{ __('common.delete') }}</span>
                                        </button>
                                    @else
                                        <span class="text-[12px] text-black/30 dark:text-white/30">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center">
                                    <i data-lucide="users" class="w-12 h-12 mx-auto text-black/20 dark:text-white/20"
                                        stroke-width="1.5"></i>
                                    <p class="text-[15px] font-semibold text-black dark:text-white mt-3">{{ __('admin.belum_ada_data_pengguna_terdaftar') }}</p>
                                    <p class="text-[13px] text-black/50 dark:text-white/50 mt-1">{{ __('admin.tidak_ditemukan_data_pengguna_sesuai') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-4 py-3 border-t border-black/5 dark:border-white/10">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Apple HIG Bento Hard Delete User Modal with 2FA Verification -->
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-[24px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.12] p-6 sm:p-7 shadow-2xl space-y-5">
                    
                    <!-- Header -->
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-[16px] bg-[#FF3B30]/10 text-[#FF3B30] flex items-center justify-center shrink-0">
                            <i data-lucide="user-x" class="w-6 h-6" stroke-width="2"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[17px] font-bold text-black dark:text-white">Hapus Permanen Pengguna Platform</h3>
                            <p class="text-[12px] text-black/55 dark:text-white/55 mt-0.5 leading-relaxed">
                                Tindakan penghapusan permanen (hard delete) akun pengguna, keanggotaan, sesi, dan berkas terkait.
                            </p>
                        </div>
                    </div>

                    <!-- 2FA Check Condition -->
                    <template x-if="!adminHas2FA">
                        <div class="space-y-4">
                            <div class="p-4 rounded-[16px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[13px] text-black/80 dark:text-white/90 space-y-2">
                                <div class="font-bold text-[#C93400] dark:text-[#FF9F0A] flex items-center gap-2">
                                    <i data-lucide="shield-alert" class="w-4 h-4" stroke-width="2"></i>
                                    <span>Google Authenticator (2FA) Wajib Diaktifkan</span>
                                </div>
                                <p class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                    Demi mencegah penyalahgunaan hak akses atau kehilangan data pengguna yang fatal, fitur penghapusan pengguna wajib diverifikasi dengan 2FA Google Authenticator.
                                </p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <button type="button" @click="showDeleteModal = false"
                                    class="h-10 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition">
                                    Tutup
                                </button>
                                <a href="{{ route('admin.profile.index') }}"
                                    class="h-10 px-5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] transition inline-flex items-center gap-2 shadow-sm">
                                    <i data-lucide="key" class="w-4 h-4"></i>
                                    <span>Aktifkan 2FA di Profil</span>
                                </a>
                            </div>
                        </div>
                    </template>

                    <template x-if="adminHas2FA">
                        <form method="POST" :action="deleteActionUrl" class="space-y-4">
                            @csrf
                            @method('DELETE')

                            <!-- Danger Warning Banner -->
                            <div class="p-4 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 text-[12px] text-black/75 dark:text-white/80 leading-relaxed space-y-1">
                                <div class="font-bold text-[#FF3B30] dark:text-[#FF453A] flex items-center gap-1.5">
                                    <i data-lucide="alert-octagon" class="w-4 h-4" stroke-width="2"></i>
                                    <span>Peringatan Kritis: Penghapusan Tidak Dapat Dibatalkan</span>
                                </div>
                                <p class="text-black/60 dark:text-white/60">
                                    Pengguna <strong class="text-black dark:text-white" x-text="deleteUserName"></strong> (<span class="font-mono" x-text="deleteUserEmail"></span>) beserta riwayat keanggotaan bisnis dan sesi akan dihapus secara permanen.
                                </p>
                            </div>

                            <!-- Input 1: Confirmation Email -->
                            <div>
                                <label class="block text-[12.5px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                    Ketik alamat email pengguna <span class="font-bold text-[#FF3B30]" x-text="'\'' + deleteUserEmail + '\''"></span> untuk konfirmasi:
                                </label>
                                <input type="text" name="confirm_email" required x-model="deleteConfirmInput"
                                    placeholder="Ketik email pengguna di sini"
                                    class="w-full h-11 px-4 bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-[14px] text-black dark:text-white placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition">
                            </div>

                            <!-- Input 2: 2FA Google Authenticator Code -->
                            <div>
                                <label class="block text-[12.5px] font-semibold text-black/75 dark:text-white/75 mb-1.5">
                                    Kode 6 Digit Google Authenticator <span class="text-[#FF3B30]">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" name="totp_code" maxlength="6" pattern="[0-9]{6}" required x-model="deleteTotpCode"
                                        placeholder="000000"
                                        class="w-full h-11 px-4 text-center font-mono text-[18px] tracking-[0.3em] font-bold bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] rounded-[12px] text-black dark:text-white focus:outline-none focus:ring-2 focus:ring-[#FF3B30]/50 transition">
                                </div>
                                <span class="text-[11px] text-black/45 dark:text-white/45 mt-1 block">
                                    Buka aplikasi Google Authenticator di ponsel Anda dan masukkan kode 6-digit terkini.
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-black/[0.06] dark:border-white/[0.08]">
                                <button type="button" @click="showDeleteModal = false"
                                    class="h-11 px-4 rounded-[12px] text-[13px] font-medium text-black/70 dark:text-white/70 hover:bg-black/5 dark:hover:bg-white/5 transition cursor-pointer">
                                    {{ __('common.cancel') }}
                                </button>
                                <button type="submit"
                                    :disabled="!isDeleteFormValid()"
                                    :class="isDeleteFormValid() ? 'bg-[#FF3B30] hover:bg-[#D70015] active:scale-[0.98] cursor-pointer' : 'bg-black/20 dark:bg-white/20 text-black/40 dark:text-white/40 cursor-not-allowed'"
                                    class="h-11 px-5 rounded-[12px] text-[13px] font-bold text-white transition-all inline-flex items-center gap-2 shadow-sm">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    <span>Hapus Pengguna Sekarang</span>
                                </button>
                            </div>
                        </form>
                    </template>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function adminUsersManager() {
    return {
        showDeleteModal: false,
        deleteUserId: '',
        deleteUserName: '',
        deleteUserEmail: '',
        deleteActionUrl: '',
        deleteConfirmInput: '',
        deleteTotpCode: '',
        adminHas2FA: @js(Auth::guard('admin')->user()?->hasTwoFactorEnabled() ?? false),
        openDeleteModal(id, name, email, url) {
            this.deleteUserId = id;
            this.deleteUserName = name;
            this.deleteUserEmail = email;
            this.deleteActionUrl = url;
            this.deleteConfirmInput = '';
            this.deleteTotpCode = '';
            this.showDeleteModal = true;
            this.$nextTick(() => {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },
        isDeleteFormValid() {
            if (!this.adminHas2FA) return false;
            const confirm = this.deleteConfirmInput.trim().toLowerCase();
            const expectedEmail = this.deleteUserEmail.trim().toLowerCase();
            const isEmailValid = (confirm === expectedEmail);
            const isTotpValid = (this.deleteTotpCode.trim().length === 6 && /^\d+$/.test(this.deleteTotpCode.trim()));
            return isEmailValid && isTotpValid;
        }
    };
}
</script>
@endpush
