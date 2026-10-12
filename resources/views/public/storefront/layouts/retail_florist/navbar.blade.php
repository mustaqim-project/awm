@if (view()->exists("public.storefront.themes.retail_florist.navbar"))
    @include("public.storefront.themes.retail_florist.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif