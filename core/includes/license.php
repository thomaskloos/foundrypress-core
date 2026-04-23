<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| FoundryPress License Functions
|--------------------------------------------------------------------------
*/

if (!function_exists('fp_license_file')) {
    function fp_license_file(): string
    {
        return dirname(__DIR__) . '/license.json';
    }
}

if (!function_exists('fp_license_data')) {
    function fp_license_data(): array
    {
        $file = fp_license_file();

        if (!is_file($file)) {
            return [
                'license_key' => '',
                'domain'      => '',
                'status'      => 'missing',
                'installed_at'=> null,
                'last_check'  => null,
            ];
        }

        $json = file_get_contents($file);
        if ($json === false || trim($json) === '') {
            return [
                'license_key' => '',
                'domain'      => '',
                'status'      => 'missing',
                'installed_at'=> null,
                'last_check'  => null,
            ];
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            return [
                'license_key' => '',
                'domain'      => '',
                'status'      => 'invalid',
                'installed_at'=> null,
                'last_check'  => null,
            ];
        }

        return array_merge([
            'license_key' => '',
            'domain'      => '',
            'status'      => 'missing',
            'installed_at'=> null,
            'last_check'  => null,
        ], $data);
    }
}

if (!function_exists('fp_license_key')) {
    function fp_license_key(): string
    {
        $data = fp_license_data();
        return trim((string) ($data['license_key'] ?? ''));
    }
}

if (!function_exists('fp_license_status')) {
    function fp_license_status(): string
    {
        $data = fp_license_data();
        return (string) ($data['status'] ?? 'missing');
    }
}

if (!function_exists('fp_has_license_key')) {
    function fp_has_license_key(): bool
    {
        return fp_license_key() !== '';
    }
}

if (!function_exists('fp_license_is_active')) {
    function fp_license_is_active(): bool
    {
        return fp_license_status() === 'active';
    }
}

if (!function_exists('fp_current_domain')) {
    function fp_current_domain(): string
    {
        return $_SERVER['HTTP_HOST'] ?? '';
    }
}

if (!function_exists('fp_license_matches_domain')) {
    function fp_license_matches_domain(): bool
    {
        $data = fp_license_data();

        $savedDomain = strtolower(trim((string) ($data['domain'] ?? '')));
        $currentDomain = strtolower(trim(fp_current_domain()));

        if ($savedDomain === '' || $currentDomain === '') {
            return false;
        }

        return $savedDomain === $currentDomain;
    }
}

if (!function_exists('fp_license_is_valid_for_local_use')) {
    function fp_license_is_valid_for_local_use(): bool
    {
        if (!fp_has_license_key()) {
            return false;
        }

        $status = fp_license_status();

        if (!in_array($status, ['active', 'valid'], true)) {
            return false;
        }

        return fp_license_matches_domain();
    }
}

if (!function_exists('fp_write_license_data')) {
    function fp_write_license_data(array $data): bool
    {
        $file = fp_license_file();

        $payload = array_merge([
            'license_key'  => '',
            'domain'       => fp_current_domain(),
            'status'       => 'inactive',
            'installed_at' => null,
            'last_check'   => null,
        ], $data);

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $json !== false && file_put_contents($file, $json) !== false;
    }
}

if (!function_exists('fp_update_license_status')) {
    function fp_update_license_status(string $status, ?string $lastCheck = null): bool
    {
        $data = fp_license_data();
        $data['status'] = $status;
        $data['last_check'] = $lastCheck ?? date(DATE_ATOM);

        return fp_write_license_data($data);
    }
}