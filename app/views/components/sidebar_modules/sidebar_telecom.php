<?php
// Telecom Menu
?>
<li class="nav-item">
  <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-telecom">
    <span>
      <span class="nav-link-icon"><i class="ti ti-signal-5g"></i></span>
      <span class="nav-link-title">Telecom</span>
    </span>
    <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
  </a>
  <div class="collapse" id="menu-telecom" data-bs-parent="#sidebar-nav">
    <ul class="nav nav-sm flex-column">

      <!-- General -->
      <li class="nav-item">
        <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-telecom-general">
          <span><i class="ti ti-database me-2"></i> General</span>
          <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
        </a>
        <div class="collapse" id="menu-telecom-general">
          <ul class="nav nav-sm flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/telecom/packages">Packages</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/telecom/isps">ISPs</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/telecom/accounts">ISP Accounts</a></li>
          </ul>
        </div>
      </li>

      <!-- Forms -->
      <li class="nav-item">
        <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-telecom-forms">
          <span><i class="ti ti-file-plus me-2"></i> Forms</span>
          <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
        </a>
        <div class="collapse" id="menu-telecom-forms">
          <ul class="nav nav-sm flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/telecom/request_form">New Request</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/telecom/upgrade_form">Upgrade / Downgrade</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/telecom/service_transfer">Service Transfer</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/telecom/termination">Termination</a></li>
          </ul>
        </div>
      </li>

      <!-- Reports -->
      <li class="nav-item">
        <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-telecom-reports">
          <span><i class="ti ti-report-search me-2"></i> Reports</span>
          <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
        </a>
        <div class="collapse" id="menu-telecom-reports">
          <ul class="nav nav-sm flex-column ms-3">
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/telecom/reports/service_logs">Service Logs</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/telecom/reports/active_services">Active Services</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/telecom/reports/billing">Billing</a></li>
          </ul>
        </div>
      </li>

    </ul>
  </div>
</li>
