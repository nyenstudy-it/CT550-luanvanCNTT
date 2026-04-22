<?php

namespace App\Http\Controllers;

use App\Models\Discount;
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
            'password' => 'required|min:8|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/|confirmed',
            'phone'    => 'required|regex:/^0\d{9}$/|unique:customers,phone'
        ], [
            'password.regex' => 'Mật khẩu phải chứa tối thiểu 8 ký tự, bao gồm: chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*?&)',
            'phone.regex' => 'Số điện thoại phải đúng 10 số và bắt đầu với 0',
            'phone.unique' => 'Số điện thoại này đã được đăng ký'
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

            $welcomeDiscountCount = Discount::query()
                ->whereDoesntHave('products')
                ->get()
                ->filter(fn(Discount $discount) => $discount->isActive() && $discount->isEligibleForCompletedOrdersCount(0))
                ->count();

            $successMessage = 'Đăng ký thành công';

            if ($welcomeDiscountCount > 0) {
                $successMessage .= '. Bạn có ' . $welcomeDiscountCount . ' voucher dành cho khách mới trong mục Voucher của tôi.';
            }

            return redirect('/')->with('success', $successMessage);
        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Có lỗi xảy ra, vui lòng thử lại.');
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

        $user = User::where('email', $request->email)
            ->where('role', 'customer')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withInput()
                ->with('error', 'Email hoặc mật khẩu không đúng');
        }

        if ($user->status !== 'active') {
            $reason = $user->locked_reason ?: 'Tài khoản đang bị khóa, vui lòng liên hệ quản trị viên.';

            return back()
                ->withInput()
                ->with('error', 'Tài khoản của bạn đã bị khóa. Lý do: ' . $reason);
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect('/')->with('success', 'Đăng nhập thành công');
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
        /** @var User $user */
        $user = Auth::user();
        $customer = $user->customer;

        $rules = [
            'name'     => 'required|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'phone'    => 'required|regex:/^0\d{9}$/|unique:customers,phone,' . $customer->id,
            'address'  => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'ward'     => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender'   => 'nullable|in:male,female,other',
            'current_password' => 'required_with:password',
            'password' => 'nullable|min:8|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/|confirmed',
            'avatar'   => 'nullable|image|max:2048',
            'is_default_address' => 'nullable|boolean'
        ];

        $messages = [
            'phone.regex' => 'Số điện thoại phải đúng 10 số và bắt đầu với 0',
            'phone.unique' => 'Số điện thoại này đã được đăng ký',
            'password.regex' => 'Mật khẩu phải chứa tối thiểu 8 ký tự, bao gồm: chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*?&)'
        ];

        $request->validate($rules, $messages);

        // Kiểm tra mật khẩu hiện tại nếu có thay đổi mật khẩu
        if ($request->filled('password') && !Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Mật khẩu hiện tại không đúng'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            $user->update([
                'name'  => $request->name,
                'email' => $request->email
            ]);

            if ($request->filled('password')) {
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
                    'is_default_address' => (int) $request->boolean('is_default_address')
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

            return back()->with('error', $e->getMessage() ?: 'Có lỗi xảy ra, vui lòng thử lại.');
        }
    }
}
