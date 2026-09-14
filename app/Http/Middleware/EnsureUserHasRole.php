<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Route-group-level gate ONLY. This is deliberately coarse — it keeps a
     * student out of /admin/* entirely — but every individual action still
     * goes through a Policy for the resource it touches. Never rely on this
     * middleware alone for authorization.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_if(! $user, 403);
        abort_if(! $user->isActive(), 403, 'Your account is not active.');
        abort_unless($user->hasRole(...$roles), 403);

        return $next($request);
    }
}
