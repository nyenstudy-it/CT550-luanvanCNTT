@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <h6 class="mb-3">Nhập kho</h6>

            <form action="{{ route('admin.imports.store') }}" method="POST">
                @csrf

                {{-- Nhà phân phối --}}
                <div class="mb-3">
                    <label class="form-label">Nhà phân phối</label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">-- Chọn nhà phân phối --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Ngày nhập --}}
                <div class="mb-3">
                    <label class="form-label">Ngày nhập</label>
                    <input type="date" name="import_date" class="form-control" required>
                </div>

                <hr>

                <h6>Danh sách sản phẩm nhập</h6>

                <table class="table table-bordered align-middle" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Giá nhập</th>
                            <th width="80"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="items[0][product_variant_id]" class="form-select" required>
                                    <option value="">-- Chọn biến thể --</option>
                                    @foreach ($variants as $variant)
                                        <option value="{{ $variant->id }}">
                                            {{ $variant->product->name }}
                                            ({{ $variant->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control" min="1" required>
                            </td>
                            <td>
                                <input type="number" name="items[0][unit_price]" class="form-control" min="0" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <button type="button" class="btn btn-secondary btn-sm mb-3" id="addRow">
                    + Thêm dòng
                </button>

                <div class="text-end">
                    <button class="btn btn-primary">
                        Lưu phiếu nhập
                    </button>
                </div>

            </form>

        </div>
    </div>

    {{-- JS thêm dòng --}}
    <script>
        let index = 1;

        document.getElementById('addRow').addEventListener('click', function () {
            const table = document.querySelector('#itemsTable tbody');
            const row = table.insertRow();

            row.innerHTML = `
            <td>
                <select name="items[${index}][product_variant_id]" class="form-select" required>
                    <option value="">-- Chọn biến thể --</option>
                    @foreach ($variants as $variant)
                        <option value="{{ $variant->id }}">
                            {{ $variant->product->name }} ({{ $variant->sku }})
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${index}][quantity]" class="form-control" min="1" required>
            </td>
            <td>
                <input type="number" name="items[${index}][unit_price]" class="form-control" min="0" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
            </td>
        `;

            index++;
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
            }
        });
    </script>
@endsection