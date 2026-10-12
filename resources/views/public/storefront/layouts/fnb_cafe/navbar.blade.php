@if (view()->exists("public.storefront.themes.fnb_cafe.navbar"))
    @include("public.storefront.themes.fnb_cafe.navbar")
@else
    @include("public.storefront.layouts.navbar")
@endif