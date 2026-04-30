// ログアウト
document.addEventListener('DOMContentLoaded', () => {
  const logoutBtn = document.getElementById('logout-box');
  if(! logoutBtn) return;

  logoutBtn.addEventListener('click', function () {
    if(! confirm('ログアウトしますが宜しいですか？')) {
      return;
    }
    this.closest('form').submit();
  });
 
});