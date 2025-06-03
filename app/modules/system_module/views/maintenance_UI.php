<?php
$title = $title ?? 'Maintenance';
$config = require CONFIG_PATH . '/app.php';
$debugMode = $config['debug'] ?? false;
?>

<div class="page-header d-print-none mb-4">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">
          <i class="ti ti-tool me-2"></i><?= htmlspecialchars($title) ?> Tools
        </h2>
        <div class="text-muted mt-1">Backup, restore, and inspect your system confidently.</div>
      </div>
      <div class="col-auto">
        <form method="post" action="<?= BASE_URL ?>/system/maintenance/toggle_debug_mode" onsubmit="return confirmDebugModeSwitch(this);">
          <input type="hidden" name="debug" value="<?= $debugMode ? '0' : '1' ?>">
          <button type="submit" class="btn btn-<?= $debugMode ? 'success' : 'secondary' ?>">
            <i class="ti ti-bug me-1"></i>
            <?= $debugMode ? 'Debug Mode: ON' : 'Debug Mode: OFF' ?>
          </button>
        </form>
      </div>
      <div class="col-auto">
        <button id="trigger-permission-scan" class="btn btn-outline-primary">
          <i class="ti ti-shield-check me-1"></i> Sync Permissions
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function confirmDebugModeSwitch(form) {
  const enabling = form.querySelector('input[name="debug"]').value === '1';
  return confirm(
    enabling
      ? 'Enabling Debug Mode will show extra debug info, stack traces, and enable advanced developer tools.\n\nThis is NOT recommended in production environments.'
      : 'Disabling Debug Mode will hide debug info and restrict advanced tools.\n\nThis is recommended for production environments.'
  );
}

document.getElementById('trigger-permission-scan').onclick = function () {
  if (!confirm("This will scan all modules and sync missing permissions.\n\nContinue?")) return;

  fetch('/system/maintenance/scan_permissions')

    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        alert(`✅ Synced! ${data.inserted_count} new permissions were added.`);
        location.reload();
      } else {
        alert(`❌ Error: ${data.message}`);
      }
    })
    .catch(err => {
      alert(`⛔ AJAX error: ${err}`);
    });
};
</script>

<div class="col-12">
  <div class="card shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center">
      <div>
        <h3 class="card-title mb-1"><i class="ti ti-shield-lock me-2"></i>Full System Backup</h3>
        <small class="text-muted">Creates a full .zip of your codebase and database in one click.</small>
      </div>
      <a href="<?= BASE_URL ?>/system/maintenance/full_system_backup" class="btn btn-danger">
        <i class="ti ti-package me-1"></i>Full Backup
      </a>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row g-4">

      <!-- ✅ Modular: Database Tools -->
      <div class="col-md-6">
        <?php include __DIR__ . '/maintenance/db_UI.php'; ?>
      </div>

      <!-- ✅ Modular: Folder Export -->
      <div class="col-md-6">
        <?php include __DIR__ . '/maintenance/folder_structure_UI.php'; ?>
      </div>

    </div>
  </div>
</div>
