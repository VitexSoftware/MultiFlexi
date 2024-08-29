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

use Ease\TWB4\LinkButton;
use Ease\WebPage;
use MultiFlexi\Application;
use MultiFlexi\Company;
use MultiFlexi\RunTemplate;

require_once './init.php';
$oPage->onlyForLogged();

$runTemplate = new RunTemplate(WebPage::getRequestValue('id', 'int'));
$companies = new Company($runTemplate->getDataValue('company_id'));
$app = new Application($runTemplate->getDataValue('app_id'));

$app->setDataValue('company_id', $companies->getMyKey());
$app->setDataValue('app_id', $app->getMyKey());
$app->setDataValue('app_name', $app->getRecordName());

$configurator = new \MultiFlexi\Configuration([
    'runtemplate_id' => $runTemplate->getMyKey(),
    'app_id' => $app->getMyKey(),
    'company_id' => $companies->getMyKey(),
], ['autoload' => false]);

if ($oPage->isPosted()) {
    if ($configurator->takeData($_POST) && null !== $configurator->saveToSQL()) {
        $configurator->addStatusMessage(_('Config fields Saved'), 'success');
    } else {
        $configurator->addStatusMessage(_('Error saving Config fields'), 'error');
    }
}

$oPage->addItem(new PageTop($runTemplate->getRecordName().' '._('Configuration')));

$appPanel = new ApplicationPanel($app, new RunTemplatePanel($runTemplate));
$appPanel->headRow->addColumn(2, new LinkButton('periodbehaviour.php?id='.$runTemplate->getMyKey(), '🛠️&nbsp;'._('Actions'), 'secondary btn-lg btn-block'));

$oPage->container->addItem(new CompanyPanel($companies, $appPanel));

$oPage->addItem(new PageBottom());
$oPage->draw();
