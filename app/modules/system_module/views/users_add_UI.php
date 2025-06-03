<?php

use App\Helpers\Core\CSRFHelper;

$title = $title ?? 'Add New User';

?>

<div class="page-header d-print-none mb-4">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">
          <i class="ti ti-user-plus me-2"></i><?= htmlspecialchars($title) ?>
        </h2>
        <div class="text-muted mt-1">
          Create a new user account with specific roles and permissions
        </div>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <a href="<?= BASE_URL ?>/system/users" class="btn btn-outline-secondary">
          <i class="ti ti-arrow-left me-1"></i> Back to Users
        </a>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-8">
        <div class="card">
          <div class="card-header bg-primary-lt">
            <h3 class="card-title text-primary">
              <i class="ti ti-user me-2"></i>User Information
            </h3>
          </div>
          <div class="card-body">
            <form action="<?= BASE_URL ?>/system/users/save" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
              <?= CSRFHelper::input('users_add') ?>
              
              <!-- Avatar Upload -->
              <div class="text-center mb-4">
                <div class="avatar avatar-xxxl mb-3" style="cursor: pointer; width: 200px; height: 200px;" onclick="document.getElementById('avatarInput').click()">
                  <img id="avatarPreview" src="<?= BASE_URL ?>/assets/img/user.png" 
                       class="rounded-circle w-100 h-100 object-fit-cover" alt="Preview">
                  <div class="avatar-upload-overlay">
                    <i class="ti ti-camera" style="font-size: 2rem;"></i>
                  </div>
                </div>
                <input type="file" id="avatarInput" name="avatar" class="d-none" 
                       accept="image/*" onchange="previewAvatar(this)">
                <div class="text-muted small">Click to change profile picture</div>
                <small class="form-hint">Recommended size: 400x400 pixels</small>
              </div>

              <div class="row g-3">
                <!-- Username -->
                <div class="col-12">
                  <label class="form-label required">
                    <i class="ti ti-at me-1"></i>Username
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="ti ti-at"></i>
                    </span>
                    <input type="text" name="username" class="form-control" 
                           placeholder="Enter username" required
                           pattern="[a-zA-Z0-9_]{3,32}"
                           title="Username must be 3-32 characters and can only contain letters, numbers, and underscores">
                  </div>
                  <div class="invalid-feedback">Please enter a valid username</div>
                </div>

                <!-- Full Name -->
                <div class="col-12">
                  <label class="form-label">
                    <i class="ti ti-user me-1"></i>Full Name
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="ti ti-user"></i>
                    </span>
                    <input type="text" name="full_name" class="form-control" 
                           placeholder="Enter full name">
                  </div>
                </div>

                <!-- Password -->
                <div class="col-12">
                  <label class="form-label required">
                    <i class="ti ti-lock me-1"></i>Password
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="ti ti-lock"></i>
                    </span>
                    <input type="password" name="password" id="password" 
                           class="form-control" placeholder="Enter password" required
                           minlength="4">
                    <button class="btn btn-outline-secondary" type="button" 
                            onclick="togglePassword()">
                      <i class="ti ti-eye"></i>
                    </button>
                  </div>
                  <div class="invalid-feedback">Password must be at least 4 characters</div>
                </div>

                <!-- Role and Status -->
                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ti ti-shield me-1"></i>Role
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="ti ti-shield"></i>
                    </span>
                    <select name="role" class="form-select">
                      <option value="Admin">
                        <i class="ti ti-shield me-1"></i>Admin
                      </option>
                      <option value="Editor">
                        <i class="ti ti-edit me-1"></i>Editor
                      </option>
                      <option value="User" selected>
                        <i class="ti ti-user me-1"></i>User
                      </option>
                    </select>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">
                    <i class="ti ti-toggle-left me-1"></i>Status
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">
                      <i class="ti ti-toggle-left"></i>
                    </span>
                    <select name="status" class="form-select">
                      <option value="Active" selected>
                        <i class="ti ti-circle-check me-1"></i>Active
                      </option>
                      <option value="Inactive">
                        <i class="ti ti-circle-x me-1"></i>Inactive
                      </option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="card-footer text-end mt-4">
                <button type="submit" class="btn btn-primary">
                  <i class="ti ti-device-floppy me-1"></i>Save User
                </button>
                <a href="<?= BASE_URL ?>/system/users" class="btn btn-link">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.avatar {
  position: relative;
  display: inline-block;
  border: 3px solid var(--tblr-border-color);
  transition: all 0.3s ease;
}

.avatar:hover {
  border-color: var(--tblr-primary);
  transform: scale(1.02);
}

.avatar-upload-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  opacity: 0;
  transition: all 0.3s ease;
}

.avatar:hover .avatar-upload-overlay {
  opacity: 1;
}

.object-fit-cover {
  object-fit: cover;
}

.input-group-text {
  background-color: var(--tblr-bg-surface);
  border-right: none;
}

.input-group .form-control {
  border-left: none;
}

.input-group .form-control:focus {
  border-color: var(--tblr-border-color);
  box-shadow: none;
}

.input-group:focus-within {
  box-shadow: 0 0 0 0.25rem rgba(var(--tblr-primary-rgb), 0.25);
}

.input-group:focus-within .input-group-text,
.input-group:focus-within .form-control {
  border-color: var(--tblr-primary);
}
</style>

<script>
// Preview avatar image
function previewAvatar(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('avatarPreview').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Toggle password visibility
function togglePassword() {
  const passwordInput = document.getElementById('password');
  const type = passwordInput.type === 'password' ? 'text' : 'password';
  passwordInput.type = type;
  
  const icon = document.querySelector('.btn-outline-secondary i');
  icon.className = type === 'password' ? 'ti ti-eye' : 'ti ti-eye-off';
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('.needs-validation');
  form.addEventListener('submit', function(event) {
    if (!form.checkValidity()) {
      event.preventDefault();
      event.stopPropagation();
    }
    form.classList.add('was-validated');
  });
});
</script>
