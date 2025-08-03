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

$runtemplate_id = \Ease\TWB4\WebPage::getRequestValue('runtemplate', 'int');
$interval = \Ease\TWB4\WebPage::getRequestValue('interval');
$state = $interval !== 'n';

$result = false;

if (null !== $runtemplate_id) {
    $switcher = new \MultiFlexi\RunTemplate();
    $switcher->setData(['id' => $runtemplate_id, 'crontab' => $interval]);
    http_response_code($switcher->setState($state) ? 201 : 400);
} else {
    http_response_code(404);
}
