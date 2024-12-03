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
WebPage::singleton()->addItem(new PageTop(_('Company') . ': ' . $companies->getRecordName()));

$kredenc = new \MultiFlexi\Credential();
$kredenc->setDataValue('company_id', $companies->getMyKey());

$creds = $kredenc->listingQuery()->where(['company_id' => $companies->getMyKey()])->fetchAll();
$credList = new \Ease\TWB4\Table();
$credList->addRowHeaderColumns(['', _('Name'), _('Type'), _('Used by')]);
$rtplcr = new \MultiFlexi\RunTplCreds();

foreach ($creds as $crd) {
    unset($crd['company_id']);
    $crd['name'] = new ATag('credential.php?id=' . $crd['id'], $crd['name']);

    $class = '\\MultiFlexi\\Ui\\Form\\' . $crd['formType'];

    if ($crd['formType'] && class_exists($class)) {
        $crd['id'] = new \Ease\Html\ImgTag($class::$logo, $crd['formType'], ['height' => '30px']);
    } else {
        $crd['id'] = '⁉️';
    }

    $runtlUsing = $rtplcr->getRuntemplatesForCredential($kredenc->getMyKey())->select(['runtemplate.name', 'company_id', 'app_id'])->leftJoin('runtemplate ON runtemplate.id = runtplcreds.runtemplate_id')->fetchAll();

    if ($runtlUsing) {

        $runtemplatesDiv = new \Ease\Html\DivTag();

        foreach ($runtlUsing as $runtemplateData) {
            $linkProperties['title'] = $runtemplateData['name'];
            $lastJobInfo = $jobber->listingQuery()->select(['id', 'exitcode'], true)->where(['company_id' => $runtemplateData['company_id'], 'app_id' => $runtemplateData['app_id']])->order('id DESC')->limit(1)->fetchAll();

            if ($lastJobInfo) {
                $companyAppStatus = new \Ease\Html\ATag('job.php?id=' . $lastJobInfo[0]['id'], new ExitCode($lastJobInfo[0]['exitcode'], ['style' => 'font-size: 1.0em; font-family: monospace;']), ['class' => 'btn btn-inverse btn-sm']);
            } else {
                $companyAppStatus = new \Ease\TWB4\Badge('disabled', '🪤', ['style' => 'font-size: 1.0em; font-family: monospace;']);
            }

            $runtemplatesDiv->addItem(new \Ease\Html\SpanTag([new \Ease\Html\ATag('runtemplate.php?id=' . $runtemplateData['id'], '⚗️#' . $runtemplateData['id'], ['class' => 'btn btn-inverse btn-sm', 'title' => $runtemplateData['name']]), $companyAppStatus], ['class' => 'btn-group', 'role' => 'group']));
        }


        $crd['used'] = _('Used by') . '(' . \count($runtlUsing) . ')' . $runtemplatesDiv;
    } else {
        $crd['used'] = '';
    }

    $credList->addRowColumns($crd);
}

$companyPanelContents[] = $credList;
$bottomLine = new \Ease\TWB4\LinkButton('credential.php?company_id=' . $companies->getMyKey(), '️➕ 🔐' . _('Create'), 'info btn-lg btn-block');

WebPage::singleton()->container->addItem(new CompanyPanel($companies, $companyPanelContents, $bottomLine));
WebPage::singleton()->addItem(new PageBottom('company/' . $companies->getMyKey()));
WebPage::singleton()->draw();
