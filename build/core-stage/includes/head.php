<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? $siteName;
$pageDesc  = $pageDesc ?? 'FoundryPress site.';
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <meta name="description" content="<?= h($pageDesc) ?>">
    <link rel="canonical" href="<?= h($currentUrl) ?>">
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
    <link rel="icon" type="image/png" sizes="192x192"  href="/assets/icons/android-icon-192x192.png">
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