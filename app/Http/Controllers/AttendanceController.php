<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Staff;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\IpUtils;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $query = Attendance::with('staff.user');
        if ($request->from_date) {
            $query->whereDate('work_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('work_date', '<=', $request->to_date);
        }

        if ($request->shift) {
            $query->where('shift', $request->shift);
        }
        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        $attendances = $query
            ->orderBy('work_date', 'asc')
            ->orderByRaw("FIELD(shift, 'morning', 'afternoon')")
            ->get();

        if ($request->status) {
            $attendances = $attendances->filter(function ($item) use ($request) {
                return $item->computed_status === $request->status;
            });
        }

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

        $staffs = Staff::with('user')->get();

        return view('admin.attendances.index', compact(
            'attendances',
            'calendarEvents',
            'staffs'
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

        // Lấy đúng Staff + user
        $staffs = Staff::whereHas('user')
            ->with('user')
            ->get();

        // Lấy attendance đúng quan hệ
        $calendarEvents = Attendance::whereHas('staff.user')
            ->with('staff.user')
            ->get()
            ->map(function ($a) {
                return [
                    'title' => $a->staff->user->name . ' - ' .
                        ($a->shift === 'morning' ? 'Ca sáng' : 'Ca chiều'),
                    'start' => $a->work_date . 'T' . $a->expected_check_in,
                    'end'   => $a->work_date . 'T' . $a->expected_check_out,
                ];
            });

        return view('admin.attendances.create', compact(
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

        $attendance = Attendance::with('staff.user')->findOrFail($id);

        $staffs = Staff::with('user')->get();

        $calendarEvents = Attendance::whereHas('staff.user')
            ->with('staff.user')
            ->get()
            ->map(function ($a) {
                return [
                    'title' => $a->staff->user->name . ' - ' .
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

        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'network_type' => 'nullable|string|max:50',
        ]);

        if ($attendance->staff_id !== $user->id) {
            abort(403);
        }

        if ($attendance->work_date !== $now->toDateString()) {
            return back()->with('error', 'Chưa tới ngày làm việc');
        }

        if ($attendance->check_in) {
            return back()->with('error', 'Bạn đã check-in');
        }

        $accessResult = $this->evaluateAttendanceAccess(
            $request->ip(),
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null
        );

        if (!$accessResult['allowed']) {
            return back()->with('error', $accessResult['message']);
        }

        $expectedStart = Carbon::parse(
            $attendance->work_date . ' ' . $attendance->expected_check_in,
            'Asia/Ho_Chi_Minh'
        );

        $lateMinutes = $expectedStart->diffInMinutes($now, false);

        if ($lateMinutes < 0) {
            return back()->with('error', 'Chưa tới giờ làm');
        }

        if ($lateMinutes > 120) {
            return back()->with('error', 'Trễ quá 2 tiếng');
        }

        $isLate = $lateMinutes > 15;

        $attendance->update([
            'check_in' => $now,
            'is_late'  => $isLate ? 1 : 0,
            'late_status' => $isLate ? 'rejected' : null,
            'late_reason' => null,
            'check_in_ip' => $request->ip(),
            'check_in_latitude' => $validated['latitude'] ?? null,
            'check_in_longitude' => $validated['longitude'] ?? null,
            'check_in_network_type' => $validated['network_type'] ?? null,
            'check_in_distance_meters' => $accessResult['distance_meters'],
            'check_in_verification_method' => $accessResult['method'],
        ]);

        $adminRecipients = Notification::recipientIdsForGroups(['admin']);
        $shiftLabel = $attendance->shift === 'morning' ? 'ca sáng' : 'ca chiều';
        Notification::createForRecipients($adminRecipients, [
            'type' => 'attendance_check_in',
            'title' => 'Nhân viên vừa chấm công',
            'content' => $user->name . ' đã check-in ' . $shiftLabel . ' ngày ' . $attendance->work_date . '.',
            'related_id' => $attendance->id,
        ]);

        return back()->with('success', 'Check-in thành công');
    }

    public function checkOut(Request $request, Attendance $attendance)
    {
        $user  = Auth::user();
        $staff = $user->staff;

        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'network_type' => 'nullable|string|max:50',
        ]);

        if (!$staff || $attendance->staff_id !== $staff->user_id) {
            abort(403);
        }

        if (!$attendance->check_in) {
            return back()->with('error', 'Chưa check-in.');
        }

        if ($attendance->check_out) {
            return back()->with('error', 'Đã check-out rồi.');
        }

        $accessResult = $this->evaluateAttendanceAccess(
            $request->ip(),
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null
        );

        if (!$accessResult['allowed']) {
            return back()->with('error', $accessResult['message']);
        }

        $now = Carbon::now('Asia/Ho_Chi_Minh');

        $checkIn = Carbon::parse($attendance->check_in, 'Asia/Ho_Chi_Minh');

        $expectedStart = Carbon::parse(
            $attendance->work_date . ' ' . $attendance->expected_check_in,
            'Asia/Ho_Chi_Minh'
        );

        $expectedEnd = Carbon::parse(
            $attendance->work_date . ' ' . $attendance->expected_check_out,
            'Asia/Ho_Chi_Minh'
        );

        /*
    |--------------------------------------------------------------------------
    | AUTO CHECKOUT SAU 2 GIỜ TỪ CHECK-IN
    |--------------------------------------------------------------------------
    */

        $twoHoursAfterCheckIn = $checkIn->copy()->addHours(2);

        if ($now->gte($twoHoursAfterCheckIn)) {
            // Hệ thống tự đóng ca tại giờ kết thúc ca
            $checkOut = $expectedEnd;
        } else {
            $checkOut = $now;
        }

        if ($checkOut->lte($checkIn)) {
            return back()->with('error', 'Thời gian không hợp lệ.');
        }

        /*
    |--------------------------------------------------------------------------
    | KIỂM TRA VỀ SỚM
    |--------------------------------------------------------------------------
    */

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

        /*
    |--------------------------------------------------------------------------
    | TÍNH PHÚT LÀM THỰC TẾ
    |--------------------------------------------------------------------------
    */

        $actualEnd = $checkOut->gt($expectedEnd) ? $expectedEnd : $checkOut;

        $workedMinutes = $checkIn->diffInMinutes($actualEnd);

        // Trừ phút đi trễ (>15p đã đánh dấu từ check-in)
        if ($attendance->is_late) {
            $lateMinutes = $expectedStart->diffInMinutes($checkIn);
            $workedMinutes -= $lateMinutes;
        }

        // Trừ phút về sớm nếu chưa được duyệt
        if ($attendance->is_early_leave && $attendance->early_leave_status !== 'approved') {
            $earlyMinutes = $actualEnd->diffInMinutes($expectedEnd);
            $workedMinutes -= $earlyMinutes;
        }

        if ($workedMinutes < 0) {
            $workedMinutes = 0;
        }

        /*
    |--------------------------------------------------------------------------
    | TÍNH LƯƠNG
    |--------------------------------------------------------------------------
    */

        $hourlyRate = $staff->employment_status === 'official'
            ? $staff->official_hourly_wage
            : $staff->probation_hourly_wage;

        $salary = ($workedMinutes / 60) * $hourlyRate;

        $attendance->check_out      = $checkOut;
        $attendance->worked_minutes = $workedMinutes;
        $attendance->salary_amount  = round($salary);
        $attendance->is_completed   = 1;
        $attendance->check_out_ip = $request->ip();
        $attendance->check_out_latitude = $validated['latitude'] ?? null;
        $attendance->check_out_longitude = $validated['longitude'] ?? null;
        $attendance->check_out_network_type = $validated['network_type'] ?? null;
        $attendance->check_out_distance_meters = $accessResult['distance_meters'];
        $attendance->check_out_verification_method = $accessResult['method'];

        $attendance->save();

        return back()->with('success', 'Check-out thành công.');
    }

    public function pending()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $attendances = Attendance::with('staff.user')
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

        $expectedStart = Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_in);
        $expectedEnd   = Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_out);

        $expectedMinutes = $expectedStart->diffInMinutes($expectedEnd);


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

        $expectedStart = Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_in);
        $expectedEnd   = Carbon::parse($attendance->work_date . ' ' . $attendance->expected_check_out);

        $expectedMinutes = $expectedStart->diffInMinutes($expectedEnd);


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

        if (!$staff || $attendance->staff_id !== $staff->user_id) {
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

        if (!$staff || $attendance->staff_id !== $staff->user_id) {
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

    private function evaluateAttendanceAccess(string $ip, ?float $latitude, ?float $longitude): array
    {
        $allowedNetworks = config('attendance.allowed_networks', []);
        $officeLat = config('attendance.origin_latitude');
        $officeLng = config('attendance.origin_longitude');
        $maxDistanceMeters = (float) config('attendance.max_distance_meters', 50);

        $allowByNetwork = $this->isAllowedNetwork($ip, $allowedNetworks);
        $allowByRadius = false;
        $distanceMeters = null;

        if (
            is_numeric($officeLat)
            && is_numeric($officeLng)
            && $latitude !== null
            && $longitude !== null
        ) {
            $distanceMeters = $this->calculateDistanceMeters(
                (float) $officeLat,
                (float) $officeLng,
                $latitude,
                $longitude
            );

            $allowByRadius = $distanceMeters <= $maxDistanceMeters;
        }

        if ($allowByNetwork && $allowByRadius) {
            $method = 'both';
        } elseif ($allowByNetwork) {
            $method = 'wifi';
        } elseif ($allowByRadius) {
            $method = 'radius';
        } else {
            $method = 'none';
        }

        if ($method === 'none') {
            $message = 'Chấm công bị từ chối: cần cùng mạng Wi-Fi hợp lệ hoặc trong bán kính 50m từ điểm gốc.';

            if ($distanceMeters !== null) {
                $message .= ' Khoảng cách hiện tại: ' . round($distanceMeters, 2) . 'm.';
            }

            return [
                'allowed' => false,
                'method' => $method,
                'distance_meters' => $distanceMeters,
                'message' => $message,
            ];
        }

        return [
            'allowed' => true,
            'method' => $method,
            'distance_meters' => $distanceMeters,
            'message' => null,
        ];
    }

    private function isAllowedNetwork(string $ip, array $allowedNetworks): bool
    {
        if (empty($allowedNetworks)) {
            return false;
        }

        return IpUtils::checkIp($ip, $allowedNetworks);
    }

    private function calculateDistanceMeters(
        float $originLat,
        float $originLng,
        float $targetLat,
        float $targetLng
    ): float {
        $earthRadius = 6371000;

        $latFrom = deg2rad($originLat);
        $lngFrom = deg2rad($originLng);
        $latTo = deg2rad($targetLat);
        $lngTo = deg2rad($targetLng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2)
                + cos($latFrom) * cos($latTo) * pow(sin($lngDelta / 2), 2)
        ));

        return $earthRadius * $angle;
    }
}
