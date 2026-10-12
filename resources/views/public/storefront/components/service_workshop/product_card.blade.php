{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Apex Velocity (Bengkel Mobil & Motor) (Bengkel Mobil, Motor, Ganti Oli & Sparepart Asli)   --}}
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

@if (view()->exists("public.storefront.themes.service_workshop.product_card"))
    @include("public.storefront.themes.service_workshop.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif