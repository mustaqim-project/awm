@if (view()->exists("public.storefront.themes.retail_grocery.navbar"))
    @include("public.storefront.themes.retail_grocery.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif