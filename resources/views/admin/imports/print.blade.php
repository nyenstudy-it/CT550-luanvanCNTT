<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Phiếu nhập kho #{{ $import->id }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #000;
        }

        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .sub-title {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 6px;
        }

        th {
            background: #f2f2f2;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .sign {
            margin-top: 50px;
            width: 100%;
            text-align: center;
        }

        .sign td {
            border: none;
            width: 33%;
        }
    </style>
</head>

<body>

    <div class="title">PHIẾU NHẬP KHO</div>
    <div class="sub-title">Mã phiếu: #{{ $import->id }}</div>

    <p><strong>Nhà cung cấp:</strong> {{ $import->supplier->name }}</p>
    <p><strong>Ngày nhập:</strong> {{ \Carbon\Carbon::parse($import->import_date)->format('d/m/Y') }}</p>

    <br>

    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Sản phẩm</th>
                <th>Biến thể (SKU)</th>
                <th>Số lượng</th>
                <th>Giá nhập</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach($import->items as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item->variant?->product?->name }}</td>
                    <td>{{ $item->variant?->sku }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price) }}</td>
                    <td class="text-right">
                        {{ number_format($item->quantity * $item->unit_price) }}
                    </td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="5" class="text-right"><strong>Tổng cộng</strong></td>
                <td class="text-right"><strong>{{ number_format($import->total_amount) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <table class="sign">
        <tr>
            <td>
                Người lập phiếu<br><br><br>
                (Ký, ghi rõ họ tên)
            </td>
            <td>
                Thủ kho<br><br><br>
                (Ký, ghi rõ họ tên)
            </td>
            <td>
                Nhà cung cấp<br><br><br>
                (Ký, ghi rõ họ tên)
            </td>
        </tr>
    </table>

</body>

</html>