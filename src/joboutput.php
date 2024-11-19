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

$jobID = WebPage::singleton()->getRequestValue('id', 'int');
$mode = WebPage::singleton()->getRequestValue('mode');
$jobber = new \MultiFlexi\Job($jobID);

$output = $jobber->getDataValue($mode === 'err' ? 'stderr' : 'stdout');
$quoted = sprintf('"job-%s"', $jobber->getMyKey().'-'.str_replace(' ', '_', $jobber->application->getRecordName()).'.'.($mode === 'err' ? 'stderr' : 'stdout').'.txt');
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.$quoted);
header('Content-Transfer-Encoding: binary');
header('Connection: Keep-Alive');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: '.mb_strlen($output));
echo $output;
