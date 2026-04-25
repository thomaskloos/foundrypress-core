<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Core App Config (Portable)
|--------------------------------------------------------------------------
| Safe to load before install.
|--------------------------------------------------------------------------
*/

// Filesystem paths
define('FP_ROOT_PATH', dirname(__DIR__));
define('FP_CONFIG_PATH', FP_ROOT_PATH . '/config');
define('FP_INCLUDES_PATH', FP_ROOT_PATH . '/includes');
define('FP_HUB_PATH', FP_ROOT_PATH . '/hub');
define('FP_LICENSE_FILE', FP_ROOT_PATH . '/license.json');

// Detect protocol
$scheme = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443')
) ? 'https' : 'http';

// Detect host
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detect web base path from filesystem install path
$documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
    ? rtrim(str_replace('\\', '/', (string) $_SERVER['DOCUMENT_ROOT']), '/')
    : '';

$rootPathNormalized = rtrim(str_replace('\\', '/', FP_ROOT_PATH), '/');

$basePath = '';

if ($documentRoot !== '' && str_starts_with($rootPathNormalized, $documentRoot)) {
    $basePath = substr($rootPathNormalized, strlen($documentRoot));
    $basePath = $basePath === false ? '' : $basePath;
}

$basePath = '/' . trim((string) $basePath, '/');
$basePath = $basePath === '/' ? '' : $basePath;

define('FP_BASE_PATH', $basePath);
define('FP_BASE_URL', $scheme . '://' . $host . FP_BASE_PATH);
define('FP_ASSETS_URL', FP_BASE_URL . '/assets');