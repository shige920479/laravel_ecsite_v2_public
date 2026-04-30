export function initDeleteForCreate(wrapper) {
  wrapper.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-image-delete]');
    if (! btn) return;

    handleDelete(btn);
  })
}

async function handleDelete(btn) {
  const index = btn.dataset.index;
  const wrapper = btn.closest('[data-image-block]')
  const url = deleteUrl.replace('_index_', index);

  try {
    const res = await fetch(url , {
      method: "DELETE",
      headers: {
        "X-CSRF-TOKEN": CSRF_TOKEN,
        "Accept": "application/json"
      },
    });

    if (! res.ok) {
      const { error } = await res.json();
      console.error("hanleDelete Error: " + error.message);
      alert(error.message || "画像の削除に失敗しました");
      return
    }

    const img = wrapper.querySelector('.preview-img')
    if(! img) {
      alert('削除する画像がありません');
      return
    }
    img.remove();
    wrapper.querySelector('[data-image-upload]').value = '';
    

  } catch (error) {
    console.error('通信エラー:', error);
    alert("通信エラーです。お手数ですが、再度、実行願います");
  }
}