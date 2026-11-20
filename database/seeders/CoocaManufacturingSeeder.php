<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Template\BusinessTemplateService;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\ChartOfAccount;
use App\Models\CostModel;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LaborRate;
use App\Models\Location;
use App\Models\Machine;
use App\Models\Material;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
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

final class CoocaManufacturingSeeder extends Seeder
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
        $this->command?->info('Seeding Manufacturing: COOCA Manufacturing Indonesia...');

        DB::transaction(function () {
            // 1. Business Creation
            $business = Business::updateOrCreate(
                ['slug' => 'cooca-manufacturing-indonesia'],
                [
                    'name' => 'COOCA Manufacturing Indonesia',
                    'currency' => 'IDR',
                    'currency_precision' => 0,
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                    'phone' => '0811-2233-4403',
                    'email' => 'kontak@coocamfg.id',
                    'address' => 'Kawasan Industri GIIC Blok C-15, Cikarang Pusat, Bekasi, Jawa Barat',
                    'tax_identification_number' => '01.234.567.8-013.000',
                    'bank_name' => 'Bank Central Asia (BCA)',
                    'bank_account_number' => '8800112203',
                    'bank_account_holder' => 'PT Cooca Manufaktur Industri',
                    'pos_enable_tax' => true,
                    'pos_tax_percent' => 11,
                    'template_code' => 'mfg_garment',
                    'industry_category' => 'manufacturing',
                    'is_demo' => true,
                    'is_active' => true,
                ]
            );

            Context::setBusiness($business);
            $this->journalService->ensureStandardAccounts($business);

            // 2. Business Type Template
            $template = BusinessTypeTemplate::where('code', 'mfg_garment')->first();
            if ($template) {
                $this->templateService->apply($business, $template);
            }

            // 3. User Accounts & Roles
            $roles = Role::whereNull('business_id')->get()->keyBy('slug');

            $demoUsers = [
                ['key' => 'owner', 'name' => 'Ir. Hartono Wibowo (Owner & Factory Director)', 'email' => 'owner@coocamfg.id', 'role' => 'owner'],
                ['key' => 'manager', 'name' => 'Agus Priyanto, ST (Plant & Production Manager)', 'email' => 'manager@coocamfg.id', 'role' => 'manager'],
                ['key' => 'warehouse', 'name' => 'Slamet Riyadi (Warehouse & Logistics Lead)', 'email' => 'warehouse@coocamfg.id', 'role' => 'warehouse'],
                ['key' => 'finance', 'name' => 'Siti Nur Aini, SE (Cost Accounting & Finance)', 'email' => 'finance@coocamfg.id', 'role' => 'finance'],
                ['key' => 'production', 'name' => 'Joko Susilo (Production Line Supervisor)', 'email' => 'production@coocamfg.id', 'role' => 'staff'],
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

            // Global Testing Users
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

            // 4. Locations (2 Sites: Factory and Distribution Warehouse)
            $locationSpecs = [
                [
                    'name' => 'Pabrik Cikarang (Main Factory & Workshop)',
                    'slug' => 'pabrik-cikarang',
                    'code' => 'LOC-CKR',
                    'type' => 'factory',
                    'is_primary' => true,
                    'address' => 'Kawasan Industri GIIC Blok C-15, Cikarang Pusat, Bekasi',
                    'phone' => '021-89900001',
                ],
                [
                    'name' => 'Gudang Distribusi Tangerang (Hub Logistik)',
                    'slug' => 'gudang-tangerang',
                    'code' => 'LOC-TNG',
                    'type' => 'warehouse',
                    'is_primary' => false,
                    'address' => 'Kawasan Industri Manis, Jl. Manis Raya No. 12, Tangerang',
                    'phone' => '021-59100002',
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

            $factoryLoc = $locations['pabrik-cikarang'];
            $warehouseLoc = $locations['gudang-tangerang'];

            // 5. Units Lookup
            $units = Unit::all()->keyBy('code');

            // 6. Machines & Overhead Rates
            $machineSpecs = [
                [
                    'code' => 'MCH-SEW-01',
                    'name' => 'Mesin Jahit High-Speed Juki DDL-9000C',
                    'purchase_price' => 18500000,
                    'maintenance_cost_per_hour' => 7500,
                    'electricity_cost_per_hour' => 4500,
                    'useful_life_hours' => 20000,
                    'notes' => 'Mesin jahit otomatis single needle dengan pemotong benang otomatis.',
                ],
                [
                    'code' => 'MCH-OBR-01',
                    'name' => 'Mesin Obras High-Speed Pegasus M900',
                    'purchase_price' => 14000000,
                    'maintenance_cost_per_hour' => 6000,
                    'electricity_cost_per_hour' => 3800,
                    'useful_life_hours' => 20000,
                    'notes' => 'Mesin obras 4 benang untuk jahitan tepi kaos dan kemeja.',
                ],
                [
                    'code' => 'MCH-CUT-01',
                    'name' => 'Mesin Potong Kain Otomatis Eastman 10 Inch',
                    'purchase_price' => 32000000,
                    'maintenance_cost_per_hour' => 12000,
                    'electricity_cost_per_hour' => 8500,
                    'useful_life_hours' => 25000,
                    'notes' => 'Mesin cutting vertikal untuk menumpuk dan memotong ratusan layer kain presisi.',
                ],
                [
                    'code' => 'MCH-PRS-01',
                    'name' => 'Mesin Heat Press Pneumatic 40x60cm',
                    'purchase_price' => 22000000,
                    'maintenance_cost_per_hour' => 5000,
                    'electricity_cost_per_hour' => 11000,
                    'useful_life_hours' => 18000,
                    'notes' => 'Mesin press sablon, transfer film DTF dan interlining.',
                ],
                [
                    'code' => 'MCH-BTN-01',
                    'name' => 'Mesin Pasang Kancing & Lubang Kancing Brother HE-800B',
                    'purchase_price' => 28000000,
                    'maintenance_cost_per_hour' => 8000,
                    'electricity_cost_per_hour' => 5000,
                    'useful_life_hours' => 20000,
                    'notes' => 'Mesin elektronik pembuatan lubang kancing kemeja dan jaket.',
                ],
            ];

            $machines = [];
            foreach ($machineSpecs as $mchData) {
                $machines[] = Machine::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $mchData['code']],
                    [
                        'name' => $mchData['name'],
                        'slug' => Str::slug($mchData['name']),
                        'purchase_price' => $mchData['purchase_price'],
                        'residual_value' => 2000000,
                        'useful_life_hours' => $mchData['useful_life_hours'],
                        'maintenance_cost_per_hour' => $mchData['maintenance_cost_per_hour'],
                        'electricity_cost_per_hour' => $mchData['electricity_cost_per_hour'],
                        'notes' => $mchData['notes'],
                    ]
                );
            }

            // 7. Labor Rates (Work Centers)
            $laborSpecs = [
                ['name' => 'Operator Pola & Potong (Cutting Line)', 'basis' => 'hourly', 'rate' => 35000, 'notes' => 'Tenaga ahli pembuat pola karton dan cutting kain bertingkat.'],
                ['name' => 'Operator Jahit & Obras (Sewing Line)', 'basis' => 'hourly', 'rate' => 32000, 'notes' => 'Operator mesin jahit dan obras garment berpengalaman.'],
                ['name' => 'Operator Finishing & QC Inspection', 'basis' => 'hourly', 'rate' => 28000, 'notes' => 'Pemeriksaan jahitan cacat, buang benang, dan pengukuran toleransi.'],
                ['name' => 'Operator Setrika Uap & Packaging', 'basis' => 'hourly', 'rate' => 25000, 'notes' => 'Steam pressing uap, pasang hangtag, dan packing polybag.'],
            ];

            $laborRates = [];
            foreach ($laborSpecs as $lSpec) {
                $laborRates[] = LaborRate::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => Str::slug($lSpec['name'])],
                    [
                        'name' => $lSpec['name'],
                        'basis' => $lSpec['basis'],
                        'rate_amount' => $lSpec['rate'],
                        'working_days_per_month' => 22,
                        'working_hours_per_day' => 8,
                        'utilization_rate' => 85,
                        'notes' => $lSpec['notes'],
                    ]
                );
            }

            // 8. Raw Material Suppliers (12 Suppliers)
            $supplierSpecs = [
                ['name' => 'PT Sri Rejeki Isman Tbk (Sritex)', 'contact' => 'Bambang Irawan', 'phone' => '0271-593188', 'email' => 'sales@sritex.test', 'address' => 'Jl. KH Samanhudi 88, Sukoharjo, Solo'],
                ['name' => 'PT Dan Liris Textile Industry', 'contact' => 'Hendro Susanto', 'phone' => '0271-714400', 'email' => 'marketing@danliris.test', 'address' => 'Banaran, Grogol, Sukoharjo'],
                ['name' => 'PT Ateja Multi Industri', 'contact' => 'Agus Setiawan', 'phone' => '022-6866322', 'email' => 'commercial@ateja.test', 'address' => 'Jl. Raya Batujajar Km 2.8, Bandung Barat'],
                ['name' => 'PT YKK Zipper Indonesia', 'contact' => 'Rina Marlina', 'phone' => '021-8710645', 'email' => 'order@ykk.test', 'address' => 'Jl. Raya Jakarta-Bogor Km 29, Cimanggis, Depok'],
                ['name' => 'PT Coats Rejo Indonesia (Thread)', 'contact' => 'Didik Wahyudi', 'phone' => '021-8752000', 'email' => 'sales@coats.test', 'address' => 'Jl. Raya Tajur No. 168, Bogor'],
                ['name' => 'PT Lucky Print Abadi', 'contact' => 'Willy Tan', 'phone' => '021-5902000', 'email' => 'b2b@luckyprint.test', 'address' => 'Jl. Raya Serang Km 14, Cikupa, Tangerang'],
                ['name' => 'PT Polyfin Canggih', 'contact' => 'Ferry Handoko', 'phone' => '022-7798888', 'email' => 'sales@polyfin.test', 'address' => 'Jl. Raya Rancaekek Km 19, Sumedang'],
                ['name' => 'CV Kancing Jaya Abadi', 'contact' => 'Hendra Wijaya', 'phone' => '021-6902233', 'email' => 'order@kancingjaya.test', 'address' => 'Pusat Niaga Pasar Pagi Mangga Dua, Jakarta'],
                ['name' => 'PT Woven Label Indonesia', 'contact' => 'Jessica Pratama', 'phone' => '021-55952211', 'email' => 'sales@wovenlabel.test', 'address' => 'Kawasan Pergudangan Pantai Indah Dadap, Tangerang'],
                ['name' => 'PT Plasindo Plastik Kemasan', 'contact' => 'Johan Suryadi', 'phone' => '021-5558899', 'email' => 'polybag@plasindo.test', 'address' => 'Jl. Daan Mogot Km 19, Batuceper, Tangerang'],
                ['name' => 'CV Sentosa Interlining & Furing', 'contact' => 'Hadi Purnomo', 'phone' => '021-6258877', 'email' => 'furing@sentosainterlining.test', 'address' => 'Pasar Tanah Abang Blok F, Jakarta Pusat'],
                ['name' => 'PT Duniatex Group', 'contact' => 'Heru Prasetyo', 'phone' => '0271-825555', 'email' => 'order@duniatex.test', 'address' => 'Palur, Karanganyar, Jawa Tengah'],
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
                        'notes' => 'Termin Pembayaran: Net 30 Hari via Transfer Bank',
                    ]
                );
            }

            // 9. 34 Raw Materials (Fabrics, Trims, Accessories, Packaging)
            $rawMaterialsCatalog = [
                // Fabrics (12 SKUs)
                ['code' => 'RAW-FAB-01', 'name' => 'Kain Katun Combed 30s Hitam Reaktif (kg)', 'unit' => 'kg', 'price' => 118000, 'sup_idx' => 0, 'stock' => 500],
                ['code' => 'RAW-FAB-02', 'name' => 'Kain Katun Combed 30s Putih Bersih (kg)', 'unit' => 'kg', 'price' => 114000, 'sup_idx' => 0, 'stock' => 450],
                ['code' => 'RAW-FAB-03', 'name' => 'Kain Katun Combed 24s Heavyweight Navy (kg)', 'unit' => 'kg', 'price' => 122000, 'sup_idx' => 0, 'stock' => 350],
                ['code' => 'RAW-FAB-04', 'name' => 'Kain Cotton Fleece 280gsm Hitam (kg)', 'unit' => 'kg', 'price' => 135000, 'sup_idx' => 1, 'stock' => 300],
                ['code' => 'RAW-FAB-05', 'name' => 'Kain Cotton Fleece 280gsm Abu Misty (kg)', 'unit' => 'kg', 'price' => 138000, 'sup_idx' => 1, 'stock' => 280],
                ['code' => 'RAW-FAB-06', 'name' => 'Kain Drill USA Halus Khaki (m)', 'unit' => 'm', 'price' => 38000, 'sup_idx' => 2, 'stock' => 600],
                ['code' => 'RAW-FAB-07', 'name' => 'Kain Drill USA Halus Hitam (m)', 'unit' => 'm', 'price' => 38000, 'sup_idx' => 2, 'stock' => 500],
                ['code' => 'RAW-FAB-08', 'name' => 'Kain Oxford Cotton Putih Kemeja (m)', 'unit' => 'm', 'price' => 42000, 'sup_idx' => 5, 'stock' => 400],
                ['code' => 'RAW-FAB-09', 'name' => 'Kain Flannel Tartan Kotak Casual (m)', 'unit' => 'm', 'price' => 48000, 'sup_idx' => 5, 'stock' => 350],
                ['code' => 'RAW-FAB-10', 'name' => 'Kain Taslan Milky Waterproof Hitam (m)', 'unit' => 'm', 'price' => 32000, 'sup_idx' => 6, 'stock' => 450],
                ['code' => 'RAW-FAB-11', 'name' => 'Kain Lacoste Pique Cotton CVC (kg)', 'unit' => 'kg', 'price' => 128000, 'sup_idx' => 0, 'stock' => 250],
                ['code' => 'RAW-FAB-12', 'name' => 'Kain Canvas Tebal 12oz Natural (m)', 'unit' => 'm', 'price' => 45000, 'sup_idx' => 2, 'stock' => 300],

                // Threads & Rib (5 SKUs)
                ['code' => 'RAW-TRD-01', 'name' => 'Benang Jahit Astra Coats Spun Polyester 40/2 Hitam', 'unit' => 'pcs', 'price' => 18000, 'sup_idx' => 4, 'stock' => 200],
                ['code' => 'RAW-TRD-02', 'name' => 'Benang Jahit Astra Coats Spun Polyester 40/2 Putih', 'unit' => 'pcs', 'price' => 18000, 'sup_idx' => 4, 'stock' => 180],
                ['code' => 'RAW-TRD-03', 'name' => 'Benang Obras Polyester 3 Warna Roll Besar', 'unit' => 'pcs', 'price' => 24000, 'sup_idx' => 4, 'stock' => 150],
                ['code' => 'RAW-RIB-01', 'name' => 'Kain Rib Leher Katun Combed 30s Hitam (kg)', 'unit' => 'kg', 'price' => 125000, 'sup_idx' => 0, 'stock' => 80],
                ['code' => 'RAW-RIB-02', 'name' => 'Kain Rib Leher Katun Combed 30s Putih (kg)', 'unit' => 'kg', 'price' => 122000, 'sup_idx' => 0, 'stock' => 70],

                // Zippers & Buttons (7 SKUs)
                ['code' => 'RAW-ZIP-01', 'name' => 'Ritsleting YKK Metal Zipper No. 5 65cm (pcs)', 'unit' => 'pcs', 'price' => 14000, 'sup_idx' => 3, 'stock' => 600],
                ['code' => 'RAW-ZIP-02', 'name' => 'Ritsleting YKK Coil Nylon 20cm Celana (pcs)', 'unit' => 'pcs', 'price' => 4500, 'sup_idx' => 3, 'stock' => 800],
                ['code' => 'RAW-ZIP-03', 'name' => 'Ritsleting YKK Vislon Jaket 60cm (pcs)', 'unit' => 'pcs', 'price' => 9500, 'sup_idx' => 3, 'stock' => 500],
                ['code' => 'RAW-BTN-01', 'name' => 'Kancing Poliester 18L Lubang 4 Kemeja (gross/144pcs)', 'unit' => 'box', 'price' => 22000, 'sup_idx' => 7, 'stock' => 100],
                ['code' => 'RAW-BTN-02', 'name' => 'Kancing Logam Snap Button Jacket (gross/144pcs)', 'unit' => 'box', 'price' => 65000, 'sup_idx' => 7, 'stock' => 80],
                ['code' => 'RAW-BTN-03', 'name' => 'Kancing Celana Logam Jeans 24L (gross/144pcs)', 'unit' => 'box', 'price' => 48000, 'sup_idx' => 7, 'stock' => 90],
                ['code' => 'RAW-ELT-01', 'name' => 'Karet Elastis 3.5cm Pinggang Celana (roll/30m)', 'unit' => 'roll', 'price' => 42000, 'sup_idx' => 7, 'stock' => 60],

                // Lining & Interlining (4 SKUs)
                ['code' => 'RAW-LIN-01', 'name' => 'Kain Furing Asahi Hitam Halus (m)', 'unit' => 'm', 'price' => 12000, 'sup_idx' => 10, 'stock' => 500],
                ['code' => 'RAW-INT-01', 'name' => 'Interlining Krah Kemeja Vliselin M32 Lem (m)', 'unit' => 'm', 'price' => 16000, 'sup_idx' => 10, 'stock' => 350],
                ['code' => 'RAW-INT-02', 'name' => 'Pita Tali Katun Hoodie Bertali 120cm (pasang)', 'unit' => 'pcs', 'price' => 3500, 'sup_idx' => 7, 'stock' => 600],
                ['code' => 'RAW-INT-03', 'name' => 'Stopper Tali Jaket Logam Metalik (pcs)', 'unit' => 'pcs', 'price' => 1800, 'sup_idx' => 7, 'stock' => 800],

                // Labels & Packaging (6 SKUs)
                ['code' => 'RAW-LBL-01', 'name' => 'Label Woven Damask Brand Utama COOCA (1000pcs)', 'unit' => 'box', 'price' => 350000, 'sup_idx' => 8, 'stock' => 30],
                ['code' => 'RAW-LBL-02', 'name' => 'Label Satin Care Instruction & Size (1000pcs)', 'unit' => 'box', 'price' => 180000, 'sup_idx' => 8, 'stock' => 35],
                ['code' => 'RAW-LBL-03', 'name' => 'Hangtag Ivory 350gsm Doff Laminasi (1000pcs)', 'unit' => 'box', 'price' => 280000, 'sup_idx' => 8, 'stock' => 40],
                ['code' => 'RAW-PKG-01', 'name' => 'Polybag Sablon Logo Zipper Lock 30x40 (pack/100pcs)', 'unit' => 'pack', 'price' => 65000, 'sup_idx' => 9, 'stock' => 150],
                ['code' => 'RAW-PKG-02', 'name' => 'Karton Box Master Pengiriman Garment 60x40x40cm', 'unit' => 'pcs', 'price' => 18000, 'sup_idx' => 9, 'stock' => 200],
                ['code' => 'RAW-PKG-03', 'name' => 'Silica Gel Desiccant Sachet 2g (pack/500pcs)', 'unit' => 'pack', 'price' => 45000, 'sup_idx' => 9, 'stock' => 50],
            ];

            $materialModels = [];
            foreach ($rawMaterialsCatalog as $mSpec) {
                $unitObj = $units[$mSpec['unit']] ?? $units['pcs'];
                $supObj = $suppliers[$mSpec['sup_idx']] ?? $suppliers[0];

                $mat = Material::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $mSpec['code']],
                    [
                        'name' => $mSpec['name'],
                        'slug' => Str::slug($mSpec['name']),
                        'unit_id' => $unitObj->id,
                        'supplier_id' => $supObj->id,
                        'description' => "Material baku manufaktur: {$mSpec['name']}",
                    ]
                );

                $mat->prices()->updateOrCreate(
                    ['effective_date' => now()->toDateString()],
                    [
                        'business_id' => $business->id,
                        'supplier_id' => $supObj->id,
                        'purchase_unit_id' => $unitObj->id,
                        'purchase_price' => $mSpec['price'],
                        'yield_percentage' => 100,
                        'waste_percentage' => 0,
                    ]
                );

                $materialModels[$mSpec['code']] = $mat;

                // Inventory Stock in Factory
                InventoryStock::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'location_id' => $factoryLoc->id,
                        'material_id' => $mat->id,
                    ],
                    [
                        'product_id' => null,
                        'quantity' => $mSpec['stock'],
                        'reserved_quantity' => 0,
                        'last_cost' => $mSpec['price'],
                        'avg_purchase_cost' => $mSpec['price'],
                    ]
                );

                StockMovement::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'material_id' => $mat->id,
                        'location_id' => $factoryLoc->id,
                        'reference_number' => "INIT-RAW-{$factoryLoc->code}-{$mat->code}",
                    ],
                    [
                        'product_id' => null,
                        'movement_type' => StockMovement::TYPE_INITIAL,
                        'quantity_change' => $mSpec['stock'],
                        'balance_after' => $mSpec['stock'],
                        'unit_cost' => $mSpec['price'],
                        'total_cost' => $mSpec['stock'] * $mSpec['price'],
                        'notes' => "Saldo Awal Gudang Bahan Baku Pabrik {$factoryLoc->name}",
                        'created_by' => $userModels['owner']->id,
                    ]
                );
            }

            // 10. Product Categories for Finished Goods
            $fgCategories = [
                'Kaos & T-Shirt Garment' => 'Produksi kaos polos, kaos sablon, dan raglan katun combed berkualitas tinggi.',
                'Hoodie & Outerwear' => 'Jaket hoodie fleece, zip-up jacket, dan sweatshirt tebal.',
                'Kemeja & Flannel' => 'Kemeja formal oxford, casual flannel, dan kemeja lapangan PDL.',
                'Celana & Bottomwear' => 'Celana chino stretch, celana cargo multi saku, dan celana drill.',
                'Jaket & Rompi Spesial' => 'Jaket coach taslan, jaket parka, dan rompi safety proyek.',
            ];

            $catModels = [];
            foreach ($fgCategories as $cName => $cDesc) {
                $catModels[$cName] = ProductCategory::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => Str::slug($cName)],
                    ['name' => $cName, 'description' => $cDesc]
                );
            }

            // 11. Finished Goods Products (18 Finished Goods with Production BOM)
            $finishedGoodsCatalog = [
                // T-Shirts (4 SKUs)
                [
                    'code' => 'MFG-TSH-01',
                    'name' => 'Kaos Polos Combed 30s Hitam Basic',
                    'cat' => 'Kaos & T-Shirt Garment',
                    'sell' => 55000,
                    'labor_cost' => 6500,
                    'machine_cost' => 2500,
                    'bom' => [
                        ['mat' => 'RAW-FAB-01', 'qty' => 0.22, 'unit' => 'kg', 'waste' => 5], // 220g combed + 5% scrap
                        ['mat' => 'RAW-RIB-01', 'qty' => 0.03, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'RAW-TRD-01', 'qty' => 0.02, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-TSH-02',
                    'name' => 'Kaos Polos Combed 30s Putih Basic',
                    'cat' => 'Kaos & T-Shirt Garment',
                    'sell' => 55000,
                    'labor_cost' => 6500,
                    'machine_cost' => 2500,
                    'bom' => [
                        ['mat' => 'RAW-FAB-02', 'qty' => 0.22, 'unit' => 'kg', 'waste' => 5],
                        ['mat' => 'RAW-RIB-02', 'qty' => 0.03, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'RAW-TRD-02', 'qty' => 0.02, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-TSH-03',
                    'name' => 'Kaos Polos Combed 24s Heavyweight Navy',
                    'cat' => 'Kaos & T-Shirt Garment',
                    'sell' => 68000,
                    'labor_cost' => 7000,
                    'machine_cost' => 2800,
                    'bom' => [
                        ['mat' => 'RAW-FAB-03', 'qty' => 0.28, 'unit' => 'kg', 'waste' => 5],
                        ['mat' => 'RAW-RIB-01', 'qty' => 0.03, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'RAW-TRD-01', 'qty' => 0.02, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-TSH-04',
                    'name' => 'Polo Shirt Lacoste CVC Premium Kerah',
                    'cat' => 'Kaos & T-Shirt Garment',
                    'sell' => 95000,
                    'labor_cost' => 12000,
                    'machine_cost' => 4500,
                    'bom' => [
                        ['mat' => 'RAW-FAB-11', 'qty' => 0.32, 'unit' => 'kg', 'waste' => 5],
                        ['mat' => 'RAW-BTN-01', 'qty' => 0.02, 'unit' => 'box', 'waste' => 0], // 3 kancing
                        ['mat' => 'RAW-TRD-01', 'qty' => 0.03, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],

                // Hoodies (4 SKUs)
                [
                    'code' => 'MFG-HOD-01',
                    'name' => 'Hoodie Jumper Pullover Cotton Fleece Hitam',
                    'cat' => 'Hoodie & Outerwear',
                    'sell' => 165000,
                    'labor_cost' => 18000,
                    'machine_cost' => 7000,
                    'bom' => [
                        ['mat' => 'RAW-FAB-04', 'qty' => 0.65, 'unit' => 'kg', 'waste' => 8], // 650g fleece + 8% pattern waste
                        ['mat' => 'RAW-INT-02', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                        ['mat' => 'RAW-TRD-01', 'qty' => 0.05, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-HOD-02',
                    'name' => 'Hoodie Zipper Metal Fleece Abu Misty',
                    'cat' => 'Hoodie & Outerwear',
                    'sell' => 185000,
                    'labor_cost' => 22000,
                    'machine_cost' => 8500,
                    'bom' => [
                        ['mat' => 'RAW-FAB-05', 'qty' => 0.70, 'unit' => 'kg', 'waste' => 8],
                        ['mat' => 'RAW-ZIP-01', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0], // YKK metal
                        ['mat' => 'RAW-INT-02', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-HOD-03',
                    'name' => 'Crewneck Sweatshirt Fleece Basic Hitam',
                    'cat' => 'Hoodie & Outerwear',
                    'sell' => 135000,
                    'labor_cost' => 14000,
                    'machine_cost' => 5500,
                    'bom' => [
                        ['mat' => 'RAW-FAB-04', 'qty' => 0.55, 'unit' => 'kg', 'waste' => 6],
                        ['mat' => 'RAW-RIB-01', 'qty' => 0.08, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-HOD-04',
                    'name' => 'Jaket Coach Taslan Waterproof Furing Asahi',
                    'cat' => 'Hoodie & Outerwear',
                    'sell' => 175000,
                    'labor_cost' => 25000,
                    'machine_cost' => 9000,
                    'bom' => [
                        ['mat' => 'RAW-FAB-10', 'qty' => 1.8, 'unit' => 'm', 'waste' => 5],
                        ['mat' => 'RAW-LIN-01', 'qty' => 1.6, 'unit' => 'm', 'waste' => 5],
                        ['mat' => 'RAW-BTN-02', 'qty' => 0.05, 'unit' => 'box', 'waste' => 0], // 6 snap buttons
                    ],
                ],

                // Shirts (4 SKUs)
                [
                    'code' => 'MFG-SHT-01',
                    'name' => 'Kemeja Oxford Formal Slim Fit Lengan Panjang Putih',
                    'cat' => 'Kemeja & Flannel',
                    'sell' => 145000,
                    'labor_cost' => 24000,
                    'machine_cost' => 8000,
                    'bom' => [
                        ['mat' => 'RAW-FAB-08', 'qty' => 1.6, 'unit' => 'm', 'waste' => 6],
                        ['mat' => 'RAW-INT-01', 'qty' => 0.2, 'unit' => 'm', 'waste' => 0],
                        ['mat' => 'RAW-BTN-01', 'qty' => 0.06, 'unit' => 'box', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-SHT-02',
                    'name' => 'Kemeja Flannel Tartan Casual Lengan Panjang',
                    'cat' => 'Kemeja & Flannel',
                    'sell' => 155000,
                    'labor_cost' => 24000,
                    'machine_cost' => 8000,
                    'bom' => [
                        ['mat' => 'RAW-FAB-09', 'qty' => 1.7, 'unit' => 'm', 'waste' => 7],
                        ['mat' => 'RAW-BTN-01', 'qty' => 0.06, 'unit' => 'box', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-SHT-03',
                    'name' => 'Kemeja PDL Lapangan Tactical Multi Saku',
                    'cat' => 'Kemeja & Flannel',
                    'sell' => 165000,
                    'labor_cost' => 28000,
                    'machine_cost' => 9500,
                    'bom' => [
                        ['mat' => 'RAW-FAB-07', 'qty' => 2.0, 'unit' => 'm', 'waste' => 8],
                        ['mat' => 'RAW-BTN-01', 'qty' => 0.08, 'unit' => 'box', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-SHT-04',
                    'name' => 'Kemeja Oxford Casual Lengan Pendek Hitam',
                    'cat' => 'Kemeja & Flannel',
                    'sell' => 125000,
                    'labor_cost' => 20000,
                    'machine_cost' => 7000,
                    'bom' => [
                        ['mat' => 'RAW-FAB-08', 'qty' => 1.3, 'unit' => 'm', 'waste' => 5],
                        ['mat' => 'RAW-BTN-01', 'qty' => 0.05, 'unit' => 'box', 'waste' => 0],
                    ],
                ],

                // Pants (3 SKUs)
                [
                    'code' => 'MFG-PNT-01',
                    'name' => 'Celana Chino Drill Slim Fit Khaki',
                    'cat' => 'Celana & Bottomwear',
                    'sell' => 155000,
                    'labor_cost' => 25000,
                    'machine_cost' => 8500,
                    'bom' => [
                        ['mat' => 'RAW-FAB-06', 'qty' => 1.4, 'unit' => 'm', 'waste' => 7],
                        ['mat' => 'RAW-ZIP-02', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                        ['mat' => 'RAW-BTN-03', 'qty' => 0.01, 'unit' => 'box', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-PNT-02',
                    'name' => 'Celana Cargo Panjang Tactical 6 Saku',
                    'cat' => 'Celana & Bottomwear',
                    'sell' => 175000,
                    'labor_cost' => 30000,
                    'machine_cost' => 10000,
                    'bom' => [
                        ['mat' => 'RAW-FAB-07', 'qty' => 1.8, 'unit' => 'm', 'waste' => 8],
                        ['mat' => 'RAW-ZIP-02', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                        ['mat' => 'RAW-BTN-03', 'qty' => 0.05, 'unit' => 'box', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-PNT-03',
                    'name' => 'Celana Pendek Chino Casual Khaki',
                    'cat' => 'Celana & Bottomwear',
                    'sell' => 110000,
                    'labor_cost' => 18000,
                    'machine_cost' => 6000,
                    'bom' => [
                        ['mat' => 'RAW-FAB-06', 'qty' => 0.9, 'unit' => 'm', 'waste' => 5],
                        ['mat' => 'RAW-ZIP-02', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],

                // Jackets & Specials (3 SKUs)
                [
                    'code' => 'MFG-JKT-01',
                    'name' => 'Jaket Parka Drill Furing Asahi Hoodie',
                    'cat' => 'Jaket & Rompi Spesial',
                    'sell' => 220000,
                    'labor_cost' => 35000,
                    'machine_cost' => 12000,
                    'bom' => [
                        ['mat' => 'RAW-FAB-07', 'qty' => 2.2, 'unit' => 'm', 'waste' => 8],
                        ['mat' => 'RAW-LIN-01', 'qty' => 1.8, 'unit' => 'm', 'waste' => 6],
                        ['mat' => 'RAW-ZIP-03', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-JKT-02',
                    'name' => 'Rompi Vest Safety Multi Pocket Proyek',
                    'cat' => 'Jaket & Rompi Spesial',
                    'sell' => 115000,
                    'labor_cost' => 20000,
                    'machine_cost' => 6500,
                    'bom' => [
                        ['mat' => 'RAW-FAB-06', 'qty' => 1.1, 'unit' => 'm', 'waste' => 5],
                        ['mat' => 'RAW-ZIP-03', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'MFG-JKT-03',
                    'name' => 'Tote Bag Canvas Heavy Duty 12oz Sablon',
                    'cat' => 'Jaket & Rompi Spesial',
                    'sell' => 45000,
                    'labor_cost' => 7000,
                    'machine_cost' => 3000,
                    'bom' => [
                        ['mat' => 'RAW-FAB-12', 'qty' => 0.6, 'unit' => 'm', 'waste' => 4],
                    ],
                ],
            ];

            $productModels = [];
            $bomHeaderModels = [];
            foreach ($finishedGoodsCatalog as $pSpec) {
                $catId = $catModels[$pSpec['cat']]->id ?? null;
                $unitObj = $units['pcs'];

                $product = Product::withTrashed()
                    ->where('business_id', $business->id)
                    ->where('code', $pSpec['code'])
                    ->first();

                $productData = [
                    'business_id' => $business->id,
                    'category_id' => $catId,
                    'output_unit_id' => $unitObj->id,
                    'code' => $pSpec['code'],
                    'name' => $pSpec['name'],
                    'slug' => Str::slug($pSpec['name']),
                    'selling_price' => $pSpec['sell'],
                    'base_cost' => 0,
                    'type' => Product::TYPE_GOODS,
                    'bom_consumption_type' => Product::BOM_CONSUMPTION_ON_PRODUCTION,
                    'show_in_pos' => true,
                    'show_in_sales_order' => true,
                    'show_in_website' => true,
                    'business_type_hint' => 'mfg_garment',
                    'description' => "Barang jadi garment standar ekspor: {$pSpec['name']}",
                    'is_active' => true,
                ];

                if ($product) {
                    if ($product->trashed()) {
                        $product->restore();
                    }
                    $product->update($productData);
                } else {
                    $product = Product::create($productData);
                }

                // Cost Model & Production BOM
                $costModel = CostModel::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'product_id' => $product->id,
                        'slug' => 'hpp-mfg-' . Str::slug($pSpec['name']),
                    ],
                    [
                        'name' => 'BOM Standar HPP ' . $pSpec['name'],
                        'method' => CostModel::METHOD_RECIPE_BOM,
                        'is_active' => true,
                    ]
                );

                $bomHeader = $costModel->bomHeaders()->firstOrCreate(
                    ['cost_model_id' => $costModel->id],
                    [
                        'name' => 'BOM Manufaktur ' . $pSpec['name'],
                        'type' => BomHeader::TYPE_BOM,
                        'level' => 1,
                    ]
                );

                $totalMaterialCost = 0.0;
                foreach ($pSpec['bom'] as $bItem) {
                    if (isset($materialModels[$bItem['mat']])) {
                        $matObj = $materialModels[$bItem['mat']];
                        $rUnit = $units[$bItem['unit']] ?? $units['pcs'];
                        $qty = (float) $bItem['qty'];
                        $waste = (float) ($bItem['waste'] ?? 0);

                        $bomHeader->items()->updateOrCreate(
                            ['bom_header_id' => $bomHeader->id, 'material_id' => $matObj->id],
                            [
                                'unit_id' => $rUnit->id,
                                'quantity' => $qty,
                                'waste_percentage' => $waste,
                            ]
                        );

                        $price = (float) ($matObj->latestPrice?->purchase_price ?? 0);
                        $totalMaterialCost += ($qty * (1 + $waste / 100) * $price);
                    }
                }

                $laborCost = (float) $pSpec['labor_cost'];
                $machineCost = (float) $pSpec['machine_cost'];
                $unitHpp = round($totalMaterialCost + $laborCost + $machineCost, 2);

                $product->update(['base_cost' => $unitHpp]);
                $productModels[$pSpec['code']] = $product;
                $bomHeaderModels[$pSpec['code']] = $bomHeader;
            }

            // 12. B2B Corporate & Distributor Customers (14 Customers)
            $customerNames = [
                ['name' => 'PT Mega Busana Nusantara', 'company' => 'Mega Busana Group', 'phone' => '021-5801122'],
                ['name' => 'CV Distro Denim Bandung', 'company' => 'Distro Denim Syndicate', 'phone' => '022-4203344'],
                ['name' => 'PT Sumber Gaya Retail Tbk', 'company' => 'Sumber Gaya Fashion', 'phone' => '021-29557788'],
                ['name' => 'Koperasi Karyawan Astra Honda', 'company' => 'Kopkar AHM Cikarang', 'phone' => '021-8980123'],
                ['name' => 'PT Mitra Uniform Indonesia', 'company' => 'Mitra Seragam Kerja', 'phone' => '021-54309988'],
                ['name' => 'Brand Lokal Erigo Apparel', 'company' => 'PT Erigo Garment Pratama', 'phone' => '021-77889900'],
                ['name' => 'Brand Lokal Roughneck 1991', 'company' => 'CV Roughneck Apparel', 'phone' => '022-7201122'],
                ['name' => 'PT Inti Corpora Solusindo', 'company' => 'Inti Corpora Merchandise', 'phone' => '021-3901122'],
                ['name' => 'CV Jaya Mandiri Konveksi', 'company' => 'Jaya Mandiri Grosir', 'phone' => '021-6302211'],
                ['name' => 'PT Surya Cipta Busana', 'company' => 'Surya Cipta Retail', 'phone' => '021-4501188'],
                ['name' => 'Toko Grosir Kaos Tanah Abang', 'company' => 'UD Sinar Kaos', 'phone' => '021-3145566'],
                ['name' => 'Distributor Apparel Jawa Timur', 'company' => 'PT Jawa Timur Garment', 'phone' => '031-5034455'],
                ['name' => 'PT Retail Modern Nusantara', 'company' => 'Modern Retail Group', 'phone' => '021-7221199'],
                ['name' => 'Koperasi Pertamina EP', 'company' => 'Kopkar Pertamina EP', 'phone' => '021-5290123'],
            ];

            $customers = [];
            foreach ($customerNames as $cSpec) {
                $customers[] = Customer::updateOrCreate(
                    ['business_id' => $business->id, 'name' => $cSpec['name']],
                    [
                        'slug' => Str::slug($cSpec['name']),
                        'company_name' => $cSpec['company'],
                        'phone' => $cSpec['phone'],
                        'email' => Str::slug($cSpec['name']) . '@mfgclient.test',
                        'segment' => 'wholesale',
                        'billing_address' => 'Kawasan Industri & Perdagangan B2B',
                        'payment_terms_days' => 30,
                        'is_active' => true,
                    ]
                );
            }

            // 13. Production Orders (SPKP) with Scrap/Defect Tracking & Balanced Journals
            $productionOrdersSpec = [
                [
                    'order_number' => 'SPKP-2026-001',
                    'product_code' => 'MFG-TSH-01', // Kaos Combed 30s Hitam
                    'planned_qty' => 500,
                    'actual_qty' => 492,
                    'rejected_qty' => 8, // QC scrap defect
                    'status' => ProductionOrder::STATUS_COMPLETED,
                    'days_ago' => 10,
                    'batch' => 'BATCH-TSH-202601-A',
                ],
                [
                    'order_number' => 'SPKP-2026-002',
                    'product_code' => 'MFG-HOD-01', // Hoodie Fleece Hitam
                    'planned_qty' => 200,
                    'actual_qty' => 196,
                    'rejected_qty' => 4,
                    'status' => ProductionOrder::STATUS_COMPLETED,
                    'days_ago' => 8,
                    'batch' => 'BATCH-HOD-202601-B',
                ],
                [
                    'order_number' => 'SPKP-2026-003',
                    'product_code' => 'MFG-SHT-01', // Kemeja Oxford Putih
                    'planned_qty' => 300,
                    'actual_qty' => 294,
                    'rejected_qty' => 6,
                    'status' => ProductionOrder::STATUS_COMPLETED,
                    'days_ago' => 6,
                    'batch' => 'BATCH-SHT-202601-C',
                ],
                [
                    'order_number' => 'SPKP-2026-004',
                    'product_code' => 'MFG-PNT-01', // Celana Chino Khaki
                    'planned_qty' => 250,
                    'actual_qty' => 245,
                    'rejected_qty' => 5,
                    'status' => ProductionOrder::STATUS_COMPLETED,
                    'days_ago' => 4,
                    'batch' => 'BATCH-PNT-202601-D',
                ],
                [
                    'order_number' => 'SPKP-2026-005',
                    'product_code' => 'MFG-TSH-04', // Polo Shirt Lacoste
                    'planned_qty' => 300,
                    'actual_qty' => 295,
                    'rejected_qty' => 5,
                    'status' => ProductionOrder::STATUS_COMPLETED,
                    'days_ago' => 2,
                    'batch' => 'BATCH-POL-202601-E',
                ],
                [
                    'order_number' => 'SPKP-2026-006',
                    'product_code' => 'MFG-JKT-01', // Jaket Parka
                    'planned_qty' => 150,
                    'actual_qty' => 0,
                    'rejected_qty' => 0,
                    'status' => ProductionOrder::STATUS_IN_PROGRESS,
                    'days_ago' => 1,
                    'batch' => 'BATCH-JPR-202601-F',
                ],
                [
                    'order_number' => 'SPKP-2026-007',
                    'product_code' => 'MFG-HOD-02', // Hoodie Zipper Metal
                    'planned_qty' => 200,
                    'actual_qty' => 0,
                    'rejected_qty' => 0,
                    'status' => ProductionOrder::STATUS_RELEASED,
                    'days_ago' => 0,
                    'batch' => 'BATCH-HOD-202601-G',
                ],
            ];

            // Chart of Accounts for Production Auto-Journals
            $wipAccount = ChartOfAccount::where('business_id', $business->id)->where('code', '1-1040')->first()
                ?? ChartOfAccount::where('business_id', $business->id)->where('code', '1-1004')->first();
            $rawAccount = ChartOfAccount::where('business_id', $business->id)->where('code', '1-1030')->first()
                ?? ChartOfAccount::where('business_id', $business->id)->where('code', '1-1004')->first();
            $fgAccount = ChartOfAccount::where('business_id', $business->id)->where('code', '1-1004')->first();
            $laborExpenseAccount = ChartOfAccount::where('business_id', $business->id)->where('code', '6-6010')->first()
                ?? ChartOfAccount::where('business_id', $business->id)->where('code', '2-2001')->first();
            $overheadExpenseAccount = ChartOfAccount::where('business_id', $business->id)->where('code', '6-6002')->first()
                ?? ChartOfAccount::where('business_id', $business->id)->where('code', '2-2001')->first();

            foreach ($productionOrdersSpec as $pOrderSpec) {
                $prod = $productModels[$pOrderSpec['product_code']];
                $bom = $bomHeaderModels[$pOrderSpec['product_code']];
                $plannedQty = (float) $pOrderSpec['planned_qty'];
                $actualQty = (float) $pOrderSpec['actual_qty'];
                $rejectedQty = (float) $pOrderSpec['rejected_qty'];

                // Calculate costs from BOM
                $unitMaterialCost = 0.0;
                foreach ($bom->items as $bItem) {
                    $matPrice = (float) ($bItem->material?->latestPrice?->purchase_price ?? 0);
                    $qtyWithWaste = (float) $bItem->quantity * (1 + (float) $bItem->waste_percentage / 100);
                    $unitMaterialCost += ($qtyWithWaste * $matPrice);
                }

                $unitHpp = (float) $prod->base_cost;
                $unitLaborCost = 15000.0;
                $unitMachineCost = 5000.0;
                if ($unitHpp > $unitMaterialCost) {
                    $overheadRemaining = $unitHpp - $unitMaterialCost;
                    $unitLaborCost = round($overheadRemaining * 0.70, 2);
                    $unitMachineCost = round($overheadRemaining * 0.30, 2);
                }

                $totalMatCost = round($plannedQty * $unitMaterialCost, 2);
                $totalLabCost = round($plannedQty * $unitLaborCost, 2);
                $totalMchCost = round($plannedQty * $unitMachineCost, 2);
                $totalProdCost = $totalMatCost + $totalLabCost + $totalMchCost;

                $startDate = Carbon::now()->subDays($pOrderSpec['days_ago'])->toDateString();
                $endDate = $pOrderSpec['status'] === ProductionOrder::STATUS_COMPLETED
                    ? Carbon::now()->subDays($pOrderSpec['days_ago'] - 2)->toDateString()
                    : null;

                $prodOrder = ProductionOrder::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'order_number' => $pOrderSpec['order_number'],
                    ],
                    [
                        'product_id' => $prod->id,
                        'bom_header_id' => $bom->id,
                        'source_location_id' => $factoryLoc->id,
                        'target_location_id' => $warehouseLoc->id,
                        'planned_quantity' => $plannedQty,
                        'actual_quantity' => $actualQty,
                        'rejected_quantity' => $rejectedQty,
                        'status' => $pOrderSpec['status'],
                        'planned_start_date' => $startDate,
                        'actual_end_date' => $endDate,
                        'unit_cost_hpp' => $unitHpp,
                        'total_material_cost' => $totalMatCost,
                        'total_labor_cost' => $totalLabCost,
                        'total_machine_cost' => $totalMchCost,
                        'total_production_cost' => $totalProdCost,
                        'batch_number' => $pOrderSpec['batch'],
                        'notes' => "Perintah Kerja Produksi Garment SPKP #{$pOrderSpec['order_number']}",
                        'created_by' => $userModels['manager']->id,
                        'completed_by' => $pOrderSpec['status'] === ProductionOrder::STATUS_COMPLETED ? $userModels['production']->id : null,
                    ]
                );

                if ($prodOrder->wasRecentlyCreated) {
                    // Populate Production Order Items
                    foreach ($bom->items as $bItem) {
                        $consumedQty = $plannedQty * (float) $bItem->quantity * (1 + (float) $bItem->waste_percentage / 100);
                        $matPrice = (float) ($bItem->material?->latestPrice?->purchase_price ?? 0);

                        ProductionOrderItem::create([
                            'production_order_id' => $prodOrder->id,
                            'material_id' => $bItem->material_id,
                            'unit_id' => $bItem->unit_id,
                            'planned_quantity' => $consumedQty,
                            'actual_consumed_quantity' => $actualQty > 0 ? $consumedQty : 0,
                            'waste_quantity' => $rejectedQty > 0 ? ($consumedQty * ($rejectedQty / $plannedQty)) : 0,
                            'unit_cost' => $matPrice,
                            'total_cost' => $consumedQty * $matPrice,
                            'batch_number' => $prodOrder->batch_number,
                            'notes' => 'Alokasi bahan baku SPKP',
                        ]);
                    }

                    // Stock movements & balanced journals for completed orders
                    if ($prodOrder->status === ProductionOrder::STATUS_COMPLETED) {
                        // Increase finished goods stock in warehouse
                        $fgStock = InventoryStock::firstOrCreate(
                            [
                                'business_id' => $business->id,
                                'location_id' => $warehouseLoc->id,
                                'product_id' => $prod->id,
                            ],
                            [
                                'material_id' => null,
                                'quantity' => 0,
                                'reserved_quantity' => 0,
                                'last_cost' => $unitHpp,
                                'avg_purchase_cost' => $unitHpp,
                            ]
                        );
                        $fgStock->increment('quantity', $actualQty);

                        StockMovement::create([
                            'business_id' => $business->id,
                            'location_id' => $warehouseLoc->id,
                            'product_id' => $prod->id,
                            'material_id' => null,
                            'movement_type' => StockMovement::TYPE_PRODUCTION_RECEIPT,
                            'quantity_change' => $actualQty,
                            'balance_after' => $fgStock->quantity,
                            'unit_cost' => $unitHpp,
                            'total_cost' => $actualQty * $unitHpp,
                            'reference_number' => $prodOrder->order_number,
                            'notes' => "Hasil Produksi SPKP Selesai ({$actualQty} unit bagus, {$rejectedQty} defect)",
                            'created_by' => $userModels['production']->id,
                        ]);

                        // 1. Double-entry Journal: Material Issue to Floor (Dr WIP, Cr Raw Material, Cr Labor, Cr Machine Overhead)
                        $issueEntry = JournalEntry::updateOrCreate(
                            [
                                'business_id' => $business->id,
                                'reference_type' => JournalEntry::REF_PRODUCTION_ORDER,
                                'reference_id' => $prodOrder->id,
                                'entry_number' => 'JRN-PROD-ISSUE-' . $prodOrder->order_number,
                            ],
                            [
                                'entry_date' => $prodOrder->planned_start_date,
                                'description' => "Rilis Bahan Baku & Alokasi Biaya SPKP #{$prodOrder->order_number} ({$prod->name})",
                                'total_debit' => $totalProdCost,
                                'total_credit' => $totalProdCost,
                                'created_by' => $userModels['production']->id,
                            ]
                        );

                        if ($issueEntry->wasRecentlyCreated) {
                            // Dr. Barang Dalam Proses (WIP)
                            JournalEntryLine::create([
                                'journal_entry_id' => $issueEntry->id,
                                'account_id' => $wipAccount->id,
                                'type' => JournalEntryLine::TYPE_DEBIT,
                                'amount' => $totalProdCost,
                                'notes' => "Masuk WIP lantai produksi #{$prodOrder->order_number}",
                            ]);
                            // Cr. Persediaan Bahan Baku (Raw Material)
                            JournalEntryLine::create([
                                'journal_entry_id' => $issueEntry->id,
                                'account_id' => $rawAccount->id,
                                'type' => JournalEntryLine::TYPE_CREDIT,
                                'amount' => $totalMatCost,
                                'notes' => 'Bahan baku terpakai produksi',
                            ]);
                            // Cr. Biaya Tenaga Kerja Langsung
                            JournalEntryLine::create([
                                'journal_entry_id' => $issueEntry->id,
                                'account_id' => $laborExpenseAccount->id,
                                'type' => JournalEntryLine::TYPE_CREDIT,
                                'amount' => $totalLabCost,
                                'notes' => 'Alokasi upah tenaga kerja produksi',
                            ]);
                            // Cr. Biaya Overhead Mesin & Pabrik
                            JournalEntryLine::create([
                                'journal_entry_id' => $issueEntry->id,
                                'account_id' => $overheadExpenseAccount->id,
                                'type' => JournalEntryLine::TYPE_CREDIT,
                                'amount' => $totalMchCost,
                                'notes' => 'Alokasi listrik & perawatan mesin pabrik',
                            ]);
                        }

                        // 2. Double-entry Journal: Production Completion (Dr Finished Goods, Cr WIP)
                        $compEntry = JournalEntry::updateOrCreate(
                            [
                                'business_id' => $business->id,
                                'reference_type' => JournalEntry::REF_PRODUCTION_ORDER,
                                'reference_id' => $prodOrder->id,
                                'entry_number' => 'JRN-PROD-COMP-' . $prodOrder->order_number,
                            ],
                            [
                                'entry_date' => $prodOrder->actual_end_date,
                                'description' => "Penyelesaian Produksi Barang Jadi SPKP #{$prodOrder->order_number} ({$actualQty} pcs {$prod->name})",
                                'total_debit' => $totalProdCost,
                                'total_credit' => $totalProdCost,
                                'created_by' => $userModels['production']->id,
                            ]
                        );

                        if ($compEntry->wasRecentlyCreated) {
                            // Dr. Persediaan Barang Jadi (Finished Goods)
                            JournalEntryLine::create([
                                'journal_entry_id' => $compEntry->id,
                                'account_id' => $fgAccount->id,
                                'type' => JournalEntryLine::TYPE_DEBIT,
                                'amount' => $totalProdCost,
                                'notes' => "Penerimaan {$actualQty} unit barang jadi ke gudang",
                            ]);
                            // Cr. Barang Dalam Proses (WIP)
                            JournalEntryLine::create([
                                'journal_entry_id' => $compEntry->id,
                                'account_id' => $wipAccount->id,
                                'type' => JournalEntryLine::TYPE_CREDIT,
                                'amount' => $totalProdCost,
                                'notes' => "Pembersihan akun WIP atas selesai produksi #{$prodOrder->order_number}",
                            ]);
                        }
                    }
                }
            }

            // 14. B2B Sales / Distribution Orders (with Balanced Auto-Journals)
            $register = PosRegister::updateOrCreate(
                ['business_id' => $business->id, 'location_id' => $warehouseLoc->id, 'code' => 'REG-TNG-01'],
                [
                    'name' => 'Kasir Grosir & Penjualan Distributor (Tangerang)',
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
                    'location_id' => $warehouseLoc->id,
                    'user_id' => $userModels['finance']->id,
                    'opened_at' => Carbon::now()->subHours(8),
                    'closed_at' => Carbon::now()->subHour(),
                    'opening_cash' => 0,
                    'closing_cash_actual' => 0,
                    'closing_cash_expected' => 0,
                    'cash_difference' => 0,
                    'total_cash_sales' => 0,
                    'total_non_cash_sales' => 18500000,
                ]
            );

            $sampleB2bSales = [
                [
                    'order_number' => 'SO-MFG-2026-001',
                    'customer' => $customers[0], // PT Mega Busana
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'MFG-TSH-01', 'qty' => 100], // 100x Kaos Hitam @ 55,000 = 5.5M
                        ['code' => 'MFG-HOD-01', 'qty' => 50],  // 50x Hoodie Hitam @ 165,000 = 8.25M
                    ],
                ],
                [
                    'order_number' => 'SO-MFG-2026-002',
                    'customer' => $customers[1], // CV Distro Denim
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'MFG-SHT-01', 'qty' => 50], // 50x Kemeja Oxford @ 145,000 = 7.25M
                        ['code' => 'MFG-PNT-01', 'qty' => 40], // 40x Celana Chino @ 155,000 = 6.2M
                    ],
                ],
            ];

            foreach ($sampleB2bSales as $bSale) {
                $subtotal = 0.0;
                $totalHpp = 0.0;
                $lineItemsData = [];

                foreach ($bSale['items'] as $itemSpec) {
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
                        'order_number' => $bSale['order_number'],
                    ],
                    [
                        'location_id' => $warehouseLoc->id,
                        'pos_shift_id' => $shift->id,
                        'pos_register_id' => $register->id,
                        'user_id' => $userModels['finance']->id,
                        'customer_id' => $bSale['customer']->id,
                        'order_date' => Carbon::now()->toDateString(),
                        'status' => PosOrder::STATUS_COMPLETED,
                        'order_type' => 'takeaway',
                        'sales_channel' => 'pos',
                        'customer_name_guest' => $bSale['customer']->name,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'paid_amount' => $totalAmount,
                        'change_amount' => 0,
                        'total_hpp_cost' => $totalHpp,
                        'total_gross_profit' => $totalAmount - $totalHpp,
                        'notes' => 'Pesanan Penjualan B2B Grosir Distributor Garment',
                    ]
                );

                if ($order->wasRecentlyCreated) {
                    foreach ($lineItemsData as $line) {
                        PosOrderItem::create(array_merge($line, ['pos_order_id' => $order->id]));
                    }

                    PosOrderPayment::create([
                        'pos_order_id' => $order->id,
                        'payment_method' => $bSale['payment_method'],
                        'amount' => $totalAmount,
                        'reference_number' => 'PAY-' . $order->order_number,
                        'status' => 'settled',
                    ]);

                    // Balanced Auto-Journal Entry
                    $this->journalService->recordPosSaleJournal($order);
                }
            }
        });

        $this->command?->info('✓ Sukses seeding COOCA Manufacturing Indonesia (Manufacturing)!');
    }
}
