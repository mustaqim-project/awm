{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Universal Storefront (Bento Multi-Bisnis) (Universal Storefront untuk Segala Jenis Usaha)   --}}
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

@if (view()->exists("public.storefront.themes.universal_storefront.product_card"))
    @include("public.storefront.themes.universal_storefront.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif