<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Debug - Orders</title>
    <style>
        body{font-family: Arial, Helvetica, sans-serif; padding:16px}
        table{border-collapse:collapse;width:100%;margin-bottom:16px}
        th,td{border:1px solid #ddd;padding:8px;text-align:left}
        th{background:#f5f5f5}
    </style>
</head>
<body>
    <h1>Debug: Orders for {{ auth()->user()->name ?? auth()->id() }}</h1>

    @if($orders->isEmpty())
        <p><strong>No orders found for this user.</strong></p>
    @else
        @foreach($orders as $order)
            <section style="margin-bottom:28px">
                <h2>Order #{{ $order->id }} — Status: {{ $order->status ?? 'N/A' }} — Created: {{ $order->created_at }}</h2>
                <p>
                    <strong>Customer ID:</strong> {{ $order->customer_id ?? '-' }}
                    &nbsp; | &nbsp;
                    <strong>Total:</strong> {{ $order->total ?? ($order->grand_total ?? '-') }}
                </p>
                <p>
                    <strong>Payment:</strong>
                    @if($order->payment)
                        ID: {{ $order->payment->id }} — Status: {{ $order->payment->status ?? 'N/A' }} — Amount: {{ $order->payment->amount ?? 'N/A' }}
                    @else
                        <em>None</em>
                    @endif
                </p>

                <table>
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Variant ID</th>
                            <th>Product ID</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->product_variant_id ?? ($item->variant_id ?? '-') }}</td>
                                <td>{{ $item->variant->product_id ?? '-' }}</td>
                                <td>{{ $item->variant->product->name ?? '-' }}</td>
                                <td>{{ $item->quantity ?? ($item->qty ?? '-') }}</td>
                                <td>{{ $item->price ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endforeach
    @endif

</body>
</html>
