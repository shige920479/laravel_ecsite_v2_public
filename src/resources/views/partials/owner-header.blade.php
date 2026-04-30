@php
  $sessionKey = config('constants.session_key');
  $hasSession = array_filter($sessionKey, fn($key) => session()->has($key) && ! empty(session($key)));
  $ajaxView = request()->routeIs('owner.item.image.create') || request()->routeIs('owner.item.image.edit')
                || request()->routeIs('owner.shop.create') || request()->routeIs('owner.shop.edit')
@endphp
<header id="header" >
  <div class="header-wrapper">
    <h1 class="site-title">
      @if(! empty($hasSession) || $ajaxView)
        <form action="{{ route('owner.session.clear') }}" method="post">
          @csrf
          <input type="hidden" name="route" value="owner.shop.index">
          <button type="button" id="has-session-logo" data-has-session>
            <img src="{{ asset('images/logo.png') }}" alt="" />
          </button>
        </form>
      @else
        @auth
          <a href="{{ route('owner.shop.index') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" />
          </a>
        @else
          <a href="{{ route('home.index') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" />
          </a>
        @endauth
      @endif
    </h1>
    @if(! empty($hasSession) || $ajaxView)
      <form action="{{ route('owner.session.clear') }}" method="post">
        @csrf
        <input type="hidden" name="route" id="route-name">
        <nav id="navi">
          <ul id="owner-nav-menu">
            <li class="nav-items">
              <button type="button" data-has-session data-route="owner.shop.index">店舗情報</button>
            </li>
            <li class="nav-items">
              <button type="button" data-has-session data-route="owner.item.index">商品管理</button>
            </li>
            <li class="nav-items">
              <button type="button" data-has-session  data-route="owner.item.create">商品登録</button>
            </li>
            <li class="nav-items">
              <button type="button" data-has-session  data-route="owner.stocks.csv.create">在庫一括登録</button>
            </li>
          </ul>
        </nav>
     </form>
    @else 
      <nav id="navi">
        <ul id="owner-nav-menu">
          <li class="nav-items">
            <a href="{{ route('owner.shop.index') }}">店舗情報</a>
          </li>
          <li class="nav-items">
            <a href="{{ route('owner.item.index') }}">商品管理</a>
          </li>
          <li class="nav-items">
            <a href="{{ route('owner.item.create') }}">商品登録</a>
          </li>
          <li class="nav-items">
            <a href="{{ route('owner.stocks.csv.create') }}">在庫一括登録</a>
          </li>
        </ul>
      </nav>
    @endif
    @if (Auth::guard('web_owner')->check())
      <div class="flex items-center gap-10">
          <div>
            <span id="login-owner">{{ auth()->user()->name }} 様</span>
          </div>
        <form action="{{ route('owner.logout') }}" method="post">
          @csrf
          <div id="logout-box">
            <div class="header-icon logout"><img src="{{ asset('images/logout.png') }}" alt="" /></div>
            <div class="header-icon-text">ログアウト</div>
          </div>
        </form>
      </div>
    @endif
  </div>
</header>