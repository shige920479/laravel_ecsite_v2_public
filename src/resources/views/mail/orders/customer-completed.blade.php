<h1>ご注文ありがとうございます</h1>

<p>{{ $user->name }} 様</p>
<p>注文番号: {{ $order->id }}</p>
<p>注文日時: {{ $order->ordered_at }}</p>

<h2>配送先</h2>
<p>〒{{ $shippingFirst->shipment->shipping_postcode }}</p>
<p>{{ $shippingFirst->shipment->shipping_address }}</p>
<p>{{ $shippingFirst->shipment->shipping_phone }}</p>

<h2>ご注文内容</h2>

@foreach ($shipmentResults as $shipmentResult)
    <h3>{{ $shipmentResult->shipment->shop->name }}</h3>

    <table>
        <thead>
            <tr>
                <th>商品名</th>
                <th>数量</th>
                <th>単価</th>
                <th>小計</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($shipmentResult->orderItems as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->price_in_tax) }}円</td>
                    <td>{{ number_format($item->subtotal_in_tax) }}円</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach

<p>合計: {{ number_format($order->total_in_tax) }}円</p>