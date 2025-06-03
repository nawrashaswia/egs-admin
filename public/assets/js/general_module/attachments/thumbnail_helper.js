// File: public/assets/js/attachment_helpers/thumbnail_helper.js

/**
 * Render a visual preview (image or icon) for a file
 * @param {File} file
 * @returns {Promise<HTMLElement>} A preview container element
 */
export async function renderFileThumbnail(file) {
  const ext = file.name.split('.').pop().toLowerCase();
  const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(ext);

  const container = document.createElement('div');
  container.className = 'position-relative text-center small border rounded p-2 bg-white shadow-sm';
  container.style.width = '120px';

  const label = document.createElement('div');
  label.className = 'text-truncate mt-1';
  label.title = file.name;
  label.textContent = file.name;

  if (isImage) {
    const reader = new FileReader();
    const img = document.createElement('img');
    img.className = 'img-fluid rounded';
    img.style.maxHeight = '80px';
    img.alt = file.name;

    const loadPromise = new Promise((resolve, reject) => {
      reader.onload = e => {
        img.src = e.target.result;
        container.prepend(img);
        container.appendChild(label);
        resolve(container);
      };
      reader.onerror = () => reject(new Error("Image preview failed"));
    });

    reader.readAsDataURL(file);
    return loadPromise;

  } else {
    const icon = document.createElement('i');
    icon.className = `ti ti-file-type-${ext} text-muted`; // Uses Tabler file icon if available
    icon.style.fontSize = '2rem';

    container.prepend(icon);
    container.appendChild(label);
    return container;
  }
}
