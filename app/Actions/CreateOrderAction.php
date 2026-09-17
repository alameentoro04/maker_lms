<?php

namespace App\Actions;

use App\Models\CartItem;
use App\Models\Cohort;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Creates a pending Order (+ its OrderItems) — from either a single cohort
 * (the "buy now" path on a cohort page) or a whole cart (several cohorts
 * and/or packages in one checkout). Never touches enrollment — that only
 * happens once a Payment on this order is verified, via
 * ActivateEnrollmentFromPaymentAction.
 */
class CreateOrderAction
{
    public function execute(User $user, Cohort $cohort): Order
    {
        return $this->executeForItems($user, collect([
            (object) ['cohort' => $cohort, 'package' => null],
        ]));
    }

    /** @param Collection<int, CartItem> $cartItems */
    public function executeFromCart(User $user, Collection $cartItems, ?Coupon $coupon = null): Order
    {
        return $this->executeForItems($user, $cartItems, $coupon);
    }

    /** @param Collection<int, CartItem|object> $items */
    private function executeForItems(User $user, Collection $items, ?Coupon $coupon = null): Order
    {
        $lines = $items->map(function ($item) {
            $cohort = $item->cohort;
            $package = $item->package ?? null;

            return [
                'cohort_id' => $cohort?->id,
                'package_id' => $package?->id,
                'description' => $cohort ? "{$cohort->course->title} — {$cohort->name}" : $package->title,
                'amount' => $cohort ? $cohort->course->price : $package->price,
                'currency' => $cohort ? $cohort->course->currency : $package->currency,
            ];
        });

        $subtotal = $lines->sum('amount');
        $discount = $coupon ? $coupon->discountFor($subtotal) : 0;
        $currency = $lines->first()['currency'] ?? 'NGN';

        $firstCohort = $items->first()?->cohort;

        $order = Order::query()->create([
            'reference' => $this->generateReference(),
            'user_id' => $user->id,
            'course_id' => $firstCohort?->course_id,
            'cohort_id' => $firstCohort?->id,
            'coupon_id' => $coupon?->id,
            'discount_amount' => $discount,
            'amount' => max(0, $subtotal - $discount),
            'currency' => $currency,
            'status' => 'pending',
        ]);

        foreach ($lines as $line) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'cohort_id' => $line['cohort_id'],
                'package_id' => $line['package_id'],
                'description' => $line['description'],
                'amount' => $line['amount'],
            ]);
        }

        return $order;
    }

    private function generateReference(): string
    {
        do {
            $reference = 'ORD-'.now()->format('Y').'-'.strtoupper(Str::random(8));
        } while (Order::query()->where('reference', $reference)->exists());

        return $reference;
    }
}
