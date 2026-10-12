@if (view()->exists("public.storefront.themes.fnb_bakery.navbar"))
    @include("public.storefront.themes.fnb_bakery.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif