<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tài khoản</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .card {
            border-radius: 12px;
        }

        .toggle-link {
            cursor: pointer;
            color: #198754;
            font-weight: 600;
        }

        .toggle-link:hover {
            text-decoration: underline;
        }

        .form-container {
            display: none;
        }

        .form-container.active {
            display: block;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5">

                <div class="card shadow border-0">
                    <div class="card-body p-4">

                        {{-- ================= REGISTER FORM ================= --}}
                        <div id="registerForm" class="form-container active">
                            <h4 class="text-center mb-4 fw-bold text-success">
                                Tạo tài khoản
                            </h4>

                            <form method="POST" action="{{ route('register') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Họ và tên</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" name="address" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Mật khẩu</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Xác nhận mật khẩu</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-success w-100">
                                    Đăng ký
                                </button>
                            </form>

                            <div class="text-center mt-3">
                                <small>
                                    Đã có tài khoản?
                                    <span class="toggle-link" onclick="showLogin()">Đăng nhập</span>
                                </small>
                            </div>
                        </div>

                        {{-- ================= LOGIN FORM ================= --}}
                        <div id="loginForm" class="form-container">
                            <h4 class="text-center mb-4 fw-bold text-success">
                                Đăng nhập
                            </h4>

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Mật khẩu</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-success w-100">
                                    Đăng nhập
                                </button>
                            </form>

                            <div class="text-center mt-3">
                                <small>
                                    Chưa có tài khoản?
                                    <span class="toggle-link" onclick="showRegister()">Đăng ký</span>
                                </small>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function showLogin() {
            document.getElementById("registerForm").classList.remove("active");
            document.getElementById("loginForm").classList.add("active");
        }

        function showRegister() {
            document.getElementById("loginForm").classList.remove("active");
            document.getElementById("registerForm").classList.add("active");
        }
    </script>

</body>

</html>