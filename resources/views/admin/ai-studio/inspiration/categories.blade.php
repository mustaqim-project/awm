@extends('layouts.admin', [
    'title' => 'Kelola Kategori Inspirasi - Admin Console',
    'headerTitle' => 'Taksonomi & Kategori Inspirasi Visual',
    'headerSubtitle' => 'Kelola hierarki kategori, pengelompokan industri UMKM, dan urutan tampilan katalog',
])

@section('content')
<div class="space-y-6 pb-28 lg:pb-10" x-data="adminInspirationCategories()">

    <!-- Header Action Cockpit -->
    <div class="rounded-[18px] backdrop-blur-md bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-5 sm:p-6 space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-lg font-bold text-black dark:text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-[10px] bg-[#34C759]/15 text-[#34C759] flex items-center justify-center">
                        <i data-lucide="tag" class="w-4 h-4"></i>
                    </span>
                    <span>Taksonomi Kategori Visual</span>
                </h1>
                <p class="text-xs text-black/60 dark:text-white/60">
                    Kategori mempermudah merchant mencari referensi komposisi sesuai jenis produk & target sektor industri.
                </p>
            </div>

            <button type="button" @click="openCreateModal()"
                class="px-4 py-2 rounded-[10px] bg-[#007AFF] hover:bg-[#0071E3] text-white text-xs font-bold shadow-xs transition-all flex items-center gap-1.5">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Kategori Baru</span>
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
            <a href="{{ route('admin.ai-studio.inspiration.categories.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-xs font-bold transition-all bg-[#007AFF] text-white shadow-xs">
                <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                <span>Taksonomi Kategori</span>
            </a>
        </div>
    </div>

    <!-- Table Kategori -->
    <div class="rounded-[16px] bg-white dark:bg-[#1C1C1E] border border-black/5 dark:border-white/10 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-black/[0.02] dark:bg-white/[0.02] border-b border-black/5 dark:border-white/10 text-black/60 dark:text-white/60 font-semibold">
                    <tr>
                        <th class="py-3 px-4">Ikon</th>
                        <th class="py-3 px-4">Nama & Slug</th>
                        <th class="py-3 px-4">Deskripsi</th>
                        <th class="py-3 px-4">Sektor Industri</th>
                        <th class="py-3 px-4">Urutan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5 dark:divide-white/5">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-black/[0.01] dark:hover:bg-white/[0.01] transition-colors">
                            <td class="py-3 px-4">
                                <span class="w-8 h-8 rounded-[8px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center font-bold">
                                    <i data-lucide="{{ $cat->icon ?: 'image' }}" class="w-4 h-4"></i>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-black dark:text-white">{{ $cat->name }}</div>
                                <div class="text-[10px] text-black/40 dark:text-white/40">{{ $cat->slug }}</div>
                            </td>
                            <td class="py-3 px-4 max-w-xs truncate text-black/70 dark:text-white/70">
                                {{ $cat->description ?: '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-black/[0.04] dark:bg-white/[0.06]">
                                    {{ $cat->industry_id ?: 'Multi-Sektor' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono">{{ $cat->sort_order }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $cat->is_active ? 'bg-[#34C759]/10 text-[#34C759]' : 'bg-red-500/10 text-red-500' }}">
                                    {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <button type="button" @click="openEditModal(@js($cat))"
                                    class="px-2.5 py-1 rounded-[6px] text-xs font-semibold text-[#007AFF] hover:bg-[#007AFF]/10">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.ai-studio.inspiration.categories.destroy', $cat->id) }}" class="inline"
                                    onsubmit="return confirm('Hapus kategori ini?')">
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
                            <td colspan="7" class="p-8 text-center text-black/40 dark:text-white/40">Belum ada kategori yang dikonfigurasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH / EDIT KATEGORI -->
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
                <h3 class="text-sm font-bold text-black dark:text-white" x-text="isEdit ? 'Ubah Kategori' : 'Tambah Kategori Baru'"></h3>
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
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Nama Kategori</label>
                    <input type="text" name="name" x-model="formData.name" required
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Slug URL (Opsional)</label>
                    <input type="text" name="slug" x-model="formData.slug" placeholder="otomatis digenerate jika kosong"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Deskripsi</label>
                    <textarea name="description" x-model="formData.description" rows="2"
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Icon Lucide</label>
                        <input type="text" name="icon" x-model="formData.icon" placeholder="tag, image, sparkles..."
                            class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Urutan (Sort Order)</label>
                        <input type="number" name="sort_order" x-model="formData.sort_order"
                            class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-black/70 dark:text-white/70 mb-1">Target Sektor Industri</label>
                    <input type="text" name="industry_id" x-model="formData.industry_id" placeholder="fnb, fashion, retail, beauty..."
                        class="w-full px-3 py-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/10 dark:border-white/10 text-xs text-black dark:text-white">
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
function adminInspirationCategories() {
    return {
        modalOpen: false,
        isEdit: false,
        formAction: '{{ route('admin.ai-studio.inspiration.categories.store') }}',
        formData: { name: '', slug: '', description: '', icon: 'tag', sort_order: 0, industry_id: '' },

        openCreateModal() {
            this.isEdit = false;
            this.formAction = '{{ route('admin.ai-studio.inspiration.categories.store') }}';
            this.formData = { name: '', slug: '', description: '', icon: 'tag', sort_order: 0, industry_id: '' };
            this.modalOpen = true;
        },

        openEditModal(cat) {
            this.isEdit = true;
            this.formAction = `{{ url('admin/ai-studio/inspiration/categories') }}/${cat.id}`;
            this.formData = { ...cat };
            this.modalOpen = true;
        }
    };
}
</script>
@endpush
@endsection
