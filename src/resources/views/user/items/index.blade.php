@extends('layouts.app')

@section('content')

<x-headline />

<div id="top" class="wrapper">
  <div id="items-root"
    data-config='@json($viewData)'
    data-categories='@json($categories)'
  >
  </div>
</div> 

@endsection