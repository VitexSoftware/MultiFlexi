<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use \Ease\Document,
    \Ease\Html\ImgTag,
    \Ease\Html\SpanTag,
    \Ease\TWB4\Panel,
    \MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Company Tasks')));
$companies = new Company(Document::getRequestValue('company_id', 'int'));
if (strlen($companies->getDataValue('logo'))) {
    $companyTasksHeading[] = new ImgTag($companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid']);
}
$companyTasksHeading[] = new SpanTag($companies->getDataValue('nazev') . '&nbsp;', ['style' => 'font-size: xxx-large;']);
$companyTasksHeading[] = _('Assigned applications');
$oPage->container->addItem(new Panel($companyTasksHeading, 'default', new \MultiFlexi\Ui\ServicesForCompanyForm($companies, ['id' => 'apptoggle'])));
$oPage->addItem(new PageBottom());
$oPage->draw();
