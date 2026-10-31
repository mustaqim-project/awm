@extends('layouts.admin', ['title' => __('admin.filter_moderasi_konten_komunitas_admin_console'),
    'headerTitle' => 'Moderasi Kata Terlarang',
    'headerSubtitle' => 'Cegah dan tangkal konten judol, scam, kata kasar, dan pornografi pada postingan & komentar komunitas Cooca',
])

@section('content')
    <div class="space-y-6 max-w-[1250px] w-full min-w-0 mx-auto pb-28 lg:pb-10" x-data="{
        createOpen: false,
        bulkOpen: false,
        editOpen: false,
        editData: {
            id: '',
            word: '',
            category: 'judol',
            action: 'block',
            is_active: true,
            description: ''
        },
        openEdit(item) {
            this.editData = {
                id: item.id,
                word: item.word,
                category: item.category,
                action: item.action,
                is_active: Boolean(item.is_active),
                description: item.description || ''
            };
            this.editOpen = true;
        }
    }">

        <!-- Page Header Bento -->
        <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-5 sm:p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-[16px] bg-[#FF3B30]/10 flex items-center justify-center text-[#FF3B30] dark:text-[#FF453A] shrink-0">
                    <i data-lucide="shield-alert" class="w-6 h-6" stroke-width="1.8"></i>
                </div>
                <div>
                    <h1 class="text-[19px] sm:text-[20px] font-bold text-black dark:text-white tracking-tight">
                        {{ __('admin.filter_moderasi_kata_komunitas') }}
                    </h1>
                    <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">
                        Menangkal postingan &amp; komentar bermuatan judi online (judol), penipuan (scam), kata kasar toxic, dan pornografi.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                @if(Auth::guard('admin')->user()?->hasPermission('admin.word_filters.create'))
                <form action="{{ route('admin.word-filters.seed-defaults') }}" method="POST" onsubmit="return confirm('Muat ulang kata bawaan standar Cooca?')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[12px] text-[13px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] text-black/75 dark:text-white/80 border border-black/[0.06] dark:border-white/[0.08] transition-all cursor-pointer">
                        <i data-lucide="sparkles" class="w-4 h-4 text-[#AF52DE]" stroke-width="1.8"></i>
                        <span>Muat Kata Bawaan</span>
                    </button>
                </form>

                <button type="button" @click="bulkOpen = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[12px] text-[13px] font-semibold bg-black/[0.04] dark:bg-white/[0.06] hover:bg-black/[0.08] dark:hover:bg-white/[0.10] text-black/85 dark:text-white/90 border border-black/[0.06] dark:border-white/[0.08] transition-all cursor-pointer">
                    <i data-lucide="list-plus" class="w-4 h-4 text-[#007AFF]" stroke-width="1.8"></i>
                    <span>Tambah Massal</span>
                </button>

                <button type="button" @click="createOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[12px] text-[13px] font-semibold bg-[#007AFF] hover:bg-[#0071E3] text-white shadow-sm shadow-[#007AFF]/25 transition-all cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4" stroke-width="2"></i>
                    <span>Tambah Kata Baru</span>
                </button>
                @endif
            </div>
        </div>

        <!-- Bento Metrics Cards (Statistik Kategori Terlarang) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
            <!-- 1. Total Kata -->
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-4 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-black/50 dark:text-white/50 text-[11px] font-bold uppercase tracking-wider">
                    <span>Total Aturan</span>
                    <i data-lucide="shield-check" class="w-4 h-4 text-[#007AFF]"></i>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono tracking-tight text-black dark:text-white">{{ $stats['total'] }}</div>
                    <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium mt-0.5">{{ $stats['active'] }} aturan aktif</div>
                </div>
            </div>

            <!-- 2. Judol -->
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-4 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-[#FF3B30] dark:text-[#FF453A] text-[11px] font-bold uppercase tracking-wider">
                    <span>Judi Online</span>
                    <i data-lucide="dices" class="w-4 h-4"></i>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono tracking-tight text-[#FF3B30] dark:text-[#FF453A]">{{ $stats['judol'] }}</div>
                    <div class="text-[11px] text-black/40 dark:text-white/40 font-medium mt-0.5">Slot, zeus, gacor, dll</div>
                </div>
            </div>

            <!-- 3. Scam -->
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-4 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-[#FF9500] dark:text-[#FF9F0A] text-[11px] font-bold uppercase tracking-wider">
                    <span>Penipuan (Scam)</span>
                    <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono tracking-tight text-[#FF9500] dark:text-[#FF9F0A]">{{ $stats['scam'] }}</div>
                    <div class="text-[11px] text-black/40 dark:text-white/40 font-medium mt-0.5">Pinjol, transfer dulu, dll</div>
                </div>
            </div>

            <!-- 4. Kata Kasar -->
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-4 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-[#AF52DE] dark:text-[#BF5AF2] text-[11px] font-bold uppercase tracking-wider">
                    <span>Kata Kasar</span>
                    <i data-lucide="message-square-off" class="w-4 h-4"></i>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono tracking-tight text-[#AF52DE] dark:text-[#BF5AF2]">{{ $stats['kata_kasar'] }}</div>
                    <div class="text-[11px] text-black/40 dark:text-white/40 font-medium mt-0.5">Toxic &amp; profanity</div>
                </div>
            </div>

            <!-- 5. Pornografi -->
            <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-4 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between text-rose-600 dark:text-rose-400 text-[11px] font-bold uppercase tracking-wider">
                    <span>Pornografi</span>
                    <i data-lucide="eye-off" class="w-4 h-4"></i>
                </div>
                <div class="mt-3">
                    <div class="text-2xl font-black font-mono tracking-tight text-rose-600 dark:text-rose-400">{{ $stats['pornografi'] }}</div>
                    <div class="text-[11px] text-black/40 dark:text-white/40 font-medium mt-0.5">Asusila &amp; video syur</div>
                </div>
            </div>
        </div>

        <!-- Filter Pills & Search Bar -->
        <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <!-- Category Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 sm:pb-0">
                <a href="{{ route('admin.word-filters.index', ['category' => 'all', 'q' => $search]) }}"
                    class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all shrink-0 {{ ($category === 'all' || empty($category)) ? 'bg-[#007AFF] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                    {{ __('common.all') }} ({{ $stats['total'] }})
                </a>
                <a href="{{ route('admin.word-filters.index', ['category' => 'judol', 'q' => $search]) }}"
                    class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all shrink-0 {{ $category === 'judol' ? 'bg-[#FF3B30] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                    Judol ({{ $stats['judol'] }})
                </a>
                <a href="{{ route('admin.word-filters.index', ['category' => 'scam', 'q' => $search]) }}"
                    class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all shrink-0 {{ $category === 'scam' ? 'bg-[#FF9500] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                    Scam ({{ $stats['scam'] }})
                </a>
                <a href="{{ route('admin.word-filters.index', ['category' => 'kata_kasar', 'q' => $search]) }}"
                    class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all shrink-0 {{ $category === 'kata_kasar' ? 'bg-[#AF52DE] text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                    Kata Kasar ({{ $stats['kata_kasar'] }})
                </a>
                <a href="{{ route('admin.word-filters.index', ['category' => 'pornografi', 'q' => $search]) }}"
                    class="px-3 py-1.5 rounded-[10px] text-[12px] font-semibold transition-all shrink-0 {{ $category === 'pornografi' ? 'bg-rose-600 text-white shadow-xs' : 'text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5' }}">
                    Pornografi ({{ $stats['pornografi'] }})
                </a>
            </div>

            <!-- Search Input -->
            <form method="GET" action="{{ route('admin.word-filters.index') }}" class="relative sm:w-64">
                <input type="hidden" name="category" value="{{ $category }}">
                <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3 top-2.5"></i>
                <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('admin.cari_kata_terlarang') }}"
                    class="w-full pl-9 pr-8 py-2 rounded-[12px] text-[12.5px] bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                @if($search)
                    <a href="{{ route('admin.word-filters.index', ['category' => $category]) }}"
                        class="absolute right-2.5 top-2.5 text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Table Container -->
        <div class="rounded-[22px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px] border-collapse">
                    <thead>
                        <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-black/50 dark:text-white/50 text-[11px] font-bold uppercase tracking-wider">
                            <th class="py-3.5 px-4 sm:px-6">Kata / Frasa Terlarang</th>
                            <th class="py-3.5 px-4">Kategori</th>
                            <th class="py-3.5 px-4">Tindakan</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 hidden md:table-cell">Keterangan</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                        @forelse($filters as $filter)
                            <tr class="hover:bg-black/[0.02] dark:hover:bg-white/[0.02] transition-colors">
                                <!-- Word -->
                                <td class="py-3.5 px-4 sm:px-6 font-mono font-bold text-black dark:text-white">
                                    <span class="px-2 py-0.5 rounded-md bg-black/[0.05] dark:bg-white/[0.08] border border-black/[0.06] dark:border-white/[0.08]">
                                        {{ $filter->word }}
                                    </span>
                                </td>

                                <!-- Category Badge -->
                                <td class="py-3.5 px-4">
                                    @if($filter->category === 'judol')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Judol</span>
                                        </span>
                                    @elseif($filter->category === 'scam')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span>Scam</span>
                                        </span>
                                    @elseif($filter->category === 'kata_kasar')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            <span>Kata Kasar</span>
                                        </span>
                                    @elseif($filter->category === 'pornografi')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-pink-50 dark:bg-pink-950/60 text-pink-700 dark:text-pink-400 border border-pink-200 dark:border-pink-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                                            <span>Pornografi</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            <span>Kustom</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Action -->
                                <td class="py-3.5 px-4 font-medium">
                                    @if($filter->action === 'block')
                                        <span class="text-rose-600 dark:text-rose-400 flex items-center gap-1 font-semibold text-xs">
                                            <i data-lucide="ban" class="w-3.5 h-3.5"></i>
                                            <span>Blokir Konten</span>
                                        </span>
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1 font-semibold text-xs">
                                            <i data-lucide="asterisk" class="w-3.5 h-3.5"></i>
                                            <span>Sensor (***)</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-3.5 px-4">
                                    @if(Auth::guard('admin')->user()?->hasPermission('admin.word_filters.toggle'))
                                    <form method="POST" action="{{ route('admin.word-filters.toggle', $filter) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold transition cursor-pointer {{ $filter->is_active ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 hover:bg-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $filter->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            <span>{{ $filter->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </button>
                                    </form>
                                    @else
                                    <span class=\"inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $filter->is_active ? 'bg-[#34C759]/15 text-[#34C759] dark:text-[#30D158]' : 'bg-black/10 dark:bg-white/10 text-black/50 dark:text-white/50' }}\"><span class=\"w-1.5 h-1.5 rounded-full {{ $filter->is_active ? 'bg-[#34C759]' : 'bg-black/40 dark:bg-white/40' }}\"></span>{{ $filter->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    @endif
                                </td>

                                <!-- Description / Notes -->
                                <td class="py-3.5 px-4 text-black/50 dark:text-white/50 text-[12px] hidden md:table-cell truncate max-w-xs">
                                    {{ $filter->description ?? '-' }}
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <button type="button" @click="openEdit(@js($filter))"
                                            class="p-1.5 rounded-[9px] text-black/50 hover:text-black dark:text-white/50 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition cursor-pointer"
                                            title="{{ __('admin.ubah_aturan') }}">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        @if(Auth::guard('admin')->user()?->hasPermission('admin.word_filters.delete'))
                                        <form method="POST" action="{{ route('admin.word-filters.destroy', $filter) }}"
                                            data-confirm="Hapus kata &quot;{{ $filter->word }}&quot; dari filter moderasi?"
                                            onsubmit="return confirm(this.dataset.confirm)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 rounded-[9px] text-rose-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer"
                                                title="{{ __('admin.hapus_aturan_2') }}">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-black/40 dark:text-white/40">
                                    <i data-lucide="shield-check" class="w-8 h-8 mx-auto mb-2 text-black/20 dark:text-white/20"></i>
                                    <p class="font-medium text-sm">Tidak ada kata terlarang dalam kategori ini.</p>
                                    <p class="text-xs mt-1">Gunakan tombol "Muat Kata Bawaan" atau "Tambah Kata Baru" di atas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($filters->hasPages())
                <div class="p-4 border-t border-black/[0.04] dark:border-white/[0.06]">
                    {{ $filters->links() }}
                </div>
            @endif
        </div>

        <!-- ======================================================== -->
        <!-- MODAL 1: Tambah Kata Baru (Single) -->
        <!-- ======================================================== -->
        <template x-teleport="body">
            <div x-show="createOpen" x-cloak
                class="fixed inset-0 z-[200] bg-black/60 dark:bg-black/75 backdrop-blur-md flex items-center justify-center p-4 transition-all"
                role="dialog" aria-modal="true" @click.self="createOpen = false" @keydown.escape.window="createOpen = false">
                <div @click.stop
                    class="relative z-10 bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl max-w-md w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-bold text-black dark:text-white">Tambah Kata Terlarang</h3>
                    </div>
                    <button type="button" @click="createOpen = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.word-filters.store') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Kata / Frasa Terlarang</label>
                        <input type="text" name="word" required placeholder="{{ __('admin.contoh_slot_gacor_transfer_dulu_bokep') }}"
                            class="w-full px-3.5 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Kategori</label>
                            <select name="category" required
                                class="w-full px-3 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                                <option value="judol">Judi Online (Judol)</option>
                                <option value="scam">Penipuan / Scam</option>
                                <option value="kata_kasar">Kata Kasar &amp; Toxic</option>
                                <option value="pornografi">Pornografi &amp; Asusila</option>
                                <option value="custom">Kustom / Khusus</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Tindakan</label>
                            <select name="action" required
                                class="w-full px-3 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                                <option value="block">Tolak / Blokir</option>
                                <option value="censor">Sensor (***)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Catatan / Keterangan (Opsional)</label>
                        <input type="text" name="description" placeholder="{{ __('admin.alasan_pencekalan') }}"
                            class="w-full px-3.5 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="createOpen = false"
                            class="px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer">{{ __('common.cancel') }}</button>
                        <button type="submit"
                            class="px-4 py-2 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] shadow-xs cursor-pointer">{{ __('admin.simpan_kata') }}</button>
                    </div>
                </form>
                </div>
            </div>
        </template>

        <!-- ======================================================== -->
        <!-- MODAL 2: Tambah Massal (Bulk Import) -->
        <!-- ======================================================== -->
        <template x-teleport="body">
            <div x-show="bulkOpen" x-cloak
                class="fixed inset-0 z-[200] bg-black/60 dark:bg-black/75 backdrop-blur-md flex items-center justify-center p-4 transition-all"
                role="dialog" aria-modal="true" @click.self="bulkOpen = false" @keydown.escape.window="bulkOpen = false">
                <div @click.stop
                    class="relative z-10 bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl max-w-lg w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] bg-[#34C759]/10 text-[#34C759] flex items-center justify-center">
                            <i data-lucide="list-plus" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-bold text-black dark:text-white">{{ __('admin.tambah_kata_filter_massal') }}</h3>
                    </div>
                    <button type="button" @click="bulkOpen = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.word-filters.bulk-store') }}" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">
                            Daftar Kata (Pisahkan dengan Koma atau Baris Baru)
                        </label>
                        <textarea name="words" required rows="5" placeholder="slot88, bocoran rtp, bandar togel&#10;pinjaman kilat langsung cair&#10;anjing, bangsat"
                            class="w-full px-3.5 py-2.5 rounded-[12px] text-xs font-mono bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]"></textarea>
                        <p class="text-[11px] text-black/40 dark:text-white/40 mt-1">
                            Admin dapat memasukkan puluhan kata baru sekaligus. Sistem otomatis mengabaikan kata yang sudah ada.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Kategori Bersama</label>
                            <select name="category" required
                                class="w-full px-3 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                                <option value="judol">Judi Online (Judol)</option>
                                <option value="scam">Penipuan / Scam</option>
                                <option value="kata_kasar">Kata Kasar &amp; Toxic</option>
                                <option value="pornografi">Pornografi &amp; Asusila</option>
                                <option value="custom">Kustom / Khusus</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Tindakan</label>
                            <select name="action" required
                                class="w-full px-3 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                                <option value="block">Tolak / Blokir</option>
                                <option value="censor">Sensor (***)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Catatan Batch (Opsional)</label>
                        <input type="text" name="description" placeholder="Import massal kata terlarang..."
                            class="w-full px-3.5 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="bulkOpen = false"
                            class="px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer">{{ __('common.cancel') }}</button>
                        <button type="submit"
                            class="px-4 py-2 rounded-[12px] text-xs font-bold text-white bg-[#34C759] hover:bg-[#30D158] shadow-xs cursor-pointer">{{ __('admin.import_semua_kata') }}</button>
                    </div>
                </form>
                </div>
            </div>
        </template>

        <!-- ======================================================== -->
        <!-- MODAL 3: Edit Kata -->
        <!-- ======================================================== -->
        <template x-teleport="body">
            <div x-show="editOpen" x-cloak
                class="fixed inset-0 z-[200] bg-black/60 dark:bg-black/75 backdrop-blur-md flex items-center justify-center p-4 transition-all"
                role="dialog" aria-modal="true" @click.self="editOpen = false" @keydown.escape.window="editOpen = false">
                <div @click.stop
                    class="relative z-10 bg-white dark:bg-[#1C1C1E] rounded-[24px] border border-black/[0.08] dark:border-white/[0.1] shadow-2xl max-w-md w-full p-6 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-black/[0.04] dark:border-white/[0.06]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[10px] bg-[#AF52DE]/10 text-[#AF52DE] flex items-center justify-center">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-bold text-black dark:text-white">{{ __('admin.ubah_aturan_kata') }}</h3>
                    </div>
                    <button type="button" @click="editOpen = false" class="text-black/40 hover:text-black dark:text-white/40 dark:hover:text-white">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" :action="'/admin/word-filters/' + editData.id" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Kata / Frasa</label>
                        <input type="text" name="word" required x-model="editData.word"
                            class="w-full px-3.5 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Kategori</label>
                            <select name="category" required x-model="editData.category"
                                class="w-full px-3 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                                <option value="judol">Judi Online (Judol)</option>
                                <option value="scam">Penipuan / Scam</option>
                                <option value="kata_kasar">Kata Kasar &amp; Toxic</option>
                                <option value="pornografi">Pornografi &amp; Asusila</option>
                                <option value="custom">Kustom / Khusus</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Tindakan</label>
                            <select name="action" required x-model="editData.action"
                                class="w-full px-3 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                                <option value="block">Tolak / Blokir</option>
                                <option value="censor">Sensor (***)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-black/70 dark:text-white/70 mb-1">Keterangan</label>
                        <input type="text" name="description" x-model="editData.description"
                            class="w-full px-3.5 py-2.5 rounded-[12px] text-xs bg-black/[0.04] dark:bg-white/[0.06] border border-black/[0.08] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:ring-2 focus:ring-[#007AFF]">
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="edit-active-toggle" name="is_active" value="1" x-model="editData.is_active"
                            class="w-4 h-4 rounded text-[#007AFF] focus:ring-[#007AFF]">
                        <label for="edit-active-toggle" class="text-xs font-semibold text-black/80 dark:text-white/80 cursor-pointer">
                            Aktifkan aturan pencekalan ini
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-2">
                        <button type="button" @click="editOpen = false"
                            class="px-4 py-2 rounded-[12px] text-xs font-semibold text-black/60 dark:text-white/60 hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer">{{ __('common.cancel') }}</button>
                        <button type="submit"
                            class="px-4 py-2 rounded-[12px] text-xs font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] shadow-xs cursor-pointer">
                            Perbarui Aturan
                        </button>
                    </div>
                </form>
                </div>
            </div>
        </template>

    </div>
@endsection
