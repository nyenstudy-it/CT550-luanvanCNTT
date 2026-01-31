<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    // ADMIN xem toàn bộ phân ca
    public function index()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $attendances = Attendance::with('staff.user')
            ->orderBy('work_date', 'desc')
            ->get();

        return view('admin.staff.attendances', compact('attendances'));
    }

    // STAFF xem ca của chính mình
    public function staffIndex()
    {
        $user = Auth::user();

        abort_unless($user->staff, 403);

        $attendances = Attendance::where('staff_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->get();

        return view('admin.staff.staff_attendances', compact('attendances'));
    }

    // ADMIN tạo phân ca
    public function create()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $staffs = Staff::with('user')->get();

        $calendarEvents = Attendance::with('staff.user')->get()
            ->map(function ($a) {
                return [
                    'title' => $a->staff->user->name . ' - ' .
                        ($a->shift === 'morning' ? 'Ca sáng' : 'Ca chiều'),
                    'start' => $a->work_date . 'T' . $a->expected_check_in,
                    'end'   => $a->work_date . 'T' . $a->expected_check_out,
                ];
            });

        return view('admin.staff.attendance_create', compact(
            'staffs',
            'calendarEvents'
        ));
    }

    // ADMIN lưu phân ca
    public function store(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'staff_id' => 'required|exists:staffs,user_id',
            'work_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon',
            'expected_check_in' => 'required',
            'expected_check_out' => 'required',

            'staff_id' => [
                'required',
                Rule::unique('attendances')->where(
                    fn($q) =>
                    $q->where('staff_id', $request->staff_id)
                        ->where('work_date', $request->work_date)
                        ->where('shift', $request->shift)
                ),
            ],
        ]);

        Attendance::create([
            'staff_id' => $request->staff_id,
            'work_date' => $request->work_date,
            'shift' => $request->shift,
            'expected_check_in' => $request->expected_check_in,
            'expected_check_out' => $request->expected_check_out,
        ]);

        return redirect()
            ->route('admin.staff.attendances')
            ->with('success', 'Phân ca làm việc thành công.');
    }

    // STAFF check-in
    public function checkIn(Attendance $attendance)
    {
        $user = Auth::user();
        $staff = $user->staff;
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        abort_unless($staff, 403);

        if ($user->status !== 'active') {
            return back()->with('error', 'Tài khoản đã bị khóa');
        }

        if ($attendance->staff_id !== $user->id) {
            abort(403);
        }

        if ($attendance->work_date !== $now->toDateString()) {
            return back()->with('error', 'Chưa tới ngày làm việc');
        }

        if ($attendance->check_in) {
            return back()->with('error', 'Bạn đã check-in');
        }

        $start = Carbon::parse(
            $attendance->work_date . ' ' . $attendance->expected_check_in,
            'Asia/Ho_Chi_Minh'
        );

        $lateMinutes = $start->diffInMinutes($now, false);

        if ($lateMinutes < 0) {
            return back()->with('error', 'Chưa tới giờ làm');
        }

        if ($lateMinutes > 120) {
            return back()->with('error', 'Trễ quá 2 tiếng, không thể chấm công');
        }

        $attendance->update([
            'check_in' => $now,
            'is_late' => $lateMinutes > 15,
        ]);

        return back()->with('success', 'Check-in thành công');
    }

    // STAFF check-out
    public function checkOut(Attendance $attendance)
    {
        $user = Auth::user();
        $staff = $user->staff;

        abort_unless($staff, 403);

        if ($user->status !== 'active') {
            return back()->with('error', 'Tài khoản đã bị khóa');
        }

        if ($attendance->staff_id !== $user->id) {
            abort(403);
        }

        if (!$attendance->check_in) {
            return back()->with('error', 'Bạn chưa check-in');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'Bạn đã check-out');
        }

        $attendance->update([
            'check_out' => Carbon::now('Asia/Ho_Chi_Minh'),
        ]);

        return back()->with('success', 'Check-out thành công');
    }
}
