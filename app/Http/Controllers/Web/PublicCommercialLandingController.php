<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

final class PublicCommercialLandingController extends Controller
{
    /**
     * Landing page for Aplikasi Kasir Gratis Selamanya.
     */
    public function kasirGratis(): View
    {
        return view('public.landing_commercial.kasir_gratis');
    }

    /**
     * Landing page for Aplikasi Kasir Android.
     */
    public function kasirAndroid(): View
    {
        return view('public.landing_commercial.kasir_android');
    }

    /**
     * Landing page for Aplikasi Kasir Offline.
     */
    public function kasirOffline(): View
    {
        return view('public.landing_commercial.kasir_offline');
    }
}

