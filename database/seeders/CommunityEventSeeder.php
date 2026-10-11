<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommunityEvent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CommunityEventSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Event Mendatang 1: OFFLINE & GRATIS
        CommunityEvent::updateOrCreate(
            ['slug' => 'temu-akbar-umkm-digital-2026'],
            [
                'title' => 'Temu Akbar UMKM Digital & Workshop Finansial 2026',
                'description' => 'Ajang silaturahmi tahunan ribuan pelaku usaha UMKM di ekosistem Cooca. Membahas strategi ekspansi bisnis, pengelolaan margin HPP, dan permodalan usaha sehat.',
                'banner_image' => 'community/events/temu-akbar-umkm.jpg',
                'event_type' => CommunityEvent::TYPE_OFFLINE,
                'price_type' => CommunityEvent::PRICE_TYPE_FREE,
                'price' => 0,
                'location_name' => 'Menara Mandiri Sudirman - Ballroom Lt. 9',
                'location_address' => 'Jl. Jend. Sudirman Kav. 54-55, Senayan, Kebayoran Baru, Jakarta Selatan',
                'latitude' => -6.2239450,
                'longitude' => 106.8090210,
                'checkin_radius_meters' => 250,
                'start_time' => Carbon::now()->addDays(5)->setHour(9)->setMinute(0)->setSecond(0),
                'end_time' => Carbon::now()->addDays(5)->setHour(17)->setMinute(0)->setSecond(0),
                'registration_deadline' => Carbon::now()->addDays(4)->setHour(23)->setMinute(59)->setSecond(59),
                'quota' => 120,
                'checkin_code' => 'VEN-JAKARTA-2026',
                'status' => CommunityEvent::STATUS_PUBLISHED,
            ]
        );

        // 2. Event Mendatang 2: OFFLINE & BERBAYAR
        CommunityEvent::updateOrCreate(
            ['slug' => 'bazaar-kuliner-kreatif-surabaya-2026'],
            [
                'title' => 'Bazaar Produk Kreatif & Jejaring Bisnis Kuliner Surabaya',
                'description' => 'Pameran produk unggulan F&B dan retail lokal serta sesi business matching bersama investor dan distributor regional Jawa Timur.',
                'banner_image' => 'community/events/bazaar-kuliner-surabaya.jpg',
                'event_type' => CommunityEvent::TYPE_OFFLINE,
                'price_type' => CommunityEvent::PRICE_TYPE_PAID,
                'price' => 75000,
                'location_name' => 'Grand City Convention Center Lt. 3',
                'location_address' => 'Jl. Walikota Mustajab No. 1, Ketabang, Genteng, Surabaya, Jawa Timur',
                'latitude' => -7.2618210,
                'longitude' => 112.7503140,
                'checkin_radius_meters' => 200,
                'start_time' => Carbon::now()->addDays(12)->setHour(10)->setMinute(0)->setSecond(0),
                'end_time' => Carbon::now()->addDays(12)->setHour(20)->setMinute(0)->setSecond(0),
                'registration_deadline' => Carbon::now()->addDays(10)->setHour(18)->setMinute(0)->setSecond(0),
                'quota' => 80,
                'checkin_code' => 'VEN-SURABAYA-2026',
                'status' => CommunityEvent::STATUS_PUBLISHED,
            ]
        );

        // 3. Event Mendatang 3: ONLINE & BERBAYAR
        CommunityEvent::updateOrCreate(
            ['slug' => 'masterclass-optimasi-hpp-cashflow'],
            [
                'title' => 'Masterclass Online: Optimasi HPP & Manajemen Arus Kas Sehat',
                'description' => 'Workshop intensif perhitungan resep BOM, pengendalian biaya operasional, dan eliminasi kebocoran inventori kasir bersama praktisi akuntansi bisnis via Zoom interaktif.',
                'banner_image' => 'community/events/masterclass-hpp-cashflow.jpg',
                'event_type' => CommunityEvent::TYPE_ONLINE,
                'price_type' => CommunityEvent::PRICE_TYPE_PAID,
                'price' => 149000,
                'location_name' => 'Zoom Cloud Meetings & Private Stream',
                'location_address' => 'Akses link webinar otomatis dibuka pada tiket peserta terdaftar',
                'online_meeting_url' => 'https://meet.cooca.id/masterclass-hpp-2026',
                'latitude' => null,
                'longitude' => null,
                'checkin_radius_meters' => 100,
                'start_time' => Carbon::now()->addDays(18)->setHour(13)->setMinute(30)->setSecond(0),
                'end_time' => Carbon::now()->addDays(18)->setHour(16)->setMinute(30)->setSecond(0),
                'registration_deadline' => Carbon::now()->addDays(17)->setHour(23)->setMinute(59)->setSecond(59),
                'quota' => 50,
                'checkin_code' => 'VEN-ONLINE-HPP2026',
                'status' => CommunityEvent::STATUS_PUBLISHED,
            ]
        );

        // 4. Event Mendatang 4: ONLINE & GRATIS
        CommunityEvent::updateOrCreate(
            ['slug' => 'webinar-nasional-omset-pos-2026'],
            [
                'title' => 'Webinar Nasional: Strategi Melejitkan Transaksi Kasir POS & Omnichannel',
                'description' => 'Kupas tuntas strategi automasi pembukuan kasir POS, integrasi marketplace, dan retensi pelanggan loyal bagi ribuan UMKM Indonesia.',
                'banner_image' => 'community/events/temu-akbar-umkm.jpg',
                'event_type' => CommunityEvent::TYPE_ONLINE,
                'price_type' => CommunityEvent::PRICE_TYPE_FREE,
                'price' => 0,
                'location_name' => 'Live Zoom Webinar & YouTube Live',
                'location_address' => 'Siaran online interaktif terbuka untuk seluruh mitra Cooca',
                'online_meeting_url' => 'https://meet.cooca.id/webinar-nasional-pos',
                'latitude' => null,
                'longitude' => null,
                'checkin_radius_meters' => 100,
                'start_time' => Carbon::now()->addDays(7)->setHour(19)->setMinute(0)->setSecond(0),
                'end_time' => Carbon::now()->addDays(7)->setHour(21)->setMinute(0)->setSecond(0),
                'registration_deadline' => Carbon::now()->addDays(7)->setHour(18)->setMinute(0)->setSecond(0),
                'quota' => 500,
                'checkin_code' => 'VEN-ONLINE-WEBINAR2026',
                'status' => CommunityEvent::STATUS_PUBLISHED,
            ]
        );

        // 4. Event Masa Lalu (SUDAH LEWAT) - Membuktikan filter "yang sudah lewat jangan tampilkan"
        CommunityEvent::firstOrCreate(
            ['slug' => 'kopdar-perdana-owner-cooca-2025'],
            [
                'id' => (string) Str::uuid(),
                'title' => 'Kopdar Perdana Owner Cooca 2025 (Arsip Lampau)',
                'description' => 'Event lama yang sudah berakhir. Event ini otomatis disaring dan tidak boleh tampil di feed komunitas.',
                'event_type' => CommunityEvent::TYPE_OFFLINE,
                'location_name' => 'Auditorium Cooca HQ Kuningan',
                'location_address' => 'Kuningan, Jakarta Selatan',
                'latitude' => -6.2200000,
                'longitude' => 106.8300000,
                'checkin_radius_meters' => 100,
                'start_time' => Carbon::now()->subDays(10)->setHour(9)->setMinute(0)->setSecond(0),
                'end_time' => Carbon::now()->subDays(10)->setHour(17)->setMinute(0)->setSecond(0),
                'registration_deadline' => Carbon::now()->subDays(11)->setHour(23)->setMinute(59)->setSecond(59),
                'quota' => 100,
                'checkin_code' => 'VEN-PAST-2025',
                'status' => CommunityEvent::STATUS_PUBLISHED,
            ]
        );
    }
}
