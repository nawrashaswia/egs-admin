<aside class="navbar navbar-vertical navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid d-flex flex-column px-0">

    <!-- 🌀 Brand -->
    <a class="navbar-brand my-3 text-center" href="<?= BASE_URL ?>/dashboard">
      <img src="<?= BASE_URL ?>/assets/img/logo.svg" height="36" alt="<?= APP_NAME ?>" class="navbar-brand-image">
    </a>

    <!-- 🔍 Sidebar Search -->
    <div class="px-3 pb-3">
      <div class="input-icon shadow-sm rounded bg-secondary bg-opacity-25">
        <span class="input-icon-addon">
          <i class="ti ti-search text-white opacity-75"></i>
        </span>
        <input type="text"
               id="sidebar-search"
               class="form-control form-control-sm text-white bg-transparent border-0"
               style="height: 34px;"
               placeholder="Search modules..."
               onkeyup="filterSidebarMenu(this.value)">
      </div>
    </div>

    <!-- 📱 Collapse Button (Mobile) -->
    <button class="navbar-toggler my-2 mx-3 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- 📂 Sidebar Navigation -->
    <div class="collapse navbar-collapse d-flex flex-column h-100" id="sidebar-menu">
      <ul class="navbar-nav pt-lg-2 flex-grow-1 overflow-y-auto" id="sidebar-nav">

        <!-- 🏠 Dashboard Link -->
        <li class="nav-item">
          <a class="nav-link active" href="<?= BASE_URL ?>/dashboard">
            <span class="nav-link-icon"><i class="ti ti-home"></i></span>
            <span class="nav-link-title">Dashboard</span>
          </a>
        </li>

        <!-- 🔁 Dynamic Modules -->
        <?php
        $modulesPath = __DIR__ . '/sidebar_modules/';
        $orderedModules = [
          'sidebar_general.php',
          'sidebar_hr.php',
          'sidebar_telecom.php',
          'sidebar_assets.php',
          'sidebar_ptt.php',
          'sidebar_tickets.php',
          'sidebar_system.php',
        ];

        foreach ($orderedModules as $moduleFile) {
          $fullPath = $modulesPath . $moduleFile;
          if (file_exists($fullPath)) {
            include $fullPath;
          } elseif (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "<!-- ⚠ Module missing: $moduleFile -->";
          }
        }
        ?>
      </ul>

      <!-- 🚩 Development/Trace Mode Pills -->
      <div class="mb-3 px-3 animate__animated animate__fadeInUp">
        <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
          <!-- Dev/Debug Mode -->
          <div class="mode-pill <?= (defined('DEBUG_MODE') && DEBUG_MODE) ? 'on' : 'off' ?>"
               title="Toggle Dev/Debug Mode"
               onclick="toggleDebugMode(this)">
            <i class="ti ti-terminal-2"></i>
            <span>Dev</span>
            <span class="status ms-1"><?= (defined('DEBUG_MODE') && DEBUG_MODE) ? 'ON' : 'OFF' ?></span>
          </div>
          <!-- Trace Mode -->
          <div class="mode-pill <?= ($traceMode ?? (isset($GLOBALS['config']['trace_mode']) && $GLOBALS['config']['trace_mode'])) ? 'on' : 'off' ?>"
               title="Toggle Trace Mode"
               onclick="toggleTraceMode(this)">
            <i class="ti ti-hammer"></i>
            <span>Trace</span>
            <span class="status ms-1"><?= ($traceMode ?? (isset($GLOBALS['config']['trace_mode']) && $GLOBALS['config']['trace_mode'])) ? 'ON' : 'OFF' ?></span>
          </div>
        </div>
      </div>

      <!-- 🚪 Logout -->
      <div class="mt-auto px-3 pb-4">
        <a class="btn btn-danger w-100 d-flex align-items-center justify-content-center gap-2 shadow-sm animate__animated animate__pulse animate__infinite"
           href="<?= BASE_URL ?>/logout">
          <i class="ti ti-logout"></i>
          <span class="fw-bold">Logout</span>
        </a>
      </div>
    </div>
  </div>
</aside>

<style>
.navbar-vertical.bg-dark .nav-link {
  font-size: 1rem;
  font-weight: 500;
  color: #ddd;
  padding: 0.6rem 1rem;
  position: relative;
  transition: background-color 0.2s ease;
}

.navbar-vertical.bg-dark .nav-link:hover,
.navbar-vertical.bg-dark .nav-link:focus,
.navbar-vertical.bg-dark .nav-link.active {
  background-color: rgba(255, 255, 255, 0.05);
  color: #fff;
  font-weight: 600;
}

.navbar-vertical.bg-dark .nav-link::before {
  content: "";
  position: absolute;
  left: 0;
  top: 0.4rem;
  bottom: 0.4rem;
  width: 3px;
  background-color: transparent;
  border-radius: 2px;
  transition: background-color 0.3s;
}

.navbar-vertical.bg-dark .nav-link.active::before {
  background-color: #f03e3e;
}

.navbar-vertical.bg-dark .collapse.show .nav-link:not(.active)::before {
  background-color: transparent;
}

.navbar-vertical.bg-dark .collapse.show {
  border-left: 2px solid #f03e3e;
  padding-left: 0.4rem;
  margin-left: 0.25rem;
}

/* Level 1 - Main menu items */
.navbar-vertical.bg-dark .nav-link.level-1 {
  font-weight: 700;
  color: #fff;
  padding: 0.7rem 1.1rem;
}

/* Level 2 - Submenu items */
.navbar-vertical.bg-dark .nav-link.level-2 {
  font-size: 1rem;
  font-weight: 600;
  color: #f8f9fa;
  padding-left: 2.1rem;
}

/* Level 3 - Sub-submenu items */
.navbar-vertical.bg-dark .nav-link.level-3 {
  font-size: 0.95rem;
  font-weight: 500;
  color: #dee2e6;
  padding-left: 2.9rem;
}

.navbar-vertical .nav-link .nav-link-collapse-icon {
  display: inline-flex;
  align-items: center;
  margin-left: auto;
  transition: transform 0.25s;
}

.navbar-vertical .nav-link[aria-expanded="true"] .nav-link-collapse-icon {
  transform: rotate(180deg);
}

.devmode-card {
  background: rgba(33, 150, 243, 0.85);
  backdrop-filter: blur(6px) saturate(120%);
  border: 1.5px solid #1976d2;
  box-shadow: 0 4px 24px rgba(33,150,243,0.18);
  transition: box-shadow 0.2s;
}
.devmode-card:hover {
  box-shadow: 0 8px 32px rgba(33,150,243,0.28);
}
.devmode-icon {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(135deg, #1976d2 0%, #42a5f5 100%);
  color: #fff;
  font-size: 1.5rem;
  box-shadow: 0 2px 8px rgba(33,150,243,0.13);
}
</style>

<!-- Add JS for toggles -->
<script>
function toggleDebugMode(el) {
  el.classList.add('loading');
  fetch('/system/maintenance/toggle_debug_mode', {
    method: 'POST',
    body: 'debug=' + (el.classList.contains('on') ? '0' : '1'),
    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
  }).then(() => location.reload());
}
function toggleTraceMode(el) {
  el.classList.add('loading');
  fetch('/general/logmanager/toggle_trace_mode', {method: 'POST'})
    .then(() => location.reload());
}
</script>
