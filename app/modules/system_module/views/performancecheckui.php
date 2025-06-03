<?php
use App\Helpers\Core\PerformanceMonitor;

// Get performance data
$summary = PerformanceMonitor::getSummary();
?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">
          Performance Monitor
        </h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
          <button class="btn btn-primary d-none d-sm-inline-block" onclick="refreshPerformanceData()">
            <i class="ti ti-refresh me-1"></i>
            Refresh
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <!-- Backend Performance -->
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title">Backend Performance</h3>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="subheader">Total Time</div>
                </div>
                <div class="h1 mb-3"><?= number_format($summary['total_time'], 3) ?>s</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="subheader">Memory Usage</div>
                </div>
                <div class="h1 mb-3"><?= number_format($summary['total_memory'] / 1024 / 1024, 2) ?> MB</div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="subheader">Peak Memory</div>
                </div>
                <div class="h1 mb-3"><?= number_format($summary['peak_memory'] / 1024 / 1024, 2) ?> MB</div>
              </div>
            </div>
          </div>
        </div>

        <?php if (!empty($summary['warnings'])): ?>
        <div class="alert alert-warning mt-3">
          <h4 class="alert-title">Warnings</h4>
          <ul class="mt-2">
            <?php foreach ($summary['warnings'] as $warning): ?>
            <li><?= htmlspecialchars($warning) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Top 10 Included Files -->
    <?php if (!empty($summary['top_files'])): ?>
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title">Top 10 Included Files</h3>
      </div>
      <div class="card-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>File</th>
              <th>Size (KB)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($summary['top_files'] as $file): ?>
            <tr>
              <td style="word-break:break-all; max-width: 400px;"> <?= htmlspecialchars($file['file']) ?> </td>
              <td><?= number_format($file['size'] / 1024, 2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Frontend Performance -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Frontend Performance</h3>
      </div>
      <div class="card-body">
        <div id="frontend-metrics">
          <div class="row g-3">
            <div class="col-md-4">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="subheader">Page Load Time</div>
                  </div>
                  <div class="h1 mb-3" id="page-load-time">-</div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="subheader">Memory Usage</div>
                  </div>
                  <div class="h1 mb-3" id="memory-usage">-</div>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                    <div class="subheader">AJAX Calls</div>
                  </div>
                  <div class="h1 mb-3" id="ajax-calls">-</div>
                </div>
              </div>
            </div>
          </div>

          <div id="frontend-warnings" class="alert alert-warning mt-3 d-none">
            <h4 class="alert-title">Warnings</h4>
            <ul class="mt-2" id="warning-list"></ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/performance-monitor.js"></script>
<script>
function refreshPerformanceData() {
  const summary = performanceMonitor.getSummary();
  
  // Update metrics
  document.getElementById('page-load-time').textContent = 
    (summary.totalTime / 1000).toFixed(3) + 's';
  
  if (summary.memory) {
    document.getElementById('memory-usage').textContent = 
      (summary.memory.used / 1024 / 1024).toFixed(2) + ' MB';
  }
  
  document.getElementById('ajax-calls').textContent = 
    summary.counters.fetch || 0;

  // Update warnings
  const warningList = document.getElementById('warning-list');
  const warningContainer = document.getElementById('frontend-warnings');
  
  if (summary.warnings.length > 0) {
    warningList.innerHTML = summary.warnings
      .map(warning => `<li>${warning}</li>`)
      .join('');
    warningContainer.classList.remove('d-none');
  } else {
    warningContainer.classList.add('d-none');
  }
}

// Initial load
document.addEventListener('DOMContentLoaded', refreshPerformanceData);

// Refresh every 5 seconds
setInterval(refreshPerformanceData, 5000);
</script> 