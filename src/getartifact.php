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
WebPage::singleton()->onlyForLogged();

$artifactID = WebPage::singleton()->getRequestValue('id', 'int');
$artifactor = new \MultiFlexi\Artifact($artifactID, ['autoload' => true]);

if (!$artifactor->getMyKey()) {
    http_response_code(404);

    exit;
}

$artifactData = stripslashes((string) $artifactor->getDataValue('artifact'));
$contentType = $artifactor->getDataValue('content_type');
$filename = $artifactor->getDataValue('filename');

$quoted = '"'.str_replace(['"', '\\', "\r", "\n"], ['\\"', '\\\\', '', ''], basename((string) $filename)).'"';
header('Content-Description: File Transfer');
header('Content-Type: '.($contentType !== null && $contentType !== '' ? $contentType : 'application/octet-stream'));
header('Content-Disposition: attachment; filename='.$quoted);
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: '.\strlen($artifactData));
echo $artifactData;
