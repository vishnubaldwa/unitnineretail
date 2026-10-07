<?php
/**
 * Unit Nine Retail - Email Management Portal Configuration
 */
defined('UNR_ADMIN') or define('UNR_ADMIN', true);

$configFile = __DIR__ . '/config.json';

// Default configuration
$defaultConfig = [
    'cpanel_host'   => '127.0.0.1',
    'cpanel_port'   => 2083,
    'cpanel_user'   => 'unitnineretail',
    'cpanel_token'  => 'MMBN79KXOE2BIWMYQXHBECPHSNHAEA4M',
    'domain'        => 'unitnineretail.com',
    'admin_user'    => 'admin',
    'admin_pass'    => 'UnitNine@2026', // Can be changed in Admin settings
    'company_name'  => 'Unit Nine Retail',
];

if (!file_exists($configFile)) {
    file_put_contents($configFile, json_encode($defaultConfig, JSON_PRETTY_PRINT));
}

$config = json_decode(file_get_contents($configFile), true) ?: $defaultConfig;

return $config;
