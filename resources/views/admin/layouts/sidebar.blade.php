<!-- Sidebar Start -->
<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-light navbar-light">
        <a href="{{ route('admin.dashboard') }}" class="navbar-brand mx-4 mb-3">
            <h3 class="text-primary">DASHBOARD</h3>
        </a>
        <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img src="{{ Auth::user()->avatar
                    ? asset('storage/' . Auth::user()->avatar)
                    : asset('img/user.jpg') }}" class="rounded-circle" style="width: 40px; height: 40px;" alt="Avatar">

                <div
                    class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1">
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
            <a href="{{ route('admin.dashboard') }}" class="nav-item nav-link active">
                <i class="fa fa-tachometer-alt me-2"></i>Dashboard
            </a>

            {{-- MENU ADMIN --}}
            @if(Auth::user()->role === 'admin')
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
                        <a href="{{ route('admin.staff.attendances') }}" class="dropdown-item">
                            Phân ca
                        </a>
                    </div>
                </div>
            @endif

            {{-- MENU STAFF --}}
            @if(Auth::user()->role === 'staff')
                <a href="{{ route('staff.staff_attendances') }}" class="nav-item nav-link">
                    <i class="fa fa-clock me-2"></i>
                    Chấm công
                </a>
            @endif

            {{-- Nhà phân phối --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-laptop me-2"></i>Nhà phân phối
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

            {{-- DANH MỤC (ADMIN + STAFF) --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa fa-laptop me-2"></i>Danh mục
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
        </div>

    </nav>
</div>
<!-- Sidebar End -->