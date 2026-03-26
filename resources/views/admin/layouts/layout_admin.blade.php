<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>DASHBOARD- SEN HỒNG OCOP</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{ asset('backend/lib/owlcarousel/assets/owl.carousel.min.css') }}" rel="stylesheet">
    <link href="{{ asset('backend/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css') }}" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{ asset('backend/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Template Stylesheet -->
    <link href="{{ asset('backend/css/style.css') }}" rel="stylesheet">

    <style>
        /* Unified list action buttons across admin lists */
        .content .btn.btn-sm,
        .content .btn-group .btn.btn-sm {
            font-weight: 600;
        }

        .content .btn-outline-primary,
        .content .btn-outline-info,
        .content .btn-outline-success,
        .content .btn-outline-warning,
        .content .btn-outline-danger,
        .content .btn-outline-secondary {
            color: #fff;
        }

        .content .btn-outline-primary,
        .content .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .content .btn-outline-info,
        .content .btn-info {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            color: #fff;
        }

        .content .btn-outline-success,
        .content .btn-success {
            background-color: #198754;
            border-color: #198754;
        }

        .content .btn-outline-warning,
        .content .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .content .btn-outline-danger,
        .content .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .content .btn-outline-secondary,
        .content .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        /* Keep the same color on hover/focus for consistency */
        .content .btn-outline-primary:hover,
        .content .btn-primary:hover,
        .content .btn-outline-primary:focus,
        .content .btn-primary:focus {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        .content .btn-outline-info:hover,
        .content .btn-info:hover,
        .content .btn-outline-info:focus,
        .content .btn-info:focus {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            color: #fff;
        }

        .content .btn-outline-success:hover,
        .content .btn-success:hover,
        .content .btn-outline-success:focus,
        .content .btn-success:focus {
            background-color: #198754;
            border-color: #198754;
            color: #fff;
        }

        .content .btn-outline-warning:hover,
        .content .btn-warning:hover,
        .content .btn-outline-warning:focus,
        .content .btn-warning:focus {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .content .btn-outline-danger:hover,
        .content .btn-danger:hover,
        .content .btn-outline-danger:focus,
        .content .btn-danger:focus {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }

        .content .btn-outline-secondary:hover,
        .content .btn-secondary:hover,
        .content .btn-outline-secondary:focus,
        .content .btn-secondary:focus {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }

        .content .badge {
            font-size: 11px;
            font-weight: 600;
            border-radius: 999px;
            padding: 6px 10px;
        }
    </style>
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner"
            class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        {{-- Sidebar --}}
        @include('admin.layouts.sidebar')

        {{-- Content --}}
        <div class="content d-flex flex-column vh-100 overflow-auto">

            {{-- Navbar --}}
            @include('admin.layouts.navbar')

            {{-- Page Content --}}
            <main class="flex-grow-1">
                @include('admin.partials.alert')
                @yield('content')
            </main>
            <!-- Footer Start -->
            @include('admin.layouts.footer')

        </div>
        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="{{ asset('backend/lib/chart/chart.min.js') }}"></script>
    <script src="{{ asset('backend/lib/easing/easing.min.js') }}"></script>
    <script src="{{ asset('backend/lib/waypoints/waypoints.min.js') }}"></script>
    <script src="{{ asset('backend/lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('backend/lib/tempusdominus/js/moment.min.js') }}"></script>
    <script src="{{ asset('backend/lib/tempusdominus/js/moment-timezone.min.js') }}"></script>
    <script src="{{ asset('backend/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js') }}"></script>


    <!-- Template Javascript -->
    <script src="{{ asset('backend/js/main.js') }}"></script>
    @stack('scripts')
    <script>
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(el => el.remove());
        }, 5000);
    </script>

</body>

</html>