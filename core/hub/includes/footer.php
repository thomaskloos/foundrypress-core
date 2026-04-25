<?php
declare(strict_types=1);
$license = function_exists('load_license_config') ? load_license_config() : [];
$licensedTo = trim((string) ($license['licensed_to'] ?? ''));
$footerSiteName = '';

if (isset($siteName) && trim((string) $siteName) !== '') {
    $footerSiteName = trim((string) $siteName);
} elseif (defined('FP_SITE_NAME')) {
    $footerSiteName = trim((string) FP_SITE_NAME);
}
?>
<footer class="hub-footer">
    <div class="hub-footer__inner">
        <p>
            <?php if ($footerSiteName !== ''): ?>
                <?= h($footerSiteName) ?> • Powered by
            <?php else: ?>
                Powered by
            <?php endif; ?>

            <a
                href="https://foundrypressapp.com"
                target="_blank"
                rel="noopener noreferrer"
            >
                FoundryPress
            </a>

            <?php if ($licensedTo !== ''): ?>
                • Licensed to <?= h($licensedTo) ?>
            <?php endif; ?>
        </p>
    </div>
</footer>
<script src="<?= h(asset_url('js/hub.js')) ?>"></script>
</div>
</body>
</html>