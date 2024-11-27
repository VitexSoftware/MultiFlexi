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
$companies = new Company(WebPage::getRequestValue('company_id', 'int'));
WebPage::singleton()->addItem(new PageTop(_('Company').': '.$companies->getRecordName()));

$kredenc = new \MultiFlexi\Credential();
$kredenc->setDataValue('company_id', $companies->getMyKey());

$creds = $kredenc->listingQuery()->where(['company_id' => $companies->getMyKey()])->fetchAll();
$credList = new \Ease\TWB4\Table();
$credList->addRowHeaderColumns(['', _('Name'), _('Type')]);

foreach ($creds as $job) {
    unset($job['company_id']);
    $job['name'] = new ATag('credential.php?id='.$job['id'], $job['name']);

    $class = '\\MultiFlexi\\Ui\\Form\\'.$job['formType'];

    if ($job['formType'] && class_exists($class)) {
        $job['id'] = new \Ease\Html\ImgTag($class::$logo, $job['formType'], ['height' => '30px']);
    } else {
        $job['id'] = '⁉️';
    }

    $credList->addRowColumns($job);
}

$companyPanelContents[] = $credList;
$bottomLine = new \Ease\TWB4\LinkButton('credential.php?company_id='.$companies->getMyKey(), '️➕ 🔐'._('Create'), 'info btn-lg btn-block');

WebPage::singleton()->container->addItem(new CompanyPanel($companies, $companyPanelContents, $bottomLine));
WebPage::singleton()->addItem(new PageBottom('company/'.$companies->getMyKey()));
WebPage::singleton()->draw();
