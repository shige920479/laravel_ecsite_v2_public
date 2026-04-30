@extends('layouts.app')

@section('content')
<x-headline/>

@php 
if ($errors->any())
  $errors = $errors->toArray() ?? [];
@endphp

<div class="content container cart">
  <div
    id="cart-index-root"
    data-config='@json([
      "errors" => $errors
    ])'
  >
  </div>
</div>

@endsection