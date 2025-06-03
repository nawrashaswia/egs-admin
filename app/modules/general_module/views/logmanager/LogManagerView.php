<?php

use App\Helpers\Core\FlashHelper;

if (!class_exists(FlashHelper::class)) {
    require_once HELPERS_PATH . '/core/FlashHelper.php';
}

$config = require CONFIG_PATH . '/app.php';
$traceMode = $config['trace_mode'] ?? false;
?>

<?php ob_start(); ?>

<!-- Page Header -->
<div class="page-header d-print-none">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">
                <i class="ti ti-clipboard-text me-2"></i> Centralized Log Manager
            </h2>
            <div class="text-muted mt-1">Review backend events, trace info, and audit logs</div>
        </div>
        <div class="col-auto">
            <a href="<?= BASE_URL ?>/general/logmanager/construction" target="_blank" class="btn btn-info me-2">
                <i class="ti ti-hammer"></i> View Construction Trace Logs
            </a>
            <form method="post" action="<?= BASE_URL ?>/general/logmanager/toggle_trace_mode" style="display:inline-block;">
                <button type="submit" class="btn <?= $traceMode ? 'btn-danger' : 'btn-success' ?>">
                    <?= $traceMode ? '🛑 Disable Tracing' : '🛠 Enable Tracing' ?>
                </button>
            </form>
            <form method="post" action="<?= BASE_URL ?>/general/logmanager/delete_all" style="display:inline-block; margin-left: 10px;" onsubmit="return confirm('Are you sure you want to delete ALL logs? This action cannot be undone.');">
                <button type="submit" class="btn btn-outline-danger">
                    <i class="ti ti-trash"></i> Delete All Logs
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Level</label>
                <select class="form-select" name="level">
                    <option value="">All</option>
                    <option value="DEBUG">DEBUG</option>
                    <option value="INFO">INFO</option>
                    <option value="WARN">WARN</option>
                    <option value="ERROR">ERROR</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">User</label>
                <input type="text" class="form-control" name="user" placeholder="system, admin, etc">
            </div>
            <div class="col-md-2">
                <label class="form-label">Trace ID</label>
                <input type="text" class="form-control" name="trace_id" placeholder="TRACE-YYYYMMDD-xxx">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tags</label>
                <input type="text" class="form-control" name="tag" placeholder="e.g. boot.db, db, ...">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ti ti-filter"></i> Filter Logs
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Logs Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="ti ti-activity me-2"></i> Recent Logs</h3>
    </div>
    <div class="card-body">
        <?php if (empty($logs)): ?>
            <div class="alert alert-info">
                <i class="ti ti-info-circle me-2"></i> No logs found in the system.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle text-wrap">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 140px;">Timestamp</th>
                            <th style="min-width: 200px;">Event</th>
                            <th style="min-width: 90px;">Level</th>
                            <th>User</th>
                            <th>Mode</th>
                            <th>Trace ID</th>
                            <th>Tags</th>
                            <th style="min-width: 300px;">Context</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                $level = strtoupper($log['level']);
                                $levelBadge = match($level) {
                                    'ERROR' => 'bg-light text-danger border border-danger',
                                    'WARN', 'WARNING' => 'bg-light text-warning border border-warning',
                                    'DEBUG' => 'bg-light text-info border border-info',
                                    'INFO' => 'bg-light text-secondary border border-secondary',
                                    default => 'bg-light text-muted border'
                                };
                            ?>
                            <tr>
                                <td class="text-muted"><?= htmlspecialchars($log['timestamp']) ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($log['event']) ?></td>
                                <td><span class="badge <?= $levelBadge ?> px-2 py-1"><?= htmlspecialchars($level) ?></span></td>
                                <td><?= htmlspecialchars($log['user']) ?></td>
                                <td>
                                    <?php if ($log['mode'] === 'trace' && !empty($log['trace_id'])): ?>
                                        <span class="badge bg-primary-lt text-primary border border-primary rounded-pill px-2 py-1">
                                            <i class="ti ti-hammer me-1"></i> Construction
                                        </span>
                                    <?php elseif ($log['mode'] === 'trace'): ?>
                                        <span class="badge bg-yellow-lt text-yellow-800 border border-yellow rounded-pill px-2 py-1">
                                            <i class="ti ti-search me-1"></i> Diagnostic
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-lt text-dark border border-secondary rounded-pill px-2 py-1">
                                            <i class="ti ti-server me-1"></i> System
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($log['trace_id'])): ?>
                                        <span class="badge bg-light text-primary border px-2 py-1"><?= htmlspecialchars($log['trace_id']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($log['tag'])): ?>
                                        <span class="badge bg-info text-dark border px-2 py-1"><?= htmlspecialchars($log['tag']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $context = json_decode($log['context'], true);
                                        if (json_last_error() === JSON_ERROR_NONE):
                                    ?>
                                        <details>
                                            <summary>View JSON</summary>
                                            <pre class="bg-light text-dark p-2 border rounded small mb-0" style="max-height: 200px; overflow: auto;">
<?= json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?>
                                            </pre>
                                        </details>
                                    <?php else: ?>
                                        <code class="text-danger">Invalid JSON</code>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require VIEWS_PATH . '/layout/main.php'; ?>
