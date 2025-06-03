import { buildAttachmentForm } from './general_module/attachments/form_builder.js';
import { suggestFilename, extractModuleName } from './attachment_helpers.js';

// Only initialize if we're on a page with attachments
if (document.getElementById('attachment-upload-modal') && window.currentAttachmentRef) {
  document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById('attachment-upload-modal');
    const body = document.getElementById('attachment-upload-body');

    if (!modal || !body) {
      console.warn('Attachment modal: Required DOM elements missing.');
      return;
    }

    // Store the element that had focus before modal opened
    let previousActiveElement = null;

    modal.addEventListener('show.bs.modal', () => {
      // Store the element that had focus
      previousActiveElement = document.activeElement;

      // 🧱 Inject uploader form into modal body
      body.innerHTML = buildAttachmentForm(window.currentAttachmentRef);

      // ✍️ Suggest filename
      const filenameField = document.querySelector('[data-filename-input]');
      if (filenameField) {
        filenameField.value = suggestFilename(window.currentAttachmentRef);
      }

      // 📁 Set path preview text
      const pathPreview = document.getElementById('path-preview');
      if (pathPreview) {
        const module = extractModuleName();
        pathPreview.innerText = `/uploads/attachments/${module}/${window.currentAttachmentRef}/`;
        pathPreview.parentElement?.classList.add('d-none');
      }

      // 🔎 Focus the file input after a short delay to ensure modal is fully shown
      setTimeout(() => {
        const fileInput = document.getElementById('real-file-input');
        if (fileInput) {
          fileInput.focus();
        }
      }, 300);
    });

    // Handle modal hidden event
    modal.addEventListener('hidden.bs.modal', () => {
      // Restore focus to the element that had it before modal opened
      if (previousActiveElement) {
        previousActiveElement.focus();
      }
    });
  });
}
