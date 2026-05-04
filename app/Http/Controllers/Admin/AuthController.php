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
        // Nếu URL có redirect thì lưu lại để sau khi đăng nhập chuyển về trang đó.
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

        // Lấy trạng thái ghi nhớ đăng nhập từ checkbox remember.
        $remember = $request->boolean('remember');

        // Thử đăng nhập bằng email và password.
        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Sai email hoặc mật khẩu.');
        }

        // Regenerate session để tránh session fixation sau khi đăng nhập.
        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Nếu tài khoản bị khóa thì logout ngay và hủy session.
        if ((int) $user->status !== 1) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Tài khoản của bạn đang bị khóa.');
        }

        // Admin hoặc manager đăng nhập xong thì vào trang quản trị.
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

        // Tạo user mới, mật khẩu được hash trước khi lưu.
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'status'   => 1,
        ]);

        // Gán role customer cho user mới đăng ký.
        $customerRole = Role::where('slug', 'customer')->first();

        if ($customerRole) {
            $user->roles()->attach($customerRole->id);
        }

        // Đăng nhập luôn sau khi đăng ký thành công.
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))
            ->with('success', 'Đăng ký thành công.');
    }

    public function logout(Request $request)
    {
        // Đăng xuất user và hủy session hiện tại.
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

        // Tạo OTP 6 số ngẫu nhiên.
        $otp = (string) random_int(100000, 999999);

        // Lưu OTP đã hash vào bảng password_reset_tokens.
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Gửi OTP về email của người dùng.
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

        // Lấy bản ghi OTP theo email.
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        // Không có OTP thì báo lỗi.
        if (!$record) {
            return back()->withInput($request->only('email'))
                ->with('error', 'OTP không tồn tại hoặc đã hết hạn.');
        }

        // OTP chỉ có hiệu lực 10 phút.
        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withInput($request->only('email'))
                ->with('error', 'OTP đã hết hạn. Vui lòng yêu cầu mã mới.');
        }

        // So sánh OTP người dùng nhập với OTP đã hash trong database.
        if (!Hash::check($request->otp, $record->token)) {
            return back()->withInput($request->only('email'))
                ->with('error', 'OTP không đúng.');
        }

        $user = User::where('email', $request->email)->first();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Đổi mật khẩu xong thì xóa OTP để không dùng lại được.
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

        // Đổi mật khẩu xong thì logout để user đăng nhập lại bằng mật khẩu mới.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.');
    }
}
