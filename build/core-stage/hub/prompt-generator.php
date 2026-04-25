<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

fp_require_hub_login();
fp_require_valid_license();

$pageTitle = 'Prompt Generator • FoundryPress Hub';
$pageDesc  = 'Generate and save structured prompts for articles, images, products, emails, and social content.';
$currentUrl = $hubBase . '/prompt-generator.php';

$registeredBrands = function_exists('get_registered_brands') ? get_registered_brands() : [];

$selectedBrand = trim((string) ($_POST['brand'] ?? ($_GET['brand'] ?? ($defaultBrand ?? ''))));
$contentType   = trim((string) ($_POST['content_type'] ?? 'article'));
$topic         = trim((string) ($_POST['topic'] ?? ''));
$goal          = trim((string) ($_POST['goal'] ?? ''));
$audience      = trim((string) ($_POST['audience'] ?? ''));
$notes         = trim((string) ($_POST['notes'] ?? ''));
$savePrompt    = isset($_POST['save_prompt']) && $_POST['save_prompt'] === '1';

$generatedPrompt = '';
$success = '';
$error = '';

$brandConfig = $selectedBrand !== '' ? load_brand_config($selectedBrand) : [];

$brandName   = (string) ($brandConfig['name'] ?? 'This brand');
$brandTone   = (string) ($brandConfig['tone'] ?? 'clear, helpful, and grounded');
$voiceNotes  = (string) ($brandConfig['voice_notes'] ?? 'Keep the content practical and easy to understand.');
$author      = (string) ($brandConfig['default_author'] ?? 'FoundryPress');
$template    = (string) ($brandConfig['template'] ?? 'core-clean');

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

if (!function_exists('save_json_file_safe')) {
    function save_json_file_safe(string $file, array $data): bool
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return false;
        }

        return file_put_contents($file, $json) !== false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($selectedBrand === '') {
        $error = 'Please select a brand.';
    } elseif ($contentType === '') {
        $error = 'Please select a prompt type.';
    } elseif ($topic === '') {
        $error = 'Please enter a topic.';
    } elseif ($brandConfig === []) {
        $error = 'The selected brand could not be loaded.';
    } else {
        $goalLine = $goal !== '' ? "Primary goal: {$goal}" : "Primary goal: Create useful, structured content.";
        $audienceLine = $audience !== '' ? "Target audience: {$audience}" : "Target audience: People who need clarity, simplicity, and practical guidance.";
        $notesLine = $notes !== '' ? "Additional notes: {$notes}" : "Additional notes: Keep the content aligned with the brand and avoid unnecessary filler.";

        $commonContext = <<<TEXT
Brand: {$brandName}
Tone: {$brandTone}
Voice notes: {$voiceNotes}
Default author: {$author}
Template direction: {$template}
Topic: {$topic}
{$goalLine}
{$audienceLine}
{$notesLine}
TEXT;

        switch ($contentType) {
            case 'article':
                $generatedPrompt = <<<TEXT
Write a high-quality long-form article for {$brandName}.

{$commonContext}

Instructions:
- Write with a {$brandTone} tone.
- Follow these voice notes: {$voiceNotes}
- Make the article clear, useful, and structured.
- Use a strong headline.
- Include a brief introduction that connects with the reader's problem.
- Organize the content with helpful subheadings.
- Keep paragraphs readable and natural.
- Avoid hype, fluff, and exaggerated claims.
- End with a grounded conclusion or next step.

Output format:
- Title
- Meta description
- Suggested slug
- Article excerpt
- Full article body
TEXT;
                break;

            case 'image':
                $generatedPrompt = <<<TEXT
Create a premium editorial-style image prompt for {$brandName}.

{$commonContext}

Instructions:
- Generate an image concept for the topic: {$topic}
- Match the brand tone: {$brandTone}
- Reflect these voice notes visually: {$voiceNotes}
- Keep the composition clean, modern, and usable for website content.
- Avoid clutter.
- Make the image suitable for article headers, social posts, or promotional use.
- Include mood, setting, composition, lighting, and design direction.
- Make it feel aligned with the {$template} template style.

Output format:
- Image title
- Visual concept
- Full AI image prompt
- Suggested use cases
TEXT;
                break;

            case 'product':
                $generatedPrompt = <<<TEXT
Create a digital product concept for {$brandName} based on this topic.

{$commonContext}

Instructions:
- Generate a product idea connected to the topic: {$topic}
- Keep it aligned with the brand tone: {$brandTone}
- Reflect these voice notes: {$voiceNotes}
- Focus on usefulness, clarity, and transformation.
- Suggest a product that could become a PDF, guide, workbook, planner, checklist, mini-course, or email series.
- Make the concept realistic and valuable.
- Avoid generic filler ideas.

Output format:
- Product title
- Product type
- Core promise
- Problem it solves
- Transformation it offers
- Suggested sections or modules
- Optional upsell or companion content
TEXT;
                break;

            case 'email':
                $generatedPrompt = <<<TEXT
Write an email prompt for {$brandName}.

{$commonContext}

Instructions:
- Write an email that matches the brand tone: {$brandTone}
- Follow these voice notes: {$voiceNotes}
- Make it feel personal, clear, and useful.
- Avoid hype and spammy language.
- Build around the topic: {$topic}
- Keep the goal front and center.
- Include a subject line and preview text.
- End with a natural call to action.

Output format:
- Subject line
- Preview text
- Email body
- CTA suggestion
TEXT;
                break;

            case 'social':
                $generatedPrompt = <<<TEXT
Create a social media content prompt for {$brandName}.

{$commonContext}

Instructions:
- Generate a social post based on the topic: {$topic}
- Match the brand tone: {$brandTone}
- Follow these voice notes: {$voiceNotes}
- Keep it concise, clear, and emotionally honest.
- Avoid sounding generic or overly promotional.
- Make it suitable for platforms like Pinterest, Facebook, Instagram, or X.
- Include a hook, body, and CTA if appropriate.

Output format:
- Short caption
- Longer caption
- Hook ideas
- CTA ideas
- Optional image text overlay ideas
TEXT;
                break;

            default:
                $error = 'Unsupported prompt type selected.';
                break;
        }

        if ($generatedPrompt !== '' && $savePrompt && $error === '') {
            $promptsFile = dirname(__DIR__) . '/brands/' . $selectedBrand . '/_data/prompts.json';
            $existingPrompts = load_json_file_safe($promptsFile);

            $record = [
                'id' => uniqid('prompt_', true),
                'brand' => $selectedBrand,
                'brand_name' => $brandName,
                'type' => $contentType,
                'topic' => $topic,
                'goal' => $goal,
                'audience' => $audience,
                'notes' => $notes,
                'prompt' => $generatedPrompt,
                'created_at' => date('c'),
            ];

            $existingPrompts[] = $record;

            if (save_json_file_safe($promptsFile, $existingPrompts)) {
                $success = 'Prompt generated and saved successfully.';
            } else {
                $error = 'Prompt was generated, but could not be saved to prompts.json.';
            }
        } elseif ($generatedPrompt !== '' && $error === '') {
            $success = 'Prompt generated successfully.';
        }
    }
}

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/nav.php';
?>

<main class="hub-main">
    <section class="hub-hero">
        <div class="hub-hero__content">
            <span class="hub-kicker">Content Engine</span>
            <h1>Prompt Generator</h1>
            <p>
                Generate structured, brand-aware prompts for articles, images, products, emails, and social content.
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
                        <label for="content_type">Prompt Type</label>
                        <select id="content_type" name="content_type" required>
                            <option value="article" <?= $contentType === 'article' ? 'selected' : '' ?>>Article</option>
                            <option value="image" <?= $contentType === 'image' ? 'selected' : '' ?>>Image</option>
                            <option value="product" <?= $contentType === 'product' ? 'selected' : '' ?>>Product</option>
                            <option value="email" <?= $contentType === 'email' ? 'selected' : '' ?>>Email</option>
                            <option value="social" <?= $contentType === 'social' ? 'selected' : '' ?>>Social Post</option>
                        </select>
                    </div>

                    <div class="hub-form__group">
                        <label for="topic">Topic</label>
                        <input id="topic" name="topic" type="text" value="<?= h($topic) ?>" placeholder="Example: What is OMAD?" required>
                    </div>

                    <div class="hub-form__group">
                        <label for="goal">Goal</label>
                        <input id="goal" name="goal" type="text" value="<?= h($goal) ?>" placeholder="Example: Explain simply for beginners">
                    </div>

                    <div class="hub-form__group">
                        <label for="audience">Audience</label>
                        <input id="audience" name="audience" type="text" value="<?= h($audience) ?>" placeholder="Example: Adults over 40 trying to simplify weight loss">
                    </div>

                    <div class="hub-form__group">
                        <label for="notes">Additional Notes</label>
                        <textarea id="notes" name="notes" rows="5" placeholder="Anything else the prompt should reflect..."><?= h($notes) ?></textarea>
                    </div>

                    <div class="hub-form__group">
                        <label class="hub-checkbox">
                            <input type="checkbox" name="save_prompt" value="1" <?= $savePrompt ? 'checked' : '' ?>>
                            <span>Save this prompt to the brand's prompts.json file</span>
                        </label>
                    </div>

                    <div class="hub-form__actions">
                        <button class="hub-btn hub-btn--primary" type="submit">Generate Prompt</button>
                        <a class="hub-btn hub-btn--secondary" href="/hub/">Back to Hub</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

   <?php if ($generatedPrompt !== ''): ?>
    <section class="hub-section">
        <div class="hub-section__head">
            <h2>Generated Prompt</h2>
            <p>This output is brand-aware and ready to use in your content workflow.</p>
        </div>

        <div class="hub-panel">
            <div class="hub-panel__content">
                <div class="hub-output-actions">
                    <button
                        class="hub-btn hub-btn--primary js-copy-prompt"
                        type="button"
                        data-copy-target="generated-prompt-output"
                    >
                        Copy Prompt
                    </button>

                    <a
                        class="hub-btn hub-btn--secondary"
                        href="<?= h($foundryPressGptUrl ?? '#') ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Open FoundryPress GPT
                    </a>
                </div>

                <textarea
                    id="generated-prompt-output"
                    class="hub-prompt-output"
                    rows="22"
                    readonly
                ><?= h($generatedPrompt) ?></textarea>
            </div>
        </div>
    </section>
<?php endif; ?>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>