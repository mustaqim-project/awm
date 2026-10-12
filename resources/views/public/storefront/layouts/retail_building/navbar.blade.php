@if (view()->exists("public.storefront.themes.retail_building.navbar"))
    @include("public.storefront.themes.retail_building.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif