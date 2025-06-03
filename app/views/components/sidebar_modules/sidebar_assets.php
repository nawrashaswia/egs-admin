
<!-- Assets Menu -->
<li class="nav-item">
  <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-assets">
    <span>
      <span class="nav-link-icon"><i class="ti ti-device-desktop"></i></span>
      <span class="nav-link-title">Assets</span>
    </span>
    <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
  </a>
  <div class="collapse" id="menu-assets" data-bs-parent="#sidebar-nav">
    <ul class="nav nav-sm flex-column">

      <!-- General Devices -->
      <li class="nav-item">
        <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-assets-general">
          <span><i class="ti ti-building-warehouse me-2"></i> General</span>
          <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
        </a>
        <div class="collapse" id="menu-assets-general">
          <ul class="nav nav-sm flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/device_manager">Device Manager</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/stock_overview">Stock Overview</a></li>
          </ul>
        </div>
      </li>

      <!-- PTT Management -->
      <li class="nav-item">
        <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-assets-ptt">
          <span><i class="ti ti-radio me-2"></i> PTT</span>
          <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
        </a>
        <div class="collapse" id="menu-assets-ptt">
          <ul class="nav nav-sm flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/ptt/devices">PTT Devices</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/ptt/frequencies">Manage Frequencies</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/ptt/groups">PTT Groups</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/ptt/master_device">Assign Master PTT</a></li>
          </ul>
        </div>
      </li>

      <!-- Forms -->
      <li class="nav-item">
        <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-assets-forms">
          <span><i class="ti ti-file-text me-2"></i> Forms</span>
          <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
        </a>
        <div class="collapse" id="menu-assets-forms">
          <ul class="nav nav-sm flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/forms/device_assignment">Device Assignment</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/forms/return_form">Device Return</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/forms/maintenance_log">Maintenance Log</a></li>
          </ul>
        </div>
      </li>

      <!-- Reports -->
      <li class="nav-item">
        <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-assets-reports">
          <span><i class="ti ti-report me-2"></i> Reports</span>
          <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
        </a>
        <div class="collapse" id="menu-assets-reports">
          <ul class="nav nav-sm flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/reports/assignment_summary">Assignment Summary</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/reports/device_utilization">Device Utilization</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/assets/reports/ptt_status">PTT Status</a></li>
          </ul>
        </div>
      </li>

    </ul>
  </div>
</li>
