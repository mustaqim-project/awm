<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

final class SearchableSelectComponentTest extends TestCase
{
    public function test_slot_mode_renders_native_select_and_searchable_wrapper(): void
    {
        $view = Blade::render(
            <<<'BLADE'
            <x-searchable-select placeholder="Pilih Kategori">
                <select name="category_id" id="cat-select" required class="sr-only">
                    <option value="">Semua Kategori</option>
                    <option value="1" selected>Elektronik</option>
                    <option value="2">Pakaian</option>
                </select>
            </x-searchable-select>
            BLADE
        );

        $this->assertStringContainsString('x-data="coocaSearchableSelect', $view);
        $this->assertStringContainsString('name="category_id"', $view);
        $this->assertStringContainsString('id="cat-select"', $view);
        $this->assertStringContainsString('required', $view);
        $this->assertStringContainsString('class="sr-only"', $view);
        $this->assertStringContainsString('Pilih Kategori', $view);
        $this->assertStringContainsString('Elektronik', $view);
        $this->assertStringContainsString('Pakaian', $view);
        $this->assertStringContainsString('x-ref="triggerButton"', $view);
        $this->assertStringContainsString('x-ref="searchInput"', $view);
    }

    public function test_props_mode_generates_native_select_options(): void
    {
        $options = [
            'opt1' => 'Pilihan Satu',
            'opt2' => 'Pilihan Dua',
        ];

        $view = Blade::render(
            <<<'BLADE'
            <x-searchable-select
                name="my_selection"
                id="my-id"
                :options="$options"
                value="opt2"
                placeholder="Pilih item..."
            />
            BLADE,
            ['options' => $options]
        );

        $this->assertStringContainsString('name="my_selection"', $view);
        $this->assertStringContainsString('id="my-id"', $view);
        $this->assertStringContainsString('value="opt1"', $view);
        $this->assertStringContainsString('value="opt2"', $view);
        $this->assertStringContainsString('selected', $view);
        $this->assertStringContainsString('Pilihan Satu', $view);
        $this->assertStringContainsString('Pilihan Dua', $view);
    }

    public function test_popover_has_bento_hig_tokens_and_search_input(): void
    {
        $view = Blade::render(
            <<<'BLADE'
            <x-searchable-select placeholder="Pilih..." searchPlaceholder="Ketik pencarian...">
                <select name="unit">
                    <option value="pcs">Pcs</option>
                </select>
            </x-searchable-select>
            BLADE
        );

        $this->assertStringContainsString('rounded-[14px]', $view);
        $this->assertStringContainsString('bg-white dark:bg-[#1C1C1E]', $view);
        $this->assertStringContainsString('placeholder="Ketik pencarian..."', $view);
        $this->assertStringContainsString('custom-scrollbar', $view);
    }

    public function test_component_javascript_defines_event_dispatchers(): void
    {
        $view = Blade::render(
            <<<'BLADE'
            <x-searchable-select>
                <select name="test">
                    <option value="1">1</option>
                </select>
            </x-searchable-select>
            BLADE
        );

        $this->assertStringContainsString("dispatchEvent(new Event('input', { bubbles: true }))", $view);
        $this->assertStringContainsString("dispatchEvent(new Event('change', { bubbles: true }))", $view);
        $this->assertStringContainsString('MutationObserver', $view);
    }
}
