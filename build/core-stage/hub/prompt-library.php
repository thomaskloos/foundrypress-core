<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

fp_require_hub_login();
fp_require_valid_license();

$pageTitle = 'Prompt Library • FoundryPress Hub';
$pageDesc  = 'Browse saved prompts across your FoundryPress brands.';
$currentUrl = $hubBase . '/prompt-library.php';

$registeredBrands = function_exists('get_registered_brands') ? get_registered_brands() : [];
$selectedBrand = trim((string) ($_GET['brand'] ?? 'all'));

if (!function_exists('load_json_file_safe')) {
    function load_json_file_safe(string $file): array
    {
        if (!is_file($file)) {
            return [];
        }

        $json = file_get_contents($file);
        if ($json === false || trim($json) === '') {
            return [];
        }

        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }
}

$promptRecords = [];

foreach ($registeredBrands as $brand) {
    $slug = (string) ($brand['slug'] ?? '');
    if ($slug === '') {
        continue;
    }

    if ($selectedBrand !== 'all' && $selectedBrand !== $slug) {
        continue;
    }

    $promptsFile = dirname(__DIR__) . '/brands/' . $slug . '/_data/prompts.json';
    $records = load_json_file_safe($promptsFile);

    foreach ($records as $record) {
        if (!is_array($record)) {
            continue;
        }

        $record['brand_slug'] = $slug;
        $record['brand_name'] = (string) ($record['brand_name'] ?? ($brand['name'] ?? $slug));
        $promptRecords[] = $record;
    }
}

usort($promptRecords, static function (array $a, array $b): int {
    $aTime = strtotime((string) ($a['created_at'] ?? '')) ?: 0;
    $bTime = strtotime((string) ($b['created_at'] ?? '')) ?: 0;
    return $bTime <=> $aTime;
});

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/nav.php';
?>

<main class="hub-main">
    <section class="hub-hero">
        <div class="hub-hero__content">
            <span class="hub-kicker">Saved Workflow</span>
            <h1>Prompt Library</h1>
            <p>
                Browse saved prompts across your brands and reuse them in your content workflow.
            </p>
        </div>
    </section>

    <section class="hub-section">
        <div class="hub-panel">
            <div class="hub-panel__content">
                <form class="hub-filter-form" method="get">
                    <div class="hub-form__group">
                        <label for="brand">Filter by Brand</label>
                        <select id="brand" name="brand">
                            <option value="all" <?= $selectedBrand === 'all' ? 'selected' : '' ?>>All Brands</option>
                            <?php foreach ($registeredBrands as $brand): ?>
                                <?php $slug = (string) ($brand['slug'] ?? ''); ?>
                                <option value="<?= h($slug) ?>" <?= $selectedBrand === $slug ? 'selected' : '' ?>>
                                    <?= h((string) ($brand['name'] ?? $slug)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hub-form__actions">
                        <button class="hub-btn hub-btn--primary" type="submit">Apply Filter</button>
                        <a class="hub-btn hub-btn--secondary" href="/hub/prompt-library.php">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="hub-section">
        <div class="hub-section__head">
            <h2>Saved Prompts</h2>
            <p>
                <?= count($promptRecords) ?> prompt<?= count($promptRecords) === 1 ? '' : 's' ?> found.
            </p>
        </div>

        <?php if ($promptRecords === []): ?>
            <div class="hub-panel">
                <div class="hub-panel__content">
                    <p>No saved prompts were found for this selection yet.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="hub-library-grid">
                <?php foreach ($promptRecords as $record): ?>
                    <?php
                    $brandName = (string) ($record['brand_name'] ?? 'Unknown Brand');
                    $type = (string) ($record['type'] ?? 'prompt');
                    $topic = (string) ($record['topic'] ?? 'Untitled Topic');
                    $goal = (string) ($record['goal'] ?? '');
                    $audience = (string) ($record['audience'] ?? '');
                    $notes = (string) ($record['notes'] ?? '');
                    $prompt = (string) ($record['prompt'] ?? '');
                    $createdAt = (string) ($record['created_at'] ?? '');
                    ?>
                    <article class="hub-library-card">
                        <div class="hub-library-card__meta">
                            <span class="hub-card__tag"><?= h(ucfirst($type)) ?></span>
                            <span class="hub-library-card__brand"><?= h($brandName) ?></span>
                        </div>

                        <h3><?= h($topic) ?></h3>

                        <?php if ($goal !== ''): ?>
                            <p><strong>Goal:</strong> <?= h($goal) ?></p>
                        <?php endif; ?>

                        <?php if ($audience !== ''): ?>
                            <p><strong>Audience:</strong> <?= h($audience) ?></p>
                        <?php endif; ?>

                        <?php if ($notes !== ''): ?>
                            <p><strong>Notes:</strong> <?= h($notes) ?></p>
                        <?php endif; ?>

                        <?php if ($createdAt !== ''): ?>
                            <p class="hub-library-card__date">
                                Saved: <?= h(date('M j, Y g:i A', strtotime($createdAt) ?: time())) ?>
                            </p>
                        <?php endif; ?>

                        <textarea class="hub-prompt-output hub-prompt-output--library" rows="14" readonly><?= h($prompt) ?></textarea>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>