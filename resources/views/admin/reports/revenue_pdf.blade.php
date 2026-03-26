<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Báo cáo doanh thu</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111;
            line-height: 1.5;
        }

        h2 {
            margin: 0 0 4px;
            font-size: 18px;
        }

        .meta {
            margin-bottom: 12px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
        }

        th {
            background: #efefef;
            text-align: left;
        }

        .right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h2>Báo cáo doanh thu cửa hàng</h2>
    <div class="meta">Ngày xuất: {{ $generatedAt->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>Hạng mục</th>
                <th class="right">Giá trị</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Doanh thu gộp</td>
                <td class="right">{{ number_format($revenueSummary['gross_sale'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Hoàn tiền</td>
                <td class="right">{{ number_format($revenueSummary['refund_amount'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Doanh thu thuần</td>
                <td class="right">{{ number_format($revenueSummary['net_revenue'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Giá vốn hàng đã bán</td>
                <td class="right">{{ number_format($revenueSummary['cogs'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Chi phí nhập hàng</td>
                <td class="right">{{ number_format($revenueSummary['import_cost'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Chi phí lương</td>
                <td class="right">{{ number_format($revenueSummary['salary_cost'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td>Chi phí ship</td>
                <td class="right">{{ number_format($revenueSummary['shipping_cost'], 0, ',', '.') }} ₫</td>
            </tr>
            <tr>
                <td><strong>Lợi nhuận ước tính</strong></td>
                <td class="right"><strong>{{ number_format($revenueSummary['estimated_profit'], 0, ',', '.') }}
                        ₫</strong></td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Ngày</th>
                <th class="right">Tiền thực nhận</th>
            </tr>
        </thead>
        <tbody>
            @foreach($monthlyFinance['labels'] as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="right">{{ number_format($monthlyFinance['values'][$i] ?? 0, 0, ',', '.') }} ₫</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Tuần</th>
                <th class="right">Doanh thu thuần</th>
            </tr>
        </thead>
        <tbody>
            @foreach($weeklyRevenue['labels'] as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="right">{{ number_format($weeklyRevenue['values'][$i] ?? 0, 0, ',', '.') }} ₫</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>