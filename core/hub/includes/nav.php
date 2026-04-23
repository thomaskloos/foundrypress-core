<?php
declare(strict_types=1);

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/hub/', PHP_URL_PATH) ?: '/hub/';
$previewBrand = $defaultBrand ?? 'demo';
?>
<header class="hub-topbar">
    <div class="hub-topbar__inner">
        <div class="hub-brand">
            <a class="site-logo" href="/">
    <img src="/assets/img/logo.png" alt="FoundryPress" class="site-logo__img">
</a>
        </div>

        <nav class="hub-nav" aria-label="Hub Navigation">
            <a class="hub-nav__link <?= is_active_path('/hub/', $currentPath) ? 'is-active' : '' ?>" href="/hub/">Dashboard</a>
            <a class="hub-nav__link <?= is_active_path('/hub/create-brand.php', $currentPath) ? 'is-active' : '' ?>" href="/hub/create-brand.php">Create Brand</a>
            <a class="hub-nav__link <?= is_active_path('/hub/theme-switcher.php', $currentPath) ? 'is-active' : '' ?>" href="/hub/theme-switcher.php">Theme Switcher</a>
            <a class="hub-nav__link <?= is_active_path('/hub/license.php', $currentPath) ? 'is-active' : '' ?>" href="/hub/license.php">License</a>
            <a class="hub-nav__link <?= is_active_path('/hub/prompt-generator.php', $currentPath) ? 'is-active' : '' ?>" href="/hub/prompt-generator.php">Prompt Generator</a>
            <a class="hub-nav__link <?= is_active_path('/hub/prompt-library.php', $currentPath) ? 'is-active' : '' ?>" href="/hub/prompt-library.php">Prompt Library</a>
            <a class="hub-nav__link <?= is_active_path('/hub/gpt-builder.php', $currentPath) ? 'is-active' : '' ?>" href="/hub/gpt-builder.php">GPT Builder</a>
            <a class="hub-nav__link" href="<?= h(brand_articles_url($previewBrand)) ?>">Preview Brand</a>
            <a class="hub-nav__link" href="/">Main Site</a>
        </nav>
    </div>
</header>