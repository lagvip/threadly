<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ((int)($user->status ?? 1) === 0) {
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

        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Bạn không có quyền truy cập chức năng này.');
        }

        return $next($request);
    }
}
