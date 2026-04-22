<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Http\Request;

class ReviewReplyController extends Controller
{
    public function store(Review $review, Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:review_replies,id'
        ]);

        $user = $request->user();
        if (! $user) {
            return redirect()->back()->with('error', 'Bạn cần đăng nhập để phản hồi.');
        }

        $isAdmin = $user->hasRole('admin') || $user->hasRole('staff');
        $isCustomer = $user->hasRole('customer');

        // Admin/Staff có thể reply trực tiếp
        // Customer chỉ có thể reply nested (parent_id phải có)
        if (!$isAdmin && $isCustomer && !$request->filled('parent_id')) {
            return redirect()->back()->with('error', 'Khách hàng chỉ có thể trả lời bình luận, không thể phản hồi trực tiếp lên đánh giá.');
        }

        // Admin/Staff có status approved ngay, customer pending
        $status = $isAdmin ? 'approved' : 'pending';

        ReviewReply::create([
            'review_id' => $review->id,
            'parent_id' => $request->input('parent_id'),
            'user_id' => $user->id,
            'author_name' => $user->name,
            'is_admin' => $isAdmin,
            'content' => $request->input('content'),
            'status' => $status,
        ]);

        return redirect()->back()->with('success', 'Phản hồi đã được gửi' . ($status === 'pending' ? ', chờ duyệt.' : '.'));
    }
}
