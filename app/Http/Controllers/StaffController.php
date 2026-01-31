<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Staff;
use App\Models\User;

class StaffController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email',
            'password' => 'required|min:8',

            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',

            // POSITION = nghiệp vụ
            'position' => 'required|in:cashier,warehouse,delivery',

            'start_date' => 'nullable|date',
            'probation_start' => 'nullable|date',
            'probation_end' => 'nullable|date',

            'employment_status' => 'required|in:probation,official,resigned',

            'probation_hourly_wage' => 'nullable|numeric',
            'official_hourly_wage' => 'nullable|numeric',

            'avatar' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')
                    ->store('avatars', 'public');
            }

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'staff',
                'status'   => 'active',
                'avatar'   => $avatarPath,
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
                'probation_hourly_wage' => $request->probation_hourly_wage ?? 20000,
                'official_hourly_wage'  => $request->official_hourly_wage ?? 30000,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.staff.list')
                ->with('success', 'Nhân viên đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
