@if (view()->exists("public.storefront.themes.service_laundry.footer"))
    @include("public.storefront.themes.service_laundry.footer")
@else
    @include("public.storefront.layouts.footer")
@endif