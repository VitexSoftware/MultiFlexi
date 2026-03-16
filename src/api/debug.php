<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/../../vendor/autoload.php';

\Ease\Shared::singleton()->loadConfig(\dirname(__DIR__).'/../.env', true);

$uri = \Ease\WebPage::getUri();
$uriParts = explode('/api/', $uri);
$basePath = $uriParts[0].'/api';

echo "Full URI: {$uri}\n";
echo 'URI Parts: '.print_r($uriParts, true)."\n";
echo "Base Path: {$basePath}\n";
echo "Expected Path: {$basePath}/VitexSoftware/MultiFlexi/1.0.0/\n";
