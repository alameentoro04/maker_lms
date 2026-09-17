<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Cohort;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Cart is DB-backed (cart_items table), not session-based — nothing is lost
 * if the browser closes or the person switches devices mid-decision.
 */
class CartController extends Controller
{
    public function index(Request $request): Response
    {
        $items = CartItem::query()->where('user_id', $request->user()->id)
            ->with(['cohort.course', 'package'])
            ->get();

        return Inertia::render('Student/Cart/Index', [
            'items' => $items->map(fn (CartItem $i) => [
                'id' => $i->id,
                'label' => $i->label(),
                'amount' => $i->amount(),
                'formatted_amount' => $this->formatAmount($i->amount(), $i->currency()),
            ]),
            'subtotal' => $this->formatAmount($items->sum(fn (CartItem $i) => $i->amount()), $items->first()?->currency() ?? 'NGN'),
        ]);
    }

    public function addCohort(Request $request, Cohort $cohort): RedirectResponse
    {
        abort_unless($cohort->isAcceptingEnrollment(), 422, 'This cohort is not open for enrollment.');

        CartItem::query()->firstOrCreate(['user_id' => $request->user()->id, 'cohort_id' => $cohort->id]);

        return back()->with('status', 'Added to cart.');
    }

    public function addPackage(Request $request, Package $package): RedirectResponse
    {
        abort_unless($package->is_published, 404);

        CartItem::query()->firstOrCreate(['user_id' => $request->user()->id, 'package_id' => $package->id]);

        return back()->with('status', 'Added to cart.');
    }

    public function remove(Request $request, CartItem $item): RedirectResponse
    {
        abort_unless($item->user_id === $request->user()->id, 403);

        $item->delete();

        return back()->with('status', 'Removed from cart.');
    }

    private function formatAmount(int $amount, string $currency): string
    {
        $symbol = match ($currency) { 'NGN' => '₦', 'USD' => '$', default => $currency.' ' };

        return $symbol.number_format($amount / 100);
    }
}
