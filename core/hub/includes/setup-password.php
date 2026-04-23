<?php
declare(strict_types=1);

require __DIR__ . '/../includes/config.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/auth.php';

$configFile = __DIR__ . '/includes/auth-config.php';

$success = '';
$error = '';

$currentConfigured = fp_hub_is_configured();

$username = trim((string) ($_POST['username'] ?? 'admin'));
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
$overwrite = !empty($_POST['overwrite']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($currentConfigured && !$overwrite) {
        $error = 'Hub login is already configured. Check the overwrite box to replace it.';
    } elseif ($username === '') {
        $error = 'Please enter a username.';
    } elseif ($password === '') {
        $error = 'Please enter a password.';
    } elseif (strlen($password) < 10) {
        $error = 'Please use a password at least 10 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($hash === false) {
            $error = 'Could not generate password hash.';
        } else {
            $usernameExport = var_export($username, true);
            $hashExport = var_export($hash, true);

            $content = <<<PHP
<?php
declare(strict_types=1);

if (!defined('FP_HUB_USERNAME')) {
    define('FP_HUB_USERNAME', {$usernameExport});
}

if (!defined('FP_HUB_PASSWORD_HASH')) {
    define('FP_HUB_PASSWORD_HASH', {$hashExport});
}

if (!defined('FP_HUB_SESSION_KEY')) {
    define('FP_HUB_SESSION_KEY', 'foundrypress_hub_auth');
}
PHP;

            $written = file_put_contents($configFile, $content, LOCK_EX);

            if ($written === false) {
                $error = 'Could not write auth-config.php. Check file permissions.';
            } else {
                $success = 'Hub login saved successfully. You can now log in at /hub/login.php. For security, delete /hub/setup-password.php after setup.';
                $currentConfigured = true;
            }
        }
    }
}

$pageTitle = 'Set Hub Password • FoundryPress';
$pageDesc  = 'Create the Hub login for this FoundryPress install.';
$currentUrl = $baseUrl . '/hub/setup-password.php';

require __DIR__ . '/../includes/head.php';
?>

<main class="site-main">
    <section class="page-section">
        <div class="container" style="max-width: 560px;">
            <div class="hub-panel" style="padding: 2rem;">
                <span class="hub-kicker">One-Time Setup</span>
                <h1 style="margin-top: 0.5rem;">Create Hub Login</h1>
                <p>Set the username and password required to access the FoundryPress Hub.</p>

                <?php if ($success !== ''): ?>
                    <div class="hub-alert hub-alert--success"><?= h($success) ?></div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="hub-alert hub-alert--error"><?= h($error) ?></div>
                <?php endif; ?>

                <?php if ($currentConfigured): ?>
                    <div class="hub-alert" style="margin-top: 1rem; background: #f8fbff; border: 1px solid #d9e2ec; border-radius: 12px; padding: 1rem;">
                        Hub login is already configured.
                    </div>
                <?php endif; ?>

                <form method="post" class="hub-form" style="margin-top: 1rem;">
                    <div class="hub-form__group">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" value="<?= h($username) ?>" required>
                    </div>

                    <div class="hub-form__group">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" required>
                    </div>

                    <div class="hub-form__group">
                        <label for="confirm_password">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" required>
                    </div>

                    <?php if ($currentConfigured): ?>
                        <div class="hub-form__group">
                            <label style="display:flex; gap:0.5rem; align-items:center;">
                                <input type="checkbox" name="overwrite" value="1">
                                <span>Overwrite existing Hub login</span>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="hub-form__actions">
                        <button class="hub-btn hub-btn--primary" type="submit">Save Hub Login</button>
                        <a class="hub-btn hub-btn--secondary" href="/hub/login.php">Go to Login</a>
                    </div>
                </form>

                <div style="margin-top: 1.25rem; color:#627d98; font-size:0.95rem; line-height:1.6;">
                    <strong>Important:</strong> After you create the password, delete <code>/hub/setup-password.php</code> from the server.
                </div>
            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>