<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function index(): Response
    {
        abort_unless(request()->user()->hasPermission('payments.manage'), 403);

        return Inertia::render('Admin/Coupons/Index', [
            'coupons' => Coupon::query()->latest()->get()->map(fn (Coupon $c) => [
                'id' => $c->id,
                'code' => $c->code,
                'type' => $c->type,
                'value' => $c->value,
                'used_count' => $c->used_count,
                'max_uses' => $c->max_uses,
                'source' => $c->source,
                'expires_at' => $c->expires_at?->toFormattedDateString(),
            ]),
        ]);
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('payments.manage'), 403);

        Coupon::query()->create([
            ...$request->validated(),
            'code' => strtoupper($request->validated()['code']),
            'source' => 'admin',
        ]);

        return back()->with('status', 'Coupon created.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        abort_unless(request()->user()->hasPermission('payments.manage'), 403);

        $coupon->delete();

        return back()->with('status', 'Coupon deleted.');
    }
}
