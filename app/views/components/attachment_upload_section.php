<div class="card mb-3">
  <div class="card-header">
    <strong>Attachments</strong>
  </div>
  <div class="card-body">

    <!-- Hidden context fields (from form controller) -->
    <input type="hidden" name="attachment_module" value="<?= $module ?>">
    <input type="hidden" name="attachment_section" value="<?= $section ?>">
    <input type="hidden" name="attachment_record_id" value="<?= $record_id ?>">
    <input type="hidden" name="attachment_status" value="<?= $status ?>">
    <input type="hidden" name="attachment_employee_no" value="<?= $employee_no ?>">
    <input type="hidden" name="attachment_full_name" value="<?= $full_name ?>">

    <!-- Dropzone UI -->
    <div id="attachment-dropzone" class="dropzone border border-dashed text-center p-4">
      <i class="ti ti-upload fs-2 text-muted"></i>
      <p class="text-muted mb-0">Drag & drop files here or click to upload</p>
      <input type="file" id="attachment-file" name="file" class="d-none" multiple>
    </div>

    <!-- Remarks (optional note for each upload) -->
    <div class="mt-3">
      <input type="text" name="attachment_remark" class="form-control" placeholder="Optional note about file...">
    </div>

    <!-- Preview uploaded files -->
    <div id="uploaded-files-list" class="mt-3">
      <!-- JS will populate this with uploaded files -->
    </div>

  </div>
</div>
