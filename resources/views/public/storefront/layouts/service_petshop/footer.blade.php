@if (view()->exists("public.storefront.themes.service_petshop.footer"))
    @include("public.storefront.themes.service_petshop.footer")
@else
    @include("public.storefront.layouts.footer")
@endif