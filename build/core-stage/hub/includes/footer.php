<?php
declare(strict_types=1);
$license = function_exists('load_license_config') ? load_license_config() : [];
$licensedTo = trim((string) ($license['licensed_to'] ?? ''));
?>
<footer class="hub-footer">
    <div class="hub-footer__inner">
        <p>
            FoundryPress Hub • Demo and testing environment
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