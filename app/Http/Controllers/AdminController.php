<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            'email' => 'required|email',
            'password' => 'required|min:8',

        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng.']);
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            return back()->withErrors(['email' => 'Tài khoản đã bị khóa.']);
        }

        if (!in_array($user->role, ['admin', 'staff'])) {
            Auth::logout();
            return back()->withErrors(['email' => 'Không có quyền truy cập.']);
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

    //STAFF MANAGEMENT (ADMIN ONLY) 
    public function staffManagement()
    {
        $staffs = Staff::with('user')->get();
        return view('admin.staff.list', compact('staffs'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        if ($user->role === 'staff' && $user->staff) {
            $user->staff->update([
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'address' => $request->address,
            ]);
        }

        return back()->with('success', 'Cập nhật thông tin cá nhân thành công');
    }

    public function staffCreate()
    {
        return view('admin.staff.create');
    }

    public function staffStore(Request $request)
    {
        DB::beginTransaction();
    try {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'position' => 'required',
            'employment_status' => 'required|in:probation,official,resigned',
        ]);

            $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'avatar'   => $avatarPath,
            'role'     => 'staff', 
            'status'   => 'active',
        ]);

        Staff::create([
            'user_id'               => $user->id,
            'phone'                 => $request->phone,
            'date_of_birth'         => $request->date_of_birth,
            'address'               => $request->address,
            'position'              => $request->position,
            'start_date'            => $request->start_date,
            'probation_start'       => $request->probation_start,
            'probation_end'         => $request->probation_end,
            'employment_status'     => $request->employment_status,
            'probation_hourly_wage' => 20000,
            'official_hourly_wage'  => 30000,
            'created_at'            => now(),
        ]);

        DB::commit();

        return redirect()
            ->route('admin.staff.list')
            ->with('success', 'Tạo nhân viên thành công');
    } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }

    public function staffEdit($id)
    {
        $staff = Staff::with('user')->where('user_id', $id)->firstOrFail();
        return view('admin.staff.edit', compact('staff'));
    }

    public function staffUpdate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name'     => 'required|string|max:255',
                'position' => 'required',
                'employment_status' => 'required|in:probation,official,resigned',
            ]);

            $staff = Staff::where('user_id', $id)->firstOrFail();
            $user = User::findOrFail($id);

            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $avatarPath;
            }

            $user->name = $request->name;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();

            $staff->phone = $request->phone;
            $staff->date_of_birth = $request->date_of_birth;
            $staff->address = $request->address;
            $staff->position = $request->position;
            $staff->start_date = $request->start_date;
            $staff->probation_start = $request->probation_start;
            $staff->probation_end = $request->probation_end;
            $staff->employment_status = $request->employment_status;
            $staff->save();

            DB::commit();

            return redirect()
                ->route('admin.staff.list')
                ->with('success', 'Cập nhật nhân viên thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }   
    }

    public function staffLock($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'locked';
        $user->save();

        return redirect()
            ->route('admin.staff.list')
            ->with('success', 'Khóa nhân viên thành công');
    }
    public function staffUnlock($id)
    {
        $user = User::findOrFail($id);
        $user->status = 'active';
        $user->save();

        return redirect()
            ->route('admin.staff.list')
            ->with('success', 'Mở khóa nhân viên thành công');
    }
    
}