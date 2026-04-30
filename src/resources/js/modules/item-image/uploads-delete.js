export function initDeleteForUploads(wrapper) {
  wrapper.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-uploads-delete]');
    if (! btn) return;

    if (! confirm('この画像を削除しますか？削除すると画像は元に戻せません')) return;
    handleDelete(btn);
  })
}

async function handleDelete(btn) {
  const block = btn.closest('[data-image-block]');
  const imageId = block.querySelector('input[name="item_image_ids[]"]').value;
  const deleteUrl = uploadsDeleteUrl.replace('PLACEHOLDER', imageId);

  try {
    const res = await fetch(deleteUrl, {
      method: 'DELETE',
      headers: {
          "X-CSRF-TOKEN": CSRF_TOKEN,
          "Accept": "application/json"
      },
    });

    if (! res.ok) {
      const data = await res.json();
      console.error('DB-error:' + data.error);
      alert(data.error);
      return;
    }

    const deleteDiv = block.querySelector('.current-div');
    if (deleteDiv) deleteDiv.remove();
    block.querySelector('input[name="item_image_ids[]"]').value = '';
    block.querySelector('input[name="filenames[]"]').value = '';
    const form = block.closest('form');
    const flag = form.querySelector('input[name="has-deleted"]');
    if (flag) flag.value = '1';

  } catch (error) {
    console.error('削除失敗:', error.message);
    alert("システムエラーです。お手数ですが、再度、実行願います");
  }
}


