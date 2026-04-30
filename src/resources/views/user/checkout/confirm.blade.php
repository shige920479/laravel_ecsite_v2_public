@extends('layouts.app')

@section('content')
<x-headline />
<div class="content container confirm">
  <div
    id="checkout-confirm-root"
    data-config='@json([
      "carts" => $carts,
      "route" => route('cart.index'),
    ])'
  >
  </div>
</div>
@endsection