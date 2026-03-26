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
                        <div class="voucher-card voucher-card-system {{ $statusClass }}">
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
                                <p class="voucher-date mb-0">
                                    <strong>Phạm vi:</strong>
                                    @if($discount->products->isEmpty())
                                        Toàn shop
                                    @else
                                        {{ $discount->products->count() }} sản phẩm
                                    @endif
                                </p>
                            </div>
                            <div class="voucher-footer">
                                @if(!$used && !$expired)
                                    <a href="{{ route('pages.home') }}?discount={{ $discount->id }}"
                                        class="btn btn-sm btn-success w-100">Sử dụng ngay</a>
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
        .voucher-card-system {
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f7fcf5 100%);
            border: 1px solid #d9e7d1;
            border-left: 6px solid #66a84f;
            border-radius: 14px;
            box-shadow: 0 12px 24px rgba(22, 58, 24, 0.12);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .voucher-card-system:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 30px rgba(22, 58, 24, 0.18);
        }

        .voucher-card-system::after {
            content: "";
            position: absolute;
            top: -30px;
            right: -30px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(102, 168, 79, 0.12);
        }

        .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: rgba(102, 168, 79, 0.08);
            border-bottom: 1px dashed #d9e7d1;
            position: relative;
            z-index: 1;
        }

        .voucher-icon i {
            font-size: 1.5rem;
            color: #2d7a3f;
        }

        .voucher-code {
            font-weight: 700;
            font-size: 1.05rem;
            color: #ffffff;
            background: linear-gradient(135deg, #2d7a3f 0%, #3f944e 100%);
            padding: 6px 12px;
            border-radius: 999px;
            letter-spacing: 0.5px;
            box-shadow: 0 6px 12px rgba(45, 122, 63, 0.25);
        }

        .voucher-status {
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .voucher-card-system.active .voucher-status {
            background: #e8f6eb;
            color: #2d7a3f;
        }

        .voucher-card-system.expired .voucher-status {
            background: #fdecef;
            color: #bb2d3b;
        }

        .voucher-card-system.used .voucher-status {
            background: #eceff3;
            color: #4f5d70;
        }

        .voucher-card-system.expired {
            border-left-color: #d9534f;
            background: linear-gradient(135deg, #ffffff 0%, #fff6f6 100%);
        }

        .voucher-card-system.used {
            border-left-color: #8c9aa7;
            background: linear-gradient(135deg, #ffffff 0%, #f6f8fa 100%);
        }

        .voucher-body {
            padding: 15px 20px;
            position: relative;
            z-index: 1;
        }

        .voucher-value,
        .voucher-date {
            margin-bottom: 6px;
            font-size: 0.95rem;
            color: #415048;
        }

        .voucher-footer {
            padding: 15px 20px;
            position: relative;
            z-index: 1;
        }
    </style>

@endsection