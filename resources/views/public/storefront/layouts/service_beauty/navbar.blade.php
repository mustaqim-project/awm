@if (view()->exists("public.storefront.themes.service_beauty.navbar"))
    @include("public.storefront.themes.service_beauty.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif