<?php
declare(strict_types=1);

require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$brandSlug = $defaultBrand;

$pageTitle  = 'Welcome to FoundryPress';
$pageDesc   = 'Learn how your FoundryPress publishing system works.';
$currentUrl = rtrim((string) $baseUrl, '/') . '/';

$license = function_exists('load_license_config') ? load_license_config() : [];

$isActivated = (bool) ($license['is_activated'] ?? false);
$licenseStatus = trim((string) ($license['status'] ?? ($isActivated ? 'active' : 'inactive')));

$hubUrl      = rtrim((string) $baseUrl, '/') . '/hub/';
$articlesUrl = '';

if ($brandSlug !== '') {
    $articlesUrl = rtrim((string) $baseUrl, '/') . brand_articles_url($brandSlug);
}

$themeUrl    = rtrim((string) $baseUrl, '/') . '/hub/theme-switcher.php';
$licenseUrl  = rtrim((string) $baseUrl, '/') . '/hub/license.php';

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/nav.php';
?>

<main class="site-main">
    <section class="demo-banner">
        <div class="demo-banner__inner">
            <div>
                <span class="site-kicker">FoundryPress is installed</span>
                <h1>Your publishing system is ready.</h1>
                <p>
                    This page is your starting point. Learn how the Hub, brands, articles,
                    themes, and license system work together.
                </p>
            </div>

            <div class="demo-banner__actions">
                <a class="site-btn site-btn--primary" href="<?= h($hubUrl) ?>">Open the Hub</a>
                <a class="site-btn site-btn--secondary" href="<?= h($articlesUrl) ?>">View Demo Articles</a>
            </div>
        </div>
    </section>

    <section class="site-section">
        <div class="site-section__head">
            <h2>Start here</h2>
            <p>Follow these steps to understand the core FoundryPress workflow.</p>
        </div>

        <div class="article-grid">
            <article class="article-card">
                <span class="site-kicker">Step 1</span>
                <h2>Open the Hub</h2>
                <p>
                    The Hub is where you manage brands, review workflows, activate your license,
                    and access publishing tools.
                </p>
                <a class="article-card__link" href="<?= h($hubUrl) ?>">Go to Hub</a>
            </article>

            <article class="article-card">
                <span class="site-kicker">Step 2</span>
                <h2>Preview articles</h2>
                <p>
                    Demo content shows how JSON-based articles render on the front end using
                    clean article URLs.
                </p>
                <a class="article-card__link" href="<?= h($articlesUrl) ?>">Browse Articles</a>
            </article>

            <article class="article-card">
                <span class="site-kicker">Step 3</span>
                <h2>Try themes</h2>
                <p>
                    Use the theme switcher to see how a brand can change presentation without
                    changing article content.
                </p>
                <a class="article-card__link" href="<?= h($themeUrl) ?>">Open Theme Switcher</a>
            </article>
        </div>
    </section>

    <section class="site-section">
        <div class="site-section__head">
            <h2>How FoundryPress is organized</h2>
            <p>The system is intentionally simple and portable.</p>
        </div>

        <div class="article-grid">
            <article class="article-card">
                <h2>Brands</h2>
                <p>
                    Each brand keeps its own configuration, assets, and article data inside
                    the <code>/brands/</code> structure.
                </p>
            </article>

            <article class="article-card">
                <h2>Articles</h2>
                <p>
                    Content is stored as JSON instead of a database, making the system easier
                    to move, inspect, and back up.
                </p>
            </article>

            <article class="article-card">
                <h2>Clean URLs</h2>
                <p>
                    Article pages use readable routes like <code>/articles/demo/article-slug</code>
                    for a simple publishing experience.
                </p>
            </article>
        </div>
    </section>

    <section class="site-section">
        <div class="site-section__head">
            <h2>Install status</h2>
            <p>Check the basics before building your first real brand.</p>
        </div>

        <div class="article-grid">
            <article class="article-card">
                <h2>License</h2>
                <p>
                    Current status:
                    <strong><?= h($licenseStatus !== '' ? ucfirst($licenseStatus) : 'Inactive') ?></strong>
                </p>
                <a class="article-card__link" href="<?= h($licenseUrl) ?>">
                    <?= $isActivated ? 'Review License' : 'Activate License' ?>
                </a>
            </article>

            <article class="article-card">
                <h2>Default brand</h2>
                <p>
                    Current preview brand:
                    <strong><?= h($brandSlug) ?></strong>
                </p>
                <a class="article-card__link" href="<?= h($articlesUrl) ?>">Preview Brand</a>
            </article>

            <article class="article-card">
                <h2>Next move</h2>
                <p>
                    After reviewing the demo, create your first brand and replace the sample
                    articles with your own content.
                </p>
                <a class="article-card__link" href="<?= h($hubUrl) ?>">Start Building</a>
            </article>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>