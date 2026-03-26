<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewLike;
use Illuminate\Http\Request;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review, Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $customer = $user->customer ?? null;
        if (! $customer) {
            return response()->json(['error' => 'No customer record'], 403);
        }

        $like = ReviewLike::where('review_id', $review->id)->where('customer_id', $customer->id)->first();
        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            ReviewLike::create(['review_id' => $review->id, 'customer_id' => $customer->id]);
            $liked = true;
        }

        return response()->json(['liked' => $liked, 'count' => $review->likes()->count()]);
    }
}
