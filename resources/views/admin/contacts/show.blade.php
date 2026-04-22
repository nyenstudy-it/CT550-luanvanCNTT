@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Chi tiết liên hệ</h4>
                <div>
                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-secondary btn-sm">Quay lại</a>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Người gửi</dt>
                    <dd class="col-sm-9">{{ $contact->name }}</dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9"><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></dd>

                    <dt class="col-sm-3">Nội dung</dt>
                    <dd class="col-sm-9">{{ nl2br(e($contact->message)) }}</dd>

                    <dt class="col-sm-3">Ngày gửi</dt>
                    <dd class="col-sm-9">{{ $contact->created_at }}</dd>

                    <dt class="col-sm-3">Trạng thái</dt>
                    <dd class="col-sm-9">
                        @if($contact->status === 'pending')
                            <span class="badge badge-warning">Chờ xử lý</span>
                        @elseif($contact->status === 'read')
                            <span class="badge badge-secondary">Đã xem</span>
                        @else
                            <span class="badge badge-success">Đã trả lời</span>
                        @endif
                    </dd>

                    @if($contact->reply)
                        <dt class="col-sm-3">Phản hồi</dt>
                        <dd class="col-sm-9">{{ nl2br(e($contact->reply)) }}</dd>

                        <dt class="col-sm-3">Người trả lời</dt>
                        <dd class="col-sm-9">{{ $contact->repliedByUser?->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Ngày trả lời</dt>
                        <dd class="col-sm-9">{{ $contact->replied_at }}</dd>
                    @endif
                </dl>

                <hr>

                <form action="{{ route('admin.contacts.reply', $contact) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="reply">Trả lời <span class="text-danger">*</span></label>
                        <textarea name="reply" id="reply" class="form-control @error('reply') is-invalid @enderror"
                            rows="6" required>{{ old('reply', $contact->reply) }}</textarea>
                        @error('reply')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="send_email" id="send_email" value="1" checked>
                        <label class="form-check-label" for="send_email">
                            Gửi email thông báo cho khách hàng
                        </label>
                        <small class="text-muted d-block mt-1">
                            ✓ Email sẽ được gửi tới: <strong>{{ $contact->email }}</strong>
                        </small>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <strong>💡 Thông tin tự động điền:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Thời gian trả lời: {{ now()->format('d/m/Y H:i') }}</li>
                            <li>Người trả lời: {{ auth()->user()->name }}</li>
                        </ul>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Gửi phản hồi</button>
                        @if($contact->status === 'pending')
                            <a href="{{ route('admin.contacts.markAsRead', $contact) }}" class="btn btn-secondary">Đánh dấu đã
                                xem</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection