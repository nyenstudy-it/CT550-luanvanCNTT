@extends('admin.layouts.layout_admin')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">
        <div class="mb-4">
            <h5 class="mb-1">Thêm nhân viên</h5>
            <small class="text-muted">Tạo hồ sơ nhân viên mới, kèm thông tin tài khoản, công việc và mức lương theo
                giờ.</small>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data">
            @csrf
            @php($submitLabel = 'Thêm nhân viên')
            @include('admin.staff._form')
        </form>
    </div>
</div>
@endsection