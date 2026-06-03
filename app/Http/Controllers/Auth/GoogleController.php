<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleAuthService;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function __construct(protected GoogleAuthService $googleAuth)
    {
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = $this->googleAuth->login(Socialite::driver('google')->user());
            $route = $this->googleAuth->redirectRouteFor($user);

            return $route ? redirect()->route($route) : redirect('/');
        } catch (\Exception $e) {
            $loginRoute = Route::has('admin.auth.login') ? 'admin.auth.login' : 'login';

            return redirect()
                ->route($loginRoute)
                ->with('error', 'Đăng nhập Google thất bại: ' . $e->getMessage());
        }
    }
}
