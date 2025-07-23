<?php
declare(strict_types=1);

ini_set('display_errors', 1); // à enlever en prod
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\DashboardProxyClient;
use App\Controllers\IpController;

$config = require __DIR__ . '/../app/Config/config.php';

$client = new DashboardProxyClient(
    $config['dashboard']['url'],
    $config['dashboard']['user'],
    $config['dashboard']['password'],
    $config['dashboard']['verify_ssl']
);

$ctrl = new IpController(
    $client,
    __DIR__ . '/../storage/cache/ips.json',
    $config['cache_ttl']
);

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode($ctrl->list());
    exit;
}

$dashboardBase = $config['dashboard_base_url'];
include __DIR__ . '/../app/Views/ip/list.php';
