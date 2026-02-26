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

        $attendances = Attendance::with('staff.user')
            ->orderBy('work_date', 'asc')
            ->orderByRaw("FIELD(shift, 'morning', 'afternoon')")
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

        return view('admin.attendances.index', compact(
            'attendances',
            'calendarEvents'
        ));
    }

    public function staffIndex()
    {
        $user = Auth::user();

        $attendances = Attendance::where('staff_id', $user->id)
            ->orderBy('work_date', 'asc')
            ->orderByRaw("FIELD(shift, 'morning', 'afternoon')")
            ->paginate(10);

        $totalWorkedMinutes = Attendance::where('staff_id', $user->id)
            ->sum('worked_minutes');

        $totalSalary = Attendance::where('staff_id', $user->id)
            ->sum('salary_amount');


        return view('admin.attendances.staff_attendances', compact('attendances', 'totalWorkedMinutes', 'totalSalary'));
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

        return view('admin.attendances.create
        ', compact(
            'staffs',
            'calendarEvents'
        ));
    }

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
            ->route('admin.attendances.index')
            ->with('success', 'Phân ca làm việc thành công.');
    }

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

        return view('admin.attendances.edit', compact(
            'attendance',
            'staffs',
            'calendarEvents'
        ));
    }

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
            ->route('admin.attendances.index')
            ->with('success', 'Cập nhật phân ca thành công!');
    }

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

    public function checkIn(Request $request, Attendance $attendance)
    {
        $user = Auth::user();
        $now  = Carbon::now('Asia/Ho_Chi_Minh');

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

        $isLate = $lateMinutes > 15;

        $reasonType = $request->input('reason_type');
        $reason     = $request->input('reason');

        if ($isLate) {

            if (!$reason || $reasonType !== 'late') {
                return back()
                    ->with('require_reason', true)
                    ->with('attendance_id', $attendance->id)
                    ->with('reason_type', 'late');
            }

            $attendance->update([
                'check_in'     => $now,
                'is_late'      => 1,
                'late_reason'  => $reason,
                'late_status'  => 'pending',
            ]);
        } else {

            $attendance->update([
                'check_in'    => $now,
                'is_late'     => $lateMinutes > 0 ? 1 : 0,
                'late_status' => null,
            ]);
        }

        return back()->with('success', 'Check-in thành công');
    }

    public function checkOut(Request $request, Attendance $attendance)
    {
        $user  = Auth::user();
        $staff = $user->staff;

        if (!$staff || $attendance->staff_id !== $staff->user_id) {
            abort(403);
        }

        if (!$attendance->check_in) {
            return back()->with('error', 'Chưa check-in.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'Đã check-out rồi.');
        }

        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $attendance->check_out = $now;

        $checkIn  = Carbon::parse($attendance->check_in);
        $checkOut = Carbon::parse($attendance->check_out);

        $expectedStart = Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_in);
        $expectedEnd   = Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_out);

        if ($checkOut->lte($checkIn)) {
            return back()->with('error', 'Thời gian không hợp lệ.');
        }

        if ($checkOut->lt($expectedEnd)) {

            $attendance->is_early_leave = 1;

            $reasonType = $request->input('reason_type');
            $reason     = $request->input('reason');

            if (!$reason || $reasonType !== 'early') {
                return back()->with('error', 'Bạn đang check-out trước giờ. Vui lòng nhập lý do.');
            }

            $attendance->early_leave_reason = $reason;
            $attendance->early_leave_status = 'pending';
        } else {
            $attendance->is_early_leave = 0;
        }

        $fullShiftMinutes = $expectedStart->diffInMinutes($expectedEnd);
        $workedMinutes    = $fullShiftMinutes;

        if ($attendance->is_late) {

            $lateMinutes = $expectedStart->diffInMinutes($checkIn);

            if ($attendance->late_status !== 'approved') {
                $workedMinutes -= $lateMinutes;
            }
        }

        if ($attendance->is_early_leave) {

            $earlyMinutes = $checkOut->diffInMinutes($expectedEnd);

            if ($attendance->early_leave_status !== 'approved') {
                $workedMinutes -= $earlyMinutes;
            }
        }

        if ($workedMinutes < 0) {
            $workedMinutes = 0;
        }

        $hourlyRate = $staff->employment_status === 'official'
            ? $staff->official_hourly_wage
            : $staff->probation_hourly_wage;

        $salary = ($workedMinutes / 60) * $hourlyRate;

        $attendance->worked_minutes = $workedMinutes;
        $attendance->salary_amount  = round($salary);
        $attendance->is_completed   = 1;

        $attendance->save();

        return back()->with('success', 'Check-out thành công.');
    }

    public function pending()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $attendances = Attendance::with('staff')
            ->where(function ($q) {
                $q->where('late_status', 'pending')
                    ->orWhere('early_leave_status', 'pending');
            })
            ->orderBy('work_date', 'desc')
            ->get();

        return view('admin.attendances.pending', compact('attendances'));
    }

    public function approveLate(Attendance $attendance)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($attendance->late_status !== 'pending') {
            return back()->with('error', 'Không có yêu cầu duyệt.');
        }

        $attendance->late_status = 'approved';

        $staff = $attendance->staff;

        $hourlyRate = $staff->employment_status === 'official'
            ? $staff->official_hourly_wage
            : $staff->probation_hourly_wage;

        $expectedMinutes = 180;

        $attendance->worked_minutes = $expectedMinutes;
        $attendance->salary_amount  = round(($expectedMinutes / 60) * $hourlyRate);

        $attendance->save();

        return back()->with('success', 'Đã duyệt đi trễ và tính đủ lương ca.');
    }

    public function rejectLate(Attendance $attendance)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($attendance->late_status !== 'pending') {
            return back()->with('error', 'Không có yêu cầu duyệt.');
        }

        $attendance->late_status = 'rejected';

        $attendance->save();

        return back()->with('success', 'Đã từ chối đi trễ.');
    }

    public function approveEarly(Attendance $attendance)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($attendance->early_leave_status !== 'pending') {
            return back()->with('error', 'Không có yêu cầu duyệt.');
        }

        $attendance->early_leave_status = 'approved';

        $staff = $attendance->staff;

        $hourlyRate = $staff->employment_status === 'official'
            ? $staff->official_hourly_wage
            : $staff->probation_hourly_wage;

        $expectedMinutes = 180;

        if (($attendance->worked_minutes ?? 0) < $expectedMinutes) {

            $attendance->worked_minutes = $expectedMinutes;
            $attendance->salary_amount  = round(($expectedMinutes / 60) * $hourlyRate);
        }

        $attendance->save();

        return back()->with('success', 'Đã duyệt về sớm và tính đủ lương ca.');
    }

    public function rejectEarly(Attendance $attendance)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        if ($attendance->early_leave_status !== 'pending') {
            return back()->with('error', 'Không có yêu cầu duyệt.');
        }

        $attendance->early_leave_status = 'rejected';

        $attendance->save();

        return back()->with('success', 'Đã từ chối về sớm.');
    }

    public function submitLateReason(Request $request, Attendance $attendance)
    {
        $staff = Auth::user()->staff;

        if (!$staff || $attendance->staff_id !== $staff->id) {
            abort(403);
        }

        if (!$attendance->is_late) {
            return back()->with('error', 'Ca này không có đi trễ.');
        }

        if (in_array($attendance->late_status, ['approved', 'rejected'])) {
            return back()->with('error', 'Yêu cầu đã được xử lý.');
        }

        $request->validate([
            'late_reason' => 'required|string|max:500'
        ]);

        $attendance->update([
            'late_reason' => $request->late_reason,
            'late_status' => 'pending'
        ]);

        return back()->with('success', 'Đã gửi lý do đi trễ.');
    }

    public function submitEarlyReason(Request $request, Attendance $attendance)
    {
        $staff = Auth::user()->staff;

        if (!$staff || $attendance->staff_id !== $staff->id) {
            abort(403);
        }

        if (!$attendance->is_early_leave) {
            return back()->with('error', 'Ca này không có về sớm.');
        }

        if (in_array($attendance->early_leave_status, ['approved', 'rejected'])) {
            return back()->with('error', 'Yêu cầu đã được xử lý.');
        }

        $request->validate([
            'early_leave_reason' => 'required|string|max:500'
        ]);

        $attendance->update([
            'early_leave_reason' => $request->early_leave_reason,
            'early_leave_status' => 'pending'
        ]);

        return back()->with('success', 'Đã gửi lý do về sớm.');
    }
}
