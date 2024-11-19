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
WebPage::singleton()->onlyForLogged();
WebPage::singleton()->addItem(new PageTop(_('Company Tasks')));
$companies = new Company(Document::getRequestValue('company_id', 'int'));
WebPage::singleton()->container->addItem(new CompanyPanel($companies, new \MultiFlexi\Ui\ServicesForCompanyForm($companies, ['id' => 'apptoggle'])));
WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
