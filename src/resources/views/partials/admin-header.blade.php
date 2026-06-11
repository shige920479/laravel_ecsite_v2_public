<header id="header">
  <div class="header-wrapper">
    <h1 class="site-title">
      <a href="{{ route('home.index') }}">
        <img src="{{ asset('images/logo.png') }}" alt="" />
      </a>
    </h1>
    <nav id="navi">
      <ul id="owner-nav-menu" class="admin">
        <li class="nav-items admin"><a href="{{ route('admin.owners.index') }}">オーナー一覧</a></li>
        @can('owner.create')
          <li class="nav-items admin"><a href="{{ route('admin.owners.create') }}">オーナー登録</a></li>
        @endcan
        <li class="nav-items admin"><a href="{{ route('admin.categories.index') }}">カテゴリー一覧</a></li>
        @can('category.create')
          <li class="nav-items admin"><a href="{{ route('admin.category.create') }}">カテゴリー登録</a></li>
        @endcan
        <li class="nav-items admin"><a href="{{ route('admin.reviews') }}">レビュー管理</a></li>
        @can('admin.view')
          <li class="nav-items admin"><a href="{{ route('admin.admins.index') }}">管理者一覧</a></li>
        @endcan
        @auth('web_admin')
          <li class="ml-10">
            <form action="{{ route('admin.logout') }}" method="post">
              @csrf
              <div id="logout-box">
                <div class="header-icon logout"><img src="{{ asset('images/logout.png') }}" alt="" /></div>
                <div class="header-icon-text">ログアウト</div>
              </div>
            </form>
          </li>
        @endauth
      </ul>
    </nav>
  </div>
</header>
