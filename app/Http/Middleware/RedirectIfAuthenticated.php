<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
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
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if (Auth::check() && $request->isMethod('get')) {
            $user = Auth::user();

            if ($user->isAdmin() || $user->isManager()) {
                return redirect()->route('admin.homeAdmin');
            }

            return redirect()->route('home');
        }

        return $next($request);
    }
}
