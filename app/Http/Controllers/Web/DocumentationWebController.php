<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\Context;
use Illuminate\View\View;

final class DocumentationWebController extends Controller
{
    /**
     * Tampilkan halaman dokumentasi dan panduan penggunaan sistem Cooca.
     */
    public function index(): View
    {
        $user = Context::user();
        $business = Context::business();

        return view('app.docs.index', compact('user', 'business'));
    }
}
