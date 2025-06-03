<?php ob_start(); ?>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible" role="alert">
        <?= htmlspecialchars($_SESSION['flash']['message']) ?>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="page-header d-print-none mb-3">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">
                🏗️ Active Construction Traces
            </h2>
            <div class="text-muted mt-1">Logs recorded during active development tracing</div>
        </div>
        <div class="col-auto">
            <form method="post" action="<?= BASE_URL ?>/general/logmanager/delete_all_construction" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete ALL construction logs? This action cannot be undone.');">
                <button type="submit" class="btn btn-outline-danger">
                    <i class="ti ti-trash"></i> Delete All Construction Logs
                </button>
            </form>
        </div>
    </div>
</div>

<?php if (!$traceMode): ?>
    <div class="alert alert-info">
        <i class="ti ti-info-circle me-2"></i> Trace mode is currently <strong>disabled</strong>.
    </div>
<?php endif; ?>

<!-- Active Trace Sessions -->
<div class="card mb-4">
    <div class="card-body">
        <h3 class="card-title mb-3"><i class="ti ti-map-pin me-2"></i> Tracked Files (Active Sessions)</h3>

        <?php if (empty($traceSessions)): ?>
            <div class="alert alert-warning mb-0">
                <i class="ti ti-alert-circle me-2"></i>
                <?php if ($traceMode): ?>
                    Tracing is active, but no files have initiated a trace session.
                <?php else: ?>
                    No trace sessions found.
                <?php endif; ?>
            </div>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($traceSessions as $session): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="text-break">
                            <div class="fw-bold"><i class="ti ti-file-text me-1"></i> <?= htmlspecialchars(basename($session['file'])) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($session['file']) ?></small><br>
                            <small>Started: <?= htmlspecialchars($session['started_at']) ?></small>
                            <?php if (!empty($session['notes'])): ?>
                                <br><small class="text-primary">🏷️ <?= htmlspecialchars($session['notes']) ?></small>
                            <?php endif; ?>
                        </div>
                        <span class="badge bg-light text-primary border border-primary rounded-pill"><?= htmlspecialchars($session['trace_id']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<!-- Files Declaring Trace But No Active Session -->
<?php if (!empty($declaredTraceFiles)): ?>
    <?php
        $trackedPaths = array_map('realpath', array_column($traceSessions, 'file'));
        $untrackedDeclared = array_filter($declaredTraceFiles, function ($file) use ($trackedPaths) {
            $declaredPath = realpath($file['file']);
            return $declaredPath && !in_array($declaredPath, $trackedPaths);
        });
    ?>

    <?php if (!empty($untrackedDeclared)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="card-title mb-3"><i class="ti ti-code me-2"></i> Declared Files (No Session)</h3>
                <ul class="list-group">
                    <?php foreach ($untrackedDeclared as $file): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="text-break">
                                <code><?= htmlspecialchars($file['file']) ?></code>
                                <?php if (!empty($file['note'])): ?>
                                    <br><small class="text-muted">🏷️ <?= htmlspecialchars($file['note']) ?></small>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-light text-secondary border border-secondary">Declared</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Construction Logs Table -->
<div class="card">
    <div class="card-body">
        <h3 class="card-title mb-3"><i class="ti ti-file-search me-2"></i> Construction Log Records</h3>

        <input type="text" class="form-control mb-3" id="filterTraceId" placeholder="🔍 Filter by Trace ID">

        <div id="logsTableContainer">
            <div class="alert alert-info">Loading logs...</div>
        </div>
        <div class="d-grid mt-3">
            <button id="loadMoreLogs" class="btn btn-outline-primary">Load More</button>
        </div>
    </div>
</div>

<script>
let logsOffset = 0;
const logsLimit = 50;
let allLogs = [];

function renderLogsTable(logs) {
    if (!logs.length && logsOffset === 0) {
        document.getElementById('logsTableContainer').innerHTML = '<div class="alert alert-warning"><i class="ti ti-alert-circle me-2"></i> No construction logs were found.</div>';
        document.getElementById('loadMoreLogs').style.display = 'none';
        return;
    }
    let html = '<div class="table-responsive"><table class="table table-bordered table-striped align-middle text-wrap" id="logsTable"><thead class="table-light"><tr><th>Timestamp</th><th>Event</th><th>Level</th><th>User</th><th>Trace ID</th><th>Context</th></tr></thead><tbody>';
    for (const log of allLogs) {
        const ctx = (() => { try { return JSON.parse(log.context); } catch { return null; } })();
        const levelClass = {
            'error': 'text-danger border border-danger',
            'warn': 'text-warning border border-warning',
            'warning': 'text-warning border border-warning',
            'debug': 'text-info border border-info',
            'info': 'text-secondary border border-secondary',
        }[String(log.level).toLowerCase()] || 'text-muted border border-light';
        html += `<tr>
            <td>${log.timestamp ? escapeHtml(log.timestamp) : ''}</td>
            <td>${log.event ? escapeHtml(log.event) : ''}</td>
            <td><span class="badge bg-light ${levelClass}">${log.level ? escapeHtml(log.level) : ''}</span></td>
            <td>${log.user ? escapeHtml(log.user) : ''}</td>
            <td><code>${log.trace_id ? escapeHtml(log.trace_id) : ''}</code> <button class="btn btn-sm btn-outline-secondary ms-1" onclick="navigator.clipboard.writeText('${log.trace_id ? escapeHtml(log.trace_id) : ''}')">📋</button></td>
            <td>` + (ctx ? `<details><summary>View JSON</summary><pre>${escapeHtml(JSON.stringify(ctx, null, 2))}</pre></details>` : '<code>Invalid JSON</code>') + `</td>
        </tr>`;
    }
    html += '</tbody></table></div>';
    document.getElementById('logsTableContainer').innerHTML = html;
    document.getElementById('loadMoreLogs').style.display = logs.length < logsLimit ? 'none' : 'block';
}

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function loadLogs() {
    document.getElementById('loadMoreLogs').disabled = true;
    fetch(`/ajax/general_module/fetch_construction_logs?offset=${logsOffset}&limit=${logsLimit}`)
        .then(res => res.json())
        .then(logs => {
            if (logs.length) {
                allLogs = allLogs.concat(logs);
                logsOffset += logs.length;
            }
            renderLogsTable(logs);
            document.getElementById('loadMoreLogs').disabled = false;
        })
        .catch(() => {
            document.getElementById('logsTableContainer').innerHTML = '<div class="alert alert-danger">Failed to load logs.</div>';
            document.getElementById('loadMoreLogs').disabled = false;
        });
}

document.getElementById('loadMoreLogs').addEventListener('click', loadLogs);

document.getElementById('filterTraceId').addEventListener('input', function () {
    const filterValue = this.value.toLowerCase();
    const filtered = allLogs.filter(log => String(log.trace_id).toLowerCase().includes(filterValue));
    renderLogsTable(filtered);
});

// Initial load
loadLogs();
</script>

<?php $content = ob_get_clean(); ?>
<?php require VIEWS_PATH . '/layout/main.php'; ?>
