<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Company Tasks')));
$companyApp = new \MultiFlexi\RunTemplate(\Ease\Document::getRequestValue('id', 'int'));
$appData = $companyApp->getAppInfo();
$companies = new Company($companyApp->getDataValue('company_id'));
if (strlen($companies->getDataValue('logo'))) {
    $companyTasksHeading[] = new \Ease\Html\ImgTag($companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid','style' => 'height']);
}
$companyTasksHeading[] = new \Ease\Html\SpanTag($companies->getDataValue('name') . '&nbsp;', ['style' => 'font-size: xxx-large;']);
$companyTasksHeading[] = _('Assigned applications');
$oPage->container->addItem(new CompanyPanel($companies, new AppRow($appData)));
$oPage->addItem(new PageBottom());
$oPage->draw();
