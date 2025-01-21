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

require_once './init.php';

WebPage::singleton()->onlyForLogged();
$results = new \Ease\Html\UlTag();

$searchTerm = \Ease\WebPage::getRequestValue('search');

$runTemplater = new \MultiFlexi\RunTemplate();
$runtemplatesFound = $runTemplater->listingQuery()->where('name LIKE "%'.$searchTerm.'%"');

if ($runtemplatesFound->count()) {
    foreach ($runtemplatesFound as $runTemplate) {
        $results->addItemSmart(new \Ease\Html\ATag('runtemplate.php?id='.$runTemplate['id'], '⚗️&nbsp;'.$runTemplate['name']));
    }
}

$apper = new \MultiFlexi\Application();
$appsFound = $apper->listingQuery()->where('name LIKE "%'.$searchTerm.'%"')->whereOr('executable LIKE "%'.$searchTerm.'%"')->whereOr('uuid', $searchTerm);

if ($appsFound->count()) {
    foreach ($appsFound as $app) {
        $results->addItemSmart(new \Ease\Html\ATag('app.php?id='.$app['id'], new \Ease\Html\ImgTag('appimage.php?uuid='.$app['uuid'], $app['name'], ['height' => '20px']).'️&nbsp;'.$app['name']));
    }
}

$companer = new \MultiFlexi\Company();
$companyFound = $companer->listingQuery()->where('name LIKE "%'.$searchTerm.'%"');

if ($companyFound->count()) {
    foreach ($companyFound as $company) {
        $results->addItemSmart(new \Ease\Html\ATag('company.php?id='.$company['id'], new \Ease\Html\ImgTag($company['logo'], $company['name'], ['height' => '20px']).'️&nbsp;'.$company['name']));
    }
}

$jobber = new \MultiFlexi\Job();
$jobsFound = $jobber->listingQuery()->where('stdout LIKE "%'.$searchTerm.'%"')->whereOr('stderr LIKE "%'.$searchTerm.'%"');

if ($jobsFound->count()) {
    foreach ($jobsFound as $job) {
        $results->addItemSmart(new \Ease\Html\ATag('job.php?id='.$job['id'], '🏁 Job #'.$job['id']));
    }
}

$actionConfier = new \MultiFlexi\ActionConfig();
$actionConfigsFound = $actionConfier->listingQuery()->where('value LIKE "%'.$searchTerm.'%"');

if ($actionConfigsFound->count()) {
    foreach ($actionConfigsFound as $actionConfig) {
        $module = '\\MultiFlexi\\Action\\'.$actionConfig['module'];
        $moduleLogo = new \Ease\Html\ImgTag($module::logo(), $module::name(), ['title' => $module::description(), 'height' => 20]);
        $results->addItemSmart(new \Ease\Html\ATag('actions.php?id='.$actionConfig['runtemplate_id'], '🔧&nbsp; '._('RunTemplate').' #'.$actionConfig['runtemplate_id'].' '.$moduleLogo.' '.$actionConfig['keyname'].'='.$actionConfig['value']));
    }
}

$credentor = new \MultiFlexi\Credential();
$credentialsFound = $credentor->listingQuery()->where('name LIKE "%'.$searchTerm.'%"');

if ($credentialsFound->count()) {
    foreach ($credentialsFound as $credential) {
        $results->addItemSmart(new \Ease\Html\ATag('credential.php?id='.$credential['id'], '🔐 Credential #'.$credential['id'].' '.$credential['name']));
    }
}

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

WebPage::singleton()->container->addItem($results);

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
