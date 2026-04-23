<?php
declare(strict_types=1);

/**
 * FoundryPress License Functions
 *
 * Shared by the main site and the Hub.
 */

if (!defined('FP_LOCAL_LICENSE_CONFIG_FILE')) {
    define('FP_LOCAL_LICENSE_CONFIG_FILE', dirname(__DIR__) . '/license.json');
}

if (!defined('FP_LICENSE_FILE')) {
    define('FP_LICENSE_FILE', FP_LOCAL_LICENSE_CONFIG_FILE);
}

if (!defined('FP_LICENSE_ACTIVATE_URL')) {
    define('FP_LICENSE_ACTIVATE_URL', 'https://foundrypressapp.com/license-api/activate.php');
}

if (!defined('FP_LICENSE_CHECK_URL')) {
    define('FP_LICENSE_CHECK_URL', 'https://foundrypressapp.com/license-api/check.php');
}

if (!defined('FP_LICENSE_RECHECK_INTERVAL')) {
    define('FP_LICENSE_RECHECK_INTERVAL', 7 * 24 * 60 * 60);
}

if (!defined('FP_LICENSE_FAIL_GRACE')) {
    define('FP_LICENSE_FAIL_GRACE', 14 * 24 * 60 * 60);
}

if (!function_exists('fp_normalize_domain')) {
    function fp_normalize_domain(string $domain): string
    {
        $domain = trim(strtolower($domain));
        if ($domain === '') {
            return '';
        }

        if (str_contains($domain, '://')) {
            $host = parse_url($domain, PHP_URL_HOST);
            if (is_string($host) && $host !== '') {
                $domain = strtolower($host);
            }
        }

        $domain = preg_replace('/:\d+$/', '', $domain) ?? $domain;
        $domain = rtrim($domain, '/');
        $domain = preg_replace('/^www\./', '', $domain) ?? $domain;

        return $domain;
    }
}

if (!function_exists('fp_get_current_domain')) {
    function fp_get_current_domain(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
        return fp_normalize_domain((string) $host);
    }
}

if (!function_exists('fp_read_license_file')) {
    function fp_read_license_file(): array
    {
        if (!is_file(FP_LICENSE_FILE)) {
            return [];
        }

        $json = file_get_contents(FP_LICENSE_FILE);
        if ($json === false || trim($json) === '') {
            return [];
        }

        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }
}

if (!function_exists('fp_write_license_file')) {
    function fp_write_license_file(array $data): bool
    {
        $dir = dirname(FP_LICENSE_FILE);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                return false;
            }
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        return file_put_contents(FP_LICENSE_FILE, $json, LOCK_EX) !== false;
    }
}

if (!function_exists('fp_delete_license_file')) {
    function fp_delete_license_file(): bool
    {
        if (!is_file(FP_LICENSE_FILE)) {
            return true;
        }
        return unlink(FP_LICENSE_FILE);
    }
}

if (!function_exists('fp_remote_post_json')) {
    function fp_remote_post_json(string $url, array $payload): array
    {
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($jsonPayload === false) {
            return [
                'ok' => false,
                'http_code' => 0,
                'data' => [
                    'status' => 'invalid',
                    'message' => 'Could not encode request payload.',
                ],
            ];
        }

        if (!function_exists('curl_init')) {
            return [
                'ok' => false,
                'http_code' => 0,
                'data' => [
                    'status' => 'invalid',
                    'message' => 'cURL is not available on this server.',
                ],
            ];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Content-Length: ' . strlen($jsonPayload),
            ],
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return [
                'ok' => false,
                'http_code' => $httpCode,
                'data' => [
                    'status' => 'invalid',
                    'message' => 'Remote request failed: ' . $curlError,
                ],
            ];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return [
                'ok' => false,
                'http_code' => $httpCode,
                'data' => [
                    'status' => 'invalid',
                    'message' => 'Remote server returned an invalid response.',
                ],
            ];
        }

        return [
            'ok' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'data' => $decoded,
        ];
    }
}

if (!function_exists('fp_activate_remote_license')) {
    function fp_activate_remote_license(string $licenseKey): array
    {
        $licenseKey = trim($licenseKey);
        $domain = fp_get_current_domain();

        if ($licenseKey === '' || $domain === '') {
            return [
                'valid' => false,
                'status' => 'invalid',
                'message' => 'License key or current domain is missing.',
            ];
        }

        $payload = [
            'license_key' => $licenseKey,
            'domain'      => $domain,
            'product'     => 'foundrypress',
            'version'     => defined('FP_VERSION') ? (string) FP_VERSION : '1.0.0',
        ];

        $remote = fp_remote_post_json(FP_LICENSE_ACTIVATE_URL, $payload);
        $data = is_array($remote['data'] ?? null) ? $remote['data'] : [];
        $httpCode = (int) ($remote['http_code'] ?? 0);

        if (($data['valid'] ?? false) !== true) {
            return [
                'valid' => false,
                'status' => (string) ($data['status'] ?? 'invalid'),
                'message' => (string) ($data['message'] ?? 'Activation failed.'),
                'plan' => $data['plan'] ?? null,
                'max_domains' => $data['max_domains'] ?? null,
                'activated_domain' => $data['activated_domain'] ?? $domain,
                'licensed_to' => $data['licensed_to'] ?? null,
                'last_check_http_code' => $httpCode,
                'last_check_result' => 'activation_failed',
                'last_check_message' => (string) ($data['message'] ?? 'Activation failed.'),
            ];
        }

        $localLicense = [
            'licensed_to'           => trim((string) ($data['licensed_to'] ?? '')),
            'license_key'           => strtoupper($licenseKey),
            'product_version'       => defined('FP_VERSION') ? (string) FP_VERSION : '1.0.0',
            'is_activated'          => true,
            'activated_domain'      => (string) ($data['activated_domain'] ?? $domain),
            'license_plan'          => (string) ($data['plan'] ?? ''),
            'max_domains'           => (int) ($data['max_domains'] ?? 0),
            'status'                => (string) ($data['status'] ?? 'active'),
            'message'               => (string) ($data['message'] ?? 'License activated successfully.'),
            'plan'                  => $data['plan'] ?? null,
            'last_checked_at'       => gmdate('c'),
            'last_check_http_code'  => $httpCode,
            'last_check_result'     => 'activation_success',
            'last_check_message'    => (string) ($data['message'] ?? 'License activated successfully.'),
        ];

        if (!fp_write_license_file($localLicense)) {
            return [
                'valid' => false,
                'status' => 'invalid',
                'message' => 'License activated, but local license file could not be written.',
                'last_check_http_code' => $httpCode,
                'last_check_result' => 'local_write_failed',
                'last_check_message' => 'License activated, but local license file could not be written.',
            ];
        }

        return [
            'valid' => true,
            'status' => (string) ($localLicense['status'] ?? 'active'),
            'message' => (string) ($localLicense['message'] ?? 'License activated successfully.'),
            'plan' => $localLicense['license_plan'] ?? null,
            'max_domains' => $localLicense['max_domains'] ?? null,
            'activated_domain' => $localLicense['activated_domain'] ?? $domain,
            'licensed_to' => $localLicense['licensed_to'] ?? null,
            'last_check_http_code' => $httpCode,
            'last_check_result' => 'activation_success',
            'last_check_message' => (string) ($localLicense['message'] ?? 'License activated successfully.'),
        ];
    }
}

if (!function_exists('fp_check_remote_license')) {
    function fp_check_remote_license(string $licenseKey, string $domain): array
    {
        $licenseKey = trim($licenseKey);
        $domain = fp_normalize_domain($domain);

        if ($licenseKey === '' || $domain === '') {
            return [
                'ok' => false,
                'http_code' => 0,
                'status' => 'invalid',
                'message' => 'License key or domain is missing.',
                'plan' => null,
                'max_domains' => null,
                'activated_domain' => $domain,
                'licensed_to' => null,
            ];
        }

        $remote = fp_remote_post_json(FP_LICENSE_CHECK_URL, [
            'license_key' => $licenseKey,
            'domain'      => $domain,
            'product'     => 'foundrypress',
            'version'     => defined('FP_VERSION') ? (string) FP_VERSION : '1.0.0',
        ]);

        $data = is_array($remote['data'] ?? null) ? $remote['data'] : [];

        return [
            'ok' => (bool) ($remote['ok'] ?? false),
            'http_code' => (int) ($remote['http_code'] ?? 0),
            'status' => (string) ($data['status'] ?? 'invalid'),
            'message' => (string) ($data['message'] ?? 'Remote check failed.'),
            'plan' => $data['plan'] ?? null,
            'max_domains' => $data['max_domains'] ?? null,
            'activated_domain' => $data['activated_domain'] ?? $domain,
            'licensed_to' => $data['licensed_to'] ?? null,
        ];
    }
}

if (!function_exists('fp_get_license_status')) {
    function fp_get_license_status(): array
    {
        $license = fp_read_license_file();

        if (empty($license)) {
            return [
                'status' => 'invalid',
                'message' => 'No local license file found.',
                'plan' => null,
                'max_domains' => null,
                'activated_domain' => null,
                'licensed_to' => null,
                'last_check_http_code' => 0,
                'last_check_result' => 'no_local_license',
                'last_check_message' => 'No local license file found.',
            ];
        }

        $licenseKey = trim((string) ($license['license_key'] ?? ''));
        $domain = fp_get_current_domain();

        if ($licenseKey === '' || $domain === '') {
            return [
                'status' => 'invalid',
                'message' => 'License key or current domain is missing.',
                'plan' => $license['license_plan'] ?? ($license['plan'] ?? null),
                'max_domains' => $license['max_domains'] ?? null,
                'activated_domain' => $license['activated_domain'] ?? null,
                'licensed_to' => $license['licensed_to'] ?? null,
                'last_check_http_code' => (int) ($license['last_check_http_code'] ?? 0),
                'last_check_result' => 'missing_key_or_domain',
                'last_check_message' => 'License key or current domain is missing.',
            ];
        }

        $lastCheckedAt = strtotime((string) ($license['last_checked_at'] ?? ''));
        $shouldRecheck = !$lastCheckedAt || (time() - $lastCheckedAt) >= FP_LICENSE_RECHECK_INTERVAL;

        if (!$shouldRecheck) {
            return [
                'status' => (string) ($license['status'] ?? 'invalid'),
                'message' => (string) ($license['message'] ?? 'License loaded from local cache.'),
                'plan' => $license['license_plan'] ?? ($license['plan'] ?? null),
                'max_domains' => $license['max_domains'] ?? null,
                'activated_domain' => $license['activated_domain'] ?? null,
                'licensed_to' => $license['licensed_to'] ?? null,
                'last_check_http_code' => (int) ($license['last_check_http_code'] ?? 0),
                'last_check_result' => (string) ($license['last_check_result'] ?? 'cached'),
                'last_check_message' => (string) ($license['last_check_message'] ?? ($license['message'] ?? 'License loaded from local cache.')),
            ];
        }

        $remote = fp_check_remote_license($licenseKey, $domain);
        $httpCode = (int) ($remote['http_code'] ?? 0);
        $remoteStatus = (string) ($remote['status'] ?? 'invalid');
        $remoteMessage = (string) ($remote['message'] ?? 'Remote license validation failed.');

        $hasStructuredResponse =
            isset($remote['status']) ||
            isset($remote['message']) ||
            isset($remote['plan']) ||
            isset($remote['max_domains']) ||
            isset($remote['licensed_to']);

        if (($remote['ok'] ?? false) === true || $hasStructuredResponse) {
            $license['status']               = $remote['status'] ?? ($license['status'] ?? 'invalid');
            $license['message']              = $remote['message'] ?? ($license['message'] ?? 'Remote license check completed.');
            $license['license_plan']         = $remote['plan'] ?? ($license['license_plan'] ?? ($license['plan'] ?? null));
            $license['plan']                 = $remote['plan'] ?? ($license['plan'] ?? null);
            $license['max_domains']          = $remote['max_domains'] ?? ($license['max_domains'] ?? null);
            $license['activated_domain']     = $remote['activated_domain'] ?? $domain;
            $license['licensed_to']          = $remote['licensed_to'] ?? ($license['licensed_to'] ?? null);
            $license['is_activated']         = (($remote['status'] ?? 'invalid') === 'active');
            $license['last_check_http_code'] = $httpCode;
            $license['last_check_message']   = $remoteMessage;

            if (($remote['ok'] ?? false) === true && $remoteStatus === 'active') {
                $license['last_checked_at'] = gmdate('c');
                $license['last_check_result'] = 'remote_valid';
            } elseif ($remoteStatus === 'revoked') {
                $license['last_check_result'] = 'remote_revoked';
            } elseif ($remoteStatus === 'expired') {
                $license['last_check_result'] = 'remote_expired';
            } elseif ($remoteStatus === 'invalid') {
                $license['last_check_result'] = 'remote_invalid';
            } else {
                $license['last_check_result'] = 'remote_response_received';
            }

            fp_write_license_file($license);

            return [
                'status' => (string) ($license['status'] ?? 'invalid'),
                'message' => (string) ($license['message'] ?? ''),
                'plan' => $license['license_plan'] ?? ($license['plan'] ?? null),
                'max_domains' => $license['max_domains'] ?? null,
                'activated_domain' => $license['activated_domain'] ?? null,
                'licensed_to' => $license['licensed_to'] ?? null,
                'last_check_http_code' => (int) ($license['last_check_http_code'] ?? 0),
                'last_check_result' => (string) ($license['last_check_result'] ?? 'remote_response_received'),
                'last_check_message' => (string) ($license['last_check_message'] ?? ''),
            ];
        }

        if ($lastCheckedAt && (time() - $lastCheckedAt) < FP_LICENSE_FAIL_GRACE) {
            $license['last_check_http_code'] = $httpCode;
            $license['last_check_result'] = 'remote_unreachable_using_cache';
            $license['last_check_message'] = 'Using cached license data. Remote validation temporarily unavailable.';
            fp_write_license_file($license);

            return [
                'status' => (string) ($license['status'] ?? 'active'),
                'message' => 'Using cached license data. Remote validation temporarily unavailable.',
                'plan' => $license['license_plan'] ?? ($license['plan'] ?? null),
                'max_domains' => $license['max_domains'] ?? null,
                'activated_domain' => $license['activated_domain'] ?? $domain,
                'licensed_to' => $license['licensed_to'] ?? null,
                'last_check_http_code' => (int) ($license['last_check_http_code'] ?? 0),
                'last_check_result' => (string) ($license['last_check_result'] ?? 'remote_unreachable_using_cache'),
                'last_check_message' => (string) ($license['last_check_message'] ?? 'Using cached license data. Remote validation temporarily unavailable.'),
            ];
        }

        $license['status'] = 'invalid';
        $license['message'] = 'Remote license validation failed and grace period has expired.';
        $license['is_activated'] = false;
        $license['last_check_http_code'] = $httpCode;
        $license['last_check_result'] = 'remote_failed_grace_expired';
        $license['last_check_message'] = $remoteMessage;
        fp_write_license_file($license);

        return [
            'status' => 'invalid',
            'message' => 'Remote license validation failed and grace period has expired.',
            'plan' => $license['license_plan'] ?? ($license['plan'] ?? null),
            'max_domains' => $license['max_domains'] ?? null,
            'activated_domain' => $license['activated_domain'] ?? $domain,
            'licensed_to' => $license['licensed_to'] ?? null,
            'last_check_http_code' => (int) ($license['last_check_http_code'] ?? 0),
            'last_check_result' => (string) ($license['last_check_result'] ?? 'remote_failed_grace_expired'),
            'last_check_message' => (string) ($license['last_check_message'] ?? $remoteMessage),
        ];
    }
}

if (!function_exists('fp_license_is_active')) {
    function fp_license_is_active(): bool
    {
        $status = fp_get_license_status();
        return ($status['status'] ?? 'invalid') === 'active';
    }
}

if (!function_exists('fp_validate_license_or_redirect')) {
    function fp_validate_license_or_redirect(string $redirectUrl = '/hub/config.php'): void
    {
        $status = fp_get_license_status();
        if (($status['status'] ?? 'invalid') !== 'active') {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
}

if (!function_exists('fp_require_valid_license')) {
    function fp_require_valid_license(): void
    {
        fp_validate_license_or_redirect('/hub/config.php');
    }
}

if (!function_exists('fp_format_license_datetime')) {
    function fp_format_license_datetime(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '—';
        }
        $ts = strtotime($value);
        if ($ts === false) {
            return $value;
        }
        return date('M j, Y g:i A T', $ts);
    }
}
