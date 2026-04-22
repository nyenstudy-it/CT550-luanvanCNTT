<!DOCTYPE html>

<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: Segoe UI;
        }

        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.15);
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25);
        }

        .alert {
            border-radius: 8px;
        }

        .invalid-feedback {
            color: #dc3545 !important;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>

</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5">

                <div class="card">
                    <div class="card-body p-4">

                        <h4 class="text-center mb-4 fw-bold text-success">
                            Đặt lại mật khẩu
                        </h4>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">

                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mật khẩu mới</label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    pattern="^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                                    title="Mật khẩu phải chứa tối thiểu 8 ký tự, bao gồm: chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*?&)"
                                    required>
                                @error('password')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted d-block mt-1">
                                    Yêu cầu: 8+ ký tự, chữ hoa, chữ thường, số, ký tự đặc biệt (@$!%*?&)
                                </small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Xác nhận mật khẩu</label>
                                <input type="password" name="password_confirmation"
                                    class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback d-block">
                                        <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <button class="btn btn-success w-100">
                                Đặt lại mật khẩu
                            </button>

                        </form>

                        <div class="text-center mt-3">

                            <a href="{{ route('login') }}" class="text-success text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập
                            </a>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Auto-hide success alert after 4 seconds
        setTimeout(() => {
            document.querySelectorAll(".alert-success").forEach(el => {
                el.remove();
            });
        }, 4000);
    </script>

</body>

</html>