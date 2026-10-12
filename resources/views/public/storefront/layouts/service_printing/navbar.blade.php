@if (view()->exists("public.storefront.themes.service_printing.navbar"))
    @include("public.storefront.themes.service_printing.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif