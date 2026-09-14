<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Single entry point after login/registration — routes each role to its
     * own dashboard controller rather than branching inside every page.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        return to_route($request->user()->dashboardRoute());
    }
}
