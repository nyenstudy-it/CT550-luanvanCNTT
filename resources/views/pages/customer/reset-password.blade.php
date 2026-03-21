<!DOCTYPE html>

<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
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

                        @if ($errors->any())

                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">

                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label>Mật khẩu mới</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-4">
                                <label>Xác nhận mật khẩu</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>

                            <button class="btn btn-success w-100">
                                Đặt lại mật khẩu
                            </button>

                        </form>

                        <div class="text-center mt-3">

                            <a href="{{ route('login') }}" class="text-success">
                                Quay lại đăng nhập
                            </a>

                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>