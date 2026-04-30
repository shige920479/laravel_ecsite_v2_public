document.addEventListener('DOMContentLoaded', () => {
  const linkBtn = document.querySelectorAll('[data-has-session]');
  if (linkBtn.length < 1) return;

  linkBtn.forEach((btn) => {
    btn.addEventListener('click', async (e) => {
      if (! confirm('入力内容が破棄されますが、宜しいでしょうか？')) return;

      const hiddenInput = document.getElementById('route-name');
      if (! hiddenInput) return;
      hiddenInput.value = e.target.dataset.route;
      e.target.closest('form').submit();
    })
  })

})