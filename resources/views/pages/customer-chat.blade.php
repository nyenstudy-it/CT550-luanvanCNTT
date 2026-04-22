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
                        <h2>Chat với cửa hàng</h2>
                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.trangchu') }}">Trang chủ</a>
                            <span>Chat</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="product spad">
        <div class="container">
            <div class="customer-chat card border-0 shadow-sm overflow-hidden">
                <div class="customer-chat__header">
                    <div>
                        <h5 class="mb-0">Sen Hong OCOP Support</h5>
                        <small class="text-muted">Thường phản hồi trong vài phút</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success">Online</span>
                        <a href="{{ route('pages.trangchu') }}" class="btn btn-sm btn-light" title="Quay lại">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>

                <div id="messages-container" class="customer-chat__messages">
                    <div class="empty-state">
                        <i class="fa fa-comments"></i>
                        <p>Đang tải tin nhắn...</p>
                    </div>
                </div>

                <div class="customer-chat__composer">
                    <textarea id="message-input" class="form-control" rows="2"
                        placeholder="Nhập câu hỏi của bạn về sản phẩm..."></textarea>
                    <button id="send-button" class="btn btn-primary" type="button">
                        <i class="fa fa-paper-plane me-1"></i> Gửi
                    </button>
                </div>
            </div>
        </div>
    </section>

    <style>
        .customer-chat {
            max-width: 920px;
            margin: 0 auto;
            height: calc(100vh - 260px);
            min-height: 560px;
            display: grid;
            grid-template-rows: auto 1fr auto;
            background: linear-gradient(180deg, #f4f7fd 0%, #fefefe 100%);
            position: relative;
        }

        .customer-chat__header {
            background: #fff;
            border-bottom: 1px solid #e7edf7;
            padding: 14px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .customer-chat__messages {
            overflow-y: auto;
            padding: 16px;
        }

        .empty-state {
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #9aa6b2;
            gap: 8px;
        }

        .empty-state i {
            font-size: 30px;
        }

        .msg-row {
            display: flex;
            margin-bottom: 12px;
        }

        .msg-row.customer {
            justify-content: flex-end;
        }

        .msg-row.staff,
        .msg-row.admin {
            justify-content: flex-start;
        }

        .msg-bubble {
            max-width: 74%;
            border-radius: 16px;
            padding: 10px 12px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        .msg-row.customer .msg-bubble {
            background: #2f7bff;
            color: #fff;
            border-top-right-radius: 4px;
        }

        .msg-row.staff .msg-bubble,
        .msg-row.admin .msg-bubble {
            background: #ffffff;
            color: #1d2a3b;
            border-top-left-radius: 4px;
        }

        .msg-name {
            font-size: 12px;
            margin-bottom: 4px;
            color: #6d7a89;
        }

        .msg-row.customer .msg-name {
            color: rgba(255, 255, 255, 0.85);
        }

        .msg-time {
            font-size: 11px;
            margin-top: 4px;
            opacity: 0.75;
        }

        .customer-chat__composer {
            background: #fff;
            border-top: 1px solid #e7edf7;
            padding: 12px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: end;
        }

        #message-input {
            resize: none;
            border-radius: 12px;
            border: 1px solid #d7deea;
            padding: 10px 12px;
        }

        @media (max-width: 767.98px) {
            .customer-chat {
                height: auto;
                min-height: 520px;
            }

            .customer-chat__header {
                position: sticky;
                top: 0;
                padding: 12px 14px;
            }

            .customer-chat__messages {
                min-height: 300px;
            }

            .msg-bubble {
                max-width: 85%;
            }
        }
    </style>

    <script>
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

        document.addEventListener('DOMContentLoaded', function () {
            const messagesContainer = document.getElementById('messages-container');
            const messageInput = document.getElementById('message-input');
            const sendButton = document.getElementById('send-button');

            // State management
            let lastMessageId = 0;
            let isLoading = false;
            let isSending = false;
            let pollInterval = null;
            let messageCache = new Set();  // Track rendered messages to avoid duplicates

            // Initial load
            loadMessages(true);

            sendButton.addEventListener('click', sendMessage);
            messageInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // REAL-TIME POLLING: Poll mỗi 2 giây thay vì 8 -> cảm giác real-time như Zalo
            pollInterval = setInterval(() => {
                if (!isLoading && !isSending) {
                    loadMessages(false);
                }
            }, 2000);

            function loadMessages(isInitial = false) {
                if (isLoading) return;
                isLoading = true;

                const params = isInitial ? '' : `?from_id=${lastMessageId}`;

                fetch('{{ route('customer.chat') }}' + params)
                    .then(res => {
                        if (!res.ok) throw new Error('Network error');
                        return res.json();
                    })
                    .then(data => {
                        if (!Array.isArray(data)) {
                            data = [];
                        }

                        if (data.length === 0) {
                            if (isInitial && lastMessageId === 0) {
                                messagesContainer.innerHTML = '<div class="empty-state"><i class="fa fa-comments"></i><p>Bạn chưa có tin nhắn nào.</p></div>';
                            }
                            return;
                        }

                        // Render messages incrementally
                        if (isInitial) {
                            // Initial load: clear and render all messages
                            const fragment = document.createDocumentFragment();
                            messageCache.clear();

                            data.forEach(msg => {
                                const el = createMessageElement(msg);
                                if (el && msg.id) {
                                    messageCache.add(msg.id);
                                    fragment.appendChild(el);
                                }
                            });

                            messagesContainer.innerHTML = '';
                            messagesContainer.appendChild(fragment);
                        } else {
                            // Incremental: append only new messages
                            const fragment = document.createDocumentFragment();
                            let newCount = 0;

                            data.forEach(msg => {
                                if (msg.id && !messageCache.has(msg.id)) {
                                    const el = createMessageElement(msg);
                                    if (el) {
                                        messageCache.add(msg.id);
                                        fragment.appendChild(el);
                                        newCount++;
                                    }
                                }
                            });

                            if (newCount > 0) {
                                // Remove empty state if it exists
                                const emptyState = messagesContainer.querySelector('.empty-state');
                                if (emptyState) emptyState.remove();

                                messagesContainer.appendChild(fragment);
                            }
                        }

                        // Update lastMessageId (highest ID)
                        if (data.length > 0) {
                            const ids = data.map(m => parseInt(m.id)).filter(id => !isNaN(id));
                            if (ids.length > 0) {
                                lastMessageId = Math.max(...ids);
                            }
                        }

                        // Smooth scroll to bottom if user is scrolled near bottom
                        const isNearBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop - messagesContainer.clientHeight < 100;
                        if (isNearBottom || isInitial) {
                            requestAnimationFrame(() => {
                                messagesContainer.scrollTop = messagesContainer.scrollHeight;
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Load messages error:', err);
                    })
                    .finally(() => {
                        isLoading = false;
                    });
            }

            function createMessageElement(msg) {
                try {
                    const senderName = msg.sender_type === 'customer'
                        ? 'Bạn'
                        : (msg.staff?.name || msg.staff_id ? 'Nhân viên' : 'Cửa hàng');

                    const div = document.createElement('div');
                    div.className = `msg-row ${msg.sender_type}`;
                    div.setAttribute('data-msg-id', msg.id);

                    const bubble = document.createElement('div');
                    bubble.className = 'msg-bubble';

                    const name = document.createElement('div');
                    name.className = 'msg-name';
                    name.textContent = senderName;

                    const content = document.createElement('div');
                    content.textContent = msg.message || '';

                    const time = document.createElement('div');
                    time.className = 'msg-time';
                    time.textContent = formatDateTime(msg.created_at);

                    bubble.appendChild(name);
                    bubble.appendChild(content);
                    bubble.appendChild(time);
                    div.appendChild(bubble);

                    return div;
                } catch (e) {
                    console.error('Error creating message element:', e);
                    return null;
                }
            }

            function sendMessage() {
                const text = messageInput.value.trim();
                if (!text || isSending) return;

                isSending = true;
                sendButton.disabled = true;
                const originalText = sendButton.innerHTML;
                sendButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

                // Optimistic UI: Show message immediately
                const optimisticMsg = {
                    id: 'temp-' + Date.now(),
                    message: text,
                    sender_type: 'customer',
                    created_at: new Date().toISOString()
                };

                const el = createMessageElement(optimisticMsg);
                if (el) {
                    const emptyState = messagesContainer.querySelector('.empty-state');
                    if (emptyState) emptyState.remove();
                    messagesContainer.appendChild(el);
                    requestAnimationFrame(() => {
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    });
                }

                fetch('{{ route('customer.chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message: text,
                        product_id: null
                    })
                })
                    .then(res => res.json().then(data => ({
                        ok: res.ok,
                        status: res.status,
                        data: data
                    })))
                    .then(({ ok, status, data }) => {
                        if (!ok) {
                            let errorMessage = data.message || 'Gửi tin nhắn thất bại, vui lòng thử lại.';
                            if (data.errors) {
                                errorMessage = Object.values(data.errors)[0];
                                if (Array.isArray(errorMessage)) {
                                    errorMessage = errorMessage[0];
                                }
                            }

                            showErrorAlert(errorMessage);
                            // Remove optimistic message on error
                            const optimisticEl = messagesContainer.querySelector(`[data-msg-id="${optimisticMsg.id}"]`);
                            if (optimisticEl) optimisticEl.remove();
                            throw new Error('SEND_FAILED');
                        }

                        if (data.success) {
                            messageInput.value = '';
                            // DON'T reset lastMessageId - just load incremental updates
                            // This is key for performance!
                            loadMessages(false);
                        } else {
                            showErrorAlert(data.message || 'Gửi tin nhắn thất bại, vui lòng thử lại.');
                            // Remove optimistic message on error
                            const optimisticEl = messagesContainer.querySelector(`[data-msg-id="${optimisticMsg.id}"]`);
                            if (optimisticEl) optimisticEl.remove();
                            throw new Error('SEND_FAILED');
                        }
                    })
                    .catch(error => {
                        if (error.message !== 'SEND_FAILED') {
                            console.error('Send error:', error);
                            // Remove optimistic message on network error
                            const optimisticEl = messagesContainer.querySelector(`[data-msg-id="${optimisticMsg.id}"]`);
                            if (optimisticEl) optimisticEl.remove();
                            showErrorAlert('Lỗi kết nối. Vui lòng kiểm tra lại và thử lại.');
                        }
                    })
                    .finally(() => {
                        isSending = false;
                        sendButton.disabled = false;
                        sendButton.innerHTML = originalText;
                    });
            }

            function showErrorAlert(message) {
                popup('error', 'Lỗi', message, {
                    confirmButtonColor: '#dc3545',
                    timer: 5000
                });
            }

            function formatDateTime(value) {
                try {
                    const d = new Date(value);
                    if (Number.isNaN(d.getTime())) return '';
                    return d.toLocaleString('vi-VN');
                } catch (e) {
                    return '';
                }
            }

            // Cleanup on page unload
            window.addEventListener('beforeunload', () => {
                if (pollInterval) clearInterval(pollInterval);
            });
        });
    </script>
@endsection