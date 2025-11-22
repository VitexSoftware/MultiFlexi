<?php

require_once __DIR__.'/../../vendor/autoload.php';

\Ease\Shared::singleton()->loadConfig(\dirname(__DIR__).'/../.env', true);

$uri = \Ease\WebPage::getUri();
$uriParts = explode('/api/', $uri);
$basePath = $uriParts[0].'/api';

echo "Full URI: $uri\n";
echo "URI Parts: " . print_r($uriParts, true) . "\n";
echo "Base Path: $basePath\n";
echo "Expected Path: {$basePath}/VitexSoftware/MultiFlexi/1.0.0/\n";
