@if (view()->exists("public.storefront.themes.service_workshop.footer"))
    @include("public.storefront.themes.service_workshop.footer")
@else
    @include("public.storefront.layouts.footer")
@endif