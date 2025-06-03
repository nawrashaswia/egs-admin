<!-- HR Menu -->
<li class="nav-item">
  <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-hr">
    <span>
      <span class="nav-link-icon"><i class="ti ti-users"></i></span>
      <span class="nav-link-title">HR</span>
    </span>
    <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
  </a>
  <div class="collapse" id="menu-hr" data-bs-parent="#sidebar-nav">
    <ul class="nav nav-sm flex-column">

      <!-- Staff submenu -->
      <li class="nav-item">
        <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-hr-staff">
          <span><i class="ti ti-user me-2"></i> Staff</span>
          <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
        </a>
        <div class="collapse" id="menu-hr-staff">
          <ul class="nav nav-sm flex-column ms-3"> <!-- ms-3 adds left spacing for L3 -->
            <li class="nav-item"><a class="nav-link" href="#">View</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Add</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Archive</a></li>
          </ul>
        </div>
      </li>

      <!-- Grades -->
      <li class="nav-item">
        <a class="nav-link" href="#"><i class="ti ti-school me-2"></i> Grades</a>
      </li>
      <!-- Countries -->
      <li class="nav-item">
        <a class="nav-link" href="<?= BASE_URL ?>/hr/countries"><i class="ti ti-flag me-2"></i> Countries</a>
      </li>
    </ul>
  </div>
</li>
