<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Review;
use App\Models\OrderReturn;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    private function buildSuggestedRefundCustomers()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return Order::whereHas('payment', function ($query) use ($currentMonth, $currentYear) {
            $query->whereIn('refund_status', ['pending', 'completed'])
                ->whereMonth('updated_at', $currentMonth)
                ->whereYear('updated_at', $currentYear);
        })
            ->with(['customer' => fn($q) => $q->withCount('orders'), 'customer.user', 'payment'])
            ->get()
            ->groupBy('customer_id')
            ->filter(function ($orders) {
                return count($orders) >= 2;
            })
            ->map(function ($orders) {
                $customer = $orders->first()?->customer;
                $user = $customer?->user;
                if (!$customer || !$user) {
                    return null;
                }

                return [
                    'customer' => $customer,
                    'refund_count' => count($orders),
                    'orders' => $orders->take(5),
                    'total_refund_amount' => $orders->sum(fn($o) => $o->total_amount ?? 0),
                    'is_locked' => $user->status === 'locked',
                ];
            })
            ->filter()
            ->reject(fn($item) => $item['is_locked'])
            ->values();
    }

    private const LOCK_REASON_PRESETS = [
        'negative_reviews' => 'Quá nhiều đánh giá tiêu cực',
        'spam' => 'Spam/lạm dụng hệ thống',
        'fraud' => 'Gian lận',
        'refund_abuse' => 'Lạm dụng hoàn tiền',
        'other' => 'Lý do khác',
    ];

    // DANH SÁCH KHÁCH HÀNG
    public function list(Request $request)
    {
        $query = Customer::with('user')
            ->withCount('orders');

        // TÌM KIẾM TÊN / EMAIL
        if ($request->keyword) {
            $query->whereHas('user', function ($q) use ($request) {
                $escaped = addcslashes($request->keyword, '\\%_');
                $q->where('name', 'like', '%' . $escaped . '%')
                    ->orWhere('email', 'like', '%' . $escaped . '%');
            });
        }

        // LỌC TRẠNG THÁI TÀI KHOẢN
        if ($request->status) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // LỌC NGÀY ĐĂNG KÝ
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $customers = $query
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::whereHas('user', fn($q) => $q->where('status', 'active'))->count(),
            'locked' => Customer::whereHas('user', fn($q) => $q->where('status', 'locked'))->count(),
            'new_this_month' => Customer::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $lockReasonPresets = self::LOCK_REASON_PRESETS;

        return view('admin.customers.list', compact('customers', 'stats', 'lockReasonPresets'));
    }


    // XEM CHI TIẾT KHÁCH HÀNG
    public function show($id)
    {
        $customer = Customer::with('user')
            ->withCount('orders')
            ->findOrFail($id);

        return view('admin.customers.show', compact('customer'));
    }


    // KHÓA TÀI KHOẢN
    public function lock(Request $request, $id)
    {
        $request->validate([
            'reason_key' => 'required|string|in:' . implode(',', array_keys(self::LOCK_REASON_PRESETS)),
            'reason_note' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);

        $reasonKey = $request->input('reason_key');
        $reasonLabel = self::LOCK_REASON_PRESETS[$reasonKey] ?? 'Lý do khác';
        $reasonNote = trim((string) $request->input('reason_note'));

        $lockedReason = $reasonLabel;
        if ($reasonNote !== '') {
            $lockedReason .= ' - ' . $reasonNote;
        }

        $user->update([
            'status' => 'locked',
            'locked_reason' => $lockedReason,
            'locked_at' => now(),
        ]);

        // Return JSON for AJAX/fetch requests
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã khóa tài khoản khách hàng',
            ]);
        }

        return redirect()->back()->with('success', 'Đã khóa tài khoản khách hàng');
    }



    // MỞ KHÓA TÀI KHOẢN
    public function unlock($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'status' => 'active',
            'locked_reason' => null,
            'locked_at' => null,
        ]);

        return redirect()->back()->with('success', 'Đã mở khóa tài khoản');
    }



    // XỨ LÝ KHÁCH CÓ NHIỀU ĐÁNH GIÁ TIÊU CỰC
    public function negativereviewflagged(Request $request)
    {
        $negativeThreshold = 5; // Số review bị từ chối để khóa account

        // Tìm khách hàng có quá nhiều review bị từ chối
        $flaggedCustomers = Customer::with(['user', 'reviews'])
            ->get()
            ->filter(function ($customer) use ($negativeThreshold) {
                $rejectedCount = Review::where('customer_id', $customer->id)
                    ->where('status', 'rejected')
                    ->count();

                return $rejectedCount >= $negativeThreshold &&
                    $customer->user->status !== 'locked'; // Chưa bị khóa
            })
            ->values();

        return view('admin.customers.flagged-negative-reviews', compact('flaggedCustomers', 'negativeThreshold'));
    }

    // KHÓA KHÁCH HÀNG VỀ ĐÁNH GIÁ TIÊU CỰC
    public function lockForNegativeReview(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rejectedCount = Review::where('customer_id', $user->customer->id)
            ->where('status', 'rejected')
            ->count();

        $user->update([
            'status' => 'locked',
            'locked_reason' => 'Quá nhiều đánh giá tiêu cực bị từ chối (' . $rejectedCount . ' review)',
            'locked_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Đã khóa tài khoản do quá nhiều đánh giá tiêu cực');
    }

    // XEM DANH SÁCH KHÁCH BÌNH LUẬN TIÊU CỰC
    public function customerNegativeReviews(Request $request)
    {
        $query = Review::with(['customer', 'product', 'customer.user'])
            ->where('status', 'rejected')
            ->latest();

        // Lọc theo khách hàng
        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        // Lọc theo rating
        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        // Lọc theo ngày
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $negativeReviews = $query->paginate(20);

        // Thống kê
        $stats = [
            'total_rejected' => Review::where('status', 'rejected')->count(),
            'customers_with_rejected' => Review::where('status', 'rejected')
                ->distinct('customer_id')
                ->count('customer_id'),
            'avg_rating_rejected' => Review::where('status', 'rejected')
                ->avg('rating'),
        ];

        return view('admin.customers.negative-reviews', compact('negativeReviews', 'stats'));
    }

    // ⭐ ĐỀ XUẤT KHÓA KHÁCH HÀNG CÓ NHIỀU ĐƠN HOÀN TRẢ TRONG THÁNG
    public function suggestLockRefundRequests()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Lấy các đơn hàng có refund status = 'pending' hoặc 'completed' trong tháng này (từ Payment table)
        $allSuggested = $this->buildSuggestedRefundCustomers();

        // Pagination: 10 per page
        $page = request('page', 1);
        $perPage = 10;
        $total = $allSuggested->count();
        $items = $allSuggested->slice(($page - 1) * $perPage, $perPage)->values();
        $suggestedCustomers = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => route('admin.suggest-lock-refund-requests'),
                'query' => request()->query(),
            ]
        );

        $stats = [
            'total_refunds_this_month' => Order::whereHas('payment', function ($query) use ($currentMonth, $currentYear) {
                $query->whereIn('refund_status', ['pending', 'completed'])
                    ->whereMonth('updated_at', $currentMonth)
                    ->whereYear('updated_at', $currentYear);
            })->count(),
            'customers_flagged' => $total,
            'month' => now()->format('m/Y'),
            'total_refund_amount' => Order::whereHas('payment', function ($query) use ($currentMonth, $currentYear) {
                $query->whereIn('refund_status', ['pending', 'completed'])
                    ->whereMonth('updated_at', $currentMonth)
                    ->whereYear('updated_at', $currentYear);
            })->sum('total_amount'),
        ];

        return view('admin.customers.suggest-lock-refund-requests', compact('suggestedCustomers', 'stats'));
    }

    public function apiSuggestLockRefundRequests()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Lấy các đơn hàng có refund status = 'pending' hoặc 'completed' trong tháng này
        $allSuggested = $this->buildSuggestedRefundCustomers();

        // Pagination: 10 per page
        $page = request('page', 1);
        $perPage = 10;
        $total = $allSuggested->count();
        $lastPage = ceil($total / $perPage);
        $items = $allSuggested->slice(($page - 1) * $perPage, $perPage)->values();

        $stats = [
            'total_refunds_this_month' => Order::whereHas('payment', function ($query) use ($currentMonth, $currentYear) {
                $query->whereIn('refund_status', ['pending', 'completed'])
                    ->whereMonth('updated_at', $currentMonth)
                    ->whereYear('updated_at', $currentYear);
            })->count(),
            'customers_flagged' => $total,
            'month' => now()->format('m/Y'),
            'total_refund_amount' => Order::whereHas('payment', function ($query) use ($currentMonth, $currentYear) {
                $query->whereIn('refund_status', ['pending', 'completed'])
                    ->whereMonth('updated_at', $currentMonth)
                    ->whereYear('updated_at', $currentYear);
            })->sum('total_amount'),
        ];

        return response()->json([
            'suggestedCustomers' => $items,
            'stats' => $stats,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'total' => $total,
                'per_page' => $perPage,
            ],
        ]);
    }

    // API: Chi tiết yêu cầu hoàn trả cho một khách (trả kèm ảnh)
    public function apiRefundDetails($customerId)
    {
        // API hiện đang được gọi từ nhiều trang, có trang truyền customers.id,
        // có trang truyền users.id (orders.customer_id). Hỗ trợ cả hai để tránh lệch dữ liệu.
        $resolvedUserId = (int) $customerId;
        $customerByRowId = Customer::find($customerId);
        if ($customerByRowId && !empty($customerByRowId->user_id)) {
            $resolvedUserId = (int) $customerByRowId->user_id;
        }

        // Lấy TẤT CẢ hoàn trả của khách (không chỉ tháng hiện tại)
        $orders = Order::where('customer_id', $resolvedUserId)
            ->whereHas('returns')
            ->with(['returns.images', 'payment'])
            ->get();

        $returns = [];
        foreach ($orders as $order) {
            foreach ($order->returns as $ret) {
                $imgUrls = [];
                foreach ($ret->images as $img) {
                    $path = $img->image_path ?? null;
                    if ($path) {
                        $imgUrls[] = Storage::url($path);
                    }
                }

                // Find payment info on order (latest)
                $payment = $order->payment;
                $refundAmount = null;
                $refundStatus = null;
                if ($payment) {
                    $refundAmount = $payment->refund_amount ?? null;
                    $refundStatus = $payment->refund_status ?? null;
                }

                $returns[] = [
                    'id' => $ret->id,
                    'order_id' => $order->id,
                    'order_total' => $order->total_amount ?? null,
                    'refund_amount' => $refundAmount,
                    'refund_status' => $refundStatus,
                    'reason_vn' => $ret->reason_vn ?? $ret->reason,
                    'description' => $ret->description,
                    'status_vn' => $ret->status_vn ?? $ret->status,
                    'images' => $imgUrls,
                    'created_at' => $ret->created_at?->toDateTimeString(),
                ];
            }
        }

        return response()->json([
            'customer_id' => $customerId,
            'resolved_user_id' => $resolvedUserId,
            'returns' => $returns,
        ]);
    }

    // XÓA KHÁCH HÀNG
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('admin.customers.list')
            ->with('success', 'Đã xóa khách hàng thành công.');
    }
}
