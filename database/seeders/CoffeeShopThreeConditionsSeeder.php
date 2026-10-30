<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BomHeader;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\CostModel;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryStock;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialPrice;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOrderPayment;
use App\Models\PosRegister;
use App\Models\PosShift;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class CoffeeShopThreeConditionsSeeder extends Seeder
{
    /**
     * Run the database seeds for the 3 Coffee Shop Product & Inventory Conditions:
     * 1. Standalone Product (Biji Kopi Arabica A kemasan 1 kg)
     * 2. Recipe / BOM Product (Iced Latte 16oz)
     * 3. Fluctuating Raw Material Purchase Prices & Moving Average Cost (October 2026)
     */
    public function run(): void
    {
        DB::transaction(function () {
            // ─────────────────────────────────────────────────────────────────
            // 1. BUSINESS & OWNER ACCOUNT SETUP
            // ─────────────────────────────────────────────────────────────────
            $business = Business::where('slug', 'kopi-senja-utama')->first();

            $businessAttributes = [
                'name' => 'Kopi Senja Utama (Coffee Shop)',
                'slug' => 'kopi-senja-utama',
                'currency' => 'IDR',
                'currency_precision' => 0,
                'phone' => '0812-1111-0002',
                'email' => 'owner.cafe@cooca.id',
                'address' => 'Jl. Ranggamalela No. 9, Dago, Bandung 40116',
                'tax_identification_number' => '01.892.443.2-429.000',
                'pos_enable_tax' => false,
                'pos_enable_service_charge' => false,
                'allow_negative_stock' => false,
                'template_code' => 'fnb_cafe',
                'disabled_modules' => ['b2b_sales', 'labor_machines'],
            ];

            if (! $business) {
                $business = Business::create(array_merge([
                    'id' => (string) Str::uuid(),
                ], $businessAttributes));
            } else {
                $business->update($businessAttributes);
            }

            // Owner User
            $owner = User::updateOrCreate(
                ['email' => 'owner.cafe@cooca.id'],
                [
                    'name' => 'Fajar Nugraha (Owner)',
                    'password' => Hash::make('password123'),
                    'phone' => '0812-1111-0002',
                    'phone_verified_at' => now(),
                    'email_verified_at' => now(),
                    'active_business_id' => $business->id,
                ]
            );

            BusinessMembership::updateOrCreate(
                ['business_id' => $business->id, 'user_id' => $owner->id],
                ['role' => 'owner']
            );

            // Cashier User
            $cashier = User::updateOrCreate(
                ['email' => 'kasir.cafe@cooca.id'],
                [
                    'name' => 'Siti Rahma (Kasir)',
                    'password' => Hash::make('password123'),
                    'phone' => '0812-3344-5562',
                    'phone_verified_at' => now(),
                    'email_verified_at' => now(),
                    'active_business_id' => $business->id,
                ]
            );

            BusinessMembership::updateOrCreate(
                ['business_id' => $business->id, 'user_id' => $cashier->id],
                ['role' => 'cashier']
            );

            // Primary Outlet Location
            $location = Location::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'LOC-DAGO'],
                [
                    'name' => 'Coffee Bar & Roastery Dago',
                    'slug' => 'coffee-bar-dago',
                    'type' => 'outlet',
                    'address' => 'Jl. Ranggamalela No. 9, Dago, Bandung',
                    'phone' => '0812-1111-0002',
                    'is_primary' => true,
                    'is_active' => true,
                ]
            );

            // POS Register & Shift
            $posRegister = PosRegister::firstOrCreate(
                ['business_id' => $business->id, 'code' => 'REG-DAGO-01'],
                [
                    'location_id' => $location->id,
                    'name' => 'Kasir Utama Bar Dago',
                    'is_active' => true,
                ]
            );

            $posShift = PosShift::where('business_id', $business->id)
                ->where('location_id', $location->id)
                ->where('status', PosShift::STATUS_OPEN)
                ->first();

            if (! $posShift) {
                $posShift = PosShift::create([
                    'business_id' => $business->id,
                    'pos_register_id' => $posRegister->id,
                    'location_id' => $location->id,
                    'user_id' => $cashier->id,
                    'opened_at' => Carbon::parse('2026-10-01 07:00:00'),
                    'opening_cash' => 500000.0,
                    'status' => PosShift::STATUS_OPEN,
                    'notes' => 'Shift Operasional Kasir Utama Coffee Bar Dago',
                ]);
            }

            // ─────────────────────────────────────────────────────────────────
            // 2. UNITS & CONVERSIONS
            // ─────────────────────────────────────────────────────────────────
            $uKg = Unit::firstOrCreate(['code' => 'kg'], ['name' => 'Kilogram', 'category' => Unit::CATEGORY_WEIGHT, 'is_base' => true]);
            $uG = Unit::firstOrCreate(['code' => 'g'], ['name' => 'Gram', 'category' => Unit::CATEGORY_WEIGHT, 'is_base' => false]);
            $uL = Unit::firstOrCreate(['code' => 'l'], ['name' => 'Liter', 'category' => Unit::CATEGORY_VOLUME, 'is_base' => true]);
            $uMl = Unit::firstOrCreate(['code' => 'ml'], ['name' => 'Mililiter', 'category' => Unit::CATEGORY_VOLUME, 'is_base' => false]);
            $uPcs = Unit::firstOrCreate(['code' => 'pcs'], ['name' => 'Pieces', 'category' => Unit::CATEGORY_QUANTITY, 'is_base' => true]);
            $uCup = Unit::firstOrCreate(['code' => 'cup'], ['name' => 'Cup', 'category' => Unit::CATEGORY_QUANTITY, 'is_base' => false]);

            UnitConversion::firstOrCreate(
                ['from_unit_id' => $uKg->id, 'to_unit_id' => $uG->id],
                ['factor' => 1000.0]
            );
            UnitConversion::firstOrCreate(
                ['from_unit_id' => $uG->id, 'to_unit_id' => $uKg->id],
                ['factor' => 0.001]
            );
            UnitConversion::firstOrCreate(
                ['from_unit_id' => $uL->id, 'to_unit_id' => $uMl->id],
                ['factor' => 1000.0]
            );
            UnitConversion::firstOrCreate(
                ['from_unit_id' => $uMl->id, 'to_unit_id' => $uL->id],
                ['factor' => 0.001]
            );

            // ─────────────────────────────────────────────────────────────────
            // 3. SUPPLIER SETUP
            // ─────────────────────────────────────────────────────────────────
            $supplierKopi = Supplier::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'cv-gayo-mountain-coffee'],
                [
                    'name' => 'CV Gayo Mountain Specialty Coffee',
                    'contact_person' => 'Teuku Fauzan',
                    'phone' => '0813-9900-1122',
                    'email' => 'order@gayomountain.co.id',
                    'address' => 'Takengon, Aceh Tengah',
                ]
            );

            $supplierDairy = Supplier::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'pt-greenfields-dairy-bandung'],
                [
                    'name' => 'Distributor Greenfields & Dairy Bandung',
                    'contact_person' => 'Bambang Sudiro',
                    'phone' => '0812-7788-2233',
                    'email' => 'supply@dairybandung.co.id',
                    'address' => 'Kawasan Pergudangan Batununggal Blok B-4, Bandung',
                ]
            );

            $supplierPackaging = Supplier::updateOrCreate(
                ['business_id' => $business->id, 'slug' => 'cv-java-eco-cup-packaging'],
                [
                    'name' => 'CV Java Eco Cup & Packaging',
                    'contact_person' => 'Iwan Darmawan',
                    'phone' => '0813-2211-7788',
                    'email' => 'info@javacup.co.id',
                    'address' => 'Jl. Industri Cimahi No. 18, Cimahi',
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 4. KATEGORI PRODUK & BAHAN BAKU
            // ─────────────────────────────────────────────────────────────────
            $catRetail = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'biji-kopi-kemasan'],
                ['name' => 'Biji Kopi Kemasan (Retail)']
            );
            $catEspresso = ProductCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'coffee-espresso'],
                ['name' => 'Signature Coffee & Espresso']
            );

            $catMatBeans = MaterialCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'biji-kopi-roasted'],
                ['name' => 'Biji Kopi Roasted']
            );
            $catMatDairy = MaterialCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'susu-sirup-dairy'],
                ['name' => 'Susu, Sirup & Pemanis']
            );
            $catMatPack = MaterialCategory::firstOrCreate(
                ['business_id' => $business->id, 'slug' => 'packaging-cup-lid'],
                ['name' => 'Kemasan & Cup']
            );

            // ─────────────────────────────────────────────────────────────────
            // 5. KONDISI 1   PRODUK STANDALONE (Biji Kopi Arabica A 1 kg)
            // ─────────────────────────────────────────────────────────────────
            $prodStandalone = Product::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'CFE-BEAN-ARA-1KG'],
                [
                    'name' => 'Biji Kopi Arabica A 1 kg',
                    'slug' => 'biji-kopi-arabica-a-1-kg',
                    'category_id' => $catRetail->id,
                    'output_unit_id' => $uPcs->id,
                    'base_cost' => 110000.0,    // Harga beli kemasan
                    'selling_price' => 165000.0, // Harga jual langsung ke pelanggan
                    'min_stock' => 5.0,
                    'type' => Product::TYPE_GOODS,
                    'is_active' => true,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'description' => 'Biji kopi Arabica Specialty pilihan dalam kemasan 1 kg ber-valve dan zipper. Dijual langsung tanpa racikan resep.',
                ]
            );

            // CostModel Standalone (Simple / Retail)
            CostModel::updateOrCreate(
                ['business_id' => $business->id, 'product_id' => $prodStandalone->id],
                [
                    'name' => "Model HPP Retail - {$prodStandalone->name}",
                    'method' => CostModel::METHOD_RETAIL,
                    'is_active' => true,
                ]
            );

            // Saldo Awal Standalone: 30 pcs @ Rp 110.000 per 1 Oktober 2026
            $stockStandalone = InventoryStock::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'product_id' => $prodStandalone->id,
                    'material_id' => null,
                ],
                [
                    'quantity' => 30.0,
                    'last_cost' => 110000.0,
                    'avg_purchase_cost' => 110000.0,
                ]
            );

            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'product_id' => $prodStandalone->id,
                    'movement_type' => StockMovement::TYPE_INITIAL,
                    'reference_number' => 'INIT-BEAN-1KG',
                ],
                [
                    'quantity_change' => 30.0,
                    'balance_after' => 30.0,
                    'unit_cost' => 110000.0,
                    'total_cost' => 3300000.0,
                    'notes' => 'Saldo awal cut-off go-live sistem per 1 Oktober 2026',
                    'created_by' => $owner->id,
                    'created_at' => Carbon::parse('2026-10-01 08:00:00'),
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 6. KONDISI 2 & 3   MASTER INGREDIENTS (BAHAN BAKU RESEP)
            // ─────────────────────────────────────────────────────────────────
            // Bahan 1: Biji Kopi Arabica (Satuan dasar: gram, dibeli dalam kg)
            $matKopi = Material::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'CFE-MAT-BEANS-ARA'],
                [
                    'name' => 'Biji Kopi Arabica (Espresso Roast)',
                    'slug' => 'biji-kopi-arabica-espresso-roast',
                    'category_id' => $catMatBeans->id,
                    'unit_id' => $uG->id,
                    'supplier_id' => $supplierKopi->id,
                    'description' => 'Biji kopi sangrai Arabica untuk bahan racikan minuman espresso & latte.',
                ]
            );

            // Bahan 2: Susu UHT (Satuan dasar: ml, dibeli dalam liter)
            $matSusu = Material::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'CFE-MAT-MILK-UHT'],
                [
                    'name' => 'Susu UHT Full Cream Dairy Fresh',
                    'slug' => 'susu-uht-full-cream-dairy-fresh',
                    'category_id' => $catMatDairy->id,
                    'unit_id' => $uMl->id,
                    'supplier_id' => $supplierDairy->id,
                    'description' => 'Susu cair UHT untuk campuran latte dan milk-based drinks.',
                ]
            );

            // Bahan 3: Gula Cair Liquid Sugar (Satuan dasar: ml, dibeli dalam liter)
            $matGula = Material::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'CFE-MAT-SUGAR-LIQ'],
                [
                    'name' => 'Gula Cair Liquid Sugar Fruktosa',
                    'slug' => 'gula-cair-liquid-sugar-fruktosa',
                    'category_id' => $catMatDairy->id,
                    'unit_id' => $uMl->id,
                    'supplier_id' => $supplierDairy->id,
                    'description' => 'Sirup gula cair murni siap pakai untuk takaran manis konsisten.',
                ]
            );

            // Bahan 4: Cup 16oz dan Tutup (Satuan dasar: pcs)
            $matCup = Material::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'CFE-MAT-CUP-LID'],
                [
                    'name' => 'Cup Cold 16oz + Straw Lid Kopi Senja',
                    'slug' => 'cup-cold-16oz-straw-lid-kopi-senja',
                    'category_id' => $catMatPack->id,
                    'unit_id' => $uPcs->id,
                    'supplier_id' => $supplierPackaging->id,
                    'description' => 'Gelas plastik 16oz sablon logo Kopi Senja beserta tutup straw-less lid.',
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 7. KONDISI 2   PRODUK RACIKAN & RESEP BOM (Iced Latte Single Origin)
            // ─────────────────────────────────────────────────────────────────
            $prodLatte = Product::updateOrCreate(
                ['business_id' => $business->id, 'code' => 'CFE-ICED-LATTE'],
                [
                    'name' => 'Iced Latte Single Origin',
                    'slug' => 'iced-latte-single-origin',
                    'category_id' => $catEspresso->id,
                    'output_unit_id' => $uCup->id,
                    'base_cost' => 6450.0,     // Dihitung otomatis dari akumulasi resep bahan
                    'selling_price' => 25000.0, // Harga jual per cup di POS
                    'min_stock' => 0.0,
                    'type' => Product::TYPE_GOODS,
                    'is_active' => true,
                    'show_in_pos' => true,
                    'show_in_website' => true,
                    'description' => 'Espresso Arabica single origin dipadukan dengan susu UHT dingin dan gula cair dalam cup 16oz.',
                ]
            );

            // Setup CostModel bertipe recipe_bom
            $costModelLatte = CostModel::updateOrCreate(
                ['business_id' => $business->id, 'product_id' => $prodLatte->id],
                [
                    'name' => "Model Resep BOM - {$prodLatte->name}",
                    'method' => CostModel::METHOD_RECIPE_BOM,
                    'is_active' => true,
                ]
            );

            $bomHeaderLatte = BomHeader::updateOrCreate(
                ['cost_model_id' => $costModelLatte->id],
                [
                    'type' => BomHeader::TYPE_RECIPE,
                    'name' => 'Resep Standar Iced Latte 16oz',
                    'level' => 1,
                ]
            );

            // Hapus item BOM lama agar resep bersih dan konsisten
            $bomHeaderLatte->items()->delete();

            // Item 1: Biji Kopi Arabica 20 gram
            $bomHeaderLatte->items()->create([
                'material_id' => $matKopi->id,
                'quantity' => 20.0,
                'unit_id' => $uG->id,
                'waste_percentage' => 0.0,
                'sort_order' => 1,
                'notes' => 'Dosis double shot espresso standard basket 20g',
            ]);

            // Item 2: Susu UHT 100 ml
            $bomHeaderLatte->items()->create([
                'material_id' => $matSusu->id,
                'quantity' => 100.0,
                'unit_id' => $uMl->id,
                'waste_percentage' => 0.0,
                'sort_order' => 2,
                'notes' => 'Fresh cold milk 100ml',
            ]);

            // Item 3: Gula Cair 15 ml
            $bomHeaderLatte->items()->create([
                'material_id' => $matGula->id,
                'quantity' => 15.0,
                'unit_id' => $uMl->id,
                'waste_percentage' => 0.0,
                'sort_order' => 3,
                'notes' => '1 pump liquid fructose 15ml',
            ]);

            // Item 4: Cup & Tutup 1 pcs
            $bomHeaderLatte->items()->create([
                'material_id' => $matCup->id,
                'quantity' => 1.0,
                'unit_id' => $uPcs->id,
                'waste_percentage' => 0.0,
                'sort_order' => 4,
                'notes' => 'Cup 16oz sablon + straw lid',
            ]);

            // ─────────────────────────────────────────────────────────────────
            // 8. KONDISI 3   TRANSAKSI PEMBELIAN BAHAN BAKU FLUKTUATIF (OKTOBER 2026)
            // ─────────────────────────────────────────────────────────────────
            // Pembelian Batch 1: 1 Oktober 2026
            // - Biji Kopi: 10 kg (10.000g) @ Rp 150.000/kg (Rp 150/g) = Rp 1.500.000
            // - Susu UHT: 20 liter (20.000ml) @ Rp 20.000/liter (Rp 20/ml) = Rp 400.000
            // - Gula Cair: 5 liter (5.000ml) @ Rp 30.000/liter (Rp 30/ml) = Rp 150.000
            // - Cup & Tutup: 200 pcs @ Rp 1.000/pcs = Rp 200.000
            $po1 = PurchaseOrder::updateOrCreate(
                ['business_id' => $business->id, 'po_number' => 'PO-202610-001'],
                [
                    'po_type' => PurchaseOrder::TYPE_SUPPLIER,
                    'supplier_id' => $supplierKopi->id,
                    'order_date' => Carbon::parse('2026-10-01'),
                    'status' => PurchaseOrder::STATUS_COMPLETED,
                    'subtotal' => 2250000.0,
                    'total_amount' => 2250000.0,
                    'notes' => 'Pengadaan bahan baku batch 1 awal Oktober 2026',
                    'created_by' => $owner->id,
                ]
            );

            $gr1 = GoodsReceipt::updateOrCreate(
                ['business_id' => $business->id, 'receipt_number' => 'GR-202610-001'],
                [
                    'location_id' => $location->id,
                    'supplier_id' => $supplierKopi->id,
                    'purchase_order_id' => $po1->id,
                    'receipt_date' => Carbon::parse('2026-10-01'),
                    'status' => 'completed',
                    'notes' => 'Penerimaan bahan fisik batch 1 lengkap',
                    'received_by' => $owner->id,
                ]
            );

            // Catat MaterialPrice sequence 1 per 1 Oktober 2026
            MaterialPrice::updateOrCreate(
                ['material_id' => $matKopi->id, 'sequence' => 1],
                [
                    'business_id' => $business->id,
                    'supplier_id' => $supplierKopi->id,
                    'purchase_price' => 150000.0,
                    'purchase_unit_id' => $uKg->id,
                    'yield_percentage' => 100.0,
                    'waste_percentage' => 0.0,
                    'effective_date' => Carbon::parse('2026-10-01'),
                    'notes' => 'Invoice Pembelian #1 (10 kg @ Rp 150.000/kg)',
                ]
            );
            MaterialPrice::updateOrCreate(
                ['material_id' => $matSusu->id, 'sequence' => 1],
                [
                    'business_id' => $business->id,
                    'supplier_id' => $supplierDairy->id,
                    'purchase_price' => 20000.0,
                    'purchase_unit_id' => $uL->id,
                    'yield_percentage' => 100.0,
                    'waste_percentage' => 0.0,
                    'effective_date' => Carbon::parse('2026-10-01'),
                    'notes' => 'Invoice Susu UHT 20L @ Rp 20.000/L',
                ]
            );
            MaterialPrice::updateOrCreate(
                ['material_id' => $matGula->id, 'sequence' => 1],
                [
                    'business_id' => $business->id,
                    'supplier_id' => $supplierDairy->id,
                    'purchase_price' => 30000.0,
                    'purchase_unit_id' => $uL->id,
                    'yield_percentage' => 100.0,
                    'waste_percentage' => 0.0,
                    'effective_date' => Carbon::parse('2026-10-01'),
                    'notes' => 'Invoice Gula Cair 5L @ Rp 30.000/L',
                ]
            );
            MaterialPrice::updateOrCreate(
                ['material_id' => $matCup->id, 'sequence' => 1],
                [
                    'business_id' => $business->id,
                    'supplier_id' => $supplierPackaging->id,
                    'purchase_price' => 1000.0,
                    'purchase_unit_id' => $uPcs->id,
                    'yield_percentage' => 100.0,
                    'waste_percentage' => 0.0,
                    'effective_date' => Carbon::parse('2026-10-01'),
                    'notes' => 'Invoice Cup 16oz 200 pcs @ Rp 1.000/pcs',
                ]
            );

            // Mutasi Stok Penerimaan Batch 1 (1 Oktober)
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matKopi->id,
                    'reference_number' => 'GR-202610-001',
                    'movement_type' => StockMovement::TYPE_GOODS_RECEIPT,
                ],
                [
                    'quantity_change' => 10000.0, // 10 kg = 10.000 gram
                    'balance_after' => 10000.0,
                    'unit_cost' => 150.0,         // Rp 150 per gram
                    'total_cost' => 1500000.0,
                    'notes' => 'Penerimaan PO-202610-001 (10 kg @ Rp 150.000)',
                    'created_by' => $owner->id,
                    'created_at' => Carbon::parse('2026-10-01 09:00:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matSusu->id,
                    'reference_number' => 'GR-202610-001',
                    'movement_type' => StockMovement::TYPE_GOODS_RECEIPT,
                ],
                [
                    'quantity_change' => 20000.0, // 20 L = 20.000 ml
                    'balance_after' => 20000.0,
                    'unit_cost' => 20.0,          // Rp 20 per ml
                    'total_cost' => 400000.0,
                    'notes' => 'Penerimaan Susu UHT 20 Liter',
                    'created_by' => $owner->id,
                    'created_at' => Carbon::parse('2026-10-01 09:00:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matGula->id,
                    'reference_number' => 'GR-202610-001',
                    'movement_type' => StockMovement::TYPE_GOODS_RECEIPT,
                ],
                [
                    'quantity_change' => 5000.0, // 5 L = 5.000 ml
                    'balance_after' => 5000.0,
                    'unit_cost' => 30.0,         // Rp 30 per ml
                    'total_cost' => 150000.0,
                    'notes' => 'Penerimaan Gula Cair 5 Liter',
                    'created_by' => $owner->id,
                    'created_at' => Carbon::parse('2026-10-01 09:00:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matCup->id,
                    'reference_number' => 'GR-202610-001',
                    'movement_type' => StockMovement::TYPE_GOODS_RECEIPT,
                ],
                [
                    'quantity_change' => 200.0,
                    'balance_after' => 200.0,
                    'unit_cost' => 1000.0,
                    'total_cost' => 200000.0,
                    'notes' => 'Penerimaan Cup 16oz 200 pcs',
                    'created_by' => $owner->id,
                    'created_at' => Carbon::parse('2026-10-01 09:00:00'),
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 9. TRANSAKSI PENJUALAN BATCH 1 (5 OKTOBER 2026)
            // - Jual 2 pcs Biji Kopi Standalone @ Rp 165.000 = Rp 330.000 (Stok produk: 30 - 2 = 28 pcs)
            // - Jual 20 cup Iced Latte @ Rp 25.000 = Rp 500.000
            //   Deductions: Kopi -400g (sisa 9.600g), Susu -2.000ml (sisa 18.000ml), Gula -300ml (sisa 4.700ml), Cup -20pcs (sisa 180pcs)
            // ─────────────────────────────────────────────────────────────────
            $order1 = PosOrder::updateOrCreate(
                ['business_id' => $business->id, 'order_number' => 'POS-SENJA-20261005-001'],
                [
                    'location_id' => $location->id,
                    'pos_register_id' => $posRegister->id,
                    'pos_shift_id' => $posShift->id,
                    'user_id' => $cashier->id,
                    'order_date' => Carbon::parse('2026-10-05'),
                    'order_type' => 'takeaway',
                    'sales_channel' => 'pos',
                    'status' => PosOrder::STATUS_COMPLETED,
                    'subtotal' => 830000.0,
                    'total_amount' => 830000.0,
                    'paid_amount' => 830000.0,
                    'change_amount' => 0.0,
                    'total_hpp_cost' => (2 * 110000.0) + (20 * 6450.0), // Rp 220.000 + Rp 129.000 = Rp 349.000
                    'total_gross_profit' => 830000.0 - 349000.0,        // Rp 481.000
                    'created_at' => Carbon::parse('2026-10-05 14:30:00'),
                ]
            );

            PosOrderItem::updateOrCreate(
                ['pos_order_id' => $order1->id, 'product_id' => $prodStandalone->id],
                [
                    'product_name' => $prodStandalone->name,
                    'product_code' => $prodStandalone->code,
                    'unit_price' => 165000.0,
                    'unit_cost_hpp' => 110000.0,
                    'quantity' => 2.0,
                    'subtotal' => 330000.0,
                    'total_price' => 330000.0,
                    'total_hpp' => 220000.0,
                ]
            );

            PosOrderItem::updateOrCreate(
                ['pos_order_id' => $order1->id, 'product_id' => $prodLatte->id],
                [
                    'product_name' => $prodLatte->name,
                    'product_code' => $prodLatte->code,
                    'unit_price' => 25000.0,
                    'unit_cost_hpp' => 6450.0,
                    'quantity' => 20.0,
                    'subtotal' => 500000.0,
                    'total_price' => 500000.0,
                    'total_hpp' => 129000.0,
                ]
            );

            PosOrderPayment::updateOrCreate(
                ['pos_order_id' => $order1->id, 'payment_method' => 'qris'],
                [
                    'amount' => 830000.0,
                    'net_amount' => 830000.0,
                    'status' => 'paid',
                ]
            );

            // Mutasi Pengurangan Stok Batch 1 (5 Oktober)
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'product_id' => $prodStandalone->id,
                    'reference_number' => 'POS-SENJA-20261005-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -2.0,
                    'balance_after' => 28.0,
                    'unit_cost' => 110000.0,
                    'total_cost' => 220000.0,
                    'notes' => 'Penjualan POS Biji Kopi Standalone 2 pcs',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-05 14:30:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matKopi->id,
                    'reference_number' => 'POS-SENJA-20261005-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -400.0, // 20 cup x 20g
                    'balance_after' => 9600.0,
                    'unit_cost' => 150.0,
                    'total_cost' => 60000.0,
                    'notes' => 'Konsumsi 20 cup Iced Latte: Biji Kopi -400g',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-05 14:30:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matSusu->id,
                    'reference_number' => 'POS-SENJA-20261005-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -2000.0, // 20 cup x 100ml
                    'balance_after' => 18000.0,
                    'unit_cost' => 20.0,
                    'total_cost' => 40000.0,
                    'notes' => 'Konsumsi 20 cup Iced Latte: Susu -2000ml',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-05 14:30:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matGula->id,
                    'reference_number' => 'POS-SENJA-20261005-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -300.0, // 20 cup x 15ml
                    'balance_after' => 4700.0,
                    'unit_cost' => 30.0,
                    'total_cost' => 9000.0,
                    'notes' => 'Konsumsi 20 cup Iced Latte: Gula -300ml',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-05 14:30:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matCup->id,
                    'reference_number' => 'POS-SENJA-20261005-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -20.0,
                    'balance_after' => 180.0,
                    'unit_cost' => 1000.0,
                    'total_cost' => 20000.0,
                    'notes' => 'Konsumsi 20 cup Iced Latte: Cup 16oz -20pcs',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-05 14:30:00'),
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 10. PEMBELIAN BATCH 2 (10 OKTOBER 2026)   HARGA NAIK KE Rp 160.000/kg
            // - Beli 20 kg Biji Kopi Arabica @ Rp 160.000/kg (Rp 160/g) = Rp 3.200.000
            // - Perhitungan Moving Average Kopi:
            //   Stok lama: 9.600g @ Rp 150 = Rp 1.440.000
            //   Masuk baru: 20.000g @ Rp 160 = Rp 3.200.000
            //   Total nilai persediaan: Rp 4.640.000 / 29.600g = Rp 156,757 / gram (Rp 156.757/kg)
            // ─────────────────────────────────────────────────────────────────
            $po2 = PurchaseOrder::updateOrCreate(
                ['business_id' => $business->id, 'po_number' => 'PO-202610-002'],
                [
                    'po_type' => PurchaseOrder::TYPE_SUPPLIER,
                    'supplier_id' => $supplierKopi->id,
                    'order_date' => Carbon::parse('2026-10-10'),
                    'status' => PurchaseOrder::STATUS_COMPLETED,
                    'subtotal' => 3200000.0,
                    'total_amount' => 3200000.0,
                    'notes' => 'Pengadaan Biji Kopi Batch 2 (20 kg @ Rp 160.000/kg)',
                    'created_by' => $owner->id,
                ]
            );

            $gr2 = GoodsReceipt::updateOrCreate(
                ['business_id' => $business->id, 'receipt_number' => 'GR-202610-002'],
                [
                    'location_id' => $location->id,
                    'supplier_id' => $supplierKopi->id,
                    'purchase_order_id' => $po2->id,
                    'receipt_date' => Carbon::parse('2026-10-10'),
                    'status' => 'completed',
                    'notes' => 'Penerimaan biji kopi batch 2 lengkap',
                    'received_by' => $owner->id,
                ]
            );

            MaterialPrice::updateOrCreate(
                ['material_id' => $matKopi->id, 'sequence' => 2],
                [
                    'business_id' => $business->id,
                    'supplier_id' => $supplierKopi->id,
                    'purchase_price' => 160000.0,
                    'purchase_unit_id' => $uKg->id,
                    'yield_percentage' => 100.0,
                    'waste_percentage' => 0.0,
                    'effective_date' => Carbon::parse('2026-10-10'),
                    'notes' => 'Invoice Pembelian #2 (20 kg @ Rp 160.000/kg)',
                ]
            );

            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matKopi->id,
                    'reference_number' => 'GR-202610-002',
                    'movement_type' => StockMovement::TYPE_GOODS_RECEIPT,
                ],
                [
                    'quantity_change' => 20000.0,
                    'balance_after' => 29600.0, // 9.600g + 20.000g = 29.600g
                    'unit_cost' => 160.0,
                    'total_cost' => 3200000.0,
                    'notes' => 'Penerimaan PO-202610-002 (20 kg @ Rp 160.000)',
                    'created_by' => $owner->id,
                    'created_at' => Carbon::parse('2026-10-10 10:00:00'),
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 11. TRANSAKSI PENJUALAN BATCH 2 (15 OKTOBER 2026)
            // - Jual 1 pcs Biji Kopi Standalone @ Rp 165.000 (Stok produk: 28 - 1 = 27 pcs)
            // - Jual 25 cup Iced Latte @ Rp 25.000 = Rp 625.000
            //   Deductions: Kopi -500g (sisa 29.100g), Susu -2.500ml (sisa 15.500ml), Gula -375ml (sisa 4.325ml), Cup -25pcs (sisa 155pcs)
            // ─────────────────────────────────────────────────────────────────
            $order2 = PosOrder::updateOrCreate(
                ['business_id' => $business->id, 'order_number' => 'POS-SENJA-20261015-001'],
                [
                    'location_id' => $location->id,
                    'pos_register_id' => $posRegister->id,
                    'pos_shift_id' => $posShift->id,
                    'user_id' => $cashier->id,
                    'order_date' => Carbon::parse('2026-10-15'),
                    'order_type' => 'dine_in',
                    'sales_channel' => 'pos',
                    'status' => PosOrder::STATUS_COMPLETED,
                    'subtotal' => 790000.0, // (1 x 165.000) + (25 x 25.000) = 790.000
                    'total_amount' => 790000.0,
                    'paid_amount' => 790000.0,
                    'change_amount' => 0.0,
                    'total_hpp_cost' => (1 * 110000.0) + (25 * 6585.0), // Rp 110.000 + Rp 164.625 = Rp 274.625
                    'total_gross_profit' => 790000.0 - 274625.0,
                    'created_at' => Carbon::parse('2026-10-15 15:15:00'),
                ]
            );

            PosOrderItem::updateOrCreate(
                ['pos_order_id' => $order2->id, 'product_id' => $prodStandalone->id],
                [
                    'product_name' => $prodStandalone->name,
                    'product_code' => $prodStandalone->code,
                    'unit_price' => 165000.0,
                    'unit_cost_hpp' => 110000.0,
                    'quantity' => 1.0,
                    'subtotal' => 165000.0,
                    'total_price' => 165000.0,
                    'total_hpp' => 110000.0,
                ]
            );

            PosOrderItem::updateOrCreate(
                ['pos_order_id' => $order2->id, 'product_id' => $prodLatte->id],
                [
                    'product_name' => $prodLatte->name,
                    'product_code' => $prodLatte->code,
                    'unit_price' => 25000.0,
                    'unit_cost_hpp' => 6585.0,
                    'quantity' => 25.0,
                    'subtotal' => 625000.0,
                    'total_price' => 625000.0,
                    'total_hpp' => 164625.0,
                ]
            );

            PosOrderPayment::updateOrCreate(
                ['pos_order_id' => $order2->id, 'payment_method' => 'cash'],
                [
                    'amount' => 790000.0,
                    'net_amount' => 790000.0,
                    'status' => 'paid',
                ]
            );

            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'product_id' => $prodStandalone->id,
                    'reference_number' => 'POS-SENJA-20261015-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -1.0,
                    'balance_after' => 27.0,
                    'unit_cost' => 110000.0,
                    'total_cost' => 110000.0,
                    'notes' => 'Penjualan POS Biji Kopi Standalone 1 pcs',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-15 15:15:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matKopi->id,
                    'reference_number' => 'POS-SENJA-20261015-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -500.0, // 25 cup x 20g
                    'balance_after' => 29100.0,  // 29.600g - 500g = 29.100g
                    'unit_cost' => 156.76,
                    'total_cost' => 78378.0,
                    'notes' => 'Konsumsi 25 cup Iced Latte: Biji Kopi -500g',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-15 15:15:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matSusu->id,
                    'reference_number' => 'POS-SENJA-20261015-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -2500.0,
                    'balance_after' => 15500.0,
                    'unit_cost' => 20.0,
                    'total_cost' => 50000.0,
                    'notes' => 'Konsumsi 25 cup Iced Latte: Susu -2500ml',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-15 15:15:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matGula->id,
                    'reference_number' => 'POS-SENJA-20261015-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -375.0,
                    'balance_after' => 4325.0,
                    'unit_cost' => 30.0,
                    'total_cost' => 11250.0,
                    'notes' => 'Konsumsi 25 cup Iced Latte: Gula -375ml',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-15 15:15:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matCup->id,
                    'reference_number' => 'POS-SENJA-20261015-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -25.0,
                    'balance_after' => 155.0,
                    'unit_cost' => 1000.0,
                    'total_cost' => 25000.0,
                    'notes' => 'Konsumsi 25 cup Iced Latte: Cup 16oz -25pcs',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-15 15:15:00'),
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 12. PEMBELIAN BATCH 3 (20 OKTOBER 2026)   HARGA NAIK KE Rp 170.000/kg
            // - Beli 10 kg Biji Kopi Arabica @ Rp 170.000/kg (Rp 170/g) = Rp 1.700.000
            // - Perhitungan Moving Average Kopi Terkini:
            //   Stok lama: 29.100g @ Rp 156,75676 = Rp 4.561.622
            //   Masuk baru: 10.000g @ Rp 170 = Rp 1.700.000
            //   Total nilai persediaan: Rp 6.261.622 / 39.100g = Rp 160,1438 / gram (Rp 160.144/kg)
            // ─────────────────────────────────────────────────────────────────
            $po3 = PurchaseOrder::updateOrCreate(
                ['business_id' => $business->id, 'po_number' => 'PO-202610-003'],
                [
                    'po_type' => PurchaseOrder::TYPE_SUPPLIER,
                    'supplier_id' => $supplierKopi->id,
                    'order_date' => Carbon::parse('2026-10-20'),
                    'status' => PurchaseOrder::STATUS_COMPLETED,
                    'subtotal' => 1700000.0,
                    'total_amount' => 1700000.0,
                    'notes' => 'Pengadaan Biji Kopi Batch 3 (10 kg @ Rp 170.000/kg)',
                    'created_by' => $owner->id,
                ]
            );

            $gr3 = GoodsReceipt::updateOrCreate(
                ['business_id' => $business->id, 'receipt_number' => 'GR-202610-003'],
                [
                    'location_id' => $location->id,
                    'supplier_id' => $supplierKopi->id,
                    'purchase_order_id' => $po3->id,
                    'receipt_date' => Carbon::parse('2026-10-20'),
                    'status' => 'completed',
                    'notes' => 'Penerimaan biji kopi batch 3 lengkap',
                    'received_by' => $owner->id,
                ]
            );

            MaterialPrice::updateOrCreate(
                ['material_id' => $matKopi->id, 'sequence' => 3],
                [
                    'business_id' => $business->id,
                    'supplier_id' => $supplierKopi->id,
                    'purchase_price' => 170000.0,
                    'purchase_unit_id' => $uKg->id,
                    'yield_percentage' => 100.0,
                    'waste_percentage' => 0.0,
                    'effective_date' => Carbon::parse('2026-10-20'),
                    'notes' => 'Invoice Pembelian #3 (10 kg @ Rp 170.000/kg)',
                ]
            );

            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matKopi->id,
                    'reference_number' => 'GR-202610-003',
                    'movement_type' => StockMovement::TYPE_GOODS_RECEIPT,
                ],
                [
                    'quantity_change' => 10000.0,
                    'balance_after' => 39100.0, // 29.100g + 10.000g = 39.100g
                    'unit_cost' => 170.0,
                    'total_cost' => 1700000.0,
                    'notes' => 'Penerimaan PO-202610-003 (10 kg @ Rp 170.000)',
                    'created_by' => $owner->id,
                    'created_at' => Carbon::parse('2026-10-20 11:00:00'),
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 13. TRANSAKSI PENJUALAN BATCH 3 (25 OKTOBER 2026)
            // - Jual 2 pcs Biji Kopi Standalone @ Rp 165.000 (Stok produk: 27 - 2 = 25 pcs)
            // - Jual 30 cup Iced Latte @ Rp 25.000 = Rp 750.000
            //   Deductions: Kopi -600g (sisa 38.500g), Susu -3.000ml (sisa 12.500ml), Gula -450ml (sisa 3.875ml), Cup -30pcs (sisa 125pcs)
            // ─────────────────────────────────────────────────────────────────
            $order3 = PosOrder::updateOrCreate(
                ['business_id' => $business->id, 'order_number' => 'POS-SENJA-20261025-001'],
                [
                    'location_id' => $location->id,
                    'pos_register_id' => $posRegister->id,
                    'pos_shift_id' => $posShift->id,
                    'user_id' => $cashier->id,
                    'order_date' => Carbon::parse('2026-10-25'),
                    'order_type' => 'takeaway',
                    'sales_channel' => 'pos',
                    'status' => PosOrder::STATUS_COMPLETED,
                    'subtotal' => 1080000.0, // (2 x 165.000) + (30 x 25.000) = 1.080.000
                    'total_amount' => 1080000.0,
                    'paid_amount' => 1080000.0,
                    'change_amount' => 0.0,
                    'total_hpp_cost' => (2 * 110000.0) + (30 * 6653.0), // Rp 220.000 + Rp 199.590 = Rp 419.590
                    'total_gross_profit' => 1080000.0 - 419590.0,
                    'created_at' => Carbon::parse('2026-10-25 16:45:00'),
                ]
            );

            PosOrderItem::updateOrCreate(
                ['pos_order_id' => $order3->id, 'product_id' => $prodStandalone->id],
                [
                    'product_name' => $prodStandalone->name,
                    'product_code' => $prodStandalone->code,
                    'unit_price' => 165000.0,
                    'unit_cost_hpp' => 110000.0,
                    'quantity' => 2.0,
                    'subtotal' => 330000.0,
                    'total_price' => 330000.0,
                    'total_hpp' => 220000.0,
                ]
            );

            PosOrderItem::updateOrCreate(
                ['pos_order_id' => $order3->id, 'product_id' => $prodLatte->id],
                [
                    'product_name' => $prodLatte->name,
                    'product_code' => $prodLatte->code,
                    'unit_price' => 25000.0,
                    'unit_cost_hpp' => 6653.0,
                    'quantity' => 30.0,
                    'subtotal' => 750000.0,
                    'total_price' => 750000.0,
                    'total_hpp' => 199590.0,
                ]
            );

            PosOrderPayment::updateOrCreate(
                ['pos_order_id' => $order3->id, 'payment_method' => 'debit_card'],
                [
                    'amount' => 1080000.0,
                    'net_amount' => 1080000.0,
                    'status' => 'paid',
                ]
            );

            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'product_id' => $prodStandalone->id,
                    'reference_number' => 'POS-SENJA-20261025-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -2.0,
                    'balance_after' => 25.0, // 27 - 2 = 25 pcs
                    'unit_cost' => 110000.0,
                    'total_cost' => 220000.0,
                    'notes' => 'Penjualan POS Biji Kopi Standalone 2 pcs',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-25 16:45:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matKopi->id,
                    'reference_number' => 'POS-SENJA-20261025-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -600.0, // 30 cup x 20g
                    'balance_after' => 38500.0,  // 39.100g - 600g = 38.500g
                    'unit_cost' => 160.14,
                    'total_cost' => 96084.0,
                    'notes' => 'Konsumsi 30 cup Iced Latte: Biji Kopi -600g',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-25 16:45:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matSusu->id,
                    'reference_number' => 'POS-SENJA-20261025-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -3000.0,
                    'balance_after' => 12500.0,  // 15.500ml - 3.000ml = 12.500ml
                    'unit_cost' => 20.0,
                    'total_cost' => 60000.0,
                    'notes' => 'Konsumsi 30 cup Iced Latte: Susu -3000ml',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-25 16:45:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matGula->id,
                    'reference_number' => 'POS-SENJA-20261025-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -450.0,
                    'balance_after' => 3875.0,   // 4.325ml - 450ml = 3.875ml
                    'unit_cost' => 30.0,
                    'total_cost' => 13500.0,
                    'notes' => 'Konsumsi 30 cup Iced Latte: Gula -450ml',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-25 16:45:00'),
                ]
            );
            StockMovement::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'material_id' => $matCup->id,
                    'reference_number' => 'POS-SENJA-20261025-001',
                    'movement_type' => StockMovement::TYPE_POS_SALE,
                ],
                [
                    'quantity_change' => -30.0,
                    'balance_after' => 125.0,    // 155pcs - 30pcs = 125pcs
                    'unit_cost' => 1000.0,
                    'total_cost' => 30000.0,
                    'notes' => 'Konsumsi 30 cup Iced Latte: Cup 16oz -30pcs',
                    'created_by' => $cashier->id,
                    'created_at' => Carbon::parse('2026-10-25 16:45:00'),
                ]
            );

            // ─────────────────────────────────────────────────────────────────
            // 14. UPDATE FINAL INVENTORY STOCKS & MOVING AVERAGE VALUE
            // ─────────────────────────────────────────────────────────────────
            // 1. Produk Standalone (CFE-BEAN-ARA-1KG): Sisa 25 pcs
            InventoryStock::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'product_id' => $prodStandalone->id,
                    'material_id' => null,
                ],
                [
                    'quantity' => 25.0,
                    'last_cost' => 110000.0,
                    'avg_purchase_cost' => 110000.0,
                ]
            );

            // 2. Bahan Kopi (CFE-MAT-BEANS-ARA): Sisa 38.500 gram (38,5 kg)
            // Moving average akhir: Rp 160.144 / kg = Rp 160,144 / gram
            InventoryStock::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'product_id' => null,
                    'material_id' => $matKopi->id,
                ],
                [
                    'quantity' => 38500.0,
                    'last_cost' => 170.0,
                    'avg_purchase_cost' => 160.144,
                ]
            );

            // 3. Bahan Susu (CFE-MAT-MILK-UHT): Sisa 12.500 ml
            InventoryStock::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'product_id' => null,
                    'material_id' => $matSusu->id,
                ],
                [
                    'quantity' => 12500.0,
                    'last_cost' => 20.0,
                    'avg_purchase_cost' => 20.0,
                ]
            );

            // 4. Bahan Gula (CFE-MAT-SUGAR-LIQ): Sisa 3.875 ml
            InventoryStock::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'product_id' => null,
                    'material_id' => $matGula->id,
                ],
                [
                    'quantity' => 3875.0,
                    'last_cost' => 30.0,
                    'avg_purchase_cost' => 30.0,
                ]
            );

            // 5. Bahan Cup (CFE-MAT-CUP-LID): Sisa 125 pcs
            InventoryStock::updateOrCreate(
                [
                    'business_id' => $business->id,
                    'location_id' => $location->id,
                    'product_id' => null,
                    'material_id' => $matCup->id,
                ],
                [
                    'quantity' => 125.0,
                    'last_cost' => 1000.0,
                    'avg_purchase_cost' => 1000.0,
                ]
            );
        });
    }
}
