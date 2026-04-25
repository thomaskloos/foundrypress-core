<?php
declare(strict_types=1);

$siteConfigFile = __DIR__ . '/config/site.php';
$installFile = __DIR__ . '/install.php';

if (!is_file($siteConfigFile) && is_file($installFile)) {
    header('Location: /install.php');
    exit;
}

require __DIR__ . '/bootstrap.php';

$brandSlug = trim((string) ($defaultBrand ?? ''));
$brandConfig = $brandSlug !== '' ? load_brand_config($brandSlug) : [];

$brandName = trim((string) ($brandConfig['name'] ?? $siteName));
$tagline = trim((string) ($brandConfig['tagline'] ?? ''));
$description = trim((string) ($brandConfig['description'] ?? ''));

$homepage = is_array($brandConfig['homepage'] ?? null) ? $brandConfig['homepage'] : [];

$headline = trim((string) ($homepage['headline'] ?? 'Welcome to ' . $brandName));
$intro = trim((string) ($homepage['intro'] ?? ($description !== '' ? $description : 'Your site is ready.')));
$primaryCtaText = trim((string) ($homepage['primary_cta_text'] ?? 'Read Articles'));
$primaryCtaUrl = trim((string) ($homepage['primary_cta_url'] ?? ''));

$articlesUrl = '';
if ($brandSlug !== '') {
    $articlesUrl = rtrim((string) $baseUrl, '/') . brand_articles_url($brandSlug);
}

if ($primaryCtaUrl === '' && $articlesUrl !== '') {
    $primaryCtaUrl = $articlesUrl;
}

$pageTitle = $brandName;
$pageDesc = $intro;
$currentUrl = rtrim((string) $baseUrl, '/') . '/';

$articles = $brandSlug !== '' ? load_articles_for_brand($brandSlug) : [];
$featuredArticle = null;

foreach ($articles as $article) {
    if (($article['status'] ?? '') === 'published' && !empty($article['featured'])) {
        $featuredArticle = $article;
        break;
    }
}

if ($featuredArticle === null) {
    foreach ($articles as $article) {
        if (($article['status'] ?? '') === 'published') {
            $featuredArticle = $article;
            break;
        }
    }
}

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/nav.php';
?>

<main class="site-main">
    <section class="demo-banner">
        <div class="demo-banner__inner">
            <div>
                <?php if ($tagline !== ''): ?>
                    <span class="site-kicker"><?= h($tagline) ?></span>
                <?php endif; ?>

                <h1><?= h($headline) ?></h1>
                <p><?= h($intro) ?></p>
            </div>

            <div class="demo-banner__actions">
                <?php if ($primaryCtaUrl !== ''): ?>
                    <a class="site-btn site-btn--primary" href="<?= h($primaryCtaUrl) ?>">
                        <?= h($primaryCtaText !== '' ? $primaryCtaText : 'Learn More') ?>
                    </a>
                <?php endif; ?>

                <?php if ($articlesUrl !== ''): ?>
                    <a class="site-btn site-btn--secondary" href="<?= h($articlesUrl) ?>">
                        View Articles
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="site-section">
        <div class="site-section__head">
            <h2>Latest from <?= h($brandName) ?></h2>
            <p><?= h($description !== '' ? $description : 'Explore the latest published content.') ?></p>
        </div>

        <div class="article-grid">
            <?php if ($featuredArticle !== null): ?>
                <article class="article-card">
                    <span class="site-kicker"><?= h((string) ($featuredArticle['category'] ?? 'Featured')) ?></span>
                    <h2><?= h((string) ($featuredArticle['title'] ?? 'Featured Article')) ?></h2>

                    <?php if (!empty($featuredArticle['summary'])): ?>
                        <p><?= h((string) $featuredArticle['summary']) ?></p>
                    <?php endif; ?>

                    <a class="article-card__link" href="<?= h(brand_article_url($brandSlug, (string) ($featuredArticle['slug'] ?? ''))) ?>">
                        Read Article
                    </a>
                </article>
            <?php else: ?>
                <article class="article-card">
                    <h2>Your homepage is ready</h2>
                    <p>Add your first article from the Hub or by editing your brand article JSON file.</p>

                    <?php if ($articlesUrl !== ''): ?>
                        <a class="article-card__link" href="<?= h($articlesUrl) ?>">View Articles</a>
                    <?php endif; ?>
                </article>
            <?php endif; ?>

            <article class="article-card">
                <h2>About <?= h($brandName) ?></h2>
                <p><?= h($description !== '' ? $description : 'Update your site details in the Hub Site Settings page.') ?></p>
                <a class="article-card__link" href="/hub/site-settings.php">Edit Site Settings</a>
            </article>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>