@if (view()->exists("public.storefront.themes.service_printing.footer"))
    @include("public.storefront.themes.service_printing.footer")
@else
    @include("public.storefront.layouts.footer")
@endif