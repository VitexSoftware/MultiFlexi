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

use Ease\Html\ATag;
use MultiFlexi\Company;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

WebPage::singleton()->addItem(new PageTop(_('Credentials')));

$kredenc = new \MultiFlexi\Credential();

$creds = $kredenc->listingQuery()->select(['company.name AS company'])->leftJoin('company ON credentials.company_id = company.id')->fetchAll();
$credList = new \Ease\TWB4\Table();
$credList->addRowHeaderColumns(['', _('Name'), _('Type'), _('Company')]);

foreach ($creds as $crd) {
    $crd['name'] = new ATag('credential.php?id='.$crd['id'], $crd['name']);

    $class = '\\MultiFlexi\\Ui\\Form\\'.$crd['formType'];

    if ($crd['formType'] && class_exists($class)) {
        $crd['id'] = new \Ease\Html\ImgTag($class::$logo, $crd['formType'], ['height' => '30px']);
    } else {
        $crd['id'] = '⁉️';
    }

    $crd['company'] = new ATag('company.php?id='.$crd['company_id'], new CompanyLogo(new Company($crd['company_id']), ['height' => '20px']));
    unset($crd['company_id']);

    $credList->addRowColumns($crd);
}

$companyPanelContents[] = $credList;

WebPage::singleton()->container->addItem($companyPanelContents);
WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
