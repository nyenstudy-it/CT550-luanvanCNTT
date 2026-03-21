<section class="hero">
    <div class="container">
        <div class="row">
            @if(empty($hideCategories))
                <div class="col-lg-3">
                    <div class="hero__categories {{ !empty($showCategories) ? 'show' : '' }}">
                        <div class="hero__categories__all">
                            <i class="fa fa-bars"></i>
                            <span>Danh mục sản phẩm</span>
                        </div>

                        <ul class="hero__categories__menu">
                            @foreach ($categories as $category)
                                <li>
                                    <a href="{{ route('categories.show', $category->id) }}">
                                        {{ $category->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            @endif


            <div class="{{ empty($hideCategories) ? 'col-lg-9' : 'col-lg-12' }}">
                {{-- SEARCH --}}
                <div class="hero__search">
                    <div class="hero__search__form" style="position: relative;">
                        <form action="{{ route('products.index') }}">
                            <input type="text" id="search-input" placeholder="Bạn cần tìm gì?" autocomplete="off">
                            <button type="submit" class="site-btn">TÌM</button>
                        </form>

                        <!-- Dropdown gợi ý -->
                        <div id="search-results" class="search-results"></div>
                    </div>

                    <div class="hero__search__phone">
                        <div class="hero__search__phone__icon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <div class="hero__search__phone__text">
                            <h5>+84 346 600 661</h5>
                            <span>Hỗ trợ 24/7</span>
                        </div>
                    </div>
                </div>

                {{-- BANNER: CHỈ HIỆN KHI ĐƯỢC PHÉP --}}
                @if (!empty($showBanner))
                    <div class="hero__item set-bg" data-setbg="{{ asset('frontend/images/hero/banner.jpg') }}">
                        <div class="hero__text">
                            <span>FRUIT FRESH</span>
                            <h2>Vegetable <br />100% Organic</h2>
                            <p>Free Pickup and Delivery Available</p>
                            <a href="#" class="primary-btn">SHOP NOW</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>


    <style>
        /* Container dropdown */
        .hero__search__form {
            position: relative;
            /* cực kỳ quan trọng */
        }

        /* Dropdown gợi ý */
        .search-results {
            position: absolute;
            top: 100%;
            /* ngay dưới input */
            left: 0;
            width: 100%;
            /* bằng width của input */
            z-index: 1000;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 300px;
            overflow-y: auto;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Item trong dropdown */
        .search-item {
            display: flex;
            align-items: center;
            padding: 5px 10px;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #f1f1f1;
        }

        .search-item:last-child {
            border-bottom: none;
        }

        /* Hover */
        .search-item:hover {
            background-color: #f1f1f1;
        }

        /* Ảnh sản phẩm */
        .search-item img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            margin-right: 10px;
        }

        /* Thông tin text */
        .search-item .info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .search-item .info .name {
            font-size: 14px;
            font-weight: 500;
        }

        .search-item .info .price {
            font-size: 13px;
            color: #777;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('search-input');
            const results = document.getElementById('search-results');
            let timer;

            input.addEventListener('keyup', function () {
                clearTimeout(timer);
                const query = this.value.trim();

                if (query.length === 0) {
                    results.style.display = 'none';
                    results.innerHTML = '';
                    return;
                }

                timer = setTimeout(() => {
                    fetch(`/search-products?query=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.length === 0) {
                                results.innerHTML = '<div class="p-2 text-muted">Không có sản phẩm nào</div>';
                                results.style.display = 'block';
                                return;
                            }

                            results.innerHTML = data.map(p => `
                        <a href="/products/${p.id}" class="search-item">
                            <img src="${p.image ?? '/images/no-image.png'}" alt="${p.name}">
                            <div class="info">
                                <div class="name">${p.name}</div>
                                <div class="price">${Number(p.price).toLocaleString()}₫</div>
                            </div>
                        </a>
                    `).join('');

                            results.style.display = 'block';
                        });
                }, 300);
            });

            // Ẩn dropdown khi click ra ngoài
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.hero__search__form')) {
                    results.style.display = 'none';
                }
            });
        });
    </script>

</section>