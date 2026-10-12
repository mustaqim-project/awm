@if (view()->exists("public.storefront.themes.service_carwash.navbar"))
    @include("public.storefront.themes.service_carwash.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif