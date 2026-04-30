import { initDeleteForCreate } from "./delete";
import { initUploadForCreate } from "./upload";
import { initUploadForUpdate} from "./update";
import { initDeleteForUpdate } from "./update-delete";
import { initDeleteForUploads } from "./uploads-delete";


document.addEventListener('DOMContentLoaded', () => {
  const wrapper = document.querySelector('[data-item-image]');
  if(! wrapper) return;

  initUploadForCreate(wrapper);
  initUploadForUpdate(wrapper);
  initDeleteForCreate(wrapper);
  initDeleteForUpdate(wrapper);
  initDeleteForUploads(wrapper);
})