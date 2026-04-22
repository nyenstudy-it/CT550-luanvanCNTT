<!-- Navbar Start -->
<?php
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
?>
<nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
    <a href="<?php echo e($homeRoute); ?>" class="navbar-brand d-flex d-lg-none me-4">
        <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
    </a>
    <a href="#" class="sidebar-toggler flex-shrink-0">
        <i class="fa fa-bars"></i>
    </a>

    

    <div class="navbar-nav align-items-center ms-auto">

        <?php if($canChat): ?>
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle position-relative" data-bs-toggle="dropdown">
                    <i class="fa fa-envelope me-lg-2"></i>
                    <?php if($chatUnreadCount > 0): ?>
                        <span class="admin-notification-badge"><?php echo e($chatUnreadCount); ?></span>
                    <?php endif; ?>
                    <span class="d-none d-lg-inline-flex">Tin nhắn</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                    <a href="#" class="dropdown-item js-open-admin-chat"
                        data-customer-id="<?php echo e($selectedCustomerFromQuery > 0 ? $selectedCustomerFromQuery : ''); ?>">
                        <div class="d-flex align-items-start">
                            <div class="ms-1">
                                <h6 class="fw-normal mb-0">Tin nhắn từ khách hàng</h6>
                                <small>
                                    <?php echo e($chatUnreadCount > 0
            ? 'Bạn có ' . $chatUnreadCount . ' tin nhắn chưa đọc'
            : 'Hiện không có tin nhắn chưa đọc'); ?>

                                </small>
                            </div>
                        </div>
                    </a>
                    <hr class="dropdown-divider">
                    <a href="#" class="dropdown-item text-center js-open-admin-chat"
                        data-customer-id="<?php echo e($selectedCustomerFromQuery > 0 ? $selectedCustomerFromQuery : ''); ?>">Mở
                        chatbox</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Notifications dropdown -->
        <div class="nav-item dropdown">
            <a class="nav-link position-relative dropdown-toggle" href="#" id="adminNotiToggle" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell me-lg-2"></i>
                <?php if($unreadCount > 0): ?>
                    <span class="admin-notification-badge"><?php echo e($unreadCount); ?></span>
                <?php endif; ?>
                <span class="d-none d-lg-inline-flex">Thông báo</span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0 admin-notification-dropdown admin-noti-pretty"
                aria-labelledby="adminNotiToggle">
                <li class="px-3 py-2">
                    <form method="POST" action="<?php echo e(route('admin.notifications.markAllRead')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100" <?php echo e($unreadCount > 0 ? '' : 'disabled'); ?>>
                            Đọc tất cả
                        </button>
                    </form>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $noti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php if($noti->type !== 'chat_customer_message'): ?>
                        <li>
                            <a href="<?php echo e(route('admin.notifications.read', $noti->id)); ?>"
                                class="dropdown-item admin-noti-item <?php echo e(!$noti->is_read ? 'unread' : ''); ?>"
                                data-notification-type="<?php echo e($noti->type); ?>"
                                data-read-url="<?php echo e(route('admin.notifications.read', $noti->id)); ?>">
                                <h6 class="fw-normal mb-1"><?php echo e($noti->title); ?></h6>
                                <small class="text-muted d-block"><?php echo e($noti->display_content); ?></small>
                                <small class="text-muted"><?php echo e($noti->created_at->diffForHumans()); ?></small>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <li><span class="dropdown-item text-center">Không có thông báo</span></li>
                <?php endif; ?>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a href="<?php echo e(route('admin.notifications')); ?>" class="dropdown-item text-center view-all">
                        Xem tất cả thông báo
                    </a>
                </li>
            </ul>
        </div>

        <!-- User dropdown -->
        <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img src="<?php echo e(Auth::user()->avatar
    ? asset('storage/' . Auth::user()->avatar)
    : asset('img/user.jpg')); ?>" class="rounded-circle" style="width: 40px; height: 40px;" alt="Avatar">

                <span class="d-none d-lg-inline-flex"><?php echo e(Auth::user()->name); ?></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                <a href="<?php echo e(route('profile.show')); ?>" class="dropdown-item">Thông tin cá nhân</a>
                <a href="#" class="dropdown-item">Cài đặt</a>
                <a href="<?php echo e(route('admin.logout')); ?>" class="dropdown-item">Đăng xuất</a>
            </div>
        </div>

    </div>
</nav>

<?php if($canChat): ?>
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
                        <div class="col-lg-8 d-flex flex-column" style="max-height: 68vh;">
                            <div class="p-3 border-bottom" style="flex-shrink: 0;">
                                <strong id="admin-chat-customer-name">Chưa chọn hội thoại</strong>
                            </div>
                            <div id="admin-chat-messages" class="overflow-auto p-3 bg-light"
                                style="height: auto; max-height: 380px; min-height: 330px; flex-shrink: 0;">
                                <div class="text-muted text-center mt-4">Chọn khách hàng để xem tin nhắn</div>
                            </div>
                            <div class="p-3 border-top" style="flex-shrink: 0;">
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
            let lastMessageId = null;  // Track last message ID for incremental loading
            let pollTimer = null;
            let isLoadingConversation = false;  // Prevent duplicate requests

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

            let adminMessageCache = new Set();  // Track message IDs to prevent duplicates

            function appendMessages(messages) {
                if (!Array.isArray(messages) || messages.length === 0) {
                    return;
                }

                // Create messages incrementally (append only new, non-duplicate messages)
                const fragment = document.createDocumentFragment();
                let newCount = 0;

                messages.forEach(function (msg) {
                    if (!msg.id || adminMessageCache.has(msg.id)) {
                        return;  // Skip duplicates
                    }

                    adminMessageCache.add(msg.id);
                    const mine = msg.sender_type !== 'customer';
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start');
                    messageDiv.setAttribute('data-msg-id', msg.id);
                    messageDiv.innerHTML = [
                        '<div class="p-2 rounded ' + (mine ? 'bg-primary text-white' : 'bg-white border') + '" style="max-width: 78%;">',
                        '<div>' + escapeHtml(msg.message || '') + '</div>',
                        '<small class="d-block mt-1 ' + (mine ? 'text-white-50' : 'text-muted') + '">' + formatTime(msg.created_at) + '</small>',
                        '</div>'
                    ].join('');
                    fragment.appendChild(messageDiv);
                    newCount++;
                });

                if (newCount > 0) {
                    // Remove placeholder if exists
                    const placeholder = messagesEl.querySelector('.text-muted');
                    if (placeholder) {
                        placeholder.remove();
                    }

                    messagesEl.appendChild(fragment);

                    // Only auto-scroll if user was already at bottom (within 100px)
                    const isAtBottom = messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 100;
                    if (isAtBottom) {
                        messagesEl.scrollTop = messagesEl.scrollHeight;
                    }
                }

                // Track last message ID for next incremental load
                if (messages.length > 0) {
                    lastMessageId = messages[messages.length - 1].id;
                }
            }

            function renderMessages(messages) {
                if (!Array.isArray(messages) || messages.length === 0) {
                    messagesEl.innerHTML = '<div class="text-muted text-center mt-4">Chưa có tin nhắn</div>';
                    lastMessagesHash = '';
                    lastMessageId = null;
                    adminMessageCache.clear();
                    return;
                }

                const hash = simpleHash(messages);
                if (hash === lastMessagesHash) {
                    return;
                }
                lastMessagesHash = hash;

                // Clear container
                messagesEl.innerHTML = '';
                adminMessageCache.clear();

                // Render messages in chunks to avoid massive reflow
                // Process 25 messages at a time
                const chunkSize = 25;
                let chunkIndex = 0;

                function renderChunk() {
                    const start = chunkIndex * chunkSize;
                    const end = Math.min(start + chunkSize, messages.length);
                    const fragment = document.createDocumentFragment();

                    for (let i = start; i < end; i++) {
                        const msg = messages[i];
                        if (!msg.id) continue;

                        adminMessageCache.add(msg.id);
                        const mine = msg.sender_type !== 'customer';

                        const div = document.createElement('div');
                        div.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start');
                        div.setAttribute('data-msg-id', msg.id);
                        div.innerHTML = [
                            '<div class="p-2 rounded ' + (mine ? 'bg-primary text-white' : 'bg-white border') + '" style="max-width: 78%;">',
                            '<div>' + escapeHtml(msg.message || '') + '</div>',
                            '<small class="d-block mt-1 ' + (mine ? 'text-white-50' : 'text-muted') + '">' + formatTime(msg.created_at) + '</small>',
                            '</div>'
                        ].join('');
                        fragment.appendChild(div);
                    }

                    messagesEl.appendChild(fragment);

                    // Process next chunk
                    chunkIndex++;
                    if (chunkIndex * chunkSize < messages.length) {
                        // Schedule next chunk render
                        requestAnimationFrame(renderChunk);
                    } else {
                        // All chunks done - scroll to bottom
                        messagesEl.scrollTop = messagesEl.scrollHeight;
                    }
                }

                // Start rendering
                renderChunk();

                // Track last message ID
                if (messages.length > 0) {
                    lastMessageId = messages[messages.length - 1].id;
                }
            }

            function loadConversations(preferredCustomerId) {
                fetch('<?php echo e(route('admin.chats.list')); ?>', {
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
                            loadConversation(activeCustomerId, true);
                        }
                    })
                    .catch(function () {
                        listEl.innerHTML = '<div class="p-3 text-danger text-center">Không tải được hội thoại</div>';
                    });
            }

            function loadConversation(customerId, isInitial) {
                activeCustomerId = Number(customerId);

                // Prevent duplicate requests
                if (isLoadingConversation) {
                    return;
                }
                isLoadingConversation = true;

                const selected = conversationData.find(function (item) {
                    return Number(item.customer_id) === activeCustomerId;
                });
                if (selected) {
                    nameEl.textContent = selected.customer_name || 'Khách hàng';
                }

                // Re-render list immediately so active highlight matches the opened conversation.
                renderConversationList(conversationData);

                // Build query with incremental loading support
                let url = '/admin/chats/' + activeCustomerId;
                if (!isInitial && lastMessageId) {
                    url += '?from_id=' + lastMessageId;
                }

                fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function (messages) {
                        if (isInitial || !lastMessageId) {
                            // Initial load: render all
                            renderMessages(messages);
                        } else {
                            // Incremental load: append new messages
                            appendMessages(messages);
                        }
                        updateNavbarBadge();
                    })
                    .catch(function (err) {
                        console.error('Error loading conversation:', err);
                        if (!isInitial) {
                            // On incremental load errors, just silently skip (network might be slow)
                            return;
                        }
                        messagesEl.innerHTML = '<div class="text-danger text-center mt-4">Không tải được tin nhắn</div>';
                    })
                    .finally(function () {
                        isLoadingConversation = false;
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
                const originalText = sendBtn.textContent;
                sendBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

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
                            return response.json().then(data => {
                                throw new Error(data.message || 'Gửi thất bại');
                            });
                        }
                        return response.json();
                    })
                    .then(function () {
                        replyEl.value = '';
                        // Use incremental loading - just load new messages since lastMessageId
                        // This is MUCH faster than full reload
                        setTimeout(function () {
                            loadConversation(activeCustomerId, false);
                        }, 200);  // Small delay to ensure message is saved to DB
                    })
                    .catch(function (err) {
                        console.error('Error sending reply:', err);
                        alert('Lỗi gửi tin nhắn: ' + err.message);
                    })
                    .finally(function () {
                        sendBtn.disabled = false;
                        sendBtn.textContent = originalText;
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
                lastMessageId = null;  // Reset for new conversation
                loadConversation(customerId, true);
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

                // ⚡ ULTRA-OPTIMIZED POLLING:
                // - Base interval: 3s (good balance between real-time and performance)
                // - Conversation list: only refresh on open, and every 30s max
                // - This reduces DB load significantly
                let lastConversationPoll = Date.now();
                const conversationPollInterval = 30000;  // 30 seconds (reduced from 15)
                let pollCount = 0;

                pollTimer = setInterval(function () {
                    if (activeCustomerId) {
                        // Poll active conversation
                        loadConversation(activeCustomerId, false);
                    }

                    pollCount++;
                    // Refresh conversation list very infrequently (every 10 polls = 30 seconds)
                    const now = Date.now();
                    if (now - lastConversationPoll > conversationPollInterval) {
                        loadConversations(activeCustomerId);
                        lastConversationPoll = now;
                    }
                }, 3000);  // Increased from 2500ms to 3000ms - still feels real-time with better performance
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
                updateNavbarBadge();
            });

            if (<?php echo json_encode($openAdminChatOnLoad, 15, 512) ?>) {
                modal.show();
                activeCustomerId = <?php echo json_encode($selectedCustomerFromQuery > 0 ? $selectedCustomerFromQuery : null, 15, 512) ?>;
                loadConversations(activeCustomerId);
            }
        });
    </script>
<?php endif; ?>
<!-- Navbar End --><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/layouts/navbar.blade.php ENDPATH**/ ?>