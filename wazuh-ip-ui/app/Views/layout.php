<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $title ?? 'IPs Wazuh' ?></title>
<!-- Tailwind CDN (rapide) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
window.DASHBOARD_BASE = "<?= htmlspecialchars($dashboardBase ?? '', ENT_QUOTES) ?>";
</script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">
<header class="bg-white shadow p-4 mb-6">
  <div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold"><?= $title ?? '' ?></h1>
  </div>
</header>
<main class="container mx-auto px-4">
  <?= $content ?>
</main>
<script src="/assets/js/app.js"></script>
</body>
</html>