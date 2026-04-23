<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Core Site Settings
|--------------------------------------------------------------------------
*/

$siteName = 'FoundryPress Demo';
$baseUrl = 'https://app.foundrypressapp.com';

/*
|--------------------------------------------------------------------------
| System URLs
|--------------------------------------------------------------------------
*/

$articlesBaseUrl = $baseUrl . '/articles';
$hubBase = $baseUrl . '/hub';

$mainSiteUrl = 'https://foundrypressapp.com';
$docsUrl = 'https://foundrypressapp.com/docs';
$licenseApiBaseUrl = 'https://foundrypressapp.com/license-api';

$foundryPressGptUrl = 'https://chatgpt.com/g/g-69dd0c575d84819189deaff4eb4dd710-foundrypress-brand-builder';

/*
|--------------------------------------------------------------------------
| Demo / Fallback Brand
|--------------------------------------------------------------------------
|
| Used only when a brand is not explicitly provided.
| This keeps the demo environment working without hardcoding brand slugs
| throughout the templates.
|
*/

$defaultBrand = 'demo';