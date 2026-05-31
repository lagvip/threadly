<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordOtpRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordOtpRequest;
use App\Services\Auth\AdminAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(protected AdminAuthService $auth)
    {
    }

    public function showLogin(Request $request)
    {
        if ($request->filled('redirect')) {
            session(['url.intended' => $request->redirect]);
        }

        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function postLogin(LoginRequest $request)
    {
        try {
            $user = $this->auth->attemptLogin($request->validated(), $request->boolean('remember'));
        } catch (RuntimeException $e) {
            if ($e->getMessage() === 'Tài khoản của bạn đang bị khóa.') {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')
                ->withInput($request->only('email'))
                ->with('error', $e->getMessage());
        }

        $request->session()->regenerate();

        if ($user->isAdmin() || $user->isManager()) {
            return redirect()->intended(route('admin.homeAdmin'));
        }

        return redirect()->intended(route('home'));
    }

    public function postRegister(RegisterRequest $request)
    {
        $this->auth->register($request->validated());
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Đăng ký thành công.');
    }

    public function logout(Request $request)
    {
        $this->logoutCurrentSession($request);

        return redirect()->route('login');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(ForgotPasswordOtpRequest $request)
    {
        $this->auth->sendOtp((string) $request->input('email'));

        return redirect()
            ->route('password.reset', ['email' => $request->input('email')])
            ->with('success', 'OTP đã được gửi về email của bạn.');
    }

    public function showResetPasswordForm(Request $request)
    {
        return view('auth.reset-password', [
            'email' => $request->email,
        ]);
    }

    public function resetPasswordWithOtp(ResetPasswordOtpRequest $request)
    {
        try {
            $this->auth->resetPasswordWithOtp(
                (string) $request->input('email'),
                (string) $request->input('otp'),
                (string) $request->input('password')
            );
        } catch (RuntimeException $e) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('login')
            ->with('success', 'Đặt lại mật khẩu thành công. Bạn hãy đăng nhập lại.');
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $this->auth->changePassword(
                $request->user(),
                (string) $request->input('current_password'),
                (string) $request->input('password')
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $this->logoutCurrentSession($request);

        return redirect()
            ->route('login')
            ->with('success', 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.');
    }

    protected function logoutCurrentSession(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
