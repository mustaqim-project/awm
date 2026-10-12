@if (view()->exists("public.storefront.themes.retail_florist.footer"))
    @include("public.storefront.themes.retail_florist.footer")
@else
    @include("public.storefront.layouts.footer")
@endif