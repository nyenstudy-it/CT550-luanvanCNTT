@extends('layout')

@php
    /** @var \App\Models\Product $product */
    /** @var \Illuminate\Support\Collection|null $orders */
    /** @var \App\Models\Order|null $selectedOrder */
    /** @var bool|null $canReview */

    $approvedReviews = $product->reviews()->where('status', 'approved')->latest()->get();
    $reviewCount = $approvedReviews->count();

    $resolvedCanReview = (bool) ($canReview ?? false);

    if (!isset($canReview) && auth()->check()) {
        $customer = auth()->user()->customer ?? null;

        if ($customer && $customer->id) {
            $resolvedCanReview = \App\Models\Order::where('customer_id', $customer->user_id)
                ->where('status', 'completed')
                ->whereHas('payment', function ($q) {
                    $q->where('status', 'paid');
                })
                ->whereHas('items', function ($q) use ($product) {
                    $q->whereHas('variant', function ($q2) use ($product) {
                        $q2->where('product_id', $product->id);
                    });
                })
                ->exists();
        }
    }
@endphp

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')
    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>{{ $product->name }}</h2>
                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.home') }}">Trang chủ</a>
                            @if($product->category)
                                <a href="#">{{ $product->category->name }}</a>
                            @endif
                            <span>{{ $product->name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="product-details spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__pic">
                        <div class="product__details__pic__item">
                            <img id="mainImage"
                                src="{{ $product->image ? asset('storage/' . $product->image) : asset('frontend/images/product/product-1.jpg') }}"
                                alt="{{ $product->name }}"
                                style="width:100%; height:420px; object-fit:cover; border-radius:6px; transition: opacity .2s;">
                        </div>

                        <div class="product__details__pic__slider owl-carousel">
                            @foreach($product->variants as $variant)
                                @foreach($variant->images as $img)
                                    <img src="{{ asset('storage/' . $img->image_path) }}"
                                        data-imgbigurl="{{ asset('storage/' . $img->image_path) }}"
                                        data-variant-id="{{ $variant->id }}" class="variant-image" alt="{{ $product->name }}">
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6">
                    <div class="product__details__text">
                        <h3>{{ $product->name }}</h3>

                        <div class="product__details__rating">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fa fa-star{{ $i <= ($product->ocop_star ?? 0) ? '' : '-o' }}"></i>
                            @endfor
                            <span>({{ $reviewCount }} reviews)</span>
                        </div>

                        <div class="product__details__price" id="priceWrap">
                            @if($productPricing['has_discount'])
                                <span id="price" class="text-danger">{{ number_format($productPricing['final_price']) }}
                                    đ</span>
                                <small class="text-muted d-block"><del
                                        id="basePrice">{{ number_format($productPricing['base_price']) }} đ</del> <span
                                        id="discountBadge">{{ $productPricing['discount_label'] }}</span></small>
                            @else
                                <span id="price">{{ number_format($productPricing['base_price']) }} đ</span>
                            @endif
                        </div>

                        <p>{{ $product->description ?? 'Chưa có mô tả.' }}</p>

                        <div class="product__details__option mb-3">
                            <span>Chọn loại:</span>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @forelse($product->variants as $variant)
                                    @php
                                        $variantPriceData = $variantPricing[$variant->id] ?? [
                                            'base_price' => (float) $variant->price,
                                            'final_price' => (float) $variant->price,
                                            'has_discount' => false,
                                            'discount_label' => null,
                                        ];
                                    @endphp
                                    <button type="button" class="variant-btn btn btn-outline-secondary btn-sm"
                                        data-id="{{ $variant->id }}" data-price="{{ $variantPriceData['base_price'] }}"
                                        data-final-price="{{ $variantPriceData['final_price'] }}"
                                        data-has-discount="{{ $variantPriceData['has_discount'] ? 1 : 0 }}"
                                        data-discount-label="{{ $variantPriceData['discount_label'] }}"
                                        data-stock="{{ $variant->inventory?->quantity ?? 0 }}">
                                        {{ $variant->color ?? $variant->volume ?? $variant->weight ?? $variant->size ?? $variant->sku }}
                                    </button>
                                @empty
                                    <span class="text-muted">Sản phẩm chưa có biến thể.</span>
                                @endforelse
                            </div>
                        </div>

                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="variant_id" id="selectedVariant">

                            <div class="product__details__quantity">
                                <div class="quantity">
                                    <div class="pro-qty">
                                        <input type="number" name="quantity" id="quantityInput" value="1" min="1">
                                    </div>
                                </div>
                            </div>

                            <small id="stockText" class="d-block mt-1 text-muted"></small>

                            <button type="submit" class="primary-btn" {{ $product->variants->isEmpty() ? 'disabled' : '' }}>
                                THÊM GIỎ HÀNG
                            </button>
                            @auth
                                <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST"
                                    class="d-inline wishlist-form">
                                    @csrf
                                    <button type="submit" class="heart-icon ms-2">
                                        <span class="icon_heart_alt">
                                            <i class="fa fa-heart {{ $product->is_favorited ? 'text-danger' : '' }}"
                                                style="font-size: 24px;"></i>
                                        </span>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="heart-icon ms-2" title="Vui lòng đăng nhập">
                                    <span class="icon_heart_alt">
                                        <i class="fa fa-heart" style="font-size: 24px;"></i>
                                    </span>
                                </a>
                            @endauth
                        </form>

                        <ul>
                            <li><b>Danh mục</b> <span>{{ $product->category?->name }}</span></li>
                            <li><b>Nhà cung cấp</b> <span>{{ $product->supplier?->name }}</span></li>
                            <li><b>Trọng lượng</b> <span>{{ $product->weight ?? '0.5 kg' }}</span></li>
                            <li>
                                <b>Availability</b>
                                <span>{{ ($product->inventory_total ?? 0) > 0 ? 'In Stock' : 'Hết hàng' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="product__details__tab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Thông tin sản
                                    phẩm</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab">Đánh giá
                                    ({{ $reviewCount }})</a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    <p>{!! nl2br(e($product->description)) !!}</p>
                                </div>
                            </div>

                            <div class="tab-pane" id="tabs-3" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    @forelse($approvedReviews as $review)
                                        <div class="review-item p-3 mb-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                @php
                                                    $reviewerName = $review->is_anonymous
                                                        ? 'Khách hàng ẩn danh'
                                                        : ($review->customer?->user?->name ?? $review->customer?->name ?? 'Khách');
                                                @endphp
                                                <div>
                                                    <strong>{{ $reviewerName }}</strong>
                                                    @if ($review->is_anonymous)
                                                        <span class="badge bg-info ms-2" style="font-size:11px;">Ẩn danh</span>
                                                    @endif
                                                </div>
                                                <div class="text-end">
                                                    <small
                                                        class="text-muted d-block">{{ $review->created_at->format('d/m/Y') }}</small>
                                                    <button class="btn btn-sm btn-link text-decoration-none review-like-btn"
                                                        data-id="{{ $review->id }}">
                                                        <i class="fa fa-heart text-danger"></i>
                                                        <span class="like-count">{{ $review->likes()->count() }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mb-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fa fa-star{{ $i <= $review->rating ? '' : '-o' }}"
                                                        style="color:#ff9900;"></i>
                                                @endfor
                                            </div>
                                            <p class="mb-0">{!! nl2br(e($review->content)) !!}</p>

                                            {{-- Phản hồi của Admin (giống Shopee) --}}
                                            @php $adminReplies = $review->replies()->where('is_admin', true)->orderBy('created_at')->get(); @endphp
                                            @if($adminReplies->isNotEmpty())
                                                <div class="mt-2 ms-3 ps-3 border-start border-primary">
                                                    @foreach($adminReplies as $reply)
                                                        <div class="mb-1">
                                                            <span class="badge bg-primary me-1" style="font-size:11px;">Người bán</span>
                                                            <small class="text-muted">{{ $reply->created_at->format('d/m/Y') }}</small>
                                                            <p class="mb-0 mt-1" style="font-size:14px;">
                                                                {!! nl2br(e($reply->content)) !!}
                                                            </p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                    @endforelse

                                    @auth
                                        @if($alreadyReviewed ?? false)
                                            <div class="alert alert-info mt-4 mb-0">
                                                <i class="fa fa-check-circle me-2"></i>
                                                Bạn đã đánh giá sản phẩm này rồi
                                            </div>
                                        @elseif($resolvedCanReview)
                                            <form action="{{ route('reviews.store') }}" method="POST"
                                                class="review-form mt-4 p-3 bg-light rounded border">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                                <h5 class="mb-3" style="font-weight: 600; font-size: 16px;">
                                                    <i class="fa fa-pen me-2"></i>Viết đánh giá
                                                </h5>

                                                {{-- Order Selection --}}
                                                @if(isset($selectedOrder) && $selectedOrder)
                                                    <input type="hidden" name="order_id" value="{{ $selectedOrder->id }}">
                                                    <small class="text-muted d-block mb-3">
                                                        Đơn hàng: #{{ $selectedOrder->id }} -
                                                        {{ $selectedOrder->created_at->format('d/m/Y') }}
                                                    </small>
                                                @elseif(isset($orders) && $orders->count())
                                                    <div class="mb-3">
                                                        <label class="form-label mb-1" style="font-size: 14px;"><strong>Chọn đơn hàng
                                                                <span class="text-danger">*</span></strong></label>
                                                        <select name="order_id" class="form-select" style="font-size: 14px;" required>
                                                            <option value="">-- Chọn --</option>
                                                            @foreach($orders as $o)
                                                                <option value="{{ $o->id }}">#{{ $o->id }} -
                                                                    {{ $o->created_at->format('d/m/Y') }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif

                                                {{-- Star Rating --}}
                                                <div class="mb-3">
                                                    <label class="form-label mb-1" style="font-size: 14px;"><strong>Đánh giá <span
                                                                class="text-danger">*</span></strong></label>
                                                    <div class="rating-input">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <input type="radio" name="rating" value="{{ $i }}" id="star-{{ $i }}"
                                                                class="rating-radio d-none">
                                                            <label for="star-{{ $i }}" class="rating-label me-2"
                                                                style="cursor: pointer; font-size: 24px;">
                                                                <i class="fa fa-star-o"></i>
                                                            </label>
                                                        @endfor
                                                    </div>
                                                </div>

                                                {{-- Review Content --}}
                                                <div class="mb-3">
                                                    <label class="form-label mb-1" style="font-size: 14px;"><strong>Nhận xét <span
                                                                class="text-danger">*</span></strong></label>
                                                    <textarea name="content" class="form-control review-textarea"
                                                        placeholder="Chia sẻ cảm nhận..." rows="3"
                                                        style="font-size: 14px; resize: vertical;" required></textarea>
                                                    <small class="text-muted d-block mt-1"><span class="char-count">0</span>/500 ký
                                                        tự</small>
                                                </div>

                                                {{-- Anonymous --}}
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="is_anonymous" value="1"
                                                            id="is_anonymous_review" class="form-check-input">
                                                        <label class="form-check-label" for="is_anonymous_review"
                                                            style="font-size: 14px;">
                                                            Đăng ẩn danh
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Submit Button --}}
                                                <button type="submit" class="btn btn-primary btn-sm" style="font-size: 14px;">
                                                    <i class="fa fa-send me-2"></i>Gửi đánh giá
                                                </button>
                                            </form>

                                            <style>
                                                .review-form {
                                                    max-width: 500px;
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

                                                .rating-label i.fa-star {
                                                    opacity: 1;
                                                }

                                                .rating-label i.fa-star-o {
                                                    opacity: 0.35;
                                                }

                                                .rating-label:hover {
                                                    transform: scale(1.15);
                                                }

                                                .review-textarea {
                                                    border: 1px solid #ddd;
                                                }

                                                .review-textarea:focus {
                                                    border-color: #7fad39;
                                                    box-shadow: 0 0 0 0.2rem rgba(127, 173, 57, 0.15);
                                                }
                                            </style>

                                        @else
                                            <div class="alert alert-warning mt-4 mb-0">
                                                <i class="fa fa-info-circle me-2"></i>
                                                Bạn cần mua sản phẩm này để có thể đánh giá
                                            </div>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Define popup function to support additional options
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
            const mainImage = document.getElementById('mainImage');
            const priceWrapEl = document.getElementById('priceWrap');
            const selectedVariantInput = document.getElementById('selectedVariant');
            const quantityInput = document.getElementById('quantityInput');
            const stockText = document.getElementById('stockText');
            const submitBtn = document.querySelector('.primary-btn');

            const variantBtns = document.querySelectorAll('.variant-btn');
            const variantImgs = document.querySelectorAll('.variant-image');
            const $slider = $('.product__details__pic__slider');

            if ($slider.length > 0) {
                $slider.owlCarousel({
                    items: 4,
                    margin: 10,
                    dots: false,
                    nav: true,
                    smartSpeed: 300
                });
            }

            function changeMainImage(src) {
                if (!src || !mainImage) {
                    return;
                }

                mainImage.style.opacity = 0;
                setTimeout(function () {
                    mainImage.src = src;
                    mainImage.style.opacity = 1;
                }, 200);
            }

            function updateStock(stock) {
                stock = parseInt(stock, 10) || 0;

                if (quantityInput) {
                    quantityInput.max = stock;
                }

                if (stock <= 0) {
                    if (stockText) {
                        stockText.innerText = 'Hết hàng';
                        stockText.classList.add('text-danger');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = true;
                    }
                    if (quantityInput) {
                        quantityInput.value = 0;
                    }
                    return;
                }

                if (stockText) {
                    stockText.innerText = 'Còn ' + stock + ' sản phẩm';
                    stockText.classList.remove('text-danger');
                }

                if (submitBtn) {
                    submitBtn.disabled = false;
                }

                if (quantityInput) {
                    if (parseInt(quantityInput.value, 10) > stock) {
                        quantityInput.value = stock;
                    }
                    if (parseInt(quantityInput.value, 10) < 1) {
                        quantityInput.value = 1;
                    }
                }
            }

            variantBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const variantId = this.dataset.id;
                    const price = this.dataset.price;
                    const finalPrice = this.dataset.finalPrice || price;
                    const hasDiscount = this.dataset.hasDiscount === '1';
                    const discountLabel = this.dataset.discountLabel || '';
                    const stock = this.dataset.stock;

                    if (selectedVariantInput) {
                        selectedVariantInput.value = variantId;
                    }

                    if (priceWrapEl) {
                        const baseText = new Intl.NumberFormat('vi-VN').format(price) + ' đ';
                        const finalText = new Intl.NumberFormat('vi-VN').format(finalPrice) + ' đ';

                        if (hasDiscount) {
                            priceWrapEl.innerHTML = '<span id="price" class="text-danger">' + finalText + '</span>' +
                                '<small class="text-muted d-block"><del id="basePrice">' + baseText + '</del> <span id="discountBadge">' + discountLabel + '</span></small>';
                        } else {
                            priceWrapEl.innerHTML = '<span id="price">' + baseText + '</span>';
                        }
                    }

                    variantBtns.forEach(function (b) {
                        b.classList.remove('btn-primary');
                        b.classList.add('btn-outline-secondary');
                    });

                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('btn-primary');

                    updateStock(stock);

                    const firstImg = document.querySelector('.variant-image[data-variant-id="' + variantId + '"]');
                    if (firstImg) {
                        changeMainImage(firstImg.dataset.imgbigurl);

                        if ($slider.length > 0) {
                            const index = Array.from(variantImgs).indexOf(firstImg);
                            if (index >= 0) {
                                $slider.trigger('to.owl.carousel', [index, 300, true]);
                            }
                        }
                    }
                });
            });

            if (quantityInput) {
                quantityInput.addEventListener('input', function () {
                    const max = parseInt(quantityInput.max, 10);
                    let value = parseInt(quantityInput.value, 10);

                    if (Number.isNaN(value)) {
                        value = 1;
                    }
                    if (!Number.isNaN(max) && value > max) {
                        quantityInput.value = max;
                    }
                    if (value < 1) {
                        quantityInput.value = 1;
                    }
                });
            }

            if (variantBtns.length > 0) {
                variantBtns[0].click();
            }

            // Wishlist AJAX handler
            document.querySelectorAll('.wishlist-form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const btn = this.querySelector('button');
                    const icon = btn.querySelector('i');

                    fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.favorited) {
                                icon.classList.add('text-danger');
                            } else {
                                icon.classList.remove('text-danger');
                            }
                        })
                        .catch(err => console.error(err));
                });
            });

            const reviewForm = document.querySelector('.review-form');

            if (reviewForm) {
                const ratingInputDiv = reviewForm.querySelector('.rating-input');

                const contentEl = reviewForm.querySelector('[name="content"]');
                const charCount = reviewForm.querySelector('.char-count');

                if (ratingInputDiv) {
                    const inputs = ratingInputDiv.querySelectorAll('.rating-radio');
                    const labels = ratingInputDiv.querySelectorAll('.rating-label');

                    // Listen for change on inputs
                    inputs.forEach(function (input, index) {
                        input.addEventListener('change', function () {
                            labels.forEach((label, i) => {
                                if (i < index + 1) {
                                    label.innerHTML = '<i class="fa fa-star" style="color: #ff9900; opacity: 1;"></i>';
                                } else {
                                    label.innerHTML = '<i class="fa fa-star-o" style="color: #ff9900; opacity: 0.35;"></i>';
                                }
                            });
                        });
                    });

                    // Hover effect
                    labels.forEach(function (label, index) {
                        label.addEventListener('mouseenter', function () {
                            labels.forEach((l, i) => {
                                if (i <= index) {
                                    l.innerHTML = '<i class="fa fa-star" style="color: #ff9900; opacity: 1;"></i>';
                                } else {
                                    l.innerHTML = '<i class="fa fa-star-o" style="color: #ff9900; opacity: 0.35;"></i>';
                                }
                            });
                        });
                    });

                    ratingInputDiv.addEventListener('mouseleave', function () {
                        const checked = ratingInputDiv.querySelector('.rating-radio:checked');
                        if (checked) {
                            const checkedIndex = Array.from(inputs).indexOf(checked);
                            labels.forEach((label, i) => {
                                if (i < checkedIndex + 1) {
                                    label.innerHTML = '<i class="fa fa-star" style="color: #ff9900; opacity: 1;"></i>';
                                } else {
                                    label.innerHTML = '<i class="fa fa-star-o" style="color: #ff9900; opacity: 0.35;"></i>';
                                }
                            });
                        } else {
                            labels.forEach(l => {
                                l.innerHTML = '<i class="fa fa-star-o" style="color: #ff9900; opacity: 0.35;"></i>';
                            });
                        }
                    });
                }

                // Character counter
                if (charCount && contentEl) {
                    contentEl.addEventListener('input', function () {
                        charCount.textContent = this.value.length;
                    });
                }

                // Form submission validation
                reviewForm.addEventListener('submit', function (e) {

                    const rating = reviewForm.querySelector('input[name="rating"]:checked');
                    const content = contentEl ? contentEl.value.trim() : '';

                    if (!rating || !content) {
                        let errorMsg = '';
                        if (!rating) errorMsg += 'Vui lòng chọn số sao. ';
                        if (!content) errorMsg += 'Vui lòng viết đánh giá.';

                        popup('warning', 'Thiếu thông tin đánh giá', errorMsg);
                        e.preventDefault();
                        return;
                    }
                });
            }

            // Like button AJAX
            document.querySelectorAll('.review-like-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const reviewId = this.getAttribute('data-id');
                    fetch("/reviews/" + reviewId + "/like", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({})
                    })
                        .then(r => r.json())
                        .then(data => {
                            const icon = btn.querySelector('i');
                            const count = btn.querySelector('.like-count');

                            if (data.liked) {
                                icon.classList.remove('fa-heart-o');
                                icon.classList.add('fa-heart');
                            } else {
                                icon.classList.remove('fa-heart');
                                icon.classList.add('fa-heart-o');
                            }

                            if (count) {
                                count.textContent = data.count;
                            }
                        })
                        .catch(err => console.error(err));
                });
            });
        });
    </script>
@endpush