@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')
    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container">

            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">

                        <h2>Sản phẩm</h2>

                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.trangchu') }}">Trang chủ</a>
                            <span>Sản phẩm</span>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    @if(!empty($keyword))
        <section class="product spad" style="padding-top: 20px; padding-bottom: 0;">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <p style="font-size: 16px; color: #555; margin: 0;">
                            Đây là kết quả tìm kiếm cho "<strong>{{ $keyword }}</strong>" - Tìm thấy
                            <strong>{{ $products->total() }}</strong> sản phẩm
                        </p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="product spad">
        <div class="container">

            <div class="row">

                <div class="col-lg-3 col-md-4">

                    <div class="filter-box">

                        <!-- DANH MỤC -->
                        <div class="sidebar__item">
                            <h4 class="price-title">Danh mục</h4>
                            <ul>
                                <li>
                                    <a href="{{ route('products.index') }}">Tất cả</a>
                                </li>
                                @foreach($categories as $c)
                                    <li>
                                        <a href="{{ route('products.index', ['category_id' => $c->id]) }}">
                                            {{ $c->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- GIÁ -->
                        <div class="sidebar__item">
                            <h4 class="price-title">Khoảng giá</h4>

                            <ul class="price-filter">

                                <li>
                                    <a
                                        href="{{ route('products.index', array_merge(request()->query(), ['price_range' => '0-100000'])) }}">
                                        Dưới 100.000₫
                                    </a>
                                </li>

                                <li>
                                    <a
                                        href="{{ route('products.index', array_merge(request()->query(), ['price_range' => '100000-300000'])) }}">
                                        100.000₫ - 300.000₫
                                    </a>
                                </li>

                                <li>
                                    <a
                                        href="{{ route('products.index', array_merge(request()->query(), ['price_range' => '300000-500000'])) }}">
                                        300.000₫ - 500.000₫
                                    </a>
                                </li>

                                <li>
                                    <a
                                        href="{{ route('products.index', array_merge(request()->query(), ['price_range' => '500000-1000000'])) }}">
                                        500.000₫ - 1.000.000₫
                                    </a>
                                </li>

                                <li>
                                    <a
                                        href="{{ route('products.index', array_merge(request()->query(), ['price_range' => '1000000-99999999'])) }}">
                                        Trên 1.000.000₫
                                    </a>
                                </li>

                            </ul>
                        </div>
                        <!-- NHÀ CUNG CẤP -->
                        <div class="sidebar__item">
                            <h4 class="price-title">Nhà cung cấp</h4>
                            <ul>
                                @foreach($suppliers as $s)
                                    <li>
                                        <a href="{{ route('products.index', ['supplier_id' => $s->id]) }}">
                                            {{ $s->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                    </div>

                </div>
                <div class="col-lg-9 col-md-8">
                    <div class="row">

                        @forelse($products as $product)

                            @php
                                $price = optional($product->variants->first())->price ?? 0;
                                $finalPrice = $product->display_final_price ?? $price;
                                $basePrice = $product->display_base_price ?? $price;
                                $hasDiscount = (bool) ($product->display_has_discount ?? false);
                                $image = $product->image
                                    ? asset('storage/' . $product->image)
                                    : asset('frontend/images/product/product-1.jpg');
                            @endphp

                            <div class="col-lg-4 col-md-6 col-sm-6 mb-4">

                                <div class="product__item custom-card">

                                    <!-- ẢNH -->
                                    <div class="product__item__pic">
                                        <a href="{{ route('products.show', $product->id) }}">
                                            <img src="{{ $image }}" alt="{{ $product->name }}"
                                                onerror="this.src='{{ asset('frontend/images/product/product-1.jpg') }}';">
                                        </a>

                                        {{-- Hidden form for wishlist --}}
                                        <form id="wishlist-all-{{ $product->id }}"
                                            action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="d-none">
                                            @csrf
                                        </form>

                                        <a href="javascript:void(0)" onclick="allProductsWishlist({{ $product->id }});"
                                            style="position: absolute; top: 10px; right: 10px; font-size: 20px; {{ $product->is_favorited ? 'color:#e74c3c;' : 'color:#333;' }} z-index: 10; background: rgba(255, 255, 255, 0.9); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;"
                                            title="{{ $product->is_favorited ? 'Bỏ yêu thích' : 'Thêm yêu thích' }}"
                                            onmouseover="this.style.background='rgba(255, 255, 255, 1)'; this.style.transform='scale(1.1)';"
                                            onmouseout="this.style.background='rgba(255, 255, 255, 0.9)'; this.style.transform='scale(1)';">
                                            <i class="fa fa-heart"></i>
                                        </a>

                                    </div>

                                    <!-- TEXT -->
                                    <div class="product__item__text">

                                        @php $ocop = (int) ($product->ocop_star ?? 0); @endphp
                                        <!-- TÊN + SAO OCOP -->
                                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                            <h6 class="product-name mb-0" style="line-height:1.35;">
                                                <a href="{{ route('products.show', $product->id) }}"
                                                    style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;text-overflow:ellipsis;">
                                                    {{ $product->name }}
                                                </a>
                                            </h6>
                                            @if($ocop > 0)
                                                <span
                                                    style="flex-shrink:0;font-size:11px;font-weight:700;color:#92600a;background:#fef9c3;border:1px solid #fde68a;border-radius:999px;padding:2px 8px;white-space:nowrap;display:inline-flex;align-items:center;gap:3px;">
                                                    <i class="fa fa-star" style="font-size:10px;color:#f59e0b;"></i>{{ $ocop }} sao
                                                </span>
                                            @endif
                                        </div>

                                        <!-- ⭐ Đánh giá khách hàng -->
                                        <div class="product__rating" style="margin-bottom:6px;">
                                            @php
                                                $avgRating = round((float) ($product->avg_rating ?? 0));
                                                $reviewCount = (int) ($product->review_count ?? 0);
                                            @endphp

                                            @for($i = 1; $i <= 5; $i++)
                                                <i
                                                    class="fa fa-star {{ $i <= $avgRating ? 'text-warning' : 'text-secondary' }}"></i>
                                            @endfor

                                            <span class="ocop-label">({{ $reviewCount }})</span>
                                        </div>

                                        <!-- GIÁ -->
                                        @if($hasDiscount)
                                            <h5 class="text-danger mb-0">{{ number_format((int) max(0, $finalPrice ?? 0), 0) }}đ
                                            </h5>
                                            <small
                                                class="text-muted"><del>{{ number_format((int) max(0, $basePrice ?? 0), 0) }}đ</del>
                                                {{ $product->display_discount_label }}</small>
                                        @else
                                            <h5>{{ number_format((int) max(0, $price ?? 0), 0) }}đ</h5>
                                        @endif

                                        <!-- 🔥 MUA NGAY -->
                                        <a href="{{ route('products.show', $product->id) }}" class="buy-now-btn">
                                            <i class="fa fa-bolt"></i> Mua ngay
                                        </a>

                                    </div>

                                </div>

                            </div>



                        @empty
                            <div class="col-12 text-center">
                                <h5>Không có sản phẩm nào</h5>
                            </div>
                        @endforelse

                    </div>

                    @if($products->hasPages())
                        <div class="shop__pagination__footer mt-4 pt-3 border-top">
                            {{ $products->appends(request()->query())->links() }}
                        </div>
                    @endif

                </div>

            </div>
        </div>
    </section>

    {{-- AUTO DISMISS ALERT --}}
    <script>
        setTimeout(function () {
            document.querySelectorAll(".auto-dismiss").forEach(function (el) {
                el.remove();
            });
        }, 3000);

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

        // ================= WISHLIST AJAX (POPUP VERSION) =================
        function allProductsWishlist(productId) {
            @auth
                                                                const wishlistForm = document.getElementById('wishlist-all-' + productId);
                if (!wishlistForm) return;

                const formData = new FormData(wishlistForm);
                fetch('{{ route("wishlist.toggle", ":id") }}'.replace(':id', productId), {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Find the product item and update the heart icon color
                            const allWishlistLinks = document.querySelectorAll(`[onclick*="allProductsWishlist(${productId})"]`);
                            allWishlistLinks.forEach(link => {
                                if (data.isAddedToWishlist) {
                                    link.style.color = '#e74c3c';
                                } else {
                                    link.style.color = 'inherit';
                                }
                            });

                            // Show popup using centralized system
                            const message = data.isAddedToWishlist
                                ? 'Thêm vào yêu thích'
                                : 'Xoá khỏi yêu thích';
                            popup('success', 'Thành công', message, {
                                confirmButtonText: 'Đóng'
                            });
                        } else {
                            popup('error', 'Lỗi', data.message || 'Có lỗi xảy ra', {
                                confirmButtonText: 'Đóng'
                            });
                        }
                    })
                    .catch(error => {
                        const errorMsg = error.message || 'Có lỗi xảy ra. Vui lòng thử lại.';
                        popup('error', 'Lỗi', errorMsg, {
                            confirmButtonText: 'Đóng'
                        });
                        console.error("Error:", error);
                    });
            @endauth
            @guest
                popup('warning', 'Bạn chưa đăng nhập', 'Hãy đăng nhập để thêm sản phẩm vào yêu thích.', {
                    confirmButtonText: 'Đăng nhập',
                    cancelButtonText: 'Để sau',
                    showCancelButton: true
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = '{{ route("login") }}';
                    }
                });
            @endguest
                                }
    </script>

@endsection