<h1>いつもご愛顧いただきありがとうございます。</h1>
<p>以下ご注文につきましては決済期限を過ぎてますので、キャンセルとさせて戴きました。</p>
<br>
<p>{{ $user->name }} 様</p>
<p>決済期限: {{ $checkoutRequest->expires_at }}</p>

<h2>ご注文キャンセル内訳</h2>

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
        @foreach ($checkoutItems as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price_in_tax) }}円</td>
                <td>{{ number_format($item->subtotal_in_tax) }}円</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p>合計: {{ number_format($checkoutRequest->total_in_tax) }}円</p>