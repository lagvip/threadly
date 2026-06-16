<?php

namespace App\Services\Auth;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class GoogleAuthService
{
    public function __construct(
        protected RoleRepositoryInterface $roles,
        protected UserRepositoryInterface $users,
    ) {}

    public function login($googleUser): User
    {
        $user = $this->users->findByGoogleIdOrEmail((string) $googleUser->id, (string) $googleUser->email);

        if (! $user) {
            $user = $this->users->create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(24)),
            ]);
        } else {
            $this->users->update($user, [
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        }

        $this->attachCustomerRole($user);
        Auth::login($user, true);

        return $user;
    }

    public function redirectRouteFor(User $user): ?string
    {
        if (method_exists($user, 'hasRole') && $user->hasRole('admin') && Route::has('admin.homeAdmin')) {
            return 'admin.homeAdmin';
        }

        foreach (['client.checkout.index', 'client.cart.index', 'client.home', 'home'] as $route) {
            if (Route::has($route)) {
                return $route;
            }
        }

        return null;
    }

    protected function attachCustomerRole(User $user): void
    {
        $customerRole = $this->roles->findBySlug('customer');

        if ($customerRole && ! $user->hasRole('customer')) {
            $this->users->syncRolesWithoutDetaching($user, [$customerRole->id]);
        }
    }
}
