<?php

namespace App\Actions;

use App\Models\Cohort;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Creates a pending Order (+ its single OrderItem) for a cohort checkout.
 * Does NOT touch enrollment — that only happens once a Payment on this order
 * is verified, via ActivateEnrollmentFromPaymentAction.
 */
class CreateOrderAction
{
    public function execute(User $user, Cohort $cohort): Order
    {
        $course = $cohort->course;

        $order = Order::query()->create([
            'reference' => $this->generateReference(),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'cohort_id' => $cohort->id,
            'amount' => $course->price,
            'currency' => $course->currency,
            'status' => 'pending',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'description' => "{$course->title} — {$cohort->name}",
            'amount' => $course->price,
        ]);

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
