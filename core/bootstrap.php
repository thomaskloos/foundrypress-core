<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('FOUNDRYPRESSSESSID');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

require_once FP_INCLUDES_PATH . '/install-guard.php';

fp_require_install();

require_once FP_CONFIG_PATH . '/site.php';
require_once FP_INCLUDES_PATH . '/functions.php';
require_once FP_INCLUDES_PATH . '/license.php';

$authConfigFiles = [
    FP_INCLUDES_PATH . '/auth-config.php',
    FP_HUB_PATH . '/includes/auth-config.php',
];

$authFiles = [
    FP_INCLUDES_PATH . '/auth.php',
    FP_HUB_PATH . '/includes/auth.php',
];

foreach ($authConfigFiles as $authConfigFile) {
    if (is_file($authConfigFile)) {
        require_once $authConfigFile;
        break;
    }
}

foreach ($authFiles as $authFile) {
    if (is_file($authFile)) {
        require_once $authFile;
        break;
    }
}