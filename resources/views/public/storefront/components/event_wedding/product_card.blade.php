{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Event & Wedding (Event Organizer, Wedding Organizer, Dekorasi & Rental)   --}}
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

@if (view()->exists("public.storefront.themes.event_wedding.product_card"))
    @include("public.storefront.themes.event_wedding.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif