<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

/*
|--------------------------------------------------------------------------
| Core Site Settings Compatibility Bridge
|--------------------------------------------------------------------------
| Prefer /config/site.php after install.
|--------------------------------------------------------------------------
*/

$siteName = defined('FP_SITE_NAME')
    ? (string) FP_SITE_NAME
    : 'FoundryPress';

$baseUrl = defined('FP_BASE_URL')
    ? rtrim((string) FP_BASE_URL, '/')
    : (defined('FP_DETECTED_BASE_URL')
        ? rtrim((string) FP_DETECTED_BASE_URL, '/')
        : '');

$articlesBaseUrl = $baseUrl . '/articles';
$hubBase = $baseUrl . '/hub';

$mainSiteUrl = 'https://foundrypressapp.com';
$docsUrl = 'https://foundrypressapp.com/docs';
$licenseApiBaseUrl = 'https://foundrypressapp.com/license-api';

$foundryPressGptUrl = 'https://chatgpt.com/g/g-69dd0c575d84819189deaff4eb4dd710-foundrypress-brand-builder';

$defaultBrand = defined('FP_BRAND_NAME')
    ? (string) FP_BRAND_NAME
    : '';