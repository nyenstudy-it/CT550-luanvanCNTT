@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <!-- Header với nút Quay lại -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="mb-0">Tạo blog mới</h6>
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Quay lại</a>
            </div>

            <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Thông tin cơ bản blog -->
                <div class="mb-3">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tóm tắt</label>
                    <textarea name="summary" class="form-control">{{ old('summary') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nội dung chính</label>
                    <textarea name="content" class="form-control" required>{{ old('content') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Hình ảnh đại diện</label>
                    <input type="file" name="image" class="form-control">
                </div>

                <hr>

                <!-- Blocks -->
                <h6>Blocks (Nội dung / Hình ảnh)</h6>
                <div id="blocks-container"></div>
                <button type="button" id="add-block" class="btn btn-secondary btn-sm mb-3">+ Thêm block</button>
                <br>
                <button type="submit" class="btn btn-primary">Tạo blog</button>
            </form>
        </div>
    </div>

    <script>
        let blockIndex = 0;
        const container = document.getElementById('blocks-container');

        // Thêm block mới
        document.getElementById('add-block').addEventListener('click', () => {
            const block = document.createElement('div');
            block.classList.add('mb-3', 'border', 'p-2');

            block.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Block #${blockIndex + 1}</strong>
                <button type="button" class="btn btn-danger btn-sm remove-block">Xóa</button>
            </div>
            <textarea name="blocks[${blockIndex}][content]" class="form-control mb-2" placeholder="Nội dung block (văn bản)"></textarea>
            <input type="file" name="blocks[${blockIndex}][image]" class="form-control mb-2">
        `;

            container.appendChild(block);

            block.querySelector('.remove-block').addEventListener('click', () => block.remove());

            blockIndex++;
        });
    </script>
@endsection