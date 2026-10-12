@if (view()->exists("public.storefront.themes.retail_fashion.navbar"))
    @include("public.storefront.themes.retail_fashion.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif