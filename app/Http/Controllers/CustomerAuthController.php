<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{

    public function showRegister()
    {
        return view('pages.customer.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone'    => 'required'
        ]);

        DB::beginTransaction();

        try {

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'customer',
                'status'   => 'active'
            ]);

            Customer::create([
                'user_id'            => $user->id,
                'phone'              => $request->phone,
                'address'            => $request->address,
                'is_default_address' => 1
            ]);

            DB::commit();

            Auth::login($user);

            return redirect('/')->with('success', 'Đăng ký thành công');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra, vui lòng thử lại.');
        }
    }
    public function showLogin()
    {
        return view('pages.customer.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt([
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => 'customer',
            'status'   => 'active'
        ])) {

            $request->session()->regenerate();

            return redirect('/')->with('success', 'Đăng nhập thành công');
        }

        return back()
            ->withInput()
            ->with('error', 'Sai thông tin hoặc tài khoản bị khóa');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function profile()
    {
        $customer = Auth::user()->customer;

        return view('pages.customer.profile', compact('customer'));
    }

    public function profileUpdate(Request $request)
{
    $user = Auth::user();
    $customer = $user->customer;

    $request->validate([
        'name'     => 'required|max:255',
        'email'    => 'required|email|unique:users,email,' . $user->id,
        'phone'    => 'required',
        'address'  => 'nullable|string|max:255',
        'province' => 'nullable|string|max:255',
        'district' => 'nullable|string|max:255',
        'ward'     => 'nullable|string|max:255',
        'date_of_birth' => 'nullable|date',
        'gender'   => 'nullable|in:male,female,other',
        'current_password' => 'required_with:password',
        'password' => 'nullable|min:6|confirmed',
        'avatar'   => 'nullable|image|max:2048'
    ]);

    DB::beginTransaction();

    try {
        $user->update([
            'name'  => $request->name,
            'email' => $request->email
        ]);

        if ($request->filled('password')) {

            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Mật khẩu hiện tại không đúng'
                ])->withInput();
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }

        if ($request->hasFile('avatar')) {

            // Xoá avatar cũ nếu có
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');

            $user->update([
                'avatar' => $path
            ]);
        }

        if ($customer) {
            $customer->update([
                'phone'         => $request->phone,
                'address'       => $request->address,
                'province'      => $request->province,
                'district'      => $request->district,
                'ward'          => $request->ward,
                'date_of_birth' => $request->date_of_birth,
                'gender'        => $request->gender,
                'is_default_address' => $request->is_default_address
            ]);
        }

        DB::commit();

            if ($request->redirect == 'checkout') {
                return redirect()->route('checkout')
                    ->with('success', 'Cập nhật địa chỉ thành công');
            }

            return back()->with('success', 'Cập nhật thông tin thành công');
        } catch (\Exception $e) {

        DB::rollBack();

        return back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại.');
    }
}

}
