<?php

use App\Helpers\Core\FlashHelper;

// Assume $fullPage is passed in from layout/main.php
$fullPage = $fullPage ?? false;

?>

<!-- ✅ Flash Toast Notification -->
<?php FlashHelper::renderToast(); ?>

<?php if (!$fullPage): ?>
  <footer class="footer footer-transparent d-print-none mt-auto">
    <div class="container-xl text-center small text-muted">
      &copy; <?= date('Y') ?> <?= APP_NAME ?> — All rights reserved.
    </div>
  </footer>
<?php endif; ?>

<!-- JS Dependencies -->
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // re-enable any manual Bootstrap JS if needed here
  });
</script>


<script src="<?= BASE_URL ?>/assets/js/tabler.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/layout.core.js"></script>

<!-- Optional Attachment Script -->
<?php if (!$fullPage && !empty($ref)): ?>
  <script>
    window.currentAttachmentRef = <?= json_encode($ref) ?>;
    <?php if (!empty($ruleId)): ?>
    window.currentAttachmentRuleId = <?= json_encode($ruleId) ?>;
    <?php endif; ?>
  </script>
  <script type="module">
    import { setupUploader } from "/assets/js/attachment_uploader.js";
    import "/assets/js/attachment_modal.js";
    setupUploader();
  </script>
<?php endif; ?>
