<section class="hero">
    <div class="container">
        <div class="row">
            <?php if(empty($hideCategories)): ?>
                <div class="col-lg-3">
                    <div class="hero__categories <?php echo e(!empty($showCategories) ? 'show' : ''); ?>">
                        <div class="hero__categories__all">
                            <i class="fa fa-bars"></i>
                            <span>Danh mục sản phẩm</span>
                        </div>

                        <ul class="hero__categories__menu">
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li>
                                    <a href="<?php echo e(route('categories.show', $category->id)); ?>">
                                        <?php echo e($category->name); ?>

                                    </a>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>

                </div>
            <?php endif; ?>


            <div class="<?php echo e(empty($hideCategories) ? 'col-lg-9' : 'col-lg-12'); ?>">
                
                <div class="hero__search">
                    <div class="hero__search__form" style="position: relative;">
                        <form action="<?php echo e(route('products.index')); ?>">
                            <input type="text" id="search-input" name="keyword" placeholder="Bạn cần tìm gì?"
                                autocomplete="off">
                            <button type="button" id="voice-search-btn" class="voice-search-btn"
                                title="Tìm kiếm bằng giọng nói">
                                <i class="fa fa-microphone"></i>
                            </button>
                            <button type="submit" class="site-btn">TÌM</button>
                        </form>

                        <!-- Dropdown gợi ý -->
                        <div id="search-results" class="search-results"></div>

                        <!-- Voice modal (centered) -->
                        <div id="voice-modal" class="voice-modal-overlay" style="display:none;">
                            <div class="voice-modal-content">
                                <div class="voice-mic">
                                    <i class="fa fa-microphone"></i>
                                </div>
                                <div class="voice-text">
                                    <div class="voice-title">Đang nghe…</div>
                                    <div id="voice-transcript" class="voice-transcript">&nbsp;</div>
                                </div>
                            </div>
                        </div>
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
                
                <?php if(!empty($showBanner)): ?>
                    <div class="hero__item set-bg" data-setbg="<?php echo e(asset('frontend/images/hero/banner.png')); ?>">
                        <div class="hero__text">
                            <span>SEN HỒNG OCOP</span>
                            <h2>Sản phẩm OCOP <br />Đồng Tháp</h2>
                            <p>Đặc sản địa phương – Chất lượng – An toàn</p>
                            <a href="<?php echo e(route('products.index')); ?>" class="primary-btn">Xem sản phẩm</a>
                        </div>
                    </div>
                <?php endif; ?>

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

        .search-item:visited,
        .search-item:hover,
        .search-item:focus {
            color: #333;
            text-decoration: none;
        }

        .search-item:last-child {
            border-bottom: none;
        }

        /* Hover */
        .search-item:hover {
            background-color: #f1f1f1;
        }

        .search-item:hover .info .name {
            color: #333;
        }

        .search-item:hover .info .price {
            color: #777;
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

        /* Voice search button */
        .hero__search__form form .voice-search-btn {
            position: absolute;
            right: 105px;
            top: 50%;
            transform: translateY(-50%);
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #e7e7e7;
            border-radius: 50%;
            cursor: pointer;
            color: #aaa;
            font-size: 16px;
            padding: 0;
            z-index: 12;
            transition: color 0.2s;
            line-height: 1;
        }

        .hero__search__form form .voice-search-btn:hover {
            color: #7fad39;
        }

        .hero__search__form form .voice-search-btn.listening {
            color: #e53935;
            animation: pulse-mic 1s infinite;
        }

        .hero__search__form form input {
            padding-right: 150px;
        }

        .hero__search__form form .site-btn {
            z-index: 11;
        }

        @keyframes pulse-mic {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }

            100% {
                opacity: 1;
            }
        }

        /* Centered voice modal styles */
        .voice-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            -webkit-backdrop-filter: blur(2px);
            backdrop-filter: blur(2px);
        }

        .voice-modal-content {
            background: #fff;
            padding: 22px 24px;
            border-radius: 12px;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 320px;
            max-width: 90%;
        }

        .voice-mic {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff5252, #e53935);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 28px;
            box-shadow: 0 8px 20px rgba(229, 57, 53, 0.28);
            animation: mic-pulse 1.4s infinite;
        }

        @keyframes mic-pulse {
            0% {
                transform: scale(1);
                opacity: 1
            }

            50% {
                transform: scale(1.08);
                opacity: 0.8
            }

            100% {
                transform: scale(1);
                opacity: 1
            }
        }

        .voice-text .voice-title {
            font-weight: 700;
            margin-bottom: 6px;
            color: #222;
        }

        .voice-transcript {
            color: #444;
            font-size: 14px;
            max-height: 64px;
            overflow: auto;
            word-break: break-word;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchForm = document.querySelector('.hero__search__form form');
            const input = document.getElementById('search-input');
            const results = document.getElementById('search-results');
            const allProductsUrl = "<?php echo e(route('products.index')); ?>";
            let timer;

            if (!searchForm || !input || !results) {
                return;
            }

            function goToSearchResults(rawQuery) {
                const query = (rawQuery || '').trim();
                if (query.length === 0) {
                    return;
                }

                const targetUrl = `${allProductsUrl}?keyword=${encodeURIComponent(query)}`;
                window.location.href = targetUrl;
            }

            searchForm.addEventListener('submit', function (e) {
                e.preventDefault();
                goToSearchResults(input.value);
            });

            // ---- Live search khi gõ ----
            input.addEventListener('keyup', function () {
                clearTimeout(timer);
                const query = this.value.trim();

                if (query.length === 0) {
                    results.style.display = 'none';
                    results.innerHTML = '';
                    return;
                }

                timer = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            });

            // Ẩn dropdown khi click ra ngoài
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.hero__search__form')) {
                    results.style.display = 'none';
                }
            });

            // ---- Hàm gọi API gợi ý ----
            function fetchSuggestions(query) {
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
                                <img src="${p.image ?? '/frontend/images/product/product-1.jpg'}" alt="${p.name}">
                                <div class="info">
                                    <div class="name">${p.name}</div>
                                    <div class="price">
                                        ${p.has_discount
                                ? `<span style="color:#d32f2f;font-weight:600;">${Number(p.final_price).toLocaleString()}₫</span> <small style="color:#777;"><del>${Number(p.price).toLocaleString()}₫</del></small>`
                                : `${Number(p.price).toLocaleString()}₫`
                            }
                                    </div>
                                </div>
                            </a>
                        `).join('');

                        results.style.display = 'block';
                    });
            }

            // ---- Voice Search ----
            const voiceBtn = document.getElementById('voice-search-btn');

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            const isSecureVoiceContext = location.protocol === 'https:' ||
                location.hostname === 'localhost' ||
                location.hostname === '127.0.0.1';

            if (!SpeechRecognition || !voiceBtn) {
                // Ẩn nút nếu trình duyệt không hỗ trợ
                if (voiceBtn) voiceBtn.style.display = 'none';
            } else if (!isSecureVoiceContext) {
                voiceBtn.addEventListener('click', function () {
                    alert('Voice search chỉ hoạt động trên HTTPS hoặc localhost.');
                });
            } else {
                const recognition = new SpeechRecognition();
                recognition.lang = 'vi-VN';
                recognition.continuous = false;
                recognition.interimResults = true;
                let isListening = false;
                let finalTranscript = '';

                voiceBtn.addEventListener('click', function () {
                    if (isListening) {
                        recognition.stop();
                        return;
                    }
                    try {
                        recognition.start();
                    } catch (error) {
                        showStatus('Không thể khởi động microphone. Vui lòng thử lại.', 3000);
                    }
                });

                recognition.addEventListener('start', function () {
                    isListening = true;
                    finalTranscript = '';
                    voiceBtn.classList.add('listening');
                    voiceBtn.querySelector('i').className = 'fa fa-microphone';
                    // Clear input box and show centered modal
                    input.value = '';
                    const modal = document.getElementById('voice-modal');
                    const transcriptEl = document.getElementById('voice-transcript');
                    if (modal) {
                        modal.style.display = 'flex';
                        if (transcriptEl) transcriptEl.textContent = '';
                    }
                });

                recognition.addEventListener('result', function (e) {
                    let interimTranscript = '';

                    for (let i = e.resultIndex; i < e.results.length; i++) {
                        const chunk = e.results[i][0].transcript;
                        if (e.results[i].isFinal) {
                            finalTranscript += `${chunk} `;
                        } else {
                            interimTranscript += chunk;
                        }
                    }

                    const liveText = `${finalTranscript}${interimTranscript}`.replace(/\s+/g, ' ').trim();
                    // Only update modal, NOT the input box
                    const transcriptEl = document.getElementById('voice-transcript');
                    if (transcriptEl) transcriptEl.textContent = liveText || '';

                    if (liveText.length > 0) {
                        fetchSuggestions(liveText);
                    }
                });

                recognition.addEventListener('end', function () {
                    isListening = false;
                    voiceBtn.classList.remove('listening');

                    // Hide modal immediately - do not show popup
                    const modal = document.getElementById('voice-modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }

                    // Display the recognized text in the input box
                    const finalText = finalTranscript.trim();
                    input.value = finalText;

                    // Auto-trigger search results display and navigate
                    if (finalText.length > 0) {
                        goToSearchResults(finalText);
                    }
                });

                recognition.addEventListener('error', function (e) {
                    isListening = false;
                    voiceBtn.classList.remove('listening');
                    const messages = {
                        'not-allowed': 'Vui lòng cấp quyền microphone.',
                        'no-speech': 'Không nghe thấy giọng nói.',
                        'network': 'Lỗi mạng, không thể nhận diện giọng.',
                    };
                    alert(messages[e.error] || 'Lỗi nhận diện giọng nói.');
                    // Hide modal immediately on error
                    const modal = document.getElementById('voice-modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                });
            }
        });
    </script>

</section><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/pages/components/hero.blade.php ENDPATH**/ ?>