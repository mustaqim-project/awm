@if (view()->exists("public.storefront.themes.fnb_catering.footer"))
    @include("public.storefront.themes.fnb_catering.footer")
@else
    @include("public.storefront.layouts.footer")
@endif