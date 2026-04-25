<?php
declare(strict_types=1);

require_once __DIR__ . '/auth-config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!function_exists('fp_hub_is_configured')) {
    function fp_hub_is_configured(): bool
    {
        return defined('FP_HUB_PASSWORD_HASH') && trim((string) FP_HUB_PASSWORD_HASH) !== '';
    }
}

if (!function_exists('fp_hub_is_logged_in')) {
    function fp_hub_is_logged_in(): bool
    {
        return !empty($_SESSION[FP_HUB_SESSION_KEY]);
    }
}

if (!function_exists('fp_hub_login')) {
    function fp_hub_login(string $username, string $password): bool
    {
        $username = trim($username);

        if (!fp_hub_is_configured()) {
            return false;
        }

        if ($username !== FP_HUB_USERNAME) {
            return false;
        }

        if (!password_verify($password, FP_HUB_PASSWORD_HASH)) {
            return false;
        }

        session_regenerate_id(true);

        $_SESSION[FP_HUB_SESSION_KEY] = [
            'username' => $username,
            'logged_in_at' => date('c'),
        ];

        return true;
    }
}

if (!function_exists('fp_hub_logout')) {
    function fp_hub_logout(): void
    {
        unset($_SESSION[FP_HUB_SESSION_KEY]);
        session_regenerate_id(true);
    }
}

if (!function_exists('fp_require_hub_login')) {
    function fp_require_hub_login(): void
    {
        if (!fp_hub_is_configured()) {
            header('Location: /hub/setup-password.php');
            exit;
        }

        if (fp_hub_is_logged_in()) {
            return;
        }

        $redirect = $_SERVER['REQUEST_URI'] ?? '/hub/';
        header('Location: /hub/login.php?redirect=' . rawurlencode($redirect));
        exit;
    }
}