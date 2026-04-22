@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">Danh sách liên hệ</h5>
                </div>
                <a href="{{ route('admin.contacts.statistics') }}" class="btn btn-sm btn-info">Thống kê</a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Tổng liên hệ</small>
                        <h4 class="mb-0">{{ $contacts->total() }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Chờ xử lý</small>
                        <h4 class="mb-0 text-warning">{{ \App\Models\Contact::where('status', 'pending')->count() }}</h4>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="border rounded bg-white p-3 h-100">
                        <small class="text-muted d-block mb-1">Đã trả lời</small>
                        <h4 class="mb-0 text-success">{{ \App\Models\Contact::where('status', 'replied')->count() }}</h4>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.contacts.index') }}"
                class="row g-3 mb-4 border rounded bg-white p-3">
                <div class="col-md-4">
                    <label class="form-label">Từ khóa</label>
                    <input type="text" name="keyword" class="form-control" value="{{ request('keyword') }}"
                        placeholder="Tên, email, nội dung...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Lọc</button>
                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-secondary">Đặt lại</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">STT</th>
                            <th>Người gửi</th>
                            <th>Email</th>
                            <th>Nội dung</th>
                            <th>Ngày gửi</th>
                            <th width="160">Trạng thái</th>
                            <th width="160">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($contacts as $index => $contact)
                            <tr>
                                <td>{{ $contacts->firstItem() + $index }}</td>
                                <td>{{ $contact->name }}</td>
                                <td><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></td>
                                <td><span class="text-muted">{{ Str::limit($contact->message, 80) }}</span></td>
                                <td>{{ $contact->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($contact->status === 'pending')
                                        <span class="badge bg-warning text-dark">Chờ xử lý</span>
                                    @elseif($contact->status === 'read')
                                        <span class="badge bg-secondary">Đã xem</span>
                                    @else
                                        <span class="badge bg-success">Đã trả lời</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info mb-1" data-bs-toggle="modal"
                                        data-bs-target="#contactModal{{ $contact->id }}">Chi tiết</button>

                                    <form action="{{ route('admin.contacts.destroy', $contact) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Xóa liên hệ này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger mb-1">Xóa</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Chi tiết -->
                            <div class="modal fade" id="contactModal{{ $contact->id }}" tabindex="-1"
                                aria-labelledby="contactModalLabel{{ $contact->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="contactModalLabel{{ $contact->id }}">Liên hệ:
                                                {{ $contact->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3"><strong>Email:</strong> {{ $contact->email }}</div>
                                            <div class="mb-3"><strong>Ngày gửi:</strong>
                                                {{ $contact->created_at->format('d/m/Y H:i') }}</div>
                                            <div class="mb-3"><strong>Nội dung:</strong>
                                                <p>{!! nl2br(e($contact->message)) !!}</p>
                                            </div>

                                            <hr>

                                            <div class="d-flex gap-2">
                                                <form action="{{ route('admin.contacts.reply', $contact) }}" method="POST" class="m-0">
                                                    @csrf
                                                    <div class="mb-3">
                                                        <label for="reply_{{ $contact->id }}">Phản hồi</label>
                                                        <textarea name="reply" id="reply_{{ $contact->id }}" class="form-control"
                                                            rows="5">{{ old('reply', $contact->reply) }}</textarea>
                                                    </div>
                                                    <div>
                                                        <button class="btn btn-primary">Gửi phản hồi</button>
                                                    </div>
                                                </form>

                                                @if($contact->status === 'pending')
                                                    <form action="{{ route('admin.contacts.markAsRead', $contact) }}" method="POST" class="m-0">
                                                        @csrf
                                                        <button class="btn btn-secondary">Đánh dấu đã xem</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Đóng</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Chưa có liên hệ nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $contacts->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>setTimeout(()=>{document.querySelectorAll('.auto-dismiss').forEach(el=>el.remove())},3000)</script>
@endsection