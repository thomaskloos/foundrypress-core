<?php
declare(strict_types=1);

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$navBrand = $defaultBrand ?? 'demo';

if (session_status() !== PHP_SESSION_ACTIVE && !headers_sent()) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Check actual hub session directly
|--------------------------------------------------------------------------
*/

$isHubLoggedIn = !empty($_SESSION['fp_hub_auth'])
    || !empty($_SESSION['foundrypress_hub_auth'])
    || !empty($_SESSION['hub_auth']);

$hubUrl = $isHubLoggedIn ? '/hub/' : '/hub/login.php';
$hubLabel = $isHubLoggedIn ? 'Hub' : 'Hub Login';
?>
<header class="site-header">
    <div class="site-header__inner">
        <a class="site-logo" href="/">
            <img src="/assets/img/logo.png" alt="FoundryPress" class="site-logo__img">
        </a>

        <nav class="site-nav" aria-label="Main Navigation">
            <a class="site-nav__link" href="/">Home</a>
            <a class="site-nav__link" href="<?= h(brand_articles_url($navBrand)) ?>">Articles</a>
            <a class="site-nav__link" href="<?= h($hubUrl) ?>">
                <?= h($hubLabel) ?>
            </a>
        </nav>
    </div>
</header>