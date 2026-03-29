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
                        <h2>{{ $category->name }}</h2>
                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.trangchu') }}">Trang chu</a>
                            <a href="{{ route('products.index') }}">San pham</a>
                            <span>{{ $category->name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="product spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-4">
                    <div class="sidebar__item">
                        <h4>Nha cung cap</h4>
                        <ul>
                            @foreach($suppliers as $supplier)
                                <li>
                                    <a
                                        href="{{ route('categories.show', ['id' => $category->id, 'supplier_id' => $supplier->id]) }}">
                                        {{ $supplier->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-lg-9 col-md-8">
                    <div class="row">
                        @forelse($products as $product)
                            @php
                                $price = (float) optional($product->variants->first())->price;
                                $image = $product->image ? asset('storage/' . $product->image) : asset('frontend/images/product/product-1.jpg');
                            @endphp
                            <div class="col-lg-4 col-md-6 col-sm-6 mb-4">
                                <div class="product__item">
                                    <div class="product__item__pic">
                                        <a href="{{ route('products.show', $product->id) }}">
                                            <img src="{{ $image }}" alt="{{ $product->name }}">
                                        </a>
                                    </div>
                                    <div class="product__item__text">
                                        <h6><a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a></h6>
                                        <h5>{{ number_format($price) }} đ</h5>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center">
                                <h6>Khong co san pham nao trong danh muc nay.</h6>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection