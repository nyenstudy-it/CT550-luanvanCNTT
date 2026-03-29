@extends('admin.layouts.layout_admin')

@section('navbar')
    @include('admin.layouts.navbar')
@endsection

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <h3 class="mb-0">Tất cả thông báo</h3>
            <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary" {{ $unreadCount > 0 ? '' : 'disabled' }}>
                    Đọc tất cả
                </button>
            </form>
        </div>

        <div class="list-group">
            @forelse($notifications as $noti)
                <a href="{{ route('admin.notifications.read', $noti->id) }}" class="list-group-item list-group-item-action">
                    <h6 class="fw-bold mb-1">{{ $noti->title }}</h6>
                    <p class="mb-1 text-muted">{{ $noti->display_content }}</p>
                    <small class="text-muted">{{ $noti->created_at->diffForHumans() }}</small>
                </a>
                <hr class="my-1">
            @empty
                <div class="list-group-item text-center text-muted">
                    Không có thông báo
                </div>
            @endforelse
        </div>

        @if(method_exists($notifications, 'total'))
            <div class="mt-3 d-flex justify-content-center">
                <span class="text-muted small">
                    Đang hiển thị {{ $notifications->count() }} / {{ $notifications->total() }} thông báo (tối đa 5 thông báo
                    mỗi trang)
                </span>
            </div>
        @endif

        @if(method_exists($notifications, 'links'))
            <div class="mt-3 d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection