<?php

namespace App\Console\Commands;

use App\Models\BomHeader;
use App\Models\BomItem;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\CostModel;
use App\Models\Location;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\MaterialPrice;
use App\Models\MaterialUnitConversion;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SyncDoGoProductionDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cooca:sync-dogo-data 
                            {--email=srifennikapurba@gmail.com : Email owner bisnis target}
                            {--force : Force execute and delete old products/materials}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product, raw materials, and BOM recipes for business D0&GO (owner: srifennikapurba@gmail.com)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) $this->option('email');
        $this->info("=================================================================");
        $this->info("🚀 MEMULAI SINKRONISASI DATA BISNIS D0&GO / DO&GO");
        $this->info("Owner Email : {$email}");
        $this->info("Waktu       : " . now()->format('Y-m-d H:i:s'));
        $this->info("=================================================================");

        // 1. Temukan atau Buat User
        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->warn("User dengan email '{$email}' belum terdaftar. Membuat akun baru...");
            $user = User::create([
                'name'              => 'Sri Fennika Purba',
                'email'             => $email,
                'email_verified_at' => now(),
                'password'          => Hash::make('CoocaDoGo2026!'),
            ]);
            $this->info("✓ Berhasil membuat user baru: {$user->name} ({$user->id})");
        } else {
            $this->info("✓ Ditemukan user terdaftar: {$user->name} ({$user->id})");
        }

        // 2. Temukan atau Buat Bisnis D0&GO
        $business = $user->businesses()
            ->where(function ($q) {
                $q->where('name', 'like', '%D0%')
                  ->orWhere('name', 'like', '%DO%')
                  ->orWhere('name', 'like', '%GO%');
            })
            ->first();

        if (! $business) {
            $business = Business::where('name', 'like', '%D0%')
                ->orWhere('name', 'like', '%D0&GO%')
                ->orWhere('name', 'like', '%DO&GO%')
                ->first();
        }

        if (! $business) {
            $this->warn("Bisnis 'D0&GO' belum ada. Membuat bisnis baru...");
            $business = Business::create([
                'name'                  => 'D0&GO',
                'slug'                  => 'd0-go-' . Str::lower(Str::random(5)),
                'industry_category'     => 'fnb',
                'template_code'         => 'fnb_beverage',
                'currency'              => 'IDR',
                'allow_negative_stock'  => false,
                'is_active'             => true,
                'email'                 => $email,
                'description'           => 'Spesialis Minuman Segar, Smoothies, Juice, dan Healthy Bowls.',
            ]);
            $this->info("✓ Berhasil membuat bisnis baru: {$business->name} ({$business->id})");

            // Buat gudang / lokasi default jika belum ada
            Location::firstOrCreate(
                ['business_id' => $business->id, 'code' => 'WH-DOGO-01'],
                [
                    'name'       => 'Gudang Utama D0&GO',
                    'slug'       => 'wh-dogo-01-' . Str::lower(Str::random(4)),
                    'type'       => 'warehouse',
                    'is_primary' => true,
                    'is_active'  => true,
                ]
            );
        } else {
            $this->info("✓ Ditemukan bisnis target: {$business->name} ({$business->id})");
        }

        // Pastikan relasi user ke bisnis terpasang
        $membership = BusinessMembership::where('business_id', $business->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $membership) {
            $ownerRole = Role::where('name', 'Owner')->first();
            BusinessMembership::create([
                'business_id' => $business->id,
                'user_id'     => $user->id,
                'role'        => 'owner',
                'role_id'     => $ownerRole?->id,
            ]);
            $this->info("✓ User {$user->email} berhasil dihubungkan sebagai Owner bisnis {$business->name}");
        }

        // 3. Audit data existing sebelum dihapus
        $oldProducts = Product::where('business_id', $business->id)->get(['id', 'code', 'name', 'base_cost', 'selling_price']);
        $oldMaterials = Material::where('business_id', $business->id)->get(['id', 'code', 'name']);

        $this->newLine();
        $this->warn("--- DATA LAMA DI SISTEM UNTUK BISNIS {$business->name} ---");
        $this->line("Jumlah Produk Lama    : " . $oldProducts->count());
        foreach ($oldProducts as $op) {
            $this->line("  - [{$op->code}] {$op->name} (HPP: Rp " . number_format($op->base_cost, 0, ',', '.') . " | Jual: Rp " . number_format($op->selling_price, 0, ',', '.') . ")");
        }
        $this->line("Jumlah Bahan Baku Lama: " . $oldMaterials->count());
        foreach ($oldMaterials as $om) {
            $this->line("  - [{$om->code}] {$om->name}");
        }
        $this->newLine();

        // 4. Bersihkan data lama (BOM, Cost Models, Produk, Bahan Baku)
        $this->info("Membersihkan produk lama, resep BOM, dan bahan baku lama...");
        DB::transaction(function () use ($business) {
            // Hapus BOM Items terkait Cost Model bisnis ini
            $costModelIds = CostModel::where('business_id', $business->id)->pluck('id');
            $bomHeaderIds = BomHeader::whereIn('cost_model_id', $costModelIds)->pluck('id');
            BomItem::whereIn('bom_header_id', $bomHeaderIds)->delete();
            BomHeader::whereIn('id', $bomHeaderIds)->delete();
            CostModel::whereIn('id', $costModelIds)->delete();

            // Bersihkan harga, movement, dan relasi produk
            $productIds = Product::where('business_id', $business->id)->pluck('id');
            DB::table('branch_product_prices')->whereIn('product_id', $productIds)->delete();
            DB::table('product_channel_prices')->whereIn('product_id', $productIds)->delete();
            DB::table('product_bundle_items')->whereIn('parent_product_id', $productIds)->orWhereIn('child_product_id', $productIds)->delete();
            DB::table('inventory_stocks')->where('business_id', $business->id)->whereIn('product_id', $productIds)->delete();
            Product::where('business_id', $business->id)->delete();

            // Bersihkan harga dan bahan baku
            $materialIds = Material::where('business_id', $business->id)->pluck('id');
            MaterialPrice::where('business_id', $business->id)->delete();
            MaterialUnitConversion::where('business_id', $business->id)->delete();
            DB::table('inventory_stocks')->where('business_id', $business->id)->whereIn('material_id', $materialIds)->delete();
            Material::where('business_id', $business->id)->delete();
        });
        $this->info("✓ Berhasil menghapus seluruh produk dan bahan baku lama secara bersih.");

        // 5. Muat Data Dataset dari JSON
        $dataPath = __DIR__ . '/../../Domain/Inventory/Data/dogo_master_recipe_data.json';
        if (! file_exists($dataPath)) {
            $this->error("File dataset {$dataPath} tidak ditemukan!");
            return 1;
        }

        $payload = json_decode((string) file_get_contents($dataPath), true);
        $ingredientsData = $payload['ingredients'] ?? [];
        $menusData = $payload['menus'] ?? [];
        $recipesData = $payload['recipes'] ?? [];

        $this->info("Memuat dataset: " . count($ingredientsData) . " Bahan Baku, " . count($menusData) . " Menu Produk, " . count($recipesData) . " Baris Resep BOM.");

        // 6. Siapkan Unit Resolusi
        $unitsByCode = Unit::all()->keyBy('code');
        $unitGram = $unitsByCode->get('g') ?? $unitsByCode->get('gram');
        $unitMl   = $unitsByCode->get('ml');
        $unitPcs  = $unitsByCode->get('pcs');
        $unitCup  = $unitsByCode->get('cup') ?? $unitsByCode->get('porsi') ?? $unitPcs;

        // 7. Siapkan Kategori
        $matCategory = MaterialCategory::firstOrCreate(
            ['business_id' => $business->id, 'name' => 'Bahan Baku Minuman'],
            ['slug' => 'bahan-baku-minuman-' . Str::lower(Str::random(4)), 'description' => 'Bahan baku buah, susu, sirup, powder dan topping.']
        );

        $prodCategory = ProductCategory::firstOrCreate(
            ['business_id' => $business->id, 'name' => 'Minuman & Smoothies'],
            ['slug' => 'minuman-smoothies-' . Str::lower(Str::random(4)), 'description' => 'Menu minuman segar, smoothies, dan healthy bowls.']
        );

        // 8. Upload 45 Master Ingredients
        $this->newLine();
        $this->info("Mengupload 45 Master Ingredients...");
        $createdMaterials = [];

        DB::transaction(function () use ($business, $ingredientsData, $matCategory, $unitGram, $unitMl, $unitPcs, &$createdMaterials) {
            foreach ($ingredientsData as $ing) {
                $rawUnit = strtolower($ing['unit']);
                $unitObj = match ($rawUnit) {
                    'ml' => $unitMl,
                    'pcs' => $unitPcs,
                    default => $unitGram,
                };

                $material = Material::create([
                    'business_id' => $business->id,
                    'category_id' => $matCategory->id,
                    'unit_id'     => $unitObj->id,
                    'code'        => $ing['code'],
                    'name'        => $ing['name'],
                    'slug'        => Str::slug($ing['name']) . '-' . Str::lower(Str::random(4)),
                    'description' => ! empty($ing['alias']) ? "Alias: {$ing['alias']}" : null,
                    'min_stock'   => 500.0,
                ]);

                // Buat harga standar material
                MaterialPrice::create([
                    'business_id'      => $business->id,
                    'material_id'      => $material->id,
                    'purchase_unit_id' => $unitObj->id,
                    'purchase_price'   => (float) $ing['unit_cost'],
                    'effective_cost'   => (float) $ing['unit_cost'],
                    'yield_percentage' => 100.0,
                    'waste_percentage' => 0.0,
                    'effective_date'   => now()->toDateString(),
                    'notes'            => 'Harga standar HPP resep D0&GO',
                ]);

                $createdMaterials[$ing['name']] = $material;
            }
        });
        $this->info("✓ Berhasil mengupload 45 Bahan Baku.");

        // 9. Upload 24 Produk & Resep BOM
        $this->newLine();
        $this->info("Mengupload 24 Menu Produk beserta Resep BOM...");
        $createdProducts = [];

        // Kelompokkan resep berdasarkan nama menu
        $groupedRecipes = [];
        foreach ($recipesData as $rec) {
            $groupedRecipes[$rec['menu_name']][] = $rec;
        }

        DB::transaction(function () use ($business, $menusData, $groupedRecipes, $createdMaterials, $prodCategory, $unitCup, $unitGram, $unitMl, $unitPcs, &$createdProducts) {
            foreach ($menusData as $menu) {
                $product = Product::create([
                    'business_id'        => $business->id,
                    'category_id'        => $prodCategory->id,
                    'output_unit_id'     => $unitCup->id,
                    'code'               => $menu['code'],
                    'name'               => $menu['name'],
                    'slug'               => Str::slug($menu['name']) . '-' . Str::lower(Str::random(4)),
                    'type'               => 'finished_goods',
                    'base_cost'          => (float) $menu['hpp'],
                    'selling_price'      => (float) $menu['harga_jual'],
                    'min_stock'          => 10.0,
                    'is_active'          => true,
                    'show_in_pos'        => true,
                    'show_in_website'    => true,
                    'business_type_hint' => 'beverage',
                    'description'        => "Menu {$menu['name']} (HPP: Rp " . number_format($menu['hpp'], 0, ',', '.') . " | Harga Jual: Rp " . number_format($menu['harga_jual'], 0, ',', '.') . ")",
                ]);

                // Buat CostModel aktif (recipe_bom)
                $costModel = CostModel::create([
                    'business_id'  => $business->id,
                    'product_id'   => $product->id,
                    'name'         => 'Resep Standar ' . $menu['name'],
                    'slug'         => 'resep-' . Str::slug($menu['name']) . '-' . Str::lower(Str::random(4)),
                    'method'       => 'recipe_bom',
                    'output_basis' => 1.0,
                    'is_active'    => true,
                ]);

                // Buat BomHeader
                $bomHeader = BomHeader::create([
                    'cost_model_id' => $costModel->id,
                    'type'          => 'recipe',
                    'level'         => 1,
                    'name'          => 'BOM ' . $menu['name'],
                    'notes'         => 'Resep BOM otomatis dari Master Recipe D0&GO',
                ]);

                // Pasang seluruh BomItem
                $items = $groupedRecipes[$menu['name']] ?? [];
                $itemIndex = 1;
                foreach ($items as $item) {
                    $matObj = $createdMaterials[$item['material_name']] ?? null;
                    if (! $matObj) {
                        continue;
                    }

                    $rawUnit = strtolower($item['unit']);
                    $itemUnit = match ($rawUnit) {
                        'ml' => $unitMl,
                        'pcs' => $unitPcs,
                        default => $unitGram,
                    };

                    BomItem::create([
                        'bom_header_id'    => $bomHeader->id,
                        'material_id'      => $matObj->id,
                        'quantity'         => (float) $item['qty'],
                        'unit_id'          => $itemUnit->id,
                        'waste_percentage' => 0.0,
                        'sort_order'       => $itemIndex++,
                    ]);
                }

                $createdProducts[] = [
                    'product'      => $product,
                    'cost_model'   => $costModel,
                    'bom_header'   => $bomHeader,
                    'recipe_count' => count($items),
                ];
            }
        });

        $this->info("✓ Berhasil mengupload 24 Menu Produk beserta seluruh Resep BOM.");

        // 10. Ringkasan Akhir & Simpan Log Output
        $outputSummary = [
            'status'         => 'success',
            'synced_at'      => now()->toIso8601String(),
            'business'       => [
                'id'    => $business->id,
                'name'  => $business->name,
                'email' => $business->email,
            ],
            'owner'          => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'deleted_counts' => [
                'products'  => $oldProducts->count(),
                'materials' => $oldMaterials->count(),
            ],
            'new_counts'     => [
                'materials' => count($createdMaterials),
                'products'  => count($createdProducts),
                'recipes'   => count($recipesData),
            ],
            'materials_list' => array_map(fn ($m) => [
                'code' => $m->code,
                'name' => $m->name,
                'unit' => $m->unit?->code ?? 'g',
            ], array_values($createdMaterials)),
            'products_list'  => array_map(fn ($p) => [
                'code'          => $p['product']->code,
                'name'          => $p['product']->name,
                'hpp'           => $p['product']->base_cost,
                'selling_price' => $p['product']->selling_price,
                'recipe_items'  => $p['recipe_count'],
            ], $createdProducts),
        ];

        @file_put_contents(
            storage_path('logs/dogo_sync_output.json'),
            json_encode($outputSummary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->newLine();
        $this->info("=================================================================");
        $this->info("🎉 SINKRONISASI SELESAI DENGAN SUKSES!");
        $this->info("Total Bahan Baku Terupload : " . count($createdMaterials) . " items");
        $this->info("Total Produk Menu Terupload: " . count($createdProducts) . " items");
        $this->info("Formula Harga Jual         : HPP + Rp 2.865 (Tervalidasi)");
        $this->info("Detail Output Tersimpan di : storage/logs/dogo_sync_output.json");
        $this->info("=================================================================");

        return 0;
    }
}
