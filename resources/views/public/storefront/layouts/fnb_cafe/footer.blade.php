@if (view()->exists("public.storefront.themes.fnb_cafe.footer"))
    @include("public.storefront.themes.fnb_cafe.footer")
@else
    @include("public.storefront.layouts.footer")
@endif