<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $attendances = Attendance::with('user')
            ->orderBy('work_date', 'asc')
            ->orderBy('shift', 'asc')
            ->get();

        $calendarEvents = $attendances->map(function ($a) {

            $shiftLabel = $a->shift === 'morning'
                ? 'Ca sáng'
                : 'Ca chiều';

            return [
                'title' => $a->staff->user->name . ' - ' . $shiftLabel,
                'start' => $a->work_date . 'T' . $a->expected_check_in,
                'end'   => $a->work_date . 'T' . $a->expected_check_out,

                'extendedProps' => [
                    'name' => $a->staff->user->name,
                    'date' => $a->work_date,
                    'shift' => $shiftLabel,
                    'expected_in' => $a->expected_check_in,
                    'expected_out' => $a->expected_check_out,
                    'check_in' => $a->check_in,
                    'check_out' => $a->check_out,
                    'worked_minutes' => $a->worked_minutes,
                    'salary' => $a->salary_amount,
                    'is_late' => $a->is_late,
                    'is_early_leave' => $a->is_early_leave,
                ],
            ];
        });

        return view('admin.staff.attendances', compact(
            'attendances',
            'calendarEvents'
        ));
    }

    public function staffIndex()
    {
        $user = Auth::user();

        $attendances = Attendance::where('staff_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->get();

        return view('admin.staff.staff_attendances', compact('attendances'));
    }

    public function create()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $staffs = User::where('role', 'staff')->get();

        $calendarEvents = Attendance::with('user')->get()
            ->map(function ($a) {
                return [
                    'title' => $a->user->name . ' - ' .
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

    // ================= STORE =================
    public function store(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'staff_id' => [
                'required',
                'exists:users,id',
                Rule::unique('attendances')->where(
                    fn($q) =>
                    $q->where('staff_id', $request->staff_id)
                        ->where('work_date', $request->work_date)
                        ->where('shift', $request->shift)
                ),
            ],
            'work_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon',
            'expected_check_in' => 'required',
            'expected_check_out' => 'required',
        ]);

        Attendance::create($request->only([
            'staff_id',
            'work_date',
            'shift',
            'expected_check_in',
            'expected_check_out',
        ]));

        return redirect()
            ->route('admin.staff.attendances')
            ->with('success', 'Phân ca làm việc thành công.');
    }

    // ================= EDIT =================
    public function edit($id)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $attendance = Attendance::with('user')->findOrFail($id);
        $staffs = User::where('role', 'staff')->get();

        $calendarEvents = Attendance::with('user')->get()
            ->map(function ($a) {
                return [
                    'title' => $a->user->name . ' - ' .
                        ($a->shift === 'morning' ? 'Ca sáng' : 'Ca chiều'),
                    'start' => $a->work_date . 'T' . $a->expected_check_in,
                    'end'   => $a->work_date . 'T' . $a->expected_check_out,
                ];
            });

        return view('admin.staff.attendance_edit', compact(
            'attendance',
            'staffs',
            'calendarEvents'
        ));
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'staff_id' => 'required|exists:users,id',
            'work_date' => 'required|date',
            'shift' => 'required|in:morning,afternoon',
            'expected_check_in' => 'required',
            'expected_check_out' => 'required',
        ]);

        $attendance = Attendance::findOrFail($id);

        $attendance->update($request->only([
            'work_date',
            'shift',
            'expected_check_in',
            'expected_check_out',
        ]));

        return redirect()
            ->route('admin.staff.attendances')
            ->with('success', 'Cập nhật phân ca thành công!');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $attendance = Attendance::findOrFail($id);

        if ($attendance->check_in) {
            return back()->with('error', 'Không thể xoá ca đã chấm công!');
        }

        $attendance->delete();

        return back()->with('success', 'Xoá ca thành công!');
    }

    // ================= CHECK IN =================
    public function checkIn(Attendance $attendance)
    {
        $user = Auth::user();
        $now = Carbon::now('Asia/Ho_Chi_Minh');

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
            return back()->with('error', 'Trễ quá 2 tiếng');
        }

        $attendance->update([
            'check_in' => $now,
            'is_late' => $lateMinutes > 15,
        ]);

        return back()->with('success', 'Check-in thành công');
    }

    // ================= CHECK OUT =================
    public function checkOut(Attendance $attendance)
    {
        $user = Auth::user();

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
