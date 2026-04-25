<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

$brand = trim((string) ($_GET['brand'] ?? ''));
$previewTheme = trim((string) ($_GET['preview_theme'] ?? ''));

/*
|--------------------------------------------------------------------------
| If brand is provided → show that brand
|--------------------------------------------------------------------------
*/
if ($brand !== '') {
    $brandConfig = load_brand_config($brand);
    $articles = load_articles_for_brand($brand);

    if ($brandConfig === []) {
        http_response_code(404);
        $pageTitle = 'Brand Not Found • FoundryPress';
        $pageDesc  = 'The requested brand could not be found.';
        $currentUrl = $articlesBaseUrl . '/' . rawurlencode($brand) . '/';

        require dirname(__DIR__) . '/includes/head.php';
        require dirname(__DIR__) . '/includes/nav.php';
        ?>
        <main class="site-main">
            <div class="site-empty">
                The requested brand could not be found.
            </div>
        </main>
        <?php
        require dirname(__DIR__) . '/includes/footer.php';
        exit;
    }

    $pageTitle = ($brandConfig['name'] ?? 'Brand') . ' Articles • FoundryPress';
    $pageDesc  = 'Article library powered by FoundryPress.';
    $currentUrl = $articlesBaseUrl . '/' . rawurlencode($brand) . '/';

    require dirname(__DIR__) . '/includes/head.php';
    require dirname(__DIR__) . '/includes/nav.php';
    ?>

    <main class="site-main">
        <section class="site-hero">
            <span class="site-kicker">Articles</span>
            <h1><?= h((string) ($brandConfig['name'] ?? 'Brand')) ?></h1>
            <p>
                Browse the article library for this brand.
                <?php if ($previewTheme !== ''): ?>
                    <br><strong>Preview theme:</strong> <?= h($previewTheme) ?>
                <?php endif; ?>
            </p>
        </section>

        <?php if ($articles === []): ?>
            <div class="site-empty">No articles found yet.</div>
        <?php else: ?>
            <div class="article-grid">
                <?php foreach ($articles as $article): ?>
                    <?php
                    $slug = (string) ($article['slug'] ?? '');
                    $title = (string) ($article['title'] ?? 'Untitled');
                    $desc = (string) ($article['desc'] ?? '');
                    $url = brand_article_url($brand, $slug);

                    if ($previewTheme !== '') {
                        $url .= '?preview_theme=' . rawurlencode($previewTheme);
                    }
                    ?>
                    <article class="article-card">
                        <h2><?= h($title) ?></h2>
                        <p><?= h($desc) ?></p>
                        <a class="article-card__link" href="<?= h($url) ?>">Read Article</a>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php
    require dirname(__DIR__) . '/includes/footer.php';
    exit;
}

/*
|--------------------------------------------------------------------------
| No brand provided → handle selector / redirect
|--------------------------------------------------------------------------
*/

$brands = get_registered_brands();

if (count($brands) === 1) {
    header('Location: ' . brand_articles_url((string) $brands[0]['slug']));
    exit;
}

if ($brands === []) {
    $pageTitle = 'Articles • FoundryPress';
    $pageDesc  = 'No brands available yet.';

    require dirname(__DIR__) . '/includes/head.php';
    require dirname(__DIR__) . '/includes/nav.php';
    ?>
    <main class="site-main">
        <section class="site-hero">
            <h1>No Brands Yet</h1>
            <p>Create your first brand in the hub to begin.</p>
        </section>
    </main>
    <?php
    require dirname(__DIR__) . '/includes/footer.php';
    exit;
}

$pageTitle = 'Select a Brand • FoundryPress';
$pageDesc  = 'Choose a brand to view its articles.';

require dirname(__DIR__) . '/includes/head.php';
require dirname(__DIR__) . '/includes/nav.php';
?>

<main class="site-main">
    <section class="site-hero">
        <span class="site-kicker">FoundryPress</span>
        <h1>Select a Brand</h1>
        <p>Choose a brand to explore its article library.</p>
    </section>

    <section class="site-section">
        <div class="article-grid">
            <?php foreach ($brands as $brandItem): ?>
                <article class="article-card">
                    <h2><?= h((string) $brandItem['name']) ?></h2>

                    <?php if (($brandItem['tagline'] ?? '') !== ''): ?>
                        <p><strong><?= h((string) $brandItem['tagline']) ?></strong></p>
                    <?php endif; ?>

                    <?php if (($brandItem['description'] ?? '') !== ''): ?>
                        <p><?= h((string) $brandItem['description']) ?></p>
                    <?php endif; ?>

                    <a class="article-card__link" href="<?= h(brand_articles_url((string) $brandItem['slug'])) ?>">
                        View Articles
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>