<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    /**
     * Routes/path yang dibolehkan saat user masih harus melengkapi profile.
     */
    private array $allowedRouteNames = [
        'account.setup',
        'account.setup.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->must_complete_profile || $user->must_change_password)) {
            $routeName = $request->route()?->getName();

            if (! in_array($routeName, $this->allowedRouteNames, true)) {
                return redirect()->route('account.setup');
            }
        }

        return $next($request);
    }
}
