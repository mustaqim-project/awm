@if (view()->exists("public.storefront.themes.service_carwash.footer"))
    @include("public.storefront.themes.service_carwash.footer")
@else
    @include("public.storefront.layouts.footer")
@endif