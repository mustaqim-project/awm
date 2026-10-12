@if (view()->exists("public.storefront.themes.fnb_fastfood.navbar"))
    @include("public.storefront.themes.fnb_fastfood.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif