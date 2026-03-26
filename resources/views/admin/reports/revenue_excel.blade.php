<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Báo cáo doanh thu</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            vertical-align: middle;
        }

        th {
            background: #e9ecef;
            text-align: left;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .meta {
            margin-bottom: 14px;
        }
    </style>
</head>

<body>
    <div class="title">Báo cáo doanh thu cửa hàng</div>
    <div class="meta">Thời gian xuất: {{ $generatedAt->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>Chỉ số</th>
                <th>Giá trị</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Doanh thu gộp</td>
                <td>{{ number_format($revenueSummary['gross_sale'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Hoàn tiền</td>
                <td>{{ number_format($revenueSummary['refund_amount'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Doanh thu thuần</td>
                <td>{{ number_format($revenueSummary['net_revenue'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Giá vốn hàng đã bán</td>
                <td>{{ number_format($revenueSummary['cogs'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Chi phí nhập hàng</td>
                <td>{{ number_format($revenueSummary['import_cost'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Chi phí lương</td>
                <td>{{ number_format($revenueSummary['salary_cost'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Chi phí ship</td>
                <td>{{ number_format($revenueSummary['shipping_cost'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Lợi nhuận ước tính</td>
                <td>{{ number_format($revenueSummary['estimated_profit'], 0, ',', '.') }} ₫</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="2">Tiền thực nhận theo ngày trong tháng {{ $monthLabel }}</th>
            </tr>
            <tr>
                <th>Ngày</th>
                <th>Tiền thực nhận</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyFinance['labels'] as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ number_format($monthlyFinance['values'][$i] ?? 0, 0, ',', '.') }} ₫</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="2">Doanh thu theo tuần</th>
            </tr>
            <tr>
                <th>Tuần</th>
                <th>Doanh thu thuần</th>
            </tr>
        </thead>
        <tbody>
            @foreach($weeklyRevenue['labels'] as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ number_format($weeklyRevenue['values'][$i] ?? 0, 0, ',', '.') }} ₫</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>