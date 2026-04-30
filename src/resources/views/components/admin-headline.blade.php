<div id="information">
  @guest
    <i class="text-base tracking-widest">管理者ページです ログインしてください</i>
  @endguest
  @auth('web_admin')
    <i class="text-base tracking-widest">管理者ページにログインしました</i>
  @endauth
</div>