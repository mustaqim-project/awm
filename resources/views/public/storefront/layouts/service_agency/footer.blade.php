@if (view()->exists("public.storefront.themes.service_agency.footer"))
    @include("public.storefront.themes.service_agency.footer")
@else
    @include("public.storefront.layouts.footer")
@endif