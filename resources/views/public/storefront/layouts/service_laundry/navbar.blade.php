@if (view()->exists("public.storefront.themes.service_laundry.navbar"))
    @include("public.storefront.themes.service_laundry.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif