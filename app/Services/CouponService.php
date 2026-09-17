<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Support\Str;

class CouponService
{
    /** @return array{coupon: Coupon, discount: int}|null */
    public function resolve(string $code, User $user, int $subtotal): ?array
    {
        $coupon = Coupon::query()->where('code', strtoupper(trim($code)))->first();

        if (! $coupon || ! $coupon->isValidFor($user)) {
            return null;
        }

        return ['coupon' => $coupon, 'discount' => $coupon->discountFor($subtotal)];
    }

    public function markUsed(Coupon $coupon): void
    {
        $coupon->increment('used_count');
    }

    /** Issues a one-time coupon to a user — used for both sides of the referral reward. */
    public function issueReferralCoupon(User $user, string $source): Coupon
    {
        $percent = PlatformSetting::get('referrals', 'discount_percent', 10);

        return Coupon::query()->create([
            'code' => 'REF-'.strtoupper(Str::random(8)),
            'type' => 'percent',
            'value' => $percent,
            'issued_to' => $user->id,
            'source' => $source,
            'max_uses' => 1,
        ]);
    }
}
