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

use Ease\Document;
use MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Company Tasks')));
$companies = new Company(Document::getRequestValue('company_id', 'int'));
$oPage->container->addItem(new CompanyPanel($companies, new \MultiFlexi\Ui\ServicesForCompanyForm($companies, ['id' => 'apptoggle'])));
$oPage->addItem(new PageBottom());
$oPage->draw();
