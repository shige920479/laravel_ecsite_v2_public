@props(['messages' => []])
@props(['className' => ''])

@php
 $list = is_array($messages) ? $messages : (empty($messages) ? [] : [$messages]);
@endphp

@if ($list)
  @foreach ($list as $message)
    {{-- <span class="error-msg {{ $className }}" >{{ $message }}</span><br> --}}
    <div class="error-msg {{ $className }}"  >{{ $message }}</div>
  @endforeach
@endif