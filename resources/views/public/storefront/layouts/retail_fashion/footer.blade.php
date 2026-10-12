@if (view()->exists("public.storefront.themes.retail_fashion.footer"))
    @include("public.storefront.themes.retail_fashion.footer")
@else
    @include("public.storefront.layouts.footer")
@endif