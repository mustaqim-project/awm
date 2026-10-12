@if (view()->exists("public.storefront.themes.service_petshop.navbar"))
    @include("public.storefront.themes.service_petshop.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif