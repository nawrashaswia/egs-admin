<!-- General Menu -->
<li class="nav-item">
  <a class="nav-link collapsed d-flex justify-content-between align-items-center level-1" data-bs-toggle="collapse" href="#menu-general">
    <span>
      <span class="nav-link-icon"><i class="ti ti-settings"></i></span>
      <span class="nav-link-title">General</span>
    </span>
    <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
  </a>
  <div class="collapse" id="menu-general" data-bs-parent="#sidebar-nav">
    <ul class="nav nav-sm flex-column">

      <!-- Attachment Manager -->
      <li class="nav-item">
        <a class="nav-link collapsed d-flex justify-content-between align-items-center level-2" data-bs-toggle="collapse" href="#menu-attachment-manager">
          <span><i class="ti ti-paperclip me-2"></i> Attachments</span>
          <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
        </a>
        <div class="collapse" id="menu-attachment-manager">
          <ul class="nav nav-sm flex-column ms-3">
            <li class="nav-item">
              <a class="nav-link level-3" href="<?= BASE_URL ?>/general/attachment_manager/settings_ui">Manage Rules</a>
            </li>
          </ul>
        </div>
      </li>

<!-- Log Manager -->
<li class="nav-item">
  <a class="nav-link collapsed d-flex justify-content-between align-items-center level-2" data-bs-toggle="collapse" href="#menu-log-manager">
    <span><i class="ti ti-list-details me-2"></i> Log Manager</span>
    <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
  </a>
  <div class="collapse" id="menu-log-manager">
    <ul class="nav nav-sm flex-column ms-3">
      <li class="nav-item">
        <a class="nav-link level-3" href="<?= BASE_URL ?>/general/logmanager">
          <i class="ti ti-server me-2"></i> System Logs
        </a>
      </li>
      <li class="nav-item">
      <a class="nav-link level-3" href="<?= BASE_URL ?>/general/logmanager/construction">
        <i class="ti ti-hammer me-2"></i> Trace Logs
      </a>
      </li>
    </ul>
  </div>
</li>


    </ul>
  </div>
</li>
