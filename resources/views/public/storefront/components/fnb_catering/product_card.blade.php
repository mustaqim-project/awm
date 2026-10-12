{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Epicurean Box (Catering & Bento) (Catering Harian, Prasmanan & Bento Box)   --}}
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

@if (view()->exists("public.storefront.themes.fnb_catering.product_card"))
    @include("public.storefront.themes.fnb_catering.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif