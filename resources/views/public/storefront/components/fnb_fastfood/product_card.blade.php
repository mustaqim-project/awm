{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Neon Crunch (Fast Food & Snack) (Fast Food, Burger, Ayam Crispy & Snack)   --}}
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

@if (view()->exists("public.storefront.themes.fnb_fastfood.product_card"))
    @include("public.storefront.themes.fnb_fastfood.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif