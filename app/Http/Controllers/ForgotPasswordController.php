<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{

    // Form nhập email
    public function showLinkRequestForm()
    {
        return view('pages.customer.forgot-password');
    }

    // Gửi link reset
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {

            return back()->with('success', 'Link đặt lại mật khẩu đã gửi email.');
        }

        return back()->withErrors([
            'email' => 'Email không tồn tại trong hệ thống'
        ]);
    }

    // Form nhập mật khẩu mới
    public function showResetForm($token)
    {
        return view('pages.customer.reset-password', [
            'token' => $token
        ]);
    }

    // Cập nhật mật khẩu
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6'
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Đổi mật khẩu thành công');
        }

        return back()->withErrors(['email' => 'Token không hợp lệ']);
    }
}
