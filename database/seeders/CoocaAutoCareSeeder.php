<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Template\BusinessTemplateService;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\CommerceReservation;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CoocaAutoCareSeeder extends Seeder
{
    private BusinessTemplateService $templateService;
    private AutoJournalService $journalService;

    public function __construct()
    {
        $this->templateService = new BusinessTemplateService();
        $this->journalService = new AutoJournalService();
    }

    public function run(): void
    {
        $this->command?->info('Seeding Automotive Workshop: COOCA AutoCare...');

        DB::transaction(function () {
            // 1. Business Creation
            $business = Business::updateOrCreate(
                ['slug' => 'cooca-autocare'],
                [
                    'name' => 'COOCA AutoCare',
                    'currency' => 'IDR',
                    'currency_precision' => 0,
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                    'phone' => '0811-2233-4404',
                    'email' => 'kontak@coocaautocare.id',
                    'address' => 'Jl. Pahlawan Seribu No. 77, BSD City, Serpong, Tangerang Selatan',
                    'tax_identification_number' => '01.234.567.8-014.000',
                    'bank_name' => 'Bank Central Asia (BCA)',
                    'bank_account_number' => '8800112204',
                    'bank_account_holder' => 'PT Cooca Otomotif Prima',
                    'pos_enable_tax' => true,
                    'pos_tax_percent' => 11,
                    'template_code' => 'service_workshop',
                    'industry_category' => 'services',
                    'is_demo' => true,
                    'is_active' => true,
                ]
            );

            Context::setBusiness($business);
            $this->journalService->ensureStandardAccounts($business);

            // 2. Apply Template
            $template = BusinessTypeTemplate::where('code', 'service_workshop')->first();
            if ($template) {
                $this->templateService->apply($business, $template);
            }

            // 3. User Accounts & Roles
            $roles = Role::whereNull('business_id')->get()->keyBy('slug');

            $demoUsers = [
                ['key' => 'owner', 'name' => 'Hendra Setiawan (Owner & Managing Director)', 'email' => 'owner@coocaautocare.id', 'role' => 'owner'],
                ['key' => 'manager', 'name' => 'Bambang Sudibyo (Service Manager & Kepala Bengkel)', 'email' => 'manager@coocaautocare.id', 'role' => 'manager'],
                ['key' => 'cashier', 'name' => 'Anisa Rahmawati (Front Desk & Kasir Servis)', 'email' => 'cashier@coocaautocare.id', 'role' => 'cashier'],
                ['key' => 'mechanic', 'name' => 'Rizal Pratama (Senior Mechanic & Diagnostician)', 'email' => 'mechanic@coocaautocare.id', 'role' => 'staff'],
                ['key' => 'finance', 'name' => 'Lestari Handayani (Finance & Sparepart Accounting)', 'email' => 'finance@coocaautocare.id', 'role' => 'finance'],
            ];

            $userModels = [];
            foreach ($demoUsers as $uData) {
                $user = User::updateOrCreate(
                    ['email' => $uData['email']],
                    [
                        'name' => $uData['name'],
                        'phone' => '0812' . rand(10000000, 99999999),
                        'password' => Hash::make('password123'),
                        'email_verified_at' => now(),
                        'onboarding_completed' => true,
                        'onboarding_completed_at' => now(),
                        'onboarding_current_step' => 0,
                        'onboarding_version' => 1,
                    ]
                );

                $roleModel = $roles[$uData['role']] ?? null;
                $business->users()->syncWithoutDetaching([
                    $user->id => [
                        'id' => (string) Str::uuid(),
                        'role' => $uData['role'],
                        'role_id' => $roleModel?->id,
                    ],
                ]);

                if (! $user->active_business_id) {
                    $user->update(['active_business_id' => $business->id]);
                }
                $userModels[$uData['key']] = $user;
            }

            // Global Testing Accounts
            $globalTesters = ['demo@cooca.id', 'testing@cooca.id'];
            foreach ($globalTesters as $gEmail) {
                $gUser = User::where('email', $gEmail)->first();
                if ($gUser) {
                    $business->users()->syncWithoutDetaching([
                        $gUser->id => [
                            'id' => (string) Str::uuid(),
                            'role' => 'owner',
                            'role_id' => $roles['owner']?->id,
                        ],
                    ]);
                }
            }

            // 4. Locations (2 Workshops)
            $locationSpecs = [
                [
                    'name' => 'BSD Pusat (Bengkel & Service Center Utama)',
                    'slug' => 'bsd-pusat',
                    'code' => 'LOC-AC-BSD',
                    'type' => 'outlet',
                    'is_primary' => true,
                    'address' => 'Jl. Pahlawan Seribu No. 77, BSD City, Serpong, Tangerang Selatan',
                    'phone' => '021-5378001',
                ],
                [
                    'name' => 'Bintaro Sektor 9 (Cabang Service Express)',
                    'slug' => 'bintaro-sektor-9',
                    'code' => 'LOC-AC-BIN',
                    'type' => 'outlet',
                    'is_primary' => false,
                    'address' => 'Jl. Bintaro Utama 9 Blok HB-1 No. 5, Pondok Aren, Tangerang Selatan',
                    'phone' => '021-7452002',
                ],
            ];

            $locations = [];
            foreach ($locationSpecs as $lSpec) {
                $locations[$lSpec['slug']] = Location::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => $lSpec['slug']],
                    [
                        'name' => $lSpec['name'],
                        'code' => $lSpec['code'],
                        'type' => $lSpec['type'],
                        'is_primary' => $lSpec['is_primary'],
                        'is_active' => true,
                        'address' => $lSpec['address'],
                        'phone' => $lSpec['phone'],
                    ]
                );
            }

            $primaryLoc = $locations['bsd-pusat'];

            // 5. Units Lookup
            $units = Unit::all()->keyBy('code');

            // 6. Categories
            $categoriesData = [
                'Jasa Servis & Perawatan Mesin' => 'Jasa tune up, servis berkala, scanner OBD2, dan gurah mesin.',
                'Jasa Kaki-kaki & Suspensi' => 'Spooring 3D, balancing 4 roda, servis rem, dan perbaikan shockbreaker.',
                'Jasa AC & Kelistrikan Mobil' => 'Servis AC tanpa bongkar, flushing oli kompresor, dan cek dinamo/aki.',
                'Oli Mesin & Pelumas Transmisi' => 'Oli mesin bensin/diesel fully synthetic dan oli transmisi matic ATF/CVT.',
                'Suku Cadang & Filter' => 'Filter oli, filter udara, filter AC kabin, busi iridium, dan v-belt.',
                'Sistem Pengereman & Understeel' => 'Kampas rem depan/belakang, minyak rem DOT 4, dan bearing roda.',
                'Aki & Kelistrikan' => 'Aki kering bebas perawatan (MF), bohlam lampu LED, dan klakson.',
            ];

            $categories = [];
            foreach ($categoriesData as $cName => $cDesc) {
                $categories[$cName] = ProductCategory::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => Str::slug($cName)],
                    ['name' => $cName, 'description' => $cDesc]
                );
            }

            // 7. Suppliers (10 Automotive Distributors)
            $supplierSpecs = [
                ['name' => 'PT Astra Otoparts Tbk', 'contact' => 'Dharmawan Hartono', 'phone' => '021-4603550', 'email' => 'sales@astra-otoparts.test', 'address' => 'Jl. Pegangsaan Dua Km 2.2, Kelapa Gading, Jakarta Utara'],
                ['name' => 'PT Shell Indonesia (Lubricants)', 'contact' => 'Rina Wijayanti', 'phone' => '021-75924700', 'email' => 'order@shell-lubricants.test', 'address' => 'Talavera Office Park, TB Simatupang, Jakarta Selatan'],
                ['name' => 'PT Castrol Indonesia', 'contact' => 'Eko Prasetyo', 'phone' => '021-52900011', 'email' => 'commercial@castrol.test', 'address' => 'Perwata Tower Lt. 15, Pluit Raya, Jakarta Utara'],
                ['name' => 'PT Denso Sales Indonesia', 'contact' => 'Yusuf Maulana', 'phone' => '021-6518877', 'email' => 'sales@denso.test', 'address' => 'Sunter II, Jl. Gaya Motor I No. 6, Jakarta Utara'],
                ['name' => 'PT NGK Busi Indonesia', 'contact' => 'Hengky Kurniawan', 'phone' => '021-8710645', 'email' => 'order@ngk-sparkplugs.test', 'address' => 'Jl. Raya Jakarta-Bogor Km 26.6, Ciracas, Jakarta Timur'],
                ['name' => 'PT Bendix Brake Indonesia', 'contact' => 'Albert Susanto', 'phone' => '021-58309900', 'email' => 'sales@bendix-id.test', 'address' => 'Kawasan Industri Puri Niaga, Kembangan, Jakarta Barat'],
                ['name' => 'PT GS Battery Indonesia', 'contact' => 'Budi Santoso', 'phone' => '021-4602877', 'email' => 'order@gs-battery.test', 'address' => 'Jl. Laksamana Yos Sudarso, Sunter, Jakarta Utara'],
                ['name' => 'PT Prestone Lubricants & Coolant', 'contact' => 'Didik Setiawan', 'phone' => '021-56961122', 'email' => 'b2b@prestone.test', 'address' => 'Kawasan Industri Daan Mogot, Jakarta Barat'],
                ['name' => 'PT Bosch Automotive Indonesia', 'contact' => 'Maya Anggraeni', 'phone' => '021-30055800', 'email' => 'sales@bosch-auto.test', 'address' => 'Gedung Ventura Lt. 8, Cilandak, Jakarta Selatan'],
                ['name' => 'PT Mobil 1 Lubricants Indonesia', 'contact' => 'Ferry Handoko', 'phone' => '021-5203300', 'email' => 'order@mobil1.test', 'address' => 'Wisma GKBI Lt. 27, Sudirman, Jakarta'],
            ];

            $suppliers = [];
            foreach ($supplierSpecs as $sSpec) {
                $suppliers[] = Supplier::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => Str::slug($sSpec['name'])],
                    [
                        'name' => $sSpec['name'],
                        'contact_person' => $sSpec['contact'],
                        'phone' => $sSpec['phone'],
                        'email' => $sSpec['email'],
                        'address' => $sSpec['address'],
                        'notes' => 'Termin Pembayaran: Net 30 Hari',
                    ]
                );
            }

            // 8. 24 Automotive Services (type='service')
            $servicesCatalog = [
                ['code' => 'SRV-AC-001', 'name' => 'Jasa Tune Up & Servis Berkala Injeksi Mesin', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 150000, 'cost' => 45000, 'desc' => 'Pembersihan throttle body, cek busi, cek filter, scanner OBD2 45 parameter.'],
                ['code' => 'SRV-AC-002', 'name' => 'Jasa Ganti Oli Mesin & Filter Oli', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 35000, 'cost' => 8000, 'desc' => 'Kuras oli mesin dan pasang filter oli baru dengan torsi standar pabrik.'],
                ['code' => 'SRV-AC-003', 'name' => 'Jasa Flushing Oli Transmisi Matic ATF / CVT', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 175000, 'cost' => 45000, 'desc' => 'Kuras tuntas oli matic menggunakan mesin ATF changer sirkulasi bertekanan.'],
                ['code' => 'SRV-AC-004', 'name' => 'Jasa Servis Rem 4 Roda & Bleeding Minyak Rem', 'cat' => 'Jasa Kaki-kaki & Suspensi', 'price' => 125000, 'cost' => 35000, 'desc' => 'Bongkar, bersihkan kerak kampas rem, amplas tromol/cakram, pelumasan pin kaliper.'],
                ['code' => 'SRV-AC-005', 'name' => 'Jasa Spooring 3D Komputerized & Balancing 4 Roda', 'cat' => 'Jasa Kaki-kaki & Suspensi', 'price' => 200000, 'cost' => 50000, 'desc' => 'Kalibrasi camber, caster, toe sudut roda 3D sensor dan balancing timbal.'],
                ['code' => 'SRV-AC-006', 'name' => 'Jasa Servis & Cuci AC Mobil No-Bongkar (Evaporator Cleaning)', 'cat' => 'Jasa AC & Kelistrikan Mobil', 'price' => 250000, 'cost' => 65000, 'desc' => 'Cuci evaporator kamera endoskopi, fogging disinfektan kabin antibakteri.'],
                ['code' => 'SRV-AC-007', 'name' => 'Jasa Flushing Oli Kompresor AC & Isi Freon R134a Komplit', 'cat' => 'Jasa AC & Kelistrikan Mobil', 'price' => 350000, 'cost' => 95000, 'desc' => 'Pengurasan oli kompresor lama, vakum sistem, isi oli PAG baru dan freon murni.'],
                ['code' => 'SRV-AC-008', 'name' => 'Jasa Gurah Mesin / Carbon Cleaner Ruang Bakar', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 220000, 'cost' => 60000, 'desc' => 'Pembersihan kerak karbon piston dengan cairan busa kimia dan vakum hisap.'],
                ['code' => 'SRV-AC-009', 'name' => 'Jasa Kalibrasi Throttle Body & Reset ECU Scanner', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 95000, 'cost' => 25000, 'desc' => 'Reset adaptasi idle RPM mesin dan pembacaan fault code sensor DTC.'],
                ['code' => 'SRV-AC-010', 'name' => 'Jasa Penggantian Shockbreaker Depan (Sepasang)', 'cat' => 'Jasa Kaki-kaki & Suspensi', 'price' => 180000, 'cost' => 45000, 'desc' => 'Bongkar per keong dan pemasangan shockbreaker depan baru.'],
                ['code' => 'SRV-AC-011', 'name' => 'Jasa Penggantian Timing Belt / Fan Belt Set', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 165000, 'cost' => 40000, 'desc' => 'Penyetelan tegangan v-belt dinamo ampere dan kompresor AC.'],
                ['code' => 'SRV-AC-012', 'name' => 'Jasa Kuras Radiator Coolant Mesin (Flushing Radiator)', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 75000, 'cost' => 20000, 'desc' => 'Pengurasan air radiator lama dan sirkulasi pembersihan kerak pipa pendingin.'],
                ['code' => 'SRV-AC-013', 'name' => 'Jasa Penggantian Kampas Kopling Manual (Clutch Set)', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 450000, 'cost' => 120000, 'desc' => 'Turun transmisi manual, ganti plat kopling, dekrup matahari, dan bearing release.'],
                ['code' => 'SRV-AC-014', 'name' => 'Jasa Fogging Kabin Antibakteri & Aromaterapi', 'cat' => 'Jasa AC & Kelistrikan Mobil', 'price' => 65000, 'cost' => 15000, 'desc' => 'Asap antibakteri eliminasi kuman bau apek dan tungau di jok kabin mobil.'],
                ['code' => 'SRV-AC-015', 'name' => 'Jasa Detailing Ruang Mesin (Engine Bay Detailing)', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 120000, 'cost' => 30000, 'desc' => 'Pembersihan minyak oli kerak blok mesin dengan degreaser dan dressing proteksi.'],
                ['code' => 'SRV-AC-016', 'name' => 'Jasa Uji Emisi Gas Buang Resmi & Upload KLHK', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 110000, 'cost' => 25000, 'desc' => 'Uji gas CO, HC, CO2, O2, Lambda dan cetak sertifikat resmi lulus uji emisi.'],
                ['code' => 'SRV-AC-017', 'name' => 'Jasa Poles & Coating Headlamp Menguning (Sepasang)', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 180000, 'cost' => 45000, 'desc' => 'Amplas bertingkat dan nano burn coating mika lampu kusam kembali jernih.'],
                ['code' => 'SRV-AC-018', 'name' => 'Jasa Pengecekan Aki & Sistem Pengisian Dinamo Alternator', 'cat' => 'Jasa AC & Kelistrikan Mobil', 'price' => 35000, 'cost' => 8000, 'desc' => 'Analisa kesehatan aki (SOH, SOC, CCA) dengan digital battery analyzer.'],
                ['code' => 'SRV-AC-019', 'name' => 'Jasa Bubut Piringan Cakram Rem On-Car (Sepasang)', 'cat' => 'Jasa Kaki-kaki & Suspensi', 'price' => 220000, 'cost' => 55000, 'desc' => 'Perataan piringan cakram bergelombang langsung di mobil tanpa copot piringan.'],
                ['code' => 'SRV-AC-020', 'name' => 'Jasa General Check-Up 45 Titik Pra-Mudik / Trip Jauh', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 85000, 'cost' => 20000, 'desc' => 'Pengecekan menyeluruh rem, ban, aki, lampu, oli, radiator, kaki-kaki, wiper.'],
                ['code' => 'SRV-AC-021', 'name' => 'Jasa Penggantian Fuel Pump & Filter Bensin In-Tank', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 150000, 'cost' => 40000, 'desc' => 'Bongkar jok belakang/tangki dan pasang pompa bensin elektrik baru.'],
                ['code' => 'SRV-AC-022', 'name' => 'Jasa Servis Dinamo Starter / Dinamo Alternator', 'cat' => 'Jasa AC & Kelistrikan Mobil', 'price' => 175000, 'cost' => 45000, 'desc' => 'Bongkar dinamo, ganti brush arang komutator, bersihkan rotor dan lilitan.'],
                ['code' => 'SRV-AC-023', 'name' => 'Jasa Penggantian Link Stabilizer & Bushing Kaki-Kaki', 'cat' => 'Jasa Kaki-kaki & Suspensi', 'price' => 110000, 'cost' => 30000, 'desc' => 'Bongkar dan pasang link stabilizer serta karet bushing arm yang pecah.'],
                ['code' => 'SRV-AC-024', 'name' => 'Jasa Derek & Emergency Roadside Assistance (Radius 10km)', 'cat' => 'Jasa Servis & Perawatan Mesin', 'price' => 350000, 'cost' => 100000, 'desc' => 'Layanan derek gendong towing dan jump start aki mogok 24 jam.'],
            ];

            $productModels = [];
            foreach ($servicesCatalog as $sSpec) {
                $catId = $categories[$sSpec['cat']]->id ?? null;
                $unitObj = $units['sesi'] ?? $units['paket'] ?? $units['pcs'];

                $product = Product::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $sSpec['code']],
                    [
                        'type' => Product::TYPE_SERVICE,
                        'bom_consumption_type' => Product::BOM_NO_BOM,
                        'category_id' => $catId,
                        'output_unit_id' => $unitObj->id,
                        'name' => $sSpec['name'],
                        'slug' => Str::slug($sSpec['name']),
                        'selling_price' => $sSpec['price'],
                        'base_cost' => $sSpec['cost'],
                        'show_in_pos' => true,
                        'show_in_sales_order' => true,
                        'show_in_website' => true,
                        'business_type_hint' => 'service_workshop',
                        'description' => $sSpec['desc'],
                        'is_active' => true,
                    ]
                );

                $productModels[$sSpec['code']] = $product;
            }

            // 9. 34 Spare Parts & Consumables (type='goods')
            $sparePartsCatalog = [
                // Oils & Lubricants (7 SKUs)
                ['code' => 'PRT-OIL-01', 'name' => 'Oli Mesin Shell Helix Ultra 5W-40 Fully Synthetic 4L', 'cat' => 'Oli Mesin & Pelumas Transmisi', 'sell' => 450000, 'cost' => 340000, 'unit' => 'pcs', 'stock' => 30],
                ['code' => 'PRT-OIL-02', 'name' => 'Oli Mesin Mobil 1 Triple Action Power 0W-20 4L', 'cat' => 'Oli Mesin & Pelumas Transmisi', 'sell' => 580000, 'cost' => 440000, 'unit' => 'pcs', 'stock' => 25],
                ['code' => 'PRT-OIL-03', 'name' => 'Oli Mesin Castrol Magnatec 10W-40 4L', 'cat' => 'Oli Mesin & Pelumas Transmisi', 'sell' => 360000, 'cost' => 275000, 'unit' => 'pcs', 'stock' => 40],
                ['code' => 'PRT-OIL-04', 'name' => 'Oli Transmisi Matic Toyota T-IV ATF 4L Galon', 'cat' => 'Oli Mesin & Pelumas Transmisi', 'sell' => 320000, 'cost' => 245000, 'unit' => 'pcs', 'stock' => 20],
                ['code' => 'PRT-OIL-05', 'name' => 'Oli Transmisi Matic Toyota WS Low Viscosity 4L', 'cat' => 'Oli Mesin & Pelumas Transmisi', 'sell' => 390000, 'cost' => 305000, 'unit' => 'pcs', 'stock' => 25],
                ['code' => 'PRT-OIL-06', 'name' => 'Oli Transmisi CVT Honda HCF-2 3.5L', 'cat' => 'Oli Mesin & Pelumas Transmisi', 'sell' => 420000, 'cost' => 325000, 'unit' => 'pcs', 'stock' => 20],
                ['code' => 'PRT-OIL-07', 'name' => 'Minyak Rem Prestone DOT 4 Synthetic 300ml', 'cat' => 'Sistem Pengereman & Understeel', 'sell' => 45000, 'cost' => 28000, 'unit' => 'pcs', 'stock' => 50],

                // Filters & Spark Plugs (8 SKUs)
                ['code' => 'PRT-FLT-01', 'name' => 'Filter Oli Denso Original Innova / Fortuner / Hilux', 'cat' => 'Suku Cadang & Filter', 'sell' => 48000, 'cost' => 30000, 'unit' => 'pcs', 'stock' => 60],
                ['code' => 'PRT-FLT-02', 'name' => 'Filter Oli Denso Original Avanza / Xenia / Rush / Terios', 'cat' => 'Suku Cadang & Filter', 'sell' => 38000, 'cost' => 24000, 'unit' => 'pcs', 'stock' => 70],
                ['code' => 'PRT-FLT-03', 'name' => 'Filter Oli Honda Genuine Brio / Mobilio / HR-V / CR-V', 'cat' => 'Suku Cadang & Filter', 'sell' => 55000, 'cost' => 36000, 'unit' => 'pcs', 'stock' => 50],
                ['code' => 'PRT-FLT-04', 'name' => 'Filter Udara Mesin Denso Avanza / Xenia Dual VVT-i', 'cat' => 'Suku Cadang & Filter', 'sell' => 85000, 'cost' => 55000, 'unit' => 'pcs', 'stock' => 40],
                ['code' => 'PRT-FLT-05', 'name' => 'Filter AC Kabin Carbon Antibakteri Brio / Jazz GK5', 'cat' => 'Suku Cadang & Filter', 'sell' => 95000, 'cost' => 60000, 'unit' => 'pcs', 'stock' => 35],
                ['code' => 'PRT-SPK-01', 'name' => 'Busi Iridium NGK Laser ILZKR7B-11 (pcs)', 'cat' => 'Suku Cadang & Filter', 'sell' => 110000, 'cost' => 75000, 'unit' => 'pcs', 'stock' => 80],
                ['code' => 'PRT-SPK-02', 'name' => 'Busi Denso Iridium Power IK20 (pcs)', 'cat' => 'Suku Cadang & Filter', 'sell' => 95000, 'cost' => 65000, 'unit' => 'pcs', 'stock' => 75],
                ['code' => 'PRT-BLT-01', 'name' => 'V-Belt Fan Belt Bando 6PK1150 Avanza', 'cat' => 'Suku Cadang & Filter', 'sell' => 125000, 'cost' => 85000, 'unit' => 'pcs', 'stock' => 30],

                // Brakes & Suspension (8 SKUs)
                ['code' => 'PRT-BRK-01', 'name' => 'Kampas Rem Depan Bendix General CT Innova Reborn', 'cat' => 'Sistem Pengereman & Understeel', 'sell' => 380000, 'cost' => 280000, 'unit' => 'pcs', 'stock' => 20],
                ['code' => 'PRT-BRK-02', 'name' => 'Kampas Rem Depan Bendix General CT Avanza / Xenia', 'cat' => 'Sistem Pengereman & Understeel', 'sell' => 260000, 'cost' => 190000, 'unit' => 'pcs', 'stock' => 30],
                ['code' => 'PRT-BRK-03', 'name' => 'Kampas Rem Belakang Tromol MK Kashiyama Avanza', 'cat' => 'Sistem Pengereman & Understeel', 'sell' => 165000, 'cost' => 115000, 'unit' => 'pcs', 'stock' => 25],
                ['code' => 'PRT-BRK-04', 'name' => 'Kampas Rem Depan Akebono Original Honda HR-V / Civic', 'cat' => 'Sistem Pengereman & Understeel', 'sell' => 450000, 'cost' => 340000, 'unit' => 'pcs', 'stock' => 20],
                ['code' => 'PRT-LNK-01', 'name' => 'Link Stabilizer Depan 555 Japan Innova Reborn (Set)', 'cat' => 'Sistem Pengereman & Understeel', 'sell' => 380000, 'cost' => 270000, 'unit' => 'pcs', 'stock' => 15],
                ['code' => 'PRT-SHK-01', 'name' => 'Shockbreaker Depan Kayaba Excel-G Avanza Pair', 'cat' => 'Sistem Pengereman & Understeel', 'sell' => 1150000, 'cost' => 880000, 'unit' => 'pcs', 'stock' => 10],
                ['code' => 'PRT-TIE-01', 'name' => 'Tie Rod End Set 555 Japan Avanza / Xenia', 'cat' => 'Sistem Pengereman & Understeel', 'sell' => 290000, 'cost' => 210000, 'unit' => 'pcs', 'stock' => 18],
                ['code' => 'PRT-BRG-01', 'name' => 'Wheel Bearing Roda Depan NSK Japan Avanza with ABS', 'cat' => 'Sistem Pengereman & Understeel', 'sell' => 220000, 'cost' => 155000, 'unit' => 'pcs', 'stock' => 22],

                // Batteries & Electrical (6 SKUs)
                ['code' => 'PRT-BAT-01', 'name' => 'Aki Kering GS Astra MF 55D23L 12V 60Ah (Innova / CRV)', 'cat' => 'Aki & Kelistrikan', 'sell' => 1050000, 'cost' => 820000, 'unit' => 'pcs', 'stock' => 15],
                ['code' => 'PRT-BAT-02', 'name' => 'Aki Kering GS Astra MF 40B19L 12V 35Ah (Brio / Avanza)', 'cat' => 'Aki & Kelistrikan', 'sell' => 820000, 'cost' => 640000, 'unit' => 'pcs', 'stock' => 20],
                ['code' => 'PRT-BAT-03', 'name' => 'Aki Kering Amaron Pro 42B20L 12V 35Ah Garansi 12 Bln', 'cat' => 'Aki & Kelistrikan', 'sell' => 890000, 'cost' => 690000, 'unit' => 'pcs', 'stock' => 18],
                ['code' => 'PRT-LGT-01', 'name' => 'Bohlam Lampu Depan Osram H4 Night Breaker 12V 60/55W', 'cat' => 'Aki & Kelistrikan', 'sell' => 240000, 'cost' => 170000, 'unit' => 'pcs', 'stock' => 25],
                ['code' => 'PRT-LGT-02', 'name' => 'Bohlam LED Headlamp Philips Ultinon Essential H7', 'cat' => 'Aki & Kelistrikan', 'sell' => 520000, 'cost' => 380000, 'unit' => 'pcs', 'stock' => 15],
                ['code' => 'PRT-HRN-01', 'name' => 'Klakson Keong Hella Midnight Black Twin Tone 12V', 'cat' => 'Aki & Kelistrikan', 'sell' => 180000, 'cost' => 125000, 'unit' => 'pcs', 'stock' => 20],

                // Fluids & Maintenance (5 SKUs)
                ['code' => 'PRT-CLT-01', 'name' => 'Air Radiator Coolant Prestone Cor-Guard Ready to Use 4L', 'cat' => 'Suku Cadang & Filter', 'sell' => 95000, 'cost' => 68000, 'unit' => 'pcs', 'stock' => 35],
                ['code' => 'PRT-CLN-01', 'name' => 'Cairan Carbon Cleaner Throttle Body CRC 500ml', 'cat' => 'Suku Cadang & Filter', 'sell' => 85000, 'cost' => 58000, 'unit' => 'pcs', 'stock' => 40],
                ['code' => 'PRT-WIP-01', 'name' => 'Wiper Blade Frameless Bosch Clear Advantage 24 & 16 Inch Pair', 'cat' => 'Suku Cadang & Filter', 'sell' => 145000, 'cost' => 95000, 'unit' => 'pcs', 'stock' => 30],
                ['code' => 'PRT-PST-01', 'name' => 'Power Steering Fluid STP Synthetic 354ml', 'cat' => 'Oli Mesin & Pelumas Transmisi', 'sell' => 45000, 'cost' => 30000, 'unit' => 'pcs', 'stock' => 30],
                ['code' => 'PRT-CAP-01', 'name' => 'Tutup Radiator Denso 1.1 Bar Original Valve', 'cat' => 'Suku Cadang & Filter', 'sell' => 55000, 'cost' => 35000, 'unit' => 'pcs', 'stock' => 25],
            ];

            foreach ($sparePartsCatalog as $gSpec) {
                $catId = $categories[$gSpec['cat']]->id ?? null;
                $unitObj = $units[$gSpec['unit']] ?? $units['pcs'];

                $product = Product::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $gSpec['code']],
                    [
                        'type' => Product::TYPE_GOODS,
                        'bom_consumption_type' => Product::BOM_NO_BOM,
                        'category_id' => $catId,
                        'output_unit_id' => $unitObj->id,
                        'name' => $gSpec['name'],
                        'slug' => Str::slug($gSpec['name']),
                        'selling_price' => $gSpec['sell'],
                        'base_cost' => $gSpec['cost'],
                        'show_in_pos' => true,
                        'show_in_sales_order' => true,
                        'show_in_website' => true,
                        'business_type_hint' => 'service_workshop',
                        'description' => "Suku cadang dan sparepart original COOCA AutoCare: {$gSpec['name']}",
                        'is_active' => true,
                    ]
                );

                $productModels[$gSpec['code']] = $product;

                // Create initial inventory stock across both workshop locations
                foreach ($locations as $locKey => $loc) {
                    $stockQty = $locKey === 'bsd-pusat' ? $gSpec['stock'] : round($gSpec['stock'] * 0.6);

                    InventoryStock::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'location_id' => $loc->id,
                            'product_id' => $product->id,
                        ],
                        [
                            'material_id' => null,
                            'quantity' => $stockQty,
                            'reserved_quantity' => 0,
                            'last_cost' => $gSpec['cost'],
                            'avg_purchase_cost' => $gSpec['cost'],
                        ]
                    );

                    StockMovement::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'product_id' => $product->id,
                            'location_id' => $loc->id,
                            'reference_number' => "INIT-PART-{$loc->code}-{$product->code}",
                        ],
                        [
                            'material_id' => null,
                            'movement_type' => StockMovement::TYPE_INITIAL,
                            'quantity_change' => $stockQty,
                            'balance_after' => $stockQty,
                            'unit_cost' => $gSpec['cost'],
                            'total_cost' => $stockQty * $gSpec['cost'],
                            'notes' => "Stok Awal Sparepart {$loc->name}",
                            'created_by' => $userModels['owner']->id,
                        ]
                    );
                }
            }

            // 10. Customers & 35 Vehicles (with License Plate, Model, Mileage)
            $customerVehicleCatalog = [
                ['name' => 'Robert Gunawan', 'phone' => '0812-7001-0001', 'plate' => 'B 1024 SS', 'model' => 'Toyota Kijang Innova Zenix 2.0 V 2023', 'km' => 14200],
                ['name' => 'Fandi Ahmad Prasetyo', 'phone' => '0812-7001-0002', 'plate' => 'B 2841 PQR', 'model' => 'Honda CR-V 1.5 Turbo Prestige 2021', 'km' => 42500],
                ['name' => 'Ir. Hendra Wijaya', 'phone' => '0812-7001-0003', 'plate' => 'B 1789 WXY', 'model' => 'Toyota Avanza 1.5 G CVT 2022', 'km' => 28300],
                ['name' => 'Budi Hartono Santoso', 'phone' => '0812-7001-0004', 'plate' => 'B 9912 TUL', 'model' => 'Mitsubishi Pajero Sport Dakar 4x2 2020', 'km' => 67800],
                ['name' => 'Darmawan Sutanto', 'phone' => '0812-7001-0005', 'plate' => 'B 3145 KLO', 'model' => 'Toyota Fortuner 2.8 VRZ GR Sport 2022', 'km' => 31400],
                ['name' => 'Stephanie Permata', 'phone' => '0812-7001-0006', 'plate' => 'B 4402 EFG', 'model' => 'Honda HR-V 1.5 SE CVT 2022', 'km' => 19800],
                ['name' => 'Kevin Sanjaya Sukamuljo', 'phone' => '0812-7001-0007', 'plate' => 'B 6710 ZZZ', 'model' => 'Hyundai Ioniq 5 Signature Long Range 2022', 'km' => 22100],
                ['name' => 'Agus Pratama Nugraha', 'phone' => '0812-7001-0008', 'plate' => 'B 1823 ABC', 'model' => 'Toyota Raize 1.0T GR Sport CVT 2021', 'km' => 35000],
                ['name' => 'Ibu Maya Indah Sari', 'phone' => '0812-7001-0009', 'plate' => 'B 5591 NMO', 'model' => 'Honda Brio RS CVT Facelift 2021', 'km' => 41200],
                ['name' => 'Pak Anton Wijayakusuma', 'phone' => '0812-7001-0010', 'plate' => 'B 2049 JKL', 'model' => 'Suzuki Ertiga Hybrid GX AT 2022', 'km' => 26500],
                ['name' => 'Clarissa Putri Utami', 'phone' => '0812-7001-0011', 'plate' => 'B 7731 CDE', 'model' => 'Mazda CX-5 Kuro Edition 2021', 'km' => 38900],
                ['name' => 'Doni Setiawan Pratama', 'phone' => '0812-7001-0012', 'plate' => 'B 1156 DEF', 'model' => 'Toyota Calya 1.2 G AT 2020', 'km' => 54000],
                ['name' => 'Eko Sulistyo Hadi', 'phone' => '0812-7001-0013', 'plate' => 'B 8920 GHI', 'model' => 'Daihatsu Sigra 1.2 R DLX AT 2021', 'km' => 48200],
                ['name' => 'Rina Marlinawati', 'phone' => '0812-7001-0014', 'plate' => 'B 3324 JKL', 'model' => 'Honda City Hatchback RS CVT 2022', 'km' => 18400],
                ['name' => 'Didik Suryanto Wibowo', 'phone' => '0812-7001-0015', 'plate' => 'B 6615 MNO', 'model' => 'Nissan Serena Highway Star C27 2019', 'km' => 72100],
                ['name' => 'Willy Tanumihardja', 'phone' => '0812-7001-0016', 'plate' => 'B 4801 STU', 'model' => 'Wuling Almaz RS Pro 1.5T 2021', 'km' => 39400],
                ['name' => 'Ferry Handoko Susilo', 'phone' => '0812-7001-0017', 'plate' => 'B 9112 VWX', 'model' => 'Toyota Yaris GR Sport CVT 2022', 'km' => 21000],
                ['name' => 'Jessica Wongso Negoro', 'phone' => '0812-7001-0018', 'plate' => 'B 2378 YZA', 'model' => 'Suzuki XL7 Alpha AT 2021', 'km' => 44700],
                ['name' => 'Heru Prasetyo Utomo', 'phone' => '0812-7001-0019', 'plate' => 'B 5082 BCD', 'model' => 'Mitsubishi Xpander Ultimate CVT 2021', 'km' => 36800],
                ['name' => 'Dr. William Hartanto', 'phone' => '0812-7001-0020', 'plate' => 'B 7194 EFG', 'model' => 'BMW 320i Sport G20 LCI 2020', 'km' => 33500],
                ['name' => 'Ir. Michael Halim', 'phone' => '0812-7001-0021', 'plate' => 'B 8830 HIJ', 'model' => 'Mercedes-Benz C200 W205 Avantgarde 2019', 'km' => 58000],
                ['name' => 'Slamet Riyadi Subroto', 'phone' => '0812-7001-0022', 'plate' => 'B 1475 KLM', 'model' => 'Toyota Rush 1.5 S GR Sport AT 2021', 'km' => 32900],
                ['name' => 'Gunawan Wibisono', 'phone' => '0812-7001-0023', 'plate' => 'B 3962 NOP', 'model' => 'Daihatsu Terios R Custom AT 2020', 'km' => 49500],
                ['name' => 'Nadia Putri Amanda', 'phone' => '0812-7001-0024', 'plate' => 'B 6251 QRS', 'model' => 'Hyundai Creta Prime 1.5 IVT 2022', 'km' => 24600],
                ['name' => 'Yudi Pratama Kusuma', 'phone' => '0812-7001-0025', 'plate' => 'B 8540 TUV', 'model' => 'Kia Sonet Premiere iVT 2021', 'km' => 37100],
                ['name' => 'Budi Hermanto Wijaya', 'phone' => '0812-7001-0026', 'plate' => 'B 2933 WXY', 'model' => 'Subaru Forester 2.0i-S EyeSight 2022', 'km' => 16200],
                ['name' => 'Ratna Dewi Kusuma', 'phone' => '0812-7001-0027', 'plate' => 'B 4122 ZAB', 'model' => 'Toyota Voxy 2.0 AT Premium 2022', 'km' => 27400],
                ['name' => 'Alvin Susanto Halim', 'phone' => '0812-7001-0028', 'plate' => 'B 7411 CDE', 'model' => 'Honda Civic RS Sedan 1.5 Turbo 2022', 'km' => 15800],
                ['name' => 'Handoko Salim Pranoto', 'phone' => '0812-7001-0029', 'plate' => 'B 9600 FGH', 'model' => 'Isuzu Mu-X 1.9 4x4 AT 2022', 'km' => 29300],
                ['name' => 'Sari Indrawati Lukman', 'phone' => '0812-7001-0030', 'plate' => 'B 1388 IJK', 'model' => 'Toyota Corolla Cross Hybrid 2021', 'km' => 40500],
                ['name' => 'Johan Suryadi Tan', 'phone' => '0812-7001-0031', 'plate' => 'B 3577 LMN', 'model' => 'Lexus RX300 F-Sport AWD 2020', 'km' => 45200],
                ['name' => 'Hadi Purnomo Sidik', 'phone' => '0812-7001-0032', 'plate' => 'B 5866 OPQ', 'model' => 'Nissan Kicks e-POWER 2021', 'km' => 34100],
                ['name' => 'Veronica Tanuwidjaja', 'phone' => '0812-7001-0033', 'plate' => 'B 7055 RST', 'model' => 'Mazda 3 Hatchback 2.0 AT 2021', 'km' => 25900],
                ['name' => 'David Christian Salim', 'phone' => '0812-7001-0034', 'plate' => 'B 8244 UVW', 'model' => 'Toyota Hilux Double Cabin 2.4 V 2020', 'km' => 61000],
                ['name' => 'Farida Hasyim Assegaf', 'phone' => '0812-7001-0035', 'plate' => 'B 9433 XYZ', 'model' => 'Ford Ranger Wildtrak 2.0 Bi-Turbo 2022', 'km' => 33000],
            ];

            $customers = [];
            foreach ($customerVehicleCatalog as $cSpec) {
                $customers[] = Customer::updateOrCreate(
                    ['business_id' => $business->id, 'phone' => $cSpec['phone']],
                    [
                        'name' => $cSpec['name'],
                        'slug' => Str::slug($cSpec['name']),
                        'email' => Str::slug($cSpec['name']) . '@autoclient.test',
                        'vehicle_license_plate' => $cSpec['plate'],
                        'vehicle_model' => $cSpec['model'],
                        'vehicle_mileage' => $cSpec['km'],
                        'billing_address' => 'BSD City / Bintaro Sektor 9 Area, Tangerang Selatan',
                        'is_active' => true,
                    ]
                );
            }

            // 11. POS Register & Shifts
            $register = PosRegister::updateOrCreate(
                ['business_id' => $business->id, 'location_id' => $primaryLoc->id, 'code' => 'REG-WS-01'],
                [
                    'name' => 'Kasir Servis Utama & Penerimaan SPK (BSD)',
                    'is_active' => true,
                ]
            );

            $shift = PosShift::firstOrCreate(
                [
                    'business_id' => $business->id,
                    'pos_register_id' => $register->id,
                    'status' => PosShift::STATUS_CLOSED,
                ],
                [
                    'location_id' => $primaryLoc->id,
                    'user_id' => $userModels['cashier']->id,
                    'opened_at' => Carbon::now()->subHours(9),
                    'closed_at' => Carbon::now()->subHour(),
                    'opening_cash' => 300000,
                    'closing_cash_actual' => 300000 + 1200000,
                    'closing_cash_expected' => 300000 + 1200000,
                    'cash_difference' => 0,
                    'total_cash_sales' => 1200000,
                    'total_non_cash_sales' => 5400000,
                ]
            );

            // 12. SPK Work Orders (10 SPK Orders with Technician, Notes, Plate & Mileage)
            $sampleSpkOrders = [
                [
                    'order_number' => 'SPK-AC-20260101-001',
                    'cust_idx' => 0, // Robert Gunawan (Innova Zenix B 1024 SS)
                    'notes' => 'Servis berkala 15.000 km, ganti oli mesin Shell 5W-40, filter oli Denso, tune up injeksi & scanner.',
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'SRV-AC-001', 'qty' => 1], // Tune up
                        ['code' => 'SRV-AC-002', 'qty' => 1], // Ganti oli
                        ['code' => 'PRT-OIL-01', 'qty' => 1], // Shell Helix Ultra 4L
                        ['code' => 'PRT-FLT-01', 'qty' => 1], // Filter oli Innova
                    ],
                ],
                [
                    'order_number' => 'SPK-AC-20260101-002',
                    'cust_idx' => 1, // Fandi Ahmad (CR-V B 2841 PQR)
                    'notes' => 'Servis rem 4 roda berdecit, kuras minyak rem DOT 4, ganti kampas rem depan Bendix, balancing 4 roda.',
                    'payment_method' => PosOrderPayment::METHOD_QRIS,
                    'items' => [
                        ['code' => 'SRV-AC-004', 'qty' => 1], // Servis rem 4 roda
                        ['code' => 'PRT-BRK-04', 'qty' => 1], // Kampas rem Akebono CR-V
                        ['code' => 'PRT-OIL-07', 'qty' => 2], // Minyak rem Prestone 300ml
                        ['code' => 'SRV-AC-005', 'qty' => 1], // Spooring 3D
                    ],
                ],
                [
                    'order_number' => 'SPK-AC-20260101-003',
                    'cust_idx' => 2, // Ir. Hendra (Avanza B 1789 WXY)
                    'notes' => 'Kuras oli transmisi matic ATF changer, servis berkala, ganti busi iridium & filter udara.',
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'items' => [
                        ['code' => 'SRV-AC-003', 'qty' => 1], // Flushing matic
                        ['code' => 'PRT-OIL-05', 'qty' => 1], // Toyota WS ATF 4L
                        ['code' => 'PRT-SPK-01', 'qty' => 4], // 4x Busi NGK Iridium
                        ['code' => 'PRT-FLT-04', 'qty' => 1], // Filter udara Avanza
                    ],
                ],
                [
                    'order_number' => 'SPK-AC-20260101-004',
                    'cust_idx' => 5, // Stephanie (HR-V B 4402 EFG)
                    'notes' => 'Cuci AC evaporator no-bongkar, fogging disinfektan kabin, ganti filter AC cabin carbon antibakteri.',
                    'payment_method' => PosOrderPayment::METHOD_QRIS,
                    'items' => [
                        ['code' => 'SRV-AC-006', 'qty' => 1], // Cuci AC no-bongkar
                        ['code' => 'SRV-AC-014', 'qty' => 1], // Fogging kabin
                        ['code' => 'PRT-FLT-05', 'qty' => 1], // Filter AC kabin
                    ],
                ],
                [
                    'order_number' => 'SPK-AC-20260101-005',
                    'cust_idx' => 8, // Ibu Maya (Brio B 5591 NMO)
                    'notes' => 'Aki starter drop lemah saat pagi, cek sistem kelistrikan & ganti aki kering GS Astra MF baru.',
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'SRV-AC-018', 'qty' => 1], // Cek kelistrikan aki
                        ['code' => 'PRT-BAT-02', 'qty' => 1], // Aki GS Astra 40B19L
                    ],
                ],
                [
                    'order_number' => 'SPK-AC-20260101-006',
                    'cust_idx' => 4, // Darmawan (Fortuner B 3145 KLO)
                    'notes' => 'Gurah mesin carbon cleaner ruang bakar, kuras radiator coolant Prestone, ganti wiper Bosch.',
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'SRV-AC-008', 'qty' => 1], // Gurah mesin
                        ['code' => 'PRT-CLN-01', 'qty' => 1], // Carbon cleaner CRC
                        ['code' => 'SRV-AC-012', 'qty' => 1], // Kuras radiator
                        ['code' => 'PRT-CLT-01', 'qty' => 1], // Coolant Prestone 4L
                        ['code' => 'PRT-WIP-01', 'qty' => 1], // Wiper Bosch
                    ],
                ],
                [
                    'order_number' => 'SPK-AC-20260101-007',
                    'cust_idx' => 7, // Agus Pratama (Raize B 1823 ABC)
                    'notes' => 'Servis berkala ganti oli Mobil 1 0W-20 & filter oli Denso, general check up 45 titik.',
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'items' => [
                        ['code' => 'SRV-AC-002', 'qty' => 1], // Ganti oli
                        ['code' => 'PRT-OIL-02', 'qty' => 1], // Mobil 1 0W-20
                        ['code' => 'PRT-FLT-02', 'qty' => 1], // Filter oli Denso
                        ['code' => 'SRV-AC-020', 'qty' => 1], // Check up 45 titik
                    ],
                ],
                [
                    'order_number' => 'SPK-AC-20260101-008',
                    'cust_idx' => 10, // Clarissa (Mazda CX-5 B 7731 CDE)
                    'notes' => 'Headlamp menguning kusam, poles & nano burn coating lampu depan sepasang, uji emisi gas buang.',
                    'payment_method' => PosOrderPayment::METHOD_QRIS,
                    'items' => [
                        ['code' => 'SRV-AC-017', 'qty' => 1], // Poles headlamp
                        ['code' => 'SRV-AC-016', 'qty' => 1], // Uji emisi
                    ],
                ],
                [
                    'order_number' => 'SPK-AC-20260101-009',
                    'cust_idx' => 9, // Pak Anton (Ertiga B 2049 JKL)
                    'notes' => 'Kaki-kaki depan glodakan saat lewat jalan rusak, ganti link stabilizer depan & bushing arm.',
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'items' => [
                        ['code' => 'SRV-AC-023', 'qty' => 1], // Ganti link stabilizer
                        ['code' => 'PRT-LNK-01', 'qty' => 1], // Link stabilizer 555
                        ['code' => 'SRV-AC-005', 'qty' => 1], // Spooring 3D
                    ],
                ],
                [
                    'order_number' => 'SPK-AC-20260101-010',
                    'cust_idx' => 3, // Budi Hartono (Pajero B 9912 TUL)
                    'notes' => 'Servis besar 70.000 km, ganti oli mesin Castrol Magnatec, ganti kampas rem depan Bendix, detailing mesin.',
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'SRV-AC-001', 'qty' => 1], // Tune up
                        ['code' => 'PRT-OIL-03', 'qty' => 2], // 2x Castrol 4L = 8L diesel
                        ['code' => 'PRT-BRK-01', 'qty' => 1], // Bendix Innova/Pajero
                        ['code' => 'SRV-AC-015', 'qty' => 1], // Engine bay detailing
                    ],
                ],
            ];

            foreach ($sampleSpkOrders as $spkSpec) {
                $cObj = $customers[$spkSpec['cust_idx']];
                $subtotal = 0.0;
                $totalHpp = 0.0;
                $lineItemsData = [];

                foreach ($spkSpec['items'] as $itemSpec) {
                    $prod = $productModels[$itemSpec['code']];
                    $qty = (float) $itemSpec['qty'];
                    $price = (float) $prod->selling_price;
                    $hpp = (float) $prod->base_cost;
                    $lineTotal = $price * $qty;
                    $lineHpp = $hpp * $qty;

                    $subtotal += $lineTotal;
                    $totalHpp += $lineHpp;

                    $lineItemsData[] = [
                        'product_id' => $prod->id,
                        'product_name' => $prod->name,
                        'product_code' => $prod->code,
                        'unit_price' => $price,
                        'unit_cost_hpp' => $hpp,
                        'quantity' => $qty,
                        'subtotal' => $lineTotal,
                        'total_price' => $lineTotal,
                        'total_hpp' => $lineHpp,
                    ];
                }

                $taxAmount = 0.0;
                $totalAmount = $subtotal + $taxAmount;

                $order = PosOrder::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'order_number' => $spkSpec['order_number'],
                    ],
                    [
                        'location_id' => $primaryLoc->id,
                        'pos_shift_id' => $shift->id,
                        'pos_register_id' => $register->id,
                        'user_id' => $userModels['cashier']->id,
                        'customer_id' => $cObj->id,
                        'order_date' => Carbon::now()->toDateString(),
                        'status' => PosOrder::STATUS_COMPLETED,
                        'order_type' => 'takeaway',
                        'sales_channel' => 'pos',
                        'customer_name_guest' => $cObj->name,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'paid_amount' => $totalAmount,
                        'change_amount' => 0,
                        'total_hpp_cost' => $totalHpp,
                        'total_gross_profit' => $totalAmount - $totalHpp,
                        'notes' => $spkSpec['notes'],
                        // Automotive SPK Specific Fields:
                        'vehicle_license_plate' => $cObj->vehicle_license_plate,
                        'vehicle_model' => $cObj->vehicle_model,
                        'vehicle_mileage' => $cObj->vehicle_mileage,
                        'technician_id' => $userModels['mechanic']->id,
                        'service_notes' => $spkSpec['notes'],
                    ]
                );

                if ($order->wasRecentlyCreated) {
                    foreach ($lineItemsData as $line) {
                        PosOrderItem::create(array_merge($line, ['pos_order_id' => $order->id]));

                        // If physical goods (spareparts), decrement stock
                        $pModel = $productModels[$line['product_code']];
                        if ($pModel->type === Product::TYPE_GOODS) {
                            $inv = InventoryStock::where('business_id', $business->id)
                                ->where('location_id', $primaryLoc->id)
                                ->where('product_id', $pModel->id)
                                ->first();

                            if ($inv) {
                                $inv->decrement('quantity', $line['quantity']);
                                StockMovement::create([
                                    'business_id' => $business->id,
                                    'location_id' => $primaryLoc->id,
                                    'product_id' => $pModel->id,
                                    'material_id' => null,
                                    'movement_type' => StockMovement::TYPE_POS_SALE,
                                    'quantity_change' => -$line['quantity'],
                                    'balance_after' => $inv->quantity,
                                    'unit_cost' => $line['unit_cost_hpp'],
                                    'total_cost' => $line['total_hpp'],
                                    'reference_number' => $order->order_number,
                                    'notes' => "Pemakaian Suku Cadang SPK #{$order->order_number} ({$cObj->vehicle_license_plate})",
                                    'created_by' => $userModels['cashier']->id,
                                ]);
                            }
                        }
                    }

                    PosOrderPayment::create([
                        'pos_order_id' => $order->id,
                        'payment_method' => $spkSpec['payment_method'],
                        'amount' => $totalAmount,
                        'reference_number' => 'PAY-' . $order->order_number,
                        'status' => 'settled',
                    ]);

                    // Automatically Record Balanced SAK EMKM Double-Entry Journal
                    $this->journalService->recordPosSaleJournal($order);
                }
            }

            // 13. Service Booking Reservations (CommerceReservation)
            $reservationSpecs = [
                [
                    'code' => 'RSV-AC-2026-001',
                    'cust_idx' => 19, // Dr. William (BMW 320i B 7194 EFG)
                    'date' => Carbon::today()->addDay()->toDateString(),
                    'slot' => '09:00',
                    'duration' => 120,
                    'dp' => 200000,
                    'notes' => 'Booking Servis Berkala & Ganti Oli Mesin BMW 320i Sport',
                ],
                [
                    'code' => 'RSV-AC-2026-002',
                    'cust_idx' => 20, // Ir. Michael (Mercedes C200 B 8830 HIJ)
                    'date' => Carbon::today()->addDay()->toDateString(),
                    'slot' => '11:00',
                    'duration' => 90,
                    'dp' => 200000,
                    'notes' => 'Booking Spooring 3D, Balancing & Pengecekan Sistem AC Mercy',
                ],
                [
                    'code' => 'RSV-AC-2026-003',
                    'cust_idx' => 25, // Budi Hermanto (Subaru Forester B 2933 WXY)
                    'date' => Carbon::today()->addDays(2)->toDateString(),
                    'slot' => '13:30',
                    'duration' => 150,
                    'dp' => 250000,
                    'notes' => 'Booking Kuras Oli Transmisi CVT & Flushing Radiator Coolant',
                ],
                [
                    'code' => 'RSV-AC-2026-004',
                    'cust_idx' => 30, // Johan Suryadi (Lexus RX300 B 3577 LMN)
                    'date' => Carbon::today()->addDays(3)->toDateString(),
                    'slot' => '10:00',
                    'duration' => 180,
                    'dp' => 300000,
                    'notes' => 'Booking General Check-Up 45 Titik & Cuci AC Tanpa Bongkar Lexus',
                ],
            ];

            foreach ($reservationSpecs as $rSpec) {
                $cModel = $customers[$rSpec['cust_idx']];
                CommerceReservation::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'reservation_code' => $rSpec['code'],
                    ],
                    [
                        'location_id' => $primaryLoc->id,
                        'customer_name' => $cModel->name,
                        'customer_phone' => $cModel->phone,
                        'customer_email' => $cModel->email,
                        'reservation_date' => $rSpec['date'],
                        'time_slot' => $rSpec['slot'],
                        'guest_count' => 1,
                        'vehicle_plate' => $cModel->vehicle_license_plate,
                        'vehicle_model' => $cModel->vehicle_model,
                        'service_duration_minutes' => $rSpec['duration'],
                        'staff_id' => $userModels['mechanic']->id,
                        'dp_amount' => $rSpec['dp'],
                        'dp_payment_method' => 'qris',
                        'dp_paid_at' => now(),
                        'payment_status' => 'paid',
                        'status' => CommerceReservation::STATUS_CONFIRMED,
                        'notes' => $rSpec['notes'],
                    ]
                );
            }
        });

        $this->command?->info('✓ Sukses seeding COOCA AutoCare (Automotive Workshop)!');
    }
}
