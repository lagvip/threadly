<?php

namespace App\Services\Auth;

use App\Contracts\Repositories\PasswordResetTokenRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Jobs\System\SendPasswordResetOtpJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminAuthService
{
    public function __construct(
        protected RoleRepositoryInterface $roles,
        protected UserRepositoryInterface $users,
        protected PasswordResetTokenRepositoryInterface $passwordResetTokens,
    ) {}

    public function attemptLogin(array $credentials, bool $remember): User
    {
        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)) {
            throw new RuntimeException('Sai email hoặc mật khẩu.');
        }

        /** @var User $user */
        $user = Auth::user();

        if ((int) $user->status !== User::STATUS_ACTIVE) {
            Auth::logout();
            throw new RuntimeException('Tài khoản của bạn đang bị khóa.');
        }

        return $user;
    }

    public function register(array $data): User
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => User::STATUS_ACTIVE,
        ]);

        $customerRole = $this->roles->findBySlug('customer');

        if ($customerRole) {
            $this->users->attachRole($user, (int) $customerRole->id);
        }

        Auth::login($user);

        return $user;
    }

    public function sendOtp(string $email): void
    {
        $otp = (string) random_int(100000, 999999);

        $this->passwordResetTokens->updateOrCreate($email, Hash::make($otp));

        SendPasswordResetOtpJob::dispatch($email, $otp);
    }

    public function resetPasswordWithOtp(string $email, string $otp, string $password): void
    {
        $record = $this->passwordResetTokens->findByEmail($email);

        if (! $record) {
            throw new RuntimeException('OTP không tồn tại hoặc đã hết hạn.');
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            $this->passwordResetTokens->deleteByEmail($email);
            throw new RuntimeException('OTP đã hết hạn. Vui lòng yêu cầu mã mới.');
        }

        if (! Hash::check($otp, $record->token)) {
            throw new RuntimeException('OTP không đúng.');
        }

        $this->users->updatePasswordByEmail($email, Hash::make($password));

        $this->passwordResetTokens->deleteByEmail($email);
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new RuntimeException('Mật khẩu hiện tại không đúng.');
        }

        if (Hash::check($newPassword, $user->password)) {
            throw new RuntimeException('Mật khẩu mới không được trùng mật khẩu hiện tại.');
        }

        $this->users->update($user, [
            'password' => Hash::make($newPassword),
        ]);
    }
}
