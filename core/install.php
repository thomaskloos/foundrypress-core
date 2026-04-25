<?php
declare(strict_types=1);

session_start();

/*
|--------------------------------------------------------------------------
| FoundryPress Installer
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
    $siteName      = var_export($data['site_name'], true);
    $brandSlug     = var_export($data['brand_slug'], true);
    $adminUsername = var_export($data['admin_username'], true);
    $passwordHash  = var_export($data['admin_password_hash'], true);
    $installedAt   = var_export($data['installed_at'], true);

    $php = <<<PHP
<?php
declare(strict_types=1);

define('FP_INSTALLED', true);
define('FP_SITE_NAME', {$siteName});
define('FP_BRAND_NAME', {$brandSlug});
define('FP_ADMIN_USERNAME', {$adminUsername});
define('FP_ADMIN_PASSWORD_HASH', {$passwordHash});
define('FP_INSTALLED_AT', {$installedAt});

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

    $brandConfig = [
        'name' => $brandName,
        'slug' => $brandSlug,
        'tagline' => 'Your brand tagline',
        'description' => 'Describe your business here.',

        'homepage' => [
            'headline' => 'Welcome to ' . $siteName,
            'intro' => 'Your FoundryPress site is live. Update this homepage content in Hub → Site Settings.',
            'primary_cta_text' => 'Read Articles',
            'primary_cta_url' => '/articles/' . $brandSlug . '/',
        ],

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

    return $json !== false &&
        file_put_contents(
            $dataDir . '/articles.json',
            $json,
            LOCK_EX
        ) !== false;
}

$rootPath = __DIR__;
$configDir = $rootPath . '/config';
$siteConfigFile = $configDir . '/site.php';
$licenseFile = $rootPath . '/license.json';

$alreadyInstalled = is_file($siteConfigFile);

$errors = [];
$success = false;

$defaults = [
    'site_name' => 'FoundryPress Site',
    'brand_name' => 'My Brand',
    'base_url' => detect_base_url(),
    'admin_username' => 'admin',
    'license_key' => ''
];

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

        write_php_config([
            'site_name' => $siteName,
            'brand_slug' => $brandSlug,
            'base_url' => $baseUrl,
            'admin_username' => $adminUsername,
            'admin_password_hash' => password_hash($adminPassword, PASSWORD_DEFAULT),
            'installed_at' => $installedAt
        ], $siteConfigFile);

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
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Install FoundryPress</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root {
    --bg: #f6f4ef;
    --panel: #ffffff;
    --text: #18212f;
    --muted: #667085;
    --border: #d8dee8;
    --accent: #2f5d62;
    --accent-dark: #24484c;
    --danger: #b42318;
    --success: #027a48;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    background:
        radial-gradient(circle at top left, rgba(47, 93, 98, 0.12), transparent 34rem),
        var(--bg);
    color: var(--text);
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
    line-height: 1.5;
}

.container {
    max-width: 760px;
    margin: 56px auto;
    padding: 0 20px;
}

.card,
.container {
    background: transparent;
}

.container > h1,
.container > form,
.container > .success,
.container > .error {
    max-width: none;
}

.container {
    background: var(--panel);
    border: 1px solid rgba(16, 24, 40, 0.08);
    border-radius: 24px;
    padding: 42px;
    box-shadow: 0 24px 70px rgba(16, 24, 40, 0.08);
}

h1 {
    margin: 0 0 10px;
    font-size: clamp(2rem, 4vw, 3rem);
    letter-spacing: -0.04em;
    line-height: 1;
}

h1::after {
    content: "Create your site config, first brand, starter article, and Hub login.";
    display: block;
    max-width: 560px;
    margin-top: 14px;
    color: var(--muted);
    font-size: 1rem;
    font-weight: 400;
    letter-spacing: 0;
    line-height: 1.6;
}

form {
    margin-top: 28px;
    display: grid;
    gap: 16px;
}

input {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 14px 16px;
    background: #fff;
    color: var(--text);
    font: inherit;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 4px rgba(47, 93, 98, 0.12);
}

button {
    justify-self: start;
    border: 0;
    border-radius: 999px;
    background: #2563eb;
    color: #fff;
    padding: 13px 22px;
    font: inherit;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s ease, transform 0.15s ease;
}

button:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
}

.error,
.success {
    margin-top: 18px;
    border-radius: 14px;
    padding: 14px 16px;
}

.error {
    background: #fff3f0;
    border: 1px solid #fecdca;
    color: var(--danger);
}

.success {
    background: #ecfdf3;
    border: 1px solid #abefc6;
    color: var(--success);
}

.success a {
    color: var(--accent-dark);
    font-weight: 700;
}

@media (min-width: 720px) {
    form {
        grid-template-columns: 1fr 1fr;
    }

    input[name="base_url"],
    input[name="license_key"],
    input[name="admin_password"],
    button {
        grid-column: 1 / -1;
    }
}

@media (max-width: 640px) {
    .container {
        margin: 20px auto;
        padding: 28px 20px;
        border-radius: 18px;
    }
}
</style>
</head>
<body>

<div class="container">
    <h1>Install FoundryPress</h1>

    <?php if ($alreadyInstalled): ?>
        <div class="success">FoundryPress is already installed.</div>

    <?php elseif ($success): ?>
        <div class="success">
            Installation complete.
            <br><br>
            <a href="/hub/login.php">Go to Hub Login</a>
        </div>

    <?php else: ?>

        <?php foreach ($errors as $error): ?>
            <div class="error"><?= h($error) ?></div>
        <?php endforeach; ?>

        <form method="post">
            <input name="site_name" placeholder="Site Name" value="<?= h($defaults['site_name']) ?>">
            <input name="brand_name" placeholder="Brand Name" value="<?= h($defaults['brand_name']) ?>">
            <input name="base_url" placeholder="Base URL" value="<?= h($defaults['base_url']) ?>">
            <input name="admin_username" placeholder="Admin Username" value="<?= h($defaults['admin_username']) ?>">
            <input name="license_key" placeholder="License Key">
            <input name="admin_password" type="password" placeholder="Password">
            <button type="submit">Install</button>
        </form>

    <?php endif; ?>
</div>

</body>
</html>