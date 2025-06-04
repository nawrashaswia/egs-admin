<?php ob_start(); ?>
<div class="page-header d-print-none mb-4">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">HR Module</div>
                <h2 class="page-title">
                    <i class="ti ti-flag me-2"></i> Countries
                </h2>
                <div class="text-muted mt-1">Manage countries for HR operations</div>
            </div>
            <div class="col-auto">
                <a href="<?= BASE_URL ?>/hr/countries/add" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Add Country
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <div class="row align-items-center w-100">
            <div class="col">
                <h3 class="card-title">Countries</h3>
            </div>
            <div class="col-auto">
                <form method="get" class="input-icon" action="">
                    <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="form-control form-control-sm" placeholder="Search...">
                    <span class="input-icon-addon">
                        <i class="ti ti-search"></i>
                    </span>
                </form>
            </div>
        </div>
    </div>
    <div class="table-responsive" style="overflow: visible">
        <table class="table card-table table-vcenter table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>ISO</th>
                    <th>Currency</th>
                    <th>Dial</th>
                    <th>Prefixes</th>
                    <th>Timezone</th>
                    <th>Flag</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
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
                        <td>
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($country['flag_image']) ?>" alt="flag" class="rounded border" width="28" height="20">
                        </td>
                        <td>
                            <?php if ($country['is_active']): ?>
                                <span class="badge bg-success-lt text-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-lt text-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-icon dropdown-toggle" data-bs-toggle="dropdown" aria-label="Actions">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="<?= BASE_URL ?>/hr/countries/edit?id=<?= $country['id'] ?>" class="dropdown-item">
                                        <i class="ti ti-edit me-1"></i> Edit
                                    </a>
                                    <a href="<?= BASE_URL ?>/hr/countries/delete?id=<?= $country['id'] ?>" class="dropdown-item text-danger" onclick="return confirm('Delete this country?');">
                                        <i class="ti ti-trash me-1"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php require VIEWS_PATH . '/layout/main.php'; ?>
