@if (view()->exists("public.storefront.themes.service_agency.navbar"))
    @include("public.storefront.themes.service_agency.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif