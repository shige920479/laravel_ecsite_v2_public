
@if (request()->routeIs('items.ranking'))
  <div id="ranking-headline"><i id="rank-en">Ranking</i><i id="rank-ja">ランキング</i></div>
@else
  <div id="information"><i>ただいまセール中</i></div>
@endif
