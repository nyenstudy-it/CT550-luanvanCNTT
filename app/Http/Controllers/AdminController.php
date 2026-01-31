<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Staff;

class AdminController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function index()
    {
        return view('admin.dashboard');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng']);
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            return back()->withErrors(['email' => 'Tài khoản đã bị khóa']);
        }

        // admin + nhân viên mới được vào admin area
        if (!in_array($user->role, ['admin', 'staff'])) {
            Auth::logout();
            return back()->withErrors(['email' => 'Không có quyền truy cập']);
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

    // ADMIN ONLY
    public function staffManagement()
    {
        $staffs = Staff::with('user')->get();
        return view('admin.staff.list', compact('staffs'));
    }

    // ADMIN + STAFF
    public function profile()
    {
        return view('admin.profile', [
            'user' => Auth::user()
        ]);
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        if ($user->staff) {
            $user->staff->update([
                'phone'         => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'address'       => $request->address,
            ]);
        }

        return back()->with('success', 'Cập nhật thông tin thành công');
    }

    // ADMIN ONLY
    public function staffCreate()
    {
        return view('admin.staff.create');
    }

    public function staffStore(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'name'   => 'required|string|max:255',
                'email'  => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'position' => 'required|in:cashier,warehouse,delivery',
                'employment_status' => 'required|in:probation,official,resigned',
            ]);

            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
            }

            // ROLE = POSITION
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'avatar'   => $avatarPath,
                'role' => 'staff',
                'status'   => 'active',
            ]);

            Staff::create([
                'user_id'           => $user->id,
                'phone'             => $request->phone,
                'date_of_birth'     => $request->date_of_birth,
                'address'           => $request->address,
                'position'          => $request->position,
                'start_date'        => $request->start_date,
                'probation_start'   => $request->probation_start,
                'probation_end'     => $request->probation_end,
                'employment_status' => $request->employment_status,
                'probation_hourly_wage' => 20000,
                'official_hourly_wage'  => 30000,
                'created_at'        => now(),
            ]);

            DB::commit();

            return redirect()->route('admin.staff.list')
                ->with('success', 'Tạo nhân viên thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function staffEdit($id)
    {
        $staff = Staff::with('user')
            ->where('user_id', $id)
            ->firstOrFail();

        return view('admin.staff.edit', compact('staff'));
    }

    public function staffUpdate(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'name'   => 'required|string|max:255',
                'position' => 'required|in:cashier,warehouse,delivery',
                'employment_status' => 'required|in:probation,official,resigned',
            ]);

            $user  = User::findOrFail($id);
            $staff = Staff::where('user_id', $id)->firstOrFail();

            if ($request->hasFile('avatar')) {
                $user->avatar = $request->file('avatar')->store('avatars', 'public');
            }

            $user->name = $request->name;
            $user->role = 'staff';

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            $staff->update([
                'phone'             => $request->phone,
                'date_of_birth'     => $request->date_of_birth,
                'address'           => $request->address,
                'position'          => $request->position,
                'start_date'        => $request->start_date,
                'probation_start'   => $request->probation_start,
                'probation_end'     => $request->probation_end,
                'employment_status' => $request->employment_status,
            ]);

            DB::commit();

            return redirect()->route('admin.staff.list')
                ->with('success', 'Cập nhật nhân viên thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function staffLock($id)
    {
        User::where('id', $id)->update(['status' => 'locked']);

        return redirect()->route('admin.staff.list')
            ->with('success', 'Khóa nhân viên thành công');
    }

    public function staffUnlock($id)
    {
        User::where('id', $id)->update(['status' => 'active']);

        return redirect()->route('admin.staff.list')
            ->with('success', 'Mở khóa nhân viên thành công');
    }

    public function staffDestroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);

            // Không cho admin tự xóa mình
            if ($user->id === Auth::id()) {
                return back()->with('error', 'Không thể xóa chính mình');
            }

            $user->delete(); // staff sẽ tự xóa theo cascade

            DB::commit();

            return redirect()
                ->route('admin.staff.list')
                ->with('success', 'Xóa nhân viên thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra');
        }
    }
}
