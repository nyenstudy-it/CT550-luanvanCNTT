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
        $attendances = Attendance::with('staff.user')
            ->orderBy('work_date', 'desc')
            ->get();

        return view('admin.staff.attendances', compact('attendances'));
    }

    // STAFF xem ca của chính mình
    public function staffIndex()
    {
        $staff = Auth::user()->staff;

        $attendances = Attendance::where('staff_id', $staff->user_id)
            ->orderBy('work_date', 'desc')
            ->get();

        return view('admin.staff.staff_attendances', compact('attendances'));
    }

    // ADMIN tạo phân ca
    public function create()
    {
        $staffs = Staff::with('user')->get();

        $attendances = Attendance::with('staff.user')->get();

        $calendarEvents = $attendances->map(function ($a) {
            return [
                'title' => $a->staff->user->name . ' - ' . ($a->shift === 'morning' ? 'Ca sáng' : 'Ca chiều'),
                'start' => $a->work_date . 'T' . $a->expected_check_in,
                'end'   => $a->work_date . 'T' . $a->expected_check_out,
                'color' => $a->shift === 'morning' ? '#0d6efd' : '#198754',
            ];
        })->values()->toArray();

        return view('admin.staff.attendance_create', compact(
            'staffs',
            'attendances',
            'calendarEvents'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staffs,user_id',
            'work_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon',
            'expected_check_in' => 'required',
            'expected_check_out' => 'required',

            Rule::unique('attendances')->where(function ($query) use ($request) {
                return $query->where('staff_id', $request->staff_id)
                    ->where('work_date', $request->work_date)
                    ->where('shift', $request->shift);
            }),
        ]);

        Attendance::create([
            'staff_id' => $request->staff_id,
            'work_date' => $request->work_date,
            'shift' => $request->shift,
            'expected_check_in' => $request->expected_check_in,
            'expected_check_out' => $request->expected_check_out,
            'status' => 'scheduled',
        ]);

        return redirect()
            ->route('admin.staff.attendances')
            ->with('success', 'Phân ca làm việc thành công.');
    }

    // STAFF check-in
    public function checkIn(Attendance $attendance)
    {
        
        $staff = Auth::user()->staff;
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        // Nhân viên bị khóa
        if (Auth::user()->status !== 'active') {
            return back()->with('error', 'Tài khoản của bạn đã bị khóa');
        }

        // Đúng ca của mình
        if ($attendance->staff_id !== $staff->user_id) {
            abort(403);
        }

        // Đúng ngày
        if ($attendance->work_date !== $now->toDateString()) {
            return back()->with('error', 'Chưa tới ngày làm việc');
        }

        // Đã check-in rồi
        if ($attendance->check_in) {
            return back()->with('error', 'Bạn đã check-in rồi');
        }

        $startTime = Carbon::parse(
            $attendance->work_date . ' ' . $attendance->expected_check_in,
            'Asia/Ho_Chi_Minh'
        );

        $lateMinutes = $startTime->diffInMinutes(now(), false);

        // Chưa tới giờ
        if ($lateMinutes < 0) {
            return back()->with('error', 'Chưa tới giờ làm việc');
        }

        // Trễ quá 120 phút → cấm
        if ($lateMinutes > 120) {
            return back()->with('error', 'Bạn đã đi trễ quá 2 tiếng, không thể chấm công');
        }

        // Trễ 15–120 phút → đánh dấu trễ
        $isLate = $lateMinutes > 15;

        $attendance->update([
            'check_in' => now(),
            'is_late' => $isLate,
            'status' => 'working',
        ]);

        return back()->with(
            'success',
            $isLate
                ? 'Check-in thành công (bạn đã đi trễ)'
                : 'Check-in thành công'
        );
    }

    // STAFF check-out
    public function checkOut(Attendance $attendance)
    {
        $staff = Auth::user()->staff;

        // Nhân viên bị khóa
        if (Auth::user()->status !== 'active') {
            return back()->with('error', 'Tài khoản của bạn đã bị khóa');
        }

        if ($attendance->staff_id !== $staff->user_id) {
            abort(403);
        }

        if (!$attendance->check_in) {
            return back()->with('error', 'Bạn chưa check-in');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'Bạn đã check-out rồi');
        }

        $attendance->update([
            'check_out' => Carbon::now('Asia/Ho_Chi_Minh'),
            'status' => 'completed',
        ]);

        return back()->with('success', 'Check-out thành công');
    }
}
