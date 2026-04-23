<?php
declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Core Brand Identity
    |--------------------------------------------------------------------------
    */
    'name' => 'Demo Brand',
    'slug' => 'demo',
    'tagline' => 'A live FoundryPress demo brand.',
    'description' => 'A demo brand used to test themes, layouts, workflows, and front-end article presentation inside FoundryPress.',

    /*
    |--------------------------------------------------------------------------
    | Domain / Routing (Add Your Domain Name('https://yourdomain.com') in base_url)
    |--------------------------------------------------------------------------
    */
    'base_url' => '',
    'article_path' => '/articles/',
    'brand_path' => '/brands/demo/',

    /*
    |--------------------------------------------------------------------------
    | Brand Assets
    |--------------------------------------------------------------------------
    */
    'logo' => '/brands/demo/assets/logo.png',
    'og_default' => '/brands/demo/assets/og-default.jpg',

    /*
    |--------------------------------------------------------------------------
    | Brand Identity Styling
    |--------------------------------------------------------------------------
    */
    'colors' => [
        'primary' => '#2F5D62',
        'secondary' => '#F3F6F6',
        'accent' => '#A7C4BC',
        'text' => '#1E1E1E',
    ],

    'typography' => [
        'heading' => 'Inter, sans-serif',
        'body' => 'Inter, sans-serif',
    ],

    'template' => 'core-clean',

    /*
    |--------------------------------------------------------------------------
    | Voice & Tone
    |--------------------------------------------------------------------------
    */
    'tone' => 'calm, structured, encouraging, honest',
    'voice_notes' => 'Avoid hype. Focus on clarity, simplicity, and grounded guidance.',

    /*
    |--------------------------------------------------------------------------
    | Content Defaults
    |--------------------------------------------------------------------------
    */
    'default_author' => 'FoundryPress',
    'default_category' => 'General',
    'default_status' => 'Draft',

    /*
    |--------------------------------------------------------------------------
    | SEO Defaults
    |--------------------------------------------------------------------------
    */
    'seo' => [
        'meta_title_suffix' => ' | FoundryPress Demo',
        'meta_description' => 'A FoundryPress demo brand for testing themes, layouts, and structured publishing workflows.',
        'og_image' => '/brands/demo/assets/og-default.jpg',
    ],

    /*
    |--------------------------------------------------------------------------
    | CTA Defaults
    |--------------------------------------------------------------------------
    */
    'cta' => [
        'text' => 'Explore Articles',
        'url' => '/articles/demo/',
    ],

];