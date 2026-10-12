@if (view()->exists("public.storefront.themes.fnb_catering.navbar"))
    @include("public.storefront.themes.fnb_catering.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif