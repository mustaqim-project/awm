{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Flora Romance (Florist & Karangan Bunga) (Florist, Bunga Segar, Buket Wisuda & Papan Bunga)   --}}
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

@if (view()->exists("public.storefront.themes.retail_florist.product_card"))
    @include("public.storefront.themes.retail_florist.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif