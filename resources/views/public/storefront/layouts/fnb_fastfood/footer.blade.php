@if (view()->exists("public.storefront.themes.fnb_fastfood.footer"))
    @include("public.storefront.themes.fnb_fastfood.footer")
@else
    @include("public.storefront.layouts.footer")
@endif