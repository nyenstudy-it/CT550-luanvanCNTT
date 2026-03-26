<!-- Sidebar Start -->
<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-light navbar-light">

        @php
            $authUser = Auth::user();
            $isAdmin = $authUser->role === 'admin';
            $isStaff = $authUser->role === 'staff';
            $position = $isStaff ? ($authUser->staff?->position ?? null) : null;

            $canPosition = fn(string ...$positions) => $isAdmin || ($isStaff && $position && in_array($position, $positions, true));

            $canAttendance = $isStaff;
            $canWarehouse = $canPosition('warehouse');
            $canOrders = $canPosition('cashier', 'order_staff');
            $canContent = $canPosition('order_staff');
            $canBlogs = $canPosition('cashier');
            $positionLabel = match ($position) {
                'cashier' => 'Thu ngân',
                'warehouse' => 'Nhân viên kho',
                'order_staff' => 'Xử lý đơn hàng',
                default => $isAdmin ? 'Quản trị viên' : ucfirst($authUser->role),
            };
        @endphp

        {{-- LOGO --}}
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand mx-4 mb-3">
            <h3 class="text-primary">DASHBOARD</h3>
        </a>

        {{-- USER INFO --}}
        <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img src="{{ $authUser->avatar ? asset('storage/' . $authUser->avatar) : asset('img/user.jpg') }}"
                    class="rounded-circle" style="width: 40px; height: 40px;" alt="Avatar">
                <div
                    class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1">
                </div>
            </div>
            <div class="ms-3">
                <a href="{{ route('profile.show') }}" class="text-decoration-none text-dark">
                    <h6 class="mb-0">{{ $authUser->name }}</h6>
                    <span class="text-muted small">{{ $positionLabel }}</span>
                </a>
            </div>
        </div>

        <div class="navbar-nav w-100">

            {{-- DASHBOARD --}}
            <a href="{{ route('admin.dashboard') }}"
                class="nav-item nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa fa-tachometer-alt me-2"></i>Dashboard
            </a>

            {{-- ADMIN ONLY: Nhân viên, Bảng lương --}}
            @if($isAdmin)
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-users me-2"></i>Nhân viên
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.staff.list') }}" class="dropdown-item">Danh sách nhân viên</a>
                        {{-- <a href="{{ route('admin.staff.create') }}" class="dropdown-item">Thêm nhân viên</a> --}}
                        <a href="{{ route('admin.attendances.index') }}" class="dropdown-item">Phân ca</a>
                    </div>
                </div>

                <a href="{{ route('admin.salaries.index') }}"
                    class="nav-item nav-link {{ request()->routeIs('admin.salaries.*') ? 'active' : '' }}">
                    <i class="fa fa-money-bill-wave me-2"></i>Bảng lương
                </a>
            @endif

            {{-- STAFF ONLY: Chấm công --}}
            @if($canAttendance)
                <a href="{{ route('staff.staff_attendances') }}"
                    class="nav-item nav-link {{ request()->routeIs('staff.staff_attendances') ? 'active' : '' }}">
                    <i class="fa fa-clock me-2"></i>Chấm công
                </a>
            @endif

            {{-- ADMIN + WAREHOUSE: Nhà phân phối, Danh mục, Sản phẩm, Kho --}}
            @if($canWarehouse)
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-truck me-2"></i>Nhà phân phối
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.suppliers.list') }}" class="dropdown-item">Danh sách nhà phân phối</a>
                        {{-- <a href="{{ route('admin.suppliers.create') }}" class="dropdown-item">Thêm nhà phân phối</a>
                        --}}
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-list me-2"></i>Danh mục
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.categories.list') }}" class="dropdown-item">Danh sách danh mục</a>
                        {{-- <a href="{{ route('admin.categories.create') }}" class="dropdown-item">Thêm danh mục</a> --}}
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-box me-2"></i>Sản phẩm
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.products.list') }}" class="dropdown-item">Danh sách sản phẩm</a>
                        {{-- <a href="{{ route('admin.products.create') }}" class="dropdown-item">Thêm sản phẩm</a> --}}
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-warehouse me-2"></i>Kho hàng
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.inventories.list') }}" class="dropdown-item">Tồn kho</a>
                        <a href="{{ route('admin.imports.create') }}" class="dropdown-item">Nhập kho</a>
                        <a href="{{ route('admin.imports.list') }}" class="dropdown-item">Phiếu nhập</a>

                    </div>
                </div>
            @endif

            {{-- ADMIN + CASHIER + ORDER_STAFF: Khách hàng, Đơn hàng --}}
            @if($canOrders)
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-user me-2"></i>Khách hàng
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.customers.list') }}" class="dropdown-item">Danh sách khách hàng</a>
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-shopping-cart me-2"></i>Đơn hàng
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.orders') }}" class="dropdown-item">Danh sách đơn hàng</a>
                    </div>
                </div>
            @endif

            {{-- ADMIN + ORDER_STAFF: Mã giảm giá, Đánh giá --}}
            @if($canContent)
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-ticket-alt me-2"></i>Mã giảm giá
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.discounts.index') }}" class="dropdown-item">Danh sách mã giảm giá</a>
                        {{-- <a href="{{ route('admin.discounts.create') }}" class="dropdown-item">Tạo mã mới</a> --}}
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-star me-2"></i>Đánh giá
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.reviews') }}" class="dropdown-item">Danh sách đánh giá</a>
                    </div>
                </div>

            @endif

            {{-- ADMIN + CASHIER: Blog --}}
            @if($canBlogs)

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-blog me-2"></i>Blog
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="{{ route('admin.blogs.index') }}" class="dropdown-item">Danh sách blog</a>
                        {{-- <a href="{{ route('admin.blogs.create') }}" class="dropdown-item">Thêm blog</a> --}}
                    </div>
                </div>
            @endif

        </div>
    </nav>
</div>
<!-- Sidebar End -->