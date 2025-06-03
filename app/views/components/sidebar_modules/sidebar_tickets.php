<!-- Tickets Menu -->
<li class="nav-item">
  <a class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#menu-tickets">
    <span>
      <span class="nav-link-icon"><i class="ti ti-ticket"></i></span>
      <span class="nav-link-title">Tickets</span>
    </span>
    <i class="ti ti-chevron-down transition small opacity-75" data-bs-toggle-icon></i>
  </a>
  <div class="collapse" id="menu-tickets" data-bs-parent="#sidebar-nav">
    <ul class="nav nav-sm flex-column">

      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/tickets">View Tickets</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/tickets/create">Create Ticket</a></li>
      <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/tickets/archive">Archive</a></li>

    </ul>
  </div>
</li>
