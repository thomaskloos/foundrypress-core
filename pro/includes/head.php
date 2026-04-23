<?php
declare(strict_types=1);

$pageTitle  = $pageTitle ?? $siteName;
$pageDesc   = $pageDesc ?? 'FoundryPress site.';
$currentUrl = $currentUrl ?? $baseUrl;

$themeStylesheet = '';
$themeSlug = '';

if (isset($brandConfig) && is_array($brandConfig)) {
    $savedTheme = trim((string) ($brandConfig['template'] ?? 'core-clean'));
    $previewTheme = trim((string) ($_GET['preview_theme'] ?? ''));

    $themeSlug = $savedTheme;

    if ($previewTheme !== '') {
        $previewFile = dirname(__DIR__) . '/themes/' . $previewTheme . '/theme.css';

        if (is_file($previewFile)) {
            $themeSlug = $previewTheme;
        }
    }

    if ($themeSlug !== '') {
        $themeFile = dirname(__DIR__) . '/themes/' . $themeSlug . '/theme.css';

        if (is_file($themeFile)) {
            $themeStylesheet = '/themes/' . $themeSlug . '/theme.css';
        }
    }
}

/*
|--------------------------------------------------------------------------
| Optional Pro SEO Module
|--------------------------------------------------------------------------
|
| Load the Pro SEO module only if it exists. This keeps core safe and
| allows the Pro archive to extend SEO without breaking standard installs.
|
*/
$proSeoFile = dirname(__DIR__) . '/pro/includes/seo.php';

if (is_file($proSeoFile)) {
    require_once $proSeoFile;
}

$finalTitle = $pageTitle;
$finalDesc = $pageDesc;
$finalCanonical = $currentUrl;
$finalRobots = 'index,follow';
$finalOgImage = '';
$finalTwitterCard = 'summary_large_image';

/*
|--------------------------------------------------------------------------
| Resolve SEO Brand Slug
|--------------------------------------------------------------------------
|
| This build does not always define $brandSlug. So we fall back in this order:
| 1. $brandSlug
| 2. $brandConfig['slug']
| 3. $defaultBrand
| 4. 'demo'
|
*/
$seoBrandSlug = '';

if (!empty($brandSlug)) {
    $seoBrandSlug = (string) $brandSlug;
} elseif (!empty($brandConfig['slug'])) {
    $seoBrandSlug = (string) $brandConfig['slug'];
} elseif (!empty($defaultBrand)) {
    $seoBrandSlug = (string) $defaultBrand;
} else {
    $seoBrandSlug = 'demo';
}

if (function_exists('fp_pro_get_page_seo')) {
    $seoTags = fp_pro_get_page_seo(
        $seoBrandSlug,
        (string) ($pageKey ?? 'home'),
        $brandConfig ?? [],
        [
            'title' => (string) $pageTitle,
            'description' => (string) $pageDesc,
            'canonical' => (string) $currentUrl,
            'robots' => 'index,follow',
            'og_image' => (string) ($ogImage ?? ''),
            'path' => (string) ($pagePath ?? ''),
        ]
    );

    $finalTitle = (string) ($seoTags['title'] ?? $finalTitle);
    $finalDesc = (string) ($seoTags['description'] ?? $finalDesc);
    $finalCanonical = (string) ($seoTags['canonical'] ?? $finalCanonical);
    $finalRobots = (string) ($seoTags['robots'] ?? $finalRobots);
    $finalOgImage = (string) ($seoTags['og_image'] ?? $finalOgImage);
    $finalTwitterCard = (string) ($seoTags['twitter_card'] ?? $finalTwitterCard);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= h($finalTitle) ?></title>
    <meta name="description" content="<?= h($finalDesc) ?>">
    <link rel="canonical" href="<?= h($finalCanonical) ?>">
    <meta name="robots" content="<?= h($finalRobots) ?>">

    <meta property="og:title" content="<?= h($finalTitle) ?>">
    <meta property="og:description" content="<?= h($finalDesc) ?>">
    <meta property="og:url" content="<?= h($finalCanonical) ?>">
    <meta property="og:type" content="website">

    <meta name="twitter:card" content="<?= h($finalTwitterCard) ?>">
    <meta name="twitter:title" content="<?= h($finalTitle) ?>">
    <meta name="twitter:description" content="<?= h($finalDesc) ?>">

    <?php if ($finalOgImage !== ''): ?>
        <meta property="og:image" content="<?= h($finalOgImage) ?>">
        <meta name="twitter:image" content="<?= h($finalOgImage) ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="<?= h(asset_url('css/site.css')) ?>">
    <?php if ($themeStylesheet !== ''): ?>
        <link rel="stylesheet" href="<?= h($themeStylesheet) ?>">
    <?php endif; ?>

    <link rel="apple-touch-icon" sizes="57x57" href="/assets/icons/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/assets/icons/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/assets/icons/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/assets/icons/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/assets/icons/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/assets/icons/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/assets/icons/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/assets/icons/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/assets/icons/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/assets/icons/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
    <link rel="manifest" href="/assets/icons/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/assets/icons/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>
<body>
<div class="site-shell">