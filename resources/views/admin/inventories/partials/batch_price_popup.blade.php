<div class="mb-3">
    <h6 class="mb-1">{{ $variant->product->name ?? 'Sản phẩm' }}</h6>
    <div class="small text-muted">
        SKU: {{ $variant->sku ?? 'N/A' }} |
        Giá bán hiện tại: <span class="fw-semibold text-dark">{{ number_format($sellingPrice) }} đ</span> |
        Tồn kho hiện tại: <span class="fw-semibold">{{ number_format((int) $inventory->quantity) }}</span>
    </div>
</div>

<style>
    .batch-status-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 10px 12px;
        background: #fcfdff;
    }

    .batch-status-card.is-expired {
        border-color: #f1b0b7;
        background: #fff6f6;
    }

    .batch-status-card.is-expiring {
        border-color: #f7d58a;
        background: #fffaf2;
    }

    .batch-status-card.is-safe {
        border-color: #badbcc;
        background: #f5fff8;
    }

    .batch-status-card.is-no-expiry {
        border-color: #d6d8db;
        background: #f8f9fa;
    }
</style>

<div class="row g-2 mb-3">
    @foreach ($batchItems->where('remaining_quantity', '>', 0)->take(3) as $batch)
        @php
            $expiry = $batch->expired_at ? \Carbon\Carbon::parse($batch->expired_at)->startOfDay() : null;
            $today = \Carbon\Carbon::today();
            $daysToExpire = $expiry ? $today->diffInDays($expiry, false) : null;
            $statusClass = !$expiry ? 'is-no-expiry' : ($daysToExpire < 0 ? 'is-expired' : ($daysToExpire <= 30 ? 'is-expiring' : 'is-safe'));
            $statusBadge = !$expiry ? ['bg-secondary', 'Không có HSD'] : ($daysToExpire < 0 ? ['bg-danger', 'Đã hết hạn'] : ($daysToExpire <= 30 ? ['bg-warning text-dark', 'Sắp hết hạn'] : ['bg-success', 'An toàn']));
        @endphp
        <div class="col-12 col-lg-4">
            <div class="batch-status-card {{ $statusClass }} h-100">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div class="fw-semibold">PN-{{ str_pad($batch->import_id, 5, '0', STR_PAD_LEFT) }}</div>
                    <span class="badge {{ $statusBadge[0] }}">{{ $statusBadge[1] }}</span>
                </div>
                <div class="small text-muted">Còn lại: <span
                        class="fw-semibold text-dark">{{ number_format((int) $batch->remaining_quantity) }}</span></div>
                <div class="small text-muted">NSX: <span
                        class="fw-semibold text-dark">{{ $batch->manufacture_date ? \Carbon\Carbon::parse($batch->manufacture_date)->format('d/m/Y') : '—' }}</span>
                </div>
                <div class="small text-muted">HSD: <span
                        class="fw-semibold text-dark">{{ $expiry ? $expiry->format('d/m/Y') : 'Không có' }}</span></div>
                @if (!is_null($daysToExpire))
                    <div
                        class="small mt-2 {{ $daysToExpire < 0 ? 'text-danger' : ($daysToExpire <= 30 ? 'text-warning' : 'text-success') }}">
                        {{ $daysToExpire < 0 ? 'Quá hạn ' . abs($daysToExpire) . ' ngày' : 'Còn ' . $daysToExpire . ' ngày đến hạn' }}
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Lần nhập</th>
                <th>Ngày nhập</th>
                <th>Nhà phân phối</th>
                <th>Người nhập</th>
                <th>NSX</th>
                <th>HSD</th>
                <th>Trạng thái lô</th>
                <th>SL nhập</th>
                <th>Còn lại</th>
                <th>Đã bán</th>
                <th>Giá nhập/lô</th>
                <th>Giá bán hiện tại</th>
                <th>Chênh lệch</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batchItems as $index => $batch)
                @php
                    $importQty = (int) $batch->quantity;
                    $remainingQty = (int) $batch->remaining_quantity;
                    $soldQty = max(0, $importQty - $remainingQty);
                    $importPrice = (float) $batch->unit_price;
                    $diff = $sellingPrice - $importPrice;
                    $expiry = $batch->expired_at ? \Carbon\Carbon::parse($batch->expired_at)->startOfDay() : null;
                    $manufactureDate = $batch->manufacture_date ? \Carbon\Carbon::parse($batch->manufacture_date)->format('d/m/Y') : '—';
                    $daysToExpire = $expiry ? \Carbon\Carbon::today()->diffInDays($expiry, false) : null;
                    $statusBadge = !$expiry
                        ? ['bg-secondary', 'Không có HSD']
                        : ($daysToExpire < 0
                            ? ['bg-danger', 'Đã hết hạn']
                            : ($daysToExpire <= 30 ? ['bg-warning text-dark', 'Sắp hết hạn'] : ['bg-success', 'An toàn']));
                    $importDate = $batch->import?->import_date
                        ? \Carbon\Carbon::parse($batch->import->import_date)->format('d/m/Y')
                        : ($batch->import?->created_at ? $batch->import->created_at->format('d/m/Y') : '—');
                @endphp
                <tr>
                    <td>
                        <span class="fw-semibold">PN-{{ str_pad($batch->import_id, 5, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td>
                        {{ $importDate }}
                    </td>
                    <td>{{ $batch->import?->supplier?->name ?? '—' }}</td>
                    <td>{{ $batch->import?->staff?->name ?? '—' }}</td>
                    <td>{{ $manufactureDate }}</td>
                    <td>{{ $expiry ? $expiry->format('d/m/Y') : 'Không có' }}</td>
                    <td>
                        <span class="badge {{ $statusBadge[0] }}">{{ $statusBadge[1] }}</span>
                        @if (!is_null($daysToExpire))
                            <div
                                class="small mt-1 {{ $daysToExpire < 0 ? 'text-danger' : ($daysToExpire <= 30 ? 'text-warning' : 'text-muted') }}">
                                {{ $daysToExpire < 0 ? 'Quá hạn ' . abs($daysToExpire) . ' ngày' : 'Còn ' . $daysToExpire . ' ngày' }}
                            </div>
                        @endif
                    </td>
                    <td class="text-end">{{ number_format($importQty) }}</td>
                    <td class="text-end">{{ number_format($remainingQty) }}</td>
                    <td class="text-end">{{ number_format($soldQty) }}</td>
                    <td class="text-end">{{ number_format($importPrice) }} đ</td>
                    <td class="text-end">{{ number_format($sellingPrice) }} đ</td>
                    <td class="text-end {{ $diff >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">
                        {{ number_format($diff) }} đ
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center text-muted">Chưa có dữ liệu lô nhập cho biến thể này.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($batchItems->isNotEmpty())
    <div class="small text-muted mt-2">
        Ghi chú: Chênh lệch = Giá bán hiện tại - Giá nhập từng lô (để tham khảo biên lợi nhuận gộp theo lô).
    </div>
@endif