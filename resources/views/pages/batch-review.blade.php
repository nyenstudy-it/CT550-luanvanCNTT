@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')
    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>Đánh giá sản phẩm</h2>
                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.home') }}">Trang chủ</a>
                            <a href="{{ route('orders.my') }}">Đơn hàng của tôi</a>
                            <span>Đánh giá sản phẩm</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="spad">
        <div class="container" style="max-width: 1000px;">
            <div class="row">
                <div class="col-12">
                    <h3 class="mb-3">Đánh giá sản phẩm - Đơn hàng #{{ $order->id }}</h3>
                    <small class="text-muted d-block mb-4">Ngày đặt: {{ $order->created_at->format('d/m/Y H:i') }}</small>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Lỗi!</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('reviews.batch-store') }}" method="POST" id="batch-review-form">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">

                        {{-- Các sản phẩm --}}
                        <div id="products-container">
                            @forelse ($productsData as $productId => $data)
                                @php
                                    $product = $data['product'];
                                    $variant = $data['variant'];
                                    $pricing = $data['pricing'];
                                    $alreadyReviewed = $data['alreadyReviewed'];
                                    $image = $variant->primaryImage?->image_path
                                        ? asset('storage/' . $variant->primaryImage->image_path)
                                        : ($product->image ? asset('storage/' . $product->image) : asset('frontend/images/product/product-1.jpg'));
                                @endphp
                                <div class="review-product-block mb-4 p-3 border rounded bg-light"
                                    data-product-id="{{ $productId }}">
                                    <div class="row g-3">
                                        {{-- Ảnh sản phẩm --}}
                                        <div class="col-md-2">
                                            <img src="{{ $image }}" alt="{{ $product->name }}" class="img-fluid rounded"
                                                style="max-height:120px; object-fit:contain; width:100%;"
                                                onerror="this.src='{{ asset('frontend/images/product/product-1.jpg') }}';">
                                        </div>

                                        {{-- Thông tin sản phẩm --}}
                                        <div class="col-md-10">
                                            <h6 class="mb-1">{{ $product->name }}</h6>

                                            <small class="text-muted d-block mb-2">
                                                Loại: {{ $variant->displayName ?? $variant->name }}
                                            </small>

                                            <div class="mb-3">
                                                @if ($pricing['has_discount'])
                                                    <span class="text-danger fw-bold">
                                                        {{ number_format($pricing['final_price']) }} đ
                                                    </span>
                                                    <small class="text-muted d-block">
                                                        <del>{{ number_format($pricing['base_price']) }} đ</del>
                                                        {{ $pricing['discount_label'] }}
                                                    </small>
                                                @else
                                                    <span class="fw-bold">{{ number_format($pricing['base_price']) }} đ</span>
                                                @endif
                                            </div>

                                            {{-- Form đánh giá --}}
                                            @if (!$alreadyReviewed)
                                                <div class="review-form-group mt-2 p-2 bg-white rounded border">
                                                    {{-- Rating --}}
                                                    <div class="mb-2">
                                                        <label class="form-label mb-1" style="font-size: 14px;">
                                                            <strong>Đánh giá <span class="text-danger">*</span></strong>
                                                        </label>
                                                        <div class="rating-input" data-product-id="{{ $productId }}">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <input type="radio" name="reviews[{{ $productId }}][rating]"
                                                                    value="{{ $i }}" id="rating-{{ $productId }}-{{ $i }}"
                                                                    class="rating-radio d-none">
                                                                <label for="rating-{{ $productId }}-{{ $i }}" class="rating-label me-2"
                                                                    style="cursor: pointer; font-size: 24px;">
                                                                    <i class="fa fa-star-o" style="color: #ff9900;"></i>
                                                                </label>
                                                            @endfor
                                                        </div>
                                                    </div>

                                                    {{-- Review content --}}
                                                    <div class="mb-2">
                                                        <label class="form-label mb-1" style="font-size: 14px;">
                                                            <strong>Nhận xét <span class="text-danger">*</span></strong>
                                                        </label>
                                                        <textarea name="reviews[{{ $productId }}][content]"
                                                            class="form-control review-content" placeholder="Chia sẻ cảm nhận..."
                                                            rows="2" maxlength="1000"></textarea>
                                                        <small class="text-muted d-block mt-1">
                                                            <span class="char-count">0</span>/1000 ký tự
                                                        </small>
                                                    </div>

                                                    {{-- Anonymous option --}}
                                                    <div class="mb-2">
                                                        <div class="form-check">
                                                            <input type="checkbox" name="reviews[{{ $productId }}][is_anonymous]"
                                                                value="1" id="anonymous-{{ $productId }}" class="form-check-input">
                                                            <label class="form-check-label" for="anonymous-{{ $productId }}"
                                                                style="font-size: 14px;">
                                                                Đăng ẩn danh
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input product-review-checkbox"
                                                            name="reviews[{{ $productId }}][selected]" value="1"
                                                            data-product-id="{{ $productId }}">
                                                        <label class="form-check-label" style="font-size: 14px;">
                                                            Đánh giá sản phẩm này
                                                        </label>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="alert alert-info mt-2 mb-0 py-2">
                                                    <i class="fa fa-check-circle me-1"></i>
                                                    <small>Bạn đã đánh giá sản phẩm này rồi</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="alert alert-warning">
                                    Đơn hàng này không có sản phẩm để đánh giá.
                                </div>
                            @endforelse
                        </div>

                        {{-- Thông báo trạng thái --}}
                        <div id="status-message" class="alert d-none mt-3" role="alert"></div>

                        {{-- Nút hành động --}}
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fa fa-send me-2"></i>Gửi
                            </button>
                            <a href="{{ route('orders.my') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <style>
        .review-product-block {
            transition: all 0.3s ease;
        }

        .review-product-block:hover {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .rating-label {
            display: inline-block;
            transition: all 0.2s ease;
            cursor: pointer;
            font-size: 20px;
        }

        .rating-label i {
            color: #ff9900;
        }

        .rating-label i.fa-star-o {
            opacity: 0.3;
        }

        .rating-label i.fa-star {
            opacity: 1;
        }

        .rating-label:hover {
            transform: scale(1.15);
        }
    </style>

    <script>
        function popup(icon, title, text, additionalOptions) {
            if (window.ocopPopup && typeof window.ocopPopup.fire === 'function') {
                return window.ocopPopup.fire(Object.assign({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonColor: '#7fad39'
                }, additionalOptions || {}));
            }

            if (typeof Swal !== 'undefined') {
                return Swal.fire(Object.assign({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonColor: '#7fad39'
                }, additionalOptions || {}));
            }

            return Promise.resolve({ isConfirmed: false, isDismissed: true });
        }

        document.addEventListener('DOMContentLoaded', function () {
            function syncReviewCheckboxState(block) {
                if (!block) return;
                const rating = block.querySelector('.rating-radio:checked');
                const contentInput = block.querySelector('.review-content');
                const checkbox = block.querySelector('.product-review-checkbox');
                if (!contentInput || !checkbox) return;

                const hasContent = contentInput.value.trim().length > 0;
                checkbox.disabled = !(rating && hasContent);

                if (checkbox.disabled) {
                    checkbox.checked = false;
                }
            }

            document.querySelectorAll('.review-product-block').forEach(function (block) {
                const ratingContainer = block.querySelector('.rating-input');
                const contentInput = block.querySelector('.review-content');
                const labels = ratingContainer ? ratingContainer.querySelectorAll('.rating-label') : [];
                const inputs = ratingContainer ? ratingContainer.querySelectorAll('.rating-radio') : [];

                inputs.forEach(function (input, index) {
                    input.addEventListener('change', function () {
                        labels.forEach(function (label, i) {
                            label.innerHTML = i <= index
                                ? '<i class="fa fa-star"></i>'
                                : '<i class="fa fa-star-o"></i>';
                        });
                        syncReviewCheckboxState(block);
                    });
                });

                labels.forEach(function (label, index) {
                    label.addEventListener('mouseenter', function () {
                        labels.forEach(function (node, i) {
                            node.innerHTML = i <= index
                                ? '<i class="fa fa-star"></i>'
                                : '<i class="fa fa-star-o"></i>';
                        });
                    });
                });

                if (ratingContainer) {
                    ratingContainer.addEventListener('mouseleave', function () {
                        const checked = ratingContainer.querySelector('.rating-radio:checked');
                        if (!checked) {
                            labels.forEach(function (label) {
                                label.innerHTML = '<i class="fa fa-star-o"></i>';
                            });
                            return;
                        }

                        const checkedIndex = Array.from(inputs).indexOf(checked);
                        labels.forEach(function (label, i) {
                            label.innerHTML = i <= checkedIndex
                                ? '<i class="fa fa-star"></i>'
                                : '<i class="fa fa-star-o"></i>';
                        });
                    });
                }

                if (contentInput) {
                    contentInput.addEventListener('input', function () {
                        const counter = block.querySelector('.char-count');
                        if (counter) {
                            counter.textContent = String(contentInput.value.length);
                        }
                        syncReviewCheckboxState(block);
                    });
                }

                syncReviewCheckboxState(block);
            });

            const form = document.getElementById('batch-review-form');
            if (!form) return;

            form.addEventListener('submit', function (e) {
                const checkedProducts = document.querySelectorAll('.product-review-checkbox:checked');
                if (checkedProducts.length === 0) {
                    e.preventDefault();
                    popup('warning', 'Thiếu thông tin', 'Vui lòng chọn ít nhất một sản phẩm để đánh giá');
                    return;
                }

                let hasError = false;
                checkedProducts.forEach(function (checkbox) {
                    const block = checkbox.closest('.review-product-block');
                    const rating = block ? block.querySelector('.rating-radio:checked') : null;
                    const content = block ? (block.querySelector('.review-content')?.value || '').trim() : '';

                    if (!rating) {
                        hasError = true;
                        popup('warning', 'Thiếu thông tin', 'Vui lòng chọn số sao cho sản phẩm đã tick.');
                        return;
                    }

                    if (!content) {
                        hasError = true;
                        popup('warning', 'Thiếu thông tin', 'Vui lòng viết nhận xét cho sản phẩm đã tick.');
                    }
                });

                if (hasError) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection