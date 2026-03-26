<div class="mb-3">
    <h6 class="mb-1">{{ $variant->product->name ?? 'Sản phẩm' }}</h6>
    <div class="small text-muted">
        SKU: {{ $variant->sku ?? 'N/A' }} |
        Giá bán hiện tại: <span class="fw-semibold text-dark">{{ number_format($sellingPrice) }} đ</span> |
        Tồn kho hiện tại: <span class="fw-semibold">{{ number_format((int) $inventory->quantity) }}</span>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Lần nhập</th>
                <th>Ngày nhập</th>
                <th>Nhà phân phối</th>
                <th>Người nhập</th>
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
                    <td colspan="10" class="text-center text-muted">Chưa có dữ liệu lô nhập cho biến thể này.</td>
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
