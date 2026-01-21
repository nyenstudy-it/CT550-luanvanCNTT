@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <h6 class="mb-4">Thêm Nhà Phân Phối</h6>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.suppliers.store') }}">
                @csrf

                {{-- Tên nhà phân phối --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Tên nhà phân phối</label>
                    <div class="col-sm-10">
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>

                {{-- Email --}}
                {{-- <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                        <input type="email" name="email" class="form-control">
                    </div>
                </div> --}}

                {{-- Số điện thoại --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Số điện thoại</label>
                    <div class="col-sm-10">
                        <input type="text" name="phone" class="form-control">
                    </div>
                </div>

                {{-- Địa chỉ --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Địa chỉ</label>
                    <div class="col-sm-10">
                        <input type="text" name="address" class="form-control">
                    </div>
                </div>

                {{-- Mô tả --}}
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label">Mô tả</label>
                    <div class="col-sm-10">
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    Thêm nhà phân phối
                </button>
            </form>
        </div>
    </div>
@endsection