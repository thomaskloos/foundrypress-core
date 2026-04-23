<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| FoundryPress Install Guard
|--------------------------------------------------------------------------
| Redirects to install.php when config/site.php does not exist.
|--------------------------------------------------------------------------
*/

if (!function_exists('fp_site_config_file')) {
    function fp_site_config_file(): string
    {
        return FP_CONFIG_PATH . '/site.php';
    }
}

if (!function_exists('fp_is_installed')) {
    function fp_is_installed(): bool
    {
        return is_file(fp_site_config_file());
    }
}

if (!function_exists('fp_is_install_request')) {
    function fp_is_install_request(): bool
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($requestUri, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return false;
        }

        $installPath = FP_BASE_PATH . '/install.php';
        $installPath = preg_replace('#/+#', '/', $installPath);
        $installPath = $installPath === null ? '/install.php' : $installPath;

        return $path === $installPath;
    }
}

if (!function_exists('fp_install_url')) {
    function fp_install_url(): string
    {
        $path = FP_BASE_PATH . '/install.php';
        $path = preg_replace('#/+#', '/', $path);

        return $path === null ? '/install.php' : $path;
    }
}

if (!function_exists('fp_redirect_to_install')) {
    function fp_redirect_to_install(): never
    {
        header('Location: ' . fp_install_url());
        exit;
    }
}

if (!function_exists('fp_require_install')) {
    function fp_require_install(): void
    {
        if (fp_is_installed()) {
            return;
        }

        if (fp_is_install_request()) {
            return;
        }

        fp_redirect_to_install();
    }
}