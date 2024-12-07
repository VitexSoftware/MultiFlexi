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

use MultiFlexi\Company;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

$companies = new Company();
WebPage::singleton()->addItem(new PageTop(_('Company list')));

$companyTable = new \Ease\TWB4\Table();

foreach ($companies->listingQuery() as $companyInfo) {
    $companies->setData($companyInfo);
    $companyId = $companyInfo['id'];
    //    $companyColumns['enabled'] = new \Ease\Html\Widgets\SemaforLight($companyInfo['enabled'] === 1 ? 'green' : 'red', ['width' => 20]);
    $companyColumns['logo'] = new CompanyLinkButton($companies, ['height' => 64]);
    $companyColumns['name'] = new \Ease\Html\ATag('company.php?id='.$companyId, $companyInfo['name']);
    $companyColumns['ic'] = $companyInfo['ic'];

    $companyColumns['setup'] = new \Ease\TWB4\LinkButton('companiesetup.php?id='.$companyId, '🛠️&nbsp;'._('Setup'), 'secondary btn-lg btn-block ');
    $companyColumns['tasks'] = new \Ease\TWB4\LinkButton('tasks.php?company_id='.$companyId, '🔧&nbsp;'._('Tasks'), 'secondary btn-lg btn-block');
    $companyColumns['apps'] = new \Ease\TWB4\LinkButton('companyapps.php?company_id='.$companyId, '📌&nbsp;'._('Applications'), 'secondary btn-lg btn-block');
    $companyColumns['delete'] = new \Ease\TWB4\LinkButton('companydelete.php?id='.$companyId, '☠️&nbsp;'._('Delete'), 'danger');

    $companyTable->addRowColumns($companyColumns);
}

WebPage::singleton()->container->addItem($companyTable);

WebPage::singleton()->addItem(new PageBottom('companies'));
WebPage::singleton()->draw();
