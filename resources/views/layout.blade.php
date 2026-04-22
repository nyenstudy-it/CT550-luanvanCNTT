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
    <!-- SweetAlert2 - Load early for popups -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    @php
        $flashMessages = [
            'order_success' => session('order_success'),
            'success' => session('success'),
            'error' => session('error'),
            'warning' => session('warning'),
            'info' => session('info'),
            'showCartPopup' => session('showCartPopup'),
        ];
    @endphp
    <script>
        // Check localStorage for cart popup flag in case session data was consumed by AJAX
        if (!{!! json_encode($flashMessages['showCartPopup']) !!} && localStorage.getItem('showCartPopupOnLoad')) {
            void 0;
        }

        (function () {
            const flashMessages = {!! json_encode($flashMessages) !!};

            const cartUrl = {!! json_encode(route('cart.list')) !!};
            const loginUrl = {!! json_encode(route('login')) !!};
            const orderSuccessBaseUrl = {!! json_encode(url('/order')) !!};
            const shownMessages = new Set();

            function hasSwal() {
                return typeof Swal !== 'undefined';
            }

            function normalizeLevel(level) {
                if (!level) {
                    return 'info';
                }

                const normalized = String(level).toLowerCase();
                if (['success', 'error', 'warning', 'info'].includes(normalized)) {
                    return normalized;
                }

                if (normalized.includes('danger')) {
                    return 'error';
                }

                return 'info';
            }

            function defaultTitle(level) {
                switch (normalizeLevel(level)) {
                    case 'success':
                        return 'Thành công';
                    case 'error':
                        return 'Có lỗi xảy ra';
                    case 'warning':
                        return 'Lưu ý';
                    default:
                        return 'Thông báo';
                }
            }

            function firePopup(options) {
                const popupOptions = Object.assign({
                    icon: 'info',
                    title: 'Thông báo',
                    confirmButtonColor: '#7fad39'
                }, options || {});

                if (hasSwal()) {
                    return Swal.fire(popupOptions);
                }

                console.warn('⚠ Swal not available, using fallback alert()');
                // FALLBACK: Use simple alert() - better than nothing
                const alertMessage = (popupOptions.title || '') + '\n\n' + (popupOptions.text || '');
                alert(alertMessage || 'Thông báo');

                // Return a promise that mimics Swal result
                return Promise.resolve({
                    isConfirmed: true,
                    isDismissed: false
                });
            }

            async function notify(level, message, options) {
                const text = (message || '').toString().trim();
                if (!text) {
                    return;
                }

                const lowerText = text.toLowerCase();
                const dedupeKey = normalizeLevel(level) + '::' + text;

                // Không dedup popup "thêm vào giỏ hàng" - nếu người dùng thêm nhiều lần cần popup mỗi lần
                const isAddToCart = lowerText.includes('thêm vào giỏ hàng');

                if (!isAddToCart) {
                    // Apply dedup cho popup khác
                    if (shownMessages.has(dedupeKey)) {
                        return;
                    }
                    shownMessages.add(dedupeKey);
                }

                const lv = normalizeLevel(level);
                // lowerText already defined above at line 256, don't redefine

                if (lv === 'success' && lowerText.includes('thêm vào giỏ hàng')) {
                    const result = await firePopup(Object.assign({
                        icon: 'success',
                        title: 'Thêm vào giỏ hàng thành công',
                        text: 'Bạn muốn thanh toán ngay hay tiếp tục mua sắm?',
                        showCancelButton: true,
                        confirmButtonText: 'Thanh toán',
                        cancelButtonText: 'Tiếp tục mua sắm'
                    }, options || {}));

                    if (result.isConfirmed) {
                        window.location.href = cartUrl;
                    }

                    return;
                }

                if (lv === 'success' && lowerText.includes('thêm vào yêu thích')) {
                    await firePopup(Object.assign({
                        icon: 'success',
                        title: 'Đã thêm vào yêu thích',
                        text: 'Sản phẩm đã được lưu vào danh sách yêu thích của bạn.'
                    }, options || {}));
                    return;
                }

                if (lv === 'success' && lowerText.includes('đã gửi đánh giá, chờ duyệt')) {
                    await firePopup(Object.assign({
                        icon: 'success',
                        title: 'Cảm ơn đã đánh giá',
                        text: 'Cảm ơn bạn đã đánh giá sản phẩm. Quản trị viên sẽ xem xét đánh giá của bạn trong thời gian sớm nhất.'
                    }, options || {}));
                    return;
                }

                if (lv === 'success' && (lowerText.includes('đã huỷ đơn hàng') || lowerText.includes('đã hủy đơn hàng'))) {
                    await firePopup(Object.assign({
                        icon: 'success',
                        title: 'Huỷ đơn thành công',
                        text: 'Đơn hàng của bạn đã được huỷ thành công.'
                    }, options || {}));
                    return;
                }

                if (lv === 'success' && lowerText.includes('chờ xử lý hoàn hàng')) {
                    await firePopup(Object.assign({
                        icon: 'success',
                        title: 'Đã gửi yêu cầu hoàn hàng',
                        text: 'Yêu cầu hoàn hàng đã được ghi nhận. Cửa hàng sẽ xử lý trong thời gian sớm nhất.'
                    }, options || {}));
                    return;
                }

                if (lv === 'success' && lowerText.includes('đã gửi yêu cầu hoàn hàng')) {
                    await firePopup(Object.assign({
                        icon: 'success',
                        title: 'Đã gửi yêu cầu hoàn hàng',
                        text: 'Yêu cầu hoàn hàng của bạn đã được gửi thành công.'
                    }, options || {}));
                    return;
                }

                if (lv === 'success' && lowerText.includes('xác nhận nhận hàng')) {
                    await firePopup(Object.assign({
                        icon: 'success',
                        title: 'Đã nhận được hàng',
                        text: 'Cảm ơn bạn đã xác nhận nhận hàng.'
                    }, options || {}));
                    return;
                }

                if ((lv === 'warning' || lv === 'info') && lowerText.includes('chưa đăng nhập')) {
                    const result = await firePopup(Object.assign({
                        icon: 'warning',
                        title: 'Bạn chưa đăng nhập',
                        text: text,
                        showCancelButton: true,
                        confirmButtonText: 'Đăng nhập',
                        cancelButtonText: 'Để sau'
                    }, options || {}));

                    if (result.isConfirmed) {
                        window.location.href = loginUrl;
                    }

                    return;
                }

                await firePopup(Object.assign({
                    icon: lv,
                    title: defaultTitle(lv),
                    text: text
                }, options || {}));
            }

            async function showOrderSuccess(orderId) {
                if (!orderId) {
                    return false;
                }

                const safeOrderId = String(orderId).trim();
                if (!safeOrderId) {
                    return false;
                }

                try {
                    await firePopup({
                        icon: 'success',
                        title: 'Đặt hàng thành công!',
                        html: 'Cảm ơn bạn đã mua hàng tại <b>SEN HỒNG OCOP</b><br>Mã đơn hàng: <b>#' + safeOrderId + '</b>',
                        confirmButtonText: 'Xem đơn hàng'
                    });

                    // Only navigate if we're not already on the order detail page
                    const currentPath = window.location.pathname;
                    const orderDetailPath = '/order/' + encodeURIComponent(safeOrderId);

                    if (!currentPath.includes(orderDetailPath)) {
                        window.location.href = orderSuccessBaseUrl + '/' + encodeURIComponent(safeOrderId);
                    }

                    return true;
                } catch (error) {
                    console.error('✗ showOrderSuccess: Error showing popup', error);
                    return false;
                }
            }

            function alertToLevel(alertElement) {
                if (!alertElement) {
                    return 'info';
                }

                if (alertElement.classList.contains('alert-success')) {
                    return 'success';
                }

                if (alertElement.classList.contains('alert-danger')) {
                    return 'error';
                }

                if (alertElement.classList.contains('alert-warning')) {
                    return 'warning';
                }

                return 'info';
            }

            async function showFlashMessages() {
                // Check localStorage flag in case PHP session was consumed by AJAX
                const localStorageValue = localStorage.getItem('showCartPopupOnLoad');
                const localStorageCartPopup = localStorageValue === 'true';

                // Check both conditions
                const phpSessionHasFlag = flashMessages.showCartPopup === true;
                const shouldShowCartPopup = phpSessionHasFlag || localStorageCartPopup;

                // Check order_success first
                const orderSuccessResult = await showOrderSuccess(flashMessages.order_success);
                if (orderSuccessResult) {
                    return;
                }

                // Handle explicit cart popup flag - GUARANTEED to show (with or without Swal)
                if (shouldShowCartPopup) {
                    // Clear localStorage flag
                    localStorage.removeItem('showCartPopupOnLoad');

                    const result = await firePopup({
                        icon: 'success',
                        title: 'Thêm vào giỏ hàng thành công',
                        text: 'Bạn muốn thanh toán ngay hay tiếp tục mua sắm?',
                        showCancelButton: true,
                        confirmButtonText: 'Thanh toán',
                        cancelButtonText: 'Tiếp tục mua sắm',
                        confirmButtonColor: '#7fad39'
                    });

                    if (result.isConfirmed) {
                        window.location.href = cartUrl;
                    }
                    return;
                }

                if (flashMessages.success) {
                    await notify('success', flashMessages.success);
                }
                if (flashMessages.error) {
                    await notify('error', flashMessages.error);
                }
                if (flashMessages.warning) {
                    await notify('warning', flashMessages.warning);
                }
                if (flashMessages.info) {
                    await notify('info', flashMessages.info);
                }
            }

            async function convertInlineAlertsToPopup() {
                const alerts = Array.from(document.querySelectorAll('main .alert'));

                for (const alertElement of alerts) {
                    const text = (alertElement.textContent || '').trim();
                    if (!text) {
                        alertElement.remove();
                        continue;
                    }

                    await notify(alertToLevel(alertElement), text);
                    alertElement.remove();
                }
            }

            window.ocopPopup = {
                fire: firePopup,
                notify: function (level, message, options) {
                    notify(level, message, options).catch(err => {
                        console.error('Error in notify():', err);
                    });
                }
            };

            // Global popup() function - available on all pages
            window.popup = function (icon, title, text, additionalOptions) {
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
            };

            (function () {
                // If document is already loaded (readyState is 'interactive' or 'complete'),
                // we need to call showFlashMessages immediately since DOMContentLoaded won't fire
                if (document.readyState === 'interactive' || document.readyState === 'complete') {
                    // Small delay to ensure all event listeners are ready
                    setTimeout(async function () {
                        if (!window.__ocopFlashMessagesShown) {
                            window.__ocopFlashMessagesShown = true;
                            await showFlashMessages();
                            await convertInlineAlertsToPopup();
                        }
                    }, 50);
                }
            })();

            document.addEventListener('DOMContentLoaded', async function () {
                if (window.__ocopFlashMessagesShown === true) {
                    return;
                }

                if (window.__ocopSkipGlobalPopup === true) {
                    return;
                }

                // Wait briefly for Swal to be available if not already
                let waitCount = 0;
                while (!window.Swal && waitCount < 50) {
                    await new Promise(resolve => setTimeout(resolve, 10));
                    waitCount++;
                }

                if (!window.Swal) {
                    console.warn('⚠ WARNING: Swal not available after waiting 500ms - using fallback alert()');
                }

                // Set flag BEFORE calling showFlashMessages to avoid duplicate calls
                window.__ocopFlashMessagesShown = true;
                await showFlashMessages();
                await convertInlineAlertsToPopup();
            });

            // Fallback: nếu DOMContentLoaded không trigger (page đã load trước khi script chạy), dùng window.load
            window.addEventListener('load', async function () {
                if (window.__ocopFlashMessagesShown === true) {
                    return;
                }

                if (window.__ocopSkipGlobalPopup === true) {
                    return;
                }

                // Set flag to prevent duplicate execution
                window.__ocopFlashMessagesShown = true;

                // Show flash messages at window.load as fallback
                await showFlashMessages();
                await convertInlineAlertsToPopup();
            }, { once: true });

        })();
    </script>

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
    <script
        src="{{ asset('frontend/js/chatbox.js') }}?v={{ filemtime(public_path('frontend/js/chatbox.js')) }}"></script>

    @stack('scripts')

</body>

</html>