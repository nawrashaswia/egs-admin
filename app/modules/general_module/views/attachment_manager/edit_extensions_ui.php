<?php


use App\Core\DB;
use App\Helpers\Core\FlashHelper;

// 🧠 Boot Kernel if not already booted (for standalone access)
if (!class_exists(DB::class)) {
    require_once dirname(__DIR__, 4) . '/core/AppKernel.php';
    \App\Core\AppKernel::boot();
}

$pdo = DB::connect();
$ruleId = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ❌ If ID is invalid
if (!$ruleId) {
    FlashHelper::set('error', 'Invalid rule ID.');
    header('Location: ' . BASE_URL . '/general/attachment_manager/settings_ui');
    exit;
}

// 📥 Fetch rule data
$stmt = $pdo->prepare("SELECT * FROM attachment_rules WHERE id = ?");
$stmt->execute([$ruleId]);
$rule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$rule) {
    FlashHelper::set('error', 'Rule not found.');
    header('Location: ' . BASE_URL . '/general/attachment_manager/settings_ui');
    exit;
}

$extList = implode(', ', array_map('trim', explode(',', $rule['allowed_extensions'])));
?>

<div class="page-header d-print-none mb-4">
  <h2 class="page-title">✏️ Edit Attachment Rule</h2>
</div>

<div class="row">
  <div class="col-md-6">
    <form method="post" action="<?= BASE_URL ?>/general/attachment_manager/edit_extensions">
      <input type="hidden" name="id" value="<?= (int)$rule['id'] ?>">

      <div class="card">
        <div class="card-body">

          <div class="mb-3">
            <label class="form-label">Rule Name</label>
            <input type="text" name="rule_name" class="form-control" required
                   value="<?= htmlspecialchars($rule['rule_name'], ENT_QUOTES) ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Allowed Extensions (comma separated)</label>
            <input type="text" name="allowed_extensions" class="form-control" required
                   value="<?= htmlspecialchars($extList, ENT_QUOTES) ?>">
            <div class="form-text">Example: jpg, png, pdf</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Max File Size (MB)</label>
            <input type="number" name="max_size_mb" class="form-control" min="1" required
                   value="<?= (int)$rule['max_size_mb'] ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($rule['notes'], ENT_QUOTES) ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
              <option value="1" <?= $rule['is_active'] ? 'selected' : '' ?>>Active</option>
              <option value="0" <?= !$rule['is_active'] ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>

          <div class="mt-4">
            <button class="btn btn-primary" type="submit">💾 Save Changes</button>
            <a href="<?= BASE_URL ?>/general/attachment_manager/settings_ui" class="btn btn-secondary">Cancel</a>
          </div>

        </div>
      </div>
    </form>
  </div>
</div>
