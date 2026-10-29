<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Social Proof & Public Credibility Settings (Prompt 1)
    |--------------------------------------------------------------------------
    |
    | Controls whether unverified social proof metrics (user count, rating)
    | are displayed on the public landing page hero. Per consumer protection
    | compliance and honesty guidelines, this is false by default until
    | verified reviews and third-party audit links exist.
    |
    */
    'social_proof' => [
        'show' => (bool) env('MARKETING_SHOW_SOCIAL_PROOF', false),
        'user_count' => env('MARKETING_SOCIAL_PROOF_USERS', '10.000+'),
        'rating_value' => env('MARKETING_SOCIAL_PROOF_RATING', '4.9'),
        'rating_source_url' => env('MARKETING_SOCIAL_PROOF_URL', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Case Studies & Scenarios (Prompt 2)
    |--------------------------------------------------------------------------
    |
    | Configurable case study repository for public marketing.
    | Status:
    |   - 'illustration': Example scenario / simulation. Claims must not state "Terverifikasi"
    |     or "Audit", and must display an illustration badge & statutory disclaimer.
    |   - 'verified': Real business customer with explicit publication consent, verified quote,
    |     and business location/photo.
    |
    */
    'case_studies' => [
        'disclaimer' => 'Angka merupakan ilustrasi berdasarkan skenario umum dan bukan jaminan hasil.',

        'items' => [
            [
                'id' => 1,
                'status' => 'illustration',
                'business_name' => 'Kopi Sudut Santai',
                'branches' => '3 Gerai',
                'city' => 'Bandung',
                'industry' => 'fnb',
                'industry_label' => 'F&B / Kedai Kopi (3 Gerai)',
                'stat_badge' => 'Hemat 12.4% Bahan',
                'title' => 'Kopi Sudut Santai: Resep Otomatis Memotong Bahan Basi & Selisih Susu',
                'challenge' => 'Takaran bahan beda per barista. 45 liter susu hilang tiap bulan tanpa nota jelas.',
                'solution' => 'Resep BOM terpotong otomatis tiap pesanan kasir dan terhubung printer dapur KOT.',
                'solution_route' => 'public.solutions.fnb',
                'solution_label' => 'Solusi F&B',
                'verified_data' => [
                    'real_photo_url' => null,
                    'owner_name' => null,
                    'owner_quote' => null,
                    'published_date' => null,
                    'permission_granted' => false,
                ],
                'details' => [
                    'title' => 'Kopi Sudut Santai - 3 Gerai (Bandung)',
                    'description' => 'Sebelumnya pemilik kedai sering mengeluhkan selisih stok susu cair dan bubuk kopi espresso yang mencapai Rp 2,8 juta per bulan tanpa bisa melacak shift mana yang boros.',
                    'steps' => [
                        [
                            'step' => 'Langkah 1: Standardisasi Takaran (BOM)',
                            'desc' => 'Setiap menu kopi dikunci resep gramasi resminya ke dalam sistem COOCA (contoh: 18 gr espresso, 150 ml susu).'
                        ],
                        [
                            'step' => 'Langkah 2: Integrasi Printer Dapur Barista',
                            'desc' => 'Pesanan kasir langsung tercetak otomatis ke meja barista, menghentikan kebiasaan memberi porsi gratis tanpa tiket.'
                        ],
                        [
                            'step' => 'Hasil: Penghematan Rp 3,2 Juta / Bulan',
                            'desc' => 'Food cost turun 12.4% dan pemilik kini dapat memantau stok susu ketiga cabang cukup dari handphone.'
                        ]
                    ]
                ]
            ],
            [
                'id' => 2,
                'status' => 'illustration',
                'business_name' => 'Toko Kelontong Berkah',
                'branches' => '1 Gerai',
                'city' => 'Cirebon',
                'industry' => 'retail',
                'industry_label' => 'Retail / Kelontong & Grosir',
                'stat_badge' => 'Piutang Macet Turun 85%',
                'title' => 'Toko Kelontong Berkah: Bon Hutang Nol Macet dengan Pengingat Otomatis',
                'challenge' => 'Bon kasbon ditulis di buku tulis kertas yang sering robek dan lupa ditagih berbulan-bulan.',
                'solution' => 'Batas pagu hutang otomatis per pelanggan & rekap pengingat via WhatsApp terjadwal.',
                'solution_route' => 'public.solutions.retail',
                'solution_label' => 'Solusi Retail',
                'verified_data' => [
                    'real_photo_url' => null,
                    'owner_name' => null,
                    'owner_quote' => null,
                    'published_date' => null,
                    'permission_granted' => false,
                ],
                'details' => [
                    'title' => 'Toko Kelontong Berkah (Cirebon)',
                    'description' => 'Pemilik toko mengelola lebih dari 2.500 SKU barang kebutuhan pokok dan melayani kasbon pelanggan warga sekitar. Rekap piutang yang tercecer menyebabkan modal macet.',
                    'steps' => [
                        [
                            'step' => 'Langkah 1: Impor Excel 2.500 Barang',
                            'desc' => 'Katalog barang dan modal harga beli dimasukkan ke COOCA dalam 10 menit via impor spreadsheet.'
                        ],
                        [
                            'step' => 'Langkah 2: Plafon Kasbon Digital',
                            'desc' => 'Setiap pelanggan diberi limit kredit maksimal. Transaksi kasir menolak otomatis jika hutang lama belum dilunasi.'
                        ],
                        [
                            'step' => 'Hasil: Arus Kas Kembali Sehat',
                            'desc' => 'Piutang macet berkurang 85% dalam 60 hari berkat pesan pengingat jatuh tempo WhatsApp otomatis.'
                        ]
                    ]
                ]
            ],
            [
                'id' => 3,
                'status' => 'illustration',
                'business_name' => 'Bengkel Motor Perkasa',
                'branches' => '1 Bengkel',
                'city' => 'Semarang',
                'industry' => 'workshop',
                'industry_label' => 'Otomotif / Bengkel & Sparepart',
                'stat_badge' => 'Servis Naik 35%',
                'title' => 'Bengkel Motor Perkasa: Tertib SPK Digital & Transparansi Komisi Montir',
                'challenge' => 'Oli dan sparepart keluar laci tanpa nota. Komisi bagi hasil 6 montir sering memicu ribut.',
                'solution' => 'Surat Perintah Kerja (SPK) digital terikat nomor polisi motor & komisi jasa terhitung otomatis.',
                'solution_route' => 'public.solutions.workshop',
                'solution_label' => 'Solusi Bengkel',
                'verified_data' => [
                    'real_photo_url' => null,
                    'owner_name' => null,
                    'owner_quote' => null,
                    'published_date' => null,
                    'permission_granted' => false,
                ],
                'details' => [
                    'title' => 'Bengkel Motor Perkasa (Semarang)',
                    'description' => 'Sering terjadi komplain montir merasa komisi servisnya dipotong, sementara pemilik bengkel sering mendapati stok kampas rem dan oli berkurang tanpa ada nota.',
                    'steps' => [
                        [
                            'step' => 'Langkah 1: SPK Digital Plat Nomor',
                            'desc' => 'Setiap motor masuk wajib input plat nomor dan keluhan di tablet kasir penerima servis.'
                        ],
                        [
                            'step' => 'Langkah 2: Ambil Sparepart Berbasis SPK',
                            'desc' => 'Bagian gudang hanya mengeluarkan sparepart bila sudah tercatat di SPK resmi sistem.'
                        ],
                        [
                            'step' => 'Hasil: Bebas Selisih & Montir Puas',
                            'desc' => 'Komisi jasa mekanik terhitung otomatis secara transparan. Antrean servis meningkat 35%.'
                        ]
                    ]
                ]
            ],
            [
                'id' => 4,
                'status' => 'illustration',
                'business_name' => 'Fresh Laundry Express',
                'branches' => '1 Gerai',
                'city' => 'Surabaya',
                'industry' => 'services',
                'industry_label' => 'Jasa / Laundry Kiloan & Satuan',
                'stat_badge' => '0% Baju Tertukar',
                'title' => 'Fresh Laundry Express: Zero Baju Tertukar dengan Pelacakan QR Rak',
                'challenge' => 'Nota bon kertas luntur basah kena detergen. Pakaian pelanggan sering tertukar di rak saat ramai.',
                'solution' => 'Label barcode per kantong, nomor rak digital, dan WhatsApp otomatis saat cuci selesai.',
                'solution_route' => 'public.solutions.services',
                'solution_label' => 'Solusi Jasa',
                'verified_data' => [
                    'real_photo_url' => null,
                    'owner_name' => null,
                    'owner_quote' => null,
                    'published_date' => null,
                    'permission_granted' => false,
                ],
                'details' => [
                    'title' => 'Fresh Laundry Express (Surabaya)',
                    'description' => 'Menangani hingga 400 kg cucian per hari. Masalah utama adalah nota bon kertas basah terkena detergen dan pakaian pelanggan sering tertukar saat jam sibuk.',
                    'steps' => [
                        [
                            'step' => 'Langkah 1: Label Barcode Anti-Air',
                            'desc' => 'Struk barcode ditempelkan pada keranjang cucian saat penimbangan awal.'
                        ],
                        [
                            'step' => 'Langkah 2: Alur Rak Selesai Siap Ambil',
                            'desc' => 'Saat disetrika dan masuk rak, kasir memindai barcode dan status WhatsApp otomatis terkirim ke pelanggan.'
                        ],
                        [
                            'step' => 'Hasil: Nol Komplain Pakaian Hilang',
                            'desc' => 'Kecepatan pengambilan cucian meningkat dan kepuasan pelanggan naik signifikan.'
                        ]
                    ]
                ]
            ],
            [
                'id' => 5,
                'status' => 'illustration',
                'business_name' => 'Boutique Hijab Syari',
                'branches' => '1 Toko Fisik + Online',
                'city' => 'Solo',
                'industry' => 'fashion',
                'industry_label' => 'Fashion / Butik & Pakaian',
                'stat_badge' => 'Stok Multi-Kanal Sinkron',
                'title' => 'Boutique Hijab Syari: Stok Live TikTok & Toko Fisik Terkunci Akurat',
                'challenge' => 'Barang live streaming malam terjual 50 pcs, tapi siangnya masih dibeli orang di toko fisik.',
                'solution' => 'Sentralisasi SKU variasi warna/ukuran dengan sinkronisasi inventori real-time.',
                'solution_route' => 'public.solutions.retail',
                'solution_label' => 'Solusi Inventori',
                'verified_data' => [
                    'real_photo_url' => null,
                    'owner_name' => null,
                    'owner_quote' => null,
                    'published_date' => null,
                    'permission_granted' => false,
                ],
                'details' => [
                    'title' => 'Boutique Hijab Syari (Solo)',
                    'description' => 'Menjual pakaian muslimah via siaran live media sosial sekaligus memiliki butik offline. Stok sering bentrok karena penjualan malam belum tercatat saat toko buka pagi.',
                    'steps' => [
                        [
                            'step' => 'Langkah 1: Satu Database Sentral',
                            'desc' => 'Seluruh pesanan live diinput ke kasir COOCA sehingga etalase offline langsung mengetahui sisa stok riil.'
                        ],
                        [
                            'step' => 'Hasil: Zero Overselling',
                            'desc' => 'Tidak ada lagi pesanan marketplace atau pelanggan toko yang harus dibatalkan karena stok dobel jual.'
                        ]
                    ]
                ]
            ],
            [
                'id' => 6,
                'status' => 'illustration',
                'business_name' => 'Konveksi Maju Bersama',
                'branches' => '1 Workshop',
                'city' => 'Pekalongan',
                'industry' => 'fashion',
                'industry_label' => 'Manufaktur / Konveksi & Sablon',
                'stat_badge' => 'Margin Terkunci 22%',
                'title' => 'Konveksi Maju Bersama: Presisi HPP Produksi Seragam Sebelum Tender',
                'challenge' => 'Pasang harga tender modal kira-kira. Saat bahan katun naik, proyek berakhir impas nombok.',
                'solution' => 'Estimator HPP multi-bahan (kain, benang, sablon) & tracking uang muka (DP) bertahap.',
                'solution_route' => 'kalkulator.hpp',
                'solution_label' => 'Kalkulator HPP',
                'verified_data' => [
                    'real_photo_url' => null,
                    'owner_name' => null,
                    'owner_quote' => null,
                    'published_date' => null,
                    'permission_granted' => false,
                ],
                'details' => [
                    'title' => 'Konveksi Maju Bersama (Pekalongan)',
                    'description' => 'Menerima pesanan seragam instansi dan komunitas. Kerap kali merugi karena kenaikan harga bahan kain katun di tengah proses penjahitan.',
                    'steps' => [
                        [
                            'step' => 'Langkah 1: Simulasi Biaya HPP Bahan & Upah',
                            'desc' => 'Menghitung kebutuhan kain per meter, kancing, benang, sablon, dan upah jahit sebelum mengajukan penawaran harga.'
                        ],
                        [
                            'step' => 'Hasil: Margin Keuntungan Aman',
                            'desc' => 'Margin laba kotor 22% terlindungi dari fluktuasi bahan baku dan cashflow proyek terkontrol.'
                        ]
                    ]
                ]
            ],
        ],
    ],
];
