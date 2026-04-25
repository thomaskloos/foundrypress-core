<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/license.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

/*
|--------------------------------------------------------------------------
| Force Installer Before Site Usage
|--------------------------------------------------------------------------
| If the site has not been installed yet and install.php exists,
| redirect users to the installer instead of showing a broken homepage.
|--------------------------------------------------------------------------
*/

$siteConfigFile = FP_CONFIG_PATH . '/site.php';
$installFile = FP_ROOT_PATH . '/install.php';

if (!is_file($siteConfigFile) && is_file($installFile)) {
    $requestPath = parse_url(
        $_SERVER['REQUEST_URI'] ?? '/',
        PHP_URL_PATH
    ) ?: '/';

    $installPath = rtrim((string) FP_BASE_PATH, '/') . '/install.php';

    if ($requestPath !== $installPath) {
        header('Location: ' . $installPath);
        exit;
    }
}
