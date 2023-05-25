<?php

/**
 * Multi Flexi - Scheduled actions executor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

use AbraFlexi\MultiFlexi\Application;
use AbraFlexi\MultiFlexi\Company;
use AbraFlexi\MultiFlexi\Configuration;

require_once '../vendor/autoload.php';
\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');

define('EASE_LOGGER', 'syslog|\AbraFlexi\MultiFlexi\LogToSQL');
\Ease\Shared::user(new \Ease\Anonym());
$companer = new Company();
$companys = $companer->listingQuery()->select('abraflexis.*')->select('company.id AS company_id')->leftJoin('abraflexis ON abraflexis.id = company.abraflexi');
$customConfig = new Configuration();
$interval = $argc == 2 ? $argv[1] : null;
if ($interval) {
    $ap2c = new AppToCompany();
    foreach ($companys as $company) {
        LogToSQL::singleton()->setCompany($company['company_id']);
        $appsForCompany = $ap2c->getColumnsFromSQL(['id', 'interv'], ['company_id' => $company['company_id'], 'interv' => $interval]);
        if (empty($appsForCompany)) {
            $companer->addStatusMessage(sprintf(_('No applications to run for %s in interval %s'), $company['nazev'], $interval), 'debug');
        } else {
            foreach ($appsForCompany as $servData) {
                if (!is_null($interval) && ($interval != $servData['interv'])) {
                    continue;
                }
                $jobber = new Job();
                $jobber->performJob($servData['id']);
            }
        }
    }
} else {
    echo "interval y/m/w/d/h missing\n";
    exit(1);
}
