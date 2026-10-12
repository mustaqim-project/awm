@if (view()->exists("public.storefront.themes.fnb_bakery.footer"))
    @include("public.storefront.themes.fnb_bakery.footer")
@else
    @include("public.storefront.layouts.footer")
@endif