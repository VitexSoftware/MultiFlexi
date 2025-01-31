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

$quoted = basename($artifactor->getDataValue('filename'));
header('Content-Description: File Transfer');
header('Content-Type: '.$artifactor->getDataValue('content_type') ?? 'application/octet-stream');
header('Content-Disposition: attachment; filename="'.$quoted.'"');
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: '.mb_strlen(stripslashes($artifactor->getDataValue('artifact'))));
echo stripslashes($artifactor->getDataValue('artifact'));
