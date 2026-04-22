<!-- Sidebar Start -->
<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-light navbar-light">

        <?php
            $authUser = Auth::user();
            $isAdmin = $authUser->role === 'admin';
            $isStaff = $authUser->role === 'staff';
            $position = $isStaff ? ($authUser->staff?->position ?? null) : null;

            $canPosition = fn(string ...$positions) => $isAdmin || ($isStaff && $position && in_array($position, $positions, true));

            $canAttendance = $isStaff;
            $canReports = $canPosition('cashier');
            $canWarehouse = $canPosition('warehouse');
            $canOrders = $canPosition('cashier', 'order_staff');
            $canContent = $canPosition('order_staff');
            $canBlogs = $canPosition('cashier');
            $canChat = $isAdmin || $isStaff;
            $homeRoute = $canReports
                ? route('admin.dashboard')
                : ($canWarehouse
                    ? route('admin.inventories.list')
                    : ($canOrders
                        ? route('admin.orders')
                        : ($canContent
                            ? route('admin.reviews')
                            : route('profile.show'))));
            $positionLabel = match ($position) {
                'cashier' => 'Thu ngân',
                'warehouse' => 'Nhân viên kho',
                'order_staff' => 'Xử lý đơn hàng',
                default => $isAdmin ? 'Quản trị viên' : ucfirst($authUser->role),
            };
        ?>

        
        <a href="<?php echo e($homeRoute); ?>" class="navbar-brand mx-4 mb-3">
            <h3 class="text-primary">DASHBOARD</h3>
        </a>

        
        <div class="d-flex align-items-center ms-4 mb-4">
            <div class="position-relative">
                <img src="<?php echo e($authUser->avatar ? asset('storage/' . $authUser->avatar) : asset('img/user.jpg')); ?>"
                    class="rounded-circle" style="width: 40px; height: 40px;" alt="Avatar">
                <div
                    class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1">
                </div>
            </div>
            <div class="ms-3">
                <a href="<?php echo e(route('profile.show')); ?>" class="text-decoration-none text-dark">
                    <h6 class="mb-0"><?php echo e($authUser->name); ?></h6>
                    <span class="text-muted small"><?php echo e($positionLabel); ?></span>
                </a>
            </div>
        </div>

        <div class="navbar-nav w-100">

            
            <?php if($canReports): ?>
                <a href="<?php echo e(route('admin.dashboard')); ?>"
                    class="nav-item nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                    <i class="fa fa-tachometer-alt me-2"></i>Thống kê
                </a>
            <?php endif; ?>

            <?php if($canReports): ?>
                <a href="<?php echo e(route('admin.revenue.stats')); ?>"
                    class="nav-item nav-link <?php echo e(request()->routeIs('admin.revenue.stats') ? 'active' : ''); ?>">
                    <i class="fa fa-chart-line me-2"></i>Doanh thu
                </a>
            <?php endif; ?>

            
            <?php if($isAdmin): ?>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-users me-2"></i>Nhân viên
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.staff.list')); ?>" class="dropdown-item">Danh sách nhân viên</a>
                        
                        <a href="<?php echo e(route('admin.attendances.index')); ?>" class="dropdown-item">Phân ca</a>
                    </div>
                </div>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-money-bill-wave me-2"></i>Bảng lương
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.salaries.index')); ?>" class="dropdown-item">
                            <i></i>Bảng lương nhân viên
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if($canAttendance): ?>
                <a href="<?php echo e(route('staff.staff_attendances')); ?>"
                    class="nav-item nav-link <?php echo e(request()->routeIs('staff.staff_attendances') ? 'active' : ''); ?>">
                    <i class="fa fa-clock me-2"></i>Chấm công
                </a>
            <?php endif; ?>

            
            <?php if($canWarehouse): ?>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-truck me-2"></i>Nhà phân phối
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.suppliers.list')); ?>" class="dropdown-item">Danh sách nhà phân phối</a>
                        
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-list me-2"></i>Danh mục
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.categories.list')); ?>" class="dropdown-item">Danh sách danh mục</a>
                        
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-box me-2"></i>Sản phẩm
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.products.list')); ?>" class="dropdown-item">Danh sách sản phẩm</a>
                        
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-warehouse me-2"></i>Kho hàng
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.inventories.list')); ?>" class="dropdown-item">Tồn kho</a>
                        <a href="<?php echo e(route('admin.imports.create')); ?>" class="dropdown-item">Nhập kho</a>
                        <a href="<?php echo e(route('admin.imports.list')); ?>" class="dropdown-item">Phiếu nhập</a>

                    </div>
                </div>
            <?php endif; ?>

            
            <?php if($canOrders): ?>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-user me-2"></i>Khách hàng
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.customers.list')); ?>" class="dropdown-item">Danh sách khách hàng</a>
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-shopping-cart me-2"></i>Đơn hàng
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.orders')); ?>" class="dropdown-item">Danh sách đơn hàng</a>
                    </div>
                </div>

            <?php endif; ?>

            
            <?php if($canContent): ?>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-ticket-alt me-2"></i>Mã giảm giá
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.discounts.index')); ?>" class="dropdown-item">Danh sách mã giảm giá</a>
                        
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-star me-2"></i>Đánh giá
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.reviews')); ?>" class="dropdown-item">Danh sách đánh giá</a>
                    </div>
                </div>

            <?php endif; ?>

            
            <?php if($canBlogs): ?>

                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fa fa-blog me-2"></i>Blog
                    </a>
                    <div class="dropdown-menu bg-transparent border-0">
                        <a href="<?php echo e(route('admin.blogs.index')); ?>" class="dropdown-item">Danh sách blog</a>
                        
                    </div>
                </div>
            <?php endif; ?>

            
            <?php if($canChat): ?>
                <a href="<?php echo e(route('admin.contacts.index')); ?>"
                    class="nav-item nav-link <?php echo e(request()->routeIs('admin.contacts.*') ? 'active' : ''); ?>">
                    <i class="fa fa-envelope me-2"></i>Liên hệ
                </a>
            <?php endif; ?>

        </div>
    </nav>
</div>
<!-- Sidebar End --><?php /**PATH C:\xampp\htdocs\luanvan\resources\views/admin/layouts/sidebar.blade.php ENDPATH**/ ?>