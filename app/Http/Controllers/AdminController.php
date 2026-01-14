<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AdminController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function register()
    {
        return view('admin.register');
    }

    public function index()
    {
        return view('admin.dashboard');
    }
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Sai email hoặc mật khẩu
        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không đúng.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        // Tài khoản bị khóa
        if ($user->status !== 'active') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa.',
            ])->onlyInput('email');
        }

        // Không phải admin / staff
        if (!in_array($user->role, ['admin', 'staff'])) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'Bạn không có quyền truy cập trang quản trị.',
            ])->onlyInput('email');
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
