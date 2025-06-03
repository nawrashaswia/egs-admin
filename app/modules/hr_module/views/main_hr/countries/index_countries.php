<?php ob_start(); ?>
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title"><i class="ti ti-flag me-2"></i> Countries</h2>
                <div class="text-muted mt-1">Manage countries for HR operations</div>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/hr/countries/add" class="btn btn-primary">
                    <i class="ti ti-plus"></i> Add Country
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Countries</h3>
    </div>
    <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle text-wrap">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>ISO Code</th>
                        <th>Currency</th>
                        <th>Dial Key</th>
                        <th>Prefixes</th>
                        <th>Timezone</th>
                        <th>Flag</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($countries as $country): ?>
                        <tr>
                            <td><?= htmlspecialchars($country['id']) ?></td>
                            <td><?= htmlspecialchars($country['name']) ?></td>
                            <td><?= htmlspecialchars($country['iso_code']) ?></td>
                            <td><?= htmlspecialchars($country['default_currency_code']) ?></td>
                            <td><?= htmlspecialchars($country['base_dial_key']) ?></td>
                            <td><?= htmlspecialchars($country['accepted_prefixes']) ?></td>
                            <td><?= htmlspecialchars($country['timezone']) ?></td>
                            <td><img src="<?= BASE_URL . '/' . htmlspecialchars($country['flag_image']) ?>" alt="flag" width="32"></td>
                            <td>
                                <?php if ($country['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/hr/countries/edit?id=<?= $country['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="<?= BASE_URL ?>/hr/countries/delete?id=<?= $country['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this country?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php require VIEWS_PATH . '/layout/main.php'; ?>
