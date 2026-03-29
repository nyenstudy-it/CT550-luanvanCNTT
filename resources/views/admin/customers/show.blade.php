@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Chi tiet khach hang</h5>
                    <small class="text-muted">Thong tin duoc lay truc tiep tu ho so tai khoan va du lieu dat hang.</small>
                </div>
                <a href="{{ route('admin.customers.list') }}" class="btn btn-secondary btn-sm">Quay lai danh sach</a>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Ma khach hang</small>
                        <div class="fw-semibold">#{{ $customer->id }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Ho ten</small>
                        <div class="fw-semibold">{{ $customer->user->name ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Email</small>
                        <div class="fw-semibold">{{ $customer->user->email ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">So dien thoai</small>
                        <div class="fw-semibold">{{ $customer->phone ?? '-' }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">So don hang</small>
                        <div class="fw-semibold">{{ number_format($customer->orders_count ?? 0) }}</div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Trang thai tai khoan</small>
                        <div class="fw-semibold">
                            @if(($customer->user->status ?? 'active') === 'locked')
                                <span class="badge bg-danger">Bi khoa</span>
                            @else
                                <span class="badge bg-success">Hoat dong</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="border rounded bg-white p-3">
                        <small class="text-muted d-block mb-1">Dia chi day du</small>
                        <div class="fw-semibold">{{ $customer->full_address ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection