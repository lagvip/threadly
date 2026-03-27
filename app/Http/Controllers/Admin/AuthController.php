<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
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

    public function postLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Sai email hoặc mật khẩu.');
        }

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ((int) $user->status !== 1) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Tài khoản của bạn đang bị khóa.');
        }

        if ($user->isAdmin() || $user->isManager()) {
            return redirect()->intended(route('admin.homeAdmin'));
        }

        return redirect()->intended(route('home'));
    }

    public function postRegister(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'status'   => 1,
        ]);

        $customerRole = Role::where('slug', 'customer')->first();

        if ($customerRole) {
            $user->roles()->attach($customerRole->id);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Đăng ký thành công.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'Email này chưa tồn tại trong hệ thống.',
        ]);

        $email = $request->email;
        $otp = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        Mail::raw("Mã OTP đặt lại mật khẩu của bạn là: {$otp}. Mã có hiệu lực trong 10 phút.", function ($message) use ($email) {
            $message->to($email)
                    ->subject('Mã OTP đặt lại mật khẩu');
        });

        return redirect()->route('password.reset', ['email' => $email])
            ->with('success', 'OTP đã được gửi về email của bạn.');
    }

    public function showResetPasswordForm(Request $request)
    {
        return view('auth.reset-password', [
            'email' => $request->email
        ]);
    }

    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return back()->withInput($request->only('email'))
                ->with('error', 'OTP không tồn tại hoặc đã hết hạn.');
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withInput($request->only('email'))
                ->with('error', 'OTP đã hết hạn. Vui lòng yêu cầu mã mới.');
        }

        if (!Hash::check($request->otp, $record->token)) {
            return back()->withInput($request->only('email'))
                ->with('error', 'OTP không đúng.');
        }

        $user = User::where('email', $request->email)->first();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Đặt lại mật khẩu thành công. Bạn hãy đăng nhập lại.');
    }
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Mật khẩu hiện tại không đúng.');
        }
        if (Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Mật khẩu mới không được trùng mật khẩu hiện tại.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.');
    }
}
