@if (view()->exists("public.storefront.themes.retail_grocery.footer"))
    @include("public.storefront.themes.retail_grocery.footer")
@else
    @include("public.storefront.layouts.footer")
@endif