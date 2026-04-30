<h1>注文のお知らせ</h1>

<p>{{ $owner->name }} 様</p>
<p>注文番号: {{ $order->id }}</p>
<p>注文日時: {{ $order->ordered_at }}</p>

<h2>配送先</h2>
<p>{{ $shipment->shipping_name }} 様</p>
<p>〒{{ $shipment->shipping_postcode }}</p>
<p>{{ $shipment->shipping_address }}</p>
<p>{{ $shipment->shipping_phone }}</p>

<h2>ご注文内容</h2>
  <table>
      <thead>
          <tr>
              <th>商品ID</th>
              <th>商品名</th>
              <th>数量</th>
              <th>単価</th>
              <th>小計</th>
          </tr>
      </thead>
      <tbody>
          @foreach ($orderItems as $item)
              <tr>
                  <td>{{ $item->item_id }}</td>
                  <td>{{ $item->item_name }}</td>
                  <td>{{ $item->quantity }}</td>
                  <td>{{ number_format($item->price_in_tax) }}円</td>
                  <td>{{ number_format($item->subtotal_in_tax) }}円</td>
              </tr>
          @endforeach
      </tbody>
  </table>

<p>合計: {{ number_format($totalInTax) }}円</p>