<?php
$title ??= 'Oops! Server Glitched';
$reason ??= 'Something unexpected happened.';
$code ??= 'SRV1';
$debug ??= [];
$diagnosis ??= [];

$uuid = uniqid('err_', true);
$timestamp = date('Y-m-d H:i:s');
$base = defined('BASE_URL') ? BASE_URL : '/';

$phrases = ["Oooops!", "Welp!", "This is awkward...", "Something exploded...", "Well, that didn't work."];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/assets/css/error-404.css" />
</head>
<body>
<div class="error-wrapper animate-fade-in text-center">
  <img src="/assets/img/face_error.svg" alt="Error Face" class="error-img" />

  <div class="rotating-phrase" id="rotatingPhrase" style="margin-bottom: 1.25rem;">Oooops!</div>
  <div class="status-code" style="font-size: 1.25rem; margin-bottom: 1rem;">
    <?= htmlspecialchars($code) ?> — Server Error
  </div>

  <p class="error-subtitle"><?= htmlspecialchars($reason) ?></p>

  <div class="error-meta text-muted small mb-3" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1rem;">
    <div><strong>UUID:</strong> <code><?= $uuid ?></code></div>
    <div><strong>Timestamp:</strong> <?= $timestamp ?></div>
  </div>

  <?php if (!empty($diagnosis)): ?>
    <div class="error-diagnosis">
      <h2>🧠 Doctor’s Suggestion</h2>
      <ul>
        <li><strong>Source:</strong> <?= htmlspecialchars($diagnosis['source'] ?? 'Unknown') ?></li>
        <li><strong>File:</strong> <?= htmlspecialchars($diagnosis['file'] ?? '-') ?></li>
        <li><strong>Line:</strong> <?= htmlspecialchars($diagnosis['line'] ?? '-') ?></li>
        <li><strong>Suggestion:</strong> <?= htmlspecialchars($diagnosis['suggestion'] ?? '-') ?></li>
        <li><strong>Memory:</strong> <?= htmlspecialchars($diagnosis['memory'] ?? '-') ?></li>
        <li><strong>Peak Memory:</strong> <?= htmlspecialchars($diagnosis['peak_memory'] ?? '-') ?></li>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ((defined('APP_DEBUG') && APP_DEBUG) || (defined('DEBUG_MODE') && DEBUG_MODE)): ?>
    <?php if (!empty($debug)): ?>
      <details class="error-details mt-4">
        <summary class="btn btn-outline-secondary">Technical Debug Info</summary>
        <pre class="text-start small mt-3" style="white-space: pre-wrap; word-wrap: break-word; max-height: 300px; overflow: auto;">
<?= htmlspecialchars(print_r($debug, true)) ?>
        </pre>
      </details>
    <?php endif; ?>
  <?php endif; ?>

  <div class="mt-4">
    <a href="<?= $base ?>" class="btn btn-outline-danger">← Beam Me Home</a>
  </div>
</div>

<script>
const phrases = <?= json_encode($phrases) ?>;
const el = document.getElementById('rotatingPhrase');
let index = 0;

setInterval(() => {
  index = (index + 1) % phrases.length;
  el.classList.remove('fade-in');
  void el.offsetWidth;
  el.textContent = phrases[index];
  el.classList.add('fade-in');
}, 3500);
</script>
</body>
</html>
