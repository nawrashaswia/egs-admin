<?php

use App\Core\App;
use App\Helpers\Core\FlashHelper;
use App\Helpers\Core\CSRFHelper;

if (App::get('auth')->check()) {
    App::get('response')->redirect(BASE_URL . '/dashboard');
}

$loginSuccess = FlashHelper::get('login_success');

ob_start();
?>

<div class="login-animated-wrapper animate__animated animate__fadeIn">
  <div class="login-glow-bg"></div>

  <div class="login-split glass">
    <div class="login-left animate__animated animate__fadeInLeft wow animate__slideInLeft" data-wow-delay="0.2s">
      <div class="brand-logo animate__animated animate__bounceIn">
        <i class="ti ti-shield-lock-filled" style="font-size: 2.8rem;"></i>
      </div>
      <h1 class="tracking-in-expand">EGS-ADMIN</h1>
      <p class="fade-in-text">Secure login to your control panel</p>
    </div>

    <div class="login-right animate__animated animate__fadeInRight wow animate__zoomIn" data-wow-delay="0.4s">
      <?php if ($loginSuccess): ?>
        <div class="login-success-card">
          <div class="login-success-inner">
            <div class="icon ti ti-circle-check"></div>
            <h2 class="mb-2">Login Successful!</h2>
            <div class="text-muted mb-2">Redirecting to your dashboard...</div>
            <div class="spinner-border" role="status"></div>
          </div>
        </div>
        <script>
          setTimeout(() => {
            document.querySelector('.login-success-card')?.classList.add('animate__fadeOut');
            setTimeout(() => window.location.href = "<?= BASE_URL ?>/dashboard", 800);
          }, 1500);
        </script>
        <noscript><meta http-equiv="refresh" content="2;url=<?= BASE_URL ?>/dashboard"></noscript>
      <?php else: ?>
        <form class="login-form-card animate__animated animate__fadeInUp wow animate__zoomIn" data-wow-delay="0.6s" action="<?= BASE_URL ?>/login/submit" method="post" autocomplete="off" novalidate>
          <?= CSRFHelper::input('login') ?>

          <div class="text-center mb-4">
            <i class="ti ti-login" style="font-size: 2rem; color:#6f8cff;"></i>
            <h2 class="card-title mb-1">Sign in</h2>
            <p class="text-muted small mb-2">Enter your credentials</p>
          </div>

          <?php FlashHelper::renderToast(); ?>

          <div class="mb-3 input-icon position-relative">
            <span class="input-icon-addon"><i class="ti ti-user"></i></span>
            <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
          </div>

          <div class="mb-2 input-icon position-relative">
            <span class="input-icon-addon"><i class="ti ti-lock"></i></span>
            <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Password" required>
            <span class="show-password-toggle" onclick="togglePassword()">
              <i class="ti ti-eye" id="togglePasswordIcon"></i>
            </span>
          </div>

          <div class="mb-2 text-end">
            <a href="#" class="small text-muted">Forgot password?</a>
          </div>

          <div class="form-footer mt-3">
            <button type="submit" class="btn btn-primary w-100 animate__animated animate__pulse wow" data-wow-delay="1s">
              <i class="ti ti-login me-2"></i> Sign in
            </button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<link href="<?= BASE_URL ?>/assets/css/login.css" rel="stylesheet">

<!-- Include WOW.js and Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
<script>
  new WOW().init();

  function togglePassword() {
    const input = document.getElementById('loginPassword');
    const icon = document.getElementById('togglePasswordIcon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.replace('ti-eye', 'ti-eye-off');
    } else {
      input.type = 'password';
      icon.classList.replace('ti-eye-off', 'ti-eye');
    }
  }
</script>

<?php
$content = ob_get_clean();
\App\Core\ViewRenderer::render('layout/main', [
  'title' => 'Login',
  'fullPage' => true,
  'content' => $content
]);