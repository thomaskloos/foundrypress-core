<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once FP_INCLUDES_PATH . '/install-guard.php';

fp_require_install();

require_once FP_CONFIG_PATH . '/site.php';
require_once FP_INCLUDES_PATH . '/functions.php';
require_once FP_INCLUDES_PATH . '/license.php';

$authConfig = FP_INCLUDES_PATH . '/auth-config.php';
$authFile   = FP_INCLUDES_PATH . '/auth.php';

if (file_exists($authConfig)) {
    require_once $authConfig;
}

if (file_exists($authFile)) {
    require_once $authFile;
}