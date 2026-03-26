@extends('layout')
@section('hero')
    @include('pages.components.hero', ['showBanner' => true])
@endsection
@section('content')

    <style>
        .voucher-card-system {
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f7fcf5 100%);
            border: 1px solid #d9e7d1;
            border-left: 6px solid #66a84f;
            border-radius: 14px;
            box-shadow: 0 12px 24px rgba(22, 58, 24, 0.12);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            min-height: 210px;
        }

        .voucher-card-system:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 30px rgba(22, 58, 24, 0.18);
        }

        .voucher-card-system::after {
            content: "";
            position: absolute;
            top: -30px;
            right: -30px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(102, 168, 79, 0.12);
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: rgba(102, 168, 79, 0.08);
            border-bottom: 1px dashed #d9e7d1;
            position: relative;
            z-index: 1;
        }

        .voucher-icon i {
            font-size: 1.5rem;
            color: #2d7a3f;
        }

        .voucher-code {
            font-weight: 700;
            font-size: 1.05rem;
            color: #ffffff;
            background: linear-gradient(135deg, #2d7a3f 0%, #3f944e 100%);
            padding: 6px 12px;
            border-radius: 999px;
            letter-spacing: 0.5px;
            box-shadow: 0 6px 12px rgba(45, 122, 63, 0.25);
        }

        .voucher-status {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .voucher-card-system.active .voucher-status {
            background: #e8f6eb;
            color: #2d7a3f;
        }

        .voucher-card-system.expired .voucher-status {
            background: #fdecef;
            color: #bb2d3b;
        }

        .voucher-card-system.used .voucher-status {
            background: #eceff3;
            color: #4f5d70;
        }

        .voucher-card-system.expired {
            border-left-color: #d9534f;
            background: linear-gradient(135deg, #ffffff 0%, #fff6f6 100%);
        }

        .voucher-card-system.used {
            border-left-color: #8c9aa7;
            background: linear-gradient(135deg, #ffffff 0%, #f6f8fa 100%);
        }

        .voucher-body {
            padding: 15px 20px;
            position: relative;
            z-index: 1;
        }

        .voucher-value,
        .voucher-date {
            margin-bottom: 6px;
            font-size: 0.95rem;
            color: #415048;
        }

        .voucher-footer {
            padding: 15px 20px;
            position: relative;
            z-index: 1;
        }
    </style>

        <!-- Categories Section Begin -->
        <section class="categories">
            <div class="container">
                <div class="row">
                    <div class="categories__slider owl-carousel">

                        @foreach ($categories as $category)
                                            <div class="categories__item set-bg" data-setbg="{{ $category->image_url
                            ? asset('storage/' . $category->image_url)
                            : asset('frontend/images/categories/cat-1.jpg') }}">

                                                <h5>
                                                    <a href="{{ route('categories.show', $category->id) }}">
                                                        {{ $category->name }}
                                                    </a>
                                                </h5>

                                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </section>
        <!-- Categories Section End -->


    <!-- Vouchers Section Begin -->
    <section class="vouchers spad">
        <div class="container">
            <h2 class="section-title mb-4">Mã giảm giá</h2>

            <div class="voucher-carousel owl-carousel owl-theme">
                @forelse ($discounts as $discount)
                    @php
    $now = now();
    $status = 'Đang áp dụng';
    $statusClass = 'active';
    $isSaved = in_array($discount->code, $savedDiscountCodes ?? []);
    if ($discount->start_at && $now->lt($discount->start_at)) {
        $status = 'Chưa bắt đầu';
        $statusClass = 'used';
    } elseif ($discount->end_at && $now->gt($discount->end_at)) {
        $status = 'Hết hạn';
        $statusClass = 'expired';
    }
                    @endphp

                    <div class="item">
                        <div class="voucher-card voucher-card-system {{ $statusClass }}">
                            <div class="voucher-header">
                                <div class="voucher-icon">
                                    <i class="fa fa-ticket-alt"></i>
                                </div>
                                <div class="voucher-code">{{ $discount->code }}</div>
                                <div class="voucher-status">{{ $status }}</div>
                            </div>

                            <div class="voucher-body">
                                <p class="voucher-value">
                                    <strong>Giá trị:</strong>
                                    @if($discount->type == 'percent')
                                        {{ $discount->value }} %
                                    @else
                                        {{ number_format($discount->value, 0, ',', '.') }} đ
                                    @endif
                                </p>

                                <p class="voucher-date">
                                    <strong>Hạn:</strong> {{ $discount->end_at?->format('d/m/Y') ?? 'Không giới hạn' }}
                                </p>

                                <p class="voucher-date mb-0">
                                    <strong>Phạm vi:</strong> Toàn shop
                                </p>
                            </div>

                            <div class="voucher-footer">
                                @if($isSaved)
                                    <button class="btn btn-sm btn-success w-100" disabled>
                                        Đã lưu
                                    </button>
                                @else
                                    <form action="{{ route('cart.save_discount') }}" method="POST" class="mb-0">
                                        @csrf
                                        <input type="hidden" name="code" value="{{ $discount->code }}">
                                        <button type="submit" class="btn btn-sm btn-outline-success w-100">
                                            Lưu
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                @empty
                    <div class="text-muted">Chưa có mã giảm giá nào</div>
                @endforelse
            </div>
        </div>
    </section>
    <!-- Vouchers Section End -->

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Init Owl Carousel
            $('.voucher-carousel').owlCarousel({
                loop: false,
                margin: 10,
                nav: true,
                dots: false,
                responsive: {
                    0: { items: 1 },
                    576: { items: 2 },
                    768: { items: 3 },
                    992: { items: 4 }
                }
            });
        });
    </script>

                <!-- Featured Section Begin -->
                <section class="featured spad">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="section-title">
                                    <h2>Sản phẩm nổi bật</h2>
                                </div>
                                <div class="featured__controls">
                                    <ul>
                                        <li class="active" data-filter="*">Tất cả</li>

                                        @foreach ($categories as $category)
                                            <li data-filter=".cat-{{ $category->id }}">
                                                {{ $category->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="row featured__filter">
                                @foreach ($products as $product)
                                                @php
    $variant = $product->variants->first();
    $inWishlist = auth()->check()
        && \App\Models\Wishlist::where('user_id', auth()->id())
            ->where('product_id', $product->id)->exists();
                                                @endphp

                                                {{-- Hidden forms for wishlist & cart --}}
                                                <form id="wishlist-home-{{ $product->id }}"
                                                    action="{{ route('wishlist.toggle', $product->id) }}"
                                                    method="POST" class="d-none">
                                                    @csrf
                                                </form>
                                                @if($variant)
                                                <form id="cart-home-{{ $product->id }}"
                                                    action="{{ route('cart.add') }}"
                                                    method="POST" class="d-none">
                                                    @csrf
                                                    <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                </form>
                                                @endif

                                                <div class="col-lg-3 col-md-4 col-sm-6 mix cat-{{ $product->category_id }}">
                                                    <div class="featured__item">

                                                        <div class="featured__item__pic set-bg" data-setbg="{{ $product->image
        ? asset('storage/' . $product->image)
        : asset('images/no-image.png') }}">
                                                            <ul class="featured__item__pic__hover">
                                                                <li>
                                                                    <a href="javascript:void(0)"
                                                                        onclick="homeWishlist({{ $product->id }});"
                                                                        style="{{ $inWishlist ? 'color:#e74c3c;' : '' }}"
                                                                        title="{{ $inWishlist ? 'Bỏ yêu thích' : 'Thêm yêu thích' }}">
                                                                        <i class="fa fa-heart"></i>
                                                                    </a>
                                                                </li>
                                                                <li><a href="{{ route('products.show', $product->id) }}"><i class="fa fa-retweet"></i></a></li>
                                                                <li>
                                                                    <a href="{{ route('products.show', $product->id) }}"
                                                                        title="Xem chi tiết sản phẩm">
                                                                        <i class="fa fa-shopping-cart"></i>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </div>

                                                        <div class="featured__item__text">
                                                            <h6>
                                                                <a href="{{ route('products.show', $product->id) }}">
                                                                    {{ $product->name }}
                                                                </a>
                                                            </h6>
                                                            @if($product->display_has_discount)
                                                                <h5 class="mb-0 text-danger">{{ number_format($product->display_final_price) }} đ</h5>
                                                                <small class="text-muted"><del>{{ number_format($product->display_base_price) }} đ</del> {{ $product->display_discount_label }}</small>
                                                            @else
                                                                <h5>{{ number_format($variant?->price ?? 0) }} đ</h5>
                                                            @endif
                                                        </div>

                                                    </div>
                                                </div>
                                @endforeach

                                </div>

                            </div>
                        </div>

                    </div>
                </section>
                <!-- Featured Section End -->

                <!-- Banner Begin -->
                <div class="banner">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="banner__item">
                                    <img src="{{ asset('frontend/images/banner/banner-1.png') }}" alt="">
                                    <div class="banner__text">
                                        <h3>Sản phẩm OCOP Đồng Tháp</h3>
                                        <p>Cửa hàng Sen Hồng OCOP</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="banner__item">
                                    <img src="{{ asset('frontend/images/banner/banner-2.png') }}" alt="">
                                    <div class="banner__text">
                                        <h3>Đặc sản địa phương</h3>
                                        <p>Chất lượng – Uy tín – An toàn</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Banner End -->

                <!-- Latest Product Section Begin -->
                <section class="latest-product spad">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-4 col-md-6">
                                <div class="latest-product__text">
                                    <h4>Sản phẩm mới</h4>
                                    <div class="latest-product__slider owl-carousel">
                                        @foreach ($latestProducts->chunk(3) as $chunk)
                                            <div class="latest-prdouct__slider__item">

                                                @foreach ($chunk as $product)
                                                                        @php
        $variant = $product->variants->first();
                                                                        @endphp

                                                                        <a href="{{ route('products.show', $product->id) }}" class="latest-product__item">

                                                                            <div class="latest-product__item__pic">
                                                                                <img src="{{ $product->image
            ? asset('storage/' . $product->image)
            : asset('images/no-image.png') }}" width="60" height="60" class="rounded"
                                                                                    style="object-fit: cover" alt="{{ $product->name }}">
                                                                            </div>

                                                                            <div class="latest-product__item__text">
                                                                                <h6>{{ $product->name }}</h6>
                                                                                @if($product->display_has_discount)
                                                                                    <span class="text-danger">{{ number_format($product->display_final_price) }} đ</span>
                                                                                    <small class="text-muted d-block"><del>{{ number_format($product->display_base_price) }} đ</del> {{ $product->display_discount_label }}</small>
                                                                                @else
                                                                                    <span>{{ number_format($variant?->price ?? 0) }} đ</span>
                                                                                @endif
                                                                            </div>

                                                                        </a>
                                                @endforeach

                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <div class="latest-product__text">
                                    <h4>Sản phẩm bán chạy</h4>
                                    <div class="latest-product__slider owl-carousel">
                                        @forelse ($bestSellingProducts->chunk(3) as $chunk)
                                            <div class="latest-prdouct__slider__item">
                                                @foreach ($chunk as $bsProduct)
                                                    @php $bsVariant = $bsProduct->variants->first(); @endphp
                                                    <a href="{{ route('products.show', $bsProduct->id) }}" class="latest-product__item">
                                                        <div class="latest-product__item__pic">
                                                            <img src="{{ $bsProduct->image ? asset('storage/' . $bsProduct->image) : asset('images/no-image.png') }}"
                                                                width="60" height="60" class="rounded" style="object-fit:cover" alt="{{ $bsProduct->name }}">
                                                        </div>
                                                        <div class="latest-product__item__text">
                                                            <h6>{{ $bsProduct->name }}</h6>
                                                            @if($bsProduct->display_has_discount)
                                                                <span class="text-danger">{{ number_format($bsProduct->display_final_price) }} đ</span>
                                                                <small class="text-muted d-block"><del>{{ number_format($bsProduct->display_base_price) }} đ</del> {{ $bsProduct->display_discount_label }}</small>
                                                            @else
                                                                <span>{{ number_format($bsVariant?->price ?? 0) }} đ</span>
                                                            @endif
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @empty
                                            <div class="latest-prdouct__slider__item">
                                                <p class="text-muted px-2 py-3">Chưa có dữ liệu</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="latest-product__text">
                                    <h4>Đánh giá cao</h4>
                                    <div class="latest-product__slider owl-carousel">
                                        @forelse ($topRatedProducts->chunk(3) as $chunk)
                                            <div class="latest-prdouct__slider__item">
                                                @foreach ($chunk as $trProduct)
                                                    @php $trVariant = $trProduct->variants->first(); @endphp
                                                    <a href="{{ route('products.show', $trProduct->id) }}" class="latest-product__item">
                                                        <div class="latest-product__item__pic">
                                                            <img src="{{ $trProduct->image ? asset('storage/' . $trProduct->image) : asset('images/no-image.png') }}"
                                                                width="60" height="60" class="rounded" style="object-fit:cover" alt="{{ $trProduct->name }}">
                                                        </div>
                                                        <div class="latest-product__item__text">
                                                            <h6>{{ $trProduct->name }}</h6>
                                                            @if($trProduct->display_has_discount)
                                                                <span class="text-danger">{{ number_format($trProduct->display_final_price) }} đ</span>
                                                                <small class="text-muted d-block"><del>{{ number_format($trProduct->display_base_price) }} đ</del> {{ $trProduct->display_discount_label }}</small>
                                                            @else
                                                                <span>{{ number_format($trVariant?->price ?? 0) }} đ</span>
                                                            @endif
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @empty
                                            <div class="latest-prdouct__slider__item">
                                                <p class="text-muted px-2 py-3">Chưa có dữ liệu</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Latest Product Section End -->

                <section class="featured spad pt-0">
                    <div class="container">
                        <div class="section-title">
                            <h2>Bán chạy và đánh giá cao</h2>
                        </div>
                        <div class="row">
                            @forelse($bestSellerTopRatedProducts as $item)
                                @php $variant = $item->variants->first(); @endphp
                                <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
                                    <div class="latest-product__item border rounded p-2 h-100">
                                        <a href="{{ route('products.show', $item->id) }}" class="latest-product__item__pic">
                                            <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('images/no-image.png') }}"
                                                width="75" height="75" class="rounded" style="object-fit:cover" alt="{{ $item->name }}">
                                        </a>
                                        <div class="latest-product__item__text">
                                            <h6><a href="{{ route('products.show', $item->id) }}">{{ $item->name }}</a></h6>
                                            @if($item->display_has_discount)
                                                <span class="text-danger">{{ number_format($item->display_final_price) }} đ</span>
                                            @else
                                                <span>{{ number_format($variant?->price ?? 0) }} đ</span>
                                            @endif
                                            <small class="d-block text-muted">Đã bán: {{ number_format($item->total_sold ?? 0) }}</small>
                                            <small class="d-block text-warning">Đánh giá: {{ number_format($item->avg_rating ?? 0, 1) }}/5</small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-muted text-center">Chưa có dữ liệu sản phẩm nổi bật</div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <!-- Blog Section Begin -->
                <section class="from-blog spad">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="section-title from-blog__title">
                                    <h2>Tin tức</h2>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @forelse ($blogs as $blog)
                                <div class="col-lg-4 col-md-4 col-sm-6">
                                    <div class="blog__item">
                                        <div class="blog__item__pic">
                                            <img src="{{ $blog->image ? asset('storage/' . $blog->image) : asset('frontend/images/blog/blog-1.jpg') }}"
                                                alt="{{ $blog->title }}">
                                        </div>
                                        <div class="blog__item__text">
                                            <ul>
                                                <li>
                                                    <i class="fa fa-calendar-o"></i>
                                                    {{ \Carbon\Carbon::parse($blog->created_at)->format('d/m/Y') }}
                                                </li>
                                                <li>
                                                    <i class="fa fa-comment-o"></i> 0
                                                </li>
                                            </ul>
                                            <h5>
                                                <a href="{{ route('blogs.show', $blog->slug) }}">
                                                    {{ $blog->title }}
                                                </a>
                                            </h5>
                                            <p>{{ \Illuminate\Support\Str::limit($blog->summary, 100) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted">
                                    Chưa có bài viết nào.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
                <!-- Blog Section End -->

                <script>
                    // ---- Sản phẩm nổi bật: wishlist & cart ----
                    function homeWishlist(productId) {
                        @auth
                            var f = document.getElementById('wishlist-home-' + productId);
                            if (f) f.submit();
                        @else
                            Swal.fire({
                                title: 'Chưa đăng nhập',
                                text: 'Vui lòng đăng nhập để thêm vào yêu thích',
                                icon: 'warning',
                                confirmButtonText: 'Đăng nhập',
                                showCancelButton: true,
                                cancelButtonText: 'Hủy'
                            }).then(result => {
                                if (result.isConfirmed) window.location.href = '{{ route("login") }}';
                            });
                        @endauth
                    }

                    function homeAddCart(productId, hasVariant) {
                        if (!hasVariant) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Chưa thể thêm',
                                text: 'Sản phẩm chưa có phiên bản, vui lòng xem trang chi tiết.',
                                confirmButtonText: 'Xem chi tiết'
                            }).then(result => {
                                if (result.isConfirmed) window.location.href = '{{ url("/products") }}/' + productId;
                            });
                            return;
                        }
                        var form = document.getElementById('cart-home-' + productId);
                        if (form) {
                            form.submit();
                        } else {
                            window.location.href = '{{ url("/products") }}/' + productId;
                        }
                    }

                    // ---- Flash messages ----
                    @if(session('success'))
                        Swal.fire({ icon: 'success', title: '{{ session("success") }}', timer: 2500, showConfirmButton: false });
                    @endif
                    @if(session('error'))
                        Swal.fire({ icon: 'error', title: '{{ session("error") }}' });
                    @endif
                </script>

@endsection