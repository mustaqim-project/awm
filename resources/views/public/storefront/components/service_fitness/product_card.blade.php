{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Titan Kinetic (Gym & Fitness Center) (Pusat Kebugaran, Gym, Yoga & Personal Trainer)   --}}
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

@if (view()->exists("public.storefront.themes.service_fitness.product_card"))
    @include("public.storefront.themes.service_fitness.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif