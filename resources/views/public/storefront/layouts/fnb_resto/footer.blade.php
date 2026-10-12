@if (view()->exists("public.storefront.themes.fnb_resto.footer"))
    @include("public.storefront.themes.fnb_resto.footer")
@else
    @include("public.storefront.layouts.footer")
@endif