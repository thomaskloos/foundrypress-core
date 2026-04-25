<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

$brand = trim((string) ($_GET['brand'] ?? ''));
$slug  = trim((string) ($_GET['slug'] ?? ''));
$previewTheme = trim((string) ($_GET['preview_theme'] ?? ''));

if ($brand === '' || $slug === '') {
    http_response_code(404);
    echo 'Invalid request.';
    exit;
}

$brandConfig = load_brand_config($brand);
$articles = load_articles_for_brand($brand);
$article = find_article_by_slug($articles, $slug);

if ($brandConfig === [] || $article === null) {
    http_response_code(404);
    echo 'Article not found.';
    exit;
}

$title = (string) ($article['title'] ?? 'Untitled');
$description = (string) (
    $article['meta_description']
    ?? $article['summary']
    ?? $article['excerpt']
    ?? ''
);

$body = (string) (
    $article['body']
    ?? $article['content']
    ?? ''
);

$author = (string) ($article['author'] ?? ($brandConfig['default_author'] ?? 'FoundryPress'));
$category = (string) ($article['category'] ?? ($brandConfig['default_category'] ?? 'General'));
$publishDate = (string) ($article['publish_date'] ?? '');
$readingTime = (string) ($article['reading_time'] ?? '');

$heroImage = trim((string) (
    $article['hero_image']
    ?? $article['og_image']
    ?? ''
));

$ctaText = trim((string) ($article['cta_text'] ?? ''));
$ctaUrl  = trim((string) ($article['cta_url'] ?? ''));

$pageTitle = $title . ' • ' . ($brandConfig['name'] ?? 'Brand');
$pageDesc  = $description;
$currentUrl = brand_article_url($brand, $slug);

require dirname(__DIR__) . '/includes/head.php';
require dirname(__DIR__) . '/includes/nav.php';
?>

<main class="site-main article-page">

    <article class="article-shell">

        <header class="article-header">
            <a class="article-back-link" href="<?= h(brand_articles_url($brand)) ?>">
                ← Back to Articles
            </a>

            <?php if ($category !== ''): ?>
                <span class="site-kicker"><?= h($category) ?></span>
            <?php endif; ?>

            <h1><?= h($title) ?></h1>

            <?php if ($description !== ''): ?>
                <p class="article-lede"><?= h($description) ?></p>
            <?php endif; ?>

            <div class="article-meta">
                <?php if ($author !== ''): ?>
                    <span><?= h($author) ?></span>
                <?php endif; ?>

                <?php if ($publishDate !== ''): ?>
                    <span><?= h($publishDate) ?></span>
                <?php endif; ?>

                <?php if ($readingTime !== ''): ?>
                    <span><?= h($readingTime) ?></span>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($heroImage !== ''): ?>
            <figure class="article-hero-image">
                <img src="<?= h($heroImage) ?>" alt="<?= h($title) ?>">
            </figure>
        <?php endif; ?>

        <div class="article-content">
            <?= $body ?>
        </div>

        <?php if ($ctaText !== '' && $ctaUrl !== ''): ?>
            <aside class="article-cta">
                <h2>Ready for the next step?</h2>
                <p>Continue exploring your FoundryPress system from the Hub.</p>
                <a class="site-btn site-btn--primary" href="<?= h($ctaUrl) ?>">
                    <?= h($ctaText) ?>
                </a>
            </aside>
        <?php endif; ?>

    </article>

</main>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>