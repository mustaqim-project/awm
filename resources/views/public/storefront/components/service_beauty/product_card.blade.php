{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Aura Glamour (Klinik Estetika & Salon) (Klinik Kecantikan, Estetika Kulit, Salon & Spa)   --}}
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

@if (view()->exists("public.storefront.themes.service_beauty.product_card"))
    @include("public.storefront.themes.service_beauty.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif