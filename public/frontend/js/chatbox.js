(function () {
    var root = document.getElementById("ai-chatbox");
    if (!root || !window.chatboxConfig) {
        return;
    }

    var toggle = document.getElementById("ai-chatbox-toggle");
    var panel = document.getElementById("ai-chatbox-panel");
    var closeBtn = document.getElementById("ai-chatbox-close");
    var form = document.getElementById("ai-chatbox-form");
    var input = document.getElementById("ai-chatbox-input");
    var messages = document.getElementById("ai-chatbox-messages");
    var suggestions = document.getElementById("ai-chatbox-suggestions");

    var storeToggle = document.getElementById("store-chatbox-toggle");
    var storePanel = document.getElementById("store-chatbox-panel");
    var storeCloseBtn = document.getElementById("store-chatbox-close");
    var storeForm = document.getElementById("store-chatbox-form");
    var storeInput = document.getElementById("store-chatbox-input");
    var storeMessages = document.getElementById("store-chatbox-messages");
    var storeBadge = document.getElementById("store-chatbox-badge");

    var history = [];
    var storeUnreadPollTimer = null;
    var storeUnreadRequestActive = false;
    var STORE_UNREAD_POLL_DELAY = 30000;

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function appendMessage(role, content) {
        var div = document.createElement("div");
        div.className =
            "ai-chatbox__message " +
            (role === "user"
                ? "ai-chatbox__message--user"
                : "ai-chatbox__message--assistant");
        div.innerHTML = escapeHtml(content);
        messages.appendChild(div);
        scrollToBottom();
    }

    function appendProducts(products) {
        if (!Array.isArray(products) || products.length === 0) {
            return;
        }

        var list = document.createElement("div");
        list.className = "ai-chatbox__products";

        products.forEach(function (product) {
            var card = document.createElement("article");
            card.className = "ai-chatbox__product";

            var price = product.price
                ? Number(product.price).toLocaleString("vi-VN") + " VND"
                : "Liên hệ";
            var stock = Number(product.stock || 0);
            var imageUrl = product.image_url || "";

            card.innerHTML = [
                '<a class="ai-chatbox__product-link" href="' +
                    escapeHtml(product.url || "#") +
                    '">',
                imageUrl
                    ? '<img class="ai-chatbox__product-image" src="' +
                      escapeHtml(imageUrl) +
                      '" alt="' +
                      escapeHtml(product.name || "Sản phẩm") +
                      '" loading="lazy">'
                    : "",
                '<span class="ai-chatbox__product-title">' +
                    escapeHtml(product.name || "Sản phẩm") +
                    "</span>",
                "</a>",
                '<div class="ai-chatbox__meta">Danh mục: ' +
                    escapeHtml(product.category || "Chưa rõ") +
                    "</div>",
                '<div class="ai-chatbox__meta">Giá: ' +
                    escapeHtml(price) +
                    " | Tồn: " +
                    escapeHtml(String(stock)) +
                    "</div>",
            ].join("");

            list.appendChild(card);
        });

        messages.appendChild(list);
        scrollToBottom();
    }

    function renderSuggestions(items) {
        if (!suggestions) {
            return;
        }

        suggestions.innerHTML = "";
        if (!Array.isArray(items) || items.length === 0) {
            return;
        }

        items.slice(0, 3).forEach(function (question) {
            if (!question) {
                return;
            }

            var button = document.createElement("button");
            button.type = "button";
            button.className = "ai-chatbox__suggestion";
            button.textContent = question;
            button.addEventListener("click", function () {
                sendMessage(question);
            });
            suggestions.appendChild(button);
        });
    }

    function setPanelOpen(isOpen) {
        panel.hidden = !isOpen;
        if (isOpen) {
            input.focus();
        }
    }

    function setStorePanelOpen(isOpen) {
        if (!storePanel) {
            return;
        }

        storePanel.hidden = !isOpen;
        if (isOpen) {
            stopStoreUnreadPolling();
            setPanelOpen(false);
            loadStoreMessages();
            updateStoreUnreadCount(0);
            if (storeInput) {
                storeInput.focus();
            }
            // Mark chat notifications as read when opening chat
            markChatNotificationsAsRead();
        } else {
            fetchStoreUnreadCount();
        }
    }

    function stopStoreUnreadPolling() {
        if (storeUnreadPollTimer) {
            window.clearTimeout(storeUnreadPollTimer);
            storeUnreadPollTimer = null;
        }
    }

    function scheduleStoreUnreadPolling(delay) {
        if (!window.chatboxConfig.storeUnreadEndpoint || !storePanel) {
            return;
        }

        stopStoreUnreadPolling();

        if (document.hidden || !storePanel.hidden) {
            return;
        }

        storeUnreadPollTimer = window.setTimeout(function () {
            fetchStoreUnreadCount();
        }, typeof delay === "number" ? delay : STORE_UNREAD_POLL_DELAY);
    }

    function markChatNotificationsAsRead() {
        const endpoint = window.chatboxConfig?.markChatNotificationsEndpoint;
        const csrfToken = window.chatboxConfig?.csrfToken;

        if (!endpoint || !csrfToken) {
            return;
        }

        fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        }).catch(function () {
            // Silently ignore if endpoint not available
        });
    }

    function updateStoreUnreadCount(count) {
        if (!storeBadge) {
            return;
        }

        var safeCount = Number(count || 0);
        storeBadge.textContent = safeCount;
        if (safeCount > 0) {
            storeBadge.classList.remove("d-none");
        } else {
            storeBadge.classList.add("d-none");
        }
    }

    function fetchStoreUnreadCount() {
        if (!window.chatboxConfig.storeUnreadEndpoint || !storePanel) {
            return;
        }

        if (document.hidden) {
            stopStoreUnreadPolling();
            return;
        }

        if (!storePanel.hidden) {
            updateStoreUnreadCount(0);
            return;
        }

        if (storeUnreadRequestActive) {
            return;
        }

        storeUnreadRequestActive = true;

        fetch(window.chatboxConfig.storeUnreadEndpoint, {
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Failed");
                }
                return response.json();
            })
            .then(function (data) {
                updateStoreUnreadCount(
                    data && data.unread_count ? data.unread_count : 0,
                );
            })
            .finally(function () {
                storeUnreadRequestActive = false;
                scheduleStoreUnreadPolling();
            })
            .catch(function () {
                // Keep current badge state when polling fails.
            });
    }

    toggle.addEventListener("click", function () {
        setPanelOpen(panel.hidden);
    });

    closeBtn.addEventListener("click", function () {
        setPanelOpen(false);
    });

    if (
        storeToggle &&
        storePanel &&
        storeCloseBtn &&
        storeForm &&
        storeInput &&
        storeMessages
    ) {
        storeToggle.addEventListener("click", function () {
            setStorePanelOpen(storePanel.hidden);
        });

        storeCloseBtn.addEventListener("click", function () {
            setStorePanelOpen(false);
        });

        storeForm.addEventListener("submit", function (event) {
            event.preventDefault();
            sendStoreMessage(storeInput.value);
        });

        setInterval(function () {
            if (!storePanel.hidden) {
                loadStoreMessages(false);
            }
        }, 4000);

        if (window.chatboxConfig.openStoreChatOnLoad) {
            setStorePanelOpen(true);
        } else {
            fetchStoreUnreadCount();
        }

        document.addEventListener("visibilitychange", function () {
            if (document.hidden) {
                stopStoreUnreadPolling();
                return;
            }

            if (storePanel.hidden) {
                fetchStoreUnreadCount();
            }
        });
    }

    function sendMessage(rawMessage) {
        var message = String(rawMessage || "").trim();
        if (!message) {
            return;
        }

        appendMessage("user", message);
        history.push({ role: "user", content: message });
        input.value = "";

        fetch(window.chatboxConfig.endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": window.chatboxConfig.csrfToken,
                Accept: "application/json",
            },
            body: JSON.stringify({
                message: message,
                history: history.slice(-6),
            }),
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Request failed");
                }

                return response.json();
            })
            .then(function (data) {
                var answer =
                    data && data.answer
                        ? data.answer
                        : "Xin lỗi, mình chưa thể phản hồi lúc này.";
                appendMessage("assistant", answer);
                history.push({ role: "assistant", content: answer });
                appendProducts(data.products || []);
                renderSuggestions(data.suggested_questions || []);
            })
            .catch(function () {
                appendMessage(
                    "assistant",
                    "Hệ thống đang bận, bạn vui lòng thử lại sau ít phút.",
                );
            });
    }

    form.addEventListener("submit", function (event) {
        event.preventDefault();
        sendMessage(input.value);
    });

    function appendStoreMessage(senderType, content, createdAt) {
        var div = document.createElement("div");
        div.className =
            "store-chatbox__message " +
            (senderType === "customer"
                ? "store-chatbox__message--customer"
                : "store-chatbox__message--staff");

        var sender = senderType === "customer" ? "Bạn" : "Cửa hàng";
        var timeText = createdAt
            ? new Date(createdAt).toLocaleString("vi-VN")
            : "";

        div.innerHTML =
            '<div class="store-chatbox__content">' +
            escapeHtml(content || "") +
            "</div>" +
            '<div class="store-chatbox__meta">' +
            escapeHtml(sender + (timeText ? " • " + timeText : "")) +
            "</div>";

        storeMessages.appendChild(div);
    }

    function loadStoreMessages(keepBottom) {
        if (!window.chatboxConfig.storeFetchEndpoint) {
            return;
        }

        fetch(window.chatboxConfig.storeFetchEndpoint, {
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Failed");
                }
                return response.json();
            })
            .then(function (data) {
                storeMessages.innerHTML = "";

                if (!Array.isArray(data) || data.length === 0) {
                    appendStoreMessage(
                        "staff",
                        "Xin chào, cửa hàng có thể hỗ trợ gì cho bạn?",
                        null,
                    );
                    return;
                }

                data.forEach(function (msg) {
                    appendStoreMessage(
                        msg.sender_type,
                        msg.message,
                        msg.created_at,
                    );
                });

                if (keepBottom !== false) {
                    storeMessages.scrollTop = storeMessages.scrollHeight;
                }
            })
            .catch(function () {
                storeMessages.innerHTML =
                    '<div class="store-chatbox__message store-chatbox__message--staff">Không thể tải tin nhắn lúc này.</div>';
            });
    }

    function sendStoreMessage(rawMessage) {
        var message = String(rawMessage || "").trim();
        if (!message || !window.chatboxConfig.storeSendEndpoint) {
            return;
        }

        fetch(window.chatboxConfig.storeSendEndpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": window.chatboxConfig.csrfToken,
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({
                message: message,
                product_id: null,
            }),
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Failed");
                }
                return response.json();
            })
            .then(function (data) {
                if (data && data.success) {
                    storeInput.value = "";
                    loadStoreMessages();
                    updateStoreUnreadCount(0);
                }
            })
            .catch(function () {
                appendStoreMessage(
                    "staff",
                    "Gửi tin nhắn thất bại, bạn vui lòng thử lại.",
                    null,
                );
            });
    }

    renderSuggestions(window.chatboxConfig.suggestedQuestions || []);
})();
