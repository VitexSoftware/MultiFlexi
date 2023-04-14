<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Company Tasks')));
$companies = new Company(\Ease\Document::getRequestValue('company_id', 'int'));

if(strlen($companies->getDataValue('logo'))) {
      $companyTasksHeading[] =  new \Ease\Html\ImgTag($companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid']);
}
$companyTasksHeading[] = $companies->getDataValue('nazev').'&nbsp;';
$companyTasksHeading[] = _('Assigned applications');

$oPage->container->addItem(new Panel( $companyTasksHeading  , 'default', new ServicesForCompanyForm($companies, ['id' => 'apptoggle'])));
$oPage->addItem(new PageBottom());
$oPage->draw();
