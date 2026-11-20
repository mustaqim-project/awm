<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Template\BusinessTemplateService;
use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\CostModel;
use App\Models\Customer;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosPrinter;
use App\Models\PosPrinterRoutingRule;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\PosTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductModifierGroup;
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

final class CoocaBistroFnbSeeder extends Seeder
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
        $this->command?->info('Seeding Food & Beverage: COOCA Bistro & Coffee...');

        DB::transaction(function () {
            // 1. Business Creation
            $business = Business::updateOrCreate(
                ['slug' => 'cooca-bistro-coffee'],
                [
                    'name' => 'COOCA Bistro & Coffee',
                    'currency' => 'IDR',
                    'currency_precision' => 0,
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                    'phone' => '0811-2233-4402',
                    'email' => 'kontak@coocabistro.id',
                    'address' => 'Jl. Senopati No. 45, Kebayoran Baru, Jakarta Selatan',
                    'tax_identification_number' => '01.234.567.8-012.000',
                    'bank_name' => 'Bank Mandiri',
                    'bank_account_number' => '1370011223344',
                    'bank_account_holder' => 'PT Cooca Bistro Nusantara',
                    'pos_enable_tax' => true,
                    'pos_tax_percent' => 10,
                    'pos_enable_service_charge' => true,
                    'pos_service_charge_percent' => 5,
                    'template_code' => 'fnb_resto',
                    'industry_category' => 'fnb',
                    'is_demo' => true,
                    'is_active' => true,
                ]
            );

            Context::setBusiness($business);
            $this->journalService->ensureStandardAccounts($business);

            // 2. Business Type Template
            $template = BusinessTypeTemplate::where('code', 'fnb_resto')->first();
            if ($template) {
                $this->templateService->apply($business, $template);
            }

            // 3. User Accounts & Roles
            $roles = Role::whereNull('business_id')->get()->keyBy('slug');

            $demoUsers = [
                ['name' => 'Chef Adrian Prasetya (Owner & Exec Chef)', 'email' => 'owner@coocabistro.id', 'role' => 'owner'],
                ['name' => 'Maya Angeline (Floor & Outlet Manager)', 'email' => 'manager@coocabistro.id', 'role' => 'manager'],
                ['name' => 'Rian Hidayat (Head Cashier & POS)', 'email' => 'cashier@coocabistro.id', 'role' => 'cashier'],
                ['name' => 'Dedi Gunawan (Kitchen Storage & Inventory)', 'email' => 'warehouse@coocabistro.id', 'role' => 'warehouse'],
                ['name' => 'Clarissa Tan (Finance & Cashier Audit)', 'email' => 'finance@coocabistro.id', 'role' => 'finance'],
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
                $userModels[$uData['role']] = $user;
            }

            // Also attach global testing accounts
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

            // 4. Locations (3 Outlets)
            $locationSpecs = [
                [
                    'name' => 'Senopati (Flagship Bistro & Bar)',
                    'slug' => 'senopati',
                    'code' => 'LOC-SNT',
                    'type' => 'outlet',
                    'is_primary' => true,
                    'address' => 'Jl. Senopati No. 45, Kebayoran Baru, Jakarta Selatan',
                    'phone' => '021-72780001',
                ],
                [
                    'name' => 'Grand Indonesia (Mall Outlet)',
                    'slug' => 'grand-indonesia',
                    'code' => 'LOC-GI',
                    'type' => 'outlet',
                    'is_primary' => false,
                    'address' => 'Grand Indonesia East Mall Lt. 3A, Thamrin, Jakarta Pusat',
                    'phone' => '021-23580002',
                ],
                [
                    'name' => 'BSD Green Office Park (Coffee & Eatery)',
                    'slug' => 'bsd-gop',
                    'code' => 'LOC-BSD',
                    'type' => 'outlet',
                    'is_primary' => false,
                    'address' => 'The Breeze BSD, Green Office Park 6, BSD City, Tangerang',
                    'phone' => '021-50550003',
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

            $primaryLoc = $locations['senopati'];

            // 5. Units Lookup
            $units = Unit::all()->keyBy('code');

            // 6. F&B Suppliers (10 Suppliers)
            $supplierSpecs = [
                ['name' => 'PT Sukanda Djaya (Diamond Cold Storage)', 'contact' => 'Eko Sulistyo', 'phone' => '021-4602901', 'email' => 'sales@sukandadjaya.test', 'address' => 'Kawasan Industri MM2100, Cikarang'],
                ['name' => 'PT Classic Fine Foods Indonesia', 'contact' => 'Jessica Wong', 'phone' => '021-78832000', 'email' => 'order@classicfinefoods.test', 'address' => 'Jl. TB Simatupang No. 18, Jakarta Selatan'],
                ['name' => 'Tanamera Coffee Roastery Indonesia', 'contact' => 'Dwi Prasetyo', 'phone' => '021-29625599', 'email' => 'beans@tanameracoffee.test', 'address' => 'Thamrin City Office Park, Jakarta Pusat'],
                ['name' => 'Greenfields Dairy Indonesia', 'contact' => 'Sari Indrawati', 'phone' => '021-52892200', 'email' => 'horeca@greenfieldsdairy.test', 'address' => 'Cyber 2 Tower, Rasuna Said, Jakarta Selatan'],
                ['name' => 'PT Prambanan Kencana (Baking & Pastry)', 'contact' => 'Hengky Wijaya', 'phone' => '021-56968000', 'email' => 'order@prambanankencana.test', 'address' => 'Jl. Daan Mogot Km 10 No. 2, Jakarta Barat'],
                ['name' => 'CV Sayur Segar Hidroponik Lembang', 'contact' => 'Kang Asep', 'phone' => '0812-2233-4455', 'email' => 'sayur@lembangfresh.test', 'address' => 'Jl. Maribaya No. 15, Lembang, Bandung'],
                ['name' => 'PT Indoguna Utama (Meat & Poultry Specialist)', 'contact' => 'Rudi Santoso', 'phone' => '021-8602200', 'email' => 'sales@indoguna.test', 'address' => 'Jl. Taruna No. 1, Pondok Bambu, Jakarta Timur'],
                ['name' => 'Monin Gourmet Flavors Indonesia', 'contact' => 'Nadia Putri', 'phone' => '021-57953300', 'email' => 'order@monin-id.test', 'address' => 'Plaza Sentral Lt. 12, Sudirman, Jakarta'],
                ['name' => 'Oatside Dairy Alternative Singapore', 'contact' => 'Kevin Lim', 'phone' => '021-29881122', 'email' => 'b2b@oatside.test', 'address' => 'South Quarter Tower A, Cilandak, Jakarta'],
                ['name' => 'PT Sumber Kemasan Kuliner', 'contact' => 'Budi Hartono', 'phone' => '021-54378899', 'email' => 'order@kemasankuliner.test', 'address' => 'Jl. Kapuk Kamal Raya No. 45, Jakarta Utara'],
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
                        'notes' => 'Termin Pembayaran: Net 14 Hari',
                    ]
                );
            }

            // 7. 30 Raw Ingredients (Materials) with Prices and Stock
            $rawMaterialsCatalog = [
                ['code' => 'MAT-COF-01', 'name' => 'Biji Kopi Arabica Gayo Full Wash (kg)', 'unit' => 'kg', 'price' => 145000, 'supplier_idx' => 2, 'stock' => 50],
                ['code' => 'MAT-COF-02', 'name' => 'Biji Kopi Robusta Temanggung Fine (kg)', 'unit' => 'kg', 'price' => 85000, 'supplier_idx' => 2, 'stock' => 40],
                ['code' => 'MAT-MLK-01', 'name' => 'Fresh Milk Greenfields Pasteurized 1L', 'unit' => 'l', 'price' => 21000, 'supplier_idx' => 3, 'stock' => 120],
                ['code' => 'MAT-MLK-02', 'name' => 'Oat Milk Barista Oatside 1L', 'unit' => 'l', 'price' => 36000, 'supplier_idx' => 8, 'stock' => 80],
                ['code' => 'MAT-SYR-01', 'name' => 'Sirup Vanilla Monin 700ml', 'unit' => 'ml', 'price' => 180, 'supplier_idx' => 7, 'stock' => 7000], // Rp 126k/btl
                ['code' => 'MAT-SYR-02', 'name' => 'Sirup Caramel Monin 700ml', 'unit' => 'ml', 'price' => 180, 'supplier_idx' => 7, 'stock' => 7000],
                ['code' => 'MAT-SYR-03', 'name' => 'Gula Aren Cair Organik 1L', 'unit' => 'ml', 'price' => 45, 'supplier_idx' => 4, 'stock' => 10000],
                ['code' => 'MAT-TEA-01', 'name' => 'Bubuk Matcha Uji Ceremonial Grade (g)', 'unit' => 'g', 'price' => 450, 'supplier_idx' => 4, 'stock' => 2000],
                ['code' => 'MAT-TEA-02', 'name' => 'Teh Earl Grey Loose Leaf Twinings (g)', 'unit' => 'g', 'price' => 180, 'supplier_idx' => 4, 'stock' => 3000],
                ['code' => 'MAT-CHO-01', 'name' => 'Dark Chocolate Callebaut 54.5% Callets (kg)', 'unit' => 'kg', 'price' => 185000, 'supplier_idx' => 4, 'stock' => 25],
                ['code' => 'MAT-MEA-01', 'name' => 'Daging Sapi Wagyu Ribeye MB5 (kg)', 'unit' => 'kg', 'price' => 420000, 'supplier_idx' => 6, 'stock' => 30],
                ['code' => 'MAT-MEA-02', 'name' => 'Daging Sapi Sirloin Australian Angus (kg)', 'unit' => 'kg', 'price' => 260000, 'supplier_idx' => 6, 'stock' => 40],
                ['code' => 'MAT-MEA-03', 'name' => 'Dada Ayam Fillet Boneless Skinless (kg)', 'unit' => 'kg', 'price' => 58000, 'supplier_idx' => 6, 'stock' => 60],
                ['code' => 'MAT-FIS-01', 'name' => 'Norwegian Fresh Salmon Trout Fillet (kg)', 'unit' => 'kg', 'price' => 290000, 'supplier_idx' => 0, 'stock' => 25],
                ['code' => 'MAT-PAS-01', 'name' => 'Pasta Spaghetti Barilla No. 5 500g', 'unit' => 'pack', 'price' => 24000, 'supplier_idx' => 1, 'stock' => 50],
                ['code' => 'MAT-PAS-02', 'name' => 'Pasta Fettuccine De Cecco 500g', 'unit' => 'pack', 'price' => 28000, 'supplier_idx' => 1, 'stock' => 40],
                ['code' => 'MAT-CHS-01', 'name' => 'Keju Parmesan Grana Padano Wedge (kg)', 'unit' => 'kg', 'price' => 280000, 'supplier_idx' => 1, 'stock' => 15],
                ['code' => 'MAT-CHS-02', 'name' => 'Keju Mozzarella Perfetto Block (kg)', 'unit' => 'kg', 'price' => 135000, 'supplier_idx' => 0, 'stock' => 25],
                ['code' => 'MAT-BUT-01', 'name' => 'Mentega Elle & Vire Unsalted Butter (kg)', 'unit' => 'kg', 'price' => 165000, 'supplier_idx' => 1, 'stock' => 20],
                ['code' => 'MAT-CRM-01', 'name' => 'Anchor Cooking Cream Culinary 1L', 'unit' => 'l', 'price' => 65000, 'supplier_idx' => 0, 'stock' => 45],
                ['code' => 'MAT-OIL-01', 'name' => 'Extra Virgin Olive Oil Monini 1L', 'unit' => 'ml', 'price' => 160, 'supplier_idx' => 1, 'stock' => 15000],
                ['code' => 'MAT-TRF-01', 'name' => 'Black Truffle Oil Urbani 250ml', 'unit' => 'ml', 'price' => 850, 'supplier_idx' => 1, 'stock' => 1500],
                ['code' => 'MAT-POT-01', 'name' => 'Kentang French Fries Aviko Straight Cut 2.5kg', 'unit' => 'pack', 'price' => 88000, 'supplier_idx' => 0, 'stock' => 35],
                ['code' => 'MAT-EGG-01', 'name' => 'Telur Ayam Omega Segar (butir)', 'unit' => 'pcs', 'price' => 2600, 'supplier_idx' => 5, 'stock' => 300],
                ['code' => 'MAT-VEG-01', 'name' => 'Selada Romaine Hidroponik Segar (kg)', 'unit' => 'kg', 'price' => 35000, 'supplier_idx' => 5, 'stock' => 20],
                ['code' => 'MAT-VEG-02', 'name' => 'Tomat Cherry Hidroponik Merah (kg)', 'unit' => 'kg', 'price' => 42000, 'supplier_idx' => 5, 'stock' => 15],
                ['code' => 'MAT-VEG-03', 'name' => 'Bawang Bombai Import (kg)', 'unit' => 'kg', 'price' => 28000, 'supplier_idx' => 5, 'stock' => 30],
                ['code' => 'MAT-VEG-04', 'name' => 'Bawang Putih Kupas Segar (kg)', 'unit' => 'kg', 'price' => 38000, 'supplier_idx' => 5, 'stock' => 25],
                ['code' => 'MAT-RIC-01', 'name' => 'Beras Organik Mentik Wangi Susu (kg)', 'unit' => 'kg', 'price' => 22000, 'supplier_idx' => 5, 'stock' => 80],
                ['code' => 'MAT-FLR-01', 'name' => 'Tepung Terigu Komachi Japanese Bread Flour (kg)', 'unit' => 'kg', 'price' => 26000, 'supplier_idx' => 4, 'stock' => 50],
            ];

            $materialModels = [];
            foreach ($rawMaterialsCatalog as $mSpec) {
                $unitObj = $units[$mSpec['unit']] ?? $units['kg'];
                $supObj = $suppliers[$mSpec['supplier_idx']] ?? $suppliers[0];

                $mat = Material::updateOrCreate(
                    ['business_id' => $business->id, 'code' => $mSpec['code']],
                    [
                        'name' => $mSpec['name'],
                        'slug' => Str::slug($mSpec['name']),
                        'unit_id' => $unitObj->id,
                        'supplier_id' => $supObj->id,
                        'description' => "Bahan baku resep COOCA Bistro: {$mSpec['name']}",
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

                // Inventory Stock across 3 outlets
                foreach ($locations as $locKey => $loc) {
                    $multiplier = match ($locKey) {
                        'senopati' => 1.0,
                        'grand-indonesia' => 0.7,
                        'bsd-gop' => 0.6,
                    };
                    $qty = round($mSpec['stock'] * $multiplier);

                    InventoryStock::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'location_id' => $loc->id,
                            'material_id' => $mat->id,
                        ],
                        [
                            'product_id' => null,
                            'quantity' => $qty,
                            'reserved_quantity' => 0,
                            'last_cost' => $mSpec['price'],
                            'avg_purchase_cost' => $mSpec['price'],
                        ]
                    );

                    StockMovement::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'material_id' => $mat->id,
                            'location_id' => $loc->id,
                            'reference_number' => "INIT-MAT-{$loc->code}-{$mat->code}",
                        ],
                        [
                            'product_id' => null,
                            'movement_type' => StockMovement::TYPE_INITIAL,
                            'quantity_change' => $qty,
                            'balance_after' => $qty,
                            'unit_cost' => $mSpec['price'],
                            'total_cost' => $qty * $mSpec['price'],
                            'notes' => "Stok Awal Bahan Baku {$loc->name}",
                            'created_by' => $userModels['owner']->id,
                        ]
                    );
                }
            }

            // 8. Categories for Menu Items
            $menuCategories = [
                'Specialty Coffee & Espresso' => 'Kopi berbasis espresso dengan biji arabika pilihan dan teknik seduh presisi.',
                'Artisan Tea & Matcha' => 'Teh daun premium dan matcha autentik Uji Jepang.',
                'Signature Mocktails' => 'Minuman segar racikan mixologist berbahan dasar buah dan rempah.',
                'Mains & Steaks' => 'Hidangan utama daging wagyu, steak pilihan, dan olahan ayam panggang spesial.',
                'Pasta & Risotto' => 'Pasta artisan al dente dengan saus khas bistro modern.',
                'Appetizers & Bites' => 'Camilan pembuka, salad segar, dan finger food.',
                'Desserts & Sweet Bites' => 'Kue manis dan hidangan penutup penutup santap lezat.',
            ];

            $catModels = [];
            foreach ($menuCategories as $cName => $cDesc) {
                $catModels[$cName] = ProductCategory::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => Str::slug($cName)],
                    ['name' => $cName, 'description' => $cDesc]
                );
            }

            // 9. Menu Items (34 Menu Items with Recipes & BOM)
            $menuCatalog = [
                // Coffee (6 SKUs)
                [
                    'code' => 'FNB-COF-01',
                    'name' => 'Single Espresso Romano',
                    'cat' => 'Specialty Coffee & Espresso',
                    'sell' => 28000,
                    'recipe' => [
                        ['mat' => 'MAT-COF-01', 'qty' => 0.018, 'unit' => 'kg', 'waste' => 0], // 18g coffee
                    ],
                ],
                [
                    'code' => 'FNB-COF-02',
                    'name' => 'Cafe Latte (Hot / Iced)',
                    'cat' => 'Specialty Coffee & Espresso',
                    'sell' => 42000,
                    'recipe' => [
                        ['mat' => 'MAT-COF-01', 'qty' => 0.018, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-MLK-01', 'qty' => 0.200, 'unit' => 'l', 'waste' => 5], // 200ml milk
                    ],
                ],
                [
                    'code' => 'FNB-COF-03',
                    'name' => 'Cappuccino Italian Roast',
                    'cat' => 'Specialty Coffee & Espresso',
                    'sell' => 42000,
                    'recipe' => [
                        ['mat' => 'MAT-COF-01', 'qty' => 0.018, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-MLK-01', 'qty' => 0.150, 'unit' => 'l', 'waste' => 5],
                    ],
                ],
                [
                    'code' => 'FNB-COF-04',
                    'name' => 'Spanish Vanilla Latte',
                    'cat' => 'Specialty Coffee & Espresso',
                    'sell' => 48000,
                    'recipe' => [
                        ['mat' => 'MAT-COF-01', 'qty' => 0.018, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-MLK-01', 'qty' => 0.180, 'unit' => 'l', 'waste' => 5],
                        ['mat' => 'MAT-SYR-01', 'qty' => 20, 'unit' => 'ml', 'waste' => 0], // 20ml vanilla
                    ],
                ],
                [
                    'code' => 'FNB-COF-05',
                    'name' => 'Caramel Macchiato Bistro',
                    'cat' => 'Specialty Coffee & Espresso',
                    'sell' => 48000,
                    'recipe' => [
                        ['mat' => 'MAT-COF-01', 'qty' => 0.018, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-MLK-01', 'qty' => 0.180, 'unit' => 'l', 'waste' => 5],
                        ['mat' => 'MAT-SYR-02', 'qty' => 20, 'unit' => 'ml', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-COF-06',
                    'name' => 'Kopi Susu Gula Aren Senopati',
                    'cat' => 'Specialty Coffee & Espresso',
                    'sell' => 35000,
                    'recipe' => [
                        ['mat' => 'MAT-COF-02', 'qty' => 0.018, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-MLK-01', 'qty' => 0.150, 'unit' => 'l', 'waste' => 5],
                        ['mat' => 'MAT-SYR-03', 'qty' => 25, 'unit' => 'ml', 'waste' => 0],
                    ],
                ],

                // Tea & Chocolate (5 SKUs)
                [
                    'code' => 'FNB-TEA-01',
                    'name' => 'Signature Uji Matcha Latte',
                    'cat' => 'Artisan Tea & Matcha',
                    'sell' => 48000,
                    'recipe' => [
                        ['mat' => 'MAT-TEA-01', 'qty' => 8, 'unit' => 'g', 'waste' => 0],
                        ['mat' => 'MAT-MLK-01', 'qty' => 0.200, 'unit' => 'l', 'waste' => 5],
                    ],
                ],
                [
                    'code' => 'FNB-TEA-02',
                    'name' => 'Royal Earl Grey Lavender Tea',
                    'cat' => 'Artisan Tea & Matcha',
                    'sell' => 38000,
                    'recipe' => [
                        ['mat' => 'MAT-TEA-02', 'qty' => 6, 'unit' => 'g', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-CHO-01',
                    'name' => 'Callebaut Belgian Dark Chocolate 54%',
                    'cat' => 'Artisan Tea & Matcha',
                    'sell' => 48000,
                    'recipe' => [
                        ['mat' => 'MAT-CHO-01', 'qty' => 0.040, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-MLK-01', 'qty' => 0.180, 'unit' => 'l', 'waste' => 5],
                    ],
                ],
                [
                    'code' => 'FNB-TEA-03',
                    'name' => 'Iced Lychee Peach Jasmine Tea',
                    'cat' => 'Artisan Tea & Matcha',
                    'sell' => 40000,
                    'recipe' => [
                        ['mat' => 'MAT-TEA-02', 'qty' => 5, 'unit' => 'g', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-TEA-04',
                    'name' => 'Lemon Grass Infused Ginger Tea',
                    'cat' => 'Artisan Tea & Matcha',
                    'sell' => 36000,
                    'recipe' => [
                        ['mat' => 'MAT-TEA-02', 'qty' => 5, 'unit' => 'g', 'waste' => 0],
                    ],
                ],

                // Mocktails (4 SKUs)
                [
                    'code' => 'FNB-MCK-01',
                    'name' => 'Senopati Sunset Spritz',
                    'cat' => 'Signature Mocktails',
                    'sell' => 45000,
                    'recipe' => [
                        ['mat' => 'MAT-SYR-01', 'qty' => 15, 'unit' => 'ml', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-MCK-02',
                    'name' => 'Sparkling Berry Basil Refresher',
                    'cat' => 'Signature Mocktails',
                    'sell' => 45000,
                    'recipe' => [
                        ['mat' => 'MAT-SYR-01', 'qty' => 15, 'unit' => 'ml', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-MCK-03',
                    'name' => 'Cucumber Mint Botanical Cooler',
                    'cat' => 'Signature Mocktails',
                    'sell' => 42000,
                    'recipe' => [
                        ['mat' => 'MAT-VEG-01', 'qty' => 0.050, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-MCK-04',
                    'name' => 'Espresso Tonic Yuzu Fizz',
                    'cat' => 'Signature Mocktails',
                    'sell' => 45000,
                    'recipe' => [
                        ['mat' => 'MAT-COF-01', 'qty' => 0.018, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],

                // Mains & Steaks (7 SKUs)
                [
                    'code' => 'FNB-STK-01',
                    'name' => 'Wagyu Ribeye MB5 Steak 200g',
                    'cat' => 'Mains & Steaks',
                    'sell' => 295000,
                    'recipe' => [
                        ['mat' => 'MAT-MEA-01', 'qty' => 0.200, 'unit' => 'kg', 'waste' => 5], // 200g wagyu + 5% trim
                        ['mat' => 'MAT-BUT-01', 'qty' => 0.025, 'unit' => 'kg', 'waste' => 0], // butter
                        ['mat' => 'MAT-OIL-01', 'qty' => 15, 'unit' => 'ml', 'waste' => 0],    // olive oil
                        ['mat' => 'MAT-TRF-01', 'qty' => 5, 'unit' => 'ml', 'waste' => 0],     // truffle oil
                    ],
                ],
                [
                    'code' => 'FNB-STK-02',
                    'name' => 'Australian Angus Sirloin Steak 200g',
                    'cat' => 'Mains & Steaks',
                    'sell' => 185000,
                    'recipe' => [
                        ['mat' => 'MAT-MEA-02', 'qty' => 0.200, 'unit' => 'kg', 'waste' => 5],
                        ['mat' => 'MAT-BUT-01', 'qty' => 0.020, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-OIL-01', 'qty' => 15, 'unit' => 'ml', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-STK-03',
                    'name' => 'Pan-Seared Norwegian Salmon with Creamy Dill',
                    'cat' => 'Mains & Steaks',
                    'sell' => 165000,
                    'recipe' => [
                        ['mat' => 'MAT-FIS-01', 'qty' => 0.180, 'unit' => 'kg', 'waste' => 5],
                        ['mat' => 'MAT-CRM-01', 'qty' => 0.050, 'unit' => 'l', 'waste' => 0],
                        ['mat' => 'MAT-BUT-01', 'qty' => 0.015, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-STK-04',
                    'name' => 'Herb Roasted Half Chicken with Demi-Glace',
                    'cat' => 'Mains & Steaks',
                    'sell' => 115000,
                    'recipe' => [
                        ['mat' => 'MAT-MEA-03', 'qty' => 0.350, 'unit' => 'kg', 'waste' => 5],
                        ['mat' => 'MAT-BUT-01', 'qty' => 0.020, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-VEG-04', 'qty' => 0.020, 'unit' => 'kg', 'waste' => 5],
                    ],
                ],
                [
                    'code' => 'FNB-STK-05',
                    'name' => 'Bistro Wagyu Beef Burger & Fries',
                    'cat' => 'Mains & Steaks',
                    'sell' => 125000,
                    'recipe' => [
                        ['mat' => 'MAT-MEA-01', 'qty' => 0.150, 'unit' => 'kg', 'waste' => 5],
                        ['mat' => 'MAT-CHS-02', 'qty' => 0.030, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-POT-01', 'qty' => 0.150, 'unit' => 'pack', 'waste' => 0],
                        ['mat' => 'MAT-VEG-01', 'qty' => 0.030, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-STK-06',
                    'name' => 'Crispy Chicken Steak with Blackpepper Gravy',
                    'cat' => 'Mains & Steaks',
                    'sell' => 88000,
                    'recipe' => [
                        ['mat' => 'MAT-MEA-03', 'qty' => 0.200, 'unit' => 'kg', 'waste' => 5],
                        ['mat' => 'MAT-POT-01', 'qty' => 0.120, 'unit' => 'pack', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-STK-07',
                    'name' => 'Wagyu Fried Rice with Sunny Side Up',
                    'cat' => 'Mains & Steaks',
                    'sell' => 95000,
                    'recipe' => [
                        ['mat' => 'MAT-MEA-01', 'qty' => 0.070, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-RIC-01', 'qty' => 0.180, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-EGG-01', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                        ['mat' => 'MAT-VEG-04', 'qty' => 0.015, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],

                // Pasta & Risotto (5 SKUs)
                [
                    'code' => 'FNB-PAS-01',
                    'name' => 'Spaghetti Carbonara Tradizionale (Guanciale Style)',
                    'cat' => 'Pasta & Risotto',
                    'sell' => 98000,
                    'recipe' => [
                        ['mat' => 'MAT-PAS-01', 'qty' => 0.200, 'unit' => 'pack', 'waste' => 0], // 100g pasta
                        ['mat' => 'MAT-CHS-01', 'qty' => 0.030, 'unit' => 'kg', 'waste' => 0],   // parmesan
                        ['mat' => 'MAT-EGG-01', 'qty' => 2, 'unit' => 'pcs', 'waste' => 0],
                        ['mat' => 'MAT-CRM-01', 'qty' => 0.030, 'unit' => 'l', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-PAS-02',
                    'name' => 'Fettuccine Truffle Wild Mushroom',
                    'cat' => 'Pasta & Risotto',
                    'sell' => 110000,
                    'recipe' => [
                        ['mat' => 'MAT-PAS-02', 'qty' => 0.200, 'unit' => 'pack', 'waste' => 0],
                        ['mat' => 'MAT-TRF-01', 'qty' => 10, 'unit' => 'ml', 'waste' => 0],
                        ['mat' => 'MAT-CRM-01', 'qty' => 0.060, 'unit' => 'l', 'waste' => 0],
                        ['mat' => 'MAT-CHS-01', 'qty' => 0.025, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-PAS-03',
                    'name' => 'Spaghetti Aglio Olio e Peperoncino with Prawn',
                    'cat' => 'Pasta & Risotto',
                    'sell' => 88000,
                    'recipe' => [
                        ['mat' => 'MAT-PAS-01', 'qty' => 0.200, 'unit' => 'pack', 'waste' => 0],
                        ['mat' => 'MAT-OIL-01', 'qty' => 30, 'unit' => 'ml', 'waste' => 0],
                        ['mat' => 'MAT-VEG-04', 'qty' => 0.025, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-PAS-04',
                    'name' => 'Penne Bolognese Al Forno',
                    'cat' => 'Pasta & Risotto',
                    'sell' => 92000,
                    'recipe' => [
                        ['mat' => 'MAT-PAS-01', 'qty' => 0.200, 'unit' => 'pack', 'waste' => 0],
                        ['mat' => 'MAT-MEA-02', 'qty' => 0.060, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-CHS-02', 'qty' => 0.030, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-PAS-05',
                    'name' => 'Creamy Salmon Fettuccine with Spinach',
                    'cat' => 'Pasta & Risotto',
                    'sell' => 120000,
                    'recipe' => [
                        ['mat' => 'MAT-PAS-02', 'qty' => 0.200, 'unit' => 'pack', 'waste' => 0],
                        ['mat' => 'MAT-FIS-01', 'qty' => 0.080, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-CRM-01', 'qty' => 0.060, 'unit' => 'l', 'waste' => 0],
                    ],
                ],

                // Appetizers (4 SKUs)
                [
                    'code' => 'FNB-APP-01',
                    'name' => 'Truffle Parmesan French Fries',
                    'cat' => 'Appetizers & Bites',
                    'sell' => 52000,
                    'recipe' => [
                        ['mat' => 'MAT-POT-01', 'qty' => 0.150, 'unit' => 'pack', 'waste' => 0], // ~375g fries
                        ['mat' => 'MAT-TRF-01', 'qty' => 5, 'unit' => 'ml', 'waste' => 0],
                        ['mat' => 'MAT-CHS-01', 'qty' => 0.020, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-APP-02',
                    'name' => 'Crispy Chicken Wings with Spicy Honey Butter',
                    'cat' => 'Appetizers & Bites',
                    'sell' => 58000,
                    'recipe' => [
                        ['mat' => 'MAT-MEA-03', 'qty' => 0.250, 'unit' => 'kg', 'waste' => 5],
                        ['mat' => 'MAT-BUT-01', 'qty' => 0.020, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-APP-03',
                    'name' => 'Classic Caesar Salad with Grilled Chicken',
                    'cat' => 'Appetizers & Bites',
                    'sell' => 68000,
                    'recipe' => [
                        ['mat' => 'MAT-VEG-01', 'qty' => 0.100, 'unit' => 'kg', 'waste' => 5],
                        ['mat' => 'MAT-VEG-02', 'qty' => 0.050, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-MEA-03', 'qty' => 0.080, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-CHS-01', 'qty' => 0.015, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-APP-04',
                    'name' => 'Calamari Fritti with Tartar Dip',
                    'cat' => 'Appetizers & Bites',
                    'sell' => 62000,
                    'recipe' => [
                        ['mat' => 'MAT-FLR-01', 'qty' => 0.050, 'unit' => 'kg', 'waste' => 0],
                    ],
                ],

                // Desserts (3 SKUs)
                [
                    'code' => 'FNB-DES-01',
                    'name' => 'Classic Italian Tiramisu with Mascarpone',
                    'cat' => 'Desserts & Sweet Bites',
                    'sell' => 55000,
                    'recipe' => [
                        ['mat' => 'MAT-COF-01', 'qty' => 0.015, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-CHO-01', 'qty' => 0.020, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-CRM-01', 'qty' => 0.040, 'unit' => 'l', 'waste' => 0],
                        ['mat' => 'MAT-EGG-01', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-DES-02',
                    'name' => 'Warm Chocolate Lava Cake with Vanilla Gelato',
                    'cat' => 'Desserts & Sweet Bites',
                    'sell' => 55000,
                    'recipe' => [
                        ['mat' => 'MAT-CHO-01', 'qty' => 0.050, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-BUT-01', 'qty' => 0.030, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-EGG-01', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],
                [
                    'code' => 'FNB-DES-03',
                    'name' => 'Basque Burnt Cheesecake Slice',
                    'cat' => 'Desserts & Sweet Bites',
                    'sell' => 48000,
                    'recipe' => [
                        ['mat' => 'MAT-CHS-02', 'qty' => 0.050, 'unit' => 'kg', 'waste' => 0],
                        ['mat' => 'MAT-CRM-01', 'qty' => 0.040, 'unit' => 'l', 'waste' => 0],
                        ['mat' => 'MAT-EGG-01', 'qty' => 1, 'unit' => 'pcs', 'waste' => 0],
                    ],
                ],
            ];

            $productModels = [];
            foreach ($menuCatalog as $pSpec) {
                $catId = $catModels[$pSpec['cat']]->id ?? null;
                $unitObj = $units['porsi'] ?? $units['pcs'];

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
                    'base_cost' => 0, // will be updated from BOM below
                    'type' => Product::TYPE_GOODS,
                    'bom_consumption_type' => Product::BOM_CONSUMPTION_ON_SALE,
                    'show_in_pos' => true,
                    'show_in_sales_order' => true,
                    'show_in_website' => true,
                    'business_type_hint' => 'fnb_resto',
                    'description' => "Menu signature COOCA Bistro & Coffee: {$pSpec['name']}",
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

                // Cost Model & Recipe BOM
                $costModel = CostModel::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'product_id' => $product->id,
                        'slug' => 'hpp-' . Str::slug($pSpec['name']),
                    ],
                    [
                        'name' => 'Resep HPP ' . $pSpec['name'],
                        'method' => CostModel::METHOD_RECIPE_BOM,
                        'is_active' => true,
                    ]
                );

                $bomHeader = $costModel->bomHeaders()->firstOrCreate(
                    ['cost_model_id' => $costModel->id],
                    [
                        'name' => 'Resep Standar ' . $pSpec['name'],
                        'type' => BomHeader::TYPE_RECIPE,
                        'level' => 1,
                    ]
                );

                $totalCalculatedHpp = 0.0;
                foreach ($pSpec['recipe'] as $rItem) {
                    if (isset($materialModels[$rItem['mat']])) {
                        $matObj = $materialModels[$rItem['mat']];
                        $rUnit = $units[$rItem['unit']] ?? $units['pcs'];
                        $qty = (float) $rItem['qty'];
                        $waste = (float) ($rItem['waste'] ?? 0);

                        $bomHeader->items()->updateOrCreate(
                            ['bom_header_id' => $bomHeader->id, 'material_id' => $matObj->id],
                            [
                                'unit_id' => $rUnit->id,
                                'quantity' => $qty,
                                'waste_percentage' => $waste,
                            ]
                        );

                        $price = (float) ($matObj->latestPrice?->purchase_price ?? 0);
                        $totalCalculatedHpp += ($qty * (1 + $waste / 100) * $price);
                    }
                }

                $finalHpp = round($totalCalculatedHpp, 2);
                $product->update(['base_cost' => $finalHpp]);
                $productModels[$pSpec['code']] = $product;
            }

            // 10. Modifiers (Groups & Options)
            $modifierGroupsData = [
                [
                    'name' => 'Tingkat Kemanisan (Sweetness)',
                    'description' => 'Pilihan kadar gula untuk racikan kopi dan minuman.',
                    'selection_type' => 'single',
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Normal Sweetness (100%)', 'price_delta' => 0],
                        ['name' => 'Less Sweet (70%)', 'price_delta' => 0],
                        ['name' => 'Low Sugar (30%)', 'price_delta' => 0],
                        ['name' => 'No Sugar Added (0%)', 'price_delta' => 0],
                    ],
                    'applied_to_prefixes' => ['FNB-COF-', 'FNB-TEA-', 'FNB-CHO-'],
                ],
                [
                    'name' => 'Pilihan Susu (Dairy / Plant-Based)',
                    'description' => 'Ganti susu sapi reguler dengan pilihan plant-based.',
                    'selection_type' => 'single',
                    'is_required' => false,
                    'options' => [
                        ['name' => 'Fresh Milk Greenfields (Standard)', 'price_delta' => 0],
                        ['name' => 'Oat Milk Barista Oatside', 'price_delta' => 8000],
                        ['name' => 'Almond Milk Artisan', 'price_delta' => 10000],
                    ],
                    'applied_to_prefixes' => ['FNB-COF-02', 'FNB-COF-03', 'FNB-COF-04', 'FNB-COF-05', 'FNB-TEA-01'],
                ],
                [
                    'name' => 'Extra Espresso Shot',
                    'description' => 'Tambah shot espresso gayo aromatik.',
                    'selection_type' => 'single',
                    'is_required' => false,
                    'options' => [
                        ['name' => 'Single Shot Espresso (+1)', 'price_delta' => 7000],
                        ['name' => 'Double Shot Espresso (+2)', 'price_delta' => 12000],
                    ],
                    'applied_to_prefixes' => ['FNB-COF-'],
                ],
                [
                    'name' => 'Tingkat Kematangan Steak (Doneness)',
                    'description' => 'Tingkat kematangan panggangan daging steak.',
                    'selection_type' => 'single',
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Medium Rare (Juicy & Tender)', 'price_delta' => 0],
                        ['name' => 'Medium (Recommended)', 'price_delta' => 0],
                        ['name' => 'Medium Well', 'price_delta' => 0],
                        ['name' => 'Well Done', 'price_delta' => 0],
                    ],
                    'applied_to_prefixes' => ['FNB-STK-01', 'FNB-STK-02'],
                ],
                [
                    'name' => 'Pilihan Side Dish Pendamping',
                    'description' => 'Pilihan karbohidrat pendamping hidangan steak.',
                    'selection_type' => 'single',
                    'is_required' => true,
                    'options' => [
                        ['name' => 'Crispy French Fries with Herb Salt', 'price_delta' => 0],
                        ['name' => 'Creamy Truffle Mashed Potato', 'price_delta' => 10000],
                        ['name' => 'Garlic Butter Herb Rice', 'price_delta' => 5000],
                    ],
                    'applied_to_prefixes' => ['FNB-STK-01', 'FNB-STK-02', 'FNB-STK-03', 'FNB-STK-04'],
                ],
            ];

            foreach ($modifierGroupsData as $mgData) {
                $group = ModifierGroup::updateOrCreate(
                    ['business_id' => $business->id, 'name' => $mgData['name']],
                    [
                        'description' => $mgData['description'],
                        'selection_type' => $mgData['selection_type'],
                        'min_selection' => $mgData['is_required'] ? 1 : 0,
                        'max_selection' => 1,
                        'is_required' => $mgData['is_required'],
                        'is_active' => true,
                    ]
                );

                foreach ($mgData['options'] as $idx => $optData) {
                    ModifierOption::updateOrCreate(
                        ['modifier_group_id' => $group->id, 'name' => $optData['name']],
                        [
                            'price_delta' => $optData['price_delta'],
                            'sort_order' => $idx + 1,
                            'is_active' => true,
                        ]
                    );
                }

                // Link to products
                foreach ($productModels as $code => $prod) {
                    $shouldAttach = false;
                    foreach ($mgData['applied_to_prefixes'] as $prefix) {
                        if (str_starts_with($code, $prefix)) {
                            $shouldAttach = true;
                            break;
                        }
                    }

                    if ($shouldAttach) {
                        ProductModifierGroup::firstOrCreate(
                            [
                                'product_id' => $prod->id,
                                'modifier_group_id' => $group->id,
                            ],
                            ['sort_order' => 1]
                        );
                    }
                }
            }

            // 11. Dine-in Tables (pos_tables) across Outlets
            $tableDefinitions = [
                // Senopati Outlet (10 tables)
                ['loc' => 'senopati', 'num' => 'T-01', 'name' => 'Meja Indoor 01', 'cap' => 2, 'status' => 'available'],
                ['loc' => 'senopati', 'num' => 'T-02', 'name' => 'Meja Indoor 02', 'cap' => 2, 'status' => 'occupied'],
                ['loc' => 'senopati', 'num' => 'T-03', 'name' => 'Meja Indoor 03', 'cap' => 4, 'status' => 'available'],
                ['loc' => 'senopati', 'num' => 'T-04', 'name' => 'Meja Indoor 04', 'cap' => 4, 'status' => 'occupied'],
                ['loc' => 'senopati', 'num' => 'T-05', 'name' => 'Meja Indoor 05', 'cap' => 6, 'status' => 'available'],
                ['loc' => 'senopati', 'num' => 'O-01', 'name' => 'Meja Outdoor Smoking 01', 'cap' => 4, 'status' => 'available'],
                ['loc' => 'senopati', 'num' => 'O-02', 'name' => 'Meja Outdoor Smoking 02', 'cap' => 4, 'status' => 'available'],
                ['loc' => 'senopati', 'num' => 'O-03', 'name' => 'Meja Outdoor Terrace 03', 'cap' => 4, 'status' => 'occupied'],
                ['loc' => 'senopati', 'num' => 'VIP-01', 'name' => 'Ruang Privat VIP Room 1', 'cap' => 10, 'status' => 'available'],
                ['loc' => 'senopati', 'num' => 'VIP-02', 'name' => 'Ruang Privat VIP Room 2', 'cap' => 12, 'status' => 'available'],

                // Grand Indonesia (4 tables)
                ['loc' => 'grand-indonesia', 'num' => 'GI-01', 'name' => 'Meja GI Center 01', 'cap' => 2, 'status' => 'available'],
                ['loc' => 'grand-indonesia', 'num' => 'GI-02', 'name' => 'Meja GI Center 02', 'cap' => 4, 'status' => 'occupied'],
                ['loc' => 'grand-indonesia', 'num' => 'GI-03', 'name' => 'Meja GI Window 03', 'cap' => 4, 'status' => 'available'],
                ['loc' => 'grand-indonesia', 'num' => 'GI-04', 'name' => 'Meja GI Sofa 04', 'cap' => 6, 'status' => 'available'],

                // BSD GOP (4 tables)
                ['loc' => 'bsd-gop', 'num' => 'BSD-01', 'name' => 'Meja Kanopi Breeze 01', 'cap' => 4, 'status' => 'available'],
                ['loc' => 'bsd-gop', 'num' => 'BSD-02', 'name' => 'Meja Kanopi Breeze 02', 'cap' => 4, 'status' => 'occupied'],
                ['loc' => 'bsd-gop', 'num' => 'BSD-03', 'name' => 'Meja Garden Outdoor 03', 'cap' => 6, 'status' => 'available'],
                ['loc' => 'bsd-gop', 'num' => 'BSD-04', 'name' => 'Meja Meeting Pod 04', 'cap' => 8, 'status' => 'available'],
            ];

            $tables = [];
            foreach ($tableDefinitions as $tDef) {
                $locObj = $locations[$tDef['loc']];
                $tables[$tDef['num']] = PosTable::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'location_id' => $locObj->id,
                        'table_number' => $tDef['num'],
                    ],
                    [
                        'name' => $tDef['name'],
                        'capacity' => $tDef['cap'],
                        'status' => $tDef['status'],
                        'qr_token' => 'QR-' . strtoupper(Str::slug($tDef['loc'])) . '-' . $tDef['num'],
                        'is_active' => true,
                    ]
                );
            }

            // 12. POS Printers & Routing Rules
            $printerSpecs = [
                ['name' => 'Printer Kasir Utama (Cashier)', 'station' => 'cashier', 'usage' => PosPrinter::USAGE_CASHIER_RECEIPT, 'ip' => '192.168.1.200', 'is_default' => true],
                ['name' => 'Printer Dapur Panas (Kitchen Hot)', 'station' => 'kitchen', 'usage' => PosPrinter::USAGE_KITCHEN_ORDER, 'ip' => '192.168.1.201', 'is_default' => false],
                ['name' => 'Printer Barista & Minuman (Bar)', 'station' => 'bar', 'usage' => PosPrinter::USAGE_BAR_ORDER, 'ip' => '192.168.1.202', 'is_default' => false],
                ['name' => 'Printer Pastry & Dessert', 'station' => 'pastry', 'usage' => PosPrinter::USAGE_PASTRY_ORDER, 'ip' => '192.168.1.203', 'is_default' => false],
            ];

            $printers = [];
            foreach ($printerSpecs as $prSpec) {
                $printer = PosPrinter::updateOrCreate(
                    ['business_id' => $business->id, 'location_id' => $primaryLoc->id, 'name' => $prSpec['name']],
                    [
                        'alias' => $prSpec['name'],
                        'station' => $prSpec['station'],
                        'connection_type' => 'lan',
                        'interface_address' => $prSpec['ip'],
                        'port' => 9100,
                        'paper_width' => 80,
                        'copies' => 1,
                        'auto_cut' => true,
                        'open_cash_drawer' => $prSpec['station'] === 'cashier',
                        'is_active' => true,
                        'is_default' => $prSpec['is_default'],
                        'assigned_usages' => [$prSpec['usage']],
                    ]
                );
                $printers[$prSpec['station']] = $printer;
            }

            // Routing rules
            $ruleSpecs = [
                ['printer' => 'cashier', 'doc_type' => PosPrinterRoutingRule::DOC_CUSTOMER_RECEIPT, 'dept' => PosPrinterRoutingRule::DEPT_GENERAL],
                ['printer' => 'kitchen', 'doc_type' => PosPrinterRoutingRule::DOC_KITCHEN_TICKET, 'dept' => PosPrinterRoutingRule::DEPT_FOOD],
                ['printer' => 'bar', 'doc_type' => PosPrinterRoutingRule::DOC_BAR_TICKET, 'dept' => PosPrinterRoutingRule::DEPT_BEVERAGE],
                ['printer' => 'pastry', 'doc_type' => PosPrinterRoutingRule::DOC_PASTRY_TICKET, 'dept' => PosPrinterRoutingRule::DEPT_FOOD],
            ];

            foreach ($ruleSpecs as $idx => $rSpec) {
                $prModel = $printers[$rSpec['printer']];
                PosPrinterRoutingRule::updateOrCreate(
                    [
                        'business_id' => $business->id,
                        'location_id' => $primaryLoc->id,
                        'printer_id' => $prModel->id,
                        'document_type' => $rSpec['doc_type'],
                    ],
                    [
                        'department' => $rSpec['dept'],
                        'copies' => 1,
                        'is_active' => true,
                        'priority' => $idx + 1,
                    ]
                );
            }

            // 13. Customers (20 Dine-in & VIP Guests)
            $customerNames = [
                'Nadia Safitri (VIP Club)', 'Bramantyo Wardhana', 'Amanda Gabriella', 'Rafi Ahmad Fauzi',
                'Jessica Iskandar', 'Dimas Anggara', 'Patricia Gouw', 'Jonathan Christie',
                'Gisella Anastasia', 'Reza Rahadian', 'Tara Basro', 'Chicco Jerikho',
                'Mikha Tambayong', 'Afgan Syahreza', 'Raisa Andriana', 'Isyana Sarasvati',
                'Vidi Aldiano', 'Maudy Ayunda', 'Nicholas Saputra', 'Chelsea Islan',
            ];

            $customers = [];
            foreach ($customerNames as $idx => $cName) {
                $customers[] = Customer::updateOrCreate(
                    ['business_id' => $business->id, 'name' => $cName],
                    [
                        'slug' => Str::slug($cName),
                        'phone' => '0812-8800-' . str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT),
                        'email' => Str::slug($cName) . '@bistroguest.test',
                        'segment' => $idx < 5 ? 'vip' : 'regular',
                        'billing_address' => 'Senopati / Dharmawangsa Area, Jakarta Selatan',
                        'is_active' => true,
                    ]
                );
            }

            // 14. POS Shift & Dine-in Orders (with Balanced Double-Entry Auto-Journaling)
            $register = PosRegister::updateOrCreate(
                ['business_id' => $business->id, 'location_id' => $primaryLoc->id, 'code' => 'REG-SNT-01'],
                [
                    'name' => 'Kasir Utama Senopati (Bar & Dining)',
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
                    'opened_at' => Carbon::now()->subHours(10),
                    'closed_at' => Carbon::now()->subHour(),
                    'opening_cash' => 500000,
                    'closing_cash_actual' => 500000 + 850000,
                    'closing_cash_expected' => 500000 + 850000,
                    'cash_difference' => 0,
                    'total_cash_sales' => 850000,
                    'total_non_cash_sales' => 3200000,
                ]
            );

            // Sample Dine-in Orders with Tax & Service Charge
            $sampleDineInOrders = [
                [
                    'order_number' => 'POS-FNB-20260101-001',
                    'table' => $tables['T-02'],
                    'customer' => $customers[0], // VIP
                    'payment_method' => PosOrderPayment::METHOD_QRIS,
                    'items' => [
                        ['code' => 'FNB-STK-01', 'qty' => 2], // 2x Wagyu MB5 (295k)
                        ['code' => 'FNB-PAS-02', 'qty' => 1], // 1x Truffle Fettuccine (110k)
                        ['code' => 'FNB-COF-02', 'qty' => 2], // 2x Cafe Latte (42k)
                        ['code' => 'FNB-DES-01', 'qty' => 1], // 1x Tiramisu (55k)
                    ],
                ],
                [
                    'order_number' => 'POS-FNB-20260101-002',
                    'table' => $tables['T-04'],
                    'customer' => $customers[1],
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'FNB-STK-03', 'qty' => 2], // 2x Pan-Seared Salmon (165k)
                        ['code' => 'FNB-APP-01', 'qty' => 1], // 1x Truffle Fries (52k)
                        ['code' => 'FNB-MCK-01', 'qty' => 2], // 2x Sunset Spritz (45k)
                    ],
                ],
                [
                    'order_number' => 'POS-FNB-20260101-003',
                    'table' => $tables['O-03'],
                    'customer' => $customers[2],
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'items' => [
                        ['code' => 'FNB-COF-06', 'qty' => 3], // 3x Kopi Susu Aren (35k)
                        ['code' => 'FNB-APP-02', 'qty' => 1], // 1x Chicken Wings (58k)
                        ['code' => 'FNB-DES-02', 'qty' => 2], // 2x Lava Cake (55k)
                    ],
                ],
                [
                    'order_number' => 'POS-FNB-20260101-004',
                    'table' => $tables['T-01'],
                    'customer' => $customers[3],
                    'payment_method' => PosOrderPayment::METHOD_QRIS,
                    'items' => [
                        ['code' => 'FNB-PAS-01', 'qty' => 1], // 1x Carbonara (98k)
                        ['code' => 'FNB-COF-03', 'qty' => 1], // 1x Cappuccino (42k)
                    ],
                ],
                [
                    'order_number' => 'POS-FNB-20260101-005',
                    'table' => $tables['VIP-01'],
                    'customer' => $customers[4],
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'FNB-STK-01', 'qty' => 4], // 4x Wagyu MB5 (295k)
                        ['code' => 'FNB-PAS-02', 'qty' => 2], // 2x Truffle Fettuccine (110k)
                        ['code' => 'FNB-APP-01', 'qty' => 2], // 2x Truffle Fries (52k)
                        ['code' => 'FNB-MCK-02', 'qty' => 4], // 4x Berry Basil (45k)
                        ['code' => 'FNB-DES-01', 'qty' => 2], // 2x Tiramisu (55k)
                    ],
                ],
                [
                    'order_number' => 'POS-FNB-20260101-006',
                    'table' => $tables['T-03'],
                    'customer' => $customers[5],
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'items' => [
                        ['code' => 'FNB-STK-05', 'qty' => 2], // 2x Wagyu Burger (125k)
                        ['code' => 'FNB-TEA-01', 'qty' => 2], // 2x Matcha Latte (48k)
                    ],
                ],
                [
                    'order_number' => 'POS-FNB-20260101-007',
                    'table' => $tables['T-05'],
                    'customer' => $customers[6],
                    'payment_method' => PosOrderPayment::METHOD_QRIS,
                    'items' => [
                        ['code' => 'FNB-STK-07', 'qty' => 2], // 2x Wagyu Fried Rice (95k)
                        ['code' => 'FNB-COF-04', 'qty' => 2], // 2x Spanish Latte (48k)
                    ],
                ],
                [
                    'order_number' => 'POS-FNB-20260101-008',
                    'table' => $tables['O-01'],
                    'customer' => $customers[7],
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'items' => [
                        ['code' => 'FNB-COF-01', 'qty' => 2], // 2x Espresso (28k)
                        ['code' => 'FNB-DES-03', 'qty' => 2], // 2x Basque Cheesecake (48k)
                    ],
                ],
            ];

            foreach ($sampleDineInOrders as $sOrder) {
                $subtotal = 0.0;
                $totalHpp = 0.0;
                $lineItemsData = [];

                foreach ($sOrder['items'] as $itemSpec) {
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

                // 10% PB1 Restaurant Tax and 5% Service Charge
                $serviceCharge = round($subtotal * 0.05);
                $taxAmount = round(($subtotal + $serviceCharge) * 0.10);
                $totalAmount = $subtotal + $serviceCharge + $taxAmount;

                $order = PosOrder::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'order_number' => $sOrder['order_number'],
                    ],
                    [
                        'location_id' => $primaryLoc->id,
                        'pos_shift_id' => $shift->id,
                        'pos_register_id' => $register->id,
                        'pos_table_id' => $sOrder['table']->id,
                        'table_or_reference' => $sOrder['table']->table_number,
                        'user_id' => $userModels['cashier']->id,
                        'customer_id' => $sOrder['customer']->id,
                        'order_date' => Carbon::now()->toDateString(),
                        'status' => PosOrder::STATUS_COMPLETED,
                        'order_type' => 'dine_in',
                        'sales_channel' => 'pos',
                        'customer_name_guest' => $sOrder['customer']->name,
                        'subtotal' => $subtotal,
                        'service_charge_percentage' => 5,
                        'service_charge_amount' => $serviceCharge,
                        'tax_percentage' => 10,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'paid_amount' => $totalAmount,
                        'change_amount' => 0,
                        'total_hpp_cost' => $totalHpp,
                        'total_gross_profit' => $totalAmount - $totalHpp,
                        'notes' => "Dine-in Pesanan Meja {$sOrder['table']->table_number}",
                    ]
                );

                if ($order->wasRecentlyCreated) {
                    foreach ($lineItemsData as $line) {
                        PosOrderItem::create(array_merge($line, ['pos_order_id' => $order->id]));
                    }

                    PosOrderPayment::create([
                        'pos_order_id' => $order->id,
                        'payment_method' => $sOrder['payment_method'],
                        'amount' => $totalAmount,
                        'reference_number' => 'PAY-' . $order->order_number,
                        'status' => 'settled',
                    ]);

                    // Automatically Record Balanced SAK EMKM Double-Entry Journal
                    $this->journalService->recordPosSaleJournal($order);
                }
            }
        });

        $this->command?->info('✓ Sukses seeding COOCA Bistro & Coffee (Food & Beverage)!');
    }
}
