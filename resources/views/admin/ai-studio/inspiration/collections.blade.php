@extends('layouts.admin', [
    'title' => 'Kelola Koleksi Global Inspirasi - Admin Console',
    'headerTitle' => 'Koleksi Global Terkurasi COOCA',
    'headerSubtitle' => 'Kelola papan inspirasi tematik resmi yang dapat diakses oleh seluruh merchant di platform',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-10" x-data="adminInspirationCollections()">

    <!-- Header Action Cockpit -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-lg font-bold text-black dark:text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-[10px] bg-amber-500/15 text-amber-500 flex items-center justify-center">
                        <i data-lucide="bookmark" class="w-4 h-4"></i>
                    </span>
                    <span>Papan Koleksi Global Terkurasi</span>
                </h1>
                <p class="text-xs text-black/60 dark:text-white/60">
                    Koleksi resmi membantu merchant menemukan preset visual berkualitas tinggi dalam 1 klik.
                </p>
            </div>

            <button type="button" @click="openCreateModal()"
                class="px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs transition-all flex items-center gap-1.5">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Buat Koleksi Baru</span>
            </button>
        </div>

        <!-- Submodule Navigation Tabs -->
        <div class="w-full flex items-center gap-1.5 border-t border-black/5 dark:border-white/10 pt-4 overflow-x-auto no-scrollbar">
            <a href="{{ route('admin.ai-studio.visual.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition-all">
                <i data-lucide="image" class="w-3.5 h-3.5"></i>
                <span>Studio Visual &amp; Medsos</span>
            </a>
            <a href="{{ route('admin.ai-studio.sources.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition-all">
                <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                <span>Sumber Inspirasi &amp; Tren</span>
            </a>
            <a href="{{ route('admin.ai-studio.pricing.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition-all">
                <i data-lucide="coins" class="w-3.5 h-3.5"></i>
                <span>Model Registry &amp; Tarif</span>
            </a>
            <a href="{{ route('admin.ai-studio.inspiration.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] transition-all">
                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                <span>Katalog &amp; Moderasi</span>
            </a>
            <a href="{{ route('admin.ai-studio.inspiration.collections.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-bold transition-all bg-[#007AFF] text-white shadow-xs">
                <i data-lucide="bookmark" class="w-3.5 h-3.5"></i>
                <span>Koleksi Global</span>
            </a>
        </div>
    </div>

    <!-- Table Koleksi Global -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-3 px-4">Nama Koleksi</th>
                        <th class="py-3 px-4">Slug</th>
                        <th class="py-3 px-4">Deskripsi</th>
                        <th class="py-3 px-4">Jumlah Item</th>
                        <th class="py-3 px-4">Unggulan (Featured)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($collections as $col)
                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                            <td class="py-3 px-4 font-bold text-black dark:text-white">
                                {{ $col->name }}
                            </td>
                            <td class="py-3 px-4 text-black/50 dark:text-white/50">{{ $col->slug }}</td>
                            <td class="py-3 px-4 max-w-xs truncate text-black/70 dark:text-white/70">
                                {{ $col->description ?: '-' }}
                            </td>
                            <td class="py-3 px-4 font-semibold">{{ $col->items_count }} item</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $col->is_featured ? 'bg-amber-500/10 text-amber-600' : 'bg-black/[0.04] text-black/50' }}">
                                    {{ $col->is_featured ? 'Ya' : 'Tidak' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $col->status === 'active' ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-red-500/10 text-red-500' }}">
                                    {{ ucfirst($col->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <button type="button" @click="openEditModal(@js($col))"
                                    class="px-2.5 py-1 rounded-[6px] text-xs font-semibold text-[#007AFF] hover:bg-[#007AFF]/10">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.ai-studio.inspiration.collections.destroy', $col->id) }}" class="inline"
                                    onsubmit="return confirm('Hapus koleksi global ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-[6px] text-xs font-semibold text-red-500 hover:bg-red-500/10">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-black/40 dark:text-white/40">Belum ada koleksi global yang dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($collections->hasPages())
            <div class="p-4 border-t border-black/5 dark:border-white/5">
                {{ $collections->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL TAMBAH / EDIT KOLEKSI GLOBAL -->
    <div x-show="modalOpen" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div @click.away="modalOpen = false"
            class="w-full max-w-md rounded-[20px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-5 sm:p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-black/5 dark:border-white/10 pb-3">
                <h3 class="text-sm font-bold text-black dark:text-white" x-text="isEdit ? 'Ubah Koleksi Global' : 'Tambah Koleksi Global Baru'"></h3>
                <button type="button" @click="modalOpen = false" class="text-black/40 hover:text-black">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="formAction" method="POST" class="space-y-3">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nama Koleksi</label>
                    <input type="text" name="name" x-model="formData.name" required
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Deskripsi</label>
                    <textarea name="description" x-model="formData.description" rows="2"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" x-model="formData.is_featured" class="rounded text-[#007AFF]">
                    <label for="is_featured" class="text-xs text-black/80 dark:text-white/80">Tampilkan sebagai Koleksi Unggulan di Beranda</label>
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button type="button" @click="modalOpen = false" class="px-3.5 py-1.5 rounded-[10px] text-xs font-medium text-black/60">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-[10px] bg-[#007AFF] text-white text-xs font-bold shadow-xs">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function adminInspirationCollections() {
    return {
        modalOpen: false,
        isEdit: false,
        formAction: '{{ route('admin.ai-studio.inspiration.collections.store') }}',
        formData: { name: '', description: '', is_featured: false },

        openCreateModal() {
            this.isEdit = false;
            this.formAction = '{{ route('admin.ai-studio.inspiration.collections.store') }}';
            this.formData = { name: '', description: '', is_featured: false };
            this.modalOpen = true;
        },

        openEditModal(col) {
            this.isEdit = true;
            this.formAction = `{{ url('admin/ai-studio/inspiration/collections') }}/${col.id}`;
            this.formData = { name: col.name, description: col.description, is_featured: !!col.is_featured };
            this.modalOpen = true;
        }
    };
}
</script>
@endpush
@endsection

