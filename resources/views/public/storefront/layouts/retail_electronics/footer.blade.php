@if (view()->exists("public.storefront.themes.retail_electronics.footer"))
    @include("public.storefront.themes.retail_electronics.footer")
@else
    @include("public.storefront.layouts.footer")
@endif