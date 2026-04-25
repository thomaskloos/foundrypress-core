<?php
declare(strict_types=1);

$footerSiteName = '';

if (isset($siteName) && trim((string) $siteName) !== '') {
    $footerSiteName = trim((string) $siteName);
} elseif (defined('FP_SITE_NAME')) {
    $footerSiteName = trim((string) FP_SITE_NAME);
}
?>
<footer class="site-footer">
    <div class="site-footer__inner">
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

</div>
</footer>
</div>
</body>
</html>