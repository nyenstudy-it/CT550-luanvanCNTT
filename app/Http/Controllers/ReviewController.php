<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\ProductPricingService;
use App\Models\Notification;

class ReviewController extends Controller
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function reviewForm(Product $product, $orderId = null)
    {
        $product->loadMissing('variants.inventory', 'variants.images', 'variants.primaryImage', 'category', 'supplier');

        $firstVariantPrice = (float) ($product->variants->first()?->price ?? 0);
        $productPricing = $this->productPricingService->pricingForProduct($product, $firstVariantPrice);
        $variantPricing = $product->variants->mapWithKeys(function ($variant) use ($product) {
            $pricing = $this->productPricingService->pricingForProduct($product, (float) $variant->price);

            return [
                $variant->id => $pricing,
            ];
        });

        $user = Auth::user();
        $customer = $user->customer ?? null;

        if (!$customer) {
            return view('pages.review', [
                'product' => $product,
                'orders' => collect(),
                'canReview' => false,
                'error' => 'Không tìm thấy thông tin khách hàng.',
                'productPricing' => $productPricing,
                'variantPricing' => $variantPricing,
            ]);
        }

        $orders = Order::where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->whereHas('payment', function ($q) {
                $q->where('status', 'paid');
            })
            ->whereHas('items', function ($q) use ($product) {
                $q->whereHas('variant', function ($qv) use ($product) {
                    $qv->where('product_id', $product->id);
                });
            })
            ->get();

        $selectedOrder = null;
        if ($orderId) {
            $selectedOrder = $orders->firstWhere('id', $orderId);
        }

        if ($orders->isEmpty() || ($orderId && !$selectedOrder)) {

            Log::info('ReviewForm check', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'requested_order_id' => $orderId,
                'found_order_ids' => $orders->pluck('id')->toArray(),
                'customer_id' => $customer->id,
            ]);

            return view('pages.review', [
                'product' => $product,
                'orders' => $orders,
                'canReview' => false,
                'error' => 'Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua và nhận hàng.',
                'productPricing' => $productPricing,
                'variantPricing' => $variantPricing,
            ]);
        }

        return view('pages.review', [
            'product' => $product,
            'orders' => $orders,
            'selectedOrder' => $selectedOrder,
            'canReview' => true,
            'productPricing' => $productPricing,
            'variantPricing' => $variantPricing,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'nullable|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $customer = $user->customer ?? null;

        // Nếu không có order_id, tự tìm đơn hàng hợp lệ mới nhất
        $orderQuery = Order::where('customer_id', $customer->id ?? 0)
            ->where('status', 'completed')
            ->whereHas('payment', fn($q) => $q->where('status', 'paid'))
            ->whereHas('items', fn($q) => $q->whereHas('variant', fn($q2) => $q2->where('product_id', $request->input('product_id'))));

        if ($request->filled('order_id')) {
            $orderQuery->where('id', $request->order_id);
        }

        $order = $orderQuery->latest()->firstOrFail();

        $item = $order->items()->whereHas('variant', fn($q) => $q->where('product_id', $request->input('product_id')))->firstOrFail();

        // Tạo review (gắn customer_id thực tế từ order)
        $review = $item->variant->product->reviews()->create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'rating' => $request->input('rating'),
            'content' => $request->input('content'),
            'status' => 'pending'
        ]);

        $reviewRecipients = Notification::recipientIdsForGroups(['admin', 'order_staff']);
        Notification::createForRecipients($reviewRecipients, [
            'type' => 'new_review',
            'title' => 'Có đánh giá mới',
            'content' => 'Sản phẩm #' . $request->input('product_id') . ' có đánh giá mới cần xử lý.',
            'related_id' => $review->id,
        ]);

        // Trả về trang trước (sản phẩm) thay vì chuyển sang danh sách đơn
        return redirect()->back()->with('success', 'Đã gửi đánh giá, chờ duyệt.');
    }
}
