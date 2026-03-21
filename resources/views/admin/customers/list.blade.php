@extends('admin.layouts.layout_admin')

@section('content')

    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            {{-- FILTER --}}
            <form method="GET" class="row g-3 mb-3">

                <div class="col-md-4">
                    <label>Tìm theo tên</label>
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control"
                        placeholder="Nhập tên khách hàng...">
                </div>

                <div class="col-md-3">
                    <label>Tài khoản</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả --</option>

                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            Hoạt động
                        </option>

                        <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>
                            Bị khóa
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Từ</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <label>Đến</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-primary w-100">
                        Lọc
                    </button>
                </div>

            </form>


            {{-- TITLE --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Danh sách khách hàng</h6>
            </div>


            {{-- TABLE --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>SĐT</th>
                            <th>Số đơn hàng</th>
                            <th>Ngày đăng ký</th>
                            <th>Tài khoản</th>
                            <th width="250">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($customers as $index => $customer)

                            <tr>

                                <td>
                                    {{ $customers->firstItem() + $index }}
                                </td>

                                <td>
                                    {{ $customer->user->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $customer->user->email ?? '-' }}
                                </td>

                                <td>
                                    {{ $customer->phone ?? '-' }}
                                </td>

                                <td>
                                    {{ $customer->orders_count ?? 0 }}
                                </td>

                                <td>
                                    {{ $customer->created_at->format('d/m/Y') }}
                                </td>

                                <td>

                                    @if ($customer->user->status === 'active')

                                        <span class="badge bg-success">
                                            Hoạt động
                                        </span>

                                    @else

                                        <span class="badge bg-danger">
                                            Bị khóa
                                        </span>

                                    @endif

                                </td>

                                <td>

                                    {{-- XEM --}}
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-info">
                                        Xem
                                    </a>


                                    {{-- KHÓA / MỞ --}}
                                    @if ($customer->user->status === 'active')

                                        <form method="POST" action="{{ route('admin.customers.lock', $customer->user_id) }}"
                                            class="d-inline" onsubmit="return confirm('Khóa khách hàng này?')">

                                            @csrf

                                            <button class="btn btn-sm btn-danger">
                                                Khóa
                                            </button>

                                        </form>

                                    @else

                                        <form method="POST" action="{{ route('admin.customers.unlock', $customer->user_id) }}"
                                            class="d-inline" onsubmit="return confirm('Mở khóa khách hàng này?')">

                                            @csrf

                                            <button class="btn btn-sm btn-success">
                                                Mở
                                            </button>

                                        </form>

                                    @endif


                                    {{-- XÓA --}}
                                    <form method="POST" action="{{ route('admin.customers.destroy', $customer->id) }}"
                                        class="d-inline" onsubmit="return confirm('Xóa khách hàng này?')">

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-sm btn-outline-danger">
                                            Xóa
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center">
                                    Chưa có khách hàng nào
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>
            </div>


            {{-- PAGINATION --}}
            <div class="d-flex justify-content-center mt-3 small">

                {{ $customers->onEachSide(1)
        ->appends(request()->query())
        ->links('pagination::bootstrap-5') }}

            </div>

        </div>

    </div>
@endsection