@if (view()->exists("public.storefront.themes.fnb_resto.navbar"))
    @include("public.storefront.themes.fnb_resto.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif