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

$app_id = \Ease\TWB4\WebPage::getRequestValue('app');
$company_id = \Ease\TWB4\WebPage::getRequestValue('company');
$interval = \Ease\TWB4\WebPage::getRequestValue('interval');
$state = $interval !== 'n';

$result = false;

if (null !== $app_id && null !== $company_id) {
    $switcher = new \MultiFlexi\RunTemplate();
    $switcher->setData(['app_id' => (int) $app_id, 'company_id' => (int) $company_id, 'interv' => $interval]);

    if ($switcher->setState($state)) {
        $switcher->performInit();
        $result = 201;
    } else {
        $result = 400;
    }

    http_response_code($result);
} else {
    http_response_code(404);
}
