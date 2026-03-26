@extends('admin.layouts.layout_admin')

@section('navbar')
    @include('admin.layouts.navbar')
@endsection

@section('content')
    <div class="container-fluid pt-4 px-4">
        <h3 class="mb-4">Tất cả thông báo</h3>

        <div class="list-group">
            @forelse($notifications as $noti)
                <a href="{{ route('admin.notifications.read', $noti->id) }}" class="list-group-item list-group-item-action">
                    <h6 class="fw-bold mb-1">{{ $noti->title }}</h6>
                    <p class="mb-1 text-muted">{{ $noti->content }}</p>
                    <small class="text-muted">{{ $noti->created_at->diffForHumans() }}</small>
                </a>
                <hr class="my-1">
            @empty
                <div class="list-group-item text-center text-muted">
                    Không có thông báo
                </div>
            @endforelse
        </div>
    </div>
@endsection