<?php
declare(strict_types=1);

require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';

$pageTitle = 'FoundryPress Demo';
$pageDesc  = 'Explore the FoundryPress demo environment.';
$currentUrl = $baseUrl . '/';

$license = load_license_config();

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/nav.php';
?>

<main class="site-main">
    <section class="demo-banner">
        <div class="demo-banner__inner">
            <div>
                <span class="site-kicker">Live Demo</span>
                <h1>Explore FoundryPress in action</h1>
                <p>
                    This is the live demo and test environment for FoundryPress themes, workflows, and brand structure.
                </p>
            </div>

            <div class="demo-banner__actions">
                <a class="site-btn site-btn--primary" href="https://foundrypressapp.com/" target="_blank" rel="noopener noreferrer">Back to Main Site</a>
                <a class="site-btn site-btn--secondary" href="https://foundrypressapp.com/docs/" target="_blank" rel="noopener noreferrer">View Docs</a>
            </div>
        </div>
    </section>

    <section class="site-section">
        <div class="site-section__head">
            <h2>Start exploring</h2>
            <p>Use the links below to preview the system.</p>
        </div>

        <div class="article-grid">
            <article class="article-card">
                <h2>Open the Hub</h2>
                <p>See the internal dashboard and workflow tools.</p>
                <a class="article-card__link" href="/hub/">Open Hub</a>
            </article>

            <article class="article-card">
                <h2>Browse Demo Articles</h2>
                <p>See how content renders on the front end.</p>
                <a class="article-card__link" href="/articles/demo/">View Articles</a>
            </article>

            <article class="article-card">
                <h2>See Themes</h2>
                <p>Preview theme switching and brand styling.</p>
                <a class="article-card__link" href="/hub/theme-switcher.php">Open Theme Switcher</a>
            </article>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>