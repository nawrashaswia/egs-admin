export function buildAttachmentForm(ref) {
  return `
    <form id="attachment-upload-form" enctype="multipart/form-data" autocomplete="off">
      <input type="hidden" name="reference_number" value="${ref}">
      <div class="row g-4 align-items-stretch">
        <!-- Left Column: Drag & Drop and Scan -->
        <div class="col-12 col-md-6 d-flex flex-column gap-4">
          <!-- Drag & Drop Upload -->
          <div class="card flex-fill shadow-sm border-0 mb-4">
            <div class="card-header bg-blue-lt d-flex align-items-center justify-content-between">
              <h3 class="card-title mb-0 text-blue"><i class="ti ti-cloud-upload me-2"></i>Upload File</h3>
            </div>
            <div class="card-body p-4">
              <label class="form-label fw-bold" for="real-file-input">Select or Drop File <span class="text-danger">*</span></label>
              <div id="drop-area" class="border border-dashed rounded-2 p-4 text-center text-muted position-relative tabler-drop-area" style="cursor: pointer; min-height: 160px; transition: border-color 0.2s;" role="button" tabindex="0" aria-label="Click or drag and drop files here">
                <i class="ti ti-cloud-upload text-primary" style="font-size: 2.5rem;"></i>
                <div class="mt-2 fs-5">Drag a file here or <span class="text-primary text-decoration-underline">click to browse</span></div>
                <input type="file" name="file" style="display: none;" id="real-file-input" aria-label="Select file to upload" accept="*/*">
              </div>
              <div class="form-text text-muted">Allowed: images, PDF, docs, zip, etc. (see rules)</div>
            </div>
          </div>
          <!-- Scan Document Card -->
          <div class="card flex-fill shadow-sm border-0">
            <div class="card-header bg-green-lt d-flex align-items-center">
              <h3 class="card-title mb-0 text-green"><i class="ti ti-device-scanner me-2"></i>Scan Document</h3>
            </div>
            <div class="card-body p-4">
              <p class="text-muted mb-3">Use the scanner to capture documents directly into your attachments. Supported: feeder or glass.</p>
              <button type="button" class="btn btn-outline-success btn-lg w-100 d-flex align-items-center justify-content-center gap-2" data-scan aria-label="Scan document">
                <i class="ti ti-device-scanner"></i> <span>Scan Now</span>
              </button>
              <div class="form-text mt-2 text-muted">Scanner will auto-detect the source.</div>
              <div id="scan-status" class="mt-3 text-center text-muted small d-none"></div>
            </div>
          </div>
        </div>
        <!-- Right Column: File Info, Preview, Upload -->
        <div class="col-12 col-md-6 d-flex flex-column gap-4">
          <div class="card flex-fill shadow-sm border-0">
            <div class="card-header bg-gray-lt d-flex align-items-center">
              <h3 class="card-title mb-0 text-dark"><i class="ti ti-info-circle me-2"></i>File Information</h3>
            </div>
            <div class="card-body p-4">
              <!-- Preview Zone -->
              <div id="preview-zone" class="mb-3 border rounded-2 p-3 bg-light d-none animate__animated animate__fadeIn" aria-live="polite">
                <div class="text-muted small mb-2"><i class="ti ti-eye me-1"></i>File Preview:</div>
                <div id="preview-files" class="d-flex flex-wrap gap-2 align-items-center">
                  <!-- JS will render thumbnails or file icons here -->
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label" for="custom-filename">Save As (filename) <span class="text-danger">*</span></label>
                <input class="form-control" type="text" name="custom_filename" id="custom-filename" required placeholder="Auto-suggested..." aria-describedby="filenameHelp">
                <div id="filenameHelp" class="form-text">You can change the filename before uploading.</div>
              </div>
              <div class="mb-3 text-muted small">
                <span class="fw-bold">📁 Save Path:</span> <code id="path-preview">Loading...</code>
              </div>
            </div>
            <div class="card-footer bg-transparent text-end">
              <button type="submit" class="btn btn-primary" id="upload-btn" disabled>
                <i class="ti ti-upload me-1"></i> Upload File
              </button>
            </div>
          </div>
          <!-- Upload Status -->
          <div id="upload-status" class="mt-4 text-muted small" aria-live="polite"></div>
        </div>
      </div>
    </form>
    <!--
      To make the modal wider, add 'modal-xl' to the modal-dialog in the HTML (attachment_block.php):
      <div class="modal-dialog modal-xl modal-dialog-centered">
    -->
  `;
}