<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    // Real metrics (students, revenue, pending payments, ...) land in Phase 3+
    // once courses/cohorts/payments exist. Placeholder keeps the route real
    // and the layout/navigation testable now.
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Admin/Dashboard');
    }
}
