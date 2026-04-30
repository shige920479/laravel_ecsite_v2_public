@extends('layouts.app')

@section('content')
<x-headline/>

  <div class="content container">
    <div
      id="item-show-root"
      data-config='@json([
        "item" => $item,
        "isFavorite" => $isFavorite,
        "isLoggedIn" => auth()->check()
      ])'
      data-url="{{ $prevUrl }}"
    >
    </div>
  </div>

@endsection