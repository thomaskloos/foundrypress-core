<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

fp_require_hub_login();
fp_require_valid_license();

$pageTitle = 'Theme Switcher • FoundryPress Hub';
$pageDesc  = 'Select a theme for each FoundryPress brand without editing configuration files by hand.';
$currentUrl = $hubBase . '/theme-switcher.php';

$registeredBrands = get_registered_brands();
$availableThemes = get_available_themes();

$selectedBrand = trim((string) ($_POST['brand'] ?? ($_GET['brand'] ?? '')));
$selectedTheme = trim((string) ($_POST['theme'] ?? ''));

if ($selectedBrand === '' && !empty($registeredBrands)) {
    $selectedBrand = (string) ($registeredBrands[0]['slug'] ?? '');
}

$success = '';
$error = '';

$brandConfig = $selectedBrand !== '' ? load_brand_config($selectedBrand) : [];
$currentTheme = (string) ($brandConfig['template'] ?? 'core-clean');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($selectedBrand === '') {
        $error = 'Please select a brand.';
    } elseif ($selectedTheme === '') {
        $error = 'Please select a theme.';
    } elseif ($brandConfig === []) {
        $error = 'The selected brand could not be loaded.';
    } else {
        $brandConfig['template'] = $selectedTheme;

        if (save_brand_config($selectedBrand, $brandConfig)) {
            $success = 'Theme updated successfully.';

            // Reload brand config so the UI reflects the saved theme immediately.
            $brandConfig = load_brand_config($selectedBrand);
            $currentTheme = (string) ($brandConfig['template'] ?? 'core-clean');
        } else {
            $error = 'Could not save the brand theme.';
        }
    }
}

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/nav.php';
?>

<main class="hub-main">
    <section class="hub-hero">
        <div class="hub-hero__content">
            <span class="hub-kicker">Brand Styling</span>
            <h1>Theme Switcher</h1>
            <p>
                Select a theme for a brand without editing configuration files by hand.
            </p>
        </div>
    </section>

    <section class="hub-section">
        <div class="hub-panel">
            <div class="hub-panel__content">
                <?php if ($success !== ''): ?>
                    <div class="hub-alert hub-alert--success"><?= h($success) ?></div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="hub-alert hub-alert--error"><?= h($error) ?></div>
                <?php endif; ?>

                <form class="hub-form" method="post">
                    <div class="hub-form__group">
                        <label for="brand">Brand</label>
                        <select id="brand" name="brand" onchange="this.form.submit()">
                            <option value="">Select a brand</option>
                            <?php foreach ($registeredBrands as $brand): ?>
                                <?php $slug = (string) ($brand['slug'] ?? ''); ?>
                                <option value="<?= h($slug) ?>" <?= $selectedBrand === $slug ? 'selected' : '' ?>>
                                    <?= h((string) ($brand['name'] ?? $slug)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($selectedBrand === ''): ?>
                        <div class="hub-alert hub-alert--error">
                            Select a brand first, then choose a theme below.
                        </div>
                    <?php endif; ?>

                    <?php if ($brandConfig !== []): ?>
                        <div class="hub-panel" style="padding: 1rem; margin-top: 0.5rem;">
                            <p><strong>Current brand:</strong> <?= h((string) ($brandConfig['name'] ?? $selectedBrand)) ?></p>
                            <p><strong>Current theme:</strong> <?= h($currentTheme) ?></p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section class="hub-section">
        <div class="hub-section__head">
            <h2>Theme Preview</h2>
            <p>Select a visual direction for the chosen brand.</p>
        </div>

        <?php if ($selectedBrand !== '' && $brandConfig !== []): ?>
            <div class="hub-panel" style="margin-bottom: 1rem;">
                <p><strong>Editing brand:</strong> <?= h((string) ($brandConfig['name'] ?? $selectedBrand)) ?></p>
            </div>
        <?php endif; ?>

        <div class="hub-theme-grid">
            <?php foreach ($availableThemes as $theme): ?>
                <?php
                $themeSlug = (string) ($theme['slug'] ?? '');
                $themeName = (string) ($theme['name'] ?? 'Theme');
                $themeDesc = (string) ($theme['description'] ?? '');
                $themeLabel = (string) ($theme['label'] ?? 'Theme');
                $colors = is_array($theme['colors'] ?? null) ? $theme['colors'] : [];

                $bg = (string) ($colors['bg'] ?? '#f6f8fb');
                $surface = (string) ($colors['surface'] ?? '#ffffff');
                $text = (string) ($colors['text'] ?? '#1d3243');
                $accent = (string) ($colors['accent'] ?? '#2f6fed');

                $isCurrent = $currentTheme === $themeSlug;
                ?>
                <article class="hub-theme-card<?= $isCurrent ? ' is-current' : '' ?>">
                    <div class="hub-theme-card__preview" style="background: <?= h($bg) ?>;">
                        <div class="hub-theme-card__surface" style="background: <?= h($surface) ?>; border-color: <?= h($accent) ?>22;">
                            <div class="hub-theme-card__line hub-theme-card__line--title" style="background: <?= h($text) ?>;"></div>
                            <div class="hub-theme-card__line" style="background: <?= h($text) ?>55;"></div>
                            <div class="hub-theme-card__line" style="background: <?= h($text) ?>33;"></div>
                            <div class="hub-theme-card__accent" style="background: <?= h($accent) ?>;"></div>
                        </div>
                    </div>

                    <span class="hub-card__tag"><?= h($themeLabel) ?></span>
                    <h3><?= h($themeName) ?></h3>
                    <p><?= h($themeDesc) ?></p>

                    <div class="hub-theme-card__swatches">
                        <span style="background: <?= h($bg) ?>;"></span>
                        <span style="background: <?= h($surface) ?>;"></span>
                        <span style="background: <?= h($text) ?>;"></span>
                        <span style="background: <?= h($accent) ?>;"></span>
                    </div>

                  <form method="post" class="hub-theme-card__form">
    <input type="hidden" name="brand" value="<?= h($selectedBrand) ?>">
    <input type="hidden" name="theme" value="<?= h($themeSlug) ?>">

    <?php
    $previewUrl = $selectedBrand !== ''
        ? brand_articles_url($selectedBrand) . '?preview_theme=' . rawurlencode($themeSlug)
        : '#';
    ?>

    <div class="hub-card__actions">
        <?php if ($selectedBrand === ''): ?>
            <button class="hub-btn hub-btn--secondary" type="button" disabled>
                Select Brand First
            </button>
        <?php else: ?>
            <button class="hub-btn <?= $isCurrent ? 'hub-btn--secondary' : 'hub-btn--primary' ?>" type="submit">
                <?= $isCurrent ? 'Current Theme' : 'Use This Theme' ?>
            </button>

            <a class="hub-btn hub-btn--secondary" href="<?= h($previewUrl) ?>" target="_blank" rel="noopener noreferrer">
                Preview Live
            </a>
        <?php endif; ?>
    </div>
</form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="hub-section">
        <div class="hub-form__actions">
            <?php if ($selectedBrand !== ''): ?>
                <a class="hub-btn hub-btn--secondary" href="<?= h(brand_articles_url($selectedBrand)) ?>">
                    Preview Brand
                </a>
            <?php endif; ?>

            <a class="hub-btn hub-btn--secondary" href="/hub/">
                Back to Hub
            </a>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>