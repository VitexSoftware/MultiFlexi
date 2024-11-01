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

namespace MultiFlexi\Ui;

require_once './init.php';

$oPage->onlyForLogged();

header('Cache-Control: max-age=31536000'); // Cache for 1 year
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT'); // Expires in 1 year


$uuid = WebPage::getRequestValue('uuid');

if (file_exists('images/' . $uuid . '.svg')) {
    $contentType = 'image/svg+xml';
    $imageData = file_get_contents('images/' . $uuid . '.svg');
} else {
    $app = new \MultiFlexi\Application();
    $image = $app->listingQuery()->select('image', true)->where('uuid', $uuid)->limit(1)->fetch('image');
// Extract content/type from data URI
    [$contentType, $base64Data] = explode(',', $image);
    [, $contentType] = explode(':', $contentType);

// Convert base64 data to original format
    $imageData = base64_decode($base64Data, true);
}

// Set proper content-type header
header('Content-Type: ' . str_replace(';base64', '', $contentType));

// Send image data to the browser
echo $imageData;
