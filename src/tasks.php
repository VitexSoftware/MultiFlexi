<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Document;
use Ease\Html\ImgTag;
use Ease\Html\SpanTag;
use Ease\TWB4\Panel;
use MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Company Tasks')));
$companies = new Company(Document::getRequestValue('company_id', 'int'));
$oPage->container->addItem(new CompanyPanel($companies, 'default', new \MultiFlexi\Ui\ServicesForCompanyForm($companies, ['id' => 'apptoggle'])));
$oPage->addItem(new PageBottom());
$oPage->draw();
