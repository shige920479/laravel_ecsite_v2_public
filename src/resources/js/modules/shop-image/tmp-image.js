// 画像ファイルの保存
document.addEventListener('DOMContentLoaded', () => {
  const inputImage = document.getElementById('input-image');
  if(! inputImage) return;

  inputImage.addEventListener('change', async function (e) {
    try {
      const imageFile = e.target.files[0];
      const requestUrl = e.target.dataset.uploadUrl;
      const previewDiv = document.getElementById('file-preview');

      if(! imageFile) return;

      const formData = new FormData();
      formData.append('image', imageFile);

      const res = await fetch(requestUrl, {
        method: "POST",
        headers: {
          "X-CSRF-TOKEN": CSRF_TOKEN,
          "Accept": 'application/json',
        },
        body: formData
      });

      if(res.status === 422) {
        const {errors} = await res.json();
        console.error('Validation errors:' + errors.image[0]);
        alert(errors.image[0] ?? '画像ファイルが正しくありません');
        e.target.value = '';
        return;
      }

      if(! res.ok) {
        const {error} = await res.json();
        e.target.value = '';
        alert(error.message);
        return;
      }

      const data = await res.json();
      previewDiv.innerHTML = `<img src="${data.path}" alt="preview">`;
      const selectedImage = document.getElementById('shop-tmp-img');
      if(selectedImage) selectedImage.remove();

    } catch (error) {
        console.error('通信エラー:', error);
        alert('予期せぬエラーが発生しました');
    }
  })
})