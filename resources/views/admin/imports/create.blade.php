@extends('admin.layouts.layout_admin')

@section('content')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded p-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h5 class="mb-1">Nhập kho</h5>
                    <p class="text-muted mb-0">Chọn nhà phân phối, xem nhanh sản phẩm và biến thể trước khi nhập kho.</p>
                </div>
                <div class="import-summary-badge">
                    <span>Tổng tạm tính</span>
                    <strong id="grandTotal">0 đ</strong>
                </div>
            </div>

            <form action="{{ route('admin.imports.store') }}" method="POST">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-lg-7">
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

                    <div class="col-lg-5">
                        <label class="form-label">Ngày nhập</label>
                        <input type="date" name="import_date" class="form-control" required>
                    </div>
                </div>

                <div class="supplier-products-panel mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Sản phẩm thuộc nhà phân phối</h6>
                        <small class="text-muted" id="supplierProductCount">Chưa chọn nhà phân phối</small>
                    </div>
                    <div id="supplierProductsGrid" class="supplier-products-grid empty-state">
                        Chọn nhà phân phối để xem sản phẩm, số biến thể và tồn kho hiện tại.
                    </div>
                </div>

                <hr>

                <h6>Danh sách sản phẩm nhập</h6>

                <table class="table table-bordered align-middle" id="itemsTable">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Biến thể</th>
                            <th>Tồn hiện tại</th>
                            <th>Giá bán hiện tại</th>
                            <th>Số lượng</th>
                            <th>Giá nhập</th>
                            <th>Thành tiền</th>
                            <th width="80"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="text" class="form-control search-input product-search mb-2"
                                    placeholder="Tìm nhanh sản phẩm...">
                                <select name="items[0][product_id]" class="form-select product-select" required>
                                    <option value="">-- Chọn sản phẩm --</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control search-input variant-search mb-2"
                                    placeholder="Tìm nhanh biến thể..." disabled>
                                <select name="items[0][product_variant_id]" class="form-select variant-select" required>
                                    <option value="">-- Chọn biến thể --</option>
                                </select>
                            </td>
                            <td>
                                <div class="meta-box stock-display">0</div>
                            </td>
                            <td>
                                <div class="meta-box sale-price-display">0 đ</div>
                            </td>
                            <td>
                                <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1"
                                    required>
                            </td>
                            <td>
                                <input type="number" name="items[0][unit_price]" class="form-control unit-price-input"
                                    min="0" required>
                                <div class="price-helper mt-2">
                                    <button type="button" class="btn btn-link btn-sm p-0 latest-price-btn d-none">Dùng giá nhập gần
                                        nhất</button>
                                    <div class="latest-price-display text-muted small">Chưa có lịch sử nhập</div>
                                    <div class="price-warning text-danger small d-none">Giá nhập đang cao hơn giá bán hiện tại.</div>
                                </div>
                            </td>
                            <td>
                                <div class="meta-box row-total-display">0 đ</div>
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

    <style>
        .import-summary-badge {
            background: linear-gradient(135deg, #eef8ff, #f6ffef);
            border: 1px solid #d5e7d5;
            border-radius: 12px;
            padding: 12px 16px;
            min-width: 220px;
            text-align: right;
        }

        .import-summary-badge span {
            display: block;
            color: #6b7280;
            font-size: 13px;
        }

        .import-summary-badge strong {
            font-size: 24px;
            color: #1f2937;
        }

        .supplier-products-panel {
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
        }

        .supplier-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .supplier-products-grid.empty-state {
            display: block;
            padding: 18px;
            color: #6b7280;
            background: #f9fafb;
            border-radius: 10px;
            border: 1px dashed #d1d5db;
        }

        .product-mini-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #fbfdff;
            padding: 12px;
        }

        .product-mini-card h6 {
            margin-bottom: 8px;
            font-size: 14px;
            line-height: 1.45;
        }

        .product-mini-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #6b7280;
        }

        .meta-box {
            min-height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            font-weight: 600;
        }

        .search-input {
            font-size: 13px;
            border-color: #d8dee6;
            background: #fcfdff;
        }

        .latest-price-btn {
            text-decoration: none;
            font-weight: 600;
        }

        .price-warning {
            font-weight: 600;
        }

        .unit-price-input.is-warning {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.12);
        }
    </style>

    <script>
        let index = 1;
        let currentProducts = [];
        let variantsByProduct = {};

        const supplierSelect = document.getElementById('supplierSelect');
        const supplierProductsGrid = document.getElementById('supplierProductsGrid');
        const supplierProductCount = document.getElementById('supplierProductCount');
        const grandTotal = document.getElementById('grandTotal');

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('vi-VN').format(Number(value || 0)) + ' đ';
        }

        function renderSupplierProducts(products) {
            if (!products.length) {
                supplierProductsGrid.className = 'supplier-products-grid empty-state';
                supplierProductsGrid.innerHTML = 'Nhà phân phối này chưa có sản phẩm nào.';
                supplierProductCount.textContent = '0 sản phẩm';
                return;
            }

            supplierProductsGrid.className = 'supplier-products-grid';
            supplierProductsGrid.innerHTML = products.map(product => `
                    <div class="product-mini-card">
                        <h6>${escapeHtml(product.name)}</h6>
                        <div class="product-mini-meta"><span>Biến thể</span><strong>${product.variant_count}</strong></div>
                        <div class="product-mini-meta"><span>Tồn hiện tại</span><strong>${product.total_stock}</strong></div>
                    </div>
                `).join('');
            supplierProductCount.textContent = `${products.length} sản phẩm`;
        }

        function updateRowSummary(row) {
            const variantSelect = row.querySelector('.variant-select');
            const selectedOption = variantSelect?.selectedOptions?.[0];
            const stockDisplay = row.querySelector('.stock-display');
            const salePriceDisplay = row.querySelector('.sale-price-display');
            const quantityInput = row.querySelector('.quantity-input');
            const unitPriceInput = row.querySelector('.unit-price-input');
            const rowTotalDisplay = row.querySelector('.row-total-display');
            const latestPriceDisplay = row.querySelector('.latest-price-display');
            const latestPriceButton = row.querySelector('.latest-price-btn');
            const priceWarning = row.querySelector('.price-warning');

            const stock = selectedOption?.dataset.stock || 0;
            const salePrice = selectedOption?.dataset.price || 0;
            const latestImportPrice = selectedOption?.dataset.latestImportPrice;

            if (stockDisplay) stockDisplay.textContent = stock;
            if (salePriceDisplay) salePriceDisplay.textContent = formatCurrency(salePrice);

            if (latestPriceDisplay) {
                latestPriceDisplay.textContent = latestImportPrice
                    ? `Giá nhập gần nhất: ${formatCurrency(latestImportPrice)}`
                    : 'Chưa có lịch sử nhập';
            }

            if (latestPriceButton) {
                latestPriceButton.classList.toggle('d-none', !latestImportPrice);
                latestPriceButton.dataset.latestPrice = latestImportPrice || '';
            }

            const quantity = Number(quantityInput?.value || 0);
            const unitPrice = Number(unitPriceInput?.value || 0);
            if (rowTotalDisplay) rowTotalDisplay.textContent = formatCurrency(quantity * unitPrice);

            const isHigherThanSalePrice = unitPrice > 0 && Number(salePrice) > 0 && unitPrice > Number(salePrice);
            if (priceWarning) {
                priceWarning.classList.toggle('d-none', !isHigherThanSalePrice);
            }
            if (unitPriceInput) {
                unitPriceInput.classList.toggle('is-warning', isHigherThanSalePrice);
            }
        }

        function updateGrandTotal() {
            let total = 0;
            document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
                const quantity = Number(row.querySelector('.quantity-input')?.value || 0);
                const unitPrice = Number(row.querySelector('.unit-price-input')?.value || 0);
                total += quantity * unitPrice;
            });
            grandTotal.textContent = formatCurrency(total);
        }

        function hydrateProductSelect(select, keyword = '') {
            select.innerHTML = '<option value="">-- Chọn sản phẩm --</option>';
            const normalizedKeyword = keyword.trim().toLowerCase();
            currentProducts
                .filter(product => !normalizedKeyword || product.name.toLowerCase().includes(normalizedKeyword))
                .forEach(product => {
                    select.innerHTML += `<option value="${product.id}">${escapeHtml(product.name)} (${product.variant_count} biến thể)</option>`;
                });

            if (normalizedKeyword && select.options.length === 1) {
                select.innerHTML += '<option value="" disabled>Không tìm thấy sản phẩm phù hợp</option>';
            }
        }

        function filterVariants(row, keyword = '') {
            const productId = row.querySelector('.product-select')?.value;
            const variantSelect = row.querySelector('.variant-select');

            variantSelect.innerHTML = '<option value="">-- Chọn biến thể --</option>';

            if (!productId || !variantsByProduct[productId]) {
                return;
            }

            const normalizedKeyword = keyword.trim().toLowerCase();

            variantsByProduct[productId]
                .filter(variant => !normalizedKeyword || variant.label.toLowerCase().includes(normalizedKeyword))
                .forEach(variant => {
                    variantSelect.innerHTML += `<option value="${variant.id}" data-stock="${variant.stock}" data-price="${variant.price}" data-latest-import-price="${variant.latest_import_price ?? ''}">${escapeHtml(variant.label)}</option>`;
                });

            if (normalizedKeyword && variantSelect.options.length === 1) {
                variantSelect.innerHTML += '<option value="" disabled>Không tìm thấy biến thể phù hợp</option>';
            }
        }

        function loadVariants(productId, variantSelect) {
            variantSelect.innerHTML = '<option value="">-- Chọn biến thể --</option>';

            if (!productId) return Promise.resolve();

            if (variantsByProduct[productId]) {
                variantsByProduct[productId].forEach(variant => {
                    variantSelect.innerHTML += `<option value="${variant.id}" data-stock="${variant.stock}" data-price="${variant.price}" data-latest-import-price="${variant.latest_import_price ?? ''}">${escapeHtml(variant.label)}</option>`;
                });
                return Promise.resolve();
            }

            return fetch(`/admin/imports/get-variants/${productId}`)
                .then(res => res.json())
                .then(variants => {
                    variantsByProduct[productId] = variants;
                    variants.forEach(variant => {
                        variantSelect.innerHTML += `<option value="${variant.id}" data-stock="${variant.stock}" data-price="${variant.price}" data-latest-import-price="${variant.latest_import_price ?? ''}">${escapeHtml(variant.label)}</option>`;
                    });
                });
        }

        function resetRow(row) {
            const variantSearch = row.querySelector('.variant-search');
            const variantSelect = row.querySelector('.variant-select');
            const unitPriceInput = row.querySelector('.unit-price-input');

            if (variantSearch) {
                variantSearch.value = '';
                variantSearch.disabled = true;
            }

            if (variantSelect) {
                variantSelect.innerHTML = '<option value="">-- Chọn biến thể --</option>';
            }

            if (unitPriceInput) {
                unitPriceInput.classList.remove('is-warning');
            }

            updateRowSummary(row);
            updateGrandTotal();
        }

        supplierSelect.addEventListener('change', function () {

            const supplierId = this.value;

            document.querySelectorAll('.product-select').forEach(select => {
                select.innerHTML = '<option value="">-- Chọn sản phẩm --</option>';
            });

            document.querySelectorAll('.product-search').forEach(input => {
                input.value = '';
            });

            document.querySelectorAll('.variant-select').forEach(select => {
                select.innerHTML = '<option value="">-- Chọn biến thể --</option>';
            });

            document.querySelectorAll('.variant-search').forEach(input => {
                input.value = '';
                input.disabled = true;
            });

            currentProducts = [];
            variantsByProduct = {};
            supplierProductsGrid.className = 'supplier-products-grid empty-state';
            supplierProductsGrid.innerHTML = 'Nhà phân phối này chưa có sản phẩm nào.';
            supplierProductCount.textContent = '0 sản phẩm';

            document.querySelectorAll('#itemsTable tbody tr').forEach(row => updateRowSummary(row));
            updateGrandTotal();

            if (!supplierId) return;

            fetch(`/admin/imports/get-products/${supplierId}`)
                .then(res => res.json())
                .then(products => {
                    currentProducts = products;
                    renderSupplierProducts(products);

                    document.querySelectorAll('.product-select').forEach(select => {
                        const row = select.closest('tr');
                        const productSearch = row.querySelector('.product-search');
                        hydrateProductSelect(select, productSearch?.value || '');
                    });
                });
        });


        document.addEventListener('change', function (e) {

            if (e.target.classList.contains('product-select')) {

                const productId = e.target.value;
                const row = e.target.closest('tr');
                const variantSelect = row.querySelector('.variant-select');
                const variantSearch = row.querySelector('.variant-search');

                if (!productId) {
                    resetRow(row);
                    return;
                }

                if (variantSearch) {
                    variantSearch.disabled = false;
                    variantSearch.value = '';
                }

                loadVariants(productId, variantSelect).then(() => updateRowSummary(row));
            }

            if (e.target.classList.contains('variant-select')) {
                updateRowSummary(e.target.closest('tr'));
                updateGrandTotal();
            }

            if (e.target.classList.contains('quantity-input') || e.target.classList.contains('unit-price-input')) {
                updateRowSummary(e.target.closest('tr'));
                updateGrandTotal();
            }
        });


        document.getElementById('addRow').addEventListener('click', function () {

            const table = document.querySelector('#itemsTable tbody');
            const row = table.insertRow();

            row.innerHTML = `
                <td>
                    <input type="text" class="form-control search-input product-search mb-2" placeholder="Tìm nhanh sản phẩm...">
                    <select name="items[${index}][product_id]" class="form-select product-select" required>
                        <option value="">-- Chọn sản phẩm --</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control search-input variant-search mb-2" placeholder="Tìm nhanh biến thể..." disabled>
                    <select name="items[${index}][product_variant_id]" class="form-select variant-select" required>
                        <option value="">-- Chọn biến thể --</option>
                    </select>
                </td>
                <td>
                    <div class="meta-box stock-display">0</div>
                </td>
                <td>
                    <div class="meta-box sale-price-display">0 đ</div>
                </td>
                <td>
                    <input type="number" name="items[${index}][quantity]" class="form-control quantity-input" min="1" required>
                </td>
                <td>
                    <input type="number" name="items[${index}][unit_price]" class="form-control unit-price-input" min="0" required>
                    <div class="price-helper mt-2">
                        <button type="button" class="btn btn-link btn-sm p-0 latest-price-btn d-none">Dùng giá nhập gần nhất</button>
                        <div class="latest-price-display text-muted small">Chưa có lịch sử nhập</div>
                        <div class="price-warning text-danger small d-none">Giá nhập đang cao hơn giá bán hiện tại.</div>
                    </div>
                </td>
                <td>
                    <div class="meta-box row-total-display">0 đ</div>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                </td>
            `;

            // nếu đã chọn nhà phân phối thì load lại sản phẩm cho dòng mới
            if (currentProducts.length > 0) {
                const newSelect = row.querySelector('.product-select');
                const productSearch = row.querySelector('.product-search');
                hydrateProductSelect(newSelect, productSearch?.value || '');
            }

            index++;
        });


        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
                updateGrandTotal();
            }
        });

        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('product-search')) {
                const row = e.target.closest('tr');
                const productSelect = row.querySelector('.product-select');
                const previousValue = productSelect.value;
                hydrateProductSelect(productSelect, e.target.value);

                if ([...productSelect.options].some(option => option.value === previousValue)) {
                    productSelect.value = previousValue;
                }
                return;
            }

            if (e.target.classList.contains('variant-search')) {
                const row = e.target.closest('tr');
                const variantSelect = row.querySelector('.variant-select');
                const previousValue = variantSelect.value;
                filterVariants(row, e.target.value);

                if ([...variantSelect.options].some(option => option.value === previousValue)) {
                    variantSelect.value = previousValue;
                }

                updateRowSummary(row);
                return;
            }

            if (e.target.classList.contains('quantity-input') || e.target.classList.contains('unit-price-input')) {
                updateRowSummary(e.target.closest('tr'));
                updateGrandTotal();
            }
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('latest-price-btn')) {
                const row = e.target.closest('tr');
                const unitPriceInput = row.querySelector('.unit-price-input');
                const latestPrice = e.target.dataset.latestPrice;

                if (unitPriceInput && latestPrice) {
                    unitPriceInput.value = latestPrice;
                    updateRowSummary(row);
                    updateGrandTotal();
                }
            }
        });

        updateGrandTotal();
    </script>

@endsection