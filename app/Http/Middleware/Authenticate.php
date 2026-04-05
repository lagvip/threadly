<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, ...$guards): Response
    {
        $this->authenticate($request, $guards);

        $user = Auth::user();

        if ($user && (int)($user->status ?? 1) === 0) {
            $reason = trim((string)($user->ban_reason ?? ''));

            Auth::logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => $reason !== ''
                        ? 'Tài khoản của bạn đã bị khóa. Lý do: ' . $reason
                        : 'Tài khoản của bạn đã bị khóa.',
                ]);
        }

        return $next($request);
    }
}
