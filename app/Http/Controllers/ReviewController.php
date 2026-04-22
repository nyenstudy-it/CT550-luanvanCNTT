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
                'reviewNotice' => 'Không tìm thấy thông tin khách hàng.',
                'productPricing' => $productPricing,
                'variantPricing' => $variantPricing,
            ]);
        }

        $orders = Order::where('customer_id', $customer->user_id)
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

        $customer = $user->customer;
        $alreadyReviewed = Review::query()
            ->where('customer_id', $customer?->user_id)
            ->where('product_id', $product->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($orders->isEmpty() || ($orderId && !$selectedOrder)) {
            return view('pages.review', [
                'product' => $product,
                'orders' => $orders,
                'canReview' => false,
                'alreadyReviewed' => $alreadyReviewed,
                'productPricing' => $productPricing,
                'variantPricing' => $variantPricing,
            ]);
        }

        return view('pages.review', [
            'product' => $product,
            'orders' => $orders,
            'selectedOrder' => $selectedOrder,
            'canReview' => !$alreadyReviewed,
            'alreadyReviewed' => $alreadyReviewed,
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
            'is_anonymous' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $customer = $user->customer ?? null;
        
        if (!$customer) {
            return redirect()->back()->with('error', 'Lỗi: không tìm thấy thông tin khách hàng');
        }

        // Nếu không truyền `order_id`, hệ thống tự tìm đơn hợp lệ mới nhất của khách cho sản phẩm này.
        $orderQuery = Order::where('customer_id', $customer->user_id)
            ->where('status', 'completed')
            ->whereHas('payment', fn($q) => $q->where('status', 'paid'))
            ->whereHas('items', fn($q) => $q->whereHas('variant', fn($q2) => $q2->where('product_id', $request->input('product_id'))));

        if ($request->filled('order_id')) {
            $orderQuery->where('id', $request->order_id);
        }

        $order = $orderQuery->latest()->firstOrFail();

        // ✅ FIX: Verify order belongs to current user
        if ($order->customer_id !== $customer->user_id) {
            abort(403, 'Unauthorized: This order does not belong to you');
        }

        $order->loadMissing('customer');

        $item = $order->items()->whereHas('variant', fn($q) => $q->where('product_id', $request->input('product_id')))->firstOrFail();

        // Luôn lấy thông tin khách hàng từ đơn để đảm bảo đồng nhất dữ liệu.
        $customer = $order->customer;
        if (!$customer) {
            $customer = Auth::user()->customer;
            if (!$customer) {
                return redirect()->back()->with('error', 'Lỗi: không tìm thấy thông tin khách hàng');
            }
        }

        $alreadyReviewed = Review::query()
            ->where('customer_id', $customer->user_id)
            ->where('product_id', $request->input('product_id'))
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($alreadyReviewed) {
            return redirect()->back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        // Tạo review
        $review = $item->variant->product->reviews()->create([
            'customer_id' => $customer->user_id,
            'order_id' => $order->id,
            'rating' => $request->input('rating'),
            'content' => $request->input('content'),
            'is_anonymous' => $request->boolean('is_anonymous', false),
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

    /**
     * Hiển thị form đánh giá nhiều sản phẩm trong một đơn hàng.
     */
    public function batchForm(Order $order)
    {
        $user = Auth::user();
        $customer = $user->customer ?? null;

        if (!$customer || $order->customer_id !== $customer->user_id) {
            return redirect()->route('orders.my')->with('error', 'Không có quyền truy cập đơn hàng này.');
        }

        if ($order->status !== 'completed') {
            return redirect()->route('orders.my')->with('error', 'Chỉ có thể đánh giá đơn hàng đã hoàn thành.');
        }

        $items = $order->items()->with('variant.product', 'variant.images')->get();

        $reviewedProductIds = Review::where('customer_id', $customer->user_id)
            ->whereIn('status', ['pending', 'approved'])
            ->whereIn('product_id', $items->pluck('variant.product_id')->unique())
            ->pluck('product_id')
            ->toArray();

        $productsData = [];
        foreach ($items as $item) {
            $product = $item->variant->product;
            $productsData[$product->id] = [
                'product' => $product,
                'variant' => $item->variant,
                'images' => $item->variant->images,
                'pricing' => $this->productPricingService->pricingForProduct($product, (float) $item->variant->price),
                'alreadyReviewed' => in_array($product->id, $reviewedProductIds),
            ];
        }

        return view('pages.batch-review', [
            'order' => $order,
            'productsData' => $productsData,
            'reviewedProductIds' => $reviewedProductIds,
        ]);
    }

    /**
     * Lưu các đánh giá theo đơn hàng.
     */
    public function batchStore(Request $request)
    {
        $user = Auth::user();
        $customer = $user->customer ?? null;

        $order = Order::findOrFail($request->input('order_id'));
        if ($order->customer_id !== $customer->user_id) {
            return redirect()->back()->with('error', 'Không có quyền truy cập đơn hàng này.');
        }

        $reviews = $request->input('reviews', []);

        if (empty($reviews)) {
            return redirect()->back()->with('error', 'Vui lòng viết đánh giá cho ít nhất một sản phẩm.');
        }

        $successes = [];
        $errors = [];

        foreach ($reviews as $productId => $reviewData) {
            $isSelected = filter_var($reviewData['selected'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if (!$isSelected) {
                continue;
            }

            if (empty($reviewData['rating']) || empty($reviewData['content'])) {
                continue;
            }

            $productId = (int) $productId;
            $alreadyReviewed = Review::query()
                ->where('customer_id', $customer->user_id)
                ->where('product_id', $productId)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($alreadyReviewed) {
                $errors[] = "Sản phẩm #$productId: Bạn đã đánh giá sản phẩm này rồi.";
                continue;
            }

            // Kiểm tra sản phẩm có trong order không
            $item = $order->items()->whereHas('variant', fn($q) => $q->where('product_id', $productId))->first();
            if (!$item) {
                $errors[] = "Sản phẩm #$productId: Không tìm thấy sản phẩm này trong đơn hàng.";
                continue;
            }

            // Tạo review
            try {
                $review = Review::create([
                    'product_id' => $productId,
                    'customer_id' => $customer->user_id,
                    'order_id' => $order->id,
                    'rating' => $reviewData['rating'],
                    'content' => $reviewData['content'],
                    'is_anonymous' => $reviewData['is_anonymous'] ?? false,
                    'status' => 'pending'
                ]);

                $successes[] = $reviewData['content'] ? "Đánh giá cho sản phẩm đã được gửi." : null;

                // Thông báo cho admin
                $reviewRecipients = Notification::recipientIdsForGroups(['admin', 'order_staff']);
                Notification::createForRecipients($reviewRecipients, [
                    'type' => 'new_review',
                    'title' => 'Có đánh giá mới',
                    'content' => 'Sản phẩm #' . $productId . ' có đánh giá mới cần xử lý.',
                    'related_id' => $review->id,
                ]);
            } catch (\Exception $e) {
                $errors[] = "Sản phẩm #$productId: " . $e->getMessage();
            }
        }

        $successCount = count(array_filter($successes));
        $message = $successCount > 0
            ? "Đã gửi " . $successCount . " đánh giá, chờ duyệt."
            : "Vui lòng chọn ít nhất một sản phẩm và nhập đầy đủ số sao, nhận xét để gửi đánh giá.";

        if (!empty($errors)) {
            $message .= "\n" . implode("\n", array_filter($errors));
        }

        if ($successCount > 0) {
            return redirect()->route('orders.my')->with('success', $message);
        }

        return redirect()->back()->with('error', $message);
    }
}
