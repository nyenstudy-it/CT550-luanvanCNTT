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
                    <h2>Chat voi cua hang</h2>
                    <div class="breadcrumb__option">
                        <a href="{{ route('pages.trangchu') }}">Trang chu</a>
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
                    <small class="text-muted">Thuong phan hoi trong vai phut</small>
                </div>
                <span class="badge bg-success">Online</span>
            </div>

            <div id="messages-container" class="customer-chat__messages">
                <div class="empty-state">
                    <i class="fa fa-comments"></i>
                    <p>Dang tai tin nhan...</p>
                </div>
            </div>

            <div class="customer-chat__composer">
                <textarea id="message-input" class="form-control" rows="2" placeholder="Nhap cau hoi cua ban ve san pham..."></textarea>
                <button id="send-button" class="btn btn-primary" type="button">
                    <i class="fa fa-paper-plane me-1"></i> Gui
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
    }

    .customer-chat__header {
        background: #fff;
        border-bottom: 1px solid #e7edf7;
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const messagesContainer = document.getElementById('messages-container');
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');

        loadMessages();

        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        setInterval(loadMessages, 4000);

        function loadMessages() {
            fetch('{{ route('customer.chat') }}')
                .then(res => res.json())
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        messagesContainer.innerHTML = '<div class="empty-state"><i class="fa fa-comments"></i><p>Ban chua co tin nhan nao.</p></div>';
                        return;
                    }

                    messagesContainer.innerHTML = data.map(msg => {
                        const senderName = msg.sender_type === 'customer'
                            ? 'Ban'
                            : (msg.staff?.name || 'Cua hang');

                        return `
                            <div class="msg-row ${msg.sender_type}">
                                <div class="msg-bubble">
                                    <div class="msg-name">${escapeHTML(senderName)}</div>
                                    <div>${escapeHTML(msg.message || '')}</div>
                                    <div class="msg-time">${formatDateTime(msg.created_at)}</div>
                                </div>
                            </div>
                        `;
                    }).join('');

                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                });
        }

        function sendMessage() {
            const text = messageInput.value.trim();
            if (!text) return;

            sendButton.disabled = true;
            sendButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

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
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        loadMessages();
                    }
                })
                .finally(() => {
                    sendButton.disabled = false;
                    sendButton.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Gui';
                });
        }

        function formatDateTime(value) {
            const d = new Date(value);
            if (Number.isNaN(d.getTime())) return '';
            return d.toLocaleString('vi-VN');
        }

        function escapeHTML(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }
    });
</script>
@endsection
