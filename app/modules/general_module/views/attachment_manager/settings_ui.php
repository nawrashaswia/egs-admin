<?php

\App\Core\Logger::startConstructionTrace(__FILE__, 'Tracing user login flow');
\App\Helpers\general_module\logmanager\ConstructionTraceScanner::autoLogPageLoad(__FILE__);


use App\Helpers\General_Module\AttachmentUI;
use App\Helpers\Core\HiLoSequence;
use App\Core\DB;

$ref = HiLoSequence::get();
$GLOBALS['ref'] = $ref;

// ✅ Initialize reference number once (module is auto-detected safely here)
$ref = $GLOBALS['ref'] ?? null;
$ruleId = 1;

// ✅ Fetch rules
try {
    $stmt = DB::connect()->prepare("SELECT * FROM attachment_rules ORDER BY id ASC");
    $stmt->execute();
    $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $rules = [];
}

// ✅ Helper to render extension badges
function renderExtensionBadges(string $extensions): string {
    if (trim($extensions) === '*') {
        return '<span class="badge bg-success-lt text-success">All</span>';
    }

    $extArray = array_map('trim', explode(',', $extensions));
    $output = '';
    foreach ($extArray as $ext) {
        $icon = strtolower($ext);
        $output .= '<span class="badge bg-secondary-lt text-uppercase d-inline-flex align-items-center me-1 mb-1">
                      <i class="ti ti-file-type-' . $icon . '" style="font-size: 0.85rem;"></i>
                      <span class="ms-1">' . htmlspecialchars($ext) . '</span>
                    </span>';
    }
    return $output;
}
?>

<div class="page-header d-print-none mb-4">
  <h2 class="page-title"><i class="ti ti-paperclip me-2"></i> Attachment Rules</h2>
  <p class="text-muted">Manage allowed extensions and file size restrictions for uploads.</p>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>📁 Registered Rules</strong>
    <a href="<?= BASE_URL ?>/general/attachment_manager/attachment_rule_add" class="btn btn-sm btn-primary">
      <i class="ti ti-plus"></i> Add Rule
    </a>
  </div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Rule Name</th>
          <th>Extensions</th>
          <th>Max Size (MB)</th>
          <th>Notes</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rules as $index => $rule): ?>
        <tr>
          <td><?= $index + 1 ?></td>
          <td><?= htmlspecialchars($rule['rule_name']) ?></td>
          <td><?= renderExtensionBadges($rule['allowed_extensions']) ?></td>
          <td><?= htmlspecialchars($rule['max_size_mb']) ?> MB</td>
          <td><?= htmlspecialchars($rule['notes']) ?></td>
          <td>
            <?php if ($rule['is_active']): ?>
              <span class="badge bg-success-lt text-success">Active</span>
            <?php else: ?>
              <span class="badge bg-muted-lt text-muted">Inactive</span>
            <?php endif; ?>
          </td>
          <td class="text-end">
            <a href="<?= BASE_URL ?>/general/attachment_manager/edit_extensions_ui?id=<?= $rule['id'] ?>" class="btn btn-sm btn-warning">
              <i class="ti ti-edit"></i>
            </a> 
            <a href="<?= BASE_URL ?>/general/attachment_manager/toggle_extension_rule?id=<?= $rule['id'] ?>"
               class="btn btn-sm btn-<?= $rule['is_active'] ? 'secondary' : 'success' ?>"
               onclick="return confirm('Are you sure you want to <?= $rule['is_active'] ? 'deactivate' : 'activate' ?> this rule?')">
              <i class="ti ti-power"></i>
            </a>
            <a href="<?= BASE_URL ?>/general/attachment_manager/delete_extension_rule?id=<?= $rule['id'] ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Are you sure you want to delete this rule?')">
              <i class="ti ti-trash"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>


  </div>
</div>