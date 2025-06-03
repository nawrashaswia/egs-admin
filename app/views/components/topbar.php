<header class="navbar navbar-expand-md d-print-none">
  <div class="container-xl">

    <!-- 🔰 Brand -->
    <a href="<?= BASE_URL ?>/dashboard" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">
      <i class="ti ti-triangle-filled text-danger" style="transform: rotate(90deg); font-size: 1.6rem; animation: popInTriangle 0.4s ease-out;"></i>
      <span class="fw-bold fs-5 text-dark" style="letter-spacing: 0.5px;">
        <?= APP_NAME ?>
      </span>
    </a>

    <!-- 🙍‍♂️ User Dropdown -->
    <div class="navbar-nav flex-row ms-auto">
      <div class="nav-item dropdown">
        <a href="javascript:void(0)" class="nav-link d-flex align-items-center text-reset" data-bs-toggle="dropdown" aria-expanded="false">
          <?php
            $avatar = $_SESSION['avatar'] ?? null;
            $avatarUrl = $avatar
              ? BASE_URL . '/uploads/avatars/' . $avatar
              : BASE_URL . '/assets/img/user.png';
            $fullName = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Guest';
            $status = $_SESSION['status'] ?? 'active';

            $statusColor = match (strtolower($status)) {
              'active'   => 'bg-success',
              'inactive' => 'bg-secondary',
              'away'     => 'bg-warning',
              'busy'     => 'bg-danger',
              default    => 'bg-muted'
            };
          ?>

          <span class="avatar avatar-sm rounded-circle position-relative shadow"
                style="background-image: url('<?= $avatarUrl ?>'); width: 38px; height: 38px; background-size: cover;">
            <span class="position-absolute border border-white <?= $statusColor ?>"
                  style="width: 10px; height: 10px; bottom: 0; right: 0; border-radius: 50%;"></span>
          </span>

          <span class="ms-2 fw-semibold text-dark d-none d-xl-inline-block">
            <?= htmlspecialchars($fullName) ?>
          </span>
        </a>

        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow animate__animated animate__fadeInUp">
          <div class="dropdown-header text-center text-primary fw-bold">
            👋 Hello, <?= htmlspecialchars($fullName) ?>
          </div>
          <div class="dropdown-divider"></div>
          <a href="<?= BASE_URL ?>/profile" class="dropdown-item"><i class="ti ti-user me-2"></i> Profile</a>
          <a href="<?= BASE_URL ?>/dashboard" class="dropdown-item"><i class="ti ti-layout-dashboard me-2"></i> Dashboard</a>
          <div class="dropdown-divider"></div>
          <a href="<?= BASE_URL ?>/logout" class="dropdown-item text-danger"><i class="ti ti-logout me-2"></i> Logout</a>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- 🔺 Brand Icon Animation -->
<style>
  @keyframes popInTriangle {
    0%   { transform: rotate(-90deg) scale(0.7); opacity: 0; }
    100% { transform: rotate(90deg) scale(1); opacity: 1; }
  }
</style>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>