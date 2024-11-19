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

use MultiFlexi\Configuration;

/**
 * MultiFlexi - Config fields editor.
 *
 * @deprecated since version 1.14
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

require_once './init.php';
WebPage::singleton()->onlyForLogged();
WebPage::singleton()->addItem(new PageTop(_('App custom config Fields')));
$appId = WebPage::getRequestValue('app_id', 'int');
$companyId = WebPage::getRequestValue('company_id', 'int');

$configurator = new Configuration(['app_id' => $appId, 'company_id' => $companyId], ['autoload' => false]);
$configurator->setDataValue('app_id', $appId);

if (WebPage::singleton()->isPosted()) {
    if ($configurator->takeData($_POST) && null !== $configurator->saveToSQL()) {
        $configurator->addStatusMessage(_('Config fields Saved'), 'success');
    } else {
        $configurator->addStatusMessage(
            _('Error saving Config fields'),
            'error',
        );
    }
}

$app = new \MultiFlexi\Application($appId);
$company = new \MultiFlexi\Company($companyId);
$runTemplater = new \MultiFlexi\RunTemplate();
$runTemplater->loadFromSQL($runTemplater->runTemplateID($appId, $companyId));

$appPanel = new ApplicationPanel($app, new CustomAppConfigForm($configurator));

$runTemplateButton = new \Ease\TWB4\LinkButton('runtemplate.php?id='.$runTemplater->getMyKey(), '⚗️&nbsp;'._('Run Template'), 'dark btn-lg btn-block');
$appPanel->headRow->addColumn(2, $runTemplateButton);

WebPage::singleton()->container->addItem(new CompanyPanel($company, $appPanel));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
