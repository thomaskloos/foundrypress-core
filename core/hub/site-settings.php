<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

fp_require_hub_login();
fp_require_valid_license();

$pageTitle = 'Site Settings • FoundryPress';
$pageDesc = 'Manage public homepage and brand settings.';
$currentUrl = $hubBase . '/site-settings.php';

$registeredBrands = get_registered_brands();
$brandSlugs = array_map(static fn(array $brand): string => (string) $brand['slug'], $registeredBrands);

$selectedBrand = trim((string) ($_POST['brand'] ?? $_GET['brand'] ?? ($defaultBrand ?? '')));

if ($selectedBrand === '' || !in_array($selectedBrand, $brandSlugs, true)) {
    $selectedBrand = $brandSlugs[0] ?? '';
}

$brandConfig = $selectedBrand !== '' ? load_brand_config($selectedBrand) : [];
$homepage = is_array($brandConfig['homepage'] ?? null) ? $brandConfig['homepage'] : [];

$success = '';
$error = '';

$values = [
    'name' => (string) ($brandConfig['name'] ?? ''),
    'tagline' => (string) ($brandConfig['tagline'] ?? ''),
    'description' => (string) ($brandConfig['description'] ?? ''),
    'headline' => (string) ($homepage['headline'] ?? ''),
    'intro' => (string) ($homepage['intro'] ?? ''),
    'primary_cta_text' => (string) ($homepage['primary_cta_text'] ?? ''),
    'primary_cta_url' => (string) ($homepage['primary_cta_url'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $value) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }

    if ($selectedBrand === '' || $brandConfig === []) {
        $error = 'No editable brand was found.';
    } elseif ($values['name'] === '') {
        $error = 'Brand name is required.';
    } else {
        $brandConfig['name'] = $values['name'];
        $brandConfig['tagline'] = $values['tagline'];
        $brandConfig['description'] = $values['description'];

        $brandConfig['homepage'] = [
            'headline' => $values['headline'],
            'intro' => $values['intro'],
            'primary_cta_text' => $values['primary_cta_text'],
            'primary_cta_url' => $values['primary_cta_url'],
        ];

        if (save_brand_config($selectedBrand, $brandConfig)) {
            $success = 'Site settings saved.';
        } else {
            $error = 'Could not save brand settings. Check file permissions.';
        }
    }
}

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/nav.php';
?>

<main class="hub-main">
    <section class="hub-hero">
        <div class="hub-hero__content">
            <span class="hub-kicker">Site Settings</span>
            <h1>Public Homepage Settings</h1>
            <p>Control what visitors see on the public homepage of this FoundryPress install.</p>
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

                <?php if ($registeredBrands === []): ?>
                    <p>No brands were found. Create a brand first.</p>
                <?php else: ?>
                    <form class="hub-form" method="post">
                        <div class="hub-form__group">
                            <label for="brand">Brand</label>
                            <select id="brand" name="brand" onchange="window.location='?brand=' + encodeURIComponent(this.value)">
                                <?php foreach ($registeredBrands as $brand): ?>
                                    <option value="<?= h((string) $brand['slug']) ?>" <?= ((string) $brand['slug'] === $selectedBrand) ? 'selected' : '' ?>>
                                        <?= h((string) $brand['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="hub-form__group">
                            <label for="name">Brand Name</label>
                            <input id="name" name="name" type="text" value="<?= h($values['name']) ?>" required>
                        </div>

                        <div class="hub-form__group">
                            <label for="tagline">Tagline</label>
                            <input id="tagline" name="tagline" type="text" value="<?= h($values['tagline']) ?>">
                        </div>

                        <div class="hub-form__group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4"><?= h($values['description']) ?></textarea>
                        </div>

                        <hr>

                        <div class="hub-form__group">
                            <label for="headline">Homepage Headline</label>
                            <input id="headline" name="headline" type="text" value="<?= h($values['headline']) ?>">
                        </div>

                        <div class="hub-form__group">
                            <label for="intro">Homepage Intro</label>
                            <textarea id="intro" name="intro" rows="5"><?= h($values['intro']) ?></textarea>
                        </div>

                        <div class="hub-form__group">
                            <label for="primary_cta_text">Primary Button Text</label>
                            <input id="primary_cta_text" name="primary_cta_text" type="text" value="<?= h($values['primary_cta_text']) ?>">
                        </div>

                        <div class="hub-form__group">
                            <label for="primary_cta_url">Primary Button URL</label>
                            <input id="primary_cta_url" name="primary_cta_url" type="text" value="<?= h($values['primary_cta_url']) ?>">
                        </div>

                        <div class="hub-form__actions">
                            <button class="hub-btn hub-btn--primary" type="submit">Save Site Settings</button>
                            <a class="hub-btn hub-btn--secondary" href="/">View Homepage</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
