@extends('layout')

@section('hero')
    @include('pages.components.hero', ['showBanner' => false, 'heroNormal' => true])
@endsection

@section('content')

    <section class="breadcrumb-section set-bg" data-setbg="{{ asset('frontend/images/breadcrumb.jpg') }}">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>Voucher của tôi</h2>
                        <div class="breadcrumb__option">
                            <a href="{{ route('pages.home') }}">Trang chủ</a>
                            <a href="{{ route('discounts') }}">Voucher của tôi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="checkout spad">
        <div class="container">
            <div class="row">
                @forelse($discounts as $discount)
                    @php
                        $used = $discount->usages->isNotEmpty();
                        $expired = $discount->end_at && \Carbon\Carbon::parse($discount->end_at)->lt($now);
                        $status = $used ? 'Đã sử dụng' : ($expired ? 'Hết hạn' : 'Còn hiệu lực');
                        $statusClass = $used ? 'used' : ($expired ? 'expired' : 'active');
                    @endphp

                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="voucher-card {{ $statusClass }}">
                            <div class="voucher-header">
                                <div class="voucher-icon">
                                    <i class="fa fa-ticket-alt"></i>
                                </div>
                                <div class="voucher-code">{{ $discount->code }}</div>
                                <div class="voucher-status">{{ $status }}</div>
                            </div>
                            <div class="voucher-body">
                                <p class="voucher-value">
                                    <strong>Giảm:</strong> {{ $discount->value }}
                                    {{ $discount->type == 'percent' ? '%' : 'VND' }}
                                </p>
                                <p class="voucher-date">
                                    <strong>Thời hạn:</strong>
                                    @if($discount->start_at)
                                        {{ \Carbon\Carbon::parse($discount->start_at)->format('d/m/Y') }} -
                                        {{ $discount->end_at ? \Carbon\Carbon::parse($discount->end_at)->format('d/m/Y') : 'Không thời hạn' }}
                                    @else
                                        Không thời hạn
                                    @endif
                                </p>
                            </div>
                            <div class="voucher-footer">
                                @if(!$used && !$expired)
                                    <a href="{{ route('pages.home') }}?discount={{ $discount->id }}"
                                        class="btn btn-sm btn-orange w-100">Sử dụng ngay</a>
                                @else
                                    <button class="btn btn-sm btn-secondary w-100" disabled>Không thể sử dụng</button>
                                @endif
                            </div>
                        </div>
                    </div>

                @empty
                    <p class="text-muted">Bạn chưa có voucher nào.</p>
                @endforelse
            </div>
        </div>
    </section>

    <style>
        .voucher-card {
            background: linear-gradient(135deg, #fff3e0, #ffe0b2);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s;
        }

        .voucher-card:hover {
            transform: translateY(-3px);
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: rgba(255, 152, 0, 0.1);
        }

        .voucher-icon i {
            font-size: 1.5rem;
            color: #ff9800;
        }

        .voucher-code {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .voucher-status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.85rem;
            color: #fff;
        }

        .voucher-card.active .voucher-status {
            background: #4caf50;
        }

        .voucher-card.expired .voucher-status {
            background: #f44336;
        }

        .voucher-card.used .voucher-status {
            background: #9e9e9e;
        }

        .voucher-body {
            padding: 15px 20px;
        }

        .voucher-value,
        .voucher-date {
            margin-bottom: 6px;
            font-size: 0.95rem;
        }

        .voucher-footer {
            padding: 15px 20px;
        }

        .btn-orange {
            background-color: #ff9800;
            color: #fff;
            border: none;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn-orange:hover {
            background-color: #fb8c00;
        }
    </style>

@endsection