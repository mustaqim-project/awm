@if (view()->exists("public.storefront.themes.retail_bookstore.navbar"))
    @include("public.storefront.themes.retail_bookstore.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif