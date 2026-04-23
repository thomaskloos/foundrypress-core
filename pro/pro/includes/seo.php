<?php
declare(strict_types=1);

if (!defined('FP_PRO_VERSION')) {
    define('FP_PRO_VERSION', '1.0.0');
}

function fp_pro_is_available(): bool
{
    return defined('FP_PRO_VERSION');
}
if (!function_exists('fp_has_pro')) {
    function fp_has_pro(): bool
    {
        return fp_pro_is_available();
    }
}

if (!function_exists('fp_pro_seo_file')) {
    function fp_pro_seo_file(string $brandSlug): string
    {
        return dirname(__DIR__, 2) . '/brands/' . $brandSlug . '/_data/seo.json';
    }
}

if (!function_exists('fp_pro_ensure_directory')) {
    function fp_pro_ensure_directory(string $dir): bool
    {
        if (is_dir($dir)) {
            return true;
        }

        return mkdir($dir, 0775, true) || is_dir($dir);
    }
}

if (!function_exists('fp_pro_default_seo_data')) {
    function fp_pro_default_seo_data(array $brandConfig = []): array
    {
        $siteUrl = (string) ($brandConfig['site_url'] ?? '');
        $brandName = (string) ($brandConfig['name'] ?? 'Brand');

        return [
            'site_title' => $brandName,
            'title_separator' => ' | ',
            'default_meta_description' => '',
            'default_og_image' => '',
            'default_twitter_card' => 'summary_large_image',
            'robots' => 'index,follow',
            'canonical_base' => rtrim($siteUrl, '/'),
            'pages' => [
                'home' => [
                    'title' => '',
                    'description' => '',
                    'canonical' => '',
                    'robots' => '',
                    'og_image' => '',
                ],
                'about' => [
                    'title' => '',
                    'description' => '',
                    'canonical' => '',
                    'robots' => '',
                    'og_image' => '',
                ],
                'contact' => [
                    'title' => '',
                    'description' => '',
                    'canonical' => '',
                    'robots' => '',
                    'og_image' => '',
                ],
                'privacy' => [
                    'title' => '',
                    'description' => '',
                    'canonical' => '',
                    'robots' => '',
                    'og_image' => '',
                ],
                'terms' => [
                    'title' => '',
                    'description' => '',
                    'canonical' => '',
                    'robots' => '',
                    'og_image' => '',
                ],
            ],
        ];
    }
}

if (!function_exists('fp_pro_array_merge_recursive_distinct')) {
    function fp_pro_array_merge_recursive_distinct(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = fp_pro_array_merge_recursive_distinct($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }
}

if (!function_exists('fp_pro_load_seo')) {
    function fp_pro_load_seo(string $brandSlug, array $brandConfig = []): array
    {
        $defaults = fp_pro_default_seo_data($brandConfig);
        $file = fp_pro_seo_file($brandSlug);

        if (!is_file($file)) {
            return $defaults;
        }

        $json = file_get_contents($file);
        if ($json === false || trim($json) === '') {
            return $defaults;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return $defaults;
        }

        return fp_pro_array_merge_recursive_distinct($defaults, $data);
    }
}

if (!function_exists('fp_pro_save_seo')) {
    function fp_pro_save_seo(string $brandSlug, array $seoData): bool
    {
        $file = fp_pro_seo_file($brandSlug);
        $dir = dirname($file);

        if (!fp_pro_ensure_directory($dir)) {
            return false;
        }

        $json = json_encode($seoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return false;
        }

        return file_put_contents($file, $json . PHP_EOL) !== false;
    }
}

if (!function_exists('fp_pro_get_page_seo')) {
    function fp_pro_get_page_seo(string $brandSlug, string $pageKey, array $brandConfig = [], array $fallback = []): array
    {
        $seo = fp_pro_load_seo($brandSlug, $brandConfig);
        $page = $seo['pages'][$pageKey] ?? [];

        $title = trim((string) ($page['title'] ?? ''));
        $desc = trim((string) ($page['description'] ?? ''));
        $canonical = trim((string) ($page['canonical'] ?? ''));
        $robots = trim((string) ($page['robots'] ?? ''));
        $ogImage = trim((string) ($page['og_image'] ?? ''));

        $siteTitle = trim((string) ($seo['site_title'] ?? ''));
        $titleSeparator = (string) ($seo['title_separator'] ?? ' | ');

        $finalTitle = $title !== ''
            ? $title
            : (string) ($fallback['title'] ?? $siteTitle);

        if ($finalTitle !== '' && $siteTitle !== '' && $title !== '' && $finalTitle !== $siteTitle) {
            $finalTitle .= $titleSeparator . $siteTitle;
        }

        $finalDescription = $desc !== ''
            ? $desc
            : (string) ($fallback['description'] ?? ($seo['default_meta_description'] ?? ''));

        $finalCanonical = $canonical !== ''
            ? $canonical
            : (string) ($fallback['canonical'] ?? '');

        if ($finalCanonical === '' && !empty($seo['canonical_base']) && !empty($fallback['path'])) {
            $finalCanonical = rtrim((string) $seo['canonical_base'], '/') . '/' . ltrim((string) $fallback['path'], '/');
        }

        $finalRobots = $robots !== ''
            ? $robots
            : (string) ($fallback['robots'] ?? ($seo['robots'] ?? 'index,follow'));

        $finalOgImage = $ogImage !== ''
            ? $ogImage
            : (string) ($fallback['og_image'] ?? ($seo['default_og_image'] ?? ''));

        return [
            'title' => $finalTitle,
            'description' => $finalDescription,
            'canonical' => $finalCanonical,
            'robots' => $finalRobots,
            'og_image' => $finalOgImage,
            'twitter_card' => (string) ($seo['default_twitter_card'] ?? 'summary_large_image'),
            'site_title' => $siteTitle,
        ];
    }
}

if (!function_exists('fp_pro_render_seo_tags')) {
    function fp_pro_render_seo_tags(array $seo): void
    {
        $title = trim((string) ($seo['title'] ?? ''));
        $description = trim((string) ($seo['description'] ?? ''));
        $canonical = trim((string) ($seo['canonical'] ?? ''));
        $robots = trim((string) ($seo['robots'] ?? ''));
        $ogImage = trim((string) ($seo['og_image'] ?? ''));
        $twitterCard = trim((string) ($seo['twitter_card'] ?? 'summary_large_image'));

        if ($title !== '') {
            echo '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</title>\n";
            echo '<meta property="og:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "\">\n";
            echo '<meta name="twitter:title" content="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        if ($description !== '') {
            echo '<meta name="description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . "\">\n";
            echo '<meta property="og:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . "\">\n";
            echo '<meta name="twitter:description" content="' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        if ($canonical !== '') {
            echo '<link rel="canonical" href="' . htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') . "\">\n";
            echo '<meta property="og:url" content="' . htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        if ($robots !== '') {
            echo '<meta name="robots" content="' . htmlspecialchars($robots, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        if ($ogImage !== '') {
            echo '<meta property="og:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') . "\">\n";
            echo '<meta name="twitter:image" content="' . htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') . "\">\n";
        }

        if ($twitterCard !== '') {
            echo '<meta name="twitter:card" content="' . htmlspecialchars($twitterCard, ENT_QUOTES, 'UTF-8') . "\">\n";
        }
    }
}