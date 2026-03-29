<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Ogani Template">
    <meta name="keywords" content="Ogani, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SEN HỒNG OCOP</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;900&display=swap" rel="stylesheet">
    <!-- Css Styles -->
    <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/chatbox.css') }}" type="text/css">
</head>

<body>
    @php
        $storeChatUnreadCount = 0;
        if (auth()->check() && auth()->user()->role === 'customer') {
            $storeChatUnreadCount = \App\Models\CustomerMessage::query()
                ->where('customer_id', auth()->id())
                ->where('sender_type', '!=', 'customer')
                ->where('is_read', false)
                ->count();
        }
    @endphp
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    {{-- <!-- Humberger Begin -->
    <div class="humberger__menu__overlay"></div>
    <div class="humberger__menu__wrapper">
        <div class="humberger__menu__logo">
            <a href="#"><img src="{{ asset('frontend/images/logo.png') }}" alt=""></a>
        </div>
        <div class="humberger__menu__cart">
            <ul>
                <li><a href="#"><i class="fa fa-heart"></i> <span>1</span></a></li>
                <li><a href="#"><i class="fa fa-shopping-bag"></i> <span>3</span></a></li>
            </ul>
            <div class="header__cart__price">item: <span>$150.00</span></div>
        </div>
        <div class="humberger__menu__widget">
            <div class="header__top__right__language">
                <img src="{{ asset('frontend/images/language.png') }}" alt="">
                <div>English</div>
                <span class="arrow_carrot-down"></span>
                <ul>
                    <li><a href="#">Spanis</a></li>
                    <li><a href="#">English</a></li>
                </ul>
            </div>
            <div class="header__top__right__auth">
                <a href="#"><i class="fa fa-user"></i>Đăng nhập</a>
            </div>
        </div>
        <nav class="humberger__menu__nav mobile-menu">
            <ul>
                <li class="active"><a href="{{ route('pages.home') }}">Trang chủ</a></li>
                <li><a href="./shop-grid.html">Sản phẩm</a></li>
                <li><a href="#">Pages</a>
                    <ul class="header__menu__dropdown">
                        <li><a href="./shop-details.html">Shop Details</a></li>
                        <li><a href="./shoping-cart.html">Shoping Cart</a></li>
                        <li><a href="./checkout.html">Check Out</a></li>
                        <li><a href="./blog-details.html">Blog Details</a></li>
                    </ul>
                </li>
                <li><a href="./blog.html">Blog</a></li>
                <li><a href="./contact.html">Contact</a></li>
            </ul>
        </nav>
        <div id="mobile-menu-wrap"></div>
        <div class="header__top__right__social">
            <a href="#"><i class="fa fa-facebook"></i></a>
            <a href="#"><i class="fa fa-twitter"></i></a>
            <a href="#"><i class="fa fa-linkedin"></i></a>
            <a href="#"><i class="fa fa-pinterest-p"></i></a>
        </div>
        <div class="humberger__menu__contact">
            <ul>
                <li><i class="fa fa-envelope"></i> hello@colorlib.com</li>
                <li>Free Shipping for all Order of $99</li>
            </ul>
        </div>
    </div>
    <!-- Humberger End --> --}}
    @include('pages.components.header')
    @yield('hero')
    <main>
        @yield('content')
    </main>
    @include('pages.components.footer')

    <!-- Js Plugins -->
    <script src="{{ asset('frontend/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('frontend/js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('frontend/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('frontend/js/jquery.slicknav.js') }}"></script>
    <script src="{{ asset('frontend/js/mixitup.min.js') }}"></script>
    <script src="{{ asset('frontend/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('frontend/js/main.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- JS show/hide categories -->
    <script>
        $(document).ready(function () {
            var isHome = {{ !empty($showCategories) && $showCategories ? 'true' : 'false' }};

            if (!isHome) {
                // Hover vào nút xổ danh mục
                $('.hero__categories__all').hover(function () {
                    $(this).siblings('.hero__categories__menu').stop(true, true).slideDown(200);
                }, function () {
                    $(this).siblings('.hero__categories__menu').stop(true, true).slideUp(200);
                });

                // Hover vào menu, giữ menu mở
                $('.hero__categories__menu').hover(function () {
                    $(this).stop(true, true).show();
                }, function () {
                    $(this).stop(true, true).slideUp(200);
                });
            }
        });
    </script>



    @yield('scripts')

    @if(session('order_success'))

        <script>

            document.addEventListener("DOMContentLoaded", function () {

                Swal.fire({
                    icon: 'success',
                    title: 'Đặt hàng thành công!',
                    html: `
                                    Cảm ơn bạn đã mua hàng tại <b>SEN HỒNG OCOP</b><br>
                                    Mã đơn hàng: <b>#{{ session('order_success') }}</b>
                                `,
                    confirmButtonText: 'Xem đơn hàng',
                    confirmButtonColor: '#28a745'
                }).then(() => {

                    window.location.href = "{{ route('orders.detail', session('order_success')) }}";

                });

            });

        </script>

    @endif

    <div id="ai-chatbox" class="ai-chatbox">
        @auth
            @if(auth()->user()->role === 'customer')
                <button id="store-chatbox-toggle" class="ai-chatbox__store-link" type="button" aria-label="Chat với cửa hàng"
                    title="Chat với cửa hàng">
                    <i class="fa fa-comments"></i>
                    <span id="store-chatbox-badge"
                        class="ai-chatbox__store-badge {{ $storeChatUnreadCount > 0 ? '' : 'd-none' }}">{{ $storeChatUnreadCount }}</span>
                </button>
            @endif
        @endauth

        <button id="ai-chatbox-toggle" class="ai-chatbox__toggle" type="button" aria-label="Mở trợ lý AI">
            Tư vấn AI
        </button>

        <section id="ai-chatbox-panel" class="ai-chatbox__panel" hidden>
            <header class="ai-chatbox__header">
                <h3>Trợ lý sản phẩm</h3>
                <button id="ai-chatbox-close" class="ai-chatbox__close" type="button" aria-label="Đóng">x</button>
            </header>

            <div id="ai-chatbox-messages" class="ai-chatbox__messages">
                <div class="ai-chatbox__message ai-chatbox__message--assistant">
                    Xin chào, mình có thể gợi ý sản phẩm theo nhu cầu và ngân sách của bạn.
                </div>
            </div>

            <div id="ai-chatbox-suggestions" class="ai-chatbox__suggestions"></div>

            <form id="ai-chatbox-form" class="ai-chatbox__form">
                <textarea id="ai-chatbox-input" rows="2" placeholder="Nhập nhu cầu của bạn..." required></textarea>
                <button type="submit">Gửi</button>
            </form>
        </section>

        @auth
            @if(auth()->user()->role === 'customer')
                <section id="store-chatbox-panel" class="store-chatbox__panel" hidden>
                    <header class="store-chatbox__header">
                        <h3>Chat với cửa hàng</h3>
                        <button id="store-chatbox-close" class="store-chatbox__close" type="button" aria-label="Đóng">x</button>
                    </header>

                    <div id="store-chatbox-messages" class="store-chatbox__messages">
                        <div class="store-chatbox__message store-chatbox__message--staff">
                            Xin chào, cửa hàng có thể hỗ trợ gì cho bạn?
                        </div>
                    </div>

                    <form id="store-chatbox-form" class="store-chatbox__form">
                        <textarea id="store-chatbox-input" rows="2" placeholder="Nhập câu hỏi của bạn..." required></textarea>
                        <button type="submit">Gửi</button>
                    </form>
                </section>
            @endif
        @endauth
    </div>

    <script>
        window.chatboxConfig = {
            endpoint: "{{ route('ai.chatbox') }}",
            csrfToken: "{{ csrf_token() }}",
            storeFetchEndpoint: "{{ auth()->check() && auth()->user()->role === 'customer' ? route('customer.chat') : '' }}",
            storeSendEndpoint: "{{ auth()->check() && auth()->user()->role === 'customer' ? route('customer.chat.send') : '' }}",
            storeUnreadEndpoint: "{{ auth()->check() && auth()->user()->role === 'customer' ? route('customer.chat.unreadCount') : '' }}",
            markChatNotificationsEndpoint: "{{ auth()->check() && auth()->user()->role === 'customer' ? route('customer.notifications.markChatRead') : '' }}",
            openStoreChatOnLoad: {{ request()->boolean('open_store_chat') ? 'true' : 'false' }},
            suggestedQuestions: [
                "Gợi ý sản phẩm làm quà OCOP dưới 500.000đ",
                "Mình cần sản phẩm tốt cho sức khỏe cho người lớn tuổi",
                "Có sản phẩm nào đang bán chạy và còn hàng không?"
            ]
        };
    </script>
    <script src="{{ asset('frontend/js/chatbox.js') }}"></script>

</body>

</html>