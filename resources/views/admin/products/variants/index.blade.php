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
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr class="text-center">
                        <th width="50">STT</th>
                        <th>SKU</th>
                        <th>Ảnh</th>
                        <th>Thuộc tính biến thể</th>
                        <th>Giá</th>
                        <th>NSX</th>
                        <th>HSD</th>
                        <th width="120">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($product->variants as $index => $variant)
                                    <tr>
                                        {{-- STT --}}
                                        <td class="text-center">
                                            {{ $index + 1 }}
                                        </td>

                                        {{-- SKU --}}
                                        <td>
                                            <span class="fw-bold">{{ $variant->sku }}</span>
                                        </td>

                                        {{-- Ảnh --}}
                                        <td class="text-center">
                                            @if ($variant->primaryImage)
                                                <img src="{{ asset('storage/' . $variant->primaryImage->image_path) }}" width="60" height="60"
                                                    class="rounded" style="object-fit: cover">
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- Thuộc tính biến thể --}}
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
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        {{-- Giá --}}
                                        <td class="text-end">
                                            {{ number_format($variant->price) }} đ
                                        </td>

                                        {{-- NSX --}}
                                        <td class="text-center">
                                            {{ $variant->manufacture_date
        ? \Carbon\Carbon::parse($variant->manufacture_date)->format('d/m/Y')
        : '—' }}
                                        </td>

                                        {{-- HSD --}}
                                        <td class="text-center">
                                            {{ $variant->expired_at
        ? \Carbon\Carbon::parse($variant->expired_at)->format('d/m/Y')
        : '—' }}
                                        </td>

                                        {{-- Thao tác --}}
                                        <td class="text-center">
                                            <a href="{{ route('admin.products.variants.edit', $variant->id) }}" class="btn btn-sm btn-warning mb-1">
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
                                        </td>
                                    </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
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