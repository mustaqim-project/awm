{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Clinical Pure (Apotek & Alkes) (Apotek, Obat Resep Dokter, Vitamin & Alat Medis)   --}}
{{-- ========================================================================= --}}
@props([
    'product' => null,
    'item' => null,
    'business' => null,
    'theme' => null,
])

@php
    $productItem = $product ?? $item;
@endphp

@if (view()->exists("public.storefront.themes.retail_pharmacy.product_card"))
    @include("public.storefront.themes.retail_pharmacy.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif