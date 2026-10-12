{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Vogue Minimalist (Fashion & Butik) (Butik Fashion, Pakaian Pria/Wanita & Aksesoris)   --}}
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

@if (view()->exists("public.storefront.themes.retail_fashion.product_card"))
    @include("public.storefront.themes.retail_fashion.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif