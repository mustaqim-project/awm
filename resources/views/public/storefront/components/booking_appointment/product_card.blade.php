{{-- ========================================================================= --}}
{{-- PRODUCT CARD COMPONENT: Booking & Appointment (Reservasi Salon, Klinik, Bengkel, Studio & Konsultasi)   --}}
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

@if (view()->exists("public.storefront.themes.booking_appointment.product_card"))
    @include("public.storefront.themes.booking_appointment.product_card", ['item' => $productItem, 'product' => $productItem, 'business' => $business, 'theme' => $theme])
@else
    @include("public.storefront.components.product_card", ['product' => $productItem, 'business' => $business, 'theme' => $theme])
@endif