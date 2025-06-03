<?php
$title ??= 'Oops! Page Glitched';
$base = defined('BASE_URL') ? BASE_URL : '/';

$phrases = ["Oooops!", "Welp!", "This is awkward...", "404. Not today.", "Uh oh!"];
$diagnosis = [
  'symptoms' => 'The page escaped reality.',
  'cause' => 'Broken link or mistyped route.',
  'suggestion' => 'Return home and try again from a safe place.',
];

$debug = [
  'URI' => $_SERVER['REQUEST_URI'],
  'Method' => $_SERVER['REQUEST_METHOD'],
  'Client IP' => $_SERVER['REMOTE_ADDR'],
  'User Agent' => $_SERVER['HTTP_USER_AGENT'],
  'Timestamp' => gmdate("Y-m-d\TH:i:s\Z"),
  'Session ID' => session_id(),
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $title ?></title>
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/assets/css/error-404.css" />
</head>
<body>
  <div class="error-wrapper">
    <img src="/assets/img/face_error.svg" alt="Error Face" class="error-img" />

    <div class="rotating-phrase" id="rotatingPhrase">Oooops!</div>
    <div class="status-code">404 — Not Found</div>

    <p class="error-subtitle">The page you're looking for went off the rails.</p>

    <div class="error-diagnosis">
      <h2>Doctor’s Suggestion</h2>
      <ul>
        <li><strong>Symptoms:</strong> <?= $diagnosis['symptoms'] ?></li>
        <li><strong>Cause:</strong> <?= $diagnosis['cause'] ?></li>
        <li><strong>Suggestion:</strong> <?= $diagnosis['suggestion'] ?></li>
      </ul>
    </div>

    <details class="error-details">
      <summary>Technical Debug Info</summary>
      <ul>
        <?php foreach ($debug as $k => $v): ?>
          <li><strong><?= $k ?>:</strong> <?= htmlspecialchars($v) ?></li>
        <?php endforeach; ?>
      </ul>
    </details>

<a href="<?= $base ?: '/' ?>" class="btn mt-4" role="button" rel="noopener">← Beam Me Home</a>
  </div>

  <script>
    const phrases = ["Oooops!", "Welp!", "This is awkward...", "404. Not today.", "Uh oh!"];
    const el = document.getElementById('rotatingPhrase');
    let index = 0;

    setInterval(() => {
      index = (index + 1) % phrases.length;
      el.classList.remove('fade-in');
      void el.offsetWidth; // force reflow
      el.textContent = phrases[index];
      el.classList.add('fade-in');
    }, 3500);
  </script>
</body>
</html>
