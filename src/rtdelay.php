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

/**
 * Convert a time string (hh:mm:ss) to the number of seconds.
 */
function timeStringToSeconds(string $timeString): int
{
    $parts = explode(':', $timeString);
    $seconds = 0;

    if (\count($parts) === 3) {
        $seconds += (int) $parts[0] * 3600; // hours to seconds
        $seconds += (int) $parts[1] * 60;   // minutes to seconds
        $seconds += (int) $parts[2];        // seconds
    } elseif (\count($parts) === 2) {
        $seconds += (int) $parts[0] * 60;   // minutes to seconds
        $seconds += (int) $parts[1];        // seconds
    } elseif (\count($parts) === 1) {
        $seconds += (int) $parts[0];        // seconds
    }

    return $seconds;
}

$runtemplate_id = \Ease\TWB4\WebPage::getRequestValue('runtemplate', 'int');
$delay = \Ease\TWB4\WebPage::getRequestValue('delay');
$state = $delay !== 'n';

$result = false;

if (null !== $runtemplate_id) {
    $delayInSeconds = timeStringToSeconds($delay);
    $switcher = new \MultiFlexi\RunTemplate();
    $switcher->setData(['id' => $runtemplate_id, 'delay' => $delayInSeconds]);
    http_response_code($switcher->dbsync() ? 201 : 400);
} else {
    http_response_code(404);
}
