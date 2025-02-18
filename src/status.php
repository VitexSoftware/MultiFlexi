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

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

$jobber = new \MultiFlexi\Job();

$width = 500;
$height = 500;

$todaysJobs = $jobber->listingQuery()->select('exitcode', true)->limit($width * $height)->orderBy('id')->fetchAll();

$jobGraph = new JobGraph($width, $height, $todaysJobs);
$jobGraph->generateImage();
$base64Image = $jobGraph->getBase64Image();

$imageTag = new \Ease\Html\ImgTag('data:image/png;base64,'.$base64Image, 'Job Success/Failure Graph', ['width' => $width, 'height' => $height]);

// Calculate percentages
$totalJobs = $jobGraph->getTotalJobs();
$successCount = $jobGraph->getSuccessCount();
$failureCount = $jobGraph->getFailureCount();
$noExecutableCount = $jobGraph->getNoExecutableCount();
$exceptionCount = $jobGraph->getExceptionCount();
$waitingCount = $jobGraph->getWaitingCount();
$successPercentage = ($totalJobs > 0) ? ($successCount / $totalJobs) * 100 : 0;
$failurePercentage = ($totalJobs > 0) ? ($failureCount / $totalJobs) * 100 : 0;
$noExecutablePercentage = ($totalJobs > 0) ? ($noExecutableCount / $totalJobs) * 100 : 0;
$exceptionPercentage = ($totalJobs > 0) ? ($exceptionCount / $totalJobs) * 100 : 0;
$waitingPercentage = ($totalJobs > 0) ? ($waitingCount / $totalJobs) * 100 : 0;

// Create the legend
$legend = new \Ease\Html\DivTag(
    [
        new \Ease\Html\SpanTag('■', ['style' => 'color: rgb(0, 255, 0);']).' Success: '.$successCount.' ('.number_format($successPercentage, 2).'%)',
        new \Ease\Html\SpanTag('■', ['style' => 'color: rgb(255, 0, 0); margin-left: 10px;']).' Failure: '.$failureCount.' ('.number_format($failurePercentage, 2).'%)',
        new \Ease\Html\SpanTag('■', ['style' => 'color: rgb(255, 255, 0); margin-left: 10px;']).' No Executable: '.$noExecutableCount.' ('.number_format($noExecutablePercentage, 2).'%)',
        new \Ease\Html\SpanTag('■', ['style' => 'color: rgb(0, 0, 0); margin-left: 10px;']).' Exception: '.$exceptionCount.' ('.number_format($exceptionPercentage, 2).'%)',
        new \Ease\Html\SpanTag('■', ['style' => 'color: rgb(0, 0, 255); margin-left: 10px;']).' Waiting: '.$waitingCount.' ('.number_format($waitingPercentage, 2).'%)',
        new \Ease\Html\DivTag('Total Jobs: '.$totalJobs, ['style' => 'margin-top: 10px;']),
    ],
    ['style' => 'margin-top: 20px;'],
);

WebPage::singleton()->container->addItem($imageTag);
WebPage::singleton()->container->addItem($legend);

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
