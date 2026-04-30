/**
 * @param {HTMLElement} wrapper 
 */

import { BASE_PATH } from "../../config";

export function initUploadForUpdate(wrapper) {
  const inputImages = wrapper.querySelectorAll('[data-update-upload]');
  if(inputImages.length < 1) return;

  inputImages.forEach((input) => {
    input.addEventListener('change', handleUpload);
  })
}

async function handleUpload(e) {
  const input = e.target;
  const imageWrapper = input.closest('[data-image-block]');
  const imageFile = input.files?.[0];
  if(! imageFile) return;

  const index = input.dataset.preview;
  const previewDiv = imageWrapper.querySelector('.preview-div');
  // const previewImg = previewDiv.querySelector('.preview-img');

  const formData = new FormData();
  formData.append('image', imageFile);
  formData.append('index', index);

  try {
    const res = await fetch(uploadUrl, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": CSRF_TOKEN,
        "Accept": 'application/json',
      },
      body: formData
    });

    if (res.status === 422) {
      const {errors} = await res.json();
      console.error('Validation errors:' + errors.image[0]);
      alert(errors.image[0] ?? '画像ファイルが正しくありません');
      this.value = '';
      return;
    }
    
    if (!res.ok) {
      const {error} = await res.json();
      console.error('Upload error: ' + error.message);
      alert(error.message || '画像のアップロードに失敗しました')
      this.value = '';
    }

    const data = await res.json();
    // if(previewImg) previewImg.remove();
    if(previewDiv) previewDiv.remove();
  
    const createDiv = document.createElement('div');
    createDiv.classList = 'preview-div';
    const createP = document.createElement('p');
    const createLabel = document.createElement('label');
    createLabel.innerText = "画像プレビュー";
    createP.appendChild(createLabel);
    createDiv.appendChild(createP);

    const elem = document.createElement('img');
    elem.classList = 'preview-img';
    elem.src = data.path;
    const btn = document.createElement('button');
    btn.classList = 'image-trush';
    btn.type ='button';
    btn.setAttribute('data-update-delete', "");
    btn.dataset.index = index;
    const trush = document.createElement('img');
    trush.classList = 'delete-icon';
    trush.src = `${BASE_PATH}/images/trush.png`;
    btn.appendChild(trush);

    createDiv.appendChild(elem);
    createDiv.appendChild(btn);

    imageWrapper.querySelector('[data-image-flex]').prepend(createDiv);
    
  } catch (error) {
    console.error('通信エラー:', error);
    alert('予期せぬエラーが発生しました');
    this.value = '';
  }
}