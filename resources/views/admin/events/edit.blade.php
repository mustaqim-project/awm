@extends('layouts.admin', [
    'title' => 'Edit Event: ' . $event->title . ' - Cooca Admin',
    'headerTitle' => 'Edit Event Komunitas',
    'headerSubtitle' => 'Perbarui rincian waktu, lokasi, kuota, atau batas pendaftaran event',
])

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-16">
    <!-- Breadcrumb & Back -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-black/60 dark:text-white/60 hover:text-[#007AFF] transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>{{ __('admin.kembali_ke_detail_event') }}</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-[#1C1C1E] p-6 sm:p-8 rounded-[20px] border border-black/[0.08] dark:border-white/[0.08] shadow-xs">
        <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="space-y-6"
            x-data="{
                eventType: '{{ old('event_type', $event->event_type) }}',
                priceType: '{{ old('price_type', $event->price_type ?? 'free') }}',
                getGps() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition((pos) => {
                            $refs.latInput.value = pos.coords.latitude.toFixed(6);
                            $refs.lngInput.value = pos.coords.longitude.toFixed(6);
                            alert('Koordinat GPS berhasil diambil: ' + pos.coords.latitude.toFixed(4) + ', ' + pos.coords.longitude.toFixed(4));
                        }, (err) => {
                            alert('Gagal mengambil GPS: ' + err.message);
                        });
                    } else {
                        alert('Browser tidak mendukung Geolocation.');
                    }
                }
            }">
            @csrf
            @method('PUT')

            <!-- Section 1: Informasi Dasar -->
            <div class="border-b border-black/[0.06] dark:border-white/[0.06] pb-5">
                <h3 class="text-[15px] font-bold text-black dark:text-white">Informasi Pokok Event</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Judul, deskripsi, dan banner poster agenda.</p>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                            Judul Agenda Event <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="title" required value="{{ old('title', $event->title) }}"
                            class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                        @error('title') <p class="text-[11px] text-[#FF3B30] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                            Deskripsi Lengkap &amp; Rangkaian Agenda <span class="text-[#FF3B30]">*</span>
                        </label>
                        <textarea name="description" required rows="4"
                            class="w-full p-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] resize-y">{{ old('description', $event->description) }}</textarea>
                        @error('description') <p class="text-[11px] text-[#FF3B30] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                                Tipe Penyelenggaraan <span class="text-[#FF3B30]">*</span>
                            </label>
                            <select name="event_type" x-model="eventType" required
                                class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                                <option value="offline" {{ old('event_type', $event->event_type) === 'offline' ? 'selected' : '' }}>Offline di Tempat / Venue Fisik</option>
                                <option value="online" {{ old('event_type', $event->event_type) === 'online' ? 'selected' : '' }}>Online Webinar / Live Streaming</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                                Akses Biaya Tiket <span class="text-[#FF3B30]">*</span>
                            </label>
                            <select name="price_type" x-model="priceType" required
                                class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                                <option value="free" {{ old('price_type', $event->price_type ?? 'free') === 'free' ? 'selected' : '' }}>Gratis (Free Event)</option>
                                <option value="paid" {{ old('price_type', $event->price_type ?? 'free') === 'paid' ? 'selected' : '' }}>Berbayar (Paid Event)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Input Harga Tiket (Hanya jika Berbayar) -->
                    <div x-show="priceType === 'paid'" x-cloak class="p-4 rounded-[14px] bg-[#FF9500]/10 border border-[#FF9500]/20 space-y-2">
                        <label class="block text-[12px] font-bold text-[#FF9500] dark:text-[#FF9F0A]">
                            Harga Tiket Peserta (Rupiah) <span class="text-[#FF3B30]">*</span>
                        </label>
                        <div class="relative max-w-sm">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-xs text-black/50 dark:text-white/50">Rp</span>
                            <input type="number" name="price" min="0" step="1000" value="{{ old('price', (int)$event->price) }}" placeholder="Contoh: 75000"
                                class="w-full h-10 pl-10 pr-3.5 rounded-[12px] text-[13px] font-mono bg-white dark:bg-black/40 border border-[#FF9500]/40 text-black dark:text-white focus:outline-hidden focus:border-[#FF9500]">
                        </div>
                        <p class="text-[11px] text-black/50 dark:text-white/50">Masukkan nominal harga tiket per peserta dalam mata uang Rupiah.</p>
                        @error('price') <p class="text-[11px] text-[#FF3B30]">{{ $message }}</p> @enderror
                    </div>

                    <!-- Input Link Meeting Online (Hanya jika Online) -->
                    <div x-show="eventType === 'online'" x-cloak class="p-4 rounded-[14px] bg-[#007AFF]/10 border border-[#007AFF]/20 space-y-2">
                        <label class="block text-[12px] font-bold text-[#007AFF] dark:text-[#0A84FF]">
                            Tautan Pertemuan Online (Zoom / GMeet / Teams)
                        </label>
                        <input type="url" name="online_meeting_url" value="{{ old('online_meeting_url', $event->online_meeting_url) }}" placeholder="https://meet.google.com/xyz-... atau https://zoom.us/j/..."
                            class="w-full h-10 px-3.5 rounded-[12px] text-[13px] font-mono bg-white dark:bg-black/40 border border-[#007AFF]/40 text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                        <p class="text-[11px] text-black/50 dark:text-white/50">Tautan ini akan langsung muncul di tiket peserta yang telah mendaftar.</p>
                        @error('online_meeting_url') <p class="text-[11px] text-[#FF3B30]">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                                Status Publikasi <span class="text-[#FF3B30]">*</span>
                            </label>
                            <select name="status" required
                                class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                                <option value="published" {{ old('status', $event->status) === 'published' ? 'selected' : '' }}>Dipublikasikan (Tampil di Komunitas)</option>
                                <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>

                    <div>
                        <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                            Poster / Banner Gambar (Maks 5 MB)
                        </label>
                        @if($event->banner_url)
                            <div class="mb-2">
                                <img src="{{ $event->banner_url }}" alt="{{ $event->title }}" class="h-24 w-auto rounded-[10px] object-cover border border-black/[0.08] dark:border-white/[0.08]">
                            </div>
                        @endif
                        <input type="file" name="banner_image" accept="image/*"
                            class="w-full text-[12px] file:mr-3 file:py-2 file:px-3 file:rounded-[8px] file:border-0 file:text-[12px] file:font-semibold file:bg-[#007AFF]/10 file:text-[#007AFF] hover:file:bg-[#007AFF]/20 text-black/60 dark:text-white/60">
                    </div>
                </div>
            </div>

            <!-- Section 2: Jadwal & Kuota -->
            <div class="border-b border-black/[0.06] dark:border-white/[0.06] pb-5">
                <h3 class="text-[15px] font-bold text-black dark:text-white">Jadwal &amp; Batas Pendaftaran</h3>
                <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Waktu pelaksanaan dan batas akhir pendaftaran.</p>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                            Tanggal &amp; Jam Mulai <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="datetime-local" name="start_time" required
                            value="{{ old('start_time', $event->start_time->format('Y-m-d\TH:i')) }}"
                            class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                            Tanggal &amp; Jam Selesai <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="datetime-local" name="end_time" required
                            value="{{ old('end_time', $event->end_time->format('Y-m-d\TH:i')) }}"
                            class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                            Kapan Terakhir Daftar <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="datetime-local" name="registration_deadline" required
                            value="{{ old('registration_deadline', $event->registration_deadline->format('Y-m-d\TH:i')) }}"
                            class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                        Kuota Maksimal Peserta (Opsional)
                    </label>
                    <input type="number" name="quota" min="1" value="{{ old('quota', $event->quota) }}"
                        class="w-full sm:w-64 h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                </div>
            </div>

            <!-- Section 3: Lokasi & Geofencing -->
            <div class="border-b border-black/[0.06] dark:border-white/[0.06] pb-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-bold text-black dark:text-white">Lokasi &amp; Geofencing Check-in</h3>
                        <p class="text-[12px] text-black/50 dark:text-white/50 mt-0.5">Venue fisik dan titik GPS check-in.</p>
                    </div>
                    <button type="button" @click="getGps()"
                        class="px-3 py-1.5 rounded-[8px] text-[11px] font-semibold bg-[#007AFF]/10 text-[#007AFF] hover:bg-[#007AFF]/20 transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
                        <span>Ambil Koordinat Saya</span>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                            Nama Tempat / Venue <span class="text-[#FF3B30]">*</span>
                        </label>
                        <input type="text" name="location_name" required value="{{ old('location_name', $event->location_name) }}"
                            class="w-full h-10 px-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                    </div>

                    <div>
                        <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                            Alamat Lengkap / Link Webinar
                        </label>
                        <textarea name="location_address" rows="2"
                            class="w-full p-3.5 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF] resize-none">{{ old('location_address', $event->location_address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" x-show="eventType === 'offline'">
                        <div>
                            <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                                Latitude GPS
                            </label>
                            <input type="text" name="latitude" x-ref="latInput" value="{{ old('latitude', $event->latitude) }}"
                                class="w-full h-10 px-3.5 rounded-[12px] text-[13px] font-mono bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                                Longitude GPS
                            </label>
                            <input type="text" name="longitude" x-ref="lngInput" value="{{ old('longitude', $event->longitude) }}"
                                class="w-full h-10 px-3.5 rounded-[12px] text-[13px] font-mono bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                        </div>

                        <div>
                            <label class="block text-[12px] font-bold text-black dark:text-white mb-1">
                                Radius Toleransi Check-in
                            </label>
                            <div class="relative">
                                <input type="number" name="checkin_radius_meters" value="{{ old('checkin_radius_meters', $event->checkin_radius_meters) }}" min="10" max="5000"
                                    class="w-full h-10 px-3.5 pr-10 rounded-[12px] text-[13px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.1] dark:border-white/[0.1] text-black dark:text-white focus:outline-hidden focus:border-[#007AFF]">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] text-black/45 dark:text-white/45">meter</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.events.show', $event) }}"
                    class="px-4 py-2.5 rounded-[12px] text-[13px] font-semibold text-black/70 dark:text-white/70 hover:bg-black/[0.05] dark:hover:bg-white/[0.08] transition">{{ __('common.cancel') }}</a>
                @if(Auth::guard('admin')->user()?->hasPermission('admin.events.edit'))
<button type="submit"
                    class="px-6 py-2.5 rounded-[12px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0062CC] shadow-sm shadow-[#007AFF]/25 transition cursor-pointer active:scale-[0.98]">{{ __('common.save_changes') }}</button>
@endif
            </div>
        </form>
    </div>
</div>
@endsection
