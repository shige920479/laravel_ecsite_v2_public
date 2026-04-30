<header id="header">
  <div class="header-wrapper">
    <h1 class="site-title">
      <a href="{{ route('home.index') }}">
        <img src="{{ asset('images/logo.png') }}" alt="" />
      </a>
    </h1>
    <div id="search-box">
      <form action="{{ route('home.index') }}" method="get">
        <input 
          id="search-input"
          name="item_search"
          type="text"
          value="{{ request('item_search') }}"
          placeholder="商品検索 キーワード入力"
        />
        <button id="search-btn">
          <img src="{{ asset('images/search.png') }}" alt=""/>
        </button>
      </form>
    </div>
    <div class="login-user-name">
      @auth
        <span>{{ auth()->user()->name }}</span>
      @endauth
      @guest
        <span>ゲスト 様</span>
      @endguest
    </div>
    <nav id="navi">
      <ul class="nav-menu">
        <li class="nav-li">
          @auth
            <form action="{{ route('logout') }}" method="post">
              @csrf
              <div id="logout-box">
                <div class="header-icon logout"><img src="{{ asset('images/logout.png') }}" alt="" /></div>
                <div class="header-icon-text">ログアウト</div>
              </div>
            </form>
          @endauth
          @guest
            <a href="{{ route('login') }}">
              <div class="header-icon"><img src="{{ asset('images/logindoor.png') }}" alt="" /></div>
              <div class="header-icon-text">ログイン</div>
            </a>
          @endguest
        </li>
        <li class="nav-li">
          <a href="{{ route('items.ranking') }}">
            <div class="header-icon"><img src="{{ asset('images/ranking.png') }}" alt="" /></div>
            <div class="header-icon-text">ランキング</div>
          </a>
        </li>
        <li class="nav-li">
          <a href="{{ route('favorite.index') }}">
            <div class="header-icon"><img src="{{ asset('images/hartmark.png') }}" alt="" /></div>
            <div class="header-icon-text">お気に入り</div>
          </a>
        </li>
        <li class="nav-li">
          <a href="{{ route('cart.index') }}">
            <div class="header-icon"><img src="{{ asset('images/cart.png') }}" alt="" /></div>
            <div class="header-icon-text">カート</div>
          </a>
        </li>

        <li x-data="{ open: false }" class="nav-li relative">
          <button 
            type="button"
            @click="open = !open"
            id="menuButton"
            class="humburger-menu"
          >
            <div class="header-icon">
              <img src="{{ asset('images/hamburger.png') }}" alt="" />
            </div>
            <div class="header-icon-text">メニュー</div>
          </button>
          <ul 
            x-show="open"
            x-cloak
            @click.outside="open = false"
            id="menuDropDown"
            class="absolute right-0"
          >
            <li class="menu-item">
              <a href="{{ route('mypage.reviews.index') }}">
                <img src="{{ asset("images/menu/review.svg") }}" alt="">
                <span>投稿したレビュー</span>
              </a>
            </li>
            <li class="menu-item">
              <a href="{{ route('mypage.orders.index') }}">
                <img src="{{ asset("images/menu/receipt.svg") }}" alt="">
                <span>購入履歴</span>
              </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('mypage.account.edit') }}">
                <img src="{{ asset("images/menu/account.svg") }}" alt="">
                <span>アカウント</span>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
    </div>
</header>