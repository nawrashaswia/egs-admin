import { compressFile, suggestFilename, extractModuleName, setupScanButtons } from './attachment_helpers.js';
import { refreshAttachmentList } from './general_module/attachments/refresh_list.js';
import { renderFileThumbnail } from './general_module/attachments/thumbnail_helper.js';

let selectedCompressedFile = null;
let uploaderInitialized = false;

function updateUploadButtonState() {
  const uploadBtn = document.getElementById('upload-btn');
  const filenameInput = document.getElementById('custom-filename');
  if (!uploadBtn || !filenameInput) return;
  uploadBtn.disabled = !(selectedCompressedFile && filenameInput.value.trim());
}

export function setupUploader() {
  const modal = document.getElementById('attachment-upload-modal');
  if (!modal) return;

  modal.addEventListener('shown.bs.modal', () => {
    if (uploaderInitialized) return;
    uploaderInitialized = true;

    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('real-file-input');
    const uploadForm = document.getElementById('attachment-upload-form');
    const filenameInput = document.getElementById('custom-filename');

    if (!dropArea || !fileInput || !uploadForm) return;

    dropArea.addEventListener('click', () => fileInput.click());

    dropArea.addEventListener('dragover', e => {
      e.preventDefault();
      dropArea.classList.add('border-primary');
    });

    dropArea.addEventListener('dragleave', () => {
      dropArea.classList.remove('border-primary');
    });

    dropArea.addEventListener('drop', e => {
      e.preventDefault();
      dropArea.classList.remove('border-primary');
      const files = e.dataTransfer?.files;
      if (files && files.length > 0) {
        handleFile(files[0]);
      }
    });

    fileInput.addEventListener('change', e => {
      const files = e.target.files;
      if (files && files.length > 0) {
        handleFile(files[0]);
      }
    });

    if (filenameInput) {
      filenameInput.addEventListener('input', updateUploadButtonState);
    }

    uploadForm.addEventListener('submit', e => {
      e.preventDefault();
      uploadFile();
    });

    setupScanButtons();
  });

  modal.addEventListener('hide.bs.modal', () => {
    selectedCompressedFile = null;
    uploaderInitialized = false;
    updateUploadButtonState();
  });
}

async function handleFile(file) {
  const previewZone = document.getElementById('preview-zone');
  const previewFiles = document.getElementById('preview-files');
  const filenameInput = document.getElementById('custom-filename');
  const fileInput = document.getElementById('real-file-input');
  const pathPreview = document.getElementById('path-preview');
  const statusBox = document.getElementById('upload-status');

  if (!previewZone || !previewFiles || !statusBox || !fileInput) return;

  previewZone.classList.remove('d-none');
  previewFiles.innerHTML = '';
  selectedCompressedFile = null;
  updateUploadButtonState();

  const ext = file.name.split('.').pop().toLowerCase();
  const container = await renderFileThumbnail(file);

  const removeBtn = document.createElement('button');
  removeBtn.type = 'button';
  removeBtn.className = 'btn btn-sm btn-link text-danger position-absolute top-0 end-0';
  removeBtn.innerHTML = '<i class="ti ti-x"></i>';
  removeBtn.onclick = () => {
    fileInput.value = '';
    previewFiles.innerHTML = '';
    previewZone.classList.add('d-none');
    selectedCompressedFile = null;
    if (filenameInput) filenameInput.value = '';
    if (pathPreview) {
      pathPreview.innerText = 'Loading...';
      pathPreview.parentElement?.classList.add('d-none');
    }
    statusBox.innerHTML = '';
    updateUploadButtonState();
  };
  container.appendChild(removeBtn);
  previewFiles.appendChild(container);

  if (filenameInput) {
    filenameInput.value = `${suggestFilename(window.currentAttachmentRef)}.${ext}`;
    filenameInput.addEventListener('input', updateUploadButtonState);
  }
  updateUploadButtonState();

  if (pathPreview) {
    const module = extractModuleName();
    pathPreview.innerText = `/uploads/attachments/${module}/${window.currentAttachmentRef}/`;
    pathPreview.parentElement?.classList.remove('d-none');
  }

  statusBox.textContent = 'Compressing file...';
  try {
    const compressed = await compressFile(file);
    selectedCompressedFile = compressed;

    const originalMB = (file.size / 1024 / 1024).toFixed(2);
    const compressedMB = (compressed.size / 1024 / 1024).toFixed(2);
    const savedPercent = 100 - Math.round((compressed.size / file.size) * 100);

    statusBox.innerHTML = `
      <ul>
        <li><strong>${file.name}</strong></li>
        <li>${originalMB}MB → ${compressedMB}MB (${savedPercent}% saved)</li>
      </ul>
    `;
    updateUploadButtonState();
  } catch (err) {
    statusBox.innerHTML = `<span class="text-danger">Compression failed.</span>`;
    updateUploadButtonState();
  }
}

async function uploadFile() {
  const filename = document.getElementById('custom-filename')?.value.trim();
  const statusBox = document.getElementById('upload-status');

  if (!filename || !selectedCompressedFile) {
    statusBox.innerHTML = `<span class="text-danger">Missing file or filename.</span>`;
    return;
  }

  const formData = new FormData();
  formData.append('reference_number', window.currentAttachmentRef);
  formData.append('custom_filename', filename);
  formData.append('file', selectedCompressedFile);

  if (window.currentAttachmentRuleId) {
    formData.append('rule_id', window.currentAttachmentRuleId);
  }

  statusBox.innerHTML += '<div>Uploading...</div>';

  try {
        const res = await fetch('/ajax/general_module/handle_attachment_upload', {
      method: 'POST',
      body: formData
    });

    const contentType = res.headers.get('content-type') || '';

    if (!res.ok) {
      const errorText = contentType.includes('application/json')
        ? (await res.json()).message
        : await res.text();
      throw new Error(errorText || `HTTP error ${res.status}`);
    }

    if (!contentType.includes('application/json')) {
      throw new Error('Invalid server response type');
    }

    const data = await res.json();

    if (data.success) {
      statusBox.innerHTML += '<div class="text-success">Upload complete.</div>';
      sessionStorage.setItem('attachmentUploadSuccess', '1');
      refreshAttachmentList();
      setTimeout(() => document.getElementById('attachment-upload-modal')?.querySelector('.btn-close')?.click(), 1000);
    } else {
      statusBox.innerHTML += `<div class="text-danger">${data.message}</div>`;
    }

  } catch (error) {
    statusBox.innerHTML += `<div class="text-danger">Upload failed: ${error.message}</div>`;
  }
}
