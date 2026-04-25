<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

fp_require_hub_login();

$pageTitle  = 'License & Activation • FoundryPress';
$pageDesc   = 'Activate and configure your FoundryPress install.';
$currentUrl = $hubBase . '/config.php';

$license = function_exists('load_license_config') ? load_license_config() : [];

$currentDomain = function_exists('fp_get_current_domain')
    ? fp_get_current_domain()
    : '';

$licensedTo     = trim((string) ($_POST['licensed_to'] ?? ($license['licensed_to'] ?? '')));
$licenseKey     = trim((string) ($_POST['license_key'] ?? ($license['license_key'] ?? '')));
$productVersion = trim((string) ($_POST['product_version'] ?? ($license['product_version'] ?? (defined('FP_VERSION') ? (string) FP_VERSION : '1.0.0'))));

$success = '';
$error   = '';

$licenseErrorCode = trim((string) ($_GET['license_error'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($licenseKey === '') {
        $error = 'Please enter your license key.';
    } else {
        $result = fp_activate_remote_license($licenseKey);

        if (!empty($result['valid'])) {
            $savePayload = [
                'licensed_to'          => trim((string) ($result['licensed_to'] ?? $licensedTo)),
                'license_key'          => strtoupper($licenseKey),
                'product_version'      => $productVersion,
                'is_activated'         => (($result['status'] ?? 'invalid') === 'active'),
                'activated_domain'     => (string) ($result['activated_domain'] ?? $currentDomain),
                'license_plan'         => (string) ($result['plan'] ?? ''),
                'max_domains'          => (int) ($result['max_domains'] ?? 0),
                'status'               => (string) ($result['status'] ?? 'invalid'),
                'message'              => (string) ($result['message'] ?? ''),
                'last_checked_at'      => gmdate('c'),
                'last_check_http_code' => (int) ($result['last_check_http_code'] ?? 200),
                'last_check_result'    => (string) ($result['last_check_result'] ?? 'activation_success'),
                'last_check_message'   => (string) ($result['last_check_message'] ?? ($result['message'] ?? 'License activated successfully.')),
            ];

            $saveOk = function_exists('save_license_config') ? save_license_config($savePayload) : true;

            if ($saveOk) {
                $license = function_exists('load_license_config') ? load_license_config() : $savePayload;

                $licensedTo     = trim((string) ($license['licensed_to'] ?? ''));
                $licenseKey     = trim((string) ($license['license_key'] ?? strtoupper($licenseKey)));
                $productVersion = trim((string) ($license['product_version'] ?? $productVersion));

                $domainsUsed = (int) ($result['domains_used'] ?? 0);
                $maxDomains  = (int) ($result['max_domains'] ?? 0);

                $success = $domainsUsed > 0 && $maxDomains > 0
                    ? 'FoundryPress activated successfully. Domain usage: ' . $domainsUsed . ' / ' . $maxDomains . '.'
                    : (string) ($result['message'] ?? 'FoundryPress activated successfully.');
            } else {
                $error = 'License validated, but local activation details could not be saved.';
            }
        } else {
            $error = (string) ($result['message'] ?? 'Activation failed.');
        }
    }
}

$license = function_exists('load_license_config') ? load_license_config() : $license;
$licenseStatus = fp_get_license_status();

$isActivated = (bool) ($license['is_activated'] ?? (($licenseStatus['status'] ?? 'invalid') === 'active'));
$activatedDomain = trim((string) ($license['activated_domain'] ?? ($licenseStatus['activated_domain'] ?? '')));
$licensePlan = strtolower(trim((string) ($license['license_plan'] ?? ($licenseStatus['plan'] ?? ''))));
$maxDomains = (int) ($license['max_domains'] ?? ($licenseStatus['max_domains'] ?? 0));

$licensedToDisplay = trim((string) ($licenseStatus['licensed_to'] ?? $licensedTo));

$planDisplay = match ($licensePlan) {
    'solo' => 'Solo',
    'studio' => 'Studio',
    'pro' => 'Pro',
    'internal' => 'Internal',
    default => ($licensePlan !== '' ? ucfirst($licensePlan) : '—'),
};

$statusValue = (string) ($licenseStatus['status'] ?? 'invalid');
$statusLabel = ucfirst($statusValue);
$statusClass = match ($statusValue) {
    'active'  => 'fp-license-status--active',
    'revoked' => 'fp-license-status--revoked',
    'expired' => 'fp-license-status--expired',
    default   => 'fp-license-status--invalid',
};

$rawLicenseFile = function_exists('fp_read_license_file') ? fp_read_license_file() : [];
$lastCheckedRaw = (string) ($rawLicenseFile['last_checked_at'] ?? '');
$lastCheckedAt = function_exists('fp_format_license_datetime')
    ? fp_format_license_datetime($lastCheckedRaw)
    : ($lastCheckedRaw !== '' ? $lastCheckedRaw : '—');

$lastCheckResult = (string) ($licenseStatus['last_check_result'] ?? '—');
$lastCheckMessage = (string) ($licenseStatus['last_check_message'] ?? '—');
$lastCheckHttpCode = (string) (($licenseStatus['last_check_http_code'] ?? null) !== null
    ? (string) ($licenseStatus['last_check_http_code'])
    : '—');

$domainsUsedDisplay = $isActivated && $maxDomains > 0 ? '1 / ' . $maxDomains : '—';

$upgradeBaseUrl = 'https://foundrypressapp.com/buy';
$upgradeOptions = [];

if ($licensePlan === 'solo') {
    $upgradeOptions = [
        [
            'name' => 'Studio',
            'sites' => '3 sites',
            'desc' => 'Ideal for multiple installs, growing brands, or a small client setup.',
            'url'  => $upgradeBaseUrl . '?plan=solo-to-studio',
            'cta'  => 'Buy Studio License',
        ],
        [
            'name' => 'Pro',
            'sites' => '10 sites',
            'desc' => 'Best for freelancers, studios, and serious multi-site publishing.',
            'url'  => $upgradeBaseUrl . '?plan=solo-to-pro',
            'cta'  => 'Buy Pro License',
        ],
    ];
} elseif ($licensePlan === 'studio') {
    $upgradeOptions = [
        [
            'name' => 'Pro',
            'sites' => '10 sites',
            'desc' => 'Scale up for client work, larger site portfolios, or long-term growth.',
            'url'  => $upgradeBaseUrl . '?plan=studio-to-pro',
            'cta'  => 'Buy Pro License',
        ],
    ];
}

require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/nav.php';
?>

<main class="hub-main">
    <style>
        .fp-license-card,
        .fp-upgrade-card {
            background: #ffffff;
            border: 1px solid #d9e2ec;
            border-radius: 18px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            margin-top: 1.5rem;
        }

        .fp-license-card__header,
        .fp-upgrade-card__header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .fp-license-card__title,
        .fp-upgrade-card__title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #102a43;
        }

        .fp-license-card__subtitle,
        .fp-upgrade-card__subtitle {
            margin: 0.35rem 0 0;
            color: #486581;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .fp-license-badge,
        .fp-plan-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.8rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .fp-license-status--active {
            background: #ecfdf3;
            color: #027a48;
            border: 1px solid #abefc6;
        }

        .fp-license-status--invalid,
        .fp-license-status--expired,
        .fp-license-status--revoked {
            background: #fef3f2;
            color: #b42318;
            border: 1px solid #fecdca;
        }

        .fp-plan-pill {
            background: #eef4ff;
            color: #1d4ed8;
            border: 1px solid #c7d7fe;
        }

        .fp-license-grid,
        .fp-upgrade-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .fp-license-item {
            background: #f8fbff;
            border: 1px solid #e6edf5;
            border-radius: 14px;
            padding: 0.9rem 1rem;
        }

        .fp-license-item--full {
            grid-column: 1 / -1;
        }

        .fp-license-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #627d98;
            margin-bottom: 0.35rem;
        }

        .fp-license-value {
            display: block;
            font-size: 0.98rem;
            color: #102a43;
            line-height: 1.5;
            word-break: break-word;
        }

        .fp-activation-summary {
            background: #f8fbff;
            border: 1px solid #e6edf5;
            border-radius: 16px;
            padding: 1rem 1.1rem;
            margin-bottom: 1rem;
        }

        .fp-activation-summary p {
            margin: 0.35rem 0;
        }

        .fp-upgrade-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 1rem;
        }

        .fp-upgrade-option {
            background: #f8fbff;
            border: 1px solid #e6edf5;
            border-radius: 16px;
            padding: 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .fp-upgrade-option__title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #102a43;
        }

        .fp-upgrade-option__meta {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1d4ed8;
        }

        .fp-upgrade-option__desc {
            margin: 0;
            color: #486581;
            line-height: 1.6;
        }

        .fp-upgrade-option__actions {
            margin-top: auto;
        }

        .fp-upgrade-note {
            margin-top: 0.75rem;
            color: #627d98;
            line-height: 1.6;
        }

        @media (max-width: 700px) {
            .fp-license-card__header,
            .fp-upgrade-card__header {
                flex-direction: column;
                align-items: flex-start;
            }

            .fp-license-grid,
            .fp-upgrade-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="hub-hero">
        <div class="hub-hero__content">
            <span class="hub-kicker">License</span>
            <h1>License & Activation</h1>
            <p>Activate your license, manage your plan, and verify your FoundryPress install.</p>
        </div>
    </section>

    <section class="hub-section">
        <div class="hub-panel">
            <div class="hub-panel__content">

                <?php if ($licenseErrorCode !== ''): ?>
                    <div class="hub-alert hub-alert--error">
                        <?php if ($licenseErrorCode === 'inactive'): ?>
                            You need to activate FoundryPress before using protected hub features.
                        <?php elseif ($licenseErrorCode === 'domain_mismatch'): ?>
                            This install is activated for a different domain. Please revalidate your license.
                        <?php else: ?>
                            A valid license is required to continue.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success !== ''): ?>
                    <div class="hub-alert hub-alert--success"><?= h($success) ?></div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="hub-alert hub-alert--error"><?= h($error) ?></div>
                <?php endif; ?>

                <div class="fp-activation-summary">
                    <p><strong>Status:</strong> <?= h($isActivated ? 'Activated' : 'Not Activated') ?></p>
                    <p><strong>Current Domain:</strong> <?= h($currentDomain !== '' ? $currentDomain : '—') ?></p>
                    <p><strong>Activated Domain:</strong> <?= h($activatedDomain !== '' ? $activatedDomain : '—') ?></p>
                    <p><strong>Plan:</strong> <?= h($planDisplay) ?></p>
                    <p><strong>Domains Used:</strong> <?= h($domainsUsedDisplay) ?></p>
                    <p><strong>Max Domains:</strong> <?= $maxDomains > 0 ? h((string) $maxDomains) : '—' ?></p>
                    <p><strong>Version:</strong> <?= h($productVersion) ?></p>
                </div>

                <?php if ($isActivated): ?>
                    <div class="hub-alert hub-alert--info">
                        Need more domains? Purchase a higher plan below and activate your new license key here.
                    </div>
                <?php endif; ?>

                <form class="hub-form" method="post">
                    <div class="hub-form__group">
                        <label for="licensed_to">Licensed To</label>
                        <input
                            id="licensed_to"
                            name="licensed_to"
                            type="text"
                            value="<?= h($licensedTo) ?>"
                            placeholder="Business or customer name"
                        >
                    </div>

                    <div class="hub-form__group">
                        <label for="license_key">License Key</label>
                        <input
                            id="license_key"
                            name="license_key"
                            type="text"
                            value="<?= h($licenseKey) ?>"
                            placeholder="FP-7KQ2-XM4P-9A6D"
                            autocomplete="off"
                        >
                    </div>

                    <div class="hub-form__group">
                        <label for="product_version">Product Version</label>
                        <input
                            id="product_version"
                            name="product_version"
                            type="text"
                            value="<?= h($productVersion) ?>"
                            readonly
                        >
                    </div>

                    <div class="hub-form__actions">
                        <button class="hub-btn hub-btn--primary" type="submit">
                            <?= $isActivated ? 'Revalidate License' : 'Activate License' ?>
                        </button>
                        <a class="hub-btn hub-btn--secondary" href="/hub/">Back to Hub</a>
                    </div>
                </form>

                <div class="fp-license-card">
                    <div class="fp-license-card__header">
                        <div>
                            <h2 class="fp-license-card__title">License Status</h2>
                            <p class="fp-license-card__subtitle">
                                Current activation and validation details for this FoundryPress install.
                            </p>
                        </div>
                        <span class="fp-license-badge <?= h($statusClass) ?>">
                            <?= h($statusLabel) ?>
                        </span>
                    </div>

                    <div class="fp-license-grid">
                        <div class="fp-license-item">
                            <span class="fp-license-label">Message</span>
                            <span class="fp-license-value"><?= h((string) ($licenseStatus['message'] ?? '—')) ?></span>
                        </div>

                        <div class="fp-license-item">
                            <span class="fp-license-label">Licensed To</span>
                            <span class="fp-license-value"><?= h($licensedToDisplay !== '' ? $licensedToDisplay : '—') ?></span>
                        </div>

                        <div class="fp-license-item">
                            <span class="fp-license-label">Plan</span>
                            <span class="fp-license-value"><?= h((string) ($planDisplay)) ?></span>
                        </div>

                        <div class="fp-license-item">
                            <span class="fp-license-label">Max Domains</span>
                            <span class="fp-license-value"><?= h((string) (($licenseStatus['max_domains'] ?? null) !== null ? $licenseStatus['max_domains'] : '—')) ?></span>
                        </div>

                        <div class="fp-license-item">
                            <span class="fp-license-label">Current Domain</span>
                            <span class="fp-license-value"><?= h($currentDomain !== '' ? $currentDomain : '—') ?></span>
                        </div>

                        <div class="fp-license-item">
                            <span class="fp-license-label">Activated Domain</span>
                            <span class="fp-license-value"><?= h((string) ($licenseStatus['activated_domain'] ?? '—')) ?></span>
                        </div>

                        <div class="fp-license-item">
                            <span class="fp-license-label">Last Checked</span>
                            <span class="fp-license-value"><?= h($lastCheckedAt) ?></span>
                        </div>

                        <div class="fp-license-item">
                            <span class="fp-license-label">Last Check HTTP Code</span>
                            <span class="fp-license-value"><?= h($lastCheckHttpCode) ?></span>
                        </div>

                        <div class="fp-license-item">
                            <span class="fp-license-label">Last Check Result</span>
                            <span class="fp-license-value"><?= h($lastCheckResult !== '' ? $lastCheckResult : '—') ?></span>
                        </div>

                        <div class="fp-license-item fp-license-item--full">
                            <span class="fp-license-label">Last Check Message</span>
                            <span class="fp-license-value"><?= h($lastCheckMessage !== '' ? $lastCheckMessage : '—') ?></span>
                        </div>
                    </div>
                </div>

                <?php if ($isActivated && !empty($upgradeOptions)): ?>
                    <div class="fp-upgrade-card">
                        <div class="fp-upgrade-card__header">
                            <div>
                                <h2 class="fp-upgrade-card__title">Need More Sites?</h2>
                                <p class="fp-upgrade-card__subtitle">
                                    Purchase a higher plan to unlock more domain activations. After purchase, you will receive a new upgraded license key.
                                </p>
                            </div>
                            <span class="fp-plan-pill">
                                Current Plan: <?= h($planDisplay) ?>
                            </span>
                        </div>

                        <div class="fp-upgrade-grid">
                            <?php foreach ($upgradeOptions as $option): ?>
                                <div class="fp-upgrade-option">
                                    <div>
                                        <h3 class="fp-upgrade-option__title"><?= h($option['name']) ?></h3>
                                        <div class="fp-upgrade-option__meta"><?= h($option['sites']) ?></div>
                                    </div>

                                    <p class="fp-upgrade-option__desc"><?= h($option['desc']) ?></p>

                                    <div class="fp-upgrade-option__actions">
                                        <a
                                            class="hub-btn hub-btn--primary"
                                            href="<?= h($option['url']) ?>"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            <?= h($option['cta']) ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <p class="fp-upgrade-note">
                            After purchase, your upgraded license key will be issued manually. Return here and activate your new key to apply the upgrade.
                        </p>
                    </div>
                <?php elseif ($isActivated && in_array($licensePlan, ['pro', 'internal'], true)): ?>
                    <div class="fp-upgrade-card">
                        <div class="fp-upgrade-card__header">
                            <div>
                                <h2 class="fp-upgrade-card__title">Top Plan Active</h2>
                                <p class="fp-upgrade-card__subtitle">
                                    You are currently on the highest available FoundryPress plan.
                                </p>
                            </div>
                            <span class="fp-plan-pill">
                                Current Plan: <?= h($planDisplay) ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </section>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>