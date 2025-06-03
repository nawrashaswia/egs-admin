<!-- System Menu -->
<li class="nav-item">
  <a class="nav-link collapsed d-flex justify-content-between align-items-center level-1" data-bs-toggle="collapse" href="#menu-system">
    <span>
      <span class="nav-link-icon"><i class="ti ti-settings"></i></span>
      <span class="nav-link-title">System</span>
    </span>
    <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
  </a>
  <div class="collapse" id="menu-system" data-bs-parent="#sidebar-nav">
    <ul class="nav nav-sm flex-column">

      <!-- Dashboard -->
      <li class="nav-item">
        <a class="nav-link level-2" href="<?= BASE_URL ?>/system">
          <i class="ti ti-dashboard me-2"></i> Dashboard
        </a>
      </li>

      <!-- Users -->
      <li class="nav-item">
        <a class="nav-link level-2" href="<?= BASE_URL ?>/system/users">
          <i class="ti ti-users me-2"></i> Users
        </a>
      </li>

      <!-- Permissions -->
      <li class="nav-item">
        <a class="nav-link level-2" href="<?= BASE_URL ?>/system/permissions">
          <i class="ti ti-shield-lock me-2"></i> Permissions
        </a>
      </li>

      <!-- Performance Monitor -->
      <li class="nav-item">
        <a class="nav-link level-2" href="<?= BASE_URL ?>/system/performance">
          <i class="ti ti-chart-bar me-2"></i> Performance Monitor
        </a>
      </li>

      <!-- Router Manager -->
      <li class="nav-item">
        <a class="nav-link level-2" href="<?= BASE_URL ?>/system/router-manager">
          <i class="ti ti-route me-2"></i> Router Manager
        </a>
      </li>

      <!-- Modules -->
      <li class="nav-item">
        <a class="nav-link level-2" href="<?= BASE_URL ?>/system/modules">
          <i class="ti ti-puzzle me-2"></i> Modules
        </a>
      </li>

      <!-- Maintenance -->
      <li class="nav-item">
        <a class="nav-link level-2" href="<?= BASE_URL ?>/system/maintenance">
          <i class="ti ti-tools me-2"></i> Maintenance
        </a>
      </li>

    </ul>
  </div>
</li>
