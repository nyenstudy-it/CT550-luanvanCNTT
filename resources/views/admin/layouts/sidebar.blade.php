<!-- Sidebar Start -->
<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-light navbar-light">

        {{-- LOGO --}}
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand mx-4 mb-3">
            <h3 class="text-primary">DASHBOARD</h3>
        </a>

        {{-- USER INFO --}}
        <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img src="{{ Auth::user()->avatar
    ? asset('storage/' . Auth::user()->avatar)
    : asset('img/user.jpg') }}" class="rounded-circle" style="width: 40px; height: 40px;" alt="Avatar">

                <div class="bg-success rounded-circle border border-2 border-white 
                            position-absolute end-0 bottom-0 p-1">
                </div>
            </div>
            <div class="ms-3">
                <a href="{{ route('profile.show') }}" class="text-decoration-none text-dark">
                    <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                    <span class="text-muted">{{ Auth::user()->role }}</span>
                </a>
            </div>
        </div>

        <div class="navbar-nav w-100">

            {{-- DASHBOARD --}}
            <a href="{{ route('admin.dashboard') }}"
                class="nav-item nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa fa-tachometer-alt me-2"></i>Dashboard
            </a>

            {{-- ================= ADMIN MENU ================= --}}
            @if(Auth::user()->role === 'admin')

                {{-- NHÂN VIÊN --}}
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-users me-2"></i>Nhân viên
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.staff.list') }}" class="dropdown-item">
                            Danh sách nhân viên
                        </a>
                        <a href="{{ route('admin.staff.create') }}" class="dropdown-item">
                            Thêm nhân viên
                        </a>
                        <a href="{{ route('admin.attendances.index') }}" class="dropdown-item">
                            Phân ca
                        </a>
                    </div>
                </div>

                {{-- BẢNG LƯƠNG --}}
                <a href="{{ route('admin.salaries.index') }}"
                    class="nav-item nav-link {{ request()->routeIs('admin.salaries.*') ? 'active' : '' }}">
                    <i class="fa fa-money-bill-wave me-2"></i>Bảng lương
                </a>

            @endif

            {{-- KHÁCH HÀNG --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-user me-2"></i>Khách hàng
                </a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="{{ route('admin.customers.list') }}" class="dropdown-item">
                        Danh sách khách hàng
                    </a>
                </div>
            </div>


            {{-- ================= STAFF MENU ================= --}}
            @if(Auth::user()->role === 'staff')

                <a href="{{ route('staff.staff_attendances') }}"
                    class="nav-item nav-link {{ request()->routeIs('staff.staff_attendances') ? 'active' : '' }}">
                    <i class="fa fa-clock me-2"></i>Chấm công
                </a>

                {{-- <a href="{{ route('staff.salary') }}"
                    class="nav-item nav-link {{ request()->routeIs('staff.salary') ? 'active' : '' }}">
                    <i class="fa fa-money-bill-wave me-2"></i>Lương của tôi
                </a> --}}

            @endif

            {{-- ================= NHÀ PHÂN PHỐI ================= --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-truck me-2"></i>Nhà phân phối
                </a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="{{ route('admin.suppliers.list') }}" class="dropdown-item">
                        Danh sách nhà phân phối
                    </a>
                    <a href="{{ route('admin.suppliers.create') }}" class="dropdown-item">
                        Thêm nhà phân phối
                    </a>
                </div>
            </div>

            {{-- ================= DANH MỤC ================= --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-list me-2"></i>Danh mục
                </a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="{{ route('admin.categories.list') }}" class="dropdown-item">
                        Danh sách danh mục
                    </a>
                    <a href="{{ route('admin.categories.create') }}" class="dropdown-item">
                        Thêm danh mục
                    </a>
                </div>
            </div>

            {{-- ================= SẢN PHẨM ================= --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-box me-2"></i>Sản phẩm
                </a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="{{ route('admin.products.list') }}" class="dropdown-item">
                        Danh sách sản phẩm
                    </a>
                    <a href="{{ route('admin.products.create') }}" class="dropdown-item">
                        Thêm sản phẩm
                    </a>
                </div>
            </div>

            {{-- ================= KHO HÀNG ================= --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-warehouse me-2"></i>Kho hàng
                </a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="{{ route('admin.imports.create') }}" class="dropdown-item">
                        Nhập kho
                    </a>
                    <a href="{{ route('admin.imports.list') }}" class="dropdown-item">
                        Phiếu nhập
                    </a>
                    <a href="{{ route('admin.inventories.list') }}" class="dropdown-item">
                        Tồn kho
                    </a>
                </div>
            </div>


            {{-- ================= ĐƠN HÀNG ================= --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-shopping-cart me-2"></i>Đơn hàng
                </a>
                <div class="dropdown-menu bg-transparent border-0">
            
                    <a href="{{ route('admin.orders') }}" class="dropdown-item">
                        Danh sách đơn hàng
                    </a>
            
                </div>
            </div>

            {{-- MÃ GIẢM GIÁ --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-ticket-alt me-2"></i>Mã giảm giá
                </a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="{{ route('admin.discounts.index') }}" class="dropdown-item">
                        Danh sách mã giảm giá
                    </a>
                    <a href="{{ route('admin.discounts.create') }}" class="dropdown-item">
                        Tạo mã mới
                    </a>
                </div>
            </div>

            {{-- ================= BLOG ================= --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-blog me-2"></i>Blog
                </a>
                <div class="dropdown-menu bg-transparent border-0">
                    <a href="{{ route('admin.blogs.index') }}" class="dropdown-item">
                        Danh sách blog
                    </a>
                    <a href="{{ route('admin.blogs.create') }}" class="dropdown-item">
                        Thêm blog
                    </a>
                </div>
            </div>


        </div>
    </nav>
</div>
<!-- Sidebar End -->