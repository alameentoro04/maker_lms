<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Referral;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\CouponService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(Request $request): Response
    {
        // ?ref=CODE is carried through registration as a hidden field rather
        // than a session value, so it survives even if the person browses
        // around before signing up (see resources/js/Pages/Auth/Register.tsx).
        return Inertia::render('Auth/Register', [
            'referralCode' => $request->query('ref'),
        ]);
    }

    public function store(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            $studentRole = Role::query()->where('slug', Role::STUDENT)->firstOrFail();

            $user = User::query()->create([
                'role_id' => $studentRole->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'referral_code' => $this->generateReferralCode(),
                'password' => Hash::make($validated['password']),
            ]);

            UserProfile::query()->create([
                'user_id' => $user->id,
                'country' => $validated['country'] ?? null,
            ]);

            if (! empty($validated['referral_code'])) {
                $referrer = User::query()->where('referral_code', $validated['referral_code'])->first();

                // Self-referral and duplicate-referred-user attempts are silently
                // ignored rather than erroring the registration over a promo code.
                if ($referrer && $referrer->id !== $user->id) {
                    Referral::query()->firstOrCreate(['referred_id' => $user->id], ['referrer_id' => $referrer->id]);
                    (new CouponService())->issueReferralCoupon($user, 'referral_referred');
                }
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }

    private function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::query()->where('referral_code', $code)->exists());

        return $code;
    }
}
