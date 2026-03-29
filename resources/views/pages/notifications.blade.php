@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false])
@endsection

@section('content')

    <!-- Breadcrumb -->
    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>Thông báo</h2>
                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.home') }}">Trang chủ</a>
                            <span>Thông báo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Notification List -->
    <div class="container py-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <h3 class="mb-0">Thông báo của tôi</h3>
            <form method="POST" action="{{ route('customer.notifications.markAllRead') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary" {{ $unreadCount > 0 ? '' : 'disabled' }}>
                    Đọc tất cả
                </button>
            </form>
        </div>

        <div class="list-group">
            @forelse($notifications as $noti)
                <a href="{{ route('customer.notifications.read', $noti->id) }}" class="list-group-item list-group-item-action">
                    {{-- Tiêu đề --}}
                    <h6 class="fw-bold mb-1">{{ $noti->title }}</h6>

                    {{-- Nội dung --}}
                    <p class="mb-1 text-muted">{{ $noti->display_content }}</p>

                    {{-- Thời gian --}}
                    <small class="text-muted">{{ $noti->created_at->diffForHumans() }}</small>
                </a>
                <hr class="my-1">
            @empty
                <div class="list-group-item text-center text-muted">
                    Không có thông báo
                </div>
            @endforelse
        </div>

        {{-- Nút làm mới --}}
        @if($notifications->count() > 0)
            <div class="mt-3 text-center">
                <a href="{{ route('customer.notifications') }}" class="btn btn-primary btn-sm">Làm mới</a>
            </div>
        @endif
    </div>

@endsection