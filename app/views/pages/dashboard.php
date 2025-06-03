

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
      <h2 class="page-title">
<?php
use App\Core\App;
$user = App::get('auth')->user();



?>
<h2 class="mb-4">Welcome, <?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Guest') ?> 👋</h2>
        
      </h2> 
       <div class="text-muted mt-1">Overview of system modules and stats</div>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row row-cards">
      
      <!-- HR Staff -->
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <span class="avatar bg-primary-lt me-3">
                <svg class="icon" width="24" height="24"><use xlink:href="#icon-users" /></svg>
              </span>
              <div>
                <div class="font-weight-medium">124</div>
                <div class="text-muted">Staff Members</div>
              </div>
            </div>
          </div>
          <a href="<?= BASE_URL ?>/hr/dashboard" class="card-footer text-primary">Manage HR</a>
        </div>
      </div>

      <!-- Telecom Services -->
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <span class="avatar bg-yellow-lt me-3">
                <svg class="icon" width="24" height="24"><use xlink:href="#icon-phone" /></svg>
              </span>
              <div>
                <div class="font-weight-medium">62</div>
                <div class="text-muted">Active Services</div>
              </div>
            </div>
          </div>
          <a href="<?= BASE_URL ?>/telecom/dashboard" class="card-footer text-yellow">Telecom Module</a>
        </div>
      </div>

      <!-- Tickets -->
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <span class="avatar bg-pink-lt me-3">
                <svg class="icon" width="24" height="24"><use xlink:href="#icon-ticket" /></svg>
              </span>
              <div>
                <div class="font-weight-medium">18</div>
                <div class="text-muted">Open Tickets</div>
              </div>
            </div>
          </div>
          <a href="<?= BASE_URL ?>/tickets" class="card-footer text-pink">View Tickets</a>
        </div>
      </div>

      <!-- Placeholder for next module -->
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body">
            <div class="d-flex align-items-center">
              <span class="avatar bg-success-lt me-3">
                <svg class="icon" width="24" height="24"><use xlink:href="#icon-building" /></svg>
              </span>
              <div>
                <div class="font-weight-medium">9</div>
                <div class="text-muted">Assets Tracked</div>
              </div>
            </div>
          </div>
          <a href="<?= BASE_URL ?>/assets" class="card-footer text-success">Manage Assets</a>
        </div>
      </div>

    </div>
  </div>
</div>

