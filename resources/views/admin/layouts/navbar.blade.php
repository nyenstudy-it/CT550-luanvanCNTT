<!-- Navbar Start -->
@php
    $authUser = Auth::user();
    $isAdmin = $authUser->role === 'admin';
    $isStaff = $authUser->role === 'staff';
    $position = $isStaff ? ($authUser->staff?->position ?? null) : null;
    $canReports = $isAdmin || ($isStaff && $position === 'cashier');
    $canWarehouse = $isAdmin || ($isStaff && $position === 'warehouse');
    $canOrders = $isAdmin || ($isStaff && in_array($position, ['cashier', 'order_staff'], true));
    $canContent = $isAdmin || ($isStaff && $position === 'order_staff');
    $canChat = $isAdmin || $isStaff;
    $selectedCustomerFromQuery = (int) request()->query('customer', 0);
    $openAdminChatOnLoad = request()->boolean('open_admin_chat');
    $chatUnreadCount = $canChat
        ? \App\Models\CustomerMessage::query()
            ->where('sender_type', 'customer')
            ->where('is_read', false)
            ->count()
        : 0;
    $homeRoute = $canReports
        ? route('admin.dashboard')
        : ($canWarehouse
            ? route('admin.inventories.list')
            : ($canOrders
                ? route('admin.orders')
                : ($canContent
                    ? route('admin.reviews')
                    : route('profile.show'))));
@endphp
<nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
    <a href="{{ $homeRoute }}" class="navbar-brand d-flex d-lg-none me-4">
        <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
    </a>
    <a href="#" class="sidebar-toggler flex-shrink-0">
        <i class="fa fa-bars"></i>
    </a>

    <form class="d-none d-md-flex ms-4">
        <input class="form-control border-0" type="search" placeholder="Search">
    </form>

    <div class="navbar-nav align-items-center ms-auto">

        @if($canChat)
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle position-relative" data-bs-toggle="dropdown">
                    <i class="fa fa-envelope me-lg-2"></i>
                    @if($chatUnreadCount > 0)
                        <span class="admin-notification-badge">{{ $chatUnreadCount }}</span>
                    @endif
                    <span class="d-none d-lg-inline-flex">Tin nhắn</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                    <a href="#" class="dropdown-item js-open-admin-chat"
                        data-customer-id="{{ $selectedCustomerFromQuery > 0 ? $selectedCustomerFromQuery : '' }}">
                        <div class="d-flex align-items-start">
                            <div class="ms-1">
                                <h6 class="fw-normal mb-0">Tin nhắn từ khách hàng</h6>
                                <small>
                                    {{ $chatUnreadCount > 0
            ? 'Bạn có ' . $chatUnreadCount . ' tin nhắn chưa đọc'
            : 'Hiện không có tin nhắn chưa đọc' }}
                                </small>
                            </div>
                        </div>
                    </a>
                    <hr class="dropdown-divider">
                    <a href="#" class="dropdown-item text-center js-open-admin-chat"
                        data-customer-id="{{ $selectedCustomerFromQuery > 0 ? $selectedCustomerFromQuery : '' }}">Mở
                        chatbox</a>
                </div>
            </div>
        @endif

        <!-- Notifications dropdown -->
        <div class="nav-item dropdown">
            <a class="nav-link position-relative dropdown-toggle" href="#" id="adminNotiToggle" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell me-lg-2"></i>
                @if($unreadCount > 0)
                    <span class="admin-notification-badge">{{ $unreadCount }}</span>
                @endif
                <span class="d-none d-lg-inline-flex">Thông báo</span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0 admin-notification-dropdown"
                aria-labelledby="adminNotiToggle">
                <li class="px-3 py-2">
                    <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100" {{ $unreadCount > 0 ? '' : 'disabled' }}>
                            Đọc tất cả
                        </button>
                    </form>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                @forelse($notifications as $noti)
                    @if($noti->type !== 'chat_customer_message')
                        <li>
                            <a href="{{ route('admin.notifications.read', $noti->id) }}" class="dropdown-item"
                                data-notification-type="{{ $noti->type }}"
                                data-read-url="{{ route('admin.notifications.read', $noti->id) }}">
                                <h6 class="fw-normal mb-1">{{ $noti->title }}</h6>
                                <small class="text-muted d-block">{{ $noti->display_content }}</small>
                                <small class="text-muted">{{ $noti->created_at->diffForHumans() }}</small>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    @endif
                @empty
                    <li><span class="dropdown-item text-center">Không có thông báo</span></li>
                @endforelse
                <li>
                    <a href="{{ route('admin.notifications') }}" class="dropdown-item text-center">
                        Xem các thông báo trước đó
                    </a>
                </li>
            </ul>
        </div>

        <!-- User dropdown -->
        <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img src="{{ Auth::user()->avatar
    ? asset('storage/' . Auth::user()->avatar)
    : asset('img/user.jpg') }}" class="rounded-circle" style="width: 40px; height: 40px;" alt="Avatar">

                <span class="d-none d-lg-inline-flex">{{ Auth::user()->name }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                <a href="{{ route('profile.show') }}" class="dropdown-item">Thông tin cá nhân</a>
                <a href="#" class="dropdown-item">Cài đặt</a>
                <a href="{{ route('admin.logout') }}" class="dropdown-item">Đăng xuất</a>
            </div>
        </div>

    </div>
</nav>

@if($canChat)
    <div class="modal fade" id="adminChatModal" tabindex="-1" aria-labelledby="adminChatModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title" id="adminChatModalLabel">Chat khách hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0" style="height: 68vh; min-height: 520px;">
                        <div class="col-lg-4 border-end d-flex flex-column">
                            <div class="p-3 border-bottom">
                                <input id="admin-chat-search" type="text" class="form-control form-control-sm"
                                    placeholder="Tìm khách hàng...">
                            </div>
                            <div id="admin-chat-conversations" class="flex-grow-1 overflow-auto">
                                <div class="p-3 text-muted text-center">Đang tải hội thoại...</div>
                            </div>
                        </div>
                        <div class="col-lg-8 d-flex flex-column">
                            <div class="p-3 border-bottom">
                                <strong id="admin-chat-customer-name">Chưa chọn hội thoại</strong>
                            </div>
                            <div id="admin-chat-messages" class="flex-grow-1 p-3 bg-light overflow-auto">
                                <div class="text-muted text-center mt-4">Chọn khách hàng để xem tin nhắn</div>
                            </div>
                            <div class="p-3 border-top">
                                <div class="d-flex gap-2">
                                    <textarea id="admin-chat-reply" class="form-control" rows="2"
                                        placeholder="Nhập nội dung phản hồi..."></textarea>
                                    <button id="admin-chat-send" type="button" class="btn btn-primary">Gửi</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('adminChatModal');
            if (!modalEl) {
                return;
            }

            const modal = new bootstrap.Modal(modalEl);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const listEl = document.getElementById('admin-chat-conversations');
            const searchEl = document.getElementById('admin-chat-search');
            const messagesEl = document.getElementById('admin-chat-messages');
            const nameEl = document.getElementById('admin-chat-customer-name');
            const replyEl = document.getElementById('admin-chat-reply');
            const sendBtn = document.getElementById('admin-chat-send');

            let activeCustomerId = null;
            let conversationData = [];
            let lastConversationHash = null;
            let lastMessagesHash = null;
            let pollTimer = null;

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function formatTime(value) {
                if (!value) {
                    return '';
                }
                return new Date(value).toLocaleString('vi-VN');
            }

            function simpleHash(data) {
                return JSON.stringify(data).substring(0, 100);
            }

            function updateNavbarBadge() {
                const totalUnread = conversationData.reduce(function (sum, item) {
                    return sum + (item.unread_count || 0);
                }, 0);

                // Find the "Tin nhắn" dropdown (first one with fa-envelope icon)
                const dropdownToggle = document.querySelector('.nav-link.dropdown-toggle:has(.fa-envelope)');
                if (!dropdownToggle) {
                    return;
                }

                let badgeEl = dropdownToggle.querySelector('.admin-notification-badge');
                if (totalUnread > 0) {
                    if (!badgeEl) {
                        badgeEl = document.createElement('span');
                        badgeEl.className = 'admin-notification-badge';
                        dropdownToggle.appendChild(badgeEl);
                    }
                    badgeEl.textContent = totalUnread;
                } else {
                    if (badgeEl) {
                        badgeEl.remove();
                    }
                }

                // Update the text in dropdown
                const smallEl = dropdownToggle.parentElement.querySelector('.dropdown-item small');
                if (smallEl) {
                    smallEl.textContent = totalUnread > 0
                        ? 'Bạn có ' + totalUnread + ' tin nhắn chưa đọc'
                        : 'Hiện không có tin nhắn chưa đọc';
                }
            }

            function renderConversationList(items) {
                const keyword = (searchEl?.value || '').trim().toLowerCase();
                const filtered = items.filter(function (item) {
                    if (!keyword) {
                        return true;
                    }
                    return String(item.customer_name || '').toLowerCase().includes(keyword)
                        || String(item.last_message || '').toLowerCase().includes(keyword);
                });

                if (!filtered.length) {
                    listEl.innerHTML = '<div class="p-3 text-muted text-center">Không tìm thấy hội thoại</div>';
                    return;
                }

                // Include activeCustomerId in hash so when selection changes, we re-render
                const hash = simpleHash(filtered) + ':' + activeCustomerId;
                if (hash === lastConversationHash) {
                    return;
                }
                lastConversationHash = hash;

                listEl.innerHTML = filtered.map(function (item) {
                    const isActive = Number(activeCustomerId) === Number(item.customer_id);
                    return [
                        '<button type="button" class="list-group-item list-group-item-action border-0 border-bottom js-conversation-item ' + (isActive ? 'active' : '') + '" data-customer-id="' + item.customer_id + '">',
                        '<div class="d-flex justify-content-between align-items-start">',
                        '<div class="me-2">',
                        '<div class="fw-semibold">' + escapeHtml(item.customer_name || 'Khách hàng') + '</div>',
                        '<small class="text-muted d-block text-truncate" style="max-width: 210px;">' + escapeHtml(item.last_message || '') + '</small>',
                        '</div>',
                        '<div class="text-end">',
                        (item.unread_count > 0 ? '<span class="badge bg-danger rounded-pill">' + item.unread_count + '</span>' : ''),
                        '<small class="text-muted d-block">' + formatTime(item.last_at) + '</small>',
                        '</div>',
                        '</div>',
                        '</button>'
                    ].join('');
                }).join('');
            }

            function renderMessages(messages) {
                if (!Array.isArray(messages) || messages.length === 0) {
                    messagesEl.innerHTML = '<div class="text-muted text-center mt-4">Chưa có tin nhắn</div>';
                    lastMessagesHash = '';
                    return;
                }

                const hash = simpleHash(messages);
                if (hash === lastMessagesHash) {
                    return;
                }
                lastMessagesHash = hash;

                messagesEl.innerHTML = messages.map(function (msg) {
                    const mine = msg.sender_type !== 'customer';
                    return [
                        '<div class="d-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start') + '">',
                        '<div class="p-2 rounded ' + (mine ? 'bg-primary text-white' : 'bg-white border') + '" style="max-width: 78%;">',
                        '<div>' + escapeHtml(msg.message || '') + '</div>',
                        '<small class="d-block mt-1 ' + (mine ? 'text-white-50' : 'text-muted') + '">' + formatTime(msg.created_at) + '</small>',
                        '</div>',
                        '</div>'
                    ].join('');
                }).join('');

                messagesEl.scrollTop = messagesEl.scrollHeight;
            }

            function loadConversations(preferredCustomerId) {
                fetch('{{ route('admin.chats.list') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Failed');
                        }
                        return response.json();
                    })
                    .then(function (payload) {
                        conversationData = Array.isArray(payload.data) ? payload.data : [];
                        if (!activeCustomerId && preferredCustomerId) {
                            activeCustomerId = Number(preferredCustomerId);
                        }
                        if (!activeCustomerId && conversationData.length > 0) {
                            activeCustomerId = conversationData[0].customer_id;
                        }
                        renderConversationList(conversationData);
                        updateNavbarBadge();
                        if (activeCustomerId) {
                            loadConversation(activeCustomerId);
                        }
                    })
                    .catch(function () {
                        listEl.innerHTML = '<div class="p-3 text-danger text-center">Không tải được hội thoại</div>';
                    });
            }

            function loadConversation(customerId) {
                activeCustomerId = Number(customerId);
                const selected = conversationData.find(function (item) {
                    return Number(item.customer_id) === activeCustomerId;
                });
                if (selected) {
                    nameEl.textContent = selected.customer_name || 'Khách hàng';
                }

                // Re-render list immediately so active highlight matches the opened conversation.
                renderConversationList(conversationData);

                fetch('/admin/chats/' + activeCustomerId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Failed');
                        }
                        return response.json();
                    })
                    .then(function (messages) {
                        renderMessages(messages);
                        updateNavbarBadge();
                    })
                    .catch(function () {
                        messagesEl.innerHTML = '<div class="text-danger text-center mt-4">Không tải được tin nhắn</div>';
                    });
            }

            function sendReply() {
                if (!activeCustomerId) {
                    return;
                }

                const reply = (replyEl.value || '').trim();
                if (!reply) {
                    return;
                }

                sendBtn.disabled = true;
                fetch('/admin/chats/' + activeCustomerId + '/reply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ reply: reply })
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Failed');
                        }
                        return response.json();
                    })
                    .then(function () {
                        replyEl.value = '';
                        loadConversations(activeCustomerId);
                        loadConversation(activeCustomerId);
                    })
                    .finally(function () {
                        sendBtn.disabled = false;
                    });
            }

            document.querySelectorAll('.js-open-admin-chat').forEach(function (el) {
                el.addEventListener('click', function (event) {
                    event.preventDefault();
                    const customerId = this.dataset.customerId ? Number(this.dataset.customerId) : null;
                    const readUrl = this.dataset.readUrl;

                    if (readUrl) {
                        fetch(readUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        }).catch(function () {
                            // Ignore silently.
                        });
                    }

                    modal.show();
                    activeCustomerId = customerId || activeCustomerId;
                    loadConversations(activeCustomerId);
                });
            });

            listEl.addEventListener('click', function (event) {
                const item = event.target.closest('.js-conversation-item');
                if (!item) {
                    return;
                }
                const customerId = item.dataset.customerId;
                loadConversation(customerId);
            });

            if (searchEl) {
                searchEl.addEventListener('input', function () {
                    renderConversationList(conversationData);
                });
            }

            sendBtn.addEventListener('click', sendReply);
            replyEl.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    sendReply();
                }
            });

            modalEl.addEventListener('shown.bs.modal', function () {
                loadConversations(activeCustomerId);
                if (pollTimer) {
                    clearInterval(pollTimer);
                }
                pollTimer = setInterval(function () {
                    if (activeCustomerId) {
                        loadConversation(activeCustomerId);
                    } else {
                        loadConversations();
                    }
                }, 8000);
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
                updateNavbarBadge();
            });

            if (@json($openAdminChatOnLoad)) {
                modal.show();
                activeCustomerId = @json($selectedCustomerFromQuery > 0 ? $selectedCustomerFromQuery : null);
                loadConversations(activeCustomerId);
            }
        });
    </script>
@endif
<!-- Navbar End -->