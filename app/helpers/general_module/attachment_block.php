<?php
if (!isset($ref)) {
    echo "<div class='alert alert-danger'>Attachment reference number not set.</div>";
    return;
}
?>

<!-- Make reference globally available to JS -->
<script>
  window.currentAttachmentRef = <?= json_encode($ref) ?>;
  if (!window.currentAttachmentRef) {
    console.warn("⚠️ Attachment ref is not defined.");
  }
</script>

<!-- Toast for upload success -->
<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 2000;">
  <div id="attachment-success-toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="min-width: 260px;">
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center gap-2">
        <i class="ti ti-check text-white"></i>
        <span>Attachment uploaded successfully!</span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<div id="attachment-launcher" class="card position-fixed bottom-0 end-0 m-4 shadow-xl border-0 tabler-glass" style="width: 420px; z-index: 1080; border-radius: 1.25rem; backdrop-filter: blur(6px); background: rgba(255,255,255,0.85);">
  <div class="card-header d-flex justify-content-between align-items-center py-2 px-4 bg-blue-lt border-0" style="border-top-left-radius: 1.25rem; border-top-right-radius: 1.25rem;">
    <div class="d-flex align-items-center gap-2">
      <span class="bg-blue text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 2.2rem; height: 2.2rem;"><i class="ti ti-paperclip" style="font-size: 1.3rem;"></i></span>
      <span class="fw-semibold text-blue" style="font-size: 1.08rem; letter-spacing: 0.5px;">Attachments</span>
    </div>
    <div class="d-flex align-items-center gap-1">
      <button type="button" class="btn btn-icon btn-sm btn-light border-0" id="attachment-minimize-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Minimize" aria-label="Minimize Attachments">
        <i class="ti ti-chevron-down"></i>
      </button>
      <button type="button" class="btn btn-icon btn-sm btn-primary" id="attachment-upload-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Upload Files" data-bs-toggle="modal" data-bs-target="#attachment-upload-modal" aria-label="Upload Files">
        <i class="ti ti-upload" style="font-size: 1.15rem;"></i>
      </button>
    </div>
  </div>
  <div class="card-body p-4 pt-3" id="attachment-body-content" style="font-size: 0.97rem; border-bottom-left-radius: 1.25rem; border-bottom-right-radius: 1.25rem;">
    <div class="mb-2 d-flex align-items-center gap-2">
      <span class="text-muted">📄 Ref:</span>
      <span class="badge bg-blue text-white text-uppercase fw-bold px-3 py-1 shadow-sm" style="font-size: 0.93em; letter-spacing: 1px;"><?= htmlspecialchars($ref) ?></span>
    </div>
    <hr class="my-2" style="opacity:0.13;">
    <div class="text-muted mb-3 d-flex align-items-center gap-2">
      <i class="ti ti-info-circle me-1"></i>
      <span>Click the <b>upload</b> button to add or manage your files. All files are securely tied to this form.</span>
    </div>
    <div id="attachment-list" class="mb-2 attachment-list-enhanced">
      <div id="attachment-empty-msg" class="text-secondary fst-italic text-center py-3" style="font-size: 1.01em; background: #f6f8fa; border-radius: 0.5rem;">
        <i class="ti ti-folder-open me-1"></i> No files uploaded yet.
      </div>
    </div>
  </div>
  <div class="card-body p-4 pt-3 d-none" id="attachment-body-minimized" style="font-size: 0.97rem; border-bottom-left-radius: 1.25rem; border-bottom-right-radius: 1.25rem;">
    <div id="attachment-list-minimized" class="attachment-list-enhanced">
      <!-- Only the attachments table will be shown here -->
    </div>
    <div class="text-center mt-2">
      <button type="button" class="btn btn-sm btn-outline-primary" id="attachment-maximize-btn" aria-label="Maximize Attachments">
        <i class="ti ti-chevron-up"></i> Show Details
      </button>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="attachment-upload-modal" tabindex="-1" role="dialog" aria-labelledby="attachmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="border-radius: 1.1rem;">
      <div class="modal-header sticky-top bg-white" style="z-index:2; border-top-left-radius: 1.1rem; border-top-right-radius: 1.1rem;">
        <h5 class="modal-title d-flex align-items-center gap-2" id="attachmentModalLabel">
          <i class="ti ti-upload me-1 text-primary"></i> <span>Upload Attachments</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="attachment-upload-body" style="background: #f8fafd; border-bottom-left-radius: 1.1rem; border-bottom-right-radius: 1.1rem;">
        <!-- Uploader will be injected by JS -->
        <div class="text-center text-muted py-5">
          <i class="ti ti-loader ti-spin me-2"></i>Loading uploader...
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Minimize/maximize logic
(function() {
  const launcher = document.getElementById('attachment-launcher');
  const bodyContent = document.getElementById('attachment-body-content');
  const bodyMinimized = document.getElementById('attachment-body-minimized');
  const minimizeBtn = document.getElementById('attachment-minimize-btn');
  const maximizeBtnId = 'attachment-maximize-btn';

  if (minimizeBtn && bodyContent && bodyMinimized) {
    minimizeBtn.addEventListener('click', function() {
      bodyContent.classList.add('d-none');
      bodyMinimized.classList.remove('d-none');
      // Copy the attachment list to minimized view
      const list = document.getElementById('attachment-list');
      const minList = document.getElementById('attachment-list-minimized');
      if (list && minList) {
        minList.innerHTML = list.innerHTML;
      }
    });
  }
  document.addEventListener('click', function(e) {
    if (e.target && e.target.id === maximizeBtnId) {
      bodyContent.classList.remove('d-none');
      bodyMinimized.classList.add('d-none');
    }
  });
})();
// Ensure upload button triggers modal
(function() {
  const uploadBtn = document.getElementById('attachment-upload-btn');
  if (uploadBtn) {
    uploadBtn.addEventListener('click', function() {
      const modal = document.getElementById('attachment-upload-modal');
      if (modal) {
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modal);
        modalInstance.show();
      }
    });
  }
})();
// Enhance attachment list display (truncate long names, add tooltips, icons)
(function() {
  function enhanceAttachmentList() {
    document.querySelectorAll('.attachment-list-enhanced .attachment-row').forEach(row => {
      const nameCell = row.querySelector('.attachment-filename');
      if (nameCell && nameCell.textContent.length > 32) {
        nameCell.title = nameCell.textContent;
        nameCell.textContent = nameCell.textContent.slice(0, 28) + '...';
      }
    });
  }
  // Run on DOMContentLoaded and after list refresh
  document.addEventListener('DOMContentLoaded', enhanceAttachmentList);
  document.addEventListener('attachment-list-refreshed', enhanceAttachmentList);
})();
// Show toast after upload and modal close
(function() {
  const modal = document.getElementById('attachment-upload-modal');
  const toastEl = document.getElementById('attachment-success-toast');
  if (modal && toastEl) {
    modal.addEventListener('hidden.bs.modal', function() {
      if (sessionStorage.getItem('attachmentUploadSuccess') === '1') {
        const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
        toast.show();
        sessionStorage.removeItem('attachmentUploadSuccess');
      }
    });
  }
})();
</script>

<style>
#attachment-launcher {
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.3s cubic-bezier(.4,2,.3,1);
  backdrop-filter: blur(6px);
}
#attachment-launcher .btn-icon:hover {
  background-color: #206bc4 !important;
  color: white !important;
}
#attachment-launcher .card-header {
  border-bottom: none;
}
#attachment-launcher .card-body {
  border-bottom-left-radius: 1.25rem;
  border-bottom-right-radius: 1.25rem;
}
.attachment-list-enhanced .attachment-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0.5rem;
  border-radius: 0.4rem;
  margin-bottom: 0.15rem;
  background: #f8fafd;
  font-size: 0.97em;
  transition: background 0.2s;
}
.attachment-list-enhanced .attachment-row:hover {
  background: #e7f1ff;
}
.attachment-list-enhanced .attachment-filename {
  flex: 1 1 0%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}
.attachment-list-enhanced .attachment-icon {
  color: #206bc4;
  font-size: 1.1em;
}
</style>
