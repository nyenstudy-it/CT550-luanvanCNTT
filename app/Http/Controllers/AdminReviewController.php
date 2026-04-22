<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminReviewController extends Controller
{
    private function buildSuggestedNegativeReviewers()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return Review::where('status', 'rejected')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->with(['customer' => fn($q) => $q->withCount('orders'), 'customer.user', 'product'])
            ->get()
            ->groupBy('customer_id')
            ->filter(function ($reviews) {
                return count($reviews) >= 3;
            })
            ->map(function ($reviews) {
                $customer = $reviews->first()?->customer;
                $user = $customer?->user;
                if (!$customer || !$user) {
                    return null;
                }

                return [
                    'customer' => $customer,
                    'rejected_count' => count($reviews),
                    'reviews' => $reviews->take(5),
                    'is_locked' => $user->status === 'locked',
                ];
            })
            ->filter()
            ->reject(fn($item) => $item['is_locked'])
            ->values();
    }

    public function index(Request $request)
    {
        $query = Review::with(['product', 'customer', 'replies'])
            ->withCount(['likes', 'replies'])
            ->whereIn('status', ['pending', 'approved', 'rejected']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);
            $escapedKeyword = addcslashes($keyword, '\\%_');
            $query->where(function ($q) use ($escapedKeyword) {
                $q->where('content', 'like', '%' . $escapedKeyword . '%')
                    ->orWhereHas('product', function ($q2) use ($escapedKeyword) {
                        $q2->where('name', 'like', '%' . $escapedKeyword . '%');
                    })
                    ->orWhereHas('customer.user', function ($q3) use ($escapedKeyword) {
                        $q3->where('name', 'like', '%' . $escapedKeyword . '%');
                    });
            });
        }

        $reviews = $query->latest()->paginate(10)->appends($request->query());

        $summary = [
            'total' => Review::whereIn('status', ['pending', 'approved', 'rejected'])->count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'summary'));
    }

    public function reply(Review $review, Request $request)
    {
        // Check permission - only admin/staff
        $user = $request->user();
        if (!($user->hasRole('admin') || $user->hasRole('staff'))) {
            return back()->with('error', 'Không có quyền trả lời đánh giá.');
        }

        $request->validate(['content' => 'required|string|max:2000']);

        $review->replies()->create([
            'user_id' => $user->id,
            'author_name' => $user->name,
            'is_admin' => true,
            'content' => $request->input('content'),
            'status' => 'approved',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Đã trả lời đánh giá.'], 201);
        }

        return back()->with('success', 'Đã trả lời đánh giá.');
    }

    public function replies(Review $review)
    {
        $replies = $review->replies()->orderBy('created_at', 'asc')->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'author_name' => $r->author_name ?? ($r->user->name ?? 'Admin'),
                'content' => $r->content,
                'created_at' => $r->created_at->format('d/m/Y H:i'),
            ];
        });

        return response()->json(['replies' => $replies]);
    }

    public function approve(Review $review)
    {
        $review->status = 'approved';
        $review->save();

        if ($review->customer && $review->customer->user_id) {
            Notification::createForRecipients([(int)$review->customer->user_id], [
                'type' => 'review_approved',
                'title' => 'Đánh giá đã được duyệt',
                'content' => 'Đánh giá của bạn cho sản phẩm "' . ($review->product?->name ?? 'Sản phẩm') . '" đã được duyệt và hiển thị công khai.',
                'related_id' => $review->id,
            ]);
        }

        return back()->with('success', 'Đã duyệt đánh giá.');
    }

    public function reject(Review $review)
    {
        $review->status = 'rejected';
        $review->save();
        if ($review->customer && $review->customer->user_id) {
            Notification::createForRecipients([(int)$review->customer->user_id], [
                'type' => 'review_rejected',
                'title' => 'Đánh giá đã bị từ chối',
                'content' => 'Đánh giá của bạn cho sản phẩm "' . ($review->product?->name ?? 'Sản phẩm') . '" đã bị từ chối. Bạn có thể đánh giá lại.',
                'related_id' => $review->id,
            ]);
        }

        return back()->with('success', 'Đã từ chối đánh giá.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Đã xóa đánh giá.');
    }

    // ⭐ ĐỀ XUẤT KHÓA KHÁCH HÀNG CÓ NHIỀU REVIEW TIÊU CỰC TRONG THÁNG
    public function suggestLockNegativeReviewers()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Lấy các khách hàng có 3+ review bị reject trong tháng này
        $allSuggested = $this->buildSuggestedNegativeReviewers();

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
                'path' => route('admin.suggest-lock-negative-reviewers'),
                'query' => request()->query(),
            ]
        );

        $stats = [
            'total_rejected_this_month' => Review::where('status', 'rejected')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count(),
            'customers_flagged' => $total,
            'month' => now()->format('m/Y'),
        ];

        return view('admin.customers.suggest-lock-negative-reviewers', compact('suggestedCustomers', 'stats'));
    }

    // 🔌 API: Lấy dữ liệu đề xuất khóa (AJAX)
    public function apiSuggestLockNegativeReviewers()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Lấy các khách hàng có 3+ review bị reject trong tháng này
        $allSuggested = $this->buildSuggestedNegativeReviewers();

        // Pagination: 10 per page
        $page = request('page', 1);
        $perPage = 10;
        $total = $allSuggested->count();
        $lastPage = ceil($total / $perPage);
        $items = $allSuggested->slice(($page - 1) * $perPage, $perPage)->values();

        $stats = [
            'total_rejected_this_month' => Review::where('status', 'rejected')
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count(),
            'customers_flagged' => $total,
            'month' => now()->format('m/Y'),
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
}
