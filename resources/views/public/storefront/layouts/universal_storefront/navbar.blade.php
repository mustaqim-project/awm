@if (view()->exists("public.storefront.themes.universal_storefront.navbar"))
    @include("public.storefront.themes.universal_storefront.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif