@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    Biến thể của sản phẩm: <b>{{ $product->name }}</b>
                </h5>

                <a href="{{ route('admin.products.variants.create', $product->id) }}" class="btn btn-primary mb-3">
                    + Thêm biến thể
                </a>

            </div>
            <style>
                .variant-table {
                    table-layout: fixed;
                }

                .variant-table th,
                .variant-table td {
                    vertical-align: middle;
                    font-size: 14px;
                    padding: 10px 8px;
                }

                .variant-sku {
                    font-weight: 700;
                    font-size: 13px;
                    word-break: break-word;
                }

                .variant-attrs {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 6px;
                }

                .variant-attr-chip {
                    display: inline-block;
                    padding: 3px 8px;
                    border-radius: 999px;
                    background: #eef2ff;
                    border: 1px solid #dbe4ff;
                    color: #334155;
                    font-size: 12px;
                    line-height: 1.4;
                }

                .variant-thumb {
                    width: 52px;
                    height: 52px;
                    object-fit: cover;
                    border-radius: 8px;
                    border: 1px solid #e5e7eb;
                }

                .variant-price {
                    font-weight: 700;
                    white-space: nowrap;
                }

                .variant-actions {
                    display: flex;
                    flex-direction: column;
                    gap: 6px;
                    align-items: stretch;
                }

                .variant-actions .btn {
                    width: 100%;
                }
            </style>

            <table class="table table-bordered align-middle variant-table">
                <colgroup>
                    <col style="width: 6%">
                    <col style="width: 22%">
                    <col style="width: 10%">
                    <col style="width: 34%">
                    <col style="width: 14%">
                    <col style="width: 14%">
                </colgroup>
                <thead class="table-light">
                    <tr class="text-center">
                        <th>STT</th>
                        <th>SKU</th>
                        <th>Ảnh</th>
                        <th>Thuộc tính biến thể</th>
                        <th>Giá</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($product->variants as $index => $variant)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td><span class="variant-sku">{{ $variant->sku }}</span></td>
                            <td class="text-center">
                                @if ($variant->primaryImage)
                                    <img src="{{ asset('storage/' . $variant->primaryImage->image_path) }}" class="variant-thumb"
                                        alt="{{ $variant->sku }}">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
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
                                    <div class="variant-attrs">
                                        @foreach ($attrs as $attr)
                                            <span class="variant-attr-chip">{{ $attr }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="variant-price">{{ number_format($variant->price) }} đ</span>
                            </td>
                            <td>
                                <div class="variant-actions">
                                    <a href="{{ route('admin.products.variants.edit', $variant->id) }}"
                                        class="btn btn-sm btn-warning">
                                        Sửa
                                    </a>

                                    <form method="POST" action="{{ route('admin.products.variants.destroy', $variant->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('Xóa biến thể {{ $variant->sku }} này?')">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Chưa có biến thể nào cho sản phẩm này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <a href="{{ route('admin.products.list') }}" class="btn btn-secondary btn-sm">
                ← Quay lại
            </a>


        </div>
    </div>
@endsection