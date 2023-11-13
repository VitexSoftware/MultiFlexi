<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\Row;
use MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();

$companies = new Company();
$oPage->addItem(new PageTop(_('Company list')));

$companyTable = new \Ease\TWB4\Table();

foreach ($companies->listingQuery() as $companyInfo) {
    $companies->setData($companyInfo);
    $companyId = $companyInfo['id'];
    $companyColumns['enabled'] = new \Ease\ui\SemaforLight($companyInfo['enabled'] == 1 ? 'green' : 'red', ['width' => 20]);
    $companyColumns['logo'] = new CompanyLinkButton($companies, ['height' => 64]);
    $companyColumns['name'] = new \Ease\Html\ATag('company.php?id=' . $companyId, $companyInfo['name']);
    $companyColumns['ic'] = $companyInfo['ic'];

    $companyColumns['setup'] = new \Ease\TWB4\LinkButton('companysetup.php?id=' . $companyId, '🛠️&nbsp;' . _('Setup'), 'secondary btn-lg btn-block ');
    $companyColumns['tasks'] = new \Ease\TWB4\LinkButton('tasks.php?company_id=' . $companyId, '🔧&nbsp;' . _('Tasks'), 'secondary btn-lg btn-block');
    $companyColumns['apps'] = new \Ease\TWB4\LinkButton('companyapps.php?company_id=' . $companyId, '🔁&nbsp;' . _('Applications'), 'secondary btn-lg btn-block');
    $companyColumns['delete'] = new \Ease\TWB4\LinkButton('companydelete.php?id=' . $companyId, '☠️&nbsp;' . _('Delete'), 'danger');

    $companyTable->addRowColumns($companyColumns);
}

$oPage->container->addItem($companyTable);

$oPage->addItem(new PageBottom());
$oPage->draw();
