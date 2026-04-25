<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
fp_require_login();

$seoFile = dirname(__DIR__) . '/config/seo.json';

// Ensure config directory exists
$configDir = dirname($seoFile);
if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

// Load existing data
$seo = [];
if (file_exists($seoFile)) {
    $seo = json_decode(file_get_contents($seoFile), true) ?? [];
}

// Get POST data
$pageKey = trim($_POST['page_key'] ?? 'home');
$title   = trim($_POST['title'] ?? '');
$desc    = trim($_POST['description'] ?? '');

// Save
$seo[$pageKey] = [
    'title' => $title,
    'description' => $desc,
];

// Write file
file_put_contents($seoFile, json_encode($seo, JSON_PRETTY_PRINT));

// Redirect back
header('Location: /hub/seo.php?saved=1');
exit;