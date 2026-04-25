<?php
declare(strict_types=1);

if (!defined('FP_HUB_USERNAME')) {
    define('FP_HUB_USERNAME', defined('FP_ADMIN_USERNAME') ? (string) FP_ADMIN_USERNAME : 'admin');
}

if (!defined('FP_HUB_PASSWORD_HASH')) {
    define('FP_HUB_PASSWORD_HASH', defined('FP_ADMIN_PASSWORD_HASH') ? (string) FP_ADMIN_PASSWORD_HASH : '');
}

if (!defined('FP_HUB_SESSION_KEY')) {
    define('FP_HUB_SESSION_KEY', 'foundrypress_hub_user');
}