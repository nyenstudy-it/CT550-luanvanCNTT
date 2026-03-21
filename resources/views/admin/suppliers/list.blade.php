@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">
{{-- 
            <form method="GET" action="{{ route('admin.suppliers.list') }}" class="row g-3 mb-3">

                <div class="col-md-3">
                    <label class="form-label">Tên nhà phân phối</label>
                    <input type="text" name="name" value="{{ request('name') }}" class="form-control" placeholder="Nhập tên NPP">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ request('phone') }}" class="form-control"
                        placeholder="Nhập số điện thoại">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="address" value="{{ request('address') }}" class="form-control"
                        placeholder="Nhập địa chỉ">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        Lọc
                    </button>

                    <a href="{{ route('admin.suppliers.list') }}" class="btn btn-secondary w-100">
                        Reset
                    </a>
                </div>

            </form> --}}

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Danh sách nhà phân phối</h6>
                <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary btn-sm">
                    + Thêm nhà phân phối
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Tên nhà phân phối</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Mô tả</th>
                            <th width="150">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($suppliers as $index => $supplier)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>{{ $supplier->address }}</td>
                                <td>{{ $supplier->description ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-warning">
                                        Sửa
                                    </a>

                                    <form action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Xóa nhà phân phối này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if ($suppliers->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center">
                                    Chưa có nhà phân phối nào
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {{ $suppliers->appends(request()->query())->links() }}

            </div>

        </div>
    </div>
@endsection