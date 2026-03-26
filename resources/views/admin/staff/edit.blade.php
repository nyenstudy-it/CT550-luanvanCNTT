@extends('admin.layouts.layout_admin')

@section('content')
<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4">
        <div class="mb-4">
            <h5 class="mb-1">Chỉnh sửa nhân viên</h5>
            <small class="text-muted">Cập nhật hồ sơ, mức lương và tình trạng làm việc của nhân viên trong cùng một màn
                hình.</small>
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

        <form method="POST" action="{{ route('admin.staff.update', $staff->user_id) }}" enctype="multipart/form-data">
            @csrf
            @php($submitLabel = 'Cập nhật nhân viên')
            @include('admin.staff._form')
        </form>
    </div>
</div>
@endsection