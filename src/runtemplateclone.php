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

use Ease\WebPage;
use MultiFlexi\RunTemplate;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

$runTemplate = new RunTemplate(WebPage::getRequestValue('id', 'int'));
$originalEnv = $runTemplate->getRuntemplateEnvironment();
$cloneName = \Ease\TWB5\WebPage::getRequestValue('clonename');

$runTemplate->unsetDataValue($runTemplate->getKeyColumn());
$runTemplate->setDataValue('name', $cloneName);

try {
    $cloneId = $runTemplate->insertToSQL();

    $configurator = new \MultiFlexi\Configuration([
        'runtemplate_id' => $runTemplate->getMyKey(),
        'app_id' => $runTemplate->getDataValue('app_id'),
        'company_id' => $runTemplate->getDataValue('company_id'),
    ], ['autoload' => false]);

    if ($configurator->takeData(\MultiFlexi\Environmentor::flatEnv($originalEnv)) && null !== $configurator->saveToSQL()) {
        $configurator->addStatusMessage(_('Config fields Saved'), 'success');
    } else {
        $configurator->addStatusMessage(_('Error saving Config fields'), 'error');
    }

    WebPage::singleton()->redirect('runtemplate.php?id='.$cloneId);
} catch (Exception $exc) {
    WebPage::singleton()->addItem(new PageTop(_('Runtemplate Clone')));
    WebPage::singleton()->addStatusMessage(_('Error creating runtemplate clone'), 'error');
    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();
}
