<?php
declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| FoundryPress Installer
|--------------------------------------------------------------------------
| Creates:
| /config/site.php
| /license.json
| /brands/{brand}/brand.php
| /brands/{brand}/_data/articles.json
| /brands/{brand}/assets/
|--------------------------------------------------------------------------
*/

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function detect_scheme(): string
{
    return (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443')
    ) ? 'https' : 'http';
}

function detect_host(): string
{
    return $_SERVER['HTTP_HOST'] ?? 'localhost';
}

function detect_base_url(): string
{
    return detect_scheme() . '://' . detect_host();
}

function ensure_dir(string $path): bool
{
    if (is_dir($path)) {
        return true;
    }

    return mkdir($path, 0755, true);
}

function fp_slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value !== '' ? $value : 'default';
}

function write_php_config(array $data, string $targetFile): bool
{
    $php = <<<PHP
<?php
declare(strict_types=1);

define('FP_INSTALLED', true);
define('FP_SITE_NAME', {$data['site_name']});
define('FP_BRAND_NAME', {$data['brand_slug']});
define('FP_BASE_URL', {$data['base_url']});
define('FP_ADMIN_USERNAME', {$data['admin_username']});
define('FP_ADMIN_PASSWORD_HASH', {$data['admin_password_hash']});
define('FP_INSTALLED_AT', {$data['installed_at']});

PHP;

    return file_put_contents($targetFile, $php, LOCK_EX) !== false;
}

function write_license_json(array $data, string $targetFile): bool
{
    $payload = [
        'license_key' => $data['license_key'],
        'domain' => parse_url($data['base_url'], PHP_URL_HOST) ?: '',
        'status' => $data['license_key'] !== '' ? 'active' : 'unlicensed',
        'installed_at' => $data['installed_at'],
    ];

    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    return $json !== false && file_put_contents($targetFile, $json, LOCK_EX) !== false;
}

function write_initial_brand(
    string $rootPath,
    string $siteName,
    string $brandName,
    string $brandSlug,
    string $installedAt
): bool {
    $brandDir = $rootPath . '/brands/' . $brandSlug;
    $dataDir = $brandDir . '/_data';
    $assetsDir = $brandDir . '/assets';

    if (
        !ensure_dir($brandDir)
        || !ensure_dir($dataDir)
        || !ensure_dir($assetsDir)
    ) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | brand.php
    |--------------------------------------------------------------------------
    */

    $brandConfig = [
        'name' => $brandName,
        'slug' => $brandSlug,
        'tagline' => 'Your brand tagline',
        'description' => 'Describe your business, audience, or content focus here.',

        // NEW homepage config
        'homepage' => [
            'headline' => 'Welcome to ' . $siteName,
            'intro' => 'Your FoundryPress site is live. Update this homepage content in Hub → Site Settings.',
            'primary_cta_text' => 'Read Articles',
            'primary_cta_url' => '/articles/' . $brandSlug . '/',
        ],

        'base_url' => '',
        'article_path' => '/articles/',
        'brand_path' => '/brands/' . $brandSlug . '/',
        'logo' => '',
        'template' => 'core-clean',
        'default_author' => 'FoundryPress',
        'default_category' => 'General',
        'default_status' => 'draft',
    ];

    $brandPhp = <<<PHP
<?php
declare(strict_types=1);

return %s;

PHP;

    if (
        file_put_contents(
            $brandDir . '/brand.php',
            sprintf($brandPhp, var_export($brandConfig, true)),
            LOCK_EX
        ) === false
    ) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Starter article
    |--------------------------------------------------------------------------
    */

    $starterArticle = [
        [
            'id' => 'welcome',
            'brand' => $brandSlug,
            'title' => 'Welcome to ' . $siteName,
            'slug' => 'welcome',
            'status' => 'published',
            'featured' => true,
            'category' => 'Getting Started',
            'summary' => 'Your first article is ready.',
            'body' => '<p>Your FoundryPress installation is complete.</p>',
            'author' => 'FoundryPress',
            'publish_date' => date('Y-m-d'),
            'updated_at' => $installedAt,
        ]
    ];

    $json = json_encode(
        $starterArticle,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    );

    if (
        $json === false ||
        file_put_contents(
            $dataDir . '/articles.json',
            $json,
            LOCK_EX
        ) === false
    ) {
        return false;
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| Main Install Logic
|--------------------------------------------------------------------------
*/

$rootPath = __DIR__;
$configDir = $rootPath . '/config';
$siteConfigFile = $configDir . '/site.php';
$licenseFile = $rootPath . '/license.json';

$alreadyInstalled = is_file($siteConfigFile);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyInstalled) {
    $siteName = trim((string) ($_POST['site_name'] ?? ''));
    $brandName = trim((string) ($_POST['brand_name'] ?? ''));
    $baseUrl = rtrim(trim((string) ($_POST['base_url'] ?? '')), '/');
    $adminUsername = trim((string) ($_POST['admin_username'] ?? ''));
    $adminPassword = (string) ($_POST['admin_password'] ?? '');
    $licenseKey = trim((string) ($_POST['license_key'] ?? ''));

    $brandSlug = fp_slugify($brandName);

    if ($siteName === '') $errors[] = 'Site name required.';
    if ($brandName === '') $errors[] = 'Brand name required.';
    if ($baseUrl === '') $errors[] = 'Base URL required.';
    if ($adminUsername === '') $errors[] = 'Admin username required.';
    if ($adminPassword === '') $errors[] = 'Admin password required.';

    if (empty($errors)) {
        ensure_dir($configDir);

        $installedAt = date(DATE_ATOM);

        $configWritten = write_php_config([
            'site_name' => var_export($siteName, true),
            'brand_slug' => var_export($brandSlug, true),
            'base_url' => var_export($baseUrl, true),
            'admin_username' => var_export($adminUsername, true),
            'admin_password_hash' => var_export(password_hash($adminPassword, PASSWORD_DEFAULT), true),
            'installed_at' => var_export($installedAt, true),
        ], $siteConfigFile);

        if (!$configWritten) {
            $errors[] = 'Could not write site config.';
        }

        write_license_json([
            'license_key' => $licenseKey,
            'base_url' => $baseUrl,
            'installed_at' => $installedAt
        ], $licenseFile);

        write_initial_brand(
            $rootPath,
            $siteName,
            $brandName,
            $brandSlug,
            $installedAt
        );

        $success = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Install FoundryPress</title>
</head>
<body>

<?php if ($alreadyInstalled): ?>
    <h1>FoundryPress Already Installed</h1>
    <p>Delete install.php after setup.</p>

<?php elseif ($success): ?>
    <h1>Installation Complete</h1>
    <p>Your site is ready.</p>
    <a href="/hub/login.php">Login to Hub</a>

<?php else: ?>
    <h1>Install FoundryPress</h1>

    <?php foreach ($errors as $error): ?>
        <p><?= h($error) ?></p>
    <?php endforeach; ?>

    <form method="post">
        <input name="site_name" placeholder="Site Name" required>
        <input name="brand_name" placeholder="Brand Name" required>
        <input name="base_url" value="<?= h(detect_base_url()) ?>" required>
        <input name="admin_username" placeholder="Admin Username" required>
        <input name="license_key" placeholder="License Key">
        <input name="admin_password" type="password" placeholder="Password" required>
        <button type="submit">Install</button>
    </form>
<?php endif; ?>

</body>
</html>