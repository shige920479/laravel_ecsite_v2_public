<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return match($guard) {
                    'web_superuser' => redirect()->route('superuser.index'),
                    'web_admin' => redirect()->route('admin.home'),
                    'web_owner' => redirect()->route('owner.shop.index'),
                    'web' => redirect()->route('home.index'),
                    default => redirect()->route('home.index')
                };
            }
        }

        return $next($request);
    }
}
