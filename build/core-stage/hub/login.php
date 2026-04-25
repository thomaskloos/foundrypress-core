<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (!fp_hub_is_configured()) {
    header('Location: /hub/setup-password.php');
    exit;
}

if (fp_hub_is_logged_in()) {
    header('Location: /hub/');
    exit;
}

$error = '';
$redirect = trim((string) ($_GET['redirect'] ?? $_POST['redirect'] ?? '/hub/'));
$redirect = $redirect !== '' ? $redirect : '/hub/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (fp_hub_login($username, $password)) {
        header('Location: ' . $redirect);
        exit;
    }

    $error = 'Invalid username or password.';
}

$pageTitle = 'Hub Login • FoundryPress';
$pageDesc  = 'Login required to access the FoundryPress Hub.';
$currentUrl = $baseUrl . '/hub/login.php';

require __DIR__ . '/includes/head.php';
?>

<main class="site-main">
    <section class="page-section">
        <div class="container" style="max-width: 520px;">
            <div class="hub-panel" style="padding: 2rem;">
                <span class="hub-kicker">Protected Area</span>
                <h1 style="margin-top: 0.5rem;">Hub Login</h1>
                <p>Enter your username and password to access the FoundryPress Hub.</p>

                <?php if ($error !== ''): ?>
                    <div class="hub-alert hub-alert--error"><?= h($error) ?></div>
                <?php endif; ?>

                <form method="post" class="hub-form" style="margin-top: 1rem;">
                    <input type="hidden" name="redirect" value="<?= h($redirect) ?>">

                    <div class="hub-form__group">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" autocomplete="username" required>
                    </div>

                    <div class="hub-form__group">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>

                    <div class="hub-form__actions">
                        <button class="hub-btn hub-btn--primary" type="submit">Log In</button>
                        <a class="hub-btn hub-btn--secondary" href="/">Back to Site</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>