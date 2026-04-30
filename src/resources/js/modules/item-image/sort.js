document.addEventListener('DOMContentLoaded', () => {
  const sortableList = document.getElementById('sortable-list');
  if (! sortableList) return;

  Sortable.create(sortableList, {
    animation: 200,
    onEnd: updateSortOrder
  });

  // 'sort_order'の順番を更新 [商品画像の編集]
  function updateSortOrder() {
    const items = document.querySelectorAll('#sortable-list .sortable-item');
    items.forEach((item, index) => {
      const input = item.querySelector("input[name='sort_order[]']");
      if(input) {
        input.value = index + 1;
      }
    })
  }
})