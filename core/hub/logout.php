<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

fp_hub_logout();
header('Location: /hub/login.php');
exit;
