@props(['status' => 'info'])

@php
  if(session('status') === 'info') { $className = 'success'; } 
  if(session('status') === 'alert') { $className = 'alert'; } 
@endphp

@session('message')
  <span class="{{ $className }} mb-3">{{ session('message') }}</span>
@endsession

