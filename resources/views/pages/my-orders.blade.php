@extends('layout')

@section('content')
    @php
        $currentStatus = request('status');
        $reviewFilter = $reviewFilter ?? request('review');
        $reviewedProductLookup = array_flip($reviewedProductIds ?? []);
        $tabs = [
            ['label' => 'Tất cả', 'params' => [], 'active' => $currentStatus === null && $reviewFilter !== 'unreviewed'],
            ['label' => 'Chưa đánh giá', 'params' => ['review' => 'unreviewed'], 'active' => $reviewFilter === 'unreviewed'],
            ['label' => 'Chờ xử lý', 'params' => ['status' => 'pending'], 'active' => $currentStatus === 'pending' && $reviewFilter !== 'unreviewed'],
            ['label' => 'Đã xác nhận', 'params' => ['status' => 'confirmed'], 'active' => $currentStatus === 'confirmed' && $reviewFilter !== 'unreviewed'],
            ['label' => 'Đang giao', 'params' => ['status' => 'shipping'], 'active' => $currentStatus === 'shipping' && $reviewFilter !== 'unreviewed'],
            ['label' => 'Hoàn thành', 'params' => ['status' => 'completed'], 'active' => $currentStatus === 'completed' && $reviewFilter !== 'unreviewed'],
            ['label' => 'Đã huỷ', 'params' => ['status' => 'cancelled'], 'active' => $currentStatus === 'cancelled' && $reviewFilter !== 'unreviewed'],
            ['label' => 'Chờ hoàn tiền', 'params' => ['status' => 'refund_requested'], 'active' => $currentStatus === 'refund_requested' && $reviewFilter !== 'unreviewed'],
            ['label' => 'Đã hoàn tiền', 'params' => ['status' => 'refunded'], 'active' => $currentStatus === 'refunded' && $reviewFilter !== 'unreviewed'],
        ];
        $statusConfig = [
            'pending' => ['label' => 'Chờ xử lý', 'icon' => 'fa-clock', 'color' => '#f59e0b', 'bg' => '#fffbeb'],
            'confirmed' => ['label' => 'Đã xác nhận', 'icon' => 'fa-check-circle', 'color' => '#0ea5e9', 'bg' => '#f0f9ff'],
            'shipping' => ['label' => 'Đang giao', 'icon' => 'fa-shipping-fast', 'color' => '#6366f1', 'bg' => '#eef2ff'],
            'completed' => ['label' => 'Hoàn thành', 'icon' => 'fa-check-double', 'color' => '#22c55e', 'bg' => '#f0fdf4'],
            'cancelled' => ['label' => 'Đã huỷ', 'icon' => 'fa-times-circle', 'color' => '#ef4444', 'bg' => '#fef2f2'],
            'refund_requested' => ['label' => 'Chờ hoàn tiền', 'icon' => 'fa-undo-alt', 'color' => '#f97316', 'bg' => '#fff7ed'],
            'refunded' => ['label' => 'Đã hoàn tiền', 'icon' => 'fa-wallet', 'color' => '#7fad39', 'bg' => '#f0fdf4'],
        ];
    @endphp

    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>Đơn hàng của tôi</h2>
                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.home') }}">Trang chủ</a>
                            <a href="{{ route('orders.my') }}">Đơn hàng của tôi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5" style="background:#f5f5f5; min-height:60vh;">
        <div class="container" style="max-width:900px;">

            {{-- Filter tabs --}}
            <div class="order-tabs mb-4">
                @foreach($tabs as $tab)
                    <a href="{{ route('orders.my', $tab['params']) }}" class="order-tab {{ $tab['active'] ? 'active' : '' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>

            {{-- Empty state --}}
            @if($orders->count() == 0)
                <div class="text-center py-5">
                    <i class="fa fa-shopping-bag fa-3x mb-3" style="color:#d1d5db;"></i>
                    <p class="text-muted mb-3">Bạn chưa có đơn hàng nào.</p>
                    <a href="{{ route('pages.home') }}" class="btn btn-success px-4">
                        <i class="fa fa-store me-1"></i> Mua sắm ngay
                    </a>
                </div>
            @else

                {{-- Order cards --}}
                @foreach($orders as $order)
                    @php
                        $cfg = $statusConfig[$order->status] ?? ['label' => $order->status, 'icon' => 'fa-circle', 'color' => '#6b7280', 'bg' => '#f9fafb'];
                        $firstItems = $order->items->take(3);
                        $extraCount = $order->items->count() - 3;
                        $productIdsInOrder = $order->items->pluck('variant.product_id')->filter()->unique();
                        $firstUnreviewedItem = null;
                        $isFullyReviewedCompleted = false;

                        if ($order->status === 'completed') {
                            $firstUnreviewedItem = $order->items->first(function ($item) use ($reviewedProductLookup) {
                                $productId = $item->variant?->product_id;
                                return $productId && !isset($reviewedProductLookup[$productId]);
                            });

                            $isFullyReviewedCompleted = $productIdsInOrder->isNotEmpty() && !$firstUnreviewedItem;
                        }
                    @endphp

                    <div class="order-card mb-3">

                        {{-- Card header --}}
                        <div class="order-card-header">
                            <span class="order-id">
                                <i class="fa fa-receipt me-1"></i> Đơn #{{ $order->id }}
                            </span>
                            <span class="order-date text-muted small">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </span>
                            <span class="order-status ms-auto" style="color:{{ $cfg['color'] }}; background:{{ $cfg['bg'] }};">
                                <i class="fa {{ $cfg['icon'] }} me-1"></i> {{ $cfg['label'] }}
                            </span>
                            @if($isFullyReviewedCompleted)
                                <span class="order-review-badge">
                                    <i class="fa fa-star me-1"></i> Đã đánh giá
                                </span>
                            @endif
                        </div>

                        {{-- Product rows --}}
                        <div class="order-items">
                            @foreach($firstItems as $item)
                                @php
                                    $product = $item->variant?->product;
                                    $variantImagePath = $item->variant?->images?->first()?->image_path;
                                    $productImagePath = $product?->images?->first()?->image_path ?? $product?->image;
                                    $imgPath = $variantImagePath ?: $productImagePath;
                                    $imgSrc = $imgPath ? asset('storage/' . $imgPath) : asset('frontend/images/product/product-1.jpg');
                                    $productName = $product?->name ?? 'Sản phẩm';
                                    $variantInfo = collect([
                                        $item->variant?->size ?? null,
                                        $item->variant?->volume ?? null,
                                        $item->variant?->weight ?? null,
                                        $item->variant?->color ?? null,
                                    ])->filter()->first();
                                @endphp
                                <div class="order-item-row">
                                    <img src="{{ $imgSrc }}" class="order-item-img" alt="{{ $productName }}">
                                    <div class="order-item-info flex-grow-1">
                                        <div class="order-item-name">{{ $productName }}</div>
                                        @if($variantInfo)
                                            <div class="order-item-variant text-muted small">Phân loại: {{ $variantInfo }}</div>
                                        @endif
                                        <div class="order-item-qty text-muted small">x{{ $item->quantity }}</div>
                                    </div>
                                    <div class="order-item-price">
                                        {{ number_format($item->price) }}&thinsp;đ
                                    </div>
                                </div>
                            @endforeach

                            @if($extraCount > 0)
                                <div class="ps-3 pb-2 text-muted small">
                                    <i class="fa fa-ellipsis-h me-1"></i> và {{ $extraCount }} sản phẩm khác…
                                </div>
                            @endif
                        </div>

                        {{-- Card footer --}}
                        <div class="order-card-footer">
                            <div class="order-total">
                                <span class="text-muted small">Tổng tiền:</span>
                                <span class="order-total-amount">{{ number_format($order->total_amount) }}&thinsp;đ</span>
                            </div>
                            <div class="order-actions">
                                @if($firstUnreviewedItem)
                                    <a href="{{ route('reviews.form', ['product' => $firstUnreviewedItem->variant->product_id, 'order' => $order->id]) }}"
                                        class="btn-order-action btn-action-solid">
                                        <i class="fa fa-star me-1"></i> Đánh giá sản phẩm
                                    </a>
                                @endif
                                <a href="{{ route('orders.detail', $order->id) }}" class="btn-order-action btn-action-outline">
                                    <i class="fa fa-eye me-1"></i> Xem chi tiết
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach

                {{-- Pagination --}}
                @if($orders->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links('vendor.pagination.custom') }}
                    </div>
                @endif

            @endif
        </div>
    </section>

    <style>
        /* ── Filter tabs ── */
        .order-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            background: #fff;
            border-radius: 10px;
            padding: 12px 14px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .07);
        }

        .order-tab {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            color: #555;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            background: #fff;
            transition: all .18s;
        }

        .order-tab:hover {
            border-color: #7fad39;
            color: #7fad39;
        }

        .order-tab.active {
            background: #7fad39;
            color: #fff;
            border-color: #7fad39;
        }

        /* ── Order card ── */
        .order-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .07);
            overflow: hidden;
        }

        .order-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            flex-wrap: wrap;
        }

        .order-id {
            font-weight: 600;
            font-size: 13px;
            color: #374151;
        }

        .order-status {
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .order-review-badge {
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            color: #047857;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
        }

        /* ── Items ── */
        .order-items {
            padding: 4px 0;
        }

        .order-item-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-bottom: 1px solid #f9fafb;
        }

        .order-item-row:last-of-type {
            border-bottom: none;
        }

        .order-item-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #f3f4f6;
            flex-shrink: 0;
        }

        .order-item-name {
            font-size: 14px;
            font-weight: 500;
            color: #111;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .order-item-price {
            font-size: 14px;
            font-weight: 600;
            color: #ef4444;
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* ── Footer ── */
        .order-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-top: 1px solid #f3f4f6;
            background: #fafafa;
            flex-wrap: wrap;
            gap: 8px;
        }

        .order-total {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .order-total-amount {
            font-size: 18px;
            font-weight: 700;
            color: #ef4444;
        }

        .order-actions {
            display: flex;
            gap: 8px;
        }

        .btn-order-action {
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all .18s;
            display: inline-flex;
            align-items: center;
        }

        .btn-action-outline {
            border: 1px solid #7fad39;
            color: #7fad39;
            background: #fff;
        }

        .btn-action-outline:hover {
            background: #7fad39;
            color: #fff;
        }

        .btn-action-solid {
            border: 1px solid #ef4444;
            color: #fff;
            background: #ef4444;
        }

        .btn-action-solid:hover {
            background: #dc2626;
            border-color: #dc2626;
            color: #fff;
        }

        @media (max-width: 576px) {
            .order-item-img {
                width: 48px;
                height: 48px;
            }

            .order-total-amount {
                font-size: 15px;
            }
        }

        /* ── Pagination ── */
        .order-pagination {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #374151;
            transition: all .18s;
        }

        .page-btn:hover:not(.disabled):not(.active) {
            border-color: #7fad39;
            color: #7fad39;
        }

        .page-btn.active {
            background: #7fad39;
            border-color: #7fad39;
            color: #fff;
        }

        .page-btn.disabled {
            opacity: .45;
            cursor: default;
            pointer-events: none;
        }
    </style>

@endsection