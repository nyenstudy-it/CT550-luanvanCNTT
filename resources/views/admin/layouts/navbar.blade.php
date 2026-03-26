<!-- Navbar Start -->
@php
    $authUser = Auth::user();
    $isAdmin = $authUser->role === 'admin';
    $isStaff = $authUser->role === 'staff';
    $position = $isStaff ? ($authUser->staff?->position ?? null) : null;
    $canReports = $isAdmin || ($isStaff && $position === 'cashier');
    $canWarehouse = $isAdmin || ($isStaff && $position === 'warehouse');
    $canOrders = $isAdmin || ($isStaff && in_array($position, ['cashier', 'order_staff'], true));
    $canContent = $isAdmin || ($isStaff && $position === 'order_staff');
    $homeRoute = $canReports
        ? route('admin.dashboard')
        : ($canWarehouse
            ? route('admin.inventories.list')
            : ($canOrders
                ? route('admin.orders')
                : ($canContent
                    ? route('admin.reviews')
                    : route('profile.show'))));
@endphp
<nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
    <a href="{{ $homeRoute }}" class="navbar-brand d-flex d-lg-none me-4">
        <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
    </a>
    <a href="#" class="sidebar-toggler flex-shrink-0">
        <i class="fa fa-bars"></i>
    </a>

    <form class="d-none d-md-flex ms-4">
        <input class="form-control border-0" type="search" placeholder="Search">
    </form>

    <div class="navbar-nav align-items-center ms-auto">

        <!-- Messages dropdown (tùy chỉnh nếu cần) -->
        <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fa fa-envelope me-lg-2"></i>
                <span class="d-none d-lg-inline-flex">Tin nhắn</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                <a href="#" class="dropdown-item">
                    <div class="d-flex align-items-center">
                        <img class="rounded-circle" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                        <div class="ms-2">
                            <h6 class="fw-normal mb-0">Jhon send you a message</h6>
                            <small>15 minutes ago</small>
                        </div>
                    </div>
                </a>
                <hr class="dropdown-divider">
                <a href="#" class="dropdown-item text-center">See all messages</a>
            </div>
        </div>

        <!-- Notifications dropdown -->
        <div class="nav-item dropdown">
            <a class="nav-link position-relative dropdown-toggle" href="#" id="adminNotiToggle" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell me-lg-2"></i>
                @if($unreadCount > 0)
                    <span class="admin-notification-badge">{{ $unreadCount }}</span>
                @endif
                <span class="d-none d-lg-inline-flex">Thông báo</span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0 admin-notification-dropdown"
                aria-labelledby="adminNotiToggle">
                @forelse($notifications as $noti)
                    <li>
                        <a href="{{ route('admin.notifications.read', $noti->id) }}" class="dropdown-item">
                            <h6 class="fw-normal mb-1">{{ $noti->title }}</h6>
                            <small class="text-muted d-block">{{ $noti->display_content }}</small>
                            <small class="text-muted">{{ $noti->created_at->diffForHumans() }}</small>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                @empty
                    <li><span class="dropdown-item text-center">Không có thông báo</span></li>
                @endforelse
                <li>
                    <a href="{{ route('admin.notifications') }}" class="dropdown-item text-center">Xem tất cả</a>
                </li>
            </ul>
        </div>

        <!-- User dropdown -->
        <div class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                <img src="{{ Auth::user()->avatar
    ? asset('storage/' . Auth::user()->avatar)
    : asset('img/user.jpg') }}" class="rounded-circle" style="width: 40px; height: 40px;" alt="Avatar">

                <span class="d-none d-lg-inline-flex">{{ Auth::user()->name }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end bg-light border-0 rounded-0 rounded-bottom m-0">
                <a href="{{ route('profile.show') }}" class="dropdown-item">Thông tin cá nhân</a>
                <a href="#" class="dropdown-item">Cài đặt</a>
                <a href="{{ route('admin.logout') }}" class="dropdown-item">Đăng xuất</a>
            </div>
        </div>

    </div>
</nav>
<!-- Navbar End -->