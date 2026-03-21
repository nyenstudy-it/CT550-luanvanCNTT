<!DOCTYPE html>

<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tài khoản</title>

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

        .form-control {
            border-radius: 8px;
            padding: 10px;
        }

        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.15);
        }

        .password-box {
            position: relative;
        }

        .eye-btn {
            position: absolute;
            right: 12px;
            top: 38px;
            border: none;
            background: none;
            font-size: 18px;
            color: #888;
            cursor: pointer;
        }

        .eye-btn:hover {
            color: #198754;
        }

        .alert {
            border-radius: 8px;
        }
    </style>

</head>

<body>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5">

                <div class="card">
                    <div class="card-body p-4">

                        {{-- ALERT --}}

                        @if(session('success'))

                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))

                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())

                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- LOGIN FORM --}}

                        <div id="loginForm" class="form-container active">

                            <h4 class="text-center mb-4 fw-bold text-success">
                                Đăng nhập
                            </h4>

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                                        required>
                                </div>

                                <div class="mb-4 password-box">

                                    <label class="form-label">Mật khẩu</label>

                                    <input type="password" name="password" id="loginPassword" class="form-control"
                                        required>

                                    <button type="button" class="eye-btn"
                                        onclick="togglePassword('loginPassword','eyeLogin')">

                                        <i id="eyeLogin" class="bi bi-eye"></i>

                                    </button>

                                </div>

                                <button type="submit" class="btn btn-success w-100">
                                    Đăng nhập
                                </button>

                            </form>

                            <div class="text-center mt-3">

                                <small>
                                    <a href="{{ route('password.request') }}" class="text-decoration-none text-success">
                                        Quên mật khẩu?
                                    </a>
                                </small>

                            </div>

                            <div class="text-center mt-3">

                                <small>
                                    Chưa có tài khoản?
                                    <span class="toggle-link" onclick="showRegister()">Đăng ký</span>
                                </small>

                            </div>

                        </div>

                        {{-- REGISTER FORM --}}

                        <div id="registerForm" class="form-container">

                            <h4 class="text-center mb-4 fw-bold text-success">
                                Tạo tài khoản
                            </h4>

                            <form method="POST" action="{{ route('register') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Họ và tên</label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-control"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <input type="text" name="address" value="{{ old('address') }}" class="form-control">
                                </div>

                                <div class="mb-3 password-box">

                                    <label class="form-label">Mật khẩu</label>

                                    <input type="password" name="password" id="registerPassword" class="form-control"
                                        required>

                                    <button type="button" class="eye-btn"
                                        onclick="togglePassword('registerPassword','eyeRegister')">

                                        <i id="eyeRegister" class="bi bi-eye"></i>

                                    </button>

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

        function togglePassword(inputId, iconId) {

            let input = document.getElementById(inputId);
            let icon = document.getElementById(iconId);

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            }

        }

        /* auto hide alert */

        setTimeout(() => {
            document.querySelectorAll(".alert").forEach(el => el.remove());
        }, 4000);

        /* giữ form register nếu lỗi */

        @if(old('name') || old('phone'))
            showRegister();
        @endif

    </script>

</body>

</html>