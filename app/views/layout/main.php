<?php
$title = $title ?? APP_NAME;
$fullPage = $fullPage ?? false; // Used to skip shared layout
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="<?= CHARSET ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title) ?></title>

  <!-- Tabler CSS -->
  <link href="<?= BASE_URL ?>/assets/css/tabler.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/tabler-flags.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/tabler-payments.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/tabler-vendors.min.css" rel="stylesheet">

  <!-- Tabler Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


  <!-- Custom -->
  <link href="<?= BASE_URL ?>/assets/css/custom.css" rel="stylesheet">

   <style>
    body {
      font-family: var(--tblr-font-sans-serif);
      font-size: 0.99rem;
    }
  </style>
</head>

<body class="layout-fluid">
  <div class="page">

<?php if (!$fullPage): ?>
  <?php require VIEWS_PATH . '/components/sidebar.php'; ?>
<?php endif; ?>

<div class="page-wrapper">

  <?php if (!$fullPage): ?>
    <?php require VIEWS_PATH . '/components/topbar.php'; ?>
  <?php endif; ?>

<?php if ($fullPage): ?>
  <div class="page-body-fullscreen">
    <?= $content ?? '' ?>
  </div>
<?php else: ?>
  <div class="page-body">
    <div class="container-xl py-4">
      <?= $content ?? '' ?>
    </div>
  </div>
<?php endif; ?>
  <?php require VIEWS_PATH . '/layout/footer.php'; ?> <!-- 🔥 Always include -->
</div>

  </div>
</body>
</html>
