<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

fp_require_hub_login();
fp_require_valid_license();

$pageTitle = 'GPT Builder • FoundryPress Hub';
$pageDesc  = 'Generate brand-aware Custom GPT instructions from your FoundryPress brand configuration.';
$currentUrl = $hubBase . '/gpt-builder.php';

$registeredBrands = function_exists('get_registered_brands') ? get_registered_brands() : [];

$selectedBrand = trim((string) ($_POST['brand'] ?? ($_GET['brand'] ?? ($defaultBrand ?? ''))));
$primaryUse    = trim((string) ($_POST['primary_use'] ?? 'content'));
$extraRules    = trim((string) ($_POST['extra_rules'] ?? ''));
$includeProducts = isset($_POST['include_products']) && $_POST['include_products'] === '1';
$includeEmails   = isset($_POST['include_emails']) && $_POST['include_emails'] === '1';
$includeSocial   = isset($_POST['include_social']) && $_POST['include_social'] === '1';

$generatedInstructions = '';
$error = '';

$brandConfig = $selectedBrand !== '' ? load_brand_config($selectedBrand) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($selectedBrand === '') {
        $error = 'Please select a brand.';
    } elseif ($brandConfig === []) {
        $error = 'The selected brand could not be loaded.';
    } else {
        $brandName        = (string) ($brandConfig['name'] ?? 'This Brand');
        $tagline          = (string) ($brandConfig['tagline'] ?? '');
        $description      = (string) ($brandConfig['description'] ?? '');
        $tone             = (string) ($brandConfig['tone'] ?? 'clear, helpful, and grounded');
        $voiceNotes       = (string) ($brandConfig['voice_notes'] ?? 'Keep the content practical, calm, and easy to understand.');
        $defaultAuthor    = (string) ($brandConfig['default_author'] ?? 'FoundryPress');
        $template         = (string) ($brandConfig['template'] ?? 'core-clean');
        $defaultCategory  = (string) ($brandConfig['default_category'] ?? 'General');

        $brandIdentity = [];
        if ($tagline !== '') {
            $brandIdentity[] = "Tagline: {$tagline}";
        }
        if ($description !== '') {
            $brandIdentity[] = "Brand description: {$description}";
        }
        $brandIdentityText = $brandIdentity !== [] ? implode("\n", $brandIdentity) . "\n" : '';

        $useCaseBlock = match ($primaryUse) {
            'content' => <<<TEXT
PRIMARY ROLE:
- Create high-quality articles, outlines, summaries, and content ideas for the brand.
- Focus on useful, clear, structured writing.
TEXT,
            'products' => <<<TEXT
PRIMARY ROLE:
- Create digital product ideas, outlines, workbooks, guides, checklists, and supporting content for the brand.
- Focus on transformation, clarity, and practical usefulness.
TEXT,
            'marketing' => <<<TEXT
PRIMARY ROLE:
- Create content that supports traffic, opt-ins, offers, product positioning, and conversion-focused messaging.
- Keep the brand voice intact and avoid hype.
TEXT,
            default => <<<TEXT
PRIMARY ROLE:
- Support the brand with clear, useful, structured content and strategic content development.
TEXT,
        };

        $capabilities = [
            '- Generate article titles, outlines, and full drafts.',
            '- Write in a voice aligned with the brand configuration.',
            '- Keep output structured, readable, and grounded.',
            '- Avoid exaggerated claims, fluff, and generic filler.',
        ];

        if ($includeProducts) {
            $capabilities[] = '- Generate product concepts, workbook structures, guide outlines, and supporting materials.';
        }

        if ($includeEmails) {
            $capabilities[] = '- Generate email sequences, welcome emails, follow-up emails, and launch support emails.';
        }

        if ($includeSocial) {
            $capabilities[] = '- Generate social captions, hooks, post variations, and image text ideas.';
        }

        $capabilitiesText = implode("\n", $capabilities);

        $extraRulesText = $extraRules !== ''
            ? "ADDITIONAL RULES:\n{$extraRules}\n"
            : '';

        $generatedInstructions = <<<TEXT
You are the FoundryPress Brand Assistant for {$brandName}.

YOUR PURPOSE:
You help create brand-aligned content, ideas, and assets that match the voice, structure, and tone of {$brandName}.

{$brandIdentityText}BRAND VOICE:
- Tone: {$tone}
- Voice notes: {$voiceNotes}

BRAND DEFAULTS:
- Default author: {$defaultAuthor}
- Default category: {$defaultCategory}
- Template direction: {$template}

{$useCaseBlock}

CORE CAPABILITIES:
{$capabilitiesText}

WRITING RULES:
- Always write in a way that matches the brand tone and voice.
- Prioritize clarity, usefulness, and structure.
- Keep paragraphs readable and natural.
- Use subheadings and organized flow when appropriate.
- Avoid hype, fluff, clickbait, and exaggerated language.
- Avoid sounding robotic, generic, or overproduced.
- Keep the writing honest, grounded, and practical.

OUTPUT PREFERENCES:
- When writing articles, provide:
  - title
  - suggested slug
  - meta description
  - article excerpt
  - structured body
- When generating ideas, provide multiple useful options.
- When unsure, choose the clearest and most practical direction.

CONTENT QUALITY STANDARD:
- Output should feel like it belongs to a thoughtful, premium content brand.
- It should be easy to read, emotionally grounded, and strategically useful.
- It should feel human, not templated.

{$extraRulesText}IMPORTANT:
- Stay aligned with {$brandName}.
- Do not drift into off-brand language.
- Do not default to hype-based marketing language.
- When possible, make the output feel calm, structured, and genuinely helpful.
TEXT;
    }
}

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/nav.php';
?>

<main class="hub-main">
    <section class="hub-hero">
        <div class="hub-hero__content">
            <span class="hub-kicker">AI Workflow</span>
            <h1>GPT Builder</h1>
            <p>
                Generate Custom GPT instructions from your FoundryPress brand settings so each brand can have its own aligned AI assistant.
            </p>
        </div>
    </section>

    <section class="hub-section">
        <div class="hub-panel">
            <div class="hub-panel__content">

                <?php if ($error !== ''): ?>
                    <div class="hub-alert hub-alert--error"><?= h($error) ?></div>
                <?php endif; ?>

                <form class="hub-form" method="post">
                    <div class="hub-form__group">
                        <label for="brand">Brand</label>
                        <select id="brand" name="brand" required>
                            <option value="">Select a brand</option>
                            <?php foreach ($registeredBrands as $brand): ?>
                                <?php $slug = (string) ($brand['slug'] ?? ''); ?>
                                <option value="<?= h($slug) ?>" <?= $selectedBrand === $slug ? 'selected' : '' ?>>
                                    <?= h((string) ($brand['name'] ?? $slug)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hub-form__group">
                        <label for="primary_use">Primary GPT Use</label>
                        <select id="primary_use" name="primary_use">
                            <option value="content" <?= $primaryUse === 'content' ? 'selected' : '' ?>>Content Creation</option>
                            <option value="products" <?= $primaryUse === 'products' ? 'selected' : '' ?>>Product Creation</option>
                            <option value="marketing" <?= $primaryUse === 'marketing' ? 'selected' : '' ?>>Marketing Support</option>
                        </select>
                    </div>

                    <div class="hub-form__group">
                        <label class="hub-checkbox">
                            <input type="checkbox" name="include_products" value="1" <?= $includeProducts ? 'checked' : '' ?>>
                            <span>Include product creation capabilities</span>
                        </label>
                    </div>

                    <div class="hub-form__group">
                        <label class="hub-checkbox">
                            <input type="checkbox" name="include_emails" value="1" <?= $includeEmails ? 'checked' : '' ?>>
                            <span>Include email writing capabilities</span>
                        </label>
                    </div>

                    <div class="hub-form__group">
                        <label class="hub-checkbox">
                            <input type="checkbox" name="include_social" value="1" <?= $includeSocial ? 'checked' : '' ?>>
                            <span>Include social content capabilities</span>
                        </label>
                    </div>

                    <div class="hub-form__group">
                        <label for="extra_rules">Additional Rules</label>
                        <textarea id="extra_rules" name="extra_rules" rows="6" placeholder="Add any extra instructions for the GPT..."><?= h($extraRules) ?></textarea>
                    </div>

                    <div class="hub-form__actions">
                        <button class="hub-btn hub-btn--primary" type="submit">Generate GPT Instructions</button>
                        <a class="hub-btn hub-btn--secondary" href="/hub/">Back to Hub</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <?php if ($generatedInstructions !== ''): ?>
        <section class="hub-section">
            <div class="hub-section__head">
                <h2>Generated GPT Instructions</h2>
                <p>Copy these into your Custom GPT instructions to create a brand-aligned assistant.</p>
            </div>

            <div class="hub-panel">
                <div class="hub-panel__content">
                    <div class="hub-output-actions">
                        <button
                            class="hub-btn hub-btn--primary js-copy-prompt"
                            type="button"
                            data-copy-target="gpt-builder-output"
                        >
                            Copy Instructions
                        </button>

                        <a
                            class="hub-btn hub-btn--secondary"
                            href="<?= h($foundryPressGptUrl ?? 'https://chatgpt.com/gpts') ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open ChatGPT
                        </a>
                    </div>

                    <textarea
                        id="gpt-builder-output"
                        class="hub-prompt-output"
                        rows="24"
                        readonly
                    ><?= h($generatedInstructions) ?></textarea>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>