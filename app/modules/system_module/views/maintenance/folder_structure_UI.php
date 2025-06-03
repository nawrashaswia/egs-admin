<?php
$basePath = BASE_PATH;
$profilePath = BASE_PATH . '/storage/system/folder_export_save.json';
$saved = ['include_folders' => [], 'include_files_in' => []];

// ✅ Load saved preferences from JSON
if (file_exists($profilePath)) {
  $json = json_decode(file_get_contents($profilePath), true);
  $saved['include_folders'] = $json['include_folders'] ?? [];
  $saved['include_files_in'] = $json['include_files_in'] ?? [];
}

// ✅ Recursively scan folder tree
function getFolderTree(string $dir, string $prefix = ''): array {
  $result = [];
  foreach (scandir($dir) as $entry) {
    if ($entry === '.' || $entry === '..') continue;
    $fullPath = $dir . DIRECTORY_SEPARATOR . $entry;
    if (is_dir($fullPath)) {
      $relative = ltrim($prefix . '/' . $entry, '/');
      $result[$relative] = getFolderTree($fullPath, $relative);
    }
  }
  return $result;
}

$folderTree = getFolderTree($basePath, 'www'); // Start from 'www'
?>

<!-- ✨ STYLE OVERRIDE -->
<style>
  .tree-item {
    padding: 6px 0;
    border-bottom: 1px solid #eee;
  }
  .tree-item:last-child {
    border-bottom: none;
  }
  .tree-row {
    padding-left: 2px;
    font-size: 15px;
  }
  .form-check-input {
    margin-top: 0;
    cursor: pointer;
  }
  .folder-label {
    font-weight: 500;
    color: #333;
    display: flex;
    align-items: center;
  }
  .tree-children {
    margin-left: 20px;
    border-left: 1px solid #e1e1e1;
    padding-left: 12px;
    margin-top: 3px;
  }
</style>


<div class="card shadow-sm">
  <div class="card-header">
    <h3 class="card-title"><i class="ti ti-folder-cog me-2"></i>Folder Export</h3>
  </div>
  <div class="card-body">

    <!-- 🔘 Toolbar -->
    <div class="mb-3 d-flex flex-wrap gap-2">
      <button class="btn btn-sm btn-outline-secondary" onclick="toggleAll(true)">
        <i class="ti ti-check me-1"></i>Select All
      </button>
      <button class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">
        <i class="ti ti-x me-1"></i>Deselect All
      </button>
      <button class="btn btn-sm btn-outline-success" onclick="saveSelection(this)">
        <i class="ti ti-device-floppy me-1"></i>Save Selection
      </button>

      <form method="post" action="<?= BASE_URL ?>/system/maintenance/folder_export" id="exportForm" class="d-inline">
        <input type="hidden" name="include_folders[]">
        <input type="hidden" name="include_files_in[]">
        <input type="hidden" name="zip_export" id="zipExportInput" value="0">

        <button class="btn btn-sm btn-outline-primary" type="submit">
          <i class="ti ti-download me-1"></i>Export as TXT
        </button>
        <button class="btn btn-sm btn-outline-dark" type="button" onclick="triggerZipExport()">
          <i class="ti ti-archive me-1"></i>Export Selected as ZIP
        </button>
      </form>
    </div>

    <!-- 📦 Full System Backup ZIP -->
    <div class="alert alert-info d-flex align-items-center justify-content-between p-2 small mt-3">
      <div><i class="ti ti-info-circle me-2"></i>Need a full backup of the entire system?</div>
      <a href="<?= BASE_URL ?>/system/maintenance/full_zip_backup" class="btn btn-sm btn-danger">
        <i class="ti ti-package me-1"></i>Backup Entire System as ZIP
      </a>
    </div>

    <!-- 🌳 Folder Tree -->
    <div class="border rounded bg-light p-3" style="max-height: 500px; overflow-y: auto;">
      <?php
      function renderTree(array $tree, string $prefix = '', array $savedFolders = [], array $savedFiles = []) {
        foreach ($tree as $path => $children) {
          $folderName = basename($path);
          $folderChecked = in_array($path, $savedFolders) ? 'checked' : '';
          $fileChecked = in_array($path, $savedFiles) ? 'checked' : '';

          echo "<div class='tree-item' data-path='$path'>";
          echo "  <div class='tree-row d-flex align-items-center'>";
          echo "    <input type='checkbox' class='form-check-input me-2 folder-checkbox' value='$path' $folderChecked title='Include folder'>";
          echo "    <input type='checkbox' class='form-check-input me-3 files-checkbox' value='$path' $fileChecked title='Include files in folder'>";
          echo "    <span class='folder-label'><img src='https://img.icons8.com/windows/20/000000/folder-invoices.png' class='me-2' style='width:16px;height:16px;' alt='📁'>" . htmlspecialchars($folderName) . "</span>";
          echo "  </div>";

          if (!empty($children)) {
            echo "<div class='tree-children'>";
            renderTree($children, $path, $savedFolders, $savedFiles);
            echo "</div>";
          }

          echo "</div>";
        }
      }

      renderTree($folderTree, '', $saved['include_folders'], $saved['include_files_in']);
      ?>
    </div>
  </div>
</div>

<!-- ✅ Toast -->
<div id="custom-toast" class="toast align-items-center text-white border-0 position-fixed top-0 end-0 m-4 d-none" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1080;">
  <div class="d-flex">
    <div class="toast-body" id="custom-toast-body"></div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
</div>

<!-- ✅ Constants -->
<script>
  const BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/assets/js/system_module/folder_structure.js"></script>
