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
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <section class="product-details spad">
        <div class="container">
            <div class="row">

                {{-- IMAGE --}}
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__pic">

                        <div class="product__details__pic__item">
                            <img id="mainImage"
                                src="{{ $product->image ? asset('storage/' . $product->image) : asset('images/no-image.png') }}"
                                style="width:100%; height:420px; object-fit:cover; border-radius:6px;">
                        </div>

                        <div class="product__details__pic__slider owl-carousel">
                            @php $index = 0; @endphp
                            @foreach ($product->variants as $variant)
                                @foreach ($variant->images as $img)
                                    <img src="{{ asset('storage/' . $img->image_path) }}"
                                        data-imgbigurl="{{ asset('storage/' . $img->image_path) }}"
                                        data-variant-id="{{ $variant->id }}" data-index="{{ $index }}" class="variant-image">
                                    @php $index++; @endphp
                                @endforeach
                            @endforeach
                        </div>

                    </div>
                </div>

                {{-- INFO --}}
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__text">

                        <h3>{{ $product->name }}</h3>

                        <div class="product__details__rating">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="fa fa-star{{ $i <= ($product->ocop_star ?? 0) ? '' : '-o' }}"></i>
                            @endfor
                            <span>({{ $product->ocop_year ?? 'OCOP' }})</span>
                        </div>

                        <div class="product__details__price" id="price">
                            {{ number_format($product->variants->first()?->price ?? 0) }} đ
                        </div>

                        <p>{{ $product->description ?? 'Chưa có mô tả.' }}</p>

                        {{-- VARIANT --}}
                        <div class="product__details__option mb-3">
                            <span>Chọn loại:</span>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach ($product->variants as $variant)
                                    <button type="button" class="variant-btn btn btn-outline-secondary btn-sm"
                                        data-id="{{ $variant->id }}" data-price="{{ $variant->price }}"
                                        data-stock="{{ $variant->inventory?->quantity ?? 0 }}">
                                        {{ $variant->volume ?? $variant->size ?? $variant->sku }}
                                    </button>
                                @endforeach
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

                            <button type="submit" class="primary-btn">THÊM GIỎ HÀNG</button>
                        </form>

                        <ul>
                            <li><b>Danh mục</b> <span>{{ $product->category?->name }}</span></li>
                            <li><b>Nhà cung cấp</b> <span>{{ $product->supplier?->name }}</span></li>
                        </ul>

                    </div>
                </div>

                {{-- TAB giữ nguyên --}}
            </div>
        </div>
    </section>

@endsection


<script>
    document.addEventListener('DOMContentLoaded', function () {

        const mainImage = document.getElementById('mainImage');
        const priceEl = document.getElementById('price');
        const selectedVariantInput = document.getElementById('selectedVariant');
        const quantityInput = document.getElementById('quantityInput');
        const stockText = document.getElementById('stockText');
        const submitBtn = document.querySelector('.primary-btn');

        const variantBtns = document.querySelectorAll('.variant-btn');
        const variantImgs = document.querySelectorAll('.variant-image');
        const $slider = $('.product__details__pic__slider');

        $slider.owlCarousel({
            items: 4,
            margin: 10,
            dots: false,
            nav: true,
            smartSpeed: 300
        });

        function changeMainImage(src) {
            if (!src) return;
            mainImage.style.opacity = 0;
            setTimeout(() => {
                mainImage.src = src;
                mainImage.style.opacity = 1;
            }, 200);
        }

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

        variantBtns.forEach(btn => {
            btn.addEventListener('click', function () {

                const variantId = this.dataset.id;
                const price = this.dataset.price;
                const stock = this.dataset.stock;

                selectedVariantInput.value = variantId;

                priceEl.innerText =
                    new Intl.NumberFormat('vi-VN').format(price) + ' đ';

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
                    const index = firstImg.dataset.index;
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

    });
</script>