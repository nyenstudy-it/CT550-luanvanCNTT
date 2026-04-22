<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #f9f9f9;
        }

        .header {
            background: #7fad39;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .content {
            background: white;
            padding: 20px;
            border: 1px solid #ddd;
        }

        .reply-box {
            background: #f0f8ff;
            border-left: 4px solid #7fad39;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }

        .label {
            font-weight: bold;
            color: #7fad39;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>📨 Cửa hàng đã trả lời liên hệ của bạn</h2>
        </div>

        <div class="content">
            <p>Xin chào <strong>{{ $contact->name }}</strong>,</p>

            <p>Cửa hàng OCOP Sên Hồng đã gửi phản hồi cho liên hệ của bạn (ID: {{ $contact->id }}).</p>

            <div class="reply-box">
                <p class="label">☐ Liên hệ gốc của bạn:</p>
                <p>{{ nl2br(e($contact->message)) }}</p>
            </div>

            <div class="reply-box">
                <p class="label">✓ Phản hồi từ {{ $adminName }}:</p>
                <p>{{ nl2br(e($replyText)) }}</p>
            </div>

            <p>
                <strong>Thời gian trả lời:</strong> {{ now()->format('d/m/Y H:i') }}
            </p>

            <p>
                Nếu bạn có câu hỏi tiếp theo, vui lòng <a href="{{ url('/contact') }}">liên hệ lại</a> với chúng tôi
                hoặc sử dụng chức năng chat trực tiếp trên website.
            </p>

            <p>
                Cảm ơn bạn đã lựa chọn OCOP Sên Hồng!<br>
                <strong>Đội ngũ hỗ trợ khách hàng</strong>
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} OCOP Sên Hồng. Tất cả quyền được bảo lưu.</p>
            <p>Đây là email tự động, vui lòng không trả lời email này.</p>
        </div>
    </div>
</body>

</html>