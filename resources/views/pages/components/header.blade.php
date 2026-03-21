<!-- Header Section Begin -->
<header class="header">
    <div class="header__top">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="header__top__left">
                        <ul>
                            <li><i class="fa fa-envelope"></i>senhongocopp@gmail.com</li>
                            <li>Giao hàng tận nơi miễn phí với đơn hàng chỉ từ 199k</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="header__top__right">
                        <div class="header__top__right__social">
                            <a href="#"><i class="fa fa-facebook"></i></a>
                            <a href="#"><i class="fa fa-twitter"></i></a>
                            <a href="#"><i class="fa fa-linkedin"></i></a>
                            <a href="#"><i class="fa fa-pinterest-p"></i></a>
                        </div>
                        <div class="header__top__right__language">
                            <img src="{{ asset('frontend/images/language.jpg') }}" alt="">
                            <div>Tiếng Việt</div>
                            <span class="arrow_carrot-down"></span>
                            <ul>
                                <li><a href="#">Tiếng Anh</a></li>
                                <li><a href="#">Tiếng Việt</a></li>
                            </ul>
                        </div>
                        <div class="header__top__right__auth">
                        
                            @auth
                                @if(auth()->user()->role === 'customer')
                                    <div class="dropdown">
                                        <a href="#">
                                            <i class="fa fa-user"></i>
                                            {{ auth()->user()->name }}
                                        </a>
                                        <ul class="header__menu__dropdown">
                                            <li>
                                                <a href="{{ route('customer.profile') }}">
                                                    Hồ sơ cá nhân
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('orders.my') }}">
                                                    Đơn hàng của tôi
                                                </a>
                                            </li>

                                            <li>
                                                <a href="{{ route('discounts') }}">
                                                    Mã giảm giá của tôi
                                                </a>
                                            </li>

                                            <li>
                                                <form action="{{ route('logout') }}" method="POST">
                                                    @csrf
                                                    <button type="submit">
                                                        Đăng xuất
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                @endif
                            @else
                                <div class="dropdown">
                                    <a href="{{ route('login') }}">
                                        <i class="fa fa-user"></i> Đăng nhập
                                    </a>
                                    <ul class="header__menu__dropdown">
                                        <li>
                                            <a href="{{ route('register') }}">
                                                Đăng ký
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @endauth
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="header__logo">
                    <a href="./index.html"><img src="{{ asset('frontend/images/logo.png') }}" alt=""></a>
                </div>
            </div>
            <div class="col-lg-7">
                <nav class="header__menu">
                    <ul>
                        <li class="active"><a href="{{ route('pages.trangchu') }}">Trang chủ</a></li>
                        <li>
                            <a href="{{ route('products.index') }}">Sản phẩm</a>
                        </li>
                        <li><a href="{{route('blogs.index')}}">Tin tức</a></li>
                        <li><a href="{{ route('contact') }}">Liên hệ</a></li>
                    </ul>
                </nav>
            </div>
            <div class="col-lg-2">
                <div class="header__cart">
                    <ul>
                        <li>
                            <a href="{{ route('wishlist.index') }}">
                                <i class="fa fa-heart"></i>
                                <span>
                                    {{ auth()->check() ? auth()->user()->wishlists()->count() : 0 }}
                                </span>
                            </a>
                        </li>

                        <li><a href="{{ route('cart.list') }}"><i class="fa fa-shopping-bag"></i> <span>{{ session('cart') ? count(session('cart')) : 0 }}</span></a></li>
                    </ul>
                    {{-- <div class="header__cart__price">item: <span>$150.00</span></div> --}}
                </div>
            </div>
        </div>
        <div class="humberger__open">
            <i class="fa fa-bars"></i>
        </div>
    </div>
</header>
<!-- Header Section End -->