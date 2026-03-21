@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

    <!-- Breadcrumb -->
    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container">

            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">

                        <h2>Sản phẩm yêu thích</h2>

                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.trangchu') }}">Trang chủ</a>
                            <span>Yêu thích</span>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>


        <!-- Wishlist -->
        <section class="wishlist spad">
            <div class="container">

                <div class="wishlist-box p-4 bg-white rounded shadow-sm">

                    @if($items->count() > 0)

                        <div class="row g-4">
                            @foreach($items as $item)
                                @php
            $product = $item->product;
            $variant = $product->variants->first();
            $price = optional($variant)->price ?? 0;
            $image = $product->image
                ? asset('storage/' . $product->image)
                : asset('images/no-image.png');
            $ocop = $product->ocop_star ?? 0;
                                @endphp

                                <div class="col-lg-3 col-md-4 col-sm-6">

                                    <div class="product-card shadow-sm rounded position-relative overflow-hidden">

                                        <!-- XÓA -->
                                        <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST"
                                            class="position-absolute top-2 end-2 remove-btn">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-light p-1">
                                                <i class="fa fa-times text-danger"></i>
                                            </button>
                                        </form>

                                        <!-- ẢNH -->
                                        <a href="{{ route('products.show', $product->id) }}">
                                            <div class="product-card-img text-center p-3 bg-light">
                                                <img src="{{ $image }}" alt="{{ $product->name }}" class="img-fluid"
                                                    style="max-height:180px; object-fit:contain;">
                                            </div>
                                        </a>

                                        <!-- NỘI DUNG -->
                                        <div class="product-card-body p-3">

                                            <!-- TÊN SP -->
                                            <h5 class="product-card-title mb-2 text-truncate" title="{{ $product->name }}">
                                                <a href="{{ route('products.show', $product->id) }}"
                                                    class="text-dark fw-bold fs-6">{{ $product->name }}</a>
                                            </h5>

                                            <!-- ⭐ OCOP -->
                                            <div class="d-flex align-items-center mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fa fa-star {{ $i <= $ocop ? 'text-warning' : 'text-secondary' }} me-1"></i>
                                                @endfor
                                                @if($ocop > 0)
                                                    <span class="badge bg-warning text-dark ms-auto">{{ $ocop }} OCOP</span>
                                                @endif
                                            </div>

                                            <!-- GIÁ -->
                                            <h5 class="text-success fw-bold mb-3 fs-5">{{ number_format($price) }}₫</h5>

                                            <!-- MUA NGAY -->
                                            <a href="{{ route('products.show', $product->id) }}"
                                                class="btn btn-buy-now w-100 py-2 fs-6 fw-bold">
                                                <i class="fa fa-bolt me-2"></i> Mua ngay
                                            </a>

                                        </div>

                                    </div>

                                </div>
                            @endforeach
                        </div>

                    @else
                        <div class="text-center p-5">
                            <h4 class="mb-3">Chưa có sản phẩm yêu thích</h4>
                            <a href="{{ route('products.index') }}" class="btn btn-success">
                                Đi mua sắm
                            </a>
                        </div>
                    @endif

                </div>

            </div>
        </section>

@endsection