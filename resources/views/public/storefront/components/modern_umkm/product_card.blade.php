{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Modern UMKM (Usaha Kecil, Usaha Rumahan & Bisnis Umum)   --}}
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

@if (view()->exists("public.storefront.themes.modern_umkm.product_card"))
    @include("public.storefront.themes.modern_umkm.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif