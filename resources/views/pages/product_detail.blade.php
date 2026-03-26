@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

    <style>
        .recently-viewed-card {
            display: block;
            border: 1px solid #e8e8e8;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .recently-viewed-card:hover {
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-3px);
            text-decoration: none;
        }

        .recently-viewed-card__image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .recently-viewed-card__body {
            padding: 10px 12px 12px;
        }

        .recently-viewed-card__name {
            color: #1c1c1c;
            font-weight: 600;
            font-size: 14px;
            line-height: 1.35;
            min-height: 38px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 6px;
        }

        .recently-viewed-card__price {
            color: #dd2222;
            font-size: 15px;
            font-weight: 700;
        }

        .recently-viewed-card__base {
            font-size: 12px;
            color: #8d8d8d;
            display: block;
        }
    </style>

    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>{{ $product->name }}</h2>
                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.home') }}">Trang chủ</a>
                            <a href="#">{{ $product->category?->name }}</a>
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
                {{-- IMAGE --}}
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__pic">
                        <div class="product__details__pic__item">
                            <img id="mainImage"
                                src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.png') }}"
                                alt="{{ $product->name }}"
                                style="width:100%; height:420px; object-fit:cover; border-radius:6px;">
                        </div>
                        <div class="product__details__pic__slider owl-carousel">
                            @foreach ($product->variants as $variant)
                                @foreach ($variant->images as $img)
                                    <img src="{{ asset('storage/' . $img->image_path) }}"
                                        data-imgbigurl="{{ asset('storage/' . $img->image_path) }}"
                                        data-variant-id="{{ $variant->id }}" class="variant-image">
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- INFO --}}
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__text">
                        <h3>{{ $product->name }}</h3>

                        {{-- Rating --}}
                        <div class="product__details__rating">
                            {{-- OCOP stars (official) --}}
                            <div>
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star{{ $i <= ($product->ocop_star ?? 0) ? '' : '-o' }}"></i>
                                @endfor
                                @if($product->ocop_star)
                                    <small class="ms-2">{{ $product->ocop_star }} OCOP</small>
                                @endif
                            </div>

                            {{-- Customer reviews (calculated from approved reviews) --}}
                            <div class="mt-1">
                                @php $avg = $product->avg_rating ?? 0;
                                $count = $product->approvedReviews()->count(); @endphp
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa {{ $i <= round($avg) ? 'fa-star text-warning' : 'fa-star-o text-muted' }}"></i>
                                @endfor
                                <small class="ms-2">{{ number_format($avg, 1) }}/5 ({{ $count }})</small>
                            </div>
                        </div>

                        <div class="product__details__price" id="priceWrap">
                            @if($productPricing['has_discount'])
                                <span id="price" class="text-danger">{{ number_format($productPricing['final_price']) }} đ</span>
                                <small class="text-muted d-block"><del id="basePrice">{{ number_format($productPricing['base_price']) }} đ</del> <span id="discountBadge">{{ $productPricing['discount_label'] }}</span></small>
                            @else
                                <span id="price">{{ number_format($productPricing['base_price']) }} đ</span>
                            @endif
                        </div>

                        <p>{{ $product->description ?? 'Chưa có mô tả.' }}</p>

                        {{-- Variant selection --}}
                        <div class="product__details__option mb-3">
                            <span>Chọn loại:</span>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach ($product->variants as $variant)
                                    @php
                                        $variantPriceData = $variantPricing[$variant->id] ?? [
                                            'base_price' => (float) $variant->price,
                                            'final_price' => (float) $variant->price,
                                            'has_discount' => false,
                                            'discount_label' => null,
                                        ];
                                    @endphp
                                    <button type="button" class="variant-btn btn btn-outline-secondary btn-sm"
                                        data-id="{{ $variant->id }}"
                                        data-price="{{ $variantPriceData['base_price'] }}"
                                        data-final-price="{{ $variantPriceData['final_price'] }}"
                                        data-has-discount="{{ $variantPriceData['has_discount'] ? 1 : 0 }}"
                                        data-discount-label="{{ $variantPriceData['discount_label'] }}"
                                        data-stock="{{ $variant->inventory?->quantity ?? 0 }}">
                                        {{ $variant->color ?? $variant->volume ?? $variant->weight ?? $variant->size ?? $variant->sku }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Quantity & Add to cart & Wishlist — cùng 1 hàng --}}
                        @php
                            $inWishlist = auth()->check()
                                && \App\Models\Wishlist::where('user_id', auth()->id())
                                    ->where('product_id', $product->id)->exists();
                        @endphp

                        <small id="stockText" class="d-block mb-2 text-muted"></small>

                        <form id="addToCartForm" action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="variant_id" id="selectedVariant">

                            <div style="display:flex;align-items:stretch;gap:8px;flex-wrap:wrap;">

                                {{-- Số lượng: nút − input + --}}
                                <div style="display:flex;align-items:center;height:44px;border:2px solid #e0e0e0;border-radius:4px;overflow:hidden;background:#fff;">
                                    <button type="button"
                                        onclick="var q=document.getElementById('quantityInput');if(parseInt(q.value)>1)q.value=parseInt(q.value)-1;"
                                        style="width:36px;height:44px;border:none;background:none;font-size:18px;color:#555;cursor:pointer;">−</button>
                                    <input type="number" name="quantity" id="quantityInput" value="1" min="1"
                                        style="width:40px;height:44px;border:none;border-left:1px solid #e0e0e0;border-right:1px solid #e0e0e0;text-align:center;font-size:15px;outline:none;-moz-appearance:textfield;appearance:textfield;">
                                    <button type="button"
                                        onclick="var q=document.getElementById('quantityInput');q.value=parseInt(q.value)+1;"
                                        style="width:36px;height:44px;border:none;background:none;font-size:18px;color:#555;cursor:pointer;">+</button>
                                </div>

                                {{-- Thêm giỏ hàng --}}
                                <button type="submit"
                                    style="height:44px;padding:0 22px;background:#7fad39;color:#fff;border:2px solid #7fad39;border-radius:4px;font-size:13px;font-weight:700;letter-spacing:.6px;cursor:pointer;white-space:nowrap;">
                                    THÊM GIỎ HÀNG
                                </button>

                                {{-- Tim yêu thích --}}
                                @auth
                                    <button type="button"
                                        onclick="document.getElementById('wishlist-form').submit();"
                                        title="{{ $inWishlist ? 'Bỏ yêu thích' : 'Thêm yêu thích' }}"
                                        style="width:44px;height:44px;border:2px solid {{ $inWishlist ? '#e74c3c' : '#e0e0e0' }};border-radius:4px;background:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                                        <i class="fa {{ $inWishlist ? 'fa-heart' : 'fa-heart-o' }}"
                                            style="font-size:18px;color:{{ $inWishlist ? '#e74c3c' : '#aaa' }};"></i>
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="js-login-required-wishlist" title="Đăng nhập để thêm yêu thích"
                                        style="width:44px;height:44px;border:2px solid #e0e0e0;border-radius:4px;background:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;">
                                        <i class="fa fa-heart-o" style="font-size:18px;color:#aaa;"></i>
                                    </a>
                                @endauth

                            </div>
                        </form>

                        {{-- Form wishlist ẩn --}}
                        @auth
                            <form id="wishlist-form" action="{{ route('wishlist.toggle', $product->id) }}" method="POST" style="display:none;">
                                @csrf
                            </form>
                        @endauth

                        {{-- Extra info --}}
                        <ul>
                            <li><b>Danh mục</b> <span>{{ $product->category?->name }}</span></li>
                            <li><b>Nhà cung cấp</b> <span>{{ $product->supplier?->name }}</span></li>
                            <li><b>Trọng lượng</b> <span>{{ $product->weight ?? '0.5 kg' }}</span></li>
                            <li><b>Tình trạng</b>
                                <span>{{ $product->inventory_total > 0 ? 'Còn hàng' : 'Hết hàng' }}</span>
                            </li>
                            <li><b>Chia sẻ</b>
                                <div class="share">
                                    <a href="#"><i class="fa fa-facebook"></i></a>
                                    <a href="#"><i class="fa fa-twitter"></i></a>
                                    <a href="#"><i class="fa fa-instagram"></i></a>
                                    <a href="#"><i class="fa fa-pinterest"></i></a>
                                </div>
                            </li>
                        </ul>

                    </div>
                </div>

                {{-- TAB: Description / Information / Reviews --}}
                <div class="col-lg-12">
                    <div class="product__details__tab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab">Thông tin sản
                                    phẩm</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-3" role="tab">Đánh giá
                                    ({{ $product->approvedReviews()->count() }})</a>
                            </li>
                        </ul>
                        <div class="tab-content">

                            {{-- Description --}}
                            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    <p>{{ $product->description }}</p>
                                </div>
                            </div>

                            {{-- Reviews --}}
                            <div class="tab-pane" id="tabs-3" role="tabpanel">
                                <div class="product__details__tab__desc">

                                    {{-- Hiển thị review --}}
                                    @forelse($product->approvedReviews()->latest()->get() as $review)
                                        <div class="review-item p-3 mb-3 border rounded" id="review-{{ $review->id }}">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div>
                                                    <strong>{{ $review->customer?->user?->name ?? $review->customer?->name ?? 'Khách' }}</strong>
                                                    <div>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fa fa-star{{ $i <= $review->rating ? '' : '-o' }}" style="color:#ff9900;"></i>
                                                        @endfor
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-muted d-block">{{ $review->created_at->format('d/m/Y H:i') }}</small>
                                                    <button class="btn btn-sm btn-link text-decoration-none review-like-btn" data-id="{{ $review->id }}">
                                                        <i class="fa fa-heart text-danger"></i>
                                                        <span class="like-count">{{ $review->likes()->count() }}</span>
                                                    </button>
                                                </div>
                                            </div>

                                            <p class="mb-1">{{ $review->content }}</p>

                                            {{-- Replies --}}
                                            <div class="review-replies ps-3">
                                                @foreach($review->replies()->where('status','approved')->latest()->get() as $rep)
                                                    <div class="reply-item mb-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>{{ $rep->author_name }}</strong>
                                                                @if($rep->is_admin)
                                                                    <span class="badge bg-primary ms-2">Admin</span>
                                                                @endif
                                                                <div class="text-muted small">{{ $rep->created_at->format('d/m/Y H:i') }}</div>
                                                            </div>
                                                            <div></div>
                                                        </div>
                                                        <p class="mb-0">{{ $rep->content }}</p>
                                                    </div>
                                                @endforeach
                                            </div>

                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-3">
                                            Hiện chưa có đánh giá nào, hãy mua hàng và chia sẻ cảm nhận của bạn!
                                        </div>
                                    @endforelse

                                    {{-- Form review nếu đã đăng nhập và đủ điều kiện --}}
                                    @auth
                                        @php
                                            $customer = auth()->user()->customer ?? null;
                                            $canReview = false;
                                            $canComment = false;
                                            if ($customer) {
                                                $canReview = \App\Models\Order::where('customer_id', $customer->id)
                                                    ->where('status', 'completed')
                                                    ->whereHas('items', function ($q) use ($product) {
                                                        $q->whereHas('variant', function ($q2) use ($product) {
                                                            $q2->where('product_id', $product->id);
                                                        });
                                                    })->exists();

                                                // same eligibility for comments
                                                $canComment = $canReview;
                                            }
                                        @endphp


                                        @if($canReview)
                                            <form action="{{ route('reviews.store') }}" method="POST" class="mt-3">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <div class="mb-2">
                                                    <textarea name="content" class="form-control" placeholder="Viết đánh giá..."
                                                        required></textarea>
                                                </div>
                                                <div class="mb-2">
                                                    <select name="rating" class="form-select" required>
                                                        <option value="">Chọn số sao</option>
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <option value="{{ $i }}">{{ $i }} sao</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                                            </form>
                                        @else
                                            <div class="alert alert-info mt-3">
                                                Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua và nhận hàng.
                                            </div>
                                        @endif

                                            @endauth

                                </div>
                            </div>


                        </div>
                    </div>
                </div>

                @if(isset($recentViewedProducts) && $recentViewedProducts->count())
                    <div class="col-lg-12 mt-4">
                        <div class="section-title mb-3">
                            <h4>Sản phẩm vừa xem</h4>
                        </div>

                        <div class="recently-viewed-carousel owl-carousel">
                            @foreach($recentViewedProducts as $recentProduct)
                                @php
                                    $recentVariant = $recentProduct->variants->first();
                                    $recentImage = optional(optional($recentVariant)->images->first())->image_path ?? $recentProduct->image;
                                    $recentBasePrice = (float) ($recentProduct->display_base_price ?? optional($recentVariant)->price ?? 0);
                                    $recentFinalPrice = (float) ($recentProduct->display_final_price ?? $recentBasePrice);
                                    $recentHasDiscount = (bool) ($recentProduct->display_has_discount ?? false);
                                @endphp

                                <div class="item">
                                    <a href="{{ route('products.show', $recentProduct->id) }}" class="recently-viewed-card">
                                        <img class="recently-viewed-card__image"
                                            src="{{ $recentImage ? asset('storage/' . $recentImage) : asset('images/no-image.png') }}"
                                            alt="{{ $recentProduct->name }}">

                                        <div class="recently-viewed-card__body">
                                            <div class="recently-viewed-card__name">{{ $recentProduct->name }}</div>

                                            @if($recentHasDiscount)
                                                <div class="recently-viewed-card__price">{{ number_format($recentFinalPrice) }} đ</div>
                                                <span class="recently-viewed-card__base"><del>{{ number_format($recentBasePrice) }} đ</del></span>
                                            @else
                                                <div class="recently-viewed-card__price">{{ number_format($recentBasePrice) }} đ</div>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </section>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const sessionSuccess = @json(session('success'));
        const sessionError = @json(session('error'));
        const loginUrl = @json(route('login'));
        const cartUrl = @json(route('cart.list'));

        const mainImage = document.getElementById('mainImage');
        const priceWrapEl = document.getElementById('priceWrap');
        const selectedVariantInput = document.getElementById('selectedVariant');
        const quantityInput = document.getElementById('quantityInput');
        const stockText = document.getElementById('stockText');
        const submitBtn = document.querySelector('.primary-btn');
        const addToCartForm = document.getElementById('addToCartForm');

        const variantBtns = document.querySelectorAll('.variant-btn');
        const variantImgs = document.querySelectorAll('.variant-image');
        const $slider = $('.product__details__pic__slider');

        function popup(icon, title, text) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire({ icon, title, text, confirmButtonColor: '#7fad39' });
            }
            alert(text || title);
            return Promise.resolve();
        }

        if (sessionError) {
            popup('error', 'Thông báo', sessionError);
        }

        if (sessionSuccess) {
            if (typeof Swal !== 'undefined' && sessionSuccess.toLowerCase().includes('giỏ hàng')) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thêm vào giỏ hàng thành công',
                    text: 'Bạn muốn thanh toán ngay hay tiếp tục mua sắm?',
                    showCancelButton: true,
                    confirmButtonText: 'Thanh toán',
                    cancelButtonText: 'Tiếp tục mua sắm',
                    confirmButtonColor: '#7fad39'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = cartUrl;
                    }
                });
            } else {
                popup('success', 'Thành công', sessionSuccess);
            }
        }

        document.querySelectorAll('.js-login-required-wishlist').forEach(function (el) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Bạn chưa đăng nhập',
                        text: 'Hãy đăng nhập để thêm sản phẩm vào yêu thích.',
                        showCancelButton: true,
                        confirmButtonText: 'Đăng nhập',
                        cancelButtonText: 'Để sau',
                        confirmButtonColor: '#7fad39'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = loginUrl;
                        }
                    });
                } else {
                    window.location.href = loginUrl;
                }
            });
        });

        $slider.owlCarousel({
            items: 4,
            margin: 10,
            dots: false,
            nav: true,
            smartSpeed: 300
        });

        const $recentlyViewedCarousel = $('.recently-viewed-carousel');
        if ($recentlyViewedCarousel.length) {
            $recentlyViewedCarousel.owlCarousel({
                loop: false,
                margin: 14,
                dots: false,
                nav: true,
                smartSpeed: 350,
                responsive: {
                    0: { items: 1 },
                    576: { items: 2 },
                    768: { items: 3 },
                    992: { items: 4 },
                    1200: { items: 5 }
                }
            });
        }

        function changeMainImage(src) {
            if (!src) return;
            mainImage.style.opacity = 0;
            setTimeout(() => {
                mainImage.src = src;
                mainImage.style.opacity = 1;
            }, 200);
        }

        // Like button AJAX
        document.querySelectorAll('.review-like-btn').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                const reviewId = this.getAttribute('data-id');
                fetch("/reviews/"+reviewId+"/like", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                }).then(r => r.json()).then(data => {
                    if (!data) return;
                    btn.querySelector('.like-count').textContent = data.count;
                }).catch(()=>{});
            });
        });

        function updateStock(stock) {
            stock = parseInt(stock) || 0;
            quantityInput.max = stock;

            if (stock <= 0) {
                stockText.innerText = "Hết hàng";
                stockText.classList.add("text-danger");
                submitBtn.disabled = true;
                quantityInput.value = 0;
            } else {
                stockText.innerText = "Còn " + stock + " sản phẩm";
                stockText.classList.remove("text-danger");
                submitBtn.disabled = false;

                if (parseInt(quantityInput.value) > stock) {
                    quantityInput.value = stock;
                }

                if (parseInt(quantityInput.value) < 1) {
                    quantityInput.value = 1;
                }
            }
        }

        if (addToCartForm) {
            addToCartForm.addEventListener('submit', function (e) {
                const stock = parseInt(quantityInput.max || '0', 10);
                const selectedVariant = selectedVariantInput.value;

                if (!selectedVariant) {
                    e.preventDefault();
                    popup('warning', 'Chưa chọn phân loại', 'Vui lòng chọn loại sản phẩm trước khi thêm vào giỏ hàng.');
                    return;
                }

                if (Number.isNaN(stock) || stock <= 0) {
                    e.preventDefault();
                    popup('error', 'Sản phẩm hiện hết hàng', 'Sản phẩm này hiện đã hết hàng, vui lòng chọn phân loại khác.');
                    return;
                }

                const qty = parseInt(quantityInput.value || '1', 10);
                if (qty > stock) {
                    e.preventDefault();
                    popup('warning', 'Vượt tồn kho', 'Số lượng bạn chọn vượt quá tồn kho hiện tại.');
                }
            });
        }

        variantBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const variantId = this.dataset.id;
                const price = this.dataset.price;
                const finalPrice = this.dataset.finalPrice || price;
                const hasDiscount = this.dataset.hasDiscount === '1';
                const discountLabel = this.dataset.discountLabel || '';
                const stock = this.dataset.stock;

                selectedVariantInput.value = variantId;

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

                variantBtns.forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-primary');

                updateStock(stock);

                const firstImg = document.querySelector(
                    `.variant-image[data-variant-id="${variantId}"]`
                );
                if (firstImg) {
                    changeMainImage(firstImg.dataset.imgbigurl);
                    const index = Array.from(variantImgs).indexOf(firstImg);
                    $slider.trigger('to.owl.carousel', [index, 300, true]);
                }
            });
        });

        quantityInput.addEventListener('input', function () {
            const max = parseInt(quantityInput.max);
            let value = parseInt(quantityInput.value);

            if (value > max) quantityInput.value = max;
            if (value < 1) quantityInput.value = 1;
        });

        const reviewForm = document.querySelector('.review-form');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function (e) {
                const rating = reviewForm.querySelector('[name="rating"]').value;
                const content = reviewForm.querySelector('[name="content"]').value.trim();
                if (!rating || !content) {
                    e.preventDefault();
                    alert('Vui lòng chọn số sao và viết đánh giá!');
                }
            });
        }

    });
</script>