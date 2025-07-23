<?php ob_start(); ?>
<div id="ip-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
  <!-- Boutons insérés par JS -->
</div>
<?php
$content = ob_get_clean();
$title   = 'IPs détectées';
include __DIR__ . '/../layout.php';