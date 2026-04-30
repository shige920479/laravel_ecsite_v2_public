<!DOCTYPE html>
<html  lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', config('app.name', 'ecsite12'))</title>
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  </head>
  <body>

    @if (request()->routeIs('superuser.*'))
      @include('partials.superuser-header')

    @elseif (request()->routeIs('admin.*'))
      @include('partials.admin-header')

    @elseif (request()->routeIs('owner.*'))
      @include('partials.owner-header')

    @else
      @include('partials.header')
    @endif

    <main>
      @yield('content')
    </main>
    
    @include('partials.footer')

    <script>
      const CSRF_TOKEN = @json(csrf_token());
    </script>
    @stack('scripts')

  </body>
</html>