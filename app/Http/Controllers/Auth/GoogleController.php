<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'status' => 1,
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)),
                ]);
            } else {
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            }

            // Gán role customer theo slug
            $customerRole = Role::where('slug', 'customer')->first();

            if ($customerRole && !$user->hasRole('customer')) {
                $user->roles()->syncWithoutDetaching([$customerRole->id]);
            }

            Auth::login($user, true);

            return redirect()->route('admin.homeAdmin');
        } catch (\Exception $e) {
            return redirect()->route('admin.auth.login')
                ->with('error', 'Đăng nhập Google thất bại: ' . $e->getMessage());
        }
    }
}