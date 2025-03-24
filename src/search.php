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

if (str_starts_with($searchTerm, '#')) {
    $searchTerm = substr($searchTerm, 1);
}

function addResultItem($results, $url, $label, $column, $content): void
{
    $results->addItemSmart(new \Ease\Html\ATag($url, $label.' - '.$column.': '.$content));
}

$foundItems = [];

if ($what === 'all' || $what === 'RunTemplate') {
    $runTemplater = new \MultiFlexi\RunTemplate();
    $runtemplatesFound = $runTemplater->listingQuery()->where('name LIKE "%'.$searchTerm.'%"')->whereOr(['id' => $searchTerm]);

    if ($runtemplatesFound->count()) {
        foreach ($runtemplatesFound as $runTemplate) {
            $foundItems[] = 'runtemplate.php?id='.$runTemplate['id'];
            addResultItem($results, 'runtemplate.php?id='.$runTemplate['id'], '⚗️ '.$runTemplate['name'], 'name', $runTemplate['name']);
        }
    }
}

if ($what === 'all' || $what === 'Application') {
    $apper = new \MultiFlexi\Application();
    $appsFound = $apper->listingQuery()->where('name LIKE "%'.$searchTerm.'%"')->whereOr('executable LIKE "%'.$searchTerm.'%"')->whereOr('uuid', $searchTerm)->whereOr(['id' => $searchTerm]);

    if ($appsFound->count()) {
        foreach ($appsFound as $app) {
            if (str_contains(strtolower($app['name']), strtolower($searchTerm))) {
                $foundItems[] = 'app.php?id='.$app['id'];
                addResultItem($results, 'app.php?id='.$app['id'], '🖥️ '.$app['name'], 'name', $app['name']);
            } elseif (str_contains(strtolower($app['executable']), strtolower($searchTerm))) {
                $foundItems[] = 'app.php?id='.$app['id'];
                addResultItem($results, 'app.php?id='.$app['id'], '🖥️ '.$app['name'], 'executable', $app['executable']);
            } elseif (str_contains(strtolower($app['uuid']), strtolower($searchTerm))) {
                $foundItems[] = 'app.php?id='.$app['id'];
                addResultItem($results, 'app.php?id='.$app['id'], '🖥️ '.$app['name'], 'uuid', $app['uuid']);
            } elseif ($app['id'] === $searchTerm) {
                $foundItems[] = 'app.php?id='.$app['id'];
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
            $foundItems[] = 'company.php?id='.$company['id'];
            addResultItem($results, 'company.php?id='.$company['id'], '🏢 '.$company['name'], 'name', $company['name']);
        }
    }
}

if ($what === 'all' || $what === 'Job') {
    $jobber = new \MultiFlexi\Job();
    $jobsFound = $jobber->listingQuery()->where('stdout LIKE "%'.$searchTerm.'%"')->whereOr('stderr LIKE "%'.$searchTerm.'%"')->whereOr(['id' => $searchTerm]);

    if ($jobsFound->count()) {
        foreach ($jobsFound as $job) {
            if (str_contains(strtolower($job['stdout']), strtolower($searchTerm))) {
                $foundItems[] = 'job.php?id='.$job['id'];
                addResultItem($results, 'job.php?id='.$job['id'], '🏁 Job #'.$job['id'], 'stdout', $job['stdout']);
            } elseif (str_contains(strtolower($job['stderr']), strtolower($searchTerm))) {
                $foundItems[] = 'job.php?id='.$job['id'];
                addResultItem($results, 'job.php?id='.$job['id'], '🏁 Job #'.$job['id'], 'stderr', $job['stderr']);
            } elseif ($job['id'] === $searchTerm) {
                $foundItems[] = 'job.php?id='.$job['id'];
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
            $foundItems[] = 'credential.php?id='.$credential['id'];
            addResultItem($results, 'credential.php?id='.$credential['id'], '🔐 Credential #'.$credential['id'].' '.$credential['name'], 'name', $credential['name']);
        }
    }
}

// Redirect if only one result is found
if (\count($foundItems) === 1) {
    header('Location: '.$foundItems[0]);

    exit;
}

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

WebPage::singleton()->container->addItem($results);

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
