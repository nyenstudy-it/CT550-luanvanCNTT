{{-- THÔNG TIN SẢN PHẨM --}}
<div class="row mb-4">
<div class="col-md-4">
    @if ($product->image)
        <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded border">
    @else
        <div class="text-muted text-center py-5 border rounded">
            Không có ảnh
        </div>
    @endif
</div>


    <div class="col-md-8">
        <h5 class="mb-3">{{ $product->name }}</h5>

        <p><b>Danh mục:</b> {{ $product->category->name ?? '—' }}</p>
        <p><b>Nhà cung cấp:</b> {{ $product->supplier->name ?? '—' }}</p>

        <p><b>Mô tả:</b><br>
            {{ $product->description ?? '—' }}
        </p>

        <p><b>Hướng dẫn sử dụng:</b><br>
            {{ $product->usage_instructions ?? '—' }}
        </p>

        <p><b>Bảo quản:</b>
            {{ $product->storage_instructions ?? '—' }}
        </p>

        <p>
            <b>Ngày SX:</b>
            {{ $product->manufacture_date
    ? \Carbon\Carbon::parse($product->manufacture_date)->format('d/m/Y')
    : '—' }}
            |
            <b>HSD:</b>
            {{ $product->expiry_date
    ? \Carbon\Carbon::parse($product->expiry_date)->format('d/m/Y')
    : '—' }}
        </p>

        <p>
            <b>OCOP:</b>
            {{ $product->ocop_star ?? '—' }} ⭐
            ({{ $product->ocop_year ?? '—' }})
        </p>

        <p>
            <b>Trạng thái:</b>
            @if ($product->status === 'active')
                <span class="badge bg-success">Đang bán</span>
            @else
                <span class="badge bg-secondary">Ngừng bán</span>
            @endif
        </p>
    </div>
</div>

<hr>

{{-- DANH SÁCH BIẾN THỂ --}}
<h6 class="mb-3">Danh sách biến thể</h6>

<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr class="text-center">
            <th>Ảnh</th>
            <th>SKU</th>
            <th>Thuộc tính</th>
            <th>Giá</th>
            <th>Tồn kho</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($product->variants as $variant)
            <tr>
                <td class="text-center">
                    @if ($variant->primaryImage)
                        <img src="{{ asset('storage/' . $variant->primaryImage->image_path) }}" width="60" height="60"
                            class="rounded border">
                    @else
                        —
                    @endif
                </td>

                <td>{{ $variant->sku }}</td>

                <td>
                    @php
    $attrs = [];
    if ($variant->color)
        $attrs[] = 'Màu: ' . $variant->color;
    if ($variant->size)
        $attrs[] = 'Size: ' . $variant->size;
    if ($variant->volume)
        $attrs[] = 'Dung tích: ' . $variant->volume;
    if ($variant->weight)
        $attrs[] = 'Khối lượng: ' . $variant->weight;
                    @endphp

                    @if (count($attrs))
                        <ul class="mb-0 ps-3">
                            @foreach ($attrs as $attr)
                                <li>{{ $attr }}</li>
                            @endforeach
                        </ul>
                    @else
                        —
                    @endif
                </td>

                <td class="text-end">
                    {{ number_format($variant->price) }} đ
                </td>

                <td class="text-center">
                    {{ $variant->inventory->quantity ?? 0 }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted">
                    Chưa có biến thể
                </td>
            </tr>
        @endforelse
    </tbody>
</table>