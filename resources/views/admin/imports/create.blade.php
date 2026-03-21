@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <h6 class="mb-3">Nhập kho</h6>

            <form action="{{ route('admin.imports.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nhà phân phối</label>
                    <select name="supplier_id" id="supplierSelect" class="form-select" required>
                        <option value="">-- Chọn nhà phân phối --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

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
                            <th>Biến thể</th>
                            <th>Số lượng</th>
                            <th>Giá nhập</th>
                            <th width="80"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="items[0][product_id]" class="form-select product-select" required>
                                    <option value="">-- Chọn sản phẩm --</option>
                                </select>
                            </td>
                            <td>
                                <select name="items[0][product_variant_id]" class="form-select variant-select" required>
                                    <option value="">-- Chọn biến thể --</option>
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

    <script>
        let index = 1;
        let currentProducts = [];

        const supplierSelect = document.getElementById('supplierSelect');

        supplierSelect.addEventListener('change', function () {

            const supplierId = this.value;

            document.querySelectorAll('.product-select').forEach(select => {
                select.innerHTML = '<option value="">-- Chọn sản phẩm --</option>';
            });

            document.querySelectorAll('.variant-select').forEach(select => {
                select.innerHTML = '<option value="">-- Chọn biến thể --</option>';
            });

            currentProducts = [];

            if (!supplierId) return;

            fetch(`/admin/imports/get-products/${supplierId}`)
                .then(res => res.json())
                .then(products => {
                    currentProducts = products;

                    document.querySelectorAll('.product-select').forEach(select => {
                        products.forEach(product => {
                            select.innerHTML += `<option value="${product.id}">${product.name}</option>`;
                        });
                    });
                });
        });


        document.addEventListener('change', function (e) {

            if (e.target.classList.contains('product-select')) {

                const productId = e.target.value;
                const row = e.target.closest('tr');
                const variantSelect = row.querySelector('.variant-select');

                variantSelect.innerHTML = '<option value="">-- Chọn biến thể --</option>';

                if (!productId) return;

                fetch(`/admin/imports/get-variants/${productId}`)
                    .then(res => res.json())
                    .then(variants => {
                        variants.forEach(variant => {
                            variantSelect.innerHTML += `
                            <option value="${variant.id}">
                                ${variant.sku}
                            </option>
                        `;
                        });
                    });
            }
        });


        document.getElementById('addRow').addEventListener('click', function () {

            const table = document.querySelector('#itemsTable tbody');
            const row = table.insertRow();

            row.innerHTML = `
            <td>
                <select name="items[${index}][product_id]" class="form-select product-select" required>
                    <option value="">-- Chọn sản phẩm --</option>
                </select>
            </td>
            <td>
                <select name="items[${index}][product_variant_id]" class="form-select variant-select" required>
                    <option value="">-- Chọn biến thể --</option>
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

            // nếu đã chọn nhà phân phối thì load lại sản phẩm cho dòng mới
            if (currentProducts.length > 0) {
                const newSelect = row.querySelector('.product-select');
                currentProducts.forEach(product => {
                    newSelect.innerHTML += `<option value="${product.id}">${product.name}</option>`;
                });
            }

            index++;
        });


        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
            }
        });
    </script>

@endsection