<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

fp_require_hub_login();
fp_require_valid_license();

$pageTitle = 'Create Brand • FoundryPress Hub';
$pageDesc  = 'Create a new brand inside the FoundryPress system.';
$currentUrl = $hubBase . '/create-brand.php';

$message = '';
$error = '';

$registeredBrands = function_exists('get_registered_brands') ? get_registered_brands() : [];

function fp_slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $brandName = trim((string) ($_POST['brand_name'] ?? ''));
    $brandSlug = trim((string) ($_POST['brand_slug'] ?? ''));

    if ($brandName === '') {
        $error = 'Brand name is required.';
    } else {

        $brandSlug = $brandSlug !== '' ? fp_slugify($brandSlug) : fp_slugify($brandName);

        if ($brandSlug === '') {
            $error = 'Please enter a valid brand slug.';
        } else {

            $brandsRoot = dirname(__DIR__) . '/brands';
            $brandPath  = $brandsRoot . '/' . $brandSlug;
            $dataPath   = $brandPath . '/_data';
            $assetsPath = $brandPath . '/assets';

            if (is_dir($brandPath)) {
                $error = 'That brand already exists.';
            } else {

                $ok = true;

                // Create directories
                if (!is_dir($brandsRoot) && !mkdir($brandsRoot, 0755, true)) {
                    $ok = false;
                }

                if ($ok && !mkdir($brandPath, 0755, true)) {
                    $ok = false;
                }

                if ($ok && !mkdir($dataPath, 0755, true)) {
                    $ok = false;
                }

                if ($ok && !mkdir($assetsPath, 0755, true)) {
                    $ok = false;
                }

                if (!$ok) {
                    $error = 'Unable to create folders on the server.';
                } else {

                    // 🔥 FULL BRAND CONFIG (your upgraded structure)
                    $brandPhp = <<<PHP
<?php
declare(strict_types=1);

return [

    'name' => '{$brandName}',
    'slug' => '{$brandSlug}',
    'tagline' => '',
    'description' => '',

    'base_url' => '{$baseUrl}',
    'article_path' => '/articles/',
    'brand_path' => '/brands/{$brandSlug}/',

    'logo' => '/brands/{$brandSlug}/assets/logo.png',
    'og_default' => '/brands/{$brandSlug}/assets/og-default.jpg',

    'colors' => [
        'primary' => '#2F5D62',
        'secondary' => '#F3F6F6',
        'accent' => '#A7C4BC',
        'text' => '#1E1E1E',
    ],

    'typography' => [
        'heading' => 'Inter, sans-serif',
        'body' => 'Inter, sans-serif',
    ],

    'template' => 'core-clean',

    'tone' => 'calm, structured, encouraging, honest',
    'voice_notes' => 'Avoid hype. Focus on clarity, simplicity, and grounded guidance.',

    'default_author' => 'FoundryPress',
    'default_category' => 'General',
    'default_status' => 'Draft',

    'seo' => [
        'meta_title_suffix' => ' | FoundryPress',
        'meta_description' => '',
        'og_image' => '/brands/{$brandSlug}/assets/og-default.jpg',
    ],

    'cta' => [
        'text' => 'Read More',
        'url' => '#',
    ],

];
PHP;

                    // JSON files
                    $emptyJson = json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

                    $written =
                        file_put_contents($brandPath . '/brand.php', $brandPhp) !== false &&
                        file_put_contents($dataPath . '/articles.json', $emptyJson) !== false &&
                        file_put_contents($dataPath . '/products.json', $emptyJson) !== false &&
                        file_put_contents($dataPath . '/prompts.json', $emptyJson) !== false;

                    if (!$written) {
                        $error = 'Folders created, but files could not be written.';
                    } else {
                        $message = "Brand '{$brandName}' created successfully.";
                    }
                }
            }
        }
    }
}

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/nav.php';
?>

<main class="hub-main">
    <section class="hub-hero">
        <div class="hub-hero__content">
            <span class="hub-kicker">Brand Setup</span>
            <h1>Create a New Brand</h1>
            <p>
                This will generate a full FoundryPress brand structure including config, data files, and assets.
            </p>
        </div>
    </section>
    
   <section class="hub-section">
        <div class="hub-panel">
            <div class="hub-panel__content">
    <h2>Existing Brands</h2>
    <p>Brands currently detected in the <code>/brands/</code> directory.</p>
</div>

    <?php if ($registeredBrands === []): ?>
        <div class="hub-panel">
            <div class="hub-panel__content">
                <p>No brands found yet. Create your first brand below.</p>
            </div>
        </div>
    <?php else: ?>
       
            <?php foreach ($registeredBrands as $brand): ?>
                <article class="hub-card">
                    <span class="hub-card__tag"><?= h((string) ($brand['template'] ?? 'Brand')) ?></span>
                    <h3><?= h((string) ($brand['name'] ?? $brand['slug'] ?? 'Untitled Brand')) ?></h3>

                    <?php if (!empty($brand['tagline'])): ?>
                        <p><strong><?= h((string) $brand['tagline']) ?></strong></p>
                    <?php endif; ?>

                    <?php if (!empty($brand['description'])): ?>
                        <p><?= h((string) $brand['description']) ?></p>
                    <?php else: ?>
                        <p>Slug: <?= h((string) ($brand['slug'] ?? '')) ?></p>
                    <?php endif; ?>

                    <?php if (isset($brand['article_count'])): ?>
                        <p class="hub-card__meta-text">
                            <?= h((string) $brand['article_count']) ?>
                            article<?= (int) $brand['article_count'] === 1 ? '' : 's' ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($brand['slug'])): ?>
                        <a class="hub-card__link" href="<?= h(brand_articles_url((string) $brand['slug'])) ?>">
                            Preview Articles
                        </a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

    <section class="hub-section">
        <div class="hub-panel">
            <div class="hub-panel__content">

                <?php if ($message !== ''): ?>
                    <div class="hub-alert hub-alert--success"><?= h($message) ?></div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="hub-alert hub-alert--error"><?= h($error) ?></div>
                <?php endif; ?>

                <form class="hub-form" method="post">

                    <div class="hub-form__group">
                        <label for="brand_name">Brand Name</label>
                        <input id="brand_name" name="brand_name" type="text" required>
                    </div>

                    <div class="hub-form__group">
                        <label for="brand_slug">
                            Brand Slug <span>(optional)</span>
                        </label>
                        <input id="brand_slug" name="brand_slug" type="text" placeholder="my-brand">
                    </div>

                    <div class="hub-form__actions">
                        <button class="hub-btn hub-btn--primary" type="submit">
                            Create Brand
                        </button>

                        <a class="hub-btn hub-btn--secondary" href="/hub/">
                            Back to Hub
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>