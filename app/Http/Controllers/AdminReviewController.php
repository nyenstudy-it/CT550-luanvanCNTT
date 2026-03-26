<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['product', 'customer.user', 'replies'])
            ->withCount(['likes', 'replies'])
            ->whereIn('status', ['pending', 'approved']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('content', 'like', '%' . $keyword . '%')
                    ->orWhereHas('product', function ($q2) use ($keyword) {
                        $q2->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('customer.user', function ($q3) use ($keyword) {
                        $q3->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        $reviews = $query->latest()->paginate(25)->appends($request->query());

        $summary = [
            'total' => Review::whereIn('status', ['pending', 'approved'])->count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'summary'));
    }

    public function reply(Review $review, Request $request)
    {
        $request->validate(['content' => 'required|string|max:2000']);
        $user = $request->user();
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
        return back()->with('success', 'Đã duyệt đánh giá.');
    }

    public function reject(Review $review)
    {
        $review->status = 'rejected';
        $review->save();
        return back()->with('success', 'Đã từ chối đánh giá.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Đã xóa đánh giá.');
    }
}
