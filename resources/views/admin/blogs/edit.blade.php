@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="mb-0">Chỉnh sửa blog</h6>
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Quay lại</a>
            </div>
            <form action="{{ route('admin.blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Thông tin cơ bản blog -->
                <div class="mb-3">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" value="{{ old('title', $blog->title) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $blog->slug) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tóm tắt</label>
                    <textarea name="summary" class="form-control">{{ old('summary', $blog->summary) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nội dung chính</label>
                    <textarea name="content" class="form-control" required>{{ old('content', $blog->content) }}</textarea>
                </div>


                <div class="mb-3">
                    <label class="form-label">Hình ảnh đại diện</label>
                    @if($blog->image)
                        <img src="{{ asset('storage/' . $blog->image) }}" class="img-fluid mb-2" width="200">
                    @endif
                    <input type="file" name="image" class="form-control">
                </div>

                <hr>
                <h6>Blocks (Văn bản + Hình ảnh)</h6>
                <div id="blocks-container"></div>

                <button type="button" id="add-block" class="btn btn-secondary btn-sm mb-3">+ Thêm block</button>
                <br>
                <button type="submit" class="btn btn-primary">Cập nhật blog</button>
            </form>
        </div>
    </div>

    <script>
        let blockIndex = 0;
        const container = document.getElementById('blocks-container');

        // Load blocks cũ từ DB
        const existingBlocks = @json($blog->blocks);
        existingBlocks.forEach((blk) => {
            addBlock(blk.content ?? '', blk.image ?? '');
        });

        function addBlock(content = '', image = '') {
            const block = document.createElement('div');
            block.classList.add('mb-3', 'border', 'p-2');

            block.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Block #${blockIndex + 1}</strong>
                <button type="button" class="btn btn-danger btn-sm remove-block">Xóa</button>
            </div>
            <textarea name="blocks[${blockIndex}][content]" class="form-control mb-2" placeholder="Nội dung block (văn bản)">${content}</textarea>
            <input type="file" name="blocks[${blockIndex}][image]" class="form-control mb-2">
            ${image ? `<img src="{{ asset('storage') }}/${image}" class="img-fluid mb-2" width="150">` : ''}
            ${image ? `<input type="hidden" name="blocks[${blockIndex}][old_image]" value="${image}">` : ''}
        `;

            container.appendChild(block);

            block.querySelector('.remove-block').addEventListener('click', () => {
                block.remove();
            });

            blockIndex++;
        }

        // Thêm block mới
        document.getElementById('add-block').addEventListener('click', () => {
            addBlock();
        });
    </script>
@endsection