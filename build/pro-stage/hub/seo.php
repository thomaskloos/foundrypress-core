<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$proSeoFile = __DIR__ . '/../pro/includes/seo.php';

if (is_file($proSeoFile)) {
    require_once $proSeoFile;
}

if (!function_exists('fp_pro_is_available') || !fp_pro_is_available()) {
    http_response_code(404);
    exit('Pro SEO module not installed.');
}

$brandSlug = $_GET['brand'] ?? '';
$brandSlug = is_string($brandSlug) ? trim($brandSlug) : '';

if ($brandSlug === '') {
    exit('Missing brand.');
}

if (!function_exists('load_brand_config')) {
    exit('Brand config loader not available.');
}

$brandConfig = load_brand_config($brandSlug);
if (!$brandConfig) {
    exit('Invalid brand.');
}

$seo = fp_pro_load_seo($brandSlug, $brandConfig);
$message = '';
$error = '';

$pageKeys = ['home', 'about', 'contact', 'privacy', 'terms'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seo = [
        'site_title' => trim((string)($_POST['site_title'] ?? '')),
        'title_separator' => trim((string)($_POST['title_separator'] ?? ' | ')),
        'default_meta_description' => trim((string)($_POST['default_meta_description'] ?? '')),
        'default_og_image' => trim((string)($_POST['default_og_image'] ?? '')),
        'default_twitter_card' => trim((string)($_POST['default_twitter_card'] ?? 'summary_large_image')),
        'robots' => trim((string)($_POST['robots'] ?? 'index,follow')),
        'canonical_base' => rtrim(trim((string)($_POST['canonical_base'] ?? '')), '/'),
        'pages' => [],
    ];

    foreach ($pageKeys as $pageKey) {
        $seo['pages'][$pageKey] = [
            'title' => trim((string)($_POST['pages'][$pageKey]['title'] ?? '')),
            'description' => trim((string)($_POST['pages'][$pageKey]['description'] ?? '')),
            'canonical' => trim((string)($_POST['pages'][$pageKey]['canonical'] ?? '')),
            'robots' => trim((string)($_POST['pages'][$pageKey]['robots'] ?? '')),
            'og_image' => trim((string)($_POST['pages'][$pageKey]['og_image'] ?? '')),
        ];
    }

    if (fp_pro_save_seo($brandSlug, $seo)) {
        $message = 'SEO settings saved.';
    } else {
        $error = 'Could not save SEO settings.';
    }
}

function hseo(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Manager | <?= hseo((string)($brandConfig['name'] ?? $brandSlug)) ?></title>
    <link rel="stylesheet" href="/assets/css/site.css">
    <style>
        .seo-wrap { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .seo-card { background: #fff; border: 1px solid #ddd6c8; border-radius: 18px; padding: 24px; margin-bottom: 24px; }
        .seo-grid { display: grid; gap: 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .seo-field { display: grid; gap: 8px; }
        .seo-field label { font-weight: 700; }
        .seo-field input, .seo-field textarea, .seo-field select {
            width: 100%; padding: 12px 14px; border: 1px solid #ddd6c8; border-radius: 12px; font: inherit;
        }
        .seo-field textarea { min-height: 110px; resize: vertical; }
        .seo-actions { margin-top: 20px; }
        .seo-message, .seo-error { padding: 14px 16px; border-radius: 12px; margin-bottom: 20px; }
        .seo-message { background: #edf7ed; color: #1f5c2f; }
        .seo-error { background: #fbecec; color: #8a1f1f; }
        @media (max-width: 800px) {
            .seo-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="seo-wrap">
    <div class="seo-card">
        <h1>SEO Manager</h1>
        <p>Brand: <strong><?= hseo((string)($brandConfig['name'] ?? $brandSlug)) ?></strong></p>
        <?php if ($message !== ''): ?>
            <div class="seo-message"><?= hseo($message) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="seo-error"><?= hseo($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/hub/seo-save.php">
            <div class="seo-card">
                <h2>Defaults</h2>
                <div class="seo-grid">
                    <div class="seo-field">
                        <label for="site_title">Site Title</label>
                        <input id="site_title" name="site_title" value="<?= hseo((string)($seo['site_title'] ?? '')) ?>">
                    </div>

                    <div class="seo-field">
                        <label for="title_separator">Title Separator</label>
                        <input id="title_separator" name="title_separator" value="<?= hseo((string)($seo['title_separator'] ?? ' | ')) ?>">
                    </div>

                    <div class="seo-field">
                        <label for="default_og_image">Default OG Image URL</label>
                        <input id="default_og_image" name="default_og_image" value="<?= hseo((string)($seo['default_og_image'] ?? '')) ?>">
                    </div>

                    <div class="seo-field">
                        <label for="canonical_base">Canonical Base URL</label>
                        <input id="canonical_base" name="canonical_base" value="<?= hseo((string)($seo['canonical_base'] ?? '')) ?>">
                    </div>

                    <div class="seo-field">
                        <label for="default_twitter_card">Twitter Card</label>
                        <select id="default_twitter_card" name="default_twitter_card">
                            <?php $twitterCard = (string)($seo['default_twitter_card'] ?? 'summary_large_image'); ?>
                            <option value="summary"<?= $twitterCard === 'summary' ? ' selected' : '' ?>>summary</option>
                            <option value="summary_large_image"<?= $twitterCard === 'summary_large_image' ? ' selected' : '' ?>>summary_large_image</option>
                        </select>
                    </div>

                    <div class="seo-field">
                        <label for="robots">Default Robots</label>
                        <input id="robots" name="robots" value="<?= hseo((string)($seo['robots'] ?? 'index,follow')) ?>">
                    </div>
                </div>

                <div class="seo-field" style="margin-top:16px;">
                    <label for="default_meta_description">Default Meta Description</label>
                    <textarea id="default_meta_description" name="default_meta_description"><?= hseo((string)($seo['default_meta_description'] ?? '')) ?></textarea>
                </div>
            </div>

            <?php foreach ($pageKeys as $pageKey): ?>
                <?php $page = $seo['pages'][$pageKey] ?? []; ?>
                <div class="seo-card">
                    <h2><?= ucfirst($pageKey) ?> Page</h2>
                    <div class="seo-grid">
                        <div class="seo-field">
                            <label for="pages_<?= hseo($pageKey) ?>_title">Title</label>
                            <input id="pages_<?= hseo($pageKey) ?>_title" name="pages[<?= hseo($pageKey) ?>][title]" value="<?= hseo((string)($page['title'] ?? '')) ?>">
                        </div>

                        <div class="seo-field">
                            <label for="pages_<?= hseo($pageKey) ?>_canonical">Canonical</label>
                            <input id="pages_<?= hseo($pageKey) ?>_canonical" name="pages[<?= hseo($pageKey) ?>][canonical]" value="<?= hseo((string)($page['canonical'] ?? '')) ?>">
                        </div>

                        <div class="seo-field">
                            <label for="pages_<?= hseo($pageKey) ?>_robots">Robots</label>
                            <input id="pages_<?= hseo($pageKey) ?>_robots" name="pages[<?= hseo($pageKey) ?>][robots]" value="<?= hseo((string)($page['robots'] ?? '')) ?>">
                        </div>

                        <div class="seo-field">
                            <label for="pages_<?= hseo($pageKey) ?>_og_image">OG Image URL</label>
                            <input id="pages_<?= hseo($pageKey) ?>_og_image" name="pages[<?= hseo($pageKey) ?>][og_image]" value="<?= hseo((string)($page['og_image'] ?? '')) ?>">
                        </div>
                    </div>

                    <div class="seo-field" style="margin-top:16px;">
                        <label for="pages_<?= hseo($pageKey) ?>_description">Description</label>
                        <textarea id="pages_<?= hseo($pageKey) ?>_description" name="pages[<?= hseo($pageKey) ?>][description]"><?= hseo((string)($page['description'] ?? '')) ?></textarea>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="seo-actions">
                <button type="submit" class="button button-primary">Save SEO Settings</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>