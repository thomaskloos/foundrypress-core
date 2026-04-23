<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$licenseStatus = fp_get_license_status();
fp_require_hub_login();
fp_require_valid_license();

$pageTitle = 'FoundryPress Hub';
$pageDesc  = 'FoundryPress demo hub for testing themes, workflows, and brand setups.';
$currentUrl = $hubBase . '/';

$previewBrand = $defaultBrand ?? 'demo';
$registeredBrands = get_registered_brands();

require __DIR__ . '/includes/head.php';


$hubCards = [
    [
        'title' => 'Create Brand',
        'desc'  => 'Set up a new brand with the core FoundryPress structure, config, data files, and assets folder.',
        'url'   => '/hub/create-brand.php',
        'tag'   => 'Setup',
    ],
    [
        'title' => 'Preview Articles',
        'desc'  => 'Open the current preview brand and review the front-end article experience with clean URLs.',
        'url'   => brand_articles_url($previewBrand),
        'tag'   => 'Frontend',
    ],
    [
        'title' => 'Documentation',
        'desc'  => 'Review install notes, setup guidance, and structural decisions for the FoundryPress system.',
        'url'   => $docsUrl,
        'tag'   => 'Docs',
    ],
    [
        'title' => 'Main Website',
        'desc'  => 'Jump back to the public FoundryPress website for sales pages, messaging, and product positioning.',
        'url'   => $mainSiteUrl,
        'tag'   => 'Marketing',
    ],
    [
    'title' => 'Prompt Library',
    'desc'  => 'Browse saved prompts across brands and reuse them in your content workflow.',
    'url'   => '/hub/prompt-library.php',
    'tag'   => 'Library',
    ],
    [
    'title' => 'GPT Builder',
    'desc'  => 'Generate brand-aware Custom GPT instructions from your FoundryPress brand settings.',
    'url'   => '/hub/gpt-builder.php',
    'tag'   => 'AI',
    ],
    [
    'title' => 'Theme Switcher',
    'desc'  => 'Choose a theme for each brand without editing configuration files by hand.',
    'url'   => '/hub/theme-switcher.php',
    'tag'   => 'Theme',
],
[
    'title' => 'System Config',
    'desc'  => 'Manage license details and core FoundryPress install settings.',
    'url'   => '/hub/license.php',
    'tag'   => 'Config',
],
];

$themeCards = [
    [
        'title' => 'Minimal Editorial',
        'desc'  => 'A spacious, article-first direction for clean niche sites and simple content libraries.',
    ],
    [
        'title' => 'Wellness Calm',
        'desc'  => 'A softer theme direction suited for health, lifestyle, reflective, and educational brands.',
    ],
    [
        'title' => 'Authority System',
        'desc'  => 'A more structured visual approach for product-driven brands and utility-focused publishing.',
    ],
];
?>
<?php require __DIR__ . '/includes/nav.php'; ?>

<main class="hub-main">
    <section class="hub-hero">
        <div class="hub-hero__content">
            <span class="hub-kicker">Demo Environment</span>
            <h1>FoundryPress Hub</h1>
            <p>
                This hub is the working control center for demo builds, theme testing, and new brand setup inside
                the FoundryPress ecosystem.
            </p>

            <div class="hub-hero__actions">
                <a class="hub-btn hub-btn--primary" href="/hub/create-brand.php">Create New Brand</a>
                <a class="hub-btn hub-btn--secondary" href="<?= h(brand_articles_url($previewBrand)) ?>">Preview Articles</a>
            </div>
        </div>
    </section>
    
    <section class="hub-section">
    <div class="hub-panel">
        <div class="hub-panel__content">
            <span class="hub-kicker">License Status</span>
            <h2><?= h(ucfirst((string) ($licenseStatus['status'] ?? 'inactive'))) ?></h2>
            <p><?= h((string) ($licenseStatus['message'] ?? '')) ?></p>
            <p><strong>Current Domain:</strong> <?= h(function_exists('fp_get_current_domain') ? fp_get_current_domain() : '') ?></p>
            <p><strong>Activated Domain:</strong> <?= h((string) ($licenseStatus['activated_domain'] ?? '—')) ?></p>
        </div>
    </div>
</section>

    <section class="hub-section">
        <div class="hub-section__head">
            <h2>Quick Access</h2>
            <p>Start here to test workflows, review content output, and move through the FoundryPress system.</p>
        </div>

        <div class="hub-card-grid">
            <?php foreach ($hubCards as $card): ?>
                <article class="hub-card">
                    <span class="hub-card__tag"><?= h($card['tag']) ?></span>
                    <h3><?= h($card['title']) ?></h3>
                    <p><?= h($card['desc']) ?></p>
                    <a class="hub-card__link" href="<?= h($card['url']) ?>">Open</a>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="hub-section">
        <div class="hub-section__head">
            <h2>Registered Brands</h2>
            <p>These brands were detected automatically from the <code>/brands/</code> directory.</p>
        </div>

        <?php if ($registeredBrands === []): ?>
            <div class="hub-panel">
                <div class="hub-panel__content">
                    <p>No brands were detected yet. Create your first brand to begin testing the system.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="hub-card-grid">
                <?php foreach ($registeredBrands as $brand): ?>
                    <article class="hub-card">
                        <span class="hub-card__tag"><?= h($brand['template']) ?></span>
                        <h3><?= h($brand['name']) ?></h3>

                        <?php if ($brand['tagline'] !== ''): ?>
                            <p><strong><?= h($brand['tagline']) ?></strong></p>
                        <?php endif; ?>

                        <?php if ($brand['description'] !== ''): ?>
                            <p><?= h($brand['description']) ?></p>
                        <?php else: ?>
                            <p>Slug: <?= h($brand['slug']) ?></p>
                        <?php endif; ?>

                        <p class="hub-card__meta-text">
                            <?= h((string) $brand['article_count']) ?> article<?= $brand['article_count'] === 1 ? '' : 's' ?>
                        </p>

                        <div class="hub-card__actions">
                            <a class="hub-card__link" href="<?= h(brand_articles_url($brand['slug'])) ?>">Preview</a>
                            <a class="hub-card__link" href="/hub/create-brand.php">New Brand</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="hub-section">
        <div class="hub-section__head">
            <h2>Theme Lab</h2>
            <p>Use this environment to test future visual directions before applying them to live installs.</p>
        </div>

        <div class="hub-card-grid">
            <?php foreach ($themeCards as $card): ?>
                <article class="hub-card hub-card--soft">
                    <h3><?= h($card['title']) ?></h3>
                    <p><?= h($card['desc']) ?></p>
                    <span class="hub-card__meta">Theme concept</span>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="hub-section">
        <div class="hub-panel">
            <div class="hub-panel__content">
                <span class="hub-kicker">Suggested Workflow</span>
                <h2>How to use this hub</h2>
                <ol class="hub-steps">
                    <li>Create a new brand structure.</li>
                    <li>Add or import article data.</li>
                    <li>Test front-end layouts through the article system.</li>
                    <li>Adjust theme styles and review the result.</li>
                    <li>Promote successful patterns into the final product package.</li>
                </ol>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>