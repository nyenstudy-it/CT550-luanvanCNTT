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
            $resolvedCanReview = \App\Models\Order::where('customer_id', $customer->id)
                ->where('status', 'completed')
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
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(!empty($error))
                <div class="alert alert-warning">
                    {{ $error }}
                </div>
            @endif

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
                            <a href="#" class="heart-icon"><span class="icon_heart_alt"></span></a>
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
                                    <p>{{ $product->description }}</p>
                                </div>
                            </div>

                            <div class="tab-pane" id="tabs-3" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    @forelse($approvedReviews as $review)
                                        <div class="review-item p-3 mb-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong>{{ $review->customer?->user?->name ?? $review->customer?->name ?? 'Khách' }}</strong>
                                                <small class="text-muted">{{ $review->created_at->format('d/m/Y') }}</small>
                                            </div>
                                            <div class="mb-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fa fa-star{{ $i <= $review->rating ? '' : '-o' }}"
                                                        style="color:#ff9900;"></i>
                                                @endfor
                                            </div>
                                            <p class="mb-0">{{ $review->content }}</p>

                                            {{-- Phản hồi của Admin (giống Shopee) --}}
                                            @php $adminReplies = $review->replies()->where('is_admin', true)->orderBy('created_at')->get(); @endphp
                                            @if($adminReplies->isNotEmpty())
                                                <div class="mt-2 ms-3 ps-3 border-start border-primary">
                                                    @foreach($adminReplies as $reply)
                                                        <div class="mb-1">
                                                            <span class="badge bg-primary me-1" style="font-size:11px;">Người bán</span>
                                                            <small class="text-muted">{{ $reply->created_at->format('d/m/Y') }}</small>
                                                            <p class="mb-0 mt-1" style="font-size:14px;">{{ $reply->content }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="alert alert-light border">Chưa có đánh giá nào cho sản phẩm này.</div>
                                    @endforelse

                                    @auth
                                        @if($resolvedCanReview)
                                            <form action="{{ route('reviews.store') }}" method="POST" class="mt-3 review-form">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                                @if(isset($selectedOrder) && $selectedOrder)
                                                    <input type="hidden" name="order_id" value="{{ $selectedOrder->id }}">
                                                    <div class="mb-2 text-muted">
                                                        Đơn hàng: #{{ $selectedOrder->id }} -
                                                        {{ $selectedOrder->created_at->format('d/m/Y') }}
                                                    </div>
                                                @elseif(isset($orders) && $orders->count())
                                                    <div class="mb-2">
                                                        <label>Chọn đơn hàng</label>
                                                        <select name="order_id" class="form-select" required>
                                                            <option value="">Chọn đơn</option>
                                                            @foreach($orders as $o)
                                                                <option value="{{ $o->id }}">#{{ $o->id }} -
                                                                    {{ $o->created_at->format('d/m/Y') }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif

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
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
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

            const reviewForm = document.querySelector('.review-form');
            if (reviewForm) {
                reviewForm.addEventListener('submit', function (e) {
                    const ratingEl = reviewForm.querySelector('[name="rating"]');
                    const contentEl = reviewForm.querySelector('[name="content"]');

                    const rating = ratingEl ? ratingEl.value : '';
                    const content = contentEl ? contentEl.value.trim() : '';

                    if (!rating || !content) {
                        e.preventDefault();
                        alert('Vui lòng chọn số sao và viết đánh giá.');
                    }
                });
            }
        });
    </script>
@endpush