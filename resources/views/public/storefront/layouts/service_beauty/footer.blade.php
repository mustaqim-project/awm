@if (view()->exists("public.storefront.themes.service_beauty.footer"))
    @include("public.storefront.themes.service_beauty.footer")
@else
    @include("public.storefront.layouts.footer")
@endif