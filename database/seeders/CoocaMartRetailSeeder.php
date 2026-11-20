<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Accounting\AutoJournalService;
use App\Domain\Template\BusinessTemplateService;
use App\Models\Business;
use App\Models\BusinessTypeTemplate;
use App\Models\ChartOfAccount;
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
use App\Models\SupplierPayment;
use App\Models\Unit;
use App\Models\User;
use App\Support\Context;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CoocaMartRetailSeeder extends Seeder
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
        $this->command?->info('Seeding Retail & Distribution: COOCA Mart Indonesia...');

        DB::transaction(function () {
            // 1. Business Creation
            $business = Business::updateOrCreate(
                ['slug' => 'cooca-mart-indonesia'],
                [
                    'name' => 'COOCA Mart Indonesia',
                    'currency' => 'IDR',
                    'currency_precision' => 0,
                    'rounding_strategy' => Business::ROUNDING_ROUND_100,
                    'allow_negative_stock' => false,
                    'phone' => '0811-2233-4401',
                    'email' => 'kontak@coocamart.id',
                    'address' => 'Jl. Boulevard Kelapa Gading Blok LA No. 10, Jakarta Utara',
                    'tax_identification_number' => '01.234.567.8-011.000',
                    'bank_name' => 'Bank Central Asia (BCA)',
                    'bank_account_number' => '8800112201',
                    'bank_account_holder' => 'PT Cooca Mart Indonesia',
                    'pos_enable_tax' => true,
                    'pos_tax_percent' => 11,
                    'template_code' => 'distributor_fmcg',
                    'industry_category' => 'retail',
                    'is_demo' => true,
                    'is_active' => true,
                ]
            );

            Context::setBusiness($business);
            $this->journalService->ensureStandardAccounts($business);

            // 2. Business Type Template
            $template = BusinessTypeTemplate::where('code', 'distributor_fmcg')->first();
            if ($template) {
                $this->templateService->apply($business, $template);
            }

            // 3. User Accounts & Roles
            $roles = Role::whereNull('business_id')->get()->keyBy('slug');

            $demoUsers = [
                ['name' => 'Budi Santoso (Owner COOCA Mart)', 'email' => 'owner@coocamart.id', 'role' => 'owner'],
                ['name' => 'Hendrik Kusuma (Store Manager)', 'email' => 'manager@coocamart.id', 'role' => 'manager'],
                ['name' => 'Siti Nurhaliza (Kasir Kelapa Gading)', 'email' => 'cashier@coocamart.id', 'role' => 'cashier'],
                ['name' => 'Bambang Irawan (Kepala Gudang)', 'email' => 'warehouse@coocamart.id', 'role' => 'warehouse'],
                ['name' => 'Dewi Lestari (Finance & Accounting)', 'email' => 'finance@coocamart.id', 'role' => 'finance'],
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

            // 4. Locations (3 Branches)
            $locationSpecs = [
                [
                    'name' => 'Kelapa Gading (Cabang Pusat)',
                    'slug' => 'kelapa-gading',
                    'code' => 'LOC-KG',
                    'type' => 'branch',
                    'is_primary' => true,
                    'address' => 'Jl. Boulevard Kelapa Gading Blok LA No. 10, Jakarta Utara',
                    'phone' => '021-45870001',
                ],
                [
                    'name' => 'Puri Indah (Cabang Barat)',
                    'slug' => 'puri-indah',
                    'code' => 'LOC-PI',
                    'type' => 'branch',
                    'is_primary' => false,
                    'address' => 'Jl. Puri Lingkar Luar No. 88, Kembangan, Jakarta Barat',
                    'phone' => '021-58300002',
                ],
                [
                    'name' => 'Kemang (Cabang Selatan)',
                    'slug' => 'kemang',
                    'code' => 'LOC-KM',
                    'type' => 'branch',
                    'is_primary' => false,
                    'address' => 'Jl. Kemang Raya No. 24, Mampang Prapatan, Jakarta Selatan',
                    'phone' => '021-71900003',
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

            $primaryLoc = $locations['kelapa-gading'];

            // 5. Units Lookup
            $units = Unit::all()->keyBy('code');

            // 6. Product Categories
            $categoryNames = [
                'Sembako & Bahan Pokok' => 'Beras, minyak goreng, gula, tepung, dan bumbu dapur kebutuhan pokok.',
                'Makanan Ringan & Biskuit' => 'Snack, biskuit, wafer, kacang, dan permen kemasan.',
                'Minuman Dalam Kemasan' => 'Air mineral, teh kemasan, susu cair, jus, dan minuman isotonik.',
                'Produk Susu & Olahan' => 'Susu kental manis, susu bubuk, keju olahan, dan mentega.',
                'Kebutuhan Rumah Tangga' => 'Deterjen, sabun pencuci piring, pembersih lantai, dan tisu.',
                'Perawatan Diri & Mandi' => 'Sabun mandi, shampo, pasta gigi, sikat gigi, dan deodoran.',
            ];

            $categories = [];
            foreach ($categoryNames as $catName => $catDesc) {
                $categories[$catName] = ProductCategory::updateOrCreate(
                    ['business_id' => $business->id, 'slug' => Str::slug($catName)],
                    ['name' => $catName, 'description' => $catDesc]
                );
            }

            // 7. Suppliers (12 FMCG Suppliers)
            $supplierSpecs = [
                ['name' => 'PT Indofood Sukses Makmur Tbk', 'contact' => 'Handoko Salim', 'phone' => '021-57958822', 'email' => 'sales@indofood.test', 'address' => 'Sudirman Plaza, Indofood Tower, Jakarta'],
                ['name' => 'PT Unilever Indonesia Tbk', 'contact' => 'Ratna Dewi', 'phone' => '021-80827000', 'email' => 'order@unilever.test', 'address' => 'BSD Green Office Park Kav 3, Tangerang'],
                ['name' => 'PT Mayora Indah Tbk', 'contact' => 'Alvin Susanto', 'phone' => '021-80658000', 'email' => 'distribution@mayora.test', 'address' => 'Gedung Mayora, Jl. Tomang Raya 21-23, Jakarta Barat'],
                ['name' => 'PT Wings Surya (Wings Group)', 'contact' => 'Gunawan Wibowo', 'phone' => '021-4602288', 'email' => 'b2b@wingscorp.test', 'address' => 'Jl. Tipar Cakung Kav. F 5-7, Jakarta Timur'],
                ['name' => 'PT Frisian Flag Indonesia', 'contact' => 'Rina Hartati', 'phone' => '021-8410945', 'email' => 'order@frisianflag.test', 'address' => 'Jl. Raya Bogor Km 5, Pasar Rebo, Jakarta Timur'],
                ['name' => 'PT Nestle Indonesia', 'contact' => 'Didik Suryanto', 'phone' => '021-78836000', 'email' => 'distributor@nestle.test', 'address' => 'Arkadia Green Park Tower B, Jl TB Simatupang, Jakarta'],
                ['name' => 'PT Sumber Alfaria Trijaya Tbk', 'contact' => 'Heru Prasetyo', 'phone' => '021-55755966', 'email' => 'wholesale@alfamart.test', 'address' => 'Alfa Tower, Alam Sutera, Tangerang'],
                ['name' => 'PT Bogasari Flour Mills', 'contact' => 'Antonius Wijaya', 'phone' => '021-43913000', 'email' => 'order@bogasari.test', 'address' => 'Jl. Raya Cilincing No. 1, Tanjung Priok, Jakarta'],
                ['name' => 'PT Sinar Mas Agro Resources (SMART)', 'contact' => 'Lukman Hakim', 'phone' => '021-50338899', 'email' => 'sales@smart-tbk.test', 'address' => 'Sinar Mas Land Plaza Menara 2, Thamrin, Jakarta'],
                ['name' => 'PT Ultra Jaya Milk Industry Tbk', 'contact' => 'Yudi Pratama', 'phone' => '022-86700700', 'email' => 'commercial@ultrajaya.test', 'address' => 'Jl. Raya Cimareme 131, Padalarang, Bandung'],
                ['name' => 'PT Kao Indonesia', 'contact' => 'Maya Anggraini', 'phone' => '021-7982245', 'email' => 'fmcg@kao.test', 'address' => 'Jl. MT Haryono Kav 39-40, Pancoran, Jakarta Selatan'],
                ['name' => 'PT Sayap Mas Utama', 'contact' => 'Budi Hermanto', 'phone' => '021-4602289', 'email' => 'order@sayapmas.test', 'address' => 'Kawasan Industri Pulo Gadung, Jakarta Timur'],
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

            // 8. Retail Products (38 SKUs with Wholesale Tiers)
            $productCatalog = [
                // Sembako (7 SKUs)
                ['code' => 'RTL-SEM-001', 'name' => 'Beras Pandan Wangi Premium 5kg', 'cat' => 'Sembako & Bahan Pokok', 'unit' => 'pack', 'cost' => 68000, 'sell' => 79000, 'ws_qty' => 10, 'ws_price' => 74000, 'stock' => 120],
                ['code' => 'RTL-SEM-002', 'name' => 'Beras Ramos Setra Premium 5kg', 'cat' => 'Sembako & Bahan Pokok', 'unit' => 'pack', 'cost' => 64000, 'sell' => 74500, 'ws_qty' => 10, 'ws_price' => 70000, 'stock' => 150],
                ['code' => 'RTL-SEM-003', 'name' => 'Minyak Goreng Sania Pouch 2L', 'cat' => 'Sembako & Bahan Pokok', 'unit' => 'pcs', 'cost' => 31000, 'sell' => 36500, 'ws_qty' => 12, 'ws_price' => 34000, 'stock' => 200],
                ['code' => 'RTL-SEM-004', 'name' => 'Minyak Goreng Bimoli Spesial Pouch 2L', 'cat' => 'Sembako & Bahan Pokok', 'unit' => 'pcs', 'cost' => 32500, 'sell' => 38000, 'ws_qty' => 12, 'ws_price' => 35500, 'stock' => 180],
                ['code' => 'RTL-SEM-005', 'name' => 'Gula Pasir Gulaku Putih 1kg', 'cat' => 'Sembako & Bahan Pokok', 'unit' => 'pack', 'cost' => 14500, 'sell' => 17500, 'ws_qty' => 20, 'ws_price' => 16000, 'stock' => 250],
                ['code' => 'RTL-SEM-006', 'name' => 'Tepung Terigu Segitiga Biru 1kg', 'cat' => 'Sembako & Bahan Pokok', 'unit' => 'pack', 'cost' => 10500, 'sell' => 13000, 'ws_qty' => 24, 'ws_price' => 11800, 'stock' => 220],
                ['code' => 'RTL-SEM-007', 'name' => 'Garam Beryodium Cap Kapal 250g', 'cat' => 'Sembako & Bahan Pokok', 'unit' => 'pack', 'cost' => 2800, 'sell' => 4000, 'ws_qty' => 40, 'ws_price' => 3400, 'stock' => 300],

                // Makanan Ringan (7 SKUs)
                ['code' => 'RTL-SNK-001', 'name' => 'Indomie Goreng Original 85g [Karton 40pcs]', 'cat' => 'Makanan Ringan & Biskuit', 'unit' => 'box', 'cost' => 112000, 'sell' => 128000, 'ws_qty' => 5, 'ws_price' => 121000, 'stock' => 80],
                ['code' => 'RTL-SNK-002', 'name' => 'Indomie Kuah Ayam Bawang 75g [Karton 40pcs]', 'cat' => 'Makanan Ringan & Biskuit', 'unit' => 'box', 'cost' => 108000, 'sell' => 124000, 'ws_qty' => 5, 'ws_price' => 117000, 'stock' => 75],
                ['code' => 'RTL-SNK-003', 'name' => 'Biskuit Roma Kelapa 300g', 'cat' => 'Makanan Ringan & Biskuit', 'unit' => 'pack', 'cost' => 8500, 'sell' => 11500, 'ws_qty' => 12, 'ws_price' => 10000, 'stock' => 160],
                ['code' => 'RTL-SNK-004', 'name' => 'Oreo Vanilla Sandwich Cookie 133g', 'cat' => 'Makanan Ringan & Biskuit', 'unit' => 'pack', 'cost' => 8000, 'sell' => 10500, 'ws_qty' => 12, 'ws_price' => 9200, 'stock' => 190],
                ['code' => 'RTL-SNK-005', 'name' => 'Kacang Kulit Garuda Garing 400g', 'cat' => 'Makanan Ringan & Biskuit', 'unit' => 'pack', 'cost' => 26000, 'sell' => 32000, 'ws_qty' => 10, 'ws_price' => 29000, 'stock' => 110],
                ['code' => 'RTL-SNK-006', 'name' => 'Chitato Sapi Panggang 68g', 'cat' => 'Makanan Ringan & Biskuit', 'unit' => 'pack', 'cost' => 8500, 'sell' => 11500, 'ws_qty' => 15, 'ws_price' => 10000, 'stock' => 140],
                ['code' => 'RTL-SNK-007', 'name' => 'Wafer Tango Cokelat 176g', 'cat' => 'Makanan Ringan & Biskuit', 'unit' => 'pack', 'cost' => 6800, 'sell' => 9000, 'ws_qty' => 12, 'ws_price' => 7900, 'stock' => 170],

                // Minuman Kemasan (7 SKUs)
                ['code' => 'RTL-BVG-001', 'name' => 'Aqua Air Mineral Botol 600ml [Dus 24pcs]', 'cat' => 'Minuman Dalam Kemasan', 'unit' => 'box', 'cost' => 45000, 'sell' => 54000, 'ws_qty' => 10, 'ws_price' => 49500, 'stock' => 90],
                ['code' => 'RTL-BVG-002', 'name' => 'Aqua Air Mineral Galon 19L', 'cat' => 'Minuman Dalam Kemasan', 'unit' => 'pcs', 'cost' => 17500, 'sell' => 21000, 'ws_qty' => 15, 'ws_price' => 19500, 'stock' => 85],
                ['code' => 'RTL-BVG-003', 'name' => 'Le Minerale Botol 600ml [Dus 24pcs]', 'cat' => 'Minuman Dalam Kemasan', 'unit' => 'box', 'cost' => 42000, 'sell' => 51000, 'ws_qty' => 10, 'ws_price' => 46500, 'stock' => 95],
                ['code' => 'RTL-BVG-004', 'name' => 'Teh Botol Sosro Kotak 250ml [Dus 24pcs]', 'cat' => 'Minuman Dalam Kemasan', 'unit' => 'box', 'cost' => 62000, 'sell' => 74000, 'ws_qty' => 8, 'ws_price' => 68000, 'stock' => 65],
                ['code' => 'RTL-BVG-005', 'name' => 'Teh Pucuk Harum 350ml [Dus 24pcs]', 'cat' => 'Minuman Dalam Kemasan', 'unit' => 'box', 'cost' => 54000, 'sell' => 66000, 'ws_qty' => 8, 'ws_price' => 60000, 'stock' => 70],
                ['code' => 'RTL-BVG-006', 'name' => 'Pocari Sweat Botol 500ml [Dus 24pcs]', 'cat' => 'Minuman Dalam Kemasan', 'unit' => 'box', 'cost' => 140000, 'sell' => 165000, 'ws_qty' => 5, 'ws_price' => 152000, 'stock' => 50],
                ['code' => 'RTL-BVG-007', 'name' => 'Kopi Kapal Api Spesial Mix 24g [Renceng 10pcs]', 'cat' => 'Minuman Dalam Kemasan', 'unit' => 'pack', 'cost' => 12500, 'sell' => 16000, 'ws_qty' => 10, 'ws_price' => 14200, 'stock' => 130],

                // Produk Susu (6 SKUs)
                ['code' => 'RTL-DAI-001', 'name' => 'Frisian Flag Kental Manis Putih 370g', 'cat' => 'Produk Susu & Olahan', 'unit' => 'pcs', 'cost' => 10200, 'sell' => 13000, 'ws_qty' => 24, 'ws_price' => 11600, 'stock' => 180],
                ['code' => 'RTL-DAI-002', 'name' => 'Indomilk Kental Manis Cokelat 370g', 'cat' => 'Produk Susu & Olahan', 'unit' => 'pcs', 'cost' => 9800, 'sell' => 12500, 'ws_qty' => 24, 'ws_price' => 11200, 'stock' => 160],
                ['code' => 'RTL-DAI-003', 'name' => 'Ultra Milk UHT Full Cream 1L', 'cat' => 'Produk Susu & Olahan', 'unit' => 'pcs', 'cost' => 16500, 'sell' => 19800, 'ws_qty' => 12, 'ws_price' => 18200, 'stock' => 140],
                ['code' => 'RTL-DAI-004', 'name' => 'Ultra Milk UHT Cokelat 1L', 'cat' => 'Produk Susu & Olahan', 'unit' => 'pcs', 'cost' => 16500, 'sell' => 19800, 'ws_qty' => 12, 'ws_price' => 18200, 'stock' => 135],
                ['code' => 'RTL-DAI-005', 'name' => 'Keju Kraft Cheddar All-in-One 165g', 'cat' => 'Produk Susu & Olahan', 'unit' => 'pack', 'cost' => 18500, 'sell' => 23000, 'ws_qty' => 10, 'ws_price' => 20800, 'stock' => 110],
                ['code' => 'RTL-DAI-006', 'name' => 'Blue Band Serbaguna Margarin 200g', 'cat' => 'Produk Susu & Olahan', 'unit' => 'pack', 'cost' => 8200, 'sell' => 11000, 'ws_qty' => 15, 'ws_price' => 9500, 'stock' => 150],

                // Rumah Tangga & Pembersih (6 SKUs)
                ['code' => 'RTL-HOU-001', 'name' => 'Rinso Anti Noda Deterjen Bubuk 770g', 'cat' => 'Kebutuhan Rumah Tangga', 'unit' => 'pack', 'cost' => 18000, 'sell' => 22500, 'ws_qty' => 12, 'ws_price' => 20200, 'stock' => 120],
                ['code' => 'RTL-HOU-002', 'name' => 'Sunlight Pencuci Piring Jeruk Nipis 750ml', 'cat' => 'Kebutuhan Rumah Tangga', 'unit' => 'pack', 'cost' => 13500, 'sell' => 17000, 'ws_qty' => 12, 'ws_price' => 15200, 'stock' => 160],
                ['code' => 'RTL-HOU-003', 'name' => 'So Klin Pembersih Lantai Lavender Pouch 780ml', 'cat' => 'Kebutuhan Rumah Tangga', 'unit' => 'pack', 'cost' => 9500, 'sell' => 12500, 'ws_qty' => 12, 'ws_price' => 11000, 'stock' => 140],
                ['code' => 'RTL-HOU-004', 'name' => 'Downy Pelembut & Pewangi Pakaian Pouch 650ml', 'cat' => 'Kebutuhan Rumah Tangga', 'unit' => 'pack', 'cost' => 24000, 'sell' => 30000, 'ws_qty' => 10, 'ws_price' => 27000, 'stock' => 95],
                ['code' => 'RTL-HOU-005', 'name' => 'Baygon Aerosol Anti Nyamuk & Kecoa 600ml', 'cat' => 'Kebutuhan Rumah Tangga', 'unit' => 'pcs', 'cost' => 33000, 'sell' => 41000, 'ws_qty' => 6, 'ws_price' => 37000, 'stock' => 80],
                ['code' => 'RTL-HOU-006', 'name' => 'Tisu Paseo Smart Facial 250 Sheets', 'cat' => 'Kebutuhan Rumah Tangga', 'unit' => 'pack', 'cost' => 11000, 'sell' => 14500, 'ws_qty' => 15, 'ws_price' => 12800, 'stock' => 170],

                // Perawatan Diri (5 SKUs)
                ['code' => 'RTL-PRC-001', 'name' => 'Lifebuoy Sabun Cair Total 10 Pouch 450ml', 'cat' => 'Perawatan Diri & Mandi', 'unit' => 'pack', 'cost' => 19000, 'sell' => 24500, 'ws_qty' => 12, 'ws_price' => 21800, 'stock' => 110],
                ['code' => 'RTL-PRC-002', 'name' => 'Pepsodent Pasta Gigi Pencegah Gigi Berlubang 190g', 'cat' => 'Perawatan Diri & Mandi', 'unit' => 'pcs', 'cost' => 12500, 'sell' => 16500, 'ws_qty' => 12, 'ws_price' => 14500, 'stock' => 150],
                ['code' => 'RTL-PRC-003', 'name' => 'Clear Shampoo Ice Cool Menthol 160ml', 'cat' => 'Perawatan Diri & Mandi', 'unit' => 'pcs', 'cost' => 21000, 'sell' => 27000, 'ws_qty' => 12, 'ws_price' => 24000, 'stock' => 90],
                ['code' => 'RTL-PRC-004', 'name' => 'Biore Guard Body Foam Active Antibacterial 450ml', 'cat' => 'Perawatan Diri & Mandi', 'unit' => 'pack', 'cost' => 20500, 'sell' => 26000, 'ws_qty' => 12, 'ws_price' => 23200, 'stock' => 100],
                ['code' => 'RTL-PRC-005', 'name' => 'Rexona Men Deodorant Roll-On Ice Cool 50ml', 'cat' => 'Perawatan Diri & Mandi', 'unit' => 'pcs', 'cost' => 16000, 'sell' => 21000, 'ws_qty' => 12, 'ws_price' => 18500, 'stock' => 85],
            ];

            $productModels = [];
            foreach ($productCatalog as $pSpec) {
                $catId = $categories[$pSpec['cat']]->id ?? null;
                $unitObj = $units[$pSpec['unit']] ?? $units['pcs'];

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
                    'base_cost' => $pSpec['cost'],
                    'wholesale_min_qty' => $pSpec['ws_qty'],
                    'wholesale_price' => $pSpec['ws_price'],
                    'type' => Product::TYPE_GOODS,
                    'bom_consumption_type' => Product::BOM_NO_BOM,
                    'show_in_pos' => true,
                    'show_in_sales_order' => true,
                    'show_in_website' => true,
                    'business_type_hint' => 'distributor_fmcg',
                    'description' => "Produk ritel FMCG resmi COOCA Mart: {$pSpec['name']}",
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

                $productModels[$pSpec['code']] = $product;

                // Create initial inventory stock across all 3 branches
                foreach ($locations as $locKey => $loc) {
                    $locStockMultiplier = match ($locKey) {
                        'kelapa-gading' => 1.0,
                        'puri-indah' => 0.7,
                        'kemang' => 0.5,
                    };
                    $qty = round($pSpec['stock'] * $locStockMultiplier);

                    InventoryStock::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'location_id' => $loc->id,
                            'product_id' => $product->id,
                        ],
                        [
                            'material_id' => null,
                            'quantity' => $qty,
                            'reserved_quantity' => 0,
                            'last_cost' => $pSpec['cost'],
                            'avg_purchase_cost' => $pSpec['cost'],
                        ]
                    );

                    StockMovement::updateOrCreate(
                        [
                            'business_id' => $business->id,
                            'product_id' => $product->id,
                            'location_id' => $loc->id,
                            'reference_number' => "INIT-{$loc->code}-{$product->code}",
                        ],
                        [
                            'material_id' => null,
                            'movement_type' => StockMovement::TYPE_INITIAL,
                            'quantity_change' => $qty,
                            'balance_after' => $qty,
                            'unit_cost' => $pSpec['cost'],
                            'total_cost' => $qty * $pSpec['cost'],
                            'notes' => "Saldo Awal Pembukaan Cabang {$loc->name}",
                            'created_by' => $userModels['owner']->id,
                        ]
                    );
                }
            }

            // 9. Customers (22 Customers)
            $customerNames = [
                ['name' => 'Toko Berkah Mandiri', 'company' => 'PD Berkah Mandiri', 'phone' => '0812-9001-1001', 'segment' => 'wholesale'],
                ['name' => 'Warung Kelontong Bu Siti', 'company' => 'Warung Bu Siti', 'phone' => '0813-9001-1002', 'segment' => 'retail'],
                ['name' => 'Minimarket Sumber Rezeki', 'company' => 'CV Sumber Rezeki', 'phone' => '0812-9001-1003', 'segment' => 'wholesale'],
                ['name' => 'Kantin Sejahtera Kampus', 'company' => 'Koperasi Mahasiswa Sejahtera', 'phone' => '0815-9001-1004', 'segment' => 'horeca'],
                ['name' => 'Toko Sembako Makmur Jaya', 'company' => 'UD Makmur Jaya', 'phone' => '0816-9001-1005', 'segment' => 'wholesale'],
                ['name' => 'Warung Madura 24 Jam Kelapa Gading', 'company' => 'Warung Madura KG', 'phone' => '0817-9001-1006', 'segment' => 'retail'],
                ['name' => 'Ibu Ratna Kumalasari', 'company' => null, 'phone' => '0818-9001-1007', 'segment' => 'regular'],
                ['name' => 'Pak Bambang Soedirman', 'company' => null, 'phone' => '0819-9001-1008', 'segment' => 'regular'],
                ['name' => 'Keluarga Budi Wicaksono', 'company' => null, 'phone' => '0812-9001-1009', 'segment' => 'regular'],
                ['name' => 'Toko Kelontong Harapan Baru', 'company' => 'Toko Harapan Baru', 'phone' => '0813-9001-1010', 'segment' => 'retail'],
                ['name' => 'Catering Selera Nusantara', 'company' => 'PT Rasa Boga Selera', 'phone' => '0815-9001-1011', 'segment' => 'horeca'],
                ['name' => 'Warung Kopi & Indomie Mas Joko', 'company' => 'Warmindo Mas Joko', 'phone' => '0816-9001-1012', 'segment' => 'horeca'],
                ['name' => 'Ibu Meilani Kusuma', 'company' => null, 'phone' => '0817-9001-1013', 'segment' => 'regular'],
                ['name' => 'Pak Hendra Gunawan', 'company' => null, 'phone' => '0818-9001-1014', 'segment' => 'regular'],
                ['name' => 'Toko Grosir Sahabat Kita', 'company' => 'UD Sahabat Grosir', 'phone' => '0819-9001-1015', 'segment' => 'wholesale'],
                ['name' => 'Depot Air Minum & Toko Barokah', 'company' => 'Toko Barokah', 'phone' => '0812-9001-1016', 'segment' => 'retail'],
                ['name' => 'Ibu Veronica Tanujaya', 'company' => null, 'phone' => '0813-9001-1017', 'segment' => 'regular'],
                ['name' => 'Koperasi Karyawan Graha Mitra', 'company' => 'Kopkar Graha Mitra', 'phone' => '0815-9001-1018', 'segment' => 'corporate'],
                ['name' => 'Warung Makan Ibu Nining', 'company' => 'Warung Ibu Nining', 'phone' => '0816-9001-1019', 'segment' => 'horeca'],
                ['name' => 'Toko Rezeki Abadi Kemang', 'company' => 'Toko Rezeki Abadi', 'phone' => '0817-9001-1020', 'segment' => 'retail'],
                ['name' => 'Pak David Christian', 'company' => null, 'phone' => '0818-9001-1021', 'segment' => 'regular'],
                ['name' => 'Ibu Farida Hasyim', 'company' => null, 'phone' => '0819-9001-1022', 'segment' => 'regular'],
            ];

            $customers = [];
            foreach ($customerNames as $cSpec) {
                $customers[] = Customer::updateOrCreate(
                    ['business_id' => $business->id, 'phone' => $cSpec['phone']],
                    [
                        'name' => $cSpec['name'],
                        'slug' => Str::slug($cSpec['name']),
                        'company_name' => $cSpec['company'],
                        'email' => Str::slug($cSpec['name']) . '@customer.test',
                        'segment' => $cSpec['segment'],
                        'billing_address' => 'Jl. Boulevard Kelapa Gading Raya No. ' . rand(1, 150),
                        'shipping_address' => 'Jl. Boulevard Kelapa Gading Raya No. ' . rand(1, 150),
                        'payment_terms_days' => $cSpec['segment'] === 'wholesale' ? 30 : 0,
                        'is_active' => true,
                    ]
                );
            }

            // 10. Purchase Orders, Goods Receipts & Supplier Payments
            $poSpecs = [
                [
                    'po_number' => 'PO-CM-2026-001',
                    'gr_number' => 'GR-CM-2026-001',
                    'supplier' => $suppliers[0], // Indofood
                    'days_ago' => 8,
                    'items' => [
                        ['sku' => 'RTL-SNK-001', 'qty' => 50, 'cost' => 112000],
                        ['sku' => 'RTL-SNK-002', 'qty' => 40, 'cost' => 108000],
                    ],
                    'pay_amount' => 5000000,
                ],
                [
                    'po_number' => 'PO-CM-2026-002',
                    'gr_number' => 'GR-CM-2026-002',
                    'supplier' => $suppliers[1], // Unilever
                    'days_ago' => 5,
                    'items' => [
                        ['sku' => 'RTL-HOU-001', 'qty' => 40, 'cost' => 18000],
                        ['sku' => 'RTL-HOU-002', 'qty' => 50, 'cost' => 13500],
                        ['sku' => 'RTL-PRC-001', 'qty' => 30, 'cost' => 19000],
                    ],
                    'pay_amount' => null,
                ],
                [
                    'po_number' => 'PO-CM-2026-003',
                    'gr_number' => 'GR-CM-2026-003',
                    'supplier' => $suppliers[9], // Ultra Jaya
                    'days_ago' => 3,
                    'items' => [
                        ['sku' => 'RTL-DAI-003', 'qty' => 60, 'cost' => 16500],
                        ['sku' => 'RTL-DAI-004', 'qty' => 60, 'cost' => 16500],
                    ],
                    'pay_amount' => 1980000,
                ],
            ];

            foreach ($poSpecs as $poData) {
                $subtotal = 0.0;
                foreach ($poData['items'] as $item) {
                    $subtotal += ($item['qty'] * $item['cost']);
                }
                $poTax = round($subtotal * 0.11);
                $poTotal = $subtotal + $poTax;

                $po = PurchaseOrder::updateOrCreate(
                    ['business_id' => $business->id, 'po_number' => $poData['po_number']],
                    [
                        'po_type' => 'goods',
                        'supplier_id' => $poData['supplier']->id,
                        'order_date' => Carbon::now()->subDays($poData['days_ago'])->toDateString(),
                        'expected_delivery_date' => Carbon::now()->subDays($poData['days_ago'] - 2)->toDateString(),
                        'status' => 'received',
                        'subtotal' => $subtotal,
                        'tax_percentage' => 11,
                        'tax_amount' => $poTax,
                        'total_amount' => $poTotal,
                        'notes' => "Pengadaan barang stok dari {$poData['supplier']->name}",
                        'created_by' => $userModels['manager']->id,
                    ]
                );

                foreach ($poData['items'] as $pItem) {
                    $pModel = $productModels[$pItem['sku']];
                    PurchaseOrderItem::updateOrCreate(
                        ['purchase_order_id' => $po->id, 'product_id' => $pModel->id],
                        [
                            'item_type' => 'product',
                            'item_name' => $pModel->name,
                            'sku' => $pModel->code,
                            'quantity' => $pItem['qty'],
                            'unit_id' => $pModel->output_unit_id,
                            'unit_price' => $pItem['cost'],
                            'subtotal' => $pItem['qty'] * $pItem['cost'],
                        ]
                    );
                }

                $gr = GoodsReceipt::updateOrCreate(
                    ['business_id' => $business->id, 'receipt_number' => $poData['gr_number']],
                    [
                        'location_id' => $primaryLoc->id,
                        'supplier_id' => $poData['supplier']->id,
                        'purchase_order_id' => $po->id,
                        'receipt_date' => Carbon::now()->subDays($poData['days_ago'] - 2)->toDateString(),
                        'status' => 'received',
                        'notes' => "Penerimaan fisik barang PO #{$po->po_number}",
                        'received_by' => $userModels['warehouse']->id,
                    ]
                );

                foreach ($poData['items'] as $pItem) {
                    $pModel = $productModels[$pItem['sku']];
                    GoodsReceiptItem::updateOrCreate(
                        ['goods_receipt_id' => $gr->id, 'product_id' => $pModel->id],
                        [
                            'item_name' => $pModel->name,
                            'quantity' => $pItem['qty'],
                            'received_unit_id' => $pModel->output_unit_id,
                            'unit_cost' => $pItem['cost'],
                            'effective_base_cost' => $pItem['cost'],
                        ]
                    );
                }

                $this->journalService->recordGoodsReceiptJournal($gr);
            }

            // 11. POS Register, Shift, and Orders (with Balanced Double-Entry Auto-Journaling)
            $register = PosRegister::updateOrCreate(
                ['business_id' => $business->id, 'location_id' => $primaryLoc->id, 'code' => 'REG-KG-01'],
                [
                    'name' => 'Kasir Utama #1 (Kelapa Gading)',
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
                    'opened_at' => Carbon::now()->subHours(8),
                    'closed_at' => Carbon::now()->subHour(),
                    'opening_cash' => 500000,
                    'closing_cash_actual' => 500000 + 450000,
                    'closing_cash_expected' => 500000 + 450000,
                    'cash_difference' => 0,
                    'total_cash_sales' => 450000,
                    'total_non_cash_sales' => 1250000,
                ]
            );

            // Sample POS Orders (Retail & Wholesale)
            $sampleSales = [
                [
                    'order_number' => 'POS-CM-20260101-001',
                    'customer' => $customers[0], // Toko Berkah Mandiri (Wholesale tier trigger)
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'RTL-SEM-001', 'qty' => 12], // Qty 12 >= ws_min_qty 10 -> wholesale_price 74,000
                        ['code' => 'RTL-SNK-001', 'qty' => 6],  // Qty 6 >= ws_min_qty 5 -> wholesale_price 121,000
                    ],
                ],
                [
                    'order_number' => 'POS-CM-20260101-002',
                    'customer' => $customers[1], // Warung Bu Siti
                    'payment_method' => PosOrderPayment::METHOD_QRIS,
                    'items' => [
                        ['code' => 'RTL-SEM-003', 'qty' => 6], // Normal selling price 36,500
                        ['code' => 'RTL-SEM-005', 'qty' => 5], // Normal selling price 17,500
                        ['code' => 'RTL-BVG-001', 'qty' => 2], // Dus Aqua 54,000
                    ],
                ],
                [
                    'order_number' => 'POS-CM-20260101-003',
                    'customer' => $customers[6], // Ibu Ratna Kumalasari (Retail Walk-in)
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'items' => [
                        ['code' => 'RTL-DAI-003', 'qty' => 2], // Ultra Milk 1L
                        ['code' => 'RTL-SNK-003', 'qty' => 2], // Roma Kelapa
                        ['code' => 'RTL-HOU-002', 'qty' => 1], // Sunlight
                        ['code' => 'RTL-PRC-001', 'qty' => 1], // Lifebuoy
                    ],
                ],
                [
                    'order_number' => 'POS-CM-20260101-004',
                    'customer' => $customers[2], // Minimarket Sumber Rezeki
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'RTL-BVG-001', 'qty' => 15], // Wholesale tier Aqua Dus @ 49,500
                        ['code' => 'RTL-BVG-004', 'qty' => 10], // Wholesale tier Teh Botol @ 68,000
                    ],
                ],
                [
                    'order_number' => 'POS-CM-20260101-005',
                    'customer' => $customers[7], // Pak Bambang
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'items' => [
                        ['code' => 'RTL-SEM-002', 'qty' => 1], // Beras Ramos 74,500
                        ['code' => 'RTL-SEM-004', 'qty' => 1], // Minyak Bimoli 38,000
                        ['code' => 'RTL-HOU-001', 'qty' => 1], // Rinso 22,500
                    ],
                ],
                [
                    'order_number' => 'POS-CM-20260101-006',
                    'customer' => $customers[8], // Keluarga Budi
                    'payment_method' => PosOrderPayment::METHOD_QRIS,
                    'items' => [
                        ['code' => 'RTL-DAI-001', 'qty' => 3], // Frisian Flag SKM
                        ['code' => 'RTL-DAI-005', 'qty' => 2], // Kraft Cheddar
                        ['code' => 'RTL-DAI-006', 'qty' => 2], // Blue Band
                    ],
                ],
                [
                    'order_number' => 'POS-CM-20260101-007',
                    'customer' => $customers[9], // Toko Harapan Baru
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'RTL-SEM-006', 'qty' => 25], // Wholesale Segitiga Biru
                        ['code' => 'RTL-SEM-005', 'qty' => 20], // Wholesale Gulaku
                    ],
                ],
                [
                    'order_number' => 'POS-CM-20260101-008',
                    'customer' => $customers[10], // Catering Selera Nusantara
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'RTL-SEM-001', 'qty' => 10], // Wholesale Beras Pandan Wangi
                        ['code' => 'RTL-SEM-003', 'qty' => 12], // Wholesale Minyak Sania
                        ['code' => 'RTL-SEM-007', 'qty' => 40], // Garam Cap Kapal
                    ],
                ],
                [
                    'order_number' => 'POS-CM-20260101-009',
                    'customer' => $customers[12], // Ibu Meilani
                    'payment_method' => PosOrderPayment::METHOD_CASH,
                    'items' => [
                        ['code' => 'RTL-HOU-006', 'qty' => 3], // Paseo Facial Tissue
                        ['code' => 'RTL-PRC-002', 'qty' => 2], // Pepsodent
                        ['code' => 'RTL-PRC-004', 'qty' => 1], // Biore Body Foam
                    ],
                ],
                [
                    'order_number' => 'POS-CM-20260101-010',
                    'customer' => $customers[14], // Toko Grosir Sahabat Kita
                    'payment_method' => PosOrderPayment::METHOD_TRANSFER,
                    'items' => [
                        ['code' => 'RTL-SNK-001', 'qty' => 10], // Wholesale Indomie Goreng
                        ['code' => 'RTL-SNK-002', 'qty' => 10], // Wholesale Indomie Kuah
                        ['code' => 'RTL-BVG-005', 'qty' => 8],  // Teh Pucuk Dus
                    ],
                ],
            ];

            foreach ($sampleSales as $sSale) {
                $subtotal = 0.0;
                $totalHpp = 0.0;
                $lineItemsData = [];

                foreach ($sSale['items'] as $itemSpec) {
                    $prod = $productModels[$itemSpec['code']];
                    $qty = (float) $itemSpec['qty'];

                    // Check wholesale tiered pricing
                    $unitPrice = ($prod->wholesale_min_qty && $qty >= $prod->wholesale_min_qty && $prod->wholesale_price)
                        ? (float) $prod->wholesale_price
                        : (float) $prod->selling_price;

                    $unitCost = (float) $prod->base_cost;
                    $lineTotal = $unitPrice * $qty;
                    $lineHpp = $unitCost * $qty;

                    $subtotal += $lineTotal;
                    $totalHpp += $lineHpp;

                    $lineItemsData[] = [
                        'product_id' => $prod->id,
                        'product_name' => $prod->name,
                        'product_code' => $prod->code,
                        'unit_price' => $unitPrice,
                        'unit_cost_hpp' => $unitCost,
                        'quantity' => $qty,
                        'subtotal' => $lineTotal,
                        'total_price' => $lineTotal,
                        'total_hpp' => $lineHpp,
                    ];
                }

                $taxAmount = 0.0; // retail cashiers in ID often include tax or PPN exempt for staples
                $totalAmount = $subtotal + $taxAmount;

                $order = PosOrder::firstOrCreate(
                    [
                        'business_id' => $business->id,
                        'order_number' => $sSale['order_number'],
                    ],
                    [
                        'location_id' => $primaryLoc->id,
                        'pos_shift_id' => $shift->id,
                        'pos_register_id' => $register->id,
                        'user_id' => $userModels['cashier']->id,
                        'customer_id' => $sSale['customer']->id,
                        'order_date' => Carbon::now()->toDateString(),
                        'status' => PosOrder::STATUS_COMPLETED,
                        'order_type' => 'takeaway',
                        'sales_channel' => 'pos',
                        'customer_name_guest' => $sSale['customer']->name,
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'paid_amount' => $totalAmount,
                        'change_amount' => 0,
                        'total_hpp_cost' => $totalHpp,
                        'total_gross_profit' => $totalAmount - $totalHpp,
                        'notes' => 'Transaksi POS Kasir Kelapa Gading',
                    ]
                );

                if ($order->wasRecentlyCreated) {
                    foreach ($lineItemsData as $line) {
                        PosOrderItem::create(array_merge($line, ['pos_order_id' => $order->id]));

                        // Decrement stock in Kelapa Gading branch
                        $inv = InventoryStock::where('business_id', $business->id)
                            ->where('location_id', $primaryLoc->id)
                            ->where('product_id', $line['product_id'])
                            ->first();

                        if ($inv) {
                            $inv->decrement('quantity', $line['quantity']);
                            StockMovement::create([
                                'business_id' => $business->id,
                                'location_id' => $primaryLoc->id,
                                'product_id' => $line['product_id'],
                                'material_id' => null,
                                'movement_type' => StockMovement::TYPE_POS_SALE,
                                'quantity_change' => -$line['quantity'],
                                'balance_after' => $inv->quantity,
                                'unit_cost' => $line['unit_cost_hpp'],
                                'total_cost' => $line['total_hpp'],
                                'reference_number' => $order->order_number,
                                'notes' => "Pengurangan Penjualan POS #{$order->order_number}",
                                'created_by' => $userModels['cashier']->id,
                            ]);
                        }
                    }

                    PosOrderPayment::create([
                        'pos_order_id' => $order->id,
                        'payment_method' => $sSale['payment_method'],
                        'amount' => $totalAmount,
                        'reference_number' => 'PAY-' . $order->order_number,
                        'status' => 'settled',
                    ]);

                    // Record Balanced Auto-Journal Entry
                    $this->journalService->recordPosSaleJournal($order);
                }
            }
        });

        $this->command?->info('✓ Sukses seeding COOCA Mart Indonesia (Retail & Distribution)!');
    }
}
