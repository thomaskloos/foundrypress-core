<?php
declare(strict_types=1);

$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$navBrand = $defaultBrand ?? 'demo';
?>
<header class="site-header">
    <div class="site-header__inner">
           <a class="site-logo" href="/">
           <img src="/assets/img/logo.png" alt="FoundryPress" class="site-logo__img">
           </a>
        <nav class="site-nav" aria-label="Main Navigation">
            <a class="site-nav__link" href="/">Home</a>
            <a class="site-nav__link" href="<?= h(brand_articles_url($navBrand)) ?>">Articles</a>
        </nav>
    </div>
</header>