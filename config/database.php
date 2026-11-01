<?php
require_once __DIR__ . '/database_connection.php';

$localConfigPath = __DIR__ . '/database.local.php';
$hostingConfigPath = __DIR__ . '/database_hosting.php';

$projectIsLocal = ($_SERVER['HTTP_HOST'] ?? '') === 'localhost'
    || strpos((string)($_SERVER['HTTP_HOST'] ?? ''), '127.0.0.1') !== false;

$defaultConfig = [
    'host' => 'localhost',
    'port' => '3307',
    'dbname' => 'uth_shop',
    'username' => 'root',
    'password' => '',
];

$configSource = $projectIsLocal ? $localConfigPath : $hostingConfigPath;
if (is_file($configSource)) {
    $configuredValues = require $configSource;
    if (is_array($configuredValues)) {
        $defaultConfig = array_replace($defaultConfig, array_intersect_key($configuredValues, $defaultConfig));
    }
}

$conn = connectDatabase($defaultConfig, $localConfigPath);
