@if (view()->exists("public.storefront.themes.retail_building.footer"))
    @include("public.storefront.themes.retail_building.footer")
@else
    @include("public.storefront.layouts.footer")
@endif