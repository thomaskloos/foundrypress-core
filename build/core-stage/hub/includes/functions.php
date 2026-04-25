<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| FoundryPress
|--------------------------------------------------------------------------
| © 2026 Kloos Enterprises. All rights reserved.
|
| This software is licensed, not sold. Unauthorized copying, modification,
| distribution, resale, or sublicensing of this software, in whole or in part,
| is strictly prohibited without explicit written permission.
|
| Each installation must be activated with a valid license key. License terms,
| usage limits, and domain restrictions are enforced via the FoundryPress
| licensing system.
|
| DISCLAIMER:
| This software is provided "as is", without warranty of any kind, express or
| implied, including but not limited to warranties of merchantability, fitness
| for a particular purpose, and noninfringement. In no event shall the authors
| or copyright holders be liable for any claim, damages, or other liability,
| whether in an action of contract, tort, or otherwise, arising from, out of,
| or in connection with the software or the use or other dealings in the
| software.
|
| https://foundrypressapp.com
|--------------------------------------------------------------------------
*/


require_once __DIR__ . '/license-functions.php';

/*
|--------------------------------------------------------------------------
| Output Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        global $baseUrl;

        return rtrim((string) $baseUrl, '/') . '/assets/' . ltrim($path, '/');
    }
}

/*
|--------------------------------------------------------------------------
| URL Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('brand_articles_url')) {
    function brand_articles_url(string $brand): string
    {
        return '/articles/' . rawurlencode($brand) . '/';
    }
}

if (!function_exists('brand_article_url')) {
    function brand_article_url(string $brand, string $slug): string
    {
        return '/articles/' . rawurlencode($brand) . '/' . rawurlencode($slug);
    }
}

if (!function_exists('is_active_path')) {
    function is_active_path(string $needle, string $path): bool
    {
        if ($needle === '/hub/' && ($path === '/hub' || $path === '/hub/')) {
            return true;
        }

        return str_contains($path, $needle);
    }
}

/*
|--------------------------------------------------------------------------
| License / System Config
|--------------------------------------------------------------------------
*/

if (!defined('FP_LOCAL_LICENSE_CONFIG_FILE')) {
    define('FP_LOCAL_LICENSE_CONFIG_FILE', dirname(__DIR__, 2) . '/license.json');
}

if (!function_exists('load_license_config')) {
    function load_license_config(): array
    {
        if (!is_file(FP_LOCAL_LICENSE_CONFIG_FILE)) {
            return [
                'licensed_to' => '',
                'license_key' => '',
                'product_version' => defined('FP_VERSION') ? (string) FP_VERSION : '1.0.0',
                'is_activated' => false,
                'activated_domain' => '',
                'license_plan' => '',
                'max_domains' => 0,
                'status' => 'invalid',
                'message' => '',
                'last_checked_at' => '',
                'last_check_http_code' => 0,
                'last_check_result' => '',
                'last_check_message' => '',
            ];
        }

        $json = file_get_contents(FP_LOCAL_LICENSE_CONFIG_FILE);

        if ($json === false || trim($json) === '') {
            return [
                'licensed_to' => '',
                'license_key' => '',
                'product_version' => defined('FP_VERSION') ? (string) FP_VERSION : '1.0.0',
                'is_activated' => false,
                'activated_domain' => '',
                'license_plan' => '',
                'max_domains' => 0,
                'status' => 'invalid',
                'message' => '',
                'last_checked_at' => '',
                'last_check_http_code' => 0,
                'last_check_result' => '',
                'last_check_message' => '',
            ];
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            return [
                'licensed_to' => '',
                'license_key' => '',
                'product_version' => defined('FP_VERSION') ? (string) FP_VERSION : '1.0.0',
                'is_activated' => false,
                'activated_domain' => '',
                'license_plan' => '',
                'max_domains' => 0,
                'status' => 'invalid',
                'message' => '',
                'last_checked_at' => '',
                'last_check_http_code' => 0,
                'last_check_result' => '',
                'last_check_message' => '',
            ];
        }

        $status = trim((string) ($data['status'] ?? ''));
        $activatedDomain = trim((string) ($data['activated_domain'] ?? ''));
        $licensePlan = trim((string) ($data['license_plan'] ?? ($data['plan'] ?? '')));
        $maxDomains = (int) ($data['max_domains'] ?? 0);

        $isActivated = isset($data['is_activated'])
            ? (bool) $data['is_activated']
            : ($status === 'active' && $activatedDomain !== '');

        return [
            'licensed_to' => trim((string) ($data['licensed_to'] ?? '')),
            'license_key' => strtoupper(trim((string) ($data['license_key'] ?? ''))),
            'product_version' => trim((string) ($data['product_version'] ?? (defined('FP_VERSION') ? (string) FP_VERSION : '1.0.0'))),
            'is_activated' => $isActivated,
            'activated_domain' => $activatedDomain,
            'license_plan' => $licensePlan,
            'max_domains' => $maxDomains,
            'status' => $status !== '' ? $status : ($isActivated ? 'active' : 'invalid'),
            'message' => trim((string) ($data['message'] ?? '')),
            'last_checked_at' => trim((string) ($data['last_checked_at'] ?? '')),
            'last_check_http_code' => (int) ($data['last_check_http_code'] ?? 0),
            'last_check_result' => trim((string) ($data['last_check_result'] ?? '')),
            'last_check_message' => trim((string) ($data['last_check_message'] ?? '')),
        ];
    }
}

if (!function_exists('save_license_config')) {
    function save_license_config(array $input): bool
    {
        $existing = load_license_config();

        $merged = [
            'licensed_to' => trim((string) ($input['licensed_to'] ?? $existing['licensed_to'] ?? '')),
            'license_key' => strtoupper(trim((string) ($input['license_key'] ?? $existing['license_key'] ?? ''))),
            'product_version' => trim((string) ($input['product_version'] ?? $existing['product_version'] ?? (defined('FP_VERSION') ? (string) FP_VERSION : '1.0.0'))),
            'is_activated' => isset($input['is_activated'])
                ? (bool) $input['is_activated']
                : (bool) ($existing['is_activated'] ?? false),
            'activated_domain' => trim((string) ($input['activated_domain'] ?? $existing['activated_domain'] ?? '')),
            'license_plan' => trim((string) ($input['license_plan'] ?? $input['plan'] ?? $existing['license_plan'] ?? '')),
            'max_domains' => (int) ($input['max_domains'] ?? $existing['max_domains'] ?? 0),
            'status' => trim((string) ($input['status'] ?? $existing['status'] ?? '')),
            'message' => trim((string) ($input['message'] ?? $existing['message'] ?? '')),
            'last_checked_at' => trim((string) ($input['last_checked_at'] ?? $existing['last_checked_at'] ?? '')),
            'last_check_http_code' => (int) ($input['last_check_http_code'] ?? $existing['last_check_http_code'] ?? 0),
            'last_check_result' => trim((string) ($input['last_check_result'] ?? $existing['last_check_result'] ?? '')),
            'last_check_message' => trim((string) ($input['last_check_message'] ?? $existing['last_check_message'] ?? '')),
        ];

        if ($merged['status'] === '') {
            $merged['status'] = $merged['is_activated'] ? 'active' : 'invalid';
        }

        if ($merged['message'] === '' && $merged['status'] === 'active') {
            $merged['message'] = 'License activated successfully.';
        }

        if ($merged['license_plan'] === '' && !empty($input['plan'])) {
            $merged['license_plan'] = trim((string) $input['plan']);
        }

        $dir = dirname(FP_LOCAL_LICENSE_CONFIG_FILE);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                return false;
            }
        }

        $json = json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return false;
        }

        return file_put_contents(FP_LOCAL_LICENSE_CONFIG_FILE, $json, LOCK_EX) !== false;
    }
}

if (!function_exists('clear_license_config')) {
    function clear_license_config(): bool
    {
        if (!is_file(FP_LOCAL_LICENSE_CONFIG_FILE)) {
            return true;
        }

        return unlink(FP_LOCAL_LICENSE_CONFIG_FILE);
    }
}

/*
|--------------------------------------------------------------------------
| Theme Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('get_available_themes')) {
    function get_available_themes(): array
    {
        $themesRoot = dirname(__DIR__) . '/themes';

        if (!is_dir($themesRoot)) {
            return [];
        }

        $items = scandir($themesRoot);

        if ($items === false) {
            return [];
        }

        $themes = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $themePath = $themesRoot . '/' . $item;

            if (!is_dir($themePath)) {
                continue;
            }

            if (!is_file($themePath . '/theme.css')) {
                continue;
            }

            $meta = [];
            $metaFile = $themePath . '/theme.php';

            if (is_file($metaFile)) {
                $loaded = require $metaFile;

                if (is_array($loaded)) {
                    $meta = $loaded;
                }
            }

            $themes[] = [
                'slug' => $item,
                'name' => (string) ($meta['name'] ?? ucwords(str_replace('-', ' ', $item))),
                'description' => (string) ($meta['description'] ?? ''),
                'label' => (string) ($meta['label'] ?? 'Theme'),
                'colors' => is_array($meta['colors'] ?? null) ? $meta['colors'] : [],
                'css' => '/themes/' . $item . '/theme.css',
            ];
        }

        usort($themes, static function (array $a, array $b): int {
            return strcasecmp($a['name'], $b['name']);
        });

        return $themes;
    }
}

/*
|--------------------------------------------------------------------------
| Brand Config Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('load_brand_config')) {
    function load_brand_config(string $brandSlug): array
    {
        $file = dirname(__DIR__) . '/brands/' . $brandSlug . '/brand.php';

        if (!is_file($file)) {
            return [];
        }

        $config = require $file;

        return is_array($config) ? $config : [];
    }
}

if (!function_exists('save_brand_config')) {
    function save_brand_config(string $brandSlug, array $config): bool
    {
        $file = dirname(__DIR__) . '/brands/' . $brandSlug . '/brand.php';

        if ($brandSlug === '' || !is_file($file)) {
            return false;
        }

        $export = var_export($config, true);

        $content = <<<PHP
<?php
declare(strict_types=1);

return {$export};
PHP;

        return file_put_contents($file, $content) !== false;
    }
}

/*
|--------------------------------------------------------------------------
| Article Data Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('load_articles_for_brand')) {
    function load_articles_for_brand(string $brandSlug): array
    {
        $file = dirname(__DIR__) . '/brands/' . $brandSlug . '/_data/articles.json';

        if (!is_file($file)) {
            return [];
        }

        $json = file_get_contents($file);

        if ($json === false || trim($json) === '') {
            return [];
        }

        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }
}

if (!function_exists('find_article_by_slug')) {
    function find_article_by_slug(array $articles, string $slug): ?array
    {
        foreach ($articles as $article) {
            if (($article['slug'] ?? '') === $slug) {
                return is_array($article) ? $article : null;
            }
        }

        return null;
    }
}

if (!function_exists('normalize_article_content')) {
    function normalize_article_content(string $content): array
    {
        $content = trim($content);

        if ($content === '') {
            return [];
        }

        $blocks = preg_split("/\\R{2,}/", $content) ?: [];
        $paragraphs = [];

        foreach ($blocks as $block) {
            $block = trim((string) $block);

            if ($block !== '') {
                $paragraphs[] = $block;
            }
        }

        return $paragraphs;
    }
}

/*
|--------------------------------------------------------------------------
| Prompt Data Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('load_prompts_for_brand')) {
    function load_prompts_for_brand(string $brandSlug): array
    {
        $file = dirname(__DIR__) . '/brands/' . $brandSlug . '/_data/prompts.json';

        if (!is_file($file)) {
            return [];
        }

        $json = file_get_contents($file);

        if ($json === false || trim($json) === '') {
            return [];
        }

        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }
}

if (!function_exists('save_prompts_for_brand')) {
    function save_prompts_for_brand(string $brandSlug, array $prompts): bool
    {
        $file = dirname(__DIR__) . '/brands/' . $brandSlug . '/_data/prompts.json';

        $json = json_encode($prompts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return false;
        }

        return file_put_contents($file, $json) !== false;
    }
}

/*
|--------------------------------------------------------------------------
| Brand Registry Helpers
|--------------------------------------------------------------------------
*/

if (!function_exists('get_brand_directories')) {
    function get_brand_directories(): array
    {
        $brandsRoot = dirname(__DIR__) . '/brands';

        if (!is_dir($brandsRoot)) {
            return [];
        }

        $items = scandir($brandsRoot);

        if ($items === false) {
            return [];
        }

        $brands = [];

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $brandsRoot . '/' . $item;

            if (!is_dir($fullPath)) {
                continue;
            }

            if (!is_file($fullPath . '/brand.php')) {
                continue;
            }

            $brands[] = $item;
        }

        sort($brands);

        return $brands;
    }
}

if (!function_exists('get_registered_brands')) {
    function get_registered_brands(): array
    {
        $slugs = get_brand_directories();
        $brands = [];

        foreach ($slugs as $slug) {
            $config = load_brand_config($slug);

            if ($config === []) {
                continue;
            }

            $brands[] = [
                'slug' => $slug,
                'name' => (string) ($config['name'] ?? ucfirst(str_replace('-', ' ', $slug))),
                'tagline' => (string) ($config['tagline'] ?? ''),
                'description' => (string) ($config['description'] ?? ''),
                'template' => (string) ($config['template'] ?? 'core-clean'),
                'logo' => (string) ($config['logo'] ?? ''),
                'base_url' => (string) ($config['base_url'] ?? ''),
                'article_count' => count(load_articles_for_brand($slug)),
            ];
        }

        usort($brands, static function (array $a, array $b): int {
            return strcasecmp($a['name'], $b['name']);
        });

        return $brands;
    }
}