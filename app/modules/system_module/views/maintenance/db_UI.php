<?php
// Load DB operation summary if available
$summary = $_SESSION['db_summary'] ?? null;
unset($_SESSION['db_summary']);
?>

<div class="card shadow-sm">
  <div class="card-header">
    <h3 class="card-title"><i class="ti ti-database me-2"></i>Database</h3>
  </div>

  <div class="card-body d-flex flex-column gap-3">

    <!-- 🔄 Backup + Info -->
    <div class="d-flex flex-wrap gap-2">
      <a href="<?= BASE_URL ?>/system/maintenance/db_backup" class="btn btn-outline-primary">
        <i class="ti ti-database-export me-1"></i>Backup Now
      </a>
      <a href="<?= BASE_URL ?>/system/maintenance/db_info_export" class="btn btn-outline-info">
        <i class="ti ti-schema me-1"></i>Export DB Schema
      </a>
    </div>

    <!-- 🔁 Restore Section -->
    <form action="<?= BASE_URL ?>/system/maintenance/db_restore" method="POST" enctype="multipart/form-data" class="border rounded p-3 bg-light">
      <label class="form-label mb-2"><i class="ti ti-upload me-1"></i>Upload SQL File to Restore:</label>
      <input type="file" name="sql_file" class="form-control form-control-sm mb-2" accept=".sql" required>
      <button type="submit" class="btn btn-sm btn-outline-warning">
        <i class="ti ti-database-import me-1"></i>Upload & Restore
      </button>
    </form>

    <!-- 🧾 Operation Summary -->
    <?php if ($summary): ?>
      <div class="alert alert-info mt-2 p-2 small">
        <div><strong>📝 Last Operation:</strong></div>
        <pre class="mb-0"><?= htmlspecialchars($summary) ?></pre>
      </div>
    <?php endif; ?>

  </div>
</div>
