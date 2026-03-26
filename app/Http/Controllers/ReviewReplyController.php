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

        ReviewReply::create([
            'review_id' => $review->id,
            'parent_id' => $request->input('parent_id'),
            'user_id' => $user->id,
            'author_name' => $user->name,
            'is_admin' => $user->hasRole('admin') || $user->hasRole('staff'),
            'content' => $request->input('content'),
            'status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Phản hồi đã được gửi.');
    }
}
