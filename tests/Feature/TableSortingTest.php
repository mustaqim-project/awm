<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Traits\SortableQueryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class SortingSampleItem extends Model
{
    protected $table = 'sorting_sample_items';
    protected $guarded = [];
}

final class TableSortingTest extends TestCase
{
    use RefreshDatabase;
    use SortableQueryTrait;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('sorting_sample_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->decimal('price', 15, 2);
            $table->timestamps();
        });

        SortingSampleItem::create(['name' => 'Buku Catatan', 'price' => 15000, 'created_at' => now()->subDays(2)]);
        SortingSampleItem::create(['name' => 'Alat Tulis', 'price' => 5000, 'created_at' => now()->subDay()]);
        SortingSampleItem::create(['name' => 'Cangkir Kopi', 'price' => 45000, 'created_at' => now()]);
    }

    public function test_sortable_query_trait_orders_ascending_correctly(): void
    {
        $request = Request::create('/test', 'GET', ['sort' => 'name', 'dir' => 'asc']);
        $query = SortingSampleItem::query();
        $this->applySorting($query, $request, ['name', 'price', 'created_at']);

        $results = $query->pluck('name')->toArray();
        $this->assertSame(['Alat Tulis', 'Buku Catatan', 'Cangkir Kopi'], $results);
    }

    public function test_sortable_query_trait_orders_descending_correctly(): void
    {
        $request = Request::create('/test', 'GET', ['sort' => 'price', 'dir' => 'desc']);
        $query = SortingSampleItem::query();
        $this->applySorting($query, $request, ['name', 'price', 'created_at']);

        $results = $query->pluck('price')->map(fn($p) => (float)$p)->toArray();
        $this->assertSame([45000.0, 15000.0, 5000.0], $results);
    }

    public function test_sortable_query_trait_blocks_unauthorized_column_and_falls_back_safely(): void
    {
        // Malicious column attempt or SQL injection pattern
        $request = Request::create('/test', 'GET', ['sort' => 'password_hash;DROP TABLE', 'dir' => 'asc']);
        $query = SortingSampleItem::query();
        $this->applySorting($query, $request, ['name', 'price'], 'created_at', 'desc');

        // Should fallback to created_at desc (Cangkir Kopi was created last)
        $firstItem = $query->first();
        $this->assertSame('Cangkir Kopi', $firstItem->name);
    }

    public function test_sortable_query_trait_supports_mapped_columns(): void
    {
        $request = Request::create('/test', 'GET', ['sort' => 'nominal', 'dir' => 'asc']);
        $query = SortingSampleItem::query();
        $this->applySorting($query, $request, ['nominal' => 'price'], 'created_at', 'desc');

        $firstItem = $query->first();
        $this->assertSame('Alat Tulis', $firstItem->name);
    }

    public function test_sortable_th_blade_component_renders_correctly(): void
    {
        $request = Request::create('/products', 'GET', ['search' => 'kopi', 'sort' => 'price', 'dir' => 'asc']);
        $this->app->instance('request', $request);

        $html = Blade::render(
            '<x-sortable-th column="price" title="Harga Jual" align="right" />'
        );

        $this->assertStringContainsString('Harga Jual', $html);
        $this->assertStringContainsString('sort=price', $html);
        $this->assertStringContainsString('dir=desc', $html); // Toggle to desc on next click
        $this->assertStringContainsString('search=kopi', $html); // Preserves existing query
        $this->assertStringContainsString('text-right', $html);
    }

    public function test_client_table_sort_th_blade_component_renders_correctly(): void
    {
        $html = Blade::render(
            '<x-table-sort-th :col="3" type="number" title="Harga Jual" align="right" />'
        );

        $this->assertStringContainsString("sortBy(3, 'number', \$event)", $html);
        $this->assertStringContainsString('Harga Jual', $html);
        $this->assertStringContainsString('sortCol === 3', $html);
        $this->assertStringContainsString('text-right', $html);
    }
}
