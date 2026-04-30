<header id="header">
  <div class="header-wrapper">
    <h1 class="site-title">
      <a href="{{ route('home.index') }}">
        <img src="{{ asset('images/logo.png') }}" alt="" />
      </a>
    </h1>
    <nav id="navi">
      <ul id="owner-nav-menu">
        <li class="nav-items"><a href="{{ route('admin.owners.index') }}">オーナー一覧</a></li>
        <li class="nav-items"><a href="{{ route('admin.owners.create') }}">オーナー登録</a></li>
        <li class="nav-items"><a href="{{ route('admin.categories.index') }}">カテゴリー一覧</a></li>
        <li class="nav-items"><a href="{{ route('admin.category.create') }}">カテゴリー登録</a></li>
      </ul>
    </nav>
    @auth('web_admin')
      <form action="{{ route('admin.logout') }}" method="post">
        @csrf
        <div id="logout-box">
          <div class="header-icon logout"><img src="{{ asset('images/logout.png') }}" alt="" /></div>
          <div class="header-icon-text">ログアウト</div>
        </div>
      </form>
    @endauth
  </div>
</header>
