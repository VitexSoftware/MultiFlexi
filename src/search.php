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
$what = \Ease\WebPage::getRequestValue('what');

if (strpos($searchTerm, '#') === 0) {
    $searchTerm = substr($searchTerm, 1);
}

function addResultItem($results, $url, $label, $column, $content): void
{
    $results->addItemSmart(new \Ease\Html\ATag($url, $label.' - '.$column.': '.$content));
}

if ($what === 'all' || $what === 'RunTemplate') {
    $runTemplater = new \MultiFlexi\RunTemplate();
    $runtemplatesFound = $runTemplater->listingQuery()->where('name LIKE "%'.$searchTerm.'%"')->whereOr(['id' => $searchTerm]);

    if ($runtemplatesFound->count()) {
        foreach ($runtemplatesFound as $runTemplate) {
            addResultItem($results, 'runtemplate.php?id='.$runTemplate['id'], '⚗️ '.$runTemplate['name'], 'name', $runTemplate['name']);
        }
    }
}

if ($what === 'all' || $what === 'Application') {
    $apper = new \MultiFlexi\Application();
    $appsFound = $apper->listingQuery()->where('name LIKE "%'.$searchTerm.'%"')->whereOr('executable LIKE "%'.$searchTerm.'%"')->whereOr('uuid', $searchTerm)->whereOr(['id' => $searchTerm]);

    if ($appsFound->count()) {
        foreach ($appsFound as $app) {
            if (stripos($app['name'], $searchTerm) !== false) {
                addResultItem($results, 'app.php?id='.$app['id'], '🖥️ '.$app['name'], 'name', $app['name']);
            } elseif (stripos($app['executable'], $searchTerm) !== false) {
                addResultItem($results, 'app.php?id='.$app['id'], '🖥️ '.$app['name'], 'executable', $app['executable']);
            } elseif (stripos($app['uuid'], $searchTerm) !== false) {
                addResultItem($results, 'app.php?id='.$app['id'], '🖥️ '.$app['name'], 'uuid', $app['uuid']);
            } elseif ($app['id'] === $searchTerm) {
                addResultItem($results, 'app.php?id='.$app['id'], '🖥️ '.$app['name'], 'id', $app['id']);
            }
        }
    }
}

if ($what === 'all' || $what === 'Company') {
    $companer = new \MultiFlexi\Company();
    $companyFound = $companer->listingQuery()->where('name LIKE "%'.$searchTerm.'%"')->whereOr(['id' => $searchTerm]);

    if ($companyFound->count()) {
        foreach ($companyFound as $company) {
            addResultItem($results, 'company.php?id='.$company['id'], '🏢 '.$company['name'], 'name', $company['name']);
        }
    }
}

if ($what === 'all' || $what === 'Job') {
    $jobber = new \MultiFlexi\Job();
    $jobsFound = $jobber->listingQuery()->where('stdout LIKE "%'.$searchTerm.'%"')->whereOr('stderr LIKE "%'.$searchTerm.'%"')->whereOr(['id' => $searchTerm]);

    if ($jobsFound->count()) {
        foreach ($jobsFound as $job) {
            if (stripos($job['stdout'], $searchTerm) !== false) {
                addResultItem($results, 'job.php?id='.$job['id'], '🏁 Job #'.$job['id'], 'stdout', $job['stdout']);
            } elseif (stripos($job['stderr'], $searchTerm) !== false) {
                addResultItem($results, 'job.php?id='.$job['id'], '🏁 Job #'.$job['id'], 'stderr', $job['stderr']);
            } elseif ($job['id'] === $searchTerm) {
                addResultItem($results, 'job.php?id='.$job['id'], '🏁 Job #'.$job['id'], 'id', $job['id']);
            }
        }
    }
}

if ($what === 'all' || $what === 'Credential') {
    $credentor = new \MultiFlexi\Credential();
    $credentialsFound = $credentor->listingQuery()->where('name LIKE "%'.$searchTerm.'%"')->whereOr(['id' => $searchTerm]);

    if ($credentialsFound->count()) {
        foreach ($credentialsFound as $credential) {
            addResultItem($results, 'credential.php?id='.$credential['id'], '🔐 Credential #'.$credential['id'].' '.$credential['name'], 'name', $credential['name']);
        }
    }
}

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

WebPage::singleton()->container->addItem($results);

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
