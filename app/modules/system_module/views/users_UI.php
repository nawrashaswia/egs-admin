<?php

use App\Core\AppKernel;
use App\Core\DB;
use App\Helpers\Core\CSRFHelper;

// Boot the system
AppKernel::boot();

$title = $title ?? 'User Manager';
$pdo = DB::connect();
$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header d-print-none mb-4">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title"><i class="ti ti-users me-2"></i><?= htmlspecialchars($title) ?></h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <a href="<?= BASE_URL ?>/system/users/add" class="btn btn-primary">
          <i class="ti ti-user-plus me-1"></i> Add User
        </a>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="card shadow-sm">
      <div class="card-header">
        <h3 class="card-title">System Users</h3>
      </div>
      <div class="table-responsive">
        <table class="table card-table table-vcenter text-nowrap datatable">
          <thead>
            <tr>
              <th></th>
              <th>#</th>
              <th>Username</th>
              <th>Full Name</th>
              <th>Role</th>
              <th>Status</th>
              <th>Created</th>
              <th>Last Login</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($users)): ?>
              <?php foreach ($users as $i => $user): ?>
              <tr>
                <td>
                  <?php
                    $avatar = $user['avatar'] ?? null;
                    $avatarUrl = $avatar
                      ? BASE_URL . '/uploads/avatars/' . $avatar
                      : BASE_URL . '/assets/img/user.png';
                  ?>
                  <span class="avatar avatar-sm d-block" style="background-image: url('<?= $avatarUrl ?>');"></span>
                </td>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['full_name'] ?? '-') ?></td>
                <td>
                  <span class="badge bg-blue-lt text-blue"><?= htmlspecialchars($user['role'] ?? 'User') ?></span>
                </td>
                <td>
                  <?php $status = $user['status'] ?? 'Inactive'; ?>
                  <span class="badge <?= $status === 'Active' ? 'bg-cyan-lt text-cyan' : 'bg-yellow-lt text-yellow' ?>">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>
                <td><small><?= !empty($user['created_at']) ? date('Y-m-d', strtotime($user['created_at'])) : '-' ?></small></td>
                <td><small><?= !empty($user['last_login']) ? date('Y-m-d H:i', strtotime($user['last_login'])) : '-' ?></small></td>
                <td class="text-end">
                  <a href="<?= BASE_URL ?>/system/users/edit?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">
                    <i class="ti ti-edit"></i>
                  </a>
                  <a href="<?= BASE_URL ?>/system/users/delete?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                    <i class="ti ti-trash"></i>
                  </a>
                  <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#changePasswordModal<?= $user['id'] ?>">
                    <i class="ti ti-lock"></i>
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center text-muted">No users found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- 🔒 Password Modals -->
<?php if (!empty($users) && is_array($users)): ?>
  <?php foreach ($users as $user): ?>
    <div class="modal modal-blur fade" id="changePasswordModal<?= $user['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <form action="<?= BASE_URL ?>/system/users/password" method="post" class="modal-content">
        <?= CSRFHelper::input('change_password_' . $user['id']) ?>
          <input type="hidden" name="id" value="<?= $user['id'] ?>">
          <div class="modal-header">
            <h5 class="modal-title">
              Change Password: <span class="text-blue"><?= htmlspecialchars($user['username']) ?></span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="password" class="form-control" required minlength="2">
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" required minlength="2">
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">
              <i class="ti ti-check me-1"></i> Update
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
