@if (view()->exists("public.storefront.themes.retail_bookstore.footer"))
    @include("public.storefront.themes.retail_bookstore.footer")
@else
    @include("public.storefront.layouts.footer")
@endif