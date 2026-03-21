@extends('layout')
@section('hero')
    @include('pages.components.hero', ['showBanner' => true])
@endsection
@section('content')

                <!-- Categories Section Begin -->
                <section class="categories">
                    <div class="container">
                        <div class="row">
                            <div class="categories__slider owl-carousel">
                                <div class="col-lg-3">
                                    <div class="categories__item set-bg"
                                        data-setbg="{{ asset('frontend/images/categories/cat-1.jpg') }}">
                                        <h5><a href="#">Sản phẩm chế biến</a></h5>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="categories__item set-bg"
                                        data-setbg="{{ asset('frontend/images/categories/cat-2.jpg') }}">
                                        <h5><a href="#">Gạo đặc sản</a></h5>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="categories__item set-bg"
                                        data-setbg="{{ asset('frontend/images/categories/cat-3.jpg') }}">
                                        <h5><a href="#">Nông sản</a></h5>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="categories__item set-bg"
                                        data-setbg="{{ asset('frontend/images/categories/cat-4.jpg') }}">
                                        <h5><a href="#">Thủ công mỹ nghệ</a></h5>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="categories__item set-bg"
                                        data-setbg="{{ asset('frontend/images/categories/cat-5.jpg') }}">
                                        <h5><a href="#">Mỹ phẩm thiên nhiên</a></h5>
                                    </div>
                                </div>
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
                        if ($discount->start_at && $now->lt($discount->start_at)) {
                            $status = 'Chưa bắt đầu';
                        } elseif ($discount->end_at && $now->gt($discount->end_at)) {
                            $status = 'Hết hạn';
                        }
                    @endphp

                    <div class="item">
                        <div class="voucher-card p-3 rounded shadow-sm d-flex flex-column justify-content-between">
                            <div class="voucher-info mb-2">
                                <h5 class="voucher-code mb-1">{{ $discount->code }}</h5>
                                <p class="mb-1 small">
                                    <strong>Giá trị:</strong>
                                    @if($discount->type == 'percent')
                                        {{ $discount->value }} %
                                    @else
                                        {{ number_format($discount->value, 0, ',', '.') }} đ
                                    @endif
                                </p>
                                <p class="mb-0 small">
                                    <strong>Hạn:</strong> {{ $discount->end_at?->format('d/m/Y') ?? 'Không giới hạn' }}
                                </p>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <button class="btn btn-sm btn-outline-success save-voucher" data-code="{{ $discount->code }}">
                                    Lưu
                                </button>
                                <span
                                    class="badge bg-{{ $status == 'Đang áp dụng' ? 'success' : ($status == 'Chưa bắt đầu' ? 'secondary' : 'danger') }}">
                                    {{ $status }}
                                </span>
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

            // Voucher save button
            const saved = JSON.parse(localStorage.getItem('savedVouchers')) || [];
            document.querySelectorAll('.save-voucher').forEach(btn => {
                const code = btn.dataset.code;
                if (saved.includes(code)) {
                    btn.innerText = 'Đã lưu';
                    btn.disabled = true;
                }
                btn.addEventListener('click', function () {
                    if (!saved.includes(code)) {
                        saved.push(code);
                        localStorage.setItem('savedVouchers', JSON.stringify(saved));
                        this.innerText = 'Đã lưu';
                        this.disabled = true;
                        alert('Đã lưu mã: ' + code);
                    } else {
                        alert('Mã đã lưu: ' + code);
                    }
                });
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
                                                @endphp

                                                <div class="col-lg-3 col-md-4 col-sm-6 mix cat-{{ $product->category_id }}">
                                                    <div class="featured__item">

                                                        <div class="featured__item__pic set-bg" data-setbg="{{ $product->image
            ? asset('storage/' . $product->image)
            : asset('images/no-image.png') }}">
                                                            <ul class="featured__item__pic__hover">
                                                                <li><a href="#"><i class="fa fa-heart"></i></a></li>
                                                                <li><a href="#"><i class="fa fa-retweet"></i></a></li>
                                                                <li>
                                                                    <a href="{{ route('products.show', $product->id) }}">
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
                                                            <h5>
                                                                {{ number_format($variant?->price ?? 0) }} đ
                                                            </h5>
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
                                <div class="banner__pic">
                                    <img src="{{ asset('frontend/images/banner/banner-1.jpg') }}" alt="">
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6">
                                <div class="banner__pic">
                                    <img src="{{ asset('frontend/images/banner/banner-2.jpg') }}" alt="">
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
                                                                                <span>
                                                                                    {{ number_format($variant?->price ?? 0) }} đ
                                                                                </span>
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
                                        <div class="latest-prdouct__slider__item">
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-1.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-2.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-3.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="latest-prdouct__slider__item">
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-1.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-2.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-3.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <div class="latest-product__text">
                                    <h4>Đánh giá cao</h4>
                                    <div class="latest-product__slider owl-carousel">
                                        <div class="latest-prdouct__slider__item">
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-1.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-2.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-3.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="latest-prdouct__slider__item">
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-1.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-2.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                            <a href="#" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="{{ asset('frontend/images/latest-product/lp-3.jpg') }}" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6>Crab Pool Security</h6>
                                                    <span>$30.00</span>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Latest Product Section End -->

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
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="blog__item">
                                    <div class="blog__item__pic">
                                        <img src="{{ asset('frontend/images/blog/blog-1.jpg') }}" alt="">
                                    </div>
                                    <div class="blog__item__text">
                                        <ul>
                                            <li><i class="fa fa-calendar-o"></i> May 4,2019</li>
                                            <li><i class="fa fa-comment-o"></i> 5</li>
                                        </ul>
                                        <h5><a href="#">Cooking tips make cooking simple</a></h5>
                                        <p>Sed quia non numquam modi tempora indunt ut labore et dolore magnam aliquam quaerat </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="blog__item">
                                    <div class="blog__item__pic">
                                        <img src="{{ asset('frontend/images/blog/blog-2.jpg') }}" alt="">
                                    </div>
                                    <div class="blog__item__text">
                                        <ul>
                                            <li><i class="fa fa-calendar-o"></i> May 4,2019</li>
                                            <li><i class="fa fa-comment-o"></i> 5</li>
                                        </ul>
                                        <h5><a href="#">6 ways to prepare breakfast for 30</a></h5>
                                        <p>Sed quia non numquam modi tempora indunt ut labore et dolore magnam aliquam quaerat </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-6">
                                <div class="blog__item">
                                    <div class="blog__item__pic">
                                        <img src="{{ asset('frontend/images/blog/blog-3.jpg') }}" alt="">
                                    </div>
                                    <div class="blog__item__text">
                                        <ul>
                                            <li><i class="fa fa-calendar-o"></i> May 4,2019</li>
                                            <li><i class="fa fa-comment-o"></i> 5</li>
                                        </ul>
                                        <h5><a href="#">Visit the clean farm in the US</a></h5>
                                        <p>Sed quia non numquam modi tempora indunt ut labore et dolore magnam aliquam quaerat </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Blog Section End -->
@endsection