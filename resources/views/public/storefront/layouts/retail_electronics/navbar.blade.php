@if (view()->exists("public.storefront.themes.retail_electronics.navbar"))
    @include("public.storefront.themes.retail_electronics.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif