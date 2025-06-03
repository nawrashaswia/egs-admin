<?php

use App\Helpers\General_Module\AttachmentUI;
use App\Helpers\Core\HiLoSequence;
use App\Core\DB;

$ref = HiLoSequence::get();
$GLOBALS['ref'] = $ref;

?>

<div class="page-header d-print-none mb-4">
  <h2 class="page-title">➕ Add New Attachment Rule</h2>
  <p class="text-muted">Define a restriction rule for file uploads including allowed extensions and max size.</p>
</div>

<div class="row">
  <!-- Form Section -->
  <div class="col-md-6">
    <form method="post" action="<?= BASE_URL ?>/general/attachment_manager/save_rule">
      <div class="card shadow-sm">
        <div class="card-body">

          <!-- Rule Name -->
          <div class="mb-3">
            <label class="form-label required">Rule Name</label>
            <input type="text" name="rule_name" class="form-control" placeholder="e.g., Scanned Files" required>
          </div>

          <!-- Allowed Extensions -->
          <div class="mb-3">
            <label class="form-label required">Allowed Extensions</label>
            <input type="text" name="allowed_extensions" class="form-control" placeholder="e.g., pdf,jpg,png" required>
            <small class="form-hint">Separate with commas. Use <code>*</code> to allow all types.</small>
          </div>

          <!-- Max Size -->
          <div class="mb-3">
            <label class="form-label required">Max Size (MB)</label>
            <input type="number" name="max_size_mb" class="form-control" min="1" placeholder="e.g., 25" required>
          </div>

          <!-- Notes -->
          <div class="mb-3">
            <label class="form-label">Notes / Clarifications</label>
            <textarea name="notes" rows="3" class="form-control" placeholder="Clarify usage purpose or restriction logic..."></textarea>
          </div>

          <!-- Status -->
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
              <option value="1" selected>Active</option>
              <option value="0">Disabled</option>
            </select>
          </div>

          <!-- Submit -->
          <div class="mt-4">
            <button type="submit" class="btn btn-success w-100">
              <i class="ti ti-check me-2"></i> Save Rule
            </button>
            <a href="<?= BASE_URL ?>/general/attachment_manager/settings_ui" class="btn btn-link w-100 mt-2 text-center">Cancel</a>
          </div>

        </div>
      </div>
    </form>
  </div>

  <!-- Extensions Guide Section -->
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header">
        <strong>🔠 Common Extensions in Use</strong>
      </div>
      <div class="card-body" style="max-height: 400px; overflow-y: auto;">

        <?php
        try {
            $stmt = DB::connect()->query("SELECT allowed_extensions FROM attachment_rules WHERE is_active = 1");
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $allExt = [];
            foreach ($rows as $extStr) {
                $extArr = array_map('trim', explode(',', $extStr));
                foreach ($extArr as $ext) {
                    if ($ext !== '*') {
                        $allExt[] = strtolower($ext);
                    }
                }
            }

            $uniqueExts = array_unique($allExt);
            sort($uniqueExts);
        } catch (Throwable $e) {
            $uniqueExts = [];
        }

        foreach ($uniqueExts as $ext): ?>
          <span class="badge bg-secondary-lt text-uppercase mb-1 me-1">
            <?= htmlspecialchars($ext) ?>
          </span>
        <?php endforeach; ?>

        <?php AttachmentUI::render($ref, 1); ?>

      </div>
    </div>
  </div>
</div>
