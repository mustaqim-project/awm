{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Hydro Shield (Auto Detailing & Cuci Mobil) (Cuci Mobil Hidrolik, Detailing, Coating & Salon Mobil)   --}}
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

@if (view()->exists("public.storefront.themes.service_carwash.product_card"))
    @include("public.storefront.themes.service_carwash.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif